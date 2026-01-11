<?php

namespace Webkul\Lead\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Webkul\Lead\Repositories\SalesForecastRepository;
use Webkul\Lead\Repositories\DealScoreRepository;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Services\ForecastCalculationService;
use Webkul\Lead\Services\DealVelocityService;
use Webkul\Lead\Services\HistoricalAnalysisService;
use Webkul\Lead\Services\ForecastAccuracyService;
use Webkul\Lead\Services\DealScoringService;
use Webkul\Lead\Services\WinProbabilityService;

class LeadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerModelContracts();
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/forecasting.php', 'forecasting');
    }

    /**
     * Register model contracts.
     *
     * @return void
     */
    protected function registerModelContracts()
    {
        $this->app->bind(
            \Webkul\Lead\Contracts\SalesForecast::class,
            \Webkul\Lead\Models\SalesForecast::class
        );

        $this->app->bind(
            \Webkul\Lead\Contracts\ForecastActual::class,
            \Webkul\Lead\Models\ForecastActual::class
        );

        $this->app->bind(
            \Webkul\Lead\Contracts\DealScore::class,
            \Webkul\Lead\Models\DealScore::class
        );

        $this->app->bind(
            \Webkul\Lead\Contracts\HistoricalConversion::class,
            \Webkul\Lead\Models\HistoricalConversion::class
        );
    }

    /**
     * Register repositories.
     *
     * @return void
     */
    protected function registerRepositories()
    {
        $this->app->singleton(SalesForecastRepository::class);
        $this->app->singleton(DealScoreRepository::class);
        $this->app->singleton(HistoricalConversionRepository::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    protected function registerServices()
    {
        $this->app->singleton(ForecastCalculationService::class);
        $this->app->singleton(DealVelocityService::class);
        $this->app->singleton(HistoricalAnalysisService::class);
        $this->app->singleton(ForecastAccuracyService::class);
        $this->app->singleton(DealScoringService::class);
        $this->app->singleton(WinProbabilityService::class);
    }
}
