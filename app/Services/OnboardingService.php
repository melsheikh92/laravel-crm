<?php

namespace App\Services;

use App\Models\OnboardingProgress;
use App\Models\User;
use App\Notifications\OnboardingComplete;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OnboardingService
{
    /**
     * The ordered list of wizard steps.
     *
     * @var array
     */
    protected array $steps = [
        'company_setup',
        'user_creation',
        'pipeline_config',
        'email_integration',
        'sample_data',
    ];

    /**
     * Get all wizard steps in order.
     *
     * @return array
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Get the total number of steps.
     *
     * @return int
     */
    public function getTotalSteps(): int
    {
        return count($this->steps);
    }

    /**
     * Get details for a specific step.
     *
     * @param string $step The step identifier
     * @return array|null Step details or null if step doesn't exist
     */
    public function getStepDetails(string $step): ?array
    {
        if (!$this->isValidStep($step)) {
            return null;
        }

        $stepIndex = array_search($step, $this->steps);

        return [
            'id' => $step,
            'index' => $stepIndex,
            'number' => $stepIndex + 1,
            'total' => $this->getTotalSteps(),
            'is_first' => $stepIndex === 0,
            'is_last' => $stepIndex === count($this->steps) - 1,
        ];
    }

    /**
     * Check if a step identifier is valid.
     *
     * @param string $step The step identifier
     * @return bool
     */
    public function isValidStep(string $step): bool
    {
        return in_array($step, $this->steps);
    }

    /**
     * Get or create onboarding progress for a user.
     *
     * @param int|User $user User ID or User model instance
     * @return OnboardingProgress
     */
    public function getOrCreateProgress($user): OnboardingProgress
    {
        $userId = $user instanceof User ? $user->id : $user;

        // Try to find existing record first
        $progress = OnboardingProgress::where('user_id', $userId)->first();
        if ($progress) {
            return $progress;
        }

        // If not found, create with forced FK check disable to handle potential DB inconsistencies
        $firstStep = $this->steps[0] ?? 'company_setup';

        return \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $firstStep) {
            // Disable foreign key checks
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            try {
                // Use firstOrCreate to be safe against race conditions
                $progress = OnboardingProgress::firstOrCreate(
                    ['user_id' => $userId],
                    [
                        'started_at' => now(),
                        'current_step' => $firstStep,
                        'completed_steps' => [],
                        'skipped_steps' => [],
                        'is_completed' => false,
                    ]
                );
            } finally {
                // Re-enable foreign key checks regardless of success/failure
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            return $progress;
        });
    }

    /**
     * Get onboarding progress for a user.
     *
     * @param int|User $user User ID or User model instance
     * @return OnboardingProgress|null
     */
    public function getProgress($user): ?OnboardingProgress
    {
        $userId = $user instanceof User ? $user->id : $user;

        return OnboardingProgress::getForUser($userId);
    }

    /**
     * Start onboarding for a user.
     *
     * @param int|User $user User ID or User model instance
     * @return OnboardingProgress
     */
    public function startOnboarding($user): OnboardingProgress
    {
        $progress = $this->getOrCreateProgress($user);

        if ($progress->is_completed || $progress->isInProgress()) {
            return $progress;
        }

        $progress->current_step = $this->steps[0];
        $progress->started_at = now();
        $progress->save();

        Log::info('Onboarding started', [
            'user_id' => $progress->user_id,
            'current_step' => $progress->current_step,
        ]);

        return $progress;
    }

    /**
     * Get the next step in the wizard.
     *
     * @param string $currentStep The current step identifier
     * @return string|null The next step or null if on last step
     */
    public function getNextStep(string $currentStep): ?string
    {
        if (!$this->isValidStep($currentStep)) {
            return null;
        }

        $currentIndex = array_search($currentStep, $this->steps);
        $nextIndex = $currentIndex + 1;

        return $nextIndex < count($this->steps) ? $this->steps[$nextIndex] : null;
    }

    /**
     * Get the previous step in the wizard.
     *
     * @param string $currentStep The current step identifier
     * @return string|null The previous step or null if on first step
     */
    public function getPreviousStep(string $currentStep): ?string
    {
        if (!$this->isValidStep($currentStep)) {
            return null;
        }

        $currentIndex = array_search($currentStep, $this->steps);
        $prevIndex = $currentIndex - 1;

        return $prevIndex >= 0 ? $this->steps[$prevIndex] : null;
    }

    /**
     * Navigate to the next step for a user.
     *
     * @param int|User $user User ID or User model instance
     * @return OnboardingProgress
     * @throws \Exception
     */
    public function navigateToNextStep($user): OnboardingProgress
    {
        $progress = $this->getProgress($user);

        if (!$progress) {
            throw new \Exception('Onboarding progress not found. Please start onboarding first.');
        }

        if ($progress->is_completed) {
            throw new \Exception('Onboarding is already completed.');
        }

        $nextStep = $this->getNextStep($progress->current_step);

        if (!$nextStep) {
            throw new \Exception('Already on the last step.');
        }

        $progress->setCurrentStep($nextStep);

        Log::info('Navigated to next step', [
            'user_id' => $progress->user_id,
            'current_step' => $nextStep,
        ]);

        return $progress;
    }

    /**
     * Navigate to the previous step for a user.
     *
     * @param int|User $user User ID or User model instance
     * @return OnboardingProgress
     * @throws \Exception
     */
    public function navigateToPreviousStep($user): OnboardingProgress
    {
        $progress = $this->getProgress($user);

        if (!$progress) {
            throw new \Exception('Onboarding progress not found. Please start onboarding first.');
        }

        $prevStep = $this->getPreviousStep($progress->current_step);

        if (!$prevStep) {
            throw new \Exception('Already on the first step.');
        }

        $progress->setCurrentStep($prevStep);

        Log::info('Navigated to previous step', [
            'user_id' => $progress->user_id,
            'current_step' => $prevStep,
        ]);

        return $progress;
    }

    /**
     * Navigate to a specific step for a user.
     *
     * @param int|User $user User ID or User model instance
     * @param string $step The step identifier
     * @return OnboardingProgress
     * @throws \Exception
     */
    public function navigateToStep($user, string $step): OnboardingProgress
    {
        if (!$this->isValidStep($step)) {
            throw new \Exception("Invalid step: {$step}");
        }

        $progress = $this->getOrCreateProgress($user);
        $progress->setCurrentStep($step);

        Log::info('Navigated to specific step', [
            'user_id' => $progress->user_id,
            'current_step' => $step,
        ]);

        return $progress;
    }

    /**
     * Complete a step for a user.
     *
     * @param int|User $user User ID or User model instance
     * @param string $step The step identifier
     * @param array $data Optional step data for validation
     * @return OnboardingProgress
     * @throws \Exception
     */
    public function completeStep($user, string $step, array $data = []): OnboardingProgress
    {
        if (!$this->isValidStep($step)) {
            throw new \Exception("Invalid step: {$step}");
        }

        $progress = $this->getOrCreateProgress($user);

        // Validate step data if validation method exists
        $this->validateStepData($step, $data);

        // Mark step as completed
        $progress->completeStep($step);

        Log::info('Step completed', [
            'user_id' => $progress->user_id,
            'step' => $step,
        ]);

        // Check if this was the last step and complete onboarding if so
        if ($this->isLastStep($step) && $this->canCompleteOnboarding($progress)) {
            return $this->completeOnboarding($user);
        }

        // Move to next step if available
        $nextStep = $this->getNextStep($step);
        if ($nextStep) {
            $progress->setCurrentStep($nextStep);
        }

        return $progress;
    }

    /**
     * Skip a step for a user.
     *
     * @param int|User $user User ID or User model instance
     * @param string $step The step identifier
     * @return OnboardingProgress
     * @throws \Exception
     */
    public function skipStep($user, string $step): OnboardingProgress
    {
        if (!$this->isValidStep($step)) {
            throw new \Exception("Invalid step: {$step}");
        }

        $progress = $this->getOrCreateProgress($user);

        // Mark step as skipped
        $progress->skipStep($step);

        Log::info('Step skipped', [
            'user_id' => $progress->user_id,
            'step' => $step,
        ]);

        // Check if this was the last step
        if ($this->isLastStep($step)) {
            // Complete onboarding even if last step was skipped
            return $this->completeOnboarding($user);
        }

        // Move to next step if available
        $nextStep = $this->getNextStep($step);
        if ($nextStep) {
            $progress->setCurrentStep($nextStep);
        }

        return $progress;
    }

    /**
     * Complete the onboarding process for a user.
     *
     * @param int|User $user User ID or User model instance
     * @return OnboardingProgress
     */
    public function completeOnboarding($user): OnboardingProgress
    {
        $progress = $this->getOrCreateProgress($user);
        $progress->markAsComplete();

        Log::info('Onboarding completed', [
            'user_id' => $progress->user_id,
            'completed_steps' => $progress->getCompletedStepsCount(),
            'skipped_steps' => $progress->getSkippedStepsCount(),
            'duration_hours' => $progress->getDurationInHours(),
        ]);

        // Send welcome email with next steps and resources
        try {
            Mail::send(new OnboardingComplete($progress));

            Log::info('Onboarding completion email sent', [
                'user_id' => $progress->user_id,
                'email' => $progress->user->email,
            ]);
        } catch (\Exception $e) {
            // Log error but don't block onboarding completion
            Log::warning('Failed to send onboarding completion email', [
                'user_id' => $progress->user_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $progress;
    }

    /**
     * Reset onboarding progress for a user.
     *
     * @param int|User $user User ID or User model instance
     * @return OnboardingProgress
     */
    public function resetOnboarding($user): OnboardingProgress
    {
        $progress = $this->getOrCreateProgress($user);
        $progress->reset();
        $progress->current_step = $this->steps[0];
        $progress->save();

        Log::info('Onboarding reset', [
            'user_id' => $progress->user_id,
        ]);

        return $progress;
    }

    /**
     * Check if a step is the last step.
     *
     * @param string $step The step identifier
     * @return bool
     */
    public function isLastStep(string $step): bool
    {
        if (!$this->isValidStep($step)) {
            return false;
        }

        return array_search($step, $this->steps) === count($this->steps) - 1;
    }

    /**
     * Check if a step is the first step.
     *
     * @param string $step The step identifier
     * @return bool
     */
    public function isFirstStep(string $step): bool
    {
        if (!$this->isValidStep($step)) {
            return false;
        }

        return array_search($step, $this->steps) === 0;
    }

    /**
     * Check if a user can complete onboarding.
     *
     * @param OnboardingProgress $progress
     * @return bool
     */
    public function canCompleteOnboarding(OnboardingProgress $progress): bool
    {
        // User can complete onboarding if all steps are either completed or skipped
        $processedCount = $progress->getProcessedStepsCount();

        return $processedCount >= $this->getTotalSteps();
    }

    /**
     * Get progress summary for a user.
     *
     * @param int|User $user User ID or User model instance
     * @return array
     */
    public function getProgressSummary($user): array
    {
        $progress = $this->getProgress($user);

        if (!$progress) {
            return [
                'started' => false,
                'completed' => false,
                'current_step' => null,
                'completed_steps' => [],
                'skipped_steps' => [],
                'progress_percentage' => 0,
                'total_steps' => $this->getTotalSteps(),
            ];
        }

        return [
            'started' => $progress->started_at !== null,
            'completed' => $progress->is_completed,
            'current_step' => $progress->current_step,
            'completed_steps' => $progress->completed_steps ?? [],
            'skipped_steps' => $progress->skipped_steps ?? [],
            'progress_percentage' => $progress->getProgressPercentage($this->getTotalSteps()),
            'total_steps' => $this->getTotalSteps(),
            'completed_count' => $progress->getCompletedStepsCount(),
            'skipped_count' => $progress->getSkippedStepsCount(),
            'remaining_steps' => $progress->getRemainingSteps($this->steps),
            'duration_hours' => $progress->getDurationInHours(),
            'started_at' => $progress->started_at,
            'completed_at' => $progress->completed_at,
        ];
    }

    /**
     * Check if a user should see the onboarding wizard.
     *
     * @param int|User $user User ID or User model instance
     * @return bool
     */
    public function shouldShowOnboarding($user): bool
    {
        $progress = $this->getProgress($user);

        // Show onboarding if not started or not completed
        if (!$progress) {
            return true;
        }

        return !$progress->is_completed;
    }

    /**
     * Get completion statistics for all users.
     *
     * @return array
     */
    public function getCompletionStatistics(): array
    {
        $stats = OnboardingProgress::getCompletionStats();
        $stats['average_completion_time_hours'] = OnboardingProgress::getAverageCompletionTime();

        return $stats;
    }

    /**
     * Validate step data.
     *
     * @param string $step The step identifier
     * @param array $data The data to validate
     * @throws ValidationException
     */
    protected function validateStepData(string $step, array $data): void
    {
        $rules = $this->getValidationRules($step);

        if (empty($rules)) {
            return;
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Get validation rules for a specific step.
     *
     * @param string $step The step identifier
     * @return array
     */
    protected function getValidationRules(string $step): array
    {
        // Validation rules for each step
        // These can be extended or moved to a config file
        $rules = [
            'company_setup' => [
                'company_name' => 'required|string|max:255',
                'industry' => 'nullable|string|max:100',
                'company_size' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:255',
            ],
            'user_creation' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'role' => 'nullable|string|max:50',
            ],
            'pipeline_config' => [
                'pipeline_name' => 'nullable|string|max:255',
                'stages' => 'nullable|array',
            ],
            'email_integration' => [
                'email_provider' => 'nullable|string|max:50',
                'smtp_host' => 'nullable|string|max:255',
                'smtp_port' => 'nullable|integer',
            ],
            'sample_data' => [
                'import_sample_data' => 'nullable|boolean',
            ],
        ];

        return $rules[$step] ?? [];
    }
}
