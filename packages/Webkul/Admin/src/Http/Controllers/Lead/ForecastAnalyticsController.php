<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Repositories\SalesForecastRepository;
use Webkul\Lead\Services\ForecastCalculationService;
use Webkul\Lead\Services\HistoricalAnalysisService;

class ForecastAnalyticsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected SalesForecastRepository $salesForecastRepository,
        protected ForecastCalculationService $forecastCalculationService,
        protected HistoricalAnalysisService $historicalAnalysisService
    ) {
    }

    /**
     * Get forecast trends over time.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function trends(Request $request): JsonResponse
    {
        try {
            $userId = $request->query('user_id');
            $pipelineId = $request->query('pipeline_id');
            $months = $request->query('months', 6);

            // Validate months parameter
            if ($months < 1 || $months > 24) {
                return response()->json([
                    'message' => 'Months parameter must be between 1 and 24.',
                ], 400);
            }

            // Apply user authorization
            if ($userId && ($userIds = bouncer()->getAuthorizedUserIds())) {
                if (!in_array($userId, $userIds)) {
                    return response()->json([
                        'message' => 'Unauthorized access to user data.',
                    ], 403);
                }
            }

            // Get performance trends from historical analysis
            $performanceTrends = $this->historicalAnalysisService->getPerformanceTrends(
                $userId,
                $pipelineId,
                (int) $months
            );

            // Get forecast trends
            $forecastTrends = $this->getForecastTrends($userId, $pipelineId, (int) $months);

            // Combine data for comprehensive trends
            $trends = $this->combineTrends($performanceTrends, $forecastTrends);

            // Calculate trend analysis
            $trendAnalysis = $this->calculateTrendAnalysis($trends);

            return response()->json([
                'data' => [
                    'trends' => $trends,
                    'analysis' => $trendAnalysis,
                    'period' => [
                        'months' => $months,
                        'start_date' => now()->subMonths($months - 1)->startOfMonth()->format('Y-m-d'),
                        'end_date' => now()->endOfMonth()->format('Y-m-d'),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve forecast trends: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get scenario modeling and comparison.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    /**
     * Get scenario modeling and comparison.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function scenarios(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $userId = $request->query('user_id');
                $periodType = $request->query('period_type', 'month');
                $pipelineId = $request->query('pipeline_id');

                // Validate period type
                if (!in_array($periodType, ['week', 'month', 'quarter'])) {
                    return response()->json([
                        'message' => 'Invalid period type. Must be week, month, or quarter.',
                    ], 400);
                }

                // Apply user authorization
                if ($userId) {
                    if ($userIds = bouncer()->getAuthorizedUserIds()) {
                        if (!in_array($userId, $userIds)) {
                            return response()->json([
                                'message' => 'Unauthorized access to user data.',
                            ], 403);
                        }
                    }
                } else {
                    return response()->json([
                        'message' => 'User ID is required for scenario modeling.',
                    ], 400);
                }

                // Get current open leads for the user
                $leads = $this->getOpenLeadsForUser($userId, $pipelineId);

                if ($leads->isEmpty()) {
                    return response()->json([
                        'data' => [
                            'scenarios' => [
                                'weighted' => ['value' => 0, 'description' => 'Most likely outcome based on stage probabilities'],
                                'best_case' => ['value' => 0, 'description' => 'All deals close successfully'],
                                'worst_case' => ['value' => 0, 'description' => 'Pessimistic scenario based on historical rates'],
                            ],
                            'scenario_comparison' => [],
                            'recommendations' => ['No open leads available for scenario modeling.'],
                        ],
                    ]);
                }

                // Get forecast scenarios
                $scenarios = $this->forecastCalculationService->getForecastScenarios($leads);

                // Calculate scenario spreads
                $scenarioComparison = $this->calculateScenarioComparison($scenarios);

                // Get recommendations
                $recommendations = $this->getScenarioRecommendations($scenarios, $leads);

                return response()->json([
                    'data' => [
                        'scenarios' => $scenarios,
                        'scenario_comparison' => $scenarioComparison,
                        'lead_breakdown' => [
                            'total_leads' => $leads->count(),
                            'total_value' => $leads->sum('lead_value'),
                            'average_value' => $leads->avg('lead_value'),
                        ],
                        'recommendations' => $recommendations,
                        'period_type' => $periodType,
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to retrieve forecast scenarios: ' . $e->getMessage(),
                ], 500);
            }
        }

        return view('admin::leads.forecasts.scenarios');
    }

    /**
     * Compare forecasts vs actuals.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function comparison(Request $request): JsonResponse
    {
        try {
            $userId = $request->query('user_id');
            $teamId = $request->query('team_id');
            $periodType = $request->query('period_type');
            $limit = $request->query('limit', 10);

            // Apply user authorization
            if ($userId && ($userIds = bouncer()->getAuthorizedUserIds())) {
                if (!in_array($userId, $userIds)) {
                    return response()->json([
                        'message' => 'Unauthorized access to user data.',
                    ], 403);
                }
            }

            // Build query for forecasts with actuals
            $query = $this->salesForecastRepository->getModel()
                ->with(['user', 'latestActual'])
                ->whereHas('latestActual');

            // Apply filters
            if ($userId) {
                $query->where('user_id', $userId);
            }

            if ($teamId) {
                $query->where('team_id', $teamId);
            }

            if ($periodType) {
                $query->where('period_type', $periodType);
            }

            // Apply user authorization filter
            if (!$userId && ($userIds = bouncer()->getAuthorizedUserIds())) {
                $query->whereIn('user_id', $userIds);
            }

            // Order by period start descending
            $query->orderBy('period_start', 'desc');

            // Limit results
            $forecasts = $query->limit($limit)->get();

            // Calculate comparison metrics
            $comparisonData = $this->calculateComparisonMetrics($forecasts);

            // Get accuracy insights
            $accuracyInsights = $this->getAccuracyInsights($forecasts);

            return response()->json([
                'data' => [
                    'forecasts' => $forecasts,
                    'comparison_metrics' => $comparisonData,
                    'accuracy_insights' => $accuracyInsights,
                    'period_type' => $periodType,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve forecast comparison: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get forecast trends from historical forecast data.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  int  $months
     * @return array
     */
    protected function getForecastTrends(?int $userId, ?int $pipelineId, int $months): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();

            $query = $this->salesForecastRepository->getModel()
                ->where('period_start', '>=', $startDate)
                ->where('period_start', '<=', $endDate);

            if ($userId) {
                $query->where('user_id', $userId);
            }

            if ($pipelineId) {
                // Note: This assumes pipeline filtering is done through leads
                // Adjust if needed based on actual schema
            }

            $forecasts = $query->get();

            $trends[] = [
                'period' => $startDate->format('Y-m'),
                'forecast_count' => $forecasts->count(),
                'total_forecast' => round($forecasts->sum('forecast_value'), 2),
                'total_weighted' => round($forecasts->sum('weighted_forecast'), 2),
                'total_best_case' => round($forecasts->sum('best_case'), 2),
                'total_worst_case' => round($forecasts->sum('worst_case'), 2),
                'avg_confidence' => round($forecasts->avg('confidence_score'), 2),
            ];
        }

        return $trends;
    }

    /**
     * Combine performance trends with forecast trends.
     *
     * @param  array  $performanceTrends
     * @param  array  $forecastTrends
     * @return array
     */
    protected function combineTrends(array $performanceTrends, array $forecastTrends): array
    {
        $combined = [];

        foreach ($performanceTrends as $perfTrend) {
            $period = $perfTrend['period'];
            $forecastTrend = collect($forecastTrends)->firstWhere('period', $period) ?? [];

            $combined[] = array_merge($perfTrend, $forecastTrend);
        }

        return $combined;
    }

    /**
     * Calculate trend analysis.
     *
     * @param  array  $trends
     * @return array
     */
    protected function calculateTrendAnalysis(array $trends): array
    {
        if (empty($trends)) {
            return [
                'direction' => 'neutral',
                'growth_rate' => 0,
                'volatility' => 'low',
                'summary' => 'No trend data available.',
            ];
        }

        $recentPeriods = array_slice($trends, -3);
        $wonValues = array_column($recentPeriods, 'total_won_value');
        $forecastValues = array_column($recentPeriods, 'total_forecast');

        // Calculate growth rate
        if (count($wonValues) >= 2 && $wonValues[0] > 0) {
            $growthRate = (($wonValues[count($wonValues) - 1] - $wonValues[0]) / $wonValues[0]) * 100;
        } else {
            $growthRate = 0;
        }

        // Determine direction
        $direction = $growthRate > 5 ? 'upward' : ($growthRate < -5 ? 'downward' : 'stable');

        // Calculate volatility
        $volatility = $this->calculateVolatility($wonValues);

        // Generate summary
        $summary = $this->generateTrendSummary($direction, $growthRate, $volatility);

        return [
            'direction' => $direction,
            'growth_rate' => round($growthRate, 2),
            'volatility' => $volatility,
            'summary' => $summary,
            'recent_periods_analyzed' => count($recentPeriods),
        ];
    }

    /**
     * Calculate volatility from values.
     *
     * @param  array  $values
     * @return string
     */
    protected function calculateVolatility(array $values): string
    {
        if (count($values) < 2) {
            return 'low';
        }

        $mean = array_sum($values) / count($values);
        if ($mean == 0) {
            return 'low';
        }

        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / count($values);
        $coefficientOfVariation = sqrt($variance) / $mean;

        if ($coefficientOfVariation > 0.3) {
            return 'high';
        } elseif ($coefficientOfVariation > 0.15) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Generate trend summary.
     *
     * @param  string  $direction
     * @param  float  $growthRate
     * @param  string  $volatility
     * @return string
     */
    protected function generateTrendSummary(string $direction, float $growthRate, string $volatility): string
    {
        $summaries = [
            'upward' => sprintf('Performance is trending upward with %.2f%% growth. ', abs($growthRate)),
            'downward' => sprintf('Performance is trending downward with %.2f%% decline. ', abs($growthRate)),
            'stable' => 'Performance is relatively stable. ',
        ];

        $volatilitySummaries = [
            'high' => 'High volatility detected - results vary significantly between periods.',
            'medium' => 'Moderate volatility - some fluctuation in results.',
            'low' => 'Low volatility - consistent performance across periods.',
        ];

        return ($summaries[$direction] ?? '') . ($volatilitySummaries[$volatility] ?? '');
    }

    /**
     * Get open leads for a user.
     *
     * @param  int  $userId
     * @param  int|null  $pipelineId
     * @return \Illuminate\Support\Collection
     */
    protected function getOpenLeadsForUser(int $userId, ?int $pipelineId = null)
    {
        $query = app(\Webkul\Lead\Repositories\LeadRepository::class)->getModel()
            ->with(['stage', 'pipeline'])
            ->where('user_id', $userId)
            ->where('status', 1); // Open leads

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        return $query->get();
    }

    /**
     * Calculate scenario comparison metrics.
     *
     * @param  array  $scenarios
     * @return array
     */
    protected function calculateScenarioComparison(array $scenarios): array
    {
        $weighted = $scenarios['weighted']['value'] ?? 0;
        $bestCase = $scenarios['best_case']['value'] ?? 0;
        $worstCase = $scenarios['worst_case']['value'] ?? 0;

        $upside = $bestCase - $weighted;
        $downside = $weighted - $worstCase;
        $spread = $bestCase - $worstCase;

        return [
            'upside_potential' => round($upside, 2),
            'downside_risk' => round($downside, 2),
            'total_spread' => round($spread, 2),
            'upside_percentage' => $weighted > 0 ? round(($upside / $weighted) * 100, 2) : 0,
            'downside_percentage' => $weighted > 0 ? round(($downside / $weighted) * 100, 2) : 0,
            'risk_reward_ratio' => $downside > 0 ? round($upside / $downside, 2) : 0,
        ];
    }

    /**
     * Get scenario recommendations.
     *
     * @param  array  $scenarios
     * @param  \Illuminate\Support\Collection  $leads
     * @return array
     */
    protected function getScenarioRecommendations(array $scenarios, $leads): array
    {
        $recommendations = [];

        $weighted = $scenarios['weighted']['value'] ?? 0;
        $bestCase = $scenarios['best_case']['value'] ?? 0;
        $worstCase = $scenarios['worst_case']['value'] ?? 0;

        // Analyze upside potential
        $upsidePct = $weighted > 0 ? (($bestCase - $weighted) / $weighted) * 100 : 0;
        if ($upsidePct > 50) {
            $recommendations[] = 'High upside potential detected. Focus on closing high-value deals to maximize outcomes.';
        }

        // Analyze downside risk
        $downsidePct = $weighted > 0 ? (($weighted - $worstCase) / $weighted) * 100 : 0;
        if ($downsidePct > 40) {
            $recommendations[] = 'Significant downside risk present. Prioritize qualifying leads and improving conversion rates.';
        }

        // Analyze lead distribution
        $avgValue = $leads->avg('lead_value');
        $highValueLeads = $leads->filter(fn($l) => $l->lead_value > $avgValue * 2)->count();
        if ($highValueLeads > 0) {
            $recommendations[] = sprintf('You have %d high-value leads. Focus efforts on these opportunities.', $highValueLeads);
        }

        // Default recommendation
        if (empty($recommendations)) {
            $recommendations[] = 'Maintain current strategy and monitor lead progression through pipeline stages.';
        }

        return $recommendations;
    }

    /**
     * Calculate comparison metrics between forecasts and actuals.
     *
     * @param  \Illuminate\Support\Collection  $forecasts
     * @return array
     */
    protected function calculateComparisonMetrics($forecasts): array
    {
        if ($forecasts->isEmpty()) {
            return [
                'total_forecasts' => 0,
                'average_accuracy' => 0,
                'total_variance' => 0,
                'over_forecasted_count' => 0,
                'under_forecasted_count' => 0,
                'within_10_pct_count' => 0,
            ];
        }

        $totalVariance = 0;
        $totalVariancePct = 0;
        $overForecasted = 0;
        $underForecasted = 0;
        $within10Pct = 0;

        foreach ($forecasts as $forecast) {
            if ($actual = $forecast->latestActual) {
                $variance = (float) $actual->variance;
                $variancePct = (float) $actual->variance_percentage;

                $totalVariance += $variance;
                $totalVariancePct += abs($variancePct);

                if ($variance > 0) {
                    $underForecasted++;
                } elseif ($variance < 0) {
                    $overForecasted++;
                }

                if (abs($variancePct) <= 10) {
                    $within10Pct++;
                }
            }
        }

        $count = $forecasts->count();
        $avgVariancePct = $count > 0 ? $totalVariancePct / $count : 0;

        return [
            'total_forecasts' => $count,
            'average_accuracy' => round(100 - $avgVariancePct, 2),
            'total_variance' => round($totalVariance, 2),
            'average_variance_pct' => round($avgVariancePct, 2),
            'over_forecasted_count' => $overForecasted,
            'under_forecasted_count' => $underForecasted,
            'within_10_pct_count' => $within10Pct,
            'accuracy_rate' => $count > 0 ? round(($within10Pct / $count) * 100, 2) : 0,
        ];
    }

    /**
     * Get accuracy insights from forecast comparisons.
     *
     * @param  \Illuminate\Support\Collection  $forecasts
     * @return array
     */
    protected function getAccuracyInsights($forecasts): array
    {
        if ($forecasts->isEmpty()) {
            return [
                'overall_trend' => 'No data available',
                'recommendations' => [],
            ];
        }

        $insights = [];
        $metrics = $this->calculateComparisonMetrics($forecasts);

        // Overall accuracy insight
        if ($metrics['average_accuracy'] >= 90) {
            $insights[] = 'Excellent forecasting accuracy - your predictions are highly reliable.';
        } elseif ($metrics['average_accuracy'] >= 75) {
            $insights[] = 'Good forecasting accuracy - minor adjustments may improve predictions.';
        } elseif ($metrics['average_accuracy'] >= 60) {
            $insights[] = 'Moderate forecasting accuracy - review historical patterns and adjust assumptions.';
        } else {
            $insights[] = 'Forecasting accuracy needs improvement - consider revising methodology.';
        }

        // Over/under forecasting pattern
        if ($metrics['over_forecasted_count'] > $metrics['under_forecasted_count'] * 2) {
            $insights[] = 'Consistent over-forecasting detected. Consider adjusting stage probabilities downward.';
        } elseif ($metrics['under_forecasted_count'] > $metrics['over_forecasted_count'] * 2) {
            $insights[] = 'Consistent under-forecasting detected. You may be too conservative in predictions.';
        } else {
            $insights[] = 'Balanced forecasting pattern - no significant bias detected.';
        }

        return [
            'overall_trend' => $insights[0] ?? 'No trend data available',
            'recommendations' => array_slice($insights, 1),
            'accuracy_score' => $metrics['average_accuracy'],
        ];
    }
}
