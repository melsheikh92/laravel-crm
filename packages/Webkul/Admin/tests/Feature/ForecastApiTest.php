<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadPipeline;
use Webkul\Lead\Models\LeadPipelineStage;
use Webkul\Lead\Models\SalesForecast;
use Webkul\Lead\Models\ForecastActual;
use Webkul\User\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = getDefaultAdmin();

    // Create a test pipeline
    $this->pipeline = LeadPipeline::create([
        'name' => 'Test Pipeline',
        'is_default' => true,
        'rotten_days' => 30,
    ]);

    // Create pipeline stages
    $this->stage1 = LeadPipelineStage::create([
        'lead_pipeline_id' => $this->pipeline->id,
        'name' => 'Qualification',
        'probability' => 25.0,
        'sort_order' => 1,
    ]);

    $this->stage2 = LeadPipelineStage::create([
        'lead_pipeline_id' => $this->pipeline->id,
        'name' => 'Proposal',
        'probability' => 75.0,
        'sort_order' => 2,
    ]);
});

describe('GET /api/forecasts - List forecasts', function () {
    test('can list all forecasts', function () {
        // Create test forecasts
        SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
            'metadata' => json_encode(['test' => true]),
        ]);

        SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'quarter',
            'period_start' => now()->startOfQuarter(),
            'period_end' => now()->endOfQuarter(),
            'forecast_value' => 150000,
            'weighted_forecast' => 105000,
            'best_case' => 225000,
            'worst_case' => 60000,
            'confidence_score' => 80.0,
            'metadata' => json_encode(['test' => true]),
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'period_type',
                        'period_start',
                        'period_end',
                        'forecast_value',
                        'weighted_forecast',
                        'best_case',
                        'worst_case',
                        'confidence_score',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'total',
                ],
            ]);

        expect($response->json('data'))->toHaveCount(2);
    });

    test('can filter forecasts by user', function () {
        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
        ]);

        SalesForecast::create([
            'user_id' => $user2->id,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 30000,
            'weighted_forecast' => 20000,
            'best_case' => 45000,
            'worst_case' => 10000,
            'confidence_score' => 70.0,
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.index', ['user_id' => $this->admin->id]));

        $response->assertOk();

        $forecasts = $response->json('data');
        expect($forecasts)->toHaveCount(1);
        expect($forecasts[0]['user_id'])->toBe($this->admin->id);
    });

    test('can filter forecasts by period type', function () {
        SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'week',
            'period_start' => now()->startOfWeek(),
            'period_end' => now()->endOfWeek(),
            'forecast_value' => 15000,
            'weighted_forecast' => 10000,
            'best_case' => 20000,
            'worst_case' => 5000,
            'confidence_score' => 70.0,
        ]);

        SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.index', ['period_type' => 'month']));

        $response->assertOk();

        $forecasts = $response->json('data');
        expect($forecasts)->toHaveCount(1);
        expect($forecasts[0]['period_type'])->toBe('month');
    });

    test('paginates forecast results', function () {
        // Create 20 forecasts
        for ($i = 0; $i < 20; $i++) {
            SalesForecast::create([
                'user_id' => $this->admin->id,
                'period_type' => 'month',
                'period_start' => now()->subMonths($i)->startOfMonth(),
                'period_end' => now()->subMonths($i)->endOfMonth(),
                'forecast_value' => 50000,
                'weighted_forecast' => 35000,
                'best_case' => 75000,
                'worst_case' => 20000,
                'confidence_score' => 75.5,
            ]);
        }

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.index', ['per_page' => 10]));

        $response->assertOk();

        expect($response->json('data'))->toHaveCount(10);
        expect($response->json('meta.total'))->toBe(20);
        expect($response->json('meta.last_page'))->toBe(2);
    });

    test('requires authentication', function () {
        test()->getJson(route('admin.forecasts.index'))
            ->assertStatus(401);
    });
});

describe('GET /api/forecasts/{id} - Show forecast', function () {
    test('can view a specific forecast', function () {
        $forecast = SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
            'metadata' => json_encode(['deals_count' => 10]),
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.show', $forecast->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'period_type',
                    'period_start',
                    'period_end',
                    'forecast_value',
                    'weighted_forecast',
                    'best_case',
                    'worst_case',
                    'confidence_score',
                ],
            ]);

        expect($response->json('data.id'))->toBe($forecast->id);
        expect($response->json('data.forecast_value'))->toBe('50000.00');
    });

    test('returns 404 for non-existent forecast', function () {
        test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.show', 99999))
            ->assertNotFound();
    });

    test('unauthorized user cannot access others forecast', function () {
        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        $forecast = SalesForecast::create([
            'user_id' => $user2->id,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
        ]);

        // Mock bouncer to restrict access
        $this->mock('bouncer', function ($mock) use ($user2) {
            $mock->shouldReceive('getAuthorizedUserIds')
                ->andReturn([$user2->id]);
        });

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.show', $forecast->id));

        $response->assertStatus(403);
    });

    test('requires authentication', function () {
        $forecast = SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
        ]);

        test()->getJson(route('admin.forecasts.show', $forecast->id))
            ->assertStatus(401);
    });
});

