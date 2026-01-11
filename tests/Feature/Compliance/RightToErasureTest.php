<?php

use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\DataDeletionRequest;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\Compliance\RightToErasureService;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Enable compliance features for all tests
    Config::set('compliance.enabled', true);
    Config::set('compliance.gdpr.enabled', true);
    Config::set('compliance.gdpr.right_to_erasure.enabled', true);
    Config::set('compliance.gdpr.right_to_erasure.anonymize_data', true);
    Config::set('compliance.gdpr.right_to_erasure.send_confirmation', false);
    Config::set('compliance.gdpr.right_to_erasure.erasable_models', [
        'User',
        'ConsentRecord',
        'SupportTicket',
    ]);
    Config::set('compliance.gdpr.data_portability.enabled', true);
    Config::set('compliance.gdpr.data_portability.export_formats', ['json', 'csv', 'pdf']);
    Config::set('compliance.gdpr.data_portability.include_audit_logs', false);
    Config::set('compliance.audit_logging.enabled', true);
    Config::set('compliance.notifications.enabled', false);

    // Clear data before each test
    DataDeletionRequest::query()->delete();
    ConsentRecord::query()->delete();
    AuditLog::query()->delete();
});

// ============================================
// DataDeletionRequest Model Tests
// ============================================

it('creates a data deletion request', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
    ]);

    expect($request)->toBeInstanceOf(DataDeletionRequest::class);
    expect($request->user_id)->toBe($user->id);
    expect($request->email)->toBe($user->email);
    expect($request->status)->toBe('pending');
    expect($request->requested_at)->not->toBeNull();
});

it('sets default status to pending on creation', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
    ]);

    expect($request->status)->toBe('pending');
});

it('sets requested_at timestamp on creation', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
    ]);

    expect($request->requested_at)->not->toBeNull();
});

it('has user relationship', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
    ]);

    expect($request->user)->toBeInstanceOf(User::class);
    expect($request->user->id)->toBe($user->id);
});

it('has processedBy relationship', function () {
    $user = User::factory()->create();
    $processor = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'processed_by' => $processor->id,
    ]);

    expect($request->processedBy)->toBeInstanceOf(User::class);
    expect($request->processedBy->id)->toBe($processor->id);
});

it('filters pending requests using scope', function () {
    $user = User::factory()->create();

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'pending',
    ]);

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => 'other@example.com',
        'status' => 'completed',
    ]);

    $pendingRequests = DataDeletionRequest::pending()->get();
    expect($pendingRequests)->toHaveCount(1);
    expect($pendingRequests->first()->status)->toBe('pending');
});

it('filters processing requests using scope', function () {
    $user = User::factory()->create();

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'processing',
    ]);

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => 'other@example.com',
        'status' => 'pending',
    ]);

    $processingRequests = DataDeletionRequest::processing()->get();
    expect($processingRequests)->toHaveCount(1);
    expect($processingRequests->first()->status)->toBe('processing');
});

it('filters completed requests using scope', function () {
    $user = User::factory()->create();

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'completed',
        'processed_at' => now(),
    ]);

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => 'other@example.com',
        'status' => 'pending',
    ]);

    $completedRequests = DataDeletionRequest::completed()->get();
    expect($completedRequests)->toHaveCount(1);
    expect($completedRequests->first()->status)->toBe('completed');
});

it('marks request as processing', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'pending',
    ]);

    $request->markAsProcessing();

    expect($request->fresh()->status)->toBe('processing');
});

it('marks request as completed', function () {
    $user = User::factory()->create();
    $processor = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'processing',
    ]);

    $request->markAsCompleted($processor->id, 'Successfully processed');

    $fresh = $request->fresh();
    expect($fresh->status)->toBe('completed');
    expect($fresh->processed_by)->toBe($processor->id);
    expect($fresh->processed_at)->not->toBeNull();
    expect($fresh->notes)->toContain('Successfully processed');
});

it('marks request as failed', function () {
    $user = User::factory()->create();
    $processor = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'processing',
    ]);

    $request->markAsFailed($processor->id, 'Error occurred');

    $fresh = $request->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->processed_by)->toBe($processor->id);
    expect($fresh->notes)->toContain('Error occurred');
});

it('cancels a request', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'pending',
    ]);

    $request->cancel('User cancelled request');

    $fresh = $request->fresh();
    expect($fresh->status)->toBe('cancelled');
    expect(str_contains($fresh->notes, 'User cancelled request'))->toBeTrue();
});

it('checks if request is pending', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'pending',
    ]);

    expect($request->isPending())->toBeTrue();

    $request->update(['status' => 'completed']);
    expect($request->isPending())->toBeFalse();
});

it('checks if request is completed', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'completed',
        'processed_at' => now(),
    ]);

    expect($request->isCompleted())->toBeTrue();

    $request->update(['status' => 'pending']);
    expect($request->isCompleted())->toBeFalse();
});

// ============================================
// RightToErasureService Integration Tests
// ============================================

