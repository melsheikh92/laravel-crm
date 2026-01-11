<?php

use App\Models\AuditLog;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use App\Services\Compliance\DataRetentionService;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Enable compliance features for all tests
    Config::set('compliance.enabled', true);
    Config::set('compliance.data_retention.enabled', true);
    Config::set('compliance.data_retention.auto_delete', false);
    Config::set('compliance.data_retention.prefer_anonymization', true);

    // Clear data before each test
    DataRetentionPolicy::query()->delete();
    AuditLog::query()->delete();
});

// ============================================
// DataRetentionPolicy Model Tests
// ============================================

it('creates a data retention policy', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'conditions' => [],
        'is_active' => true,
    ]);

    expect($policy)->toBeInstanceOf(DataRetentionPolicy::class);
    expect($policy->model_type)->toBe(User::class);
    expect($policy->retention_period_days)->toBe(365);
    expect($policy->delete_after_days)->toBe(395);
    expect($policy->is_active)->toBeTrue();
});

it('filters active policies using scope', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => false,
    ]);

    $activePolicies = DataRetentionPolicy::active()->get();
    expect($activePolicies)->toHaveCount(1);
    expect($activePolicies->first()->model_type)->toBe(User::class);
});

it('filters inactive policies using scope', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => false,
    ]);

    $inactivePolicies = DataRetentionPolicy::inactive()->get();
    expect($inactivePolicies)->toHaveCount(1);
    expect($inactivePolicies->first()->model_type)->toBe(AuditLog::class);
});

it('filters by model type using scope', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    $policies = DataRetentionPolicy::byModelType(User::class)->get();
    expect($policies)->toHaveCount(1);
    expect($policies->first()->model_type)->toBe(User::class);
});

it('filters by multiple model types using scope', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    $policies = DataRetentionPolicy::byModelTypes([User::class, AuditLog::class])->get();
    expect($policies)->toHaveCount(2);
});

it('filters by retention period less than specified days', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    $policies = DataRetentionPolicy::retentionLessThan(180)->get();
    expect($policies)->toHaveCount(1);
    expect($policies->first()->retention_period_days)->toBe(90);
});

it('filters by retention period greater than specified days', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    $policies = DataRetentionPolicy::retentionGreaterThan(180)->get();
    expect($policies)->toHaveCount(1);
    expect($policies->first()->retention_period_days)->toBe(365);
});

it('orders policies by retention period', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    $policies = DataRetentionPolicy::orderByRetention('asc')->get();
    expect($policies->first()->retention_period_days)->toBe(90);
    expect($policies->last()->retention_period_days)->toBe(365);
});

it('gets policies for a specific model', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    $policies = DataRetentionPolicy::getPoliciesForModel(User::class);
    expect($policies)->toHaveCount(1);
    expect($policies->first()->model_type)->toBe(User::class);
});

it('gets active policy for a specific model', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 180,
        'delete_after_days' => 210,
        'is_active' => false,
    ]);

    $policy = DataRetentionPolicy::getActivePolicyForModel(User::class);
    expect($policy)->toBeInstanceOf(DataRetentionPolicy::class);
    expect($policy->retention_period_days)->toBe(365);
});

it('gets all active policies', function () {
    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 180,
        'delete_after_days' => 210,
        'is_active' => false,
    ]);

    $policies = DataRetentionPolicy::getAllActivePolicies();
    expect($policies)->toHaveCount(2);
});

it('activates a policy', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => false,
    ]);

    $policy->activate();
    expect($policy->fresh()->is_active)->toBeTrue();
});

it('deactivates a policy', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    $policy->deactivate();
    expect($policy->fresh()->is_active)->toBeFalse();
});

it('evaluates if policy applies to a record', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'conditions' => [],
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    expect($policy->appliesTo($user))->toBeTrue();
});

