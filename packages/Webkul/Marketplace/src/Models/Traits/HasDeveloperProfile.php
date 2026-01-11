<?php

namespace Webkul\Marketplace\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Marketplace\Models\ExtensionProxy;

/**
 * Trait for User model to add developer-related functionality.
 */
trait HasDeveloperProfile
{
    /**
     * Get all extensions authored by this developer.
     */
    public function developedExtensions(): HasMany
    {
        return $this->hasMany(ExtensionProxy::modelClass(), 'author_id');
    }

    /**
     * Check if user is a developer.
     */
    public function isDeveloper(): bool
    {
        return $this->is_developer && $this->developer_status === 'approved';
    }

    /**
     * Check if user has pending developer application.
     */
    public function hasPendingDeveloperApplication(): bool
    {
        return $this->is_developer && $this->developer_status === 'pending';
    }

    /**
     * Check if user's developer status is rejected.
     */
    public function isDeveloperRejected(): bool
    {
        return $this->is_developer && $this->developer_status === 'rejected';
    }

    /**
     * Check if user's developer account is suspended.
     */
    public function isDeveloperSuspended(): bool
    {
        return $this->is_developer && $this->developer_status === 'suspended';
    }

    /**
     * Register user as a developer.
     *
     * @param  array  $developerData
     * @return bool
     */
    public function registerAsDeveloper(array $developerData): bool
    {
        $this->is_developer = true;
        $this->developer_status = 'pending';
        $this->developer_bio = $developerData['bio'] ?? null;
        $this->developer_company = $developerData['company'] ?? null;
        $this->developer_website = $developerData['website'] ?? null;
        $this->developer_support_email = $developerData['support_email'] ?? $this->email;
        $this->developer_social_links = $developerData['social_links'] ?? null;
        $this->developer_registered_at = now();

        return $this->save();
    }

    /**
     * Approve developer application.
     *
     * @return bool
     */
    public function approveDeveloper(): bool
    {
        $this->developer_status = 'approved';
        $this->developer_approved_at = now();

        return $this->save();
    }

    /**
     * Reject developer application.
     *
     * @return bool
     */
    public function rejectDeveloper(): bool
    {
        $this->developer_status = 'rejected';

        return $this->save();
    }

    /**
     * Suspend developer account.
     *
     * @return bool
     */
    public function suspendDeveloper(): bool
    {
        $this->developer_status = 'suspended';

        return $this->save();
    }

    /**
     * Update developer profile.
     *
     * @param  array  $developerData
     * @return bool
     */
    public function updateDeveloperProfile(array $developerData): bool
    {
        if (isset($developerData['bio'])) {
            $this->developer_bio = $developerData['bio'];
        }

        if (isset($developerData['company'])) {
            $this->developer_company = $developerData['company'];
        }

        if (isset($developerData['website'])) {
            $this->developer_website = $developerData['website'];
        }

        if (isset($developerData['support_email'])) {
            $this->developer_support_email = $developerData['support_email'];
        }

        if (isset($developerData['social_links'])) {
            $this->developer_social_links = $developerData['social_links'];
        }

        return $this->save();
    }

    /**
     * Get developer's total downloads.
     */
    public function getDeveloperDownloadsCount(): int
    {
        return $this->developedExtensions()->sum('downloads_count');
    }

    /**
     * Get developer's average rating.
     */
    public function getDeveloperAverageRating(): float
    {
        $rating = $this->developedExtensions()
            ->where('status', 'approved')
            ->avg('average_rating');

        return round($rating ?? 0, 2);
    }

    /**
     * Get developer's total extensions count.
     */
    public function getDeveloperExtensionsCount(): int
    {
        return $this->developedExtensions()->count();
    }

    /**
     * Get developer's approved extensions count.
     */
    public function getDeveloperApprovedExtensionsCount(): int
    {
        return $this->developedExtensions()
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Scope query to only developers.
     */
    public function scopeDevelopers($query)
    {
        return $query->where('is_developer', true);
    }

    /**
     * Scope query to approved developers.
     */
    public function scopeApprovedDevelopers($query)
    {
        return $query->where('is_developer', true)
            ->where('developer_status', 'approved');
    }

    /**
     * Scope query to pending developer applications.
     */
    public function scopePendingDevelopers($query)
    {
        return $query->where('is_developer', true)
            ->where('developer_status', 'pending');
    }
}
