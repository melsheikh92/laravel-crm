<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Collection;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Email\Repositories\EmailRepository;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\DealScoreRepository;
use Webkul\Lead\Repositories\LeadRepository;

class DealScoringService
{
    /**
     * Score component weights (must sum to 100).
     */
    const ENGAGEMENT_WEIGHT = 30.0;
    const VELOCITY_WEIGHT = 25.0;
    const VALUE_WEIGHT = 20.0;
    const HISTORICAL_WEIGHT = 15.0;
    const STAGE_PROBABILITY_WEIGHT = 10.0;

    /**
     * Engagement thresholds for scoring.
     */
    const HIGH_ENGAGEMENT_ACTIVITIES = 10;
    const MEDIUM_ENGAGEMENT_ACTIVITIES = 5;
    const HIGH_ENGAGEMENT_EMAILS = 15;
    const MEDIUM_ENGAGEMENT_EMAILS = 5;
    const ENGAGEMENT_PERIOD_DAYS = 30;

    /**
     * Value thresholds for scoring (configurable per organization).
     */
    const ENTERPRISE_VALUE = 100000;
    const LARGE_VALUE = 50000;
    const MEDIUM_VALUE = 10000;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(
        protected LeadRepository $leadRepository,
        protected DealScoreRepository $dealScoreRepository,
        protected DealVelocityService $velocityService,
        protected HistoricalAnalysisService $historicalAnalysisService,
        protected ActivityRepository $activityRepository,
        protected EmailRepository $emailRepository
    ) {
    }

    /**
     * Calculate and store the deal score for a lead.
     *
     * @param  Lead|int  $lead
     * @param  bool  $persist
     * @return array
     */
    public function scoreLead($lead, bool $persist = true): array
    {
        if (is_int($lead)) {
            $lead = $this->leadRepository->find($lead);
        }

        if (!$lead) {
            throw new \InvalidArgumentException('Lead not found');
        }

        // Calculate individual score components
        $engagementScore = $this->calculateEngagementScore($lead);
        $velocityScore = $this->calculateVelocityScore($lead);
        $valueScore = $this->calculateValueScore($lead);
        $historicalPatternScore = $this->calculateHistoricalPatternScore($lead);
        $stageProbabilityScore = $this->calculateStageProbabilityScore($lead);

        // Calculate weighted overall score
        $overallScore = $this->calculateOverallScore(
            $engagementScore,
            $velocityScore,
            $valueScore,
            $historicalPatternScore,
            $stageProbabilityScore
        );

        // Calculate win probability
        $winProbability = $this->calculateWinProbability(
            $overallScore,
            $lead,
            $stageProbabilityScore,
            $historicalPatternScore
        );

        $scoreData = [
            'score' => round($overallScore, 2),
            'win_probability' => round($winProbability, 2),
            'engagement_score' => round($engagementScore, 2),
            'velocity_score' => round($velocityScore, 2),
            'value_score' => round($valueScore, 2),
            'historical_pattern_score' => round($historicalPatternScore, 2),
            'factors' => [
                'stage_probability_score' => round($stageProbabilityScore, 2),
                'engagement_details' => $this->getEngagementDetails($lead),
                'velocity_details' => $this->getVelocityDetails($lead),
                'value_details' => $this->getValueDetails($lead),
            ],
        ];

        // Persist to database if requested
        if ($persist) {
            $this->dealScoreRepository->createOrUpdateForLead($lead->id, $scoreData);
        }

        return $scoreData;
    }

    /**
     * Score multiple leads.
     *
     * @param  Collection  $leads
     * @param  bool  $persist
     * @return Collection
     */
    public function scoreLeads(Collection $leads, bool $persist = true): Collection
    {
        return $leads->map(function ($lead) use ($persist) {
            try {
                return [
                    'lead_id' => $lead->id,
                    'score_data' => $this->scoreLead($lead, $persist),
                    'success' => true,
                ];
            } catch (\Exception $e) {
                return [
                    'lead_id' => $lead->id,
                    'error' => $e->getMessage(),
                    'success' => false,
                ];
            }
        });
    }

    /**
     * Score all active leads.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @return Collection
     */
    public function scoreAllActiveLeads(?int $userId = null, ?int $pipelineId = null): Collection
    {
        $leads = $this->getActiveLeads($userId, $pipelineId);

        return $this->scoreLeads($leads, true);
    }

