<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\HistoricalAnalysisService;

uses(TestCase::class);

beforeEach(function () {
    $this->leadRepository = Mockery::mock(LeadRepository::class);
    $this->historicalConversionRepository = Mockery::mock(HistoricalConversionRepository::class);

    $this->service = new HistoricalAnalysisService(
        $this->leadRepository,
        $this->historicalConversionRepository
    );

    // Mock the model query builder
    $this->leadRepository->model = Mockery::mock();
});

afterEach(function () {
    Mockery::close();
});

describe('analyzeUserPerformance', function () {
    test('analyzes user performance with complete data', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createHistoricalLead(1000, 'won', '2024-01-01', '2024-01-15'),
            createHistoricalLead(2000, 'won', '2024-01-02', '2024-01-20'),
            createHistoricalLead(3000, 'lost', '2024-01-03', '2024-01-18'),
            createHistoricalLead(4000, 'new', '2024-01-04', null),
        ]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->analyzeUserPerformance(1, 1, 90);

        expect($result)->toBeArray()
            ->and($result['user_id'])->toBe(1)
            ->and($result['pipeline_id'])->toBe(1)
            ->and($result['total_leads'])->toBe(4)
            ->and($result['conversion_rate'])->toBe(50.0) // 2 won out of 4 total
            ->and($result['win_rate'])->toBe(66.67) // 2 won out of 3 closed
            ->and($result['total_won_value'])->toBe(3000.0)
            ->and($result['total_lost_value'])->toBe(3000.0)
            ->and($result)->toHaveKeys([
                'user_id', 'pipeline_id', 'period_start', 'period_end',
                'total_leads', 'conversion_rate', 'win_rate', 'loss_rate',
                'average_deal_size', 'total_won_value', 'total_lost_value',
                'average_days_to_close', 'stage_breakdown', 'performance_indicators'
            ]);

        Carbon::setTestNow();
    });

    test('handles user with no leads', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->analyzeUserPerformance(1, null, 90);

        expect($result['total_leads'])->toBe(0)
            ->and($result['conversion_rate'])->toBe(0.0)
            ->and($result['win_rate'])->toBe(0.0)
            ->and($result['average_deal_size'])->toBe(0.0);

        Carbon::setTestNow();
    });

    test('calculates average days to close correctly', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createHistoricalLead(1000, 'won', '2024-01-01', '2024-01-11'), // 10 days
            createHistoricalLead(2000, 'won', '2024-01-01', '2024-01-21'), // 20 days
        ]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->analyzeUserPerformance(1, null, 90);

        expect($result['average_days_to_close'])->toBe(15.0); // (10 + 20) / 2

        Carbon::setTestNow();
    });
});

describe('analyzePipelinePerformance', function () {
    test('analyzes pipeline performance with complete metrics', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createHistoricalLeadWithUser(5000, 'won', 1, '2024-01-01', '2024-01-15'),
            createHistoricalLeadWithUser(3000, 'won', 2, '2024-01-02', '2024-01-18'),
            createHistoricalLeadWithUser(2000, 'lost', 1, '2024-01-03', '2024-01-20'),
            createHistoricalLeadWithUser(4000, 'new', 2, '2024-01-04', null),
        ]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->analyzePipelinePerformance(1, 90);

        expect($result)->toBeArray()
            ->and($result['pipeline_id'])->toBe(1)
            ->and($result['total_leads'])->toBe(4)
            ->and($result['conversion_rate'])->toBe(50.0)
            ->and($result['total_pipeline_value'])->toBe(14000.0)
            ->and($result['total_won_value'])->toBe(8000.0)
            ->and($result['total_lost_value'])->toBe(2000.0)
            ->and($result)->toHaveKeys([
                'pipeline_id', 'total_leads', 'conversion_rate',
                'win_rate', 'loss_rate', 'average_deal_size',
                'total_pipeline_value', 'total_won_value', 'total_lost_value',
                'average_days_to_close', 'stage_performance',
                'user_performance', 'velocity_metrics'
            ]);

        Carbon::setTestNow();
    });

    test('calculates user performance breakdown within pipeline', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createHistoricalLeadWithUser(5000, 'won', 1, '2024-01-01', '2024-01-15'),
            createHistoricalLeadWithUser(3000, 'won', 1, '2024-01-02', '2024-01-18'),
            createHistoricalLeadWithUser(2000, 'won', 2, '2024-01-03', '2024-01-20'),
        ]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->analyzePipelinePerformance(1, 90);

        expect($result['user_performance'])->toBeArray()
            ->and(count($result['user_performance']))->toBe(2);

        Carbon::setTestNow();
    });
});

