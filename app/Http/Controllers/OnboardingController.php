<?php

namespace App\Http\Controllers;

use App\Services\OnboardingService;
use App\Services\Onboarding\Steps\CompanySetupStep;
use App\Services\Onboarding\Steps\EmailIntegrationStep;
use App\Services\Onboarding\Steps\PipelineConfigurationStep;
use App\Services\Onboarding\Steps\SampleDataImportStep;
use App\Services\Onboarding\Steps\UserCreationStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OnboardingController extends Controller
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

        // Add auth middleware to protect onboarding endpoints
        $this->middleware('auth:user');

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
     * Display the onboarding wizard.
     * Redirects to the current step or starts onboarding if not started.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = auth()->guard('user')->user();

        // Check if onboarding is enabled
        if (!config('onboarding.enabled', true)) {
            return redirect('/dashboard')->with('info', 'Onboarding wizard is currently disabled.');
        }

        // Get or create progress
        $progress = $this->onboardingService->getOrCreateProgress($user);

        // If onboarding is already completed, show completion page or redirect
        if ($progress->is_completed) {
            return view('onboarding.complete', [
                'progress' => $progress,
                'summary' => $this->onboardingService->getProgressSummary($user),
            ]);
        }

        // Start onboarding if not started
        if (!$progress->isInProgress()) {
            $progress = $this->onboardingService->startOnboarding($user);
        }

        // Redirect to current step
        $currentStep = $progress->current_step ?? $this->onboardingService->getSteps()[0];

        // Ensure step is saved if it was missing
        if (!$progress->current_step) {
            $progress->current_step = $currentStep;
            $progress->save();
        }

        return redirect()->route('onboarding.step', ['step' => $currentStep]);
    }

    /**
     * Display a specific onboarding step.
     *
     * @param Request $request
     * @param string $step
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, string $step)
    {
        $user = auth()->guard('user')->user();

        // Validate step
        if (!$this->onboardingService->isValidStep($step)) {
            return redirect()->route('onboarding.index')->with('error', 'Invalid onboarding step.');
        }

        // Get or create progress
        $progress = $this->onboardingService->getOrCreateProgress($user);

        // If onboarding is completed, redirect to completion page
        if ($progress->is_completed) {
            return redirect()->route('onboarding.index');
        }

        // Update current step if different
        if ($progress->current_step !== $step) {
            $progress = $this->onboardingService->navigateToStep($user, $step);
        }

        // Get step details
        $stepDetails = $this->onboardingService->getStepDetails($step);
        $stepConfig = config("onboarding.steps.{$step}", []);

        // Get step implementation
        $stepImplementation = $this->stepImplementations[$step] ?? null;

        // Get default data for the step (pre-fill form if data exists)
        $defaultData = $stepImplementation ? $stepImplementation->getDefaultData($user) : [];

        // Get progress summary
        $progressSummary = $this->onboardingService->getProgressSummary($user);

        return view('onboarding.step', [
            'step' => $step,
            'stepDetails' => $stepDetails,
            'stepConfig' => $stepConfig,
            'progress' => $progress,
            'progressSummary' => $progressSummary,
            'defaultData' => $defaultData,
            'allSteps' => $this->onboardingService->getSteps(),
        ]);
    }

    /**
     * Process step submission (complete or save).
     *
     * @param Request $request
     * @param string $step
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, string $step)
    {
        $user = auth()->guard('user')->user();

        // Validate step
        if (!$this->onboardingService->isValidStep($step)) {
            return redirect()->route('onboarding.index')->with('error', 'Invalid onboarding step.');
        }

        try {
            // Get step implementation
            $stepImplementation = $this->stepImplementations[$step] ?? null;

            if (!$stepImplementation) {
                throw new \Exception("Step implementation not found for: {$step}");
            }

            // Get validation rules from config
            $validationRules = config("onboarding.validation.{$step}", []);

            // Validate request data
            if (!empty($validationRules)) {
                $validated = $request->validate($validationRules);
            } else {
                $validated = $request->all();
            }

            // Execute step (process and save data)
            $stepImplementation->execute($validated, $user);

            // Mark step as completed
            $progress = $this->onboardingService->completeStep($user, $step, $validated);

            Log::info('Onboarding step completed', [
                'user_id' => $user->id,
                'step' => $step,
            ]);

            // Check if onboarding is now completed
            if ($progress->is_completed) {
                return redirect()->route('onboarding.index')->with('success', 'Congratulations! You have completed the onboarding wizard.');
            }

            // Redirect to next step
            return redirect()->route('onboarding.step', ['step' => $progress->current_step])
                ->with('success', 'Step completed successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Onboarding step error', [
                'user_id' => $user->id,
                'step' => $step,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while processing this step: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Navigate to the next step.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function next(Request $request)
    {
        $user = auth()->guard('user')->user();

        try {
            $progress = $this->onboardingService->navigateToNextStep($user);

            return redirect()->route('onboarding.step', ['step' => $progress->current_step]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Navigate to the previous step.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function previous(Request $request)
    {
        $user = auth()->guard('user')->user();

        try {
            $progress = $this->onboardingService->navigateToPreviousStep($user);

            return redirect()->route('onboarding.step', ['step' => $progress->current_step]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Skip a step.
     *
     * @param Request $request
     * @param string $step
     * @return \Illuminate\Http\RedirectResponse
     */
    public function skip(Request $request, string $step)
    {
        $user = auth()->guard('user')->user();

        // Check if skipping is allowed globally
        if (!config('onboarding.allow_skip', true)) {
            return redirect()->back()->with('error', 'Skipping steps is not allowed.');
        }

        // Check if this specific step can be skipped
        $stepConfig = config("onboarding.steps.{$step}", []);
        if (isset($stepConfig['skippable']) && $stepConfig['skippable'] === false) {
            return redirect()->back()->with('error', 'This step cannot be skipped.');
        }

        try {
            $progress = $this->onboardingService->skipStep($user, $step);

            Log::info('Onboarding step skipped', [
                'user_id' => $user->id,
                'step' => $step,
            ]);

            // Check if onboarding is now completed
            if ($progress->is_completed) {
                return redirect()->route('onboarding.index')->with('success', 'Onboarding wizard completed.');
            }

            // Redirect to next step
            return redirect()->route('onboarding.step', ['step' => $progress->current_step])
                ->with('info', 'Step skipped. You can come back to it later.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to skip step: ' . $e->getMessage());
        }
    }

    /**
     * Complete the onboarding wizard.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request)
    {
        $user = auth()->guard('user')->user();

        try {
            $progress = $this->onboardingService->completeOnboarding($user);

            Log::info('Onboarding wizard completed', [
                'user_id' => $user->id,
                'completed_steps' => $progress->getCompletedStepsCount(),
                'skipped_steps' => $progress->getSkippedStepsCount(),
            ]);

            // TODO: Send welcome email if configured
            // if (config('onboarding.completion.send_welcome_email', true)) {
            //     // Send welcome email
            // }

            $redirectTo = config('onboarding.completion.redirect_to', '/dashboard');

            return redirect($redirectTo)->with('success', config('onboarding.completion.completion_message'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to complete onboarding: ' . $e->getMessage());
        }
    }

    /**
     * Restart the onboarding wizard.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restart(Request $request)
    {
        $user = auth()->guard('user')->user();

        // Check if restart is allowed
        if (!config('onboarding.allow_restart', true)) {
            return redirect()->back()->with('error', 'Restarting onboarding is not allowed.');
        }

        try {
            $progress = $this->onboardingService->resetOnboarding($user);

            Log::info('Onboarding wizard restarted', [
                'user_id' => $user->id,
            ]);

            return redirect()->route('onboarding.index')
                ->with('success', 'Onboarding wizard has been restarted. Let\'s begin!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to restart onboarding: ' . $e->getMessage());
        }
    }

    /**
     * Get onboarding progress (for AJAX requests).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function progress(Request $request)
    {
        $user = auth()->guard('user')->user();

        try {
            $summary = $this->onboardingService->getProgressSummary($user);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $user = auth()->guard('user')->user();

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateStep(Request $request, string $step)
    {
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
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
