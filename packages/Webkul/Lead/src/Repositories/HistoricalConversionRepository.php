<?php

namespace Webkul\Lead\Repositories;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Webkul\Core\Eloquent\Repository;
use Webkul\Lead\Contracts\HistoricalConversion;

class HistoricalConversionRepository extends Repository
{
    /**
     * Cache prefix for historical conversions.
     */
    const CACHE_PREFIX = 'historical_conversion:';

    /**
     * Cache TTL in seconds (15 minutes).
     */
    const CACHE_TTL = 900;
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
        return HistoricalConversion::class;
    }

    /**
     * Get conversion data by stage.
     *
     * @param  int  $stageId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStage(int $stageId): Collection
    {
        $cacheKey = $this->getCacheKey('stage', ['stage_id' => $stageId]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($stageId) {
            return $this->getModel()
                ->byStage($stageId)
                ->latest()
                ->get();
        });
    }

    /**
     * Get conversion data by pipeline.
     *
     * @param  int  $pipelineId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPipeline(int $pipelineId): Collection
    {
        $cacheKey = $this->getCacheKey('pipeline', ['pipeline_id' => $pipelineId]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($pipelineId) {
            return $this->getModel()
                ->byPipeline($pipelineId)
                ->latest()
                ->get();
        });
    }

    /**
     * Get conversion data by user.
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
                ->latest()
                ->get();
        });
    }

    /**
     * Get conversion data within a date range.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->getModel()
            ->inDateRange($startDate, $endDate)
            ->latest()
            ->get();
    }

    /**
     * Get high conversion rates.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHighConversionRates(): Collection
    {
        return $this->getModel()
            ->highConversionRate()
            ->current()
            ->significant()
            ->latest()
            ->get();
    }

    /**
     * Get medium conversion rates.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMediumConversionRates(): Collection
    {
        return $this->getModel()
            ->mediumConversionRate()
            ->current()
            ->significant()
            ->latest()
            ->get();
    }

    /**
     * Get low conversion rates.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowConversionRates(): Collection
    {
        return $this->getModel()
            ->lowConversionRate()
            ->current()
            ->significant()
            ->latest()
            ->get();
    }

    /**
     * Get current conversion data.
     *
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCurrent(int $days = 90): Collection
    {
        return $this->getModel()
            ->current($days)
            ->latest()
            ->get();
    }

    /**
     * Get stale conversion data.
     *
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStale(int $days = 90): Collection
    {
        return $this->getModel()
            ->stale($days)
            ->latest()
            ->get();
    }

    /**
     * Get statistically significant conversion data.
     *
     * @param  int  $minSampleSize
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSignificant(int $minSampleSize = 30): Collection
    {
        return $this->getModel()
            ->significant($minSampleSize)
            ->current()
            ->latest()
            ->get();
    }

    /**
     * Get fast-moving stages.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFastMoving(): Collection
    {
        return $this->getModel()
            ->fastMoving()
            ->current()
            ->significant()
            ->latest()
            ->get();
    }

    /**
     * Get slow-moving stages.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSlowMoving(): Collection
    {
        return $this->getModel()
            ->slowMoving()
            ->current()
            ->significant()
            ->latest()
            ->get();
    }

    /**
     * Get the latest conversion data for each stage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestForEachStage(): Collection
    {
        return $this->getModel()
            ->latestForEachStage()
            ->with(['stage', 'pipeline', 'user'])
            ->get();
    }

    /**
     * Get the latest conversion data for each pipeline.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestForEachPipeline(): Collection
    {
        return $this->getModel()
            ->latestForEachPipeline()
            ->with(['pipeline', 'user'])
            ->get();
    }

    /**
     * Get conversion data with filters.
     *
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWithFilters(array $filters): Collection
    {
        $query = $this->getModel()->query();

        if (isset($filters['stage_id'])) {
            $query->byStage($filters['stage_id']);
        }

        if (isset($filters['pipeline_id'])) {
            $query->byPipeline($filters['pipeline_id']);
        }

        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->inDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['conversion_rate_level'])) {
            match ($filters['conversion_rate_level']) {
                'high' => $query->highConversionRate(),
                'medium' => $query->mediumConversionRate(),
                'low' => $query->lowConversionRate(),
                default => null,
            };
        }

        if (isset($filters['min_conversion_rate'])) {
            $query->where('conversion_rate', '>=', $filters['min_conversion_rate']);
        }

        if (isset($filters['max_conversion_rate'])) {
            $query->where('conversion_rate', '<=', $filters['max_conversion_rate']);
        }

        if (isset($filters['min_sample_size'])) {
            $query->significant($filters['min_sample_size']);
        }

        if (isset($filters['current']) && $filters['current']) {
            $days = $filters['current_days'] ?? 90;
            $query->current($days);
        }

        if (isset($filters['stale']) && $filters['stale']) {
            $days = $filters['stale_days'] ?? 90;
            $query->stale($days);
        }

        if (isset($filters['velocity'])) {
            match ($filters['velocity']) {
                'fast' => $query->fastMoving(),
                'slow' => $query->slowMoving(),
                default => null,
            };
        }

        if (isset($filters['max_time_in_stage'])) {
            $query->where('average_time_in_stage', '<=', $filters['max_time_in_stage']);
        }

        if (isset($filters['min_time_in_stage'])) {
            $query->where('average_time_in_stage', '>=', $filters['min_time_in_stage']);
        }

        $query->with(['stage', 'pipeline', 'user']);

        return $query->latest()->get();
    }

    /**
     * Get the latest conversion for a specific stage.
     *
     * @param  int  $stageId
     * @return \Webkul\Lead\Contracts\HistoricalConversion|null
     */
    public function getLatestByStage(int $stageId): ?HistoricalConversion
    {
        return $this->getModel()
            ->byStage($stageId)
            ->latest()
            ->first();
    }

    /**
     * Get the latest conversion for a specific pipeline.
     *
     * @param  int  $pipelineId
     * @return \Webkul\Lead\Contracts\HistoricalConversion|null
     */
    public function getLatestByPipeline(int $pipelineId): ?HistoricalConversion
    {
        return $this->getModel()
            ->byPipeline($pipelineId)
            ->latest()
            ->first();
    }

    /**
     * Get the latest conversion for a specific user.
     *
     * @param  int  $userId
     * @return \Webkul\Lead\Contracts\HistoricalConversion|null
     */
    public function getLatestByUser(int $userId): ?HistoricalConversion
    {
        return $this->getModel()
            ->byUser($userId)
            ->latest()
            ->first();
    }

    /**
     * Get conversion rate for a stage, pipeline, and user combination.
     *
     * @param  int  $stageId
     * @param  int  $pipelineId
     * @param  int|null  $userId
     * @return float|null
     */
    public function getConversionRate(int $stageId, int $pipelineId, ?int $userId = null): ?float
    {
        $cacheKey = $this->getCacheKey('conversion_rate', [
            'stage_id' => $stageId,
            'pipeline_id' => $pipelineId,
            'user_id' => $userId,
        ]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($stageId, $pipelineId, $userId) {
            $query = $this->getModel()
                ->byStage($stageId)
                ->byPipeline($pipelineId)
                ->current()
                ->significant();

            if ($userId) {
                $query->byUser($userId);
            }

            $conversion = $query->latest()->first();

            return $conversion ? $conversion->conversion_rate : null;
        });
    }

    /**
     * Get average time in stage for a specific stage.
     *
     * @param  int  $stageId
     * @param  int|null  $pipelineId
     * @param  int|null  $userId
     * @return float|null
     */
    public function getAverageTimeInStage(int $stageId, ?int $pipelineId = null, ?int $userId = null): ?float
    {
        $cacheKey = $this->getCacheKey('avg_time', [
            'stage_id' => $stageId,
            'pipeline_id' => $pipelineId,
            'user_id' => $userId,
        ]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($stageId, $pipelineId, $userId) {
            $query = $this->getModel()
                ->byStage($stageId)
                ->current()
                ->significant();

            if ($pipelineId) {
                $query->byPipeline($pipelineId);
            }

            if ($userId) {
                $query->byUser($userId);
            }

            $conversion = $query->latest()->first();

            return $conversion ? $conversion->average_time_in_stage : null;
        });
    }

    /**
     * Get conversion statistics by stage.
     *
     * @param  int  $stageId
     * @return array
     */
    public function getStatsByStage(int $stageId): array
    {
        $cacheKey = $this->getCacheKey('stats_stage', ['stage_id' => $stageId]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($stageId) {
            $conversions = $this->getModel()
                ->byStage($stageId)
                ->current()
                ->significant()
                ->get();

            if ($conversions->isEmpty()) {
                return [
                    'total_records' => 0,
                    'average_conversion_rate' => null,
                    'average_time_in_stage' => null,
                    'total_sample_size' => 0,
                ];
            }

            return [
                'total_records' => $conversions->count(),
                'average_conversion_rate' => round($conversions->avg('conversion_rate'), 2),
                'min_conversion_rate' => round($conversions->min('conversion_rate'), 2),
                'max_conversion_rate' => round($conversions->max('conversion_rate'), 2),
                'average_time_in_stage' => round($conversions->avg('average_time_in_stage'), 2),
                'min_time_in_stage' => round($conversions->min('average_time_in_stage'), 2),
                'max_time_in_stage' => round($conversions->max('average_time_in_stage'), 2),
                'total_sample_size' => $conversions->sum('sample_size'),
                'average_sample_size' => round($conversions->avg('sample_size'), 0),
            ];
        });
    }

    /**
     * Get conversion statistics by pipeline.
     *
     * @param  int  $pipelineId
     * @return array
     */
    public function getStatsByPipeline(int $pipelineId): array
    {
        $cacheKey = $this->getCacheKey('stats_pipeline', ['pipeline_id' => $pipelineId]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($pipelineId) {
            $conversions = $this->getModel()
                ->byPipeline($pipelineId)
                ->current()
                ->significant()
                ->get();

            if ($conversions->isEmpty()) {
                return [
                    'total_records' => 0,
                    'average_conversion_rate' => null,
                    'average_time_in_stage' => null,
                    'total_sample_size' => 0,
                ];
            }

            return [
                'total_records' => $conversions->count(),
                'average_conversion_rate' => round($conversions->avg('conversion_rate'), 2),
                'min_conversion_rate' => round($conversions->min('conversion_rate'), 2),
                'max_conversion_rate' => round($conversions->max('conversion_rate'), 2),
                'average_time_in_stage' => round($conversions->avg('average_time_in_stage'), 2),
                'min_time_in_stage' => round($conversions->min('average_time_in_stage'), 2),
                'max_time_in_stage' => round($conversions->max('average_time_in_stage'), 2),
                'total_sample_size' => $conversions->sum('sample_size'),
                'average_sample_size' => round($conversions->avg('sample_size'), 0),
            ];
        });
    }

    /**
     * Get conversion statistics by user.
     *
     * @param  int  $userId
     * @return array
     */
    public function getStatsByUser(int $userId): array
    {
        $cacheKey = $this->getCacheKey('stats_user', ['user_id' => $userId]);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            $conversions = $this->getModel()
                ->byUser($userId)
                ->current()
                ->significant()
                ->get();

            if ($conversions->isEmpty()) {
                return [
                    'total_records' => 0,
                    'average_conversion_rate' => null,
                    'average_time_in_stage' => null,
                    'total_sample_size' => 0,
                ];
            }

            return [
                'total_records' => $conversions->count(),
                'average_conversion_rate' => round($conversions->avg('conversion_rate'), 2),
                'min_conversion_rate' => round($conversions->min('conversion_rate'), 2),
                'max_conversion_rate' => round($conversions->max('conversion_rate'), 2),
                'average_time_in_stage' => round($conversions->avg('average_time_in_stage'), 2),
                'min_time_in_stage' => round($conversions->min('average_time_in_stage'), 2),
                'max_time_in_stage' => round($conversions->max('average_time_in_stage'), 2),
                'total_sample_size' => $conversions->sum('sample_size'),
                'average_sample_size' => round($conversions->avg('sample_size'), 0),
            ];
        });
    }

    /**
     * Get overall conversion statistics.
     *
     * @return array
     */
    public function getOverallStatistics(): array
    {
        $conversions = $this->getModel()
            ->current()
            ->significant()
            ->get();

        if ($conversions->isEmpty()) {
            return [
                'total_records' => 0,
                'average_conversion_rate' => null,
                'average_time_in_stage' => null,
                'total_sample_size' => 0,
            ];
        }

        return [
            'total_records' => $conversions->count(),
            'average_conversion_rate' => round($conversions->avg('conversion_rate'), 2),
            'min_conversion_rate' => round($conversions->min('conversion_rate'), 2),
            'max_conversion_rate' => round($conversions->max('conversion_rate'), 2),
            'average_time_in_stage' => round($conversions->avg('average_time_in_stage'), 2),
            'min_time_in_stage' => round($conversions->min('average_time_in_stage'), 2),
            'max_time_in_stage' => round($conversions->max('average_time_in_stage'), 2),
            'total_sample_size' => $conversions->sum('sample_size'),
            'average_sample_size' => round($conversions->avg('sample_size'), 0),
            'high_conversion_count' => $conversions->filter(fn($c) => $c->isHighConversionRate())->count(),
            'medium_conversion_count' => $conversions->filter(fn($c) => $c->isMediumConversionRate())->count(),
            'low_conversion_count' => $conversions->filter(fn($c) => $c->isLowConversionRate())->count(),
            'fast_moving_count' => $conversions->filter(fn($c) => $c->isFastMovingStage())->count(),
            'slow_moving_count' => $conversions->filter(fn($c) => $c->isSlowMovingStage())->count(),
        ];
    }

    /**
     * Find or create a conversion record.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Webkul\Lead\Contracts\HistoricalConversion
     */
    public function findOrCreate(array $attributes, array $values = []): HistoricalConversion
    {
        $conversion = $this->getModel()
            ->where($attributes)
            ->first();

        if ($conversion) {
            $conversion->update($values);
            return $conversion;
        }

        return $this->create(array_merge($attributes, $values));
    }

    /**
     * Update or create a conversion record for a stage.
     *
     * @param  int  $stageId
     * @param  int  $pipelineId
     * @param  int|null  $userId
     * @param  array  $data
     * @return \Webkul\Lead\Contracts\HistoricalConversion
     */
    public function updateOrCreateForStage(int $stageId, int $pipelineId, ?int $userId, array $data): HistoricalConversion
    {
        $attributes = [
            'stage_id' => $stageId,
            'pipeline_id' => $pipelineId,
            'user_id' => $userId,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
        ];

        $values = [
            'conversion_rate' => $data['conversion_rate'],
            'average_time_in_stage' => $data['average_time_in_stage'],
            'sample_size' => $data['sample_size'],
        ];

        $conversion = $this->getModel()->updateOrCreate($attributes, $values);

        // Clear related caches
        $this->clearCacheForStage($stageId);
        $this->clearCacheForPipeline($pipelineId);
        if ($userId) {
            $this->clearCacheForUser($userId);
        }

        return $conversion;
    }

    /**
     * Delete stale conversion data older than specified days.
     *
     * @param  int  $days
     * @return int
     */
    public function deleteStaleData(int $days = 365): int
    {
        return $this->getModel()
            ->stale($days)
            ->delete();
    }

    /**
     * Check if conversion data exists for a stage.
     *
     * @param  int  $stageId
     * @param  int|null  $pipelineId
     * @param  int|null  $userId
     * @return bool
     */
    public function hasData(int $stageId, ?int $pipelineId = null, ?int $userId = null): bool
    {
        $query = $this->getModel()->byStage($stageId)->current();

        if ($pipelineId) {
            $query->byPipeline($pipelineId);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        return $query->exists();
    }

    /**
     * Get conversion trend over time for a stage.
     *
     * @param  int  $stageId
     * @param  int  $months
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrendByStage(int $stageId, int $months = 6): Collection
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        return $this->getModel()
            ->byStage($stageId)
            ->where('period_start', '>=', $startDate)
            ->orderBy('period_start', 'asc')
            ->get();
    }

    /**
     * Get conversion trend over time for a pipeline.
     *
     * @param  int  $pipelineId
     * @param  int  $months
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrendByPipeline(int $pipelineId, int $months = 6): Collection
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        return $this->getModel()
            ->byPipeline($pipelineId)
            ->where('period_start', '>=', $startDate)
            ->orderBy('period_start', 'asc')
            ->get();
    }

    /**
     * Get conversion data that needs refreshing.
     *
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNeedingRefresh(int $days = 30): Collection
    {
        return $this->getModel()
            ->where('period_end', '<', now()->subDays($days))
            ->latest()
            ->get();
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
     * Clear cache for a specific stage.
     *
     * @param  int  $stageId
     * @return void
     */
    public function clearCacheForStage(int $stageId): void
    {
        // Clear stage-specific cache keys
        Cache::forget($this->getCacheKey('stage', ['stage_id' => $stageId]));
        Cache::forget($this->getCacheKey('stats_stage', ['stage_id' => $stageId]));
    }

    /**
     * Clear cache for a specific pipeline.
     *
     * @param  int  $pipelineId
     * @return void
     */
    public function clearCacheForPipeline(int $pipelineId): void
    {
        // Clear pipeline-specific cache keys
        Cache::forget($this->getCacheKey('pipeline', ['pipeline_id' => $pipelineId]));
        Cache::forget($this->getCacheKey('stats_pipeline', ['pipeline_id' => $pipelineId]));
    }

    /**
     * Clear cache for a specific user.
     *
     * @param  int  $userId
     * @return void
     */
    public function clearCacheForUser(int $userId): void
    {
        // Clear user-specific cache keys
        Cache::forget($this->getCacheKey('user', ['user_id' => $userId]));
        Cache::forget($this->getCacheKey('stats_user', ['user_id' => $userId]));
    }

    /**
     * Clear all conversion-related cache.
     *
     * @param  int  $stageId
     * @param  int  $pipelineId
     * @param  int|null  $userId
     * @return void
     */
    public function clearAllRelatedCache(int $stageId, int $pipelineId, ?int $userId = null): void
    {
        $this->clearCacheForStage($stageId);
        $this->clearCacheForPipeline($pipelineId);

        if ($userId) {
            $this->clearCacheForUser($userId);
        }

        // Clear conversion rate and average time caches
        Cache::forget($this->getCacheKey('conversion_rate', [
            'stage_id' => $stageId,
            'pipeline_id' => $pipelineId,
            'user_id' => $userId,
        ]));

        Cache::forget($this->getCacheKey('avg_time', [
            'stage_id' => $stageId,
            'pipeline_id' => $pipelineId,
            'user_id' => $userId,
        ]));
    }

    /**
     * Clear all historical conversion cache.
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }
}
