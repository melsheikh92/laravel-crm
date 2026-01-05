<?php

namespace Webkul\Support\Providers;

use Illuminate\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
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

