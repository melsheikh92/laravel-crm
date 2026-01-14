<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DocCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'icon',
        'sort_order',
        'is_active',
        'visibility',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Relationships
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DocCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(DocArticle::class, 'category_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeInternal($query)
    {
        return $query->where('visibility', 'internal');
    }

    public function scopeCustomerPortal($query)
    {
        return $query->whereIn('visibility', ['public', 'customer_portal']);
    }
}
