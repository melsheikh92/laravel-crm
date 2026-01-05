<?php

namespace Webkul\AI\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\AI\Contracts\CopilotConversation as CopilotConversationContract;
use Webkul\User\Models\UserProxy;

class CopilotConversation extends Model implements CopilotConversationContract
{
    protected $table = 'copilot_conversations';

    protected $fillable = [
        'user_id',
        'title',
    ];

    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    public function messages()
    {
        return $this->hasMany(CopilotMessageProxy::modelClass());
    }
}

