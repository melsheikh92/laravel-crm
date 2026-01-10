<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\Territory\Models\Territory;
use Webkul\Territory\Models\TerritoryAssignment;
use Webkul\Territory\Models\TerritoryRule;

uses(RefreshDatabase::class);

/**
 * Lead Auto-Assignment Tests
 */
test('lead is automatically assigned to matching territory when created', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->actingAs($user);

    // Create territory with rules
    $territory = Territory::create([
        'name'   => 'California Territory',
        'code'   => 'CA-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['California'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Dispatch event manually to test listener
    $lead = Lead::create([
        'title'   => 'California Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    // Manually trigger the event listener
    event('lead.create.after', $lead);

    // Check if assignment was created
    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($territory->id)
        ->and($assignment->assignment_type)->toBe('automatic');
});

test('lead is not assigned when no territory matches', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create territory with rules that won't match
    $territory = Territory::create([
        'name'   => 'Texas Territory',
        'code'   => 'TX-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Texas'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create lead that doesn't match
    $lead = Lead::create([
        'title'   => 'New York Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    // Check that no assignment was created
    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->toBeNull();
});

test('lead is assigned to highest priority territory when multiple territories match', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create low priority territory
    $lowPriorityTerritory = Territory::create([
        'name'   => 'West Coast Territory',
        'code'   => 'WC-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $lowPriorityTerritory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['California'],
        'priority'     => 5,
        'is_active'    => true,
    ]);

    // Create high priority territory
    $highPriorityTerritory = Territory::create([
        'name'   => 'Silicon Valley Territory',
        'code'   => 'SV-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $highPriorityTerritory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['California'],
        'priority'     => 15,
        'is_active'    => true,
    ]);

    // Create lead that matches both
    $lead = Lead::create([
        'title'   => 'California Tech Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    // Check that assignment was made to high priority territory
    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($highPriorityTerritory->id);
});

test('lead is not assigned to inactive territory', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create inactive territory with matching rules
    $inactiveTerritory = Territory::create([
        'name'   => 'Inactive Territory',
        'code'   => 'IN-01',
        'type'   => 'geographic',
        'status' => 'inactive',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $inactiveTerritory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Test'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create lead
    $lead = Lead::create([
        'title'   => 'Test Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    // Check that no assignment was created
    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->toBeNull();
});

test('lead is not assigned when territory has only inactive rules', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Territory With Inactive Rules',
        'code'   => 'TI-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    // Create inactive rule
    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Test'],
        'priority'     => 10,
        'is_active'    => false,
    ]);

    // Create lead
    $lead = Lead::create([
        'title'   => 'Test Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    // Check that no assignment was created
    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->toBeNull();
});

/**
 * Organization Auto-Assignment Tests
 */
test('organization is automatically assigned to matching territory when created', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create territory with industry-based rule
    $territory = Territory::create([
        'name'   => 'Technology Territory',
        'code'   => 'TECH-01',
        'type'   => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'industry',
        'field_name'   => 'name',
        'operator'     => 'contains',
        'value'        => ['Tech'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create organization
    $organization = Organization::create([
        'name' => 'Tech Solutions Inc',
    ]);

    event('contacts.organization.create.after', $organization);

    // Check if assignment was created
    $assignment = TerritoryAssignment::where('assignable_type', Organization::class)
        ->where('assignable_id', $organization->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($territory->id)
        ->and($assignment->assignment_type)->toBe('automatic');
});

test('organization is assigned based on equality operator', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Specific Organization Territory',
        'code'   => 'SO-01',
        'type'   => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'name',
        'operator'     => '=',
        'value'        => ['Acme Corp'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create matching organization
    $organization = Organization::create([
        'name' => 'Acme Corp',
    ]);

    event('contacts.organization.create.after', $organization);

    $assignment = TerritoryAssignment::where('assignable_type', Organization::class)
        ->where('assignable_id', $organization->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($territory->id);
});

test('organization is not assigned when rule does not match', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Manufacturing Territory',
        'code'   => 'MFG-01',
        'type'   => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'industry',
        'field_name'   => 'name',
        'operator'     => 'contains',
        'value'        => ['Manufacturing'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create organization that doesn't match
    $organization = Organization::create([
        'name' => 'Tech Startup',
    ]);

    event('contacts.organization.create.after', $organization);

    $assignment = TerritoryAssignment::where('assignable_type', Organization::class)
        ->where('assignable_id', $organization->id)
        ->first();

    expect($assignment)->toBeNull();
});

/**
 * Person Auto-Assignment Tests
 */
test('person is automatically assigned to matching territory when created', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create territory with name-based rule
    $territory = Territory::create([
        'name'   => 'Executive Territory',
        'code'   => 'EXEC-01',
        'type'   => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'name',
        'operator'     => 'contains',
        'value'        => ['CEO'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create person
    $person = Person::create([
        'name'   => 'John Doe - CEO',
        'emails' => ['john@example.com'],
    ]);

    event('contacts.person.create.after', $person);

    // Check if assignment was created
    $assignment = TerritoryAssignment::where('assignable_type', Person::class)
        ->where('assignable_id', $person->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($territory->id)
        ->and($assignment->assignment_type)->toBe('automatic');
});

test('person is not assigned when no territory matches', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Senior Management Territory',
        'code'   => 'SM-01',
        'type'   => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'name',
        'operator'     => 'contains',
        'value'        => ['Director'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create person that doesn't match
    $person = Person::create([
        'name'   => 'Jane Smith',
        'emails' => ['jane@example.com'],
    ]);

    event('contacts.person.create.after', $person);

    $assignment = TerritoryAssignment::where('assignable_type', Person::class)
        ->where('assignable_id', $person->id)
        ->first();

    expect($assignment)->toBeNull();
});

/**
 * Multiple Rules Auto-Assignment Tests
 */
test('entity is assigned only when all territory rules match', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Premium California Territory',
        'code'   => 'PCA-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    // Create multiple rules - all must match
    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['California'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Premium'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create lead that matches both rules
    $lead = Lead::create([
        'title'   => 'Premium California Account',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($territory->id);
});

test('entity is not assigned when only some territory rules match', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Premium Texas Territory',
        'code'   => 'PTX-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    // Create multiple rules - all must match
    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Texas'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Premium'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create lead that matches only first rule
    $lead = Lead::create([
        'title'   => 'Texas Standard Account',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->toBeNull();
});

/**
 * Different Operator Tests
 */
test('entity is assigned based on starts_with operator', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'A-Series Territory',
        'code'   => 'AS-01',
        'type'   => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'name',
        'operator'     => 'starts_with',
        'value'        => ['A'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    $organization = Organization::create([
        'name' => 'Acme Corporation',
    ]);

    event('contacts.organization.create.after', $organization);

    $assignment = TerritoryAssignment::where('assignable_type', Organization::class)
        ->where('assignable_id', $organization->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($territory->id);
});

test('entity is assigned based on ends_with operator', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Inc Territory',
        'code'   => 'INC-01',
        'type'   => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'name',
        'operator'     => 'ends_with',
        'value'        => ['Inc'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    $organization = Organization::create([
        'name' => 'Tech Solutions Inc',
    ]);

    event('contacts.organization.create.after', $organization);

    $assignment = TerritoryAssignment::where('assignable_type', Organization::class)
        ->where('assignable_id', $organization->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($territory->id);
});

test('entity is assigned based on not_equal operator', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Non-Test Territory',
        'code'   => 'NT-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'status',
        'operator'     => '!=',
        'value'        => ['lost'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    $lead = Lead::create([
        'title'   => 'Active Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->territory_id)->toBe($territory->id);
});

/**
 * Assignment History Tests
 */
test('assignment creates proper history record', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Test Territory',
        'code'   => 'TEST-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Test'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    $lead = Lead::create([
        'title'   => 'Test Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->assigned_at)->not->toBeNull()
        ->and($assignment->assignment_type)->toBe('automatic');
});

test('no duplicate assignment created for same entity and territory', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Test Territory',
        'code'   => 'TEST-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Test'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    $lead = Lead::create([
        'title'   => 'Test Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    // Trigger event multiple times
    event('lead.create.after', $lead);
    event('lead.create.after', $lead);

    $assignmentCount = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->where('territory_id', $territory->id)
        ->count();

    expect($assignmentCount)->toBe(1);
});

/**
 * Edge Cases and Boundary Tests
 */
test('entity with empty field value is not assigned when rule expects value', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'Test Territory',
        'code'   => 'TEST-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'description',
        'operator'     => 'contains',
        'value'        => ['important'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    $lead = Lead::create([
        'title'       => 'Test Lead',
        'status'      => 'new',
        'description' => null,
        'user_id'     => $user->id,
    ]);

    event('lead.create.after', $lead);

    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->toBeNull();
});

test('territory with no rules does not auto-assign entities', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create territory without any rules
    $territory = Territory::create([
        'name'   => 'Empty Rules Territory',
        'code'   => 'ER-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $lead = Lead::create([
        'title'   => 'Test Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead);

    $assignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    expect($assignment)->toBeNull();
});

test('multiple entities can be assigned to same territory', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $territory = Territory::create([
        'name'   => 'California Territory',
        'code'   => 'CA-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $territory->id,
        'rule_type'    => 'geographic',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['California'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create multiple leads
    $lead1 = Lead::create([
        'title'   => 'California Lead 1',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    $lead2 = Lead::create([
        'title'   => 'California Lead 2',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    $lead3 = Lead::create([
        'title'   => 'California Lead 3',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    event('lead.create.after', $lead1);
    event('lead.create.after', $lead2);
    event('lead.create.after', $lead3);

    $assignmentCount = TerritoryAssignment::where('territory_id', $territory->id)
        ->where('assignable_type', Lead::class)
        ->count();

    expect($assignmentCount)->toBe(3);
});

test('different entity types can be assigned to same territory', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create territory for leads
    $leadTerritory = Territory::create([
        'name'   => 'Tech Lead Territory',
        'code'   => 'TECH-LEAD-01',
        'type'   => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $leadTerritory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'title',
        'operator'     => 'contains',
        'value'        => ['Tech'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    // Create territory for organizations and persons
    $contactTerritory = Territory::create([
        'name'   => 'Tech Contact Territory',
        'code'   => 'TECH-CONTACT-01',
        'type'   => 'account-based',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    TerritoryRule::create([
        'territory_id' => $contactTerritory->id,
        'rule_type'    => 'custom',
        'field_name'   => 'name',
        'operator'     => 'contains',
        'value'        => ['Tech'],
        'priority'     => 10,
        'is_active'    => true,
    ]);

    $lead = Lead::create([
        'title'   => 'Tech Lead',
        'status'  => 'new',
        'user_id' => $user->id,
    ]);

    $organization = Organization::create([
        'name' => 'Tech Corp',
    ]);

    $person = Person::create([
        'name'   => 'Tech Manager',
        'emails' => ['tech@example.com'],
    ]);

    event('lead.create.after', $lead);
    event('contacts.organization.create.after', $organization);
    event('contacts.person.create.after', $person);

    $leadAssignment = TerritoryAssignment::where('assignable_type', Lead::class)
        ->where('assignable_id', $lead->id)
        ->first();

    $orgAssignment = TerritoryAssignment::where('assignable_type', Organization::class)
        ->where('assignable_id', $organization->id)
        ->first();

    $personAssignment = TerritoryAssignment::where('assignable_type', Person::class)
        ->where('assignable_id', $person->id)
        ->first();

    expect($leadAssignment)->not->toBeNull()
        ->and($orgAssignment)->not->toBeNull()
        ->and($personAssignment)->not->toBeNull()
        ->and($leadAssignment->territory_id)->toBe($leadTerritory->id)
        ->and($orgAssignment->territory_id)->toBe($contactTerritory->id)
        ->and($personAssignment->territory_id)->toBe($contactTerritory->id);
});
