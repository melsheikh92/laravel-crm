<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;

class DealVelocityService
{
    /**
     * Velocity thresholds in days.
     */
    const FAST_VELOCITY_THRESHOLD = 7.0;
    const SLOW_VELOCITY_THRESHOLD = 30.0;

    /**
     * Velocity score thresholds.
     */
    const HIGH_VELOCITY_SCORE = 80.0;
    const MEDIUM_VELOCITY_SCORE = 50.0;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(
        protected LeadRepository $leadRepository,
        protected HistoricalConversionRepository $historicalConversionRepository
    ) {
    }

    /**
     * Calculate velocity score for a lead.
     *
     * @param  Lead  $lead
     * @return array
     */
    public function calculateVelocityScore($lead): array
    {
        $daysInCurrentStage = $this->getDaysInCurrentStage($lead);
        $expectedTimeInStage = $this->getExpectedTimeInStage($lead);
        $historicalAverage = $this->getHistoricalAverageTimeInStage($lead);
        $daysUntilExpectedClose = $this->getDaysUntilExpectedClose($lead);

        // Calculate velocity score (0-100)
        $velocityScore = $this->computeVelocityScore(
            $daysInCurrentStage,
            $expectedTimeInStage,
            $historicalAverage,
            $daysUntilExpectedClose
        );

        // Determine velocity level
        $velocityLevel = $this->getVelocityLevel($velocityScore);

        // Calculate days ahead or behind expected pace
        $paceVariance = $this->calculatePaceVariance(
            $daysInCurrentStage,
            $expectedTimeInStage,
            $historicalAverage
        );

        return [
            'velocity_score' => round($velocityScore, 2),
            'velocity_level' => $velocityLevel,
            'days_in_current_stage' => $daysInCurrentStage,
            'expected_time_in_stage' => $expectedTimeInStage,
            'historical_average' => $historicalAverage,
            'days_until_expected_close' => $daysUntilExpectedClose,
            'pace_variance' => $paceVariance,
            'is_ahead_of_pace' => $paceVariance < 0,
            'is_behind_pace' => $paceVariance > 0,
            'factors' => [
                'stage_progress' => $this->getStageProgressFactor($daysInCurrentStage, $expectedTimeInStage, $historicalAverage),
                'close_date_proximity' => $this->getCloseDateProximityFactor($daysUntilExpectedClose),
                'historical_comparison' => $this->getHistoricalComparisonFactor($daysInCurrentStage, $historicalAverage),
            ],
        ];
    }

    /**
     * Calculate velocity scores for multiple leads.
     *
     * @param  Collection  $leads
     * @return Collection
     */
    public function calculateVelocityScoresForLeads(Collection $leads): Collection
    {
        return $leads->map(function ($lead) {
            $velocityData = $this->calculateVelocityScore($lead);
            $lead->velocity_data = $velocityData;

            return $lead;
        });
    }

    /**
     * Get fast-moving deals.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  int  $limit
     * @return Collection
     */
    public function getFastMovingDeals(?int $userId = null, ?int $pipelineId = null, int $limit = 10): Collection
    {
        $leads = $this->getActiveLeads($userId, $pipelineId);

        $scoredLeads = $this->calculateVelocityScoresForLeads($leads);

        return $scoredLeads
            ->filter(fn($lead) => $lead->velocity_data['velocity_level'] === 'fast')
            ->sortByDesc('velocity_data.velocity_score')
            ->take($limit)
            ->values();
    }

    /**
     * Get slow-moving deals.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  int  $limit
     * @return Collection
     */
    public function getSlowMovingDeals(?int $userId = null, ?int $pipelineId = null, int $limit = 10): Collection
    {
        $leads = $this->getActiveLeads($userId, $pipelineId);

        $scoredLeads = $this->calculateVelocityScoresForLeads($leads);

        return $scoredLeads
            ->filter(fn($lead) => $lead->velocity_data['velocity_level'] === 'slow')
            ->sortBy('velocity_data.velocity_score')
            ->take($limit)
            ->values();
    }

