<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\DealScoringService;
use Webkul\Lead\Services\DealVelocityService;
use Webkul\Lead\Services\HistoricalAnalysisService;
use Webkul\Lead\Services\WinProbabilityService;

uses(TestCase::class);

beforeEach(function () {
    $this->leadRepository = Mockery::mock(LeadRepository::class);
    $this->historicalAnalysisService = Mockery::mock(HistoricalAnalysisService::class);
    $this->dealVelocityService = Mockery::mock(DealVelocityService::class);
    $this->dealScoringService = Mockery::mock(DealScoringService::class);

    $this->service = new WinProbabilityService(
        $this->leadRepository,
        $this->historicalAnalysisService,
        $this->dealVelocityService,
        $this->dealScoringService
    );
});

afterEach(function () {
    Mockery::close();
});

describe('calculateWinProbability', function () {
    test('calculates win probability with all factors', function () {
        $lead = createMockLeadForProbability(1, 50000, 75.0, now()->addDays(20));

        mockHistoricalAnalysisForProbability($this, $lead);
        mockVelocityForProbability($this, $lead, 70.0);
        mockScoringForProbability($this, $lead, 80.0);

        $result = $this->service->calculateWinProbability($lead);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys([
                'win_probability',
                'confidence_score',
                'risk_level',
                'factors',
                'factor_weights',
                'modifiers',
                'recommendations',
            ])
            ->and($result['win_probability'])->toBeGreaterThan(0)
            ->and($result['win_probability'])->toBeLessThanOrEqual(100)
            ->and($result['confidence_score'])->toBeGreaterThan(0)
            ->and($result['confidence_score'])->toBeLessThanOrEqual(100)
            ->and($result['risk_level'])->toBeIn(['low', 'medium', 'high']);
    });

    test('handles lead ID instead of object', function () {
        $leadId = 123;
        $lead = createMockLeadForProbability(1, 40000, 65.0);

        $repositoryMock = Mockery::mock();
        $repositoryMock->shouldReceive('with')
            ->with(['stage', 'pipeline', 'user'])
            ->andReturnSelf();
        $repositoryMock->shouldReceive('find')
            ->with($leadId)
            ->andReturn($lead);

        $this->leadRepository = $repositoryMock;

        $this->service = new WinProbabilityService(
            $this->leadRepository,
            $this->historicalAnalysisService,
            $this->dealVelocityService,
            $this->dealScoringService
        );

        mockHistoricalAnalysisForProbability($this, $lead);
        mockVelocityForProbability($this, $lead, 60.0);
        mockScoringForProbability($this, $lead, 70.0);

        $result = $this->service->calculateWinProbability($leadId);

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('win_probability');
    });

    test('throws exception for invalid lead', function () {
        $repositoryMock = Mockery::mock();
        $repositoryMock->shouldReceive('with')
            ->with(['stage', 'pipeline', 'user'])
            ->andReturnSelf();
        $repositoryMock->shouldReceive('find')
            ->with(999)
            ->andReturn(null);

        $this->leadRepository = $repositoryMock;

        $this->service = new WinProbabilityService(
            $this->leadRepository,
            $this->historicalAnalysisService,
            $this->dealVelocityService,
            $this->dealScoringService
        );

        $this->service->calculateWinProbability(999);
    })->throws(\InvalidArgumentException::class, 'Lead not found');

    test('handles historical analysis failure gracefully', function () {
        $lead = createMockLeadForProbability(1, 35000, 60.0);

        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->andThrow(new \Exception('Historical analysis failed'));

        $this->historicalAnalysisService
            ->shouldReceive('getConversionRatesByStage')
            ->andThrow(new \Exception('Conversion rates failed'));

        mockVelocityForProbability($this, $lead, 55.0);
        mockScoringForProbability($this, $lead, 65.0);

        $result = $this->service->calculateWinProbability($lead);

        // Should use neutral score (50.0) for historical pattern
        expect($result)->toBeArray()
            ->and($result['factors']['historical_pattern'])->toBe(50.0);
    });

    test('handles velocity calculation failure gracefully', function () {
        $lead = createMockLeadForProbability(1, 45000, 70.0);

        mockHistoricalAnalysisForProbability($this, $lead);

        $this->dealVelocityService
            ->shouldReceive('calculateVelocityScore')
            ->andThrow(new \Exception('Velocity calculation failed'));

        mockScoringForProbability($this, $lead, 75.0);

        $result = $this->service->calculateWinProbability($lead);

        // Should use neutral score (50.0) for velocity
        expect($result)->toBeArray()
            ->and($result['factors']['velocity'])->toBe(50.0);
    });

    test('handles engagement scoring failure gracefully', function () {
        $lead = createMockLeadForProbability(1, 40000, 65.0);

        mockHistoricalAnalysisForProbability($this, $lead);
        mockVelocityForProbability($this, $lead, 60.0);

        $this->dealScoringService
            ->shouldReceive('scoreLead')
            ->andThrow(new \Exception('Scoring failed'));

        $result = $this->service->calculateWinProbability($lead);

        // Should use neutral score (50.0) for engagement
        expect($result)->toBeArray()
            ->and($result['factors']['engagement'])->toBe(50.0);
    });
});

