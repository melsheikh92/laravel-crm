<?php

namespace Webkul\AI\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\AI\Contracts\CopilotMessage as CopilotMessageContract;

class CopilotMessage extends Model implements CopilotMessageContract
{
    protected $table = 'copilot_messages';

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
    ];

    public function conversation()
    {
        return $this->belongsTo(CopilotConversationProxy::modelClass());
    }
}

