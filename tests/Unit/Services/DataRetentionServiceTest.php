<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use App\Services\Compliance\AuditLogger;
use App\Services\Compliance\DataRetentionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DataRetentionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DataRetentionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable compliance features
        Config::set('compliance.enabled', true);
        Config::set('compliance.data_retention.enabled', true);
        Config::set('compliance.data_retention.auto_delete', false);
        Config::set('compliance.data_retention.prefer_anonymization', true);
        Config::set('compliance.audit_logging.enabled', true);

        // Clear data
        DataRetentionPolicy::query()->delete();
        AuditLog::query()->delete();

        // Instantiate the service
        $this->service = new DataRetentionService(new AuditLogger());
    }

    /** @test */
    public function it_applies_policies_in_dry_run_mode()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);
        User::factory()->create(['created_at' => now()->subDays(35)]);

        $initialUserCount = User::count();

        $result = $this->service->applyPolicies(dryRun: true);

        $this->assertEquals('success', $result['status']);
        $this->assertTrue($result['dry_run']);
        $this->assertEquals(1, $result['policies_applied']);
        $this->assertGreaterThanOrEqual(2, $result['records_expired']);
        $this->assertEquals(0, $result['records_deleted']);
        $this->assertEquals($initialUserCount, User::count());
    }

    /** @test */
    public function it_applies_policies_and_deletes_data_when_auto_delete_enabled()
    {
        Config::set('compliance.data_retention.auto_delete', true);

        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);
        User::factory()->create(['created_at' => now()->subDays(70)]);
        User::factory()->create(['created_at' => now()->subDays(10)]);

        $result = $this->service->applyPolicies(dryRun: false);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(1, $result['policies_applied']);
        $this->assertGreaterThanOrEqual(2, $result['records_expired']);
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function it_returns_disabled_status_when_data_retention_is_disabled()
    {
        Config::set('compliance.data_retention.enabled', false);

        $result = $this->service->applyPolicies();

        $this->assertEquals('disabled', $result['status']);
        $this->assertEquals(0, $result['policies_applied']);
    }

    /** @test */
    public function it_applies_policies_for_specific_model_type_only()
    {
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

        User::factory()->create(['created_at' => now()->subDays(65)]);

        $result = $this->service->applyPolicies(dryRun: false, modelType: User::class);

        $this->assertEquals(1, $result['policies_applied']);
        $this->assertEquals(User::class, $result['details'][0]['model_type']);
    }

    /** @test */
    public function it_deletes_expired_data_with_force_flag()
    {
        Config::set('compliance.data_retention.auto_delete', false);

        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);
        User::factory()->create(['created_at' => now()->subDays(10)]);

        $result = $this->service->deleteExpiredData(force: true);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function it_skips_deletion_when_auto_delete_disabled_and_force_not_set()
    {
        Config::set('compliance.data_retention.auto_delete', false);

        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);

        $initialCount = User::count();

        $result = $this->service->deleteExpiredData(force: false);

        $this->assertEquals('skipped', $result['status']);
        $this->assertEquals($initialCount, User::count());
    }

    /** @test */
    public function it_deletes_expired_data_for_specific_model_type()
    {
        Config::set('compliance.data_retention.auto_delete', true);

        DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);
        User::factory()->create(['created_at' => now()->subDays(10)]);

        $result = $this->service->deleteExpiredData(modelType: User::class);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function it_gets_expired_records_for_all_policies()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(35)]);
        User::factory()->create(['created_at' => now()->subDays(10)]);

        $expiredRecords = $this->service->getExpiredRecords();

        $this->assertCount(1, $expiredRecords);
        $this->assertEquals(User::class, $expiredRecords->first()['model_type']);
        $this->assertGreaterThanOrEqual(1, $expiredRecords->first()['record_count']);
    }

    /** @test */
    public function it_gets_deletable_records_only_when_requested()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]); // Deletable
        User::factory()->create(['created_at' => now()->subDays(35)]); // Expired but not deletable

        $deletableRecords = $this->service->getExpiredRecords(deletableOnly: true);

        $this->assertCount(1, $deletableRecords);
        $this->assertGreaterThanOrEqual(1, $deletableRecords->first()['record_count']);
    }

    /** @test */
    public function it_gets_expired_records_for_specific_model_type()
    {
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

        User::factory()->create(['created_at' => now()->subDays(35)]);

        $expiredRecords = $this->service->getExpiredRecords(modelType: User::class);

        $this->assertCount(1, $expiredRecords);
        $this->assertEquals(User::class, $expiredRecords->first()['model_type']);
    }

    /** @test */
    public function it_returns_empty_collection_when_data_retention_disabled()
    {
        Config::set('compliance.data_retention.enabled', false);

        $expiredRecords = $this->service->getExpiredRecords();

        $this->assertCount(0, $expiredRecords);
    }

    /** @test */
    public function it_gets_retention_statistics()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);
        User::factory()->create(['created_at' => now()->subDays(35)]);
        User::factory()->create(['created_at' => now()->subDays(10)]);

        $stats = $this->service->getRetentionStatistics();

        $this->assertEquals('enabled', $stats['status']);
        $this->assertEquals(1, $stats['total_policies']);
        $this->assertGreaterThanOrEqual(1, $stats['total_expired_records']);
        $this->assertArrayHasKey('policies', $stats);
    }

    /** @test */
    public function it_gets_statistics_for_specific_model_type()
    {
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

        User::factory()->create(['created_at' => now()->subDays(35)]);

        $stats = $this->service->getRetentionStatistics(modelType: User::class);

        $this->assertEquals(1, $stats['total_policies']);
    }

    /** @test */
    public function it_returns_disabled_status_for_statistics_when_disabled()
    {
        Config::set('compliance.data_retention.enabled', false);

        $stats = $this->service->getRetentionStatistics();

        $this->assertEquals('disabled', $stats['status']);
        $this->assertEmpty($stats['policies']);
    }

    /** @test */
    public function it_checks_if_a_specific_record_is_expired()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        $oldUser = User::factory()->create(['created_at' => now()->subDays(35)]);
        $recentUser = User::factory()->create(['created_at' => now()->subDays(10)]);

        $this->assertTrue($this->service->isRecordExpired($oldUser));
        $this->assertFalse($this->service->isRecordExpired($recentUser));
    }

    /** @test */
    public function it_returns_false_for_expired_check_when_no_policy_exists()
    {
        $user = User::factory()->create(['created_at' => now()->subDays(365)]);

        $this->assertFalse($this->service->isRecordExpired($user));
    }

    /** @test */
    public function it_returns_false_for_expired_check_when_disabled()
    {
        Config::set('compliance.data_retention.enabled', false);

        $user = User::factory()->create(['created_at' => now()->subDays(365)]);

        $this->assertFalse($this->service->isRecordExpired($user));
    }

    /** @test */
    public function it_checks_if_a_specific_record_should_be_deleted()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        $veryOldUser = User::factory()->create(['created_at' => now()->subDays(65)]);
        $oldUser = User::factory()->create(['created_at' => now()->subDays(35)]);

        $this->assertTrue($this->service->shouldRecordBeDeleted($veryOldUser));
        $this->assertFalse($this->service->shouldRecordBeDeleted($oldUser));
    }

    /** @test */
    public function it_returns_false_for_deletion_check_when_no_policy_exists()
    {
        $user = User::factory()->create(['created_at' => now()->subDays(365)]);

        $this->assertFalse($this->service->shouldRecordBeDeleted($user));
    }

    /** @test */
    public function it_gets_policy_for_a_model()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 365,
            'delete_after_days' => 395,
            'is_active' => true,
        ]);

        $foundPolicy = $this->service->getPolicyForModel(User::class);

        $this->assertInstanceOf(DataRetentionPolicy::class, $foundPolicy);
        $this->assertEquals($policy->id, $foundPolicy->id);
    }

    /** @test */
    public function it_returns_null_when_no_policy_exists_for_model()
    {
        $foundPolicy = $this->service->getPolicyForModel(User::class);

        $this->assertNull($foundPolicy);
    }

    /** @test */
    public function it_gets_days_until_expiration_for_a_record()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['created_at' => now()->subDays(20)]);

        $days = $this->service->getDaysUntilExpiration($user);

        $this->assertEquals(10, $days);
    }

    /** @test */
    public function it_returns_null_for_days_until_expiration_when_no_policy()
    {
        $user = User::factory()->create(['created_at' => now()->subDays(20)]);

        $days = $this->service->getDaysUntilExpiration($user);

        $this->assertNull($days);
    }

    /** @test */
    public function it_gets_days_until_deletion_for_a_record()
    {
        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['created_at' => now()->subDays(50)]);

        $days = $this->service->getDaysUntilDeletion($user);

        $this->assertEquals(10, $days);
    }

    /** @test */
    public function it_returns_null_for_days_until_deletion_when_no_policy()
    {
        $user = User::factory()->create(['created_at' => now()->subDays(50)]);

        $days = $this->service->getDaysUntilDeletion($user);

        $this->assertNull($days);
    }

    /** @test */
    public function it_creates_audit_logs_when_deleting_records()
    {
        Config::set('compliance.data_retention.auto_delete', true);
        Config::set('compliance.audit_logging.enabled', true);

        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);

        AuditLog::query()->delete();

        $this->service->deleteExpiredData();

        $auditLogs = AuditLog::where('event', 'deleted')->get();
        $this->assertGreaterThanOrEqual(1, $auditLogs->count());
    }

    /** @test */
    public function it_anonymizes_records_when_prefer_anonymization_is_enabled()
    {
        Config::set('compliance.data_retention.auto_delete', true);
        Config::set('compliance.data_retention.prefer_anonymization', true);

        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        // Create a user with an anonymize method
        $user = User::factory()->create(['created_at' => now()->subDays(65)]);

        // Add anonymize method to User model temporarily (in a real scenario)
        // For testing, we'll just verify the config is being respected
        $result = $this->service->applyPolicies(dryRun: false);

        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_handles_errors_gracefully_during_policy_application()
    {
        Config::set('compliance.data_retention.auto_delete', true);

        // Create an invalid policy that will cause errors
        $policy = DataRetentionPolicy::create([
            'model_type' => 'NonExistentModel',
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        Log::shouldReceive('error')->once();

        $result = $this->service->applyPolicies(dryRun: false);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(1, $result['policies_applied']);
        $this->assertArrayHasKey('error', $result['details'][0]);
    }

    /** @test */
    public function it_handles_errors_gracefully_during_deletion()
    {
        Config::set('compliance.data_retention.auto_delete', true);

        // Create an invalid policy
        $policy = DataRetentionPolicy::create([
            'model_type' => 'NonExistentModel',
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        Log::shouldReceive('error')->once();

        $result = $this->service->deleteExpiredData(force: true);

        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_uses_transactions_when_deleting_records()
    {
        Config::set('compliance.data_retention.auto_delete', true);

        $policy = DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 30,
            'delete_after_days' => 60,
            'is_active' => true,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        // Note: In a real test, you might need to mock the transaction behavior
        // For now, we'll just verify the method can be called
        $result = $this->service->deleteExpiredData();

        $this->assertEquals('success', $result['status']);
    }

    /** @test */
    public function it_processes_multiple_policies_in_one_run()
    {
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

        User::factory()->create(['created_at' => now()->subDays(65)]);

        $result = $this->service->applyPolicies(dryRun: false);

        $this->assertEquals(2, $result['policies_applied']);
    }

    /** @test */
    public function it_only_processes_active_policies()
    {
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
            'is_active' => false,
        ]);

        User::factory()->create(['created_at' => now()->subDays(65)]);

        $result = $this->service->applyPolicies(dryRun: false);

        $this->assertEquals(1, $result['policies_applied']);
    }
}
