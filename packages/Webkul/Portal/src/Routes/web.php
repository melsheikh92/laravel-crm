<?php

use Illuminate\Support\Facades\Route;
use Webkul\Portal\Http\Controllers\AuthController;
use Webkul\Portal\Http\Controllers\TicketController;
use Webkul\Portal\Http\Controllers\KbController;
use Webkul\Portal\Http\Controllers\ProfileController;
use Webkul\Portal\Http\Controllers\QuoteController;

use Webkul\Portal\Http\Controllers\DashboardController;

Route::group(['middleware' => ['web'], 'prefix' => 'portal'], function () {

    // Guest Routes
    Route::group(['middleware' => ['guest:portal']], function () {
        Route::get('login', [AuthController::class, 'login'])->name('portal.login');
        Route::post('login', [AuthController::class, 'loginPost'])->name('portal.login.store');
    });

    // Authenticated Routes
    Route::group(['middleware' => ['auth:portal']], function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('portal.dashboard');

        Route::post('logout', [AuthController::class, 'logout'])->name('portal.logout');

        // Ticket Routes
        Route::get('tickets', [TicketController::class, 'index'])->name('portal.tickets.index');
        Route::get('tickets/create', [TicketController::class, 'create'])->name('portal.tickets.create');
        Route::post('tickets', [TicketController::class, 'store'])->name('portal.tickets.store');
        Route::get('tickets/{id}', [TicketController::class, 'show'])->name('portal.tickets.show');
        Route::post('tickets/{id}/reply', [TicketController::class, 'reply'])->name('portal.tickets.reply');

        // KB Routes
        Route::get('kb', [KbController::class, 'index'])->name('portal.kb.index');
        Route::get('kb/{id}', [KbController::class, 'show'])->name('portal.kb.show');
        Route::post('kb/{id}/vote', [KbController::class, 'vote'])->name('portal.kb.vote');

        // Profile Routes
        Route::get('profile', [ProfileController::class, 'edit'])->name('portal.profile.edit');
        Route::post('profile', [ProfileController::class, 'update'])->name('portal.profile.update');

        // Quote Routes
        Route::get('quotes', [QuoteController::class, 'index'])->name('portal.quotes.index');
        Route::get('quotes/{id}', [QuoteController::class, 'show'])->name('portal.quotes.id');
    });
});