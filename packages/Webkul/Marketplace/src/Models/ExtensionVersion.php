<?php

namespace Webkul\Marketplace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Marketplace\Contracts\ExtensionVersion as ExtensionVersionContract;

class ExtensionVersion extends Model implements ExtensionVersionContract
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'version',
        'changelog',
        'file_path',
        'status',
        'release_date',
        'laravel_version',
        'crm_version',
        'php_version',
        'dependencies',
        'downloads_count',
        'file_size',
        'checksum',
        'extension_id',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'dependencies'   => 'array',
        'release_date'   => 'datetime',
        'file_size'      => 'integer',
    ];

    /**
     * Get the extension that owns the version.
     */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(ExtensionProxy::modelClass());
    }

    /**
     * Get the installations for this version.
     */
    public function installations(): HasMany
    {
        return $this->hasMany(ExtensionInstallationProxy::modelClass(), 'version_id');
    }

    /**
     * Get the submissions for this version.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ExtensionSubmissionProxy::modelClass(), 'version_id');
    }

    /**
     * Scope a query to only include approved versions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include released versions.
     */
    public function scopeReleased($query)
    {
        return $query->where('status', 'approved')
            ->whereNotNull('release_date')
            ->where('release_date', '<=', now());
    }

    /**
     * Scope a query to get the latest version.
     */
    public function scopeLatest($query)
    {
        return $query->approved()
            ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, ".", 2), ".", -1) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC');
    }

    /**
     * Check if version is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if version is released.
     */
    public function isReleased(): bool
    {
        return $this->isApproved()
            && $this->release_date !== null
            && $this->release_date <= now();
    }

    /**
     * Check compatibility with given requirements.
     */
    public function isCompatibleWith(?string $laravelVersion = null, ?string $crmVersion = null, ?string $phpVersion = null): bool
    {
        if ($laravelVersion && $this->laravel_version) {
            if (!$this->versionMatches($laravelVersion, $this->laravel_version)) {
                return false;
            }
        }

        if ($crmVersion && $this->crm_version) {
            if (!$this->versionMatches($crmVersion, $this->crm_version)) {
                return false;
            }
        }

        if ($phpVersion && $this->php_version) {
            if (!$this->versionMatches($phpVersion, $this->php_version)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a version matches a constraint.
     */
    protected function versionMatches(string $currentVersion, string $constraint): bool
    {
        // Simple version matching - can be enhanced with composer/semver
        // Supports: "8.0", "^8.0", ">=8.0", "8.*"
        $constraint = trim($constraint);

        // Exact match
        if (strpos($constraint, '*') === false && strpos($constraint, '^') === false &&
            strpos($constraint, '>=') === false && strpos($constraint, '<=') === false &&
            strpos($constraint, '>') === false && strpos($constraint, '<') === false) {
            return version_compare($currentVersion, $constraint, '=');
        }

        // Wildcard match (e.g., 8.*)
        if (strpos($constraint, '*') !== false) {
            $pattern = str_replace('*', '', $constraint);
            return strpos($currentVersion, $pattern) === 0;
        }

        // Caret match (e.g., ^8.0 means >=8.0 <9.0)
        if (strpos($constraint, '^') === 0) {
            $minVersion = ltrim($constraint, '^');
            $parts = explode('.', $minVersion);
            $majorVersion = (int) $parts[0];
            return version_compare($currentVersion, $minVersion, '>=') &&
                   version_compare($currentVersion, ($majorVersion + 1) . '.0', '<');
        }

        // >= constraint
        if (strpos($constraint, '>=') === 0) {
            $minVersion = ltrim($constraint, '>=');
            return version_compare($currentVersion, trim($minVersion), '>=');
        }

        // <= constraint
        if (strpos($constraint, '<=') === 0) {
            $maxVersion = ltrim($constraint, '<=');
            return version_compare($currentVersion, trim($maxVersion), '<=');
        }

        // > constraint
        if (strpos($constraint, '>') === 0 && strpos($constraint, '>=') !== 0) {
            $minVersion = ltrim($constraint, '>');
            return version_compare($currentVersion, trim($minVersion), '>');
        }

        // < constraint
        if (strpos($constraint, '<') === 0 && strpos($constraint, '<=') !== 0) {
            $maxVersion = ltrim($constraint, '<');
            return version_compare($currentVersion, trim($maxVersion), '<');
        }

        return false;
    }

    /**
     * Increment downloads count.
     */
    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ExtensionVersionFactory::new();
    }
}
