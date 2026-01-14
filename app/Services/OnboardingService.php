<?php

namespace App\Services;

use App\Models\OnboardingProgress;
use App\Models\User; // Assuming User model is here
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OnboardingComplete; // Assumption
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    /**
     * The defined onboarding steps in order.
     *
     * @var array
     */
    protected $steps;

    public function __construct()
    {
        // Load steps from config
        $this->steps = array_keys(config('onboarding.steps', []));
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

        // Use transaction to avoid race conditions and handle FK checks if needed
        return DB::transaction(function () use ($userId) {
            // Workaround for FK issues if any
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $progress = OnboardingProgress::firstOrCreate(
                ['user_id' => $userId],
                [
                    'started_at' => now(),
                    'completed_steps' => [],
                    'skipped_steps' => [],
                    'is_completed' => false,
                    'current_step' => $this->steps[0] ?? 'company_setup', // Initialize current_step!
                ]
            );

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Ensure current_step is set if it was null (migration/legacy issue)
            if (!$progress->current_step) {
                $progress->current_step = $this->steps[0] ?? 'company_setup';
                $progress->save();
            }

            return $progress;
        });
    }

    /**
     * Get progress summary for a user.
     *
     * @param int|User $user
     * @return array
     */
    public function getProgressSummary($user): array
    {
        $progress = $this->getOrCreateProgress($user);
        $totalSteps = count($this->steps);

        return [
            'completed_count' => $progress->getCompletedStepsCount(),
            'skipped_count' => $progress->getSkippedStepsCount(),
            'total_steps' => $totalSteps,
            'percentage' => $progress->getProgressPercentage($totalSteps),
            'current_step' => $progress->current_step,
            'is_completed' => $progress->is_completed,
            'completed_steps' => $progress->completed_steps ?? [],
        ];
    }

    /**
     * Get default data for a step (e.g. for pre-filling forms).
     */
    public function getDefaultData($user, $step)
    {
        // implementation logic to fetch data from user model or other sources
        // based on the step. For now returning empty array or user data.

        $data = [];

        if ($step === 'company_setup') {
            // Example: if user has company relation
            // $data['company_name'] = $user->company->name ?? '';
        }

        return $data;
    }

    /**
     * Complete a step for a user.
     *
     * @param int|User $user
     * @param string $step
     * @param array $data
     * @return OnboardingProgress
     */
    public function completeStep($user, string $step, array $data = []): OnboardingProgress
    {
        $progress = $this->getOrCreateProgress($user);

        // Mark step as completed
        $progress->completeStep($step);

        Log::info('Step completed', [
            'user_id' => $progress->user_id,
            'step' => $step,
        ]);

        // Check if this was the last step
        if ($this->isLastStep($step)) {
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
     * @param int|User $user
     * @param string $step
     * @return OnboardingProgress
     */
    public function skipStep($user, string $step): OnboardingProgress
    {
        $progress = $this->getOrCreateProgress($user);

        // Mark step as skipped
        $progress->skipStep($step);

        Log::info('Step skipped', [
            'user_id' => $progress->user_id,
            'step' => $step,
        ]);

        // Check if this was the last step
        if ($this->isLastStep($step)) {
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
     * Navigate to next step manually.
     */
    public function navigateToNextStep($user)
    {
        $progress = $this->getOrCreateProgress($user);
        $nextStep = $this->getNextStep($progress->current_step);
        if ($nextStep) {
            $progress->setCurrentStep($nextStep);
        }
        return $progress;
    }

    /**
     * Navigate to previous step manually.
     */
    public function navigateToPreviousStep($user)
    {
        $progress = $this->getOrCreateProgress($user);
        $prevStep = $this->getPreviousStep($progress->current_step);
        if ($prevStep) {
            $progress->setCurrentStep($prevStep);
        }
        return $progress;
    }

    /**
     * Complete the onboarding process for a user.
     *
     * @param int|User $user
     * @return OnboardingProgress
     */
    public function completeOnboarding($user): OnboardingProgress
    {
        $progress = $this->getOrCreateProgress($user);
        $progress->markAsComplete();

        Log::info('Onboarding completed', [
            'user_id' => $progress->user_id,
            'completed_steps' => $progress->getCompletedStepsCount(),
        ]);

        // Optional: Send welcome email
        // Mail::send(new OnboardingComplete($progress));

        return $progress;
    }

    public function resetOnboarding($user): OnboardingProgress
    {
        $progress = $this->getOrCreateProgress($user);
        $progress->reset();
        $progress->current_step = $this->steps[0];
        $progress->save();
        return $progress;
    }

    public function isLastStep(string $step): bool
    {
        if (!$this->isValidStep($step))
            return false;
        return array_search($step, $this->steps) === count($this->steps) - 1;
    }

    public function getNextStep(string $currentStep): ?string
    {
        if (!$this->isValidStep($currentStep))
            return null;
        $currentIndex = array_search($currentStep, $this->steps);
        $nextIndex = $currentIndex + 1;
        return $nextIndex < count($this->steps) ? $this->steps[$nextIndex] : null;
    }

    public function getPreviousStep(string $currentStep): ?string
    {
        if (!$this->isValidStep($currentStep))
            return null;
        $currentIndex = array_search($currentStep, $this->steps);
        $prevIndex = $currentIndex - 1;
        return $prevIndex >= 0 ? $this->steps[$prevIndex] : null;
    }

    protected function isValidStep($step): bool
    {
        return in_array($step, $this->steps);
    }
}
