<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Compliance\AuditReportGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Enable compliance features for all tests
    Config::set('compliance.enabled', true);
    Config::set('compliance.audit_logging.enabled', true);
    Config::set('compliance.reporting.enabled', true);
    Config::set('compliance.reporting.formats', ['csv', 'json', 'pdf']);

    // Clear audit logs before each test
    AuditLog::query()->delete();

    // Set up fake storage for file exports
    Storage::fake('local');
});

// ============================================
// AuditReportGenerator Service Tests
// ============================================

it('generates csv report with audit logs', function () {
    $user = User::factory()->create();

    // Create audit logs
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => ['name' => 'Test User'],
        'ip_address' => '127.0.0.1',
    ]);

    $generator = app(AuditReportGenerator::class);
    $csvContent = $generator->generate('csv');

    expect($csvContent)->toBeString();
    expect($csvContent)->toContain('ID');
    expect($csvContent)->toContain('Event');
    expect($csvContent)->toContain('created');
});

it('generates json report with audit logs', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => ['name' => 'Old Name'],
        'new_values' => ['name' => 'New Name'],
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json');

    expect($jsonData)->toBeArray();
    expect($jsonData)->toHaveKey('summary');
    expect($jsonData)->toHaveKey('logs');
    expect($jsonData['logs'])->toHaveCount(1);
});

it('generates pdf report with audit logs', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'deleted',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => ['name' => 'Test User'],
        'new_values' => [],
    ]);

    $generator = app(AuditReportGenerator::class);
    $pdfContent = $generator->generate('pdf');

    expect($pdfContent)->toBeString();
    expect(strlen($pdfContent))->toBeGreaterThan(0);
});

it('throws exception when reporting is disabled', function () {
    Config::set('compliance.reporting.enabled', false);

    $generator = app(AuditReportGenerator::class);

    expect(fn() => $generator->generate('csv'))->toThrow(\Exception::class, 'Compliance reporting is disabled');
});

it('throws exception for unsupported format', function () {
    $generator = app(AuditReportGenerator::class);

    expect(fn() => $generator->generate('xml'))->toThrow(\Exception::class, 'not supported');
});

it('filters audit logs by date range', function () {
    $user = User::factory()->create();

    // Create old audit log
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'created_at' => now()->subDays(60),
    ]);

    // Create recent audit log
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'created_at' => now()->subDays(5),
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json', [
        'start_date' => now()->subDays(7)->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]);

    expect($jsonData['logs'])->toHaveCount(1);
    expect($jsonData['logs'][0]['event'])->toBe('updated');
});

it('filters audit logs by event type', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json', [
        'event' => 'created',
    ]);

    expect($jsonData['logs'])->toHaveCount(1);
    expect($jsonData['logs'][0]['event'])->toBe('created');
});

it('filters audit logs by model type', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json', [
        'model_type' => User::class,
    ]);

    expect($jsonData['logs'])->toHaveCount(1);
});

it('filters audit logs by user id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    AuditLog::create([
        'user_id' => $user1->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user1->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    AuditLog::create([
        'user_id' => $user2->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user2->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json', [
        'user_id' => $user1->id,
    ]);

    expect($jsonData['logs'])->toHaveCount(1);
    expect($jsonData['logs'][0]['user_id'])->toBe($user1->id);
});

it('filters audit logs by ip address', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'ip_address' => '192.168.1.1',
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'ip_address' => '10.0.0.1',
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json', [
        'ip_address' => '192.168.1.1',
    ]);

    expect($jsonData['logs'])->toHaveCount(1);
    expect($jsonData['logs'][0]['ip_address'])->toBe('192.168.1.1');
});

it('filters audit logs by tags', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'tags' => ['sensitive', 'gdpr'],
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'tags' => ['normal'],
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json', [
        'tags' => 'sensitive',
    ]);

    expect($jsonData['logs'])->toHaveCount(1);
});

it('limits audit logs in report', function () {
    $user = User::factory()->create();

    // Create 20 audit logs
    for ($i = 0; $i < 20; $i++) {
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json', [
        'limit' => 10,
    ]);

    expect($jsonData['logs'])->toHaveCount(10);
});

it('provides summary statistics in json report', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json');

    expect($jsonData['summary'])->toBeArray();
    expect($jsonData['summary']['total_logs'])->toBeGreaterThan(0);
});

it('includes csv headers by default', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $generator = app(AuditReportGenerator::class);
    $csvContent = $generator->generate('csv');

    $lines = explode("\n", $csvContent);
    expect($lines[0])->toContain('ID');
    expect($lines[0])->toContain('Event');
});

it('can exclude csv headers', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $generator = app(AuditReportGenerator::class);
    $csvContent = $generator->generate('csv', [], ['include_headers' => false]);

    $lines = explode("\n", $csvContent);
    expect($lines[0])->not->toContain('ID');
});

