<?php

namespace Webkul\Marketplace\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Marketplace\Models\Extension::class,
        \Webkul\Marketplace\Models\ExtensionVersion::class,
        \Webkul\Marketplace\Models\ExtensionReview::class,
        \Webkul\Marketplace\Models\ExtensionInstallation::class,
        \Webkul\Marketplace\Models\ExtensionCategory::class,
        \Webkul\Marketplace\Models\ExtensionTransaction::class,
        \Webkul\Marketplace\Models\ExtensionSubmission::class,
    ];
}
