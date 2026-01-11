<?php

namespace Webkul\Lead\Listeners;

use Carbon\Carbon;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;

class UpdateHistoricalConversions
{
    /**
     * Default analysis period in days.
     */
    const ANALYSIS_PERIOD_DAYS = 90;

    /**
     * Minimum sample size for statistical significance.
     */
    const MIN_SAMPLE_SIZE = 10;

    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected HistoricalConversionRepository $historicalConversionRepository,
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Handle the lead.update.after event.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @return void
     */
    public function handle($lead)
    {
        if (! $lead instanceof Lead) {
            return;
        }

        // Check if the lead has been closed (won or lost)
        if ($this->isLeadClosed($lead)) {
            $this->updateHistoricalDataForClosedLead($lead);
        }

        // Check if stage has changed
        if ($lead->wasChanged('lead_pipeline_stage_id')) {
            $this->updateHistoricalDataForStageChange($lead);
        }
    }

    /**
     * Check if the lead is closed (won or lost).
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @return bool
     */
    protected function isLeadClosed($lead): bool
    {
        return $lead->status === 'won' || $lead->status === 'lost';
    }

    /**
     * Update historical data when a lead is closed.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @return void
     */
    protected function updateHistoricalDataForClosedLead($lead): void
    {
        if (! $lead->lead_pipeline_stage_id || ! $lead->lead_pipeline_id) {
            return;
        }

        // Update conversion data for the current stage
        $this->updateConversionDataForStage(
            $lead->lead_pipeline_stage_id,
            $lead->lead_pipeline_id,
            $lead->user_id
        );
    }

    /**
     * Update historical data when a lead changes stages.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @return void
     */
    protected function updateHistoricalDataForStageChange($lead): void
    {
        $originalStageId = $lead->getOriginal('lead_pipeline_stage_id');
        $currentStageId = $lead->lead_pipeline_stage_id;

        if (! $lead->lead_pipeline_id) {
            return;
        }

        // Update conversion data for the previous stage
        if ($originalStageId) {
            $this->updateConversionDataForStage(
                $originalStageId,
                $lead->lead_pipeline_id,
                $lead->user_id
            );
        }

        // Update conversion data for the new stage
        if ($currentStageId) {
            $this->updateConversionDataForStage(
                $currentStageId,
                $lead->lead_pipeline_id,
                $lead->user_id
            );
        }
    }

    /**
     * Update conversion data for a specific stage.
     *
     * @param  int  $stageId
     * @param  int  $pipelineId
     * @param  int|null  $userId
     * @return void
     */
    protected function updateConversionDataForStage(int $stageId, int $pipelineId, ?int $userId): void
    {
        $periodStart = now()->subDays(self::ANALYSIS_PERIOD_DAYS);
        $periodEnd = now();

        // Get all leads in this stage within the analysis period
        $leads = $this->getLeadsInStage($stageId, $pipelineId, $userId, $periodStart, $periodEnd);

        if ($leads->count() < self::MIN_SAMPLE_SIZE) {
            // Not enough data for statistical significance
            return;
        }

        // Calculate conversion metrics
        $conversionRate = $this->calculateConversionRate($leads);
        $averageTimeInStage = $this->calculateAverageTimeInStage($leads);
        $sampleSize = $leads->count();

        // Update or create historical conversion record
        $this->historicalConversionRepository->updateOrCreateForStage(
            $stageId,
            $pipelineId,
            $userId,
            [
                'conversion_rate' => $conversionRate,
                'average_time_in_stage' => $averageTimeInStage,
                'sample_size' => $sampleSize,
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
            ]
        );
    }

    /**
     * Get leads in a specific stage within a date range.
     *
     * @param  int  $stageId
     * @param  int  $pipelineId
     * @param  int|null  $userId
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return \Illuminate\Support\Collection
     */
    protected function getLeadsInStage(int $stageId, int $pipelineId, ?int $userId, Carbon $startDate, Carbon $endDate)
    {
        $query = $this->leadRepository->model
            ->where('lead_pipeline_stage_id', $stageId)
            ->where('lead_pipeline_id', $pipelineId)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Calculate conversion rate for leads.
     *
     * @param  \Illuminate\Support\Collection  $leads
     * @return float
     */
    protected function calculateConversionRate($leads): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        $closedLeads = $leads->filter(function ($lead) {
            return $lead->status === 'won' || $lead->status === 'lost';
        });

        if ($closedLeads->isEmpty()) {
            return 0.0;
        }

        $wonLeads = $closedLeads->filter(function ($lead) {
            return $lead->status === 'won';
        });

        return round(($wonLeads->count() / $closedLeads->count()) * 100, 2);
    }

    /**
     * Calculate average time in stage for leads.
     *
     * @param  \Illuminate\Support\Collection  $leads
     * @return float
     */
    protected function calculateAverageTimeInStage($leads): float
    {
        if ($leads->isEmpty()) {
            return 0.0;
        }

        // For leads that have moved to another stage or closed, calculate the time spent
        $leadsWithTimeData = $leads->filter(function ($lead) {
            return $lead->updated_at && $lead->created_at;
        });

        if ($leadsWithTimeData->isEmpty()) {
            return 0.0;
        }

        $totalDays = $leadsWithTimeData->sum(function ($lead) {
            return $lead->created_at->diffInDays($lead->updated_at);
        });

        return round($totalDays / $leadsWithTimeData->count(), 2);
    }
}
