<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;

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
});

// Webhook routes (public - called by Meta)
Route::prefix('whatsapp')->group(function () {
    // Webhook verification (GET) and message receiver (POST)
    Route::match(['get', 'post'], 'webhook', [WhatsAppController::class, 'webhook'])
        ->name('whatsapp.webhook');
});
