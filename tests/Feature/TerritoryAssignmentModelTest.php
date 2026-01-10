<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webkul\Territory\Models\Territory;
use Webkul\Territory\Models\TerritoryAssignment;
use Webkul\User\Models\User;

uses(RefreshDatabase::class);

test('territory assignment can be created with required fields', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $assignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    expect($assignment)->toBeInstanceOf(TerritoryAssignment::class)
        ->and($assignment->territory_id)->toBe($territory->id)
        ->and($assignment->assignable_type)->toBe('Webkul\Lead\Models\Lead')
        ->and($assignment->assignable_id)->toBe(1)
        ->and($assignment->assigned_by)->toBe($user->id)
        ->and($assignment->assignment_type)->toBe('automatic')
        ->and($assignment->assigned_at)->toBeInstanceOf(DateTime::class);
});

test('territory assignment belongs to a territory', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $assignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    expect($assignment->territory)->toBeInstanceOf(Territory::class)
        ->and($assignment->territory->id)->toBe($territory->id);
});

test('territory assignment belongs to assigned by user', function () {
    $owner = User::factory()->create();
    $assignedBy = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $owner->id,
    ]);

    $assignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $assignedBy->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    expect($assignment->assignedBy)->toBeInstanceOf(User::class)
        ->and($assignment->assignedBy->id)->toBe($assignedBy->id);
});

test('manual scope returns only manual assignments', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $manualAssignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    $automaticAssignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 2,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    $manualAssignments = TerritoryAssignment::manual()->get();

    expect($manualAssignments)->toHaveCount(1)
        ->and($manualAssignments->first()->id)->toBe($manualAssignment->id);
});

test('automatic scope returns only automatic assignments', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $manualAssignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    $automaticAssignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 2,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    $automaticAssignments = TerritoryAssignment::automatic()->get();

    expect($automaticAssignments)->toHaveCount(1)
        ->and($automaticAssignments->first()->id)->toBe($automaticAssignment->id);
});

test('byTerritory scope filters assignments by territory', function () {
    $user = User::factory()->create();

    $territory1 = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $territory2 = Territory::create([
        'name' => 'South Region',
        'code' => 'SOUTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $assignment1 = TerritoryAssignment::create([
        'territory_id' => $territory1->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    $assignment2 = TerritoryAssignment::create([
        'territory_id' => $territory2->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 2,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    $territory1Assignments = TerritoryAssignment::byTerritory($territory1->id)->get();

    expect($territory1Assignments)->toHaveCount(1)
        ->and($territory1Assignments->first()->id)->toBe($assignment1->id);
});

test('byAssignableType scope filters assignments by assignable type', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $leadAssignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    $organizationAssignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Contact\Models\Organization',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    $leadAssignments = TerritoryAssignment::byAssignableType('Webkul\Lead\Models\Lead')->get();

    expect($leadAssignments)->toHaveCount(1)
        ->and($leadAssignments->first()->id)->toBe($leadAssignment->id);
});

test('byType scope filters assignments by assignment type', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $manualAssignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    $automaticAssignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 2,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    $manualAssignments = TerritoryAssignment::byType('manual')->get();

    expect($manualAssignments)->toHaveCount(1)
        ->and($manualAssignments->first()->id)->toBe($manualAssignment->id);
});

test('isManual returns true for manual assignments', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $assignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    expect($assignment->isManual())->toBeTrue()
        ->and($assignment->isAutomatic())->toBeFalse();
});

test('isAutomatic returns true for automatic assignments', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $assignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    expect($assignment->isAutomatic())->toBeTrue()
        ->and($assignment->isManual())->toBeFalse();
});

test('assigned_at is cast to datetime', function () {
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $assignedAt = now();

    $assignment = TerritoryAssignment::create([
        'territory_id' => $territory->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => $assignedAt,
    ]);

    expect($assignment->assigned_at)->toBeInstanceOf(DateTime::class)
        ->and($assignment->assigned_at->format('Y-m-d H:i:s'))->toBe($assignedAt->format('Y-m-d H:i:s'));
});

test('can combine multiple scopes for complex queries', function () {
    $user = User::factory()->create();

    $territory1 = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $territory2 = Territory::create([
        'name' => 'South Region',
        'code' => 'SOUTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    // Territory 1, Lead, Manual
    $assignment1 = TerritoryAssignment::create([
        'territory_id' => $territory1->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    // Territory 1, Lead, Automatic
    $assignment2 = TerritoryAssignment::create([
        'territory_id' => $territory1->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 2,
        'assigned_by' => $user->id,
        'assignment_type' => 'automatic',
        'assigned_at' => now(),
    ]);

    // Territory 1, Organization, Manual
    $assignment3 = TerritoryAssignment::create([
        'territory_id' => $territory1->id,
        'assignable_type' => 'Webkul\Contact\Models\Organization',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    // Territory 2, Lead, Manual
    $assignment4 = TerritoryAssignment::create([
        'territory_id' => $territory2->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 3,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    // Get manual Lead assignments for territory1
    $results = TerritoryAssignment::byTerritory($territory1->id)
        ->byAssignableType('Webkul\Lead\Models\Lead')
        ->manual()
        ->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($assignment1->id);
});

test('can create multiple assignments for same entity in different territories', function () {
    $user = User::factory()->create();

    $territory1 = Territory::create([
        'name' => 'North Region',
        'code' => 'NORTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $territory2 = Territory::create([
        'name' => 'South Region',
        'code' => 'SOUTH-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $assignment1 = TerritoryAssignment::create([
        'territory_id' => $territory1->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    $assignment2 = TerritoryAssignment::create([
        'territory_id' => $territory2->id,
        'assignable_type' => 'Webkul\Lead\Models\Lead',
        'assignable_id' => 1,
        'assigned_by' => $user->id,
        'assignment_type' => 'manual',
        'assigned_at' => now(),
    ]);

    $allAssignments = TerritoryAssignment::where('assignable_id', 1)
        ->where('assignable_type', 'Webkul\Lead\Models\Lead')
        ->get();

    expect($allAssignments)->toHaveCount(2);
});
