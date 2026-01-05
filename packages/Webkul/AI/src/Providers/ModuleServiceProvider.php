<?php

namespace Webkul\AI\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\AI\Models\AIInsight::class,
        \Webkul\AI\Models\CopilotConversation::class,
        \Webkul\AI\Models\CopilotMessage::class,
    ];
}

