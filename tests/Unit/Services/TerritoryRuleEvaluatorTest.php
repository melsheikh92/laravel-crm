<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Territory\Models\Territory;
use Webkul\Territory\Models\TerritoryRule;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Repositories\TerritoryRuleRepository;
use Webkul\Territory\Services\TerritoryRuleEvaluator;
use App\Models\User;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->evaluator = app(TerritoryRuleEvaluator::class);
    $this->user = User::factory()->create();
});

describe('evaluateRule', function () {
    test('returns true when active rule matches entity', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        expect($this->evaluator->evaluateRule($rule, $entity))->toBeTrue();
    });

    test('returns false when active rule does not match entity', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'Texas';
        };

        expect($this->evaluator->evaluateRule($rule, $entity))->toBeFalse();
    });

    test('returns false when rule is inactive', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => false,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        expect($this->evaluator->evaluateRule($rule, $entity))->toBeFalse();
    });
});

describe('evaluateRules', function () {
    test('returns true when all rules match entity', function () {
        $territory = Territory::create([
            'name' => 'California Tech',
            'code' => 'CA-TECH-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule1 = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $rule2 = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => 'in',
            'value' => ['Technology', 'Software'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $rules = collect([$rule1, $rule2]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };

        expect($this->evaluator->evaluateRules($rules, $entity))->toBeTrue();
    });

    test('returns false when at least one rule does not match', function () {
        $territory = Territory::create([
            'name' => 'California Tech',
            'code' => 'CA-TECH-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule1 = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $rule2 = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => 'in',
            'value' => ['Technology', 'Software'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $rules = collect([$rule1, $rule2]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Healthcare';
        };

        expect($this->evaluator->evaluateRules($rules, $entity))->toBeFalse();
    });

    test('returns false when rules collection is empty', function () {
        $rules = collect([]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        expect($this->evaluator->evaluateRules($rules, $entity))->toBeFalse();
    });
});

describe('evaluateRulesWithAnyMatch', function () {
    test('returns true when at least one rule matches', function () {
        $territory = Territory::create([
            'name' => 'West Coast',
            'code' => 'WEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule1 = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $rule2 = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['Oregon'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $rules = collect([$rule1, $rule2]);

        $entity = new class extends Model {
            public $state = 'Oregon';
        };

        expect($this->evaluator->evaluateRulesWithAnyMatch($rules, $entity))->toBeTrue();
    });

    test('returns false when no rules match', function () {
        $territory = Territory::create([
            'name' => 'West Coast',
            'code' => 'WEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule1 = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $rule2 = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['Oregon'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $rules = collect([$rule1, $rule2]);

        $entity = new class extends Model {
            public $state = 'Texas';
        };

        expect($this->evaluator->evaluateRulesWithAnyMatch($rules, $entity))->toBeFalse();
    });

    test('returns false when rules collection is empty', function () {
        $rules = collect([]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        expect($this->evaluator->evaluateRulesWithAnyMatch($rules, $entity))->toBeFalse();
    });
});

describe('findMatchingTerritories', function () {
    test('returns matching territories with ALL strategy', function () {
        $territory1 = Territory::create([
            'name' => 'California Tech',
            'code' => 'CA-TECH-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory1->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 10,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory1->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => '=',
            'value' => ['Technology'],
            'priority' => 10,
            'is_active' => true,
        ]);

        $territory2 = Territory::create([
            'name' => 'California All',
            'code' => 'CA-ALL-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory2->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 5,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };

        $matches = $this->evaluator->findMatchingTerritories($entity, 'all');

        expect($matches)->toHaveCount(2)
            ->and($matches->first()['territory']->id)->toBe($territory1->id)
            ->and($matches->first()['priority'])->toBe(10)
            ->and($matches->last()['territory']->id)->toBe($territory2->id)
            ->and($matches->last()['priority'])->toBe(5);
    });

    test('returns matching territories with ANY strategy', function () {
        $territory = Territory::create([
            'name' => 'West Coast',
            'code' => 'WEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 10,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['Oregon'],
            'priority' => 10,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'Oregon';
        };

        $matches = $this->evaluator->findMatchingTerritories($entity, 'any');

        expect($matches)->toHaveCount(1)
            ->and($matches->first()['territory']->id)->toBe($territory->id);
    });

    test('excludes inactive territories', function () {
        $territory = Territory::create([
            'name' => 'California',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'inactive',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 10,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $matches = $this->evaluator->findMatchingTerritories($entity);

        expect($matches)->toBeEmpty();
    });

    test('excludes territories without rules', function () {
        Territory::create([
            'name' => 'Empty Territory',
            'code' => 'EMPTY-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $matches = $this->evaluator->findMatchingTerritories($entity);

        expect($matches)->toBeEmpty();
    });

    test('sorts matched territories by priority descending', function () {
        $territory1 = Territory::create([
            'name' => 'Low Priority',
            'code' => 'LOW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory1->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $territory2 = Territory::create([
            'name' => 'High Priority',
            'code' => 'HIGH-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory2->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 100,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $matches = $this->evaluator->findMatchingTerritories($entity);

        expect($matches)->toHaveCount(2)
            ->and($matches->first()['territory']->id)->toBe($territory2->id)
            ->and($matches->first()['priority'])->toBe(100)
            ->and($matches->last()['territory']->id)->toBe($territory1->id)
            ->and($matches->last()['priority'])->toBe(1);
    });
});

describe('findBestMatchingTerritory', function () {
    test('returns highest priority matching territory', function () {
        $territory1 = Territory::create([
            'name' => 'Low Priority',
            'code' => 'LOW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory1->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $territory2 = Territory::create([
            'name' => 'High Priority',
            'code' => 'HIGH-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory2->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 100,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $bestTerritory = $this->evaluator->findBestMatchingTerritory($entity);

        expect($bestTerritory)->not->toBeNull()
            ->and($bestTerritory->id)->toBe($territory2->id)
            ->and($bestTerritory->name)->toBe('High Priority');
    });

    test('returns null when no territories match', function () {
        $territory = Territory::create([
            'name' => 'California',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 10,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'Texas';
        };

        $bestTerritory = $this->evaluator->findBestMatchingTerritory($entity);

        expect($bestTerritory)->toBeNull();
    });

    test('works with ANY match strategy', function () {
        $territory = Territory::create([
            'name' => 'West Coast',
            'code' => 'WEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 10,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['Oregon'],
            'priority' => 10,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'Oregon';
        };

        $bestTerritory = $this->evaluator->findBestMatchingTerritory($entity, 'any');

        expect($bestTerritory)->not->toBeNull()
            ->and($bestTerritory->id)->toBe($territory->id);
    });
});

describe('evaluateTerritoryRules', function () {
    test('returns true when all rules match with ALL strategy', function () {
        $territory = Territory::create([
            'name' => 'California Tech',
            'code' => 'CA-TECH-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => '=',
            'value' => ['Technology'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };

        expect($this->evaluator->evaluateTerritoryRules($territory->id, $entity))->toBeTrue();
    });

    test('returns true when at least one rule matches with ANY strategy', function () {
        $territory = Territory::create([
            'name' => 'West Coast',
            'code' => 'WEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['Oregon'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'Oregon';
        };

        expect($this->evaluator->evaluateTerritoryRules($territory->id, $entity, 'any'))->toBeTrue();
    });

    test('returns false when territory has no rules', function () {
        $territory = Territory::create([
            'name' => 'Empty Territory',
            'code' => 'EMPTY-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        expect($this->evaluator->evaluateTerritoryRules($territory->id, $entity))->toBeFalse();
    });
});

describe('getMatchingRules', function () {
    test('returns only rules that match entity', function () {
        $territory = Territory::create([
            'name' => 'Test Territory',
            'code' => 'TEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $matchingRule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $nonMatchingRule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => '=',
            'value' => ['Healthcare'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $rules = collect([$matchingRule, $nonMatchingRule]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };

        $matchingRules = $this->evaluator->getMatchingRules($rules, $entity);

        expect($matchingRules)->toHaveCount(1)
            ->and($matchingRules->first()->id)->toBe($matchingRule->id);
    });

    test('returns empty collection when no rules match', function () {
        $territory = Territory::create([
            'name' => 'Test Territory',
            'code' => 'TEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $rules = collect([$rule]);

        $entity = new class extends Model {
            public $state = 'Texas';
        };

        $matchingRules = $this->evaluator->getMatchingRules($rules, $entity);

        expect($matchingRules)->toBeEmpty();
    });
});

describe('getNonMatchingRules', function () {
    test('returns only rules that do not match entity', function () {
        $territory = Territory::create([
            'name' => 'Test Territory',
            'code' => 'TEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $matchingRule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $nonMatchingRule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => '=',
            'value' => ['Healthcare'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $rules = collect([$matchingRule, $nonMatchingRule]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };

        $nonMatchingRules = $this->evaluator->getNonMatchingRules($rules, $entity);

        expect($nonMatchingRules)->toHaveCount(1)
            ->and($nonMatchingRules->first()->id)->toBe($nonMatchingRule->id);
    });

    test('returns empty collection when all rules match', function () {
        $territory = Territory::create([
            'name' => 'Test Territory',
            'code' => 'TEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $rules = collect([$rule]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $nonMatchingRules = $this->evaluator->getNonMatchingRules($rules, $entity);

        expect($nonMatchingRules)->toBeEmpty();
    });
});

describe('doesEntityMatchTerritory', function () {
    test('returns true when entity matches all territory rules', function () {
        $territory = Territory::create([
            'name' => 'California',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        expect($this->evaluator->doesEntityMatchTerritory($territory->id, $entity))->toBeTrue();
    });

    test('returns false when entity does not match territory rules', function () {
        $territory = Territory::create([
            'name' => 'California',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'Texas';
        };

        expect($this->evaluator->doesEntityMatchTerritory($territory->id, $entity))->toBeFalse();
    });

    test('returns false when territory is inactive', function () {
        $territory = Territory::create([
            'name' => 'California',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'inactive',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        expect($this->evaluator->doesEntityMatchTerritory($territory->id, $entity))->toBeFalse();
    });

    test('returns false when territory does not exist', function () {
        $entity = new class extends Model {
            public $state = 'California';
        };

        expect($this->evaluator->doesEntityMatchTerritory(999999, $entity))->toBeFalse();
    });
});

describe('evaluateRulesByType', function () {
    test('returns matching rules for specific type', function () {
        $territory = Territory::create([
            'name' => 'Test Territory',
            'code' => 'TEST-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $geographicRule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $industryRule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => '=',
            'value' => ['Technology'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };

        $geographicMatches = $this->evaluator->evaluateRulesByType('geographic', $entity, $territory->id);
        $industryMatches = $this->evaluator->evaluateRulesByType('industry', $entity, $territory->id);

        expect($geographicMatches)->toHaveCount(1)
            ->and($geographicMatches->first()->id)->toBe($geographicRule->id)
            ->and($industryMatches)->toHaveCount(1)
            ->and($industryMatches->first()->id)->toBe($industryRule->id);
    });

    test('evaluates rules across all territories when territory ID not provided', function () {
        $territory1 = Territory::create([
            'name' => 'Territory 1',
            'code' => 'T1',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $territory2 = Territory::create([
            'name' => 'Territory 2',
            'code' => 'T2',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory1->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory2->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $matches = $this->evaluator->evaluateRulesByType('geographic', $entity);

        expect($matches)->toHaveCount(2);
    });
});

describe('getEvaluationDetails', function () {
    test('returns detailed evaluation information', function () {
        $territory = Territory::create([
            'name' => 'Test Territory',
            'code' => 'TEST-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $matchingRule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $nonMatchingRule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => '=',
            'value' => ['Healthcare'],
            'priority' => 2,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };

        $details = $this->evaluator->getEvaluationDetails($territory->id, $entity);

        expect($details)->toBeArray()
            ->and($details['territory_id'])->toBe($territory->id)
            ->and($details['total_rules'])->toBe(2)
            ->and($details['matching_rules'])->toBe(1)
            ->and($details['non_matching_rules'])->toBe(1)
            ->and($details['matches'])->toBeFalse()
            ->and($details['matching_rule_ids'])->toContain($matchingRule->id)
            ->and($details['non_matching_rule_ids'])->toContain($nonMatchingRule->id);
    });

    test('returns matches true when all rules match', function () {
        $territory = Territory::create([
            'name' => 'Test Territory',
            'code' => 'TEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $details = $this->evaluator->getEvaluationDetails($territory->id, $entity);

        expect($details['matches'])->toBeTrue()
            ->and($details['total_rules'])->toBe(1)
            ->and($details['matching_rules'])->toBe(1)
            ->and($details['non_matching_rules'])->toBe(0);
    });
});

describe('findMatchingTerritoriesByType', function () {
    test('returns only territories of specified type', function () {
        $geographicTerritory = Territory::create([
            'name' => 'Geographic Territory',
            'code' => 'GEO-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $geographicTerritory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $accountTerritory = Territory::create([
            'name' => 'Account Territory',
            'code' => 'ACC-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $accountTerritory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $geographicMatches = $this->evaluator->findMatchingTerritoriesByType('geographic', $entity);
        $accountMatches = $this->evaluator->findMatchingTerritoriesByType('account-based', $entity);

        expect($geographicMatches)->toHaveCount(1)
            ->and($geographicMatches->first()['territory']->id)->toBe($geographicTerritory->id)
            ->and($accountMatches)->toHaveCount(1)
            ->and($accountMatches->first()['territory']->id)->toBe($accountTerritory->id);
    });

    test('excludes inactive territories of specified type', function () {
        $inactiveTerritory = Territory::create([
            'name' => 'Inactive Territory',
            'code' => 'INACTIVE-01',
            'type' => 'geographic',
            'status' => 'inactive',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $inactiveTerritory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };

        $matches = $this->evaluator->findMatchingTerritoriesByType('geographic', $entity);

        expect($matches)->toBeEmpty();
    });

    test('works with ANY match strategy', function () {
        $territory = Territory::create([
            'name' => 'West Coast',
            'code' => 'WEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['Oregon'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'Oregon';
        };

        $matches = $this->evaluator->findMatchingTerritoriesByType('geographic', $entity, 'any');

        expect($matches)->toHaveCount(1)
            ->and($matches->first()['territory']->id)->toBe($territory->id);
    });
});

describe('findMatchingEntities', function () {
    test('returns entities that match territory rules', function () {
        $territory = Territory::create([
            'name' => 'California',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $matchingEntity = new class extends Model {
            public $state = 'California';
        };

        $nonMatchingEntity = new class extends Model {
            public $state = 'Texas';
        };

        $entities = [$matchingEntity, $nonMatchingEntity];

        $matches = $this->evaluator->findMatchingEntities($territory->id, $entities);

        expect($matches)->toHaveCount(1)
            ->and($matches->first()->state)->toBe('California');
    });

    test('returns empty collection when no entities match', function () {
        $territory = Territory::create([
            'name' => 'California',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity1 = new class extends Model {
            public $state = 'Texas';
        };

        $entity2 = new class extends Model {
            public $state = 'Florida';
        };

        $entities = [$entity1, $entity2];

        $matches = $this->evaluator->findMatchingEntities($territory->id, $entities);

        expect($matches)->toBeEmpty();
    });

    test('works with ANY match strategy', function () {
        $territory = Territory::create([
            'name' => 'West Coast',
            'code' => 'WEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['Oregon'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity1 = new class extends Model {
            public $state = 'Oregon';
        };

        $entity2 = new class extends Model {
            public $state = 'Texas';
        };

        $entities = [$entity1, $entity2];

        $matches = $this->evaluator->findMatchingEntities($territory->id, $entities, 'any');

        expect($matches)->toHaveCount(1)
            ->and($matches->first()->state)->toBe('Oregon');
    });

    test('skips non-model objects in entities array', function () {
        $territory = Territory::create([
            'name' => 'California',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $validEntity = new class extends Model {
            public $state = 'California';
        };

        $invalidEntity = (object) ['state' => 'California'];

        $entities = [$validEntity, $invalidEntity];

        $matches = $this->evaluator->findMatchingEntities($territory->id, $entities);

        expect($matches)->toHaveCount(1);
    });
});

describe('rule type evaluation', function () {
    test('evaluates geographic rules correctly', function () {
        $territory = Territory::create([
            'name' => 'West Coast',
            'code' => 'WEST-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => 'in',
            'value' => ['California', 'Oregon', 'Washington'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'Oregon';
        };

        expect($this->evaluator->evaluateRule($rule, $entity))->toBeTrue();
    });

    test('evaluates industry rules correctly', function () {
        $territory = Territory::create([
            'name' => 'Tech Territory',
            'code' => 'TECH-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => 'in',
            'value' => ['Technology', 'Software', 'IT Services'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $industry = 'Software';
        };

        expect($this->evaluator->evaluateRule($rule, $entity))->toBeTrue();
    });

    test('evaluates account size rules correctly', function () {
        $territory = Territory::create([
            'name' => 'Enterprise Territory',
            'code' => 'ENT-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'account_size',
            'field_name' => 'revenue',
            'operator' => 'between',
            'value' => [1000000, 10000000],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $revenue = 5000000;
        };

        expect($this->evaluator->evaluateRule($rule, $entity))->toBeTrue();
    });

    test('evaluates custom rules correctly', function () {
        $territory = Territory::create([
            'name' => 'VIP Territory',
            'code' => 'VIP-01',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $rule = TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'custom',
            'field_name' => 'tier',
            'operator' => '=',
            'value' => ['platinum'],
            'priority' => 1,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $tier = 'platinum';
        };

        expect($this->evaluator->evaluateRule($rule, $entity))->toBeTrue();
    });
});
