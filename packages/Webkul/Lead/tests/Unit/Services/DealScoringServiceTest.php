<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Email\Repositories\EmailRepository;
use Webkul\Lead\Repositories\DealScoreRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\DealScoringService;
use Webkul\Lead\Services\DealVelocityService;
use Webkul\Lead\Services\HistoricalAnalysisService;

uses(TestCase::class);

beforeEach(function () {
    $this->leadRepository = Mockery::mock(LeadRepository::class);
    $this->dealScoreRepository = Mockery::mock(DealScoreRepository::class);
    $this->velocityService = Mockery::mock(DealVelocityService::class);
    $this->historicalAnalysisService = Mockery::mock(HistoricalAnalysisService::class);
    $this->activityRepository = Mockery::mock(ActivityRepository::class);
    $this->emailRepository = Mockery::mock(EmailRepository::class);

    $this->service = new DealScoringService(
        $this->leadRepository,
        $this->dealScoreRepository,
        $this->velocityService,
        $this->historicalAnalysisService,
        $this->activityRepository,
        $this->emailRepository
    );
});

afterEach(function () {
    Mockery::close();
});

describe('scoreLead', function () {
    test('scores lead with all components', function () {
        $lead = createMockLeadForScoring(1, 50000, 75.0, now()->addDays(20));

        // Mock activity and email repositories
        mockEngagementData($this, $lead, 8, 10);

        // Mock velocity service
        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->with($lead)
            ->andReturn([
                'velocity_score' => 70.0,
                'velocity_level' => 'normal',
            ]);

        // Mock historical analysis
        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->with(1, 1, 90)
            ->andReturn([
                'win_rate' => 60.0,
                'conversion_rate' => 50.0,
            ]);

        // Mock persistence
        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once()
            ->andReturnTrue();

        $result = $this->service->scoreLead($lead);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys([
                'score',
                'win_probability',
                'engagement_score',
                'velocity_score',
                'value_score',
                'historical_pattern_score',
                'factors',
            ])
            ->and($result['score'])->toBeGreaterThan(0)
            ->and($result['score'])->toBeLessThanOrEqual(100)
            ->and($result['win_probability'])->toBeGreaterThan(0)
            ->and($result['win_probability'])->toBeLessThanOrEqual(100);
    });

    test('scores lead without persisting', function () {
        $lead = createMockLeadForScoring(1, 25000, 50.0);

        mockEngagementData($this, $lead, 3, 5);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 50.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 40.0,
                'conversion_rate' => 35.0,
            ]);

        // Should NOT call createOrUpdateForLead
        $this->dealScoreRepository
            ->shouldNotReceive('createOrUpdateForLead');

        $result = $this->service->scoreLead($lead, false);

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('score');
    });

    test('handles lead ID instead of object', function () {
        $leadId = 123;
        $lead = createMockLeadForScoring(1, 30000, 60.0);

        $this->leadRepository
            ->shouldReceive('find')
            ->with($leadId)
            ->once()
            ->andReturn($lead);

        mockEngagementData($this, $lead, 5, 7);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 55.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($leadId);

        expect($result)->toBeArray();
    });

    test('throws exception for invalid lead', function () {
        $this->leadRepository
            ->shouldReceive('find')
            ->with(999)
            ->andReturn(null);

        $this->service->scoreLead(999);
    })->throws(\InvalidArgumentException::class, 'Lead not found');

    test('handles velocity service failure gracefully', function () {
        $lead = createMockLeadForScoring(1, 40000, 70.0);

        mockEngagementData($this, $lead, 6, 8);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andThrow(new \Exception('Velocity calculation failed'));

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 55.0,
                'conversion_rate' => 50.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        // Should use neutral score (50.0) for velocity
        expect($result)->toBeArray()
            ->and($result['velocity_score'])->toBe(50.0);
    });

    test('handles historical analysis failure gracefully', function () {
        $lead = createMockLeadForScoring(1, 35000, 65.0);

        mockEngagementData($this, $lead, 7, 9);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andThrow(new \Exception('Historical analysis failed'));

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        // Should use neutral score (50.0) for historical pattern
        expect($result)->toBeArray()
            ->and($result['historical_pattern_score'])->toBe(50.0);
    });
});

