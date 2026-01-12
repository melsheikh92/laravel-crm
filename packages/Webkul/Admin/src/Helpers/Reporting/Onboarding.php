<?php

namespace Webkul\Admin\Helpers\Reporting;

use App\Models\OnboardingProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Onboarding extends AbstractReporting
{
    /**
     * Get completion rate progress (current vs previous period).
     *
     * @return array
     */
    public function getCompletionRateProgress(): array
    {
        $currentStats = $this->getCompletionStatsForPeriod($this->startDate, $this->endDate);
        $previousStats = $this->getCompletionStatsForPeriod($this->lastStartDate, $this->lastEndDate);

        $currentRate = $currentStats['completion_rate'];
        $previousRate = $previousStats['completion_rate'];

        $progress = $previousRate > 0
            ? (($currentRate - $previousRate) / $previousRate) * 100
            : ($currentRate > 0 ? 100 : 0);

        return [
            'current' => round($currentRate, 2),
            'previous' => round($previousRate, 2),
            'progress' => round($progress, 2),
            'formatted_total' => round($currentRate, 1).'%',
        ];
    }

    /**
     * Get average completion time progress (current vs previous period).
     *
     * @return array
     */
    public function getAverageCompletionTimeProgress(): array
    {
        $currentTime = $this->getAverageCompletionTimeForPeriod($this->startDate, $this->endDate);
        $previousTime = $this->getAverageCompletionTimeForPeriod($this->lastStartDate, $this->lastEndDate);

        $progress = $previousTime > 0
            ? (($currentTime - $previousTime) / $previousTime) * 100
            : ($currentTime > 0 ? 100 : 0);

        // Negative progress is good here (less time = better)
        $progress = -$progress;

        return [
            'current' => round($currentTime, 2),
            'previous' => round($previousTime, 2),
            'progress' => round($progress, 2),
            'formatted_total' => $this->formatTime($currentTime),
        ];
    }

    /**
     * Get total users who started onboarding progress.
     *
     * @return array
     */
    public function getTotalStartedProgress(): array
    {
        $current = OnboardingProgress::startedBetween($this->startDate, $this->endDate)->count();
        $previous = OnboardingProgress::startedBetween($this->lastStartDate, $this->lastEndDate)->count();

        $progress = $previous > 0
            ? (($current - $previous) / $previous) * 100
            : ($current > 0 ? 100 : 0);

        return [
            'current' => $current,
            'previous' => $previous,
            'progress' => round($progress, 2),
            'formatted_total' => number_format($current),
        ];
    }

    /**
     * Get total users who completed onboarding progress.
     *
     * @return array
     */
    public function getTotalCompletedProgress(): array
    {
        $current = OnboardingProgress::completed()
            ->completedBetween($this->startDate, $this->endDate)
            ->count();

        $previous = OnboardingProgress::completed()
            ->completedBetween($this->lastStartDate, $this->lastEndDate)
            ->count();

        $progress = $previous > 0
            ? (($current - $previous) / $previous) * 100
            : ($current > 0 ? 100 : 0);

        return [
            'current' => $current,
            'previous' => $previous,
            'progress' => round($progress, 2),
            'formatted_total' => number_format($current),
        ];
    }

    /**
     * Get step analytics (completion rates per step).
     *
     * @return array
     */
    public function getStepAnalytics(): array
    {
        $steps = config('onboarding.steps', []);
        $analytics = [];

        $totalProgress = OnboardingProgress::startedBetween($this->startDate, $this->endDate)->get();

        if ($totalProgress->isEmpty()) {
            return [];
        }

        $total = $totalProgress->count();

        foreach ($steps as $stepKey => $stepConfig) {
            $completed = $totalProgress->filter(function ($progress) use ($stepKey) {
                return $progress->isStepCompleted($stepKey);
            })->count();

            $skipped = $totalProgress->filter(function ($progress) use ($stepKey) {
                return $progress->isStepSkipped($stepKey);
            })->count();

            $analytics[$stepKey] = [
                'title' => $stepConfig['title'] ?? ucfirst(str_replace('_', ' ', $stepKey)),
                'completed' => $completed,
                'skipped' => $skipped,
                'completion_rate' => round(($completed / $total) * 100, 1),
                'skip_rate' => round(($skipped / $total) * 100, 1),
            ];
        }

        return $analytics;
    }

    /**
     * Get completion statistics for a specific period.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return array
     */
    protected function getCompletionStatsForPeriod(Carbon $startDate, Carbon $endDate): array
    {
        $total = OnboardingProgress::startedBetween($startDate, $endDate)->count();
        $completed = OnboardingProgress::completed()
            ->completedBetween($startDate, $endDate)
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'completion_rate' => $total > 0 ? ($completed / $total) * 100 : 0,
        ];
    }

    /**
     * Get average completion time for a specific period (in hours).
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return float
     */
    protected function getAverageCompletionTimeForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        $completed = OnboardingProgress::completed()
            ->completedBetween($startDate, $endDate)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get();

        if ($completed->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        foreach ($completed as $progress) {
            $totalMinutes += $progress->started_at->diffInMinutes($progress->completed_at);
        }

        return round($totalMinutes / $completed->count() / 60, 2);
    }

    /**
     * Format time for display.
     *
     * @param  float  $hours
     * @return string
     */
    protected function formatTime(float $hours): string
    {
        if ($hours == 0) {
            return '0h';
        }

        if ($hours < 1) {
            return round($hours * 60).'m';
        }

        if ($hours < 24) {
            $h = floor($hours);
            $m = round(($hours - $h) * 60);
            return $m > 0 ? "{$h}h {$m}m" : "{$h}h";
        }

        $days = floor($hours / 24);
        $remainingHours = round($hours - ($days * 24));
        return $remainingHours > 0 ? "{$days}d {$remainingHours}h" : "{$days}d";
    }
}
