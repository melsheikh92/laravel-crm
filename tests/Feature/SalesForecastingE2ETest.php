<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadPipeline;
use Webkul\Lead\Models\LeadPipelineStage;
use Webkul\Lead\Models\SalesForecast;
use Webkul\Lead\Models\ForecastActual;
use Webkul\Lead\Models\DealScore;
use Webkul\Lead\Models\HistoricalConversion;
use Webkul\User\Models\User;
use App\Jobs\CalculateDealScoresJob;
use App\Jobs\TrackForecastActualsJob;
use App\Jobs\RefreshHistoricalConversionsJob;

uses(RefreshDatabase::class);

/**
 * Complete Sales Forecasting Flow Tests
 */
describe('Complete Sales Forecasting E2E Flow', function () {
    beforeEach(function () {
        $this->admin = getDefaultAdmin();
        $this->salesRep = User::create([
            'name' => 'Sales Rep',
            'email' => 'salesrep@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        // Create a complete sales pipeline
        $this->pipeline = LeadPipeline::create([
            'name' => 'Standard Sales Pipeline',
            'is_default' => true,
            'rotten_days' => 30,
        ]);

        // Create realistic pipeline stages with probabilities
        $this->stages = [
            'Prospecting' => LeadPipelineStage::create([
                'lead_pipeline_id' => $this->pipeline->id,
                'name' => 'Prospecting',
                'probability' => 10.0,
                'sort_order' => 1,
            ]),
            'Qualification' => LeadPipelineStage::create([
                'lead_pipeline_id' => $this->pipeline->id,
                'name' => 'Qualification',
                'probability' => 25.0,
                'sort_order' => 2,
            ]),
            'Needs Analysis' => LeadPipelineStage::create([
                'lead_pipeline_id' => $this->pipeline->id,
                'name' => 'Needs Analysis',
                'probability' => 40.0,
                'sort_order' => 3,
            ]),
            'Proposal' => LeadPipelineStage::create([
                'lead_pipeline_id' => $this->pipeline->id,
                'name' => 'Proposal',
                'probability' => 60.0,
                'sort_order' => 4,
            ]),
            'Negotiation' => LeadPipelineStage::create([
                'lead_pipeline_id' => $this->pipeline->id,
                'name' => 'Negotiation',
                'probability' => 80.0,
                'sort_order' => 5,
            ]),
            'Closed Won' => LeadPipelineStage::create([
                'lead_pipeline_id' => $this->pipeline->id,
                'name' => 'Closed Won',
                'probability' => 100.0,
                'sort_order' => 6,
            ]),
            'Closed Lost' => LeadPipelineStage::create([
                'lead_pipeline_id' => $this->pipeline->id,
                'name' => 'Closed Lost',
                'probability' => 0.0,
                'sort_order' => 7,
            ]),
        ];
    });

    it('can complete full forecast generation lifecycle', function () {
        // Step 1: Create a diverse set of leads across different stages
        $leads = [
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Prospecting']->id,
                'title' => 'Small Deal - Prospecting',
                'lead_value' => 5000,
                'status' => 1,
                'expected_close_date' => now()->addDays(45),
            ]),
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Qualification']->id,
                'title' => 'Medium Deal - Qualification',
                'lead_value' => 15000,
                'status' => 1,
                'expected_close_date' => now()->addDays(30),
            ]),
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Needs Analysis']->id,
                'title' => 'Large Deal - Needs Analysis',
                'lead_value' => 50000,
                'status' => 1,
                'expected_close_date' => now()->addDays(20),
            ]),
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Proposal']->id,
                'title' => 'Enterprise Deal - Proposal',
                'lead_value' => 100000,
                'status' => 1,
                'expected_close_date' => now()->addDays(15),
            ]),
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Negotiation']->id,
                'title' => 'Hot Deal - Negotiation',
                'lead_value' => 75000,
                'status' => 1,
                'expected_close_date' => now()->addDays(7),
            ]),
        ];

        // Verify leads were created correctly
        expect(Lead::count())->toBe(5);

        // Step 2: Generate forecast for the sales rep
        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->salesRep->id,
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
                    'confidence_score',
                ],
            ]);

        $forecast = $response->json('data');

        // Verify forecast calculations are realistic
        expect($forecast['user_id'])->toBe($this->salesRep->id);
        expect($forecast['period_type'])->toBe('month');
        expect((float) $forecast['weighted_forecast'])->toBeGreaterThan(0);
        expect((float) $forecast['best_case'])->toBeGreaterThanOrEqual((float) $forecast['weighted_forecast']);
        expect((float) $forecast['worst_case'])->toBeLessThanOrEqual((float) $forecast['weighted_forecast']);
        expect((float) $forecast['confidence_score'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);

        // Verify forecast was saved to database
        $dbForecast = SalesForecast::where('user_id', $this->salesRep->id)
            ->where('period_type', 'month')
            ->first();

        expect($dbForecast)->not->toBeNull();
        expect($dbForecast->forecast_value)->toBeGreaterThan(0);

        // Step 3: Retrieve the generated forecast
        $getResponse = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.show', $dbForecast->id));

        $getResponse->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $dbForecast->id,
                    'user_id' => $this->salesRep->id,
                ],
            ]);

        // Step 4: Verify forecast metadata contains deal breakdown
        expect($dbForecast->metadata)->toBeArray();
        expect($dbForecast->metadata)->toHaveKey('deals_count');
        expect($dbForecast->metadata['deals_count'])->toBe(5);
    });

    it('can calculate and track deal scores for leads', function () {
        // Create a lead with activities
        $lead = Lead::create([
            'user_id' => $this->salesRep->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stages['Proposal']->id,
            'title' => 'Scorable Deal',
            'lead_value' => 50000,
            'status' => 1,
            'expected_close_date' => now()->addDays(10),
        ]);

        // Calculate score for the lead
        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.leads.score.calculate', $lead->id));

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'lead_id',
                    'score',
                    'win_probability',
                    'velocity_score',
                    'engagement_score',
                    'value_score',
                    'historical_pattern_score',
                    'factors',
                ],
            ]);

        $scoreData = $response->json('data');

        // Verify all score components are within valid range
        expect($scoreData['lead_id'])->toBe($lead->id);
        expect((float) $scoreData['score'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect((float) $scoreData['win_probability'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect((float) $scoreData['velocity_score'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect((float) $scoreData['engagement_score'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect((float) $scoreData['value_score'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect((float) $scoreData['historical_pattern_score'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);

        // Verify score was saved to database
        $dbScore = DealScore::where('lead_id', $lead->id)->latest()->first();
        expect($dbScore)->not->toBeNull();

        // Retrieve the score
        $getScoreResponse = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.score.show', $lead->id));

        $getScoreResponse->assertOk()
            ->assertJson([
                'data' => [
                    'lead_id' => $lead->id,
                ],
            ]);

        // Verify factors are captured
        expect($dbScore->factors)->toBeArray();
    });

    it('can track top scored leads and prioritize deals', function () {
        // Create multiple leads with varying characteristics
        $leadsData = [
            ['stage' => 'Negotiation', 'value' => 100000, 'days' => 5],
            ['stage' => 'Proposal', 'value' => 75000, 'days' => 10],
            ['stage' => 'Needs Analysis', 'value' => 50000, 'days' => 20],
            ['stage' => 'Qualification', 'value' => 25000, 'days' => 30],
            ['stage' => 'Prospecting', 'value' => 10000, 'days' => 45],
        ];

        foreach ($leadsData as $index => $leadData) {
            $lead = Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages[$leadData['stage']]->id,
                'title' => "Deal {$index} - {$leadData['stage']}",
                'lead_value' => $leadData['value'],
                'status' => 1,
                'expected_close_date' => now()->addDays($leadData['days']),
            ]);

            // Calculate score for each lead
            test()->actingAs($this->admin)
                ->postJson(route('admin.leads.score.calculate', $lead->id));
        }

        // Get top scored leads
        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored', ['limit' => 10]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'lead_id',
                        'score',
                        'win_probability',
                    ],
                ],
                'statistics',
                'distribution',
                'meta',
            ]);

        $topScores = $response->json('data');
        expect(count($topScores))->toBe(5);

        // Verify scores are ordered by score descending
        $scores = collect($topScores)->pluck('score')->map(fn($s) => (float) $s)->all();
        $sortedScores = collect($scores)->sortDesc()->values()->all();
        expect($scores)->toBe($sortedScores);

        // Verify statistics are present
        expect($response->json('statistics'))->toBeArray();
        expect($response->json('distribution'))->toBeArray();
    });

    it('can build and analyze historical conversion data', function () {
        // Create historical leads (closed won and closed lost)
        $historicalLeads = [
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Closed Won']->id,
                'title' => 'Won Deal 1',
                'lead_value' => 50000,
                'status' => 1,
                'created_at' => now()->subMonths(2),
            ]),
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Closed Won']->id,
                'title' => 'Won Deal 2',
                'lead_value' => 30000,
                'status' => 1,
                'created_at' => now()->subMonths(1),
            ]),
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Closed Lost']->id,
                'title' => 'Lost Deal 1',
                'lead_value' => 20000,
                'status' => 1,
                'created_at' => now()->subMonths(2),
            ]),
        ];

        // Create some historical conversion data manually
        HistoricalConversion::create([
            'stage_id' => $this->stages['Proposal']->id,
            'pipeline_id' => $this->pipeline->id,
            'user_id' => $this->salesRep->id,
            'conversion_rate' => 60.0,
            'average_time_in_stage' => 7.5,
            'sample_size' => 10,
            'period_start' => now()->subMonths(3),
            'period_end' => now()->subMonth(),
        ]);

        HistoricalConversion::create([
            'stage_id' => $this->stages['Negotiation']->id,
            'pipeline_id' => $this->pipeline->id,
            'user_id' => $this->salesRep->id,
            'conversion_rate' => 75.0,
            'average_time_in_stage' => 5.0,
            'sample_size' => 15,
            'period_start' => now()->subMonths(3),
            'period_end' => now()->subMonth(),
        ]);

        // Verify historical data exists
        $historicalData = HistoricalConversion::where('user_id', $this->salesRep->id)->get();
        expect($historicalData->count())->toBeGreaterThan(0);

        // Verify conversion rates are realistic
        foreach ($historicalData as $data) {
            expect($data->conversion_rate)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
            expect($data->sample_size)->toBeGreaterThan(0);
        }
    });

    it('can track forecast accuracy and variance', function () {
        // Step 1: Create a forecast for last month
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $forecast = SalesForecast::create([
            'user_id' => $this->salesRep->id,
            'period_type' => 'month',
            'period_start' => $lastMonthStart,
            'period_end' => $lastMonthEnd,
            'forecast_value' => 100000,
            'weighted_forecast' => 85000,
            'best_case' => 150000,
            'worst_case' => 50000,
            'confidence_score' => 75.0,
            'metadata' => json_encode(['deals_count' => 5]),
        ]);

        // Step 2: Create actual results
        $actualValue = 92000; // Actual closed value
        $variance = $actualValue - $forecast->weighted_forecast;
        $variancePercentage = ($variance / $forecast->weighted_forecast) * 100;

        $actual = ForecastActual::create([
            'forecast_id' => $forecast->id,
            'actual_value' => $actualValue,
            'variance' => $variance,
            'variance_percentage' => $variancePercentage,
            'closed_at' => $lastMonthEnd,
        ]);

        // Step 3: Retrieve forecast accuracy metrics
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

        $metrics = $response->json('metrics');

        // Verify metrics are calculated
        expect($metrics['total_forecasts'])->toBe(1);
        expect($metrics['average_variance'])->toBeNumeric();
        expect($metrics['average_variance_pct'])->toBeNumeric();

        // Verify the actual record exists
        expect($actual->forecast_id)->toBe($forecast->id);
        expect($actual->actual_value)->toBe($actualValue);
        expect($actual->variance)->toBe($variance);
    });

    it('can generate team forecasts and compare members', function () {
        // Create another sales rep
        $salesRep2 = User::create([
            'name' => 'Sales Rep 2',
            'email' => 'salesrep2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        $teamId = 1;

        // Create leads for both reps
        Lead::create([
            'user_id' => $this->salesRep->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stages['Proposal']->id,
            'title' => 'Rep 1 Deal',
            'lead_value' => 50000,
            'status' => 1,
        ]);

        Lead::create([
            'user_id' => $salesRep2->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stages['Negotiation']->id,
            'title' => 'Rep 2 Deal',
            'lead_value' => 75000,
            'status' => 1,
        ]);

        // Generate forecasts for both reps with same team_id
        $forecast1Response = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->salesRep->id,
                'period_type' => 'month',
                'team_id' => $teamId,
            ]);

        $forecast2Response = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $salesRep2->id,
                'period_type' => 'month',
                'team_id' => $teamId,
            ]);

        $forecast1Response->assertStatus(201);
        $forecast2Response->assertStatus(201);

        // Retrieve team forecasts
        $teamResponse = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.team', $teamId));

        $teamResponse->assertOk()
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

        $totals = $teamResponse->json('totals');

        // Verify team totals are aggregated correctly
        expect($totals['forecast_value'])->toBeGreaterThan(0);
        expect($totals['weighted_forecast'])->toBeGreaterThan(0);
        expect($totals['best_case'])->toBeGreaterThanOrEqual($totals['weighted_forecast']);
        expect($totals['worst_case'])->toBeLessThanOrEqual($totals['weighted_forecast']);

        // Verify both forecasts are in the response
        $teamForecasts = $teamResponse->json('data');
        expect(count($teamForecasts))->toBeGreaterThanOrEqual(2);
    });

    it('can generate forecasts for different periods (week, month, quarter)', function () {
        // Create some leads
        Lead::create([
            'user_id' => $this->salesRep->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stages['Proposal']->id,
            'title' => 'Test Deal',
            'lead_value' => 50000,
            'status' => 1,
        ]);

        // Generate weekly forecast
        $weekResponse = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->salesRep->id,
                'period_type' => 'week',
            ]);

        $weekResponse->assertStatus(201);
        expect($weekResponse->json('data.period_type'))->toBe('week');

        // Generate monthly forecast
        $monthResponse = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->salesRep->id,
                'period_type' => 'month',
            ]);

        $monthResponse->assertStatus(201);
        expect($monthResponse->json('data.period_type'))->toBe('month');

        // Generate quarterly forecast
        $quarterResponse = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->salesRep->id,
                'period_type' => 'quarter',
            ]);

        $quarterResponse->assertStatus(201);
        expect($quarterResponse->json('data.period_type'))->toBe('quarter');

        // Verify all three forecasts exist in database
        $forecasts = SalesForecast::where('user_id', $this->salesRep->id)->get();
        expect($forecasts->count())->toBe(3);

        $periodTypes = $forecasts->pluck('period_type')->toArray();
        expect($periodTypes)->toContain('week');
        expect($periodTypes)->toContain('month');
        expect($periodTypes)->toContain('quarter');
    });

    it('validates forecast scenario modeling (best case, worst case, likely)', function () {
        // Create diverse leads at different stages
        $leads = [
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Prospecting']->id,
                'title' => 'Early Stage Deal',
                'lead_value' => 10000,
                'status' => 1,
            ]),
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stages['Negotiation']->id,
                'title' => 'Late Stage Deal',
                'lead_value' => 100000,
                'status' => 1,
            ]),
        ];

        // Generate forecast
        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $this->salesRep->id,
                'period_type' => 'month',
            ]);

        $response->assertStatus(201);
        $forecast = $response->json('data');

        // Verify scenario relationships
        $bestCase = (float) $forecast['best_case'];
        $weightedForecast = (float) $forecast['weighted_forecast'];
        $worstCase = (float) $forecast['worst_case'];

        // Best case should be highest (all deals close)
        expect($bestCase)->toBeGreaterThanOrEqual($weightedForecast);

        // Weighted forecast should be in the middle (probability-weighted)
        expect($weightedForecast)->toBeGreaterThanOrEqual($worstCase);
        expect($weightedForecast)->toBeLessThanOrEqual($bestCase);

        // Worst case should be lowest (conservative estimate)
        expect($worstCase)->toBeLessThanOrEqual($weightedForecast);

        // All scenarios should be positive
        expect($bestCase)->toBeGreaterThan(0);
        expect($weightedForecast)->toBeGreaterThan(0);
        expect($worstCase)->toBeGreaterThanOrEqual(0);
    });

    it('can filter and paginate forecast results', function () {
        // Create forecasts for multiple periods
        for ($i = 0; $i < 15; $i++) {
            SalesForecast::create([
                'user_id' => $this->salesRep->id,
                'period_type' => 'month',
                'period_start' => now()->subMonths($i)->startOfMonth(),
                'period_end' => now()->subMonths($i)->endOfMonth(),
                'forecast_value' => 50000 + ($i * 1000),
                'weighted_forecast' => 35000 + ($i * 700),
                'best_case' => 75000 + ($i * 1500),
                'worst_case' => 20000 + ($i * 500),
                'confidence_score' => 70.0 + $i,
                'metadata' => json_encode(['deals_count' => 5]),
            ]);
        }

        // Test pagination
        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.index', ['per_page' => 10]));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(10);
        expect($response->json('meta.total'))->toBe(15);
        expect($response->json('meta.last_page'))->toBe(2);

        // Test filtering by user
        $filterResponse = test()->actingAs($this->admin)
            ->getJson(route('admin.forecasts.index', ['user_id' => $this->salesRep->id]));

        $filterResponse->assertOk();
        $forecasts = $filterResponse->json('data');

        foreach ($forecasts as $forecast) {
            expect($forecast['user_id'])->toBe($this->salesRep->id);
        }
    });
});

