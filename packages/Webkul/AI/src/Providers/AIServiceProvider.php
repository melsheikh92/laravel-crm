<?php

namespace Webkul\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\AI\Repositories\CopilotConversationRepository;
use Webkul\AI\Repositories\CopilotMessageRepository;
use Webkul\AI\Repositories\InsightRepository;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        
        $this->app->bind(
            \Webkul\AI\Contracts\AIInsight::class,
            \Webkul\AI\Models\AIInsight::class
        );
        
        $this->app->bind(
            \Webkul\AI\Contracts\CopilotConversation::class,
            \Webkul\AI\Models\CopilotConversation::class
        );
        
        $this->app->bind(
            \Webkul\AI\Contracts\CopilotMessage::class,
            \Webkul\AI\Models\CopilotMessage::class
        );

        $this->app->singleton(InsightRepository::class);
        $this->app->singleton(CopilotConversationRepository::class);
        $this->app->singleton(CopilotMessageRepository::class);
    }
    
    protected function registerConfig()
    {
        // Register config if needed
    }
}