describe('analyzeStagePerformance', function () {
    test('analyzes stage performance with historical data', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createHistoricalLeadInStage(1000, 'won', 1, '2024-01-01'),
            createHistoricalLeadInStage(2000, 'won', 1, '2024-01-05'),
            createHistoricalLeadInStage(3000, 'lost', 1, '2024-01-10'),
        ]);

        mockLeadQueryWithStageFilter($this->leadRepository, $leads, 1);

        $this->historicalConversionRepository
            ->shouldReceive('getStatsByStage')
            ->with(1)
            ->andReturn([
                'average_conversion_rate' => 65.0,
                'average_time_in_stage' => 12.5,
            ]);

        $result = $this->service->analyzeStagePerformance(1, 1, 90);

        expect($result)->toBeArray()
            ->and($result['stage_id'])->toBe(1)
            ->and($result['total_leads'])->toBe(3)
            ->and($result['conversion_rate'])->toBe(65.0) // From historical data
            ->and($result['average_time_in_stage'])->toBe(12.5)
            ->and($result['won_count'])->toBe(2)
            ->and($result['lost_count'])->toBe(1)
            ->and($result['open_count'])->toBe(0);

        Carbon::setTestNow();
    });

    test('falls back to calculated conversion rate when no historical data', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createHistoricalLeadInStage(1000, 'won', 1, '2024-01-01'),
            createHistoricalLeadInStage(2000, 'lost', 1, '2024-01-05'),
        ]);

        mockLeadQueryWithStageFilter($this->leadRepository, $leads, 1);

        $this->historicalConversionRepository
            ->shouldReceive('getStatsByStage')
            ->with(1)
            ->andReturn([]);

        $result = $this->service->analyzeStagePerformance(1, null, 90);

        expect($result['conversion_rate'])->toBe(50.0); // Calculated: 1 won / 2 total

        Carbon::setTestNow();
    });
});

describe('getConversionRatesByStage', function () {
    test('retrieves conversion rates with proper filtering', function () {
        $conversions = collect([
            createMockConversion(1, 'Stage 1', 1, 'Pipeline A', 65.5, 10.0, 25),
            createMockConversion(2, 'Stage 2', 1, 'Pipeline A', 45.0, 15.0, 30),
            createMockConversion(3, 'Stage 3', 2, 'Pipeline B', 75.0, 8.0, 20),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->andReturn($conversions);

        $result = $this->service->getConversionRatesByStage(1, null, 90);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(3)
            ->and($result[0]['stage_id'])->toBe(1)
            ->and($result[0]['conversion_rate'])->toBe(65.5)
            ->and($result[0]['level'])->toBe('high')
            ->and($result[1]['level'])->toBe('medium')
            ->and($result[2]['level'])->toBe('high');
    });

    test('applies pipeline and user filters correctly', function () {
        $this->historicalConversionRepository
            ->shouldReceive('getWithFilters')
            ->with(Mockery::on(function ($filters) {
                return $filters['pipeline_id'] === 5
                    && $filters['user_id'] === 3
                    && $filters['current_days'] === 60
                    && $filters['min_sample_size'] === 10;
            }))
            ->andReturn(collect([]));

        $result = $this->service->getConversionRatesByStage(5, 3, 60);

        expect($result)->toBeArray();
    });
});

describe('getAverageDealSizesByStage', function () {
    test('calculates average deal sizes grouped by stage', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createLeadInStageWithPipeline(1000, 1, 'Stage A', 1, 'Pipeline X', '2024-01-01'),
            createLeadInStageWithPipeline(2000, 1, 'Stage A', 1, 'Pipeline X', '2024-01-05'),
            createLeadInStageWithPipeline(3000, 2, 'Stage B', 1, 'Pipeline X', '2024-01-10'),
            createLeadInStageWithPipeline(6000, 2, 'Stage B', 1, 'Pipeline X', '2024-01-15'),
        ]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->getAverageDealSizesByStage(1, null, 90);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(2)
            ->and($result[0]['stage_id'])->toBe(1)
            ->and($result[0]['average_deal_size'])->toBe(1500.0) // (1000 + 2000) / 2
            ->and($result[0]['min_deal_size'])->toBe(1000.0)
            ->and($result[0]['max_deal_size'])->toBe(2000.0)
            ->and($result[0]['total_value'])->toBe(3000.0)
            ->and($result[0]['lead_count'])->toBe(2)
            ->and($result[1]['average_deal_size'])->toBe(4500.0); // (3000 + 6000) / 2

        Carbon::setTestNow();
    });
});

