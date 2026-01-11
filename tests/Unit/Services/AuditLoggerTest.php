<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Compliance\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected AuditLogger $auditLogger;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable compliance features
        Config::set('compliance.enabled', true);
        Config::set('compliance.audit_logging.enabled', true);

        // Clear audit logs
        AuditLog::query()->delete();

        // Instantiate the service
        $this->auditLogger = new AuditLogger();
    }

    /** @test */
    public function it_logs_access_events_with_model_instance()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess(
            $user,
            null,
            ['fields' => ['email', 'name']],
            ['sensitive']
        );

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEquals('viewed', $result->event);
        $this->assertEquals(User::class, $result->auditable_type);
        $this->assertEquals($user->id, $result->auditable_id);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertArrayHasKey('fields', $result->new_values);
        $this->assertContains('sensitive', $result->tags);
    }

    /** @test */
    public function it_logs_access_events_with_class_string()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess(
            User::class,
            $user->id,
            ['fields' => ['email']],
            ['test']
        );

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEquals(User::class, $result->auditable_type);
        $this->assertEquals($user->id, $result->auditable_id);
    }

    /** @test */
    public function it_logs_change_events()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $oldValues = ['name' => 'Old Name', 'email' => 'old@example.com'];
        $newValues = ['name' => 'New Name', 'email' => 'new@example.com'];

        $result = $this->auditLogger->logChange(
            $user,
            null,
            $oldValues,
            $newValues,
            ['manual_update']
        );

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEquals('updated', $result->event);
        $this->assertArrayHasKey('name', $result->old_values);
        $this->assertArrayHasKey('name', $result->new_values);
        $this->assertEquals('Old Name', $result->old_values['name']);
        $this->assertEquals('New Name', $result->new_values['name']);
        $this->assertContains('manual_update', $result->tags);
    }

    /** @test */
    public function it_logs_deletion_events()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $deletedData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ];

        $result = $this->auditLogger->logDeletion(
            $user,
            null,
            $deletedData,
            ['permanent_deletion']
        );

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEquals('deleted', $result->event);
        $this->assertArrayHasKey('name', $result->old_values);
        $this->assertEmpty($result->new_values);
        $this->assertContains('permanent_deletion', $result->tags);
    }

    /** @test */
    public function it_logs_export_events()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $exportDetails = [
            'format' => 'csv',
            'fields' => ['name', 'email', 'created_at'],
            'record_count' => 100,
        ];

        $result = $this->auditLogger->logExport(
            $user,
            null,
            $exportDetails,
            ['data_export', 'gdpr']
        );

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEquals('exported', $result->event);
        $this->assertArrayHasKey('format', $result->new_values);
        $this->assertEquals('csv', $result->new_values['format']);
        $this->assertContains('data_export', $result->tags);
        $this->assertContains('gdpr', $result->tags);
    }

    /** @test */
    public function it_logs_custom_events()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logCustomEvent(
            'password_reset',
            $user,
            null,
            ['reset_requested' => false],
            ['reset_requested' => true, 'token_sent' => true],
            ['security', 'password']
        );

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEquals('password_reset', $result->event);
        $this->assertArrayHasKey('reset_requested', $result->old_values);
        $this->assertArrayHasKey('reset_requested', $result->new_values);
        $this->assertContains('security', $result->tags);
        $this->assertContains('password', $result->tags);
    }

    /** @test */
    public function it_masks_sensitive_fields_in_access_logs()
    {
        Config::set('compliance.audit_logging.masked_fields', ['password', 'ssn']);

        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess(
            $user,
            null,
            ['password' => 'secret123', 'ssn' => '123-45-6789', 'name' => 'John Doe']
        );

        $this->assertEquals('***MASKED***', $result->new_values['password']);
        $this->assertEquals('***MASKED***', $result->new_values['ssn']);
        $this->assertEquals('John Doe', $result->new_values['name']);
    }

    /** @test */
    public function it_masks_sensitive_fields_in_change_logs()
    {
        Config::set('compliance.audit_logging.masked_fields', ['password']);

        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logChange(
            $user,
            null,
            ['password' => 'old_secret'],
            ['password' => 'new_secret']
        );

        $this->assertEquals('***MASKED***', $result->old_values['password']);
        $this->assertEquals('***MASKED***', $result->new_values['password']);
    }

    /** @test */
    public function it_builds_tags_correctly()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess(
            $user,
            null,
            [],
            ['custom_tag', 'another_tag']
        );

        // Should include class basename, event, and custom tags
        $this->assertContains('User', $result->tags);
        $this->assertContains('viewed', $result->tags);
        $this->assertContains('custom_tag', $result->tags);
        $this->assertContains('another_tag', $result->tags);

        // Tags should be unique
        $uniqueTags = array_unique($result->tags);
        $this->assertCount(count($result->tags), $uniqueTags);
    }

    /** @test */
    public function it_returns_null_when_auditing_is_disabled()
    {
        Config::set('compliance.audit_logging.enabled', false);

        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess($user);

        $this->assertNull($result);
        $this->assertEquals(0, AuditLog::count());
    }

    /** @test */
    public function it_returns_null_when_compliance_is_disabled()
    {
        Config::set('compliance.enabled', false);

        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess($user);

        $this->assertNull($result);
        $this->assertEquals(0, AuditLog::count());
    }

    /** @test */
    public function it_uses_provided_user_id_over_authenticated_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Auth::login($user1);

        $result = $this->auditLogger->logAccess(
            $user2,
            null,
            [],
            [],
            $user2->id  // Explicitly provide user2 ID
        );

        $this->assertEquals($user2->id, $result->user_id);
        $this->assertNotEquals($user1->id, $result->user_id);
    }

    /** @test */
    public function it_handles_null_authenticated_user()
    {
        // No user is authenticated
        Auth::logout();

        $user = User::factory()->create();

        $result = $this->auditLogger->logAccess($user);

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertNull($result->user_id);
    }

    /** @test */
    public function it_resolves_model_instance_correctly()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess($user);

        $this->assertEquals(User::class, $result->auditable_type);
        $this->assertEquals($user->id, $result->auditable_id);
    }

    /** @test */
    public function it_resolves_class_string_with_id_correctly()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess(User::class, $user->id);

        $this->assertEquals(User::class, $result->auditable_type);
        $this->assertEquals($user->id, $result->auditable_id);
    }

    /** @test */
    public function it_logs_access_with_empty_metadata()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logAccess($user, null, []);

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEmpty($result->new_values);
    }

    /** @test */
    public function it_logs_change_with_empty_old_values()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logChange(
            $user,
            null,
            [],
            ['status' => 'active']
        );

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEmpty($result->old_values);
        $this->assertArrayHasKey('status', $result->new_values);
    }

    /** @test */
    public function it_logs_deletion_with_empty_data()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->auditLogger->logDeletion($user, null, []);

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertEmpty($result->old_values);
        $this->assertEmpty($result->new_values);
    }

    /** @test */
    public function it_creates_multiple_audit_logs_independently()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $this->auditLogger->logAccess($user);
        $this->auditLogger->logChange($user, null, ['name' => 'Old'], ['name' => 'New']);
        $this->auditLogger->logExport($user, null, ['format' => 'json']);

        $logs = AuditLog::all();

        $this->assertCount(3, $logs);
        $this->assertEquals('viewed', $logs[0]->event);
        $this->assertEquals('updated', $logs[1]->event);
        $this->assertEquals('exported', $logs[2]->event);
    }

    /** @test */
    public function it_handles_complex_nested_data_structures()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $complexData = [
            'user' => [
                'name' => 'John Doe',
                'preferences' => [
                    'theme' => 'dark',
                    'notifications' => ['email' => true, 'sms' => false],
                ],
            ],
            'metadata' => [
                'source' => 'web',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->auditLogger->logChange($user, null, [], $complexData);

        $this->assertInstanceOf(AuditLog::class, $result);
        $this->assertArrayHasKey('user', $result->new_values);
        $this->assertArrayHasKey('preferences', $result->new_values['user']);
        $this->assertArrayHasKey('notifications', $result->new_values['user']['preferences']);
    }

    /** @test */
    public function it_does_not_duplicate_tags()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Pass duplicate tags
        $result = $this->auditLogger->logAccess(
            $user,
            null,
            [],
            ['User', 'viewed', 'duplicate', 'duplicate']
        );

        $tagCounts = array_count_values($result->tags);

        // Each tag should appear only once
        foreach ($tagCounts as $count) {
            $this->assertEquals(1, $count);
        }
    }

    /** @test */
    public function it_respects_masked_fields_configuration_per_operation()
    {
        Config::set('compliance.audit_logging.masked_fields', ['api_key', 'token']);

        $user = User::factory()->create();
        Auth::login($user);

        // Test in logAccess
        $accessResult = $this->auditLogger->logAccess(
            $user,
            null,
            ['api_key' => 'secret123', 'token' => 'token456', 'email' => 'test@example.com']
        );

        $this->assertEquals('***MASKED***', $accessResult->new_values['api_key']);
        $this->assertEquals('***MASKED***', $accessResult->new_values['token']);
        $this->assertEquals('test@example.com', $accessResult->new_values['email']);

        // Test in logDeletion
        $deleteResult = $this->auditLogger->logDeletion(
            $user,
            null,
            ['api_key' => 'secret123', 'name' => 'Test User']
        );

        $this->assertEquals('***MASKED***', $deleteResult->old_values['api_key']);
        $this->assertEquals('Test User', $deleteResult->old_values['name']);

        // Test in logExport
        $exportResult = $this->auditLogger->logExport(
            $user,
            null,
            ['token' => 'export_token', 'format' => 'csv']
        );

        $this->assertEquals('***MASKED***', $exportResult->new_values['token']);
        $this->assertEquals('csv', $exportResult->new_values['format']);
    }

    /** @test */
    public function it_logs_events_for_different_model_types()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Log for User model
        $userLog = $this->auditLogger->logAccess($user);

        // Log for a different model type (using class string)
        $ticketLog = $this->auditLogger->logAccess('App\\Models\\SupportTicket', 123);

        $this->assertEquals(User::class, $userLog->auditable_type);
        $this->assertEquals('App\\Models\\SupportTicket', $ticketLog->auditable_type);
        $this->assertEquals(123, $ticketLog->auditable_id);
    }
}
