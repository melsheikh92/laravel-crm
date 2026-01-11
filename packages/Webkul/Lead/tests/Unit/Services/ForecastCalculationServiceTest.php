<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\SalesForecastRepository;
use Webkul\Lead\Services\ForecastCalculationService;

uses(TestCase::class);

beforeEach(function () {
    $this->leadRepository = Mockery::mock(LeadRepository::class);
    $this->salesForecastRepository = Mockery::mock(SalesForecastRepository::class);
    $this->historicalConversionRepository = Mockery::mock(HistoricalConversionRepository::class);

    $this->service = new ForecastCalculationService(
        $this->leadRepository,
        $this->salesForecastRepository,
        $this->historicalConversionRepository
    );
});

afterEach(function () {
    Mockery::close();
});

describe('calculateWeightedForecast', function () {
    test('calculates weighted forecast correctly with multiple leads', function () {
        $leads = collect([
            createMockLead(1000, 75.0),
            createMockLead(2000, 50.0),
            createMockLead(3000, 25.0),
        ]);

        $result = $this->service->calculateWeightedForecast($leads);

        // Expected: (1000 * 0.75) + (2000 * 0.50) + (3000 * 0.25) = 750 + 1000 + 750 = 2500
        expect($result)->toBe(2500.0);
    });

    test('calculates weighted forecast with 100% probability', function () {
        $leads = collect([
            createMockLead(1000, 100.0),
            createMockLead(2000, 100.0),
        ]);

        $result = $this->service->calculateWeightedForecast($leads);

        // Expected: (1000 * 1.0) + (2000 * 1.0) = 3000
        expect($result)->toBe(3000.0);
    });

    test('calculates weighted forecast with 0% probability', function () {
        $leads = collect([
            createMockLead(1000, 0.0),
            createMockLead(2000, 0.0),
        ]);

        $result = $this->service->calculateWeightedForecast($leads);

        // Expected: (1000 * 0.0) + (2000 * 0.0) = 0
        expect($result)->toBe(0.0);
    });

    test('handles empty leads collection', function () {
        $leads = collect([]);

        $result = $this->service->calculateWeightedForecast($leads);

        expect($result)->toBe(0.0);
    });

    test('handles leads with null values', function () {
        $leads = collect([
            createMockLead(null, 50.0),
            createMockLead(1000, 50.0),
        ]);

        $result = $this->service->calculateWeightedForecast($leads);

        // Expected: (0 * 0.50) + (1000 * 0.50) = 500
        expect($result)->toBe(500.0);
    });

    test('handles leads with missing stage', function () {
        $lead = new class extends Model {
            public $lead_value = 1000;
            public $stage = null;
        };

        $leads = collect([$lead]);

        $result = $this->service->calculateWeightedForecast($leads);

        // Expected: 1000 * 0.50 (default probability) = 500
        expect($result)->toBe(500.0);
    });

    test('calculates weighted forecast with decimal values', function () {
        $leads = collect([
            createMockLead(1234.56, 33.33),
            createMockLead(7890.12, 66.67),
        ]);

        $result = $this->service->calculateWeightedForecast($leads);

        // Expected: (1234.56 * 0.3333) + (7890.12 * 0.6667) = 411.52 + 5260.41 = 5671.93
        expect($result)->toBeCloseTo(5671.93, 0.5);
    });

    test('calculates weighted forecast with varying probabilities', function () {
        $leads = collect([
            createMockLead(5000, 10.0),
            createMockLead(5000, 30.0),
            createMockLead(5000, 50.0),
            createMockLead(5000, 70.0),
            createMockLead(5000, 90.0),
        ]);

        $result = $this->service->calculateWeightedForecast($leads);

        // Expected: 5000 * (0.10 + 0.30 + 0.50 + 0.70 + 0.90) = 5000 * 2.5 = 12500
        expect($result)->toBe(12500.0);
    });
});

