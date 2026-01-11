<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Webkul\Lead\Contracts\SalesForecast;
use Webkul\Lead\Models\ForecastActualProxy;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\SalesForecastRepository;

class ForecastAccuracyService
{
    /**
     * Accuracy thresholds.
     */
    const HIGHLY_ACCURATE_THRESHOLD = 90.0;
    const MODERATELY_ACCURATE_THRESHOLD = 75.0;
    const POORLY_ACCURATE_THRESHOLD = 60.0;

    /**
     * Variance thresholds.
     */
    const LOW_VARIANCE_THRESHOLD = 10.0;
    const MEDIUM_VARIANCE_THRESHOLD = 25.0;

    /**
     * Minimum sample size for meaningful analysis.
     */
    const MIN_SAMPLE_SIZE = 3;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(
        protected SalesForecastRepository $salesForecastRepository,
        protected LeadRepository $leadRepository
    ) {
    }

    /**
     * Track actual results for a forecast period.
     *
     * @param  int  $forecastId
     * @return \Webkul\Lead\Contracts\ForecastActual
     */
    public function trackActual(int $forecastId)
    {
        $forecast = $this->salesForecastRepository->findOrFail($forecastId);

        // Calculate actual value from closed leads
        $actualValue = $this->calculateActualValue($forecast);

        // Calculate variance
        $variance = $actualValue - $forecast->forecast_value;
        $variancePercentage = $forecast->forecast_value > 0
            ? ($variance / $forecast->forecast_value) * 100
            : 0;

        // Create or update forecast actual record
        $existingActual = ForecastActualProxy::modelClass()::where('forecast_id', $forecastId)
            ->latest('closed_at')
            ->first();

        $actualData = [
            'forecast_id' => $forecastId,
            'actual_value' => $actualValue,
            'variance' => $variance,
            'variance_percentage' => round($variancePercentage, 2),
            'closed_at' => now(),
        ];

        if ($existingActual) {
            $existingActual->update($actualData);

            return $existingActual;
        }

        return ForecastActualProxy::modelClass()::create($actualData);
    }

    /**
     * Calculate actual value from closed leads for a forecast period.
     *
     * @param  SalesForecast  $forecast
     * @return float
     */
    public function calculateActualValue(SalesForecast $forecast): float
    {
        $query = $this->leadRepository->getModel()
            ->whereBetween('closed_at', [$forecast->period_start, $forecast->period_end])
            ->where('status', 0); // Closed leads

        // Filter by user or team
        if ($forecast->user_id) {
            $query->where('user_id', $forecast->user_id);
        } elseif ($forecast->team_id) {
            // Team forecasts - get all users in team
            // Note: This assumes team relationship exists
            // Adjust based on actual team structure
        }

        // Only count won leads
        $wonLeads = $query->whereHas('stage', function ($q) {
            $q->where('code', 'won');
        })->get();

        return round($wonLeads->sum('lead_value'), 2);
    }

    /**
     * Get accuracy metrics for a user.
     *
     * @param  int  $userId
     * @param  int  $days
     * @return array
     */
    public function getUserAccuracyMetrics(int $userId, int $days = 90): array
    {
        $forecasts = $this->getForecastsWithActuals($userId, null, $days);

        if ($forecasts->count() < self::MIN_SAMPLE_SIZE) {
            return [
                'user_id' => $userId,
                'sample_size' => $forecasts->count(),
                'insufficient_data' => true,
                'message' => 'Insufficient data for meaningful analysis',
            ];
        }

        return $this->calculateAccuracyMetrics($forecasts, [
            'user_id' => $userId,
            'days' => $days,
        ]);
    }

    /**
     * Get accuracy metrics for a team.
     *
     * @param  int  $teamId
     * @param  int  $days
     * @return array
     */
    public function getTeamAccuracyMetrics(int $teamId, int $days = 90): array
    {
        $forecasts = $this->getForecastsWithActuals(null, $teamId, $days);

        if ($forecasts->count() < self::MIN_SAMPLE_SIZE) {
            return [
                'team_id' => $teamId,
                'sample_size' => $forecasts->count(),
                'insufficient_data' => true,
                'message' => 'Insufficient data for meaningful analysis',
            ];
        }

        return $this->calculateAccuracyMetrics($forecasts, [
            'team_id' => $teamId,
            'days' => $days,
        ]);
    }

