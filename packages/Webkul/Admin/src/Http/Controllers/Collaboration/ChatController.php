<?php

namespace Webkul\Admin\Http\Controllers\Collaboration;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Collaboration\Services\ChatService;
use Webkul\Collaboration\Services\MentionService;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService,
        protected MentionService $mentionService
    ) {}

    public function sendMessage(): JsonResponse
    {
        $this->validate(request(), [
            'channel_id' => 'required|exists:chat_channels,id',
            'content' => 'required|string',
            'reply_to_id' => 'nullable|exists:chat_messages,id',
        ]);

        $message = $this->chatService->sendMessage(
            request('channel_id'),
            request('content'),
            request('reply_to_id')
        );

        // Parse mentions
        $this->mentionService->parseMentions(
            request('content'),
            $message->id,
            request('channel_id')
        );

        return response()->json([
            'data' => $message->load(['user', 'replyTo']),
        ]);
    }

    public function getMessages(int $channelId): JsonResponse
    {
        $messages = $this->chatService->getChannelMessages($channelId);

        return response()->json([
            'data' => $messages,
        ]);
    }
}