describe('calculateWeightedProbability', function () {
    test('weights are correctly applied', function () {
        $lead = createMockLeadForProbability(1, 50000, 80.0);

        mockHistoricalAnalysisForProbability($this, $lead, 70.0);
        mockVelocityForProbability($this, $lead, 75.0);
        mockScoringForProbability($this, $lead, 85.0);

        $result = $this->service->calculateWinProbability($lead);

        // Verify weights sum correctly
        // Stage (35%) + Historical (25%) + Deal Chars (20%) + Engagement (12%) + Velocity (8%) = 100%
        expect($result['factor_weights']['stage_probability'])->toBe(35.0)
            ->and($result['factor_weights']['historical_pattern'])->toBe(25.0)
            ->and($result['factor_weights']['deal_characteristics'])->toBe(20.0)
            ->and($result['factor_weights']['engagement'])->toBe(12.0)
            ->and($result['factor_weights']['velocity'])->toBe(8.0);
    });
});

describe('applyProbabilityModifiers', function () {
    test('applies penalty for overdue deals', function () {
        $lead = createMockLeadForProbability(1, 40000, 70.0, now()->subDays(20));

        mockHistoricalAnalysisForProbability($this, $lead);
        mockVelocityForProbability($this, $lead, 60.0);
        mockScoringForProbability($this, $lead, 65.0);

        $result = $this->service->calculateWinProbability($lead);

        expect($result['modifiers'])->toBeArray()
            ->and(count($result['modifiers']))->toBeGreaterThan(0);

        $hasOverduePenalty = false;
        foreach ($result['modifiers'] as $modifier) {
            if ($modifier['type'] === 'overdue_penalty') {
                $hasOverduePenalty = true;
                expect($modifier['impact'])->toBe('negative')
                    ->and($modifier['value'])->toBeLessThan(0);
            }
        }

        expect($hasOverduePenalty)->toBeTrue();
    });

    test('applies boost for deals closing soon', function () {
        $lead = createMockLeadForProbability(1, 40000, 70.0, now()->addDays(10));

        mockHistoricalAnalysisForProbability($this, $lead);
        mockVelocityForProbability($this, $lead, 60.0);
        mockScoringForProbability($this, $lead, 65.0);

        $result = $this->service->calculateWinProbability($lead);

        expect($result['modifiers'])->toBeArray();

        $hasClosingSoonBoost = false;
        foreach ($result['modifiers'] as $modifier) {
            if ($modifier['type'] === 'closing_soon_boost') {
                $hasClosingSoonBoost = true;
                expect($modifier['impact'])->toBe('positive')
                    ->and($modifier['value'])->toBeGreaterThan(0);
            }
        }

        expect($hasClosingSoonBoost)->toBeTrue();
    });

    test('applies penalty for enterprise deals', function () {
        $lead = createMockLeadForProbability(1, 150000, 75.0, now()->addDays(30));

        mockHistoricalAnalysisForProbability($this, $lead);
        mockVelocityForProbability($this, $lead, 65.0);
        mockScoringForProbability($this, $lead, 70.0);

        $result = $this->service->calculateWinProbability($lead);

        expect($result['modifiers'])->toBeArray();

        $hasEnterpriseAdjustment = false;
        foreach ($result['modifiers'] as $modifier) {
            if ($modifier['type'] === 'enterprise_deal_adjustment') {
                $hasEnterpriseAdjustment = true;
                expect($modifier['impact'])->toBe('negative')
                    ->and($modifier['value'])->toBeLessThan(0);
            }
        }

        expect($hasEnterpriseAdjustment)->toBeTrue();
    });

    test('applies penalty for stale deals', function () {
        $lead = createMockLeadForProbability(1, 40000, 65.0, now()->addDays(20));
        $lead->created_at = now()->subDays(200);

        mockHistoricalAnalysisForProbability($this, $lead);
        mockVelocityForProbability($this, $lead, 55.0);
        mockScoringForProbability($this, $lead, 60.0);

        $result = $this->service->calculateWinProbability($lead);

        expect($result['modifiers'])->toBeArray();

        $hasStalePenalty = false;
        foreach ($result['modifiers'] as $modifier) {
            if ($modifier['type'] === 'stale_deal_penalty') {
                $hasStalePenalty = true;
                expect($modifier['impact'])->toBe('negative')
                    ->and($modifier['value'])->toBeLessThan(0);
            }
        }

        expect($hasStalePenalty)->toBeTrue();
    });
});

