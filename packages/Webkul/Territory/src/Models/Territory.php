<?php

namespace Webkul\Territory\Models;

use Database\Factories\TerritoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Territory\Contracts\Territory as TerritoryContract;
use Webkul\User\Models\UserProxy;
use App\Models\User;

class Territory extends Model implements TerritoryContract
{
    use HasFactory, SoftDeletes;

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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a territory is being deleted (soft or hard)
        static::deleting(function ($territory) {
            // Check if this is a force delete (hard delete)
            if ($territory->isForceDeleting()) {
                // Hard delete - let database handle cascades
                return;
            }

            // Soft delete - nullify parent_id in all children
            // This prevents orphaned child territories
            $territory->children()->update(['parent_id' => null]);
        });
    }

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
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The users that belong to the territory.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'territory_users')
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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TerritoryFactory
    {
        return TerritoryFactory::new();
    }
}
