<?php

namespace Webkul\Marketplace\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Http\Requests\DeveloperRegistrationRequest;
use Webkul\User\Repositories\UserRepository;

class DeveloperRegistrationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    /**
     * Show developer registration form.
     */
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        // Redirect if already a developer
        if ($user->isDeveloper()) {
            return redirect()->route('developer.marketplace.dashboard')
                ->with('info', trans('marketplace::app.developer-registration.already-developer'));
        }

        // Redirect if application is pending
        if ($user->hasPendingDeveloperApplication()) {
            return redirect()->route('marketplace.browse.index')
                ->with('info', trans('marketplace::app.developer-registration.application-pending'));
        }

        // Show reapply message if rejected
        $isRejected = $user->isDeveloperRejected();

        return view('marketplace::developer-registration.create', compact('isRejected'));
    }

    /**
     * Store developer registration.
     */
    public function store(DeveloperRegistrationRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if already a developer
            if ($user->isDeveloper()) {
                if ($request->ajax()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => trans('marketplace::app.developer-registration.already-developer'),
                    ], 400);
                }

                return redirect()->route('developer.marketplace.dashboard')
                    ->with('info', trans('marketplace::app.developer-registration.already-developer'));
            }

            // Check if application is pending
            if ($user->hasPendingDeveloperApplication()) {
                if ($request->ajax()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => trans('marketplace::app.developer-registration.application-pending'),
                    ], 400);
                }

                return redirect()->route('marketplace.browse.index')
                    ->with('info', trans('marketplace::app.developer-registration.application-pending'));
            }

            DB::beginTransaction();

            // Register user as developer
            $user->registerAsDeveloper([
                'bio' => $request->input('bio'),
                'company' => $request->input('company'),
                'website' => $request->input('website'),
                'support_email' => $request->input('support_email'),
                'social_links' => [
                    'github' => $request->input('github_url'),
                    'twitter' => $request->input('twitter_url'),
                    'linkedin' => $request->input('linkedin_url'),
                ],
            ]);

            DB::commit();

            if ($request->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.developer-registration.success'),
                    'redirect_url' => route('marketplace.browse.index'),
                ]);
            }

            return redirect()->route('marketplace.browse.index')
                ->with('success', trans('marketplace::app.developer-registration.success'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Developer registration failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer-registration.error'),
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', trans('marketplace::app.developer-registration.error'));
        }
    }

    /**
     * Show developer profile edit form.
     */
    public function edit(): View|RedirectResponse
    {
        $user = Auth::user();

        // Redirect if not a developer
        if (!$user->isDeveloper()) {
            return redirect()->route('marketplace.developer-registration.create')
                ->with('info', trans('marketplace::app.developer-registration.not-developer'));
        }

        return view('marketplace::developer-registration.edit', compact('user'));
    }

    /**
     * Update developer profile.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'bio' => 'nullable|string|max:1000',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:255',
            'github_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
        ]);

        try {
            $user = Auth::user();

            // Check if user is a developer
            if (!$user->isDeveloper() && !$user->hasPendingDeveloperApplication()) {
                if ($request->ajax()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => trans('marketplace::app.developer-registration.not-developer'),
                    ], 403);
                }

                return redirect()->route('marketplace.developer-registration.create')
                    ->with('error', trans('marketplace::app.developer-registration.not-developer'));
            }

            DB::beginTransaction();

            // Update developer profile
            $user->updateDeveloperProfile([
                'bio' => $request->input('bio'),
                'company' => $request->input('company'),
                'website' => $request->input('website'),
                'support_email' => $request->input('support_email'),
                'social_links' => [
                    'github' => $request->input('github_url'),
                    'twitter' => $request->input('twitter_url'),
                    'linkedin' => $request->input('linkedin_url'),
                ],
            ]);

            DB::commit();

            if ($request->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.developer-registration.profile-updated'),
                ]);
            }

            return redirect()->back()
                ->with('success', trans('marketplace::app.developer-registration.profile-updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Developer profile update failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer-registration.update-error'),
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', trans('marketplace::app.developer-registration.update-error'));
        }
    }

    /**
     * Get developer registration status.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();

        return new JsonResponse([
            'success' => true,
            'data' => [
                'is_developer' => $user->is_developer,
                'developer_status' => $user->developer_status,
                'is_approved' => $user->isDeveloper(),
                'is_pending' => $user->hasPendingDeveloperApplication(),
                'is_rejected' => $user->isDeveloperRejected(),
                'is_suspended' => $user->isDeveloperSuspended(),
            ],
        ]);
    }
}
