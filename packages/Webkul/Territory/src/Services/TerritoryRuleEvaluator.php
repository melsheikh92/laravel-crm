<?php

namespace Webkul\Territory\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Repositories\TerritoryRuleRepository;

class TerritoryRuleEvaluator
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected TerritoryRepository $territoryRepository,
        protected TerritoryRuleRepository $ruleRepository
    ) {}

    /**
     * Evaluate a single rule against an entity.
     *
     * @param  \Webkul\Territory\Contracts\TerritoryRule  $rule
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return bool
     */
    public function evaluateRule($rule, Model $entity): bool
    {
        if (! $rule->is_active) {
            return false;
        }

        return $rule->evaluate($entity);
    }

    /**
     * Evaluate all rules against an entity (ALL rules must match).
     *
     * @param  \Illuminate\Support\Collection  $rules
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return bool
     */
    public function evaluateRules(Collection $rules, Model $entity): bool
    {
        if ($rules->isEmpty()) {
            return false;
        }

        foreach ($rules as $rule) {
            if (! $this->evaluateRule($rule, $entity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate rules against an entity with ANY matching strategy (at least one rule must match).
     *
     * @param  \Illuminate\Support\Collection  $rules
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return bool
     */
    public function evaluateRulesWithAnyMatch(Collection $rules, Model $entity): bool
    {
        if ($rules->isEmpty()) {
            return false;
        }

        foreach ($rules as $rule) {
            if ($this->evaluateRule($rule, $entity)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find all territories that match an entity based on their rules.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  string  $matchStrategy  'all' (default) or 'any'
     * @return \Illuminate\Support\Collection
     */
    public function findMatchingTerritories(Model $entity, string $matchStrategy = 'all'): Collection
    {
        $activeTerritories = $this->territoryRepository->getActiveTerritories();
        $matchedTerritories = collect([]);

        foreach ($activeTerritories as $territory) {
            $rules = $this->ruleRepository->getActiveRulesByPriority($territory->id);

            if ($rules->isEmpty()) {
                continue;
            }

            $matches = $matchStrategy === 'any'
                ? $this->evaluateRulesWithAnyMatch($rules, $entity)
                : $this->evaluateRules($rules, $entity);

            if ($matches) {
                $highestPriority = $rules->first()->priority ?? 0;

                $matchedTerritories->push([
                    'territory'      => $territory,
                    'priority'       => $highestPriority,
                    'matching_rules' => $rules,
                ]);
            }
        }

        return $matchedTerritories->sortByDesc('priority')->values();
    }

    /**
     * Find the best matching territory for an entity (highest priority).
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  string  $matchStrategy  'all' (default) or 'any'
     * @return \Webkul\Territory\Contracts\Territory|null
     */
    public function findBestMatchingTerritory(Model $entity, string $matchStrategy = 'all')
    {
        $matchedTerritories = $this->findMatchingTerritories($entity, $matchStrategy);

        if ($matchedTerritories->isEmpty()) {
            return null;
        }

        return $matchedTerritories->first()['territory'];
    }

    /**
     * Evaluate rules for a specific territory against an entity.
     *
     * @param  int  $territoryId
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  string  $matchStrategy  'all' (default) or 'any'
     * @return bool
     */
    public function evaluateTerritoryRules(int $territoryId, Model $entity, string $matchStrategy = 'all'): bool
    {
        $rules = $this->ruleRepository->getActiveRulesByPriority($territoryId);

        if ($rules->isEmpty()) {
            return false;
        }

        return $matchStrategy === 'any'
            ? $this->evaluateRulesWithAnyMatch($rules, $entity)
            : $this->evaluateRules($rules, $entity);
    }

    /**
     * Get matching rules for an entity from a collection of rules.
     *
     * @param  \Illuminate\Support\Collection  $rules
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return \Illuminate\Support\Collection
     */
    public function getMatchingRules(Collection $rules, Model $entity): Collection
    {
        return $rules->filter(function ($rule) use ($entity) {
            return $this->evaluateRule($rule, $entity);
        });
    }

    /**
     * Get non-matching rules for an entity from a collection of rules.
     *
     * @param  \Illuminate\Support\Collection  $rules
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return \Illuminate\Support\Collection
     */
    public function getNonMatchingRules(Collection $rules, Model $entity): Collection
    {
        return $rules->filter(function ($rule) use ($entity) {
            return ! $this->evaluateRule($rule, $entity);
        });
    }

    /**
     * Check if an entity matches a specific territory.
     *
     * @param  int  $territoryId
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  string  $matchStrategy  'all' (default) or 'any'
     * @return bool
     */
    public function doesEntityMatchTerritory(int $territoryId, Model $entity, string $matchStrategy = 'all'): bool
    {
        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory || $territory->status !== 'active') {
            return false;
        }

        return $this->evaluateTerritoryRules($territoryId, $entity, $matchStrategy);
    }

    /**
     * Evaluate rules by type for an entity.
     *
     * @param  string  $ruleType
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  int|null  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function evaluateRulesByType(string $ruleType, Model $entity, ?int $territoryId = null): Collection
    {
        $rules = $territoryId
            ? $this->ruleRepository->getActiveRulesByTerritoryAndType($territoryId, $ruleType)
            : $this->ruleRepository->getActiveRulesByType($ruleType);

        return $this->getMatchingRules($rules, $entity);
    }

    /**
     * Get evaluation details for an entity against a territory's rules.
     *
     * @param  int  $territoryId
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @return array
     */
    public function getEvaluationDetails(int $territoryId, Model $entity): array
    {
        $rules = $this->ruleRepository->getActiveRulesByPriority($territoryId);
        $matchingRules = $this->getMatchingRules($rules, $entity);
        $nonMatchingRules = $this->getNonMatchingRules($rules, $entity);

        return [
            'territory_id'       => $territoryId,
            'total_rules'        => $rules->count(),
            'matching_rules'     => $matchingRules->count(),
            'non_matching_rules' => $nonMatchingRules->count(),
            'matches'            => $rules->count() === $matchingRules->count(),
            'matching_rule_ids'  => $matchingRules->pluck('id')->toArray(),
            'non_matching_rule_ids' => $nonMatchingRules->pluck('id')->toArray(),
        ];
    }

    /**
     * Find territories by type that match an entity.
     *
     * @param  string  $territoryType  'geographic' or 'account-based'
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  string  $matchStrategy  'all' (default) or 'any'
     * @return \Illuminate\Support\Collection
     */
    public function findMatchingTerritoriesByType(
        string $territoryType,
        Model $entity,
        string $matchStrategy = 'all'
    ): Collection {
        $territories = $this->territoryRepository->getByType($territoryType);
        $matchedTerritories = collect([]);

        foreach ($territories as $territory) {
            if ($territory->status !== 'active') {
                continue;
            }

            $rules = $this->ruleRepository->getActiveRulesByPriority($territory->id);

            if ($rules->isEmpty()) {
                continue;
            }

            $matches = $matchStrategy === 'any'
                ? $this->evaluateRulesWithAnyMatch($rules, $entity)
                : $this->evaluateRules($rules, $entity);

            if ($matches) {
                $highestPriority = $rules->first()->priority ?? 0;

                $matchedTerritories->push([
                    'territory'      => $territory,
                    'priority'       => $highestPriority,
                    'matching_rules' => $rules,
                ]);
            }
        }

        return $matchedTerritories->sortByDesc('priority')->values();
    }

    /**
     * Evaluate multiple entities against a territory and return matching entities.
     *
     * @param  int  $territoryId
     * @param  array  $entities
     * @param  string  $matchStrategy  'all' (default) or 'any'
     * @return \Illuminate\Support\Collection
     */
    public function findMatchingEntities(
        int $territoryId,
        array $entities,
        string $matchStrategy = 'all'
    ): Collection {
        $matchingEntities = collect([]);

        foreach ($entities as $entity) {
            if (! $entity instanceof Model) {
                continue;
            }

            if ($this->evaluateTerritoryRules($territoryId, $entity, $matchStrategy)) {
                $matchingEntities->push($entity);
            }
        }

        return $matchingEntities;
    }
}
