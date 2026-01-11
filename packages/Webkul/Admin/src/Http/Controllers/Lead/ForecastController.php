<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\ForecastRequest;
use Webkul\Lead\Repositories\SalesForecastRepository;
use Webkul\Lead\Services\ForecastCalculationService;
use Webkul\Lead\Services\ForecastAccuracyService;

class ForecastController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected SalesForecastRepository $salesForecastRepository,
        protected ForecastCalculationService $forecastCalculationService,
        protected ForecastAccuracyService $forecastAccuracyService
    ) {
    }

    /**
     * Display a listing of forecasts with optional filtering.
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = $this->salesForecastRepository->getModel()->with(['user', 'latestActual']);

            // Filter by user
            if ($userId = $request->query('user_id')) {
                $query->where('user_id', $userId);
            }

            // Filter by team
            if ($teamId = $request->query('team_id')) {
                $query->where('team_id', $teamId);
            }

            // Filter by period type
            if ($periodType = $request->query('period_type')) {
                $query->where('period_type', $periodType);
            }

            // Filter by date range
            if ($startDate = $request->query('start_date')) {
                $query->where('period_start', '>=', $startDate);
            }

            if ($endDate = $request->query('end_date')) {
                $query->where('period_end', '<=', $endDate);
            }

            // Apply user authorization
            if ($userIds = bouncer()->getAuthorizedUserIds()) {
                $query->whereIn('user_id', $userIds);
            }

            // Order by period start descending
            $query->orderBy('period_start', 'desc');

            // Paginate results
            $perPage = $request->query('per_page', 15);
            $forecasts = $query->paginate($perPage);

            return response()->json([
                'data' => $forecasts->items(),
                'meta' => [
                    'current_page' => $forecasts->currentPage(),
                    'from' => $forecasts->firstItem(),
                    'last_page' => $forecasts->lastPage(),
                    'per_page' => $forecasts->perPage(),
                    'to' => $forecasts->lastItem(),
                    'total' => $forecasts->total(),
                ],
            ]);
        }

        return view('admin::leads.forecasts.index');
    }

    /**
     * Display the specified forecast.
     */
    public function show(int $id): JsonResponse
    {
        $forecast = $this->salesForecastRepository
            ->with(['user', 'actuals'])
            ->findOrFail($id);

        // Check authorization
        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            if (!in_array($forecast->user_id, $userIds)) {
                return response()->json([
                    'message' => 'Unauthorized access to this forecast.',
                ], 403);
            }
        }

        return response()->json([
            'data' => $forecast,
        ]);
    }

    /**
     * Generate a new forecast.
     */
    public function generate(ForecastRequest $request): JsonResponse
    {
        try {
            $periodStart = $request->input('period_start')
                ? \Carbon\Carbon::parse($request->input('period_start'))
                : null;

            $forecast = $this->forecastCalculationService->generateForecast(
                $request->input('user_id'),
                $request->input('period_type'),
                $periodStart,
                $request->input('team_id')
            );

            return response()->json([
                'message' => 'Forecast generated successfully.',
                'data' => $forecast,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate forecast: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get forecasts for a specific team.
     */
    public function team(int $teamId)
    {
        if (request()->ajax() || request()->wantsJson()) {
            try {
                $forecasts = $this->salesForecastRepository->getModel()
                    ->with(['user', 'latestActual'])
                    ->where('team_id', $teamId)
                    ->orderBy('period_start', 'desc')
                    ->get();

                // Calculate team totals
                $totals = [
                    'forecast_value' => $forecasts->sum('forecast_value'),
                    'weighted_forecast' => $forecasts->sum('weighted_forecast'),
                    'best_case' => $forecasts->sum('best_case'),
                    'worst_case' => $forecasts->sum('worst_case'),
                    'avg_confidence' => $forecasts->avg('confidence_score'),
                ];

                return response()->json([
                    'data' => $forecasts,
                    'totals' => $totals,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to retrieve team forecasts: ' . $e->getMessage(),
                ], 500);
            }
        }

        return view('admin::leads.forecasts.index');
    }

    /**
     * Get forecast accuracy metrics.
     */
    public function accuracy(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $query = $this->salesForecastRepository->getModel()
                    ->with(['user', 'latestActual'])
                    ->whereHas('latestActual');

                // Filter by user
                if ($userId = $request->query('user_id')) {
                    $query->where('user_id', $userId);
                }

                // Filter by team
                if ($teamId = $request->query('team_id')) {
                    $query->where('team_id', $teamId);
                }

                // Filter by period type
                if ($periodType = $request->query('period_type')) {
                    $query->where('period_type', $periodType);
                }

                // Apply user authorization
                if ($userIds = bouncer()->getAuthorizedUserIds()) {
                    $query->whereIn('user_id', $userIds);
                }

                $forecasts = $query->orderBy('period_start', 'desc')->get();

                // Calculate accuracy metrics
                $metrics = $this->calculateAccuracyMetrics($forecasts);

                return response()->json([
                    'data' => $forecasts,
                    'metrics' => $metrics,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to retrieve accuracy metrics: ' . $e->getMessage(),
                ], 500);
            }
        }

        return view('admin::leads.forecasts.accuracy');
    }

    /**
     * Calculate accuracy metrics from forecasts with actuals.
     *
     * @param  \Illuminate\Support\Collection  $forecasts
     * @return array
     */
    protected function calculateAccuracyMetrics($forecasts): array
    {
        if ($forecasts->isEmpty()) {
            return [
                'total_forecasts' => 0,
                'average_accuracy' => 0,
                'average_variance' => 0,
                'average_variance_pct' => 0,
                'over_forecasted_count' => 0,
                'under_forecasted_count' => 0,
                'accurate_count' => 0,
            ];
        }

        $totalForecasts = $forecasts->count();
        $totalVariance = 0;
        $totalVariancePct = 0;
        $overForecasted = 0;
        $underForecasted = 0;
        $accurate = 0;

        foreach ($forecasts as $forecast) {
            if ($actual = $forecast->latestActual) {
                $variance = (float) $actual->variance;
                $variancePct = (float) $actual->variance_percentage;

                $totalVariance += abs($variance);
                $totalVariancePct += abs($variancePct);

                if ($variance > 0) {
                    $underForecasted++;
                } elseif ($variance < 0) {
                    $overForecasted++;
                }

                // Consider accurate if within 10% variance
                if (abs($variancePct) <= 10) {
                    $accurate++;
                }
            }
        }

        $averageVariance = $totalForecasts > 0 ? $totalVariance / $totalForecasts : 0;
        $averageVariancePct = $totalForecasts > 0 ? $totalVariancePct / $totalForecasts : 0;
        $averageAccuracy = $totalForecasts > 0 ? 100 - $averageVariancePct : 0;

        return [
            'total_forecasts' => $totalForecasts,
            'average_accuracy' => round($averageAccuracy, 2),
            'average_variance' => round($averageVariance, 2),
            'average_variance_pct' => round($averageVariancePct, 2),
            'over_forecasted_count' => $overForecasted,
            'under_forecasted_count' => $underForecasted,
            'accurate_count' => $accurate,
            'accuracy_rate' => $totalForecasts > 0
                ? round(($accurate / $totalForecasts) * 100, 2)
                : 0,
        ];
    }
}
