<?php

namespace App\Services\Onboarding\Steps;

use App\Services\Onboarding\AbstractWizardStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\StageRepository;

/**
 * Pipeline Configuration Step
 *
 * This step allows the administrator to set up their sales pipeline with
 * customizable stages. Users can create a new pipeline with custom stages
 * or use default stage templates with drag-drop ordering support.
 *
 * @package App\Services\Onboarding\Steps
 */
class PipelineConfigurationStep extends AbstractWizardStep
{
    /**
     * Core config repository instance.
     *
     * @var CoreConfigRepository
     */
    protected CoreConfigRepository $coreConfigRepository;

    /**
     * Pipeline repository instance.
     *
     * @var PipelineRepository
     */
    protected PipelineRepository $pipelineRepository;

    /**
     * Stage repository instance.
     *
     * @var StageRepository
     */
    protected StageRepository $stageRepository;

    /**
     * Create a new PipelineConfigurationStep instance.
     *
     * @param CoreConfigRepository $coreConfigRepository
     * @param PipelineRepository $pipelineRepository
     * @param StageRepository $stageRepository
     */
    public function __construct(
        CoreConfigRepository $coreConfigRepository,
        PipelineRepository $pipelineRepository,
        StageRepository $stageRepository
    ) {
        $this->coreConfigRepository = $coreConfigRepository;
        $this->pipelineRepository = $pipelineRepository;
        $this->stageRepository = $stageRepository;

        // Load configuration from onboarding config
        $config = config('onboarding.steps.pipeline_config', []);
        $validation = config('onboarding.validation.pipeline_config', []);

        // Set step properties from config
        $this->stepId = 'pipeline_config';
        $this->title = $config['title'] ?? 'Configure Sales Pipeline';
        $this->description = $config['description'] ?? 'Set up your sales stages and workflow';
        $this->icon = $config['icon'] ?? 'filter';
        $this->estimatedMinutes = $config['estimated_minutes'] ?? 4;
        $this->skippable = $config['skippable'] ?? true;
        $this->helpText = $config['help_text'] ?? '';
        $this->helpTips = $config['help_tips'] ?? [];
        $this->validationRules = $validation;
        $this->viewPath = 'onboarding.steps.pipeline_config';
    }

