<?php

namespace Webkul\Lead\Repositories;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Lead\Contracts\DealScore;

class DealScoreRepository extends Repository
{
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
        return DealScore::class;
    }

    /**
     * Get deal scores for a specific lead.
     *
     * @param  int  $leadId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByLead(int $leadId): Collection
    {
        return $this->model
            ->forLead($leadId)
            ->orderBy('generated_at', 'desc')
            ->get();
    }

    /**
     * Get the latest score for a specific lead.
     *
     * @param  int  $leadId
     * @return \Webkul\Lead\Contracts\DealScore|null
     */
    public function getLatestByLead(int $leadId): ?DealScore
    {
        return $this->model
            ->forLead($leadId)
            ->orderBy('generated_at', 'desc')
            ->first();
    }

    /**
     * Get high priority deals (score >= 80).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHighPriority(): Collection
    {
        return $this->model
            ->highPriority()
            ->with('lead')
            ->topScored()
            ->get();
    }

    /**
     * Get medium priority deals (score 50-79).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMediumPriority(): Collection
    {
        return $this->model
            ->mediumPriority()
            ->with('lead')
            ->topScored()
            ->get();
    }

    /**
     * Get low priority deals (score < 50).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowPriority(): Collection
    {
        return $this->model
            ->lowPriority()
            ->with('lead')
            ->topScored()
            ->get();
    }

    /**
     * Get top scored deals.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopScored(int $limit = 10): Collection
    {
        return $this->model
            ->with('lead')
            ->topScored()
            ->limit($limit)
            ->get();
    }

    /**
     * Get deals with high win probability (>= 70%).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHighWinProbability(): Collection
    {
        return $this->model
            ->highWinProbability()
            ->with('lead')
            ->topScored()
            ->get();
    }

    /**
     * Get deals with strong engagement (>= 70).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStrongEngagement(): Collection
    {
        return $this->model
            ->strongEngagement()
            ->with('lead')
            ->topScored()
            ->get();
    }

    /**
     * Get deals with fast velocity (>= 70).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFastVelocity(): Collection
    {
        return $this->model
            ->fastVelocity()
            ->with('lead')
            ->topScored()
            ->get();
    }

    /**
     * Get fresh scores (generated within specified hours).
     *
     * @param  int  $hours
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFresh(int $hours = 24): Collection
    {
        return $this->model
            ->fresh($hours)
            ->with('lead')
            ->orderBy('generated_at', 'desc')
            ->get();
    }

    /**
     * Get stale scores (older than specified hours).
     *
     * @param  int  $hours
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStale(int $hours = 24): Collection
    {
        return $this->model
            ->stale($hours)
            ->with('lead')
            ->orderBy('generated_at', 'asc')
            ->get();
    }

    /**
     * Get the latest score for each lead.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestForEachLead(): Collection
    {
        return $this->model
            ->latestForEachLead()
            ->with('lead')
            ->topScored()
            ->get();
    }

    /**
     * Get deal scores with filters.
     *
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWithFilters(array $filters): Collection
    {
        $query = $this->model->query();

        if (isset($filters['lead_id'])) {
            $query->forLead($filters['lead_id']);
        }

        if (isset($filters['priority'])) {
            match ($filters['priority']) {
                'high' => $query->highPriority(),
                'medium' => $query->mediumPriority(),
                'low' => $query->lowPriority(),
                default => null,
            };
        }

        if (isset($filters['min_score'])) {
            $query->where('score', '>=', $filters['min_score']);
        }

        if (isset($filters['max_score'])) {
            $query->where('score', '<=', $filters['max_score']);
        }

        if (isset($filters['min_win_probability'])) {
            $query->where('win_probability', '>=', $filters['min_win_probability']);
        }

        if (isset($filters['high_win_probability']) && $filters['high_win_probability']) {
            $query->highWinProbability();
        }

        if (isset($filters['strong_engagement']) && $filters['strong_engagement']) {
            $query->strongEngagement();
        }

        if (isset($filters['fast_velocity']) && $filters['fast_velocity']) {
            $query->fastVelocity();
        }

        if (isset($filters['fresh']) && $filters['fresh']) {
            $hours = $filters['fresh_hours'] ?? 24;
            $query->fresh($hours);
        }

        if (isset($filters['stale']) && $filters['stale']) {
            $hours = $filters['stale_hours'] ?? 24;
            $query->stale($hours);
        }

        if (isset($filters['latest_only']) && $filters['latest_only']) {
            $query->latestForEachLead();
        }

        if (isset($filters['generated_after'])) {
            $query->where('generated_at', '>=', $filters['generated_after']);
        }

        if (isset($filters['generated_before'])) {
            $query->where('generated_at', '<=', $filters['generated_before']);
        }

        $query->with('lead');

        $sortBy = $filters['sort_by'] ?? 'score';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        return $query->orderBy($sortBy, $sortOrder)->get();
    }

    /**
     * Get score statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $scores = $this->model->latestForEachLead()->get();

        return [
            'total_scores' => $scores->count(),
            'high_priority_count' => $scores->filter(fn ($s) => $s->isHighPriority())->count(),
            'medium_priority_count' => $scores->filter(fn ($s) => $s->isMediumPriority())->count(),
            'low_priority_count' => $scores->filter(fn ($s) => $s->isLowPriority())->count(),
            'average_score' => round($scores->avg('score'), 2),
            'average_win_probability' => round($scores->avg('win_probability'), 2),
            'average_engagement_score' => round($scores->avg('engagement_score'), 2),
            'average_velocity_score' => round($scores->avg('velocity_score'), 2),
            'average_value_score' => round($scores->avg('value_score'), 2),
            'average_historical_pattern_score' => round($scores->avg('historical_pattern_score'), 2),
            'high_win_probability_count' => $scores->filter(fn ($s) => $s->hasHighWinProbability())->count(),
            'strong_engagement_count' => $scores->filter(fn ($s) => $s->hasStrongEngagement())->count(),
            'fast_velocity_count' => $scores->filter(fn ($s) => $s->hasFastVelocity())->count(),
            'high_value_count' => $scores->filter(fn ($s) => $s->hasHighValue())->count(),
        ];
    }

    /**
     * Get score statistics for a specific lead.
     *
     * @param  int  $leadId
     * @return array
     */
    public function getStatisticsByLead(int $leadId): array
    {
        $scores = $this->model->forLead($leadId)->get();

        if ($scores->isEmpty()) {
            return [
                'total_scores' => 0,
                'latest_score' => null,
                'score_trend' => null,
                'average_score' => null,
                'best_score' => null,
                'worst_score' => null,
            ];
        }

        $latest = $scores->first();
        $oldest = $scores->last();

        return [
            'total_scores' => $scores->count(),
            'latest_score' => $latest->score,
            'score_trend' => $scores->count() > 1 ? ($latest->score - $oldest->score) : 0,
            'average_score' => round($scores->avg('score'), 2),
            'best_score' => $scores->max('score'),
            'worst_score' => $scores->min('score'),
            'latest_priority' => $latest->getPriorityLevel(),
            'latest_win_probability' => $latest->win_probability,
        ];
    }

    /**
     * Create or update a deal score for a lead.
     *
     * @param  int  $leadId
     * @param  array  $scoreData
     * @return \Webkul\Lead\Contracts\DealScore
     */
    public function createOrUpdateForLead(int $leadId, array $scoreData): DealScore
    {
        $scoreData['lead_id'] = $leadId;
        $scoreData['generated_at'] = now();

        return $this->create($scoreData);
    }

    /**
     * Delete old scores for a lead, keeping only the specified number of most recent ones.
     *
     * @param  int  $leadId
     * @param  int  $keepCount
     * @return int
     */
    public function pruneOldScores(int $leadId, int $keepCount = 10): int
    {
        $scores = $this->model
            ->forLead($leadId)
            ->orderBy('generated_at', 'desc')
            ->get();

        if ($scores->count() <= $keepCount) {
            return 0;
        }

        $scoresToDelete = $scores->slice($keepCount);

        $deleteCount = 0;
        foreach ($scoresToDelete as $score) {
            $score->delete();
            $deleteCount++;
        }

        return $deleteCount;
    }

    /**
     * Delete stale scores older than specified hours.
     *
     * @param  int  $hours
     * @param  bool  $keepLatest
     * @return int
     */
    public function deleteStaleScores(int $hours = 720, bool $keepLatest = true): int
    {
        if ($keepLatest) {
            // Get latest score IDs to preserve
            $latestScoreIds = $this->model
                ->latestForEachLead()
                ->pluck('id')
                ->toArray();

            return $this->model
                ->stale($hours)
                ->whereNotIn('id', $latestScoreIds)
                ->delete();
        }

        return $this->model
            ->stale($hours)
            ->delete();
    }

    /**
     * Get deals that need score recalculation.
     *
     * @param  int  $hours
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNeedingRecalculation(int $hours = 24): Collection
    {
        return $this->model
            ->latestForEachLead()
            ->stale($hours)
            ->with('lead')
            ->get();
    }

    /**
     * Check if a lead has a score.
     *
     * @param  int  $leadId
     * @return bool
     */
    public function hasScore(int $leadId): bool
    {
        return $this->model->forLead($leadId)->exists();
    }

    /**
     * Get score distribution by priority level.
     *
     * @return array
     */
    public function getScoreDistribution(): array
    {
        $scores = $this->model->latestForEachLead()->get();

        return [
            'high' => [
                'count' => $scores->filter(fn ($s) => $s->isHighPriority())->count(),
                'percentage' => $scores->count() > 0
                    ? round(($scores->filter(fn ($s) => $s->isHighPriority())->count() / $scores->count()) * 100, 2)
                    : 0,
            ],
            'medium' => [
                'count' => $scores->filter(fn ($s) => $s->isMediumPriority())->count(),
                'percentage' => $scores->count() > 0
                    ? round(($scores->filter(fn ($s) => $s->isMediumPriority())->count() / $scores->count()) * 100, 2)
                    : 0,
            ],
            'low' => [
                'count' => $scores->filter(fn ($s) => $s->isLowPriority())->count(),
                'percentage' => $scores->count() > 0
                    ? round(($scores->filter(fn ($s) => $s->isLowPriority())->count() / $scores->count()) * 100, 2)
                    : 0,
            ],
        ];
    }

    /**
     * Get average scores by priority level.
     *
     * @return array
     */
    public function getAverageScoresByPriority(): array
    {
        $scores = $this->model->latestForEachLead()->get();

        return [
            'high' => [
                'avg_score' => round($scores->filter(fn ($s) => $s->isHighPriority())->avg('score'), 2),
                'avg_win_probability' => round($scores->filter(fn ($s) => $s->isHighPriority())->avg('win_probability'), 2),
            ],
            'medium' => [
                'avg_score' => round($scores->filter(fn ($s) => $s->isMediumPriority())->avg('score'), 2),
                'avg_win_probability' => round($scores->filter(fn ($s) => $s->isMediumPriority())->avg('win_probability'), 2),
            ],
            'low' => [
                'avg_score' => round($scores->filter(fn ($s) => $s->isLowPriority())->avg('score'), 2),
                'avg_win_probability' => round($scores->filter(fn ($s) => $s->isLowPriority())->avg('win_probability'), 2),
            ],
        ];
    }
}
