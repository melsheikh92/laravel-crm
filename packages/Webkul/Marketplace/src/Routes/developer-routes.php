<?php

use Illuminate\Support\Facades\Route;
use Webkul\Marketplace\Http\Controllers\Developer\DashboardController;
use Webkul\Marketplace\Http\Controllers\Developer\EarningsController;
use Webkul\Marketplace\Http\Controllers\Developer\ExtensionController;
use Webkul\Marketplace\Http\Controllers\Developer\SubmissionController;
use Webkul\Marketplace\Http\Controllers\Developer\VersionController;

Route::prefix('marketplace/developer')->group(function () {
    /**
     * Developer dashboard route.
     */
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('developer.marketplace.dashboard');

        Route::get('/statistics', 'statistics')->name('developer.marketplace.dashboard.statistics');
    });

    /**
     * Developer extension management routes.
     */
    Route::controller(ExtensionController::class)->prefix('extensions')->group(function () {
        Route::get('/', 'index')->name('developer.marketplace.extensions.index');

        Route::get('/create', 'create')->name('developer.marketplace.extensions.create');

        Route::post('/', 'store')->name('developer.marketplace.extensions.store');

        Route::get('/{id}', 'show')->name('developer.marketplace.extensions.show');

        Route::get('/{id}/edit', 'edit')->name('developer.marketplace.extensions.edit');

        Route::put('/{id}', 'update')->name('developer.marketplace.extensions.update');

        Route::delete('/{id}', 'destroy')->name('developer.marketplace.extensions.destroy');

        Route::post('/{id}/upload-logo', 'uploadLogo')->name('developer.marketplace.extensions.upload_logo');

        Route::post('/{id}/upload-screenshots', 'uploadScreenshots')->name('developer.marketplace.extensions.upload_screenshots');

        Route::delete('/{id}/screenshots/{screenshot_id}', 'deleteScreenshot')->name('developer.marketplace.extensions.delete_screenshot');

        Route::get('/{id}/analytics', 'analytics')->name('developer.marketplace.extensions.analytics');
    });

    /**
     * Extension version management routes.
     */
    Route::controller(VersionController::class)->prefix('versions')->group(function () {
        Route::get('/extension/{extension_id}', 'index')->name('developer.marketplace.versions.index');

        Route::get('/extension/{extension_id}/create', 'create')->name('developer.marketplace.versions.create');

        Route::post('/extension/{extension_id}', 'store')->name('developer.marketplace.versions.store');

        Route::get('/{id}', 'show')->name('developer.marketplace.versions.show');

        Route::get('/{id}/edit', 'edit')->name('developer.marketplace.versions.edit');

        Route::put('/{id}', 'update')->name('developer.marketplace.versions.update');

        Route::delete('/{id}', 'destroy')->name('developer.marketplace.versions.destroy');

        Route::post('/{id}/upload-package', 'uploadPackage')->name('developer.marketplace.versions.upload_package');

        Route::get('/{id}/download', 'downloadPackage')->name('developer.marketplace.versions.download');
    });

    /**
     * Extension submission routes.
     */
    Route::controller(SubmissionController::class)->prefix('submissions')->group(function () {
        Route::get('/', 'index')->name('developer.marketplace.submissions.index');

        Route::post('/extension/{extension_id}/version/{version_id}', 'submit')->name('developer.marketplace.submissions.submit');

        Route::get('/{id}', 'show')->name('developer.marketplace.submissions.show');

        Route::delete('/{id}', 'cancel')->name('developer.marketplace.submissions.cancel');

        Route::post('/{id}/resubmit', 'resubmit')->name('developer.marketplace.submissions.resubmit');

        Route::get('/extension/{extension_id}', 'byExtension')->name('developer.marketplace.submissions.by_extension');

        Route::get('/pending/count', 'getPendingCount')->name('developer.marketplace.submissions.pending_count');
    });

    /**
     * Developer earnings routes.
     */
    Route::controller(EarningsController::class)->prefix('earnings')->group(function () {
        Route::get('/', 'index')->name('developer.marketplace.earnings.index');

        Route::get('/transactions', 'transactions')->name('developer.marketplace.earnings.transactions');

        Route::get('/transactions/{id}', 'showTransaction')->name('developer.marketplace.earnings.transactions.show');

        Route::get('/reports', 'reports')->name('developer.marketplace.earnings.reports');

        Route::get('/statistics', 'statistics')->name('developer.marketplace.earnings.statistics');

        Route::get('/payout-history', 'payoutHistory')->name('developer.marketplace.earnings.payout_history');

        Route::post('/payout-request', 'requestPayout')->name('developer.marketplace.earnings.request_payout');

        Route::get('/by-extension/{extension_id}', 'byExtension')->name('developer.marketplace.earnings.by_extension');
    });
});
