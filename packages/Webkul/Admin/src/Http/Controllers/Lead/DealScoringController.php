<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\DealScoreRequest;
use Webkul\Lead\Repositories\DealScoreRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\DealScoringService;

class DealScoringController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected DealScoreRepository $dealScoreRepository,
        protected DealScoringService $dealScoringService,
        protected LeadRepository $leadRepository
    ) {
    }

    /**
     * Get the score for a specific lead.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lead = $this->leadRepository->findOrFail($id);

            // Check authorization
            if ($userIds = bouncer()->getAuthorizedUserIds()) {
                if (! in_array($lead->user_id, $userIds)) {
                    return response()->json([
                        'message' => 'Unauthorized access to this lead.',
                    ], 403);
                }
            }

            $score = $this->dealScoreRepository->getLatestByLead($id);

            if (! $score) {
                return response()->json([
                    'message' => 'No score found for this lead. Please calculate the score first.',
                    'data' => null,
                ], 404);
            }

            // Get score statistics for this lead
            $statistics = $this->dealScoreRepository->getStatisticsByLead($id);

            return response()->json([
                'data' => $score,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve lead score: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate and update the score for a lead.
     */
    public function calculate(Request $request, int $id): JsonResponse
    {
        try {
            $lead = $this->leadRepository->findOrFail($id);

            // Check authorization
            if ($userIds = bouncer()->getAuthorizedUserIds()) {
                if (! in_array($lead->user_id, $userIds)) {
                    return response()->json([
                        'message' => 'Unauthorized access to this lead.',
                    ], 403);
                }
            }

            // Calculate the score
            $scoreData = $this->dealScoringService->scoreLead($lead, true);

            // Get the persisted score
            $score = $this->dealScoreRepository->getLatestByLead($id);

            return response()->json([
                'message' => 'Lead score calculated successfully.',
                'data' => $score,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to calculate lead score: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get top scored leads.
     */
    public function topScored(DealScoreRequest $request): JsonResponse
    {
        try {
            // Get query parameters
            $limit = (int) $request->query('limit', 10);

            $userId = $request->query('user_id');
            $priority = $request->query('priority');
            $minScore = $request->query('min_score');
            $minWinProbability = $request->query('min_win_probability');

            // Build filters
            $filters = [
                'latest_only' => true,
                'sort_by' => 'score',
                'sort_order' => 'desc',
            ];

            if ($priority && in_array($priority, ['high', 'medium', 'low'])) {
                $filters['priority'] = $priority;
            }

            if ($minScore !== null && is_numeric($minScore)) {
                $filters['min_score'] = (float) $minScore;
            }

            if ($minWinProbability !== null && is_numeric($minWinProbability)) {
                $filters['min_win_probability'] = (float) $minWinProbability;
            }

            // Get scores with filters
            $scores = $this->dealScoreRepository->getWithFilters($filters);

            // Apply user authorization
            if ($userIds = bouncer()->getAuthorizedUserIds()) {
                $scores = $scores->filter(function ($score) use ($userIds) {
                    return $score->lead && in_array($score->lead->user_id, $userIds);
                });
            }

            // Filter by requested user if specified
            if ($userId && is_numeric($userId)) {
                $scores = $scores->filter(function ($score) use ($userId) {
                    return $score->lead && $score->lead->user_id == $userId;
                });
            }

            // Apply limit
            $scores = $scores->take($limit);

            // Get overall statistics
            $statistics = $this->dealScoreRepository->getStatistics();
            $distribution = $this->dealScoreRepository->getScoreDistribution();

            return response()->json([
                'data' => $scores->values(),
                'statistics' => $statistics,
                'distribution' => $distribution,
                'meta' => [
                    'limit' => $limit,
                    'count' => $scores->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve top scored leads: ' . $e->getMessage(),
            ], 500);
        }
    }
}
