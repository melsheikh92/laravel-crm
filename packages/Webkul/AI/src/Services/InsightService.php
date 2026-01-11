<?php

namespace Webkul\AI\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Webkul\AI\Repositories\InsightRepository;
use Webkul\Email\Repositories\EmailRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\SalesForecastRepository;
use Webkul\Lead\Repositories\DealScoreRepository;
use Webkul\Lead\Services\DealScoringService;
use Webkul\Quote\Repositories\QuoteRepository;

class InsightService
{
    /**
     * API endpoint for OpenRouter AI service.
     */
    const OPEN_ROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * Maximum token limit for AI prompt.
     */
    const MAX_TOKENS = 8000;

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected InsightRepository $insightRepository,
        protected LeadRepository $leadRepository,
        protected EmailRepository $emailRepository,
        protected QuoteRepository $quoteRepository,
        protected SalesForecastRepository $salesForecastRepository,
        protected DealScoreRepository $dealScoreRepository,
        protected DealScoringService $dealScoringService
    ) {}

    /**
     * Generate insights for a lead.
     *
     * @param  int  $leadId
     * @return array
     */
    public function generateLeadInsights(int $leadId): array
    {
        $lead = $this->leadRepository->with([
            'person',
            'person.organization',
            'stage',
            'type',
            'source',
        ])->findOrFail($leadId);

        // Collect data for analysis
        $context = $this->buildLeadContext($lead);

        // Generate different types of insights
        $insights = [];

        // Lead scoring insight
        $scoringInsight = $this->generateLeadScoringInsight($lead, $context);
        if ($scoringInsight) {
            $insights[] = $scoringInsight;
        }

        // Relationship insight
        $relationshipInsight = $this->generateRelationshipInsight($lead, $context);
        if ($relationshipInsight) {
            $insights[] = $relationshipInsight;
        }

        // Opportunity insight
        $opportunityInsight = $this->generateOpportunityInsight($lead, $context);
        if ($opportunityInsight) {
            $insights[] = $opportunityInsight;
        }

        // Store insights
        foreach ($insights as $insight) {
            $existing = $this->insightRepository->findWhere([
                'entity_type' => 'lead',
                'entity_id' => $leadId,
                'type' => $insight['type'],
            ])->first();

            if ($existing) {
                $this->insightRepository->update($insight, $existing->id);
            } else {
                $this->insightRepository->create(array_merge($insight, [
                    'entity_type' => 'lead',
                    'entity_id' => $leadId,
                ]));
            }
        }

        return ['insights' => $insights];
    }

    /**
     * Generate insights for a person.
     *
     * @param  int  $personId
     * @return array
     */
    public function generatePersonInsights(int $personId): array
    {
        // Similar implementation for person insights
        // Simplified for now - can be expanded
        return ['insights' => []];
    }

    /**
     * Get insights for an entity.
     *
     * @param  string  $entityType
     * @param  int  $entityId
     * @return \Illuminate\Support\Collection
     */
    public function getInsights(string $entityType, int $entityId)
    {
        return $this->insightRepository->findWhere([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ])->sortByDesc('priority')->sortByDesc('created_at');
    }

    /**
     * Generate forecast insights for a sales forecast.
     *
     * @param  int  $forecastId
     * @return array|null
     */
    public function generateForecastInsights(int $forecastId): ?array
    {
        $forecast = $this->salesForecastRepository->with(['user', 'actuals'])->findOrFail($forecastId);

        // Build context for forecast analysis
        $context = $this->buildForecastContext($forecast);

        // Generate AI insight
        $prompt = $this->buildForecastInsightPrompt($forecast, $context);
        $result = $this->callAI($prompt);

        if (isset($result['error']) || !isset($result['insight'])) {
            return null;
        }

        $insight = [
            'type' => 'forecast_analysis',
            'title' => $result['insight']['title'] ?? 'Forecast Insight',
            'description' => $result['insight']['description'] ?? '',
            'priority' => $result['insight']['priority'] ?? 5,
            'metadata' => array_merge(
                $result['insight']['metadata'] ?? [],
                ['forecast_id' => $forecastId]
            ),
        ];

        // Store insight (using forecast as entity)
        $existing = $this->insightRepository->findWhere([
            'entity_type' => 'forecast',
            'entity_id' => $forecastId,
            'type' => 'forecast_analysis',
        ])->first();

        if ($existing) {
            $this->insightRepository->update($insight, $existing->id);
        } else {
            $this->insightRepository->create(array_merge($insight, [
                'entity_type' => 'forecast',
                'entity_id' => $forecastId,
            ]));
        }

        return $insight;
    }

    /**
     * Generate deal prioritization insights for a user.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return array
     */
    public function generateDealPrioritizationInsights(int $userId, int $limit = 10): array
    {
        // Get top priority deals for the user
        $topDeals = $this->dealScoringService->getTopPriorityDeals($limit, $userId);

        if ($topDeals->isEmpty()) {
            return [
                'insights' => [],
                'message' => 'No deals found for prioritization',
            ];
        }

        // Build context with deal scores
        $context = $this->buildPrioritizationContext($topDeals, $userId);

        // Generate AI recommendations
        $prompt = $this->buildPrioritizationPrompt($context);
        $result = $this->callAI($prompt);

        if (isset($result['error']) || !isset($result['insight'])) {
            return [
                'insights' => [],
                'error' => $result['error'] ?? 'Failed to generate insights',
            ];
        }

        $insight = [
            'type' => 'deal_prioritization',
            'title' => $result['insight']['title'] ?? 'Deal Prioritization Recommendations',
            'description' => $result['insight']['description'] ?? '',
            'priority' => $result['insight']['priority'] ?? 8,
            'metadata' => array_merge(
                $result['insight']['metadata'] ?? [],
                [
                    'user_id' => $userId,
                    'deal_count' => $topDeals->count(),
                    'top_deals' => $topDeals->pluck('lead_id')->toArray(),
                ]
            ),
        ];

        // Store insight for user entity
        $existing = $this->insightRepository->findWhere([
            'entity_type' => 'user',
            'entity_id' => $userId,
            'type' => 'deal_prioritization',
        ])->first();

        if ($existing) {
            $this->insightRepository->update($insight, $existing->id);
        } else {
            $this->insightRepository->create(array_merge($insight, [
                'entity_type' => 'user',
                'entity_id' => $userId,
            ]));
        }

        return ['insights' => [$insight]];
    }

    /**
     * Generate risk assessment insights for a lead.
     *
     * @param  int  $leadId
     * @return array|null
     */
    public function generateRiskAssessmentInsights(int $leadId): ?array
    {
        $lead = $this->leadRepository->with([
            'person',
            'person.organization',
            'stage',
            'pipeline',
            'user',
        ])->findOrFail($leadId);

        // Get deal score
        $dealScore = $this->dealScoreRepository->getLatestByLead($leadId);

        // Build context for risk analysis
        $context = $this->buildRiskContext($lead, $dealScore);

        // Generate AI risk assessment
        $prompt = $this->buildRiskAssessmentPrompt($lead, $context);
        $result = $this->callAI($prompt);

        if (isset($result['error']) || !isset($result['insight'])) {
            return null;
        }

        $insight = [
            'type' => 'risk_assessment',
            'title' => $result['insight']['title'] ?? 'Risk Assessment',
            'description' => $result['insight']['description'] ?? '',
            'priority' => $result['insight']['priority'] ?? 7,
            'metadata' => array_merge(
                $result['insight']['metadata'] ?? [],
                ['risk_level' => $this->determineRiskLevel($context)]
            ),
        ];

        // Store insight
        $existing = $this->insightRepository->findWhere([
            'entity_type' => 'lead',
            'entity_id' => $leadId,
            'type' => 'risk_assessment',
        ])->first();

        if ($existing) {
            $this->insightRepository->update($insight, $existing->id);
        } else {
            $this->insightRepository->create(array_merge($insight, [
                'entity_type' => 'lead',
                'entity_id' => $leadId,
            ]));
        }

        return $insight;
    }

    /**
     * Build context for lead analysis.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @return array
     */
    protected function buildLeadContext($lead): array
    {
        $context = [
            'lead' => [
                'title' => $lead->title ?? '',
                'description' => $lead->description ?? '',
                'value' => $lead->lead_value ?? 0,
                'stage' => $lead->stage ? $lead->stage->name : '',
                'created_at' => $lead->created_at->format('Y-m-d H:i:s'),
            ],
        ];

        // Get email activity
        $emails = $this->emailRepository
            ->where('lead_id', $lead->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $context['email_count'] = $emails->count();
        $context['recent_emails'] = $emails->take(5)->map(function ($email) {
            return [
                'subject' => $email->subject ?? '',
                'date' => $email->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        // Get quotes (quotes use many-to-many relationship with leads)
        $quotes = DB::table('quotes')
            ->leftJoin('lead_quotes', 'quotes.id', '=', 'lead_quotes.quote_id')
            ->where('lead_quotes.lead_id', $lead->id)
            ->select('quotes.*')
            ->get();

        $context['quote_count'] = $quotes->count();
        $context['total_quote_value'] = $quotes->sum('grand_total');

        // Get activities
        $activities = DB::table('activities')
            ->leftJoin('lead_activities', 'activities.id', '=', 'lead_activities.activity_id')
            ->where('lead_activities.lead_id', $lead->id)
            ->select('activities.*')
            ->orderBy('activities.created_at', 'desc')
            ->limit(10)
            ->get();

        $context['activity_count'] = $activities->count();

        return $context;
    }

    /**
     * Generate lead scoring insight.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @param  array  $context
     * @return array|null
     */
    protected function generateLeadScoringInsight($lead, array $context): ?array
    {
        $prompt = $this->buildLeadScoringPrompt($lead, $context);
        $result = $this->callAI($prompt);

        if (isset($result['error']) || !isset($result['insight'])) {
            return null;
        }

        return [
            'type' => 'lead_scoring',
            'title' => $result['insight']['title'] ?? 'Lead Scoring Insight',
            'description' => $result['insight']['description'] ?? '',
            'priority' => $result['insight']['priority'] ?? 5,
            'metadata' => $result['insight']['metadata'] ?? [],
        ];
    }

    /**
     * Generate relationship insight.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @param  array  $context
     * @return array|null
     */
    protected function generateRelationshipInsight($lead, array $context): ?array
    {
        $prompt = $this->buildRelationshipPrompt($lead, $context);
        $result = $this->callAI($prompt);

        if (isset($result['error']) || !isset($result['insight'])) {
            return null;
        }

        return [
            'type' => 'relationship',
            'title' => $result['insight']['title'] ?? 'Relationship Insight',
            'description' => $result['insight']['description'] ?? '',
            'priority' => $result['insight']['priority'] ?? 5,
            'metadata' => $result['insight']['metadata'] ?? [],
        ];
    }

    /**
     * Generate opportunity insight.
     *
     * @param  \Webkul\Lead\Contracts\Lead  $lead
     * @param  array  $context
     * @return array|null
     */
    protected function generateOpportunityInsight($lead, array $context): ?array
    {
        $prompt = $this->buildOpportunityPrompt($lead, $context);
        $result = $this->callAI($prompt);

        if (isset($result['error']) || !isset($result['insight'])) {
            return null;
        }

        return [
            'type' => 'opportunity',
            'title' => $result['insight']['title'] ?? 'Opportunity Insight',
            'description' => $result['insight']['description'] ?? '',
            'priority' => $result['insight']['priority'] ?? 5,
            'metadata' => $result['insight']['metadata'] ?? [],
        ];
    }

    /**
     * Build prompt for lead scoring.
     */
    protected function buildLeadScoringPrompt($lead, array $context): string
    {
        $prompt = "Analyze this CRM lead and provide a lead scoring insight.\n\n";
        $prompt .= "Lead Information:\n";
        $prompt .= "- Title: {$context['lead']['title']}\n";
        $prompt .= "- Value: {$context['lead']['value']}\n";
        $prompt .= "- Stage: {$context['lead']['stage']}\n";
        $prompt .= "- Email Count: {$context['email_count']}\n";
        $prompt .= "- Activity Count: {$context['activity_count']}\n";
        $prompt .= "- Quote Count: {$context['quote_count']} (Total Value: {$context['total_quote_value']})\n\n";
        $prompt .= "Respond with ONLY a valid JSON object (no markdown, no code blocks, no explanations):\n";
        $prompt .= '{"title":"Brief descriptive title","description":"Concise analysis (2-3 sentences)","priority":5,"metadata":{}}';

        return $prompt;
    }

    /**
     * Build prompt for relationship insight.
     */
    protected function buildRelationshipPrompt($lead, array $context): string
    {
        $prompt = "Analyze the relationship health for this CRM lead.\n\n";
        $prompt .= "Lead Information:\n";
        $prompt .= "- Title: {$context['lead']['title']}\n";
        $prompt .= "- Email Interactions: {$context['email_count']}\n";
        $prompt .= "- Activities: {$context['activity_count']}\n\n";
        $prompt .= "Respond with ONLY a valid JSON object (no markdown, no code blocks, no explanations):\n";
        $prompt .= '{"title":"Brief descriptive title","description":"Concise relationship analysis (2-3 sentences)","priority":5,"metadata":{}}';

        return $prompt;
    }

    /**
     * Build prompt for opportunity insight.
     */
    protected function buildOpportunityPrompt($lead, array $context): string
    {
        $prompt = "Analyze upsell/cross-sell opportunities for this CRM lead.\n\n";
        $prompt .= "Lead Information:\n";
        $prompt .= "- Title: {$context['lead']['title']}\n";
        $prompt .= "- Current Value: {$context['lead']['value']}\n";
        $prompt .= "- Quote Count: {$context['quote_count']} (Total Value: {$context['total_quote_value']})\n\n";
        $prompt .= "Respond with ONLY a valid JSON object (no markdown, no code blocks, no explanations):\n";
        $prompt .= '{"title":"Brief descriptive title","description":"Concise opportunity recommendation (2-3 sentences)","priority":5,"metadata":{}}';

        return $prompt;
    }

    /**
     * Call AI API.
     */
    protected function callAI(string $prompt): array
    {
        $model = core()->getConfigData('general.magic_ai.settings.other_model') 
            ?: core()->getConfigData('general.magic_ai.settings.model');

        $apiKey = core()->getConfigData('general.magic_ai.settings.api_key');

        if (!$apiKey || !$model) {
            return ['error' => trans('admin::app.leads.file.missing-api-key')];
        }

        try {
            // System message to enforce structured JSON output
            $systemMessage = "You are a helpful CRM assistant that provides structured insights. You MUST respond with ONLY valid JSON. Do not include any markdown formatting, code blocks, explanations, or text outside the JSON object. Your response must be parseable as JSON.";

            $response = \Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post(self::OPEN_ROUTER_URL, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemMessage,
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 500,
                'temperature' => 0.3, // Lower temperature for more consistent, structured output
            ]);

            if ($response->failed()) {
                throw new Exception($response->body());
            }

            $data = $response->json();

            if (isset($data['error'])) {
                throw new Exception($data['error']['message'] ?? 'AI service error');
            }

            $content = trim($data['choices'][0]['message']['content'] ?? '{}');
            
            // First, try direct JSON parsing (most efficient)
            $insight = json_decode($content, true);
            
            // If that fails, try extracting JSON from markdown or other formats
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($insight)) {
                $insight = $this->extractJsonFromResponse($content);
            }

            if (!$insight || !is_array($insight) || json_last_error() !== JSON_ERROR_NONE) {
                \Log::warning('Failed to parse AI response as JSON', [
                    'content' => substr($content, 0, 200),
                    'error' => json_last_error_msg(),
                ]);
                return ['error' => 'Failed to parse AI response as JSON'];
            }

            // Clean and validate the insight data
            $insight = $this->cleanInsightData($insight);

            return ['insight' => $insight];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Extract JSON from AI response (handles markdown code blocks and plain text).
     */
    protected function extractJsonFromResponse(string $content): ?array
    {
        $content = trim($content);

        // Remove any leading/trailing whitespace
        $content = trim($content);

        // Try to find JSON in markdown code blocks (```json ... ``` or ``` ... ```)
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/', $content, $matches)) {
            $json = json_decode(trim($matches[1]), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return $json;
            }
        }

        // Try to find the first valid JSON object using a more robust approach
        // Look for opening brace and find matching closing brace
        $start = strpos($content, '{');
        if ($start !== false) {
            $depth = 0;
            $end = $start;
            for ($i = $start; $i < strlen($content); $i++) {
                if ($content[$i] === '{') {
                    $depth++;
                } elseif ($content[$i] === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $end = $i + 1;
                        break;
                    }
                }
            }
            
            if ($depth === 0) {
                $jsonStr = substr($content, $start, $end - $start);
                $json = json_decode($jsonStr, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    return $json;
                }
            }
        }

        // Try parsing the entire content as JSON (last resort)
        $json = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        return null;
    }

    /**
     * Clean and validate insight data.
     */
    protected function cleanInsightData(array $insight): array
    {
        // Clean description: remove any remaining markdown formatting, extra whitespace, limit length
        $description = $insight['description'] ?? '';
        $description = strip_tags($description); // Remove HTML tags
        $description = preg_replace('/\*\*(.*?)\*\*/', '$1', $description); // Remove bold markdown (**text**)
        $description = preg_replace('/\*(.*?)\*/', '$1', $description); // Remove italic markdown (*text*)
        $description = preg_replace('/`(.*?)`/', '$1', $description); // Remove code markdown (`text`)
        $description = preg_replace('/#+\s*/', '', $description); // Remove markdown headers (# Header)
        $description = preg_replace('/```[\s\S]*?```/', '', $description); // Remove code blocks
        $description = preg_replace('/\n{2,}/', ' ', $description); // Replace multiple newlines with single space
        $description = preg_replace('/\s+/', ' ', $description); // Normalize whitespace
        $description = trim($description);
        
        // Limit description length to 300 characters
        if (strlen($description) > 300) {
            $description = substr($description, 0, 297) . '...';
        }

        // Clean title: remove markdown, limit length
        $title = $insight['title'] ?? 'Insight';
        $title = strip_tags($title);
        $title = preg_replace('/\*\*(.*?)\*\*/', '$1', $title);
        $title = preg_replace('/\*(.*?)\*/', '$1', $title);
        $title = preg_replace('/`(.*?)`/', '$1', $title);
        $title = preg_replace('/\s+/', ' ', $title); // Normalize whitespace
        $title = trim($title);
        
        if (strlen($title) > 100) {
            $title = substr($title, 0, 97) . '...';
        }

        // Ensure priority is a valid integer between 1-10
        $priority = isset($insight['priority']) ? (int) $insight['priority'] : 5;
        $priority = max(1, min(10, $priority));

        return [
            'title' => $title ?: 'Insight',
            'description' => $description ?: 'No description available.',
            'priority' => $priority,
            'metadata' => is_array($insight['metadata'] ?? null) ? $insight['metadata'] : [],
        ];
    }

    /**
     * Build context for forecast analysis.
     */
    protected function buildForecastContext($forecast): array
    {
        $metadata = is_array($forecast->metadata) ? $forecast->metadata : json_decode($forecast->metadata ?? '{}', true);

        $context = [
            'period_type' => $forecast->period_type,
            'period_start' => $forecast->period_start->format('Y-m-d'),
            'period_end' => $forecast->period_end->format('Y-m-d'),
            'forecast_value' => $forecast->forecast_value,
            'weighted_forecast' => $forecast->weighted_forecast,
            'best_case' => $forecast->best_case,
            'worst_case' => $forecast->worst_case,
            'confidence_score' => $forecast->confidence_score,
            'total_leads' => $metadata['total_leads'] ?? 0,
            'total_value' => $metadata['total_value'] ?? 0,
            'average_deal_size' => $metadata['average_deal_size'] ?? 0,
        ];

        // Add actual vs forecast comparison if available
        if ($forecast->actuals && $forecast->actuals->count() > 0) {
            $actual = $forecast->actuals->first();
            $context['actual_value'] = $actual->actual_value;
            $context['variance'] = $actual->variance;
            $context['variance_percentage'] = $actual->variance_percentage;
        }

        return $context;
    }

    /**
     * Build context for deal prioritization.
     */
    protected function buildPrioritizationContext($topDeals, int $userId): array
    {
        $deals = $topDeals->map(function ($scoreRecord) {
            $lead = $scoreRecord->lead;
            $factors = is_array($scoreRecord->factors) ? $scoreRecord->factors : json_decode($scoreRecord->factors ?? '{}', true);

            return [
                'lead_id' => $lead->id,
                'title' => $lead->title,
                'value' => $lead->lead_value ?? 0,
                'score' => $scoreRecord->score,
                'win_probability' => $scoreRecord->win_probability,
                'engagement_score' => $scoreRecord->engagement_score,
                'velocity_score' => $scoreRecord->velocity_score,
                'stage' => $lead->stage ? $lead->stage->name : 'Unknown',
                'expected_close_date' => $lead->expected_close_date ? $lead->expected_close_date->format('Y-m-d') : null,
            ];
        })->toArray();

        return [
            'user_id' => $userId,
            'deal_count' => count($deals),
            'total_value' => array_sum(array_column($deals, 'value')),
            'average_score' => count($deals) > 0 ? array_sum(array_column($deals, 'score')) / count($deals) : 0,
            'average_win_probability' => count($deals) > 0 ? array_sum(array_column($deals, 'win_probability')) / count($deals) : 0,
            'deals' => array_slice($deals, 0, 5), // Top 5 for prompt
        ];
    }

    /**
     * Build context for risk assessment.
     */
    protected function buildRiskContext($lead, $dealScore): array
    {
        $context = [
            'lead_id' => $lead->id,
            'title' => $lead->title,
            'value' => $lead->lead_value ?? 0,
            'stage' => $lead->stage ? $lead->stage->name : 'Unknown',
            'pipeline' => $lead->pipeline ? $lead->pipeline->name : 'Unknown',
            'created_at' => $lead->created_at->format('Y-m-d'),
            'days_in_pipeline' => $lead->created_at->diffInDays(now()),
        ];

        if ($lead->expected_close_date) {
            $context['expected_close_date'] = $lead->expected_close_date->format('Y-m-d');
            $context['days_until_close'] = now()->diffInDays($lead->expected_close_date, false);
            $context['is_overdue'] = $lead->expected_close_date < now();
        }

        // Add deal score data if available
        if ($dealScore) {
            $context['score'] = $dealScore->score;
            $context['win_probability'] = $dealScore->win_probability;
            $context['engagement_score'] = $dealScore->engagement_score;
            $context['velocity_score'] = $dealScore->velocity_score;

            $factors = is_array($dealScore->factors) ? $dealScore->factors : json_decode($dealScore->factors ?? '{}', true);
            $context['engagement_details'] = $factors['engagement_details'] ?? [];
            $context['velocity_details'] = $factors['velocity_details'] ?? [];
        }

        // Get recent activity data
        $leadContext = $this->buildLeadContext($lead);
        $context['email_count'] = $leadContext['email_count'];
        $context['activity_count'] = $leadContext['activity_count'];
        $context['quote_count'] = $leadContext['quote_count'];

        return $context;
    }

    /**
     * Build prompt for forecast insights.
     */
    protected function buildForecastInsightPrompt($forecast, array $context): string
    {
        $prompt = "Analyze this sales forecast and provide strategic insights.\n\n";
        $prompt .= "Forecast Information:\n";
        $prompt .= "- Period: {$context['period_type']} ({$context['period_start']} to {$context['period_end']})\n";
        $prompt .= "- Weighted Forecast: \${$context['weighted_forecast']}\n";
        $prompt .= "- Best Case: \${$context['best_case']}\n";
        $prompt .= "- Worst Case: \${$context['worst_case']}\n";
        $prompt .= "- Confidence Score: {$context['confidence_score']}%\n";
        $prompt .= "- Total Leads: {$context['total_leads']}\n";
        $prompt .= "- Average Deal Size: \${$context['average_deal_size']}\n";

        if (isset($context['actual_value'])) {
            $prompt .= "- Actual Value: \${$context['actual_value']}\n";
            $prompt .= "- Variance: {$context['variance_percentage']}%\n";
        }

        $prompt .= "\nProvide insights on:\n";
        $prompt .= "1. Forecast confidence and reliability\n";
        $prompt .= "2. Key risks or opportunities\n";
        $prompt .= "3. Actionable recommendations\n\n";
        $prompt .= "Respond with ONLY a valid JSON object (no markdown, no code blocks, no explanations):\n";
        $prompt .= '{"title":"Brief descriptive title","description":"Concise analysis (2-3 sentences)","priority":7,"metadata":{}}';

        return $prompt;
    }

    /**
     * Build prompt for deal prioritization.
     */
    protected function buildPrioritizationPrompt(array $context): string
    {
        $prompt = "Analyze these top-priority deals and provide prioritization recommendations.\n\n";
        $prompt .= "Portfolio Overview:\n";
        $prompt .= "- Total Deals: {$context['deal_count']}\n";
        $prompt .= "- Total Value: \${$context['total_value']}\n";
        $prompt .= "- Average Score: {$context['average_score']}\n";
        $prompt .= "- Average Win Probability: {$context['average_win_probability']}%\n\n";

        $prompt .= "Top Deals:\n";
        foreach ($context['deals'] as $i => $deal) {
            $num = $i + 1;
            $prompt .= "{$num}. {$deal['title']} - Value: \${$deal['value']}, Score: {$deal['score']}, Win%: {$deal['win_probability']}%, Stage: {$deal['stage']}\n";
        }

        $prompt .= "\nProvide strategic recommendations on:\n";
        $prompt .= "1. Which deals to focus on first and why\n";
        $prompt .= "2. Potential quick wins\n";
        $prompt .= "3. Deals that need immediate attention\n\n";
        $prompt .= "Respond with ONLY a valid JSON object (no markdown, no code blocks, no explanations):\n";
        $prompt .= '{"title":"Brief descriptive title","description":"Concise prioritization guidance (2-3 sentences)","priority":8,"metadata":{}}';

        return $prompt;
    }

    /**
     * Build prompt for risk assessment.
     */
    protected function buildRiskAssessmentPrompt($lead, array $context): string
    {
        $prompt = "Analyze this deal for potential risks and provide a risk assessment.\n\n";
        $prompt .= "Deal Information:\n";
        $prompt .= "- Title: {$context['title']}\n";
        $prompt .= "- Value: \${$context['value']}\n";
        $prompt .= "- Stage: {$context['stage']}\n";
        $prompt .= "- Days in Pipeline: {$context['days_in_pipeline']}\n";

        if (isset($context['expected_close_date'])) {
            $prompt .= "- Expected Close: {$context['expected_close_date']}\n";
            $prompt .= "- Days Until Close: {$context['days_until_close']}\n";
            if ($context['is_overdue']) {
                $prompt .= "- Status: OVERDUE\n";
            }
        }

        if (isset($context['score'])) {
            $prompt .= "- Deal Score: {$context['score']}\n";
            $prompt .= "- Win Probability: {$context['win_probability']}%\n";
            $prompt .= "- Engagement Score: {$context['engagement_score']}\n";
            $prompt .= "- Velocity Score: {$context['velocity_score']}\n";
        }

        $prompt .= "- Email Count: {$context['email_count']}\n";
        $prompt .= "- Activity Count: {$context['activity_count']}\n";
        $prompt .= "- Quote Count: {$context['quote_count']}\n\n";

        $prompt .= "Assess risks including:\n";
        $prompt .= "1. Deal stagnation or velocity concerns\n";
        $prompt .= "2. Engagement issues\n";
        $prompt .= "3. Timeline risks\n";
        $prompt .= "4. Mitigation recommendations\n\n";
        $prompt .= "Respond with ONLY a valid JSON object (no markdown, no code blocks, no explanations):\n";
        $prompt .= '{"title":"Brief descriptive title","description":"Concise risk analysis (2-3 sentences)","priority":7,"metadata":{}}';

        return $prompt;
    }

    /**
     * Determine risk level based on context.
     */
    protected function determineRiskLevel(array $context): string
    {
        $riskScore = 0;

        // Check if overdue
        if (isset($context['is_overdue']) && $context['is_overdue']) {
            $riskScore += 30;
        }

        // Check days in pipeline
        if ($context['days_in_pipeline'] > 90) {
            $riskScore += 20;
        } elseif ($context['days_in_pipeline'] > 60) {
            $riskScore += 10;
        }

        // Check engagement
        if ($context['email_count'] < 3 && $context['activity_count'] < 3) {
            $riskScore += 25;
        }

        // Check deal score if available
        if (isset($context['score'])) {
            if ($context['score'] < 40) {
                $riskScore += 20;
            } elseif ($context['score'] < 60) {
                $riskScore += 10;
            }

            // Check velocity
            if ($context['velocity_score'] < 40) {
                $riskScore += 15;
            }
        }

        // Determine level
        if ($riskScore >= 60) {
            return 'high';
        } elseif ($riskScore >= 30) {
            return 'medium';
        }

        return 'low';
    }
}

