<?php

namespace Webkul\Territory\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Territory\Contracts\TerritoryAssignment;

class TerritoryAssignmentRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'territory_id',
        'assignable_type',
        'assignable_id',
        'assignment_type',
        'assigned_by',
        'assigned_at',
        'territory.name',
        'assignedBy.name',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return TerritoryAssignment::class;
    }

    /**
     * Get assignments by territory ID.
     *
     * @param  int  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentsByTerritory(int $territoryId): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId) {
            return $query->byTerritory($territoryId);
        })->all();
    }

    /**
     * Get assignments for an assignable entity (Lead, Organization, Person).
     *
     * @param  string  $assignableType
     * @param  int  $assignableId
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentsByAssignable(string $assignableType, int $assignableId): Collection
    {
        return $this->scopeQuery(function ($query) use ($assignableType, $assignableId) {
            return $query->where('assignable_type', $assignableType)
                ->where('assignable_id', $assignableId);
        })->all();
    }

    /**
     * Get assignment history for an assignable entity ordered by date.
     *
     * @param  string  $assignableType
     * @param  int  $assignableId
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentHistory(string $assignableType, int $assignableId): Collection
    {
        return $this->scopeQuery(function ($query) use ($assignableType, $assignableId) {
            return $query->where('assignable_type', $assignableType)
                ->where('assignable_id', $assignableId)
                ->orderBy('assigned_at', 'desc');
        })->all();
    }

    /**
     * Get current territory assignment for an assignable entity.
     *
     * @param  string  $assignableType
     * @param  int  $assignableId
     * @return \Webkul\Territory\Contracts\TerritoryAssignment|null
     */
    public function getCurrentAssignment(string $assignableType, int $assignableId)
    {
        return $this->model
            ->where('assignable_type', $assignableType)
            ->where('assignable_id', $assignableId)
            ->orderBy('assigned_at', 'desc')
            ->first();
    }

    /**
     * Get manual assignments.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getManualAssignments(): Collection
    {
        return $this->scopeQuery(function ($query) {
            return $query->manual();
        })->all();
    }

    /**
     * Get automatic assignments.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAutomaticAssignments(): Collection
    {
        return $this->scopeQuery(function ($query) {
            return $query->automatic();
        })->all();
    }

    /**
     * Get assignments by assignable type.
     *
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentsByAssignableType(string $type): Collection
    {
        return $this->scopeQuery(function ($query) use ($type) {
            return $query->byAssignableType($type);
        })->all();
    }

    /**
     * Get assignments by assignment type (manual/automatic).
     *
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentsByType(string $type): Collection
    {
        return $this->scopeQuery(function ($query) use ($type) {
            return $query->byType($type);
        })->all();
    }

    /**
     * Get assignments made by a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentsByUser(int $userId): Collection
    {
        return $this->scopeQuery(function ($query) use ($userId) {
            return $query->where('assigned_by', $userId);
        })->all();
    }

    /**
     * Get assignment with all relationships loaded.
     *
     * @param  int  $id
     * @return \Webkul\Territory\Contracts\TerritoryAssignment
     */
    public function findWithRelations(int $id)
    {
        return $this->with([
            'territory',
            'assignable',
            'assignedBy',
        ])->find($id);
    }

    /**
     * Create a new territory assignment.
     *
     * @param  array  $data
     * @return \Webkul\Territory\Contracts\TerritoryAssignment
     */
    public function create(array $data)
    {
        // Set default assignment_type if not provided
        if (! isset($data['assignment_type'])) {
            $data['assignment_type'] = 'automatic';
        }

        // Set assigned_at to current time if not provided
        if (! isset($data['assigned_at'])) {
            $data['assigned_at'] = now();
        }

        return parent::create($data);
    }

    /**
     * Reassign an assignable entity to a different territory.
     *
     * @param  string  $assignableType
     * @param  int  $assignableId
     * @param  int  $newTerritoryId
     * @param  int|null  $assignedBy
     * @param  string  $assignmentType
     * @return \Webkul\Territory\Contracts\TerritoryAssignment
     */
    public function reassign(
        string $assignableType,
        int $assignableId,
        int $newTerritoryId,
        ?int $assignedBy = null,
        string $assignmentType = 'manual'
    ) {
        return $this->create([
            'territory_id'     => $newTerritoryId,
            'assignable_type'  => $assignableType,
            'assignable_id'    => $assignableId,
            'assigned_by'      => $assignedBy,
            'assignment_type'  => $assignmentType,
            'assigned_at'      => now(),
        ]);
    }

    /**
     * Bulk reassign multiple entities to a territory.
     *
     * @param  array  $assignables Array of ['type' => string, 'id' => int] items
     * @param  int  $territoryId
     * @param  int|null  $assignedBy
     * @param  string  $assignmentType
     * @return \Illuminate\Support\Collection
     */
    public function bulkReassign(
        array $assignables,
        int $territoryId,
        ?int $assignedBy = null,
        string $assignmentType = 'manual'
    ): Collection {
        $assignments = collect([]);

        foreach ($assignables as $assignable) {
            $assignment = $this->reassign(
                $assignable['type'],
                $assignable['id'],
                $territoryId,
                $assignedBy,
                $assignmentType
            );

            $assignments->push($assignment);
        }

        return $assignments;
    }

    /**
     * Delete all assignments for a territory.
     *
     * @param  int  $territoryId
     * @return bool
     */
    public function deleteByTerritory(int $territoryId): bool
    {
        return $this->model->where('territory_id', $territoryId)->delete();
    }

    /**
     * Delete all assignments for an assignable entity.
     *
     * @param  string  $assignableType
     * @param  int  $assignableId
     * @return bool
     */
    public function deleteByAssignable(string $assignableType, int $assignableId): bool
    {
        return $this->model
            ->where('assignable_type', $assignableType)
            ->where('assignable_id', $assignableId)
            ->delete();
    }

    /**
     * Get count of assignments by territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getAssignmentCount(int $territoryId): int
    {
        return $this->model->where('territory_id', $territoryId)->count();
    }

    /**
     * Get count of assignments by territory and assignable type.
     *
     * @param  int  $territoryId
     * @param  string  $assignableType
     * @return int
     */
    public function getAssignmentCountByType(int $territoryId, string $assignableType): int
    {
        return $this->model
            ->where('territory_id', $territoryId)
            ->where('assignable_type', $assignableType)
            ->count();
    }

    /**
     * Get count of manual assignments by territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getManualAssignmentCount(int $territoryId): int
    {
        return $this->model
            ->where('territory_id', $territoryId)
            ->manual()
            ->count();
    }

    /**
     * Get count of automatic assignments by territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getAutomaticAssignmentCount(int $territoryId): int
    {
        return $this->model
            ->where('territory_id', $territoryId)
            ->automatic()
            ->count();
    }

    /**
     * Get assignments grouped by territory.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentsGroupedByTerritory(): Collection
    {
        return $this->model
            ->selectRaw('territory_id, count(*) as count')
            ->groupBy('territory_id')
            ->get();
    }

    /**
     * Get assignments grouped by assignable type.
     *
     * @param  int  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentsGroupedByType(int $territoryId): Collection
    {
        return $this->model
            ->selectRaw('assignable_type, count(*) as count')
            ->where('territory_id', $territoryId)
            ->groupBy('assignable_type')
            ->get();
    }

    /**
     * Get recent assignments for a territory.
     *
     * @param  int  $territoryId
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRecentAssignments(int $territoryId, int $limit = 10): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId, $limit) {
            return $query->where('territory_id', $territoryId)
                ->orderBy('assigned_at', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Check if an assignable entity is assigned to a territory.
     *
     * @param  string  $assignableType
     * @param  int  $assignableId
     * @param  int  $territoryId
     * @return bool
     */
    public function isAssignedToTerritory(string $assignableType, int $assignableId, int $territoryId): bool
    {
        $currentAssignment = $this->getCurrentAssignment($assignableType, $assignableId);

        return $currentAssignment && $currentAssignment->territory_id === $territoryId;
    }

    /**
     * Get assignments within a date range.
     *
     * @param  int  $territoryId
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getAssignmentsByDateRange(int $territoryId, string $startDate, string $endDate): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId, $startDate, $endDate) {
            return $query->where('territory_id', $territoryId)
                ->whereBetween('assigned_at', [$startDate, $endDate]);
        })->all();
    }
}
