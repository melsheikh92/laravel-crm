<?php

namespace Webkul\Marketplace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Marketplace\Contracts\ExtensionCategory as ExtensionCategoryContract;

class ExtensionCategory extends Model implements ExtensionCategoryContract
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'parent_id',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExtensionCategoryProxy::modelClass(), 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ExtensionCategoryProxy::modelClass(), 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all extensions in this category.
     */
    public function extensions(): HasMany
    {
        return $this->hasMany(ExtensionProxy::modelClass(), 'category_id');
    }

    /**
     * Get all descendants (children, grandchildren, etc.)
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $category = $this->parent;

        while ($category) {
            $ancestors->push($category);
            $category = $category->parent;
        }

        return $ancestors;
    }

    /**
     * Scope a query to only include root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Scope a query to get categories with their children.
     */
    public function scopeWithChildren($query)
    {
        return $query->with('children');
    }

    /**
     * Scope a query to get categories ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if category is a root category.
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Get the full path of the category (e.g., "Parent / Child / Grandchild").
     */
    public function getFullPath(string $separator = ' / '): string
    {
        $path = [$this->name];
        $category = $this->parent;

        while ($category) {
            array_unshift($path, $category->name);
            $category = $category->parent;
        }

        return implode($separator, $path);
    }

    /**
     * Get the depth level of the category (root = 0).
     */
    public function getDepth(): int
    {
        $depth = 0;
        $category = $this->parent;

        while ($category) {
            $depth++;
            $category = $category->parent;
        }

        return $depth;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ExtensionCategoryFactory::new();
    }
}
