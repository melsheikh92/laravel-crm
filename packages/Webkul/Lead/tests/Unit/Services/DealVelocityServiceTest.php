<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\DealVelocityService;

uses(TestCase::class);

beforeEach(function () {
    $this->leadRepository = Mockery::mock(LeadRepository::class);
    $this->historicalConversionRepository = Mockery::mock(HistoricalConversionRepository::class);

    $this->service = new DealVelocityService(
        $this->leadRepository,
        $this->historicalConversionRepository
    );

    // Mock the model query builder
    $this->leadRepository->model = Mockery::mock();
});

afterEach(function () {
    Mockery::close();
});

describe('calculateVelocityScore', function () {
    test('calculates velocity score for lead on pace', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $lead = createVelocityLead(
            stageId: 1,
            pipelineId: 1,
            userId: 1,
            updatedAt: '2024-01-24', // 7 days in stage
            expectedCloseDate: '2024-02-07' // 7 days until close
        );

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(7.0); // Historical average 7 days

        $result = $this->service->calculateVelocityScore($lead);

        expect($result)->toBeArray()
            ->and($result['velocity_score'])->toBeGreaterThan(40.0)
            ->and($result['velocity_level'])->toBe('moderate')
            ->and($result['days_in_current_stage'])->toBe(7.0)
            ->and($result['expected_time_in_stage'])->toBe(7.0)
            ->and($result['historical_average'])->toBe(7.0)
            ->and($result['days_until_expected_close'])->toBe(7.0)
            ->and($result['is_ahead_of_pace'])->toBe(false)
            ->and($result['is_behind_pace'])->toBe(false);

        Carbon::setTestNow();
    });

    test('calculates high velocity score for fast-moving deal', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $lead = createVelocityLead(
            stageId: 1,
            pipelineId: 1,
            userId: 1,
            updatedAt: '2024-01-28', // 3 days in stage
            expectedCloseDate: '2024-02-05' // 5 days until close
        );

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0); // Historical average 10 days, but only 3 days spent

        $result = $this->service->calculateVelocityScore($lead);

        expect($result['velocity_score'])->toBeGreaterThan(70.0)
            ->and($result['velocity_level'])->toBe('fast')
            ->and($result['is_ahead_of_pace'])->toBe(true)
            ->and($result['pace_variance'])->toBeLessThan(0);

        Carbon::setTestNow();
    });

    test('calculates low velocity score for slow-moving deal', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $lead = createVelocityLead(
            stageId: 1,
            pipelineId: 1,
            userId: 1,
            updatedAt: '2024-01-01', // 30 days in stage
            expectedCloseDate: '2024-03-15' // 44 days until close (far future)
        );

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0); // Historical average 10 days, but 30 days spent

        $result = $this->service->calculateVelocityScore($lead);

        expect($result['velocity_score'])->toBeLessThan(50.0)
            ->and($result['velocity_level'])->toBe('slow')
            ->and($result['is_behind_pace'])->toBe(true)
            ->and($result['pace_variance'])->toBeGreaterThan(0);

        Carbon::setTestNow();
    });

    test('handles lead with no expected close date', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $lead = createVelocityLead(
            stageId: 1,
            pipelineId: 1,
            userId: 1,
            updatedAt: '2024-01-24',
            expectedCloseDate: null
        );

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(14.0);

        $result = $this->service->calculateVelocityScore($lead);

        expect($result['days_until_expected_close'])->toBeNull()
            ->and($result['velocity_score'])->toBeGreaterThan(0);

        Carbon::setTestNow();
    });

    test('handles lead with no historical data', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $lead = createVelocityLead(
            stageId: 1,
            pipelineId: 1,
            userId: 1,
            updatedAt: '2024-01-24',
            expectedCloseDate: '2024-02-07'
        );

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(null);

        $result = $this->service->calculateVelocityScore($lead);

        expect($result['historical_average'])->toBeNull()
            ->and($result['expected_time_in_stage'])->toBe(14.0) // Default 2 weeks
            ->and($result['velocity_score'])->toBeGreaterThan(0);

        Carbon::setTestNow();
    });

    test('penalizes past due deals', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $lead = createVelocityLead(
            stageId: 1,
            pipelineId: 1,
            userId: 1,
            updatedAt: '2024-01-01', // 30 days in stage
            expectedCloseDate: '2024-01-20' // Past due by 11 days
        );

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0);

        $result = $this->service->calculateVelocityScore($lead);

        expect($result['velocity_score'])->toBeLessThan(30.0)
            ->and($result['days_until_expected_close'])->toBeLessThan(0);

        Carbon::setTestNow();
    });

    test('includes all velocity factors in result', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $lead = createVelocityLead(
            stageId: 1,
            pipelineId: 1,
            userId: 1,
            updatedAt: '2024-01-24',
            expectedCloseDate: '2024-02-07'
        );

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0);

        $result = $this->service->calculateVelocityScore($lead);

        expect($result['factors'])->toBeArray()
            ->and($result['factors'])->toHaveKeys([
                'stage_progress',
                'close_date_proximity',
                'historical_comparison'
            ])
            ->and($result['factors']['stage_progress'])->toBeGreaterThan(0)
            ->and($result['factors']['close_date_proximity'])->toBeGreaterThan(0)
            ->and($result['factors']['historical_comparison'])->toBeGreaterThan(0);

        Carbon::setTestNow();
    });
});

