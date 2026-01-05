<?php

namespace Webkul\Integration\Providers;

use Illuminate\Support\ServiceProvider;

class IntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->app->register(ModuleServiceProvider::class);
    }

    public function register(): void
    {
        //
    }
}

