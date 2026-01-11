<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ExportUserDataJob;
use App\Models\DataDeletionRequest;
use App\Services\Compliance\RightToErasureService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DataDeletionController extends Controller
{
    /**
     * @var RightToErasureService
     */
    protected $rightToErasureService;

    /**
     * Create a new controller instance.
     */
    public function __construct(RightToErasureService $rightToErasureService)
    {
        $this->rightToErasureService = $rightToErasureService;
    }

    /**
     * Get all data deletion requests.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $status = $request->query('status');
        $userId = $request->query('user_id');
        $email = $request->query('email');

        $query = DataDeletionRequest::query()
            ->with(['user', 'processedBy'])
            ->orderBy('requested_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($email) {
            $query->where('email', $email);
        }

        $requests = $query->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Get a specific data deletion request.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $deletionRequest = DataDeletionRequest::with(['user', 'processedBy'])->find($id);

        if (!$deletionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Deletion request not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $deletionRequest,
        ]);
    }

    /**
     * Create a new data deletion request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $validated = $request->validate([
                'user_id' => 'nullable|integer|exists:users,id',
                'email' => 'nullable|email|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // If no user_id provided, use the authenticated user
            $targetUserId = $validated['user_id'] ?? $user->id;
            $email = $validated['email'] ?? null;
            $metadata = [
                'notes' => $validated['notes'] ?? 'User requested data deletion via API',
            ];

            $deletionRequest = $this->rightToErasureService->requestDeletion(
                $targetUserId,
                $email,
                $metadata
            );

            return response()->json([
                'success' => true,
                'message' => 'Deletion request created successfully',
                'data' => $deletionRequest,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deletion request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process a data deletion request.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function process(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $deletionRequest = DataDeletionRequest::find($id);

        if (!$deletionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Deletion request not found',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'force' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $force = $validated['force'] ?? false;

            $result = $this->rightToErasureService->processRequest(
                $deletionRequest,
                $user->id,
                filter_var($force, FILTER_VALIDATE_BOOLEAN)
            );

            return response()->json([
                'success' => true,
                'message' => 'Deletion request processed successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process deletion request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a data deletion request.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $deletionRequest = DataDeletionRequest::find($id);

        if (!$deletionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Deletion request not found',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:1000',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $notes = $validated['notes'] ?? 'Request cancelled via API';

            $cancelled = $deletionRequest->cancel($user->id, $notes);

            if (!$cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request cannot be cancelled (already processed)',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Deletion request cancelled successfully',
                'data' => $deletionRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel deletion request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export user data (GDPR data portability).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportData(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $validated = $request->validate([
                'user_id' => 'nullable|integer|exists:users,id',
                'format' => 'nullable|string|in:json,csv,pdf',
                'include_audit_logs' => 'nullable|boolean',
                'async' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $targetUserId = $validated['user_id'] ?? $user->id;
            $format = $validated['format'] ?? 'json';
            $includeAuditLogs = $validated['include_audit_logs'] ?? false;
            $async = $validated['async'] ?? false;

            // If async is true, queue the export job
            if (filter_var($async, FILTER_VALIDATE_BOOLEAN)) {
                ExportUserDataJob::dispatch(
                    $targetUserId,
                    $format,
                    filter_var($includeAuditLogs, FILTER_VALIDATE_BOOLEAN)
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Data export has been queued. You will be notified when it is ready.',
                ]);
            }

            // Synchronous export
            $exportData = $this->rightToErasureService->exportUserData(
                $targetUserId,
                $format,
                filter_var($includeAuditLogs, FILTER_VALIDATE_BOOLEAN)
            );

            return response()->json([
                'success' => true,
                'message' => 'User data exported successfully',
                'data' => $exportData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export user data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics for data deletion requests.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $statsByStatus = DataDeletionRequest::getStatsByStatus();
            $overdueRequests = DataDeletionRequest::getOverdueRequests();

            return response()->json([
                'success' => true,
                'data' => [
                    'by_status' => $statsByStatus,
                    'overdue' => [
                        'count' => $overdueRequests->count(),
                        'requests' => $overdueRequests,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get deletion request statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get overdue deletion requests.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function overdue(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $days = $request->query('days', 30);

        try {
            $overdueRequests = DataDeletionRequest::getOverdueRequests((int) $days);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $overdueRequests->count(),
                    'requests' => $overdueRequests,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get overdue requests: ' . $e->getMessage(),
            ], 500);
        }
    }
}
