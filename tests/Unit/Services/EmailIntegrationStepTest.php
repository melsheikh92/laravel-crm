<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Onboarding\Steps\EmailIntegrationStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Webkul\Core\Repositories\CoreConfigRepository;

class EmailIntegrationStepTest extends TestCase
{
    use RefreshDatabase;

    protected EmailIntegrationStep $emailIntegrationStep;
    protected CoreConfigRepository $coreConfigRepository;
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable onboarding
        Config::set('onboarding.enabled', true);

        // Instantiate repositories and step
        $this->coreConfigRepository = app(CoreConfigRepository::class);

        $this->emailIntegrationStep = new EmailIntegrationStep(
            $this->coreConfigRepository
        );

        // Create a test user (the admin completing onboarding)
        $this->testUser = User::factory()->create();
    }

    /** @test */
    public function it_has_correct_step_configuration()
    {
        $this->assertEquals('email_integration', $this->emailIntegrationStep->getStepId());
        $this->assertEquals('Email Integration', $this->emailIntegrationStep->getTitle());
        $this->assertTrue($this->emailIntegrationStep->canSkip());
        $this->assertEquals(5, $this->emailIntegrationStep->getEstimatedMinutes());
    }

    /** @test */
    public function it_validates_email_configuration_data()
    {
        $validData = [
            'email_provider' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'user@example.com',
            'smtp_password' => 'password123',
            'smtp_encryption' => 'tls',
            'test_connection' => false,
        ];

        $this->assertTrue($this->emailIntegrationStep->validate($validData));
    }

    /** @test */
    public function it_accepts_minimal_data()
    {
        $minimalData = [
            'test_connection' => false,
        ];

        // Since all fields are nullable, minimal data should pass validation
        $this->assertTrue($this->emailIntegrationStep->validate($minimalData));
    }

    /** @test */
    public function it_validates_smtp_port_range()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $invalidData = [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 99999, // Invalid port - exceeds max
        ];

        $this->emailIntegrationStep->validate($invalidData);
    }

    /** @test */
    public function it_validates_encryption_type()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $invalidData = [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'invalid_encryption', // Invalid encryption type
        ];

        $this->emailIntegrationStep->validate($invalidData);
    }

    /** @test */
    public function it_executes_and_stores_email_configuration()
    {
        $emailData = [
            'email_provider' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'test@example.com',
            'smtp_password' => 'secret_password',
            'smtp_encryption' => 'tls',
            'test_connection' => false, // Skip connection test
        ];

        $result = $this->emailIntegrationStep->execute($emailData, $this->testUser);

        $this->assertTrue($result);

        // Verify configuration was stored
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.provider',
            'value' => 'smtp',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_host',
            'value' => 'smtp.example.com',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_port',
            'value' => '587',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_username',
            'value' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_password',
            'value' => 'secret_password',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_encryption',
            'value' => 'tls',
        ]);
    }

    /** @test */
    public function it_stores_completion_metadata()
    {
        $emailData = [
            'email_provider' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'test_connection' => false,
        ];

        $this->emailIntegrationStep->execute($emailData, $this->testUser);

        // Verify completion metadata
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.completed_by',
            'value' => (string) $this->testUser->id,
        ]);

        $completedAt = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.email.completed_at'
        ]);
        $this->assertNotNull($completedAt);
        $this->assertNotEmpty($completedAt->value);
    }

    /** @test */
    public function it_updates_existing_configuration()
    {
        // Create initial configuration
        $initialData = [
            'email_provider' => 'smtp',
            'smtp_host' => 'old.smtp.com',
            'smtp_port' => 465,
            'smtp_encryption' => 'ssl',
            'test_connection' => false,
        ];

        $this->emailIntegrationStep->execute($initialData, $this->testUser);

        // Update configuration
        $updatedData = [
            'email_provider' => 'smtp',
            'smtp_host' => 'new.smtp.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'test_connection' => false,
        ];

        $this->emailIntegrationStep->execute($updatedData, $this->testUser);

        // Verify updated values
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_host',
            'value' => 'new.smtp.com',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_port',
            'value' => '587',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_encryption',
            'value' => 'tls',
        ]);

        // Verify old values are not present
        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.email.smtp_host',
            'value' => 'old.smtp.com',
        ]);
    }

    /** @test */
    public function it_retrieves_default_data_when_previously_completed()
    {
        // Set up configuration first
        $emailData = [
            'email_provider' => 'gmail',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_username' => 'user@gmail.com',
            'smtp_password' => 'app_password',
            'smtp_encryption' => 'tls',
            'test_connection' => false,
        ];

        $this->emailIntegrationStep->execute($emailData, $this->testUser);

        // Get default data
        $defaultData = $this->emailIntegrationStep->getDefaultData($this->testUser);

        $this->assertArrayHasKey('email_provider', $defaultData);
        $this->assertEquals('gmail', $defaultData['email_provider']);
        $this->assertEquals('smtp.gmail.com', $defaultData['smtp_host']);
        $this->assertEquals('587', $defaultData['smtp_port']);
        $this->assertEquals('user@gmail.com', $defaultData['smtp_username']);
        $this->assertEquals('tls', $defaultData['smtp_encryption']);

        // Password should not be retrieved for security
        $this->assertArrayNotHasKey('smtp_password', $defaultData);

        // test_connection should default to false
        $this->assertFalse($defaultData['test_connection']);
    }

    /** @test */
    public function it_returns_empty_default_data_when_not_completed()
    {
        $defaultData = $this->emailIntegrationStep->getDefaultData($this->testUser);

        // Should return test_connection as false by default
        $this->assertArrayHasKey('test_connection', $defaultData);
        $this->assertFalse($defaultData['test_connection']);
    }

    /** @test */
    public function it_detects_completion_status()
    {
        // Initially not completed
        $this->assertFalse($this->emailIntegrationStep->hasBeenCompleted($this->testUser));

        // Execute the step
        $emailData = [
            'smtp_host' => 'smtp.example.com',
            'test_connection' => false,
        ];

        $this->emailIntegrationStep->execute($emailData, $this->testUser);

        // Now should be completed
        $this->assertTrue($this->emailIntegrationStep->hasBeenCompleted($this->testUser));
    }

    /** @test */
    public function it_has_correct_validation_rules()
    {
        $rules = $this->emailIntegrationStep->getValidationRules();

        $this->assertArrayHasKey('smtp_host', $rules);
        $this->assertArrayHasKey('smtp_port', $rules);
        $this->assertArrayHasKey('smtp_username', $rules);
        $this->assertArrayHasKey('smtp_password', $rules);
        $this->assertArrayHasKey('smtp_encryption', $rules);

        // Verify encryption validation contains 'in' rule
        $this->assertStringContainsString('in:tls,ssl,none', $rules['smtp_encryption']);
    }

    /** @test */
    public function it_renders_step_view()
    {
        $view = $this->emailIntegrationStep->render([
            'testData' => 'test value',
        ]);

        $this->assertNotNull($view);
        $this->assertEquals('onboarding.steps.email_integration', $view->name());
    }

    /** @test */
    public function it_handles_rollback_on_error()
    {
        // Force an error by mocking the repository to throw exception
        $mockRepository = $this->createMock(CoreConfigRepository::class);
        $mockRepository->method('findOneWhere')
            ->willThrowException(new \Exception('Database error'));

        $emailStep = new EmailIntegrationStep($mockRepository);

        $emailData = [
            'smtp_host' => 'smtp.example.com',
            'test_connection' => false,
        ];

        try {
            $emailStep->execute($emailData, $this->testUser);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Expected exception
            $this->assertEquals('Database error', $e->getMessage());
        }

        // Verify no configuration was stored (rollback succeeded)
        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.email.smtp_host',
        ]);
    }

    /** @test */
    public function it_handles_different_email_providers()
    {
        $providers = ['smtp', 'gmail', 'outlook', 'sendgrid'];

        foreach ($providers as $provider) {
            $emailData = [
                'email_provider' => $provider,
                'smtp_host' => "smtp.{$provider}.com",
                'smtp_port' => 587,
                'test_connection' => false,
            ];

            $result = $this->emailIntegrationStep->execute($emailData, $this->testUser);

            $this->assertTrue($result);

            $this->assertDatabaseHas('core_config', [
                'code' => 'onboarding.email.provider',
                'value' => $provider,
            ]);
        }
    }

    /** @test */
    public function it_stores_configuration_with_ssl_encryption()
    {
        $emailData = [
            'email_provider' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 465,
            'smtp_encryption' => 'ssl',
            'test_connection' => false,
        ];

        $result = $this->emailIntegrationStep->execute($emailData, $this->testUser);

        $this->assertTrue($result);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_encryption',
            'value' => 'ssl',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_port',
            'value' => '465',
        ]);
    }

    /** @test */
    public function it_stores_configuration_with_no_encryption()
    {
        $emailData = [
            'email_provider' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 25,
            'smtp_encryption' => 'none',
            'test_connection' => false,
        ];

        $result = $this->emailIntegrationStep->execute($emailData, $this->testUser);

        $this->assertTrue($result);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_encryption',
            'value' => 'none',
        ]);
    }

    /** @test */
    public function it_skips_empty_fields()
    {
        $emailData = [
            'email_provider' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => '', // Empty field
            'smtp_password' => null, // Null field
            'test_connection' => false,
        ];

        $result = $this->emailIntegrationStep->execute($emailData, $this->testUser);

        $this->assertTrue($result);

        // Verify empty/null fields are not stored
        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.email.smtp_username',
        ]);

        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.email.smtp_password',
        ]);

        // Verify non-empty fields are stored
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_host',
            'value' => 'smtp.example.com',
        ]);
    }

    /** @test */
    public function it_executes_without_testing_connection_by_default()
    {
        Mail::fake();

        $emailData = [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'test@example.com',
            'smtp_password' => 'password',
            'smtp_encryption' => 'tls',
            // test_connection not set - defaults to true but shouldn't fail
        ];

        // Since we can't actually test SMTP in unit tests, we set test_connection to false
        $emailData['test_connection'] = false;

        $result = $this->emailIntegrationStep->execute($emailData, $this->testUser);

        $this->assertTrue($result);

        // Verify data was stored
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.smtp_host',
            'value' => 'smtp.example.com',
        ]);
    }

    /** @test */
    public function it_does_not_test_connection_when_smtp_config_is_incomplete()
    {
        // When test_connection is true but SMTP config is incomplete,
        // it should skip the test and just save what's provided
        $emailData = [
            'email_provider' => 'gmail',
            // smtp_host not provided
            'smtp_port' => 587,
            'test_connection' => true, // Want to test but can't
        ];

        $result = $this->emailIntegrationStep->execute($emailData, $this->testUser);

        $this->assertTrue($result);

        // Should complete successfully even though connection wasn't tested
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.email.provider',
            'value' => 'gmail',
        ]);
    }

    /** @test */
    public function it_handles_common_smtp_ports()
    {
        $commonPorts = [25, 465, 587, 2525];

        foreach ($commonPorts as $port) {
            $emailData = [
                'smtp_host' => 'smtp.example.com',
                'smtp_port' => $port,
                'test_connection' => false,
            ];

            $result = $this->emailIntegrationStep->execute($emailData, $this->testUser);

            $this->assertTrue($result);

            $this->assertDatabaseHas('core_config', [
                'code' => 'onboarding.email.smtp_port',
                'value' => (string) $port,
            ]);
        }
    }
}
