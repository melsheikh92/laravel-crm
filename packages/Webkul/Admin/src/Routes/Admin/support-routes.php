<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Support\TicketController;
use Webkul\Admin\Http\Controllers\Support\TicketCategoryController;
use Webkul\Admin\Http\Controllers\Support\SlaPolicyController;
use Webkul\Admin\Http\Controllers\Support\KbArticleController;
use Webkul\Admin\Http\Controllers\Support\KbCategoryController;

// Support Tickets
Route::controller(TicketController::class)->prefix('support/tickets')->group(function () {
    Route::get('', 'index')->name('admin.support.tickets.index');
    Route::get('create', 'create')->name('admin.support.tickets.create');
    Route::post('create', 'store')->name('admin.support.tickets.store');
    Route::get('{id}', 'show')->name('admin.support.tickets.show');
    Route::get('{id}/edit', 'edit')->name('admin.support.tickets.edit');
    Route::put('{id}', 'update')->name('admin.support.tickets.update');
    Route::delete('{id}', 'destroy')->name('admin.support.tickets.destroy');

    // Additional ticket actions
    Route::post('{id}/message', 'addMessage')->name('admin.support.tickets.add_message');
    Route::post('{id}/assign', 'assign')->name('admin.support.tickets.assign');
    Route::post('{id}/status', 'changeStatus')->name('admin.support.tickets.change_status');
    Route::post('{id}/close', 'close')->name('admin.support.tickets.close');
    Route::post('{id}/resolve', 'resolve')->name('admin.support.tickets.resolve');
    Route::post('{id}/attachment', 'uploadAttachment')->name('admin.support.tickets.upload_attachment');

    // Mass actions
    Route::post('mass-update', 'massUpdate')->name('admin.support.tickets.mass_update');
    Route::post('mass-destroy', 'massDestroy')->name('admin.support.tickets.mass_destroy');

    // Statistics
    Route::get('statistics', 'statistics')->name('admin.support.tickets.statistics');
});

// Ticket Categories
Route::controller(TicketCategoryController::class)->prefix('support/categories')->group(function () {
    Route::get('', 'index')->name('admin.support.categories.index');
    Route::get('create', 'create')->name('admin.support.categories.create');
    Route::post('create', 'store')->name('admin.support.categories.store');
    Route::get('{id}/edit', 'edit')->name('admin.support.categories.edit');
    Route::put('{id}', 'update')->name('admin.support.categories.update');
    Route::delete('{id}', 'destroy')->name('admin.support.categories.destroy');
});

// SLA Policies
Route::controller(SlaPolicyController::class)->prefix('support/sla/policies')->group(function () {
    Route::get('', 'index')->name('admin.support.sla.policies.index');
    Route::get('create', 'create')->name('admin.support.sla.policies.create');
    Route::post('create', 'store')->name('admin.support.sla.policies.store');
    Route::get('{id}/edit', 'edit')->name('admin.support.sla.policies.edit');
    Route::put('{id}', 'update')->name('admin.support.sla.policies.update');
    Route::delete('{id}', 'destroy')->name('admin.support.sla.policies.destroy');
    Route::get('metrics', 'metrics')->name('admin.support.sla.metrics');
});

// Knowledge Base Articles
Route::controller(KbArticleController::class)->prefix('support/kb/articles')->group(function () {
    Route::get('', 'index')->name('admin.support.kb.articles.index');
    Route::get('create', 'create')->name('admin.support.kb.articles.create');
    Route::post('create', 'store')->name('admin.support.kb.articles.store');
    Route::get('{id}/edit', 'edit')->name('admin.support.kb.articles.edit');
    Route::put('{id}', 'update')->name('admin.support.kb.articles.update');
    Route::delete('{id}', 'destroy')->name('admin.support.kb.articles.destroy');
    Route::post('{id}/publish', 'publish')->name('admin.support.kb.articles.publish');
    Route::post('{id}/unpublish', 'unpublish')->name('admin.support.kb.articles.unpublish');
    Route::get('search', 'search')->name('admin.support.kb.articles.search');
    Route::post('mass-update', 'massUpdate')->name('admin.support.kb.articles.mass_update');
    Route::post('mass-destroy', 'massDestroy')->name('admin.support.kb.articles.mass_destroy');
});

// Knowledge Base Categories
Route::controller(KbCategoryController::class)->prefix('support/kb/categories')->group(function () {
    Route::get('', 'index')->name('admin.support.kb.categories.index');
    Route::get('create', 'create')->name('admin.support.kb.categories.create');
    Route::post('create', 'store')->name('admin.support.kb.categories.store');
    Route::get('{id}/edit', 'edit')->name('admin.support.kb.categories.edit');
    Route::put('{id}', 'update')->name('admin.support.kb.categories.update');
    Route::delete('{id}', 'destroy')->name('admin.support.kb.categories.destroy');
});
