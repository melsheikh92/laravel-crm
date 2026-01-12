<?php

namespace App\Services\Onboarding\Steps;

use App\Services\Onboarding\AbstractWizardStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Webkul\Core\Repositories\CoreConfigRepository;

/**
 * Email Integration Step
 *
 * This step allows the administrator to configure email integration settings
 * for the CRM, including SMTP/IMAP configuration. It supports multiple email
 * providers and includes connection testing before saving the configuration.
 *
 * @package App\Services\Onboarding\Steps
 */
class EmailIntegrationStep extends AbstractWizardStep
{
    /**
     * Core config repository instance.
     *
     * @var CoreConfigRepository
     */
    protected CoreConfigRepository $coreConfigRepository;

    /**
     * Create a new EmailIntegrationStep instance.
     *
     * @param CoreConfigRepository $coreConfigRepository
     */
    public function __construct(CoreConfigRepository $coreConfigRepository)
    {
        $this->coreConfigRepository = $coreConfigRepository;

        // Load configuration from onboarding config
        $config = config('onboarding.steps.email_integration', []);
        $validation = config('onboarding.validation.email_integration', []);

        // Set step properties from config
        $this->stepId = 'email_integration';
        $this->title = $config['title'] ?? 'Email Integration';
        $this->description = $config['description'] ?? 'Connect your email for seamless communication';
        $this->icon = $config['icon'] ?? 'envelope';
        $this->estimatedMinutes = $config['estimated_minutes'] ?? 5;
        $this->skippable = $config['skippable'] ?? true;
        $this->helpText = $config['help_text'] ?? '';
        $this->helpTips = $config['help_tips'] ?? [];
        $this->validationRules = $validation;
        $this->viewPath = 'onboarding.steps.email_integration';
    }

    /**
     * Execute the email integration step.
     *
     * This method stores the email configuration in the core_config table.
     * If test_connection is enabled, it verifies the connection before saving.
     *
     * @param array $data The validated step data
     * @param mixed $user The user completing the step
     * @return bool True if execution was successful
     * @throws \Exception
     */
    public function execute(array $data, $user): bool
    {
        try {
            // Test connection if requested
            if (($data['test_connection'] ?? true) && $this->hasSmtpConfig($data)) {
                $this->testEmailConnection($data);
            }

            DB::beginTransaction();

            // Define the mapping of form fields to config keys
            $configMapping = [
                'email_provider' => 'onboarding.email.provider',
                'smtp_host' => 'onboarding.email.smtp_host',
                'smtp_port' => 'onboarding.email.smtp_port',
                'smtp_username' => 'onboarding.email.smtp_username',
                'smtp_password' => 'onboarding.email.smtp_password',
                'smtp_encryption' => 'onboarding.email.smtp_encryption',
            ];

            // Store each field in core_config
            foreach ($configMapping as $field => $configKey) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    $this->saveConfigValue($configKey, $data[$field]);
                }
            }

            // Store completion timestamp
            $this->saveConfigValue('onboarding.email.completed_at', now()->toDateTimeString());
            $this->saveConfigValue('onboarding.email.completed_by', $this->getUserId($user));

            DB::commit();

            Log::info('Email integration step completed successfully', [
                'step_id' => $this->getStepId(),
                'user_id' => $this->getUserId($user),
                'email_provider' => $data['email_provider'] ?? 'smtp',
                'smtp_host' => $data['smtp_host'] ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError('Failed to execute email integration step', $e, $user);

            throw $e;
        }
    }

