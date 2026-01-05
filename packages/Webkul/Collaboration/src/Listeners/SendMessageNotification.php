<?php

namespace Webkul\Collaboration\Listeners;

use Webkul\Collaboration\Events\MessageSent;
use Webkul\Collaboration\Services\NotificationService;

class SendMessageNotification
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(MessageSent $event): void
    {
        // Create notifications for channel members (except sender)
        $channel = $event->message->channel;
        foreach ($channel->members as $member) {
            if ($member->user_id !== $event->message->user_id) {
                $this->notificationService->create([
                    'user_id' => $member->user_id,
                    'type' => 'message',
                    'title' => 'New message in ' . $channel->name,
                    'message' => $event->message->user->name . ': ' . substr($event->message->content, 0, 100),
                    'data' => [
                        'channel_id' => $channel->id,
                        'message_id' => $event->message->id,
                    ],
                ]);
            }
        }
    }
}

