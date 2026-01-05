<?php

namespace Webkul\Installer\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\Installer\Console\Commands\Installer as InstallerCommand;
use Webkul\Installer\Http\Middleware\CanInstall;
use Webkul\Installer\Http\Middleware\Locale;

class InstallerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     */
    protected bool $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot(Router $router): void
    {
        $router->middlewareGroup('install', [CanInstall::class]);

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'installer');

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'installer');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'installer');

        $router->aliasMiddleware('installer_locale', Locale::class);

        Event::listen('krayin.installed', 'Webkul\Installer\Listeners\Installer@installed');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'installer');

        /**
         * Disable Debugbar for installer API routes to prevent HTML injection in JSON responses
         */
        if (class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
            $this->app['config']->set('debugbar.enabled', false);
        }

        /**
         * Route to access template applied image file
         */
        $this->app['router']->get('cache/{filename}', [
            'uses' => 'Webkul\Installer\Http\Controllers\ImageCacheController@getImage',
            'as'   => 'image_cache',
        ])->where(['filename' => '[ \w\\.\\/\\-\\@\(\)\=]+']);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([InstallerCommand::class]);
    }
}
