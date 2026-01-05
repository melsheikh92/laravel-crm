<?php

namespace Webkul\Collaboration\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Collaboration\Contracts\ChatChannel as ChatChannelContract;

class ChatChannelRepository extends Repository
{
    public function model(): string
    {
        return ChatChannelContract::class;
    }
}

