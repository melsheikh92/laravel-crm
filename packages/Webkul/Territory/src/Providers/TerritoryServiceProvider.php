<?php

namespace Webkul\Territory\Providers;

use Illuminate\Support\ServiceProvider;

class TerritoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Register module service provider
        // TODO: Uncomment when models are created in phase 2
        // $this->app->register(ModuleServiceProvider::class);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(
            \Webkul\Territory\Repositories\TerritoryRepository::class
        );

        $this->app->bind(
            \Webkul\Territory\Repositories\TerritoryRuleRepository::class
        );

        $this->app->bind(
            \Webkul\Territory\Repositories\TerritoryAssignmentRepository::class
        );

        // Register services
        $this->app->singleton(
            \Webkul\Territory\Services\TerritoryAssignmentService::class
        );

        $this->app->singleton(
            \Webkul\Territory\Services\TerritoryRuleEvaluator::class
        );

        $this->app->singleton(
            \Webkul\Territory\Services\TerritoryAnalyticsService::class
        );
    }
}
