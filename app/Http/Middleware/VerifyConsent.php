<?php

namespace App\Http\Middleware;

use App\Services\Compliance\ConsentManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerifyConsent
{
    /**
     * The consent manager instance.
     *
     * @var ConsentManager
     */
    protected $consentManager;

    /**
     * Create a new middleware instance.
     *
     * @param ConsentManager $consentManager
     * @return void
     */
    public function __construct(ConsentManager $consentManager)
    {
        $this->consentManager = $consentManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$consentTypes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$consentTypes)
    {
        // If no consent types specified, check all required consents
        if (empty($consentTypes)) {
            return $this->verifyRequiredConsents($request, $next);
        }

        // Verify specific consent types
        return $this->verifySpecificConsents($request, $next, $consentTypes);
    }

    /**
     * Verify that the user has all required consents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected function verifyRequiredConsents(Request $request, Closure $next)
    {
        // Guest users don't need consent verification
        if (!Auth::check()) {
            return $next($request);
        }

        // Check if user has all required consents
        if (!$this->consentManager->hasRequiredConsents()) {
            $missingConsents = $this->consentManager->getMissingRequiredConsents();

            Log::warning('User missing required consents', [
                'user_id' => Auth::id(),
                'missing_consents' => $missingConsents,
                'url' => $request->fullUrl(),
            ]);

            return $this->handleMissingConsent($request, $missingConsents);
        }

        return $next($request);
    }

    /**
     * Verify that the user has specific consent types.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  array  $consentTypes
     * @return mixed
     */
    protected function verifySpecificConsents(Request $request, Closure $next, array $consentTypes)
    {
        // Guest users don't need consent verification
        if (!Auth::check()) {
            return $next($request);
        }

        $missingConsents = [];

        foreach ($consentTypes as $consentType) {
            if (!$this->consentManager->checkConsent($consentType)) {
                $missingConsents[] = $consentType;
            }
        }

        if (!empty($missingConsents)) {
            Log::warning('User missing specific consents', [
                'user_id' => Auth::id(),
                'missing_consents' => $missingConsents,
                'url' => $request->fullUrl(),
            ]);

            return $this->handleMissingConsent($request, $missingConsents);
        }

        return $next($request);
    }

    /**
     * Handle missing consent by returning an appropriate response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $missingConsents
     * @return mixed
     */
    protected function handleMissingConsent(Request $request, array $missingConsents)
    {
        // For API requests, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Missing required consent(s) to access this resource.',
                'missing_consents' => $missingConsents,
                'error' => 'consent_required',
            ], 403);
        }

        // For web requests, redirect to consent page with missing consents
        return redirect()
            ->route('consent.required')
            ->with('missing_consents', $missingConsents)
            ->with('intended_url', $request->fullUrl());
    }
}
