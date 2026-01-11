<?php

use App\Events\SensitiveDataAccessed;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Compliance\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    // Enable compliance features for all tests
    Config::set('compliance.enabled', true);
    Config::set('compliance.audit_logging.enabled', true);
    Config::set('compliance.audit_logging.capture_ip', true);
    Config::set('compliance.audit_logging.capture_user_agent', true);

    // Clear audit logs before each test
    AuditLog::query()->delete();
});

// ============================================
// Auditable Trait Tests
// ============================================

it('creates audit log when user is created', function () {
    $user = User::factory()->create();

    expect(AuditLog::count())->toBe(1);

    $auditLog = AuditLog::first();
    expect($auditLog->event)->toBe('created');
    expect($auditLog->auditable_type)->toBe(User::class);
    expect($auditLog->auditable_id)->toBe($user->id);
    expect($auditLog->new_values)->toBeArray();
    expect($auditLog->old_values)->toBeArray()->toBeEmpty();
});

it('creates audit log when user is updated', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $originalName = $user->name;

    // Clear the create audit log
    AuditLog::query()->delete();

    $user->update(['name' => 'Updated Name']);

    expect(AuditLog::count())->toBe(1);

    $auditLog = AuditLog::first();
    expect($auditLog->event)->toBe('updated');
    expect($auditLog->auditable_type)->toBe(User::class);
    expect($auditLog->auditable_id)->toBe($user->id);
    expect($auditLog->old_values['name'])->toBe($originalName);
    expect($auditLog->new_values['name'])->toBe('Updated Name');
});

it('creates audit log when user is deleted', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Clear the create audit log
    AuditLog::query()->delete();

    $userId = $user->id;
    $user->delete();

    expect(AuditLog::count())->toBe(1);

    $auditLog = AuditLog::first();
    expect($auditLog->event)->toBe('deleted');
    expect($auditLog->auditable_type)->toBe(User::class);
    expect($auditLog->auditable_id)->toBe($userId);
    expect($auditLog->old_values)->toBeArray();
    expect($auditLog->new_values)->toBeArray()->toBeEmpty();
});

it('masks sensitive fields in audit logs', function () {
    Config::set('compliance.audit_logging.masked_fields', ['password', 'token']);

    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $auditLog = AuditLog::first();
    expect($auditLog->new_values['password'])->toBe('***MASKED***');
});

it('does not create audit logs when auditing is disabled', function () {
    Config::set('compliance.audit_logging.enabled', false);

    User::factory()->create([
        'role_id' => 1,
    ]);

    expect(AuditLog::count())->toBe(0);
});

it('does not create audit logs for excluded models', function () {
    Config::set('compliance.audit_logging.excluded_models', [User::class]);

    User::factory()->create([
        'role_id' => 1,
    ]);

    expect(AuditLog::count())->toBe(0);
});

it('captures IP address and user agent in audit logs', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $auditLog = AuditLog::first();
    expect($auditLog->ip_address)->not->toBeNull();
    expect($auditLog->user_agent)->not->toBeNull();
});

it('includes tags in audit logs', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $auditLog = AuditLog::first();
    expect($auditLog->tags)->toBeArray();
    expect($auditLog->tags)->toContain('User');
    expect($auditLog->tags)->toContain('created');
});

it('creates custom audit events', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $user->auditCustomEvent(
        'custom_action',
        ['field' => 'old_value'],
        ['field' => 'new_value'],
        ['custom_tag']
    );

    expect(AuditLog::count())->toBeGreaterThan(0);

    $auditLog = AuditLog::where('event', 'custom_action')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->event)->toBe('custom_action');
    expect($auditLog->old_values['field'])->toBe('old_value');
    expect($auditLog->new_values['field'])->toBe('new_value');
    expect($auditLog->tags)->toContain('custom_tag');
});

// ============================================
// AuditLogger Service Tests
// ============================================

it('logs data access events', function () {
    $auditLogger = app(AuditLogger::class);
    $user = User::factory()->create();

    $this->actingAs($user);

    $auditLogger->logAccess(
        $user,
        null,
        ['viewed_fields' => ['email', 'name']],
        ['sensitive']
    );

    expect(AuditLog::count())->toBeGreaterThan(0);

    $auditLog = AuditLog::where('event', 'viewed')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->auditable_type)->toBe(User::class);
    expect($auditLog->auditable_id)->toBe($user->id);
    expect($auditLog->tags)->toContain('sensitive');
});

