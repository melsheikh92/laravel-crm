<?php

namespace Webkul\Collaboration\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Collaboration\Contracts\UserMention as UserMentionContract;

class UserMentionRepository extends Repository
{
    public function model(): string
    {
        return UserMentionContract::class;
    }
}