it('evaluates if policy applies to a record with conditions', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'conditions' => [
            'name' => 'Test User',
        ],
        'is_active' => true,
    ]);

    $matchingUser = User::factory()->create([
        'role_id' => 1,
        'name' => 'Test User'
    ]);
    $nonMatchingUser = User::factory()->create([
        'role_id' => 1,
        'name' => 'Other User'
    ]);

    expect($policy->appliesTo($matchingUser))->toBeTrue();
    expect($policy->appliesTo($nonMatchingUser))->toBeFalse();
});

it('checks if a record is expired', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    // Create an old user
    $oldUser = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35),
    ]);

    // Create a recent user
    $recentUser = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(10),
    ]);

    expect($policy->isExpired($oldUser))->toBeTrue();
    expect($policy->isExpired($recentUser))->toBeFalse();
});

it('checks if a record should be deleted', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    // Create a very old user
    $veryOldUser = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65),
    ]);

    // Create an old but not deletable user
    $oldUser = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35),
    ]);

    expect($policy->shouldBeDeleted($veryOldUser))->toBeTrue();
    expect($policy->shouldBeDeleted($oldUser))->toBeFalse();
});

it('gets days until expiration for a record', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(20),
    ]);

    $daysUntilExpiration = $policy->getDaysUntilExpiration($user);
    expect($daysUntilExpiration)->toBe(10);
});

it('gets days until deletion for a record', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(50),
    ]);

    $daysUntilDeletion = $policy->getDaysUntilDeletion($user);
    expect($daysUntilDeletion)->toBe(10);
});

it('gets policy description', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    $description = $policy->getDescription();
    expect($description)->toContain('365 days');
    expect($description)->toContain('395 days');
});

it('gets policy statistics', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    // Create various users
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]); // Deletable
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35)]); // Expired
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(10)]); // Active

    $stats = $policy->getStatistics();
    expect($stats)->toHaveKey('total_records');
    expect($stats)->toHaveKey('total_expired');
    expect($stats)->toHaveKey('total_deletable');
    expect($stats['total_expired'])->toBeGreaterThanOrEqual(1);
    expect($stats['total_deletable'])->toBeGreaterThanOrEqual(1);
});

// ============================================
// DataRetentionService Integration Tests
// ============================================

it('applies retention policies in dry run mode', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    // Create old users
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]);
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35)]);

    $service = app(DataRetentionService::class);
    $result = $service->applyPolicies(dryRun: true);

    expect($result['status'])->toBe('success');
    expect($result['dry_run'])->toBeTrue();
    expect($result['policies_applied'])->toBe(1);
    expect(User::count())->toBe(2); // No users deleted in dry run
});

it('applies retention policies and deletes expired data when auto_delete is enabled', function () {
    Config::set('compliance.data_retention.auto_delete', true);

    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    // Create deletable users
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]);
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(70)]);
    // Create non-deletable user
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(10)]);

    $service = app(DataRetentionService::class);
    $result = $service->applyPolicies(dryRun: false);

    expect($result['status'])->toBe('success');
    expect($result['records_expired'])->toBeGreaterThanOrEqual(2);
    expect(User::count())->toBe(1); // Only the recent user remains
});

it('does not apply policies when data retention is disabled', function () {
    Config::set('compliance.data_retention.enabled', false);

    $service = app(DataRetentionService::class);
    $result = $service->applyPolicies();

    expect($result['status'])->toBe('disabled');
    expect($result['policies_applied'])->toBe(0);
});

it('applies policies for specific model type only', function () {
    Config::set('compliance.data_retention.auto_delete', true);

    DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    DataRetentionPolicy::create([
        'model_type' => AuditLog::class,
        'retention_period_days' => 90,
        'delete_after_days' => 120,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]);

    $service = app(DataRetentionService::class);
    $result = $service->applyPolicies(dryRun: false, modelType: User::class);

    expect($result['policies_applied'])->toBe(1);
    expect($result['details'][0]['model_type'])->toBe(User::class);
});

