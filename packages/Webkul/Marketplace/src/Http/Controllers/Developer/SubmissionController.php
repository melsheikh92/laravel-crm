<?php

namespace Webkul\Marketplace\Http\Controllers\Developer;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionSubmissionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class SubmissionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionVersionRepository $versionRepository,
        protected ExtensionSubmissionRepository $submissionRepository
    ) {}

    /**
     * Display a listing of the developer's submissions.
     */
    public function index(): View|JsonResponse
    {
        try {
            $userId = Auth::id();

            $submissions = $this->submissionRepository
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->bySubmitter($userId)
                        ->with(['extension', 'version', 'reviewer'])
                        ->orderBy('submitted_at', 'desc');
                })
                ->paginate(15);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $submissions,
                ]);
            }

            return view('marketplace::developer.submissions.index', compact('submissions'));
        } catch (\Exception $e) {
            Log::error('Failed to load developer submissions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.load-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.submissions.load-failed'));
        }
    }

    /**
     * Submit an extension version for review.
     */
    public function submit(int $extensionId, int $versionId): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($extensionId);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.unauthorized'),
                ], 403);
            }

            $version = $this->versionRepository->findOrFail($versionId);

            // Ensure the version belongs to the extension
            if ($version->extension_id !== $extensionId) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.version-mismatch'),
                ], 400);
            }

            // Check if version has a package file uploaded
            if (!$version->file_path) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.no-package'),
                ], 400);
            }

            // Check if there's already a pending submission for this version
            $existingSubmission = $this->submissionRepository
                ->scopeQuery(function ($query) use ($versionId) {
                    return $query->forVersion($versionId)->pending();
                })
                ->first();

            if ($existingSubmission) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.already-pending'),
                ], 400);
            }

            Event::dispatch('marketplace.submission.submit.before');

            // Create submission
            $submission = $this->submissionRepository->create([
                'extension_id' => $extensionId,
                'version_id'   => $versionId,
                'submitted_by' => Auth::id(),
                'status'       => 'pending',
                'submitted_at' => now(),
            ]);

            Event::dispatch('marketplace.submission.submit.after', $submission);

            return new JsonResponse([
                'success' => true,
                'data'    => $submission,
                'message' => trans('marketplace::app.developer.submissions.submit-success'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to submit extension for review: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.submissions.submit-failed'),
            ], 500);
        }
    }

    /**
     * Display the specified submission.
     */
    public function show(int $id): View|JsonResponse
    {
        try {
            $submission = $this->submissionRepository
                ->with(['extension', 'version', 'reviewer'])
                ->findOrFail($id);

            // Ensure the submission belongs to the current user
            if ($submission->submitted_by !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.unauthorized'),
                ], 403);
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $submission,
                ]);
            }

            return view('marketplace::developer.submissions.show', compact('submission'));
        } catch (\Exception $e) {
            Log::error('Failed to load submission: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.not-found'),
                ], 404);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.submissions.not-found'));
        }
    }

    /**
     * Cancel a pending submission.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $submission = $this->submissionRepository->findOrFail($id);

            // Ensure the submission belongs to the current user
            if ($submission->submitted_by !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.unauthorized'),
                ], 403);
            }

            // Only pending submissions can be cancelled
            if ($submission->status !== 'pending') {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.cancel-not-pending'),
                ], 400);
            }

            Event::dispatch('marketplace.submission.cancel.before', $id);

            $this->submissionRepository->delete($id);

            Event::dispatch('marketplace.submission.cancel.after', $id);

            return new JsonResponse([
                'success' => true,
                'message' => trans('marketplace::app.developer.submissions.cancel-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cancel submission: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.submissions.cancel-failed'),
            ], 500);
        }
    }

    /**
     * Resubmit a rejected submission.
     */
    public function resubmit(int $id): JsonResponse
    {
        try {
            $submission = $this->submissionRepository
                ->with(['extension', 'version'])
                ->findOrFail($id);

            // Ensure the submission belongs to the current user
            if ($submission->submitted_by !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.unauthorized'),
                ], 403);
            }

            // Only rejected submissions can be resubmitted
            if ($submission->status !== 'rejected') {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.resubmit-not-rejected'),
                ], 400);
            }

            Event::dispatch('marketplace.submission.resubmit.before', $submission);

            // Update the submission to pending status
            $submission = $this->submissionRepository->update([
                'status'       => 'pending',
                'reviewer_id'  => null,
                'review_notes' => null,
                'reviewed_at'  => null,
                'submitted_at' => now(),
            ], $id);

            // Update extension and version status back to pending
            if ($submission->extension) {
                $this->extensionRepository->update(
                    ['status' => 'pending'],
                    $submission->extension_id
                );
            }

            if ($submission->version) {
                $this->versionRepository->update(
                    ['status' => 'pending'],
                    $submission->version_id
                );
            }

            Event::dispatch('marketplace.submission.resubmit.after', $submission);

            return new JsonResponse([
                'success' => true,
                'data'    => $submission,
                'message' => trans('marketplace::app.developer.submissions.resubmit-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resubmit submission: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.submissions.resubmit-failed'),
            ], 500);
        }
    }

    /**
     * Get submissions for a specific extension.
     */
    public function byExtension(int $extensionId): View|JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($extensionId);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.unauthorized'),
                ], 403);
            }

            $submissions = $this->submissionRepository
                ->scopeQuery(function ($query) use ($extensionId) {
                    return $query->forExtension($extensionId)
                        ->with(['version', 'reviewer'])
                        ->orderBy('submitted_at', 'desc');
                })
                ->paginate(15);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'extension'   => $extension,
                        'submissions' => $submissions,
                    ],
                ]);
            }

            return view('marketplace::developer.submissions.by-extension', compact('extension', 'submissions'));
        } catch (\Exception $e) {
            Log::error('Failed to load extension submissions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.submissions.load-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.submissions.load-failed'));
        }
    }

    /**
     * Get count of pending submissions for the developer.
     */
    public function getPendingCount(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $count = $this->submissionRepository
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->bySubmitter($userId)->pending();
                })
                ->count();

            return new JsonResponse([
                'success' => true,
                'count'   => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get pending submissions count: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.submissions.count-failed'),
            ], 500);
        }
    }
}
