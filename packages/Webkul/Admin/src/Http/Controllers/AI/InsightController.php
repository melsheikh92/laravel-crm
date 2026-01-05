<?php

namespace Webkul\Admin\Http\Controllers\AI;

use Exception;
use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\AI\Services\InsightService;

class InsightController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected InsightService $insightService
    ) {}

    /**
     * Get insights for a lead.
     *
     * @param  int  $leadId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLeadInsights($leadId): JsonResponse
    {
        try {
            $insights = $this->insightService->getInsights('lead', $leadId);

            return response()->json([
                'data' => $insights,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get insights for a person.
     *
     * @param  int  $personId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPersonInsights($personId): JsonResponse
    {
        try {
            $insights = $this->insightService->getInsights('person', $personId);

            return response()->json([
                'data' => $insights,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate insights for a lead.
     *
     * @param  int  $leadId
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateLeadInsights($leadId): JsonResponse
    {
        try {
            $result = $this->insightService->generateLeadInsights($leadId);

            return response()->json([
                'data' => $result,
                'message' => trans('admin::app.ai.insights-generated'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