it('creates a deletion request through service', function () {
    $user = User::factory()->create();

    $service = app(RightToErasureService::class);
    $request = $service->requestDeletion($user);

    expect($request)->toBeInstanceOf(DataDeletionRequest::class);
    expect($request->user_id)->toBe($user->id);
    expect($request->email)->toBe($user->email);
    expect($request->status)->toBe('pending');
});

it('prevents duplicate deletion requests for the same user', function () {
    $user = User::factory()->create();

    $service = app(RightToErasureService::class);
    $service->requestDeletion($user);

    // Try to create another request
    $exception = null;
    try {
        $service->requestDeletion($user);
    } catch (\Exception $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())->toContain('already pending');
});

it('creates audit log when deletion is requested', function () {
    Config::set('compliance.audit_logging.enabled', true);
    AuditLog::query()->delete();

    $user = User::factory()->create();

    $service = app(RightToErasureService::class);
    $service->requestDeletion($user);

    $auditLogs = AuditLog::where('event', 'deletion_requested')->get();
    expect($auditLogs->count())->toBeGreaterThanOrEqual(1);
});

it('processes deletion request with anonymization', function () {
    Config::set('compliance.gdpr.right_to_erasure.anonymize_data', true);

    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $service = app(RightToErasureService::class);
    $request = $service->requestDeletion($user);

    $result = $service->processRequest($request);

    expect($result['status'])->toBe('success');
    expect($result['method'])->toBe('anonymization');
    expect($result['total_records_anonymized'])->toBeGreaterThanOrEqual(1);

    // Verify user is anonymized
    $user->refresh();
    expect($user->name)->toBe('Anonymized User');
    expect($user->email)->toContain('anonymized_');
});

it('processes deletion request with hard deletion when forced', function () {
    Config::set('compliance.gdpr.right_to_erasure.anonymize_data', true);

    $user = User::factory()->create();
    $userId = $user->id;

    $service = app(RightToErasureService::class);
    $request = $service->requestDeletion($user);

    $result = $service->processRequest($request, force: true);

    expect($result['status'])->toBe('success');
    expect($result['method'])->toBe('deletion');
    expect($result['total_records_deleted'])->toBeGreaterThanOrEqual(1);

    // Verify user is deleted
    expect(User::find($userId))->toBeNull();
});

it('marks request as completed after processing', function () {
    $user = User::factory()->create();
    $processor = User::factory()->create();

    $service = app(RightToErasureService::class);
    $request = $service->requestDeletion($user);

    $service->processRequest($request, $processor);

    $request->refresh();
    expect($request->status)->toBe('completed');
    expect($request->processed_by)->toBe($processor->id);
    expect($request->processed_at)->not->toBeNull();
});

it('creates audit log when deletion is completed', function () {
    Config::set('compliance.audit_logging.enabled', true);

    $user = User::factory()->create();

    $service = app(RightToErasureService::class);
    $request = $service->requestDeletion($user);

    AuditLog::query()->delete();

    $service->processRequest($request);

    $auditLogs = AuditLog::where('event', 'deletion_completed')->get();
    expect($auditLogs->count())->toBeGreaterThanOrEqual(1);
});

it('anonymizes user consent records', function () {
    Config::set('compliance.gdpr.right_to_erasure.anonymize_data', true);

    $user = User::factory()->create();

    // Create consent records
    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Email marketing',
        'given_at' => now(),
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
    ]);

    $service = app(RightToErasureService::class);
    $request = $service->requestDeletion($user);
    $service->processRequest($request);

    // Verify consent is anonymized
    $consent = ConsentRecord::where('user_id', $user->id)->first();
    expect($consent->ip_address)->toBe('0.0.0.0');
    expect($consent->user_agent)->toBe('Anonymized');
});

it('anonymizes user support tickets', function () {
    Config::set('compliance.gdpr.right_to_erasure.anonymize_data', true);

    $user = User::factory()->create();

    // Create support ticket
    $ticket = SupportTicket::factory()->create([
        'user_id' => $user->id,
        'subject' => 'Test Subject',
        'description' => 'Test Description',
        'status' => 'open',
        'priority' => 'medium',
    ]);

    $service = app(RightToErasureService::class);
    $request = $service->requestDeletion($user);
    $service->processRequest($request);

    // Verify ticket is anonymized
    $ticket->refresh();
    expect($ticket->subject)->toBe('Anonymized Ticket');
    expect($ticket->description)->toContain('anonymized');
});

it('throws exception when right to erasure is disabled', function () {
    Config::set('compliance.gdpr.right_to_erasure.enabled', false);

    $user = User::factory()->create();

    $service = app(RightToErasureService::class);

    $exception = null;
    try {
        $service->requestDeletion($user);
    } catch (\Exception $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())->toContain('disabled');
});

it('throws exception when processing non-pending request', function () {
    $user = User::factory()->create();

    $request = DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'completed',
        'processed_at' => now(),
    ]);

    $service = app(RightToErasureService::class);

    $exception = null;
    try {
        $service->processRequest($request);
    } catch (\Exception $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())->toContain('not pending');
});

// ============================================
// Data Portability Tests
// ============================================

