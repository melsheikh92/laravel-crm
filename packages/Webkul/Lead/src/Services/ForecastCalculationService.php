<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\SalesForecastRepository;

class ForecastCalculationService
{
    /**
     * Default confidence thresholds.
     */
    const HIGH_CONFIDENCE_THRESHOLD = 80.0;
    const MEDIUM_CONFIDENCE_THRESHOLD = 60.0;

    /**
     * Minimum sample size for statistical significance.
     */
    const MIN_SAMPLE_SIZE = 10;

    /**
     * Cache prefix for forecasts.
     */
    const CACHE_PREFIX = 'forecast:';

    /**
     * Cache TTL in seconds (10 minutes).
     */
    const CACHE_TTL = 600;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(
        protected LeadRepository $leadRepository,
        protected SalesForecastRepository $salesForecastRepository,
        protected HistoricalConversionRepository $historicalConversionRepository
    ) {
    }

    /**
     * Generate forecast for a specific user and period.
     *
     * @param  int  $userId
     * @param  string  $periodType
     * @param  Carbon|null  $periodStart
     * @param  int|null  $teamId
     * @return \Webkul\Lead\Contracts\SalesForecast
     */
    public function generateForecast(
        int $userId,
        string $periodType,
        ?Carbon $periodStart = null,
        ?int $teamId = null
    ) {
        $periodStart = $periodStart ?? $this->getPeriodStart($periodType);
        $periodEnd = $this->getPeriodEnd($periodType, $periodStart);

        // Get all open leads for the user within the period
        $leads = $this->getLeadsForForecast($userId, $periodStart, $periodEnd, $teamId);

        // Calculate forecasts
        $weightedForecast = $this->calculateWeightedForecast($leads);
        $bestCase = $this->calculateBestCase($leads);
        $worstCase = $this->calculateWorstCase($leads);
        $forecastValue = $weightedForecast; // Primary forecast is weighted

        // Calculate confidence score
        $confidenceScore = $this->calculateConfidenceScore($leads, $userId);

        // Build metadata
        $metadata = $this->buildMetadata($leads, $periodType);

        // Create or update forecast
        $existingForecast = $this->salesForecastRepository->findByUserAndPeriod(
            $userId,
            $periodType,
            $periodStart->format('Y-m-d')
        );

        $forecastData = [
            'user_id' => $userId,
            'team_id' => $teamId,
            'period_type' => $periodType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'forecast_value' => $forecastValue,
            'weighted_forecast' => $weightedForecast,
            'best_case' => $bestCase,
            'worst_case' => $worstCase,
            'confidence_score' => $confidenceScore,
            'metadata' => $metadata,
        ];

        if ($existingForecast) {
            $this->salesForecastRepository->update($forecastData, $existingForecast->id);

            // Clear cache for this user/period
            $this->clearForecastCache($userId, $periodType, $periodStart->format('Y-m-d'));

            return $this->salesForecastRepository->find($existingForecast->id);
        }

        $forecast = $this->salesForecastRepository->create($forecastData);

        // Clear cache for this user/period
        $this->clearForecastCache($userId, $periodType, $periodStart->format('Y-m-d'));

        return $forecast;
    }

    /**
     * Generate forecast for a team and period.
     *
     * @param  int  $teamId
     * @param  string  $periodType
     * @param  Carbon|null  $periodStart
     * @return \Webkul\Lead\Contracts\SalesForecast
     */
    public function generateTeamForecast(
        int $teamId,
        string $periodType,
        ?Carbon $periodStart = null
    ) {
        $periodStart = $periodStart ?? $this->getPeriodStart($periodType);
        $periodEnd = $this->getPeriodEnd($periodType, $periodStart);

        // Get all open leads for the team within the period
        $leads = $this->getLeadsForTeamForecast($teamId, $periodStart, $periodEnd);

        // Calculate forecasts
        $weightedForecast = $this->calculateWeightedForecast($leads);
        $bestCase = $this->calculateBestCase($leads);
        $worstCase = $this->calculateWorstCase($leads);
        $forecastValue = $weightedForecast;

        // Calculate team confidence score
        $confidenceScore = $this->calculateTeamConfidenceScore($leads, $teamId);

        // Build metadata
        $metadata = $this->buildMetadata($leads, $periodType, true);

        // Create or update forecast
        $existingForecast = $this->salesForecastRepository->findByTeamAndPeriod(
            $teamId,
            $periodType,
            $periodStart->format('Y-m-d')
        );

        $forecastData = [
            'user_id' => null,
            'team_id' => $teamId,
            'period_type' => $periodType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'forecast_value' => $forecastValue,
            'weighted_forecast' => $weightedForecast,
            'best_case' => $bestCase,
            'worst_case' => $worstCase,
            'confidence_score' => $confidenceScore,
            'metadata' => $metadata,
        ];

        if ($existingForecast) {
            $this->salesForecastRepository->update($forecastData, $existingForecast->id);

            // Clear cache for this team/period
            $this->clearTeamForecastCache($teamId, $periodType, $periodStart->format('Y-m-d'));

            return $this->salesForecastRepository->find($existingForecast->id);
        }

        $forecast = $this->salesForecastRepository->create($forecastData);

        // Clear cache for this team/period
        $this->clearTeamForecastCache($teamId, $periodType, $periodStart->format('Y-m-d'));

        return $forecast;
    }

