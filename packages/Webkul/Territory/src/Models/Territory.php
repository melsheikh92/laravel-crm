<?php

namespace Webkul\Territory\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Territory\Contracts\Territory as TerritoryContract;
use Webkul\User\Models\UserProxy;

class Territory extends Model implements TerritoryContract
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'parent_id',
        'status',
        'boundaries',
        'user_id',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'boundaries' => 'array',
    ];

    /**
     * Get the parent territory.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TerritoryProxy::modelClass(), 'parent_id');
    }

    /**
     * Get the child territories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(TerritoryProxy::modelClass(), 'parent_id');
    }

    /**
     * Get the owner (user) of the territory.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }

    /**
     * The users that belong to the territory.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserProxy::modelClass(), 'territory_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the territory assignments.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TerritoryAssignmentProxy::modelClass());
    }

    /**
     * Get the territory rules.
     */
    public function rules(): HasMany
    {
        return $this->hasMany(TerritoryRuleProxy::modelClass());
    }

    /**
     * Scope a query to only include active territories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter territories by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Check if the territory is geographic type.
     *
     * @return bool
     */
    public function isGeographic(): bool
    {
        return $this->type === 'geographic';
    }

    /**
     * Check if the territory is account-based type.
     *
     * @return bool
     */
    public function isAccountBased(): bool
    {
        return $this->type === 'account-based';
    }

    /**
     * Check if the territory has children.
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }
}
