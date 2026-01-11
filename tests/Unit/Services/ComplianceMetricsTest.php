<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\DataRetentionPolicy;
use App\Models\User;
use App\Services\Compliance\ComplianceMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ComplianceMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected ComplianceMetrics $complianceMetrics;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable compliance features
        Config::set('compliance.enabled', true);
        Config::set('compliance.audit_logging.enabled', true);
        Config::set('compliance.consent.enabled', true);
        Config::set('compliance.data_retention.enabled', true);
        Config::set('compliance.encryption.enabled', true);

        // Clear all data
        AuditLog::query()->delete();
        ConsentRecord::query()->delete();
        DataRetentionPolicy::query()->delete();

        // Instantiate the service
        $this->complianceMetrics = new ComplianceMetrics();
    }

    /** @test */
    public function it_returns_overview_with_all_metrics()
    {
        $overview = $this->complianceMetrics->getOverview();

        $this->assertIsArray($overview);
        $this->assertArrayHasKey('period', $overview);
        $this->assertArrayHasKey('audit_logging', $overview);
        $this->assertArrayHasKey('consent_management', $overview);
        $this->assertArrayHasKey('data_retention', $overview);
        $this->assertArrayHasKey('encryption', $overview);
        $this->assertArrayHasKey('compliance_status', $overview);
        $this->assertArrayHasKey('generated_at', $overview);
    }

    /** @test */
    public function it_returns_overview_with_custom_date_range()
    {
        $startDate = now()->subDays(7);
        $endDate = now();

        $overview = $this->complianceMetrics->getOverview([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $this->assertEquals($startDate, $overview['period']['start']);
        $this->assertEquals($endDate, $overview['period']['end']);
    }

    /** @test */
    public function it_returns_audit_log_metrics_when_enabled()
    {
        // Create some audit logs
        $user = User::factory()->create();
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => ['name' => 'Test User'],
            'ip_address' => '127.0.0.1',
        ]);

        $metrics = $this->complianceMetrics->getAuditLogMetrics();

        $this->assertIsArray($metrics);
        $this->assertTrue($metrics['enabled']);
        $this->assertGreaterThan(0, $metrics['total_logs']);
        $this->assertArrayHasKey('logs_in_period', $metrics);
        $this->assertArrayHasKey('by_event', $metrics);
        $this->assertArrayHasKey('by_model', $metrics);
        $this->assertArrayHasKey('by_user', $metrics);
        $this->assertArrayHasKey('recent_activity', $metrics);
        $this->assertArrayHasKey('retention_config', $metrics);
    }

    /** @test */
    public function it_returns_disabled_audit_metrics_when_disabled()
    {
        Config::set('compliance.audit_logging.enabled', false);

        $metrics = $this->complianceMetrics->getAuditLogMetrics();

        $this->assertFalse($metrics['enabled']);
        $this->assertEquals(0, $metrics['total_logs']);
    }

    /** @test */
    public function it_filters_audit_logs_by_date_range()
    {
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

        $startDate = now()->subDays(7);
        $endDate = now();

        $metrics = $this->complianceMetrics->getAuditLogMetrics($startDate, $endDate);

        $this->assertEquals(2, $metrics['total_logs']);
        $this->assertEquals(1, $metrics['logs_in_period']);
    }

    /** @test */
    public function it_returns_consent_metrics_when_enabled()
    {
        $user = User::factory()->create();

        // Create active consent
        ConsentRecord::create([
            'user_id' => $user->id,
            'consent_type' => 'marketing',
            'purpose' => 'Email marketing',
            'given_at' => now(),
        ]);

        // Create withdrawn consent
        ConsentRecord::create([
            'user_id' => $user->id,
            'consent_type' => 'analytics',
            'purpose' => 'Analytics tracking',
            'given_at' => now()->subDays(30),
            'withdrawn_at' => now()->subDays(10),
        ]);

        $metrics = $this->complianceMetrics->getConsentMetrics();

        $this->assertIsArray($metrics);
        $this->assertTrue($metrics['enabled']);
        $this->assertEquals(2, $metrics['total_consents']);
        $this->assertEquals(1, $metrics['active_consents']);
        $this->assertEquals(1, $metrics['withdrawn_consents']);
        $this->assertEquals(50.0, $metrics['consent_rate']);
        $this->assertEquals(50.0, $metrics['withdrawal_rate']);
        $this->assertArrayHasKey('period_activity', $metrics);
        $this->assertArrayHasKey('by_type', $metrics);
        $this->assertArrayHasKey('by_user_stats', $metrics);
        $this->assertArrayHasKey('required_consents', $metrics);
        $this->assertArrayHasKey('config', $metrics);
    }

    /** @test */
    public function it_returns_disabled_consent_metrics_when_disabled()
    {
        Config::set('compliance.consent.enabled', false);

        $metrics = $this->complianceMetrics->getConsentMetrics();

        $this->assertFalse($metrics['enabled']);
        $this->assertEquals(0, $metrics['total_consents']);
    }

    /** @test */
    public function it_calculates_consent_rates_correctly()
    {
        $user = User::factory()->create();

        // Create 7 active consents
        for ($i = 0; $i < 7; $i++) {
            ConsentRecord::create([
                'user_id' => $user->id,
                'consent_type' => 'type_' . $i,
                'purpose' => 'Purpose ' . $i,
                'given_at' => now(),
            ]);
        }

        // Create 3 withdrawn consents
        for ($i = 0; $i < 3; $i++) {
            ConsentRecord::create([
                'user_id' => $user->id,
                'consent_type' => 'withdrawn_' . $i,
                'purpose' => 'Purpose ' . $i,
                'given_at' => now()->subDays(30),
                'withdrawn_at' => now()->subDays(10),
            ]);
        }

        $metrics = $this->complianceMetrics->getConsentMetrics();

        $this->assertEquals(10, $metrics['total_consents']);
        $this->assertEquals(7, $metrics['active_consents']);
        $this->assertEquals(3, $metrics['withdrawn_consents']);
        $this->assertEquals(70.0, $metrics['consent_rate']);
        $this->assertEquals(30.0, $metrics['withdrawal_rate']);
    }

    /** @test */
    public function it_filters_consent_activity_by_date_range()
    {
        $user = User::factory()->create();

        // Consent given in period
        ConsentRecord::create([
            'user_id' => $user->id,
            'consent_type' => 'recent',
            'purpose' => 'Recent consent',
            'given_at' => now()->subDays(3),
        ]);

        // Consent given before period
        ConsentRecord::create([
            'user_id' => $user->id,
            'consent_type' => 'old',
            'purpose' => 'Old consent',
            'given_at' => now()->subDays(60),
        ]);

        $startDate = now()->subDays(7);
        $endDate = now();

        $metrics = $this->complianceMetrics->getConsentMetrics($startDate, $endDate);

        $this->assertEquals(1, $metrics['period_activity']['consents_given']);
    }

    /** @test */
    public function it_returns_data_retention_metrics_when_enabled()
    {
        // Create active policy
        DataRetentionPolicy::create([
            'model_type' => User::class,
            'retention_period_days' => 365,
            'delete_after_days' => 395,
            'is_active' => true,
        ]);

        // Create inactive policy
        DataRetentionPolicy::create([
            'model_type' => 'App\Models\SupportTicket',
            'retention_period_days' => 730,
            'delete_after_days' => 760,
            'is_active' => false,
        ]);

        $metrics = $this->complianceMetrics->getRetentionMetrics();

        $this->assertIsArray($metrics);
        $this->assertTrue($metrics['enabled']);
        $this->assertEquals(2, $metrics['total_policies']);
        $this->assertEquals(1, $metrics['active_policies']);
        $this->assertEquals(1, $metrics['inactive_policies']);
        $this->assertArrayHasKey('expired_records', $metrics);
        $this->assertArrayHasKey('deletable_records', $metrics);
        $this->assertArrayHasKey('policies_with_expired_data', $metrics);
        $this->assertArrayHasKey('config', $metrics);
    }

    /** @test */
    public function it_returns_disabled_retention_metrics_when_disabled()
    {
        Config::set('compliance.data_retention.enabled', false);

        $metrics = $this->complianceMetrics->getRetentionMetrics();

        $this->assertFalse($metrics['enabled']);
        $this->assertEquals(0, $metrics['total_policies']);
    }

    /** @test */
    public function it_returns_encryption_metrics()
    {
        $metrics = $this->complianceMetrics->getEncryptionMetrics();

        $this->assertIsArray($metrics);
        $this->assertTrue($metrics['enabled']);
        $this->assertArrayHasKey('encrypted_models', $metrics);
        $this->assertArrayHasKey('total_encrypted_fields', $metrics);
        $this->assertArrayHasKey('config', $metrics);
        $this->assertArrayHasKey('algorithm', $metrics['config']);
        $this->assertArrayHasKey('auto_decrypt', $metrics['config']);
    }

    /** @test */
    public function it_returns_disabled_encryption_metrics_when_disabled()
    {
        Config::set('compliance.encryption.enabled', false);

        $metrics = $this->complianceMetrics->getEncryptionMetrics();

        $this->assertFalse($metrics['enabled']);
    }

    /** @test */
    public function it_returns_compliance_status_with_all_frameworks()
    {
        $status = $this->complianceMetrics->getComplianceStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('overall_status', $status);
        $this->assertArrayHasKey('frameworks', $status);
        $this->assertArrayHasKey('gdpr', $status['frameworks']);
        $this->assertArrayHasKey('hipaa', $status['frameworks']);
        $this->assertArrayHasKey('soc2', $status['frameworks']);
        $this->assertArrayHasKey('issues', $status);
        $this->assertArrayHasKey('warnings', $status);
    }

    /** @test */
    public function it_detects_compliance_issues_when_features_disabled()
    {
        Config::set('compliance.audit_logging.enabled', false);

        $status = $this->complianceMetrics->getComplianceStatus();

        $this->assertGreaterThan(0, count($status['issues']));
    }

    /** @test */
    public function it_handles_zero_consents_without_division_error()
    {
        $metrics = $this->complianceMetrics->getConsentMetrics();

        $this->assertEquals(0, $metrics['total_consents']);
        $this->assertEquals(0, $metrics['consent_rate']);
        $this->assertEquals(0, $metrics['withdrawal_rate']);
    }

    /** @test */
    public function it_groups_audit_logs_by_event_type()
    {
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

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => [],
        ]);

        $metrics = $this->complianceMetrics->getAuditLogMetrics();

        $this->assertIsArray($metrics['by_event']);
        $this->assertGreaterThan(0, count($metrics['by_event']));
    }

    /** @test */
    public function it_groups_audit_logs_by_model_type()
    {
        $user = User::factory()->create();

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => [],
        ]);

        $metrics = $this->complianceMetrics->getAuditLogMetrics();

        $this->assertIsArray($metrics['by_model']);
        $this->assertGreaterThan(0, count($metrics['by_model']));
    }

    /** @test */
    public function it_provides_retention_configuration_in_audit_metrics()
    {
        $metrics = $this->complianceMetrics->getAuditLogMetrics();

        $this->assertArrayHasKey('retention_config', $metrics);
        $this->assertArrayHasKey('retention_days', $metrics['retention_config']);
        $this->assertArrayHasKey('capture_ip', $metrics['retention_config']);
        $this->assertArrayHasKey('capture_user_agent', $metrics['retention_config']);
    }

    /** @test */
    public function it_provides_consent_configuration()
    {
        Config::set('compliance.consent.types', [
            'marketing' => 'Marketing communications',
            'analytics' => 'Analytics tracking',
        ]);

        $metrics = $this->complianceMetrics->getConsentMetrics();

        $this->assertArrayHasKey('config', $metrics);
        $this->assertEquals(2, $metrics['config']['types_configured']);
        $this->assertTrue($metrics['config']['explicit_consent_required']);
    }
}