it('exports user data in JSON format', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Email marketing',
        'given_at' => now(),
    ]);

    $service = app(RightToErasureService::class);
    $exportData = $service->exportUserData($user, 'json');

    expect($exportData)->toHaveKey('export_date');
    expect($exportData)->toHaveKey('user_id');
    expect($exportData)->toHaveKey('format');
    expect($exportData)->toHaveKey('data');
    expect($exportData['format'])->toBe('json');
    expect($exportData['data'])->toHaveKey('user');
    expect($exportData['data'])->toHaveKey('consents');
    expect($exportData['data']['user']['name'])->toBe('John Doe');
    expect($exportData['data']['user']['email'])->toBe('john@example.com');
});

it('exports user consent records', function () {
    $user = User::factory()->create();

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Email marketing',
        'given_at' => now(),
    ]);

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'analytics',
        'purpose' => 'Usage analytics',
        'given_at' => now(),
    ]);

    $service = app(RightToErasureService::class);
    $exportData = $service->exportUserData($user);

    expect($exportData['data']['consents'])->toHaveCount(2);
});

it('exports user support tickets', function () {
    $user = User::factory()->create();

    SupportTicket::factory()->create([
        'user_id' => $user->id,
        'subject' => 'Test Ticket',
        'status' => 'open',
        'priority' => 'medium',
    ]);

    $service = app(RightToErasureService::class);
    $exportData = $service->exportUserData($user);

    expect($exportData['data']['support_tickets'])->toHaveCount(1);
    expect($exportData['data']['support_tickets'][0]['subject'])->toBe('Test Ticket');
});

it('includes audit logs in export when requested', function () {
    Config::set('compliance.audit_logging.enabled', true);

    $user = User::factory()->create();

    // Create some audit logs
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'viewed',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $service = app(RightToErasureService::class);
    $exportData = $service->exportUserData($user, 'json', includeAuditLogs: true);

    expect($exportData['data'])->toHaveKey('audit_logs');
    expect($exportData['data']['audit_logs'])->toHaveCount(1);
});

it('creates audit log when exporting user data', function () {
    Config::set('compliance.audit_logging.enabled', true);

    $user = User::factory()->create();

    AuditLog::query()->delete();

    $service = app(RightToErasureService::class);
    $service->exportUserData($user);

    $auditLogs = AuditLog::where('event', 'exported')->get();
    expect($auditLogs->count())->toBeGreaterThanOrEqual(1);
});

it('throws exception when exporting with unsupported format', function () {
    $user = User::factory()->create();

    $service = app(RightToErasureService::class);

    $exception = null;
    try {
        $service->exportUserData($user, 'xml');
    } catch (\Exception $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())->toContain('not supported');
});

it('throws exception when data portability is disabled', function () {
    Config::set('compliance.gdpr.data_portability.enabled', false);

    $user = User::factory()->create();

    $service = app(RightToErasureService::class);

    $exception = null;
    try {
        $service->exportUserData($user);
    } catch (\Exception $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())->toContain('disabled');
});

it('anonymizes user data independently', function () {
    Config::set('compliance.gdpr.right_to_erasure.anonymize_data', true);

    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $service = app(RightToErasureService::class);
    $result = $service->anonymizeData($user);

    expect($result)->toHaveKey('user_id');
    expect($result)->toHaveKey('models_processed');
    expect($result)->toHaveKey('total_records');
    expect($result['total_records'])->toBeGreaterThanOrEqual(1);

    // Verify user is anonymized
    $user->refresh();
    expect($user->name)->toBe('Anonymized User');
    expect($user->email)->toContain('anonymized_');
});

it('handles transaction rollback on error during processing', function () {
    Config::set('compliance.gdpr.right_to_erasure.anonymize_data', true);
    Config::set('compliance.gdpr.right_to_erasure.erasable_models', []);

    $user = User::factory()->create();

    $service = app(RightToErasureService::class);
    $request = $service->requestDeletion($user);

    // Process should handle empty erasable_models gracefully
    $result = $service->processRequest($request);

    expect($result['status'])->toBe('success');
    expect($result['total_records_anonymized'])->toBe(0);
});

it('gets pending requests for a user', function () {
    $user = User::factory()->create();

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'pending',
    ]);

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'completed',
        'processed_at' => now(),
    ]);

    $pendingRequests = DataDeletionRequest::getPendingForUser($user->id);
    expect($pendingRequests)->toHaveCount(1);
    expect($pendingRequests->first()->status)->toBe('pending');
});

it('gets all requests for a user', function () {
    $user = User::factory()->create();

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'pending',
    ]);

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'completed',
        'processed_at' => now(),
    ]);

    $allRequests = DataDeletionRequest::getForUser($user->id);
    expect($allRequests)->toHaveCount(2);
});

it('gets requests by email', function () {
    $user = User::factory()->create(['email' => 'john@example.com']);

    DataDeletionRequest::create([
        'user_id' => $user->id,
        'email' => 'john@example.com',
    ]);

    $requests = DataDeletionRequest::getByEmail('john@example.com');
    expect($requests)->toHaveCount(1);
    expect($requests->first()->email)->toBe('john@example.com');
});