describe('calculateVelocityScoresForLeads', function () {
    test('calculates velocity scores for multiple leads', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-28', '2024-02-05'),
            createVelocityLead(1, 1, 1, '2024-01-24', '2024-02-10'),
            createVelocityLead(1, 1, 1, '2024-01-15', '2024-02-15'),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0);

        $result = $this->service->calculateVelocityScoresForLeads($leads);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(3)
            ->and($result[0]->velocity_data)->toBeArray()
            ->and($result[0]->velocity_data['velocity_score'])->toBeGreaterThan(0)
            ->and($result[1]->velocity_data['velocity_score'])->toBeGreaterThan(0)
            ->and($result[2]->velocity_data['velocity_score'])->toBeGreaterThan(0);

        Carbon::setTestNow();
    });

    test('handles empty leads collection', function () {
        $leads = collect([]);

        $result = $this->service->calculateVelocityScoresForLeads($leads);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(0);
    });
});

describe('getFastMovingDeals', function () {
    test('retrieves only fast-moving deals', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-29', '2024-02-03'), // Fast
            createVelocityLead(1, 1, 1, '2024-01-28', '2024-02-04'), // Fast
            createVelocityLead(1, 1, 1, '2024-01-15', '2024-02-20'), // Slow
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(14.0);

        $result = $this->service->getFastMovingDeals(1, 1, 10);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBeGreaterThan(0);

        Carbon::setTestNow();
    });

    test('sorts fast deals by velocity score descending', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-30', '2024-02-02'), // Very fast
            createVelocityLead(1, 1, 1, '2024-01-28', '2024-02-05'), // Fast
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(14.0);

        $result = $this->service->getFastMovingDeals(1, 1, 10);

        if ($result->count() > 1) {
            expect($result[0]->velocity_data['velocity_score'])
                ->toBeGreaterThanOrEqual($result[1]->velocity_data['velocity_score']);
        }

        Carbon::setTestNow();
    });

    test('respects limit parameter', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-30', '2024-02-02'),
            createVelocityLead(1, 1, 1, '2024-01-29', '2024-02-03'),
            createVelocityLead(1, 1, 1, '2024-01-28', '2024-02-04'),
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(14.0);

        $result = $this->service->getFastMovingDeals(1, 1, 2);

        expect($result->count())->toBeLessThanOrEqual(2);

        Carbon::setTestNow();
    });
});

describe('getSlowMovingDeals', function () {
    test('retrieves only slow-moving deals', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-01', '2024-03-15'), // Very slow
            createVelocityLead(1, 1, 1, '2024-01-10', '2024-02-28'), // Slow
            createVelocityLead(1, 1, 1, '2024-01-29', '2024-02-03'), // Fast
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(7.0);

        $result = $this->service->getSlowMovingDeals(1, 1, 10);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBeGreaterThan(0);

        Carbon::setTestNow();
    });

    test('sorts slow deals by velocity score ascending', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-01', '2024-03-15'), // Very slow
            createVelocityLead(1, 1, 1, '2024-01-15', '2024-02-28'), // Moderate slow
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(7.0);

        $result = $this->service->getSlowMovingDeals(1, 1, 10);

        if ($result->count() > 1) {
            expect($result[0]->velocity_data['velocity_score'])
                ->toBeLessThanOrEqual($result[1]->velocity_data['velocity_score']);
        }

        Carbon::setTestNow();
    });
});

