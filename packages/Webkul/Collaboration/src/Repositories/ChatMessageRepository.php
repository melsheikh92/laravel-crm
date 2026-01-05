<?php

namespace Webkul\Collaboration\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Collaboration\Contracts\ChatMessage as ChatMessageContract;

class ChatMessageRepository extends Repository
{
    public function model(): string
    {
        return ChatMessageContract::class;
    }
}

