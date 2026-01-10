<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Services\TerritoryAnalyticsService;

class TerritoryAnalyticsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TerritoryAnalyticsService $analyticsService,
        protected TerritoryRepository $territoryRepository
    ) {}

    /**
     * Display territory analytics and performance reports.
     */
    public function index(): View
    {
        $territories = $this->territoryRepository->getActiveTerritories();

        return view('admin::settings.territories.analytics', compact('territories'));
    }

    /**
     * Get aggregated metrics across all territories.
     */
    public function overview(): JsonResponse
    {
        $metrics = $this->analyticsService->getAggregatedMetrics();

        return response()->json([
            'success' => true,
            'data'    => $metrics,
        ]);
    }

    /**
     * Get performance metrics for all territories.
     */
    public function allTerritories(): JsonResponse
    {
        $performance = $this->analyticsService->getAllTerritoriesPerformance();

        return response()->json([
            'success' => true,
            'data'    => $performance,
        ]);
    }

    /**
     * Get performance metrics for a specific territory.
     */
    public function territory(int $id): JsonResponse
    {
        $metrics = $this->analyticsService->getPerformanceMetrics($id);

        if (empty($metrics)) {
            return response()->json([
                'success' => false,
                'message' => trans('admin::app.settings.territories.analytics.territory-not-found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $metrics,
        ]);
    }

    /**
     * Get performance metrics within a date range.
     */
    public function dateRange(int $id): JsonResponse
    {
        $this->validate(request(), [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $metrics = $this->analyticsService->getPerformanceByDateRange(
            $id,
            request('start_date'),
            request('end_date')
        );

        if (empty($metrics)) {
            return response()->json([
                'success' => false,
                'message' => trans('admin::app.settings.territories.analytics.territory-not-found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $metrics,
        ]);
    }

    /**
     * Get top performing territories by revenue.
     */
    public function topByRevenue(): JsonResponse
    {
        $limit = request('limit', 10);

        $territories = $this->analyticsService->getTopTerritoriesByRevenue($limit);

        return response()->json([
            'success' => true,
            'data'    => $territories,
        ]);
    }

    /**
     * Get top performing territories by conversion rate.
     */
    public function topByConversionRate(): JsonResponse
    {
        $limit = request('limit', 10);

        $territories = $this->analyticsService->getTopTerritoriesByConversionRate($limit);

        return response()->json([
            'success' => true,
            'data'    => $territories,
        ]);
    }

    /**
     * Get top performing territories by lead count.
     */
    public function topByLeadCount(): JsonResponse
    {
        $limit = request('limit', 10);

        $territories = $this->analyticsService->getTopTerritoriesByLeadCount($limit);

        return response()->json([
            'success' => true,
            'data'    => $territories,
        ]);
    }

    /**
     * Compare performance between multiple territories.
     */
    public function compare(): JsonResponse
    {
        $this->validate(request(), [
            'territory_ids'   => 'required|array|min:2',
            'territory_ids.*' => 'required|integer|exists:territories,id',
        ]);

        $comparison = $this->analyticsService->comparePerformance(
            request('territory_ids')
        );

        return response()->json([
            'success' => true,
            'data'    => $comparison,
        ]);
    }

    /**
     * Get performance trend for a territory over time.
     */
    public function trend(int $id): JsonResponse
    {
        $months = request('months', 12);

        if ($months < 1 || $months > 24) {
            return response()->json([
                'success' => false,
                'message' => trans('admin::app.settings.territories.analytics.invalid-months-range'),
            ], 400);
        }

        $trend = $this->analyticsService->getPerformanceTrend($id, $months);

        return response()->json([
            'success' => true,
            'data'    => $trend,
        ]);
    }

    /**
     * Get territory rankings by performance metric.
     */
    public function rankings(): JsonResponse
    {
        $metric = request('metric', 'revenue');

        if (! in_array($metric, ['revenue', 'conversion_rate', 'lead_count'])) {
            return response()->json([
                'success' => false,
                'message' => trans('admin::app.settings.territories.analytics.invalid-metric'),
            ], 400);
        }

        $rankings = $this->analyticsService->getTerritoryRankings($metric);

        return response()->json([
            'success' => true,
            'data'    => $rankings,
        ]);
    }

    /**
     * Get performance metrics by territory type.
     */
    public function byType(): JsonResponse
    {
        $type = request('type');

        if (! in_array($type, ['geographic', 'account-based'])) {
            return response()->json([
                'success' => false,
                'message' => trans('admin::app.settings.territories.analytics.invalid-type'),
            ], 400);
        }

        $performance = $this->analyticsService->getPerformanceByType($type);

        return response()->json([
            'success' => true,
            'data'    => $performance,
        ]);
    }
}