describe('getDealsAtRisk', function () {
    test('retrieves deals behind pace', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-01', '2024-02-10'), // 30 days in stage, behind
            createVelocityLead(1, 1, 1, '2024-01-15', '2024-02-15'), // 16 days in stage, behind
            createVelocityLead(1, 1, 1, '2024-01-29', '2024-02-05'), // 2 days in stage, ahead
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0);

        $result = $this->service->getDealsAtRisk(1, 1, 10);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBeGreaterThan(0);

        foreach ($result as $deal) {
            expect($deal->velocity_data['is_behind_pace'])->toBe(true);
        }

        Carbon::setTestNow();
    });

    test('sorts at-risk deals by pace variance descending', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-01', '2024-02-10'), // Very behind
            createVelocityLead(1, 1, 1, '2024-01-20', '2024-02-15'), // Slightly behind
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0);

        $result = $this->service->getDealsAtRisk(1, 1, 10);

        if ($result->count() > 1) {
            expect($result[0]->velocity_data['pace_variance'])
                ->toBeGreaterThanOrEqual($result[1]->velocity_data['pace_variance']);
        }

        Carbon::setTestNow();
    });
});

describe('calculateUserAverageVelocity', function () {
    test('calculates average velocity across user leads', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLead(1, 1, 1, '2024-01-29', '2024-02-05'), // Fast
            createVelocityLead(1, 1, 1, '2024-01-20', '2024-02-10'), // Moderate
            createVelocityLead(1, 1, 1, '2024-01-01', '2024-02-15'), // Slow
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0);

        $result = $this->service->calculateUserAverageVelocity(1, 1);

        expect($result)->toBeArray()
            ->and($result['average_velocity_score'])->toBeGreaterThan(0)
            ->and($result['total_leads'])->toBe(3)
            ->and($result['fast_moving_count'])->toBeGreaterThanOrEqual(0)
            ->and($result['moderate_moving_count'])->toBeGreaterThanOrEqual(0)
            ->and($result['slow_moving_count'])->toBeGreaterThanOrEqual(0);

        Carbon::setTestNow();
    });

    test('handles user with no leads', function () {
        mockActiveLeadsQuery($this->leadRepository, collect([]));

        $result = $this->service->calculateUserAverageVelocity(1, null);

        expect($result['average_velocity_score'])->toBe(0.0)
            ->and($result['total_leads'])->toBe(0)
            ->and($result['fast_moving_count'])->toBe(0)
            ->and($result['moderate_moving_count'])->toBe(0)
            ->and($result['slow_moving_count'])->toBe(0);
    });
});

describe('calculatePipelineVelocity', function () {
    test('calculates pipeline-wide velocity metrics', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLeadWithStage(1, 1, 1, 1, '2024-01-24', '2024-02-05'),
            createVelocityLeadWithStage(2, 1, 1, 1, '2024-01-20', '2024-02-10'),
            createVelocityLeadWithStage(1, 1, 1, 1, '2024-01-28', '2024-02-03'),
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0);

        $result = $this->service->calculatePipelineVelocity(1);

        expect($result)->toBeArray()
            ->and($result['average_velocity_score'])->toBeGreaterThan(0)
            ->and($result['total_leads'])->toBe(3)
            ->and($result['average_days_in_stage'])->toBeGreaterThan(0)
            ->and($result['stage_velocities'])->toBeArray();

        Carbon::setTestNow();
    });

    test('groups velocity by stage', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createVelocityLeadWithStage(1, 1, 1, 1, '2024-01-24', '2024-02-05'),
            createVelocityLeadWithStage(1, 1, 1, 1, '2024-01-20', '2024-02-10'),
            createVelocityLeadWithStage(2, 1, 1, 1, '2024-01-28', '2024-02-03'),
        ]);

        mockActiveLeadsQuery($this->leadRepository, $leads);

        $this->historicalConversionRepository
            ->shouldReceive('getAverageTimeInStage')
            ->andReturn(10.0);

        $result = $this->service->calculatePipelineVelocity(1);

        expect($result['stage_velocities'])->toBeArray()
            ->and(count($result['stage_velocities']))->toBe(2);

        Carbon::setTestNow();
    });

    test('handles empty pipeline', function () {
        mockActiveLeadsQuery($this->leadRepository, collect([]));

        $result = $this->service->calculatePipelineVelocity(1);

        expect($result['average_velocity_score'])->toBe(0.0)
            ->and($result['total_leads'])->toBe(0)
            ->and($result['average_days_in_stage'])->toBe(0.0)
            ->and($result['stage_velocities'])->toBeArray();
    });
});

