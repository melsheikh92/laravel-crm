<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Collaboration\ChatController;
use Webkul\Admin\Http\Controllers\Collaboration\ChannelController;
use Webkul\Admin\Http\Controllers\Collaboration\NotificationController;

Route::prefix('collaboration')->group(function () {
    Route::controller(ChannelController::class)->prefix('channels')->group(function () {
        Route::get('/', 'index')->name('admin.collaboration.channels.index');
        Route::get('/create', 'create')->name('admin.collaboration.channels.create');
        Route::post('/', 'store')->name('admin.collaboration.channels.store');
        Route::get('/{id}', 'show')->name('admin.collaboration.channels.show');
    });

    Route::controller(ChatController::class)->prefix('chat')->group(function () {
        Route::post('/message', 'sendMessage')->name('admin.collaboration.chat.send');
        Route::get('/channel/{channelId}/messages', 'getMessages')->name('admin.collaboration.chat.messages');
    });

    Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
        Route::get('/', 'index')->name('admin.collaboration.notifications.index');
        Route::post('/{id}/read', 'markAsRead')->name('admin.collaboration.notifications.read');
        Route::get('/unread-count', 'unreadCount')->name('admin.collaboration.notifications.unread-count');
    });
});

