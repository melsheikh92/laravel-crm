<?php

namespace Webkul\Email\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Email\Repositories\EmailRepository;

class AIEmailSummaryService
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
     * Cache TTL for summaries (24 hours).
     */
    const CACHE_TTL = 86400;

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected EmailRepository $emailRepository
    ) {}

    /**
     * Generate or retrieve cached summary for an email thread.
     *
     * @param  int  $emailId
     * @return array
     */
    public function getSummary(int $emailId): array
    {
        $email = $this->emailRepository->findOrFail($emailId);

        // Determine thread identifier (root email ID)
        $threadId = $this->getThreadId($email);

        // Check cache first
        $cacheKey = "email_summary_{$threadId}";
        $cachedSummary = Cache::get($cacheKey);

        if ($cachedSummary) {
            return [
                'summary' => $cachedSummary,
                'cached' => true,
            ];
        }

        // Generate new summary
        $summary = $this->generateSummary($email, $threadId);

        // Cache the summary
        if (isset($summary['summary'])) {
            Cache::put($cacheKey, $summary['summary'], self::CACHE_TTL);
        }

        return array_merge($summary, ['cached' => false]);
    }

    /**
     * Get thread identifier (root email ID).
     *
     * @param  \Webkul\Email\Contracts\Email  $email
     * @return int
     */
    protected function getThreadId($email): int
    {
        // If this email has a parent, find the root
        if ($email->parent_id) {
            $rootEmail = $email->parent;
            while ($rootEmail && $rootEmail->parent_id) {
                $rootEmail = $rootEmail->parent;
            }
            return $rootEmail ? $rootEmail->id : $email->id;
        }

        return $email->id;
    }

    /**
     * Generate AI summary for email thread.
     *
     * @param  \Webkul\Email\Contracts\Email  $email
     * @param  int  $threadId
     * @return array
     */
    protected function generateSummary($email, int $threadId): array
    {
        // Get all emails in the thread
        $threadEmails = $this->getThreadEmails($threadId);

        if ($threadEmails->isEmpty()) {
            return ['error' => 'No emails found in thread'];
        }

        // Get related activities if lead is associated
        $activities = [];
        if ($email->lead_id) {
            $activities = DB::table('activities')
                ->leftJoin('lead_activities', 'activities.id', '=', 'lead_activities.activity_id')
                ->where('lead_activities.lead_id', $email->lead_id)
                ->select('activities.*')
                ->orderBy('activities.created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Build context for AI
        $context = $this->buildSummaryContext($threadEmails, $activities, $email);

        // Generate summary using AI
        $prompt = $this->buildSummaryPrompt($context);
        $result = $this->callAI($prompt);

        if (isset($result['error'])) {
            return $result;
        }

        return [
            'summary' => $result['summary'],
            'model' => $result['model'] ?? null,
        ];
    }

    /**
     * Get all emails in a thread.
     *
     * @param  int  $threadId
     * @return \Illuminate\Support\Collection
     */
    protected function getThreadEmails(int $threadId): \Illuminate\Support\Collection
    {
        return $this->emailRepository
            ->where(function ($query) use ($threadId) {
                $query->where('id', $threadId)
                    ->orWhere('parent_id', $threadId);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Build context for summary generation.
     *
     * @param  \Illuminate\Support\Collection  $threadEmails
     * @param  \Illuminate\Support\Collection  $activities
     * @param  \Webkul\Email\Contracts\Email  $email
     * @return array
     */
    protected function buildSummaryContext($threadEmails, $activities, $email): array
    {
        $context = [
            'thread_count' => $threadEmails->count(),
            'emails' => [],
        ];

        foreach ($threadEmails as $threadEmail) {
            $context['emails'][] = [
                'subject' => $threadEmail->subject ?? '',
                'from' => $this->formatEmailAddress($threadEmail->from ?? []),
                'to' => $this->formatEmailAddress($threadEmail->reply_to ?? []),
                'body' => strip_tags($threadEmail->reply ?? ''),
                'date' => $threadEmail->created_at->format('Y-m-d H:i:s'),
            ];
        }

        if ($activities->isNotEmpty()) {
            $context['activities'] = $activities->map(function ($activity) {
                return [
                    'type' => $activity->type ?? '',
                    'title' => $activity->title ?? '',
                    'comment' => $activity->comment ?? '',
                    'date' => $activity->created_at ? date('Y-m-d H:i:s', strtotime($activity->created_at)) : '',
                ];
            })->toArray();
        }

        // Add lead/person context if available
        if ($email->lead) {
            $lead = $email->lead;
            $context['lead'] = [
                'title' => $lead->title ?? '',
                'value' => $lead->lead_value ?? 0,
            ];
        }

        if ($email->person) {
            $person = $email->person;
            $personEmails = is_array($person->emails) ? $person->emails : [];
            $context['person'] = [
                'name' => $person->name ?? '',
                'organization' => $person->organization ? $person->organization->name : '',
            ];
        }

        return $context;
    }

    /**
     * Build prompt for summary generation.
     *
     * @param  array  $context
     * @return string
     */
    protected function buildSummaryPrompt(array $context): string
    {
        $prompt = "You are an AI assistant helping summarize email conversations for a CRM system. ";
        $prompt .= "Generate a concise, actionable summary of the email thread.\n\n";

        $prompt .= "=== EMAIL THREAD ===\n";
        $prompt .= "Total emails in thread: {$context['thread_count']}\n\n";

        foreach ($context['emails'] as $index => $email) {
            $prompt .= "Email " . ($index + 1) . ":\n";
            $prompt .= "Date: {$email['date']}\n";
            $prompt .= "From: {$email['from']}\n";
            $prompt .= "To: {$email['to']}\n";
            $prompt .= "Subject: {$email['subject']}\n";
            $prompt .= "Body: " . substr($email['body'], 0, 1000) . "\n\n";
        }

        if (!empty($context['activities'])) {
            $prompt .= "=== RELATED ACTIVITIES ===\n";
            foreach (array_slice($context['activities'], 0, 5) as $activity) {
                $prompt .= "- {$activity['type']}: {$activity['title']} ({$activity['date']})\n";
                if (!empty($activity['comment'])) {
                    $prompt .= "  " . substr($activity['comment'], 0, 200) . "\n";
                }
            }
            $prompt .= "\n";
        }

        if (!empty($context['lead'])) {
            $prompt .= "=== LEAD CONTEXT ===\n";
            $prompt .= "Lead: {$context['lead']['title']} (Value: {$context['lead']['value']})\n\n";
        }

        if (!empty($context['person'])) {
            $prompt .= "=== CONTACT CONTEXT ===\n";
            $prompt .= "Contact: {$context['person']['name']}";
            if (!empty($context['person']['organization'])) {
                $prompt .= " ({$context['person']['organization']})";
            }
            $prompt .= "\n\n";
        }

        $prompt .= "=== INSTRUCTIONS ===\n";
        $prompt .= "Generate a concise summary (2-3 sentences) that:\n";
        $prompt .= "1. Highlights the main topic and purpose of the conversation\n";
        $prompt .= "2. Identifies key decisions, questions, or action items\n";
        $prompt .= "3. Mentions important dates or deadlines if any\n";
        $prompt .= "4. Is professional and clear\n\n";
        $prompt .= "Summary:\n";

        return $prompt;
    }

    /**
     * Format email address from array.
     *
     * @param  array  $emailArray
     * @return string
     */
    protected function formatEmailAddress(array $emailArray): string
    {
        if (empty($emailArray)) {
            return '';
        }

        $first = $emailArray[0];

        if (is_string($first)) {
            return $first;
        }

        return $first['address'] ?? $first['email'] ?? '';
    }

    /**
     * Call AI API to generate summary.
     *
     * @param  string  $prompt
     * @return array
     */
    protected function callAI(string $prompt): array
    {
        $model = core()->getConfigData('general.magic_ai.settings.other_model') 
            ?: core()->getConfigData('general.magic_ai.settings.model');

        $apiKey = core()->getConfigData('general.magic_ai.settings.api_key');

        if (!$apiKey || !$model) {
            return ['error' => trans('admin::app.leads.file.missing-api-key')];
        }

        $truncatedPrompt = $this->truncatePrompt($prompt);

        try {
            $response = \Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post(self::OPEN_ROUTER_URL, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $truncatedPrompt,
                    ],
                ],
                'max_tokens' => 500,
            ]);

            if ($response->failed()) {
                throw new Exception($response->body());
            }

            $data = $response->json();

            if (isset($data['error'])) {
                throw new Exception($data['error']['message'] ?? 'AI service error');
            }

            $summary = $data['choices'][0]['message']['content'] ?? '';

            return [
                'summary' => trim($summary),
                'model' => $model,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Truncate prompt to fit within token limit.
     *
     * @param  string  $prompt
     * @return string
     */
    protected function truncatePrompt(string $prompt): string
    {
        if (strlen($prompt) > self::MAX_TOKENS) {
            $start = mb_substr($prompt, 0, self::MAX_TOKENS * 0.5);
            $end = mb_substr($prompt, -self::MAX_TOKENS * 0.3);

            return $start . "\n\n[... content truncated ...]\n\n" . $end;
        }

        return $prompt;
    }
}