describe('getWinRatesByPipeline', function () {
    test('calculates win rates grouped by pipeline', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createLeadWithPipeline(5000, 'won', 1, 'Pipeline A', '2024-01-01', '2024-01-15'),
            createLeadWithPipeline(3000, 'won', 1, 'Pipeline A', '2024-01-02', '2024-01-18'),
            createLeadWithPipeline(2000, 'lost', 1, 'Pipeline A', '2024-01-03', '2024-01-20'),
            createLeadWithPipeline(4000, 'won', 2, 'Pipeline B', '2024-01-04', '2024-01-22'),
        ]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->getWinRatesByPipeline(null, 90);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(2)
            ->and($result[0]['pipeline_id'])->toBe(1)
            ->and($result[0]['total_leads'])->toBe(3)
            ->and($result[0]['won_count'])->toBe(2)
            ->and($result[0]['lost_count'])->toBe(1)
            ->and($result[0]['win_rate'])->toBe(66.67) // 2 / 3 * 100
            ->and($result[0]['total_won_value'])->toBe(8000.0)
            ->and($result[1]['pipeline_id'])->toBe(2)
            ->and($result[1]['win_rate'])->toBe(100.0); // 1 / 1 * 100

        Carbon::setTestNow();
    });

    test('handles pipelines with no closed leads', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createLeadWithPipeline(5000, 'new', 1, 'Pipeline A', '2024-01-01', null),
            createLeadWithPipeline(3000, 'qualified', 1, 'Pipeline A', '2024-01-02', null),
        ]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->getWinRatesByPipeline(null, 90);

        expect($result[0]['win_rate'])->toBe(0.0)
            ->and($result[0]['loss_rate'])->toBe(0.0)
            ->and($result[0]['open_count'])->toBe(2);

        Carbon::setTestNow();
    });
});

describe('getWinRatesByUser', function () {
    test('calculates win rates grouped by user', function () {
        Carbon::setTestNow('2024-01-31 12:00:00');

        $leads = collect([
            createLeadWithUserInfo(5000, 'won', 1, 'User A', '2024-01-01', '2024-01-15'),
            createLeadWithUserInfo(3000, 'lost', 1, 'User A', '2024-01-02', '2024-01-18'),
            createLeadWithUserInfo(2000, 'won', 2, 'User B', '2024-01-03', '2024-01-20'),
            createLeadWithUserInfo(4000, 'won', 2, 'User B', '2024-01-04', '2024-01-22'),
        ]);

        mockLeadQuery($this->leadRepository, $leads);

        $result = $this->service->getWinRatesByUser(null, 90);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(2)
            ->and($result[0]['user_id'])->toBe(1)
            ->and($result[0]['user_name'])->toBe('User A')
            ->and($result[0]['total_leads'])->toBe(2)
            ->and($result[0]['won_count'])->toBe(1)
            ->and($result[0]['win_rate'])->toBe(50.0)
            ->and($result[1]['user_id'])->toBe(2)
            ->and($result[1]['win_rate'])->toBe(100.0);

        Carbon::setTestNow();
    });
});

describe('getPerformanceTrends', function () {
    test('generates monthly performance trends', function () {
        Carbon::setTestNow('2024-06-30 12:00:00');

        // Mock leads for different months
        $this->leadRepository->model
            ->shouldReceive('with')
            ->andReturnSelf()
            ->shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->andReturn(collect([
                createHistoricalLead(5000, 'won', '2024-01-15', '2024-01-25'),
                createHistoricalLead(3000, 'lost', '2024-01-20', '2024-01-28'),
            ]));

        $result = $this->service->getPerformanceTrends(1, null, 3);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(3)
            ->and($result[0])->toHaveKeys(['period', 'total_leads', 'won_count', 'win_rate', 'average_deal_size', 'total_value', 'total_won_value']);

        Carbon::setTestNow();
    });
});

