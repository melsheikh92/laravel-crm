<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/landing', function () {
    return view('welcome');
});

Route::post('/demo-request', [App\Http\Controllers\LandingController::class, 'sendDemoRequest'])->name('demo.request');

/*
|--------------------------------------------------------------------------
| Compliance Routes
|--------------------------------------------------------------------------
|
| Routes for compliance dashboard, audit logs, and reporting.
| All routes are protected by authentication middleware in the controller.
|
*/

Route::prefix('compliance')->name('compliance.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ComplianceController::class, 'dashboard'])->name('dashboard');
    Route::get('/audit-logs', [App\Http\Controllers\ComplianceController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/export-audit-report', [App\Http\Controllers\ComplianceController::class, 'exportAuditReport'])->name('export-audit-report');
    Route::get('/metrics', [App\Http\Controllers\ComplianceController::class, 'metrics'])->name('metrics');
});

/*
|--------------------------------------------------------------------------
| Onboarding Wizard Routes
|--------------------------------------------------------------------------
|
| Routes for the interactive onboarding wizard that guides new users
| through initial setup, configuration, and sample data import.
| All routes are protected by authentication middleware in the controller.
|
*/

Route::prefix('onboarding')->name('onboarding.')->group(function () {
    // Main wizard page - shows current step or starts onboarding
    Route::get('/', [App\Http\Controllers\OnboardingController::class, 'index'])->name('index');

    // Display a specific onboarding step
    Route::get('/step/{step}', [App\Http\Controllers\OnboardingController::class, 'show'])->name('step');

    // Process step submission (complete and save data)
    Route::post('/step/{step}', [App\Http\Controllers\OnboardingController::class, 'store'])->name('step.store');

    // Navigation actions
    Route::post('/next', [App\Http\Controllers\OnboardingController::class, 'next'])->name('next');
    Route::post('/previous', [App\Http\Controllers\OnboardingController::class, 'previous'])->name('previous');

    // Skip a specific step
    Route::post('/step/{step}/skip', [App\Http\Controllers\OnboardingController::class, 'skip'])->name('step.skip');

    // Complete the entire onboarding wizard
    Route::post('/complete', [App\Http\Controllers\OnboardingController::class, 'complete'])->name('complete');

    // Restart the onboarding wizard
    Route::post('/restart', [App\Http\Controllers\OnboardingController::class, 'restart'])->name('restart');

    // AJAX endpoints for progress and validation
    Route::get('/progress', [App\Http\Controllers\OnboardingController::class, 'progress'])->name('progress');
    Route::get('/statistics', [App\Http\Controllers\OnboardingController::class, 'statistics'])->name('statistics');
    Route::post('/step/{step}/validate', [App\Http\Controllers\OnboardingController::class, 'validateStep'])->name('step.validate');
});
