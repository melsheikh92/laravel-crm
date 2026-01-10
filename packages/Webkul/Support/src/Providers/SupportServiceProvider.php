<?php

namespace Webkul\Support\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SupportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Register module service provider
        $this->app->register(ModuleServiceProvider::class);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(
            \Webkul\Support\Repositories\SupportTicketRepository::class
        );

        $this->app->bind(
            \Webkul\Support\Repositories\TicketCategoryRepository::class
        );

        $this->app->bind(
            \Webkul\Support\Repositories\SlaPolicyRepository::class
        );

        $this->app->bind(
            \Webkul\Support\Repositories\KbCategoryRepository::class
        );

        $this->app->bind(
            \Webkul\Support\Repositories\KbArticleRepository::class
        );

        // Register services
        $this->app->singleton(
            \Webkul\Support\Services\TicketService::class
        );

        $this->app->singleton(
            \Webkul\Support\Services\SlaService::class
        );

        $this->app->singleton(
            \Webkul\Support\Services\KnowledgeBaseService::class
        );
    }
}