    /**
     * Get deals at risk (behind pace).
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  int  $limit
     * @return Collection
     */
    public function getDealsAtRisk(?int $userId = null, ?int $pipelineId = null, int $limit = 10): Collection
    {
        $leads = $this->getActiveLeads($userId, $pipelineId);

        $scoredLeads = $this->calculateVelocityScoresForLeads($leads);

        return $scoredLeads
            ->filter(fn($lead) => $lead->velocity_data['is_behind_pace'])
            ->sortByDesc('velocity_data.pace_variance')
            ->take($limit)
            ->values();
    }

    /**
     * Calculate average velocity for a user.
     *
     * @param  int  $userId
     * @param  int|null  $pipelineId
     * @return array
     */
    public function calculateUserAverageVelocity(int $userId, ?int $pipelineId = null): array
    {
        $leads = $this->getActiveLeads($userId, $pipelineId);

        if ($leads->isEmpty()) {
            return [
                'average_velocity_score' => 0.0,
                'total_leads' => 0,
                'fast_moving_count' => 0,
                'moderate_moving_count' => 0,
                'slow_moving_count' => 0,
            ];
        }

        $scoredLeads = $this->calculateVelocityScoresForLeads($leads);

        $velocityScores = $scoredLeads->pluck('velocity_data.velocity_score');

        return [
            'average_velocity_score' => round($velocityScores->avg(), 2),
            'total_leads' => $leads->count(),
            'fast_moving_count' => $scoredLeads->filter(fn($l) => $l->velocity_data['velocity_level'] === 'fast')->count(),
            'moderate_moving_count' => $scoredLeads->filter(fn($l) => $l->velocity_data['velocity_level'] === 'moderate')->count(),
            'slow_moving_count' => $scoredLeads->filter(fn($l) => $l->velocity_data['velocity_level'] === 'slow')->count(),
        ];
    }

    /**
     * Calculate pipeline velocity metrics.
     *
     * @param  int  $pipelineId
     * @return array
     */
    public function calculatePipelineVelocity(int $pipelineId): array
    {
        $leads = $this->getActiveLeads(null, $pipelineId);

        if ($leads->isEmpty()) {
            return [
                'average_velocity_score' => 0.0,
                'total_leads' => 0,
                'average_days_in_stage' => 0.0,
                'stage_velocities' => [],
            ];
        }

        $scoredLeads = $this->calculateVelocityScoresForLeads($leads);

        // Group by stage and calculate velocity per stage
        $stageVelocities = $scoredLeads->groupBy('lead_pipeline_stage_id')
            ->map(function ($stageLeads) {
                $velocityScores = $stageLeads->pluck('velocity_data.velocity_score');
                $daysInStage = $stageLeads->pluck('velocity_data.days_in_current_stage');

                return [
                    'average_velocity_score' => round($velocityScores->avg(), 2),
                    'average_days_in_stage' => round($daysInStage->avg(), 2),
                    'lead_count' => $stageLeads->count(),
                ];
            })
            ->toArray();

        return [
            'average_velocity_score' => round($scoredLeads->pluck('velocity_data.velocity_score')->avg(), 2),
            'total_leads' => $leads->count(),
            'average_days_in_stage' => round($scoredLeads->pluck('velocity_data.days_in_current_stage')->avg(), 2),
            'stage_velocities' => $stageVelocities,
        ];
    }

    /**
     * Get velocity trends for a user over time.
     *
     * @param  int  $userId
     * @param  int  $months
     * @return array
     */
    public function getVelocityTrends(int $userId, int $months = 6): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();

            $leads = $this->getClosedLeadsInPeriod($userId, $startDate, $endDate);

            $avgDaysToClose = $leads->isEmpty()
                ? 0
                : $leads->avg(fn($lead) => $this->getDaysToClose($lead));

