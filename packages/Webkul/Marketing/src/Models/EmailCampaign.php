<?php

namespace Webkul\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Marketing\Contracts\EmailCampaign as EmailCampaignContract;
use Webkul\User\Models\UserProxy;
use Webkul\Marketing\Models\EmailTemplateProxy;
use Webkul\Marketing\Models\CampaignRecipientProxy;

class EmailCampaign extends Model implements EmailCampaignContract
{
    protected $table = 'email_campaigns';

    protected $fillable = [
        'name',
        'subject',
        'content',
        'template_id',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'sent_count',
        'failed_count',
        'sender_name',
        'sender_email',
        'reply_to',
        'user_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    /**
     * Get the user that created the campaign.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Get the email template.
     */
    public function template()
    {
        return $this->belongsTo(EmailTemplateProxy::modelClass(), 'template_id');
    }

    /**
     * Get the campaign recipients.
     */
    public function recipients()
    {
        return $this->hasMany(CampaignRecipientProxy::modelClass(), 'campaign_id');
    }
}