it('logs data change events', function () {
    $auditLogger = app(AuditLogger::class);
    $user = User::factory()->create();

    $this->actingAs($user);

    $auditLogger->logChange(
        $user,
        null,
        ['name' => 'Old Name'],
        ['name' => 'New Name'],
        ['manual_change']
    );

    $auditLog = AuditLog::where('event', 'updated')->orderBy('created_at', 'desc')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->old_values['name'])->toBe('Old Name');
    expect($auditLog->new_values['name'])->toBe('New Name');
    expect($auditLog->tags)->toContain('manual_change');
});

it('logs data deletion events', function () {
    $auditLogger = app(AuditLogger::class);
    $user = User::factory()->create();

    $this->actingAs($user);

    $auditLogger->logDeletion(
        $user,
        null,
        ['name' => 'Test User'],
        ['permanent_delete']
    );

    $auditLog = AuditLog::where('event', 'deleted')->orderBy('created_at', 'desc')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->old_values['name'])->toBe('Test User');
    expect($auditLog->new_values)->toBeEmpty();
    expect($auditLog->tags)->toContain('permanent_delete');
});

it('logs data export events', function () {
    $auditLogger = app(AuditLogger::class);
    $user = User::factory()->create();

    $this->actingAs($user);

    $auditLogger->logExport(
        $user,
        null,
        ['format' => 'csv', 'fields' => ['name', 'email']],
        ['data_export']
    );

    $auditLog = AuditLog::where('event', 'exported')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->new_values['format'])->toBe('csv');
    expect($auditLog->tags)->toContain('data_export');
});

it('logs custom events with string auditable type', function () {
    $auditLogger = app(AuditLogger::class);
    $user = User::factory()->create();

    $this->actingAs($user);

    $auditLogger->logCustomEvent(
        'custom_event',
        User::class,
        $user->id,
        [],
        ['data' => 'value']
    );

    $auditLog = AuditLog::where('event', 'custom_event')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->auditable_type)->toBe(User::class);
    expect($auditLog->auditable_id)->toBe($user->id);
});

it('does not log when auditing is disabled via config', function () {
    Config::set('compliance.enabled', false);

    $auditLogger = app(AuditLogger::class);
    $user = User::factory()->create();

    $result = $auditLogger->logAccess($user);

    expect($result)->toBeNull();
});

// ============================================
// Authentication Event Listener Tests
// ============================================

it('logs successful login events', function () {
    Config::set('compliance.soc2.enabled', true);

    $user = User::factory()->create();

    Event::dispatch(new Login('web', $user, false));

    $auditLog = AuditLog::where('event', 'login')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->auditable_type)->toBe(User::class);
    expect($auditLog->auditable_id)->toBe($user->id);
    expect($auditLog->tags)->toContain('authentication');
    expect($auditLog->tags)->toContain('login');
});

it('logs logout events', function () {
    Config::set('compliance.soc2.enabled', true);

    $user = User::factory()->create();

    Event::dispatch(new Logout('web', $user));

    $auditLog = AuditLog::where('event', 'logout')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->auditable_type)->toBe(User::class);
    expect($auditLog->auditable_id)->toBe($user->id);
    expect($auditLog->tags)->toContain('authentication');
    expect($auditLog->tags)->toContain('logout');
});

it('logs failed login attempts', function () {
    Config::set('compliance.soc2.enabled', true);

    $user = User::factory()->create();

    Event::dispatch(new Failed('web', $user, [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]));

    $auditLog = AuditLog::where('event', 'login_failed')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->tags)->toContain('failed_attempt');
    expect($auditLog->new_values['credentials']['password'])->toBe('***MASKED***');
});

// ============================================
// Sensitive Data Access Event Tests
// ============================================

it('logs sensitive data access events', function () {
    Config::set('compliance.soc2.enabled', true);
    Config::set('compliance.soc2.security.log_data_access', true);

    $user = User::factory()->create();

    Event::dispatch(new SensitiveDataAccessed(
        $user,
        ['email', 'phone'],
        'view',
        ['reason' => 'customer_support'],
        ['pii']
    ));

    $auditLog = AuditLog::where('event', 'viewed')
        ->whereJsonContains('tags', 'sensitive_data_access')
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->new_values['fields_accessed'])->toContain('email');
    expect($auditLog->new_values['fields_accessed'])->toContain('phone');
    expect($auditLog->tags)->toContain('pii');
});

// ============================================
// AuditLog Model Tests
// ============================================

it('filters audit logs by event type', function () {
    $user = User::factory()->create();

    $user->update(['name' => 'Updated']);

    $createdLogs = AuditLog::byEvent('created')->get();
    $updatedLogs = AuditLog::byEvent('updated')->get();

    expect($createdLogs)->toHaveCount(1);
    expect($updatedLogs)->toHaveCount(1);
});

