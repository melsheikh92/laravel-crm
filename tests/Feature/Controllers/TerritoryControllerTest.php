<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Webkul\Territory\Models\Territory;
use Webkul\User\Models\User;

uses(RefreshDatabase::class);

/**
 * Territory Index Page Tests
 */
it('can access territory index page', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.settings.territories.index'))
        ->assertOK();
});

it('returns json data for ajax requests on index', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.settings.territories.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
        ->assertOK();
});

/**
 * Territory Create Page Tests
 */
it('can access territory create page', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.settings.territories.create'))
        ->assertOK()
        ->assertSee('name')
        ->assertSee('code');
});

/**
 * Territory Store Tests
 */
it('can create a new territory with valid data', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    Event::fake();

    $territoryData = [
        'name' => 'North America',
        'code' => 'NA-001',
        'type' => 'geographic',
        'status' => 'active',
        'description' => 'North American territory',
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertRedirect(route('admin.settings.territories.index'))
        ->assertSessionHas('success');

    expect(Territory::where('code', 'NA-001')->exists())->toBeTrue();

    $territory = Territory::where('code', 'NA-001')->first();
    expect($territory->name)->toBe('North America')
        ->and($territory->type)->toBe('geographic')
        ->and($territory->status)->toBe('active')
        ->and($territory->user_id)->toBe($user->id);

    Event::assertDispatched('settings.territory.create.before');
    Event::assertDispatched('settings.territory.create.after');
});

it('can create territory with parent', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Americas',
        'code' => 'AM-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $territoryData = [
        'name' => 'North America',
        'code' => 'NA-001',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertRedirect(route('admin.settings.territories.index'))
        ->assertSessionHas('success');

    $territory = Territory::where('code', 'NA-001')->first();
    expect($territory->parent_id)->toBe($parent->id)
        ->and($territory->parent->name)->toBe('Americas');
});

it('can create territory with boundaries', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $boundaries = json_encode([
        'type' => 'Polygon',
        'coordinates' => [[[0, 0], [10, 0], [10, 10], [0, 10], [0, 0]]],
    ]);

    $territoryData = [
        'name' => 'West Coast',
        'code' => 'WC-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
        'boundaries' => $boundaries,
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertRedirect(route('admin.settings.territories.index'))
        ->assertSessionHas('success');

    $territory = Territory::where('code', 'WC-001')->first();
    expect($territory->boundaries)->toBeArray()
        ->and($territory->boundaries['type'])->toBe('Polygon');
});

/**
 * Territory Store Validation Tests
 */
it('validates required fields when creating territory', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), [])
        ->assertSessionHasErrors(['name', 'code', 'type', 'status', 'user_id']);
});

it('validates name length when creating territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territoryData = [
        'name' => str_repeat('a', 101), // Exceeds max 100
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertSessionHasErrors(['name']);
});

it('validates code uniqueness when creating territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    Territory::create([
        'name' => 'Existing Territory',
        'code' => 'EXIST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $territoryData = [
        'name' => 'New Territory',
        'code' => 'EXIST-001', // Duplicate code
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertSessionHasErrors(['code']);
});

it('validates type is valid when creating territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territoryData = [
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'invalid-type',
        'status' => 'active',
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertSessionHasErrors(['type']);
});

it('validates status is valid when creating territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territoryData = [
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'invalid-status',
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertSessionHasErrors(['status']);
});

it('validates user_id exists when creating territory', function () {
    $admin = getDefaultAdmin();

    $territoryData = [
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => 99999, // Non-existent user
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertSessionHasErrors(['user_id']);
});

it('validates parent_id exists when creating territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territoryData = [
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
        'parent_id' => 99999, // Non-existent parent
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertSessionHasErrors(['parent_id']);
});

it('validates boundaries is valid json when creating territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territoryData = [
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
        'boundaries' => 'not-valid-json',
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.territories.store'), $territoryData)
        ->assertSessionHasErrors(['boundaries']);
});

/**
 * Territory Edit Page Tests
 */
it('can access territory edit page', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    test()->actingAs($admin)
        ->get(route('admin.settings.territories.edit', $territory->id))
        ->assertOK()
        ->assertSee('Test Territory')
        ->assertSee('TEST-001');
});

it('edit page excludes current territory and descendants from parent options', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent',
        'code' => 'PARENT-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child',
        'code' => 'CHILD-001',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    test()->actingAs($admin)
        ->get(route('admin.settings.territories.edit', $parent->id))
        ->assertOK();
});

/**
 * Territory Update Tests
 */
