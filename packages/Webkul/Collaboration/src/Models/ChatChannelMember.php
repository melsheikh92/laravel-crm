<?php

namespace Webkul\Collaboration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Collaboration\Contracts\ChatChannelMember as ChatChannelMemberContract;
use Webkul\Collaboration\Models\ChatChannelProxy;
use Webkul\User\Models\UserProxy;

class ChatChannelMember extends Model implements ChatChannelMemberContract
{
    protected $table = 'chat_channel_members';

    protected $fillable = [
        'channel_id',
        'user_id',
        'role',
        'joined_at',
        'last_read_at',
        'muted_until',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
        'muted_until' => 'datetime',
    ];

    /**
     * Get the channel.
     */
    public function channel()
    {
        return $this->belongsTo(ChatChannelProxy::modelClass(), 'channel_id');
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }
}

