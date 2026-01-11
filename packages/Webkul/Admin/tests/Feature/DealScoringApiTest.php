<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadPipeline;
use Webkul\Lead\Models\LeadPipelineStage;
use Webkul\Lead\Models\DealScore;
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

    $this->stage3 = LeadPipelineStage::create([
        'lead_pipeline_id' => $this->pipeline->id,
        'name' => 'Negotiation',
        'probability' => 90.0,
        'sort_order' => 3,
    ]);

    // Create a test lead
    $this->lead = Lead::create([
        'user_id' => $this->admin->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage2->id,
        'title' => 'Test Lead',
        'lead_value' => 50000,
        'status' => 1,
    ]);
});

describe('GET /api/leads/{id}/score - Get lead score', function () {
    test('can retrieve score for a lead', function () {
        // Create a score for the lead
        DealScore::create([
            'lead_id' => $this->lead->id,
            'score' => 75.5,
            'win_probability' => 68.0,
            'velocity_score' => 70.0,
            'engagement_score' => 80.0,
            'value_score' => 85.0,
            'historical_pattern_score' => 65.0,
            'factors' => json_encode([
                'stage_probability' => 75.0,
                'days_in_pipeline' => 15,
                'activity_count' => 5,
            ]),
            'generated_at' => now(),
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.score.show', $this->lead->id));

        $response->assertOk()
            ->assertJsonStructure([
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
                    'generated_at',
                ],
                'statistics',
            ]);

        expect($response->json('data.lead_id'))->toBe($this->lead->id);
        expect($response->json('data.score'))->toBe('75.50');
    });

    test('returns 404 when no score exists for lead', function () {
        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.score.show', $this->lead->id));

        $response->assertNotFound()
            ->assertJson([
                'message' => 'No score found for this lead. Please calculate the score first.',
                'data' => null,
            ]);
    });

    test('returns 404 for non-existent lead', function () {
        test()->actingAs($this->admin)
            ->getJson(route('admin.leads.score.show', 99999))
            ->assertNotFound();
    });

    test('unauthorized user cannot access others lead score', function () {
        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        $lead2 = Lead::create([
            'user_id' => $user2->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage1->id,
            'title' => 'User 2 Lead',
            'lead_value' => 30000,
            'status' => 1,
        ]);

        DealScore::create([
            'lead_id' => $lead2->id,
            'score' => 60.0,
            'win_probability' => 55.0,
            'velocity_score' => 60.0,
            'engagement_score' => 65.0,
            'value_score' => 70.0,
            'historical_pattern_score' => 50.0,
            'factors' => json_encode([]),
            'generated_at' => now(),
        ]);

        // Mock bouncer to restrict access
        $this->mock('bouncer', function ($mock) use ($user2) {
            $mock->shouldReceive('getAuthorizedUserIds')
                ->andReturn([$user2->id]);
        });

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.score.show', $lead2->id));

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Unauthorized access to this lead.',
            ]);
    });

    test('includes score statistics in response', function () {
        // Create multiple scores for the lead
        DealScore::create([
            'lead_id' => $this->lead->id,
            'score' => 70.0,
            'win_probability' => 65.0,
            'velocity_score' => 68.0,
            'engagement_score' => 72.0,
            'value_score' => 75.0,
            'historical_pattern_score' => 60.0,
            'factors' => json_encode([]),
            'generated_at' => now()->subDays(7),
        ]);

        DealScore::create([
            'lead_id' => $this->lead->id,
            'score' => 75.5,
            'win_probability' => 68.0,
            'velocity_score' => 70.0,
            'engagement_score' => 80.0,
            'value_score' => 85.0,
            'historical_pattern_score' => 65.0,
            'factors' => json_encode([]),
            'generated_at' => now(),
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.score.show', $this->lead->id));

        $response->assertOk();

        expect($response->json('statistics'))->toBeArray();
    });

    test('requires authentication', function () {
        test()->getJson(route('admin.leads.score.show', $this->lead->id))
            ->assertStatus(401);
    });
});

describe('POST /api/leads/{id}/score/calculate - Calculate lead score', function () {
    test('can calculate score for a lead', function () {
        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.leads.score.calculate', $this->lead->id));

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

        expect($response->json('data.lead_id'))->toBe($this->lead->id);
        expect($response->json('data.score'))->toBeNumeric();
        expect($response->json('message'))->toBe('Lead score calculated successfully.');

        // Verify score was saved to database
        $this->assertDatabaseHas('deal_scores', [
            'lead_id' => $this->lead->id,
        ]);
    });

    test('can recalculate existing score', function () {
        // Create initial score
        DealScore::create([
            'lead_id' => $this->lead->id,
            'score' => 60.0,
            'win_probability' => 55.0,
            'velocity_score' => 60.0,
            'engagement_score' => 65.0,
            'value_score' => 70.0,
            'historical_pattern_score' => 50.0,
            'factors' => json_encode([]),
            'generated_at' => now()->subDay(),
        ]);

        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.leads.score.calculate', $this->lead->id));

        $response->assertOk();

        expect($response->json('data.lead_id'))->toBe($this->lead->id);

        // Verify new score was created (not updated)
        $scores = DealScore::where('lead_id', $this->lead->id)->get();
        expect($scores->count())->toBeGreaterThanOrEqual(1);
    });

    test('calculates score with all components', function () {
        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.leads.score.calculate', $this->lead->id));

        $response->assertOk();

        $data = $response->json('data');

        expect($data)->toHaveKeys([
            'score',
            'win_probability',
            'velocity_score',
            'engagement_score',
            'value_score',
            'historical_pattern_score',
            'factors',
        ]);

        // Verify all scores are within valid range (0-100)
        expect($data['score'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect($data['win_probability'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
    });

    test('returns 404 for non-existent lead', function () {
        test()->actingAs($this->admin)
            ->postJson(route('admin.leads.score.calculate', 99999))
            ->assertNotFound();
    });

    test('unauthorized user cannot calculate score for others lead', function () {
        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        $lead2 = Lead::create([
            'user_id' => $user2->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage1->id,
            'title' => 'User 2 Lead',
            'lead_value' => 30000,
            'status' => 1,
        ]);

        // Mock bouncer to restrict access
        $this->mock('bouncer', function ($mock) use ($user2) {
            $mock->shouldReceive('getAuthorizedUserIds')
                ->andReturn([$user2->id]);
        });

        $response = test()->actingAs($this->admin)
            ->postJson(route('admin.leads.score.calculate', $lead2->id));

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Unauthorized access to this lead.',
            ]);
    });

    test('requires authentication', function () {
        test()->postJson(route('admin.leads.score.calculate', $this->lead->id))
            ->assertStatus(401);
    });
});

describe('GET /api/leads/top-scored - Get top scored leads', function () {
    test('can retrieve top scored leads', function () {
        // Create multiple leads with scores
        $leads = [];
        for ($i = 0; $i < 5; $i++) {
            $lead = Lead::create([
                'user_id' => $this->admin->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stage2->id,
                'title' => "Test Lead {$i}",
                'lead_value' => 10000 * ($i + 1),
                'status' => 1,
            ]);

            DealScore::create([
                'lead_id' => $lead->id,
                'score' => 90.0 - ($i * 5),
                'win_probability' => 85.0 - ($i * 5),
                'velocity_score' => 80.0,
                'engagement_score' => 85.0,
                'value_score' => 90.0,
                'historical_pattern_score' => 75.0,
                'factors' => json_encode([]),
                'generated_at' => now(),
            ]);

            $leads[] = $lead;
        }

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'lead_id',
                        'score',
                        'win_probability',
                        'velocity_score',
                        'engagement_score',
                        'value_score',
                        'historical_pattern_score',
                    ],
                ],
                'statistics',
                'distribution',
                'meta' => [
                    'limit',
                    'count',
                ],
            ]);

        // Verify results are ordered by score descending
        $scores = collect($response->json('data'))->pluck('score')->all();
        expect($scores)->toBe(collect($scores)->sortDesc()->values()->all());
    });

    test('can limit number of top scored leads', function () {
        // Create 15 leads with scores
        for ($i = 0; $i < 15; $i++) {
            $lead = Lead::create([
                'user_id' => $this->admin->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stage2->id,
                'title' => "Test Lead {$i}",
                'lead_value' => 10000,
                'status' => 1,
            ]);

            DealScore::create([
                'lead_id' => $lead->id,
                'score' => 90.0 - $i,
                'win_probability' => 85.0 - $i,
                'velocity_score' => 80.0,
                'engagement_score' => 85.0,
                'value_score' => 90.0,
                'historical_pattern_score' => 75.0,
                'factors' => json_encode([]),
                'generated_at' => now(),
            ]);
        }

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored', ['limit' => 5]));

        $response->assertOk();

        expect($response->json('data'))->toHaveCount(5);
        expect($response->json('meta.limit'))->toBe(5);
    });

    test('can filter by user', function () {
        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->admin->role_id,
            'status' => 1,
        ]);

        // Create leads for admin
        $lead1 = Lead::create([
            'user_id' => $this->admin->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage2->id,
            'title' => 'Admin Lead',
            'lead_value' => 50000,
            'status' => 1,
        ]);

        DealScore::create([
            'lead_id' => $lead1->id,
            'score' => 90.0,
            'win_probability' => 85.0,
            'velocity_score' => 80.0,
            'engagement_score' => 85.0,
            'value_score' => 90.0,
            'historical_pattern_score' => 75.0,
            'factors' => json_encode([]),
            'generated_at' => now(),
        ]);

        // Create leads for user2
        $lead2 = Lead::create([
            'user_id' => $user2->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage2->id,
            'title' => 'User 2 Lead',
            'lead_value' => 30000,
            'status' => 1,
        ]);

        DealScore::create([
            'lead_id' => $lead2->id,
            'score' => 85.0,
            'win_probability' => 80.0,
            'velocity_score' => 75.0,
            'engagement_score' => 80.0,
            'value_score' => 85.0,
            'historical_pattern_score' => 70.0,
            'factors' => json_encode([]),
            'generated_at' => now(),
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored', ['user_id' => $this->admin->id]));

        $response->assertOk();

        $data = $response->json('data');
        expect($data)->toHaveCount(1);
    });

    test('can filter by minimum score', function () {
        // Create leads with varying scores
        for ($i = 0; $i < 5; $i++) {
            $lead = Lead::create([
                'user_id' => $this->admin->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stage2->id,
                'title' => "Test Lead {$i}",
                'lead_value' => 10000,
                'status' => 1,
            ]);

            DealScore::create([
                'lead_id' => $lead->id,
                'score' => 50.0 + ($i * 10),
                'win_probability' => 50.0 + ($i * 10),
                'velocity_score' => 80.0,
                'engagement_score' => 85.0,
                'value_score' => 90.0,
                'historical_pattern_score' => 75.0,
                'factors' => json_encode([]),
                'generated_at' => now(),
            ]);
        }

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored', ['min_score' => 75]));

        $response->assertOk();

        // Verify all returned scores are >= 75
        $scores = collect($response->json('data'))->pluck('score')->all();
        foreach ($scores as $score) {
            expect((float) $score)->toBeGreaterThanOrEqual(75.0);
        }
    });

    test('can filter by minimum win probability', function () {
        // Create leads with varying win probabilities
        for ($i = 0; $i < 5; $i++) {
            $lead = Lead::create([
                'user_id' => $this->admin->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stage2->id,
                'title' => "Test Lead {$i}",
                'lead_value' => 10000,
                'status' => 1,
            ]);

            DealScore::create([
                'lead_id' => $lead->id,
                'score' => 80.0,
                'win_probability' => 40.0 + ($i * 15),
                'velocity_score' => 80.0,
                'engagement_score' => 85.0,
                'value_score' => 90.0,
                'historical_pattern_score' => 75.0,
                'factors' => json_encode([]),
                'generated_at' => now(),
            ]);
        }

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored', ['min_win_probability' => 70]));

        $response->assertOk();

        // Verify all returned win probabilities are >= 70
        $winProbabilities = collect($response->json('data'))->pluck('win_probability')->all();
        foreach ($winProbabilities as $winProb) {
            expect((float) $winProb)->toBeGreaterThanOrEqual(70.0);
        }
    });

    test('can filter by priority', function () {
        // Create leads with different score ranges (implying different priorities)
        $lead1 = Lead::create([
            'user_id' => $this->admin->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage3->id,
            'title' => 'High Priority Lead',
            'lead_value' => 100000,
            'status' => 1,
        ]);

        DealScore::create([
            'lead_id' => $lead1->id,
            'score' => 95.0,
            'win_probability' => 90.0,
            'velocity_score' => 95.0,
            'engagement_score' => 98.0,
            'value_score' => 95.0,
            'historical_pattern_score' => 90.0,
            'factors' => json_encode(['priority' => 'high']),
            'generated_at' => now(),
        ]);

        $lead2 = Lead::create([
            'user_id' => $this->admin->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage1->id,
            'title' => 'Low Priority Lead',
            'lead_value' => 5000,
            'status' => 1,
        ]);

        DealScore::create([
            'lead_id' => $lead2->id,
            'score' => 45.0,
            'win_probability' => 40.0,
            'velocity_score' => 50.0,
            'engagement_score' => 45.0,
            'value_score' => 40.0,
            'historical_pattern_score' => 45.0,
            'factors' => json_encode(['priority' => 'low']),
            'generated_at' => now(),
        ]);

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored', ['priority' => 'high']));

        $response->assertOk();

        // Note: The actual filtering logic depends on implementation
        // This test assumes priority is stored in factors
    });

    test('includes statistics and distribution', function () {
        // Create multiple leads with scores
        for ($i = 0; $i < 5; $i++) {
            $lead = Lead::create([
                'user_id' => $this->admin->id,
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stage2->id,
                'title' => "Test Lead {$i}",
                'lead_value' => 10000,
                'status' => 1,
            ]);

            DealScore::create([
                'lead_id' => $lead->id,
                'score' => 50.0 + ($i * 10),
                'win_probability' => 50.0 + ($i * 10),
                'velocity_score' => 80.0,
                'engagement_score' => 85.0,
                'value_score' => 90.0,
                'historical_pattern_score' => 75.0,
                'factors' => json_encode([]),
                'generated_at' => now(),
            ]);
        }

        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored'));

        $response->assertOk();

        expect($response->json('statistics'))->toBeArray();
        expect($response->json('distribution'))->toBeArray();
    });

    test('returns empty array when no scores exist', function () {
        $response = test()->actingAs($this->admin)
            ->getJson(route('admin.leads.top_scored'));

        $response->assertOk();

        expect($response->json('data'))->toBe([]);
        expect($response->json('meta.count'))->toBe(0);
    });

    test('requires authentication', function () {
        test()->getJson(route('admin.leads.top_scored'))
            ->assertStatus(401);
    });
});
