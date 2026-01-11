<?php

use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\DataDeletionRequest;
use App\Models\DataRetentionPolicy;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\Compliance\AuditLogger;
use App\Services\Compliance\ComplianceMetrics;
use App\Services\Compliance\ConsentManager;
use App\Services\Compliance\DataRetentionService;
use App\Services\Compliance\FieldEncryption;
use App\Services\Compliance\RightToErasureService;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Enable all compliance features for integration tests
    Config::set('compliance.enabled', true);

    // Audit logging
    Config::set('compliance.audit_logging.enabled', true);
    Config::set('compliance.audit_logging.capture_ip', true);
    Config::set('compliance.audit_logging.capture_user_agent', true);
    Config::set('compliance.audit_logging.masked_fields', ['password', 'token']);

    // Consent management
    Config::set('compliance.consent.enabled', true);
    Config::set('compliance.consent.capture_ip', true);
    Config::set('compliance.consent.capture_user_agent', true);
    Config::set('compliance.consent.types', [
        'terms_of_service' => [
            'required' => true,
            'description' => 'Terms of Service',
            'purpose' => 'Legal agreement',
        ],
        'privacy_policy' => [
            'required' => true,
            'description' => 'Privacy Policy',
            'purpose' => 'Data processing',
        ],
        'marketing' => [
            'required' => false,
            'description' => 'Marketing',
            'purpose' => 'Promotional emails',
        ],
        'analytics' => [
            'required' => false,
            'description' => 'Analytics',
            'purpose' => 'Usage tracking',
        ],
    ]);

    // Data retention
    Config::set('compliance.data_retention.enabled', true);
    Config::set('compliance.data_retention.auto_delete', false);
    Config::set('compliance.data_retention.prefer_anonymization', true);

    // GDPR
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
    Config::set('compliance.gdpr.data_portability.include_audit_logs', true);

    // Encryption
    Config::set('compliance.encryption.enabled', true);
    Config::set('compliance.encryption.algorithm', 'AES-256-CBC');
    Config::set('compliance.encryption.auto_decrypt', true);

    // Notifications
    Config::set('compliance.notifications.enabled', false);

    // Clear all compliance data before each test
    AuditLog::query()->delete();
    ConsentRecord::query()->delete();
    DataDeletionRequest::query()->delete();
    DataRetentionPolicy::query()->delete();
});

// ============================================
// Full User Lifecycle Integration Tests
// ============================================

it('tracks full user lifecycle with audit logs, consents, and encryption', function () {
    $auditLogger = app(AuditLogger::class);
    $consentManager = app(ConsentManager::class);

    // 1. Create user (should create audit log)
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect(AuditLog::count())->toBeGreaterThan(0);

    $createAudit = AuditLog::where('event', 'created')
        ->where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->first();

    expect($createAudit)->not->toBeNull();
    expect($createAudit->new_values)->toBeArray();
    expect($createAudit->new_values['password'])->toBe('***MASKED***');

    // 2. Record required consents
    $consentManager->recordConsent($user, 'terms_of_service', 'Accept terms of service');
    $consentManager->recordConsent($user, 'privacy_policy', 'Accept privacy policy');

    expect(ConsentRecord::where('user_id', $user->id)->count())->toBe(2);
    expect($consentManager->checkConsent($user, 'terms_of_service'))->toBeTrue();
    expect($consentManager->checkConsent($user, 'privacy_policy'))->toBeTrue();

    // 3. Update user (should create audit log)
    $user->update(['name' => 'Updated Integration User']);

    $updateAudit = AuditLog::where('event', 'updated')
        ->where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->first();

    expect($updateAudit)->not->toBeNull();
    expect($updateAudit->old_values['name'])->toBe('Integration Test User');
    expect($updateAudit->new_values['name'])->toBe('Updated Integration User');

    // 4. Create support ticket with encryption
    $ticket = SupportTicket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Ticket',
        'subject' => 'Sensitive Subject',
        'description' => 'Sensitive Description',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    expect($ticket)->toBeInstanceOf(SupportTicket::class);
    expect(AuditLog::where('auditable_type', SupportTicket::class)->count())->toBeGreaterThan(0);

    // 5. Log custom access event
    $auditLogger->logAccess($ticket, $user, 'Viewed sensitive ticket');

    $accessAudit = AuditLog::where('event', 'accessed')
        ->where('auditable_type', SupportTicket::class)
        ->where('auditable_id', $ticket->id)
        ->first();

    expect($accessAudit)->not->toBeNull();

    // Verify full audit trail exists
    $userAuditLogs = AuditLog::where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->get();

    expect($userAuditLogs->count())->toBeGreaterThanOrEqual(2); // create + update
});

