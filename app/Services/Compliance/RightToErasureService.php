<?php

namespace App\Services\Compliance;

use App\Models\ConsentRecord;
use App\Models\DataDeletionRequest;
use App\Models\SupportTicket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class RightToErasureService
{
    /**
     * The AuditLogger instance for logging erasure actions.
     *
     * @var AuditLogger
     */
    protected AuditLogger $auditLogger;

    /**
     * Create a new RightToErasureService instance.
     *
     * @param AuditLogger $auditLogger
     */
    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Request deletion of user data (GDPR right to erasure).
     *
     * @param User|int $user The user requesting deletion (or user ID)
     * @param string|null $email The email address for the request
     * @param array $metadata Additional metadata for the request
     * @return DataDeletionRequest The created deletion request
     * @throws \Exception If right to erasure is disabled
     */
    public function requestDeletion(User|int $user, ?string $email = null, array $metadata = []): DataDeletionRequest
    {
        if (!$this->isRightToErasureEnabled()) {
            throw new \Exception('Right to erasure is disabled');
        }

        $userId = is_object($user) ? $user->id : $user;
        $userModel = is_object($user) ? $user : User::find($userId);

        if (!$userModel) {
            throw new \Exception('User not found');
        }

        // Use provided email or user's email
        $requestEmail = $email ?? $userModel->email;

        // Check if there's already a pending request for this user
        $existingRequest = DataDeletionRequest::where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($existingRequest) {
            throw new \Exception('A deletion request is already pending for this user');
        }

        DB::beginTransaction();

        try {
            // Create the deletion request
            $deletionRequest = DataDeletionRequest::create([
                'user_id' => $userId,
                'email' => $requestEmail,
                'notes' => $metadata['notes'] ?? 'User requested data deletion',
            ]);

            // Log the deletion request
            $this->auditLogger->logCustomEvent(
                'deletion_requested',
                $userModel,
                null,
                [],
                [
                    'request_id' => $deletionRequest->id,
                    'email' => $requestEmail,
                ],
                ['gdpr', 'right_to_erasure', 'deletion_request'],
                $userId
            );

            // Send notification if configured
            if (Config::get('compliance.notifications.enabled') &&
                Config::get('compliance.notifications.notify_on.data_deletion_request')) {
                $this->notifyComplianceOfficers($deletionRequest);
            }

            DB::commit();

            Log::info('Data deletion request created', [
                'request_id' => $deletionRequest->id,
                'user_id' => $userId,
                'email' => $requestEmail,
            ]);

            return $deletionRequest;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating deletion request', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process a data deletion request.
     *
     * @param DataDeletionRequest|int $request The deletion request to process
     * @param User|int|null $processedBy The user processing the request
     * @param bool $force Whether to force deletion even if anonymization is preferred
     * @return array Summary of the deletion process
     * @throws \Exception If the request cannot be processed
     */
    public function processRequest(
        DataDeletionRequest|int $request,
        User|int|null $processedBy = null,
        bool $force = false
    ): array {
        if (!$this->isRightToErasureEnabled()) {
            throw new \Exception('Right to erasure is disabled');
        }

        $deletionRequest = is_object($request) ? $request : DataDeletionRequest::find($request);

        if (!$deletionRequest) {
            throw new \Exception('Deletion request not found');
        }

        if (!$deletionRequest->isPending()) {
            throw new \Exception('Deletion request is not pending');
        }

        // Mark as processing
        $deletionRequest->markAsProcessing();

        $processorId = null;
        if ($processedBy) {
            $processorId = is_object($processedBy) ? $processedBy->id : $processedBy;
        } else {
            $processorId = Auth::id();
        }

        DB::beginTransaction();

        try {
            $user = $deletionRequest->user;

            if (!$user) {
                throw new \Exception('User not found for deletion request');
            }

            // Determine whether to anonymize or delete
            $anonymize = Config::get('compliance.gdpr.right_to_erasure.anonymize_data', true) && !$force;

            $summary = [
                'request_id' => $deletionRequest->id,
                'user_id' => $user->id,
                'email' => $deletionRequest->email,
                'method' => $anonymize ? 'anonymization' : 'deletion',
                'models_processed' => [],
                'total_records_anonymized' => 0,
                'total_records_deleted' => 0,
                'status' => 'success',
            ];

            if ($anonymize) {
                // Anonymize user data
                $anonymizationResult = $this->anonymizeData($user);
                $summary['models_processed'] = $anonymizationResult['models_processed'];
                $summary['total_records_anonymized'] = $anonymizationResult['total_records'];
            } else {
                // Delete user data
                $deletionResult = $this->deleteUserData($user);
                $summary['models_processed'] = $deletionResult['models_processed'];
                $summary['total_records_deleted'] = $deletionResult['total_records'];
            }

            // Mark the request as completed
            $deletionRequest->markAsCompleted($processorId, 'Data successfully processed');

            // Log the completion
            $this->auditLogger->logCustomEvent(
                'deletion_completed',
                $user,
                null,
                [],
                [
                    'request_id' => $deletionRequest->id,
                    'method' => $summary['method'],
                    'records_processed' => $summary['total_records_anonymized'] + $summary['total_records_deleted'],
                ],
                ['gdpr', 'right_to_erasure', 'deletion_completed'],
                $processorId
            );

            // Send confirmation email if configured
            if (Config::get('compliance.gdpr.right_to_erasure.send_confirmation')) {
                $this->sendConfirmationEmail($deletionRequest);
            }

            DB::commit();

            Log::info('Data deletion request processed', [
                'request_id' => $deletionRequest->id,
                'user_id' => $user->id,
                'method' => $summary['method'],
                'records_processed' => $summary['total_records_anonymized'] + $summary['total_records_deleted'],
            ]);

            return $summary;
        } catch (\Exception $e) {
            DB::rollBack();

            // Mark the request as failed
            $deletionRequest->markAsFailed($processorId, 'Error: ' . $e->getMessage());

            Log::error('Error processing deletion request', [
                'request_id' => $deletionRequest->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Export all user data for GDPR data portability.
     *
     * @param User|int $user The user whose data to export
     * @param string $format The export format (json, csv, pdf)
     * @param bool $includeAuditLogs Whether to include audit logs in the export
     * @return array The exported user data
     * @throws \Exception If data portability is disabled
     */
    public function exportUserData(
        User|int $user,
        string $format = 'json',
        bool $includeAuditLogs = false
    ): array {
        if (!$this->isDataPortabilityEnabled()) {
            throw new \Exception('Data portability is disabled');
        }

        $userModel = is_object($user) ? $user : User::find($user);

        if (!$userModel) {
            throw new \Exception('User not found');
        }

        // Validate format
        $availableFormats = Config::get('compliance.gdpr.data_portability.export_formats', ['json']);
        if (!in_array($format, $availableFormats)) {
            throw new \Exception("Export format '{$format}' is not supported");
        }

        $exportData = [
            'export_date' => now()->toIso8601String(),
            'user_id' => $userModel->id,
            'format' => $format,
            'data' => [],
        ];

        // Export user profile data
        $exportData['data']['user'] = [
            'id' => $userModel->id,
            'name' => $userModel->name,
            'email' => $userModel->email,
            'created_at' => $userModel->created_at?->toIso8601String(),
            'updated_at' => $userModel->updated_at?->toIso8601String(),
        ];

        // Export consent records
        $consentRecords = ConsentRecord::where('user_id', $userModel->id)->get();
        $exportData['data']['consents'] = $consentRecords->map(function ($consent) {
            return [
                'id' => $consent->id,
                'consent_type' => $consent->consent_type,
                'purpose' => $consent->purpose,
                'given_at' => $consent->given_at?->toIso8601String(),
                'withdrawn_at' => $consent->withdrawn_at?->toIso8601String(),
                'is_active' => $consent->isActive(),
            ];
        })->toArray();

        // Export support tickets
        $supportTickets = SupportTicket::where('user_id', $userModel->id)->get();
        $exportData['data']['support_tickets'] = $supportTickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'subject' => $ticket->subject ?? null,
                'status' => $ticket->status ?? null,
                'priority' => $ticket->priority ?? null,
                'created_at' => $ticket->created_at?->toIso8601String(),
                'updated_at' => $ticket->updated_at?->toIso8601String(),
            ];
        })->toArray();

        // Export ticket messages if applicable
        $ticketIds = $supportTickets->pluck('id')->toArray();
        if (!empty($ticketIds)) {
            $ticketMessages = TicketMessage::whereIn('ticket_id', $ticketIds)->get();
            $exportData['data']['ticket_messages'] = $ticketMessages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'ticket_id' => $message->ticket_id,
                    'message' => $message->message ?? $message->content ?? null,
                    'created_at' => $message->created_at?->toIso8601String(),
                ];
            })->toArray();
        }

        // Export audit logs if requested
        $includeAuditLogsConfig = Config::get('compliance.gdpr.data_portability.include_audit_logs', false);
        if ($includeAuditLogs || $includeAuditLogsConfig) {
            $auditLogs = \App\Models\AuditLog::where('user_id', $userModel->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $exportData['data']['audit_logs'] = $auditLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'event' => $log->event,
                    'auditable_type' => $log->auditable_type,
                    'auditable_id' => $log->auditable_id,
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            })->toArray();
        }

        // Log the export
        $this->auditLogger->logExport(
            $userModel,
            null,
            [
                'format' => $format,
                'include_audit_logs' => $includeAuditLogs || $includeAuditLogsConfig,
                'record_count' => array_sum(array_map('count', $exportData['data'])),
            ],
            ['gdpr', 'data_portability', 'export'],
            $userModel->id
        );

        Log::info('User data exported', [
            'user_id' => $userModel->id,
            'format' => $format,
            'record_count' => array_sum(array_map('count', $exportData['data'])),
        ]);

        return $exportData;
    }

    /**
     * Anonymize user data instead of deleting it.
     *
     * @param User|int $user The user whose data to anonymize
     * @return array Summary of the anonymization process
     * @throws \Exception If anonymization fails
     */
    public function anonymizeData(User|int $user): array
    {
        $userModel = is_object($user) ? $user : User::find($user);

        if (!$userModel) {
            throw new \Exception('User not found');
        }

        $summary = [
            'user_id' => $userModel->id,
            'models_processed' => [],
            'total_records' => 0,
        ];

        DB::beginTransaction();

        try {
            // Get erasable models from config
            $erasableModels = Config::get('compliance.gdpr.right_to_erasure.erasable_models', []);

            // Anonymize the user record
            if (in_array('User', $erasableModels)) {
                $this->anonymizeUser($userModel);
                $summary['models_processed'][] = [
                    'model' => 'User',
                    'count' => 1,
                ];
                $summary['total_records']++;
            }

            // Anonymize consent records
            if (in_array('ConsentRecord', $erasableModels)) {
                $consentCount = $this->anonymizeUserConsents($userModel);
                if ($consentCount > 0) {
                    $summary['models_processed'][] = [
                        'model' => 'ConsentRecord',
                        'count' => $consentCount,
                    ];
                    $summary['total_records'] += $consentCount;
                }
            }

            // Anonymize support tickets
            if (in_array('SupportTicket', $erasableModels)) {
                $ticketCount = $this->anonymizeUserTickets($userModel);
                if ($ticketCount > 0) {
                    $summary['models_processed'][] = [
                        'model' => 'SupportTicket',
                        'count' => $ticketCount,
                    ];
                    $summary['total_records'] += $ticketCount;
                }
            }

            DB::commit();

            Log::info('User data anonymized', [
                'user_id' => $userModel->id,
                'total_records' => $summary['total_records'],
            ]);

            return $summary;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error anonymizing user data', [
                'user_id' => $userModel->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Protected helper methods
     */

    /**
     * Delete user data permanently.
     *
     * @param User $user The user whose data to delete
     * @return array Summary of the deletion process
     */
    protected function deleteUserData(User $user): array
    {
        $summary = [
            'user_id' => $user->id,
            'models_processed' => [],
            'total_records' => 0,
        ];

        // Get erasable models from config
        $erasableModels = Config::get('compliance.gdpr.right_to_erasure.erasable_models', []);

        // Delete consent records
        if (in_array('ConsentRecord', $erasableModels)) {
            $consentCount = ConsentRecord::where('user_id', $user->id)->count();
            ConsentRecord::where('user_id', $user->id)->delete();
            if ($consentCount > 0) {
                $summary['models_processed'][] = [
                    'model' => 'ConsentRecord',
                    'count' => $consentCount,
                ];
                $summary['total_records'] += $consentCount;
            }
        }

        // Delete support tickets and related data
        if (in_array('SupportTicket', $erasableModels)) {
            $tickets = SupportTicket::where('user_id', $user->id)->get();
            $ticketIds = $tickets->pluck('id')->toArray();
            $ticketCount = $tickets->count();

            // Delete ticket messages
            if (!empty($ticketIds)) {
                TicketMessage::whereIn('ticket_id', $ticketIds)->delete();
                TicketAttachment::whereIn('ticket_id', $ticketIds)->delete();
            }

            // Delete tickets
            SupportTicket::where('user_id', $user->id)->delete();

            if ($ticketCount > 0) {
                $summary['models_processed'][] = [
                    'model' => 'SupportTicket',
                    'count' => $ticketCount,
                ];
                $summary['total_records'] += $ticketCount;
            }
        }

        // Delete the user record
        if (in_array('User', $erasableModels)) {
            $user->delete();
            $summary['models_processed'][] = [
                'model' => 'User',
                'count' => 1,
            ];
            $summary['total_records']++;
        }

        return $summary;
    }

    /**
     * Anonymize a user record.
     *
     * @param User $user The user to anonymize
     * @return void
     */
    protected function anonymizeUser(User $user): void
    {
        $user->update([
            'name' => 'Anonymized User',
            'email' => 'anonymized_' . $user->id . '@deleted.local',
            'password' => bcrypt(bin2hex(random_bytes(32))),
            'remember_token' => null,
        ]);
    }

    /**
     * Anonymize user consent records.
     *
     * @param User $user The user whose consents to anonymize
     * @return int Number of consent records anonymized
     */
    protected function anonymizeUserConsents(User $user): int
    {
        $consents = ConsentRecord::where('user_id', $user->id)->get();

        foreach ($consents as $consent) {
            $consent->update([
                'ip_address' => '0.0.0.0',
                'user_agent' => 'Anonymized',
                'metadata' => json_encode(['anonymized' => true]),
            ]);
        }

        return $consents->count();
    }

    /**
     * Anonymize user support tickets.
     *
     * @param User $user The user whose tickets to anonymize
     * @return int Number of tickets anonymized
     */
    protected function anonymizeUserTickets(User $user): int
    {
        $tickets = SupportTicket::where('user_id', $user->id)->get();

        foreach ($tickets as $ticket) {
            // Check if the model has specific fields to anonymize
            $updateData = [];

            if (isset($ticket->subject)) {
                $updateData['subject'] = 'Anonymized Ticket';
            }

            if (isset($ticket->description)) {
                $updateData['description'] = 'This ticket has been anonymized per GDPR request.';
            }

            if (!empty($updateData)) {
                $ticket->update($updateData);
            }

            // Anonymize ticket messages
            $messages = TicketMessage::where('ticket_id', $ticket->id)->get();
            foreach ($messages as $message) {
                if (isset($message->message)) {
                    $message->update(['message' => 'Anonymized message']);
                } elseif (isset($message->content)) {
                    $message->update(['content' => 'Anonymized message']);
                }
            }
        }

        return $tickets->count();
    }

    /**
     * Send notification to compliance officers about a new deletion request.
     *
     * @param DataDeletionRequest $request The deletion request
     * @return void
     */
    protected function notifyComplianceOfficers(DataDeletionRequest $request): void
    {
        // This is a placeholder for notification logic
        // In a real implementation, you would send emails or notifications to compliance officers
        $officers = Config::get('compliance.notifications.compliance_officers', []);

        if (empty($officers)) {
            return;
        }

        Log::info('Compliance officers notified of deletion request', [
            'request_id' => $request->id,
            'officers' => $officers,
        ]);
    }

    /**
     * Send confirmation email to the user after deletion is processed.
     *
     * @param DataDeletionRequest $request The deletion request
     * @return void
     */
    protected function sendConfirmationEmail(DataDeletionRequest $request): void
    {
        // This is a placeholder for email confirmation logic
        // In a real implementation, you would send a confirmation email
        Log::info('Deletion confirmation email sent', [
            'request_id' => $request->id,
            'email' => $request->email,
        ]);
    }

    /**
     * Check if right to erasure is enabled.
     *
     * @return bool
     */
    protected function isRightToErasureEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.gdpr.enabled', true) &&
               Config::get('compliance.gdpr.right_to_erasure.enabled', true);
    }

    /**
     * Check if data portability is enabled.
     *
     * @return bool
     */
    protected function isDataPortabilityEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.gdpr.enabled', true) &&
               Config::get('compliance.gdpr.data_portability.enabled', true);
    }
}