describe('determineRiskLevel', function () {
    test('classifies low risk correctly', function () {
        $lead = createMockLeadForProbability(1, 50000, 90.0, now()->addDays(15));

        mockHistoricalAnalysisForProbability($this, $lead, 85.0);
        mockVelocityForProbability($this, $lead, 80.0);
        mockScoringForProbability($this, $lead, 90.0);

        $result = $this->service->calculateWinProbability($lead);

        expect($result['risk_level'])->toBe('low');
    });

    test('classifies medium risk correctly', function () {
        $lead = createMockLeadForProbability(1, 40000, 60.0, now()->addDays(30));

        mockHistoricalAnalysisForProbability($this, $lead, 55.0);
        mockVelocityForProbability($this, $lead, 50.0);
        mockScoringForProbability($this, $lead, 60.0);

        $result = $this->service->calculateWinProbability($lead);

        expect($result['risk_level'])->toBe('medium');
    });

    test('classifies high risk correctly', function () {
        $lead = createMockLeadForProbability(1, 30000, 30.0, now()->subDays(10));

        mockHistoricalAnalysisForProbability($this, $lead, 25.0);
        mockVelocityForProbability($this, $lead, 35.0);
        mockScoringForProbability($this, $lead, 30.0);

        $result = $this->service->calculateWinProbability($lead);

        expect($result['risk_level'])->toBe('high');
    });
});