describe('POST /api/forecasts/generate - Generate forecast', function () {
    test('can generate a new forecast', function () {
        // Create some test leads
        Lead::create([
            'user_id' => $this->admin->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage1->id,
            'title' => 'Test Lead 1',
            'lead_value' => 10000,
            'status' => 1,
        ]);

        Lead::create([
            'user_id' => $this->admin->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage2->id,
            'title' => 'Test Lead 2',
            'lead_value' => 20000,
            'status' => 1,
        ]);

        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->admin->id,
                'period_type' => 'month',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'period_type',
                    'forecast_value',
                    'weighted_forecast',
                    'best_case',
                    'worst_case',
                ],
            ]);

        expect($response->json('data.user_id'))->toBe($this->admin->id);
        expect($response->json('data.period_type'))->toBe('month');

        // Verify forecast was saved to database
        $this->assertDatabaseHas('sales_forecasts', [
            'user_id' => $this->admin->id,
            'period_type' => 'month',
        ]);
    });

    test('validates required fields for forecast generation', function () {
        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'period_type']);
    });

    test('validates period type values', function () {
        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->admin->id,
                'period_type' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['period_type']);
    });

    test('can generate forecast with custom period start', function () {
        Lead::create([
            'user_id' => $this->admin->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage1->id,
            'title' => 'Test Lead',
            'lead_value' => 10000,
            'status' => 1,
        ]);

        $periodStart = now()->addMonth()->startOfMonth()->format('Y-m-d');

        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->admin->id,
                'period_type' => 'month',
                'period_start' => $periodStart,
            ]);

        $response->assertStatus(201);

        expect($response->json('data.period_start'))->toContain($periodStart);
    });

    test('requires authentication', function () {
        test()->postJson(route('admin.forecasts.generate'), [
            'user_id' => $this->admin->id,
            'period_type' => 'month',
        ])->assertStatus(401);
    });
});

describe('GET /api/forecasts/team/{teamId} - Team forecasts', function () {
    test('can retrieve team forecasts', function () {
        $teamId = 1;

        SalesForecast::create([
            'user_id' => $this->admin->id,
            'team_id' => $teamId,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
        ]);

        SalesForecast::create([
            'user_id' => $this->admin->id,
            'team_id' => $teamId,
            'period_type' => 'month',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'forecast_value' => 30000,
            'weighted_forecast' => 20000,
            'best_case' => 45000,
            'worst_case' => 10000,
            'confidence_score' => 70.0,
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.team', $teamId));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'totals' => [
                    'forecast_value',
                    'weighted_forecast',
                    'best_case',
                    'worst_case',
                    'avg_confidence',
                ],
            ]);

        expect($response->json('data'))->toHaveCount(2);
        expect($response->json('totals.forecast_value'))->toBe(80000.0);
        expect($response->json('totals.weighted_forecast'))->toBe(55000.0);
    });

    test('requires authentication', function () {
        test()->getJson(route('admin.forecasts.team', 1))
            ->assertStatus(401);
    });
});

describe('GET /api/forecasts/accuracy - Forecast accuracy', function () {
    test('can retrieve forecast accuracy metrics', function () {
        $forecast = SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'month',
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
        ]);

        ForecastActual::create([
            'forecast_id' => $forecast->id,
            'actual_value' => 38000,
            'variance' => 3000,
            'variance_percentage' => 8.57,
            'closed_at' => now()->subMonth()->endOfMonth(),
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.accuracy'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'metrics' => [
                    'total_forecasts',
                    'average_accuracy',
                    'average_variance',
                    'average_variance_pct',
                    'over_forecasted_count',
                    'under_forecasted_count',
                    'accurate_count',
                    'accuracy_rate',
                ],
            ]);

        expect($response->json('metrics.total_forecasts'))->toBeGreaterThan(0);
    });

    test('can filter accuracy by user', function () {
        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        $forecast1 = SalesForecast::create([
            'user_id' => $this->admin->id,
            'period_type' => 'month',
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
            'forecast_value' => 50000,
            'weighted_forecast' => 35000,
            'best_case' => 75000,
            'worst_case' => 20000,
            'confidence_score' => 75.5,
        ]);

        ForecastActual::create([
            'forecast_id' => $forecast1->id,
            'actual_value' => 38000,
            'variance' => 3000,
            'variance_percentage' => 8.57,
            'closed_at' => now()->subMonth()->endOfMonth(),
        ]);

        $forecast2 = SalesForecast::create([
            'user_id' => $user2->id,
            'period_type' => 'month',
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
            'forecast_value' => 30000,
            'weighted_forecast' => 20000,
            'best_case' => 45000,
            'worst_case' => 10000,
            'confidence_score' => 70.0,
        ]);

        ForecastActual::create([
            'forecast_id' => $forecast2->id,
            'actual_value' => 25000,
            'variance' => 5000,
            'variance_percentage' => 20.0,
            'closed_at' => now()->subMonth()->endOfMonth(),
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.accuracy', ['user_id' => $this->admin->id]));

        $response->assertOk();

        expect($response->json('metrics.total_forecasts'))->toBe(1);
    });

    test('returns empty metrics when no actuals exist', function () {
        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.accuracy'));

        $response->assertOk();

        expect($response->json('metrics.total_forecasts'))->toBe(0);
        expect($response->json('metrics.average_accuracy'))->toBe(0);
    });

    test('requires authentication', function () {
        test()->getJson(route('admin.forecasts.accuracy'))
            ->assertStatus(401);
    });
});
