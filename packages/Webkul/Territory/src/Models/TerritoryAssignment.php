<?php

namespace Webkul\Territory\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Webkul\Territory\Contracts\TerritoryAssignment as TerritoryAssignmentContract;
use Webkul\User\Models\UserProxy;
use App\Models\User;

class TerritoryAssignment extends Model implements TerritoryAssignmentContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'territory_id',
        'assignable_type',
        'assignable_id',
        'assigned_by',
        'assignment_type',
        'assigned_at',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the territory that owns the assignment.
     */
    public function territory(): BelongsTo
    {
        return $this->belongsTo(TerritoryProxy::modelClass(), 'territory_id');
    }

    /**
     * Get the assignable model (Lead, Organization, or Person).
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who assigned this territory.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope a query to only include manual assignments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeManual(Builder $query): Builder
    {
        return $query->where('assignment_type', 'manual');
    }

    /**
     * Scope a query to only include automatic assignments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAutomatic(Builder $query): Builder
    {
        return $query->where('assignment_type', 'automatic');
    }

    /**
     * Scope a query to filter assignments by territory.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $territoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTerritory(Builder $query, int $territoryId): Builder
    {
        return $query->where('territory_id', $territoryId);
    }

    /**
     * Scope a query to filter assignments by assignable type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAssignableType(Builder $query, string $type): Builder
    {
        return $query->where('assignable_type', $type);
    }

    /**
     * Scope a query to filter assignments by assignment type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('assignment_type', $type);
    }

    /**
     * Check if the assignment is manual.
     *
     * @return bool
     */
    public function isManual(): bool
    {
        return $this->assignment_type === 'manual';
    }

    /**
     * Check if the assignment is automatic.
     *
     * @return bool
     */
    public function isAutomatic(): bool
    {
        return $this->assignment_type === 'automatic';
    }
}