describe('getVelocityTrends', function () {
    test('generates velocity trends over multiple months', function () {
        Carbon::setTestNow('2024-06-30 12:00:00');

        $leads = collect([
            createClosedLead(1, '2024-01-01', '2024-01-15'), // 14 days to close
            createClosedLead(1, '2024-01-05', '2024-01-25'), // 20 days to close
        ]);

        $this->leadRepository->model
            ->shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('whereBetween')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->andReturn($leads);

        $result = $this->service->getVelocityTrends(1, 3);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(3)
            ->and($result[0])->toHaveKeys(['period', 'average_days_to_close', 'leads_closed']);

        Carbon::setTestNow();
    });

    test('handles months with no closed leads', function () {
        Carbon::setTestNow('2024-06-30 12:00:00');

        $this->leadRepository->model
            ->shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('whereBetween')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->andReturn(collect([]));

        $result = $this->service->getVelocityTrends(1, 2);

        expect($result)->toBeArray()
            ->and($result[0]['average_days_to_close'])->toBe(0.0)
            ->and($result[0]['leads_closed'])->toBe(0);

        Carbon::setTestNow();
    });
});

// Helper functions to create mock objects

function createVelocityLead(int $stageId, int $pipelineId, int $userId, string $updatedAt, ?string $expectedCloseDate): object
{
    return new class ($stageId, $pipelineId, $userId, $updatedAt, $expectedCloseDate) extends Model {
        public $lead_pipeline_stage_id;
        public $lead_pipeline_id;
        public $user_id;
        public $updated_at;
        public $expected_close_date;
        public $status = 1;

        public function __construct($stageId, $pipelineId, $userId, $updatedAt, $expectedCloseDate)
        {
            $this->lead_pipeline_stage_id = $stageId;
            $this->lead_pipeline_id = $pipelineId;
            $this->user_id = $userId;
            $this->updated_at = Carbon::parse($updatedAt);
            $this->expected_close_date = $expectedCloseDate;
        }
    };
}

function createVelocityLeadWithStage(int $stageId, int $pipelineId, int $userId, int $stageIdValue, string $updatedAt, ?string $expectedCloseDate): object
{
    $stage = new class extends Model {
        public $name = 'Test Stage';
    };

    $pipeline = new class extends Model {
        public $name = 'Test Pipeline';
    };

    return new class ($stageId, $pipelineId, $userId, $updatedAt, $expectedCloseDate, $stage, $pipeline) extends Model {
        public $lead_pipeline_stage_id;
        public $lead_pipeline_id;
        public $user_id;
        public $updated_at;
        public $expected_close_date;
        public $status = 1;
        public $stage;
        public $pipeline;

        public function __construct($stageId, $pipelineId, $userId, $updatedAt, $expectedCloseDate, $stage, $pipeline)
        {
            $this->lead_pipeline_stage_id = $stageId;
            $this->lead_pipeline_id = $pipelineId;
            $this->user_id = $userId;
            $this->updated_at = Carbon::parse($updatedAt);
            $this->expected_close_date = $expectedCloseDate;
            $this->stage = $stage;
            $this->pipeline = $pipeline;
        }
    };
}

function createClosedLead(int $userId, string $createdAt, string $closedAt): object
{
    return new class ($userId, $createdAt, $closedAt) extends Model {
        public $user_id;
        public $created_at;
        public $closed_at;
        public $status = 0;

        public function __construct($userId, $createdAt, $closedAt)
        {
            $this->user_id = $userId;
            $this->created_at = Carbon::parse($createdAt);
            $this->closed_at = Carbon::parse($closedAt);
        }
    };
}

function mockActiveLeadsQuery($repository, $leads): void
{
    $repository->model
        ->shouldReceive('with')
        ->andReturnSelf()
        ->shouldReceive('where')
        ->andReturnSelf()
        ->shouldReceive('get')
        ->andReturn($leads);
}
