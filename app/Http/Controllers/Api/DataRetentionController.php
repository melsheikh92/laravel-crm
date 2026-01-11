<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataRetentionPolicy;
use App\Services\Compliance\DataRetentionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DataRetentionController extends Controller
{
    /**
     * @var DataRetentionService
     */
    protected $dataRetentionService;

    /**
     * Create a new controller instance.
     */
    public function __construct(DataRetentionService $dataRetentionService)
    {
        $this->dataRetentionService = $dataRetentionService;
    }

    /**
     * Get all data retention policies.
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

        $modelType = $request->query('model_type');
        $activeOnly = $request->query('active_only', false);

        $query = DataRetentionPolicy::query();

        if ($modelType) {
            $query->where('model_type', $modelType);
        }

        if (filter_var($activeOnly, FILTER_VALIDATE_BOOLEAN)) {
            $query->where('is_active', true);
        }

        $policies = $query->orderBy('model_type')
            ->orderBy('retention_period_days')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $policies,
        ]);
    }

    /**
     * Get a specific data retention policy.
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

        $policy = DataRetentionPolicy::find($id);

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Retention policy not found',
            ], 404);
        }

        // Include statistics if requested
        $includeStats = $request->query('include_stats', false);
        $policyData = $policy->toArray();

        if (filter_var($includeStats, FILTER_VALIDATE_BOOLEAN)) {
            $policyData['statistics'] = $policy->getStatistics();
        }

        return response()->json([
            'success' => true,
            'data' => $policyData,
        ]);
    }

    /**
     * Create a new data retention policy.
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
                'model_type' => 'required|string|max:255',
                'retention_period_days' => 'required|integer|min:1',
                'delete_after_days' => 'required|integer|min:1',
                'conditions' => 'nullable|array',
                'is_active' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Validate that delete_after_days is greater than retention_period_days
            if ($validated['delete_after_days'] <= $validated['retention_period_days']) {
                return response()->json([
                    'success' => false,
                    'message' => 'delete_after_days must be greater than retention_period_days',
                ], 422);
            }

            $policy = DataRetentionPolicy::create([
                'model_type' => $validated['model_type'],
                'retention_period_days' => $validated['retention_period_days'],
                'delete_after_days' => $validated['delete_after_days'],
                'conditions' => $validated['conditions'] ?? [],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Retention policy created successfully',
                'data' => $policy,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create retention policy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a data retention policy.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $policy = DataRetentionPolicy::find($id);

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Retention policy not found',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'model_type' => 'nullable|string|max:255',
                'retention_period_days' => 'nullable|integer|min:1',
                'delete_after_days' => 'nullable|integer|min:1',
                'conditions' => 'nullable|array',
                'is_active' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Validate that delete_after_days is greater than retention_period_days
            $retentionDays = $validated['retention_period_days'] ?? $policy->retention_period_days;
            $deleteDays = $validated['delete_after_days'] ?? $policy->delete_after_days;

            if ($deleteDays <= $retentionDays) {
                return response()->json([
                    'success' => false,
                    'message' => 'delete_after_days must be greater than retention_period_days',
                ], 422);
            }

            $policy->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Retention policy updated successfully',
                'data' => $policy,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update retention policy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a data retention policy.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $policy = DataRetentionPolicy::find($id);

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Retention policy not found',
            ], 404);
        }

        try {
            $policy->delete();

            return response()->json([
                'success' => true,
                'message' => 'Retention policy deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete retention policy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate a data retention policy.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function activate(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $policy = DataRetentionPolicy::find($id);

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Retention policy not found',
            ], 404);
        }

        try {
            $activated = $policy->activate();

            if (!$activated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Policy is already active',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Retention policy activated successfully',
                'data' => $policy,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate retention policy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate a data retention policy.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function deactivate(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $policy = DataRetentionPolicy::find($id);

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Retention policy not found',
            ], 404);
        }

        try {
            $deactivated = $policy->deactivate();

            if (!$deactivated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Policy is already inactive',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Retention policy deactivated successfully',
                'data' => $policy,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate retention policy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics for retention policies.
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

        $modelType = $request->query('model_type');

        try {
            $statistics = $this->dataRetentionService->getRetentionStatistics($modelType);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get retention statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get expired records according to retention policies.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function expiredRecords(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $modelType = $request->query('model_type');
        $deletableOnly = $request->query('deletable_only', false);

        try {
            $expiredRecords = $this->dataRetentionService->getExpiredRecords(
                $modelType,
                filter_var($deletableOnly, FILTER_VALIDATE_BOOLEAN)
            );

            // Transform the data to be more API-friendly (don't include full record data)
            $summary = $expiredRecords->map(function ($item) {
                return [
                    'policy_id' => $item['policy_id'],
                    'model_type' => $item['model_type'],
                    'retention_period_days' => $item['retention_period_days'],
                    'delete_after_days' => $item['delete_after_days'],
                    'record_count' => $item['record_count'],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get expired records: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply retention policies (dry run or actual).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function applyPolicies(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $dryRun = $request->query('dry_run', true);
        $modelType = $request->query('model_type');

        try {
            $result = $this->dataRetentionService->applyPolicies(
                filter_var($dryRun, FILTER_VALIDATE_BOOLEAN),
                $modelType
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply retention policies: ' . $e->getMessage(),
            ], 500);
        }
    }
}
