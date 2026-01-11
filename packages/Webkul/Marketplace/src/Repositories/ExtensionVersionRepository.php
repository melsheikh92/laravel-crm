<?php

namespace Webkul\Marketplace\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Marketplace\Contracts\ExtensionVersion;

class ExtensionVersionRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'version',
        'changelog',
        'status',
        'laravel_version',
        'crm_version',
        'php_version',
        'extension_id',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return ExtensionVersion::class;
    }

    /**
     * Get the latest version for an extension.
     *
     * @param  int  $extensionId
     * @return \Webkul\Marketplace\Contracts\ExtensionVersion|null
     */
    public function getLatestVersion($extensionId)
    {
        return $this->scopeQuery(function ($query) use ($extensionId) {
            return $query->where('extension_id', $extensionId)
                ->latest()
                ->limit(1);
        })->first();
    }

    /**
     * Get all versions for an extension.
     *
     * @param  int  $extensionId
     * @param  bool  $approvedOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByExtension($extensionId, $approvedOnly = true)
    {
        return $this->scopeQuery(function ($query) use ($extensionId, $approvedOnly) {
            $query = $query->where('extension_id', $extensionId);

            if ($approvedOnly) {
                $query->approved();
            }

            return $query->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC')
                ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, ".", 2), ".", -1) AS UNSIGNED) DESC')
                ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC');
        })->all();
    }

    /**
     * Get approved versions only.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApproved($columns = ['*'])
    {
        return $this->scopeQuery(function ($query) use ($columns) {
            return $query->approved()->select($columns);
        })->all();
    }

    /**
     * Get released versions only.
     *
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReleased($extensionId = null)
    {
        return $this->scopeQuery(function ($query) use ($extensionId) {
            $query = $query->released();

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->with(['extension'])
                ->orderBy('release_date', 'desc');
        })->all();
    }

    /**
     * Find version by extension ID and version number.
     *
     * @param  int  $extensionId
     * @param  string  $version
     * @return \Webkul\Marketplace\Contracts\ExtensionVersion|null
     */
    public function findByVersion($extensionId, $version)
    {
        return $this->findOneWhere([
            'extension_id' => $extensionId,
            'version'      => $version,
        ]);
    }

    /**
     * Check if a version exists.
     *
     * @param  int  $extensionId
     * @param  string  $version
     * @param  int|null  $excludeId
     * @return bool
     */
    public function versionExists($extensionId, $version, $excludeId = null)
    {
        $query = $this->scopeQuery(function ($q) use ($extensionId, $version, $excludeId) {
            $q = $q->where('extension_id', $extensionId)
                ->where('version', $version);

            if ($excludeId) {
                $q->where('id', '!=', $excludeId);
            }

            return $q;
        });

        return $query->count() > 0;
    }

    /**
     * Get compatible versions for given requirements.
     *
     * @param  int  $extensionId
     * @param  string|null  $laravelVersion
     * @param  string|null  $crmVersion
     * @param  string|null  $phpVersion
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompatibleVersions($extensionId, $laravelVersion = null, $crmVersion = null, $phpVersion = null)
    {
        $versions = $this->getByExtension($extensionId, true);

        return $versions->filter(function ($version) use ($laravelVersion, $crmVersion, $phpVersion) {
            return $version->isCompatibleWith($laravelVersion, $crmVersion, $phpVersion);
        });
    }

    /**
     * Get the latest compatible version for an extension.
     *
     * @param  int  $extensionId
     * @param  string|null  $laravelVersion
     * @param  string|null  $crmVersion
     * @param  string|null  $phpVersion
     * @return \Webkul\Marketplace\Contracts\ExtensionVersion|null
     */
    public function getLatestCompatibleVersion($extensionId, $laravelVersion = null, $crmVersion = null, $phpVersion = null)
    {
        $compatibleVersions = $this->getCompatibleVersions($extensionId, $laravelVersion, $crmVersion, $phpVersion);

        return $compatibleVersions->first();
    }

    /**
     * Get versions by status.
     *
     * @param  string  $status
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStatus($status, $extensionId = null)
    {
        return $this->scopeQuery(function ($query) use ($status, $extensionId) {
            $query = $query->where('status', $status);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->with(['extension'])
                ->orderBy('created_at', 'desc');
        })->all();
    }

    /**
     * Get pending versions (awaiting approval).
     *
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPending($extensionId = null)
    {
        return $this->getByStatus('pending', $extensionId);
    }

    /**
     * Check if newer version exists for an extension.
     *
     * @param  int  $extensionId
     * @param  string  $currentVersion
     * @return bool
     */
    public function hasNewerVersion($extensionId, $currentVersion)
    {
        $latestVersion = $this->getLatestVersion($extensionId);

        if (!$latestVersion || !$latestVersion->isApproved()) {
            return false;
        }

        return version_compare($latestVersion->version, $currentVersion, '>');
    }

    /**
     * Get all versions newer than specified version.
     *
     * @param  int  $extensionId
     * @param  string  $currentVersion
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNewerVersions($extensionId, $currentVersion)
    {
        $versions = $this->getByExtension($extensionId, true);

        return $versions->filter(function ($version) use ($currentVersion) {
            return version_compare($version->version, $currentVersion, '>');
        });
    }

    /**
     * Get recently released versions.
     *
     * @param  int  $days
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentlyReleased($days = 7, $limit = 10)
    {
        return $this->scopeQuery(function ($query) use ($days, $limit) {
            return $query->released()
                ->with(['extension'])
                ->where('release_date', '>=', now()->subDays($days))
                ->orderBy('release_date', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Get version statistics for an extension.
     *
     * @param  int  $extensionId
     * @return array
     */
    public function getStatistics($extensionId)
    {
        $versions = $this->findWhere(['extension_id' => $extensionId]);

        return [
            'total_versions'     => $versions->count(),
            'approved_versions'  => $versions->where('status', 'approved')->count(),
            'pending_versions'   => $versions->where('status', 'pending')->count(),
            'rejected_versions'  => $versions->where('status', 'rejected')->count(),
            'total_downloads'    => $versions->sum('downloads_count'),
            'latest_version'     => $this->getLatestVersion($extensionId)?->version,
            'latest_release'     => $this->getLatestVersion($extensionId)?->release_date?->format('Y-m-d'),
        ];
    }

    /**
     * Get version changelog history.
     *
     * @param  int  $extensionId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChangelogHistory($extensionId, $limit = 5)
    {
        return $this->scopeQuery(function ($query) use ($extensionId, $limit) {
            return $query->where('extension_id', $extensionId)
                ->approved()
                ->released()
                ->whereNotNull('changelog')
                ->where('changelog', '!=', '')
                ->orderBy('release_date', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Get versions by multiple IDs.
     *
     * @param  array  $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByIds(array $ids)
    {
        return $this->scopeQuery(function ($query) use ($ids) {
            return $query->with(['extension'])
                ->whereIn('id', $ids);
        })->all();
    }

    /**
     * Get most downloaded versions.
     *
     * @param  int  $limit
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMostDownloaded($limit = 10, $extensionId = null)
    {
        return $this->scopeQuery(function ($query) use ($limit, $extensionId) {
            $query = $query->approved()
                ->with(['extension'])
                ->where('downloads_count', '>', 0);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->orderBy('downloads_count', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Get versions that require specific dependencies.
     *
     * @param  string  $dependencyName
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDependency($dependencyName)
    {
        return $this->scopeQuery(function ($query) use ($dependencyName) {
            return $query->approved()
                ->with(['extension'])
                ->whereNotNull('dependencies')
                ->whereRaw("JSON_SEARCH(dependencies, 'one', ?) IS NOT NULL", [$dependencyName])
                ->orderBy('created_at', 'desc');
        })->all();
    }
}
