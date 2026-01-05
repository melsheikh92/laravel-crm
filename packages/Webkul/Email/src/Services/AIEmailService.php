<?php

namespace Webkul\Email\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Webkul\Email\Repositories\EmailRepository;

class AIEmailService
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
        protected EmailRepository $emailRepository
    ) {}

    /**
     * Generate AI reply for an email.
     *
     * @param  int  $emailId
     * @param  string  $tone
     * @param  string  $length
     * @return array
     */
    public function generateReply(int $emailId, string $tone = 'professional', string $length = 'medium'): array
    {
        $email = $this->emailRepository->with([
            'lead',
            'lead.person',
            'lead.person.organization',
            'lead.activities',
            'lead.quotes',
            'person',
            'person.organization',
            'parent',
            'emails',
        ])->findOrFail($emailId);

        $context = $this->buildEmailContext($email);

        $prompt = $this->buildReplyPrompt($email, $context, $tone, $length);

        return $this->callAI($prompt);
    }

    /**
     * Build email context for AI prompt.
     *
     * @param  \Webkul\Email\Contracts\Email  $email
     * @return array
     */
    protected function buildEmailContext($email): array
    {
        $context = [
            'email_subject' => $email->subject ?? '',
            'email_body' => strip_tags($email->reply ?? ''),
            'sender' => $this->formatEmailAddress($email->from ?? []),
            'recipient' => $this->formatEmailAddress($email->reply_to ?? []),
        ];

        // Get email thread
        $threadEmails = $this->getEmailThread($email);
        $context['email_thread'] = $threadEmails->map(function ($threadEmail) {
            return [
                'subject' => $threadEmail->subject,
                'body' => strip_tags($threadEmail->reply ?? ''),
                'from' => $this->formatEmailAddress($threadEmail->from ?? []),
                'date' => $threadEmail->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        // Get lead context
        if ($email->lead_id && $email->lead) {
            $lead = $email->lead;
            $context['lead'] = [
                'title' => $lead->title ?? '',
                'description' => $lead->description ?? '',
                'value' => $lead->lead_value ?? 0,
                'stage' => $lead->stage ? $lead->stage->name : '',
            ];

            // Get recent activities for the lead
            $activities = DB::table('activities')
                ->leftJoin('lead_activities', 'activities.id', '=', 'lead_activities.activity_id')
                ->where('lead_activities.lead_id', $lead->id)
                ->select('activities.*')
                ->orderBy('activities.created_at', 'desc')
                ->limit(10)
                ->get();

            $context['recent_activities'] = $activities->map(function ($activity) {
                return [
                    'type' => $activity->type ?? '',
                    'title' => $activity->title ?? '',
                    'comment' => $activity->comment ?? '',
                    'date' => $activity->created_at ? date('Y-m-d H:i:s', strtotime($activity->created_at)) : '',
                ];
            })->toArray();
        }

        // Get person context
        if ($email->person_id && $email->person) {
            $person = $email->person;
            $personEmails = is_array($person->emails) ? $person->emails : [];
            $context['person'] = [
                'name' => $person->name ?? '',
                'job_title' => $person->job_title ?? '',
                'email' => !empty($personEmails) && isset($personEmails[0]['value']) ? $personEmails[0]['value'] : '',
                'organization' => $person->organization ? $person->organization->name : '',
            ];
        } elseif ($email->lead && $email->lead->person) {
            $person = $email->lead->person;
            $personEmails = is_array($person->emails) ? $person->emails : [];
            $context['person'] = [
                'name' => $person->name ?? '',
                'job_title' => $person->job_title ?? '',
                'email' => !empty($personEmails) && isset($personEmails[0]['value']) ? $personEmails[0]['value'] : '',
                'organization' => $person->organization ? $person->organization->name : '',
            ];
        }

        return $context;
    }

    /**
     * Get email thread (all emails in conversation).
     *
     * @param  \Webkul\Email\Contracts\Email  $email
     * @return \Illuminate\Support\Collection
     */
    protected function getEmailThread($email)
    {
        $rootEmail = $email->parent_id && $email->parent ? $email->parent : $email;

        // Get all emails in the thread: root email + all replies
        $threadEmails = $this->emailRepository
            ->where(function ($query) use ($rootEmail) {
                $query->where('id', $rootEmail->id)
                    ->orWhere('parent_id', $rootEmail->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return $threadEmails;
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
     * Build prompt for AI reply generation.
     *
     * @param  \Webkul\Email\Contracts\Email  $email
     * @param  array  $context
     * @param  string  $tone
     * @param  string  $length
     * @return string
     */
    protected function buildReplyPrompt($email, array $context, string $tone, string $length): string
    {
        $toneDescription = match($tone) {
            'casual' => 'casual and friendly',
            'formal' => 'formal and professional',
            default => 'professional and courteous',
        };

        $lengthDescription = match($length) {
            'short' => 'brief and concise (2-3 sentences)',
            'long' => 'detailed and comprehensive',
            default => 'moderate length (1-2 paragraphs)',
        };

        $prompt = "You are an AI assistant helping a sales professional write email replies. ";
        $prompt .= "Generate a {$toneDescription} email reply that is {$lengthDescription}. ";
        $prompt .= "The reply should be personalized based on the CRM context provided.\n\n";

        $prompt .= "=== EMAIL TO REPLY TO ===\n";
        $prompt .= "Subject: {$context['email_subject']}\n";
        $prompt .= "From: {$context['sender']}\n";
        $prompt .= "Body:\n{$context['email_body']}\n\n";

        if (!empty($context['email_thread'])) {
            $prompt .= "=== EMAIL THREAD HISTORY ===\n";
            foreach (array_slice($context['email_thread'], -5) as $threadEmail) {
                $prompt .= "Date: {$threadEmail['date']}\n";
                $prompt .= "From: {$threadEmail['from']}\n";
                $prompt .= "Subject: {$threadEmail['subject']}\n";
                $prompt .= "Body: " . substr($threadEmail['body'], 0, 500) . "...\n\n";
            }
        }

        if (!empty($context['lead'])) {
            $prompt .= "=== LEAD CONTEXT ===\n";
            $prompt .= "Lead Title: {$context['lead']['title']}\n";
            $prompt .= "Lead Value: {$context['lead']['value']}\n";
            $prompt .= "Current Stage: {$context['lead']['stage']}\n";
            if (!empty($context['lead']['description'])) {
                $prompt .= "Description: " . substr($context['lead']['description'], 0, 300) . "...\n";
            }
            $prompt .= "\n";
        }

        if (!empty($context['person'])) {
            $prompt .= "=== CONTACT INFORMATION ===\n";
            $prompt .= "Name: {$context['person']['name']}\n";
            if (!empty($context['person']['job_title'])) {
                $prompt .= "Job Title: {$context['person']['job_title']}\n";
            }
            if (!empty($context['person']['organization'])) {
                $prompt .= "Organization: {$context['person']['organization']}\n";
            }
            $prompt .= "\n";
        }

        if (!empty($context['recent_activities'])) {
            $prompt .= "=== RECENT ACTIVITIES ===\n";
            foreach (array_slice($context['recent_activities'], 0, 5) as $activity) {
                $prompt .= "- {$activity['type']}: {$activity['title']} ({$activity['date']})\n";
                if (!empty($activity['comment'])) {
                    $prompt .= "  " . substr($activity['comment'], 0, 200) . "...\n";
                }
            }
            $prompt .= "\n";
        }

        $prompt .= "=== INSTRUCTIONS ===\n";
        $prompt .= "Write a {$toneDescription} email reply that:\n";
        $prompt .= "1. Addresses the sender's message appropriately\n";
        $prompt .= "2. Uses the CRM context to personalize the response\n";
        $prompt .= "3. Maintains a {$toneDescription} tone\n";
        $prompt .= "4. Is {$lengthDescription}\n";
        $prompt .= "5. Does NOT include email headers (To, From, Subject)\n";
        $prompt .= "6. Is ready to send (no placeholders)\n\n";
        $prompt .= "Reply content only (no subject line, no greetings/signatures unless contextually appropriate):\n";

        return $prompt;
    }

    /**
     * Call AI API to generate reply.
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
                'max_tokens' => 1000,
            ]);

            if ($response->failed()) {
                throw new Exception($response->body());
            }

            $data = $response->json();

            if (isset($data['error'])) {
                throw new Exception($data['error']['message'] ?? 'AI service error');
            }

            $reply = $data['choices'][0]['message']['content'] ?? '';

            return [
                'reply' => trim($reply),
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

