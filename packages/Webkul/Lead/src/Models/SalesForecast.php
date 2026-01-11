<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Lead\Contracts\SalesForecast as SalesForecastContract;
use Webkul\User\Models\UserProxy;

class SalesForecast extends Model implements SalesForecastContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'period_type',
        'period_start',
        'period_end',
        'forecast_value',
        'weighted_forecast',
        'best_case',
        'worst_case',
        'confidence_score',
        'metadata',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'period_start'     => 'date',
        'period_end'       => 'date',
        'forecast_value'   => 'decimal:4',
        'weighted_forecast' => 'decimal:4',
        'best_case'        => 'decimal:4',
        'worst_case'       => 'decimal:4',
        'confidence_score' => 'decimal:2',
        'metadata'         => 'array',
    ];

    /**
     * Get the user that owns the forecast.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Get the forecast actuals for this forecast.
     */
    public function actuals(): HasMany
    {
        return $this->hasMany(ForecastActualProxy::modelClass(), 'forecast_id');
    }

    /**
     * Get the latest actual for this forecast.
     */
    public function latestActual()
    {
        return $this->hasOne(ForecastActualProxy::modelClass(), 'forecast_id')
            ->latestOfMany('closed_at');
    }

    /**
     * Calculate the variance between forecast and actual.
     *
     * @return float|null
     */
    public function getVariance(): ?float
    {
        $actual = $this->latestActual;

        if (! $actual) {
            return null;
        }

        return (float) $actual->variance;
    }

    /**
     * Calculate the variance percentage between forecast and actual.
     *
     * @return float|null
     */
    public function getVariancePercentage(): ?float
    {
        $actual = $this->latestActual;

        if (! $actual) {
            return null;
        }

        return (float) $actual->variance_percentage;
    }

    /**
     * Get the accuracy score (inverse of variance percentage).
     *
     * @return float|null
     */
    public function getAccuracyScore(): ?float
    {
        $variancePercentage = $this->getVariancePercentage();

        if ($variancePercentage === null) {
            return null;
        }

        return max(0, 100 - abs($variancePercentage));
    }

    /**
     * Check if the forecast period has ended.
     *
     * @return bool
     */
    public function isPeriodEnded(): bool
    {
        return $this->period_end->isPast();
    }

    /**
     * Check if the forecast has actuals recorded.
     *
     * @return bool
     */
    public function hasActuals(): bool
    {
        return $this->actuals()->exists();
    }

    /**
     * Get the scenario spread (difference between best and worst case).
     *
     * @return float
     */
    public function getScenarioSpread(): float
    {
        return (float) ($this->best_case - $this->worst_case);
    }

    /**
     * Get the scenario spread as a percentage of forecast value.
     *
     * @return float
     */
    public function getScenarioSpreadPercentage(): float
    {
        if ($this->forecast_value == 0) {
            return 0;
        }

        return ($this->getScenarioSpread() / $this->forecast_value) * 100;
    }

    /**
     * Get the upside potential (difference between best case and forecast).
     *
     * @return float
     */
    public function getUpsidePotential(): float
    {
        return (float) ($this->best_case - $this->forecast_value);
    }

    /**
     * Get the downside risk (difference between forecast and worst case).
     *
     * @return float
     */
    public function getDownsideRisk(): float
    {
        return (float) ($this->forecast_value - $this->worst_case);
    }

    /**
     * Determine if the forecast is high confidence (>= 80%).
     *
     * @return bool
     */
    public function isHighConfidence(): bool
    {
        return $this->confidence_score >= 80;
    }

    /**
     * Determine if the forecast is medium confidence (50-79%).
     *
     * @return bool
     */
    public function isMediumConfidence(): bool
    {
        return $this->confidence_score >= 50 && $this->confidence_score < 80;
    }

    /**
     * Determine if the forecast is low confidence (< 50%).
     *
     * @return bool
     */
    public function isLowConfidence(): bool
    {
        return $this->confidence_score < 50;
    }

    /**
     * Get the confidence level as a string.
     *
     * @return string
     */
    public function getConfidenceLevel(): string
    {
        if ($this->isHighConfidence()) {
            return 'high';
        }

        if ($this->isMediumConfidence()) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Scope a query to only include forecasts for a specific period type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $periodType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPeriodType($query, string $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Scope a query to only include forecasts for a specific user.
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
     * Scope a query to only include forecasts for a specific team.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $teamId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope a query to only include forecasts within a date range.
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
     * Scope a query to only include completed forecasts (with actuals).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->whereHas('actuals');
    }

    /**
     * Scope a query to only include pending forecasts (without actuals).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->whereDoesntHave('actuals');
    }

    /**
     * Scope a query to only include high confidence forecasts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', 80);
    }
}
