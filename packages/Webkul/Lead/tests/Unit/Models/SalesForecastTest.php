<?php

use Illuminate\Support\Carbon;
use Tests\TestCase;
use Webkul\Lead\Models\ForecastActual;
use Webkul\Lead\Models\SalesForecast;
use Webkul\User\Models\User;

uses(TestCase::class);

beforeEach(function () {
    $this->forecast = new SalesForecast();
});

describe('fillable attributes', function () {
    test('has correct fillable attributes', function () {
        $expected = [
            'user_id',
            'team_id',
            'period_type',
            'period_start',
            'period_end',
            'forecast_value',
            'weighted_forecast',
            'best_case',
            'worst_case',
            'confidence_score',
            'metadata',
        ];

        expect($this->forecast->getFillable())->toBe($expected);
    });
});

describe('casts', function () {
    test('casts date attributes correctly', function () {
        $forecast = new SalesForecast([
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
        ]);

        expect($forecast->period_start)->toBeInstanceOf(Carbon::class)
            ->and($forecast->period_end)->toBeInstanceOf(Carbon::class);
    });

    test('casts decimal attributes correctly', function () {
        $forecast = new SalesForecast([
            'forecast_value' => '50000.1234',
            'weighted_forecast' => '45000.5678',
            'best_case' => '60000.9012',
            'worst_case' => '40000.3456',
            'confidence_score' => '75.25',
        ]);

        expect($forecast->forecast_value)->toBeString()
            ->and($forecast->weighted_forecast)->toBeString()
            ->and($forecast->best_case)->toBeString()
            ->and($forecast->worst_case)->toBeString()
            ->and($forecast->confidence_score)->toBeString();
    });

    test('casts metadata as array', function () {
        $metadata = ['key' => 'value', 'deals_count' => 10];
        $forecast = new SalesForecast([
            'metadata' => $metadata,
        ]);

        expect($forecast->metadata)->toBeArray()
            ->and($forecast->metadata)->toBe($metadata);
    });
});

