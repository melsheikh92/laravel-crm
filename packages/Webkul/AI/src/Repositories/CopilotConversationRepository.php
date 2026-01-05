<?php

namespace Webkul\AI\Repositories;

use Illuminate\Container\Container;
use Webkul\AI\Contracts\CopilotConversation;
use Webkul\Core\Eloquent\Repository;

class CopilotConversationRepository extends Repository
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function model()
    {
        return CopilotConversation::class;
    }
}

