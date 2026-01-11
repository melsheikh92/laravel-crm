<?php

use Illuminate\Support\Carbon;
use Tests\TestCase;
use Webkul\Lead\Models\DealScore;
use Webkul\Lead\Models\Lead;

uses(TestCase::class);

beforeEach(function () {
    $this->dealScore = new DealScore();
});

describe('fillable attributes', function () {
    test('has correct fillable attributes', function () {
        $expected = [
            'lead_id',
            'score',
            'win_probability',
            'velocity_score',
            'engagement_score',
            'value_score',
            'historical_pattern_score',
            'factors',
            'generated_at',
        ];

        expect($this->dealScore->getFillable())->toBe($expected);
    });
});

describe('casts', function () {
    test('casts decimal attributes correctly', function () {
        $dealScore = new DealScore([
            'score' => '85.50',
            'win_probability' => '70.25',
            'velocity_score' => '80.00',
            'engagement_score' => '75.50',
            'value_score' => '90.00',
            'historical_pattern_score' => '65.75',
        ]);

        expect($dealScore->score)->toBeString()
            ->and($dealScore->win_probability)->toBeString()
            ->and($dealScore->velocity_score)->toBeString()
            ->and($dealScore->engagement_score)->toBeString()
            ->and($dealScore->value_score)->toBeString()
            ->and($dealScore->historical_pattern_score)->toBeString();
    });

    test('casts generated_at as datetime', function () {
        $dealScore = new DealScore([
            'generated_at' => '2026-01-15 10:30:00',
        ]);

        expect($dealScore->generated_at)->toBeInstanceOf(Carbon::class);
    });

    test('casts factors as array', function () {
        $factors = ['engagement' => 'high', 'velocity' => 'fast'];
        $dealScore = new DealScore([
            'factors' => $factors,
        ]);

        expect($dealScore->factors)->toBeArray()
            ->and($dealScore->factors)->toBe($factors);
    });
});

describe('relationships', function () {
    test('has lead relationship', function () {
        $relation = $this->dealScore->lead();

        expect($relation)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });
});

describe('priority levels', function () {
    test('identifies high priority deals', function () {
        $dealScore = new DealScore(['score' => 85]);

        expect($dealScore->isHighPriority())->toBeTrue()
            ->and($dealScore->isMediumPriority())->toBeFalse()
            ->and($dealScore->isLowPriority())->toBeFalse()
            ->and($dealScore->getPriorityLevel())->toBe('high');
    });

    test('identifies medium priority deals', function () {
        $dealScore = new DealScore(['score' => 65]);

        expect($dealScore->isHighPriority())->toBeFalse()
            ->and($dealScore->isMediumPriority())->toBeTrue()
            ->and($dealScore->isLowPriority())->toBeFalse()
            ->and($dealScore->getPriorityLevel())->toBe('medium');
    });

    test('identifies low priority deals', function () {
        $dealScore = new DealScore(['score' => 30]);

        expect($dealScore->isHighPriority())->toBeFalse()
            ->and($dealScore->isMediumPriority())->toBeFalse()
            ->and($dealScore->isLowPriority())->toBeTrue()
            ->and($dealScore->getPriorityLevel())->toBe('low');
    });

    test('handles priority at boundary values', function () {
        $highBoundary = new DealScore(['score' => 80]);
        $mediumBoundary = new DealScore(['score' => 50]);

        expect($highBoundary->isHighPriority())->toBeTrue()
            ->and($mediumBoundary->isMediumPriority())->toBeTrue();
    });
});

describe('win probability levels', function () {
    test('identifies high win probability', function () {
        $dealScore = new DealScore(['win_probability' => 75]);

        expect($dealScore->hasHighWinProbability())->toBeTrue()
            ->and($dealScore->hasMediumWinProbability())->toBeFalse()
            ->and($dealScore->hasLowWinProbability())->toBeFalse()
            ->and($dealScore->getWinProbabilityLevel())->toBe('high');
    });

    test('identifies medium win probability', function () {
        $dealScore = new DealScore(['win_probability' => 55]);

        expect($dealScore->hasHighWinProbability())->toBeFalse()
            ->and($dealScore->hasMediumWinProbability())->toBeTrue()
            ->and($dealScore->hasLowWinProbability())->toBeFalse()
            ->and($dealScore->getWinProbabilityLevel())->toBe('medium');
    });

    test('identifies low win probability', function () {
        $dealScore = new DealScore(['win_probability' => 25]);

        expect($dealScore->hasHighWinProbability())->toBeFalse()
            ->and($dealScore->hasMediumWinProbability())->toBeFalse()
            ->and($dealScore->hasLowWinProbability())->toBeTrue()
            ->and($dealScore->getWinProbabilityLevel())->toBe('low');
    });

    test('handles win probability at boundary values', function () {
        $highBoundary = new DealScore(['win_probability' => 70]);
        $mediumBoundary = new DealScore(['win_probability' => 40]);

        expect($highBoundary->hasHighWinProbability())->toBeTrue()
            ->and($mediumBoundary->hasMediumWinProbability())->toBeTrue();
    });
});

