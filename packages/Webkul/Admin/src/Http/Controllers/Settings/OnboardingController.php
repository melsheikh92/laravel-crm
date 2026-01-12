<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use App\Services\OnboardingService;

class OnboardingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected OnboardingService $onboardingService) {}

    /**
     * Display the onboarding settings page.
     */
    public function index(): View
    {
        $progress = $this->onboardingService->getProgress(auth()->guard('user')->user());

        $progressSummary = null;

        if ($progress) {
            $progressSummary = $this->onboardingService->getProgressSummary(auth()->guard('user')->user());
        }

        return view('admin::settings.onboarding.index', [
            'progress' => $progress,
            'progressSummary' => $progressSummary,
        ]);
    }
}
