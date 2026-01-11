<?php

namespace App\Services\Compliance;

use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ComplianceMetrics
{
    /**
     * Get comprehensive compliance metrics overview.
     *
     * @param array $options Optional parameters for filtering metrics
     * @return array Comprehensive compliance metrics
     */
    public function getOverview(array $options = []): array
    {
        $startDate = $options['start_date'] ?? now()->subDays(30);
        $endDate = $options['end_date'] ?? now();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'audit_logging' => $this->getAuditLogMetrics($startDate, $endDate),
            'consent_management' => $this->getConsentMetrics($startDate, $endDate),
            'data_retention' => $this->getRetentionMetrics(),
            'encryption' => $this->getEncryptionMetrics(),
            'compliance_status' => $this->getComplianceStatus(),
            'generated_at' => now(),
        ];
    }

    /**
     * Get audit log metrics.
     *
     * @param \DateTimeInterface|null $startDate Start date for metrics
     * @param \DateTimeInterface|null $endDate End date for metrics
     * @return array Audit log metrics
     */
    public function getAuditLogMetrics($startDate = null, $endDate = null): array
    {
        if (!$this->isAuditLoggingEnabled()) {
            return [
                'enabled' => false,
                'total_logs' => 0,
            ];
        }

        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $query = AuditLog::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'enabled' => true,
            'total_logs' => AuditLog::count(),
            'logs_in_period' => $query->count(),
            'by_event' => $this->getAuditLogsByEvent($startDate, $endDate),
            'by_model' => $this->getAuditLogsByModel($startDate, $endDate),
            'by_user' => $this->getAuditLogsByUser($startDate, $endDate),
            'recent_activity' => $this->getRecentAuditActivity(),
            'retention_config' => [
                'retention_days' => Config::get('compliance.audit_logging.retention_days', 2555),
                'capture_ip' => Config::get('compliance.audit_logging.capture_ip', true),
                'capture_user_agent' => Config::get('compliance.audit_logging.capture_user_agent', true),
            ],
        ];
    }

    /**
     * Get consent management metrics.
     *
     * @param \DateTimeInterface|null $startDate Start date for metrics
     * @param \DateTimeInterface|null $endDate End date for metrics
     * @return array Consent metrics
     */
    public function getConsentMetrics($startDate = null, $endDate = null): array
    {
        if (!$this->isConsentManagementEnabled()) {
            return [
                'enabled' => false,
                'total_consents' => 0,
            ];
        }

        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $totalConsents = ConsentRecord::count();
        $activeConsents = ConsentRecord::active()->count();
        $withdrawnConsents = ConsentRecord::withdrawn()->count();

        $consentsGivenInPeriod = ConsentRecord::givenBetween($startDate, $endDate)->count();
        $consentsWithdrawnInPeriod = ConsentRecord::withdrawnBetween($startDate, $endDate)->count();

        return [
            'enabled' => true,
            'total_consents' => $totalConsents,
            'active_consents' => $activeConsents,
            'withdrawn_consents' => $withdrawnConsents,
            'consent_rate' => $totalConsents > 0 ? round(($activeConsents / $totalConsents) * 100, 2) : 0,
            'withdrawal_rate' => $totalConsents > 0 ? round(($withdrawnConsents / $totalConsents) * 100, 2) : 0,
            'period_activity' => [
                'consents_given' => $consentsGivenInPeriod,
                'consents_withdrawn' => $consentsWithdrawnInPeriod,
            ],
            'by_type' => $this->getConsentsByType(),
            'by_user_stats' => $this->getConsentUserStatistics(),
            'required_consents' => $this->getRequiredConsentsCompliance(),
            'config' => [
                'types_configured' => count(Config::get('compliance.consent.types', [])),
                'explicit_consent_required' => Config::get('compliance.consent.require_explicit_consent', true),
            ],
        ];
    }

    /**
     * Get data retention compliance metrics.
     *
     * @return array Retention metrics
     */
    public function getRetentionMetrics(): array
    {
        if (!$this->isDataRetentionEnabled()) {
            return [
                'enabled' => false,
                'total_policies' => 0,
            ];
        }

        $totalPolicies = DataRetentionPolicy::count();
        $activePolicies = DataRetentionPolicy::active()->count();
        $inactivePolicies = DataRetentionPolicy::inactive()->count();

        $expiredRecordsCount = 0;
        $deletableRecordsCount = 0;
        $policiesWithExpiredData = [];

        $policies = DataRetentionPolicy::active()->get();
        foreach ($policies as $policy) {
            $expiredRecords = $policy->getExpiredRecords();
            $deletableRecords = $policy->getDeletableRecords();

            $expiredCount = $expiredRecords->count();
            $deletableCount = $deletableRecords->count();

            $expiredRecordsCount += $expiredCount;
            $deletableRecordsCount += $deletableCount;

            if ($expiredCount > 0 || $deletableCount > 0) {
                $policiesWithExpiredData[] = [
                    'policy_id' => $policy->id,
                    'model_type' => $policy->model_type,
                    'retention_days' => $policy->retention_period_days,
                    'delete_after_days' => $policy->delete_after_days,
                    'expired_count' => $expiredCount,
                    'deletable_count' => $deletableCount,
                ];
            }
        }

        return [
            'enabled' => true,
            'total_policies' => $totalPolicies,
            'active_policies' => $activePolicies,
            'inactive_policies' => $inactivePolicies,
            'expired_records' => $expiredRecordsCount,
            'deletable_records' => $deletableRecordsCount,
            'policies_with_expired_data' => $policiesWithExpiredData,
            'compliance_status' => $deletableRecordsCount === 0 ? 'compliant' : 'action_required',
            'config' => [
                'auto_delete_enabled' => Config::get('compliance.data_retention.auto_delete', false),
                'prefer_anonymization' => Config::get('compliance.data_retention.prefer_anonymization', true),
                'grace_period_days' => Config::get('compliance.data_retention.grace_period_days', 30),
            ],
        ];
    }

    /**
     * Get encryption metrics.
     *
     * @return array Encryption metrics
     */
    public function getEncryptionMetrics(): array
    {
        if (!$this->isEncryptionEnabled()) {
            return [
                'enabled' => false,
                'encrypted_models' => [],
            ];
        }

        $encryptedFields = Config::get('compliance.encryption.encrypted_fields', []);
        $encryptedModels = [];

        foreach ($encryptedFields as $modelName => $fields) {
            if (!empty($fields)) {
                $encryptedModels[] = [
                    'model' => $modelName,
                    'fields' => $fields,
                    'field_count' => count($fields),
                ];
            }
        }

        // Additionally check for models with $encrypted property
        $modelsWithEncryption = $this->getModelsWithEncryptionTrait();

        return [
            'enabled' => true,
            'encrypted_models' => array_merge($encryptedModels, $modelsWithEncryption),
            'total_encrypted_models' => count($encryptedModels) + count($modelsWithEncryption),
            'config' => [
                'algorithm' => Config::get('compliance.encryption.algorithm', 'AES-256-CBC'),
                'auto_decrypt' => Config::get('compliance.encryption.auto_decrypt', true),
                'key_rotation_enabled' => Config::get('compliance.encryption.key_rotation.enabled', false),
                'key_rotation_days' => Config::get('compliance.encryption.key_rotation.rotation_days', 90),
            ],
        ];
    }

    /**
     * Get overall compliance status.
     *
     * @return array Compliance status
     */
    public function getComplianceStatus(): array
    {
        $issues = [];
        $warnings = [];

        // Check audit logging
        if (!$this->isAuditLoggingEnabled()) {
            $warnings[] = 'Audit logging is disabled';
        }

        // Check consent management
        if (!$this->isConsentManagementEnabled()) {
            $warnings[] = 'Consent management is disabled';
        }

        // Check for deletable records
        if ($this->isDataRetentionEnabled()) {
            $retentionMetrics = $this->getRetentionMetrics();
            if ($retentionMetrics['deletable_records'] > 0) {
                $issues[] = "{$retentionMetrics['deletable_records']} records should be deleted according to retention policies";
            }
        }

        // Check GDPR compliance
        if (Config::get('compliance.gdpr.enabled', true)) {
            if (!Config::get('compliance.gdpr.right_to_erasure.enabled', true)) {
                $warnings[] = 'GDPR right to erasure is disabled';
            }
            if (!Config::get('compliance.gdpr.data_portability.enabled', true)) {
                $warnings[] = 'GDPR data portability is disabled';
            }
        }

        $status = 'compliant';
        if (count($issues) > 0) {
            $status = 'non_compliant';
        } elseif (count($warnings) > 0) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'issues' => $issues,
            'warnings' => $warnings,
            'frameworks' => [
                'gdpr' => Config::get('compliance.gdpr.enabled', true),
                'hipaa' => Config::get('compliance.hipaa.enabled', false),
                'soc2' => Config::get('compliance.soc2.enabled', false),
            ],
            'last_checked' => now(),
        ];
    }

    /**
     * Get audit log counts by event type.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array
     */
    protected function getAuditLogsByEvent($startDate, $endDate): array
    {
        return AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->select('event', DB::raw('count(*) as count'))
            ->groupBy('event')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'event')
            ->toArray();
    }

    /**
     * Get audit log counts by model type.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array
     */
    protected function getAuditLogsByModel($startDate, $endDate): array
    {
        $results = AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->select('auditable_type', DB::raw('count(*) as count'))
            ->groupBy('auditable_type')
            ->orderBy('count', 'desc')
            ->get();

        $byModel = [];
        foreach ($results as $result) {
            $modelName = class_basename($result->auditable_type);
            $byModel[$modelName] = $result->count;
        }

        return $byModel;
    }

    /**
     * Get audit log counts by user (top 10).
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array
     */
    protected function getAuditLogsByUser($startDate, $endDate): array
    {
        $results = AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->with('user:id,name,email')
            ->get();

        $byUser = [];
        foreach ($results as $result) {
            $userName = $result->user ? $result->user->name : "User #{$result->user_id}";
            $byUser[$userName] = $result->count;
        }

        return $byUser;
    }

    /**
     * Get recent audit activity.
     *
     * @param int $limit
     * @return array
     */
    protected function getRecentAuditActivity(int $limit = 10): array
    {
        $recentLogs = AuditLog::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $recentLogs->map(function ($log) {
            return [
                'id' => $log->id,
                'event' => $log->event,
                'model' => class_basename($log->auditable_type),
                'user' => $log->user ? $log->user->name : 'System',
                'created_at' => $log->created_at,
            ];
        })->toArray();
    }

    /**
     * Get consent statistics by type.
     *
     * @return array
     */
    protected function getConsentsByType(): array
    {
        $stats = ConsentRecord::getStatsByType();

        return $stats->map(function ($stat) {
            $total = $stat->total;
            return [
                'type' => $stat->consent_type,
                'total' => $total,
                'active' => $stat->active,
                'withdrawn' => $stat->withdrawn,
                'consent_rate' => $total > 0 ? round(($stat->active / $total) * 100, 2) : 0,
                'withdrawal_rate' => $total > 0 ? round(($stat->withdrawn / $total) * 100, 2) : 0,
            ];
        })->toArray();
    }

    /**
     * Get user-level consent statistics.
     *
     * @return array
     */
    protected function getConsentUserStatistics(): array
    {
        $totalUsers = User::count();
        $usersWithConsents = ConsentRecord::distinct('user_id')->count('user_id');
        $usersWithActiveConsents = ConsentRecord::active()->distinct('user_id')->count('user_id');

        return [
            'total_users' => $totalUsers,
            'users_with_consents' => $usersWithConsents,
            'users_with_active_consents' => $usersWithActiveConsents,
            'consent_coverage' => $totalUsers > 0 ? round(($usersWithConsents / $totalUsers) * 100, 2) : 0,
        ];
    }

    /**
     * Get compliance rate for required consents.
     *
     * @return array
     */
    protected function getRequiredConsentsCompliance(): array
    {
        $consentTypes = Config::get('compliance.consent.types', []);
        $requiredTypes = array_filter($consentTypes, function ($config) {
            return $config['required'] ?? false;
        });

        $requiredConsentCompliance = [];
        $totalUsers = User::count();

        foreach ($requiredTypes as $type => $config) {
            $usersWithConsent = ConsentRecord::byType($type)
                ->active()
                ->distinct('user_id')
                ->count('user_id');

            $complianceRate = $totalUsers > 0 ? round(($usersWithConsent / $totalUsers) * 100, 2) : 0;

            $requiredConsentCompliance[] = [
                'type' => $type,
                'description' => $config['description'] ?? $type,
                'users_with_consent' => $usersWithConsent,
                'total_users' => $totalUsers,
                'compliance_rate' => $complianceRate,
                'status' => $complianceRate >= 95 ? 'compliant' : ($complianceRate >= 80 ? 'warning' : 'non_compliant'),
            ];
        }

        return $requiredConsentCompliance;
    }

    /**
     * Get models that use the Encryptable trait.
     *
     * @return array
     */
    protected function getModelsWithEncryptionTrait(): array
    {
        $modelsWithEncryption = [];

        // Check known models with Encryptable trait
        $modelsToCheck = [
            'App\Models\User',
            'App\Models\SupportTicket',
        ];

        foreach ($modelsToCheck as $modelClass) {
            if (class_exists($modelClass)) {
                $reflection = new \ReflectionClass($modelClass);

                // Check if model uses Encryptable trait
                if (in_array('App\Traits\Encryptable', array_keys($reflection->getTraits()))) {
                    try {
                        $model = new $modelClass();
                        $encryptedProperty = $reflection->getProperty('encrypted');
                        $encryptedProperty->setAccessible(true);
                        $fields = $encryptedProperty->getValue($model);

                        if (!empty($fields)) {
                            $modelsWithEncryption[] = [
                                'model' => class_basename($modelClass),
                                'fields' => $fields,
                                'field_count' => count($fields),
                            ];
                        }
                    } catch (\Exception $e) {
                        // Property doesn't exist or can't be accessed, skip
                        continue;
                    }
                }
            }
        }

        return $modelsWithEncryption;
    }

    /**
     * Check if audit logging is enabled.
     *
     * @return bool
     */
    protected function isAuditLoggingEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.audit_logging.enabled', true);
    }

    /**
     * Check if consent management is enabled.
     *
     * @return bool
     */
    protected function isConsentManagementEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.consent.enabled', true);
    }

    /**
     * Check if data retention is enabled.
     *
     * @return bool
     */
    protected function isDataRetentionEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.data_retention.enabled', true);
    }

    /**
     * Check if encryption is enabled.
     *
     * @return bool
     */
    protected function isEncryptionEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.encryption.enabled', true);
    }
}
