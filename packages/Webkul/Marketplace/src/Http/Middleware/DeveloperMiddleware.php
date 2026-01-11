<?php

namespace Webkul\Marketplace\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if user is logged in
        if (!$user) {
            return redirect()->route('admin.session.create')
                ->with('error', trans('marketplace::app.developer.auth-required'));
        }

        // Check if user is an approved developer
        if (!$user->isDeveloper()) {
            // Check if user has a pending application
            if ($user->hasPendingDeveloperApplication()) {
                return redirect()->route('marketplace.browse.index')
                    ->with('info', trans('marketplace::app.developer.application-pending'));
            }

            // Check if user's application was rejected
            if ($user->isDeveloperRejected()) {
                return redirect()->route('marketplace.browse.index')
                    ->with('warning', trans('marketplace::app.developer.application-rejected'));
            }

            // Check if user's account is suspended
            if ($user->isDeveloperSuspended()) {
                return redirect()->route('marketplace.browse.index')
                    ->with('error', trans('marketplace::app.developer.account-suspended'));
            }

            // User needs to apply
            return redirect()->route('marketplace.developer-registration.create')
                ->with('info', trans('marketplace::app.developer.registration-required'));
        }

        return $next($request);
    }
}