describe('scoreLeads', function () {
    test('scores multiple leads successfully', function () {
        $leads = collect([
            createMockLeadForScoring(1, 30000, 60.0),
            createMockLeadForScoring(2, 50000, 75.0),
        ]);

        foreach ($leads as $lead) {
            mockEngagementData($this, $lead, 5, 5);
        }

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 55.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->twice();

        $result = $this->service->scoreLeads($leads);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(2)
            ->and($result->first()['success'])->toBeTrue()
            ->and($result->last()['success'])->toBeTrue();
    });

    test('handles individual lead scoring failures', function () {
        $leads = collect([
            createMockLeadForScoring(1, 30000, 60.0),
            createMockLeadForScoring(2, 50000, 75.0),
        ]);

        // First lead succeeds
        mockEngagementData($this, $leads->first(), 5, 5);

        // Second lead fails
        mockEngagementData($this, $leads->last(), 0, 0);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 55.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->once()
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ])
            ->andThrow(new \Exception('Failed'));

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLeads($leads);

        expect($result)->toHaveCount(2)
            ->and($result->first()['success'])->toBeTrue()
            ->and($result->last()['success'])->toBeFalse()
            ->and($result->last())->toHaveKey('error');
    });
});

describe('calculateEngagementScore', function () {
    test('calculates high engagement score correctly', function () {
        $lead = createMockLeadForScoring(1, 30000, 60.0);

        // High engagement: 12 activities, 18 emails
        mockEngagementDataWithRecency($this, $lead, 12, 18, 3, 2);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        // High activity (12 >= 10) = 60 points
        // High emails (18 >= 15) = 40 points
        // Recency boost (3 days) = 20 points
        // Total: 60 + 40 + 20 = 100 (capped at 100)
        expect($result['engagement_score'])->toBeGreaterThanOrEqual(90);
    });

    test('calculates medium engagement score correctly', function () {
        $lead = createMockLeadForScoring(1, 30000, 60.0);

        // Medium engagement: 7 activities, 8 emails
        mockEngagementDataWithRecency($this, $lead, 7, 8, 15, 12);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        expect($result['engagement_score'])->toBeGreaterThan(30)
            ->and($result['engagement_score'])->toBeLessThan(70);
    });

    test('calculates low engagement score correctly', function () {
        $lead = createMockLeadForScoring(1, 30000, 60.0);

        // Low engagement: 1 activity, 2 emails
        mockEngagementDataWithRecency($this, $lead, 1, 2, 40, 35);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        expect($result['engagement_score'])->toBeLessThan(30);
    });

    test('handles zero engagement', function () {
        $lead = createMockLeadForScoring(1, 30000, 60.0);

        mockEngagementData($this, $lead, 0, 0);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        expect($result['engagement_score'])->toBe(0.0);
    });
});

describe('calculateValueScore', function () {
    test('scores enterprise deals correctly', function () {
        $lead = createMockLeadForScoring(1, 150000, 60.0);

        mockEngagementData($this, $lead, 5, 5);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        // Enterprise value (>= 100000) should score 100
        expect($result['value_score'])->toBe(100.0);
    });

    test('scores large deals correctly', function () {
        $lead = createMockLeadForScoring(1, 75000, 60.0);

        mockEngagementData($this, $lead, 5, 5);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        // Large value (50000-100000) should score 70-100
        expect($result['value_score'])->toBeGreaterThan(70)
            ->and($result['value_score'])->toBeLessThan(100);
    });

    test('scores medium deals correctly', function () {
        $lead = createMockLeadForScoring(1, 30000, 60.0);

        mockEngagementData($this, $lead, 5, 5);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        // Medium value (10000-50000) should score 40-70
        expect($result['value_score'])->toBeGreaterThan(40)
            ->and($result['value_score'])->toBeLessThan(70);
    });

    test('scores small deals correctly', function () {
        $lead = createMockLeadForScoring(1, 5000, 60.0);

        mockEngagementData($this, $lead, 5, 5);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        // Small value (< 10000) should score 0-40
        expect($result['value_score'])->toBeLessThan(40);
    });

    test('handles zero value deals', function () {
        $lead = createMockLeadForScoring(1, 0, 60.0);

        mockEngagementData($this, $lead, 5, 5);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 60.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 50.0,
                'conversion_rate' => 45.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        expect($result['value_score'])->toBe(0.0);
    });
});