    /**
     * Get leads that need rescoring (stale scores).
     *
     * @param  int  $hours
     * @return Collection
     */
    public function getLeadsNeedingRescoring(int $hours = 24): Collection
    {
        return $this->dealScoreRepository->getNeedingRecalculation($hours);
    }

    /**
     * Rescore stale leads.
     *
     * @param  int  $hours
     * @return Collection
     */
    public function rescoreStaleLeads(int $hours = 24): Collection
    {
        $scores = $this->getLeadsNeedingRescoring($hours);

        return $scores->map(function ($score) {
            try {
                return [
                    'lead_id' => $score->lead_id,
                    'score_data' => $this->scoreLead($score->lead, true),
                    'success' => true,
                ];
            } catch (\Exception $e) {
                return [
                    'lead_id' => $score->lead_id,
                    'error' => $e->getMessage(),
                    'success' => false,
                ];
            }
        });
    }

    /**
     * Get top priority deals based on scores.
     *
     * @param  int  $limit
     * @param  int|null  $userId
     * @return Collection
     */
    public function getTopPriorityDeals(int $limit = 10, ?int $userId = null): Collection
    {
        $filters = [
            'latest_only' => true,
            'sort_by' => 'score',
            'sort_order' => 'desc',
        ];

        if ($userId) {
            $scores = $this->dealScoreRepository->getWithFilters($filters)
                ->filter(fn($score) => $score->lead && $score->lead->user_id === $userId)
                ->take($limit);
        } else {
            $scores = $this->dealScoreRepository->getWithFilters($filters)->take($limit);
        }

        return $scores;
    }

    /**
     * Get deals by priority level.
     *
     * @param  string  $priority
     * @param  int|null  $userId
     * @return Collection
     */
    public function getDealsByPriority(string $priority, ?int $userId = null): Collection
    {
        $filters = [
            'priority' => $priority,
            'latest_only' => true,
            'sort_by' => 'score',
            'sort_order' => 'desc',
        ];

        $scores = $this->dealScoreRepository->getWithFilters($filters);

        if ($userId) {
            $scores = $scores->filter(fn($score) => $score->lead && $score->lead->user_id === $userId);
        }

        return $scores;
    }

    /**
     * Calculate engagement score based on activities and emails.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateEngagementScore($lead): float
    {
        $details = $this->getEngagementDetails($lead);

        $activityCount = $details['activity_count'];
        $emailCount = $details['email_count'];
        $totalCount = $activityCount + $emailCount;

        // Activity score (0-60 points)
        $activityScore = $this->calculateActivityScore($activityCount);

        // Email score (0-40 points)
        $emailScore = $this->calculateEmailScore($emailCount);

        $baseScore = $activityScore + $emailScore;

        // Apply recency boost (up to 20% bonus)
        $recencyBoost = $this->calculateRecencyBoost($details);

        return min(100, $baseScore + $recencyBoost);
    }

    /**
     * Calculate velocity score.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateVelocityScore($lead): float
    {
        try {
            $velocityData = $this->velocityService->calculateVelocityScore($lead);

            return $velocityData['velocity_score'];
        } catch (\Exception $e) {
            // Return neutral score if velocity calculation fails
            return 50.0;
        }
    }

    /**
     * Calculate value score based on deal size.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateValueScore($lead): float
    {
        $value = $lead->lead_value ?? 0;

        // Score based on value tiers
        if ($value >= self::ENTERPRISE_VALUE) {
            return 100.0;
        }

        if ($value >= self::LARGE_VALUE) {
            return 70.0 + (($value - self::LARGE_VALUE) / (self::ENTERPRISE_VALUE - self::LARGE_VALUE) * 30);
        }

        if ($value >= self::MEDIUM_VALUE) {
            return 40.0 + (($value - self::MEDIUM_VALUE) / (self::LARGE_VALUE - self::MEDIUM_VALUE) * 30);
        }

        if ($value > 0) {
            return min(40.0, ($value / self::MEDIUM_VALUE) * 40);
        }

        return 0.0;
    }

    /**
     * Calculate historical pattern score.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateHistoricalPatternScore($lead): float
    {
        if (!$lead->user_id) {
            return 50.0; // Neutral score if no user
        }

        try {
            // Get user's historical performance
            $performance = $this->historicalAnalysisService->analyzeUserPerformance(
                $lead->user_id,
                $lead->lead_pipeline_id,
                90 // Last 90 days
            );

            $winRate = $performance['win_rate'] ?? 0;
            $conversionRate = $performance['conversion_rate'] ?? 0;

            // Calculate score based on historical success
            $winRateScore = min(100, $winRate * 1.2); // Scale up win rate slightly
            $conversionScore = min(100, $conversionRate * 1.5); // Conversion rate contributes less

            // Weighted average
            return ($winRateScore * 0.6) + ($conversionScore * 0.4);
        } catch (\Exception $e) {
            return 50.0; // Neutral score if analysis fails
        }
    }

    /**
     * Calculate stage probability score.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateStageProbabilityScore($lead): float
    {
        if (!$lead->stage || !isset($lead->stage->probability)) {
            return 50.0; // Neutral score if no stage probability
        }

        // Stage probability is typically 0-100, normalize it
        return min(100, max(0, (float) $lead->stage->probability));
    }

    /**
     * Calculate overall score from components.
     *
     * @param  float  $engagement
     * @param  float  $velocity
     * @param  float  $value
     * @param  float  $historical
     * @param  float  $stageProbability
     * @return float
     */
    protected function calculateOverallScore(
        float $engagement,
        float $velocity,
        float $value,
        float $historical,
        float $stageProbability
    ): float {
        return (
            ($engagement * self::ENGAGEMENT_WEIGHT / 100) +
            ($velocity * self::VELOCITY_WEIGHT / 100) +
            ($value * self::VALUE_WEIGHT / 100) +
            ($historical * self::HISTORICAL_WEIGHT / 100) +
            ($stageProbability * self::STAGE_PROBABILITY_WEIGHT / 100)
        );
    }

