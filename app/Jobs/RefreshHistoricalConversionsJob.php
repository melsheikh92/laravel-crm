<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;

class RefreshHistoricalConversionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 300;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Minimum sample size for statistical significance.
     */
    const MIN_SAMPLE_SIZE = 5;

    /**
     * Number of days to analyze for historical data.
     *
     * @var int
     */
    protected int $analysisDays;

    /**
     * Optional user ID to filter leads.
     *
     * @var int|null
     */
    protected ?int $userId;

    /**
     * Optional pipeline ID to filter leads.
     *
     * @var int|null
     */
    protected ?int $pipelineId;

    /**
     * Create a new job instance.
     *
     * @param  int  $analysisDays  Number of days to analyze (default: 90)
     * @param  int|null  $userId  Optional user ID to filter leads
     * @param  int|null  $pipelineId  Optional pipeline ID to filter leads
     */
    public function __construct(int $analysisDays = 90, ?int $userId = null, ?int $pipelineId = null)
    {
        $this->analysisDays = $analysisDays;
        $this->userId = $userId;
        $this->pipelineId = $pipelineId;
    }

    /**
     * Execute the job.
     *
     * @param  LeadRepository  $leadRepository
     * @param  HistoricalConversionRepository  $historicalConversionRepository
     * @return void
     */
    public function handle(
        LeadRepository $leadRepository,
        HistoricalConversionRepository $historicalConversionRepository
    ): void {
        Log::info('Starting historical conversions refresh job', [
            'analysis_days' => $this->analysisDays,
            'user_id' => $this->userId,
            'pipeline_id' => $this->pipelineId,
            'started_at' => now()->toDateTimeString(),
        ]);

        try {
            $startDate = now()->subDays($this->analysisDays);
            $endDate = now();

            // Fetch leads for analysis
            $leads = $this->getLeadsForAnalysis($leadRepository, $startDate, $endDate);

            if ($leads->isEmpty()) {
                Log::info('No leads found for historical conversion refresh', [
                    'analysis_days' => $this->analysisDays,
                    'user_id' => $this->userId,
                    'pipeline_id' => $this->pipelineId,
                ]);

                return;
            }

            Log::info('Analyzing leads for historical conversions', [
                'total_leads' => $leads->count(),
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ]);

            // Calculate conversions
            $conversions = $this->calculateConversions($leads, $startDate, $endDate);

            if ($conversions->isEmpty()) {
                Log::info('No conversion data calculated', [
                    'total_leads' => $leads->count(),
                ]);

                return;
            }

            // Save conversions to database
            $results = $this->saveConversions($conversions, $historicalConversionRepository);

            Log::info('Historical conversions refresh completed successfully', [
                'analysis_days' => $this->analysisDays,
                'user_id' => $this->userId,
                'pipeline_id' => $this->pipelineId,
                'total_leads' => $leads->count(),
                'conversions_calculated' => $conversions->count(),
                'new_records' => $results['new'],
                'updated_records' => $results['updated'],
                'skipped_records' => $results['skipped'],
                'average_conversion_rate' => round($conversions->avg('conversion_rate'), 2),
                'completed_at' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error refreshing historical conversions', [
                'analysis_days' => $this->analysisDays,
                'user_id' => $this->userId,
                'pipeline_id' => $this->pipelineId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to trigger job retry
            throw $e;
        }
    }

    /**
     * Get leads for analysis.
     *
     * @param  LeadRepository  $leadRepository
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return \Illuminate\Support\Collection
     */
    protected function getLeadsForAnalysis(
        LeadRepository $leadRepository,
        Carbon $startDate,
        Carbon $endDate
    ) {
        $query = $leadRepository->model
            ->with(['stage', 'pipeline', 'user'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        if ($this->pipelineId) {
            $query->where('lead_pipeline_id', $this->pipelineId);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query->get();
    }

    /**
     * Calculate conversions from leads data.
     *
     * @param  \Illuminate\Support\Collection  $leads
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return \Illuminate\Support\Collection
     */
    protected function calculateConversions($leads, Carbon $startDate, Carbon $endDate)
    {
        $conversions = collect();

        // Group by stage, pipeline, and user
        $grouped = $leads->groupBy(function ($lead) {
            return sprintf(
                '%d-%d-%d',
                $lead->lead_pipeline_stage_id,
                $lead->lead_pipeline_id,
                $lead->user_id ?? 0
            );
        });

        foreach ($grouped as $key => $groupLeads) {
            $firstLead = $groupLeads->first();

            $stageId = $firstLead->lead_pipeline_stage_id;
            $pipelineId = $firstLead->lead_pipeline_id;
            $userId = $firstLead->user_id;

            // Skip if sample size is too small
            if ($groupLeads->count() < self::MIN_SAMPLE_SIZE) {
                continue;
            }

            // Calculate conversion rate (leads that moved to "won" stage)
            $wonLeads = $groupLeads->filter(function ($lead) {
                return $lead->stage && $lead->stage->code === 'won';
            });

            $conversionRate = ($groupLeads->count() > 0)
                ? round(($wonLeads->count() / $groupLeads->count()) * 100, 2)
                : 0.0;

            // Calculate average time in stage
            $averageTimeInStage = $this->calculateAverageTimeInStage($groupLeads);

            $conversions->push([
                'stage_id' => $stageId,
                'pipeline_id' => $pipelineId,
                'user_id' => $userId,
                'conversion_rate' => $conversionRate,
                'average_time_in_stage' => $averageTimeInStage,
                'sample_size' => $groupLeads->count(),
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ]);
        }

        return $conversions;
    }

    /**
     * Calculate average time in stage for a group of leads.
     *
     * @param  \Illuminate\Support\Collection  $leads
     * @return float
     */
    protected function calculateAverageTimeInStage($leads): float
    {
        $times = $leads->map(function ($lead) {
            if (! $lead->updated_at || ! $lead->created_at) {
                return 0;
            }

            // If lead is closed, calculate time from created to closed
            if ($lead->closed_at) {
                return $lead->created_at->diffInDays($lead->closed_at);
            }

            // Otherwise, calculate time from last update to now
            return $lead->updated_at->diffInDays(now());
        })->filter(fn ($time) => $time > 0);

        if ($times->isEmpty()) {
            return 0.0;
        }

        return round($times->avg(), 2);
    }

    /**
     * Save conversions to database.
     *
     * @param  \Illuminate\Support\Collection  $conversions
     * @param  HistoricalConversionRepository  $historicalConversionRepository
     * @return array
     */
    protected function saveConversions($conversions, HistoricalConversionRepository $historicalConversionRepository): array
    {
        $results = [
            'new' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($conversions as $conversion) {
            try {
                // Check if record exists
                $existing = $historicalConversionRepository->model
                    ->where('stage_id', $conversion['stage_id'])
                    ->where('pipeline_id', $conversion['pipeline_id'])
                    ->where('user_id', $conversion['user_id'])
                    ->where('period_start', $conversion['period_start'])
                    ->where('period_end', $conversion['period_end'])
                    ->first();

                if ($existing) {
                    // Update existing record
                    $existing->update([
                        'conversion_rate' => $conversion['conversion_rate'],
                        'average_time_in_stage' => $conversion['average_time_in_stage'],
                        'sample_size' => $conversion['sample_size'],
                    ]);

                    $results['updated']++;

                    Log::debug('Updated historical conversion record', [
                        'stage_id' => $conversion['stage_id'],
                        'pipeline_id' => $conversion['pipeline_id'],
                        'user_id' => $conversion['user_id'],
                        'conversion_rate' => $conversion['conversion_rate'],
                    ]);
                } else {
                    // Create new record
                    $historicalConversionRepository->create($conversion);

                    $results['new']++;

                    Log::debug('Created new historical conversion record', [
                        'stage_id' => $conversion['stage_id'],
                        'pipeline_id' => $conversion['pipeline_id'],
                        'user_id' => $conversion['user_id'],
                        'conversion_rate' => $conversion['conversion_rate'],
                    ]);
                }
            } catch (\Exception $e) {
                $results['skipped']++;

                Log::warning('Failed to save historical conversion record', [
                    'stage_id' => $conversion['stage_id'],
                    'pipeline_id' => $conversion['pipeline_id'],
                    'user_id' => $conversion['user_id'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Historical conversions refresh job failed', [
            'analysis_days' => $this->analysisDays,
            'user_id' => $this->userId,
            'pipeline_id' => $this->pipelineId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'failed_at' => now()->toDateTimeString(),
        ]);
    }
}
