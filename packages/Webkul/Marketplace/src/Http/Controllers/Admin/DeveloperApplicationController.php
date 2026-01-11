<?php

namespace Webkul\Marketplace\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\User\Repositories\UserRepository;

class DeveloperApplicationController extends Controller
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
     * Display a listing of developer applications.
     */
    public function index(): View
    {
        $pendingApplications = $this->userRepository->scopeQuery(function ($query) {
            return $query->pendingDevelopers()
                ->orderBy('developer_registered_at', 'desc');
        })->paginate(20);

        $approvedDevelopers = $this->userRepository->scopeQuery(function ($query) {
            return $query->approvedDevelopers()
                ->orderBy('developer_approved_at', 'desc');
        })->paginate(20);

        return view('marketplace::admin.developer-applications.index', compact('pendingApplications', 'approvedDevelopers'));
    }

    /**
     * Display the specified developer application.
     */
    public function show(int $id): View|RedirectResponse
    {
        $developer = $this->userRepository->findOrFail($id);

        if (!$developer->is_developer) {
            return redirect()->route('admin.marketplace.developer-applications.index')
                ->with('error', trans('marketplace::app.admin.developer-applications.not-developer'));
        }

        return view('marketplace::admin.developer-applications.show', compact('developer'));
    }

    /**
     * Approve a developer application.
     */
    public function approve(int $id): RedirectResponse|JsonResponse
    {
        try {
            $developer = $this->userRepository->findOrFail($id);

            if (!$developer->is_developer) {
                return $this->errorResponse(
                    trans('marketplace::app.admin.developer-applications.not-developer'),
                    404
                );
            }

            if ($developer->developer_status === 'approved') {
                return $this->errorResponse(
                    trans('marketplace::app.admin.developer-applications.already-approved'),
                    400
                );
            }

            DB::beginTransaction();

            $developer->approveDeveloper();

            DB::commit();

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.admin.developer-applications.approved'),
                ]);
            }

            return redirect()->back()
                ->with('success', trans('marketplace::app.admin.developer-applications.approved'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve developer: ' . $e->getMessage());

            return $this->errorResponse(
                trans('marketplace::app.admin.developer-applications.approve-error'),
                500
            );
        }
    }

    /**
     * Reject a developer application.
     */
    public function reject(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $developer = $this->userRepository->findOrFail($id);

            if (!$developer->is_developer) {
                return $this->errorResponse(
                    trans('marketplace::app.admin.developer-applications.not-developer'),
                    404
                );
            }

            if ($developer->developer_status === 'rejected') {
                return $this->errorResponse(
                    trans('marketplace::app.admin.developer-applications.already-rejected'),
                    400
                );
            }

            DB::beginTransaction();

            $developer->rejectDeveloper();

            DB::commit();

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.admin.developer-applications.rejected'),
                ]);
            }

            return redirect()->back()
                ->with('success', trans('marketplace::app.admin.developer-applications.rejected'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject developer: ' . $e->getMessage());

            return $this->errorResponse(
                trans('marketplace::app.admin.developer-applications.reject-error'),
                500
            );
        }
    }

    /**
     * Suspend a developer account.
     */
    public function suspend(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $developer = $this->userRepository->findOrFail($id);

            if (!$developer->is_developer) {
                return $this->errorResponse(
                    trans('marketplace::app.admin.developer-applications.not-developer'),
                    404
                );
            }

            if ($developer->developer_status === 'suspended') {
                return $this->errorResponse(
                    trans('marketplace::app.admin.developer-applications.already-suspended'),
                    400
                );
            }

            DB::beginTransaction();

            $developer->suspendDeveloper();

            DB::commit();

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.admin.developer-applications.suspended'),
                ]);
            }

            return redirect()->back()
                ->with('success', trans('marketplace::app.admin.developer-applications.suspended'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to suspend developer: ' . $e->getMessage());

            return $this->errorResponse(
                trans('marketplace::app.admin.developer-applications.suspend-error'),
                500
            );
            }
    }

    /**
     * Get pending applications count.
     */
    public function pendingCount(): JsonResponse
    {
        try {
            $count = $this->userRepository->scopeQuery(function ($query) {
                return $query->pendingDevelopers();
            })->count();

            return new JsonResponse([
                'success' => true,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get pending developer count: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to get count',
            ], 500);
        }
    }

    /**
     * Return error response.
     */
    protected function errorResponse(string $message, int $status = 400): RedirectResponse|JsonResponse
    {
        if (request()->ajax()) {
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()
            ->with('error', $message);
    }
}