    /**
     * Test the email connection with the provided settings.
     *
     * This method temporarily configures Laravel's mail system with the
     * provided settings and attempts to send a test email.
     *
     * @param array $data The email configuration data
     * @return bool True if connection test succeeds
     * @throws \Exception If connection test fails
     */
    protected function testEmailConnection(array $data): bool
    {
        try {
            // Store original mail configuration
            $originalConfig = [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password' => config('mail.mailers.smtp.password'),
                'encryption' => config('mail.mailers.smtp.encryption'),
            ];

            // Temporarily set new configuration
            config([
                'mail.mailers.smtp.host' => $data['smtp_host'] ?? '',
                'mail.mailers.smtp.port' => $data['smtp_port'] ?? 587,
                'mail.mailers.smtp.username' => $data['smtp_username'] ?? '',
                'mail.mailers.smtp.password' => $data['smtp_password'] ?? '',
                'mail.mailers.smtp.encryption' => $data['smtp_encryption'] ?? 'tls',
            ]);

            // Attempt to send a test email (dry run)
            $testEmail = $data['smtp_username'] ?? 'test@example.com';

            try {
                // Use a simple connection test instead of actually sending email
                $transport = Mail::mailer('smtp')->getSwiftMailer()->getTransport();

                if (method_exists($transport, 'start')) {
                    $transport->start();
                }

                Log::info('Email connection test successful', [
                    'smtp_host' => $data['smtp_host'] ?? null,
                    'smtp_port' => $data['smtp_port'] ?? null,
                ]);

                return true;
            } catch (\Exception $e) {
                Log::error('Email connection test failed', [
                    'smtp_host' => $data['smtp_host'] ?? null,
                    'error' => $e->getMessage(),
                ]);

                throw new \Exception('Email connection test failed: ' . $e->getMessage());
            } finally {
                // Restore original configuration
                config([
                    'mail.mailers.smtp.host' => $originalConfig['host'],
                    'mail.mailers.smtp.port' => $originalConfig['port'],
                    'mail.mailers.smtp.username' => $originalConfig['username'],
                    'mail.mailers.smtp.password' => $originalConfig['password'],
                    'mail.mailers.smtp.encryption' => $originalConfig['encryption'],
                ]);

                // Force reconnection with original config
                Mail::purge('smtp');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Check if the data contains SMTP configuration.
     *
     * @param array $data
     * @return bool
     */
    protected function hasSmtpConfig(array $data): bool
    {
        return !empty($data['smtp_host']) && !empty($data['smtp_port']);
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
     * Returns pre-filled data if the email integration has been completed before.
     *
     * @param mixed $user The current user
     * @return array
     */
    public function getDefaultData($user): array
    {
        $defaultData = [];

        // Define the mapping of config keys to form fields
        $configMapping = [
            'onboarding.email.provider' => 'email_provider',
            'onboarding.email.smtp_host' => 'smtp_host',
            'onboarding.email.smtp_port' => 'smtp_port',
            'onboarding.email.smtp_username' => 'smtp_username',
            'onboarding.email.smtp_encryption' => 'smtp_encryption',
            // Note: We intentionally don't retrieve the password for security
        ];

        // Retrieve saved values from config
        foreach ($configMapping as $configKey => $field) {
            $config = $this->coreConfigRepository->findOneWhere(['code' => $configKey]);

            if ($config && !empty($config->value)) {
                $defaultData[$field] = $config->value;
            }
        }

        // Default test_connection to false when retrieving existing data
        $defaultData['test_connection'] = false;

        return $defaultData;
    }

    /**
     * Check if this step has been previously completed.
     *
     * Returns true if email configuration has been saved.
     *
     * @param mixed $user The current user
     * @return bool
     */
    public function hasBeenCompleted($user): bool
    {
        $completedAt = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.email.completed_at'
        ]);

        return $completedAt && !empty($completedAt->value);
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

        Log::info('Email integration step completed', [
            'email_provider' => $data['email_provider'] ?? 'N/A',
            'smtp_host' => $data['smtp_host'] ?? 'N/A',
            'connection_tested' => $data['test_connection'] ?? true,
        ]);
    }

    /**
     * Handle step skip.
     *
     * This step can be skipped if the administrator wants to configure
     * email integration later.
     *
     * @param mixed $user The user skipping the step
     * @return void
     */
    public function onSkip($user): void
    {
        parent::onSkip($user);

        Log::info('Email integration step skipped', [
            'user_id' => $this->getUserId($user),
        ]);
    }
}