    /**
     * Calculate win probability.
     *
     * @param  float  $overallScore
     * @param  Lead  $lead
     * @param  float  $stageProbability
     * @param  float  $historicalScore
     * @return float
     */
    protected function calculateWinProbability(
        float $overallScore,
        $lead,
        float $stageProbability,
        float $historicalScore
    ): float {
        // Combine multiple factors for win probability
        // Overall score: 40%
        // Stage probability: 35%
        // Historical pattern: 25%

        $scoreFactor = $overallScore * 0.40;
        $stageFactor = $stageProbability * 0.35;
        $historicalFactor = $historicalScore * 0.25;

        $baseWinProbability = $scoreFactor + $stageFactor + $historicalFactor;

        // Apply modifiers based on deal characteristics
        $modifier = 1.0;

        // Boost for deals close to expected close date
        if ($lead->expected_close_date) {
            $daysUntilClose = now()->diffInDays($lead->expected_close_date, false);
            if ($daysUntilClose >= 0 && $daysUntilClose <= 30) {
                $modifier += 0.05; // 5% boost for deals closing soon
            }
        }

        // Penalty for deals past expected close date
        if ($lead->expected_close_date && $lead->expected_close_date < now()) {
            $modifier -= 0.10; // 10% penalty for overdue deals
        }

        return min(100, max(0, $baseWinProbability * $modifier));
    }

    /**
     * Calculate activity score.
     *
     * @param  int  $activityCount
     * @return float
     */
    protected function calculateActivityScore(int $activityCount): float
    {
        if ($activityCount >= self::HIGH_ENGAGEMENT_ACTIVITIES) {
            return 60.0;
        }

        if ($activityCount >= self::MEDIUM_ENGAGEMENT_ACTIVITIES) {
            return 30.0 + (($activityCount - self::MEDIUM_ENGAGEMENT_ACTIVITIES) /
                (self::HIGH_ENGAGEMENT_ACTIVITIES - self::MEDIUM_ENGAGEMENT_ACTIVITIES) * 30);
        }

        if ($activityCount > 0) {
            return ($activityCount / self::MEDIUM_ENGAGEMENT_ACTIVITIES) * 30;
        }

        return 0.0;
    }

    /**
     * Calculate email score.
     *
     * @param  int  $emailCount
     * @return float
     */
    protected function calculateEmailScore(int $emailCount): float
    {
        if ($emailCount >= self::HIGH_ENGAGEMENT_EMAILS) {
            return 40.0;
        }

        if ($emailCount >= self::MEDIUM_ENGAGEMENT_EMAILS) {
            return 20.0 + (($emailCount - self::MEDIUM_ENGAGEMENT_EMAILS) /
                (self::HIGH_ENGAGEMENT_EMAILS - self::MEDIUM_ENGAGEMENT_EMAILS) * 20);
        }

        if ($emailCount > 0) {
            return ($emailCount / self::MEDIUM_ENGAGEMENT_EMAILS) * 20;
        }

        return 0.0;
    }

