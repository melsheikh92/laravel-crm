<?php

namespace Webkul\Collaboration\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\Collaboration\Contracts\UserMention;

class UserMentioned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public UserMention $mention)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('user.' . $this->mention->user_id);
    }

    public function broadcastAs(): string
    {
        return 'user.mentioned';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->mention->id,
            'message_id' => $this->mention->message_id,
            'channel_id' => $this->mention->channel_id,
        ];
    }
}

