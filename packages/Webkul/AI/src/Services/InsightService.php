<?php

namespace Webkul\AI\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Webkul\AI\Repositories\InsightRepository;
use Webkul\Email\Repositories\EmailRepository;
use Webkul\Lead\Repositories\LeadRepository;
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
        protected QuoteRepository $quoteRepository
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
}

