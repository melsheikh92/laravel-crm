<?php

namespace Webkul\Collaboration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Collaboration\Contracts\ChatChannel as ChatChannelContract;
use Webkul\User\Models\UserProxy;
use Webkul\Collaboration\Models\ChatMessageProxy;
use Webkul\Collaboration\Models\ChatChannelMemberProxy;

class ChatChannel extends Model implements ChatChannelContract
{
    protected $table = 'chat_channels';

    protected $fillable = [
        'name',
        'type',
        'description',
        'created_by',
    ];

    /**
     * Get the user that created the channel.
     */
    public function creator()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'created_by');
    }

    /**
     * Get the channel members.
     */
    public function members()
    {
        return $this->hasMany(ChatChannelMemberProxy::modelClass(), 'channel_id');
    }

    /**
     * Get the messages.
     */
    public function messages()
    {
        return $this->hasMany(ChatMessageProxy::modelClass(), 'channel_id');
    }
}

