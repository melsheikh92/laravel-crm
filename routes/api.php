<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppTemplateController;
use Webkul\Admin\Http\Controllers\Api\TerritoryApiController;
use Webkul\Admin\Http\Controllers\Api\TerritoryRuleApiController;
use Webkul\Admin\Http\Controllers\Api\TerritoryAssignmentApiController;
use App\Http\Controllers\Api\ConsentController;
use App\Http\Controllers\Api\DataRetentionController;
use App\Http\Controllers\Api\DataDeletionController;
use App\Http\Controllers\Api\ComplianceReportController;
use App\Http\Controllers\Api\DocumentationSearchController;

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

/*
|--------------------------------------------------------------------------
| Consent Management Routes
|--------------------------------------------------------------------------
*/

// Authenticated routes for consent management
Route::middleware('auth:api')->prefix('consent')->group(function () {
    // Get available consent types
    Route::get('types', [ConsentController::class, 'types'])
        ->name('consent.types');

    // List all consents for authenticated user (with optional filtering)
    Route::get('/', [ConsentController::class, 'index'])
        ->name('consent.index');

    // Get active consents only
    Route::get('active', [ConsentController::class, 'active'])
        ->name('consent.active');

    // Check if user has all required consents
    Route::get('required/check', [ConsentController::class, 'checkRequired'])
        ->name('consent.check-required');

    // Check if user has specific consent type
    Route::get('check/{consentType}', [ConsentController::class, 'check'])
        ->name('consent.check');

    // Record a new consent
    Route::post('/', [ConsentController::class, 'store'])
        ->name('consent.store');

    // Record multiple consents at once
    Route::post('multiple', [ConsentController::class, 'storeMultiple'])
        ->name('consent.store-multiple');

    // Withdraw a specific consent
    Route::delete('{consentType}', [ConsentController::class, 'destroy'])
        ->name('consent.destroy');

    // Withdraw all consents
    Route::delete('/', [ConsentController::class, 'destroyAll'])
        ->name('consent.destroy-all');
});

/*
|--------------------------------------------------------------------------
| Data Retention Policy Routes
|--------------------------------------------------------------------------
*/

// Authenticated routes for data retention policy management
Route::middleware('auth:api')->prefix('retention-policies')->group(function () {
    // List all retention policies
    Route::get('/', [DataRetentionController::class, 'index'])
        ->name('retention-policies.index');

    // Get retention statistics
    Route::get('statistics', [DataRetentionController::class, 'statistics'])
        ->name('retention-policies.statistics');

    // Get expired records
    Route::get('expired-records', [DataRetentionController::class, 'expiredRecords'])
        ->name('retention-policies.expired-records');

    // Apply retention policies
    Route::post('apply', [DataRetentionController::class, 'applyPolicies'])
        ->name('retention-policies.apply');

    // Get a specific retention policy
    Route::get('{id}', [DataRetentionController::class, 'show'])
        ->name('retention-policies.show');

    // Create a new retention policy
    Route::post('/', [DataRetentionController::class, 'store'])
        ->name('retention-policies.store');

    // Update a retention policy
    Route::put('{id}', [DataRetentionController::class, 'update'])
        ->name('retention-policies.update');

    // Delete a retention policy
    Route::delete('{id}', [DataRetentionController::class, 'destroy'])
        ->name('retention-policies.destroy');

    // Activate a retention policy
    Route::post('{id}/activate', [DataRetentionController::class, 'activate'])
        ->name('retention-policies.activate');

    // Deactivate a retention policy
    Route::post('{id}/deactivate', [DataRetentionController::class, 'deactivate'])
        ->name('retention-policies.deactivate');
});

/*
|--------------------------------------------------------------------------
| Data Deletion Request Routes
|--------------------------------------------------------------------------
*/

// Authenticated routes for data deletion request management
Route::middleware('auth:api')->prefix('deletion-requests')->group(function () {
    // List all deletion requests
    Route::get('/', [DataDeletionController::class, 'index'])
        ->name('deletion-requests.index');

    // Get deletion request statistics
    Route::get('statistics', [DataDeletionController::class, 'statistics'])
        ->name('deletion-requests.statistics');

    // Get overdue deletion requests
    Route::get('overdue', [DataDeletionController::class, 'overdue'])
        ->name('deletion-requests.overdue');

    // Export user data (GDPR data portability)
    Route::post('export-data', [DataDeletionController::class, 'exportData'])
        ->name('deletion-requests.export-data');

    // Get a specific deletion request
    Route::get('{id}', [DataDeletionController::class, 'show'])
        ->name('deletion-requests.show');

    // Create a new deletion request
    Route::post('/', [DataDeletionController::class, 'store'])
        ->name('deletion-requests.store');

    // Process a deletion request
    Route::post('{id}/process', [DataDeletionController::class, 'process'])
        ->name('deletion-requests.process');

    // Cancel a deletion request
    Route::post('{id}/cancel', [DataDeletionController::class, 'cancel'])
        ->name('deletion-requests.cancel');
});

/*
|--------------------------------------------------------------------------
| Compliance Reporting Routes
|--------------------------------------------------------------------------
*/

// Authenticated routes for compliance reporting and metrics
Route::middleware('auth:api')->prefix('compliance')->group(function () {
    // Get compliance metrics overview
    Route::get('metrics/overview', [ComplianceReportController::class, 'overview'])
        ->name('compliance.metrics.overview');

    // Get specific compliance metrics by type
    Route::get('metrics/{type}', [ComplianceReportController::class, 'metrics'])
        ->name('compliance.metrics.type');

    // Get compliance status
    Route::get('status', [ComplianceReportController::class, 'status'])
        ->name('compliance.status');

    // Get audit report summary
    Route::get('reports/audit/summary', [ComplianceReportController::class, 'auditReportSummary'])
        ->name('compliance.reports.audit.summary');

    // Generate audit report (CSV, JSON, PDF)
    Route::post('reports/audit/generate', [ComplianceReportController::class, 'generateAuditReport'])
        ->name('compliance.reports.audit.generate');
});

/*
|--------------------------------------------------------------------------
| Documentation Search Routes
|--------------------------------------------------------------------------
*/

// Public routes for documentation search (no authentication required)
Route::prefix('docs')->group(function () {
    // Instant search endpoint
    Route::post('search', [DocumentationSearchController::class, 'search'])
        ->name('api.docs.search');

    // Autocomplete suggestions
    Route::get('autocomplete', [DocumentationSearchController::class, 'autocomplete'])
        ->name('api.docs.autocomplete');

    // Popular articles
    Route::get('popular', [DocumentationSearchController::class, 'popular'])
        ->name('api.docs.popular');

    // Helpful articles
    Route::get('helpful', [DocumentationSearchController::class, 'helpful'])
        ->name('api.docs.helpful');

    // Articles by category
    Route::get('category/{categoryId}', [DocumentationSearchController::class, 'byCategory'])
        ->name('api.docs.by-category');

    // Articles by type
    Route::get('type/{type}', [DocumentationSearchController::class, 'byType'])
        ->name('api.docs.by-type');
});