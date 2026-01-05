<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\AI\CopilotController;
use Webkul\Admin\Http\Controllers\AI\InsightController;

Route::prefix('ai')->group(function () {
    Route::controller(InsightController::class)->prefix('insights')->group(function () {
        Route::get('lead/{leadId}', 'getLeadInsights')->name('admin.ai.insights.lead');
        Route::get('person/{personId}', 'getPersonInsights')->name('admin.ai.insights.person');
        Route::post('lead/{leadId}/generate', 'generateLeadInsights')->name('admin.ai.insights.generate');
    });

    Route::controller(CopilotController::class)->prefix('copilot')->group(function () {
        Route::post('message', 'sendMessage')->name('admin.ai.copilot.message');
        Route::get('conversations', 'getConversations')->name('admin.ai.copilot.conversations');
        Route::get('conversations/{conversationId}/messages', 'getMessages')->name('admin.ai.copilot.messages');
    });
});