it('deletes expired data with force flag when auto_delete is disabled', function () {
    Config::set('compliance.data_retention.auto_delete', false);

    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]);
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(10)]);

    $service = app(DataRetentionService::class);
    $result = $service->deleteExpiredData(force: true);

    expect($result['status'])->toBe('success');
    expect(User::count())->toBe(1);
});

it('skips deletion when auto_delete is disabled and force is not set', function () {
    Config::set('compliance.data_retention.auto_delete', false);

    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]);

    $service = app(DataRetentionService::class);
    $result = $service->deleteExpiredData(force: false);

    expect($result['status'])->toBe('skipped');
    expect(User::count())->toBe(1); // User not deleted
});

it('gets expired records for all policies', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35)]);
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(10)]);

    $service = app(DataRetentionService::class);
    $expiredRecords = $service->getExpiredRecords();

    expect($expiredRecords)->toHaveCount(1);
    expect($expiredRecords->first()['model_type'])->toBe(User::class);
    expect($expiredRecords->first()['record_count'])->toBeGreaterThanOrEqual(1);
});

it('gets deletable records only when requested', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]); // Deletable
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35)]); // Expired but not deletable

    $service = app(DataRetentionService::class);
    $deletableRecords = $service->getExpiredRecords(deletableOnly: true);

    expect($deletableRecords)->toHaveCount(1);
    expect($deletableRecords->first()['record_count'])->toBeGreaterThanOrEqual(1);
});

it('gets retention statistics', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]);
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35)]);
    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(10)]);

    $service = app(DataRetentionService::class);
    $stats = $service->getRetentionStatistics();

    expect($stats['status'])->toBe('enabled');
    expect($stats['total_policies'])->toBe(1);
    expect($stats['total_expired_records'])->toBeGreaterThanOrEqual(1);
});

it('checks if a specific record is expired', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    $oldUser = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35)]);
    $recentUser = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(10)]);

    $service = app(DataRetentionService::class);
    expect($service->isRecordExpired($oldUser))->toBeTrue();
    expect($service->isRecordExpired($recentUser))->toBeFalse();
});

it('checks if a specific record should be deleted', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    $veryOldUser = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]);
    $oldUser = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(35)]);

    $service = app(DataRetentionService::class);
    expect($service->shouldRecordBeDeleted($veryOldUser))->toBeTrue();
    expect($service->shouldRecordBeDeleted($oldUser))->toBeFalse();
});

it('gets policy for a model', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 365,
        'delete_after_days' => 395,
        'is_active' => true,
    ]);

    $service = app(DataRetentionService::class);
    $foundPolicy = $service->getPolicyForModel(User::class);

    expect($foundPolicy)->toBeInstanceOf(DataRetentionPolicy::class);
    expect($foundPolicy->id)->toBe($policy->id);
});

it('service gets days until expiration for a record', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(20)]);

    $service = app(DataRetentionService::class);
    $days = $service->getDaysUntilExpiration($user);

    expect($days)->toBe(10);
});

it('service gets days until deletion for a record', function () {
    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(50)]);

    $service = app(DataRetentionService::class);
    $days = $service->getDaysUntilDeletion($user);

    expect($days)->toBe(10);
});

it('creates audit logs when deleting records', function () {
    Config::set('compliance.data_retention.auto_delete', true);
    Config::set('compliance.audit_logging.enabled', true);

    $policy = DataRetentionPolicy::create([
        'model_type' => User::class,
        'retention_period_days' => 30,
        'delete_after_days' => 60,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role_id' => 1,
        'created_at' => now()->subDays(65)]);

    AuditLog::query()->delete();

    $service = app(DataRetentionService::class);
    $service->deleteExpiredData();

    $auditLogs = AuditLog::where('event', 'deleted')->get();
    expect($auditLogs->count())->toBeGreaterThanOrEqual(1);
});
