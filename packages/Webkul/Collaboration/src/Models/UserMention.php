<?php

namespace Webkul\Collaboration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Collaboration\Contracts\UserMention as UserMentionContract;
use Webkul\Collaboration\Models\ChatMessageProxy;
use Webkul\User\Models\UserProxy;
use Webkul\Collaboration\Models\ChatChannelProxy;

class UserMention extends Model implements UserMentionContract
{
    protected $table = 'user_mentions';

    protected $fillable = [
        'message_id',
        'user_id',
        'channel_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the message.
     */
    public function message()
    {
        return $this->belongsTo(ChatMessageProxy::modelClass(), 'message_id');
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }

    /**
     * Get the channel.
     */
    public function channel()
    {
        return $this->belongsTo(ChatChannelProxy::modelClass(), 'channel_id');
    }
}