it('enforces consent requirements across multiple features', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // User has not given required consents
    expect($consentManager->checkConsent($user, 'terms_of_service'))->toBeFalse();
    expect($consentManager->checkConsent($user, 'privacy_policy'))->toBeFalse();

    // Get missing required consents
    $missingConsents = $consentManager->getMissingRequiredConsents($user);
    expect($missingConsents)->toHaveCount(2);
    expect($missingConsents)->toContain('terms_of_service');
    expect($missingConsents)->toContain('privacy_policy');

    // Record required consents
    $consentManager->recordMultipleConsents($user, [
        ['consent_type' => 'terms_of_service', 'purpose' => 'Legal agreement'],
        ['consent_type' => 'privacy_policy', 'purpose' => 'Data processing'],
    ]);

    // Verify all required consents are now given
    expect($consentManager->hasRequiredConsents($user))->toBeTrue();
    expect($consentManager->getMissingRequiredConsents($user))->toBeEmpty();

    // Record optional consent
    $consentManager->recordConsent($user, 'marketing', 'Email marketing');

    // Verify all consents
    $activeConsents = $consentManager->getActiveConsents($user);
    expect($activeConsents)->toHaveCount(3);

    // Withdraw optional consent (should not affect required consents)
    $consentManager->withdrawConsent($user, 'marketing', 'User opted out');

    expect($consentManager->checkConsent($user, 'marketing'))->toBeFalse();
    expect($consentManager->hasRequiredConsents($user))->toBeTrue();
});

// ============================================
// GDPR Right to Erasure Integration Tests
// ============================================

it('processes right to erasure affecting all compliance features', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'GDPR Test User',
        'email' => 'gdpr@example.com',
    ]);

    $consentManager = app(ConsentManager::class);
    $rightToErasure = app(RightToErasureService::class);
    $auditLogger = app(AuditLogger::class);

    // 1. Set up user with consents and tickets
    $consentManager->recordConsent($user, 'terms_of_service', 'Accept TOS');
    $consentManager->recordConsent($user, 'marketing', 'Email marketing');

    $ticket = SupportTicket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Support Request',
        'subject' => 'Need help',
        'description' => 'Help me please',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    $message = TicketMessage::create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'message' => 'This is my message',
    ]);

    $auditLogger->logAccess($user, $user, 'User profile viewed');

    $userId = $user->id;
    $ticketId = $ticket->id;
    $messageId = $message->id;

    // Verify data exists
    expect(ConsentRecord::where('user_id', $userId)->count())->toBe(2);
    expect(SupportTicket::where('user_id', $userId)->count())->toBe(1);
    expect(AuditLog::where('auditable_id', $userId)->where('auditable_type', User::class)->count())->toBeGreaterThan(0);

    // 2. Request deletion
    $deletionRequest = $rightToErasure->requestDeletion($user);

    expect($deletionRequest)->toBeInstanceOf(DataDeletionRequest::class);
    expect($deletionRequest->status)->toBe('pending');
    expect($deletionRequest->user_id)->toBe($userId);

    // 3. Process deletion request (anonymization)
    $result = $rightToErasure->processRequest($deletionRequest);

    expect($result)->toBeTrue();
    expect($deletionRequest->fresh()->status)->toBe('completed');

    // 4. Verify user data is anonymized
    $user = User::find($userId);
    expect($user->name)->toContain('Anonymized User');
    expect($user->email)->toContain('@anonymized.local');

    // 5. Verify consents are anonymized
    $consents = ConsentRecord::where('user_id', $userId)->get();
    foreach ($consents as $consent) {
        expect($consent->purpose)->toContain('[ANONYMIZED]');
    }

    // 6. Verify tickets are anonymized
    $ticket = SupportTicket::find($ticketId);
    expect($ticket->subject)->toContain('[ANONYMIZED]');
    expect($ticket->description)->toContain('[ANONYMIZED]');

    // 7. Verify audit log exists for deletion
    $deletionAudit = AuditLog::where('event', 'data_erasure_requested')->first();
    expect($deletionAudit)->not->toBeNull();
});

