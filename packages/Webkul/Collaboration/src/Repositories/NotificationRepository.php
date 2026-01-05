<?php

namespace Webkul\Collaboration\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Collaboration\Contracts\Notification as NotificationContract;

class NotificationRepository extends Repository
{
    public function model(): string
    {
        return NotificationContract::class;
    }
}

