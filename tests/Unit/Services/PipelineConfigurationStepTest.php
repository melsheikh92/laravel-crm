<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Onboarding\Steps\PipelineConfigurationStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\StageRepository;

class PipelineConfigurationStepTest extends TestCase
{
    use RefreshDatabase;

    protected PipelineConfigurationStep $pipelineConfigurationStep;
    protected CoreConfigRepository $coreConfigRepository;
    protected PipelineRepository $pipelineRepository;
    protected StageRepository $stageRepository;
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable onboarding
        Config::set('onboarding.enabled', true);

        // Instantiate the repositories and step
        $this->coreConfigRepository = app(CoreConfigRepository::class);
        $this->pipelineRepository = app(PipelineRepository::class);
        $this->stageRepository = app(StageRepository::class);
        $this->pipelineConfigurationStep = new PipelineConfigurationStep(
            $this->coreConfigRepository,
            $this->pipelineRepository,
            $this->stageRepository
        );

        // Create a test user
        $this->testUser = User::factory()->create();
    }

    /** @test */
    public function it_has_correct_step_configuration()
    {
        $this->assertEquals('pipeline_config', $this->pipelineConfigurationStep->getStepId());
        $this->assertEquals('Configure Sales Pipeline', $this->pipelineConfigurationStep->getTitle());
        $this->assertTrue($this->pipelineConfigurationStep->canSkip());
        $this->assertEquals(4, $this->pipelineConfigurationStep->getEstimatedMinutes());
    }

    /** @test */
    public function it_validates_pipeline_data()
    {
        $validData = [
            'pipeline_name' => 'Sales Pipeline',
            'stages' => [
                ['name' => 'New', 'probability' => 10, 'order' => 1],
                ['name' => 'Qualified', 'probability' => 25, 'order' => 2],
            ],
        ];

        $this->assertTrue($this->pipelineConfigurationStep->validate($validData));
    }

    /** @test */
    public function it_validates_empty_data_for_defaults()
    {
        // Empty data should be valid as defaults will be used
        $emptyData = [];

        $this->assertTrue($this->pipelineConfigurationStep->validate($emptyData));
    }

    /** @test */
    public function it_fails_validation_with_invalid_stage_probability()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $invalidData = [
            'pipeline_name' => 'Sales Pipeline',
            'stages' => [
                ['name' => 'New', 'probability' => 150, 'order' => 1], // Invalid probability > 100
            ],
        ];

        $this->pipelineConfigurationStep->validate($invalidData);
    }

    /** @test */
    public function it_fails_validation_with_missing_stage_name()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $invalidData = [
            'pipeline_name' => 'Sales Pipeline',
            'stages' => [
                ['probability' => 50, 'order' => 1], // Missing name
            ],
        ];

        $this->pipelineConfigurationStep->validate($invalidData);
    }

    /** @test */
    public function it_creates_pipeline_with_custom_stages()
    {
        $pipelineData = [
            'pipeline_name' => 'Custom Sales Pipeline',
            'stages' => [
                ['name' => 'Lead', 'probability' => 10, 'order' => 1],
                ['name' => 'Opportunity', 'probability' => 50, 'order' => 2],
                ['name' => 'Closed Won', 'probability' => 100, 'order' => 3],
            ],
        ];

        $result = $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        $this->assertTrue($result);

        // Verify pipeline was created
        $this->assertDatabaseHas('lead_pipelines', [
            'name' => 'Custom Sales Pipeline',
            'is_default' => 1,
        ]);

        // Verify stages were created
        $this->assertDatabaseHas('lead_pipeline_stages', [
            'name' => 'Lead',
            'probability' => 10,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('lead_pipeline_stages', [
            'name' => 'Opportunity',
            'probability' => 50,
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('lead_pipeline_stages', [
            'name' => 'Closed Won',
            'probability' => 100,
            'sort_order' => 3,
        ]);
    }

    /** @test */
    public function it_creates_pipeline_with_default_stages_when_no_stages_provided()
    {
        $pipelineData = [
            'pipeline_name' => 'Default Pipeline',
        ];

        $result = $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        $this->assertTrue($result);

        // Verify pipeline was created
        $this->assertDatabaseHas('lead_pipelines', [
            'name' => 'Default Pipeline',
            'is_default' => 1,
        ]);

        // Verify default stages were created (from config)
        $defaultStages = config('onboarding.steps.pipeline_config.default_stages');
        foreach ($defaultStages as $stage) {
            $this->assertDatabaseHas('lead_pipeline_stages', [
                'name' => $stage['name'],
                'probability' => $stage['probability'],
            ]);
        }
    }

    /** @test */
    public function it_creates_pipeline_with_default_name_when_no_name_provided()
    {
        $pipelineData = [];

        $result = $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        $this->assertTrue($result);

        // Verify pipeline was created with default name
        $this->assertDatabaseHas('lead_pipelines', [
            'name' => 'Default Sales Pipeline',
            'is_default' => 1,
        ]);
    }

    /** @test */
    public function it_updates_existing_pipeline()
    {
        // Create initial pipeline
        $initialData = [
            'pipeline_name' => 'Initial Pipeline',
            'stages' => [
                ['name' => 'Stage 1', 'probability' => 10, 'order' => 1],
                ['name' => 'Stage 2', 'probability' => 50, 'order' => 2],
            ],
        ];

        $this->pipelineConfigurationStep->execute($initialData, $this->testUser);

        // Update pipeline
        $updatedData = [
            'pipeline_name' => 'Updated Pipeline',
            'stages' => [
                ['name' => 'New Stage 1', 'probability' => 20, 'order' => 1],
                ['name' => 'New Stage 2', 'probability' => 60, 'order' => 2],
                ['name' => 'New Stage 3', 'probability' => 100, 'order' => 3],
            ],
        ];

        $result = $this->pipelineConfigurationStep->execute($updatedData, $this->testUser);

        $this->assertTrue($result);

        // Verify pipeline was updated
        $pipeline = Pipeline::where('name', 'Updated Pipeline')->first();
        $this->assertNotNull($pipeline);

        // Verify new stages exist
        $this->assertDatabaseHas('lead_pipeline_stages', [
            'name' => 'New Stage 1',
            'probability' => 20,
        ]);

        $this->assertDatabaseHas('lead_pipeline_stages', [
            'name' => 'New Stage 3',
            'probability' => 100,
        ]);
    }

    /** @test */
    public function it_stores_completion_metadata()
    {
        $pipelineData = [
            'pipeline_name' => 'Test Pipeline',
        ];

        $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        // Verify completion metadata
        $pipeline = Pipeline::where('name', 'Test Pipeline')->first();
        $this->assertNotNull($pipeline);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.pipeline_config.pipeline_id',
            'value' => (string) $pipeline->id,
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.pipeline_config.completed_by',
            'value' => (string) $this->testUser->id,
        ]);

        $completedAt = CoreConfig::where('code', 'onboarding.pipeline_config.completed_at')->first();
        $this->assertNotNull($completedAt);
        $this->assertNotEmpty($completedAt->value);
    }

    /** @test */
    public function it_retrieves_default_data_when_previously_completed()
    {
        // Create a pipeline
        $pipelineData = [
            'pipeline_name' => 'Existing Pipeline',
            'stages' => [
                ['name' => 'Existing Stage 1', 'probability' => 30, 'order' => 1],
                ['name' => 'Existing Stage 2', 'probability' => 70, 'order' => 2],
            ],
        ];

        $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        // Get default data
        $defaultData = $this->pipelineConfigurationStep->getDefaultData($this->testUser);

        $this->assertArrayHasKey('pipeline_name', $defaultData);
        $this->assertEquals('Existing Pipeline', $defaultData['pipeline_name']);

        $this->assertArrayHasKey('stages', $defaultData);
        $this->assertCount(2, $defaultData['stages']);

        $this->assertEquals('Existing Stage 1', $defaultData['stages'][0]['name']);
        $this->assertEquals(30, $defaultData['stages'][0]['probability']);
    }

    /** @test */
    public function it_returns_default_configuration_when_not_completed()
    {
        $defaultData = $this->pipelineConfigurationStep->getDefaultData($this->testUser);

        $this->assertIsArray($defaultData);
        $this->assertArrayHasKey('pipeline_name', $defaultData);
        $this->assertArrayHasKey('stages', $defaultData);

        // Should return default stages from config
        $defaultStages = config('onboarding.steps.pipeline_config.default_stages');
        $this->assertCount(count($defaultStages), $defaultData['stages']);
    }

    /** @test */
    public function it_detects_completion_status()
    {
        // Initially not completed
        $this->assertFalse($this->pipelineConfigurationStep->hasBeenCompleted($this->testUser));

        // Execute the step
        $pipelineData = [
            'pipeline_name' => 'Completed Pipeline',
        ];

        $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        // Now should be completed
        $this->assertTrue($this->pipelineConfigurationStep->hasBeenCompleted($this->testUser));
    }

    /** @test */
    public function it_has_correct_validation_rules()
    {
        $rules = $this->pipelineConfigurationStep->getValidationRules();

        $this->assertArrayHasKey('pipeline_name', $rules);
        $this->assertArrayHasKey('stages', $rules);
        $this->assertArrayHasKey('stages.*.name', $rules);
        $this->assertArrayHasKey('stages.*.probability', $rules);
    }

    /** @test */
    public function it_renders_step_view()
    {
        $view = $this->pipelineConfigurationStep->render([
            'testData' => 'test value',
        ]);

        $this->assertNotNull($view);
        $this->assertEquals('onboarding.steps.pipeline_config', $view->name());
    }

    /** @test */
    public function it_generates_stage_codes_from_names()
    {
        $pipelineData = [
            'pipeline_name' => 'Test Pipeline',
            'stages' => [
                ['name' => 'New Lead', 'probability' => 10, 'order' => 1],
                ['name' => 'Qualified Lead', 'probability' => 25, 'order' => 2],
            ],
        ];

        $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        // Verify stage codes are generated correctly
        $this->assertDatabaseHas('lead_pipeline_stages', [
            'name' => 'New Lead',
            'code' => 'new_lead',
        ]);

        $this->assertDatabaseHas('lead_pipeline_stages', [
            'name' => 'Qualified Lead',
            'code' => 'qualified_lead',
        ]);
    }

    /** @test */
    public function it_creates_default_pipeline_when_skipped_and_none_exists()
    {
        // Initially no pipeline exists
        $this->assertEquals(0, Pipeline::count());

        // Skip the step
        $this->pipelineConfigurationStep->onSkip($this->testUser);

        // Verify default pipeline was created
        $this->assertEquals(1, Pipeline::count());

        $pipeline = Pipeline::first();
        $this->assertEquals('Default Sales Pipeline', $pipeline->name);
        $this->assertEquals(1, $pipeline->is_default);

        // Verify default stages were created
        $this->assertGreaterThan(0, $pipeline->stages()->count());

        // Verify skip metadata was stored
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.pipeline_config.pipeline_id',
            'value' => (string) $pipeline->id,
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.pipeline_config.skipped_by',
            'value' => (string) $this->testUser->id,
        ]);
    }

    /** @test */
    public function it_does_not_create_duplicate_pipeline_when_skipped_and_one_exists()
    {
        // Create a pipeline first
        $pipelineData = [
            'pipeline_name' => 'Existing Pipeline',
        ];

        $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        $initialCount = Pipeline::count();

        // Skip the step
        $this->pipelineConfigurationStep->onSkip($this->testUser);

        // Verify no duplicate pipeline was created
        $this->assertEquals($initialCount, Pipeline::count());
    }

    /** @test */
    public function it_handles_rollback_on_error()
    {
        // Create a scenario that would cause an error
        // by mocking the repository to throw an exception
        $this->mock(PipelineRepository::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Reinstantiate step with mocked repository
        $mockedStep = new PipelineConfigurationStep(
            $this->coreConfigRepository,
            app(PipelineRepository::class),
            $this->stageRepository
        );

        try {
            $mockedStep->execute([
                'pipeline_name' => 'Failed Pipeline',
            ], $this->testUser);
        } catch (\Exception $e) {
            // Expected exception
        }

        // Verify nothing was stored in core_config
        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.pipeline_config.pipeline_id',
        ]);

        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.pipeline_config.completed_at',
        ]);
    }

    /** @test */
    public function it_sets_pipeline_as_default()
    {
        $pipelineData = [
            'pipeline_name' => 'Main Pipeline',
        ];

        $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        // Verify pipeline is set as default
        $pipeline = Pipeline::where('name', 'Main Pipeline')->first();
        $this->assertEquals(1, $pipeline->is_default);
    }

    /** @test */
    public function it_handles_stages_with_missing_probability()
    {
        $pipelineData = [
            'pipeline_name' => 'Test Pipeline',
            'stages' => [
                ['name' => 'Stage Without Probability', 'order' => 1],
            ],
        ];

        $result = $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        $this->assertTrue($result);

        // Verify stage was created with default probability (50)
        $this->assertDatabaseHas('lead_pipeline_stages', [
            'name' => 'Stage Without Probability',
            'probability' => 50,
        ]);
    }

    /** @test */
    public function it_handles_stages_with_missing_order()
    {
        $pipelineData = [
            'pipeline_name' => 'Test Pipeline',
            'stages' => [
                ['name' => 'First Stage', 'probability' => 10],
                ['name' => 'Second Stage', 'probability' => 50],
            ],
        ];

        $result = $this->pipelineConfigurationStep->execute($pipelineData, $this->testUser);

        $this->assertTrue($result);

        // Verify stages were created with auto-incremented sort_order
        $stage1 = Stage::where('name', 'First Stage')->first();
        $stage2 = Stage::where('name', 'Second Stage')->first();

        $this->assertEquals(1, $stage1->sort_order);
        $this->assertEquals(2, $stage2->sort_order);
    }
}
