<?php

namespace Webkul\Collaboration\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Collaboration\Models\ChatChannel::class,
        \Webkul\Collaboration\Models\ChatMessage::class,
        \Webkul\Collaboration\Models\ChatChannelMember::class,
        \Webkul\Collaboration\Models\Notification::class,
        \Webkul\Collaboration\Models\UserMention::class,
    ];
}