describe('getTopPerformingStages', function () {
    test('retrieves top performing stages sorted by conversion rate', function () {
        $conversions = collect([
            createMockConversion(1, 'Stage A', 1, 'Pipeline X', 85.0, 5.0, 30),
            createMockConversion(2, 'Stage B', 1, 'Pipeline X', 70.0, 8.0, 25),
            createMockConversion(3, 'Stage C', 1, 'Pipeline X', 60.0, 10.0, 20),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getWithFilters')
            ->andReturn($conversions);

        $result = $this->service->getTopPerformingStages(1, 5, 90);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(3)
            ->and($result[0]['stage_id'])->toBe(1)
            ->and($result[0]['conversion_rate'])->toBe(85.0)
            ->and($result[1]['conversion_rate'])->toBe(70.0)
            ->and($result[2]['conversion_rate'])->toBe(60.0);
    });

    test('limits results to specified count', function () {
        $conversions = collect([
            createMockConversion(1, 'Stage A', 1, 'Pipeline X', 85.0, 5.0, 30),
            createMockConversion(2, 'Stage B', 1, 'Pipeline X', 70.0, 8.0, 25),
            createMockConversion(3, 'Stage C', 1, 'Pipeline X', 60.0, 10.0, 20),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getWithFilters')
            ->andReturn($conversions);

        $result = $this->service->getTopPerformingStages(1, 2, 90);

        expect(count($result))->toBe(2);
    });
});

describe('getBottomPerformingStages', function () {
    test('retrieves bottom performing stages sorted by conversion rate', function () {
        $conversions = collect([
            createMockConversion(1, 'Stage A', 1, 'Pipeline X', 15.0, 20.0, 30),
            createMockConversion(2, 'Stage B', 1, 'Pipeline X', 25.0, 18.0, 25),
            createMockConversion(3, 'Stage C', 1, 'Pipeline X', 35.0, 15.0, 20),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getWithFilters')
            ->andReturn($conversions);

        $result = $this->service->getBottomPerformingStages(1, 5, 90);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(3)
            ->and($result[0]['conversion_rate'])->toBe(15.0)
            ->and($result[1]['conversion_rate'])->toBe(25.0)
            ->and($result[2]['conversion_rate'])->toBe(35.0);
    });
});

// Helper functions to create mock objects

function createHistoricalLead(float $value, string $stageCode, string $createdAt, ?string $closedAt): object
{
    $stage = new class ($stageCode) extends Model {
        public $code;

        public function __construct($code)
        {
            $this->code = $code;
        }
    };

    return new class ($value, $stage, $createdAt, $closedAt) extends Model {
        public $lead_value;
        public $stage;
        public $created_at;
        public $closed_at;
        public $updated_at;

        public function __construct($value, $stage, $createdAt, $closedAt)
        {
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->created_at = Carbon::parse($createdAt);
            $this->closed_at = $closedAt ? Carbon::parse($closedAt) : null;
            $this->updated_at = Carbon::parse($createdAt);
        }
    };
}

function createHistoricalLeadWithUser(float $value, string $stageCode, int $userId, string $createdAt, ?string $closedAt): object
{
    $stage = new class ($stageCode) extends Model {
        public $code;

        public function __construct($code)
        {
            $this->code = $code;
        }
    };

    $user = new class ($userId) extends Model {
        public $id;
        public $name;

        public function __construct($userId)
        {
            $this->id = $userId;
            $this->name = "User {$userId}";
        }
    };

    return new class ($value, $stage, $user, $createdAt, $closedAt) extends Model {
        public $lead_value;
        public $stage;
        public $user;
        public $user_id;
        public $created_at;
        public $closed_at;
        public $updated_at;

        public function __construct($value, $stage, $user, $createdAt, $closedAt)
        {
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->user = $user;
            $this->user_id = $user->id;
            $this->created_at = Carbon::parse($createdAt);
            $this->closed_at = $closedAt ? Carbon::parse($closedAt) : null;
            $this->updated_at = Carbon::parse($createdAt);
        }
    };
}

function createHistoricalLeadInStage(float $value, string $stageCode, int $stageId, string $createdAt): object
{
    $stage = new class ($stageCode, $stageId) extends Model {
        public $code;
        public $id;

        public function __construct($code, $id)
        {
            $this->code = $code;
            $this->id = $id;
        }
    };

    return new class ($value, $stage, $stageId, $createdAt) extends Model {
        public $lead_value;
        public $stage;
        public $lead_pipeline_stage_id;
        public $created_at;
        public $updated_at;

        public function __construct($value, $stage, $stageId, $createdAt)
        {
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->lead_pipeline_stage_id = $stageId;
            $this->created_at = Carbon::parse($createdAt);
            $this->updated_at = Carbon::parse($createdAt);
        }
    };
}

function createLeadInStageWithPipeline(float $value, int $stageId, string $stageName, int $pipelineId, string $pipelineName, string $createdAt): object
{
    $stage = new class ($stageId, $stageName) extends Model {
        public $id;
        public $name;

        public function __construct($id, $name)
        {
            $this->id = $id;
            $this->name = $name;
        }
    };

    $pipeline = new class ($pipelineId, $pipelineName) extends Model {
        public $id;
        public $name;

        public function __construct($id, $name)
        {
            $this->id = $id;
            $this->name = $name;
        }
    };

    return new class ($value, $stage, $pipeline, $stageId, $pipelineId, $createdAt) extends Model {
        public $lead_value;
        public $stage;
        public $pipeline;
        public $lead_pipeline_stage_id;
        public $lead_pipeline_id;
        public $created_at;

        public function __construct($value, $stage, $pipeline, $stageId, $pipelineId, $createdAt)
        {
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->pipeline = $pipeline;
            $this->lead_pipeline_stage_id = $stageId;
            $this->lead_pipeline_id = $pipelineId;
            $this->created_at = Carbon::parse($createdAt);
        }
    };
}

function createLeadWithPipeline(float $value, string $stageCode, int $pipelineId, string $pipelineName, string $createdAt, ?string $closedAt): object
{
    $stage = new class ($stageCode) extends Model {
        public $code;

        public function __construct($code)
        {
            $this->code = $code;
        }
    };

    $pipeline = new class ($pipelineId, $pipelineName) extends Model {
        public $id;
        public $name;

        public function __construct($id, $name)
        {
            $this->id = $id;
            $this->name = $name;
        }
    };

    return new class ($value, $stage, $pipeline, $pipelineId, $createdAt, $closedAt) extends Model {
        public $lead_value;
        public $stage;
        public $pipeline;
        public $lead_pipeline_id;
        public $created_at;
        public $closed_at;

        public function __construct($value, $stage, $pipeline, $pipelineId, $createdAt, $closedAt)
        {
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->pipeline = $pipeline;
            $this->lead_pipeline_id = $pipelineId;
            $this->created_at = Carbon::parse($createdAt);
            $this->closed_at = $closedAt ? Carbon::parse($closedAt) : null;
        }
    };
}

function createLeadWithUserInfo(float $value, string $stageCode, int $userId, string $userName, string $createdAt, ?string $closedAt): object
{
    $stage = new class ($stageCode) extends Model {
        public $code;

        public function __construct($code)
        {
            $this->code = $code;
        }
    };

    $user = new class ($userId, $userName) extends Model {
        public $id;
        public $name;

        public function __construct($id, $name)
        {
            $this->id = $id;
            $this->name = $name;
        }
    };

    return new class ($value, $stage, $user, $userId, $createdAt, $closedAt) extends Model {
        public $lead_value;
        public $stage;
        public $user;
        public $user_id;
        public $created_at;
        public $closed_at;

        public function __construct($value, $stage, $user, $userId, $createdAt, $closedAt)
        {
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->user = $user;
            $this->user_id = $userId;
            $this->created_at = Carbon::parse($createdAt);
            $this->closed_at = $closedAt ? Carbon::parse($closedAt) : null;
        }
    };
}

function createMockConversion(int $stageId, string $stageName, int $pipelineId, string $pipelineName, float $conversionRate, float $avgTime, int $sampleSize): object
{
    $stage = new class ($stageName) extends Model {
        public $name;

        public function __construct($name)
        {
            $this->name = $name;
        }
    };

    $pipeline = new class ($pipelineName) extends Model {
        public $name;

        public function __construct($name)
        {
            $this->name = $name;
        }
    };

    return new class ($stageId, $stage, $pipelineId, $pipeline, $conversionRate, $avgTime, $sampleSize) extends Model {
        public $stage_id;
        public $stage;
        public $pipeline_id;
        public $pipeline;
        public $conversion_rate;
        public $average_time_in_stage;
        public $sample_size;

        public function __construct($stageId, $stage, $pipelineId, $pipeline, $conversionRate, $avgTime, $sampleSize)
        {
            $this->stage_id = $stageId;
            $this->stage = $stage;
            $this->pipeline_id = $pipelineId;
            $this->pipeline = $pipeline;
            $this->conversion_rate = $conversionRate;
            $this->average_time_in_stage = $avgTime;
            $this->sample_size = $sampleSize;
        }
    };
}

function mockLeadQuery($repository, $leads): void
{
    $repository->model
        ->shouldReceive('with')
        ->andReturnSelf()
        ->shouldReceive('where')
        ->andReturnSelf()
        ->shouldReceive('get')
        ->andReturn($leads);
}

function mockLeadQueryWithStageFilter($repository, $leads, int $stageId): void
{
    $repository->model
        ->shouldReceive('with')
        ->andReturnSelf()
        ->shouldReceive('where')
        ->andReturnSelf()
        ->shouldReceive('get')
        ->andReturn($leads);
}
