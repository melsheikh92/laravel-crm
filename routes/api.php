<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppTemplateController;
use Webkul\Admin\Http\Controllers\Api\TerritoryApiController;
use Webkul\Admin\Http\Controllers\Api\TerritoryRuleApiController;
use Webkul\Admin\Http\Controllers\Api\TerritoryAssignmentApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| WhatsApp Routes
|--------------------------------------------------------------------------
*/

// Authenticated routes for sending WhatsApp messages
Route::middleware('auth:user')->prefix('whatsapp')->group(function () {
    // Get WhatsApp data (templates and hasBusinessAPI)
    Route::get('data', [WhatsAppController::class, 'getData'])
        ->name('whatsapp.data');

    // Send message from person profile
    Route::post('person/{personId}/send', [WhatsAppController::class, 'sendFromPerson'])
        ->name('whatsapp.person.send');

    // Send message from lead profile
    Route::post('lead/{leadId}/send', [WhatsAppController::class, 'sendFromLead'])
        ->name('whatsapp.lead.send');

    // WhatsApp Template CRUD routes
    Route::get('templates', [WhatsAppTemplateController::class, 'index'])
        ->name('whatsapp.templates.index');
    Route::get('templates/create', [WhatsAppTemplateController::class, 'create'])
        ->name('whatsapp.templates.create');
    Route::post('templates', [WhatsAppTemplateController::class, 'store'])
        ->name('whatsapp.templates.store');
    Route::get('templates/{id}', [WhatsAppTemplateController::class, 'show'])
        ->name('whatsapp.templates.show');
    Route::get('templates/{id}/edit', [WhatsAppTemplateController::class, 'edit'])
        ->name('whatsapp.templates.edit');
    Route::put('templates/{id}', [WhatsAppTemplateController::class, 'update'])
        ->name('whatsapp.templates.update');
    Route::delete('templates/{id}', [WhatsAppTemplateController::class, 'destroy'])
        ->name('whatsapp.templates.destroy');
});

// Webhook routes (public - called by Meta)
Route::prefix('whatsapp')->group(function () {
    // Webhook verification (GET) and message receiver (POST)
    Route::match(['get', 'post'], 'webhook', [WhatsAppController::class, 'webhook'])
        ->name('whatsapp.webhook');
});

/*
|--------------------------------------------------------------------------
| Territory Management API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:user')->prefix('territories')->group(function () {
    // Territory hierarchy endpoints
    Route::get('hierarchy', [TerritoryApiController::class, 'hierarchy'])
        ->name('api.territories.hierarchy');
    Route::get('roots', [TerritoryApiController::class, 'roots'])
        ->name('api.territories.roots');

    // Territory CRUD routes
    Route::get('/', [TerritoryApiController::class, 'index'])
        ->name('api.territories.index');
    Route::post('/', [TerritoryApiController::class, 'store'])
        ->name('api.territories.store');
    Route::get('{id}', [TerritoryApiController::class, 'show'])
        ->name('api.territories.show');
    Route::put('{id}', [TerritoryApiController::class, 'update'])
        ->name('api.territories.update');
    Route::delete('{id}', [TerritoryApiController::class, 'destroy'])
        ->name('api.territories.destroy');

    // Territory relationship endpoints
    Route::get('{id}/children', [TerritoryApiController::class, 'children'])
        ->name('api.territories.children');
    Route::get('{id}/descendants', [TerritoryApiController::class, 'descendants'])
        ->name('api.territories.descendants');
    Route::get('{id}/rules', [TerritoryApiController::class, 'rules'])
        ->name('api.territories.rules');
    Route::get('{id}/assignments', [TerritoryApiController::class, 'assignments'])
        ->name('api.territories.assignments');
    Route::get('{id}/statistics', [TerritoryApiController::class, 'statistics'])
        ->name('api.territories.statistics');
});

Route::middleware('auth:user')->prefix('territory-rules')->group(function () {
    // Territory Rule CRUD routes
    Route::get('/', [TerritoryRuleApiController::class, 'index'])
        ->name('api.territory-rules.index');
    Route::post('/', [TerritoryRuleApiController::class, 'store'])
        ->name('api.territory-rules.store');
    Route::get('{id}', [TerritoryRuleApiController::class, 'show'])
        ->name('api.territory-rules.show');
    Route::put('{id}', [TerritoryRuleApiController::class, 'update'])
        ->name('api.territory-rules.update');
    Route::delete('{id}', [TerritoryRuleApiController::class, 'destroy'])
        ->name('api.territory-rules.destroy');

    // Territory Rule action endpoints
    Route::patch('{id}/toggle-status', [TerritoryRuleApiController::class, 'toggleStatus'])
        ->name('api.territory-rules.toggle-status');
    Route::patch('{id}/priority', [TerritoryRuleApiController::class, 'updatePriority'])
        ->name('api.territory-rules.update-priority');
    Route::post('bulk-priorities', [TerritoryRuleApiController::class, 'bulkUpdatePriorities'])
        ->name('api.territory-rules.bulk-priorities');
});

Route::middleware('auth:user')->prefix('territory-assignments')->group(function () {
    // Territory Assignment query endpoints
    Route::get('history', [TerritoryAssignmentApiController::class, 'history'])
        ->name('api.territory-assignments.history');
    Route::get('current', [TerritoryAssignmentApiController::class, 'current'])
        ->name('api.territory-assignments.current');

    // Territory Assignment CRUD routes
    Route::get('/', [TerritoryAssignmentApiController::class, 'index'])
        ->name('api.territory-assignments.index');
    Route::post('/', [TerritoryAssignmentApiController::class, 'store'])
        ->name('api.territory-assignments.store');
    Route::get('{id}', [TerritoryAssignmentApiController::class, 'show'])
        ->name('api.territory-assignments.show');
    Route::delete('{id}', [TerritoryAssignmentApiController::class, 'destroy'])
        ->name('api.territory-assignments.destroy');

    // Territory Assignment action endpoints
    Route::post('reassign', [TerritoryAssignmentApiController::class, 'reassign'])
        ->name('api.territory-assignments.reassign');
    Route::post('bulk-reassign', [TerritoryAssignmentApiController::class, 'bulkReassign'])
        ->name('api.territory-assignments.bulk-reassign');
});
