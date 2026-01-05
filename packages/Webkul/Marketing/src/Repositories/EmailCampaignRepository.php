<?php

namespace Webkul\Marketing\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Marketing\Contracts\EmailCampaign as EmailCampaignContract;

class EmailCampaignRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return EmailCampaignContract::class;
    }
}

