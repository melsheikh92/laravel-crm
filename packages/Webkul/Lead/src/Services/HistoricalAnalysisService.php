<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;

class HistoricalAnalysisService
{
    /**
     * Conversion rate thresholds.
     */
    const HIGH_CONVERSION_THRESHOLD = 60.0;
    const MEDIUM_CONVERSION_THRESHOLD = 30.0;
    const LOW_CONVERSION_THRESHOLD = 15.0;

    /**
     * Minimum sample size for statistical significance.
     */
    const MIN_SAMPLE_SIZE = 10;

    /**
     * Default analysis period in days.
     */
    const DEFAULT_ANALYSIS_PERIOD = 90;

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
     * Analyze historical data for a specific user.
     *
     * @param  int  $userId
     * @param  int|null  $pipelineId
     * @param  int  $days
     * @return array
     */
    public function analyzeUserPerformance(int $userId, ?int $pipelineId = null, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $leads = $this->getLeadsForAnalysis($userId, $pipelineId, $startDate, $endDate);

        return [
            'user_id' => $userId,
            'pipeline_id' => $pipelineId,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'total_leads' => $leads->count(),
            'conversion_rate' => $this->calculateConversionRate($leads),
            'win_rate' => $this->calculateWinRate($leads),
            'loss_rate' => $this->calculateLossRate($leads),
            'average_deal_size' => $this->calculateAverageDealSize($leads),
            'total_won_value' => $this->calculateTotalWonValue($leads),
            'total_lost_value' => $this->calculateTotalLostValue($leads),
            'average_days_to_close' => $this->calculateAverageDaysToClose($leads),
            'stage_breakdown' => $this->getStageBreakdown($leads),
            'performance_indicators' => $this->getPerformanceIndicators($leads),
        ];
    }

    /**
     * Analyze historical data for a pipeline.
     *
     * @param  int  $pipelineId
     * @param  int  $days
     * @return array
     */
    public function analyzePipelinePerformance(int $pipelineId, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $leads = $this->getLeadsForAnalysis(null, $pipelineId, $startDate, $endDate);

        return [
            'pipeline_id' => $pipelineId,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'total_leads' => $leads->count(),
            'conversion_rate' => $this->calculateConversionRate($leads),
            'win_rate' => $this->calculateWinRate($leads),
            'loss_rate' => $this->calculateLossRate($leads),
            'average_deal_size' => $this->calculateAverageDealSize($leads),
            'total_pipeline_value' => $this->calculateTotalPipelineValue($leads),
            'total_won_value' => $this->calculateTotalWonValue($leads),
            'total_lost_value' => $this->calculateTotalLostValue($leads),
            'average_days_to_close' => $this->calculateAverageDaysToClose($leads),
            'stage_performance' => $this->getStagePerformance($pipelineId, $leads),
            'user_performance' => $this->getUserPerformanceInPipeline($leads),
            'velocity_metrics' => $this->getVelocityMetrics($leads),
        ];
    }

    /**
     * Analyze historical data for a stage.
     *
     * @param  int  $stageId
     * @param  int|null  $pipelineId
     * @param  int  $days
     * @return array
     */
    public function analyzeStagePerformance(int $stageId, ?int $pipelineId = null, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $leads = $this->getLeadsInStage($stageId, $pipelineId, $startDate, $endDate);

        $historicalData = $this->historicalConversionRepository->getStatsByStage($stageId);

        return [
            'stage_id' => $stageId,
            'pipeline_id' => $pipelineId,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'total_leads' => $leads->count(),
            'conversion_rate' => $historicalData['average_conversion_rate'] ?? $this->calculateConversionRate($leads),
            'average_time_in_stage' => $historicalData['average_time_in_stage'] ?? $this->calculateAverageTimeInStage($leads),
            'average_deal_size' => $this->calculateAverageDealSize($leads),
            'total_value' => $leads->sum('lead_value'),
            'won_count' => $this->countWonLeads($leads),
            'lost_count' => $this->countLostLeads($leads),
            'open_count' => $this->countOpenLeads($leads),
            'historical_statistics' => $historicalData,
        ];
    }

