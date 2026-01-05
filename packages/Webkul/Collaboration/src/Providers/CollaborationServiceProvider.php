<?php

namespace Webkul\Collaboration\Providers;

use Illuminate\Support\ServiceProvider;

class CollaborationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        $this->app->register(ModuleServiceProvider::class);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind contracts to models
        $this->app->bind(
            \Webkul\Collaboration\Contracts\ChatChannel::class,
            \Webkul\Collaboration\Models\ChatChannel::class
        );

        $this->app->bind(
            \Webkul\Collaboration\Contracts\ChatMessage::class,
            \Webkul\Collaboration\Models\ChatMessage::class
        );

        $this->app->bind(
            \Webkul\Collaboration\Contracts\ChatChannelMember::class,
            \Webkul\Collaboration\Models\ChatChannelMember::class
        );

        $this->app->bind(
            \Webkul\Collaboration\Contracts\Notification::class,
            \Webkul\Collaboration\Models\Notification::class
        );

        $this->app->bind(
            \Webkul\Collaboration\Contracts\UserMention::class,
            \Webkul\Collaboration\Models\UserMention::class
        );
    }
}