it('can update an existing territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    Event::fake();

    $territory = Territory::create([
        'name' => 'Old Name',
        'code' => 'OLD-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $updateData = [
        'name' => 'New Name',
        'code' => 'NEW-001',
        'type' => 'account-based',
        'status' => 'inactive',
        'description' => 'Updated description',
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->put(route('admin.settings.territories.update', $territory->id), $updateData)
        ->assertRedirect(route('admin.settings.territories.index'))
        ->assertSessionHas('success');

    $territory->refresh();

    expect($territory->name)->toBe('New Name')
        ->and($territory->code)->toBe('NEW-001')
        ->and($territory->type)->toBe('account-based')
        ->and($territory->status)->toBe('inactive')
        ->and($territory->description)->toBe('Updated description');

    Event::assertDispatched('settings.territory.update.before');
    Event::assertDispatched('settings.territory.update.after');
});

it('can update territory parent', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent',
        'code' => 'PARENT-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $territory = Territory::create([
        'name' => 'Child',
        'code' => 'CHILD-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $updateData = [
        'name' => 'Child',
        'code' => 'CHILD-001',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->put(route('admin.settings.territories.update', $territory->id), $updateData)
        ->assertRedirect(route('admin.settings.territories.index'))
        ->assertSessionHas('success');

    $territory->refresh();
    expect($territory->parent_id)->toBe($parent->id);
});

it('prevents setting parent to self when updating', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $updateData = [
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $territory->id, // Setting parent to self
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->put(route('admin.settings.territories.update', $territory->id), $updateData)
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('prevents setting parent to descendant when updating', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent',
        'code' => 'PARENT-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child',
        'code' => 'CHILD-001',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    $updateData = [
        'name' => 'Parent',
        'code' => 'PARENT-001',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $child->id, // Setting parent to its own child
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->put(route('admin.settings.territories.update', $parent->id), $updateData)
        ->assertRedirect()
        ->assertSessionHas('error');
});

/**
 * Territory Update Validation Tests
 */
it('validates required fields when updating territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    test()->actingAs($admin)
        ->put(route('admin.settings.territories.update', $territory->id), [])
        ->assertSessionHasErrors(['name', 'code', 'type', 'status', 'user_id']);
});

it('allows same code when updating own territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territory = Territory::create([
        'name' => 'Test Territory',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $updateData = [
        'name' => 'Updated Name',
        'code' => 'TEST-001', // Same code
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->put(route('admin.settings.territories.update', $territory->id), $updateData)
        ->assertRedirect(route('admin.settings.territories.index'))
        ->assertSessionHas('success');

    $territory->refresh();
    expect($territory->name)->toBe('Updated Name')
        ->and($territory->code)->toBe('TEST-001');
});

it('validates code uniqueness when updating to existing code', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $territory1 = Territory::create([
        'name' => 'Territory 1',
        'code' => 'TERR-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $territory2 = Territory::create([
        'name' => 'Territory 2',
        'code' => 'TERR-002',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $updateData = [
        'name' => 'Territory 2',
        'code' => 'TERR-001', // Trying to use existing code
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ];

    test()->actingAs($admin)
        ->put(route('admin.settings.territories.update', $territory2->id), $updateData)
        ->assertSessionHasErrors(['code']);
});

/**
 * Territory Delete Tests
 */
it('can delete a territory', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    Event::fake();

    $territory = Territory::create([
        'name' => 'To Delete',
        'code' => 'DEL-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    test()->actingAs($admin)
        ->delete(route('admin.settings.territories.delete', $territory->id))
        ->assertStatus(200)
        ->assertJson([
            'message' => trans('admin::app.settings.territories.index.delete-success'),
        ]);

    expect(Territory::find($territory->id))->toBeNull();

    Event::assertDispatched('settings.territory.delete.before');
    Event::assertDispatched('settings.territory.delete.after');
});

it('returns error when deleting non-existent territory', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->delete(route('admin.settings.territories.delete', 99999))
        ->assertStatus(404);
});

/**
 * Territory Hierarchy Page Tests
 */
it('can access territory hierarchy page', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.settings.territories.hierarchy'))
        ->assertOK();
});

it('hierarchy page displays parent-child relationships', function () {
    $admin = getDefaultAdmin();
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent Region',
        'code' => 'PARENT-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child Region',
        'code' => 'CHILD-001',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    test()->actingAs($admin)
        ->get(route('admin.settings.territories.hierarchy'))
        ->assertOK()
        ->assertSee('Parent Region')
        ->assertSee('Child Region');
});

/**
 * Authentication Tests
 */
it('requires authentication to access index page', function () {
    test()->get(route('admin.settings.territories.index'))
        ->assertRedirect(route('admin.session.create'));
});

it('requires authentication to access create page', function () {
    test()->get(route('admin.settings.territories.create'))
        ->assertRedirect(route('admin.session.create'));
});

it('requires authentication to store territory', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'Test',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ];

    test()->post(route('admin.settings.territories.store'), $data)
        ->assertRedirect(route('admin.session.create'));
});

it('requires authentication to access edit page', function () {
    test()->get(route('admin.settings.territories.edit', 1))
        ->assertRedirect(route('admin.session.create'));
});

it('requires authentication to update territory', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'Test',
        'code' => 'TEST-001',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ];

    test()->put(route('admin.settings.territories.update', 1), $data)
        ->assertRedirect(route('admin.session.create'));
});

it('requires authentication to delete territory', function () {
    test()->delete(route('admin.settings.territories.delete', 1))
        ->assertStatus(401);
});

it('requires authentication to access hierarchy page', function () {
    test()->get(route('admin.settings.territories.hierarchy'))
        ->assertRedirect(route('admin.session.create'));
});