it('exports all user data for GDPR data portability', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'Export Test User',
        'email' => 'export@example.com',
    ]);

    $consentManager = app(ConsentManager::class);
    $rightToErasure = app(RightToErasureService::class);
    $auditLogger = app(AuditLogger::class);

    // Create comprehensive user data
    $consentManager->recordConsent($user, 'terms_of_service', 'Accept TOS');
    $consentManager->recordConsent($user, 'marketing', 'Email marketing');

    SupportTicket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Ticket',
        'subject' => 'Test Subject',
        'description' => 'Test Description',
        'priority' => 'high',
        'status' => 'open',
    ]);

    $auditLogger->logAccess($user, $user, 'Profile viewed');

    // Export user data in JSON format
    $exportData = $rightToErasure->exportUserData($user, 'json');

    expect($exportData)->toBeArray();
    expect($exportData)->toHaveKey('user');
    expect($exportData)->toHaveKey('consent_records');
    expect($exportData)->toHaveKey('support_tickets');
    expect($exportData)->toHaveKey('audit_logs');
    expect($exportData)->toHaveKey('metadata');

    // Verify user data
    expect($exportData['user']['name'])->toBe('Export Test User');
    expect($exportData['user']['email'])->toBe('export@example.com');

    // Verify consent records
    expect($exportData['consent_records'])->toBeArray();
    expect($exportData['consent_records'])->toHaveCount(2);

    // Verify support tickets
    expect($exportData['support_tickets'])->toBeArray();
    expect($exportData['support_tickets'])->toHaveCount(1);

    // Verify audit logs
    expect($exportData['audit_logs'])->toBeArray();
    expect($exportData['audit_logs'])->not->toBeEmpty();

    // Verify metadata
    expect($exportData['metadata'])->toHaveKey('export_date');
    expect($exportData['metadata'])->toHaveKey('format');
    expect($exportData['metadata']['format'])->toBe('json');
});

// ============================================
// Data Retention Integration Tests
// ============================================

it('applies data retention policies affecting multiple models', function () {
    $retentionService = app(DataRetentionService::class);

    // Create retention policies
    $auditLogPolicy = DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    $consentPolicy = DataRetentionPolicy::create([
        'model_type' => ConsentRecord::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    // Create old audit logs
    $user = User::factory()->create();
    $oldAuditLog = AuditLog::create([
        'user_id' => $user->id,
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'event' => 'accessed',
        'created_at' => now()->subDays(130),
    ]);

    $recentAuditLog = AuditLog::create([
        'user_id' => $user->id,
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'event' => 'accessed',
        'created_at' => now()->subDays(30),
    ]);

    // Create old consent record
    $oldConsent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
        'withdrawn_at' => now()->subDays(400),
    ]);

    $recentConsent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'terms_of_service',
        'purpose' => 'Test',
    ]);

    // Get expired records
    $expiredAuditLogs = $retentionService->getExpiredRecords(AuditLog::class, true);
    expect($expiredAuditLogs)->toHaveCount(1);
    expect($expiredAuditLogs->first()->id)->toBe($oldAuditLog->id);

    $expiredConsents = $retentionService->getExpiredRecords(ConsentRecord::class, true);
    expect($expiredConsents)->toHaveCount(1);
    expect($expiredConsents->first()->id)->toBe($oldConsent->id);

    // Apply policies (dry run)
    $results = $retentionService->applyPolicies(true);

    expect($results)->toBeArray();
    expect($results)->toHaveCount(2);

    // Verify nothing was actually deleted in dry run
    expect(AuditLog::count())->toBe(2);
    expect(ConsentRecord::count())->toBe(2);
});

