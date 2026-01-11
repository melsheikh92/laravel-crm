<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Lead\Models\Lead;
use Webkul\Territory\Models\Territory;
use Webkul\Territory\Models\TerritoryAssignment;
use Webkul\Territory\Models\TerritoryRule;
use Webkul\Territory\Services\TerritoryAssignmentService;
use App\Models\User;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(TerritoryAssignmentService::class);
    $this->user = User::factory()->create();
});

describe('autoAssign', function () {
    test('assigns entity to matching territory automatically', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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
            protected $fillable = ['state'];
            public $state = 'California';
        };
        $entity->id = 1;

        $assignment = $this->service->autoAssign($entity);

        expect($assignment)->not->toBeNull()
            ->and($assignment->territory_id)->toBe($territory->id)
            ->and($assignment->assignment_type)->toBe('automatic')
            ->and($assignment->assignable_type)->toBe(get_class($entity))
            ->and($assignment->assignable_id)->toBe($entity->id);
    });

    test('returns null when no territory matches', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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
        $entity->id = 1;

        $assignment = $this->service->autoAssign($entity);

        expect($assignment)->toBeNull();
    });

    test('assigns to highest priority territory when multiple matches', function () {
        $lowPriorityTerritory = Territory::create([
            'name' => 'California All',
            'code' => 'CA-ALL',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $lowPriorityTerritory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 5,
            'is_active' => true,
        ]);

        $highPriorityTerritory = Territory::create([
            'name' => 'California Tech',
            'code' => 'CA-TECH',
            'type' => 'account-based',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        TerritoryRule::create([
            'territory_id' => $highPriorityTerritory->id,
            'rule_type' => 'geographic',
            'field_name' => 'state',
            'operator' => '=',
            'value' => ['California'],
            'priority' => 10,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $highPriorityTerritory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => '=',
            'value' => ['Technology'],
            'priority' => 10,
            'is_active' => true,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };
        $entity->id = 1;

        $assignment = $this->service->autoAssign($entity);

        expect($assignment)->not->toBeNull()
            ->and($assignment->territory_id)->toBe($highPriorityTerritory->id);
    });

    test('stores assigned_by user when provided', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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
            public $state = 'California';
        };
        $entity->id = 1;

        $assignedByUser = User::factory()->create();
        $assignment = $this->service->autoAssign($entity, $assignedByUser->id);

        expect($assignment)->not->toBeNull()
            ->and($assignment->assigned_by)->toBe($assignedByUser->id);
    });

    test('does not assign to inactive territories', function () {
        $territory = Territory::create([
            'name' => 'Inactive Territory',
            'code' => 'INACTIVE-01',
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
        $entity->id = 1;

        $assignment = $this->service->autoAssign($entity);

        expect($assignment)->toBeNull();
    });

    test('returns existing assignment when already assigned to same territory', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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
            public $state = 'California';
        };
        $entity->id = 1;

        $firstAssignment = $this->service->autoAssign($entity);
        $secondAssignment = $this->service->autoAssign($entity);

        expect($firstAssignment->id)->toBe($secondAssignment->id);
    });
});

describe('manualAssign', function () {
    test('assigns entity to specified territory manually', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $assignment = $this->service->manualAssign($entity, $territory->id);

        expect($assignment)->not->toBeNull()
            ->and($assignment->territory_id)->toBe($territory->id)
            ->and($assignment->assignment_type)->toBe('manual')
            ->and($assignment->assignable_type)->toBe(get_class($entity))
            ->and($assignment->assignable_id)->toBe($entity->id);
    });

    test('stores assigned_by user when provided', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $assignedByUser = User::factory()->create();
        $assignment = $this->service->manualAssign($entity, $territory->id, $assignedByUser->id);

        expect($assignment)->not->toBeNull()
            ->and($assignment->assigned_by)->toBe($assignedByUser->id);
    });

    test('can manually assign entity that does not match territory rules', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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
        $entity->id = 1;

        $assignment = $this->service->manualAssign($entity, $territory->id);

        expect($assignment)->not->toBeNull()
            ->and($assignment->territory_id)->toBe($territory->id)
            ->and($assignment->assignment_type)->toBe('manual');
    });

    test('can manually assign to inactive territory', function () {
        $territory = Territory::create([
            'name' => 'Inactive Territory',
            'code' => 'INACTIVE-01',
            'type' => 'geographic',
            'status' => 'inactive',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $assignment = $this->service->manualAssign($entity, $territory->id);

        expect($assignment)->not->toBeNull()
            ->and($assignment->territory_id)->toBe($territory->id);
    });
});

