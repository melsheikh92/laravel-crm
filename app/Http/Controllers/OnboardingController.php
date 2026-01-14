<?php

namespace App\Http\Controllers;

use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OnboardingController extends Controller
{
    protected $onboardingService;

    public function __construct(OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Show the onboarding wizard.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Check if onboarding is enabled
        if (!config('onboarding.enabled', true)) {
            return redirect('/');
        }

        $user = auth()->guard('user')->user();

        // Get or create progress
        $progress = $this->onboardingService->getOrCreateProgress($user);

        // If onboarding is already completed, show completion page or redirect
        if ($progress->is_completed) {
            return view('onboarding.complete', [
                'progress' => $progress,
                'summary' => $this->onboardingService->getProgressSummary($user),
            ]);
        }

        // If specific step is in progress, redirect there
        if ($progress->current_step) {
            return redirect()->route('onboarding.step', ['step' => $progress->current_step]);
        }

        // Otherwise (first time or reset), go to first step
        $firstStep = config('onboarding.steps.' . array_key_first(config('onboarding.steps')));

        // Fallback if current_step is null (defensive coding)
        if (!$progress->current_step) {
            $progress->current_step = 'company_setup';
            $progress->save();
            return redirect()->route('onboarding.step', ['step' => 'company_setup']);
        }

        return redirect()->route('onboarding.step', ['step' => $progress->current_step]);
    }

    /**
     * Show a specific step.
     *
     * @param string $step
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($step)
    {
        // Add fallback for invalid step
        if (!config("onboarding.steps.{$step}")) {
            return redirect()->route('onboarding.index');
        }

        $user = auth()->guard('user')->user();
        $progress = $this->onboardingService->getOrCreateProgress($user);

        // Allow viewing completed steps or the current step
        // We might want to restrict jumping ahead, but jumping back is fine
        // Logic: can view if step is in completed_steps OR step == current_step

        // Ensure we handle current_step being null defensively
        $currentStep = $progress->current_step ?? 'company_setup';

        // For now, let's just show the view with data
        return view("onboarding.steps.{$step}", [
            'step' => $step,
            'currentStep' => $currentStep,
            'progress' => $progress,
            'progressSummary' => $this->onboardingService->getProgressSummary($user),
            'defaultData' => $this->onboardingService->getDefaultData($user, $step),
            'stepConfig' => config("onboarding.steps.{$step}"),
            'allSteps' => array_keys(config('onboarding.steps')),
        ]);
    }

    /**
     * Process a step submission.
     *
     * @param Request $request
     * @param string $step
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $step)
    {
        $user = auth()->guard('user')->user();

        try {
            // Get step implementation
            $stepClass = config("onboarding.steps.{$step}.handler");
            if (!class_exists($stepClass)) {
                throw new \Exception("Handler not found for step: {$step}");
            }

            $stepImplementation = app($stepClass);

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
                // Return JSON response for AJAX requests (used by onboarding.js)
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Step completed successfully!',
                        'redirect' => route('onboarding.complete'),
                    ]);
                }
                return redirect()->route('onboarding.complete')->with('success', 'Congratulations! You have completed the onboarding wizard.');
            }

            // Redirect to next step
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Step completed successfully!',
                    'redirect' => route('onboarding.step', ['step' => $progress->current_step]),
                ]);
            }

            return redirect()->route('onboarding.step', ['step' => $progress->current_step])
                ->with('success', 'Step completed successfully!');
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
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

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage(),
                ], 500);
            }

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
     * Skip the current step.
     *
     * @param Request $request
     * @param string $step
     * @return \Illuminate\Http\RedirectResponse
     */
    public function skip(Request $request, $step)
    {
        $user = auth()->guard('user')->user();

        try {
            $progress = $this->onboardingService->skipStep($user, $step);

            if ($progress->is_completed) {
                return redirect()->route('onboarding.complete')->with('success', 'You skipped the last step. Onboarding complete!');
            }

            return redirect()->route('onboarding.step', ['step' => $progress->current_step])
                ->with('info', 'Step skipped.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show completion page.
     */
    public function complete()
    {
        $user = auth()->guard('user')->user();
        $progress = $this->onboardingService->getOrCreateProgress($user);

        return view('onboarding.complete', [
            'progress' => $progress,
            'summary' => $this->onboardingService->getProgressSummary($user),
        ]);
    }

    /**
     * Restart onboarding.
     */
    public function restart()
    {
        $user = auth()->guard('user')->user();
        $this->onboardingService->resetOnboarding($user);
        return redirect()->route('onboarding.index');
    }

    /**
     * API: Get progress
     */
    public function progress()
    {
        $user = auth()->guard('user')->user();
        $summary = $this->onboardingService->getProgressSummary($user);

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * API: Validate step
     */
    public function validateStep(Request $request, $step)
    {
        // Simple validation check without saving
        $validationRules = config("onboarding.validation.{$step}", []);

        try {
            if (!empty($validationRules)) {
                $request->validate($validationRules);
            }
            return response()->json(['success' => true]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }
}