describe('calculateBestCase', function () {
    test('sums all lead values correctly', function () {
        $leads = collect([
            createMockLead(1000, 25.0),
            createMockLead(2000, 50.0),
            createMockLead(3000, 75.0),
        ]);

        $result = $this->service->calculateBestCase($leads);

        // Best case assumes all deals close, so just sum values
        expect($result)->toBe(6000.0);
    });

    test('handles empty leads collection', function () {
        $leads = collect([]);

        $result = $this->service->calculateBestCase($leads);

        expect($result)->toBe(0.0);
    });

    test('handles leads with null values', function () {
        $leads = collect([
            createMockLead(null, 50.0),
            createMockLead(1000, 50.0),
            createMockLead(2000, 50.0),
        ]);

        $result = $this->service->calculateBestCase($leads);

        // Expected: 0 + 1000 + 2000 = 3000
        expect($result)->toBe(3000.0);
    });

    test('ignores stage probability in best case', function () {
        $leads = collect([
            createMockLead(1000, 0.0),  // Even with 0% probability
            createMockLead(2000, 100.0),
        ]);

        $result = $this->service->calculateBestCase($leads);

        // Best case includes all values regardless of probability
        expect($result)->toBe(3000.0);
    });

    test('handles large numbers correctly', function () {
        $leads = collect([
            createMockLead(1000000, 50.0),
            createMockLead(2000000, 50.0),
            createMockLead(3000000, 50.0),
        ]);

        $result = $this->service->calculateBestCase($leads);

        expect($result)->toBe(6000000.0);
    });

    test('handles decimal values correctly', function () {
        $leads = collect([
            createMockLead(1234.56, 50.0),
            createMockLead(7890.12, 50.0),
        ]);

        $result = $this->service->calculateBestCase($leads);

        expect($result)->toBe(9124.68);
    });
});

