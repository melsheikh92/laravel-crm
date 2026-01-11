<?php

namespace Webkul\Territory\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Territory\Contracts\Territory;

class TerritoryRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'name',
        'code',
        'type',
        'status',
        'user_id',
        'user.name',
        'parent_id',
        'parent.name',
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
        return Territory::class;
    }

    /**
     * Get all active territories.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveTerritories(): Collection
    {
        return $this->scopeQuery(function ($query) {
            return $query->active();
        })->all();
    }

    /**
     * Get territories by type.
     *
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getTerritoriesByType(string $type): Collection
    {
        return $this->scopeQuery(function ($query) use ($type) {
            return $query->byType($type);
        })->all();
    }

    /**
     * Get territories by type (alias for getTerritoriesByType).
     *
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getByType(string $type): Collection
    {
        return $this->getTerritoriesByType($type);
    }

    /**
     * Get root territories (territories without parent).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRootTerritories(): Collection
    {
        return $this->scopeQuery(function ($query) {
            return $query->whereNull('parent_id');
        })->all();
    }

    /**
     * Get territory hierarchy (all territories with their children).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getHierarchy(): Collection
    {
        return $this->with(['children', 'parent', 'owner'])
            ->scopeQuery(function ($query) {
                return $query->whereNull('parent_id');
            })
            ->all();
    }

    /**
     * Get territory with all its relationships.
     *
     * @param  int  $id
     * @return \Webkul\Territory\Contracts\Territory
     */
    public function findWithRelations(int $id)
    {
        return $this->with([
            'parent',
            'children',
            'owner',
            'users',
            'assignments',
            'rules',
        ])->find($id);
    }

    /**
     * Get territories by user.
     *
     * @param  int  $userId
     * @return \Illuminate\Support\Collection
     */
    public function getTerritoriesByUser(int $userId): Collection
    {
        return $this->scopeQuery(function ($query) use ($userId) {
            return $query->where('user_id', $userId)
                ->orWhereHas('users', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                });
        })->all();
    }

    /**
     * Get child territories for a parent territory.
     *
     * @param  int  $parentId
     * @return \Illuminate\Support\Collection
     */
    public function getChildTerritories(int $parentId): Collection
    {
        return $this->scopeQuery(function ($query) use ($parentId) {
            return $query->where('parent_id', $parentId);
        })->all();
    }

    /**
     * Get all descendants of a territory (recursive).
     *
     * @param  int  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function getDescendants(int $territoryId): Collection
    {
        $territory = $this->find($territoryId);

        if (! $territory) {
            return collect([]);
        }

        $descendants = collect([]);

        $this->collectDescendants($territory, $descendants);

        return $descendants;
    }

    /**
     * Recursively collect all descendants.
     *
     * @param  \Webkul\Territory\Contracts\Territory  $territory
     * @param  \Illuminate\Support\Collection  $descendants
     * @return void
     */
    protected function collectDescendants($territory, Collection &$descendants): void
    {
        $children = $this->getChildTerritories($territory->id);

        foreach ($children as $child) {
            $descendants->push($child);
            $this->collectDescendants($child, $descendants);
        }
    }

    /**
     * Check if territory code is unique.
     *
     * @param  string  $code
     * @param  int|null  $excludeId
     * @return bool
     */
    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $query = $this->model->where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() === 0;
    }

    /**
     * Create a new territory.
     *
     * @param  array  $data
     * @return \Webkul\Territory\Contracts\Territory
     */
    public function create(array $data)
    {
        // Set default status if not provided
        if (! isset($data['status'])) {
            $data['status'] = 'active';
        }

        // Ensure boundaries is properly formatted
        if (isset($data['boundaries']) && is_string($data['boundaries'])) {
            $data['boundaries'] = json_decode($data['boundaries'], true);
        }

        // Set parent_id to null if empty
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        return parent::create($data);
    }

    /**
     * Update a territory.
     *
     * @param  array  $data
     * @param  int  $id
     * @return \Webkul\Territory\Contracts\Territory
     */
    public function update(array $data, $id)
    {
        // Ensure boundaries is properly formatted
        if (isset($data['boundaries']) && is_string($data['boundaries'])) {
            $data['boundaries'] = json_decode($data['boundaries'], true);
        }

        // Set parent_id to null if empty
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        return parent::update($data, $id);
    }

    /**
     * Delete a territory.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id)
    {
        $territory = $this->find($id);

        if (! $territory) {
            return false;
        }

        // Update children to have no parent
        $this->model->where('parent_id', $id)->update(['parent_id' => null]);

        return parent::delete($id);
    }

    /**
     * Get territories with assignment count.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWithAssignmentCount(): Collection
    {
        return $this->scopeQuery(function ($query) {
            return $query->withCount('assignments');
        })->all();
    }

    /**
     * Get territories with rule count.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWithRuleCount(): Collection
    {
        return $this->scopeQuery(function ($query) {
            return $query->withCount('rules');
        })->all();
    }
}