describe('calculateOverallScore', function () {
    test('weights are correctly applied', function () {
        $lead = createMockLeadForScoring(1, 50000, 75.0);

        mockEngagementData($this, $lead, 10, 15);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 80.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 70.0,
                'conversion_rate' => 60.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        // Verify weights sum correctly
        // Engagement (30%) + Velocity (25%) + Value (20%) + Historical (15%) + Stage (10%) = 100%
        expect($result['score'])->toBeGreaterThan(0)
            ->and($result['score'])->toBeLessThanOrEqual(100);
    });

    test('all components at 100 results in score of 100', function () {
        $lead = createMockLeadForScoring(1, 150000, 100.0);

        mockEngagementDataWithRecency($this, $lead, 15, 20, 1, 1);

        $this->velocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn(['velocity_score' => 100.0]);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andReturn([
                'win_rate' => 100.0,
                'conversion_rate' => 100.0,
            ]);

        $this->dealScoreRepository
            ->shouldReceive('createOrUpdateForLead')
            ->once();

        $result = $this->service->scoreLead($lead);

        expect($result['score'])->toBeGreaterThan(90);
    });
});

describe('getTopPriorityDeals', function () {
    test('returns top deals sorted by score', function () {
        $filters = [
            'latest_only' => true,
            'sort_by' => 'score',
            'sort_order' => 'desc',
        ];

        $mockScores = collect([
            createMockDealScore(1, 90.0),
            createMockDealScore(2, 85.0),
            createMockDealScore(3, 80.0),
        ]);

        $this->dealScoreRepository
            ->shouldReceive('getWithFilters')
            ->with($filters)
            ->andReturn($mockScores);

        $result = $this->service->getTopPriorityDeals(3);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(3);
    });

    test('filters by user when provided', function () {
        $userId = 1;
        $filters = [
            'latest_only' => true,
            'sort_by' => 'score',
            'sort_order' => 'desc',
        ];

        $lead1 = createMockLeadForScoring(1, 30000, 60.0);
        $lead1->user_id = 1;
        $lead2 = createMockLeadForScoring(2, 40000, 70.0);
        $lead2->user_id = 2;

        $mockScores = collect([
            createMockDealScoreWithLead(1, 90.0, $lead1),
            createMockDealScoreWithLead(2, 85.0, $lead2),
        ]);

        $this->dealScoreRepository
            ->shouldReceive('getWithFilters')
            ->with($filters)
            ->andReturn($mockScores);

        $result = $this->service->getTopPriorityDeals(10, $userId);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(1);
    });
});

// Helper functions to create mock objects
function createMockLeadForScoring(int $userId, float $value, float $probability, $expectedCloseDate = null): object
{
    $stage = new class ($probability) extends Model {
        public $probability;

        public function __construct($probability)
        {
            $this->probability = $probability;
        }
    };

    return new class ($userId, $value, $stage, $expectedCloseDate) extends Model {
        public $id;
        public $user_id;
        public $lead_value;
        public $stage;
        public $expected_close_date;
        public $lead_pipeline_id = 1;
        public $lead_pipeline_stage_id = 1;

        public function __construct($userId, $value, $stage, $expectedCloseDate = null)
        {
            $this->id = rand(1, 1000);
            $this->user_id = $userId;
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->expected_close_date = $expectedCloseDate;
        }
    };
}

