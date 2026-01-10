<?php

namespace Webkul\Territory\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webkul\Territory\Repositories\TerritoryAssignmentRepository;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Repositories\TerritoryRuleRepository;

class TerritoryAssignmentService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected TerritoryAssignmentRepository $assignmentRepository,
        protected TerritoryRepository $territoryRepository,
        protected TerritoryRuleRepository $ruleRepository
    ) {}

    /**
     * Auto-assign an entity to a territory based on rules.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  int|null  $assignedBy
     * @return \Webkul\Territory\Contracts\TerritoryAssignment|null
     */
    public function autoAssign(Model $entity, ?int $assignedBy = null)
    {
        $matchingTerritory = $this->findMatchingTerritory($entity);

        if (! $matchingTerritory) {
            return null;
        }

        return $this->assignToTerritory($entity, $matchingTerritory->id, $assignedBy, 'automatic');
    }

    /**
     * Find matching territory for an entity based on rules.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return \Webkul\Territory\Contracts\Territory|null
     */
    public function findMatchingTerritory(Model $entity)
    {
        $activeTerritories = $this->territoryRepository->getActiveTerritories();

        $matchedTerritories = [];

        foreach ($activeTerritories as $territory) {
            $rules = $this->ruleRepository->getActiveRulesByPriority($territory->id);

            if ($rules->isEmpty()) {
                continue;
            }

            $allRulesMatch = $this->evaluateRules($rules, $entity);

            if ($allRulesMatch) {
                $highestPriority = $rules->first()->priority ?? 0;
                $matchedTerritories[] = [
                    'territory' => $territory,
                    'priority'  => $highestPriority,
                ];
            }
        }

        if (empty($matchedTerritories)) {
            return null;
        }

        usort($matchedTerritories, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        return $matchedTerritories[0]['territory'];
    }

    /**
     * Evaluate all rules for an entity.
     *
     * @param  \Illuminate\Support\Collection  $rules
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return bool
     */
    protected function evaluateRules(Collection $rules, Model $entity): bool
    {
        foreach ($rules as $rule) {
            if (! $rule->evaluate($entity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Manually assign an entity to a territory.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  int  $territoryId
     * @param  int|null  $assignedBy
     * @return \Webkul\Territory\Contracts\TerritoryAssignment
     */
    public function manualAssign(Model $entity, int $territoryId, ?int $assignedBy = null)
    {
        return $this->assignToTerritory($entity, $territoryId, $assignedBy, 'manual');
    }

    /**
     * Assign an entity to a territory.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  int  $territoryId
     * @param  int|null  $assignedBy
     * @param  string  $assignmentType
     * @return \Webkul\Territory\Contracts\TerritoryAssignment
     */
    protected function assignToTerritory(
        Model $entity,
        int $territoryId,
        ?int $assignedBy = null,
        string $assignmentType = 'automatic'
    ) {
        $assignableType = get_class($entity);

        $currentAssignment = $this->assignmentRepository->getCurrentAssignment($assignableType, $entity->id);

        if ($currentAssignment && $currentAssignment->territory_id === $territoryId) {
            return $currentAssignment;
        }

        return $this->assignmentRepository->create([
            'territory_id'     => $territoryId,
            'assignable_type'  => $assignableType,
            'assignable_id'    => $entity->id,
            'assigned_by'      => $assignedBy,
            'assignment_type'  => $assignmentType,
            'assigned_at'      => now(),
        ]);
    }

    /**
     * Reassign an entity to a different territory with ownership transfer.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  int  $newTerritoryId
     * @param  int|null  $assignedBy
     * @param  bool  $transferOwnership
     * @return \Webkul\Territory\Contracts\TerritoryAssignment
     */
    public function reassign(
        Model $entity,
        int $newTerritoryId,
        ?int $assignedBy = null,
        bool $transferOwnership = true
    ) {
        $territory = $this->territoryRepository->find($newTerritoryId);

        if (! $territory) {
            throw new \InvalidArgumentException("Territory with ID {$newTerritoryId} not found.");
        }

        $assignment = $this->assignmentRepository->reassign(
            get_class($entity),
            $entity->id,
            $newTerritoryId,
            $assignedBy,
            'manual'
        );

        if ($transferOwnership && $territory->user_id) {
            $this->transferOwnership($entity, $territory->user_id);
        }

        return $assignment;
    }

    /**
     * Transfer ownership of an entity to a new user.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  int  $newOwnerId
     * @return void
     */
    protected function transferOwnership(Model $entity, int $newOwnerId): void
    {
        if (! property_exists($entity, 'fillable') || ! in_array('user_id', $entity->getFillable())) {
            return;
        }

        $entity->user_id = $newOwnerId;
        $entity->save();
    }

    /**
     * Bulk reassign multiple entities to a territory.
     *
     * @param  array  $entities
     * @param  int  $territoryId
     * @param  int|null  $assignedBy
     * @param  bool  $transferOwnership
     * @return \Illuminate\Support\Collection
     */
    public function bulkReassign(
        array $entities,
        int $territoryId,
        ?int $assignedBy = null,
        bool $transferOwnership = true
    ): Collection {
        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory) {
            throw new \InvalidArgumentException("Territory with ID {$territoryId} not found.");
        }

        $assignments = collect([]);

        foreach ($entities as $entity) {
            if (! $entity instanceof Model) {
                continue;
            }

            $assignment = $this->assignmentRepository->reassign(
                get_class($entity),
                $entity->id,
                $territoryId,
                $assignedBy,
                'manual'
            );

            $assignments->push($assignment);

            if ($transferOwnership && $territory->user_id) {
                $this->transferOwnership($entity, $territory->user_id);
            }
        }

        return $assignments;
    }

    /**
     * Remove territory assignment from an entity.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return bool
     */
    public function unassign(Model $entity): bool
    {
        return $this->assignmentRepository->deleteByAssignable(
            get_class($entity),
            $entity->id
        );
    }

    /**
     * Get current territory for an entity.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return \Webkul\Territory\Contracts\Territory|null
     */
    public function getCurrentTerritory(Model $entity)
    {
        $assignment = $this->assignmentRepository->getCurrentAssignment(
            get_class($entity),
            $entity->id
        );

        return $assignment ? $assignment->territory : null;
    }

    /**
     * Check if an entity is assigned to a specific territory.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  int  $territoryId
     * @return bool
     */
    public function isAssignedToTerritory(Model $entity, int $territoryId): bool
    {
        return $this->assignmentRepository->isAssignedToTerritory(
            get_class($entity),
            $entity->id,
            $territoryId
        );
    }

    /**
     * Get assignment history for an entity.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentHistory(Model $entity): Collection
    {
        return $this->assignmentRepository->getAssignmentHistory(
            get_class($entity),
            $entity->id
        );
    }

    /**
     * Get all entities assigned to a territory.
     *
     * @param  int  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function getAssignedEntities(int $territoryId): Collection
    {
        return $this->assignmentRepository->getAssignmentsByTerritory($territoryId);
    }

    /**
     * Auto-assign multiple entities to territories based on rules.
     *
     * @param  array  $entities
     * @param  int|null  $assignedBy
     * @return \Illuminate\Support\Collection
     */
    public function bulkAutoAssign(array $entities, ?int $assignedBy = null): Collection
    {
        $assignments = collect([]);

        foreach ($entities as $entity) {
            if (! $entity instanceof Model) {
                continue;
            }

            $assignment = $this->autoAssign($entity, $assignedBy);

            if ($assignment) {
                $assignments->push($assignment);
            }
        }

        return $assignments;
    }
}