            $trends[] = [
                'period' => $startDate->format('Y-m'),
                'average_days_to_close' => round($avgDaysToClose, 2),
                'leads_closed' => $leads->count(),
            ];
        }

        return $trends;
    }

    /**
     * Get days in current stage for a lead.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function getDaysInCurrentStage($lead): float
    {
        if (!$lead->updated_at) {
            return 0.0;
        }

        return (float) $lead->updated_at->diffInDays(now());
    }

    /**
     * Get expected time in current stage based on historical data.
     *
     * @param  Lead  $lead
     * @return float|null
     */
    protected function getExpectedTimeInStage($lead): ?float
    {
        if (!$lead->lead_pipeline_stage_id) {
            return null;
        }

        $historicalAverage = $this->getHistoricalAverageTimeInStage($lead);

        // If no historical data, use default based on stage
        if ($historicalAverage === null) {
            return 14.0; // Default 2 weeks
        }

        return $historicalAverage;
    }

    /**
     * Get historical average time in stage.
     *
     * @param  Lead  $lead
     * @return float|null
     */
    protected function getHistoricalAverageTimeInStage($lead): ?float
    {
        if (!$lead->lead_pipeline_stage_id || !$lead->lead_pipeline_id) {
            return null;
        }

        // Try to get user-specific average first
        $avgTime = $this->historicalConversionRepository->getAverageTimeInStage(
            $lead->lead_pipeline_stage_id,
            $lead->lead_pipeline_id,
            $lead->user_id
        );

        // If no user-specific data, get general average
        if ($avgTime === null) {
            $avgTime = $this->historicalConversionRepository->getAverageTimeInStage(
                $lead->lead_pipeline_stage_id,
                $lead->lead_pipeline_id
            );
        }

        return $avgTime;
    }

    /**
     * Get days until expected close date.
     *
     * @param  Lead  $lead
     * @return float|null
     */
    protected function getDaysUntilExpectedClose($lead): ?float
    {
        if (!$lead->expected_close_date) {
            return null;
        }

        $closeDate = Carbon::parse($lead->expected_close_date);

        return (float) now()->diffInDays($closeDate, false);
    }

    /**
     * Compute velocity score based on multiple factors.
     *
     * @param  float  $daysInStage
     * @param  float|null  $expectedTime
     * @param  float|null  $historicalAvg
     * @param  float|null  $daysUntilClose
     * @return float
     */
    protected function computeVelocityScore(
        float $daysInStage,
        ?float $expectedTime,
        ?float $historicalAvg,
        ?float $daysUntilClose
    ): float {
        $factors = [];

        // Factor 1: Stage progress (40% weight)
        $stageProgressFactor = $this->getStageProgressFactor($daysInStage, $expectedTime, $historicalAvg);
        $factors['stage_progress'] = $stageProgressFactor * 0.40;

        // Factor 2: Close date proximity (35% weight)
        $closeDateFactor = $this->getCloseDateProximityFactor($daysUntilClose);
        $factors['close_date_proximity'] = $closeDateFactor * 0.35;

        // Factor 3: Historical comparison (25% weight)
        $historicalFactor = $this->getHistoricalComparisonFactor($daysInStage, $historicalAvg);
        $factors['historical_comparison'] = $historicalFactor * 0.25;

        return array_sum($factors);
    }

    /**
     * Get stage progress factor (0-100).
     *
     * @param  float  $daysInStage
     * @param  float|null  $expectedTime
     * @param  float|null  $historicalAvg
     * @return float
     */
    protected function getStageProgressFactor(float $daysInStage, ?float $expectedTime, ?float $historicalAvg): float
    {
        $expectedOrHistorical = $expectedTime ?? $historicalAvg ?? 14.0;

        if ($expectedOrHistorical <= 0) {
            return 50.0; // Neutral score
        }

        // Score higher if spending less time than expected
        $ratio = $daysInStage / $expectedOrHistorical;

        // Convert ratio to 0-100 score (inverse relationship)
        // ratio < 0.5 = 100 (very fast)
        // ratio = 1.0 = 50 (on pace)
        // ratio > 2.0 = 0 (very slow)
        if ($ratio <= 0.5) {
            return 100.0;
        }

        if ($ratio >= 2.0) {
            return 0.0;
        }

        return max(0, min(100, 100 - (($ratio - 0.5) * 66.67)));
    }

    /**
     * Get close date proximity factor (0-100).
     *
     * @param  float|null  $daysUntilClose
     * @return float
     */
    protected function getCloseDateProximityFactor(?float $daysUntilClose): float
    {
        if ($daysUntilClose === null) {
            return 50.0; // Neutral score if no close date
        }

        // Past due = 0 score
        if ($daysUntilClose < 0) {
            return 0.0;
        }

        // Very close (< 7 days) = higher score
        if ($daysUntilClose <= 7) {
            return 90.0 + ($daysUntilClose / 7 * 10);
        }

        // Moderate distance (7-30 days) = medium score
        if ($daysUntilClose <= 30) {
            return 50.0 + ((30 - $daysUntilClose) / 23 * 40);
        }

        // Far future (> 30 days) = lower score
        if ($daysUntilClose <= 90) {
            return 20.0 + ((90 - $daysUntilClose) / 60 * 30);
        }

        return 20.0;
    }

    /**
     * Get historical comparison factor (0-100).
     *
     * @param  float  $daysInStage
     * @param  float|null  $historicalAvg
     * @return float
     */
    protected function getHistoricalComparisonFactor(float $daysInStage, ?float $historicalAvg): float
    {
        if ($historicalAvg === null || $historicalAvg <= 0) {
            return 50.0; // Neutral score if no historical data
        }

        $ratio = $daysInStage / $historicalAvg;

        // Faster than historical average = higher score
        if ($ratio <= 0.5) {
            return 100.0;
        }

        if ($ratio >= 2.0) {
            return 0.0;
        }

        return max(0, min(100, 100 - (($ratio - 0.5) * 66.67)));
    }

    /**
     * Get velocity level based on score.
     *
     * @param  float  $score
     * @return string
     */
    protected function getVelocityLevel(float $score): string
    {
        if ($score >= self::HIGH_VELOCITY_SCORE) {
            return 'fast';
        }

        if ($score >= self::MEDIUM_VELOCITY_SCORE) {
            return 'moderate';
        }

        return 'slow';
    }

    /**
     * Calculate pace variance (negative = ahead, positive = behind).
     *
     * @param  float  $daysInStage
     * @param  float|null  $expectedTime
     * @param  float|null  $historicalAvg
     * @return float
     */
    protected function calculatePaceVariance(float $daysInStage, ?float $expectedTime, ?float $historicalAvg): float
    {
        $expectedOrHistorical = $expectedTime ?? $historicalAvg ?? 14.0;

        return round($daysInStage - $expectedOrHistorical, 2);
    }

    /**
     * Get active leads for a user/pipeline.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @return Collection
     */
    protected function getActiveLeads(?int $userId = null, ?int $pipelineId = null): Collection
    {
        $query = $this->leadRepository->getModel()
            ->with(['stage', 'pipeline'])
            ->where('status', 1); // Open leads only

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        return $query->get();
    }

    /**
     * Get closed leads in a specific period.
     *
     * @param  int  $userId
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return Collection
     */
    protected function getClosedLeadsInPeriod(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->leadRepository->getModel()
            ->where('user_id', $userId)
            ->where('status', 0) // Closed leads
            ->whereBetween('closed_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Get days to close for a lead.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function getDaysToClose($lead): float
    {
        if (!$lead->closed_at || !$lead->created_at) {
            return 0.0;
        }

        return (float) $lead->created_at->diffInDays($lead->closed_at);
    }
}
