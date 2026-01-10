<?php

namespace Webkul\Portal\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class PortalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'portal');

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/portal'),
        ], 'public');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/core_config.php',
            'core_config'
        );
    }
}
