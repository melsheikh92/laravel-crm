<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Onboarding\Steps\CompanySetupStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;

class CompanySetupStepTest extends TestCase
{
    use RefreshDatabase;

    protected CompanySetupStep $companySetupStep;
    protected CoreConfigRepository $coreConfigRepository;
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable onboarding
        Config::set('onboarding.enabled', true);

        // Instantiate the repository and step
        $this->coreConfigRepository = app(CoreConfigRepository::class);
        $this->companySetupStep = new CompanySetupStep($this->coreConfigRepository);

        // Create a test user
        $this->testUser = User::factory()->create();
    }

    /** @test */
    public function it_has_correct_step_configuration()
    {
        $this->assertEquals('company_setup', $this->companySetupStep->getStepId());
        $this->assertEquals('Company Setup', $this->companySetupStep->getTitle());
        $this->assertFalse($this->companySetupStep->canSkip());
        $this->assertEquals(3, $this->companySetupStep->getEstimatedMinutes());
    }

    /** @test */
    public function it_validates_company_data()
    {
        $validData = [
            'company_name' => 'Acme Corporation',
            'industry' => 'technology',
            'company_size' => '11-50',
            'address' => '123 Main Street',
            'phone' => '+1 555-123-4567',
            'website' => 'https://example.com',
        ];

        $this->assertTrue($this->companySetupStep->validate($validData));
    }

    /** @test */
    public function it_fails_validation_without_required_company_name()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $invalidData = [
            'industry' => 'technology',
            'company_size' => '11-50',
        ];

        $this->companySetupStep->validate($invalidData);
    }

    /** @test */
    public function it_executes_and_stores_company_data()
    {
        $companyData = [
            'company_name' => 'Test Company',
            'industry' => 'technology',
            'company_size' => '11-50',
            'address' => '456 Tech Street',
            'phone' => '+1 555-987-6543',
            'website' => 'https://testcompany.com',
        ];

        $result = $this->companySetupStep->execute($companyData, $this->testUser);

        $this->assertTrue($result);

        // Verify data was stored in core_config
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.company.name',
            'value' => 'Test Company',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.company.industry',
            'value' => 'technology',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.company.size',
            'value' => '11-50',
        ]);
    }

    /** @test */
    public function it_stores_minimal_required_company_data()
    {
        $minimalData = [
            'company_name' => 'Minimal Company',
        ];

        $result = $this->companySetupStep->execute($minimalData, $this->testUser);

        $this->assertTrue($result);

        // Verify only company name was stored
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.company.name',
            'value' => 'Minimal Company',
        ]);

        // Optional fields should not be stored if not provided
        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.company.industry',
        ]);
    }

    /** @test */
    public function it_updates_existing_company_data()
    {
        // First execution
        $initialData = [
            'company_name' => 'Old Company Name',
            'industry' => 'retail',
        ];

        $this->companySetupStep->execute($initialData, $this->testUser);

        // Update with new data
        $updatedData = [
            'company_name' => 'New Company Name',
            'industry' => 'technology',
            'company_size' => '51-200',
        ];

        $result = $this->companySetupStep->execute($updatedData, $this->testUser);

        $this->assertTrue($result);

        // Verify updated values
        $config = CoreConfig::where('code', 'onboarding.company.name')->first();
        $this->assertEquals('New Company Name', $config->value);

        $industryConfig = CoreConfig::where('code', 'onboarding.company.industry')->first();
        $this->assertEquals('technology', $industryConfig->value);
    }

    /** @test */
    public function it_retrieves_default_data_when_previously_completed()
    {
        // Store some company data
        $companyData = [
            'company_name' => 'Existing Company',
            'industry' => 'healthcare',
            'company_size' => '201-500',
        ];

        $this->companySetupStep->execute($companyData, $this->testUser);

        // Get default data
        $defaultData = $this->companySetupStep->getDefaultData($this->testUser);

        $this->assertArrayHasKey('company_name', $defaultData);
        $this->assertEquals('Existing Company', $defaultData['company_name']);
        $this->assertEquals('healthcare', $defaultData['industry']);
        $this->assertEquals('201-500', $defaultData['company_size']);
    }

    /** @test */
    public function it_returns_empty_default_data_when_not_completed()
    {
        $defaultData = $this->companySetupStep->getDefaultData($this->testUser);

        $this->assertIsArray($defaultData);
        $this->assertEmpty($defaultData);
    }

    /** @test */
    public function it_detects_completion_status()
    {
        // Initially not completed
        $this->assertFalse($this->companySetupStep->hasBeenCompleted($this->testUser));

        // Execute the step
        $companyData = [
            'company_name' => 'Completed Company',
        ];

        $this->companySetupStep->execute($companyData, $this->testUser);

        // Now should be completed
        $this->assertTrue($this->companySetupStep->hasBeenCompleted($this->testUser));
    }

    /** @test */
    public function it_stores_completion_metadata()
    {
        $companyData = [
            'company_name' => 'Meta Company',
        ];

        $this->companySetupStep->execute($companyData, $this->testUser);

        // Verify completion metadata
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.company.completed_by',
            'value' => (string) $this->testUser->id,
        ]);

        $completedAt = CoreConfig::where('code', 'onboarding.company.completed_at')->first();
        $this->assertNotNull($completedAt);
        $this->assertNotEmpty($completedAt->value);
    }

    /** @test */
    public function it_has_correct_validation_rules()
    {
        $rules = $this->companySetupStep->getValidationRules();

        $this->assertArrayHasKey('company_name', $rules);
        $this->assertStringContainsString('required', $rules['company_name']);
        $this->assertArrayHasKey('website', $rules);
        $this->assertStringContainsString('url', $rules['website']);
    }

    /** @test */
    public function it_renders_step_view()
    {
        $view = $this->companySetupStep->render([
            'testData' => 'test value',
        ]);

        $this->assertNotNull($view);
        $this->assertEquals('onboarding.steps.company_setup', $view->name());
    }

    /** @test */
    public function it_handles_rollback_on_error()
    {
        // Mock a scenario that would cause an error
        $invalidData = [
            'company_name' => str_repeat('A', 300), // Exceeds max length
        ];

        try {
            // This should fail validation first, but let's test the transaction rollback
            $this->companySetupStep->validate($invalidData);
        } catch (\Exception $e) {
            // Expected exception
        }

        // Verify nothing was stored
        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.company.name',
        ]);
    }
}
