<?php

namespace App\Http\Middleware;

use App\Models\OnboardingProgress;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectIfOnboardingIncomplete
{
    /**
     * Routes that should be excluded from onboarding redirect.
     *
     * @var array
     */
    protected $except = [
        'onboarding',
        'onboarding/*',
        'api/*',
        'login',
        'logout',
        'register',
        'password/*',
        'consent/*',
        'landing',
        'demo-request',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip if onboarding is disabled globally
        if (!config('onboarding.enabled', true)) {
            return $next($request);
        }

        // Skip if auto-trigger is disabled
        if (!config('onboarding.auto_trigger', true)) {
            return $next($request);
        }

        // Skip for guest users
        if (!Auth::check()) {
            return $next($request);
        }

        // Skip for excluded routes
        if ($this->shouldExclude($request)) {
            return $next($request);
        }

        // Skip for API requests
        if ($request->expectsJson()) {
            return $next($request);
        }

        // Check if user has completed onboarding
        $user = Auth::user();
        $progress = OnboardingProgress::getForUser($user->id);

        // If no progress exists, create it and redirect to onboarding
        if (!$progress) {
            Log::info('New user detected, creating onboarding progress', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            // Create onboarding progress for the user
            try {
                OnboardingProgress::getOrCreateForUser($user->id);
            } catch (\Exception $e) {
                Log::error('Failed to create onboarding progress: ' . $e->getMessage());
                // We don't rethrow here because the service might handle it or we want to avoid crashing
                // However, if it failed, we can't redirect to onboarding step...
                // But let's assume the service/controller call later will handle it or retry
            }


            return $this->redirectToOnboarding($request);
        }

        // If onboarding is incomplete, redirect to wizard
        if (!$progress->is_completed) {
            Log::debug('User has incomplete onboarding, redirecting to wizard', [
                'user_id' => $user->id,
                'current_step' => $progress->current_step,
                'completed_steps' => $progress->getCompletedStepsCount(),
            ]);

            return $this->redirectToOnboarding($request, $progress);
        }

        // User has completed onboarding, continue with request
        return $next($request);
    }

    /**
     * Determine if the request should be excluded from onboarding redirect.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldExclude(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($pattern !== '/') {
                $pattern = trim($pattern, '/');
            }

            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect user to the appropriate onboarding page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OnboardingProgress|null  $progress
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToOnboarding(Request $request, ?OnboardingProgress $progress = null)
    {
        // Store intended URL so user can be redirected after completing onboarding
        if (!$request->is('dashboard', '/')) {
            session()->put('onboarding.intended_url', $request->fullUrl());
        }

        // If progress exists and has a current step, redirect to that step
        if ($progress && $progress->current_step) {
            Log::info('Redirecting user to resume onboarding', [
                'user_id' => Auth::id(),
                'current_step' => $progress->current_step,
                'from_url' => $request->fullUrl(),
            ]);

            return redirect()
                ->route('onboarding.step', ['step' => $progress->current_step])
                ->with('info', 'Please complete the setup wizard to continue.');
        }

        // Otherwise, redirect to the onboarding index (welcome page)
        Log::info('Redirecting user to start onboarding', [
            'user_id' => Auth::id(),
            'from_url' => $request->fullUrl(),
        ]);

        return redirect()
            ->route('onboarding.index')
            ->with('info', 'Welcome! Let\'s get your CRM set up.');
    }
}