    /**
     * Compare forecast vs actual for a specific forecast.
     *
     * @param  int  $forecastId
     * @return array
     */
    public function compareForecastVsActual(int $forecastId): array
    {
        $forecast = $this->salesForecastRepository->with(['actuals', 'user'])->findOrFail($forecastId);

        $actual = $forecast->latestActual;

        if (!$actual) {
            return [
                'forecast_id' => $forecastId,
                'has_actual' => false,
                'message' => 'No actual data available for this forecast',
            ];
        }

        $scenarioPerformance = $this->evaluateScenarioPerformance($forecast, $actual);
        $insights = $this->generateComparisonInsights($forecast, $actual);

        return [
            'forecast_id' => $forecastId,
            'has_actual' => true,
            'user_id' => $forecast->user_id,
            'team_id' => $forecast->team_id,
            'period_type' => $forecast->period_type,
            'period_start' => $forecast->period_start->format('Y-m-d'),
            'period_end' => $forecast->period_end->format('Y-m-d'),
            'forecast_value' => (float) $forecast->forecast_value,
            'weighted_forecast' => (float) $forecast->weighted_forecast,
            'best_case' => (float) $forecast->best_case,
            'worst_case' => (float) $forecast->worst_case,
            'actual_value' => (float) $actual->actual_value,
            'variance' => (float) $actual->variance,
            'variance_percentage' => (float) $actual->variance_percentage,
            'accuracy_score' => $actual->getAccuracyScore(),
            'accuracy_level' => $actual->getAccuracyLevel(),
            'performance_indicator' => $actual->getPerformanceIndicator(),
            'confidence_score' => (float) $forecast->confidence_score,
            'scenario_performance' => $scenarioPerformance,
            'insights' => $insights,
        ];
    }

    /**
     * Get accuracy trends over time.
     *
     * @param  int|null  $userId
     * @param  int|null  $teamId
     * @param  int  $months
     * @return array
     */
    public function getAccuracyTrends(?int $userId = null, ?int $teamId = null, int $months = 6): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();

            $forecasts = $this->getForecastsWithActualsInRange($userId, $teamId, $startDate, $endDate);

            if ($forecasts->isEmpty()) {
                continue;
            }

            $accuracyScores = $forecasts->map(function ($forecast) {
                $actual = $forecast->latestActual;

                return $actual ? $actual->getAccuracyScore() : null;
            })->filter();

            $variances = $forecasts->map(function ($forecast) {
                $actual = $forecast->latestActual;

                return $actual ? abs((float) $actual->variance_percentage) : null;
            })->filter();

