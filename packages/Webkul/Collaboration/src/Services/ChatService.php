<?php

namespace Webkul\Collaboration\Services;

use Webkul\Collaboration\Repositories\ChatChannelRepository;
use Webkul\Collaboration\Repositories\ChatMessageRepository;
use Webkul\Collaboration\Repositories\ChatChannelMemberRepository;
use Webkul\Collaboration\Events\MessageSent;

class ChatService
{
    public function __construct(
        protected ChatChannelRepository $channelRepository,
        protected ChatMessageRepository $messageRepository,
        protected ChatChannelMemberRepository $memberRepository
    ) {
    }

    public function createChannel(array $data): \Webkul\Collaboration\Contracts\ChatChannel
    {
        $data['created_by'] = auth()->guard('user')->id();
        $channel = $this->channelRepository->create($data);

        // Add creator as member
        $this->memberRepository->create([
            'channel_id' => $channel->id,
            'user_id' => $data['created_by'],
            'role' => 'admin',
        ]);

        return $channel;
    }

    public function sendMessage(int $channelId, string $content, ?int $replyToId = null, array $attachments = []): \Webkul\Collaboration\Contracts\ChatMessage
    {
        $message = $this->messageRepository->create([
            'channel_id' => $channelId,
            'user_id' => auth()->guard('user')->id(),
            'content' => $content,
            'type' => 'message',
            'reply_to_id' => $replyToId,
            'attachments' => $attachments,
        ]);

        event(new MessageSent($message));

        return $message;
    }

    public function getChannelMessages(int $channelId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->messageRepository
            ->where('channel_id', $channelId)
            ->where('is_deleted', false)
            ->with(['user', 'replyTo', 'mentions'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();
    }

    public function updateChannel(int $id, array $data): \Webkul\Collaboration\Contracts\ChatChannel
    {
        $channel = $this->channelRepository->findOrFail($id);

        $this->channelRepository->update($data, $id);

        return $channel->fresh();
    }

    public function deleteChannel(int $id): bool
    {
        $channel = $this->channelRepository->findOrFail($id);

        // Delete all channel members
        $this->memberRepository->where('channel_id', $id)->delete();

        // Delete all messages (soft delete)
        $this->messageRepository->where('channel_id', $id)->update(['is_deleted' => true]);

        // Delete the channel
        return $this->channelRepository->delete($id);
    }
}