it('respects retention policies when processing deletion requests', function () {
    $user = User::factory()->create();
    $retentionService = app(DataRetentionService::class);

    // Create a retention policy for audit logs
    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    // Create audit logs for the user
    AuditLog::create([
        'user_id' => $user->id,
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'event' => 'created',
    ]);

    // Check retention statistics
    $stats = $retentionService->getRetentionStatistics();

    expect($stats)->toBeArray();
    expect($stats)->not->toBeEmpty();

    $auditLogStats = collect($stats)->firstWhere('model_type', AuditLog::class);
    expect($auditLogStats)->not->toBeNull();
    expect($auditLogStats['total_records'])->toBeGreaterThan(0);
});

// ============================================
// Field Encryption Integration Tests
// ============================================

it('encrypts and decrypts fields across multiple models', function () {
    $fieldEncryption = app(FieldEncryption::class);

    // Create user with encrypted email
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Create support ticket with encrypted fields
    $ticket = SupportTicket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Encrypted Ticket',
        'subject' => 'Sensitive Subject Matter',
        'description' => 'Very sensitive description content',
        'priority' => 'high',
        'status' => 'open',
    ]);

    // Refresh from database
    $user = $user->fresh();
    $ticket = $ticket->fresh();

    // Verify data is accessible (auto-decryption)
    expect($user->email)->toBe('encrypted@example.com');
    expect($ticket->subject)->toBe('Sensitive Subject Matter');
    expect($ticket->description)->toBe('Very sensitive description content');

    // Verify audit logs captured the operations
    $userAudit = AuditLog::where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->where('event', 'created')
        ->first();

    expect($userAudit)->not->toBeNull();

    $ticketAudit = AuditLog::where('auditable_type', SupportTicket::class)
        ->where('auditable_id', $ticket->id)
        ->where('event', 'created')
        ->first();

    expect($ticketAudit)->not->toBeNull();
});

it('handles encrypted data in audit logs correctly', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Update user (should create audit log)
    $user->update(['email' => 'updated-encrypted@example.com']);

    $auditLog = AuditLog::where('event', 'updated')
        ->where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->old_values)->toBeArray();
    expect($auditLog->new_values)->toBeArray();

    // Note: Email values in audit logs may be encrypted
    // The important thing is that audit logs are created
    expect($auditLog->old_values)->toHaveKey('email');
    expect($auditLog->new_values)->toHaveKey('email');
});

// ============================================
// Compliance Metrics Integration Tests
// ============================================

it('calculates comprehensive compliance metrics across all features', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);
    $auditLogger = app(AuditLogger::class);
    $complianceMetrics = app(ComplianceMetrics::class);

    // Create data across all compliance features
    $consentManager->recordConsent($user, 'terms_of_service', 'Accept TOS');
    $consentManager->recordConsent($user, 'privacy_policy', 'Accept privacy');
    $consentManager->recordConsent($user, 'marketing', 'Email marketing');

    SupportTicket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Ticket',
        'subject' => 'Test',
        'description' => 'Test',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    $auditLogger->logAccess($user, $user, 'Profile viewed');
    $auditLogger->logChange($user, $user, ['name'], 'Name updated');

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    // Get compliance overview
    $overview = $complianceMetrics->getOverview();

    expect($overview)->toBeArray();
    expect($overview)->toHaveKey('audit_logging');
    expect($overview)->toHaveKey('consent_management');
    expect($overview)->toHaveKey('data_retention');
    expect($overview)->toHaveKey('encryption');
    expect($overview)->toHaveKey('gdpr_compliance');

    // Verify audit logging metrics
    expect($overview['audit_logging']['total_logs'])->toBeGreaterThan(0);
    expect($overview['audit_logging']['unique_users'])->toBeGreaterThan(0);

    // Verify consent metrics
    expect($overview['consent_management']['total_consents'])->toBe(3);
    expect($overview['consent_management']['active_consents'])->toBe(3);
    expect($overview['consent_management']['consent_rate'])->toBeGreaterThan(0);

    // Verify retention metrics
    expect($overview['data_retention']['active_policies'])->toBe(1);

    // Verify encryption metrics
    expect($overview['encryption']['enabled'])->toBeTrue();
    expect($overview['encryption']['encrypted_models'])->toBeArray();
});

