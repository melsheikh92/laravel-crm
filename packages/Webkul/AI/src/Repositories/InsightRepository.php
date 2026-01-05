<?php

namespace Webkul\AI\Repositories;

use Illuminate\Container\Container;
use Webkul\AI\Contracts\AIInsight;
use Webkul\Core\Eloquent\Repository;

class InsightRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return AIInsight::class;
    }
}