it('exports report to file', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $generator = app(AuditReportGenerator::class);
    $path = $generator->exportToFile('csv', 'test-report.csv');

    expect($path)->toBeString();
    Storage::disk('local')->assertExists($path);
});

// ============================================
// Export Report Web Endpoint Tests
// ============================================

it('exports csv report via web endpoint', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user)->post(route('compliance.export-audit-report'), [
        'format' => 'csv',
    ]);

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition');
});

it('exports json report via web endpoint', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user)->post(route('compliance.export-audit-report'), [
        'format' => 'json',
    ]);

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/json');
});

it('exports pdf report via web endpoint', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'deleted',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user)->post(route('compliance.export-audit-report'), [
        'format' => 'pdf',
    ]);

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
});

it('requires authentication to export reports', function () {
    $response = $this->post(route('compliance.export-audit-report'), [
        'format' => 'csv',
    ]);

    $response->assertRedirect(route('login'));
});

it('validates export format', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('compliance.export-audit-report'), [
        'format' => 'xml',
    ]);

    $response->assertSessionHasErrors('format');
});

it('validates date range in export', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('compliance.export-audit-report'), [
        'format' => 'csv',
        'start_date' => '2024-12-31',
        'end_date' => '2024-01-01',
    ]);

    $response->assertSessionHasErrors('end_date');
});

it('applies filters to exported report', function () {
    $user = User::factory()->create();

    // Create multiple logs with different events
    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user)->post(route('compliance.export-audit-report'), [
        'format' => 'json',
        'event' => 'created',
    ]);

    $response->assertOk();
});

// ============================================
// API Endpoint Tests
// ============================================

it('returns compliance overview via api', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')->getJson('/api/compliance/metrics/overview');

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data' => [
            'period',
            'audit_logging',
            'consent_management',
            'data_retention',
            'encryption',
            'compliance_status',
        ],
    ]);
});

it('requires authentication for api overview', function () {
    $response = $this->getJson('/api/compliance/metrics/overview');

    $response->assertUnauthorized();
    $response->assertJson([
        'success' => false,
        'message' => 'Unauthenticated',
    ]);
});

it('accepts date range in api overview', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')->getJson('/api/compliance/metrics/overview', [
        'start_date' => now()->subDays(7)->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]);

    $response->assertOk();
});

it('returns specific metrics via api', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')->getJson('/api/compliance/metrics/audit_logging');

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data',
    ]);
});

it('returns compliance status via api', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')->getJson('/api/compliance/status');

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data' => [
            'overall_status',
            'frameworks',
            'issues',
            'warnings',
        ],
    ]);
});

it('returns audit report summary via api', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user, 'api')->getJson('/api/compliance/reports/audit/summary');

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data' => [
            'total_logs',
        ],
    ]);
});

it('generates audit report via api', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user, 'api')->postJson('/api/compliance/reports/audit/generate', [
        'format' => 'json',
    ]);

    $response->assertOk();
});

it('generates csv report via api', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user, 'api')->postJson('/api/compliance/reports/audit/generate', [
        'format' => 'csv',
    ]);

    $response->assertOk();
});

it('generates pdf report via api', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user, 'api')->postJson('/api/compliance/reports/audit/generate', [
        'format' => 'pdf',
    ]);

    $response->assertOk();
});

it('validates format in api report generation', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')->postJson('/api/compliance/reports/audit/generate', [
        'format' => 'xml',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('format');
});

it('applies filters in api report generation', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($user, 'api')->postJson('/api/compliance/reports/audit/generate', [
        'format' => 'json',
        'event' => 'created',
        'limit' => 100,
    ]);

    $response->assertOk();
});

// ============================================
// Integration Tests
// ============================================

it('exports report with multiple filters combined', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
        'ip_address' => '192.168.1.1',
        'created_at' => now()->subDays(5),
    ]);

    $generator = app(AuditReportGenerator::class);
    $jsonData = $generator->generate('json', [
        'event' => 'created',
        'ip_address' => '192.168.1.1',
        'start_date' => now()->subDays(7)->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]);

    expect($jsonData['logs'])->toHaveCount(1);
});

it('handles empty audit logs gracefully', function () {
    $generator = app(AuditReportGenerator::class);
    $csvContent = $generator->generate('csv');

    expect($csvContent)->toBeString();
    expect($csvContent)->toContain('ID'); // Headers still present
});

it('logs report generation to audit log', function () {
    $user = User::factory()->create();

    AuditLog::create([
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $initialCount = AuditLog::count();

    $generator = app(AuditReportGenerator::class);
    $this->actingAs($user);
    $generator->generate('csv');

    $finalCount = AuditLog::count();

    expect($finalCount)->toBeGreaterThan($initialCount);
});
