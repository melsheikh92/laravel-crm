<?php

namespace App\Services\Onboarding\Steps;

use App\Services\Onboarding\AbstractWizardStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Core\Repositories\CoreConfigRepository;

/**
 * Company Setup Step
 *
 * This step collects and stores the company's basic information including
 * company name, address, industry, size, phone, and website. This information
 * is used throughout the CRM for personalization and customer-facing documents.
 *
 * @package App\Services\Onboarding\Steps
 */
class CompanySetupStep extends AbstractWizardStep
{
    /**
     * Core config repository instance.
     *
     * @var CoreConfigRepository
     */
    protected CoreConfigRepository $coreConfigRepository;

    /**
     * Create a new CompanySetupStep instance.
     *
     * @param CoreConfigRepository $coreConfigRepository
     */
    public function __construct(CoreConfigRepository $coreConfigRepository)
    {
        $this->coreConfigRepository = $coreConfigRepository;

        // Load configuration from onboarding config
        $config = config('onboarding.steps.company_setup', []);
        $validation = config('onboarding.validation.company_setup', []);

        // Set step properties from config
        $this->stepId = 'company_setup';
        $this->title = $config['title'] ?? 'Company Setup';
        $this->description = $config['description'] ?? 'Set up your company profile and basic information';
        $this->icon = $config['icon'] ?? 'building';
        $this->estimatedMinutes = $config['estimated_minutes'] ?? 3;
        $this->skippable = $config['skippable'] ?? false;
        $this->helpText = $config['help_text'] ?? '';
        $this->helpTips = $config['help_tips'] ?? [];
        $this->validationRules = $validation;
        $this->viewPath = 'onboarding.steps.company_setup';
    }

    /**
     * Execute the company setup step.
     *
     * This method stores the company information in the core_config table
     * with keys prefixed by 'onboarding.company.' for easy retrieval.
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

            // Define the mapping of form fields to config keys
            $configMapping = [
                'company_name' => 'onboarding.company.name',
                'industry' => 'onboarding.company.industry',
                'company_size' => 'onboarding.company.size',
                'address' => 'onboarding.company.address',
                'phone' => 'onboarding.company.phone',
                'website' => 'onboarding.company.website',
            ];

            // Store each field in core_config
            foreach ($configMapping as $field => $configKey) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    $this->saveConfigValue($configKey, $data[$field]);
                }
            }

            // Also store the completion timestamp
            $this->saveConfigValue('onboarding.company.completed_at', now()->toDateTimeString());
            $this->saveConfigValue('onboarding.company.completed_by', $this->getUserId($user));

            DB::commit();

            Log::info('Company setup completed successfully', [
                'step_id' => $this->getStepId(),
                'user_id' => $this->getUserId($user),
                'company_name' => $data['company_name'] ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError('Failed to execute company setup step', $e, $user);

            throw $e;
        }
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
     * Returns pre-filled data if the company setup has been completed before.
     *
     * @param mixed $user The current user
     * @return array
     */
    public function getDefaultData($user): array
    {
        $defaultData = [];

        // Define the mapping of config keys to form fields
        $configMapping = [
            'onboarding.company.name' => 'company_name',
            'onboarding.company.industry' => 'industry',
            'onboarding.company.size' => 'company_size',
            'onboarding.company.address' => 'address',
            'onboarding.company.phone' => 'phone',
            'onboarding.company.website' => 'website',
        ];

        // Retrieve saved values from config
        foreach ($configMapping as $configKey => $field) {
            $config = $this->coreConfigRepository->findOneWhere(['code' => $configKey]);

            if ($config && !empty($config->value)) {
                $defaultData[$field] = $config->value;
            }
        }

        return $defaultData;
    }

    /**
     * Check if this step has been previously completed.
     *
     * Returns true if company name has been saved, as it's a required field.
     *
     * @param mixed $user The current user
     * @return bool
     */
    public function hasBeenCompleted($user): bool
    {
        $companyName = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.company.name'
        ]);

        return $companyName && !empty($companyName->value);
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

        Log::info('Company setup step completed', [
            'company_name' => $data['company_name'] ?? 'N/A',
            'industry' => $data['industry'] ?? 'N/A',
            'company_size' => $data['company_size'] ?? 'N/A',
        ]);
    }

    /**
     * Handle step skip.
     *
     * Note: This step is typically not skippable, but the method
     * is provided for completeness.
     *
     * @param mixed $user The user skipping the step
     * @return void
     */
    public function onSkip($user): void
    {
        parent::onSkip($user);

        Log::warning('Company setup step skipped', [
            'user_id' => $this->getUserId($user),
        ]);
    }
}
