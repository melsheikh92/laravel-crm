<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Lead\Contracts\ForecastActual as ForecastActualContract;

class ForecastActual extends Model implements ForecastActualContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'forecast_id',
        'actual_value',
        'variance',
        'variance_percentage',
        'closed_at',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'actual_value'         => 'decimal:4',
        'variance'             => 'decimal:4',
        'variance_percentage'  => 'decimal:2',
        'closed_at'            => 'datetime',
    ];

    /**
     * Get the sales forecast that owns this actual.
     */
    public function forecast(): BelongsTo
    {
        return $this->belongsTo(SalesForecastProxy::modelClass(), 'forecast_id');
    }

    /**
     * Check if the actual value exceeded the forecast.
     *
     * @return bool
     */
    public function exceededForecast(): bool
    {
        return $this->variance > 0;
    }

    /**
     * Check if the actual value fell short of the forecast.
     *
     * @return bool
     */
    public function fellShort(): bool
    {
        return $this->variance < 0;
    }

    /**
     * Check if the actual value matched the forecast (within tolerance).
     *
     * @param float $tolerancePercentage
     * @return bool
     */
    public function matchedForecast(float $tolerancePercentage = 5.0): bool
    {
        return abs($this->variance_percentage) <= $tolerancePercentage;
    }

    /**
     * Get the absolute variance value.
     *
     * @return float
     */
    public function getAbsoluteVariance(): float
    {
        return abs((float) $this->variance);
    }

    /**
     * Get the absolute variance percentage.
     *
     * @return float
     */
    public function getAbsoluteVariancePercentage(): float
    {
        return abs((float) $this->variance_percentage);
    }

    /**
     * Get the accuracy score (100 - abs variance percentage).
     *
     * @return float
     */
    public function getAccuracyScore(): float
    {
        return max(0, 100 - $this->getAbsoluteVariancePercentage());
    }

    /**
     * Determine if the forecast was highly accurate (within 10% variance).
     *
     * @return bool
     */
    public function isHighlyAccurate(): bool
    {
        return $this->getAbsoluteVariancePercentage() <= 10;
    }

    /**
     * Determine if the forecast was moderately accurate (10-25% variance).
     *
     * @return bool
     */
    public function isModeratelyAccurate(): bool
    {
        $variance = $this->getAbsoluteVariancePercentage();

        return $variance > 10 && $variance <= 25;
    }

    /**
     * Determine if the forecast was poorly accurate (> 25% variance).
     *
     * @return bool
     */
    public function isPoorlyAccurate(): bool
    {
        return $this->getAbsoluteVariancePercentage() > 25;
    }

    /**
     * Get the accuracy level as a string.
     *
     * @return string
     */
    public function getAccuracyLevel(): string
    {
        if ($this->isHighlyAccurate()) {
            return 'high';
        }

        if ($this->isModeratelyAccurate()) {
            return 'moderate';
        }

        return 'poor';
    }

    /**
     * Get the performance indicator (over/under/on-target).
     *
     * @param float $tolerancePercentage
     * @return string
     */
    public function getPerformanceIndicator(float $tolerancePercentage = 5.0): string
    {
        if ($this->matchedForecast($tolerancePercentage)) {
            return 'on-target';
        }

        if ($this->exceededForecast()) {
            return 'over';
        }

        return 'under';
    }

    /**
     * Calculate the forecast vs actual ratio.
     *
     * @return float|null
     */
    public function getForecastToActualRatio(): ?float
    {
        $forecast = $this->forecast;

        if (! $forecast || $forecast->forecast_value == 0) {
            return null;
        }

        return ($this->actual_value / $forecast->forecast_value) * 100;
    }

    /**
     * Scope a query to only include actuals that exceeded forecasts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExceeded($query)
    {
        return $query->where('variance', '>', 0);
    }

    /**
     * Scope a query to only include actuals that fell short of forecasts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFellShort($query)
    {
        return $query->where('variance', '<', 0);
    }

    /**
     * Scope a query to only include highly accurate forecasts (within 10% variance).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighlyAccurate($query)
    {
        return $query->whereRaw('ABS(variance_percentage) <= 10');
    }

    /**
     * Scope a query to only include moderately accurate forecasts (10-25% variance).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeModeratelyAccurate($query)
    {
        return $query->whereRaw('ABS(variance_percentage) > 10 AND ABS(variance_percentage) <= 25');
    }

    /**
     * Scope a query to only include poorly accurate forecasts (> 25% variance).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePoorlyAccurate($query)
    {
        return $query->whereRaw('ABS(variance_percentage) > 25');
    }

    /**
     * Scope a query to only include actuals closed within a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosedBetween($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('closed_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include actuals for a specific forecast.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $forecastId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForForecast($query, int $forecastId)
    {
        return $query->where('forecast_id', $forecastId);
    }
}
