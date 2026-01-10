<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webkul\Territory\Models\Territory;
use Webkul\Territory\Models\TerritoryAssignment;
use Webkul\Territory\Models\TerritoryRule;
use App\Models\User;

uses(RefreshDatabase::class);

test('territory can be created with required fields', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    expect($territory)->toBeInstanceOf(Territory::class)
        ->and($territory->name)->toBe('North Region')
        ->and($territory->code)->toBe('NORTH-01')
        ->and($territory->type)->toBe('geographic')
        ->and($territory->status)->toBe('active')
        ->and($territory->user_id)->toBe($user->id);
});

test('territory can have a parent territory', function () {
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent Region',
        'code' => 'PARENT-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child Region',
        'code' => 'CHILD-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    expect($child->parent)->toBeInstanceOf(Territory::class)
        ->and($child->parent->id)->toBe($parent->id)
        ->and($child->parent->name)->toBe('Parent Region');
});

test('territory can have multiple children', function () {
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent Region',
        'code' => 'PARENT-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child1 = Territory::create([
        'name' => 'Child Region 1',
        'code' => 'CHILD-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    $child2 = Territory::create([
        'name' => 'Child Region 2',
        'code' => 'CHILD-02',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    expect($parent->children)->toHaveCount(2)
        ->and($parent->children->pluck('id')->toArray())->toContain($child1->id, $child2->id);
});

test('territory belongs to an owner user', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    expect($territory->owner)->toBeInstanceOf(User::class)
        ->and($territory->owner->id)->toBe($user->id);
});

test('territory can have multiple users assigned', function () {
    $owner = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $owner->id,
    ]);

    $territory->users()->attach($user1->id, ['role' => 'member']);
    $territory->users()->attach($user2->id, ['role' => 'manager']);

    expect($territory->users)->toHaveCount(2)
        ->and($territory->users->pluck('id')->toArray())->toContain($user1->id, $user2->id);
});

test('territory can have multiple assignments', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $assignment1 = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    $assignment2 = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Contact\Models\Organization',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    expect($territory->assignments)->toHaveCount(2)
        ->and($territory->assignments->pluck('id')->toArray())->toContain($assignment1->id, $assignment2->id);
});

test('territory can have multiple rules', function () {
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
        'operator' => 'in',
        'value' => ['Technology', 'Software'],
        'priority' => 2,
        'is_active' => true,
    ]);

    expect($territory->rules)->toHaveCount(2)
        ->and($territory->rules->pluck('id')->toArray())->toContain($rule1->id, $rule2->id);
});

test('active scope returns only active territories', function () {
    $user = User::factory()->create();

    $activeTerritory = Territory::create([
        'name' => 'Active Region',
        'code' => 'ACTIVE-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $inactiveTerritory = Territory::create([
        'name' => 'Inactive Region',
        'code' => 'INACTIVE-01',
        'type' => 'geographic',
        'status' => 'inactive',
        'user_id' => $user->id,
    ]);

    $activeTerritories = Territory::active()->get();

    expect($activeTerritories)->toHaveCount(1)
        ->and($activeTerritories->first()->id)->toBe($activeTerritory->id);
});

test('byType scope filters territories by type', function () {
    $user = User::factory()->create();

    $geographic = Territory::create([
        'name' => 'Geographic Region',
        'code' => 'GEO-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $accountBased = Territory::create([
        'name' => 'Account-Based Region',
        'code' => 'ACC-01',
        'type' => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $geographicTerritories = Territory::byType('geographic')->get();
    $accountBasedTerritories = Territory::byType('account-based')->get();

    expect($geographicTerritories)->toHaveCount(1)
        ->and($geographicTerritories->first()->id)->toBe($geographic->id)
        ->and($accountBasedTerritories)->toHaveCount(1)
        ->and($accountBasedTerritories->first()->id)->toBe($accountBased->id);
});

test('isGeographic returns true for geographic territories', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'Geographic Region',
        'code' => 'GEO-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    expect($territory->isGeographic())->toBeTrue()
        ->and($territory->isAccountBased())->toBeFalse();
});

test('isAccountBased returns true for account-based territories', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'Account-Based Region',
        'code' => 'ACC-01',
        'type' => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    expect($territory->isAccountBased())->toBeTrue()
        ->and($territory->isGeographic())->toBeFalse();
});

test('hasChildren returns true when territory has children', function () {
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent Region',
        'code' => 'PARENT-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child Region',
        'code' => 'CHILD-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    expect($parent->hasChildren())->toBeTrue();
});

test('hasChildren returns false when territory has no children', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'Childless Region',
        'code' => 'CHILDLESS-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    expect($territory->hasChildren())->toBeFalse();
});

test('boundaries are cast to array', function () {
    $user = User::factory()->create();

    $boundaries = [
        'type' => 'Polygon',
        'coordinates' => [[[0, 0], [10, 0], [10, 10], [0, 10], [0, 0]]],
    ];

    $territory = Territory::create([
        'name' => 'Geographic Region',
        'code' => 'GEO-01',
        'type' => 'geographic',
        'status' => 'active',
        'boundaries' => $boundaries,
        'user_id' => $user->id,
    ]);

    expect($territory->boundaries)->toBeArray()
        ->and($territory->boundaries['type'])->toBe('Polygon')
        ->and($territory->boundaries['coordinates'])->toBeArray();
});

test('territory uses soft deletes', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $territory->delete();

    expect(Territory::count())->toBe(0)
        ->and(Territory::withTrashed()->count())->toBe(1)
        ->and($territory->deleted_at)->not->toBeNull();
});