    /**
     * Execute the pipeline configuration step.
     *
     * This method creates a new sales pipeline with the specified stages.
     * If no stages are provided, default stages from config are used.
     *
     * @param array $data The validated step data
     * @param mixed $user The user completing the step
     * @return bool True if execution was successful
     * @throws \Exception
     */
    public function execute(array $data, $user): bool
    {
        try {
            DB::beginTransaction();

            // Check if a default pipeline already exists
            $existingPipeline = $this->getExistingPipeline();

            if ($existingPipeline) {
                // Update existing pipeline
                $pipeline = $this->updatePipeline($existingPipeline, $data);
            } else {
                // Create new pipeline
                $pipeline = $this->createPipeline($data);
            }

            // Store completion metadata
            $this->saveConfigValue('onboarding.pipeline_config.pipeline_id', $pipeline->id);
            $this->saveConfigValue('onboarding.pipeline_config.completed_at', now()->toDateTimeString());
            $this->saveConfigValue('onboarding.pipeline_config.completed_by', $this->getUserId($user));

            DB::commit();

            Log::info('Pipeline configuration step completed successfully', [
                'step_id' => $this->getStepId(),
                'user_id' => $this->getUserId($user),
                'pipeline_id' => $pipeline->id,
                'pipeline_name' => $pipeline->name,
                'stages_count' => count($data['stages'] ?? $this->getDefaultStages()),
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError('Failed to execute pipeline configuration step', $e, $user);

            throw $e;
        }
    }

    /**
     * Create a new pipeline with stages.
     *
     * @param array $data
     * @return \Webkul\Lead\Contracts\Pipeline
     */
    protected function createPipeline(array $data)
    {
        $pipelineName = $data['pipeline_name'] ?? config('onboarding.steps.pipeline_config.fields.pipeline_name.default', 'Default Sales Pipeline');
        $stages = $data['stages'] ?? $this->getDefaultStages();

        // Prepare pipeline data
        $pipelineData = [
            'name' => $pipelineName,
            'is_default' => 1,
            'rotten_days' => 30, // Default rotten days
            'stages' => $this->prepareStagesData($stages),
        ];

        return $this->pipelineRepository->create($pipelineData);
    }

    /**
     * Update an existing pipeline with new stages.
     *
     * @param \Webkul\Lead\Contracts\Pipeline $pipeline
     * @param array $data
     * @return \Webkul\Lead\Contracts\Pipeline
     */
    protected function updatePipeline($pipeline, array $data)
    {
        $pipelineName = $data['pipeline_name'] ?? $pipeline->name;
        $stages = $data['stages'] ?? $this->getDefaultStages();

        // Prepare update data
        $updateData = [
            'name' => $pipelineName,
            'is_default' => 1,
            'stages' => $this->prepareStagesDataForUpdate($pipeline, $stages),
        ];

        return $this->pipelineRepository->update($updateData, $pipeline->id);
    }

    /**
     * Prepare stages data for pipeline creation.
     *
     * @param array $stages
     * @return array
     */
    protected function prepareStagesData(array $stages): array
    {
        $preparedStages = [];

        foreach ($stages as $index => $stage) {
            $preparedStages[] = [
                'code' => $this->generateStageCode($stage['name']),
                'name' => $stage['name'],
                'probability' => $stage['probability'] ?? 50,
                'sort_order' => $stage['order'] ?? ($index + 1),
            ];
        }

        return $preparedStages;
    }

    /**
     * Prepare stages data for pipeline update.
     *
     * @param \Webkul\Lead\Contracts\Pipeline $pipeline
     * @param array $stages
     * @return array
     */
    protected function prepareStagesDataForUpdate($pipeline, array $stages): array
    {
        $preparedStages = [];
        $existingStages = $pipeline->stages->keyBy('code');

        foreach ($stages as $index => $stage) {
            $stageCode = $this->generateStageCode($stage['name']);
            $existingStage = $existingStages->get($stageCode);

            if ($existingStage) {
                // Update existing stage
                $preparedStages[$existingStage->id] = [
                    'code' => $stageCode,
                    'name' => $stage['name'],
                    'probability' => $stage['probability'] ?? $existingStage->probability,
                    'sort_order' => $stage['order'] ?? ($index + 1),
                ];
            } else {
                // Create new stage
                $preparedStages['stage_' . $index] = [
                    'code' => $stageCode,
                    'name' => $stage['name'],
                    'probability' => $stage['probability'] ?? 50,
                    'sort_order' => $stage['order'] ?? ($index + 1),
                ];
            }
        }

        return $preparedStages;
    }

    /**
     * Generate a stage code from the stage name.
     *
     * @param string $name
     * @return string
     */
    protected function generateStageCode(string $name): string
    {
        return Str::slug($name, '_');
    }

    /**
     * Get default stages from configuration.
     *
     * @return array
     */
    protected function getDefaultStages(): array
    {
        return config('onboarding.steps.pipeline_config.default_stages', [
            ['name' => 'New', 'probability' => 10, 'order' => 1],
            ['name' => 'Qualified', 'probability' => 25, 'order' => 2],
            ['name' => 'Proposal', 'probability' => 50, 'order' => 3],
            ['name' => 'Negotiation', 'probability' => 75, 'order' => 4],
            ['name' => 'Won', 'probability' => 100, 'order' => 5],
            ['name' => 'Lost', 'probability' => 0, 'order' => 6],
        ]);
    }

    /**
     * Get existing pipeline (default or first available).
     *
     * @return \Webkul\Lead\Contracts\Pipeline|null
     */
    protected function getExistingPipeline()
    {
        // First check if we have a pipeline from a previous onboarding run
        $pipelineIdConfig = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.pipeline_config.pipeline_id'
        ]);

        if ($pipelineIdConfig && !empty($pipelineIdConfig->value)) {
            try {
                $pipeline = $this->pipelineRepository->find($pipelineIdConfig->value);
                if ($pipeline) {
                    return $pipeline;
                }
            } catch (\Exception $e) {
                Log::debug('Previously configured pipeline not found', [
                    'pipeline_id' => $pipelineIdConfig->value,
                ]);
            }
        }

        // Otherwise check for default pipeline
        return $this->pipelineRepository->getDefaultPipeline();
    }

