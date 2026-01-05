<?php

namespace Webkul\Marketing\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Marketing\Contracts\CampaignRecipient as CampaignRecipientContract;

class CampaignRecipientRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return CampaignRecipientContract::class;
    }
}

