<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DocSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'article_id',
        'parent_id',
        'title',
        'slug',
        'content',
        'level',
        'sort_order',
    ];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($section) {
            if (empty($section->slug)) {
                $section->slug = Str::slug($section->title);
            }
        });
    }

    /**
     * Relationships
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(DocArticle::class, 'article_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocSection::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DocSection::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Scopes
     */
    public function scopeRootLevel($query)
    {
        return $query->where('level', 1);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Helper methods
     */
    public function isRoot(): bool
    {
        return $this->level === 1 || empty($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    public function getPathAttribute(): string
    {
        $path = [$this->slug];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->slug);
            $parent = $parent->parent;
        }

        return implode('/', $path);
    }

    public function getAnchorIdAttribute(): string
    {
        return $this->slug;
    }

    /**
     * Get all ancestors in order
     */
    public function getAncestorsAttribute()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }
}
