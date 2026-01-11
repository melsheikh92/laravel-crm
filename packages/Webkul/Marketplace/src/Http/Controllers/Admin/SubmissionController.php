<?php

namespace Webkul\Marketplace\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Marketplace\DataGrids\SubmissionDataGrid;
use Webkul\Marketplace\Repositories\ExtensionSubmissionRepository;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Services\SecurityScanner;

class SubmissionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionSubmissionRepository $submissionRepository,
        protected ExtensionRepository $extensionRepository,
        protected SecurityScanner $securityScanner
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(SubmissionDataGrid::class)->process();
        }

        $statistics = $this->submissionRepository->getStatistics();

        return view('marketplace::admin.submissions.index', compact('statistics'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View|JsonResponse
    {
        $submission = $this->submissionRepository
            ->with(['extension', 'version', 'submitter', 'reviewer'])
            ->findOrFail($id);

        if (request()->ajax()) {
            return new JsonResponse([
                'data' => $submission,
            ]);
        }

        return view('marketplace::admin.submissions.show', compact('submission'));
    }

    /**
     * Show the review form for the specified submission.
     */
    public function review(int $id): View|JsonResponse
    {
        $submission = $this->submissionRepository
            ->with(['extension', 'version', 'submitter', 'reviewer'])
            ->findOrFail($id);

        if (request()->ajax()) {
            return new JsonResponse([
                'data' => $submission,
            ]);
        }

        return view('marketplace::admin.submissions.review', compact('submission'));
    }

    /**
     * Approve the specified submission.
     */
    public function approve(int $id): JsonResponse
    {
        $this->validate(request(), [
            'review_notes' => 'nullable|string|max:2000',
        ]);

        try {
            $submission = $this->submissionRepository->findOrFail($id);

            if (!$submission->isPending()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.admin.submissions.already-reviewed'),
                ], 400);
            }

            Event::dispatch('marketplace.submission.approve.before', $submission);

            $reviewerId = auth()->guard('user')->id();
            $reviewNotes = request()->input('review_notes');

            $submission->approve($reviewerId, $reviewNotes);

            Event::dispatch('marketplace.submission.approve.after', $submission);

            return new JsonResponse([
                'data'    => $submission,
                'message' => trans('marketplace::app.admin.submissions.approve-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to approve submission: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.admin.submissions.approve-failed'),
            ], 500);
        }
    }

    /**
     * Reject the specified submission.
     */
    public function reject(int $id): JsonResponse
    {
        $this->validate(request(), [
            'review_notes' => 'required|string|max:2000',
        ]);

        try {
            $submission = $this->submissionRepository->findOrFail($id);

            if (!$submission->isPending()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.admin.submissions.already-reviewed'),
                ], 400);
            }

            Event::dispatch('marketplace.submission.reject.before', $submission);

            $reviewerId = auth()->guard('user')->id();
            $reviewNotes = request()->input('review_notes');

            $submission->reject($reviewerId, $reviewNotes);

            Event::dispatch('marketplace.submission.reject.after', $submission);

            return new JsonResponse([
                'data'    => $submission,
                'message' => trans('marketplace::app.admin.submissions.reject-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reject submission: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.admin.submissions.reject-failed'),
            ], 500);
        }
    }

    /**
     * Run security scan on the specified submission.
     */
    public function runSecurityScan(int $id): JsonResponse
    {
        try {
            $submission = $this->submissionRepository
                ->with(['extension', 'version'])
                ->findOrFail($id);

            if (!$submission->version) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.admin.submissions.version-not-found'),
                ], 404);
            }

            Event::dispatch('marketplace.submission.security_scan.before', $submission);

            // Get the package path from the version
            $packagePath = $submission->version->file_path;

            if (!$packagePath || !file_exists($packagePath)) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.admin.submissions.package-not-found'),
                ], 404);
            }

            // Run the security scan
            $scanResults = $this->securityScanner->scan($packagePath);

            // Update the submission with scan results
            $this->submissionRepository->update([
                'security_scan_results' => $scanResults,
            ], $id);

            Event::dispatch('marketplace.submission.security_scan.after', $submission);

            return new JsonResponse([
                'data'    => $scanResults,
                'message' => trans('marketplace::app.admin.submissions.security-scan-complete'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to run security scan: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.admin.submissions.security-scan-failed'),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get security scan results for the specified submission.
     */
    public function getSecurityScanResults(int $id): JsonResponse
    {
        try {
            $submission = $this->submissionRepository->findOrFail($id);

            if (!$submission->security_scan_results) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.admin.submissions.no-scan-results'),
                ], 404);
            }

            return new JsonResponse([
                'data' => $submission->security_scan_results,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get security scan results: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.admin.submissions.get-scan-results-failed'),
            ], 500);
        }
    }

    /**
     * Get pending submissions count.
     */
    public function getPendingCount(): JsonResponse
    {
        try {
            $count = $this->submissionRepository->getPendingCount();

            return new JsonResponse([
                'data' => [
                    'count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get pending count: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.admin.submissions.get-pending-count-failed'),
            ], 500);
        }
    }

    /**
     * Mass approve the specified resources.
     */
    public function massApprove(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $count = 0;

        $submissions = $this->submissionRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        $reviewerId = auth()->guard('user')->id();

        foreach ($submissions as $submission) {
            if (!$submission->isPending()) {
                continue;
            }

            Event::dispatch('marketplace.submission.approve.before', $submission);

            $submission->approve($reviewerId, 'Mass approved by admin');

            Event::dispatch('marketplace.submission.approve.after', $submission);

            $count++;
        }

        if (!$count) {
            return response()->json([
                'message' => trans('marketplace::app.admin.submissions.mass-approve-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('marketplace::app.admin.submissions.mass-approve-success', ['count' => $count]),
        ]);
    }

    /**
     * Mass reject the specified resources.
     */
    public function massReject(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $this->validate(request(), [
            'review_notes' => 'required|string|max:2000',
        ]);

        $count = 0;
        $reviewNotes = request()->input('review_notes', 'Mass rejected by admin');

        $submissions = $this->submissionRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        $reviewerId = auth()->guard('user')->id();

        foreach ($submissions as $submission) {
            if (!$submission->isPending()) {
                continue;
            }

            Event::dispatch('marketplace.submission.reject.before', $submission);

            $submission->reject($reviewerId, $reviewNotes);

            Event::dispatch('marketplace.submission.reject.after', $submission);

            $count++;
        }

        if (!$count) {
            return response()->json([
                'message' => trans('marketplace::app.admin.submissions.mass-reject-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('marketplace::app.admin.submissions.mass-reject-success', ['count' => $count]),
        ]);
    }
}
