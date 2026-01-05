<?php

namespace Webkul\Collaboration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Collaboration\Contracts\ChatMessage as ChatMessageContract;
use Webkul\Collaboration\Models\ChatChannelProxy;
use Webkul\User\Models\UserProxy;
use Webkul\Collaboration\Models\ChatMessageProxy;
use Webkul\Collaboration\Models\UserMentionProxy;

class ChatMessage extends Model implements ChatMessageContract
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'channel_id',
        'user_id',
        'content',
        'type',
        'reply_to_id',
        'attachments',
        'is_edited',
        'edited_at',
        'is_deleted',
        'deleted_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_edited' => 'boolean',
        'is_deleted' => 'boolean',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the channel.
     */
    public function channel()
    {
        return $this->belongsTo(ChatChannelProxy::modelClass(), 'channel_id');
    }

    /**
     * Get the user that sent the message.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }

    /**
     * Get the message this is replying to.
     */
    public function replyTo()
    {
        return $this->belongsTo(ChatMessageProxy::modelClass(), 'reply_to_id');
    }

    /**
     * Get the mentions.
     */
    public function mentions()
    {
        return $this->hasMany(UserMentionProxy::modelClass(), 'message_id');
    }
}