describe('score component checks', function () {
    test('identifies strong engagement', function () {
        $dealScore = new DealScore(['engagement_score' => 75]);

        expect($dealScore->hasStrongEngagement())->toBeTrue();
    });

    test('identifies weak engagement', function () {
        $dealScore = new DealScore(['engagement_score' => 50]);

        expect($dealScore->hasStrongEngagement())->toBeFalse();
    });

    test('identifies fast velocity', function () {
        $dealScore = new DealScore(['velocity_score' => 80]);

        expect($dealScore->hasFastVelocity())->toBeTrue();
    });

    test('identifies slow velocity', function () {
        $dealScore = new DealScore(['velocity_score' => 60]);

        expect($dealScore->hasFastVelocity())->toBeFalse();
    });

    test('identifies high value', function () {
        $dealScore = new DealScore(['value_score' => 85]);

        expect($dealScore->hasHighValue())->toBeTrue();
    });

    test('identifies low value', function () {
        $dealScore = new DealScore(['value_score' => 55]);

        expect($dealScore->hasHighValue())->toBeFalse();
    });
});

describe('score freshness', function () {
    test('identifies stale scores', function () {
        $dealScore = new DealScore([
            'generated_at' => now()->subHours(25),
        ]);

        expect($dealScore->isStale())->toBeTrue()
            ->and($dealScore->isFresh())->toBeFalse();
    });

    test('identifies fresh scores', function () {
        $dealScore = new DealScore([
            'generated_at' => now()->subHours(12),
        ]);

        expect($dealScore->isStale())->toBeFalse()
            ->and($dealScore->isFresh())->toBeTrue();
    });

    test('handles custom staleness threshold', function () {
        $dealScore = new DealScore([
            'generated_at' => now()->subHours(10),
        ]);

        expect($dealScore->isStale(8))->toBeTrue()
            ->and($dealScore->isFresh(8))->toBeFalse()
            ->and($dealScore->isStale(12))->toBeFalse()
            ->and($dealScore->isFresh(12))->toBeTrue();
    });

    test('calculates age in hours correctly', function () {
        $dealScore = new DealScore([
            'generated_at' => now()->subHours(5),
        ]);

        expect($dealScore->getAgeInHours())->toBe(5);
    });
});

describe('factor analysis', function () {
    test('identifies dominant factor', function () {
        $dealScore = new DealScore([
            'engagement_score' => 60,
            'velocity_score' => 85,
            'value_score' => 70,
            'historical_pattern_score' => 55,
        ]);

        expect($dealScore->getDominantFactor())->toBe('velocity');
    });

    test('identifies weakest factor', function () {
        $dealScore = new DealScore([
            'engagement_score' => 60,
            'velocity_score' => 85,
            'value_score' => 70,
            'historical_pattern_score' => 45,
        ]);

        expect($dealScore->getWeakestFactor())->toBe('historical_pattern');
    });

    test('returns score breakdown as array', function () {
        $dealScore = new DealScore([
            'score' => 75,
            'win_probability' => 65,
            'engagement_score' => 70,
            'velocity_score' => 80,
            'value_score' => 85,
            'historical_pattern_score' => 60,
        ]);

        $breakdown = $dealScore->getScoreBreakdown();

        expect($breakdown)->toBeArray()
            ->and($breakdown)->toHaveKeys([
                'overall',
                'win_probability',
                'engagement',
                'velocity',
                'value',
                'historical_pattern',
            ])
            ->and($breakdown['overall'])->toBe('75.00')
            ->and($breakdown['win_probability'])->toBe('65.00')
            ->and($breakdown['engagement'])->toBe('70.00')
            ->and($breakdown['velocity'])->toBe('80.00')
            ->and($breakdown['value'])->toBe('85.00')
            ->and($breakdown['historical_pattern'])->toBe('60.00');
    });
});