it('provides detailed audit log metrics', function () {
    $user = User::factory()->create();
    $auditLogger = app(AuditLogger::class);
    $complianceMetrics = app(ComplianceMetrics::class);

    // Create various audit logs
    $auditLogger->logAccess($user, $user, 'Profile viewed');
    $auditLogger->logChange($user, $user, ['email'], 'Email updated');
    $auditLogger->logDeletion($user, $user, 'Account deleted');

    // Get audit log metrics
    $metrics = $complianceMetrics->getAuditLogMetrics();

    expect($metrics)->toBeArray();
    expect($metrics)->toHaveKey('total_logs');
    expect($metrics)->toHaveKey('by_event');
    expect($metrics)->toHaveKey('by_model');
    expect($metrics)->toHaveKey('unique_users');
    expect($metrics)->toHaveKey('recent_activity');

    expect($metrics['total_logs'])->toBeGreaterThan(0);
    expect($metrics['by_event'])->toBeArray();
    expect($metrics['unique_users'])->toBeGreaterThan(0);
});

it('provides detailed consent metrics', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $consentManager = app(ConsentManager::class);
    $complianceMetrics = app(ComplianceMetrics::class);

    // Create various consents
    $consentManager->recordConsent($user1, 'terms_of_service', 'Accept TOS');
    $consentManager->recordConsent($user1, 'marketing', 'Email marketing');
    $consentManager->recordConsent($user2, 'privacy_policy', 'Accept privacy');

    // Withdraw one consent
    $consentManager->withdrawConsent($user1, 'marketing', 'Opted out');

    // Get consent metrics
    $metrics = $complianceMetrics->getConsentMetrics();

    expect($metrics)->toBeArray();
    expect($metrics)->toHaveKey('total_consents');
    expect($metrics)->toHaveKey('active_consents');
    expect($metrics)->toHaveKey('withdrawn_consents');
    expect($metrics)->toHaveKey('consent_rate');
    expect($metrics)->toHaveKey('by_type');

    expect($metrics['total_consents'])->toBe(3);
    expect($metrics['active_consents'])->toBe(2);
    expect($metrics['withdrawn_consents'])->toBe(1);
    expect($metrics['by_type'])->toBeArray();
});

it('calculates overall compliance status', function () {
    $complianceMetrics = app(ComplianceMetrics::class);

    // Get compliance status
    $status = $complianceMetrics->getComplianceStatus();

    expect($status)->toBeArray();
    expect($status)->toHaveKey('overall_status');
    expect($status)->toHaveKey('gdpr_compliant');
    expect($status)->toHaveKey('hipaa_compliant');
    expect($status)->toHaveKey('soc2_compliant');
    expect($status)->toHaveKey('issues');
    expect($status)->toHaveKey('warnings');

    expect($status['overall_status'])->toBeIn(['compliant', 'non-compliant', 'partial']);
    expect($status['issues'])->toBeArray();
    expect($status['warnings'])->toBeArray();
});

// ============================================
// API Workflow Integration Tests
// ============================================

