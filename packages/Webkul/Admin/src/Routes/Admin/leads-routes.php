<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Lead\ActivityController;
use Webkul\Admin\Http\Controllers\Lead\DealScoringController;
use Webkul\Admin\Http\Controllers\Lead\EmailController;
use Webkul\Admin\Http\Controllers\Lead\ForecastAnalyticsController;
use Webkul\Admin\Http\Controllers\Lead\ForecastController;
use Webkul\Admin\Http\Controllers\Lead\LeadController;
use Webkul\Admin\Http\Controllers\Lead\QuoteController;
use Webkul\Admin\Http\Controllers\Lead\TagController;

Route::controller(LeadController::class)->prefix('leads')->group(function () {
    Route::get('', 'index')->name('admin.leads.index');

    Route::get('create', 'create')->name('admin.leads.create');

    Route::post('create', 'store')->name('admin.leads.store');

    Route::post('create-by-ai', 'createByAI')->name('admin.leads.create_by_ai');

    Route::get('view/{id}', 'view')->name('admin.leads.view');

    Route::get('edit/{id}', 'edit')->name('admin.leads.edit');

    Route::put('edit/{id}', 'update')->name('admin.leads.update');

    Route::put('attributes/edit/{id}', 'updateAttributes')->name('admin.leads.attributes.update');

    Route::put('stage/edit/{id}', 'updateStage')->name('admin.leads.stage.update');

    Route::get('search', 'search')->name('admin.leads.search');

    Route::delete('{id}', 'destroy')->name('admin.leads.delete');

    Route::post('mass-update', 'massUpdate')->name('admin.leads.mass_update');

    Route::post('mass-destroy', 'massDestroy')->name('admin.leads.mass_delete');

    Route::get('get/{pipeline_id?}', 'get')->name('admin.leads.get');

    Route::delete('product/{lead_id}', 'removeProduct')->name('admin.leads.product.remove');

    Route::put('product/{lead_id}', 'addProduct')->name('admin.leads.product.add');

    Route::get('kanban/look-up', [LeadController::class, 'kanbanLookup'])->name('admin.leads.kanban.look_up');

    Route::controller(ActivityController::class)->prefix('{id}/activities')->group(function () {
        Route::get('', 'index')->name('admin.leads.activities.index');
    });

    Route::controller(TagController::class)->prefix('{id}/tags')->group(function () {
        Route::post('', 'attach')->name('admin.leads.tags.attach');

        Route::delete('', 'detach')->name('admin.leads.tags.detach');
    });

    Route::controller(EmailController::class)->prefix('{id}/emails')->group(function () {
        Route::post('', 'store')->name('admin.leads.emails.store');

        Route::delete('', 'detach')->name('admin.leads.emails.detach');
    });

    Route::controller(QuoteController::class)->prefix('{id}/quotes')->group(function () {
        Route::delete('{quote_id?}', 'delete')->name('admin.leads.quotes.delete');
    });

    /**
     * WhatsApp routes.
     */
    Route::post('{id}/whatsapp/send', [\App\Http\Controllers\WhatsAppController::class, 'sendFromLead'])
        ->name('admin.leads.whatsapp.send');

    /**
     * Deal scoring routes.
     */
    Route::controller(DealScoringController::class)->prefix('{id}/score')->group(function () {
        Route::get('', 'show')->name('admin.leads.score.show');

        Route::post('calculate', 'calculate')->name('admin.leads.score.calculate');
    });

    Route::controller(DealScoringController::class)->group(function () {
        Route::get('top-scored', 'topScored')->name('admin.leads.top_scored');
    });
});

/**
 * Forecast routes.
 */
Route::controller(ForecastController::class)->prefix('forecasts')->group(function () {
    Route::get('dashboard', 'index')->name('admin.forecasts.index');

    Route::get('', function () {
        return redirect()->route('admin.forecasts.index');
    });

    Route::post('generate', 'generate')->name('admin.forecasts.generate');

    Route::get('accuracy', 'accuracy')->name('admin.forecasts.accuracy');

    Route::get('team/{teamId}', 'team')->name('admin.forecasts.team');

    Route::get('{id}', 'show')->name('admin.forecasts.show');
});

/**
 * Forecast analytics routes.
 */
Route::controller(ForecastAnalyticsController::class)->prefix('forecasts/analytics')->group(function () {
    Route::get('trends', 'trends')->name('admin.forecasts.analytics.trends');

    Route::get('scenarios', 'scenarios')->name('admin.forecasts.analytics.scenarios');

    Route::get('comparison', 'comparison')->name('admin.forecasts.analytics.comparison');
});
