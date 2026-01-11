<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\Lead\Services\DealScoringService;

class CalculateDealScoresJob implements ShouldQueue
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
     * @param  int|null  $userId  Optional user ID to filter leads
     * @param  int|null  $pipelineId  Optional pipeline ID to filter leads
     */
    public function __construct(?int $userId = null, ?int $pipelineId = null)
    {
        $this->userId = $userId;
        $this->pipelineId = $pipelineId;
    }

    /**
     * Execute the job.
     *
     * @param  DealScoringService  $dealScoringService
     * @return void
     */
    public function handle(DealScoringService $dealScoringService): void
    {
        Log::info('Starting deal scores calculation job', [
            'user_id' => $this->userId,
            'pipeline_id' => $this->pipelineId,
            'started_at' => now()->toDateTimeString(),
        ]);

        try {
            // Calculate scores for all active leads (or filtered subset)
            $results = $dealScoringService->scoreAllActiveLeads($this->userId, $this->pipelineId);

            // Calculate statistics
            $totalLeads = $results->count();
            $successCount = $results->where('success', true)->count();
            $failureCount = $results->where('success', false)->count();

            // Get average score from successful calculations
            $scores = $results->where('success', true)
                ->pluck('score_data.score')
                ->filter();

            $averageScore = $scores->isEmpty() ? 0 : $scores->avg();
            $minScore = $scores->isEmpty() ? 0 : $scores->min();
            $maxScore = $scores->isEmpty() ? 0 : $scores->max();

            Log::info('Deal scores calculation completed successfully', [
                'user_id' => $this->userId,
                'pipeline_id' => $this->pipelineId,
                'total_leads' => $totalLeads,
                'successful' => $successCount,
                'failed' => $failureCount,
                'average_score' => round($averageScore, 2),
                'min_score' => round($minScore, 2),
                'max_score' => round($maxScore, 2),
                'completed_at' => now()->toDateTimeString(),
            ]);

            // Log any failures for investigation
            if ($failureCount > 0) {
                $failures = $results->where('success', false);

                foreach ($failures as $failure) {
                    Log::warning('Failed to calculate deal score', [
                        'lead_id' => $failure['lead_id'],
                        'error' => $failure['error'] ?? 'Unknown error',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error calculating deal scores', [
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
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Deal scores calculation job failed', [
            'user_id' => $this->userId,
            'pipeline_id' => $this->pipelineId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'failed_at' => now()->toDateTimeString(),
        ]);
    }
}
