<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OnboardingService;
use App\Services\Onboarding\Steps\CompanySetupStep;
use App\Services\Onboarding\Steps\EmailIntegrationStep;
use App\Services\Onboarding\Steps\PipelineConfigurationStep;
use App\Services\Onboarding\Steps\SampleDataImportStep;
use App\Services\Onboarding\Steps\UserCreationStep;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for onboarding wizard operations.
 *
 * Provides JSON endpoints for AJAX step updates, progress fetching, and validation.
 * All endpoints require authentication with 'auth:user' middleware.
 */
class OnboardingApiController extends Controller
{
    /**
     * @var OnboardingService
     */
    protected $onboardingService;

    /**
     * Step implementation instances
     *
     * @var array
     */
    protected array $stepImplementations;

    /**
     * Create a new controller instance.
     */
    public function __construct(OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;

        // Initialize step implementations
        $this->stepImplementations = [
            'company_setup' => app(CompanySetupStep::class),
            'user_creation' => app(UserCreationStep::class),
            'pipeline_config' => app(PipelineConfigurationStep::class),
            'email_integration' => app(EmailIntegrationStep::class),
            'sample_data' => app(SampleDataImportStep::class),
        ];
    }

    /**
     * Get current user's onboarding progress.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $summary = $this->onboardingService->getProgressSummary($user);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve onboarding progress', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve progress: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get completion statistics (for admin dashboard).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // TODO: Add admin/manager authorization check
        // if (!$user->hasRole('admin')) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        // }

        try {
            $stats = $this->onboardingService->getCompletionStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve onboarding statistics', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate step data via AJAX.
     *
     * @param Request $request
     * @param string $step
     * @return JsonResponse
     */
    public function validateStep(Request $request, string $step): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Validate step
        if (!$this->onboardingService->isValidStep($step)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid step',
            ], 400);
        }

        try {
            // Get validation rules from config
            $validationRules = config("onboarding.validation.{$step}", []);

            if (empty($validationRules)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No validation rules defined for this step',
                ]);
            }

            // Validate request data
            $request->validate($validationRules);

            return response()->json([
                'success' => true,
                'message' => 'Validation passed',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Step validation error', [
                'user_id' => $user->id,
                'step' => $step,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update step (complete and save data).
     *
     * @param Request $request
     * @param string $step
     * @return JsonResponse
     */
    public function updateStep(Request $request, string $step): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Validate step
        if (!$this->onboardingService->isValidStep($step)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid step',
            ], 400);
        }

        // Get validation rules from config
        $validationRules = config("onboarding.validation.{$step}", []);

        try {
            // Validate request data
            if (!empty($validationRules)) {
                $request->validate($validationRules);
            }

            // Get step implementation
            $stepImplementation = $this->stepImplementations[$step] ?? null;

            if (!$stepImplementation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Step implementation not found',
                ], 500);
            }

            // Execute step to save data
            $stepImplementation->execute($request->all(), $user);

            // Mark step as completed
            $progress = $this->onboardingService->completeStep($user, $step, $request->all());

            Log::info('Onboarding step completed via API', [
                'user_id' => $user->id,
                'step' => $step,
            ]);

            // Get next step if available
            $nextStep = $progress->is_completed ? null : $progress->current_step;

            return response()->json([
                'success' => true,
                'message' => 'Step completed successfully',
                'data' => [
                    'progress' => $this->onboardingService->getProgressSummary($user),
                    'next_step' => $nextStep,
                    'is_completed' => $progress->is_completed,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to process onboarding step via API', [
                'user_id' => $user->id,
                'step' => $step,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process step: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Skip a specific step.
     *
     * @param Request $request
     * @param string $step
     * @return JsonResponse
     */
    public function skipStep(Request $request, string $step): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Check if skipping is allowed globally
        if (!config('onboarding.allow_skip', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Skipping steps is not allowed',
            ], 403);
        }

        // Check if this specific step can be skipped
        $stepConfig = config("onboarding.steps.{$step}", []);
        if (isset($stepConfig['skippable']) && $stepConfig['skippable'] === false) {
            return response()->json([
                'success' => false,
                'message' => 'This step cannot be skipped',
            ], 403);
        }

        try {
            $progress = $this->onboardingService->skipStep($user, $step);

            Log::info('Onboarding step skipped via API', [
                'user_id' => $user->id,
                'step' => $step,
            ]);

            // Get next step if available
            $nextStep = $progress->is_completed ? null : $progress->current_step;

            return response()->json([
                'success' => true,
                'message' => 'Step skipped successfully',
                'data' => [
                    'progress' => $this->onboardingService->getProgressSummary($user),
                    'next_step' => $nextStep,
                    'is_completed' => $progress->is_completed,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to skip onboarding step via API', [
                'user_id' => $user->id,
                'step' => $step,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to skip step: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Navigate to the next step.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function next(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $progress = $this->onboardingService->navigateToNextStep($user);

            return response()->json([
                'success' => true,
                'message' => 'Navigated to next step',
                'data' => [
                    'current_step' => $progress->current_step,
                    'progress' => $this->onboardingService->getProgressSummary($user),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to navigate to next step via API', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Navigate to the previous step.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function previous(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $progress = $this->onboardingService->navigateToPreviousStep($user);

            return response()->json([
                'success' => true,
                'message' => 'Navigated to previous step',
                'data' => [
                    'current_step' => $progress->current_step,
                    'progress' => $this->onboardingService->getProgressSummary($user),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to navigate to previous step via API', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete the entire onboarding wizard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $progress = $this->onboardingService->completeOnboarding($user);

            Log::info('Onboarding wizard completed via API', [
                'user_id' => $user->id,
                'completed_steps' => $progress->getCompletedStepsCount(),
                'skipped_steps' => $progress->getSkippedStepsCount(),
            ]);

            // TODO: Send welcome email if configured
            // if (config('onboarding.completion.send_welcome_email', true)) {
            //     // Send welcome email
            // }

            return response()->json([
                'success' => true,
                'message' => config('onboarding.completion.completion_message', 'Onboarding completed successfully'),
                'data' => [
                    'redirect_to' => config('onboarding.completion.redirect_to', '/dashboard'),
                    'progress' => $this->onboardingService->getProgressSummary($user),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to complete onboarding via API', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete onboarding: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restart the onboarding wizard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function restart(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Check if restart is allowed
        if (!config('onboarding.allow_restart', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Restarting onboarding is not allowed',
            ], 403);
        }

        try {
            $progress = $this->onboardingService->resetOnboarding($user);

            Log::info('Onboarding wizard restarted via API', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Onboarding wizard has been restarted',
                'data' => [
                    'current_step' => $progress->current_step,
                    'progress' => $this->onboardingService->getProgressSummary($user),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to restart onboarding via API', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restart onboarding: ' . $e->getMessage(),
            ], 500);
        }
    }
}
