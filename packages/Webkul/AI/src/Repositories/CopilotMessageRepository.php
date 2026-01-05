<?php

namespace Webkul\AI\Repositories;

use Illuminate\Container\Container;
use Webkul\AI\Contracts\CopilotMessage;
use Webkul\Core\Eloquent\Repository;

class CopilotMessageRepository extends Repository
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function model()
    {
        return CopilotMessage::class;
    }
}

