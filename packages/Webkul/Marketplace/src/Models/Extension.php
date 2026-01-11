<?php

namespace Webkul\Marketplace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Marketplace\Contracts\Extension as ExtensionContract;
use Webkul\User\Models\UserProxy;

class Extension extends Model implements ExtensionContract
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
        'long_description',
        'type',
        'category_id',
        'price',
        'status',
        'downloads_count',
        'average_rating',
        'featured',
        'logo',
        'screenshots',
        'documentation_url',
        'demo_url',
        'repository_url',
        'support_email',
        'tags',
        'requirements',
        'author_id',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'screenshots'   => 'array',
        'tags'          => 'array',
        'requirements'  => 'array',
        'featured'      => 'boolean',
        'price'         => 'decimal:2',
        'average_rating' => 'decimal:2',
    ];

    /**
     * Get the author that owns the extension.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'author_id');
    }

    /**
     * Get the category that the extension belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExtensionCategoryProxy::modelClass(), 'category_id');
    }

    /**
     * Get the versions for the extension.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ExtensionVersionProxy::modelClass());
    }

    /**
     * Get the reviews for the extension.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ExtensionReviewProxy::modelClass());
    }

    /**
     * Get the installations for the extension.
     */
    public function installations(): HasMany
    {
        return $this->hasMany(ExtensionInstallationProxy::modelClass());
    }

    /**
     * Get the transactions for the extension.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(ExtensionTransactionProxy::modelClass());
    }

    /**
     * Get the submissions for the extension.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ExtensionSubmissionProxy::modelClass());
    }

    /**
     * Scope a query to only include approved extensions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include featured extensions.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if extension is free.
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Check if extension is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Increment downloads count.
     */
    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
    }

    /**
     * Update average rating.
     */
    public function updateAverageRating(): void
    {
        $this->average_rating = $this->reviews()->avg('rating') ?? 0;
        $this->save();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ExtensionFactory::new();
    }
}