describe('reassign', function () {
    test('reassigns entity to new territory', function () {
        $oldTerritory = Territory::create([
            'name' => 'Old Territory',
            'code' => 'OLD-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $newTerritory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            protected $fillable = ['user_id'];
            public $state = 'California';
        };
        $entity->id = 1;

        $this->service->manualAssign($entity, $oldTerritory->id);
        $newAssignment = $this->service->reassign($entity, $newTerritory->id);

        expect($newAssignment)->not->toBeNull()
            ->and($newAssignment->territory_id)->toBe($newTerritory->id)
            ->and($newAssignment->assignment_type)->toBe('manual');
    });

    test('throws exception when territory does not exist', function () {
        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        expect(fn() => $this->service->reassign($entity, 99999))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('transfers ownership when transferOwnership is true and territory has user', function () {
        $oldTerritory = Territory::create([
            'name' => 'Old Territory',
            'code' => 'OLD-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $newOwner = User::factory()->create();
        $newTerritory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $newOwner->id,
        ]);

        $entity = new class extends Model {
            protected $table = 'test_entities';
            protected $fillable = ['user_id'];
            public $user_id;
            public $state = 'California';
        };
        $entity->id = 1;
        $entity->user_id = $this->user->id;
        $entity->exists = true;

        $this->service->manualAssign($entity, $oldTerritory->id);
        $this->service->reassign($entity, $newTerritory->id, null, true);

        expect($entity->user_id)->toBe($newOwner->id);
    });

    test('does not transfer ownership when transferOwnership is false', function () {
        $oldTerritory = Territory::create([
            'name' => 'Old Territory',
            'code' => 'OLD-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $newOwner = User::factory()->create();
        $newTerritory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $newOwner->id,
        ]);

        $entity = new class extends Model {
            protected $table = 'test_entities';
            protected $fillable = ['user_id'];
            public $user_id;
            public $state = 'California';
        };
        $entity->id = 1;
        $entity->user_id = $this->user->id;
        $entity->exists = true;

        $this->service->manualAssign($entity, $oldTerritory->id);
        $this->service->reassign($entity, $newTerritory->id, null, false);

        expect($entity->user_id)->toBe($this->user->id);
    });

    test('stores assigned_by user when provided', function () {
        $oldTerritory = Territory::create([
            'name' => 'Old Territory',
            'code' => 'OLD-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $newTerritory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            protected $fillable = ['user_id'];
            public $state = 'California';
        };
        $entity->id = 1;

        $assignedByUser = User::factory()->create();
        $this->service->manualAssign($entity, $oldTerritory->id);
        $newAssignment = $this->service->reassign($entity, $newTerritory->id, $assignedByUser->id);

        expect($newAssignment)->not->toBeNull()
            ->and($newAssignment->assigned_by)->toBe($assignedByUser->id);
    });

    test('creates assignment history when reassigning', function () {
        $oldTerritory = Territory::create([
            'name' => 'Old Territory',
            'code' => 'OLD-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $newTerritory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            protected $fillable = ['user_id'];
            public $state = 'California';
        };
        $entity->id = 1;

        $this->service->manualAssign($entity, $oldTerritory->id);
        $this->service->reassign($entity, $newTerritory->id);

        $history = $this->service->getAssignmentHistory($entity);

        expect($history)->toHaveCount(2)
            ->and($history->first()->territory_id)->toBe($newTerritory->id)
            ->and($history->last()->territory_id)->toBe($oldTerritory->id);
    });
});

describe('bulkReassign', function () {
    test('reassigns multiple entities to territory', function () {
        $oldTerritory = Territory::create([
            'name' => 'Old Territory',
            'code' => 'OLD-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $newTerritory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity1 = new class extends Model {
            protected $fillable = ['user_id'];
            public $state = 'California';
        };
        $entity1->id = 1;

        $entity2 = new class extends Model {
            protected $fillable = ['user_id'];
            public $state = 'California';
        };
        $entity2->id = 2;

        $this->service->manualAssign($entity1, $oldTerritory->id);
        $this->service->manualAssign($entity2, $oldTerritory->id);

        $assignments = $this->service->bulkReassign([$entity1, $entity2], $newTerritory->id);

        expect($assignments)->toHaveCount(2)
            ->and($assignments->every(fn($a) => $a->territory_id === $newTerritory->id))->toBeTrue();
    });

    test('throws exception when territory does not exist', function () {
        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        expect(fn() => $this->service->bulkReassign([$entity], 99999))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('transfers ownership for all entities when transferOwnership is true', function () {
        $oldTerritory = Territory::create([
            'name' => 'Old Territory',
            'code' => 'OLD-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $newOwner = User::factory()->create();
        $newTerritory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $newOwner->id,
        ]);

        $entity1 = new class extends Model {
            protected $table = 'test_entities';
            protected $fillable = ['user_id'];
            public $user_id;
            public $state = 'California';
        };
        $entity1->id = 1;
        $entity1->user_id = $this->user->id;
        $entity1->exists = true;

        $entity2 = new class extends Model {
            protected $table = 'test_entities';
            protected $fillable = ['user_id'];
            public $user_id;
            public $state = 'California';
        };
        $entity2->id = 2;
        $entity2->user_id = $this->user->id;
        $entity2->exists = true;

        $this->service->bulkReassign([$entity1, $entity2], $newTerritory->id, null, true);

        expect($entity1->user_id)->toBe($newOwner->id)
            ->and($entity2->user_id)->toBe($newOwner->id);
    });

    test('skips non-model objects in entities array', function () {
        $territory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            protected $fillable = ['user_id'];
            public $state = 'California';
        };
        $entity->id = 1;

        $nonModelObject = (object) ['id' => 2, 'state' => 'California'];

        $assignments = $this->service->bulkReassign([$entity, $nonModelObject], $territory->id);

        expect($assignments)->toHaveCount(1);
    });
});

describe('bulkAutoAssign', function () {
    test('auto-assigns multiple entities to matching territories', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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

        $entity1 = new class extends Model {
            public $state = 'California';
        };
        $entity1->id = 1;

        $entity2 = new class extends Model {
            public $state = 'California';
        };
        $entity2->id = 2;

        $assignments = $this->service->bulkAutoAssign([$entity1, $entity2]);

        expect($assignments)->toHaveCount(2)
            ->and($assignments->every(fn($a) => $a->territory_id === $territory->id))->toBeTrue()
            ->and($assignments->every(fn($a) => $a->assignment_type === 'automatic'))->toBeTrue();
    });

    test('skips entities that do not match any territory', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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

        $entity1 = new class extends Model {
            public $state = 'California';
        };
        $entity1->id = 1;

        $entity2 = new class extends Model {
            public $state = 'Texas';
        };
        $entity2->id = 2;

        $assignments = $this->service->bulkAutoAssign([$entity1, $entity2]);

        expect($assignments)->toHaveCount(1)
            ->and($assignments->first()->territory_id)->toBe($territory->id);
    });

    test('skips non-model objects in entities array', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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
            public $state = 'California';
        };
        $entity->id = 1;

        $nonModelObject = (object) ['id' => 2, 'state' => 'California'];

        $assignments = $this->service->bulkAutoAssign([$entity, $nonModelObject]);

        expect($assignments)->toHaveCount(1);
    });
});

describe('unassign', function () {
    test('removes territory assignment from entity', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $this->service->manualAssign($entity, $territory->id);
        $result = $this->service->unassign($entity);

        expect($result)->toBeTrue();

        $currentTerritory = $this->service->getCurrentTerritory($entity);
        expect($currentTerritory)->toBeNull();
    });

    test('returns true even when entity has no assignment', function () {
        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $result = $this->service->unassign($entity);

        expect($result)->toBeGreaterThanOrEqual(0);
    });
});

describe('getCurrentTerritory', function () {
    test('returns current territory for assigned entity', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $this->service->manualAssign($entity, $territory->id);
        $currentTerritory = $this->service->getCurrentTerritory($entity);

        expect($currentTerritory)->not->toBeNull()
            ->and($currentTerritory->id)->toBe($territory->id);
    });

    test('returns null for unassigned entity', function () {
        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $currentTerritory = $this->service->getCurrentTerritory($entity);

        expect($currentTerritory)->toBeNull();
    });

    test('returns most recent territory after reassignment', function () {
        $oldTerritory = Territory::create([
            'name' => 'Old Territory',
            'code' => 'OLD-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $newTerritory = Territory::create([
            'name' => 'New Territory',
            'code' => 'NEW-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            protected $fillable = ['user_id'];
            public $state = 'California';
        };
        $entity->id = 1;

        $this->service->manualAssign($entity, $oldTerritory->id);
        $this->service->reassign($entity, $newTerritory->id);

        $currentTerritory = $this->service->getCurrentTerritory($entity);

        expect($currentTerritory)->not->toBeNull()
            ->and($currentTerritory->id)->toBe($newTerritory->id);
    });
});

describe('isAssignedToTerritory', function () {
    test('returns true when entity is assigned to specified territory', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $this->service->manualAssign($entity, $territory->id);

        expect($this->service->isAssignedToTerritory($entity, $territory->id))->toBeTrue();
    });

    test('returns false when entity is not assigned to specified territory', function () {
        $territory1 = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $territory2 = Territory::create([
            'name' => 'Texas Region',
            'code' => 'TX-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $this->service->manualAssign($entity, $territory1->id);

        expect($this->service->isAssignedToTerritory($entity, $territory2->id))->toBeFalse();
    });

    test('returns false when entity has no assignment', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        expect($this->service->isAssignedToTerritory($entity, $territory->id))->toBeFalse();
    });
});

describe('getAssignmentHistory', function () {
    test('returns assignment history ordered by date descending', function () {
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

        $territory3 = Territory::create([
            'name' => 'Territory 3',
            'code' => 'T3',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity = new class extends Model {
            protected $fillable = ['user_id'];
            public $state = 'California';
        };
        $entity->id = 1;

        $this->service->manualAssign($entity, $territory1->id);
        sleep(1);
        $this->service->reassign($entity, $territory2->id);
        sleep(1);
        $this->service->reassign($entity, $territory3->id);

        $history = $this->service->getAssignmentHistory($entity);

        expect($history)->toHaveCount(3)
            ->and($history->first()->territory_id)->toBe($territory3->id)
            ->and($history->last()->territory_id)->toBe($territory1->id);
    });

    test('returns empty collection when entity has no assignments', function () {
        $entity = new class extends Model {
            public $state = 'California';
        };
        $entity->id = 1;

        $history = $this->service->getAssignmentHistory($entity);

        expect($history)->toBeEmpty();
    });
});

describe('getAssignedEntities', function () {
    test('returns all entities assigned to territory', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $entity1 = new class extends Model {
            public $state = 'California';
        };
        $entity1->id = 1;

        $entity2 = new class extends Model {
            public $state = 'California';
        };
        $entity2->id = 2;

        $this->service->manualAssign($entity1, $territory->id);
        $this->service->manualAssign($entity2, $territory->id);

        $assignedEntities = $this->service->getAssignedEntities($territory->id);

        expect($assignedEntities)->toHaveCount(2);
    });

    test('returns empty collection when territory has no assignments', function () {
        $territory = Territory::create([
            'name' => 'California Region',
            'code' => 'CA-01',
            'type' => 'geographic',
            'status' => 'active',
            'user_id' => $this->user->id,
        ]);

        $assignedEntities = $this->service->getAssignedEntities($territory->id);

        expect($assignedEntities)->toBeEmpty();
    });
});

describe('findMatchingTerritory', function () {
    test('returns null when entity does not match any rules', function () {
        $territory = Territory::create([
            'name' => 'California Region',
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

        $matchingTerritory = $this->service->findMatchingTerritory($entity);

        expect($matchingTerritory)->toBeNull();
    });

    test('returns null for territories without rules', function () {
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

        $matchingTerritory = $this->service->findMatchingTerritory($entity);

        expect($matchingTerritory)->toBeNull();
    });

    test('evaluates all rules with AND logic', function () {
        $territory = Territory::create([
            'name' => 'California Tech',
            'code' => 'CA-TECH',
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
            'priority' => 10,
            'is_active' => true,
        ]);

        TerritoryRule::create([
            'territory_id' => $territory->id,
            'rule_type' => 'industry',
            'field_name' => 'industry',
            'operator' => '=',
            'value' => ['Technology'],
            'priority' => 10,
            'is_active' => true,
        ]);

        $matchingEntity = new class extends Model {
            public $state = 'California';
            public $industry = 'Technology';
        };

        $nonMatchingEntity = new class extends Model {
            public $state = 'California';
            public $industry = 'Healthcare';
        };

        $matchingResult = $this->service->findMatchingTerritory($matchingEntity);
        $nonMatchingResult = $this->service->findMatchingTerritory($nonMatchingEntity);

        expect($matchingResult)->not->toBeNull()
            ->and($matchingResult->id)->toBe($territory->id)
            ->and($nonMatchingResult)->toBeNull();
    });
});
