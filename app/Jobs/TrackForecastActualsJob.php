<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\Lead\Repositories\SalesForecastRepository;
use Webkul\Lead\Services\ForecastAccuracyService;

class TrackForecastActualsJob implements ShouldQueue
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
     * Optional forecast ID to process specific forecast.
     *
     * @var int|null
     */
    protected ?int $forecastId;

    /**
     * Days of grace period after period end before tracking actuals.
     *
     * @var int
     */
    protected int $gracePeriodDays;

    /**
     * Create a new job instance.
     *
     * @param  int|null  $forecastId  Optional forecast ID to process specific forecast
     * @param  int  $gracePeriodDays  Days of grace period after period end (default: 1)
     */
    public function __construct(?int $forecastId = null, int $gracePeriodDays = 1)
    {
        $this->forecastId = $forecastId;
        $this->gracePeriodDays = $gracePeriodDays;
    }

    /**
     * Execute the job.
     *
     * @param  SalesForecastRepository  $salesForecastRepository
     * @param  ForecastAccuracyService  $forecastAccuracyService
     * @return void
     */
    public function handle(
        SalesForecastRepository $salesForecastRepository,
        ForecastAccuracyService $forecastAccuracyService
    ): void {
        Log::info('Starting forecast actuals tracking job', [
            'forecast_id' => $this->forecastId,
            'grace_period_days' => $this->gracePeriodDays,
            'started_at' => now()->toDateTimeString(),
        ]);

        try {
            // Get forecasts that need actual tracking
            $forecasts = $this->getForecastsToProcess($salesForecastRepository);

            if ($forecasts->isEmpty()) {
                Log::info('No forecasts found that need actual tracking', [
                    'forecast_id' => $this->forecastId,
                ]);

                return;
            }

            $results = [];
            $successCount = 0;
            $failureCount = 0;

            // Process each forecast
            foreach ($forecasts as $forecast) {
                try {
                    $actual = $forecastAccuracyService->trackActual($forecast->id);

                    $results[] = [
                        'forecast_id' => $forecast->id,
                        'success' => true,
                        'actual_value' => (float) $actual->actual_value,
                        'variance' => (float) $actual->variance,
                        'variance_percentage' => (float) $actual->variance_percentage,
                        'accuracy_level' => $actual->getAccuracyLevel(),
                    ];

                    $successCount++;

                    Log::info('Forecast actual tracked successfully', [
                        'forecast_id' => $forecast->id,
                        'user_id' => $forecast->user_id,
                        'team_id' => $forecast->team_id,
                        'period_type' => $forecast->period_type,
                        'period_start' => $forecast->period_start->format('Y-m-d'),
                        'period_end' => $forecast->period_end->format('Y-m-d'),
                        'forecast_value' => (float) $forecast->forecast_value,
                        'actual_value' => (float) $actual->actual_value,
                        'variance_percentage' => (float) $actual->variance_percentage,
                        'accuracy_level' => $actual->getAccuracyLevel(),
                    ]);
                } catch (\Exception $e) {
                    $results[] = [
                        'forecast_id' => $forecast->id,
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];

                    $failureCount++;

                    Log::warning('Failed to track forecast actual', [
                        'forecast_id' => $forecast->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Calculate statistics
            $totalForecasts = $forecasts->count();
            $successfulTracking = collect($results)->where('success', true);

            $avgVariancePercentage = $successfulTracking->isNotEmpty()
                ? $successfulTracking->pluck('variance_percentage')->map(fn ($v) => abs($v))->avg()
                : 0;

            $highAccuracyCount = $successfulTracking->where('accuracy_level', 'high')->count();
            $moderateAccuracyCount = $successfulTracking->where('accuracy_level', 'moderate')->count();
            $poorAccuracyCount = $successfulTracking->where('accuracy_level', 'poor')->count();

            Log::info('Forecast actuals tracking completed successfully', [
                'forecast_id' => $this->forecastId,
                'total_forecasts' => $totalForecasts,
                'successful' => $successCount,
                'failed' => $failureCount,
                'average_variance_percentage' => round($avgVariancePercentage, 2),
                'high_accuracy_count' => $highAccuracyCount,
                'moderate_accuracy_count' => $moderateAccuracyCount,
                'poor_accuracy_count' => $poorAccuracyCount,
                'completed_at' => now()->toDateTimeString(),
            ]);

            // Log any failures for investigation
            if ($failureCount > 0) {
                $failures = collect($results)->where('success', false);

                foreach ($failures as $failure) {
                    Log::warning('Forecast actual tracking failed', [
                        'forecast_id' => $failure['forecast_id'],
                        'error' => $failure['error'] ?? 'Unknown error',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error tracking forecast actuals', [
                'forecast_id' => $this->forecastId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to trigger job retry
            throw $e;
        }
    }

    /**
     * Get forecasts that need actual tracking.
     *
     * @param  SalesForecastRepository  $salesForecastRepository
     * @return \Illuminate\Support\Collection
     */
    protected function getForecastsToProcess(SalesForecastRepository $salesForecastRepository)
    {
        // If specific forecast ID provided, only process that one
        if ($this->forecastId) {
            $forecast = $salesForecastRepository->findOrFail($this->forecastId);

            // Check if forecast period has ended
            if (!$forecast->isPeriodEnded()) {
                Log::warning('Forecast period has not ended yet', [
                    'forecast_id' => $this->forecastId,
                    'period_end' => $forecast->period_end->format('Y-m-d'),
                ]);

                return collect([]);
            }

            // Check if already has actuals
            if ($forecast->hasActuals()) {
                Log::info('Forecast already has actuals recorded', [
                    'forecast_id' => $this->forecastId,
                ]);

                return collect([]);
            }

            return collect([$forecast]);
        }

        // Get all forecasts that meet the criteria
        $gracePeriodDate = now()->subDays($this->gracePeriodDays)->format('Y-m-d');

        return $salesForecastRepository->model
            ->pending() // Forecasts without actuals
            ->where('period_end', '<=', $gracePeriodDate) // Period has ended (with grace period)
            ->orderBy('period_end', 'asc')
            ->get();
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Forecast actuals tracking job failed', [
            'forecast_id' => $this->forecastId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'failed_at' => now()->toDateTimeString(),
        ]);
    }
}
