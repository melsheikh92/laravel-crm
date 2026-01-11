<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webkul\Territory\Models\Territory;
use Webkul\Territory\Models\TerritoryRule;
use App\Models\User;

uses(RefreshDatabase::class);

test('territory rule can be created with required fields', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
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

    expect($rule)->toBeInstanceOf(TerritoryRule::class)
        ->and($rule->territory_id)->toBe($territory->id)
        ->and($rule->rule_type)->toBe('geographic')
        ->and($rule->field_name)->toBe('state')
        ->and($rule->operator)->toBe('=')
        ->and($rule->value)->toBeArray()
        ->and($rule->priority)->toBe(1)
        ->and($rule->is_active)->toBeTrue();
});

test('territory rule belongs to a territory', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
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

    expect($rule->territory)->toBeInstanceOf(Territory::class)
        ->and($rule->territory->id)->toBe($territory->id);
});

test('active scope returns only active rules', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $activeRule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'geographic',
        'field_name' => 'state',
        'operator' => '=',
        'value' => ['California'],
        'priority' => 1,
        'is_active' => true,
    ]);

    $inactiveRule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'industry',
        'field_name' => 'industry',
        'operator' => '=',
        'value' => ['Technology'],
        'priority' => 2,
        'is_active' => false,
    ]);

    $activeRules = TerritoryRule::active()->get();

    expect($activeRules)->toHaveCount(1)
        ->and($activeRules->first()->id)->toBe($activeRule->id);
});

test('byType scope filters rules by rule type', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
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

    $geographicRules = TerritoryRule::byType('geographic')->get();
    $industryRules = TerritoryRule::byType('industry')->get();

    expect($geographicRules)->toHaveCount(1)
        ->and($geographicRules->first()->id)->toBe($geographicRule->id)
        ->and($industryRules)->toHaveCount(1)
        ->and($industryRules->first()->id)->toBe($industryRule->id);
});

test('byPriority scope orders rules by priority', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
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
        'operator' => '=',
        'value' => ['Technology'],
        'priority' => 3,
        'is_active' => true,
    ]);

    $rule3 = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'account_size',
        'field_name' => 'revenue',
        'operator' => '>',
        'value' => [1000000],
        'priority' => 2,
        'is_active' => true,
    ]);

    $rulesDesc = TerritoryRule::byPriority('desc')->get();
    $rulesAsc = TerritoryRule::byPriority('asc')->get();

    expect($rulesDesc->first()->id)->toBe($rule2->id)
        ->and($rulesDesc->last()->id)->toBe($rule1->id)
        ->and($rulesAsc->first()->id)->toBe($rule1->id)
        ->and($rulesAsc->last()->id)->toBe($rule2->id);
});

test('evaluate returns false for inactive rules', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
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

    $model = new class extends Model {
        public $state = 'California';
    };

    expect($rule->evaluate($model))->toBeFalse();
});