describe('generateRecommendations', function () {
    test('generates recommendations for high risk deals', function () {
        $lead = createMockLeadForProbability(1, 30000, 30.0, now()->subDays(10));

        mockHistoricalAnalysisForProbability($this, $lead, 25.0);

        $this->dealVelocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn([
                'velocity_score' => 35.0,
                'velocity_level' => 'slow',
            ]);

        $this->dealScoringService
            ->shouldReceive('scoreLead')
            ->with($lead, false)
            ->andReturn([
                'engagement_score' => 30.0,
            ]);

        $result = $this->service->calculateWinProbability($lead);

        expect($result['recommendations'])->toBeArray()
            ->and(count($result['recommendations']))->toBeGreaterThan(0);

        $hasHighPriorityAction = false;
        foreach ($result['recommendations'] as $recommendation) {
            if ($recommendation['priority'] === 'high') {
                $hasHighPriorityAction = true;
                expect($recommendation)->toHaveKeys(['priority', 'action', 'message']);
            }
        }

        expect($hasHighPriorityAction)->toBeTrue();
    });

    test('generates recommendations for slow velocity', function () {
        $lead = createMockLeadForProbability(1, 40000, 55.0, now()->addDays(30));

        mockHistoricalAnalysisForProbability($this, $lead, 50.0);

        $this->dealVelocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn([
                'velocity_score' => 40.0,
                'velocity_level' => 'slow',
            ]);

        $this->dealScoringService
            ->shouldReceive('scoreLead')
            ->with($lead, false)
            ->andReturn([
                'engagement_score' => 50.0,
            ]);

        $result = $this->service->calculateWinProbability($lead);

        $hasVelocityRecommendation = false;
        foreach ($result['recommendations'] as $recommendation) {
            if ($recommendation['action'] === 'accelerate_deal') {
                $hasVelocityRecommendation = true;
                expect($recommendation['message'])->toContain('velocity');
            }
        }

        expect($hasVelocityRecommendation)->toBeTrue();
    });

    test('generates recommendations for low engagement', function () {
        $lead = createMockLeadForProbability(1, 35000, 50.0, now()->addDays(25));

        mockHistoricalAnalysisForProbability($this, $lead, 45.0);

        $this->dealVelocityService
            ->shouldReceive('calculateVelocityScore')
            ->andReturn([
                'velocity_score' => 50.0,
                'velocity_level' => 'normal',
            ]);

        $this->dealScoringService
            ->shouldReceive('scoreLead')
            ->with($lead, false)
            ->andReturn([
                'engagement_score' => 30.0,
            ]);

        $result = $this->service->calculateWinProbability($lead);

        $hasEngagementRecommendation = false;
        foreach ($result['recommendations'] as $recommendation) {
            if ($recommendation['action'] === 'boost_engagement') {
                $hasEngagementRecommendation = true;
                expect($recommendation['message'])->toContain('engagement');
            }
        }

        expect($hasEngagementRecommendation)->toBeTrue();
    });

    test('recommends setting close date when missing', function () {
        $lead = createMockLeadForProbability(1, 40000, 60.0, null);

        mockHistoricalAnalysisForProbability($this, $lead);
        mockVelocityForProbability($this, $lead, 55.0);
        mockScoringForProbability($this, $lead, 60.0);

        $result = $this->service->calculateWinProbability($lead);

        $hasCloseDateRecommendation = false;
        foreach ($result['recommendations'] as $recommendation) {
            if ($recommendation['action'] === 'set_close_date') {
                $hasCloseDateRecommendation = true;
                expect($recommendation['message'])->toContain('close date');
            }
        }

        expect($hasCloseDateRecommendation)->toBeTrue();
    });
});

describe('calculateWinProbabilities', function () {
    test('calculates probabilities for multiple leads', function () {
        $leads = collect([
            createMockLeadForProbability(1, 40000, 70.0),
            createMockLeadForProbability(2, 50000, 65.0),
        ]);

        foreach ($leads as $lead) {
            mockHistoricalAnalysisForProbability($this, $lead);
            mockVelocityForProbability($this, $lead, 60.0);
            mockScoringForProbability($this, $lead, 65.0);
        }

        $result = $this->service->calculateWinProbabilities($leads);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(2)
            ->and($result->first()['success'])->toBeTrue()
            ->and($result->last()['success'])->toBeTrue();
    });

    test('handles individual calculation failures', function () {
        $leads = collect([
            createMockLeadForProbability(1, 40000, 70.0),
            createMockLeadForProbability(2, 50000, 65.0),
        ]);

        // First lead succeeds
        mockHistoricalAnalysisForProbability($this, $leads->first());
        mockVelocityForProbability($this, $leads->first(), 60.0);
        mockScoringForProbability($this, $leads->first(), 65.0);

        // Second lead fails
        $this->historicalAnalysisService
            ->shouldReceive('analyzeUserPerformance')
            ->with(2, 1, 90)
            ->andThrow(new \Exception('Failed'));

        $result = $this->service->calculateWinProbabilities($leads);

        expect($result)->toHaveCount(2)
            ->and($result->first()['success'])->toBeTrue()
            ->and($result->last()['success'])->toBeFalse()
            ->and($result->last())->toHaveKey('error');
    });
});

