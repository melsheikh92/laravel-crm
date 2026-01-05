<?php

namespace App\Repositories;

use Webkul\Core\Eloquent\Repository;

class WhatsAppTemplateRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Contracts\WhatsAppTemplate';
    }
}
