<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('inbound-emails:process')->everyFiveMinutes();

        // Clean up expired data according to retention policies
        $schedule->command('compliance:cleanup-expired-data')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Sales Forecasting: Calculate deal scores for all active leads
        $schedule->job(new \App\Jobs\CalculateDealScoresJob())
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->name('calculate-deal-scores')
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Deal scores calculation scheduled job failed');
            });

        // Sales Forecasting: Refresh historical conversion data weekly
        $schedule->job(new \App\Jobs\RefreshHistoricalConversionsJob())
            ->weeklyOn(0, '03:00') // Sunday at 3:00 AM
            ->withoutOverlapping()
            ->runInBackground()
            ->name('refresh-historical-conversions')
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Historical conversions refresh scheduled job failed');
            });

        // Sales Forecasting: Track forecast actuals for completed periods
        $schedule->job(new \App\Jobs\TrackForecastActualsJob())
            ->dailyAt('04:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->name('track-forecast-actuals')
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Forecast actuals tracking scheduled job failed');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
