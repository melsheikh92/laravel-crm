<?php

namespace Webkul\Marketplace\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Marketplace\Console\Commands\CheckExtensionUpdates;
use Webkul\Marketplace\Helpers\Dashboard as MarketplaceDashboard;
use Webkul\Marketplace\Http\Middleware\DeveloperMiddleware;

class MarketplaceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        // Register middleware
        $router->aliasMiddleware('developer', DeveloperMiddleware::class);

        Route::middleware(['web', 'admin_locale', 'user'])
            ->prefix(config('app.admin_path'))
            ->group(__DIR__ . '/../Routes/admin-routes.php');

        Route::middleware(['web', 'user', 'developer'])
            ->group(__DIR__ . '/../Routes/developer-routes.php');

        Route::middleware(['web'])
            ->group(__DIR__ . '/../Routes/marketplace-routes.php');

        Route::middleware(['api'])
            ->prefix('api')
            ->group(__DIR__ . '/../Routes/api-routes.php');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'marketplace');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'marketplace');

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/marketplace'),
        ], 'marketplace-views');

        $this->publishes([
            __DIR__ . '/../Config/menu.php' => config_path('marketplace/menu.php'),
            __DIR__ . '/../Config/acl.php' => config_path('marketplace/acl.php'),
        ], 'marketplace-config');

        $this->publishes([
            __DIR__ . '/../Resources/lang' => resource_path('lang/vendor/marketplace'),
        ], 'marketplace-lang');

        $this->app->register(ModuleServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('marketplace:check-updates --notify')->daily();
        });

        // COMMENTED OUT - Dashboard widgets showing incorrect localization keys
        // $this->registerDashboardWidgets();
    }

    /**
     * Register dashboard widgets - COMMENTED OUT (showing incorrect localization).
     *
     * @return void
     */
    // protected function registerDashboardWidgets()
    // {
    //     // Admin dashboard widgets
    //     Event::listen('admin.dashboard.index.content.left.after', function ($viewRenderEventManager) {
    //         $marketplaceDashboard = app(MarketplaceDashboard::class);
    //
    //         $viewRenderEventManager->addTemplate(
    //             view('marketplace::admin.dashboard.marketplace-stats', [
    //                 'marketplaceStats' => $marketplaceDashboard->getMarketplaceStats(),
    //             ])->render()
    //         );
    //     });
    //
    //     Event::listen('admin.dashboard.index.content.right.after', function ($viewRenderEventManager) {
    //         $marketplaceDashboard = app(MarketplaceDashboard::class);
    //
    //         $viewRenderEventManager->addTemplate(
    //             view('marketplace::admin.dashboard.popular-extensions', [
    //                 'popularExtensions' => $marketplaceDashboard->getPopularExtensions(5),
    //             ])->render()
    //         );
    //
    //         $viewRenderEventManager->addTemplate(
    //             view('marketplace::admin.dashboard.recent-installations', [
    //                 'recentInstallations' => $marketplaceDashboard->getRecentInstallations(5),
    //             ])->render()
    //         );
    //     });
    //
    //     // Portal dashboard widgets
    //     Event::listen('portal.dashboard.content.after', function ($viewRenderEventManager) {
    //         if (!auth()->guard('portal')->check()) {
    //             return;
    //         }
    //
    //         $user = auth()->guard('portal')->user();
    //         $marketplaceDashboard = app(MarketplaceDashboard::class);
    //
    //         $viewRenderEventManager->addTemplate(
    //             view('marketplace::portal.dashboard.installed-extensions', [
    //                 'installedExtensions' => $marketplaceDashboard->getUserInstalledExtensions($user->id, 5),
    //             ])->render()
    //         );
    //
    //         $viewRenderEventManager->addTemplate(
    //             view('marketplace::portal.dashboard.pending-updates', [
    //                 'pendingUpdates' => $marketplaceDashboard->getPendingUpdates($user->id),
    //             ])->render()
    //         );
    //     });
    // }


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->registerCommands();
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/Config/acl.php', 'acl');

        $this->mergeConfigFrom(dirname(__DIR__) . '/Config/menu.php', 'menu.admin');
    }

    /**
     * Register the commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckExtensionUpdates::class,
            ]);
        }
    }
}
