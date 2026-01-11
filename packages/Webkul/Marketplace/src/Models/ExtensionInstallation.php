<?php

namespace Webkul\Marketplace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Marketplace\Contracts\ExtensionInstallation as ExtensionInstallationContract;
use Webkul\User\Models\UserProxy;

class ExtensionInstallation extends Model implements ExtensionInstallationContract
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'installed_at',
        'updated_at_version',
        'status',
        'auto_update_enabled',
        'installation_notes',
        'settings',
        'user_id',
        'extension_id',
        'version_id',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'installed_at'         => 'datetime',
        'updated_at_version'   => 'datetime',
        'auto_update_enabled'  => 'boolean',
        'settings'             => 'array',
    ];

    /**
     * Get the user who installed the extension.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Get the extension that was installed.
     */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(ExtensionProxy::modelClass());
    }

    /**
     * Get the version that is installed.
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(ExtensionVersionProxy::modelClass(), 'version_id');
    }

    /**
     * Scope a query to only include active installations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive installations.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include failed installations.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include installations with auto-update enabled.
     */
    public function scopeAutoUpdateEnabled($query)
    {
        return $query->where('auto_update_enabled', true);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by extension.
     */
    public function scopeForExtension($query, $extensionId)
    {
        return $query->where('extension_id', $extensionId);
    }

    /**
     * Check if installation is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if installation is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Check if installation has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if auto-update is enabled.
     */
    public function hasAutoUpdateEnabled(): bool
    {
        return $this->auto_update_enabled;
    }

    /**
     * Activate the installation.
     */
    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Deactivate the installation.
     */
    public function deactivate(): void
    {
        $this->status = 'inactive';
        $this->save();
    }

    /**
     * Mark installation as failed.
     */
    public function markAsFailed(?string $notes = null): void
    {
        $this->status = 'failed';
        if ($notes) {
            $this->installation_notes = $notes;
        }
        $this->save();
    }

    /**
     * Mark installation as updating.
     */
    public function markAsUpdating(): void
    {
        $this->status = 'updating';
        $this->save();
    }

    /**
     * Mark installation as uninstalling.
     */
    public function markAsUninstalling(): void
    {
        $this->status = 'uninstalling';
        $this->save();
    }

    /**
     * Update to a new version.
     */
    public function updateToVersion(int $versionId): void
    {
        $this->version_id = $versionId;
        $this->updated_at_version = now();
        $this->status = 'active';
        $this->save();
    }

    /**
     * Enable auto-update.
     */
    public function enableAutoUpdate(): void
    {
        $this->auto_update_enabled = true;
        $this->save();
    }

    /**
     * Disable auto-update.
     */
    public function disableAutoUpdate(): void
    {
        $this->auto_update_enabled = false;
        $this->save();
    }

    /**
     * Check if this installation needs an update.
     */
    public function needsUpdate(): bool
    {
        $latestVersion = $this->extension
            ->versions()
            ->approved()
            ->latest()
            ->first();

        if (!$latestVersion) {
            return false;
        }

        return $latestVersion->id !== $this->version_id;
    }

    /**
     * Get the latest available version for this extension.
     */
    public function getLatestAvailableVersion()
    {
        return $this->extension
            ->versions()
            ->approved()
            ->latest()
            ->first();
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ExtensionInstallationFactory::new();
    }
}
