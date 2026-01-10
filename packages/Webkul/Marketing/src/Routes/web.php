<?php

use Illuminate\Support\Facades\Route;
use Webkul\Marketing\Http\Controllers\TrackController;

Route::group(['middleware' => ['web'], 'prefix' => 'marketing'], function () {
    Route::get('track/open/{id}', [TrackController::class, 'open'])->name('marketing.track.open');
    Route::get('track/click/{id}', [TrackController::class, 'click'])->name('marketing.track.click');
});