            $trends[] = [
                'period' => $startDate->format('Y-m'),
                'forecasts_count' => $forecasts->count(),
                'average_accuracy' => $accuracyScores->isNotEmpty() ? round($accuracyScores->avg(), 2) : 0,
                'average_variance' => $variances->isNotEmpty() ? round($variances->avg(), 2) : 0,
                'high_accuracy_count' => $forecasts->filter(function ($f) {
                    $actual = $f->latestActual;

                    return $actual && $actual->isHighlyAccurate();
                })->count(),
                'poor_accuracy_count' => $forecasts->filter(function ($f) {
                    $actual = $f->latestActual;

                    return $actual && $actual->isPoorlyAccurate();
                })->count(),
            ];
        }

        return $trends;
    }

    /**
     * Generate improvement suggestions based on accuracy analysis.
     *
     * @param  int|null  $userId
     * @param  int|null  $teamId
     * @param  int  $days
     * @return array
     */
    public function getImprovementSuggestions(?int $userId = null, ?int $teamId = null, int $days = 90): array
    {
        $forecasts = $this->getForecastsWithActuals($userId, $teamId, $days);

        if ($forecasts->count() < self::MIN_SAMPLE_SIZE) {
            return [
                'suggestions' => [
                    [
                        'type' => 'insufficient_data',
                        'priority' => 'high',
                        'title' => 'Generate More Forecasts',
                        'description' => 'Create at least ' . self::MIN_SAMPLE_SIZE . ' forecasts to enable meaningful accuracy analysis.',
                        'action' => 'Continue generating forecasts for upcoming periods.',
                    ],
                ],
            ];
        }

        $suggestions = [];
        $metrics = $this->calculateAccuracyMetrics($forecasts);

        // Analyze overall accuracy
        if ($metrics['average_accuracy'] < self::POORLY_ACCURATE_THRESHOLD) {
            $suggestions[] = [
                'type' => 'low_accuracy',
                'priority' => 'high',
                'title' => 'Improve Overall Forecast Accuracy',
                'description' => 'Your average accuracy is ' . round($metrics['average_accuracy'], 1) . '%. Consider reviewing your forecasting methodology.',
                'action' => 'Review historical conversion rates and update stage probabilities.',
            ];
        }

        // Analyze variance patterns
        $overestimateCount = $forecasts->filter(function ($f) {
            $actual = $f->latestActual;

            return $actual && $actual->fellShort();
        })->count();

        $underestimateCount = $forecasts->filter(function ($f) {
            $actual = $f->latestActual;

            return $actual && $actual->exceededForecast();
        })->count();

        $totalForecasts = $forecasts->count();
        $overestimateRate = ($overestimateCount / $totalForecasts) * 100;
        $underestimateRate = ($underestimateCount / $totalForecasts) * 100;

        // Consistent overestimation
        if ($overestimateRate > 60) {
            $suggestions[] = [
                'type' => 'overestimation',
                'priority' => 'high',
                'title' => 'Consistent Overestimation Pattern',
                'description' => 'You overestimate in ' . round($overestimateRate, 1) . '% of forecasts.',
                'action' => 'Consider reducing stage probabilities or using more conservative conversion rates.',
            ];
        }

        // Consistent underestimation
        if ($underestimateRate > 60) {
            $suggestions[] = [
                'type' => 'underestimation',
                'priority' => 'medium',
                'title' => 'Consistent Underestimation Pattern',
                'description' => 'You underestimate in ' . round($underestimateRate, 1) . '% of forecasts.',
                'action' => 'Your forecasts are conservative. Consider increasing confidence in stage probabilities.',
            ];
        }

        // High variance
        if ($metrics['average_variance_percentage'] > self::MEDIUM_VARIANCE_THRESHOLD) {
            $suggestions[] = [
                'type' => 'high_variance',
                'priority' => 'high',
                'title' => 'High Forecast Variance',
                'description' => 'Average variance is ' . round($metrics['average_variance_percentage'], 1) . '%. This indicates unpredictable forecasting.',
                'action' => 'Focus on deals with expected close dates and review pipeline health regularly.',
            ];
        }

        // Low confidence correlation
        $lowConfidenceForecasts = $forecasts->filter(fn($f) => $f->confidence_score < 60);
        if ($lowConfidenceForecasts->count() > ($totalForecasts * 0.5)) {
            $suggestions[] = [
                'type' => 'low_confidence',
                'priority' => 'medium',
                'title' => 'Low Confidence Scores',
                'description' => 'More than half of your forecasts have low confidence scores.',
                'action' => 'Increase deal pipeline size and ensure leads have expected close dates.',
            ];
        }

        // Scenario spread analysis
        $avgSpread = $forecasts->avg(fn($f) => $f->getScenarioSpreadPercentage());
        if ($avgSpread > 100) {
            $suggestions[] = [
                'type' => 'wide_scenarios',
                'priority' => 'low',
                'title' => 'Wide Scenario Spread',
                'description' => 'Large gap between best and worst case scenarios indicates uncertainty.',
                'action' => 'Focus on qualifying leads better to reduce uncertainty in pipeline.',
            ];
        }

        // Positive feedback for good accuracy
        if ($metrics['average_accuracy'] >= self::HIGHLY_ACCURATE_THRESHOLD) {
            $suggestions[] = [
                'type' => 'excellent_performance',
                'priority' => 'info',
                'title' => 'Excellent Forecast Accuracy',
                'description' => 'Your average accuracy is ' . round($metrics['average_accuracy'], 1) . '%. Keep up the great work!',
                'action' => 'Continue using current forecasting methodology.',
            ];
        }

        return [
            'suggestions' => $suggestions,
            'summary' => [
                'total_suggestions' => count($suggestions),
                'high_priority' => count(array_filter($suggestions, fn($s) => $s['priority'] === 'high')),
                'medium_priority' => count(array_filter($suggestions, fn($s) => $s['priority'] === 'medium')),
                'low_priority' => count(array_filter($suggestions, fn($s) => $s['priority'] === 'low')),
            ],
        ];
    }

    /**
     * Get forecasts with actuals.
     *
     * @param  int|null  $userId
     * @param  int|null  $teamId
     * @param  int  $days
     * @return Collection
     */
    protected function getForecastsWithActuals(?int $userId = null, ?int $teamId = null, int $days = 90): Collection
    {
        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        return $this->getForecastsWithActualsInRange($userId, $teamId, $startDate, $endDate);
    }

    /**
     * Get forecasts with actuals in a specific date range.
     *
     * @param  int|null  $userId
     * @param  int|null  $teamId
     * @param  string|Carbon  $startDate
     * @param  string|Carbon  $endDate
     * @return Collection
     */
    protected function getForecastsWithActualsInRange(?int $userId, ?int $teamId, $startDate, $endDate): Collection
    {
        $query = $this->salesForecastRepository->getModel()
            ->with(['actuals', 'latestActual', 'user'])
            ->whereHas('actuals')
            ->whereBetween('period_start', [
                $startDate instanceof Carbon ? $startDate->format('Y-m-d') : $startDate,
                $endDate instanceof Carbon ? $endDate->format('Y-m-d') : $endDate,
            ]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        return $query->orderBy('period_start', 'desc')->get();
    }

    /**
     * Calculate accuracy metrics for a collection of forecasts.
     *
     * @param  Collection  $forecasts
     * @param  array  $context
     * @return array
     */
    protected function calculateAccuracyMetrics(Collection $forecasts, array $context = []): array
    {
        $accuracyScores = $forecasts->map(function ($forecast) {
            $actual = $forecast->latestActual;

            return $actual ? $actual->getAccuracyScore() : null;
        })->filter();

        $variances = $forecasts->map(function ($forecast) {
            $actual = $forecast->latestActual;

            return $actual ? (float) $actual->variance_percentage : null;
        })->filter();

        $absVariances = $forecasts->map(function ($forecast) {
            $actual = $forecast->latestActual;

            return $actual ? abs((float) $actual->variance_percentage) : null;
        })->filter();

        return array_merge($context, [
            'sample_size' => $forecasts->count(),
            'average_accuracy' => $accuracyScores->isNotEmpty() ? round($accuracyScores->avg(), 2) : 0,
            'min_accuracy' => $accuracyScores->isNotEmpty() ? round($accuracyScores->min(), 2) : 0,
            'max_accuracy' => $accuracyScores->isNotEmpty() ? round($accuracyScores->max(), 2) : 0,
            'average_variance_percentage' => $absVariances->isNotEmpty() ? round($absVariances->avg(), 2) : 0,
            'average_signed_variance' => $variances->isNotEmpty() ? round($variances->avg(), 2) : 0,
            'high_accuracy_count' => $forecasts->filter(function ($f) {
                $actual = $f->latestActual;

                return $actual && $actual->isHighlyAccurate();
            })->count(),
            'moderate_accuracy_count' => $forecasts->filter(function ($f) {
                $actual = $f->latestActual;

                return $actual && $actual->isModeratelyAccurate();
            })->count(),
            'poor_accuracy_count' => $forecasts->filter(function ($f) {
                $actual = $f->latestActual;

                return $actual && $actual->isPoorlyAccurate();
            })->count(),
            'exceeded_count' => $forecasts->filter(function ($f) {
                $actual = $f->latestActual;

                return $actual && $actual->exceededForecast();
            })->count(),
            'fell_short_count' => $forecasts->filter(function ($f) {
                $actual = $f->latestActual;

                return $actual && $actual->fellShort();
            })->count(),
            'on_target_count' => $forecasts->filter(function ($f) {
                $actual = $f->latestActual;

                return $actual && $actual->matchedForecast();
            })->count(),
            'performance_level' => $this->getPerformanceLevel($accuracyScores->isNotEmpty() ? $accuracyScores->avg() : 0),
        ]);
    }

    /**
     * Evaluate scenario performance.
     *
     * @param  SalesForecast  $forecast
     * @param  mixed  $actual
     * @return array
     */
    protected function evaluateScenarioPerformance(SalesForecast $forecast, $actual): array
    {
        $actualValue = (float) $actual->actual_value;

        return [
            'within_best_worst_range' => $actualValue >= $forecast->worst_case && $actualValue <= $forecast->best_case,
            'exceeded_best_case' => $actualValue > $forecast->best_case,
            'below_worst_case' => $actualValue < $forecast->worst_case,
            'closest_scenario' => $this->getClosestScenario($forecast, $actualValue),
        ];
    }

    /**
     * Get the closest scenario to actual value.
     *
     * @param  SalesForecast  $forecast
     * @param  float  $actualValue
     * @return string
     */
    protected function getClosestScenario(SalesForecast $forecast, float $actualValue): string
    {
        $scenarios = [
            'weighted' => abs($actualValue - $forecast->weighted_forecast),
            'best' => abs($actualValue - $forecast->best_case),
            'worst' => abs($actualValue - $forecast->worst_case),
        ];

        return array_search(min($scenarios), $scenarios);
    }

    /**
     * Generate comparison insights.
     *
     * @param  SalesForecast  $forecast
     * @param  mixed  $actual
     * @return array
     */
    protected function generateComparisonInsights(SalesForecast $forecast, $actual): array
    {
        $insights = [];

        // Variance insight
        if ($actual->isHighlyAccurate()) {
            $insights[] = 'Highly accurate forecast - variance within 10%';
        } elseif ($actual->isPoorlyAccurate()) {
            $insights[] = 'Poor forecast accuracy - variance exceeds 25%';
        }

        // Performance insight
        if ($actual->exceededForecast()) {
            $insights[] = 'Actual results exceeded forecast by ' . round(abs((float) $actual->variance_percentage), 1) . '%';
        } elseif ($actual->fellShort()) {
            $insights[] = 'Actual results fell short of forecast by ' . round(abs((float) $actual->variance_percentage), 1) . '%';
        }

        // Confidence correlation
        if ($forecast->confidence_score >= 80 && $actual->isHighlyAccurate()) {
            $insights[] = 'High confidence forecast validated by accurate results';
        } elseif ($forecast->confidence_score < 50 && $actual->isPoorlyAccurate()) {
            $insights[] = 'Low confidence forecast reflected in poor accuracy';
        }

        // Scenario insight
        $scenarioPerf = $this->evaluateScenarioPerformance($forecast, $actual);
        if ($scenarioPerf['exceeded_best_case']) {
            $insights[] = 'Exceptional performance - exceeded best case scenario';
        } elseif ($scenarioPerf['below_worst_case']) {
            $insights[] = 'Underperformance - below worst case scenario';
        }

        return $insights;
    }

    /**
     * Get performance level based on average accuracy.
     *
     * @param  float  $averageAccuracy
     * @return string
     */
    protected function getPerformanceLevel(float $averageAccuracy): string
    {
        if ($averageAccuracy >= self::HIGHLY_ACCURATE_THRESHOLD) {
            return 'excellent';
        }

        if ($averageAccuracy >= self::MODERATELY_ACCURATE_THRESHOLD) {
            return 'good';
        }

        if ($averageAccuracy >= self::POORLY_ACCURATE_THRESHOLD) {
            return 'average';
        }

        return 'needs_improvement';
    }
}
