<?php

namespace Webkul\Marketing\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Marketing\Contracts\EmailTemplate as EmailTemplateContract;

class EmailTemplateRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return EmailTemplateContract::class;
    }
}

