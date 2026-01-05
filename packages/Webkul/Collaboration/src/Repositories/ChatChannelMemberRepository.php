<?php

namespace Webkul\Collaboration\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Collaboration\Contracts\ChatChannelMember as ChatChannelMemberContract;

class ChatChannelMemberRepository extends Repository
{
    public function model(): string
    {
        return ChatChannelMemberContract::class;
    }
}

