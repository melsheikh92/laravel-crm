<?php

use Illuminate\Support\Facades\Route;
use Webkul\Marketplace\Http\Controllers\Api\ExtensionApiController;
use Webkul\Marketplace\Http\Controllers\Api\InstallationApiController;
use Webkul\Marketplace\Http\Controllers\Api\ReviewApiController;
use Webkul\Marketplace\Http\Controllers\Api\VersionApiController;

/*
|--------------------------------------------------------------------------
| Marketplace API Routes
|--------------------------------------------------------------------------
|
| RESTful API routes for marketplace extensions, versions, reviews, and
| installations. These routes are designed for external integrations,
| CLI tools, and headless implementations.
|
*/

Route::prefix('marketplace')->group(function () {
    /**
     * Public API routes - no authentication required.
     */
    Route::controller(ExtensionApiController::class)->prefix('extensions')->group(function () {
        Route::get('/', 'index')->name('api.marketplace.extensions.index');

        Route::get('/{id}', 'show')->name('api.marketplace.extensions.show');

        Route::get('/slug/{slug}', 'showBySlug')->name('api.marketplace.extensions.show_by_slug');

        Route::get('/{id}/versions', 'versions')->name('api.marketplace.extensions.versions');

        Route::get('/{id}/reviews', 'reviews')->name('api.marketplace.extensions.reviews');

        Route::get('/{id}/stats', 'stats')->name('api.marketplace.extensions.stats');

        Route::get('/search', 'search')->name('api.marketplace.extensions.search');

        Route::get('/featured', 'featured')->name('api.marketplace.extensions.featured');

        Route::get('/popular', 'popular')->name('api.marketplace.extensions.popular');
    });

    /**
     * Version API routes - public access.
     */
    Route::controller(VersionApiController::class)->prefix('versions')->group(function () {
        Route::get('/{id}', 'show')->name('api.marketplace.versions.show');

        Route::get('/{id}/compatibility', 'checkCompatibility')->name('api.marketplace.versions.compatibility');

        Route::get('/{id}/changelog', 'changelog')->name('api.marketplace.versions.changelog');
    });

    /**
     * Authenticated API routes - require API token or session auth.
     */
    Route::middleware(['auth:sanctum,user'])->group(function () {
        /**
         * Extension management API routes.
         */
        Route::controller(ExtensionApiController::class)->prefix('extensions')->group(function () {
            Route::post('/', 'store')->name('api.marketplace.extensions.store');

            Route::put('/{id}', 'update')->name('api.marketplace.extensions.update');

            Route::delete('/{id}', 'destroy')->name('api.marketplace.extensions.destroy');
        });

        /**
         * Version management API routes.
         */
        Route::controller(VersionApiController::class)->prefix('versions')->group(function () {
            Route::post('/extension/{extension_id}', 'store')->name('api.marketplace.versions.store');

            Route::put('/{id}', 'update')->name('api.marketplace.versions.update');

            Route::delete('/{id}', 'destroy')->name('api.marketplace.versions.destroy');
        });

        /**
         * Installation API routes.
         */
        Route::controller(InstallationApiController::class)->prefix('installations')->group(function () {
            Route::get('/', 'index')->name('api.marketplace.installations.index');

            Route::get('/{id}', 'show')->name('api.marketplace.installations.show');

            Route::post('/', 'install')->name('api.marketplace.installations.install');

            Route::put('/{id}', 'update')->name('api.marketplace.installations.update');

            Route::delete('/{id}', 'uninstall')->name('api.marketplace.installations.uninstall');

            Route::post('/{id}/enable', 'enable')->name('api.marketplace.installations.enable');

            Route::post('/{id}/disable', 'disable')->name('api.marketplace.installations.disable');

            Route::post('/{id}/toggle-auto-update', 'toggleAutoUpdate')->name('api.marketplace.installations.toggle_auto_update');

            Route::get('/check-updates', 'checkUpdates')->name('api.marketplace.installations.check_updates');

            Route::get('/updates-available', 'availableUpdates')->name('api.marketplace.installations.updates_available');
        });

        /**
         * Review API routes.
         */
        Route::controller(ReviewApiController::class)->prefix('reviews')->group(function () {
            Route::get('/my-reviews', 'myReviews')->name('api.marketplace.reviews.my_reviews');

            Route::get('/{id}', 'show')->name('api.marketplace.reviews.show');

            Route::post('/extension/{extension_id}', 'store')->name('api.marketplace.reviews.store');

            Route::put('/{id}', 'update')->name('api.marketplace.reviews.update');

            Route::delete('/{id}', 'destroy')->name('api.marketplace.reviews.destroy');

            Route::post('/{id}/helpful', 'markHelpful')->name('api.marketplace.reviews.helpful');

            Route::post('/{id}/report', 'report')->name('api.marketplace.reviews.report');
        });
    });
});