describe('scopes', function () {
    test('highPriority scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->highPriority();

        expect($scopedQuery->toSql())->toContain('where "score" >= ?');
    });

    test('mediumPriority scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->mediumPriority();

        $sql = $scopedQuery->toSql();
        expect($sql)->toContain('where "score" >= ?')
            ->and($sql)->toContain('and "score" < ?');
    });

    test('lowPriority scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->lowPriority();

        expect($scopedQuery->toSql())->toContain('where "score" < ?');
    });

    test('highWinProbability scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->highWinProbability();

        expect($scopedQuery->toSql())->toContain('where "win_probability" >= ?');
    });

    test('strongEngagement scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->strongEngagement();

        expect($scopedQuery->toSql())->toContain('where "engagement_score" >= ?');
    });

    test('fastVelocity scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->fastVelocity();

        expect($scopedQuery->toSql())->toContain('where "velocity_score" >= ?');
    });

    test('fresh scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->fresh();

        expect($scopedQuery->toSql())->toContain('where "generated_at" >= ?');
    });

    test('fresh scope accepts custom hours parameter', function () {
        $query = DealScore::query();
        $scopedQuery = $query->fresh(48);

        expect($scopedQuery->toSql())->toContain('where "generated_at" >= ?');
    });

    test('stale scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->stale();

        expect($scopedQuery->toSql())->toContain('where "generated_at" < ?');
    });

    test('stale scope accepts custom hours parameter', function () {
        $query = DealScore::query();
        $scopedQuery = $query->stale(48);

        expect($scopedQuery->toSql())->toContain('where "generated_at" < ?');
    });

    test('topScored scope orders correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->topScored();

        expect($scopedQuery->toSql())->toContain('order by "score" desc');
    });

    test('forLead scope filters correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->forLead(123);

        expect($scopedQuery->toSql())->toContain('where "lead_id" = ?');
    });

    test('latestForEachLead scope uses subquery correctly', function () {
        $query = DealScore::query();
        $scopedQuery = $query->latestForEachLead();

        $sql = $scopedQuery->toSql();
        expect($sql)->toContain('where "id" in')
            ->and($sql)->toContain('MAX(id)');
    });
});

describe('integration scenarios', function () {
    test('high-value hot lead has expected characteristics', function () {
        $dealScore = new DealScore([
            'score' => 90,
            'win_probability' => 85,
            'engagement_score' => 95,
            'velocity_score' => 88,
            'value_score' => 92,
            'historical_pattern_score' => 80,
            'generated_at' => now()->subHours(2),
        ]);

        expect($dealScore->isHighPriority())->toBeTrue()
            ->and($dealScore->hasHighWinProbability())->toBeTrue()
            ->and($dealScore->hasStrongEngagement())->toBeTrue()
            ->and($dealScore->hasFastVelocity())->toBeTrue()
            ->and($dealScore->hasHighValue())->toBeTrue()
            ->and($dealScore->isFresh())->toBeTrue()
            ->and($dealScore->getDominantFactor())->toBe('engagement');
    });

    test('low-value cold lead has expected characteristics', function () {
        $dealScore = new DealScore([
            'score' => 35,
            'win_probability' => 25,
            'engagement_score' => 30,
            'velocity_score' => 40,
            'value_score' => 35,
            'historical_pattern_score' => 38,
            'generated_at' => now()->subHours(30),
        ]);

        expect($dealScore->isLowPriority())->toBeTrue()
            ->and($dealScore->hasLowWinProbability())->toBeTrue()
            ->and($dealScore->hasStrongEngagement())->toBeFalse()
            ->and($dealScore->hasFastVelocity())->toBeFalse()
            ->and($dealScore->hasHighValue())->toBeFalse()
            ->and($dealScore->isStale())->toBeTrue()
            ->and($dealScore->getWeakestFactor())->toBe('engagement');
    });

    test('medium priority deal with mixed signals', function () {
        $dealScore = new DealScore([
            'score' => 65,
            'win_probability' => 60,
            'engagement_score' => 55,
            'velocity_score' => 75,
            'value_score' => 80,
            'historical_pattern_score' => 50,
            'generated_at' => now()->subHours(18),
        ]);

        expect($dealScore->isMediumPriority())->toBeTrue()
            ->and($dealScore->hasMediumWinProbability())->toBeTrue()
            ->and($dealScore->hasFastVelocity())->toBeTrue()
            ->and($dealScore->hasHighValue())->toBeTrue()
            ->and($dealScore->hasStrongEngagement())->toBeFalse()
            ->and($dealScore->getDominantFactor())->toBe('value')
            ->and($dealScore->getWeakestFactor())->toBe('historical_pattern');
    });
});