test('evaluate handles equals operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
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

    $matchingModel = new class extends Model {
        public $state = 'California';
    };

    $nonMatchingModel = new class extends Model {
        public $state = 'Texas';
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles not equals operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'geographic',
        'field_name' => 'state',
        'operator' => '!=',
        'value' => ['California'],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $state = 'Texas';
    };

    $nonMatchingModel = new class extends Model {
        public $state = 'California';
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles greater than operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'account_size',
        'field_name' => 'revenue',
        'operator' => '>',
        'value' => [1000000],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $revenue = 2000000;
    };

    $nonMatchingModel = new class extends Model {
        public $revenue = 500000;
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles less than operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'account_size',
        'field_name' => 'revenue',
        'operator' => '<',
        'value' => [1000000],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $revenue = 500000;
    };

    $nonMatchingModel = new class extends Model {
        public $revenue = 2000000;
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles in operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'industry',
        'field_name' => 'industry',
        'operator' => 'in',
        'value' => ['Technology', 'Software', 'IT'],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $industry = 'Technology';
    };

    $nonMatchingModel = new class extends Model {
        public $industry = 'Healthcare';
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles not_in operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'industry',
        'field_name' => 'industry',
        'operator' => 'not_in',
        'value' => ['Technology', 'Software'],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $industry = 'Healthcare';
    };

    $nonMatchingModel = new class extends Model {
        public $industry = 'Technology';
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles contains operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'custom',
        'field_name' => 'description',
        'operator' => 'contains',
        'value' => ['enterprise'],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $description = 'This is an enterprise customer';
    };

    $nonMatchingModel = new class extends Model {
        public $description = 'This is a small business';
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles starts_with operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'custom',
        'field_name' => 'code',
        'operator' => 'starts_with',
        'value' => ['CA'],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $code = 'CA-123';
    };

    $nonMatchingModel = new class extends Model {
        public $code = 'TX-456';
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles ends_with operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'custom',
        'field_name' => 'email',
        'operator' => 'ends_with',
        'value' => ['@example.com'],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $email = 'user@example.com';
    };

    $nonMatchingModel = new class extends Model {
        public $email = 'user@test.com';
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles is_null operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'custom',
        'field_name' => 'description',
        'operator' => 'is_null',
        'value' => [],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $description = null;
    };

    $nonMatchingModel = new class extends Model {
        public $description = 'Some description';
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles is_not_null operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'custom',
        'field_name' => 'description',
        'operator' => 'is_not_null',
        'value' => [],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $description = 'Some description';
    };

    $nonMatchingModel = new class extends Model {
        public $description = null;
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles between operator correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'account_size',
        'field_name' => 'revenue',
        'operator' => 'between',
        'value' => [100000, 500000],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $revenue = 250000;
    };

    $nonMatchingModel = new class extends Model {
        public $revenue = 600000;
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('evaluate handles nested field names correctly', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'custom',
        'field_name' => 'address.state',
        'operator' => '=',
        'value' => ['California'],
        'priority' => 1,
        'is_active' => true,
    ]);

    $matchingModel = new class extends Model {
        public $address;

        public function __construct()
        {
            parent::__construct();
            $this->address = (object) ['state' => 'California'];
        }
    };

    $nonMatchingModel = new class extends Model {
        public $address;

        public function __construct()
        {
            parent::__construct();
            $this->address = (object) ['state' => 'Texas'];
        }
    };

    expect($rule->evaluate($matchingModel))->toBeTrue()
        ->and($rule->evaluate($nonMatchingModel))->toBeFalse();
});

test('isGeographic returns true for geographic rules', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
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

    expect($rule->isGeographic())->toBeTrue()
        ->and($rule->isIndustry())->toBeFalse()
        ->and($rule->isAccountSize())->toBeFalse()
        ->and($rule->isCustom())->toBeFalse();
});

test('isIndustry returns true for industry rules', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'industry',
        'field_name' => 'industry',
        'operator' => '=',
        'value' => ['Technology'],
        'priority' => 1,
        'is_active' => true,
    ]);

    expect($rule->isIndustry())->toBeTrue()
        ->and($rule->isGeographic())->toBeFalse()
        ->and($rule->isAccountSize())->toBeFalse()
        ->and($rule->isCustom())->toBeFalse();
});

test('isAccountSize returns true for account_size rules', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'account_size',
        'field_name' => 'revenue',
        'operator' => '>',
        'value' => [1000000],
        'priority' => 1,
        'is_active' => true,
    ]);

    expect($rule->isAccountSize())->toBeTrue()
        ->and($rule->isGeographic())->toBeFalse()
        ->and($rule->isIndustry())->toBeFalse()
        ->and($rule->isCustom())->toBeFalse();
});

test('isCustom returns true for custom rules', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'custom',
        'field_name' => 'custom_field',
        'operator' => '=',
        'value' => ['value'],
        'priority' => 1,
        'is_active' => true,
    ]);

    expect($rule->isCustom())->toBeTrue()
        ->and($rule->isGeographic())->toBeFalse()
        ->and($rule->isIndustry())->toBeFalse()
        ->and($rule->isAccountSize())->toBeFalse();
});

test('value is cast to array', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'geographic',
        'field_name' => 'state',
        'operator' => '=',
        'value' => ['California', 'Oregon'],
        'priority' => 1,
        'is_active' => true,
    ]);

    expect($rule->value)->toBeArray()
        ->and($rule->value)->toContain('California', 'Oregon');
});

test('is_active is cast to boolean', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'geographic',
        'field_name' => 'state',
        'operator' => '=',
        'value' => ['California'],
        'priority' => 1,
        'is_active' => 1,
    ]);

    expect($rule->is_active)->toBeBool()
        ->and($rule->is_active)->toBeTrue();
});

test('priority is cast to integer', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $rule = TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type' => 'geographic',
        'field_name' => 'state',
        'operator' => '=',
        'value' => ['California'],
        'priority' => '5',
        'is_active' => true,
    ]);

    expect($rule->priority)->toBeInt()
        ->and($rule->priority)->toBe(5);
});