    /**
     * Save a configuration value to the database.
     *
     * This method updates the config if it exists, or creates a new one.
     *
     * @param string $code The config code/key
     * @param mixed $value The config value
     * @return void
     */
    protected function saveConfigValue(string $code, mixed $value): void
    {
        $existingConfig = $this->coreConfigRepository->findOneWhere(['code' => $code]);

        if ($existingConfig) {
            $this->coreConfigRepository->update([
                'code' => $code,
                'value' => $value,
            ], $existingConfig->id);
        } else {
            $this->coreConfigRepository->create([
                'code' => $code,
                'value' => $value,
            ]);
        }
    }

    /**
     * Get default data for this step.
     *
     * Returns pre-filled data if the pipeline configuration has been completed before.
     *
     * @param mixed $user The current user
     * @return array
     */
    public function getDefaultData($user): array
    {
        $defaultData = [];

        // Get the previously configured pipeline
        $pipeline = $this->getExistingPipeline();

        if ($pipeline) {
            $defaultData['pipeline_name'] = $pipeline->name;

            // Load existing stages
            $stages = [];
            foreach ($pipeline->stages as $stage) {
                $stages[] = [
                    'name' => $stage->name,
                    'probability' => $stage->probability,
                    'order' => $stage->sort_order,
                ];
            }

            $defaultData['stages'] = $stages;
        } else {
            // Return default configuration
            $defaultData['pipeline_name'] = config('onboarding.steps.pipeline_config.fields.pipeline_name.default', 'Default Sales Pipeline');
            $defaultData['stages'] = $this->getDefaultStages();
        }

        return $defaultData;
    }

    /**
     * Check if this step has been previously completed.
     *
     * Returns true if a pipeline has been configured during onboarding.
     *
     * @param mixed $user The current user
     * @return bool
     */
    public function hasBeenCompleted($user): bool
    {
        $pipelineIdConfig = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.pipeline_config.pipeline_id'
        ]);

        return $pipelineIdConfig && !empty($pipelineIdConfig->value);
    }

    /**
     * Handle step completion.
     *
     * Logs the completion and can be extended for additional actions.
     *
     * @param array $data The step data
     * @param mixed $user The user completing the step
     * @return void
     */
    public function onComplete(array $data, $user): void
    {
        parent::onComplete($data, $user);

        Log::info('Pipeline configuration step completed', [
            'pipeline_name' => $data['pipeline_name'] ?? 'Default Sales Pipeline',
            'stages_count' => count($data['stages'] ?? $this->getDefaultStages()),
        ]);
    }

    /**
     * Handle step skip.
     *
     * When skipped, a default pipeline will be created automatically
     * by the system if one doesn't exist.
     *
     * @param mixed $user The user skipping the step
     * @return void
     */
    public function onSkip($user): void
    {
        parent::onSkip($user);

        Log::info('Pipeline configuration step skipped', [
            'user_id' => $this->getUserId($user),
        ]);

        // Create default pipeline if none exists
        try {
            $existingPipeline = $this->getExistingPipeline();

            if (!$existingPipeline) {
                DB::beginTransaction();

                $pipeline = $this->createPipeline([]);

                // Store metadata
                $this->saveConfigValue('onboarding.pipeline_config.pipeline_id', $pipeline->id);
                $this->saveConfigValue('onboarding.pipeline_config.skipped_at', now()->toDateTimeString());
                $this->saveConfigValue('onboarding.pipeline_config.skipped_by', $this->getUserId($user));

                DB::commit();

                Log::info('Default pipeline created after skip', [
                    'pipeline_id' => $pipeline->id,
                    'user_id' => $this->getUserId($user),
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create default pipeline on skip', [
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($user),
            ]);
        }
    }
}