    /**
     * Calculate recency boost for engagement.
     *
     * @param  array  $details
     * @return float
     */
    protected function calculateRecencyBoost(array $details): float
    {
        $lastActivityDays = $details['last_activity_days'] ?? null;
        $lastEmailDays = $details['last_email_days'] ?? null;

        $mostRecentDays = null;

        if ($lastActivityDays !== null && $lastEmailDays !== null) {
            $mostRecentDays = min($lastActivityDays, $lastEmailDays);
        } elseif ($lastActivityDays !== null) {
            $mostRecentDays = $lastActivityDays;
        } elseif ($lastEmailDays !== null) {
            $mostRecentDays = $lastEmailDays;
        }

        if ($mostRecentDays === null) {
            return 0.0;
        }

        // Recent engagement within 7 days = 20% boost
        if ($mostRecentDays <= 7) {
            return 20.0;
        }

        // Engagement within 14 days = 10% boost
        if ($mostRecentDays <= 14) {
            return 10.0;
        }

        // Engagement within 30 days = 5% boost
        if ($mostRecentDays <= 30) {
            return 5.0;
        }

        return 0.0;
    }

    /**
     * Get engagement details for a lead.
     *
     * @param  Lead  $lead
     * @return array
     */
    protected function getEngagementDetails($lead): array
    {
        $cutoffDate = now()->subDays(self::ENGAGEMENT_PERIOD_DAYS);

        // Count activities in the period
        $activities = $lead->activities()
            ->where('created_at', '>=', $cutoffDate)
            ->get();

        // Count emails in the period
        $emails = $this->emailRepository->getModel()
            ->where('lead_id', $lead->id)
            ->where('created_at', '>=', $cutoffDate)
            ->get();

        $lastActivity = $lead->activities()
            ->orderBy('created_at', 'desc')
            ->first();

        $lastEmail = $this->emailRepository->getModel()
            ->where('lead_id', $lead->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return [
            'activity_count' => $activities->count(),
            'email_count' => $emails->count(),
            'total_engagement_count' => $activities->count() + $emails->count(),
            'last_activity_days' => $lastActivity ? now()->diffInDays($lastActivity->created_at) : null,
            'last_email_days' => $lastEmail ? now()->diffInDays($lastEmail->created_at) : null,
            'has_recent_engagement' => ($lastActivity && now()->diffInDays($lastActivity->created_at) <= 7) ||
                ($lastEmail && now()->diffInDays($lastEmail->created_at) <= 7),
        ];
    }

    /**
     * Get velocity details for a lead.
     *
     * @param  Lead  $lead
     * @return array
     */
    protected function getVelocityDetails($lead): array
    {
        try {
            return $this->velocityService->calculateVelocityScore($lead);
        } catch (\Exception $e) {
            return [
                'velocity_score' => 50.0,
                'velocity_level' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get value details for a lead.
     *
     * @param  Lead  $lead
     * @return array
     */
    protected function getValueDetails($lead): array
    {
        $value = $lead->lead_value ?? 0;

        return [
            'value' => $value,
            'value_tier' => $this->getValueTier($value),
            'expected_close_date' => $lead->expected_close_date?->format('Y-m-d'),
            'days_until_close' => $lead->expected_close_date
                ? now()->diffInDays($lead->expected_close_date, false)
                : null,
        ];
    }

    /**
     * Get value tier for a deal.
     *
     * @param  float  $value
     * @return string
     */
    protected function getValueTier(float $value): string
    {
        if ($value >= self::ENTERPRISE_VALUE) {
            return 'enterprise';
        }

        if ($value >= self::LARGE_VALUE) {
            return 'large';
        }

        if ($value >= self::MEDIUM_VALUE) {
            return 'medium';
        }

        if ($value > 0) {
            return 'small';
        }

        return 'unknown';
    }

    /**
     * Get active leads.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @return Collection
     */
    protected function getActiveLeads(?int $userId = null, ?int $pipelineId = null): Collection
    {
        $query = $this->leadRepository->getModel()
            ->with(['stage', 'pipeline', 'user'])
            ->where('status', 1); // Open leads only

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        return $query->get();
    }
}
