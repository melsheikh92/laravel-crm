<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Collection;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\LeadRepository;

class WinProbabilityService
{
    /**
     * Win probability calculation weights (must sum to 100).
     */
    const STAGE_PROBABILITY_WEIGHT = 35.0;
    const HISTORICAL_PATTERN_WEIGHT = 25.0;
    const DEAL_CHARACTERISTICS_WEIGHT = 20.0;
    const ENGAGEMENT_WEIGHT = 12.0;
    const VELOCITY_WEIGHT = 8.0;

    /**
     * Confidence level thresholds.
     */
    const HIGH_CONFIDENCE_THRESHOLD = 75.0;
    const MEDIUM_CONFIDENCE_THRESHOLD = 50.0;

    /**
     * Risk level thresholds.
     */
    const LOW_RISK_THRESHOLD = 70.0;
    const MEDIUM_RISK_THRESHOLD = 40.0;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(
        protected LeadRepository $leadRepository,
        protected HistoricalAnalysisService $historicalAnalysisService,
        protected DealVelocityService $dealVelocityService,
        protected DealScoringService $dealScoringService
    ) {
    }

    /**
     * Calculate win probability for a lead.
     *
     * @param  Lead|int  $lead
     * @return array
     */
    public function calculateWinProbability($lead): array
    {
        if (is_int($lead)) {
            $lead = $this->leadRepository->with(['stage', 'pipeline', 'user'])->find($lead);
        }

        if (!$lead) {
            throw new \InvalidArgumentException('Lead not found');
        }

        // Calculate individual probability factors
        $stageProbabilityFactor = $this->calculateStageProbabilityFactor($lead);
        $historicalPatternFactor = $this->calculateHistoricalPatternFactor($lead);
        $dealCharacteristicsFactor = $this->calculateDealCharacteristicsFactor($lead);
        $engagementFactor = $this->calculateEngagementFactor($lead);
        $velocityFactor = $this->calculateVelocityFactor($lead);

        // Calculate weighted win probability
        $winProbability = $this->calculateWeightedProbability(
            $stageProbabilityFactor,
            $historicalPatternFactor,
            $dealCharacteristicsFactor,
            $engagementFactor,
            $velocityFactor
        );

        // Apply modifiers based on deal conditions
        $adjustedProbability = $this->applyProbabilityModifiers($lead, $winProbability);

        // Calculate confidence score
        $confidenceScore = $this->calculateConfidenceScore($lead, [
            'stage_probability' => $stageProbabilityFactor,
            'historical_pattern' => $historicalPatternFactor,
            'deal_characteristics' => $dealCharacteristicsFactor,
            'engagement' => $engagementFactor,
            'velocity' => $velocityFactor,
        ]);

        // Determine risk level
        $riskLevel = $this->determineRiskLevel($adjustedProbability);

        // Get recommendations
        $recommendations = $this->generateRecommendations($lead, $adjustedProbability, $riskLevel);

        return [
            'win_probability' => round($adjustedProbability, 2),
            'confidence_score' => round($confidenceScore, 2),
            'risk_level' => $riskLevel,
            'factors' => [
                'stage_probability' => round($stageProbabilityFactor, 2),
                'historical_pattern' => round($historicalPatternFactor, 2),
                'deal_characteristics' => round($dealCharacteristicsFactor, 2),
                'engagement' => round($engagementFactor, 2),
                'velocity' => round($velocityFactor, 2),
            ],
            'factor_weights' => [
                'stage_probability' => self::STAGE_PROBABILITY_WEIGHT,
                'historical_pattern' => self::HISTORICAL_PATTERN_WEIGHT,
                'deal_characteristics' => self::DEAL_CHARACTERISTICS_WEIGHT,
                'engagement' => self::ENGAGEMENT_WEIGHT,
                'velocity' => self::VELOCITY_WEIGHT,
            ],
            'modifiers' => $this->getAppliedModifiers($lead, $winProbability),
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Calculate win probabilities for multiple leads.
     *
     * @param  Collection  $leads
     * @return Collection
     */
    public function calculateWinProbabilities(Collection $leads): Collection
    {
        return $leads->map(function ($lead) {
            try {
                return [
                    'lead_id' => $lead->id,
                    'probability_data' => $this->calculateWinProbability($lead),
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
     * Get high-probability deals.
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  int  $limit
     * @return Collection
     */
    public function getHighProbabilityDeals(?int $userId = null, ?int $pipelineId = null, int $limit = 10): Collection
    {
        $leads = $this->getActiveLeads($userId, $pipelineId);

        return $leads->map(function ($lead) {
            $probability = $this->calculateWinProbability($lead);
            $lead->win_probability_data = $probability;

            return $lead;
        })
            ->filter(fn($lead) => $lead->win_probability_data['win_probability'] >= self::LOW_RISK_THRESHOLD)
            ->sortByDesc('win_probability_data.win_probability')
            ->take($limit)
            ->values();
    }

    /**
     * Get at-risk deals (low win probability).
     *
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @param  int  $limit
     * @return Collection
     */
    public function getAtRiskDeals(?int $userId = null, ?int $pipelineId = null, int $limit = 10): Collection
    {
        $leads = $this->getActiveLeads($userId, $pipelineId);

        return $leads->map(function ($lead) {
            $probability = $this->calculateWinProbability($lead);
            $lead->win_probability_data = $probability;

            return $lead;
        })
            ->filter(fn($lead) => $lead->win_probability_data['risk_level'] === 'high')
            ->sortBy('win_probability_data.win_probability')
            ->take($limit)
            ->values();
    }

    /**
     * Get deals by risk level.
     *
     * @param  string  $riskLevel
     * @param  int|null  $userId
     * @param  int|null  $pipelineId
     * @return Collection
     */
    public function getDealsByRiskLevel(string $riskLevel, ?int $userId = null, ?int $pipelineId = null): Collection
    {
        $leads = $this->getActiveLeads($userId, $pipelineId);

        return $leads->map(function ($lead) {
            $probability = $this->calculateWinProbability($lead);
            $lead->win_probability_data = $probability;

            return $lead;
        })
            ->filter(fn($lead) => $lead->win_probability_data['risk_level'] === $riskLevel)
            ->sortByDesc('win_probability_data.win_probability')
            ->values();
    }

    /**
     * Calculate stage probability factor (0-100).
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateStageProbabilityFactor($lead): float
    {
        if (!$lead->stage || !isset($lead->stage->probability)) {
            return 50.0; // Neutral score if no stage probability
        }

        // Stage probability is typically 0-100
        return min(100, max(0, (float) $lead->stage->probability));
    }

    /**
     * Calculate historical pattern factor (0-100).
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateHistoricalPatternFactor($lead): float
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

            // Get stage-specific conversion rates
            $stageConversions = $this->historicalAnalysisService->getConversionRatesByStage(
                $lead->lead_pipeline_id,
                $lead->user_id,
                90
            );

            $stageConversionRate = 0;
            foreach ($stageConversions as $conversion) {
                if ($conversion['stage_id'] == $lead->lead_pipeline_stage_id) {
                    $stageConversionRate = $conversion['conversion_rate'];
                    break;
                }
            }

            // Combine win rate (60%) and stage conversion rate (40%)
            return ($winRate * 0.6) + ($stageConversionRate * 0.4);
        } catch (\Exception $e) {
            return 50.0; // Neutral score if analysis fails
        }
    }

    /**
     * Calculate deal characteristics factor (0-100).
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateDealCharacteristicsFactor($lead): float
    {
        $factors = [];

        // Factor 1: Deal value relative to average (30% weight)
        $valueFactor = $this->getValueFactor($lead);
        $factors[] = $valueFactor * 0.30;

        // Factor 2: Expected close date alignment (30% weight)
        $closeDateFactor = $this->getCloseDateFactor($lead);
        $factors[] = $closeDateFactor * 0.30;

        // Factor 3: Time in pipeline (20% weight)
        $timeInPipelineFactor = $this->getTimeInPipelineFactor($lead);
        $factors[] = $timeInPipelineFactor * 0.20;

        // Factor 4: Pipeline stage position (20% weight)
        $stagePositionFactor = $this->getStagePositionFactor($lead);
        $factors[] = $stagePositionFactor * 0.20;

        return array_sum($factors);
    }

    /**
     * Calculate engagement factor (0-100).
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateEngagementFactor($lead): float
    {
        try {
            $scoreData = $this->dealScoringService->scoreLead($lead, false);

            return $scoreData['engagement_score'] ?? 50.0;
        } catch (\Exception $e) {
            return 50.0; // Neutral score if scoring fails
        }
    }

    /**
     * Calculate velocity factor (0-100).
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateVelocityFactor($lead): float
    {
        try {
            $velocityData = $this->dealVelocityService->calculateVelocityScore($lead);

            return $velocityData['velocity_score'] ?? 50.0;
        } catch (\Exception $e) {
            return 50.0; // Neutral score if velocity calculation fails
        }
    }

    /**
     * Calculate weighted win probability.
     *
     * @param  float  $stageProbability
     * @param  float  $historicalPattern
     * @param  float  $dealCharacteristics
     * @param  float  $engagement
     * @param  float  $velocity
     * @return float
     */
    protected function calculateWeightedProbability(
        float $stageProbability,
        float $historicalPattern,
        float $dealCharacteristics,
        float $engagement,
        float $velocity
    ): float {
        return (
            ($stageProbability * self::STAGE_PROBABILITY_WEIGHT / 100) +
            ($historicalPattern * self::HISTORICAL_PATTERN_WEIGHT / 100) +
            ($dealCharacteristics * self::DEAL_CHARACTERISTICS_WEIGHT / 100) +
            ($engagement * self::ENGAGEMENT_WEIGHT / 100) +
            ($velocity * self::VELOCITY_WEIGHT / 100)
        );
    }

    /**
     * Apply modifiers to win probability based on deal conditions.
     *
     * @param  Lead  $lead
     * @param  float  $baseProbability
     * @return float
     */
    protected function applyProbabilityModifiers($lead, float $baseProbability): float
    {
        $modifier = 1.0;

        // Modifier 1: Deal is past expected close date
        if ($lead->expected_close_date && $lead->expected_close_date < now()) {
            $daysOverdue = now()->diffInDays($lead->expected_close_date);

            if ($daysOverdue > 30) {
                $modifier -= 0.20; // 20% penalty for deals over 30 days overdue
            } elseif ($daysOverdue > 14) {
                $modifier -= 0.15; // 15% penalty for deals over 14 days overdue
            } else {
                $modifier -= 0.10; // 10% penalty for recently overdue deals
            }
        }

        // Modifier 2: Deal is close to expected close date (within 14 days)
        if ($lead->expected_close_date) {
            $daysUntilClose = now()->diffInDays($lead->expected_close_date, false);

            if ($daysUntilClose >= 0 && $daysUntilClose <= 14) {
                $modifier += 0.08; // 8% boost for deals closing soon
            }
        }

        // Modifier 3: High-value deal (higher scrutiny)
        if ($lead->lead_value && $lead->lead_value >= 100000) {
            $modifier -= 0.05; // 5% penalty for enterprise deals (longer sales cycles)
        }

        // Modifier 4: Deal has been in pipeline for extended time
        if ($lead->created_at) {
            $daysInPipeline = $lead->created_at->diffInDays(now());

            if ($daysInPipeline > 180) {
                $modifier -= 0.15; // 15% penalty for deals over 6 months old
            } elseif ($daysInPipeline > 90) {
                $modifier -= 0.10; // 10% penalty for deals over 3 months old
            }
        }

        return min(100, max(0, $baseProbability * $modifier));
    }

    /**
     * Calculate confidence score for the probability prediction.
     *
     * @param  Lead  $lead
     * @param  array  $factors
     * @return float
     */
    protected function calculateConfidenceScore($lead, array $factors): float
    {
        $confidenceFactors = [];

        // Factor 1: Historical data availability (30% weight)
        $hasHistoricalData = $lead->user_id && $this->hasHistoricalData($lead);
        $confidenceFactors[] = ($hasHistoricalData ? 100 : 40) * 0.30;

        // Factor 2: Deal completeness (30% weight)
        $completeness = $this->calculateDealCompleteness($lead);
        $confidenceFactors[] = $completeness * 0.30;

        // Factor 3: Factor consistency (25% weight)
        $consistency = $this->calculateFactorConsistency($factors);
        $confidenceFactors[] = $consistency * 0.25;

        // Factor 4: Time in pipeline (15% weight)
        $timeConfidence = $this->getTimeConfidence($lead);
        $confidenceFactors[] = $timeConfidence * 0.15;

        return array_sum($confidenceFactors);
    }

    /**
     * Determine risk level based on win probability.
     *
     * @param  float  $probability
     * @return string
     */
    protected function determineRiskLevel(float $probability): string
    {
        if ($probability >= self::LOW_RISK_THRESHOLD) {
            return 'low';
        }

        if ($probability >= self::MEDIUM_RISK_THRESHOLD) {
            return 'medium';
        }

        return 'high';
    }

    /**
     * Generate recommendations based on win probability and risk level.
     *
     * @param  Lead  $lead
     * @param  float  $probability
     * @param  string  $riskLevel
     * @return array
     */
    protected function generateRecommendations($lead, float $probability, string $riskLevel): array
    {
        $recommendations = [];

        if ($riskLevel === 'high') {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'schedule_immediate_follow_up',
                'message' => 'This deal is at high risk. Schedule an immediate follow-up call or meeting.',
            ];

            if ($lead->expected_close_date && $lead->expected_close_date < now()) {
                $recommendations[] = [
                    'priority' => 'high',
                    'action' => 're_qualify_deal',
                    'message' => 'Deal is past expected close date. Re-qualify the opportunity and update timeline.',
                ];
            }
        }

        if ($riskLevel === 'medium') {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'increase_engagement',
                'message' => 'Increase engagement frequency to improve win probability.',
            ];
        }

        // Velocity-based recommendations
        try {
            $velocityData = $this->dealVelocityService->calculateVelocityScore($lead);

            if ($velocityData['velocity_level'] === 'slow') {
                $recommendations[] = [
                    'priority' => 'medium',
                    'action' => 'accelerate_deal',
                    'message' => 'Deal velocity is slow. Consider creating urgency or addressing blockers.',
                ];
            }
        } catch (\Exception $e) {
            // Skip velocity recommendations if calculation fails
        }

        // Engagement-based recommendations
        try {
            $scoreData = $this->dealScoringService->scoreLead($lead, false);

            if ($scoreData['engagement_score'] < 40) {
                $recommendations[] = [
                    'priority' => 'high',
                    'action' => 'boost_engagement',
                    'message' => 'Low engagement detected. Increase touchpoints and communication.',
                ];
            }
        } catch (\Exception $e) {
            // Skip engagement recommendations if scoring fails
        }

        // Expected close date recommendations
        if (!$lead->expected_close_date) {
            $recommendations[] = [
                'priority' => 'low',
                'action' => 'set_close_date',
                'message' => 'Set an expected close date to better track deal progress.',
            ];
        }

        return $recommendations;
    }

    /**
     * Get value factor for deal characteristics.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function getValueFactor($lead): float
    {
        if (!$lead->lead_value) {
            return 50.0; // Neutral if no value
        }

        // Higher value deals are slightly riskier (longer sales cycles)
        if ($lead->lead_value >= 100000) {
            return 60.0;
        }

        if ($lead->lead_value >= 50000) {
            return 70.0;
        }

        if ($lead->lead_value >= 10000) {
            return 80.0;
        }

        return 75.0; // Small deals
    }

    /**
     * Get close date factor.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function getCloseDateFactor($lead): float
    {
        if (!$lead->expected_close_date) {
            return 50.0; // Neutral if no close date
        }

        $daysUntilClose = now()->diffInDays($lead->expected_close_date, false);

        // Past due
        if ($daysUntilClose < 0) {
            $daysOverdue = abs($daysUntilClose);

            if ($daysOverdue > 60) {
                return 10.0; // Very low score for significantly overdue deals
            }

            if ($daysOverdue > 30) {
                return 30.0;
            }

            return 50.0;
        }

        // Closing very soon (< 7 days) = moderate score (might be rushed)
        if ($daysUntilClose <= 7) {
            return 70.0;
        }

        // Optimal window (7-30 days)
        if ($daysUntilClose <= 30) {
            return 90.0;
        }

        // Medium term (30-90 days)
        if ($daysUntilClose <= 90) {
            return 75.0;
        }

        // Long term (> 90 days)
        return 60.0;
    }

    /**
     * Get time in pipeline factor.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function getTimeInPipelineFactor($lead): float
    {
        if (!$lead->created_at) {
            return 50.0;
        }

        $daysInPipeline = $lead->created_at->diffInDays(now());

        // Recent leads (< 7 days) = moderate score
        if ($daysInPipeline <= 7) {
            return 60.0;
        }

        // Optimal range (7-60 days)
        if ($daysInPipeline <= 60) {
            return 80.0;
        }

        // Aging (60-120 days)
        if ($daysInPipeline <= 120) {
            return 60.0;
        }

        // Stale (> 120 days)
        return 30.0;
    }

    /**
     * Get stage position factor.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function getStagePositionFactor($lead): float
    {
        if (!$lead->stage || !isset($lead->stage->sort_order)) {
            return 50.0;
        }

        // Later stages typically have higher close probability
        // This is a simplified calculation
        $sortOrder = $lead->stage->sort_order;

        if ($sortOrder >= 4) {
            return 90.0; // Late stage
        }

        if ($sortOrder >= 3) {
            return 70.0; // Mid-late stage
        }

        if ($sortOrder >= 2) {
            return 50.0; // Mid stage
        }

        return 30.0; // Early stage
    }

    /**
     * Get applied modifiers description.
     *
     * @param  Lead  $lead
     * @param  float  $baseProbability
     * @return array
     */
    protected function getAppliedModifiers($lead, float $baseProbability): array
    {
        $modifiers = [];

        if ($lead->expected_close_date && $lead->expected_close_date < now()) {
            $daysOverdue = now()->diffInDays($lead->expected_close_date);
            $modifiers[] = [
                'type' => 'overdue_penalty',
                'impact' => 'negative',
                'value' => $daysOverdue > 30 ? -20 : ($daysOverdue > 14 ? -15 : -10),
                'reason' => "Deal is {$daysOverdue} days past expected close date",
            ];
        }

        if ($lead->expected_close_date) {
            $daysUntilClose = now()->diffInDays($lead->expected_close_date, false);

            if ($daysUntilClose >= 0 && $daysUntilClose <= 14) {
                $modifiers[] = [
                    'type' => 'closing_soon_boost',
                    'impact' => 'positive',
                    'value' => 8,
                    'reason' => 'Deal is closing within 14 days',
                ];
            }
        }

        if ($lead->lead_value && $lead->lead_value >= 100000) {
            $modifiers[] = [
                'type' => 'enterprise_deal_adjustment',
                'impact' => 'negative',
                'value' => -5,
                'reason' => 'Enterprise deals typically have longer sales cycles',
            ];
        }

        if ($lead->created_at) {
            $daysInPipeline = $lead->created_at->diffInDays(now());

            if ($daysInPipeline > 180) {
                $modifiers[] = [
                    'type' => 'stale_deal_penalty',
                    'impact' => 'negative',
                    'value' => -15,
                    'reason' => 'Deal has been in pipeline for over 6 months',
                ];
            } elseif ($daysInPipeline > 90) {
                $modifiers[] = [
                    'type' => 'aging_deal_penalty',
                    'impact' => 'negative',
                    'value' => -10,
                    'reason' => 'Deal has been in pipeline for over 3 months',
                ];
            }
        }

        return $modifiers;
    }

    /**
     * Check if historical data is available for the lead.
     *
     * @param  Lead  $lead
     * @return bool
     */
    protected function hasHistoricalData($lead): bool
    {
        try {
            $performance = $this->historicalAnalysisService->analyzeUserPerformance(
                $lead->user_id,
                $lead->lead_pipeline_id,
                90
            );

            return ($performance['total_leads'] ?? 0) >= 5;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Calculate deal completeness score.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function calculateDealCompleteness($lead): float
    {
        $score = 0;
        $totalFields = 0;

        $fields = [
            'lead_value' => 20,
            'expected_close_date' => 20,
            'lead_pipeline_stage_id' => 20,
            'user_id' => 15,
            'person_id' => 10,
            'title' => 10,
            'status' => 5,
        ];

        foreach ($fields as $field => $weight) {
            $totalFields += $weight;

            if (isset($lead->$field) && !empty($lead->$field)) {
                $score += $weight;
            }
        }

        return ($score / $totalFields) * 100;
    }

    /**
     * Calculate factor consistency.
     *
     * @param  array  $factors
     * @return float
     */
    protected function calculateFactorConsistency(array $factors): float
    {
        $values = array_values($factors);

        if (count($values) < 2) {
            return 50.0;
        }

        $mean = array_sum($values) / count($values);
        $variance = 0;

        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }

        $variance /= count($values);
        $stdDev = sqrt($variance);

        // Lower standard deviation = higher consistency = higher confidence
        // Normalize to 0-100 scale (assuming max std dev of 40)
        $consistency = max(0, 100 - ($stdDev * 2.5));

        return $consistency;
    }

    /**
     * Get time confidence factor.
     *
     * @param  Lead  $lead
     * @return float
     */
    protected function getTimeConfidence($lead): float
    {
        if (!$lead->created_at) {
            return 50.0;
        }

        $daysInPipeline = $lead->created_at->diffInDays(now());

        // More time in pipeline = more data = higher confidence (up to a point)
        if ($daysInPipeline >= 14 && $daysInPipeline <= 90) {
            return 100.0;
        }

        if ($daysInPipeline < 14) {
            return 50.0 + ($daysInPipeline / 14 * 50);
        }

        // Very old deals have lower confidence
        if ($daysInPipeline > 180) {
            return 40.0;
        }

        return 70.0;
    }

    /**
     * Get active leads for a user/pipeline.
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