    /**
     * Calculate weighted forecast.
     * Formula: Sum of (deal_value × stage_probability) for all open deals
     *
     * @param  Collection  $leads
     * @return float
     */
    public function calculateWeightedForecast(Collection $leads): float
    {
        return $leads->reduce(function ($carry, $lead) {
            $value = (float) ($lead->lead_value ?? 0);
            $probability = $this->getStageProbability($lead);

            return $carry + ($value * $probability / 100);
        }, 0.0);
    }

    /**
     * Calculate best case scenario.
     * Formula: Sum of all deal values (100% close assumption)
     *
     * @param  Collection  $leads
     * @return float
     */
    public function calculateBestCase(Collection $leads): float
    {
        return $leads->sum(fn($lead) => (float) ($lead->lead_value ?? 0));
    }

    /**
     * Calculate worst case scenario.
     * Formula: Sum of (deal_value × historical_conversion_rate)
     *
     * @param  Collection  $leads
     * @return float
     */
    public function calculateWorstCase(Collection $leads): float
    {
        return $leads->reduce(function ($carry, $lead) {
            $value = (float) ($lead->lead_value ?? 0);
            $historicalRate = $this->getHistoricalConversionRate($lead);

            // Use historical rate if available, otherwise use a pessimistic 50% of stage probability
            $conversionRate = $historicalRate ?? ($this->getStageProbability($lead) * 0.5);

            return $carry + ($value * $conversionRate / 100);
        }, 0.0);
    }

    /**
     * Calculate confidence score for forecast.
     *
     * @param  Collection  $leads
     * @param  int  $userId
     * @return float
     */
    protected function calculateConfidenceScore(Collection $leads, int $userId): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        $factors = [];

        // Factor 1: Sample size (30% weight)
        // More leads = higher confidence
        $sampleSizeFactor = min(100, ($leads->count() / self::MIN_SAMPLE_SIZE) * 100);
        $factors['sample_size'] = $sampleSizeFactor * 0.30;

        // Factor 2: Historical data availability (25% weight)
        $leadsWithHistoricalData = $leads->filter(function ($lead) {
            return $this->getHistoricalConversionRate($lead) !== null;
        });
        $historicalDataFactor = $leads->count() > 0
            ? ($leadsWithHistoricalData->count() / $leads->count()) * 100
            : 0;
        $factors['historical_data'] = $historicalDataFactor * 0.25;

        // Factor 3: Average stage probability (20% weight)
        // Higher stage probabilities = higher confidence
        $avgStageProbability = $leads->avg(fn($lead) => $this->getStageProbability($lead));
        $factors['stage_probability'] = $avgStageProbability * 0.20;

        // Factor 4: Data freshness (15% weight)
        // Recent leads = higher confidence
        $recentLeads = $leads->filter(function ($lead) {
            return $lead->created_at && $lead->created_at->greaterThan(now()->subDays(30));
        });
        $freshnessFactor = $leads->count() > 0
            ? ($recentLeads->count() / $leads->count()) * 100
            : 0;
        $factors['freshness'] = $freshnessFactor * 0.15;

