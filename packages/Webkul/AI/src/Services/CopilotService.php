<?php

namespace Webkul\AI\Services;

use Exception;
use Webkul\AI\Repositories\CopilotConversationRepository;
use Webkul\AI\Repositories\CopilotMessageRepository;

class CopilotService
{
    /**
     * API endpoint for OpenRouter AI service.
     */
    const OPEN_ROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CopilotConversationRepository $conversationRepository,
        protected CopilotMessageRepository $messageRepository
    ) {}

    /**
     * Process user message and generate response.
     *
     * @param  int  $userId
     * @param  string  $message
     * @param  int|null  $conversationId
     * @return array
     */
    public function processMessage(int $userId, string $message, ?int $conversationId = null): array
    {
        // Get or create conversation
        if ($conversationId) {
            $conversation = $this->conversationRepository->findOrFail($conversationId);
        } else {
            $conversation = $this->conversationRepository->create([
                'user_id' => $userId,
                'title' => substr($message, 0, 50),
            ]);
        }

        // Save user message
        $userMessage = $this->messageRepository->create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $message,
        ]);

        // Get conversation history
        $history = $this->getConversationHistory($conversation->id);

        // Generate AI response
        $response = $this->generateResponse($history, $message);

        // Save AI message
        if (!isset($response['error'])) {
            $aiMessage = $this->messageRepository->create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $response['content'],
            ]);

            return [
                'conversation_id' => $conversation->id,
                'message' => $response['content'],
            ];
        }

        return $response;
    }

    /**
     * Get conversation history.
     */
    protected function getConversationHistory(int $conversationId): array
    {
        $messages = $this->messageRepository
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();

        return $messages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
            ];
        })->toArray();
    }

    /**
     * Generate AI response.
     */
    protected function generateResponse(array $history, string $userMessage): array
    {
        $model = core()->getConfigData('general.magic_ai.settings.other_model') 
            ?: core()->getConfigData('general.magic_ai.settings.model');

        $apiKey = core()->getConfigData('general.magic_ai.settings.api_key');

        if (!$apiKey || !$model) {
            return ['error' => trans('admin::app.leads.file.missing-api-key')];
        }

        $messages = array_merge($history, [
            ['role' => 'user', 'content' => $userMessage],
        ]);

        try {
            $response = \Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post(self::OPEN_ROUTER_URL, [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 1000,
            ]);

            if ($response->failed()) {
                throw new Exception($response->body());
            }

            $data = $response->json();

            if (isset($data['error'])) {
                throw new Exception($data['error']['message'] ?? 'AI service error');
            }

            return [
                'content' => $data['choices'][0]['message']['content'] ?? '',
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get user conversations.
     */
    public function getUserConversations(int $userId)
    {
        return $this->conversationRepository
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get conversation messages.
     */
    public function getConversationMessages(int $conversationId)
    {
        return $this->messageRepository
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
}

