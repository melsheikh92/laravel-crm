<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppTemplateController;

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