describe('calculateWorstCase', function () {
    test('calculates worst case with historical conversion rates', function () {
        $leads = collect([
            createMockLeadWithHistorical(1000, 75.0, 1, 1, 1),
            createMockLeadWithHistorical(2000, 50.0, 2, 1, 1),
        ]);

        // Mock historical conversion rates
        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(1, 1, 1)
            ->andReturn(60.0);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(2, 1, 1)
            ->andReturn(40.0);

        $result = $this->service->calculateWorstCase($leads);

        // Expected: (1000 * 0.60) + (2000 * 0.40) = 600 + 800 = 1400
        expect($result)->toBe(1400.0);
    });

    test('uses pessimistic fallback when no historical data', function () {
        $leads = collect([
            createMockLeadWithHistorical(1000, 80.0, 1, 1, 1),
        ]);

        // Mock no historical data
        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(1, 1, 1)
            ->andReturn(null);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(1, 1, null)
            ->andReturn(null);

        $result = $this->service->calculateWorstCase($leads);

        // Expected: 1000 * (80 * 0.5) / 100 = 1000 * 0.40 = 400
        expect($result)->toBe(400.0);
    });

    test('handles empty leads collection', function () {
        $leads = collect([]);

        $result = $this->service->calculateWorstCase($leads);

        expect($result)->toBe(0.0);
    });

    test('handles leads with null values', function () {
        $leads = collect([
            createMockLeadWithHistorical(null, 50.0, 1, 1, 1),
            createMockLeadWithHistorical(1000, 50.0, 2, 1, 1),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->andReturn(null);

        $result = $this->service->calculateWorstCase($leads);

        // Expected: (0 * 0.25) + (1000 * 0.25) = 250
        expect($result)->toBe(250.0);
    });

    test('uses general historical rate when user-specific rate not available', function () {
        $leads = collect([
            createMockLeadWithHistorical(1000, 80.0, 1, 1, 1),
        ]);

        // Mock user-specific rate not found, but general rate found
        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(1, 1, 1)
            ->andReturn(null);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(1, 1, null)
            ->andReturn(50.0);

        $result = $this->service->calculateWorstCase($leads);

        // Expected: 1000 * 0.50 = 500
        expect($result)->toBe(500.0);
    });

    test('calculates worst case with mixed data sources', function () {
        $leads = collect([
            createMockLeadWithHistorical(1000, 80.0, 1, 1, 1), // Has historical
            createMockLeadWithHistorical(2000, 60.0, 2, 1, 2), // No historical, use fallback
        ]);

        // First lead has historical data
        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(1, 1, 1)
            ->andReturn(70.0);

        // Second lead has no historical data
        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(2, 1, 2)
            ->andReturn(null);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(2, 1, null)
            ->andReturn(null);

        $result = $this->service->calculateWorstCase($leads);

        // Expected: (1000 * 0.70) + (2000 * (60 * 0.5) / 100) = 700 + 600 = 1300
        expect($result)->toBe(1300.0);
    });

    test('handles leads without pipeline information', function () {
        $lead = new class extends Model {
            public $lead_value = 1000;
            public $lead_pipeline_stage_id = null;
            public $lead_pipeline_id = null;
            public $stage = null;
        };

        $leads = collect([$lead]);

        $result = $this->service->calculateWorstCase($leads);

        // Expected: 1000 * (50 * 0.5) / 100 = 250 (default probability 50%)
        expect($result)->toBe(250.0);
    });
});

describe('getForecastScenarios', function () {
    test('returns all three forecast scenarios', function () {
        $leads = collect([
            createMockLeadWithHistorical(1000, 75.0, 1, 1, 1),
            createMockLeadWithHistorical(2000, 50.0, 2, 1, 1),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->andReturn(40.0);

        $result = $this->service->getForecastScenarios($leads);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['weighted', 'best_case', 'worst_case'])
            ->and($result['weighted'])->toHaveKeys(['value', 'description'])
            ->and($result['best_case'])->toHaveKeys(['value', 'description'])
            ->and($result['worst_case'])->toHaveKeys(['value', 'description']);
    });

    test('weighted scenario is less than best case', function () {
        $leads = collect([
            createMockLead(1000, 50.0),
            createMockLead(2000, 50.0),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->andReturn(null);

        $result = $this->service->getForecastScenarios($leads);

        expect($result['weighted']['value'])->toBeLessThanOrEqual($result['best_case']['value']);
    });

    test('worst case is less than or equal to weighted scenario', function () {
        $leads = collect([
            createMockLeadWithHistorical(1000, 50.0, 1, 1, 1),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->andReturn(30.0);

        $result = $this->service->getForecastScenarios($leads);

        expect($result['worst_case']['value'])->toBeLessThanOrEqual($result['weighted']['value']);
    });

    test('all scenarios return zero for empty leads', function () {
        $leads = collect([]);

        $result = $this->service->getForecastScenarios($leads);

        expect($result['weighted']['value'])->toBe(0.0)
            ->and($result['best_case']['value'])->toBe(0.0)
            ->and($result['worst_case']['value'])->toBe(0.0);
    });

    test('scenarios have descriptive messages', function () {
        $leads = collect([createMockLead(1000, 50.0)]);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->andReturn(null);

        $result = $this->service->getForecastScenarios($leads);

        expect($result['weighted']['description'])->toBeString()
            ->and($result['weighted']['description'])->toContain('stage probabilities')
            ->and($result['best_case']['description'])->toBeString()
            ->and($result['best_case']['description'])->toContain('All deals close')
            ->and($result['worst_case']['description'])->toBeString()
            ->and($result['worst_case']['description'])->toContain('Pessimistic');
    });

    test('calculates realistic scenario values', function () {
        $leads = collect([
            createMockLeadWithHistorical(10000, 80.0, 1, 1, 1),
            createMockLeadWithHistorical(20000, 60.0, 2, 1, 1),
            createMockLeadWithHistorical(30000, 40.0, 3, 1, 1),
        ]);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(1, 1, 1)
            ->andReturn(70.0);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(2, 1, 1)
            ->andReturn(50.0);

        $this->historicalConversionRepository
            ->shouldReceive('getConversionRate')
            ->with(3, 1, 1)
            ->andReturn(30.0);

        $result = $this->service->getForecastScenarios($leads);

        // Best case: 10000 + 20000 + 30000 = 60000
        expect($result['best_case']['value'])->toBe(60000.0);

        // Weighted: (10000 * 0.80) + (20000 * 0.60) + (30000 * 0.40) = 8000 + 12000 + 12000 = 32000
        expect($result['weighted']['value'])->toBe(32000.0);

        // Worst case: (10000 * 0.70) + (20000 * 0.50) + (30000 * 0.30) = 7000 + 10000 + 9000 = 26000
        expect($result['worst_case']['value'])->toBe(26000.0);

        // Verify ordering: worst <= weighted <= best
        expect($result['worst_case']['value'])->toBeLessThanOrEqual($result['weighted']['value'])
            ->and($result['weighted']['value'])->toBeLessThanOrEqual($result['best_case']['value']);
    });
});

// Helper functions to create mock leads
function createMockLead(float|null $value, float $probability): object
{
    $stage = new class ($probability) extends Model {
        public $probability;

        public function __construct($probability)
        {
            $this->probability = $probability;
        }
    };

    return new class ($value, $stage) extends Model {
        public $lead_value;
        public $stage;

        public function __construct($value, $stage)
        {
            $this->lead_value = $value;
            $this->stage = $stage;
        }
    };
}

function createMockLeadWithHistorical(float|null $value, float $probability, int $stageId, int $pipelineId, int $userId): object
{
    $stage = new class ($probability) extends Model {
        public $probability;

        public function __construct($probability)
        {
            $this->probability = $probability;
        }
    };

    return new class ($value, $stage, $stageId, $pipelineId, $userId) extends Model {
        public $lead_value;
        public $stage;
        public $lead_pipeline_stage_id;
        public $lead_pipeline_id;
        public $user_id;

        public function __construct($value, $stage, $stageId, $pipelineId, $userId)
        {
            $this->lead_value = $value;
            $this->stage = $stage;
            $this->lead_pipeline_stage_id = $stageId;
            $this->lead_pipeline_id = $pipelineId;
            $this->user_id = $userId;
        }
    };
}
