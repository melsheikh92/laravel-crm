<?php

use Illuminate\Support\Facades\Route;
use Webkul\Marketplace\Http\Controllers\DeveloperRegistrationController;
use Webkul\Marketplace\Http\Controllers\Marketplace\BrowseController;
use Webkul\Marketplace\Http\Controllers\Marketplace\ExtensionDetailController;
use Webkul\Marketplace\Http\Controllers\Marketplace\InstallationController;
use Webkul\Marketplace\Http\Controllers\Marketplace\MyExtensionsController;
use Webkul\Marketplace\Http\Controllers\Marketplace\PaymentController;
use Webkul\Marketplace\Http\Controllers\Marketplace\ReviewController;

Route::prefix('marketplace')->group(function () {
    /**
     * Public routes - accessible without authentication.
     */
    Route::controller(BrowseController::class)->group(function () {
        Route::get('/', 'index')->name('marketplace.browse.index');

        Route::get('/search', 'search')->name('marketplace.browse.search');

        Route::get('/category/{category_slug}', 'byCategory')->name('marketplace.browse.category');

        Route::get('/type/{type}', 'byType')->name('marketplace.browse.type');

        Route::get('/featured', 'featured')->name('marketplace.browse.featured');

        Route::get('/popular', 'popular')->name('marketplace.browse.popular');

        Route::get('/recent', 'recent')->name('marketplace.browse.recent');

        Route::get('/free', 'free')->name('marketplace.browse.free');

        Route::get('/paid', 'paid')->name('marketplace.browse.paid');
    });

    /**
     * Extension detail routes - public access.
     */
    Route::controller(ExtensionDetailController::class)->prefix('extension')->group(function () {
        Route::get('/{slug}', 'show')->name('marketplace.extension.show');

        Route::get('/{slug}/versions', 'versions')->name('marketplace.extension.versions');

        Route::get('/{slug}/reviews', 'reviews')->name('marketplace.extension.reviews');

        Route::get('/{slug}/changelog', 'changelog')->name('marketplace.extension.changelog');

        Route::get('/{slug}/compatibility', 'compatibility')->name('marketplace.extension.compatibility');
    });

    /**
     * Authenticated routes - require user login.
     */
    Route::middleware(['user'])->group(function () {
        /**
         * Developer registration routes.
         */
        Route::controller(DeveloperRegistrationController::class)->prefix('developer-registration')->group(function () {
            Route::get('/', 'create')->name('marketplace.developer-registration.create');

            Route::post('/', 'store')->name('marketplace.developer-registration.store');

            Route::get('/edit', 'edit')->name('marketplace.developer-registration.edit');

            Route::put('/', 'update')->name('marketplace.developer-registration.update');

            Route::get('/status', 'status')->name('marketplace.developer-registration.status');
        });

        /**
         * Extension installation routes.
         */
        Route::controller(InstallationController::class)->prefix('install')->group(function () {
            Route::post('/extension/{id}', 'install')->name('marketplace.install.extension');

            Route::get('/extension/{id}/status', 'installationStatus')->name('marketplace.install.status');

            Route::post('/extension/{id}/check-compatibility', 'checkCompatibility')->name('marketplace.install.check_compatibility');

            Route::post('/installation/{installation_id}/update', 'updateExtension')->name('marketplace.install.update');

            Route::delete('/installation/{installation_id}', 'uninstall')->name('marketplace.install.uninstall');

            Route::post('/installation/{installation_id}/enable', 'enable')->name('marketplace.install.enable');

            Route::post('/installation/{installation_id}/disable', 'disable')->name('marketplace.install.disable');

            Route::post('/installation/{installation_id}/auto-update', 'toggleAutoUpdate')->name('marketplace.install.toggle_auto_update');
        });

        /**
         * My extensions routes - user's installed extensions.
         */
        Route::controller(MyExtensionsController::class)->prefix('my-extensions')->group(function () {
            Route::get('/', 'index')->name('marketplace.my_extensions.index');

            Route::get('/{installation_id}', 'show')->name('marketplace.my_extensions.show');

            Route::get('/{installation_id}/settings', 'settings')->name('marketplace.my_extensions.settings');

            Route::put('/{installation_id}/settings', 'updateSettings')->name('marketplace.my_extensions.update_settings');

            Route::get('/updates/available', 'availableUpdates')->name('marketplace.my_extensions.updates');

            Route::get('/updates/check', 'checkUpdates')->name('marketplace.my_extensions.check_updates');
        });

        /**
         * Review routes - submit and manage reviews.
         */
        Route::controller(ReviewController::class)->prefix('reviews')->group(function () {
            Route::post('/extension/{extension_id}', 'store')->name('marketplace.reviews.store');

            Route::get('/{id}', 'show')->name('marketplace.reviews.show');

            Route::put('/{id}', 'update')->name('marketplace.reviews.update');

            Route::delete('/{id}', 'destroy')->name('marketplace.reviews.destroy');

            Route::post('/{id}/helpful', 'markHelpful')->name('marketplace.reviews.helpful');

            Route::post('/{id}/report', 'report')->name('marketplace.reviews.report');

            Route::get('/my-reviews', 'myReviews')->name('marketplace.reviews.my_reviews');
        });

        /**
         * Payment routes - handle extension purchases and refunds.
         */
        Route::controller(PaymentController::class)->prefix('payment')->group(function () {
            Route::post('/extension/{extension_id}/initiate', 'initiatePayment')->name('marketplace.payment.initiate');

            Route::get('/transaction/{transaction_id}/callback', 'handleCallback')->name('marketplace.payment.callback');

            Route::get('/transaction/{transaction_id}/status', 'getPaymentStatus')->name('marketplace.payment.status');

            Route::post('/transaction/{transaction_id}/cancel', 'cancelPayment')->name('marketplace.payment.cancel');

            Route::post('/transaction/{transaction_id}/refund', 'processRefund')->name('marketplace.payment.refund');
        });
    });

    /**
     * Webhook routes - public access for payment gateway callbacks.
     */
    Route::controller(PaymentController::class)->prefix('webhooks')->group(function () {
        Route::post('/payment', 'handleWebhook')->name('marketplace.webhooks.payment');
    });
});