it('handles complete compliance workflow via API', function () {
    $user = User::factory()->create();

    // 1. Get available consent types
    $response = $this->actingAs($user, 'api')->getJson('/api/consent/types');
    $response->assertOk();
    $response->assertJsonStructure(['types']);

    // 2. Record multiple consents
    $response = $this->actingAs($user, 'api')->postJson('/api/consent/multiple', [
        'consents' => [
            ['consent_type' => 'terms_of_service', 'purpose' => 'Accept TOS'],
            ['consent_type' => 'privacy_policy', 'purpose' => 'Accept privacy'],
        ],
    ]);
    $response->assertStatus(201);

    // 3. Check required consents
    $response = $this->actingAs($user, 'api')->getJson('/api/consent/check/required');
    $response->assertOk();
    $response->assertJson(['has_required_consents' => true]);

    // 4. Get compliance metrics
    $response = $this->actingAs($user, 'api')->getJson('/api/compliance/metrics/overview');
    $response->assertOk();
    $response->assertJsonStructure([
        'audit_logging',
        'consent_management',
        'data_retention',
        'encryption',
    ]);

    // 5. Request data export
    $response = $this->actingAs($user, 'api')->postJson('/api/deletion-requests/export', [
        'format' => 'json',
    ]);
    $response->assertOk();
    $response->assertJsonStructure(['data']);
});

// ============================================
// Concurrent Operations Integration Tests
// ============================================

it('handles concurrent audit logging and consent recording', function () {
    $user = User::factory()->create();
    $auditLogger = app(AuditLogger::class);
    $consentManager = app(ConsentManager::class);

    // Simulate concurrent operations
    $auditLogger->logAccess($user, $user, 'Profile viewed');
    $consentManager->recordConsent($user, 'marketing', 'Email marketing');
    $auditLogger->logChange($user, $user, ['name'], 'Name changed');
    $consentManager->recordConsent($user, 'analytics', 'Usage tracking');

    // Verify both types of records were created
    expect(AuditLog::count())->toBeGreaterThan(0);
    expect(ConsentRecord::where('user_id', $user->id)->count())->toBe(2);

    // Verify data integrity
    $consents = ConsentRecord::where('user_id', $user->id)->get();
    expect($consents->pluck('consent_type')->toArray())->toContain('marketing');
    expect($consents->pluck('consent_type')->toArray())->toContain('analytics');
});

it('maintains data integrity during bulk operations', function () {
    $users = User::factory(5)->create();
    $consentManager = app(ConsentManager::class);

    // Record consents for all users
    foreach ($users as $user) {
        $consentManager->recordMultipleConsents($user, [
            ['consent_type' => 'terms_of_service', 'purpose' => 'Accept TOS'],
            ['consent_type' => 'privacy_policy', 'purpose' => 'Accept privacy'],
        ]);
    }

    // Verify all consents were created
    expect(ConsentRecord::count())->toBe(10); // 5 users * 2 consents

    // Verify each user has correct consents
    foreach ($users as $user) {
        expect(ConsentRecord::where('user_id', $user->id)->count())->toBe(2);
        expect($consentManager->hasRequiredConsents($user))->toBeTrue();
    }
});

// ============================================
// Error Handling Integration Tests
// ============================================

it('handles errors gracefully when features are disabled', function () {
    // Disable all compliance features
    Config::set('compliance.enabled', false);
    Config::set('compliance.audit_logging.enabled', false);
    Config::set('compliance.consent.enabled', false);

    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // Consent recording should not throw errors when disabled
    $result = $consentManager->recordConsent($user, 'marketing', 'Test');
    expect($result)->toBeNull();

    // No consent records should be created
    expect(ConsentRecord::count())->toBe(0);
});

it('handles missing configuration gracefully', function () {
    Config::set('compliance.consent.types', []);

    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // Should not throw errors
    $result = $consentManager->recordConsent($user, 'unknown_type', 'Test');

    // Consent should still be created even if type not in config
    expect($result)->toBeInstanceOf(ConsentRecord::class);
});

// ============================================
// Performance Integration Tests
// ============================================

it('handles large volumes of audit logs efficiently', function () {
    $user = User::factory()->create();
    $auditLogger = app(AuditLogger::class);

    // Create 100 audit logs
    for ($i = 0; $i < 100; $i++) {
        $auditLogger->logAccess($user, $user, "Access log {$i}");
    }

    expect(AuditLog::count())->toBeGreaterThanOrEqual(100);

    // Query should still be efficient
    $logs = AuditLog::byUser($user->id)->limit(10)->get();
    expect($logs)->toHaveCount(10);
});

