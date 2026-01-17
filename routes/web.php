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
| Documentation Routes
|--------------------------------------------------------------------------
|
| Routes for public documentation portal.
| All routes are publicly accessible for viewing documentation.
|
*/

Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', [App\Http\Controllers\DocumentationController::class, 'index'])->name('index');
    Route::get('/{id}', [App\Http\Controllers\DocumentationController::class, 'show'])->name('show');
    Route::post('/{id}/vote', [App\Http\Controllers\DocumentationController::class, 'vote'])->name('vote');
});

/*
|--------------------------------------------------------------------------
| Admin Documentation Routes
|--------------------------------------------------------------------------
|
| Routes for admin documentation management.
| All routes are protected by authentication middleware in the controller.
|
*/

Route::prefix('admin/docs')->name('admin.docs.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminDocumentationController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\AdminDocumentationController::class, 'create'])->name('create');
    Route::get('/stats', [App\Http\Controllers\AdminDocumentationController::class, 'stats'])->name('stats');
    Route::post('/', [App\Http\Controllers\AdminDocumentationController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\AdminDocumentationController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\AdminDocumentationController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\AdminDocumentationController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\AdminDocumentationController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/publish', [App\Http\Controllers\AdminDocumentationController::class, 'publish'])->name('publish');
    Route::post('/{id}/unpublish', [App\Http\Controllers\AdminDocumentationController::class, 'unpublish'])->name('unpublish');
    Route::post('/mass-destroy', [App\Http\Controllers\AdminDocumentationController::class, 'massDestroy'])->name('mass-destroy');
    Route::post('/mass-update', [App\Http\Controllers\AdminDocumentationController::class, 'massUpdate'])->name('mass-update');
});