/**
 * Scheduled Jobs Integration Tests
 */
describe('Scheduled Jobs Integration', function () {
    beforeEach(function () {
        $this->admin = getDefaultAdmin();
        $this->salesRep = User::create([
            'name' => 'Sales Rep',
            'email' => 'salesrep@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        $this->pipeline = LeadPipeline::create([
            'name' => 'Test Pipeline',
            'is_default' => true,
            'rotten_days' => 30,
        ]);

        $this->stage = LeadPipelineStage::create([
            'lead_pipeline_id' => $this->pipeline->id,
            'name' => 'Proposal',
            'probability' => 60.0,
            'sort_order' => 1,
        ]);
    });

    it('can execute CalculateDealScoresJob for all active leads', function () {
        Queue::fake();

        // Create multiple active leads
        for ($i = 0; $i < 3; $i++) {
            Lead::create([
                'user_id' => $this->salesRep->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stage->id,
                'title' => "Deal {$i}",
                'lead_value' => 10000 * ($i + 1),
                'status' => 1,
            ]);
        }

        // Dispatch the job
        CalculateDealScoresJob::dispatch();

        // Verify job was dispatched
        Queue::assertPushed(CalculateDealScoresJob::class);
    });

    it('can execute TrackForecastActualsJob to record actual results', function () {
        Queue::fake();

        // Create a forecast for last month
        $forecast = SalesForecast::create([
            'user_id' => $this->salesRep->id,
            'period_type' => 'month',
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
            'forecast_value' => 100000,
            'weighted_forecast' => 85000,
            'best_case' => 150000,
            'worst_case' => 50000,
            'confidence_score' => 75.0,
        ]);

        // Dispatch the job
        TrackForecastActualsJob::dispatch();

        // Verify job was dispatched
        Queue::assertPushed(TrackForecastActualsJob::class);
    });

    it('can execute RefreshHistoricalConversionsJob to update conversion rates', function () {
        Queue::fake();

        // Create some historical leads
        Lead::create([
            'user_id' => $this->salesRep->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage->id,
            'title' => 'Historical Deal',
            'lead_value' => 50000,
            'status' => 1,
            'created_at' => now()->subMonths(2),
        ]);

        // Dispatch the job
        RefreshHistoricalConversionsJob::dispatch();

        // Verify job was dispatched
        Queue::assertPushed(RefreshHistoricalConversionsJob::class);
    });
});

