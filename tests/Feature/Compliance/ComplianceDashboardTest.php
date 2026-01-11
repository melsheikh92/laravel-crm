<?php

use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use App\Services\Compliance\ComplianceMetrics;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Enable compliance features for all tests
    Config::set('compliance.enabled', true);
    Config::set('compliance.audit_logging.enabled', true);
    Config::set('compliance.consent.enabled', true);
    Config::set('compliance.data_retention.enabled', true);
    Config::set('compliance.encryption.enabled', true);
    Config::set('compliance.reporting.enabled', true);

    // Clear data before each test
    AuditLog::query()->delete();
    ConsentRecord::query()->delete();
    DataRetentionPolicy::query()->delete();
});

// ============================================
// Compliance Dashboard View Tests
// ============================================

it('displays compliance dashboard for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.dashboard'));

    $response->assertOk();
    $response->assertViewIs('compliance.dashboard');
    $response->assertViewHas('metrics');
    $response->assertViewHas('startDate');
    $response->assertViewHas('endDate');
});

it('requires authentication to view dashboard', function () {
    $response = $this->get(route('compliance.dashboard'));

    $response->assertRedirect(route('login'));
});

it('displays metrics with default date range', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.dashboard'));

    $response->assertOk();
    $metrics = $response->viewData('metrics');

    expect($metrics)->toBeArray();
    expect($metrics)->toHaveKeys([
        'period',
        'audit_logging',
        'consent_management',
        'data_retention',
        'encryption',
        'compliance_status',
        'generated_at'
    ]);
});

it('accepts custom date range parameters', function () {
    $user = User::factory()->create();
    $startDate = now()->subDays(7);
    $endDate = now();

    $response = $this->actingAs($user)->get(route('compliance.dashboard', [
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
    ]));

    $response->assertOk();
    expect($response->viewData('startDate'))->toBeString();
    expect($response->viewData('endDate'))->toBeString();
});

it('displays audit logging metrics in dashboard', function () {
    $user = User::factory()->create();

    // Create some audit logs
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user)->get(route('compliance.dashboard'));

    $metrics = $response->viewData('metrics');
    expect($metrics['audit_logging']['enabled'])->toBeTrue();
    expect($metrics['audit_logging']['total_logs'])->toBeGreaterThan(0);
});

it('displays consent management metrics in dashboard', function () {
    $user = User::factory()->create();

    // Create consent record
    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Email marketing',
        'given_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('compliance.dashboard'));

    $metrics = $response->viewData('metrics');
    expect($metrics['consent_management']['enabled'])->toBeTrue();
    expect($metrics['consent_management']['total_consents'])->toBeGreaterThan(0);
});

it('displays data retention metrics in dashboard', function () {
    $user = User::factory()->create();

    // Create retention policy
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('compliance.dashboard'));

    $metrics = $response->viewData('metrics');
    expect($metrics['data_retention']['enabled'])->toBeTrue();
    expect($metrics['data_retention']['total_policies'])->toBeGreaterThan(0);
});

it('displays encryption metrics in dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.dashboard'));

    $metrics = $response->viewData('metrics');
    expect($metrics['encryption']['enabled'])->toBeTrue();
    expect($metrics['encryption'])->toHaveKey('config');
});

it('displays compliance status in dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.dashboard'));

    $metrics = $response->viewData('metrics');
    expect($metrics['compliance_status'])->toHaveKeys([
        'overall_status',
        'frameworks',
        'issues',
        'warnings'
    ]);
});

// ============================================
// Audit Logs View Tests
// ============================================

it('displays audit logs page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.audit-logs'));

    $response->assertOk();
    $response->assertViewIs('compliance.audit-logs');
    $response->assertViewHas('auditLogs');
    $response->assertViewHas('summary');
    $response->assertViewHas('filters');
});

it('requires authentication to view audit logs', function () {
    $response = $this->get(route('compliance.audit-logs'));

    $response->assertRedirect(route('login'));
});

it('displays paginated audit logs', function () {
    $user = User::factory()->create();

    // Create multiple audit logs
    for ($i = 0; $i < 30; $i++) {
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    $response = $this->actingAs($user)->get(route('compliance.audit-logs'));

    $response->assertOk();
    $auditLogs = $response->viewData('auditLogs');
    expect($auditLogs)->toHaveCount(25); // Default pagination
});

it('filters audit logs by date range', function () {
    $user = User::factory()->create();

    // Create old audit log
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'created_at' => now()->subDays(60),
    ]);

    // Create recent audit log
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'created_at' => now()->subDays(5),
    ]);

    $response = $this->actingAs($user)->get(route('compliance.audit-logs', [
        'start_date' => now()->subDays(7)->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertOk();
    $auditLogs = $response->viewData('auditLogs');
    expect($auditLogs->total())->toBe(1);
});

it('filters audit logs by event type', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user)->get(route('compliance.audit-logs', [
        'event' => 'created',
    ]));

    $response->assertOk();
    $auditLogs = $response->viewData('auditLogs');
    expect($auditLogs->total())->toBe(1);
});