        // Factor 5: Value distribution (10% weight)
        // Less variance in deal values = higher confidence
        $values = $leads->pluck('lead_value')->filter()->toArray();
        if (count($values) > 1) {
            $mean = array_sum($values) / count($values);
            $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / count($values);
            $coefficientOfVariation = $mean > 0 ? (sqrt($variance) / $mean) : 0;
            // Lower coefficient of variation = higher confidence
            $distributionFactor = max(0, 100 - ($coefficientOfVariation * 50));
            $factors['value_distribution'] = $distributionFactor * 0.10;
        } else {
            $factors['value_distribution'] = 50 * 0.10; // Neutral score for single value
        }

        return round(array_sum($factors), 2);
    }

    /**
     * Calculate confidence score for team forecast.
     *
     * @param  Collection  $leads
     * @param  int  $teamId
     * @return float
     */
    protected function calculateTeamConfidenceScore(Collection $leads, int $teamId): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        // Team confidence uses similar factors but with adjustments
        $factors = [];

        // Factor 1: Sample size (35% weight) - More important for teams
        $sampleSizeFactor = min(100, ($leads->count() / (self::MIN_SAMPLE_SIZE * 2)) * 100);
        $factors['sample_size'] = $sampleSizeFactor * 0.35;

        // Factor 2: Historical data availability (25% weight)
        $leadsWithHistoricalData = $leads->filter(function ($lead) {
            return $this->getHistoricalConversionRate($lead) !== null;
        });
        $historicalDataFactor = ($leadsWithHistoricalData->count() / $leads->count()) * 100;
        $factors['historical_data'] = $historicalDataFactor * 0.25;

        // Factor 3: Team member distribution (20% weight)
        // More evenly distributed across team members = higher confidence
        $userDistribution = $leads->groupBy('user_id')->map->count();
        $avgLeadsPerUser = $userDistribution->avg();
        $variance = $userDistribution->map(fn($count) => pow($count - $avgLeadsPerUser, 2))->avg();
        $distributionFactor = $avgLeadsPerUser > 0
            ? max(0, 100 - (sqrt($variance) / $avgLeadsPerUser * 50))
            : 0;
        $factors['member_distribution'] = $distributionFactor * 0.20;

        // Factor 4: Average stage probability (15% weight)
        $avgStageProbability = $leads->avg(fn($lead) => $this->getStageProbability($lead));
        $factors['stage_probability'] = $avgStageProbability * 0.15;

        // Factor 5: Data freshness (5% weight)
        $recentLeads = $leads->filter(function ($lead) {
            return $lead->created_at && $lead->created_at->greaterThan(now()->subDays(30));
        });
        $freshnessFactor = ($recentLeads->count() / $leads->count()) * 100;
        $factors['freshness'] = $freshnessFactor * 0.05;

        return round(array_sum($factors), 2);
    }

    /**
     * Get stage probability for a lead.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function getStageProbability($lead): float
    {
        if (!$lead->stage) {
            return 50.0; // Default probability if stage not found
        }

        return (float) ($lead->stage->probability ?? 50.0);
    }

    /**
     * Get historical conversion rate for a lead.
     *
     * @param  Lead  $lead
     * @return float|null
     */
    protected function getHistoricalConversionRate($lead): ?float
    {
        if (!$lead->lead_pipeline_stage_id || !$lead->lead_pipeline_id) {
            return null;
        }

        // Try to get user-specific historical rate first
        $conversionRate = $this->historicalConversionRepository->getConversionRate(
            $lead->lead_pipeline_stage_id,
            $lead->lead_pipeline_id,
            $lead->user_id
        );

        // If no user-specific rate, get general rate for stage/pipeline
        if ($conversionRate === null) {
            $conversionRate = $this->historicalConversionRepository->getConversionRate(
                $lead->lead_pipeline_stage_id,
                $lead->lead_pipeline_id
            );
        }

        return $conversionRate;
    }

    /**
     * Get leads for forecast calculation.
     *
     * @param  int  $userId
     * @param  Carbon  $periodStart
     * @param  Carbon  $periodEnd
     * @param  int|null  $teamId
     * @return Collection
     */
    protected function getLeadsForForecast(
        int $userId,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?int $teamId = null
    ): Collection {
        $query = $this->leadRepository->getModel()
            ->select(['id', 'user_id', 'lead_value', 'lead_pipeline_stage_id', 'lead_pipeline_id', 'expected_close_date', 'created_at'])
            ->with(['stage:id,name,probability,code', 'pipeline:id,name'])
            ->where('user_id', $userId)
            ->where('status', 1); // Open leads only

        // Include leads that are expected to close within the period
        // or don't have an expected close date yet
        $query->where(function ($q) use ($periodStart, $periodEnd) {
            $q->whereBetween('expected_close_date', [$periodStart, $periodEnd])
                ->orWhereNull('expected_close_date');
        });

        return $query->get();
    }

    /**
     * Get leads for team forecast calculation.
     *
     * @param  int  $teamId
     * @param  Carbon  $periodStart
     * @param  Carbon  $periodEnd
     * @return Collection
     */
    protected function getLeadsForTeamForecast(
        int $teamId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): Collection {
        // Note: This assumes team_id field exists on leads table
        // Adjust if the team relationship is structured differently
        $query = $this->leadRepository->getModel()
            ->select(['id', 'user_id', 'lead_value', 'lead_pipeline_stage_id', 'lead_pipeline_id', 'expected_close_date', 'created_at'])
            ->with(['stage:id,name,probability,code', 'pipeline:id,name', 'user:id,name'])
            ->where('status', 1); // Open leads only

        // Filter by team if team field exists
        // Otherwise, we would need to join through users table
        // Keeping it simple for now
        $query->where(function ($q) use ($periodStart, $periodEnd) {
            $q->whereBetween('expected_close_date', [$periodStart, $periodEnd])
                ->orWhereNull('expected_close_date');
        });

        return $query->get();
    }

    /**
     * Build forecast metadata.
     *
     * @param  Collection  $leads
     * @param  string  $periodType
     * @param  bool  $isTeamForecast
     * @return array
     */
    protected function buildMetadata(
        Collection $leads,
        string $periodType,
        bool $isTeamForecast = false
    ): array {
        $metadata = [
            'total_leads' => $leads->count(),
            'total_value' => $leads->sum('lead_value'),
            'average_deal_size' => $leads->avg('lead_value'),
            'period_type' => $periodType,
            'generated_at' => now()->toIso8601String(),
            'leads_with_expected_close' => $leads->whereNotNull('expected_close_date')->count(),
        ];

        if ($isTeamForecast) {
            $metadata['team_members'] = $leads->pluck('user_id')->unique()->count();
            $metadata['leads_by_member'] = $leads->groupBy('user_id')
                ->map(fn($userLeads) => $userLeads->count())
                ->toArray();
        }

        // Stage distribution
        $stageDistribution = $leads->groupBy('lead_pipeline_stage_id')
            ->map(function ($stageLeads) {
                return [
                    'count' => $stageLeads->count(),
                    'total_value' => $stageLeads->sum('lead_value'),
                ];
            })
            ->toArray();

        $metadata['stage_distribution'] = $stageDistribution;

        // Pipeline distribution
        $pipelineDistribution = $leads->groupBy('lead_pipeline_id')
            ->map(function ($pipelineLeads) {
                return [
                    'count' => $pipelineLeads->count(),
                    'total_value' => $pipelineLeads->sum('lead_value'),
                ];
            })
            ->toArray();

        $metadata['pipeline_distribution'] = $pipelineDistribution;

        return $metadata;
    }

    /**
     * Get period start date based on period type.
     *
     * @param  string  $periodType
     * @return Carbon
     */
    protected function getPeriodStart(string $periodType): Carbon
    {
        return match ($periodType) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            default => now()->startOfMonth(),
        };
    }

    /**
     * Get period end date based on period type.
     *
     * @param  string  $periodType
     * @param  Carbon  $periodStart
     * @return Carbon
     */
    protected function getPeriodEnd(string $periodType, Carbon $periodStart): Carbon
    {
        return match ($periodType) {
            'week' => $periodStart->copy()->endOfWeek(),
            'month' => $periodStart->copy()->endOfMonth(),
            'quarter' => $periodStart->copy()->endOfQuarter(),
            default => $periodStart->copy()->endOfMonth(),
        };
    }

    /**
     * Recalculate an existing forecast.
     *
     * @param  int  $forecastId
     * @return \Webkul\Lead\Contracts\SalesForecast
     */
    public function recalculateForecast(int $forecastId)
    {
        $forecast = $this->salesForecastRepository->findOrFail($forecastId);

        if ($forecast->user_id) {
            return $this->generateForecast(
                $forecast->user_id,
                $forecast->period_type,
                $forecast->period_start,
                $forecast->team_id
            );
        }

        return $this->generateTeamForecast(
            $forecast->team_id,
            $forecast->period_type,
            $forecast->period_start
        );
    }

    /**
     * Get forecast scenarios for comparison.
     *
     * @param  Collection  $leads
     * @return array
     */
    public function getForecastScenarios(Collection $leads): array
    {
        return [
            'weighted' => [
                'value' => $this->calculateWeightedForecast($leads),
                'description' => 'Most likely outcome based on stage probabilities',
            ],
            'best_case' => [
                'value' => $this->calculateBestCase($leads),
                'description' => 'All deals close successfully',
            ],
            'worst_case' => [
                'value' => $this->calculateWorstCase($leads),
                'description' => 'Pessimistic scenario based on historical rates',
            ],
        ];
    }

    /**
     * Generate cache key for forecast.
     *
     * @param  string  $type
     * @param  array  $params
     * @return string
     */
    protected function getCacheKey(string $type, array $params = []): string
    {
        $key = self::CACHE_PREFIX . $type;

        if (!empty($params)) {
            ksort($params);
            $key .= ':' . md5(json_encode($params));
        }

        return $key;
    }

    /**
     * Clear cache for a specific user forecast.
     *
     * @param  int  $userId
     * @param  string  $periodType
     * @param  string  $periodStart
     * @return void
     */
    protected function clearForecastCache(int $userId, string $periodType, string $periodStart): void
    {
        $cacheKey = $this->getCacheKey('user', [
            'user_id' => $userId,
            'period_type' => $periodType,
            'period_start' => $periodStart,
        ]);

        Cache::forget($cacheKey);

        // Also clear scenarios cache
        $scenariosKey = $this->getCacheKey('scenarios', [
            'user_id' => $userId,
            'period_type' => $periodType,
        ]);

        Cache::forget($scenariosKey);
    }

    /**
     * Clear cache for a specific team forecast.
     *
     * @param  int  $teamId
     * @param  string  $periodType
     * @param  string  $periodStart
     * @return void
     */
    protected function clearTeamForecastCache(int $teamId, string $periodType, string $periodStart): void
    {
        $cacheKey = $this->getCacheKey('team', [
            'team_id' => $teamId,
            'period_type' => $periodType,
            'period_start' => $periodStart,
        ]);

        Cache::forget($cacheKey);

        // Also clear team scenarios cache
        $scenariosKey = $this->getCacheKey('team_scenarios', [
            'team_id' => $teamId,
            'period_type' => $periodType,
        ]);

        Cache::forget($scenariosKey);
    }

    /**
     * Clear all forecast cache.
     *
     * @return void
     */
    public function clearAllForecastCache(): void
    {
        Cache::flush();
    }

    /**
     * Generate forecast for multiple users in batch.
     * Optimized for processing multiple forecasts efficiently.
     *
     * @param  array  $userIds
     * @param  string  $periodType
     * @param  Carbon|null  $periodStart
     * @param  int|null  $teamId
     * @return Collection
     */
    public function generateForecastsInBatch(
        array $userIds,
        string $periodType,
        ?Carbon $periodStart = null,
        ?int $teamId = null
    ): Collection {
        $periodStart = $periodStart ?? $this->getPeriodStart($periodType);
        $periodEnd = $this->getPeriodEnd($periodType, $periodStart);

        $forecasts = collect();

        // Pre-load all leads for all users in a single query
        $allLeads = $this->leadRepository->getModel()
            ->select(['id', 'user_id', 'lead_value', 'lead_pipeline_stage_id', 'lead_pipeline_id', 'expected_close_date', 'created_at'])
            ->with(['stage:id,name,probability,code', 'pipeline:id,name'])
            ->whereIn('user_id', $userIds)
            ->where('status', 1)
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('expected_close_date', [$periodStart, $periodEnd])
                    ->orWhereNull('expected_close_date');
            })
            ->get()
            ->groupBy('user_id');

        // Generate forecast for each user using pre-loaded data
        foreach ($userIds as $userId) {
            $userLeads = $allLeads->get($userId, collect());

            if ($userLeads->isNotEmpty()) {
                $forecast = $this->generateForecastFromLeads(
                    $userId,
                    $periodType,
                    $periodStart,
                    $periodEnd,
                    $userLeads,
                    $teamId
                );

                if ($forecast) {
                    $forecasts->push($forecast);
                }
            }
        }

        return $forecasts;
    }

    /**
     * Generate forecast from pre-loaded leads collection.
     * Internal helper method for batch processing.
     *
     * @param  int  $userId
     * @param  string  $periodType
     * @param  Carbon  $periodStart
     * @param  Carbon  $periodEnd
     * @param  Collection  $leads
     * @param  int|null  $teamId
     * @return \Webkul\Lead\Contracts\SalesForecast|null
     */
    protected function generateForecastFromLeads(
        int $userId,
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd,
        Collection $leads,
        ?int $teamId = null
    ) {
        if ($leads->isEmpty()) {
            return null;
        }

        // Calculate forecasts
        $weightedForecast = $this->calculateWeightedForecast($leads);
        $bestCase = $this->calculateBestCase($leads);
        $worstCase = $this->calculateWorstCase($leads);
        $forecastValue = $weightedForecast;

        // Calculate confidence score
        $confidenceScore = $this->calculateConfidenceScore($leads, $userId);

        // Build metadata
        $metadata = $this->buildMetadata($leads, $periodType);

        // Create or update forecast
        $existingForecast = $this->salesForecastRepository->findByUserAndPeriod(
            $userId,
            $periodType,
            $periodStart->format('Y-m-d')
        );

        $forecastData = [
            'user_id' => $userId,
            'team_id' => $teamId,
            'period_type' => $periodType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'forecast_value' => $forecastValue,
            'weighted_forecast' => $weightedForecast,
            'best_case' => $bestCase,
            'worst_case' => $worstCase,
            'confidence_score' => $confidenceScore,
            'metadata' => $metadata,
        ];

        if ($existingForecast) {
            $this->salesForecastRepository->update($forecastData, $existingForecast->id);
            $this->clearForecastCache($userId, $periodType, $periodStart->format('Y-m-d'));

            return $this->salesForecastRepository->find($existingForecast->id);
        }

        $forecast = $this->salesForecastRepository->create($forecastData);
        $this->clearForecastCache($userId, $periodType, $periodStart->format('Y-m-d'));

        return $forecast;
    }

    /**
     * Get forecast statistics using database aggregations.
     * More efficient for large datasets as calculations are done at DB level.
     *
     * @param  int  $userId
     * @param  Carbon  $periodStart
     * @param  Carbon  $periodEnd
     * @return array
     */
    public function getForecastStatisticsOptimized(
        int $userId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $stats = $this->leadRepository->getModel()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('expected_close_date', [$periodStart, $periodEnd])
                    ->orWhereNull('expected_close_date');
            })
            ->selectRaw('
                COUNT(*) as total_leads,
                SUM(lead_value) as total_value,
                AVG(lead_value) as average_value,
                MIN(lead_value) as min_value,
                MAX(lead_value) as max_value,
                COUNT(CASE WHEN expected_close_date IS NOT NULL THEN 1 END) as leads_with_date
            ')
            ->first();

        // Get stage distribution with DB aggregation
        $stageStats = $this->leadRepository->getModel()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('expected_close_date', [$periodStart, $periodEnd])
                    ->orWhereNull('expected_close_date');
            })
            ->join('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->groupBy('lead_pipeline_stage_id', 'lead_pipeline_stages.name', 'lead_pipeline_stages.probability')
            ->selectRaw('
                lead_pipeline_stage_id,
                lead_pipeline_stages.name as stage_name,
                lead_pipeline_stages.probability as stage_probability,
                COUNT(*) as lead_count,
                SUM(lead_value) as stage_total_value
            ')
            ->get();

        return [
            'total_leads' => (int) $stats->total_leads,
            'total_value' => (float) $stats->total_value,
            'average_value' => (float) $stats->average_value,
            'min_value' => (float) $stats->min_value,
            'max_value' => (float) $stats->max_value,
            'leads_with_expected_date' => (int) $stats->leads_with_date,
            'stage_distribution' => $stageStats->toArray(),
        ];
    }
}
