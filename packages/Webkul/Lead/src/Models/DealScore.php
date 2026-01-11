<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Lead\Contracts\DealScore as DealScoreContract;

class DealScore extends Model implements DealScoreContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'score',
        'win_probability',
        'velocity_score',
        'engagement_score',
        'value_score',
        'historical_pattern_score',
        'factors',
        'generated_at',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'score'                     => 'decimal:2',
        'win_probability'           => 'decimal:2',
        'velocity_score'            => 'decimal:2',
        'engagement_score'          => 'decimal:2',
        'value_score'               => 'decimal:2',
        'historical_pattern_score'  => 'decimal:2',
        'factors'                   => 'array',
        'generated_at'              => 'datetime',
    ];

    /**
     * Get the lead that owns this deal score.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    /**
     * Check if the deal is high priority (score >= 80).
     *
     * @return bool
     */
    public function isHighPriority(): bool
    {
        return $this->score >= 80;
    }

    /**
     * Check if the deal is medium priority (score 50-79).
     *
     * @return bool
     */
    public function isMediumPriority(): bool
    {
        return $this->score >= 50 && $this->score < 80;
    }

    /**
     * Check if the deal is low priority (score < 50).
     *
     * @return bool
     */
    public function isLowPriority(): bool
    {
        return $this->score < 50;
    }

    /**
     * Get the priority level as a string.
     *
     * @return string
     */
    public function getPriorityLevel(): string
    {
        if ($this->isHighPriority()) {
            return 'high';
        }

        if ($this->isMediumPriority()) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Check if the deal has high win probability (>= 70%).
     *
     * @return bool
     */
    public function hasHighWinProbability(): bool
    {
        return $this->win_probability >= 70;
    }

    /**
     * Check if the deal has medium win probability (40-69%).
     *
     * @return bool
     */
    public function hasMediumWinProbability(): bool
    {
        return $this->win_probability >= 40 && $this->win_probability < 70;
    }

    /**
     * Check if the deal has low win probability (< 40%).
     *
     * @return bool
     */
    public function hasLowWinProbability(): bool
    {
        return $this->win_probability < 40;
    }

    /**
     * Get the win probability level as a string.
     *
     * @return string
     */
    public function getWinProbabilityLevel(): string
    {
        if ($this->hasHighWinProbability()) {
            return 'high';
        }

        if ($this->hasMediumWinProbability()) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Check if the deal has strong engagement (>= 70).
     *
     * @return bool
     */
    public function hasStrongEngagement(): bool
    {
        return $this->engagement_score >= 70;
    }

    /**
     * Check if the deal has fast velocity (>= 70).
     *
     * @return bool
     */
    public function hasFastVelocity(): bool
    {
        return $this->velocity_score >= 70;
    }

    /**
     * Check if the deal has high value score (>= 70).
     *
     * @return bool
     */
    public function hasHighValue(): bool
    {
        return $this->value_score >= 70;
    }

    /**
     * Check if the score is stale (older than configured threshold).
     *
     * @param int $hours
     * @return bool
     */
    public function isStale(int $hours = 24): bool
    {
        return $this->generated_at->addHours($hours)->isPast();
    }

    /**
     * Check if the score is fresh (generated recently).
     *
     * @param int $hours
     * @return bool
     */
    public function isFresh(int $hours = 24): bool
    {
        return ! $this->isStale($hours);
    }

    /**
     * Get the age of the score in hours.
     *
     * @return int
     */
    public function getAgeInHours(): int
    {
        return $this->generated_at->diffInHours(now());
    }

    /**
     * Get the dominant factor (highest score component).
     *
     * @return string
     */
    public function getDominantFactor(): string
    {
        $scores = [
            'engagement'         => $this->engagement_score,
            'velocity'           => $this->velocity_score,
            'value'              => $this->value_score,
            'historical_pattern' => $this->historical_pattern_score,
        ];

        return array_search(max($scores), $scores);
    }

    /**
     * Get the weakest factor (lowest score component).
     *
     * @return string
     */
    public function getWeakestFactor(): string
    {
        $scores = [
            'engagement'         => $this->engagement_score,
            'velocity'           => $this->velocity_score,
            'value'              => $this->value_score,
            'historical_pattern' => $this->historical_pattern_score,
        ];

        return array_search(min($scores), $scores);
    }

    /**
     * Get the score breakdown as an array.
     *
     * @return array
     */
    public function getScoreBreakdown(): array
    {
        return [
            'overall'           => $this->score,
            'win_probability'   => $this->win_probability,
            'engagement'        => $this->engagement_score,
            'velocity'          => $this->velocity_score,
            'value'             => $this->value_score,
            'historical_pattern' => $this->historical_pattern_score,
        ];
    }

    /**
     * Scope a query to only include high priority deals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPriority($query)
    {
        return $query->where('score', '>=', 80);
    }

    /**
     * Scope a query to only include medium priority deals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMediumPriority($query)
    {
        return $query->where('score', '>=', 50)->where('score', '<', 80);
    }

    /**
     * Scope a query to only include low priority deals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowPriority($query)
    {
        return $query->where('score', '<', 50);
    }

    /**
     * Scope a query to only include deals with high win probability.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighWinProbability($query)
    {
        return $query->where('win_probability', '>=', 70);
    }

    /**
     * Scope a query to only include deals with strong engagement.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStrongEngagement($query)
    {
        return $query->where('engagement_score', '>=', 70);
    }

    /**
     * Scope a query to only include deals with fast velocity.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFastVelocity($query)
    {
        return $query->where('velocity_score', '>=', 70);
    }

    /**
     * Scope a query to only include fresh scores.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $hours
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFresh($query, int $hours = 24)
    {
        return $query->where('generated_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope a query to only include stale scores.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $hours
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStale($query, int $hours = 24)
    {
        return $query->where('generated_at', '<', now()->subHours($hours));
    }

    /**
     * Scope a query to order by score descending (highest first).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopScored($query)
    {
        return $query->orderBy('score', 'desc');
    }

    /**
     * Scope a query to only include scores for a specific lead.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $leadId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLead($query, int $leadId)
    {
        return $query->where('lead_id', $leadId);
    }

    /**
     * Scope a query to get the latest score for each lead.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestForEachLead($query)
    {
        return $query->whereIn('id', function ($subquery) {
            $subquery->selectRaw('MAX(id)')
                ->from('deal_scores')
                ->groupBy('lead_id');
        });
    }
}