describe('relationships', function () {
    test('has user relationship', function () {
        $relation = $this->forecast->user();

        expect($relation)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });

    test('has actuals relationship', function () {
        $relation = $this->forecast->actuals();

        expect($relation)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    test('has latestActual relationship', function () {
        $relation = $this->forecast->latestActual();

        expect($relation)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class);
    });
});

describe('getVariance', function () {
    test('returns null when no actuals exist', function () {
        $forecast = createMockSalesForecast(50000);

        expect($forecast->getVariance())->toBeNull();
    });

    test('returns variance from latest actual', function () {
        $forecast = createMockSalesForecastWithActual(50000, 52000);

        expect($forecast->getVariance())->toBe(2000.0);
    });
});

describe('getVariancePercentage', function () {
    test('returns null when no actuals exist', function () {
        $forecast = createMockSalesForecast(50000);

        expect($forecast->getVariancePercentage())->toBeNull();
    });

    test('returns variance percentage from latest actual', function () {
        $forecast = createMockSalesForecastWithActual(50000, 52000);

        expect($forecast->getVariancePercentage())->toBe(4.0);
    });
});

describe('getAccuracyScore', function () {
    test('returns null when no actuals exist', function () {
        $forecast = createMockSalesForecast(50000);

        expect($forecast->getAccuracyScore())->toBeNull();
    });

    test('calculates accuracy score correctly for positive variance', function () {
        $forecast = createMockSalesForecastWithActual(50000, 52000);

        // variance_percentage = 4.0
        // accuracy = 100 - abs(4.0) = 96.0
        expect($forecast->getAccuracyScore())->toBe(96.0);
    });

    test('calculates accuracy score correctly for negative variance', function () {
        $forecast = createMockSalesForecastWithActual(50000, 48000);

        // variance_percentage = -4.0
        // accuracy = 100 - abs(-4.0) = 96.0
        expect($forecast->getAccuracyScore())->toBe(96.0);
    });

    test('returns zero for very inaccurate forecasts', function () {
        $forecast = createMockSalesForecastWithActual(50000, 5000);

        // variance_percentage = -90.0
        // accuracy = max(0, 100 - 90) = 10.0
        expect($forecast->getAccuracyScore())->toBe(10.0);
    });
});

describe('isPeriodEnded', function () {
    test('returns true for past periods', function () {
        $forecast = new SalesForecast([
            'period_start' => now()->subMonths(2),
            'period_end' => now()->subMonth(),
        ]);

        expect($forecast->isPeriodEnded())->toBeTrue();
    });

    test('returns false for future periods', function () {
        $forecast = new SalesForecast([
            'period_start' => now()->addWeek(),
            'period_end' => now()->addMonth(),
        ]);

        expect($forecast->isPeriodEnded())->toBeFalse();
    });

    test('returns false for current periods', function () {
        $forecast = new SalesForecast([
            'period_start' => now()->subWeek(),
            'period_end' => now()->addWeek(),
        ]);

        expect($forecast->isPeriodEnded())->toBeFalse();
    });
});

describe('scenario calculations', function () {
    test('calculates scenario spread correctly', function () {
        $forecast = new SalesForecast([
            'best_case' => 60000,
            'worst_case' => 40000,
        ]);

        expect($forecast->getScenarioSpread())->toBe(20000.0);
    });

    test('calculates scenario spread percentage correctly', function () {
        $forecast = new SalesForecast([
            'forecast_value' => 50000,
            'best_case' => 60000,
            'worst_case' => 40000,
        ]);

        // (60000 - 40000) / 50000 * 100 = 40%
        expect($forecast->getScenarioSpreadPercentage())->toBe(40.0);
    });

    test('handles zero forecast value for spread percentage', function () {
        $forecast = new SalesForecast([
            'forecast_value' => 0,
            'best_case' => 10000,
            'worst_case' => 5000,
        ]);

        expect($forecast->getScenarioSpreadPercentage())->toBe(0.0);
    });

    test('calculates upside potential correctly', function () {
        $forecast = new SalesForecast([
            'forecast_value' => 50000,
            'best_case' => 60000,
        ]);

        expect($forecast->getUpsidePotential())->toBe(10000.0);
    });

    test('calculates downside risk correctly', function () {
        $forecast = new SalesForecast([
            'forecast_value' => 50000,
            'worst_case' => 40000,
        ]);

        expect($forecast->getDownsideRisk())->toBe(10000.0);
    });
});

describe('confidence levels', function () {
    test('identifies high confidence forecasts', function () {
        $forecast = new SalesForecast(['confidence_score' => 85]);

        expect($forecast->isHighConfidence())->toBeTrue()
            ->and($forecast->isMediumConfidence())->toBeFalse()
            ->and($forecast->isLowConfidence())->toBeFalse()
            ->and($forecast->getConfidenceLevel())->toBe('high');
    });

    test('identifies medium confidence forecasts', function () {
        $forecast = new SalesForecast(['confidence_score' => 65]);

        expect($forecast->isHighConfidence())->toBeFalse()
            ->and($forecast->isMediumConfidence())->toBeTrue()
            ->and($forecast->isLowConfidence())->toBeFalse()
            ->and($forecast->getConfidenceLevel())->toBe('medium');
    });

    test('identifies low confidence forecasts', function () {
        $forecast = new SalesForecast(['confidence_score' => 30]);

        expect($forecast->isHighConfidence())->toBeFalse()
            ->and($forecast->isMediumConfidence())->toBeFalse()
            ->and($forecast->isLowConfidence())->toBeTrue()
            ->and($forecast->getConfidenceLevel())->toBe('low');
    });

    test('handles confidence at boundary values', function () {
        $highBoundary = new SalesForecast(['confidence_score' => 80]);
        $mediumBoundary = new SalesForecast(['confidence_score' => 50]);

        expect($highBoundary->isHighConfidence())->toBeTrue()
            ->and($mediumBoundary->isMediumConfidence())->toBeTrue();
    });
});

describe('scopes', function () {
    test('byPeriodType scope filters correctly', function () {
        $query = SalesForecast::query();
        $scopedQuery = $query->byPeriodType('month');

        expect($scopedQuery->toSql())->toContain('where "period_type" = ?');
    });

    test('byUser scope filters correctly', function () {
        $query = SalesForecast::query();
        $scopedQuery = $query->byUser(1);

        expect($scopedQuery->toSql())->toContain('where "user_id" = ?');
    });

    test('byTeam scope filters correctly', function () {
        $query = SalesForecast::query();
        $scopedQuery = $query->byTeam(1);

        expect($scopedQuery->toSql())->toContain('where "team_id" = ?');
    });

    test('inDateRange scope filters correctly', function () {
        $query = SalesForecast::query();
        $scopedQuery = $query->inDateRange('2026-01-01', '2026-01-31');

        $sql = $scopedQuery->toSql();
        expect($sql)->toContain('where "period_start" >= ?')
            ->and($sql)->toContain('and "period_end" <= ?');
    });

    test('completed scope filters forecasts with actuals', function () {
        $query = SalesForecast::query();
        $scopedQuery = $query->completed();

        expect($scopedQuery->toSql())->toContain('exists');
    });

    test('pending scope filters forecasts without actuals', function () {
        $query = SalesForecast::query();
        $scopedQuery = $query->pending();

        expect($scopedQuery->toSql())->toContain('not exists');
    });

    test('highConfidence scope filters correctly', function () {
        $query = SalesForecast::query();
        $scopedQuery = $query->highConfidence();

        expect($scopedQuery->toSql())->toContain('where "confidence_score" >= ?');
    });
});

// Helper functions to create mock objects
function createMockSalesForecast(float $forecastValue, float $confidence = 75.0): SalesForecast
{
    return new class($forecastValue, $confidence) extends SalesForecast {
        private $mockLatestActual = null;

        public function __construct($forecastValue, $confidence)
        {
            parent::__construct();
            $this->forecast_value = $forecastValue;
            $this->confidence_score = $confidence;
            $this->period_end = now()->addMonth();
        }

        public function __get($key)
        {
            if ($key === 'latestActual') {
                return $this->mockLatestActual;
            }

            return parent::__get($key);
        }

        public function setMockLatestActual($actual)
        {
            $this->mockLatestActual = $actual;
        }
    };
}

function createMockSalesForecastWithActual(float $forecastValue, float $actualValue): SalesForecast
{
    $forecast = createMockSalesForecast($forecastValue);

    $variance = $actualValue - $forecastValue;
    $variancePercentage = ($variance / $forecastValue) * 100;

    $actual = new class($variance, $variancePercentage) {
        public $variance;
        public $variance_percentage;

        public function __construct($variance, $variancePercentage)
        {
            $this->variance = $variance;
            $this->variance_percentage = $variancePercentage;
        }
    };

    $forecast->setMockLatestActual($actual);

    return $forecast;
}
