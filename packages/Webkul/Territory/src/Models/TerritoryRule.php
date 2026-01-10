<?php

namespace Webkul\Territory\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Territory\Contracts\TerritoryRule as TerritoryRuleContract;

class TerritoryRule extends Model implements TerritoryRuleContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'territory_id',
        'rule_type',
        'field_name',
        'operator',
        'value',
        'priority',
        'is_active',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'value'     => 'array',
        'is_active' => 'boolean',
        'priority'  => 'integer',
    ];

    /**
     * Get the territory that owns the rule.
     */
    public function territory(): BelongsTo
    {
        return $this->belongsTo(TerritoryProxy::modelClass(), 'territory_id');
    }

    /**
     * Scope a query to only include active rules.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter rules by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('rule_type', $type);
    }

    /**
     * Scope a query to order rules by priority.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPriority(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('priority', $direction);
    }

    /**
     * Evaluate the rule against a given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function evaluate(Model $model): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $fieldValue = $this->getFieldValue($model, $this->field_name);

        return $this->compareValues($fieldValue, $this->operator, $this->value);
    }

    /**
     * Get the field value from the model, supporting nested relationships.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $fieldName
     * @return mixed
     */
    protected function getFieldValue(Model $model, string $fieldName): mixed
    {
        if (str_contains($fieldName, '.')) {
            $parts = explode('.', $fieldName);
            $value = $model;

            foreach ($parts as $part) {
                $value = $value->{$part} ?? null;

                if ($value === null) {
                    break;
                }
            }

            return $value;
        }

        return $model->{$fieldName} ?? null;
    }

    /**
     * Compare values based on the operator.
     *
     * @param  mixed  $fieldValue
     * @param  string  $operator
     * @param  mixed  $ruleValue
     * @return bool
     */
    protected function compareValues(mixed $fieldValue, string $operator, mixed $ruleValue): bool
    {
        // Handle array rule values for string operators by using the first element
        if (is_array($ruleValue) && in_array($operator, ['=', '==', '!=', 'contains', 'not_contains', 'starts_with', 'ends_with'])) {
            $ruleValue = $ruleValue[0] ?? null;
        }

        return match ($operator) {
            '=' => $fieldValue == $ruleValue,
            '==' => $fieldValue == $ruleValue,
            '!=' => $fieldValue != $ruleValue,
            '>' => $fieldValue > $ruleValue,
            '>=' => $fieldValue >= $ruleValue,
            '<' => $fieldValue < $ruleValue,
            '<=' => $fieldValue <= $ruleValue,
            'in' => is_array($ruleValue) && in_array($fieldValue, $ruleValue),
            'not_in' => is_array($ruleValue) && ! in_array($fieldValue, $ruleValue),
            'contains' => is_string($fieldValue) && is_string($ruleValue) && str_contains($fieldValue, $ruleValue),
            'not_contains' => is_string($fieldValue) && is_string($ruleValue) && ! str_contains($fieldValue, $ruleValue),
            'starts_with' => is_string($fieldValue) && is_string($ruleValue) && str_starts_with($fieldValue, $ruleValue),
            'ends_with' => is_string($fieldValue) && is_string($ruleValue) && str_ends_with($fieldValue, $ruleValue),
            'is_null' => $fieldValue === null,
            'is_not_null' => $fieldValue !== null,
            'between' => is_array($ruleValue) && count($ruleValue) === 2 && $fieldValue >= $ruleValue[0] && $fieldValue <= $ruleValue[1],
            default => false,
        };
    }

    /**
     * Check if the rule is geographic type.
     *
     * @return bool
     */
    public function isGeographic(): bool
    {
        return $this->rule_type === 'geographic';
    }

    /**
     * Check if the rule is industry type.
     *
     * @return bool
     */
    public function isIndustry(): bool
    {
        return $this->rule_type === 'industry';
    }

    /**
     * Check if the rule is account size type.
     *
     * @return bool
     */
    public function isAccountSize(): bool
    {
        return $this->rule_type === 'account_size';
    }

    /**
     * Check if the rule is custom type.
     *
     * @return bool
     */
    public function isCustom(): bool
    {
        return $this->rule_type === 'custom';
    }
}