describe('getHighProbabilityDeals', function () {
    test('returns leads with high win probability', function () {
        $leads = collect([
            createMockLeadForProbability(1, 50000, 85.0, now()->addDays(15)),
            createMockLeadForProbability(2, 40000, 75.0, now()->addDays(20)),
            createMockLeadForProbability(3, 30000, 50.0, now()->addDays(30)),
        ]);

        $query = Mockery::mock();
        $query->shouldReceive('with')
            ->with(['stage', 'pipeline', 'user'])
            ->andReturnSelf();
        $query->shouldReceive('where')
            ->with('status', 1)
            ->andReturnSelf();
        $query->shouldReceive('get')
            ->andReturn($leads);

        $this->leadRepository->model = $query;

        foreach ($leads as $lead) {
            mockHistoricalAnalysisForProbability($this, $lead, 70.0);
            mockVelocityForProbability($this, $lead, 65.0);
            mockScoringForProbability($this, $lead, 70.0);
        }

        $result = $this->service->getHighProbabilityDeals();

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBeLessThanOrEqual(10);
    });
});

describe('getAtRiskDeals', function () {
    test('returns leads with high risk level', function () {
        $leads = collect([
            createMockLeadForProbability(1, 30000, 30.0, now()->subDays(10)),
            createMockLeadForProbability(2, 35000, 35.0, now()->subDays(5)),
        ]);

        $query = Mockery::mock();
        $query->shouldReceive('with')
            ->with(['stage', 'pipeline', 'user'])
            ->andReturnSelf();
        $query->shouldReceive('where')
            ->with('status', 1)
            ->andReturnSelf();
        $query->shouldReceive('get')
            ->andReturn($leads);

        $this->leadRepository->model = $query;

        foreach ($leads as $lead) {
            mockHistoricalAnalysisForProbability($this, $lead, 25.0);
            mockVelocityForProbability($this, $lead, 30.0);
            mockScoringForProbability($this, $lead, 35.0);
        }

        $result = $this->service->getAtRiskDeals();

        expect($result)->toBeInstanceOf(Collection::class);

        foreach ($result as $lead) {
            expect($lead->win_probability_data['risk_level'])->toBe('high');
        }
    });
});

// Helper functions
function createMockLeadForProbability(int $userId, float $value, float $probability, $expectedCloseDate = null): object
{
    $stage = new class ($probability) extends Model {
        public $probability;
        public $sort_order = 3;

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
        public $created_at;
        public $person_id = 1;
        public $title = 'Test Lead';
        public $status = 1;

        public function __construct($userId, $value, $stage, $expectedCloseDate = null)
        {
            $this->id = rand(1, 1000);
            $this->user_id = $userId;
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->expected_close_date = $expectedCloseDate;
            $this->created_at = now()->subDays(30);
        }
    };
}

function mockHistoricalAnalysisForProbability($test, $lead, float $winRate = 60.0): void
{
    $test->historicalAnalysisService
        ->shouldReceive('analyzeUserPerformance')
        ->with($lead->user_id, $lead->lead_pipeline_id, 90)
        ->andReturn([
            'win_rate' => $winRate,
            'total_leads' => 10,
        ]);

    $test->historicalAnalysisService
        ->shouldReceive('getConversionRatesByStage')
        ->with($lead->lead_pipeline_id, $lead->user_id, 90)
        ->andReturn([
            [
                'stage_id' => $lead->lead_pipeline_stage_id,
                'conversion_rate' => $winRate * 0.8,
            ],
        ]);
}

function mockVelocityForProbability($test, $lead, float $velocityScore): void
{
    $test->dealVelocityService
        ->shouldReceive('calculateVelocityScore')
        ->with($lead)
        ->andReturn([
            'velocity_score' => $velocityScore,
            'velocity_level' => 'normal',
        ]);
}

function mockScoringForProbability($test, $lead, float $engagementScore): void
{
    $test->dealScoringService
        ->shouldReceive('scoreLead')
        ->with($lead, false)
        ->andReturn([
            'engagement_score' => $engagementScore,
        ]);
}
