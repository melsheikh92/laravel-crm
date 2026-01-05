<?php

namespace Webkul\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Marketing\Contracts\EmailTemplate as EmailTemplateContract;
use Webkul\User\Models\UserProxy;
use Webkul\Marketing\Models\EmailCampaignProxy;

class EmailTemplate extends Model implements EmailTemplateContract
{
    protected $table = 'email_templates';

    protected $fillable = [
        'name',
        'subject',
        'content',
        'type',
        'variables',
        'thumbnail',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that created the template.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }

    /**
     * Get the campaigns using this template.
     */
    public function campaigns()
    {
        return $this->hasMany(EmailCampaignProxy::modelClass(), 'template_id');
    }
}

