<?php

namespace Webkul\Territory\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Territory\Models\Territory::class,
        \Webkul\Territory\Models\TerritoryRule::class,
        \Webkul\Territory\Models\TerritoryAssignment::class,
    ];
}
