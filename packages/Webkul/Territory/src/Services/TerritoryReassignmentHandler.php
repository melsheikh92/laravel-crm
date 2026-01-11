<?php

namespace Webkul\Territory\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webkul\Contact\Repositories\OrganizationRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Territory\Repositories\TerritoryAssignmentRepository;
use Webkul\Territory\Repositories\TerritoryRepository;

class TerritoryReassignmentHandler
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected TerritoryRepository $territoryRepository,
        protected TerritoryAssignmentRepository $assignmentRepository,
        protected LeadRepository $leadRepository,
        protected OrganizationRepository $organizationRepository,
        protected PersonRepository $personRepository
    ) {}

    /**
     * Handle ownership transfer when a territory's owner changes.
     *
     * @param  int  $territoryId
     * @param  int|null  $oldOwnerId
     * @param  int|null  $newOwnerId
     * @return array
     */
    public function handleTerritoryOwnerChange(int $territoryId, ?int $oldOwnerId, ?int $newOwnerId): array
    {
        if ($oldOwnerId === $newOwnerId || ! $newOwnerId) {
            return [
                'success' => false,
                'message' => 'No ownership change detected or new owner is null.',
                'updated' => 0,
            ];
        }

        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory) {
            return [
                'success' => false,
                'message' => "Territory with ID {$territoryId} not found.",
                'updated' => 0,
            ];
        }

        $assignments = $this->assignmentRepository->getAssignmentsByTerritory($territoryId);

        $updatedCount = 0;

        foreach ($assignments as $assignment) {
            $entity = $this->getEntityFromAssignment($assignment);

            if ($entity && $this->transferEntityOwnership($entity, $newOwnerId)) {
                $updatedCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Successfully updated ownership for {$updatedCount} entities.",
            'updated' => $updatedCount,
        ];
    }

    /**
     * Transfer ownership of all entities in a territory to a new owner.
     *
     * @param  int  $territoryId
     * @param  int  $newOwnerId
     * @return array
     */
    public function transferTerritoryEntitiesOwnership(int $territoryId, int $newOwnerId): array
    {
        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory) {
            return [
                'success' => false,
                'message' => "Territory with ID {$territoryId} not found.",
                'updated' => 0,
            ];
        }

        $assignments = $this->assignmentRepository->getAssignmentsByTerritory($territoryId);

        $updatedCount = 0;

        foreach ($assignments as $assignment) {
            $entity = $this->getEntityFromAssignment($assignment);

            if ($entity && $this->transferEntityOwnership($entity, $newOwnerId)) {
                $updatedCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Successfully transferred ownership of {$updatedCount} entities to new owner.",
            'updated' => $updatedCount,
        ];
    }

    /**
     * Sync ownership for all entities in a territory with the territory owner.
     *
     * @param  int  $territoryId
     * @return array
     */
    public function syncTerritoryOwnership(int $territoryId): array
    {
        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory) {
            return [
                'success' => false,
                'message' => "Territory with ID {$territoryId} not found.",
                'synced' => 0,
            ];
        }

        if (! $territory->user_id) {
            return [
                'success' => false,
                'message' => 'Territory has no owner assigned.',
                'synced' => 0,
            ];
        }

        $assignments = $this->assignmentRepository->getAssignmentsByTerritory($territoryId);

        $syncedCount = 0;

        foreach ($assignments as $assignment) {
            $entity = $this->getEntityFromAssignment($assignment);

            if ($entity && $entity->user_id !== $territory->user_id) {
                if ($this->transferEntityOwnership($entity, $territory->user_id)) {
                    $syncedCount++;
                }
            }
        }

        return [
            'success' => true,
            'message' => "Successfully synced ownership for {$syncedCount} entities.",
            'synced' => $syncedCount,
        ];
    }

    /**
     * Bulk transfer ownership for multiple territories.
     *
     * @param  array  $territoryIds
     * @param  int  $newOwnerId
     * @return array
     */
    public function bulkTransferOwnership(array $territoryIds, int $newOwnerId): array
    {
        $totalUpdated = 0;
        $results = [];

        foreach ($territoryIds as $territoryId) {
            $result = $this->transferTerritoryEntitiesOwnership($territoryId, $newOwnerId);
            $totalUpdated += $result['updated'];
            $results[] = $result;
        }

        return [
            'success' => true,
            'message' => "Bulk transfer completed. Updated {$totalUpdated} entities across ".count($territoryIds).' territories.',
            'total_updated' => $totalUpdated,
            'results' => $results,
        ];
    }

    /**
     * Get entities that need ownership transfer in a territory.
     *
     * @param  int  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function getEntitiesNeedingOwnershipSync(int $territoryId): Collection
    {
        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory || ! $territory->user_id) {
            return collect([]);
        }

        $assignments = $this->assignmentRepository->getAssignmentsByTerritory($territoryId);

        $entitiesNeedingSync = collect([]);

        foreach ($assignments as $assignment) {
            $entity = $this->getEntityFromAssignment($assignment);

            if ($entity && $entity->user_id !== $territory->user_id) {
                $entitiesNeedingSync->push([
                    'assignment' => $assignment,
                    'entity' => $entity,
                    'current_owner_id' => $entity->user_id,
                    'expected_owner_id' => $territory->user_id,
                ]);
            }
        }

        return $entitiesNeedingSync;
    }

    /**
     * Transfer ownership of an entity to a new owner.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  int  $newOwnerId
     * @return bool
     */
    protected function transferEntityOwnership(Model $entity, int $newOwnerId): bool
    {
        if (! property_exists($entity, 'fillable') || ! in_array('user_id', $entity->getFillable())) {
            return false;
        }

        if ($entity->user_id === $newOwnerId) {
            return false;
        }

        try {
            $entity->user_id = $newOwnerId;
            $entity->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the entity instance from an assignment.
     *
     * @param  \Webkul\Territory\Contracts\TerritoryAssignment  $assignment
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getEntityFromAssignment($assignment): ?Model
    {
        $entityType = $assignment->assignable_type;
        $entityId = $assignment->assignable_id;

        $repository = match ($entityType) {
            'Webkul\Lead\Models\Lead' => $this->leadRepository,
            'Webkul\Contact\Models\Organization' => $this->organizationRepository,
            'Webkul\Contact\Models\Person' => $this->personRepository,
            default => null,
        };

        if (! $repository) {
            return null;
        }

        try {
            return $repository->find($entityId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get ownership statistics for a territory.
     *
     * @param  int  $territoryId
     * @return array
     */
    public function getOwnershipStatistics(int $territoryId): array
    {
        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory) {
            return [
                'territory_id' => $territoryId,
                'territory_owner_id' => null,
                'total_assignments' => 0,
                'entities_with_correct_owner' => 0,
                'entities_with_incorrect_owner' => 0,
                'entities_without_owner' => 0,
            ];
        }

        $assignments = $this->assignmentRepository->getAssignmentsByTerritory($territoryId);

        $correctOwner = 0;
        $incorrectOwner = 0;
        $noOwner = 0;

        foreach ($assignments as $assignment) {
            $entity = $this->getEntityFromAssignment($assignment);

            if (! $entity) {
                continue;
            }

            if (! $entity->user_id) {
                $noOwner++;
            } elseif ($entity->user_id === $territory->user_id) {
                $correctOwner++;
            } else {
                $incorrectOwner++;
            }
        }

        return [
            'territory_id' => $territoryId,
            'territory_owner_id' => $territory->user_id,
            'total_assignments' => $assignments->count(),
            'entities_with_correct_owner' => $correctOwner,
            'entities_with_incorrect_owner' => $incorrectOwner,
            'entities_without_owner' => $noOwner,
        ];
    }

    /**
     * Get all territories with ownership mismatches.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTerritoriesWithOwnershipMismatches(): Collection
    {
        $territories = $this->territoryRepository->findWhere(['status' => 'active']);

        $territoriesWithMismatches = collect([]);

        foreach ($territories as $territory) {
            if (! $territory->user_id) {
                continue;
            }

            $entitiesNeedingSync = $this->getEntitiesNeedingOwnershipSync($territory->id);

            if ($entitiesNeedingSync->isNotEmpty()) {
                $territoriesWithMismatches->push([
                    'territory' => $territory,
                    'mismatch_count' => $entitiesNeedingSync->count(),
                    'entities' => $entitiesNeedingSync,
                ]);
            }
        }

        return $territoriesWithMismatches;
    }
}