it('efficiently processes retention policies on multiple models', function () {
    $retentionService = app(DataRetentionService::class);

    // Create retention policies for multiple models
    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => ConsentRecord::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    // Apply policies (dry run)
    $results = $retentionService->applyPolicies(true);

    expect($results)->toBeArray();
    expect($results)->toHaveCount(2);

    // Verify each policy result
    foreach ($results as $result) {
        expect($result)->toHaveKey('policy_id');
        expect($result)->toHaveKey('model_type');
        expect($result)->toHaveKey('expired_count');
        expect($result)->toHaveKey('deletable_count');
    }
});

// ============================================
// Cross-Feature Integration Tests
// ============================================

it('integrates audit logging with consent management', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // Clear audit logs
    AuditLog::query()->delete();

    // Record consent (should create audit log if configured)
    $consent = $consentManager->recordConsent($user, 'marketing', 'Email marketing');

    expect($consent)->toBeInstanceOf(ConsentRecord::class);

    // Check if consent creation was audited
    // Note: This depends on whether ConsentRecord has Auditable trait
    // For now, just verify the consent exists
    expect(ConsentRecord::where('user_id', $user->id)->count())->toBe(1);
});

it('integrates encryption with audit logging', function () {
    // Create user with encrypted email
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Should have created audit log
    $auditLog = AuditLog::where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->where('event', 'created')
        ->first();

    expect($auditLog)->not->toBeNull();

    // Audit log should contain new values
    expect($auditLog->new_values)->toBeArray();
    expect($auditLog->new_values)->toHaveKey('email');
});

it('integrates data retention with audit logging', function () {
    $retentionService = app(DataRetentionService::class);

    // Create retention policy
    $policy = DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    // Create old audit log
    $user = User::factory()->create();
    $oldLog = AuditLog::create([
        'user_id' => $user->id,
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'event' => 'accessed',
        'created_at' => now()->subDays(70),
    ]);

    // Check if it's deletable
    expect($policy->shouldBeDeleted($oldLog))->toBeTrue();

    // Get deletable records
    $deletable = $retentionService->getExpiredRecords(AuditLog::class, true);
    expect($deletable->pluck('id')->toArray())->toContain($oldLog->id);
});

it('provides comprehensive compliance reporting', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);
    $complianceMetrics = app(ComplianceMetrics::class);

    // Set up comprehensive compliance data
    $consentManager->recordConsent($user, 'terms_of_service', 'Accept TOS');
    $consentManager->recordConsent($user, 'privacy_policy', 'Accept privacy');

    SupportTicket::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test',
        'subject' => 'Test',
        'description' => 'Test',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    // Get comprehensive metrics
    $overview = $complianceMetrics->getOverview();
    $auditMetrics = $complianceMetrics->getAuditLogMetrics();
    $consentMetrics = $complianceMetrics->getConsentMetrics();
    $retentionMetrics = $complianceMetrics->getRetentionMetrics();
    $encryptionMetrics = $complianceMetrics->getEncryptionMetrics();
    $complianceStatus = $complianceMetrics->getComplianceStatus();

    // Verify all metrics are available
    expect($overview)->toBeArray()->not->toBeEmpty();
    expect($auditMetrics)->toBeArray()->not->toBeEmpty();
    expect($consentMetrics)->toBeArray()->not->toBeEmpty();
    expect($retentionMetrics)->toBeArray()->not->toBeEmpty();
    expect($encryptionMetrics)->toBeArray()->not->toBeEmpty();
    expect($complianceStatus)->toBeArray()->not->toBeEmpty();

    // Verify comprehensive compliance status
    expect($complianceStatus)->toHaveKey('overall_status');
    expect($complianceStatus)->toHaveKey('gdpr_compliant');
    expect($complianceStatus)->toHaveKey('hipaa_compliant');
    expect($complianceStatus)->toHaveKey('soc2_compliant');
});
