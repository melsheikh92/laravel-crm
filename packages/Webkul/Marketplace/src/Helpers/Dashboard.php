<?php

namespace Webkul\Marketplace\Helpers;

use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Repositories\ExtensionSubmissionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class Dashboard
{
    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionInstallationRepository $installationRepository,
        protected ExtensionSubmissionRepository $submissionRepository,
        protected ExtensionVersionRepository $versionRepository
    ) {}

    /**
     * Get popular extensions stats.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getPopularExtensions($limit = 5)
    {
        return $this->extensionRepository->scopeQuery(function ($query) use ($limit) {
            return $query->approved()
                ->with(['author', 'category'])
                ->orderBy('downloads_count', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Get recent installations for admin.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRecentInstallations($limit = 5)
    {
        return $this->installationRepository->scopeQuery(function ($query) use ($limit) {
            return $query->with(['extension', 'user'])
                ->orderBy('installed_at', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Get user's installed extensions.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUserInstalledExtensions($userId, $limit = 5)
    {
        return $this->installationRepository->scopeQuery(function ($query) use ($userId, $limit) {
            return $query->where('user_id', $userId)
                ->with(['extension', 'version'])
                ->orderBy('installed_at', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Get pending updates for user.
     *
     * @param  int  $userId
     * @return \Illuminate\Support\Collection
     */
    public function getPendingUpdates($userId)
    {
        $installations = $this->installationRepository->scopeQuery(function ($query) use ($userId) {
            return $query->where('user_id', $userId)
                ->where('status', 'active')
                ->with(['extension.versions' => function ($query) {
                    $query->where('status', 'approved')
                        ->orderBy('created_at', 'desc');
                }, 'version']);
        })->all();

        return $installations->filter(function ($installation) {
            if (!$installation->extension || !$installation->version) {
                return false;
            }

            $latestVersion = $installation->extension->versions->first();

            if (!$latestVersion) {
                return false;
            }

            return version_compare($latestVersion->version, $installation->version->version, '>');
        })->map(function ($installation) {
            $latestVersion = $installation->extension->versions->first();

            return [
                'installation' => $installation,
                'latest_version' => $latestVersion,
                'current_version' => $installation->version->version,
            ];
        });
    }

    /**
     * Get marketplace overview stats for admin.
     *
     * @return array
     */
    public function getMarketplaceStats()
    {
        return [
            'total_extensions' => $this->extensionRepository->count(),
            'approved_extensions' => $this->extensionRepository->scopeQuery(function ($query) {
                return $query->approved();
            })->count(),
            'total_installations' => $this->installationRepository->count(),
            'active_installations' => $this->installationRepository->scopeQuery(function ($query) {
                return $query->where('status', 'active');
            })->count(),
            'pending_submissions' => $this->submissionRepository->scopeQuery(function ($query) {
                return $query->where('status', 'pending');
            })->count(),
        ];
    }
}
