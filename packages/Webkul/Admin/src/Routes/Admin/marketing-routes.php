<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Marketing\CampaignController;
use Webkul\Admin\Http\Controllers\Marketing\TemplateController;

Route::prefix('marketing')->group(function () {
    // Campaign routes
    Route::controller(CampaignController::class)->prefix('campaigns')->group(function () {
        Route::get('/', 'index')->name('admin.marketing.campaigns.index');
        Route::get('/create', 'create')->name('admin.marketing.campaigns.create');
        Route::post('/', 'store')->name('admin.marketing.campaigns.store');
        Route::post('/parse-csv', 'parseCsv')->name('admin.marketing.campaigns.parse-csv');
        // More specific routes must come before generic {id} route
        Route::get('/{id}/edit', 'edit')->name('admin.marketing.campaigns.edit');
        Route::get('/{id}/statistics', 'statistics')->name('admin.marketing.campaigns.statistics');
        Route::post('/{id}/schedule', 'schedule')->name('admin.marketing.campaigns.schedule');
        Route::post('/{id}/send', 'send')->name('admin.marketing.campaigns.send');
        Route::post('/{id}/cancel', 'cancel')->name('admin.marketing.campaigns.cancel');
        Route::post('/{id}/recipients', 'addRecipients')->name('admin.marketing.campaigns.add-recipients');
        Route::get('/{id}', 'show')->name('admin.marketing.campaigns.show');
        Route::put('/{id}', 'update')->name('admin.marketing.campaigns.update');
        Route::delete('/{id}', 'destroy')->name('admin.marketing.campaigns.destroy');
    });

    // Template routes
    Route::controller(TemplateController::class)->prefix('templates')->group(function () {
        Route::get('/', 'index')->name('admin.marketing.templates.index');
        Route::get('/create', 'create')->name('admin.marketing.templates.create');
        Route::post('/', 'store')->name('admin.marketing.templates.store');
        Route::get('/{id}', 'show')->name('admin.marketing.templates.show');
        Route::get('/{id}/edit', 'edit')->name('admin.marketing.templates.edit');
        Route::put('/{id}', 'update')->name('admin.marketing.templates.update');
        Route::delete('/{id}', 'destroy')->name('admin.marketing.templates.destroy');
        Route::post('/{id}/preview', 'preview')->name('admin.marketing.templates.preview');
    });
});