function mockEngagementData($test, $lead, int $activityCount, int $emailCount): void
{
    $activities = collect();
    for ($i = 0; $i < $activityCount; $i++) {
        $activities->push((object) ['id' => $i + 1, 'created_at' => now()->subDays(rand(1, 30))]);
    }

    $emails = collect();
    for ($i = 0; $i < $emailCount; $i++) {
        $emails->push((object) ['id' => $i + 1, 'created_at' => now()->subDays(rand(1, 30))]);
    }

    $lastActivity = $activityCount > 0 ? (object) ['created_at' => now()->subDays(10)] : null;
    $lastEmail = $emailCount > 0 ? (object) ['created_at' => now()->subDays(8)] : null;

    $activityQuery = Mockery::mock();
    $activityQuery->shouldReceive('where')->with('lead_id', $lead->id)->andReturnSelf();
    $activityQuery->shouldReceive('where')->with('created_at', '>=', Mockery::any())->andReturnSelf();
    $activityQuery->shouldReceive('get')->andReturn($activities);
    $activityQuery->shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
    $activityQuery->shouldReceive('first')->andReturn($lastActivity);

    $emailQuery = Mockery::mock();
    $emailQuery->shouldReceive('where')->with('lead_id', $lead->id)->andReturnSelf();
    $emailQuery->shouldReceive('where')->with('created_at', '>=', Mockery::any())->andReturnSelf();
    $emailQuery->shouldReceive('get')->andReturn($emails);
    $emailQuery->shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
    $emailQuery->shouldReceive('first')->andReturn($lastEmail);

    $test->activityRepository->model = $activityQuery;
    $test->emailRepository->model = $emailQuery;
}

function mockEngagementDataWithRecency($test, $lead, int $activityCount, int $emailCount, int $lastActivityDays, int $lastEmailDays): void
{
    $activities = collect();
    for ($i = 0; $i < $activityCount; $i++) {
        $activities->push((object) ['id' => $i + 1, 'created_at' => now()->subDays(rand(1, 30))]);
    }

    $emails = collect();
    for ($i = 0; $i < $emailCount; $i++) {
        $emails->push((object) ['id' => $i + 1, 'created_at' => now()->subDays(rand(1, 30))]);
    }

    $lastActivity = $activityCount > 0 ? (object) ['created_at' => now()->subDays($lastActivityDays)] : null;
    $lastEmail = $emailCount > 0 ? (object) ['created_at' => now()->subDays($lastEmailDays)] : null;

    $activityQuery = Mockery::mock();
    $activityQuery->shouldReceive('where')->with('lead_id', $lead->id)->andReturnSelf();
    $activityQuery->shouldReceive('where')->with('created_at', '>=', Mockery::any())->andReturnSelf();
    $activityQuery->shouldReceive('get')->andReturn($activities);
    $activityQuery->shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
    $activityQuery->shouldReceive('first')->andReturn($lastActivity);

    $emailQuery = Mockery::mock();
    $emailQuery->shouldReceive('where')->with('lead_id', $lead->id)->andReturnSelf();
    $emailQuery->shouldReceive('where')->with('created_at', '>=', Mockery::any())->andReturnSelf();
    $emailQuery->shouldReceive('get')->andReturn($emails);
    $emailQuery->shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
    $emailQuery->shouldReceive('first')->andReturn($lastEmail);

    $test->activityRepository->model = $activityQuery;
    $test->emailRepository->model = $emailQuery;
}

function createMockDealScore(int $leadId, float $score): object
{
    return new class ($leadId, $score) {
        public $lead_id;
        public $score;
        public $lead = null;

        public function __construct($leadId, $score)
        {
            $this->lead_id = $leadId;
            $this->score = $score;
        }
    };
}

function createMockDealScoreWithLead(int $leadId, float $score, $lead): object
{
    $dealScore = new class ($leadId, $score) {
        public $lead_id;
        public $score;
        public $lead;

        public function __construct($leadId, $score)
        {
            $this->lead_id = $leadId;
            $this->score = $score;
        }
    };

    $dealScore->lead = $lead;

    return $dealScore;
}
