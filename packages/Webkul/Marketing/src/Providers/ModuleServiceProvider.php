<?php

namespace Webkul\Marketing\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Define the module's array.
     *
     * @var array
     */
    protected $models = [
        \Webkul\Marketing\Models\Event::class,
        \Webkul\Marketing\Models\Campaign::class,
        \Webkul\Marketing\Models\EmailCampaign::class,
        \Webkul\Marketing\Models\CampaignRecipient::class,
        \Webkul\Marketing\Models\EmailTemplate::class,
    ];
}