    /**
     * Calculate overall conversion rates by stage.
     *
     * @param  int|null  $pipelineId
     * @param  int|null  $userId
     * @param  int  $days
     * @return array
     */
    public function getConversionRatesByStage(?int $pipelineId = null, ?int $userId = null, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $filters = [
            'current' => true,
            'current_days' => $days,
            'min_sample_size' => self::MIN_SAMPLE_SIZE,
        ];

        if ($pipelineId) {
            $filters['pipeline_id'] = $pipelineId;
        }

        if ($userId) {
            $filters['user_id'] = $userId;
        }

        $conversions = $this->historicalConversionRepository->getWithFilters($filters);

        return $conversions->map(function ($conversion) {
            return [
                'stage_id' => $conversion->stage_id,
                'stage_name' => $conversion->stage->name ?? 'Unknown',
                'pipeline_id' => $conversion->pipeline_id,
                'pipeline_name' => $conversion->pipeline->name ?? 'Unknown',
                'conversion_rate' => $conversion->conversion_rate,
                'average_time_in_stage' => $conversion->average_time_in_stage,
                'sample_size' => $conversion->sample_size,
                'level' => $this->getConversionRateLevel($conversion->conversion_rate),
            ];
        })->toArray();
    }

    /**
     * Calculate average deal sizes by stage.
     *
     * @param  int|null  $pipelineId
     * @param  int|null  $userId
     * @param  int  $days
     * @return array
     */
    public function getAverageDealSizesByStage(?int $pipelineId = null, ?int $userId = null, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $query = $this->leadRepository->getModel()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $leads = $query->with(['stage', 'pipeline'])->get();

        return $leads->groupBy('lead_pipeline_stage_id')
            ->map(function ($stageLeads, $stageId) {
                $firstLead = $stageLeads->first();

                return [
                    'stage_id' => $stageId,
                    'stage_name' => $firstLead->stage->name ?? 'Unknown',
                    'pipeline_id' => $firstLead->lead_pipeline_id,
                    'pipeline_name' => $firstLead->pipeline->name ?? 'Unknown',
                    'average_deal_size' => round($stageLeads->avg('lead_value'), 2),
                    'min_deal_size' => round($stageLeads->min('lead_value'), 2),
                    'max_deal_size' => round($stageLeads->max('lead_value'), 2),
                    'total_value' => round($stageLeads->sum('lead_value'), 2),
                    'lead_count' => $stageLeads->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Calculate win rates by pipeline.
     *
     * @param  int|null  $userId
     * @param  int  $days
     * @return array
     */
    public function getWinRatesByPipeline(?int $userId = null, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $query = $this->leadRepository->getModel()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $leads = $query->with(['stage', 'pipeline'])->get();

        return $leads->groupBy('lead_pipeline_id')
            ->map(function ($pipelineLeads, $pipelineId) {
                $firstLead = $pipelineLeads->first();
                $wonLeads = $pipelineLeads->filter(fn($lead) => $this->isWonLead($lead));
                $lostLeads = $pipelineLeads->filter(fn($lead) => $this->isLostLead($lead));
                $closedLeads = $wonLeads->count() + $lostLeads->count();

                return [
                    'pipeline_id' => $pipelineId,
                    'pipeline_name' => $firstLead->pipeline->name ?? 'Unknown',
                    'total_leads' => $pipelineLeads->count(),
                    'won_count' => $wonLeads->count(),
                    'lost_count' => $lostLeads->count(),
                    'open_count' => $pipelineLeads->count() - $closedLeads,
                    'win_rate' => $closedLeads > 0 ? round(($wonLeads->count() / $closedLeads) * 100, 2) : 0.0,
                    'loss_rate' => $closedLeads > 0 ? round(($lostLeads->count() / $closedLeads) * 100, 2) : 0.0,
                    'total_won_value' => round($wonLeads->sum('lead_value'), 2),
                    'total_lost_value' => round($lostLeads->sum('lead_value'), 2),
                    'average_won_deal_size' => $wonLeads->count() > 0 ? round($wonLeads->avg('lead_value'), 2) : 0.0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Calculate win rates by user.
     *
     * @param  int|null  $pipelineId
     * @param  int  $days
     * @return array
     */
    public function getWinRatesByUser(?int $pipelineId = null, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $query = $this->leadRepository->getModel()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        $leads = $query->with(['stage', 'user'])->get();

        return $leads->groupBy('user_id')
            ->map(function ($userLeads, $userId) {
                $firstLead = $userLeads->first();
                $wonLeads = $userLeads->filter(fn($lead) => $this->isWonLead($lead));
                $lostLeads = $userLeads->filter(fn($lead) => $this->isLostLead($lead));
                $closedLeads = $wonLeads->count() + $lostLeads->count();

                return [
                    'user_id' => $userId,
                    'user_name' => $firstLead->user->name ?? 'Unknown',
                    'total_leads' => $userLeads->count(),
                    'won_count' => $wonLeads->count(),
                    'lost_count' => $lostLeads->count(),
                    'open_count' => $userLeads->count() - $closedLeads,
                    'win_rate' => $closedLeads > 0 ? round(($wonLeads->count() / $closedLeads) * 100, 2) : 0.0,
                    'loss_rate' => $closedLeads > 0 ? round(($lostLeads->count() / $closedLeads) * 100, 2) : 0.0,
                    'total_won_value' => round($wonLeads->sum('lead_value'), 2),
                    'total_lost_value' => round($lostLeads->sum('lead_value'), 2),
                    'average_won_deal_size' => $wonLeads->count() > 0 ? round($wonLeads->avg('lead_value'), 2) : 0.0,
                    'average_days_to_close' => $this->calculateAverageDaysToClose($userLeads->filter(fn($l) => $this->isWonLead($l))),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get performance trends over time.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  int  $months
     * @return array
     */
    public function getPerformanceTrends(?int $userId = null, ?int $pipelineId = null, int $months = 6): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();

            $leads = $this->getLeadsForAnalysis($userId, $pipelineId, $startDate, $endDate);

            $wonLeads = $leads->filter(fn($lead) => $this->isWonLead($lead));

            $trends[] = [
                'period' => $startDate->format('Y-m'),
                'total_leads' => $leads->count(),
                'won_count' => $wonLeads->count(),
                'win_rate' => $this->calculateWinRate($leads),
                'average_deal_size' => $this->calculateAverageDealSize($leads),
                'total_value' => round($leads->sum('lead_value'), 2),
                'total_won_value' => round($wonLeads->sum('lead_value'), 2),
            ];
        }

        return $trends;
    }

    /**
     * Get top performing stages.
     *
     * @param  int|null  $pipelineId
     * @param  int  $limit
     * @param  int  $days
     * @return array
     */
    public function getTopPerformingStages(?int $pipelineId = null, int $limit = 5, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $filters = [
            'current' => true,
            'current_days' => $days,
            'min_sample_size' => self::MIN_SAMPLE_SIZE,
        ];

        if ($pipelineId) {
            $filters['pipeline_id'] = $pipelineId;
        }

        $conversions = $this->historicalConversionRepository->getWithFilters($filters);

        return $conversions
            ->sortByDesc('conversion_rate')
            ->take($limit)
            ->map(function ($conversion) {
                return [
                    'stage_id' => $conversion->stage_id,
                    'stage_name' => $conversion->stage->name ?? 'Unknown',
                    'pipeline_id' => $conversion->pipeline_id,
                    'pipeline_name' => $conversion->pipeline->name ?? 'Unknown',
                    'conversion_rate' => $conversion->conversion_rate,
                    'average_time_in_stage' => $conversion->average_time_in_stage,
                    'sample_size' => $conversion->sample_size,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get bottom performing stages.
     *
     * @param  int|null  $pipelineId
     * @param  int  $limit
     * @param  int  $days
     * @return array
     */
    public function getBottomPerformingStages(?int $pipelineId = null, int $limit = 5, int $days = self::DEFAULT_ANALYSIS_PERIOD): array
    {
        $filters = [
            'current' => true,
            'current_days' => $days,
            'min_sample_size' => self::MIN_SAMPLE_SIZE,
        ];

        if ($pipelineId) {
            $filters['pipeline_id'] = $pipelineId;
        }

        $conversions = $this->historicalConversionRepository->getWithFilters($filters);

        return $conversions
            ->sortBy('conversion_rate')
            ->take($limit)
            ->map(function ($conversion) {
                return [
                    'stage_id' => $conversion->stage_id,
                    'stage_name' => $conversion->stage->name ?? 'Unknown',
                    'pipeline_id' => $conversion->pipeline_id,
                    'pipeline_name' => $conversion->pipeline->name ?? 'Unknown',
                    'conversion_rate' => $conversion->conversion_rate,
                    'average_time_in_stage' => $conversion->average_time_in_stage,
                    'sample_size' => $conversion->sample_size,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Calculate conversion rate for leads.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateConversionRate(Collection $leads): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        $totalLeads = $leads->count();
        $convertedLeads = $leads->filter(fn($lead) => $this->isWonLead($lead))->count();

        return round(($convertedLeads / $totalLeads) * 100, 2);
    }

    /**
     * Calculate win rate for leads.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateWinRate(Collection $leads): float
    {
        $closedLeads = $leads->filter(fn($lead) => $this->isClosedLead($lead));

        if ($closedLeads->isEmpty()) {
            return 0.0;
        }

        $wonLeads = $closedLeads->filter(fn($lead) => $this->isWonLead($lead));

        return round(($wonLeads->count() / $closedLeads->count()) * 100, 2);
    }

    /**
     * Calculate loss rate for leads.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateLossRate(Collection $leads): float
    {
        $closedLeads = $leads->filter(fn($lead) => $this->isClosedLead($lead));

        if ($closedLeads->isEmpty()) {
            return 0.0;
        }

        $lostLeads = $closedLeads->filter(fn($lead) => $this->isLostLead($lead));

        return round(($lostLeads->count() / $closedLeads->count()) * 100, 2);
    }

    /**
     * Calculate average deal size.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateAverageDealSize(Collection $leads): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        return round($leads->avg('lead_value'), 2);
    }

    /**
     * Calculate total won value.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateTotalWonValue(Collection $leads): float
    {
        $wonLeads = $leads->filter(fn($lead) => $this->isWonLead($lead));

        return round($wonLeads->sum('lead_value'), 2);
    }

    /**
     * Calculate total lost value.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateTotalLostValue(Collection $leads): float
    {
        $lostLeads = $leads->filter(fn($lead) => $this->isLostLead($lead));

        return round($lostLeads->sum('lead_value'), 2);
    }

    /**
     * Calculate total pipeline value.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateTotalPipelineValue(Collection $leads): float
    {
        return round($leads->sum('lead_value'), 2);
    }

    /**
     * Calculate average days to close.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateAverageDaysToClose(Collection $leads): float
    {
        $closedLeads = $leads->filter(fn($lead) => $this->isClosedLead($lead) && $lead->closed_at && $lead->created_at);

        if ($closedLeads->isEmpty()) {
            return 0.0;
        }

        $daysToClose = $closedLeads->map(function ($lead) {
            return $lead->created_at->diffInDays($lead->closed_at);
        });

        return round($daysToClose->avg(), 2);
    }

    /**
     * Calculate average time in stage.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function calculateAverageTimeInStage(Collection $leads): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        $timeInStage = $leads->map(function ($lead) {
            if (!$lead->updated_at) {
                return 0;
            }

            return $lead->updated_at->diffInDays(now());
        });

        return round($timeInStage->avg(), 2);
    }

    /**
     * Get stage breakdown.
     *
     * @param  Collection  $leads
     * @return array
     */
    protected function getStageBreakdown(Collection $leads): array
    {
        return $leads->groupBy('lead_pipeline_stage_id')
            ->map(function ($stageLeads, $stageId) {
                $firstLead = $stageLeads->first();

                return [
                    'stage_id' => $stageId,
                    'stage_name' => $firstLead->stage->name ?? 'Unknown',
                    'lead_count' => $stageLeads->count(),
                    'total_value' => round($stageLeads->sum('lead_value'), 2),
                    'average_value' => round($stageLeads->avg('lead_value'), 2),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get stage performance for a pipeline.
     *
     * @param  int  $pipelineId
     * @param  Collection  $leads
     * @return array
     */
    protected function getStagePerformance(int $pipelineId, Collection $leads): array
    {
        return $leads->groupBy('lead_pipeline_stage_id')
            ->map(function ($stageLeads, $stageId) {
                $firstLead = $stageLeads->first();
                $wonLeads = $stageLeads->filter(fn($lead) => $this->isWonLead($lead));
                $lostLeads = $stageLeads->filter(fn($lead) => $this->isLostLead($lead));

                return [
                    'stage_id' => $stageId,
                    'stage_name' => $firstLead->stage->name ?? 'Unknown',
                    'lead_count' => $stageLeads->count(),
                    'won_count' => $wonLeads->count(),
                    'lost_count' => $lostLeads->count(),
                    'conversion_rate' => $this->calculateConversionRate($stageLeads),
                    'win_rate' => $this->calculateWinRate($stageLeads),
                    'total_value' => round($stageLeads->sum('lead_value'), 2),
                    'average_value' => round($stageLeads->avg('lead_value'), 2),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get user performance within a pipeline.
     *
     * @param  Collection  $leads
     * @return array
     */
    protected function getUserPerformanceInPipeline(Collection $leads): array
    {
        return $leads->groupBy('user_id')
            ->map(function ($userLeads, $userId) {
                $firstLead = $userLeads->first();
                $wonLeads = $userLeads->filter(fn($lead) => $this->isWonLead($lead));

                return [
                    'user_id' => $userId,
                    'user_name' => $firstLead->user->name ?? 'Unknown',
                    'lead_count' => $userLeads->count(),
                    'won_count' => $wonLeads->count(),
                    'win_rate' => $this->calculateWinRate($userLeads),
                    'total_value' => round($userLeads->sum('lead_value'), 2),
                    'total_won_value' => round($wonLeads->sum('lead_value'), 2),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get velocity metrics for leads.
     *
     * @param  Collection  $leads
     * @return array
     */
    protected function getVelocityMetrics(Collection $leads): array
    {
        $closedLeads = $leads->filter(fn($lead) => $this->isClosedLead($lead));

        return [
            'average_days_to_close' => $this->calculateAverageDaysToClose($closedLeads),
            'fastest_close' => $this->getFastestClose($closedLeads),
            'slowest_close' => $this->getSlowestClose($closedLeads),
        ];
    }

    /**
     * Get performance indicators.
     *
     * @param  Collection  $leads
     * @return array
     */
    protected function getPerformanceIndicators(Collection $leads): array
    {
        $winRate = $this->calculateWinRate($leads);
        $conversionRate = $this->calculateConversionRate($leads);
        $avgDealSize = $this->calculateAverageDealSize($leads);

        return [
            'win_rate_level' => $this->getWinRateLevel($winRate),
            'conversion_rate_level' => $this->getConversionRateLevel($conversionRate),
            'deal_size_level' => $this->getDealSizeLevel($avgDealSize),
            'overall_performance' => $this->getOverallPerformanceLevel($winRate, $conversionRate),
        ];
    }

    /**
     * Get leads for analysis.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return Collection
     */
    protected function getLeadsForAnalysis(?int $userId, ?int $pipelineId, Carbon $startDate, Carbon $endDate): Collection
    {
        $query = $this->leadRepository->getModel()
            ->select(['id', 'user_id', 'lead_value', 'lead_pipeline_stage_id', 'lead_pipeline_id', 'created_at', 'closed_at', 'status'])
            ->with(['stage:id,name,probability,code', 'pipeline:id,name', 'user:id,name'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        return $query->get();
    }

    /**
     * Get leads in a specific stage.
     *
     * @param  int  $stageId
     * @param  int|null  $pipelineId
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return Collection
     */
    protected function getLeadsInStage(int $stageId, ?int $pipelineId, Carbon $startDate, Carbon $endDate): Collection
    {
        $query = $this->leadRepository->getModel()
            ->select(['id', 'user_id', 'lead_value', 'lead_pipeline_stage_id', 'lead_pipeline_id', 'created_at', 'closed_at', 'updated_at'])
            ->with(['stage:id,name,probability,code', 'pipeline:id,name', 'user:id,name'])
            ->where('lead_pipeline_stage_id', $stageId)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        return $query->get();
    }

    /**
     * Check if lead is won.
     *
     * @param  mixed  $lead
     * @return bool
     */
    protected function isWonLead($lead): bool
    {
        return $lead->stage && $lead->stage->code === 'won';
    }

    /**
     * Check if lead is lost.
     *
     * @param  mixed  $lead
     * @return bool
     */
    protected function isLostLead($lead): bool
    {
        return $lead->stage && $lead->stage->code === 'lost';
    }

    /**
     * Check if lead is closed.
     *
     * @param  mixed  $lead
     * @return bool
     */
    protected function isClosedLead($lead): bool
    {
        return $this->isWonLead($lead) || $this->isLostLead($lead);
    }

    /**
     * Count won leads.
     *
     * @param  Collection  $leads
     * @return int
     */
    protected function countWonLeads(Collection $leads): int
    {
        return $leads->filter(fn($lead) => $this->isWonLead($lead))->count();
    }

    /**
     * Count lost leads.
     *
     * @param  Collection  $leads
     * @return int
     */
    protected function countLostLeads(Collection $leads): int
    {
        return $leads->filter(fn($lead) => $this->isLostLead($lead))->count();
    }

    /**
     * Count open leads.
     *
     * @param  Collection  $leads
     * @return int
     */
    protected function countOpenLeads(Collection $leads): int
    {
        return $leads->filter(fn($lead) => !$this->isClosedLead($lead))->count();
    }

    /**
     * Get fastest close time.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function getFastestClose(Collection $leads): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        $closeTimes = $leads->map(function ($lead) {
            if (!$lead->closed_at || !$lead->created_at) {
                return PHP_INT_MAX;
            }

            return $lead->created_at->diffInDays($lead->closed_at);
        })->filter(fn($time) => $time !== PHP_INT_MAX);

        return $closeTimes->isEmpty() ? 0.0 : round($closeTimes->min(), 2);
    }

    /**
     * Get slowest close time.
     *
     * @param  Collection  $leads
     * @return float
     */
    protected function getSlowestClose(Collection $leads): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        $closeTimes = $leads->map(function ($lead) {
            if (!$lead->closed_at || !$lead->created_at) {
                return 0;
            }

            return $lead->created_at->diffInDays($lead->closed_at);
        })->filter(fn($time) => $time > 0);

        return $closeTimes->isEmpty() ? 0.0 : round($closeTimes->max(), 2);
    }

    /**
     * Get conversion rate level.
     *
     * @param  float  $rate
     * @return string
     */
    protected function getConversionRateLevel(float $rate): string
    {
        if ($rate >= self::HIGH_CONVERSION_THRESHOLD) {
            return 'high';
        }

        if ($rate >= self::MEDIUM_CONVERSION_THRESHOLD) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get win rate level.
     *
     * @param  float  $rate
     * @return string
     */
    protected function getWinRateLevel(float $rate): string
    {
        if ($rate >= 70.0) {
            return 'excellent';
        }

        if ($rate >= 50.0) {
            return 'good';
        }

        if ($rate >= 30.0) {
            return 'average';
        }

        return 'poor';
    }

    /**
     * Get deal size level.
     *
     * @param  float  $size
     * @return string
     */
    protected function getDealSizeLevel(float $size): string
    {
        if ($size >= 100000) {
            return 'enterprise';
        }

        if ($size >= 50000) {
            return 'large';
        }

        if ($size >= 10000) {
            return 'medium';
        }

        return 'small';
    }

    /**
     * Get overall performance level.
     *
     * @param  float  $winRate
     * @param  float  $conversionRate
     * @return string
     */
    protected function getOverallPerformanceLevel(float $winRate, float $conversionRate): string
    {
        $averageScore = ($winRate + $conversionRate) / 2;

        if ($averageScore >= 60.0) {
            return 'excellent';
        }

        if ($averageScore >= 40.0) {
            return 'good';
        }

        if ($averageScore >= 20.0) {
            return 'average';
        }

        return 'needs_improvement';
    }

    /**
     * Get win rates by user with pagination.
     *
     * @param  int|null  $pipelineId
     * @param  int  $days
     * @param  int  $perPage
     * @param  int  $page
     * @return array
     */
    public function getWinRatesByUserPaginated(?int $pipelineId = null, int $days = self::DEFAULT_ANALYSIS_PERIOD, int $perPage = 15, int $page = 1): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $query = $this->leadRepository->getModel()
            ->select(['id', 'user_id', 'lead_value', 'lead_pipeline_stage_id', 'lead_pipeline_id', 'created_at', 'closed_at'])
            ->with(['stage:id,name,code', 'user:id,name'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        // Use pagination on the query level
        $leads = $query->paginate($perPage, ['*'], 'page', $page);

        $data = collect($leads->items())->groupBy('user_id')
            ->map(function ($userLeads, $userId) {
                $firstLead = $userLeads->first();
                $wonLeads = $userLeads->filter(fn($lead) => $this->isWonLead($lead));
                $lostLeads = $userLeads->filter(fn($lead) => $this->isLostLead($lead));
                $closedLeads = $wonLeads->count() + $lostLeads->count();

                return [
                    'user_id' => $userId,
                    'user_name' => $firstLead->user->name ?? 'Unknown',
                    'total_leads' => $userLeads->count(),
                    'won_count' => $wonLeads->count(),
                    'lost_count' => $lostLeads->count(),
                    'open_count' => $userLeads->count() - $closedLeads,
                    'win_rate' => $closedLeads > 0 ? round(($wonLeads->count() / $closedLeads) * 100, 2) : 0.0,
                    'loss_rate' => $closedLeads > 0 ? round(($lostLeads->count() / $closedLeads) * 100, 2) : 0.0,
                    'total_won_value' => round($wonLeads->sum('lead_value'), 2),
                    'total_lost_value' => round($lostLeads->sum('lead_value'), 2),
                    'average_won_deal_size' => $wonLeads->count() > 0 ? round($wonLeads->avg('lead_value'), 2) : 0.0,
                    'average_days_to_close' => $this->calculateAverageDaysToClose($userLeads->filter(fn($l) => $this->isWonLead($l))),
                ];
            })
            ->values()
            ->toArray();

        return [
            'data' => $data,
            'pagination' => [
                'total' => $leads->total(),
                'per_page' => $leads->perPage(),
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'from' => $leads->firstItem(),
                'to' => $leads->lastItem(),
            ],
        ];
    }

    /**
     * Get average deal sizes by stage with pagination.
     *
     * @param  int|null  $pipelineId
     * @param  int|null  $userId
     * @param  int  $days
     * @param  int  $perPage
     * @param  int  $page
     * @return array
     */
    public function getAverageDealSizesByStagePerPage(?int $pipelineId = null, ?int $userId = null, int $days = self::DEFAULT_ANALYSIS_PERIOD, int $perPage = 15, int $page = 1): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $query = $this->leadRepository->getModel()
            ->select(['id', 'lead_value', 'lead_pipeline_stage_id', 'lead_pipeline_id'])
            ->with(['stage:id,name', 'pipeline:id,name'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $leads = $query->paginate($perPage, ['*'], 'page', $page);

        $data = collect($leads->items())->groupBy('lead_pipeline_stage_id')
            ->map(function ($stageLeads, $stageId) {
                $firstLead = $stageLeads->first();

                return [
                    'stage_id' => $stageId,
                    'stage_name' => $firstLead->stage->name ?? 'Unknown',
                    'pipeline_id' => $firstLead->lead_pipeline_id,
                    'pipeline_name' => $firstLead->pipeline->name ?? 'Unknown',
                    'average_deal_size' => round($stageLeads->avg('lead_value'), 2),
                    'min_deal_size' => round($stageLeads->min('lead_value'), 2),
                    'max_deal_size' => round($stageLeads->max('lead_value'), 2),
                    'total_value' => round($stageLeads->sum('lead_value'), 2),
                    'lead_count' => $stageLeads->count(),
                ];
            })
            ->values()
            ->toArray();

        return [
            'data' => $data,
            'pagination' => [
                'total' => $leads->total(),
                'per_page' => $leads->perPage(),
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'from' => $leads->firstItem(),
                'to' => $leads->lastItem(),
            ],
        ];
    }

    /**
     * Process large dataset analysis using chunking.
     * This method is optimized for very large datasets by processing in chunks.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  int  $days
     * @param  int  $chunkSize
     * @param  callable  $callback
     * @return void
     */
    public function analyzeLargeDatasetInChunks(
        ?int $userId,
        ?int $pipelineId,
        int $days = self::DEFAULT_ANALYSIS_PERIOD,
        int $chunkSize = 1000,
        ?callable $callback = null
    ): void {
        $startDate = now()->subDays($days);
        $endDate = now();

        $query = $this->leadRepository->getModel()
            ->select(['id', 'user_id', 'lead_value', 'lead_pipeline_stage_id', 'lead_pipeline_id', 'created_at', 'closed_at', 'status'])
            ->with(['stage:id,name,probability,code', 'pipeline:id,name'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        $query->chunk($chunkSize, function ($leads) use ($callback) {
            if ($callback) {
                $callback($leads);
            }
        });
    }
}
