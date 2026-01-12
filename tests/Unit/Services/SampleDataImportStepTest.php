<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Onboarding\Steps\SampleDataImportStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Models\Type;

class SampleDataImportStepTest extends TestCase
{
    use RefreshDatabase;

    protected SampleDataImportStep $sampleDataImportStep;
    protected CoreConfigRepository $coreConfigRepository;
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable onboarding
        Config::set('onboarding.enabled', true);

        // Instantiate the repository and step
        $this->coreConfigRepository = app(CoreConfigRepository::class);
        $this->sampleDataImportStep = new SampleDataImportStep($this->coreConfigRepository);

        // Create a test user
        $this->testUser = User::factory()->create();
    }

    /** @test */
    public function it_has_correct_step_configuration()
    {
        $this->assertEquals('sample_data', $this->sampleDataImportStep->getStepId());
        $this->assertEquals('Import Sample Data', $this->sampleDataImportStep->getTitle());
        $this->assertTrue($this->sampleDataImportStep->canSkip());
        $this->assertEquals(2, $this->sampleDataImportStep->getEstimatedMinutes());
    }

    /** @test */
    public function it_validates_sample_data_import_data()
    {
        $validData = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => true,
        ];

        $this->assertTrue($this->sampleDataImportStep->validate($validData));
    }

    /** @test */
    public function it_validates_with_minimal_data()
    {
        $minimalData = [];

        // All fields are optional
        $this->assertTrue($this->sampleDataImportStep->validate($minimalData));
    }

    /** @test */
    public function it_stores_preferences_without_importing_data()
    {
        $data = [
            'import_sample_data' => false,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => false,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify preferences were stored
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.sample_data.import_requested',
            'value' => '0',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.sample_data.include_companies',
            'value' => '1',
        ]);

        // Verify no sample data was created
        $this->assertEquals(0, Organization::count());
        $this->assertEquals(0, Person::count());
        $this->assertEquals(0, Lead::count());
    }

    /** @test */
    public function it_imports_all_sample_data_when_requested()
    {
        $this->createPipelineWithStages();

        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => true,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify sample data was created
        $this->assertGreaterThan(0, Organization::count());
        $this->assertGreaterThan(0, Person::count());
        $this->assertGreaterThan(0, Lead::count());

        // Verify import counts were stored
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.sample_data.import_requested',
            'value' => '1',
        ]);

        $organizationsImported = CoreConfig::where('code', 'onboarding.sample_data.organizations_imported')->first();
        $this->assertNotNull($organizationsImported);
        $this->assertGreaterThan(0, (int) $organizationsImported->value);
    }

    /** @test */
    public function it_imports_only_companies_when_specified()
    {
        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => false,
            'include_deals' => false,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify only organizations were created
        $this->assertGreaterThan(0, Organization::count());
        $this->assertEquals(0, Person::count());
        $this->assertEquals(0, Lead::count());
    }

    /** @test */
    public function it_imports_companies_and_contacts_but_not_deals()
    {
        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => false,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify organizations and persons were created but not leads
        $this->assertGreaterThan(0, Organization::count());
        $this->assertGreaterThan(0, Person::count());
        $this->assertEquals(0, Lead::count());
    }

    /** @test */
    public function it_skips_contacts_if_no_companies_imported()
    {
        $data = [
            'import_sample_data' => true,
            'include_companies' => false,
            'include_contacts' => true,
            'include_deals' => false,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify no data was created (contacts require companies)
        $this->assertEquals(0, Organization::count());
        $this->assertEquals(0, Person::count());
    }

    /** @test */
    public function it_skips_deals_if_no_contacts_imported()
    {
        $this->createPipelineWithStages();

        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => false,
            'include_deals' => true,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify companies created but not persons or leads (deals require contacts)
        $this->assertGreaterThan(0, Organization::count());
        $this->assertEquals(0, Person::count());
        $this->assertEquals(0, Lead::count());
    }

    /** @test */
    public function it_creates_pipeline_if_none_exists()
    {
        $this->assertEquals(0, Pipeline::count());

        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => true,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify pipeline and stages were created
        $this->assertEquals(1, Pipeline::count());
        $this->assertGreaterThan(0, Stage::count());
    }

    /** @test */
    public function it_uses_existing_pipeline_if_available()
    {
        $existingPipeline = $this->createPipelineWithStages();

        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => true,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify only one pipeline exists (no duplicate created)
        $this->assertEquals(1, Pipeline::count());

        // Verify leads use the existing pipeline
        $leads = Lead::all();
        foreach ($leads as $lead) {
            $this->assertEquals($existingPipeline->id, $lead->lead_pipeline_id);
        }
    }

    /** @test */
    public function it_creates_default_sources_and_types_if_none_exist()
    {
        $this->assertEquals(0, Source::count());
        $this->assertEquals(0, Type::count());

        $this->createPipelineWithStages();

        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => true,
        ];

        $result = $this->sampleDataImportStep->execute($data, $this->testUser);

        $this->assertTrue($result);

        // Verify sources and types were created
        $this->assertGreaterThan(0, Source::count());
        $this->assertGreaterThan(0, Type::count());
    }

    /** @test */
    public function it_stores_completion_metadata()
    {
        $data = [
            'import_sample_data' => false,
        ];

        $this->sampleDataImportStep->execute($data, $this->testUser);

        // Verify completion metadata
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.sample_data.completed_by',
            'value' => (string) $this->testUser->id,
        ]);

        $completedAt = CoreConfig::where('code', 'onboarding.sample_data.completed_at')->first();
        $this->assertNotNull($completedAt);
        $this->assertNotEmpty($completedAt->value);
    }

    /** @test */
    public function it_retrieves_default_data_when_previously_completed()
    {
        // Store some sample data preferences
        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => false,
            'include_deals' => true,
        ];

        $this->sampleDataImportStep->execute($data, $this->testUser);

        // Get default data
        $defaultData = $this->sampleDataImportStep->getDefaultData($this->testUser);

        $this->assertArrayHasKey('import_sample_data', $defaultData);
        $this->assertTrue($defaultData['import_sample_data']);
        $this->assertTrue($defaultData['include_companies']);
        $this->assertFalse($defaultData['include_contacts']);
        $this->assertTrue($defaultData['include_deals']);
    }

    /** @test */
    public function it_returns_default_values_when_not_completed()
    {
        $defaultData = $this->sampleDataImportStep->getDefaultData($this->testUser);

        $this->assertIsArray($defaultData);
        $this->assertArrayHasKey('import_sample_data', $defaultData);
        $this->assertFalse($defaultData['import_sample_data']);
        $this->assertTrue($defaultData['include_companies']);
        $this->assertTrue($defaultData['include_contacts']);
        $this->assertTrue($defaultData['include_deals']);
    }

    /** @test */
    public function it_detects_completion_status()
    {
        // Initially not completed
        $this->assertFalse($this->sampleDataImportStep->hasBeenCompleted($this->testUser));

        // Execute the step
        $data = [
            'import_sample_data' => false,
        ];

        $this->sampleDataImportStep->execute($data, $this->testUser);

        // Now should be completed
        $this->assertTrue($this->sampleDataImportStep->hasBeenCompleted($this->testUser));
    }

    /** @test */
    public function it_has_correct_validation_rules()
    {
        $rules = $this->sampleDataImportStep->getValidationRules();

        $this->assertArrayHasKey('import_sample_data', $rules);
        $this->assertArrayHasKey('include_companies', $rules);
        $this->assertArrayHasKey('include_contacts', $rules);
        $this->assertArrayHasKey('include_deals', $rules);
    }

    /** @test */
    public function it_renders_step_view()
    {
        $view = $this->sampleDataImportStep->render([
            'testData' => 'test value',
        ]);

        $this->assertNotNull($view);
        $this->assertEquals('onboarding.steps.sample_data', $view->name());
    }

    /** @test */
    public function it_handles_rollback_on_error()
    {
        // Mock a scenario that would cause an error by using an invalid user ID
        $invalidUserId = 99999;

        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => true,
        ];

        try {
            $this->sampleDataImportStep->execute($data, $invalidUserId);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Expected exception
            $this->assertStringContainsString('User not found', $e->getMessage());
        }

        // Verify nothing was stored in database
        $this->assertEquals(0, Organization::count());
        $this->assertEquals(0, Person::count());
        $this->assertEquals(0, Lead::count());
    }

    /** @test */
    public function it_imports_expected_number_of_organizations()
    {
        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => false,
            'include_deals' => false,
        ];

        $this->sampleDataImportStep->execute($data, $this->testUser);

        // Verify the expected 5 sample companies
        $this->assertEquals(5, Organization::count());

        // Verify they have the expected names
        $expectedNames = [
            'Acme Corporation',
            'TechStart Solutions',
            'Global Enterprises',
            'Innovation Labs',
            'Blue Ocean Industries',
        ];

        foreach ($expectedNames as $name) {
            $this->assertDatabaseHas('organizations', ['name' => $name]);
        }
    }

    /** @test */
    public function it_imports_multiple_persons_per_organization()
    {
        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => false,
        ];

        $this->sampleDataImportStep->execute($data, $this->testUser);

        $organizationCount = Organization::count();
        $personCount = Person::count();

        // Should have 2-3 contacts per organization (5 organizations * 2-3 = 10-15 persons)
        $this->assertGreaterThanOrEqual($organizationCount * 2, $personCount);
        $this->assertLessThanOrEqual($organizationCount * 3, $personCount);

        // Verify all persons are linked to organizations
        $persons = Person::all();
        foreach ($persons as $person) {
            $this->assertNotNull($person->organization_id);
        }
    }

    /** @test */
    public function it_imports_deals_with_various_stages()
    {
        $this->createPipelineWithStages();

        $data = [
            'import_sample_data' => true,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => true,
        ];

        $this->sampleDataImportStep->execute($data, $this->testUser);

        $leads = Lead::all();

        $this->assertGreaterThan(0, $leads->count());

        // Verify leads are distributed across different stages
        $uniqueStages = $leads->pluck('lead_pipeline_stage_id')->unique();
        $this->assertGreaterThan(1, $uniqueStages->count());

        // Verify all leads have required associations
        foreach ($leads as $lead) {
            $this->assertNotNull($lead->person_id);
            $this->assertNotNull($lead->lead_pipeline_id);
            $this->assertNotNull($lead->lead_pipeline_stage_id);
            $this->assertNotNull($lead->lead_source_id);
            $this->assertNotNull($lead->lead_type_id);
        }
    }

    /** @test */
    public function it_updates_preferences_when_run_multiple_times()
    {
        // First execution
        $initialData = [
            'import_sample_data' => false,
            'include_companies' => true,
            'include_contacts' => true,
            'include_deals' => true,
        ];

        $this->sampleDataImportStep->execute($initialData, $this->testUser);

        // Update with new preferences
        $updatedData = [
            'import_sample_data' => true,
            'include_companies' => false,
            'include_contacts' => false,
            'include_deals' => false,
        ];

        $result = $this->sampleDataImportStep->execute($updatedData, $this->testUser);

        $this->assertTrue($result);

        // Verify updated values
        $config = CoreConfig::where('code', 'onboarding.sample_data.import_requested')->first();
        $this->assertEquals('1', $config->value);

        $companiesConfig = CoreConfig::where('code', 'onboarding.sample_data.include_companies')->first();
        $this->assertEquals('0', $companiesConfig->value);
    }

    /**
     * Helper method to create a pipeline with stages for testing.
     *
     * @return Pipeline
     */
    protected function createPipelineWithStages(): Pipeline
    {
        $pipeline = Pipeline::create([
            'name' => 'Test Sales Pipeline',
            'rotten_days' => 30,
            'is_default' => 1,
        ]);

        $stages = [
            ['code' => 'new', 'name' => 'New', 'probability' => 10, 'sort_order' => 1],
            ['code' => 'qualified', 'name' => 'Qualified', 'probability' => 25, 'sort_order' => 2],
            ['code' => 'proposal', 'name' => 'Proposal', 'probability' => 50, 'sort_order' => 3],
            ['code' => 'negotiation', 'name' => 'Negotiation', 'probability' => 75, 'sort_order' => 4],
            ['code' => 'won', 'name' => 'Won', 'probability' => 100, 'sort_order' => 5],
            ['code' => 'lost', 'name' => 'Lost', 'probability' => 0, 'sort_order' => 6],
        ];

        foreach ($stages as $stage) {
            Stage::create(array_merge($stage, ['lead_pipeline_id' => $pipeline->id]));
        }

        return $pipeline;
    }
}