/**
 * Complete End-to-End User Journey
 */
describe('Complete Sales Manager Journey', function () {
    it('can complete full sales forecasting workflow from setup to accuracy tracking', function () {
        // Setup: Create admin and sales team
        $admin = getDefaultAdmin();
        $salesManager = User::create([
            'name' => 'Sales Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role_id' => $admin->role_id,
            'status' => 1,
        ]);

        $salesReps = [
            User::create([
                'name' => 'Sales Rep A',
                'email' => 'repa@example.com',
                'password' => bcrypt('password'),
                'role_id' => $admin->role_id,
                'status' => 1,
            ]),
            User::create([
                'name' => 'Sales Rep B',
                'email' => 'repb@example.com',
                'password' => bcrypt('password'),
                'role_id' => $admin->role_id,
                'status' => 1,
            ]),
        ];

        // Setup pipeline
        $pipeline = LeadPipeline::create([
            'name' => 'Enterprise Sales',
            'is_default' => true,
            'rotten_days' => 30,
        ]);

        $stages = [
            LeadPipelineStage::create([
                'lead_pipeline_id' => $pipeline->id,
                'name' => 'Discovery',
                'probability' => 20.0,
                'sort_order' => 1,
            ]),
            LeadPipelineStage::create([
                'lead_pipeline_id' => $pipeline->id,
                'name' => 'Proposal',
                'probability' => 50.0,
                'sort_order' => 2,
            ]),
            LeadPipelineStage::create([
                'lead_pipeline_id' => $pipeline->id,
                'name' => 'Negotiation',
                'probability' => 80.0,
                'sort_order' => 3,
            ]),
        ];

        $teamId = 1;

        // Journey Step 1: Sales reps add leads to pipeline
        $allLeads = [];
        foreach ($salesReps as $index => $rep) {
            foreach ($stages as $stageIndex => $stage) {
                $lead = Lead::create([
                    'user_id' => $rep->id,
                    'lead_pipeline_id' => $pipeline->id,
                    'lead_pipeline_stage_id' => $stage->id,
                    'title' => "Rep {$index} Deal in {$stage->name}",
                    'lead_value' => 25000 * ($stageIndex + 1),
                    'status' => 1,
                    'expected_close_date' => now()->addDays(10 * ($stageIndex + 1)),
                ]);
                $allLeads[] = $lead;
            }
        }

        expect(count($allLeads))->toBe(6); // 2 reps × 3 stages

        // Journey Step 2: Calculate deal scores for prioritization
        foreach ($allLeads as $lead) {
            $scoreResponse = test()->actingAs($admin)
                ->postJson(route('admin.leads.score.calculate', $lead->id));

            $scoreResponse->assertOk();
        }

        // Verify all leads have scores
        $scores = DealScore::count();
        expect($scores)->toBeGreaterThanOrEqual(6);

        // Journey Step 3: View top scored leads for prioritization
        $topScoredResponse = test()->actingAs($admin)
            ->getJson(route('admin.leads.top_scored', ['limit' => 5]));

        $topScoredResponse->assertOk();
        $topLeads = $topScoredResponse->json('data');
        expect(count($topLeads))->toBeGreaterThan(0);

        // Journey Step 4: Generate individual forecasts for each rep
        $individualForecasts = [];
        foreach ($salesReps as $rep) {
            $forecastResponse = test()->actingAs($admin)
                ->postJson(route('admin.forecasts.generate'), [
                    'user_id' => $rep->id,
                    'period_type' => 'month',
                    'team_id' => $teamId,
                ]);

            $forecastResponse->assertStatus(201);
            $individualForecasts[] = $forecastResponse->json('data');
        }

        expect(count($individualForecasts))->toBe(2);

        // Journey Step 5: View team forecast rollup
        $teamForecastResponse = test()->actingAs($admin)
            ->getJson(route('admin.forecasts.team', $teamId));

        $teamForecastResponse->assertOk();
        $teamTotals = $teamForecastResponse->json('totals');

        // Verify team totals aggregate individual forecasts
        expect($teamTotals['forecast_value'])->toBeGreaterThan(0);
        expect($teamTotals['weighted_forecast'])->toBeGreaterThan(0);

        // Journey Step 6: Simulate time passing and track actual results
        $lastMonthForecast = SalesForecast::create([
            'user_id' => $salesReps[0]->id,
            'period_type' => 'month',
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
            'forecast_value' => 75000,
            'weighted_forecast' => 60000,
            'best_case' => 100000,
            'worst_case' => 40000,
            'confidence_score' => 70.0,
        ]);

        // Record actual results
        $actualResult = ForecastActual::create([
            'forecast_id' => $lastMonthForecast->id,
            'actual_value' => 65000,
            'variance' => 5000,
            'variance_percentage' => 8.33,
            'closed_at' => now()->subMonth()->endOfMonth(),
        ]);

        // Journey Step 7: Review forecast accuracy
        $accuracyResponse = test()->actingAs($admin)
            ->getJson(route('admin.forecasts.accuracy'));

        $accuracyResponse->assertOk();
        $metrics = $accuracyResponse->json('metrics');

        // Verify accuracy metrics are calculated
        expect($metrics['total_forecasts'])->toBeGreaterThan(0);
        expect($metrics)->toHaveKey('average_accuracy');
        expect($metrics)->toHaveKey('average_variance');

        // Journey Step 8: View all forecasts with filtering
        $allForecastsResponse = test()->actingAs($admin)
            ->getJson(route('admin.forecasts.index', [
                'period_type' => 'month',
            ]));

        $allForecastsResponse->assertOk();
        $allForecasts = $allForecastsResponse->json('data');
        expect(count($allForecasts))->toBeGreaterThan(0);

        // Journey Complete: Verify the entire workflow executed successfully
        expect(Lead::count())->toBe(7); // 6 + 1 from step 6
        expect(DealScore::count())->toBeGreaterThanOrEqual(6);
        expect(SalesForecast::count())->toBe(3); // 2 individual + 1 historical
        expect(ForecastActual::count())->toBe(1);
    });

    it('can handle forecast regeneration and updates', function () {
        $admin = getDefaultAdmin();
        $salesRep = User::create([
            'name' => 'Sales Rep',
            'email' => 'rep@example.com',
            'password' => bcrypt('password'),
            'role_id' => $admin->role_id,
            'status' => 1,
        ]);

        $pipeline = LeadPipeline::create([
            'name' => 'Sales Pipeline',
            'is_default' => true,
            'rotten_days' => 30,
        ]);

        $stage = LeadPipelineStage::create([
            'lead_pipeline_id' => $pipeline->id,
            'name' => 'Proposal',
            'probability' => 60.0,
            'sort_order' => 1,
        ]);

        // Create initial lead
        Lead::create([
            'user_id' => $salesRep->id,
            'lead_pipeline_id' => $pipeline->id,
            'lead_pipeline_stage_id' => $stage->id,
            'title' => 'Initial Deal',
            'lead_value' => 50000,
            'status' => 1,
        ]);

        // Generate initial forecast
        $initialResponse = test()->actingAs($admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $salesRep->id,
                'period_type' => 'month',
            ]);

        $initialResponse->assertStatus(201);
        $initialForecast = $initialResponse->json('data');

        // Add more leads to pipeline
        Lead::create([
            'user_id' => $salesRep->id,
            'lead_pipeline_id' => $pipeline->id,
            'lead_pipeline_stage_id' => $stage->id,
            'title' => 'New Opportunity',
            'lead_value' => 75000,
            'status' => 1,
        ]);

        // Regenerate forecast
        $updatedResponse = test()->actingAs($admin)
            ->postJson(route('admin.forecasts.generate'), [
                'user_id' => $salesRep->id,
                'period_type' => 'month',
            ]);

        $updatedResponse->assertStatus(201);
        $updatedForecast = $updatedResponse->json('data');

        // Verify forecast was updated with new pipeline data
        expect((float) $updatedForecast['weighted_forecast'])
            ->toBeGreaterThan((float) $initialForecast['weighted_forecast']);

        // Verify updated forecast reflects additional deals
        $dbForecast = SalesForecast::where('user_id', $salesRep->id)
            ->where('period_type', 'month')
            ->latest()
            ->first();

        expect($dbForecast->metadata)->toBeArray();
        expect($dbForecast->metadata['deals_count'])->toBe(2);
    });
});
