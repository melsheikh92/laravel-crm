<?php

namespace Webkul\Territory\Providers;

use Illuminate\Support\Facades\Event;
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

        // Register event listeners
        $this->registerEventListeners();
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Listen to Lead created event and auto-assign territory
        Event::listen('lead.create.after', function ($lead) {
            app(\Webkul\Territory\Listeners\AssignTerritoryToLead::class)->handle($lead);
        });

        // Listen to Organization created event and auto-assign territory
        Event::listen('contacts.organization.create.after', function ($organization) {
            app(\Webkul\Territory\Listeners\AssignTerritoryToOrganization::class)->handle($organization);
        });

        // Listen to Person created event and auto-assign territory
        Event::listen('contacts.person.create.after', function ($person) {
            app(\Webkul\Territory\Listeners\AssignTerritoryToPerson::class)->handle($person);
        });
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
