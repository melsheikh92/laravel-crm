<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Lead\Contracts\HistoricalConversion as HistoricalConversionContract;
use Webkul\User\Models\UserProxy;

class HistoricalConversion extends Model implements HistoricalConversionContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stage_id',
        'pipeline_id',
        'user_id',
        'conversion_rate',
        'average_time_in_stage',
        'sample_size',
        'period_start',
        'period_end',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'conversion_rate'        => 'decimal:2',
        'average_time_in_stage'  => 'decimal:2',
        'sample_size'            => 'integer',
        'period_start'           => 'date',
        'period_end'             => 'date',
    ];

    /**
     * Get the stage that this conversion data belongs to.
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(StageProxy::modelClass());
    }

    /**
     * Get the pipeline that this conversion data belongs to.
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(PipelineProxy::modelClass());
    }

    /**
     * Get the user that this conversion data belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Check if the conversion rate is high (>= 70%).
     *
     * @return bool
     */
    public function isHighConversionRate(): bool
    {
        return $this->conversion_rate >= 70;
    }

    /**
     * Check if the conversion rate is medium (40-69%).
     *
     * @return bool
     */
    public function isMediumConversionRate(): bool
    {
        return $this->conversion_rate >= 40 && $this->conversion_rate < 70;
    }

    /**
     * Check if the conversion rate is low (< 40%).
     *
     * @return bool
     */
    public function isLowConversionRate(): bool
    {
        return $this->conversion_rate < 40;
    }

    /**
     * Get the conversion rate level as a string.
     *
     * @return string
     */
    public function getConversionRateLevel(): string
    {
        if ($this->isHighConversionRate()) {
            return 'high';
        }

        if ($this->isMediumConversionRate()) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Check if the sample size is statistically significant (>= 30).
     *
     * @return bool
     */
    public function hasSignificantSampleSize(): bool
    {
        return $this->sample_size >= 30;
    }

    /**
     * Check if the data is current (period_end is recent).
     *
     * @param int $days
     * @return bool
     */
    public function isCurrent(int $days = 90): bool
    {
        return $this->period_end->greaterThanOrEqualTo(now()->subDays($days));
    }

    /**
     * Check if the data is stale (period_end is old).
     *
     * @param int $days
     * @return bool
     */
    public function isStale(int $days = 90): bool
    {
        return ! $this->isCurrent($days);
    }

    /**
     * Get the age of this data in days.
     *
     * @return int
     */
    public function getAgeInDays(): int
    {
        return $this->period_end->diffInDays(now());
    }

    /**
     * Get the period duration in days.
     *
     * @return int
     */
    public function getPeriodDuration(): int
    {
        return $this->period_start->diffInDays($this->period_end);
    }

    /**
     * Check if this is a fast-moving stage (average time < 7 days).
     *
     * @return bool
     */
    public function isFastMovingStage(): bool
    {
        return $this->average_time_in_stage < 7;
    }

    /**
     * Check if this is a slow-moving stage (average time >= 30 days).
     *
     * @return bool
     */
    public function isSlowMovingStage(): bool
    {
        return $this->average_time_in_stage >= 30;
    }

    /**
     * Get the velocity level as a string.
     *
     * @return string
     */
    public function getVelocityLevel(): string
    {
        if ($this->isFastMovingStage()) {
            return 'fast';
        }

        if ($this->isSlowMovingStage()) {
            return 'slow';
        }

        return 'moderate';
    }

    /**
     * Calculate the expected conversion count based on sample size.
     *
     * @return float
     */
    public function getExpectedConversions(): float
    {
        return ($this->sample_size * $this->conversion_rate) / 100;
    }

    /**
     * Get confidence level based on sample size and conversion rate.
     *
     * @return string
     */
    public function getConfidenceLevel(): string
    {
        if ($this->sample_size >= 100) {
            return 'high';
        }

        if ($this->sample_size >= 30) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Scope a query to only include conversions for a specific stage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $stageId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStage($query, int $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    /**
     * Scope a query to only include conversions for a specific pipeline.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $pipelineId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPipeline($query, int $pipelineId)
    {
        return $query->where('pipeline_id', $pipelineId);
    }

    /**
     * Scope a query to only include conversions for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include conversions within a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInDateRange($query, string $startDate, string $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate);
    }

    /**
     * Scope a query to only include high conversion rates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighConversionRate($query)
    {
        return $query->where('conversion_rate', '>=', 70);
    }

    /**
     * Scope a query to only include medium conversion rates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMediumConversionRate($query)
    {
        return $query->where('conversion_rate', '>=', 40)
            ->where('conversion_rate', '<', 70);
    }

    /**
     * Scope a query to only include low conversion rates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowConversionRate($query)
    {
        return $query->where('conversion_rate', '<', 40);
    }

    /**
     * Scope a query to only include statistically significant data.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $minSampleSize
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSignificant($query, int $minSampleSize = 30)
    {
        return $query->where('sample_size', '>=', $minSampleSize);
    }

    /**
     * Scope a query to only include current data.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query, int $days = 90)
    {
        return $query->where('period_end', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to only include stale data.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStale($query, int $days = 90)
    {
        return $query->where('period_end', '<', now()->subDays($days));
    }

    /**
     * Scope a query to only include fast-moving stages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFastMoving($query)
    {
        return $query->where('average_time_in_stage', '<', 7);
    }

    /**
     * Scope a query to only include slow-moving stages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSlowMoving($query)
    {
        return $query->where('average_time_in_stage', '>=', 30);
    }

    /**
     * Scope a query to get the most recent conversion data.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('period_end', 'desc');
    }

    /**
     * Scope a query to get the most recent conversion for each stage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestForEachStage($query)
    {
        return $query->whereIn('id', function ($subquery) {
            $subquery->selectRaw('MAX(id)')
                ->from('historical_conversions')
                ->groupBy('stage_id');
        });
    }

    /**
     * Scope a query to get the most recent conversion for each pipeline.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestForEachPipeline($query)
    {
        return $query->whereIn('id', function ($subquery) {
            $subquery->selectRaw('MAX(id)')
                ->from('historical_conversions')
                ->groupBy('pipeline_id');
        });
    }
}
