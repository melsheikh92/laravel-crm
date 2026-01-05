<?php

namespace Webkul\Admin\Http\Controllers\AI;

use Exception;
use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\AI\Services\CopilotService;

class CopilotController extends Controller
{
    public function __construct(
        protected CopilotService $copilotService
    ) {}

    public function sendMessage(): JsonResponse
    {
        $this->validate(request(), [
            'message' => 'required|string',
            'conversation_id' => 'nullable|integer|exists:copilot_conversations,id',
        ]);

        try {
            $userId = auth()->guard('user')->id();
            $result = $this->copilotService->processMessage(
                $userId,
                request('message'),
                request('conversation_id')
            );

            if (isset($result['error'])) {
                return response()->json([
                    'message' => $result['error'],
                ], 400);
            }

            return response()->json([
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getConversations(): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();
            $conversations = $this->copilotService->getUserConversations($userId);

            return response()->json([
                'data' => $conversations,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getMessages($conversationId): JsonResponse
    {
        try {
            $messages = $this->copilotService->getConversationMessages($conversationId);

            return response()->json([
                'data' => $messages,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

