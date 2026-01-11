<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DataRetentionPolicy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model_type',
        'retention_period_days',
        'delete_after_days',
        'conditions',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'retention_period_days' => 'integer',
        'delete_after_days' => 'integer',
        'conditions' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scopes
     */

    /**
     * Scope to get active policies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get inactive policies.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to filter by model type.
     */
    public function scopeByModelType($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope to filter by multiple model types.
     */
    public function scopeByModelTypes($query, array $modelTypes)
    {
        return $query->whereIn('model_type', $modelTypes);
    }

    /**
     * Scope to get policies with retention period less than specified days.
     */
    public function scopeRetentionLessThan($query, int $days)
    {
        return $query->where('retention_period_days', '<', $days);
    }

    /**
     * Scope to get policies with retention period greater than specified days.
     */
    public function scopeRetentionGreaterThan($query, int $days)
    {
        return $query->where('retention_period_days', '>', $days);
    }

    /**
     * Scope to get policies ordered by retention period.
     */
    public function scopeOrderByRetention($query, string $direction = 'asc')
    {
        return $query->orderBy('retention_period_days', $direction);
    }

    /**
     * Scope to get policies ordered by deletion period.
     */
    public function scopeOrderByDeletion($query, string $direction = 'asc')
    {
        return $query->orderBy('delete_after_days', $direction);
    }

    /**
     * Query methods
     */

    /**
     * Get all active policies for a specific model type.
     */
    public static function getPoliciesForModel(string $modelType): Collection
    {
        return static::where('model_type', $modelType)
            ->where('is_active', true)
            ->orderBy('retention_period_days', 'asc')
            ->get();
    }

    /**
     * Get the first active policy for a specific model type.
     */
    public static function getActivePolicyForModel(string $modelType): ?self
    {
        return static::where('model_type', $modelType)
            ->where('is_active', true)
            ->orderBy('retention_period_days', 'asc')
            ->first();
    }

    /**
     * Get all active policies.
     */
    public static function getAllActivePolicies(): Collection
    {
        return static::where('is_active', true)
            ->orderBy('model_type')
            ->orderBy('retention_period_days', 'asc')
            ->get();
    }

    /**
     * Get policies grouped by model type.
     */
    public static function getPoliciesByModelType(): Collection
    {
        return static::where('is_active', true)
            ->get()
            ->groupBy('model_type');
    }

    /**
     * Helper methods
     */

    /**
     * Check if this policy applies to a given record.
     */
    public function appliesTo(Model $record): bool
    {
        // Check if the policy is active
        if (!$this->is_active) {
            return false;
        }

        // Check if the model type matches
        if (get_class($record) !== $this->model_type) {
            return false;
        }

        // If there are no conditions, the policy applies to all records of this type
        if (empty($this->conditions)) {
            return true;
        }

        // Evaluate conditions
        return $this->evaluateConditions($record, $this->conditions);
    }

    /**
     * Check if a record has expired according to this policy's retention period.
     */
    public function isExpired(Model $record): bool
    {
        if (!$this->appliesTo($record)) {
            return false;
        }

        $recordDate = $this->getRecordDate($record);

        if (!$recordDate) {
            return false;
        }

        $retentionDate = now()->subDays($this->retention_period_days);

        return $recordDate->lt($retentionDate);
    }

    /**
     * Check if a record should be deleted according to this policy's deletion period.
     */
    public function shouldBeDeleted(Model $record): bool
    {
        if (!$this->appliesTo($record)) {
            return false;
        }

        $recordDate = $this->getRecordDate($record);

        if (!$recordDate) {
            return false;
        }

        $deletionDate = now()->subDays($this->delete_after_days);

        return $recordDate->lt($deletionDate);
    }

    /**
     * Get expired records for this policy.
     */
    public function getExpiredRecords(): Collection
    {
        if (!class_exists($this->model_type)) {
            return collect();
        }

        $model = app($this->model_type);
        $query = $model->newQuery();

        // Apply conditions if they exist
        if (!empty($this->conditions)) {
            $query = $this->applyConditionsToQuery($query, $this->conditions);
        }

        // Filter by retention period
        $retentionDate = now()->subDays($this->retention_period_days);
        $dateField = $this->getDateField($model);

        $query->where($dateField, '<', $retentionDate);

        return $query->get();
    }

    /**
     * Get records that should be deleted according to this policy.
     */
    public function getDeletableRecords(): Collection
    {
        if (!class_exists($this->model_type)) {
            return collect();
        }

        $model = app($this->model_type);
        $query = $model->newQuery();

        // Apply conditions if they exist
        if (!empty($this->conditions)) {
            $query = $this->applyConditionsToQuery($query, $this->conditions);
        }

        // Filter by deletion period
        $deletionDate = now()->subDays($this->delete_after_days);
        $dateField = $this->getDateField($model);

        $query->where($dateField, '<', $deletionDate);

        return $query->get();
    }

    /**
     * Activate this policy.
     */
    public function activate(): bool
    {
        if ($this->is_active) {
            return false;
        }

        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate this policy.
     */
    public function deactivate(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $this->is_active = false;
        return $this->save();
    }

    /**
     * Get the number of days until a record expires.
     */
    public function getDaysUntilExpiration(Model $record): ?int
    {
        if (!$this->appliesTo($record)) {
            return null;
        }

        $recordDate = $this->getRecordDate($record);

        if (!$recordDate) {
            return null;
        }

        $expirationDate = $recordDate->addDays($this->retention_period_days);
        $daysRemaining = now()->diffInDays($expirationDate, false);

        return (int) $daysRemaining;
    }

    /**
     * Get the number of days until a record should be deleted.
     */
    public function getDaysUntilDeletion(Model $record): ?int
    {
        if (!$this->appliesTo($record)) {
            return null;
        }

        $recordDate = $this->getRecordDate($record);

        if (!$recordDate) {
            return null;
        }

        $deletionDate = $recordDate->addDays($this->delete_after_days);
        $daysRemaining = now()->diffInDays($deletionDate, false);

        return (int) $daysRemaining;
    }

    /**
     * Get a human-readable description of this policy.
     */
    public function getDescription(): string
    {
        $modelName = class_basename($this->model_type);
        $status = $this->is_active ? 'Active' : 'Inactive';

        return "{$status} retention policy for {$modelName}: keep for {$this->retention_period_days} days, delete after {$this->delete_after_days} days";
    }

    /**
     * Get statistics for this policy.
     */
    public function getStatistics(): array
    {
        return [
            'total_expired' => $this->getExpiredRecords()->count(),
            'total_deletable' => $this->getDeletableRecords()->count(),
            'model_type' => $this->model_type,
            'retention_period_days' => $this->retention_period_days,
            'delete_after_days' => $this->delete_after_days,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Protected helper methods
     */

    /**
     * Evaluate conditions against a record.
     */
    protected function evaluateConditions(Model $record, array $conditions): bool
    {
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                // Handle array conditions (e.g., ['in' => [1, 2, 3]])
                if (isset($value['in']) && is_array($value['in'])) {
                    if (!in_array($record->$field, $value['in'])) {
                        return false;
                    }
                } elseif (isset($value['not_in']) && is_array($value['not_in'])) {
                    if (in_array($record->$field, $value['not_in'])) {
                        return false;
                    }
                } elseif (isset($value['gt'])) {
                    if (!($record->$field > $value['gt'])) {
                        return false;
                    }
                } elseif (isset($value['lt'])) {
                    if (!($record->$field < $value['lt'])) {
                        return false;
                    }
                } elseif (isset($value['gte'])) {
                    if (!($record->$field >= $value['gte'])) {
                        return false;
                    }
                } elseif (isset($value['lte'])) {
                    if (!($record->$field <= $value['lte'])) {
                        return false;
                    }
                }
            } else {
                // Simple equality check
                if ($record->$field !== $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Apply conditions to a query builder.
     */
    protected function applyConditionsToQuery($query, array $conditions)
    {
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                // Handle array conditions
                if (isset($value['in']) && is_array($value['in'])) {
                    $query->whereIn($field, $value['in']);
                } elseif (isset($value['not_in']) && is_array($value['not_in'])) {
                    $query->whereNotIn($field, $value['not_in']);
                } elseif (isset($value['gt'])) {
                    $query->where($field, '>', $value['gt']);
                } elseif (isset($value['lt'])) {
                    $query->where($field, '<', $value['lt']);
                } elseif (isset($value['gte'])) {
                    $query->where($field, '>=', $value['gte']);
                } elseif (isset($value['lte'])) {
                    $query->where($field, '<=', $value['lte']);
                }
            } else {
                // Simple equality condition
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * Get the date field to use for retention checks.
     */
    protected function getDateField(Model $model): string
    {
        // Check if the model has a deleted_at column (soft deletes)
        if (method_exists($model, 'getDeletedAtColumn')) {
            return $model->getDeletedAtColumn();
        }

        // Check if the model has an updated_at column
        if ($model->usesTimestamps() && $model->getUpdatedAtColumn()) {
            return $model->getUpdatedAtColumn();
        }

        // Default to created_at
        return $model->getCreatedAtColumn() ?? 'created_at';
    }

    /**
     * Get the date value from a record for retention checks.
     */
    protected function getRecordDate(Model $record)
    {
        $dateField = $this->getDateField($record);

        return $record->$dateField;
    }
}
