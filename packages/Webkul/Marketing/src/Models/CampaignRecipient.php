<?php

namespace Webkul\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Marketing\Contracts\CampaignRecipient as CampaignRecipientContract;
use Webkul\Marketing\Models\EmailCampaignProxy;
use Webkul\Contact\Models\PersonProxy;
use Webkul\Lead\Models\LeadProxy;

class CampaignRecipient extends Model implements CampaignRecipientContract
{
    protected $table = 'email_campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'person_id',
        'lead_id',
        'email',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the campaign.
     */
    public function campaign()
    {
        return $this->belongsTo(EmailCampaignProxy::modelClass(), 'campaign_id');
    }

    /**
     * Get the person.
     */
    public function person()
    {
        return $this->belongsTo(PersonProxy::modelClass(), 'person_id');
    }

    /**
     * Get the lead.
     */
    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass(), 'lead_id');
    }
}

