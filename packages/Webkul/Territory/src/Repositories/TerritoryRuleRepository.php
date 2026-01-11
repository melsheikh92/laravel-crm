<?php

namespace Webkul\Territory\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Territory\Contracts\TerritoryRule;

class TerritoryRuleRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'rule_type',
        'field_name',
        'operator',
        'priority',
        'is_active',
        'territory_id',
        'territory.name',
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
        return TerritoryRule::class;
    }

    /**
     * Get all active rules.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveRules(): Collection
    {
        return $this->scopeQuery(function ($query) {
            return $query->active();
        })->all();
    }

    /**
     * Get rules by territory ID.
     *
     * @param  int  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function getRulesByTerritory(int $territoryId): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId) {
            return $query->where('territory_id', $territoryId);
        })->all();
    }

    /**
     * Get active rules by territory ID.
     *
     * @param  int  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function getActiveRulesByTerritory(int $territoryId): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId) {
            return $query->where('territory_id', $territoryId)->active();
        })->all();
    }

    /**
     * Get rules by territory ID ordered by priority (descending by default).
     *
     * @param  int  $territoryId
     * @param  string  $direction
     * @return \Illuminate\Support\Collection
     */
    public function getRulesByPriority(int $territoryId, string $direction = 'desc'): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId, $direction) {
            return $query->where('territory_id', $territoryId)
                ->byPriority($direction);
        })->all();
    }

    /**
     * Get active rules by territory ID ordered by priority (descending by default).
     *
     * @param  int  $territoryId
     * @param  string  $direction
     * @return \Illuminate\Support\Collection
     */
    public function getActiveRulesByPriority(int $territoryId, string $direction = 'desc'): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId, $direction) {
            return $query->where('territory_id', $territoryId)
                ->active()
                ->byPriority($direction);
        })->all();
    }

    /**
     * Get all rules ordered by priority (descending by default).
     *
     * @param  string  $direction
     * @return \Illuminate\Support\Collection
     */
    public function getAllByPriority(string $direction = 'desc'): Collection
    {
        return $this->scopeQuery(function ($query) use ($direction) {
            return $query->byPriority($direction);
        })->all();
    }

    /**
     * Get active rules ordered by priority (descending by default).
     *
     * @param  string  $direction
     * @return \Illuminate\Support\Collection
     */
    public function getActiveByPriority(string $direction = 'desc'): Collection
    {
        return $this->scopeQuery(function ($query) use ($direction) {
            return $query->active()->byPriority($direction);
        })->all();
    }

    /**
     * Get rules by type.
     *
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getRulesByType(string $type): Collection
    {
        return $this->scopeQuery(function ($query) use ($type) {
            return $query->byType($type);
        })->all();
    }

    /**
     * Get active rules by type.
     *
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getActiveRulesByType(string $type): Collection
    {
        return $this->scopeQuery(function ($query) use ($type) {
            return $query->active()->byType($type);
        })->all();
    }

    /**
     * Get rules by type for a specific territory.
     *
     * @param  int  $territoryId
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getRulesByTerritoryAndType(int $territoryId, string $type): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId, $type) {
            return $query->where('territory_id', $territoryId)->byType($type);
        })->all();
    }

    /**
     * Get active rules by type for a specific territory.
     *
     * @param  int  $territoryId
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getActiveRulesByTerritoryAndType(int $territoryId, string $type): Collection
    {
        return $this->scopeQuery(function ($query) use ($territoryId, $type) {
            return $query->where('territory_id', $territoryId)
                ->active()
                ->byType($type);
        })->all();
    }

    /**
     * Get rule with territory relationship.
     *
     * @param  int  $id
     * @return \Webkul\Territory\Contracts\TerritoryRule
     */
    public function findWithTerritory(int $id)
    {
        return $this->with(['territory'])->find($id);
    }

    /**
     * Create a new territory rule.
     *
     * @param  array  $data
     * @return \Webkul\Territory\Contracts\TerritoryRule
     */
    public function create(array $data)
    {
        // Set default priority if not provided
        if (! isset($data['priority'])) {
            $data['priority'] = 0;
        }

        // Set default is_active if not provided
        if (! isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        // Ensure value is properly formatted
        if (isset($data['value']) && is_string($data['value'])) {
            $data['value'] = json_decode($data['value'], true);
        }

        return parent::create($data);
    }

    /**
     * Update a territory rule.
     *
     * @param  array  $data
     * @param  int  $id
     * @return \Webkul\Territory\Contracts\TerritoryRule
     */
    public function update(array $data, $id)
    {
        // Ensure value is properly formatted
        if (isset($data['value']) && is_string($data['value'])) {
            $data['value'] = json_decode($data['value'], true);
        }

        return parent::update($data, $id);
    }

    /**
     * Toggle rule active status.
     *
     * @param  int  $id
     * @return \Webkul\Territory\Contracts\TerritoryRule
     */
    public function toggleActiveStatus(int $id)
    {
        $rule = $this->find($id);

        if (! $rule) {
            return null;
        }

        return $this->update([
            'is_active' => ! $rule->is_active,
        ], $id);
    }

    /**
     * Update rule priority.
     *
     * @param  int  $id
     * @param  int  $priority
     * @return \Webkul\Territory\Contracts\TerritoryRule
     */
    public function updatePriority(int $id, int $priority)
    {
        return $this->update([
            'priority' => $priority,
        ], $id);
    }

    /**
     * Get highest priority value for a territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getHighestPriority(int $territoryId): int
    {
        $rule = $this->model
            ->where('territory_id', $territoryId)
            ->orderBy('priority', 'desc')
            ->first();

        return $rule ? $rule->priority : 0;
    }

    /**
     * Get lowest priority value for a territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getLowestPriority(int $territoryId): int
    {
        $rule = $this->model
            ->where('territory_id', $territoryId)
            ->orderBy('priority', 'asc')
            ->first();

        return $rule ? $rule->priority : 0;
    }

    /**
     * Bulk update rule priorities.
     *
     * @param  array  $priorities Array of ['id' => priority] pairs
     * @return bool
     */
    public function bulkUpdatePriorities(array $priorities): bool
    {
        foreach ($priorities as $id => $priority) {
            $this->updatePriority($id, $priority);
        }

        return true;
    }

    /**
     * Delete all rules for a territory.
     *
     * @param  int  $territoryId
     * @return bool
     */
    public function deleteByTerritory(int $territoryId): bool
    {
        return $this->model->where('territory_id', $territoryId)->delete();
    }

    /**
     * Get count of active rules by territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getActiveRuleCount(int $territoryId): int
    {
        return $this->model
            ->where('territory_id', $territoryId)
            ->active()
            ->count();
    }

    /**
     * Get count of rules by type for a territory.
     *
     * @param  int  $territoryId
     * @param  string  $type
     * @return int
     */
    public function getRuleCountByType(int $territoryId, string $type): int
    {
        return $this->model
            ->where('territory_id', $territoryId)
            ->byType($type)
            ->count();
    }
}