it('filters audit logs by model type', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user)->get(route('compliance.audit-logs', [
        'model_type' => User::class,
    ]));

    $response->assertOk();
    $auditLogs = $response->viewData('auditLogs');
    expect($auditLogs->total())->toBe(1);
});

it('filters audit logs by user id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    AuditLog::create([
        'user_id' => $user1->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user1->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    AuditLog::create([
        'user_id' => $user2->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user2->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user1)->get(route('compliance.audit-logs', [
        'user_id' => $user1->id,
    ]));

    $response->assertOk();
    $auditLogs = $response->viewData('auditLogs');
    expect($auditLogs->total())->toBe(1);
});

it('filters audit logs by ip address', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'ip_address' => '192.168.1.1',
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'ip_address' => '10.0.0.1',
    ]);

    $response = $this->actingAs($user)->get(route('compliance.audit-logs', [
        'ip_address' => '192.168.1.1',
    ]));

    $response->assertOk();
    $auditLogs = $response->viewData('auditLogs');
    expect($auditLogs->total())->toBe(1);
});

it('filters audit logs by tags', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'tags' => ['sensitive', 'gdpr'],
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'tags' => ['normal'],
    ]);

    $response = $this->actingAs($user)->get(route('compliance.audit-logs', [
        'tags' => 'sensitive',
    ]));

    $response->assertOk();
});

it('displays audit log summary', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user)->get(route('compliance.audit-logs'));

    $response->assertOk();
    $summary = $response->viewData('summary');
    expect($summary)->toBeArray();
    expect($summary)->toHaveKey('total_logs');
});

it('combines multiple filters correctly', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'ip_address' => '192.168.1.1',
        'created_at' => now()->subDays(5),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'ip_address' => '192.168.1.1',
        'created_at' => now()->subDays(5),
    ]);

    $response = $this->actingAs($user)->get(route('compliance.audit-logs', [
        'event' => 'created',
        'ip_address' => '192.168.1.1',
        'start_date' => now()->subDays(7)->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertOk();
    $auditLogs = $response->viewData('auditLogs');
    expect($auditLogs->total())->toBe(1);
});

it('supports custom pagination per page', function () {
    $user = User::factory()->create();

    // Create 20 audit logs
    for ($i = 0; $i < 20; $i++) {
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    $response = $this->actingAs($user)->get(route('compliance.audit-logs', [
        'per_page' => 10,
    ]));

    $response->assertOk();
    $auditLogs = $response->viewData('auditLogs');
    expect($auditLogs)->toHaveCount(10);
});

// ============================================
// Metrics AJAX Endpoint Tests
// ============================================

it('returns metrics via ajax endpoint', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.metrics'));

    $response->assertOk();
    $response->assertJson([]);
});

it('returns specific metric type via ajax', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.metrics', [
        'type' => 'audit_logging',
    ]));

    $response->assertOk();
});

it('returns overview metrics when no type specified', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.metrics'));

    $response->assertOk();
    $data = $response->json();
    expect($data)->toBeArray();
});

it('accepts date range in metrics ajax request', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('compliance.metrics', [
        'start_date' => now()->subDays(7)->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertOk();
});

// ============================================
// Integration Tests
// ============================================

it('dashboard shows real-time metrics after data creation', function () {
    $user = User::factory()->create();

    // Initially no data
    $response1 = $this->actingAs($user)->get(route('compliance.dashboard'));
    $metrics1 = $response1->viewData('metrics');
    $initialAuditCount = $metrics1['audit_logging']['total_logs'];

    // Create audit log
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    // Refresh dashboard
    $response2 = $this->actingAs($user)->get(route('compliance.dashboard'));
    $metrics2 = $response2->viewData('metrics');
    $finalAuditCount = $metrics2['audit_logging']['total_logs'];

    expect($finalAuditCount)->toBeGreaterThan($initialAuditCount);
});

it('audit logs page shows newly created logs immediately', function () {
    $user = User::factory()->create();

    $response1 = $this->actingAs($user)->get(route('compliance.audit-logs'));
    $initialCount = $response1->viewData('auditLogs')->total();

    // Create new audit log
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'test_event',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response2 = $this->actingAs($user)->get(route('compliance.audit-logs'));
    $finalCount = $response2->viewData('auditLogs')->total();

    expect($finalCount)->toBe($initialCount + 1);
});