it('filters audit logs by multiple events', function () {
    $user = User::factory()->create();
    $user->update(['name' => 'Updated']);

    $logs = AuditLog::byEvents(['created', 'updated'])->get();

    expect($logs)->toHaveCount(2);
});

it('filters audit logs by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1);
    $user1->update(['name' => 'User 1 Updated']);

    $this->actingAs($user2);
    $user2->update(['name' => 'User 2 Updated']);

    $user1Logs = AuditLog::byUser($user1->id)->get();

    expect($user1Logs)->toHaveCount(1);
    expect($user1Logs->first()->user_id)->toBe($user1->id);
});

it('filters audit logs by auditable type', function () {
    User::factory()->create();

    $logs = AuditLog::byAuditableType(User::class)->get();

    expect($logs->count())->toBeGreaterThan(0);
    expect($logs->first()->auditable_type)->toBe(User::class);
});

it('filters audit logs by tag', function () {
    $user = User::factory()->create();

    $log = AuditLog::withTag('User')->first();

    expect($log)->not->toBeNull();
    expect($log->tags)->toContain('User');
});

it('gets recent audit logs', function () {
    User::factory()->count(5)->create();

    $recent = AuditLog::recent(3)->get();

    expect($recent)->toHaveCount(3);
});

it('retrieves audit logs for a model', function () {
    $user = User::factory()->create();

    $logs = $user->auditLogs;

    expect($logs)->toHaveCount(1);
    expect($logs->first()->event)->toBe('created');
});

it('gets changes from audit log', function () {
    $user = User::factory()->create();
    $originalName = $user->name;

    AuditLog::query()->delete();

    $user->update(['name' => 'New Name']);

    $auditLog = AuditLog::first();
    $changes = $auditLog->getChanges();

    expect($changes)->toHaveKey('name');
    expect($changes['name']['old'])->toBe($originalName);
    expect($changes['name']['new'])->toBe('New Name');
});

it('checks if audit log has changes', function () {
    $user = User::factory()->create();

    $createLog = AuditLog::where('event', 'created')->first();
    expect($createLog->hasFieldChanges())->toBeFalse();

    AuditLog::query()->delete();
    $user->update(['name' => 'Updated']);

    $updateLog = AuditLog::where('event', 'updated')->first();
    expect($updateLog->hasFieldChanges())->toBeTrue();
});

it('gets audit log description', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'John Doe',
    ]);

    $auditLog = AuditLog::first();
    $description = $auditLog->getDescription();

    expect($description)->toBeString();
    expect($description)->toContain('User');
});

it('adds and removes tags from audit log', function () {
    $user = User::factory()->create();

    $auditLog = AuditLog::first();
    $auditLog->addTag('important');

    expect($auditLog->fresh()->hasTag('important'))->toBeTrue();

    $auditLog->removeTag('important');

    expect($auditLog->fresh()->hasTag('important'))->toBeFalse();
});

it('prevents duplicate tags', function () {
    $user = User::factory()->create();

    $auditLog = AuditLog::first();
    $auditLog->addTag('test');
    $auditLog->addTag('test');

    $auditLog = $auditLog->fresh();
    $testTagCount = count(array_filter($auditLog->tags, fn($tag) => $tag === 'test'));

    expect($testTagCount)->toBe(1);
});

// ============================================
// Integration Tests
// ============================================

it('creates complete audit trail for user lifecycle', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $user->update(['name' => 'Updated User']);
    $user->delete();

    $logs = AuditLog::forModel($user);

    expect($logs)->toHaveCount(3);
    expect($logs->pluck('event')->toArray())->toContain('created', 'updated', 'deleted');
});

it('maintains audit logs across multiple users and models', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1);
    $user1->update(['name' => 'User 1 Updated']);

    $this->actingAs($user2);
    $user2->update(['name' => 'User 2 Updated']);

    $totalLogs = AuditLog::count();
    $user1Logs = AuditLog::byUser($user1->id)->count();
    $user2Logs = AuditLog::byUser($user2->id)->count();

    expect($totalLogs)->toBeGreaterThanOrEqual(4);
    expect($user1Logs)->toBeGreaterThan(0);
    expect($user2Logs)->toBeGreaterThan(0);
});

it('respects configuration for IP and user agent capture', function () {
    Config::set('compliance.audit_logging.capture_ip', false);
    Config::set('compliance.audit_logging.capture_user_agent', false);

    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $auditLog = AuditLog::first();
    expect($auditLog->ip_address)->toBeNull();
    expect($auditLog->user_agent)->toBeNull();
});
