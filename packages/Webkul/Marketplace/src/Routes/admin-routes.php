<?php

use Illuminate\Support\Facades\Route;
use Webkul\Marketplace\Http\Controllers\Admin\CategoryController;
use Webkul\Marketplace\Http\Controllers\Admin\DeveloperApplicationController;
use Webkul\Marketplace\Http\Controllers\Admin\ExtensionController;
use Webkul\Marketplace\Http\Controllers\Admin\RevenueController;
use Webkul\Marketplace\Http\Controllers\Admin\SubmissionController;

Route::prefix('marketplace')->group(function () {
    /**
     * Extension management routes.
     */
    Route::controller(ExtensionController::class)->prefix('extensions')->group(function () {
        Route::get('/', 'index')->name('admin.marketplace.extensions.index');

        Route::get('/create', 'create')->name('admin.marketplace.extensions.create');

        Route::post('/', 'store')->name('admin.marketplace.extensions.store');

        Route::get('/{id}', 'show')->name('admin.marketplace.extensions.show');

        Route::get('/{id}/edit', 'edit')->name('admin.marketplace.extensions.edit');

        Route::put('/{id}', 'update')->name('admin.marketplace.extensions.update');

        Route::delete('/{id}', 'destroy')->name('admin.marketplace.extensions.destroy');

        Route::post('/{id}/enable', 'enable')->name('admin.marketplace.extensions.enable');

        Route::post('/{id}/disable', 'disable')->name('admin.marketplace.extensions.disable');

        Route::post('/{id}/feature', 'feature')->name('admin.marketplace.extensions.feature');

        Route::post('/{id}/unfeature', 'unfeature')->name('admin.marketplace.extensions.unfeature');

        Route::post('/mass-destroy', 'massDestroy')->name('admin.marketplace.extensions.mass_destroy');

        Route::post('/mass-enable', 'massEnable')->name('admin.marketplace.extensions.mass_enable');

        Route::post('/mass-disable', 'massDisable')->name('admin.marketplace.extensions.mass_disable');
    });

    /**
     * Submission review routes.
     */
    Route::controller(SubmissionController::class)->prefix('submissions')->group(function () {
        Route::get('/', 'index')->name('admin.marketplace.submissions.index');

        Route::get('/{id}', 'show')->name('admin.marketplace.submissions.show');

        Route::get('/{id}/review', 'review')->name('admin.marketplace.submissions.review');

        Route::post('/{id}/approve', 'approve')->name('admin.marketplace.submissions.approve');

        Route::post('/{id}/reject', 'reject')->name('admin.marketplace.submissions.reject');

        Route::post('/{id}/security-scan', 'runSecurityScan')->name('admin.marketplace.submissions.security_scan');

        Route::get('/{id}/security-scan/results', 'getSecurityScanResults')->name('admin.marketplace.submissions.security_scan_results');

        Route::get('/pending/count', 'getPendingCount')->name('admin.marketplace.submissions.pending_count');

        Route::post('/mass-approve', 'massApprove')->name('admin.marketplace.submissions.mass_approve');

        Route::post('/mass-reject', 'massReject')->name('admin.marketplace.submissions.mass_reject');
    });

    /**
     * Category management routes.
     */
    Route::controller(CategoryController::class)->prefix('categories')->group(function () {
        Route::get('/', 'index')->name('admin.marketplace.categories.index');

        Route::get('/create', 'create')->name('admin.marketplace.categories.create');

        Route::post('/', 'store')->name('admin.marketplace.categories.store');

        Route::get('/{id}', 'show')->name('admin.marketplace.categories.show');

        Route::get('/{id}/edit', 'edit')->name('admin.marketplace.categories.edit');

        Route::put('/{id}', 'update')->name('admin.marketplace.categories.update');

        Route::delete('/{id}', 'destroy')->name('admin.marketplace.categories.destroy');

        Route::post('/reorder', 'reorder')->name('admin.marketplace.categories.reorder');

        Route::post('/mass-destroy', 'massDestroy')->name('admin.marketplace.categories.mass_destroy');

        Route::get('/tree/data', 'getTreeData')->name('admin.marketplace.categories.tree_data');
    });

    /**
     * Developer application management routes.
     */
    Route::controller(DeveloperApplicationController::class)->prefix('developer-applications')->group(function () {
        Route::get('/', 'index')->name('admin.marketplace.developer-applications.index');

        Route::get('/{id}', 'show')->name('admin.marketplace.developer-applications.show');

        Route::post('/{id}/approve', 'approve')->name('admin.marketplace.developer-applications.approve');

        Route::post('/{id}/reject', 'reject')->name('admin.marketplace.developer-applications.reject');

        Route::post('/{id}/suspend', 'suspend')->name('admin.marketplace.developer-applications.suspend');

        Route::get('/pending/count', 'pendingCount')->name('admin.marketplace.developer-applications.pending_count');
    });

    /**
     * Revenue management routes.
     */
    Route::controller(RevenueController::class)->prefix('revenue')->group(function () {
        Route::get('/', 'index')->name('admin.marketplace.revenue.index');

        Route::get('/transactions', 'transactions')->name('admin.marketplace.revenue.transactions');

        Route::get('/transactions/{id}', 'showTransaction')->name('admin.marketplace.revenue.transactions.show');

        Route::get('/reports', 'reports')->name('admin.marketplace.revenue.reports');

        Route::get('/reports/platform', 'platformReport')->name('admin.marketplace.revenue.reports.platform');

        Route::get('/reports/seller/{seller_id}', 'sellerReport')->name('admin.marketplace.revenue.reports.seller');

        Route::get('/reports/extension/{extension_id}', 'extensionReport')->name('admin.marketplace.revenue.reports.extension');

        Route::post('/transactions/{id}/refund', 'processRefund')->name('admin.marketplace.revenue.transactions.refund');

        Route::get('/statistics', 'statistics')->name('admin.marketplace.revenue.statistics');

        Route::get('/top-sellers', 'topSellers')->name('admin.marketplace.revenue.top_sellers');

        Route::get('/top-extensions', 'topExtensions')->name('admin.marketplace.revenue.top_extensions');

        Route::put('/settings', 'updateSettings')->name('admin.marketplace.revenue.settings.update');
    });
});
