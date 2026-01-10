<?php

namespace Webkul\Marketing\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Webkul\Marketing\Console\Commands\CampaignCommand;
use Webkul\Marketing\Repositories\EmailCampaignRepository;
use Webkul\Marketing\Repositories\CampaignRecipientRepository;
use Webkul\Marketing\Repositories\EmailTemplateRepository;

class MarketingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('campaign:process')->daily();
        });
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerCommands();

        $this->app->register(ModuleServiceProvider::class);

        // Bind contracts to models
        $this->app->bind(
            \Webkul\Marketing\Contracts\EmailCampaign::class,
            \Webkul\Marketing\Models\EmailCampaign::class
        );

        $this->app->bind(
            \Webkul\Marketing\Contracts\CampaignRecipient::class,
            \Webkul\Marketing\Models\CampaignRecipient::class
        );

        $this->app->bind(
            \Webkul\Marketing\Contracts\EmailTemplate::class,
            \Webkul\Marketing\Models\EmailTemplate::class
        );

        // Register repositories as singletons
        $this->app->singleton(EmailCampaignRepository::class);
        $this->app->singleton(CampaignRecipientRepository::class);
        $this->app->singleton(EmailTemplateRepository::class);
    }

    /**
     * Register the commands.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CampaignCommand::class,
            ]);
        }
    }
}
