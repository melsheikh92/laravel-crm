<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webkul\Territory\Models\Territory;
use Webkul\Territory\Repositories\TerritoryRepository;
use App\Models\User;

uses(RefreshDatabase::class);

/**
 * Basic Hierarchy Relationship Tests
 */
test('territory can have a grandparent-parent-child hierarchy', function () {
    $user = User::factory()->create();

    $grandparent = Territory::create([
        'name' => 'Global',
        'code' => 'GLOBAL-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $parent = Territory::create([
        'name' => 'North America',
        'code' => 'NA-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $grandparent->id,
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'California',
        'code' => 'CA-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    expect($child->parent->id)->toBe($parent->id)
        ->and($child->parent->parent->id)->toBe($grandparent->id)
        ->and($parent->parent->id)->toBe($grandparent->id)
        ->and($grandparent->children)->toHaveCount(1)
        ->and($parent->children)->toHaveCount(1);
});

test('territory can have multiple levels of children', function () {
    $user = User::factory()->create();

    $root = Territory::create([
        'name' => 'World',
        'code' => 'WORLD-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    // Level 1 children
    $americas = Territory::create([
        'name' => 'Americas',
        'code' => 'AM-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root->id,
        'user_id' => $user->id,
    ]);

    $europe = Territory::create([
        'name' => 'Europe',
        'code' => 'EU-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root->id,
        'user_id' => $user->id,
    ]);

    // Level 2 children
    $northAmerica = Territory::create([
        'name' => 'North America',
        'code' => 'NA-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $americas->id,
        'user_id' => $user->id,
    ]);

    $southAmerica = Territory::create([
        'name' => 'South America',
        'code' => 'SA-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $americas->id,
        'user_id' => $user->id,
    ]);

    // Refresh to get latest counts
    $root->refresh();
    $americas->refresh();

    expect($root->children)->toHaveCount(2)
        ->and($americas->children)->toHaveCount(2)
        ->and($northAmerica->parent->id)->toBe($americas->id)
        ->and($southAmerica->parent->id)->toBe($americas->id);
});

test('root territory has no parent', function () {
    $user = User::factory()->create();

    $root = Territory::create([
        'name' => 'Root Territory',
        'code' => 'ROOT-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    expect($root->parent_id)->toBeNull()
        ->and($root->parent)->toBeNull();
});

test('territory can be moved from one parent to another', function () {
    $user = User::factory()->create();

    $parent1 = Territory::create([
        'name' => 'Parent 1',
        'code' => 'P1-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $parent2 = Territory::create([
        'name' => 'Parent 2',
        'code' => 'P2-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child',
        'code' => 'C-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent1->id,
        'user_id' => $user->id,
    ]);

    // Verify initial parent
    expect($child->parent->id)->toBe($parent1->id);

    // Move to parent2
    $child->update(['parent_id' => $parent2->id]);
    $child->refresh();
    $parent1->refresh();
    $parent2->refresh();

    expect($child->parent->id)->toBe($parent2->id)
        ->and($parent1->children)->toHaveCount(0)
        ->and($parent2->children)->toHaveCount(1);
});

/**
 * Repository Hierarchy Method Tests
 */
test('repository can get root territories', function () {
    $user = User::factory()->create();
    $repository = app(TerritoryRepository::class);

    $root1 = Territory::create([
        'name' => 'Root 1',
        'code' => 'R1-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $root2 = Territory::create([
        'name' => 'Root 2',
        'code' => 'R2-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child',
        'code' => 'C-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root1->id,
        'user_id' => $user->id,
    ]);

    $rootTerritories = $repository->getRootTerritories();

    expect($rootTerritories)->toHaveCount(2)
        ->and($rootTerritories->pluck('id')->toArray())->toContain($root1->id, $root2->id)
        ->and($rootTerritories->pluck('id')->toArray())->not->toContain($child->id);
});

test('repository can get territory hierarchy', function () {
    $user = User::factory()->create();
    $repository = app(TerritoryRepository::class);

    $root = Territory::create([
        'name' => 'Root',
        'code' => 'ROOT-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child1 = Territory::create([
        'name' => 'Child 1',
        'code' => 'C1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root->id,
        'user_id' => $user->id,
    ]);

    $child2 = Territory::create([
        'name' => 'Child 2',
        'code' => 'C2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root->id,
        'user_id' => $user->id,
    ]);

    $hierarchy = $repository->getHierarchy();

    expect($hierarchy)->toHaveCount(1)
        ->and($hierarchy->first()->id)->toBe($root->id)
        ->and($hierarchy->first()->children)->toHaveCount(2);
});

test('repository can get child territories', function () {
    $user = User::factory()->create();
    $repository = app(TerritoryRepository::class);

    $parent = Territory::create([
        'name' => 'Parent',
        'code' => 'P-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child1 = Territory::create([
        'name' => 'Child 1',
        'code' => 'C1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    $child2 = Territory::create([
        'name' => 'Child 2',
        'code' => 'C2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    $child3 = Territory::create([
        'name' => 'Child 3',
        'code' => 'C3-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    $children = $repository->getChildTerritories($parent->id);

    expect($children)->toHaveCount(3)
        ->and($children->pluck('id')->toArray())->toContain($child1->id, $child2->id, $child3->id);
});

test('repository can get all descendants recursively', function () {
    $user = User::factory()->create();
    $repository = app(TerritoryRepository::class);

    $grandparent = Territory::create([
        'name' => 'Grandparent',
        'code' => 'GP-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $parent1 = Territory::create([
        'name' => 'Parent 1',
        'code' => 'P1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $grandparent->id,
        'user_id' => $user->id,
    ]);

    $parent2 = Territory::create([
        'name' => 'Parent 2',
        'code' => 'P2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $grandparent->id,
        'user_id' => $user->id,
    ]);

    $child1 = Territory::create([
        'name' => 'Child 1',
        'code' => 'C1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent1->id,
        'user_id' => $user->id,
    ]);

    $child2 = Territory::create([
        'name' => 'Child 2',
        'code' => 'C2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent1->id,
        'user_id' => $user->id,
    ]);

    $grandchild = Territory::create([
        'name' => 'Grandchild',
        'code' => 'GC-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $child1->id,
        'user_id' => $user->id,
    ]);

    $descendants = $repository->getDescendants($grandparent->id);

    // Should get all descendants: 2 parents + 2 children + 1 grandchild = 5 total
    expect($descendants)->toHaveCount(5)
        ->and($descendants->pluck('id')->toArray())->toContain(
            $parent1->id,
            $parent2->id,
            $child1->id,
            $child2->id,
            $grandchild->id
        );
});

test('repository returns empty collection for non-existent territory descendants', function () {
    $repository = app(TerritoryRepository::class);

    $descendants = $repository->getDescendants(999999);

    expect($descendants)->toBeEmpty();
});

test('repository returns empty collection for territory with no descendants', function () {
    $user = User::factory()->create();
    $repository = app(TerritoryRepository::class);

    $territory = Territory::create([
        'name' => 'Leaf Territory',
        'code' => 'LEAF-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $descendants = $repository->getDescendants($territory->id);

    expect($descendants)->toBeEmpty();
});

/**
 * Hierarchy Deletion Tests
 */
test('deleting parent territory orphans children', function () {
    $user = User::factory()->create();
    $repository = app(TerritoryRepository::class);

    $parent = Territory::create([
        'name' => 'Parent',
        'code' => 'P-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child1 = Territory::create([
        'name' => 'Child 1',
        'code' => 'C1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    $child2 = Territory::create([
        'name' => 'Child 2',
        'code' => 'C2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    // Delete parent
    $repository->delete($parent->id);

    // Refresh children
    $child1->refresh();
    $child2->refresh();

    // Children should now have no parent
    expect($child1->parent_id)->toBeNull()
        ->and($child2->parent_id)->toBeNull()
        ->and($child1->parent)->toBeNull()
        ->and($child2->parent)->toBeNull();
});

test('soft deleted parent does not appear in children parent relationship', function () {
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent',
        'code' => 'P-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child',
        'code' => 'C-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    // Before soft delete
    expect($child->parent)->not->toBeNull()
        ->and($child->parent->id)->toBe($parent->id);

    // Soft delete parent
    $parent->delete();

    // Refresh child
    $child->refresh();

    // After deletion, parent_id is set to null
    expect($child->parent_id)->toBeNull()
        ->and($child->parent)->toBeNull();
});

/**
 * Complex Hierarchy Query Tests
 */
test('can query territories with specific depth levels', function () {
    $user = User::factory()->create();

    // Level 0 (root)
    $root = Territory::create([
        'name' => 'Root',
        'code' => 'ROOT-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    // Level 1
    $level1 = Territory::create([
        'name' => 'Level 1',
        'code' => 'L1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root->id,
        'user_id' => $user->id,
    ]);

    // Level 2
    $level2 = Territory::create([
        'name' => 'Level 2',
        'code' => 'L2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $level1->id,
        'user_id' => $user->id,
    ]);

    // Query level 0 (no parent)
    $level0Territories = Territory::whereNull('parent_id')->get();
    expect($level0Territories)->toHaveCount(1)
        ->and($level0Territories->first()->id)->toBe($root->id);

    // Query level 1 (root's children)
    $level1Territories = Territory::where('parent_id', $root->id)->get();
    expect($level1Territories)->toHaveCount(1)
        ->and($level1Territories->first()->id)->toBe($level1->id);

    // Query level 2 (level1's children)
    $level2Territories = Territory::where('parent_id', $level1->id)->get();
    expect($level2Territories)->toHaveCount(1)
        ->and($level2Territories->first()->id)->toBe($level2->id);
});

test('can find all siblings of a territory', function () {
    $user = User::factory()->create();

    $parent = Territory::create([
        'name' => 'Parent',
        'code' => 'P-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child1 = Territory::create([
        'name' => 'Child 1',
        'code' => 'C1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    $child2 = Territory::create([
        'name' => 'Child 2',
        'code' => 'C2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    $child3 = Territory::create([
        'name' => 'Child 3',
        'code' => 'C3-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    // Find siblings of child1 (excluding itself)
    $siblings = Territory::where('parent_id', $child1->parent_id)
        ->where('id', '!=', $child1->id)
        ->get();

    expect($siblings)->toHaveCount(2)
        ->and($siblings->pluck('id')->toArray())->toContain($child2->id, $child3->id)
        ->and($siblings->pluck('id')->toArray())->not->toContain($child1->id);
});

test('can find all leaf territories in hierarchy', function () {
    $user = User::factory()->create();

    $root = Territory::create([
        'name' => 'Root',
        'code' => 'ROOT-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $branch1 = Territory::create([
        'name' => 'Branch 1',
        'code' => 'B1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root->id,
        'user_id' => $user->id,
    ]);

    $branch2 = Territory::create([
        'name' => 'Branch 2',
        'code' => 'B2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root->id,
        'user_id' => $user->id,
    ]);

    $leaf1 = Territory::create([
        'name' => 'Leaf 1',
        'code' => 'L1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $branch1->id,
        'user_id' => $user->id,
    ]);

    $leaf2 = Territory::create([
        'name' => 'Leaf 2',
        'code' => 'L2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $branch2->id,
        'user_id' => $user->id,
    ]);

    // Find all territories that have no children (leaf nodes)
    $leafTerritories = Territory::whereDoesntHave('children')->get();

    expect($leafTerritories)->toHaveCount(2)
        ->and($leafTerritories->pluck('id')->toArray())->toContain($leaf1->id, $leaf2->id)
        ->and($leafTerritories->pluck('id')->toArray())->not->toContain($root->id, $branch1->id, $branch2->id);
});

test('hierarchy maintains referential integrity with eager loading', function () {
    $user = User::factory()->create();

    $root = Territory::create([
        'name' => 'Root',
        'code' => 'ROOT-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $child = Territory::create([
        'name' => 'Child',
        'code' => 'C-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root->id,
        'user_id' => $user->id,
    ]);

    // Load with relationships
    $loadedTerritory = Territory::with(['parent', 'children', 'owner'])->find($child->id);

    expect($loadedTerritory)->not->toBeNull()
        ->and($loadedTerritory->parent)->not->toBeNull()
        ->and($loadedTerritory->parent->id)->toBe($root->id)
        ->and($loadedTerritory->owner)->not->toBeNull()
        ->and($loadedTerritory->owner->id)->toBe($user->id);
});

test('can count territories at each hierarchy level', function () {
    $user = User::factory()->create();

    // Level 0
    $root1 = Territory::create([
        'name' => 'Root 1',
        'code' => 'R1-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $root2 = Territory::create([
        'name' => 'Root 2',
        'code' => 'R2-01',
        'type' => 'geographic',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    // Level 1
    $child1 = Territory::create([
        'name' => 'Child 1',
        'code' => 'C1-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root1->id,
        'user_id' => $user->id,
    ]);

    $child2 = Territory::create([
        'name' => 'Child 2',
        'code' => 'C2-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root1->id,
        'user_id' => $user->id,
    ]);

    $child3 = Territory::create([
        'name' => 'Child 3',
        'code' => 'C3-01',
        'type' => 'geographic',
        'status' => 'active',
        'parent_id' => $root2->id,
        'user_id' => $user->id,
    ]);

    $level0Count = Territory::whereNull('parent_id')->count();
    $level1Count = Territory::whereNotNull('parent_id')->whereHas('parent', function ($query) {
        $query->whereNull('parent_id');
    })->count();

    expect($level0Count)->toBe(2)
        ->and($level1Count)->toBe(3);
});
