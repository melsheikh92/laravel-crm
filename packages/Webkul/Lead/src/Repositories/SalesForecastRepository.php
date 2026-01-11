<?php

namespace Webkul\Lead\Repositories;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Webkul\Core\Eloquent\Repository;
use Webkul\Lead\Contracts\SalesForecast;

class SalesForecastRepository extends Repository
{
    /**
     * Cache prefix for forecasts.
     */
    const CACHE_PREFIX = 'sales_forecast:';

    /**
     * Cache TTL in seconds (10 minutes).
     */
    const CACHE_TTL = 600;

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return SalesForecast::class;
    }

    /**
     * Get forecasts by period type.
     *
     * @param  string  $periodType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPeriodType(string $periodType): Collection
    {
        return $this->getModel()
            ->byPeriodType($periodType)
            ->orderBy('period_start', 'desc')
            ->get();
    }

    /**
     * Get forecasts by user.
     *
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUser(int $userId): Collection
    {
        $cacheKey = $this->getCacheKey('user', ['user_id' => $userId]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            return $this->getModel()
                ->byUser($userId)
                ->orderBy('period_start', 'desc')
                ->get();
        });
    }

    /**
     * Get forecasts by team.
     *
     * @param  int  $teamId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTeam(int $teamId): Collection
    {
        $cacheKey = $this->getCacheKey('team', ['team_id' => $teamId]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($teamId) {
            return $this->getModel()
                ->byTeam($teamId)
                ->orderBy('period_start', 'desc')
                ->get();
        });
    }

    /**
     * Get forecasts within a date range.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->getModel()
            ->inDateRange($startDate, $endDate)
            ->orderBy('period_start', 'desc')
            ->get();
    }

    /**
     * Get forecasts by user and period type.
     *
     * @param  int  $userId
     * @param  string  $periodType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUserAndPeriod(int $userId, string $periodType): Collection
    {
        $cacheKey = $this->getCacheKey('user_period', [
            'user_id' => $userId,
            'period_type' => $periodType,
        ]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $periodType) {
            return $this->getModel()
                ->byUser($userId)
                ->byPeriodType($periodType)
                ->orderBy('period_start', 'desc')
                ->get();
        });
    }

    /**
     * Get forecasts by team and period type.
     *
     * @param  int  $teamId
     * @param  string  $periodType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTeamAndPeriod(int $teamId, string $periodType): Collection
    {
        $cacheKey = $this->getCacheKey('team_period', [
            'team_id' => $teamId,
            'period_type' => $periodType,
        ]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($teamId, $periodType) {
            return $this->getModel()
                ->byTeam($teamId)
                ->byPeriodType($periodType)
                ->orderBy('period_start', 'desc')
                ->get();
        });
    }

    /**
     * Get forecasts with filters.
     *
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWithFilters(array $filters): Collection
    {
        $query = $this->getModel()->query();

        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (isset($filters['team_id'])) {
            $query->byTeam($filters['team_id']);
        }

        if (isset($filters['period_type'])) {
            $query->byPeriodType($filters['period_type']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->inDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['with_actuals']) && $filters['with_actuals']) {
            $query->completed();
        }

        if (isset($filters['without_actuals']) && $filters['without_actuals']) {
            $query->pending();
        }

        if (isset($filters['high_confidence']) && $filters['high_confidence']) {
            $query->highConfidence();
        }

        return $query->orderBy('period_start', 'desc')->get();
    }

    /**
     * Get the latest forecast for a user and period type.
     *
     * @param  int  $userId
     * @param  string  $periodType
     * @return \Webkul\Lead\Contracts\SalesForecast|null
     */
    public function getLatestByUserAndPeriod(int $userId, string $periodType): ?SalesForecast
    {
        $cacheKey = $this->getCacheKey('latest_user', [
            'user_id' => $userId,
            'period_type' => $periodType,
        ]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $periodType) {
            return $this->getModel()
                ->byUser($userId)
                ->byPeriodType($periodType)
                ->orderBy('period_start', 'desc')
                ->first();
        });
    }

    /**
     * Get the latest forecast for a team and period type.
     *
     * @param  int  $teamId
     * @param  string  $periodType
     * @return \Webkul\Lead\Contracts\SalesForecast|null
     */
    public function getLatestByTeamAndPeriod(int $teamId, string $periodType): ?SalesForecast
    {
        $cacheKey = $this->getCacheKey('latest_team', [
            'team_id' => $teamId,
            'period_type' => $periodType,
        ]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($teamId, $periodType) {
            return $this->getModel()
                ->byTeam($teamId)
                ->byPeriodType($periodType)
                ->orderBy('period_start', 'desc')
                ->first();
        });
    }

    /**
     * Get completed forecasts (with actuals).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompleted(): Collection
    {
        return $this->getModel()
            ->completed()
            ->with('actuals')
            ->orderBy('period_start', 'desc')
            ->get();
    }

    /**
     * Get pending forecasts (without actuals).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPending(): Collection
    {
        return $this->getModel()
            ->pending()
            ->orderBy('period_start', 'desc')
            ->get();
    }

    /**
     * Get high confidence forecasts.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHighConfidence(): Collection
    {
        return $this->getModel()
            ->highConfidence()
            ->orderBy('confidence_score', 'desc')
            ->get();
    }

    /**
     * Get forecasts for a specific period range.
     *
     * @param  string  $periodType
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPeriodRange(string $periodType, string $startDate, string $endDate): Collection
    {
        return $this->getModel()
            ->byPeriodType($periodType)
            ->inDateRange($startDate, $endDate)
            ->orderBy('period_start', 'desc')
            ->get();
    }

    /**
     * Get forecast statistics by user.
     *
     * @param  int  $userId
     * @return array
     */
    public function getStatsByUser(int $userId): array
    {
        $forecasts = $this->getModel()
            ->byUser($userId)
            ->with('actuals')
            ->get();

        $completedForecasts = $forecasts->filter(fn($f) => $f->hasActuals());

        return [
            'total_forecasts' => $forecasts->count(),
            'completed_forecasts' => $completedForecasts->count(),
            'pending_forecasts' => $forecasts->count() - $completedForecasts->count(),
            'average_confidence' => $forecasts->avg('confidence_score'),
            'total_forecast_value' => $forecasts->sum('forecast_value'),
            'total_weighted_forecast' => $forecasts->sum('weighted_forecast'),
            'total_best_case' => $forecasts->sum('best_case'),
            'total_worst_case' => $forecasts->sum('worst_case'),
            'average_accuracy' => $completedForecasts->map(fn($f) => $f->getAccuracyScore())->filter()->avg(),
        ];
    }

    /**
     * Get forecast statistics by team.
     *
     * @param  int  $teamId
     * @return array
     */
    public function getStatsByTeam(int $teamId): array
    {
        $forecasts = $this->getModel()
            ->byTeam($teamId)
            ->with('actuals')
            ->get();

        $completedForecasts = $forecasts->filter(fn($f) => $f->hasActuals());

        return [
            'total_forecasts' => $forecasts->count(),
            'completed_forecasts' => $completedForecasts->count(),
            'pending_forecasts' => $forecasts->count() - $completedForecasts->count(),
            'average_confidence' => $forecasts->avg('confidence_score'),
            'total_forecast_value' => $forecasts->sum('forecast_value'),
            'total_weighted_forecast' => $forecasts->sum('weighted_forecast'),
            'total_best_case' => $forecasts->sum('best_case'),
            'total_worst_case' => $forecasts->sum('worst_case'),
            'average_accuracy' => $completedForecasts->map(fn($f) => $f->getAccuracyScore())->filter()->avg(),
        ];
    }

    /**
     * Find forecast by user, period type and period start date.
     *
     * @param  int  $userId
     * @param  string  $periodType
     * @param  string  $periodStart
     * @return \Webkul\Lead\Contracts\SalesForecast|null
     */
    public function findByUserAndPeriod(int $userId, string $periodType, string $periodStart): ?SalesForecast
    {
        return $this->getModel()
            ->where('user_id', $userId)
            ->where('period_type', $periodType)
            ->where('period_start', $periodStart)
            ->first();
    }

    /**
     * Find forecast by team, period type and period start date.
     *
     * @param  int  $teamId
     * @param  string  $periodType
     * @param  string  $periodStart
     * @return \Webkul\Lead\Contracts\SalesForecast|null
     */
    public function findByTeamAndPeriod(int $teamId, string $periodType, string $periodStart): ?SalesForecast
    {
        return $this->getModel()
            ->where('team_id', $teamId)
            ->where('period_type', $periodType)
            ->where('period_start', $periodStart)
            ->first();
    }

    /**
     * Delete old forecasts.
     *
     * @param  int  $daysOld
     * @return int
     */
    public function deleteOldForecasts(int $daysOld = 365): int
    {
        return $this->getModel()
            ->where('period_end', '<', now()->subDays($daysOld))
            ->delete();
    }

    /**
     * Generate cache key.
     *
     * @param  string  $type
     * @param  array  $params
     * @return string
     */
    public function getCacheKey($type, $params = null)
    {
        $key = self::CACHE_PREFIX . $type;
        $params = $params ?? [];

        if (!empty($params)) {
            ksort($params);
            $key .= ':' . md5(json_encode($params));
        }

        return $key;
    }

    /**
     * Clear cache for a specific user.
     *
     * @param  int  $userId
     * @return void
     */
    public function clearUserCache(int $userId): void
    {
        // Clear all user-related cache keys
        $patterns = ['user', 'user_period', 'latest_user'];

        foreach ($patterns as $pattern) {
            Cache::forget($this->getCacheKey($pattern, ['user_id' => $userId]));
        }

        // Also clear for all period types
        foreach (['week', 'month', 'quarter'] as $periodType) {
            Cache::forget($this->getCacheKey('user_period', [
                'user_id' => $userId,
                'period_type' => $periodType,
            ]));

            Cache::forget($this->getCacheKey('latest_user', [
                'user_id' => $userId,
                'period_type' => $periodType,
            ]));
        }
    }

    /**
     * Clear cache for a specific team.
     *
     * @param  int  $teamId
     * @return void
     */
    public function clearTeamCache(int $teamId): void
    {
        // Clear all team-related cache keys
        $patterns = ['team', 'team_period', 'latest_team'];

        foreach ($patterns as $pattern) {
            Cache::forget($this->getCacheKey($pattern, ['team_id' => $teamId]));
        }

        // Also clear for all period types
        foreach (['week', 'month', 'quarter'] as $periodType) {
            Cache::forget($this->getCacheKey('team_period', [
                'team_id' => $teamId,
                'period_type' => $periodType,
            ]));

            Cache::forget($this->getCacheKey('latest_team', [
                'team_id' => $teamId,
                'period_type' => $periodType,
            ]));
        }
    }

    /**
     * Clear all forecast cache.
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }
}
