<?php

namespace Webkul\Marketplace\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class UpdateNotifier
{
    /**
     * Cache key prefix for update checks.
     */
    const CACHE_PREFIX = 'marketplace:updates:';

    /**
     * Cache TTL for update checks (in seconds).
     */
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected ExtensionInstallationRepository $installationRepository,
        protected ExtensionVersionRepository $versionRepository,
        protected ExtensionRepository $extensionRepository
    ) {}

    /**
     * Check for updates for a specific installation.
     *
     * @param  int  $installationId
     * @return array
     */
    public function checkForUpdate(int $installationId): array
    {
        try {
            $installation = $this->installationRepository->findOrFail($installationId);

            if (!$installation->extension) {
                return [
                    'success' => false,
                    'error' => 'Extension not found',
                ];
            }

            $currentVersion = $installation->version;
            $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);

            if (!$latestVersion) {
                return [
                    'success' => true,
                    'has_update' => false,
                    'message' => 'No versions available',
                ];
            }

            if (!$latestVersion->isApproved()) {
                return [
                    'success' => true,
                    'has_update' => false,
                    'message' => 'No approved versions available',
                ];
            }

            $hasUpdate = version_compare($latestVersion->version, $currentVersion->version, '>');

            return [
                'success' => true,
                'has_update' => $hasUpdate,
                'installation' => $installation,
                'current_version' => $currentVersion->version,
                'latest_version' => $latestVersion->version,
                'update_info' => $hasUpdate ? [
                    'version_id' => $latestVersion->id,
                    'version' => $latestVersion->version,
                    'release_date' => $latestVersion->release_date?->format('Y-m-d H:i:s'),
                    'changelog' => $latestVersion->changelog,
                    'file_size' => $latestVersion->formatted_file_size,
                    'downloads_count' => $latestVersion->downloads_count,
                ] : null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to check for update', [
                'installation_id' => $installationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to check for update: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check for updates for all installations of a specific user.
     *
     * @param  int  $userId
     * @param  bool  $activeOnly
     * @return array
     */
    public function checkUserUpdates(int $userId, bool $activeOnly = true): array
    {
        try {
            $query = $this->installationRepository
                ->resetScope()
                ->scopeQuery(function ($q) use ($userId, $activeOnly) {
                    $q = $q->where('user_id', $userId);

                    if ($activeOnly) {
                        $q->active();
                    }

                    return $q->with(['extension', 'version']);
                });

            $installations = $query->all();

            $updates = [];
            $totalUpdates = 0;

            foreach ($installations as $installation) {
                $updateCheck = $this->checkForUpdate($installation->id);

                if ($updateCheck['success'] && $updateCheck['has_update']) {
                    $updates[] = [
                        'installation_id' => $installation->id,
                        'extension_id' => $installation->extension_id,
                        'extension_name' => $installation->extension->name,
                        'extension_slug' => $installation->extension->slug,
                        'current_version' => $updateCheck['current_version'],
                        'latest_version' => $updateCheck['latest_version'],
                        'update_info' => $updateCheck['update_info'],
                    ];
                    $totalUpdates++;
                }
            }

            return [
                'success' => true,
                'user_id' => $userId,
                'total_installations' => $installations->count(),
                'total_updates' => $totalUpdates,
                'updates' => $updates,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to check user updates', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to check user updates: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check for updates for all installations in the system.
     *
     * @param  bool  $activeOnly
     * @return array
     */
    public function checkAllUpdates(bool $activeOnly = true): array
    {
        try {
            $query = $this->installationRepository
                ->resetScope()
                ->scopeQuery(function ($q) use ($activeOnly) {
                    if ($activeOnly) {
                        $q->active();
                    }

                    return $q->with(['extension', 'version', 'user']);
                });

            $installations = $query->all();

            $updates = [];
            $totalUpdates = 0;
            $userUpdates = [];

            foreach ($installations as $installation) {
                $updateCheck = $this->checkForUpdate($installation->id);

                if ($updateCheck['success'] && $updateCheck['has_update']) {
                    $updateData = [
                        'installation_id' => $installation->id,
                        'user_id' => $installation->user_id,
                        'user_name' => $installation->user->name ?? 'Unknown',
                        'extension_id' => $installation->extension_id,
                        'extension_name' => $installation->extension->name,
                        'current_version' => $updateCheck['current_version'],
                        'latest_version' => $updateCheck['latest_version'],
                        'update_info' => $updateCheck['update_info'],
                    ];

                    $updates[] = $updateData;
                    $totalUpdates++;

                    // Group by user
                    if (!isset($userUpdates[$installation->user_id])) {
                        $userUpdates[$installation->user_id] = [
                            'user_id' => $installation->user_id,
                            'user_name' => $installation->user->name ?? 'Unknown',
                            'updates' => [],
                        ];
                    }

                    $userUpdates[$installation->user_id]['updates'][] = $updateData;
                }
            }

            return [
                'success' => true,
                'total_installations' => $installations->count(),
                'total_updates' => $totalUpdates,
                'updates' => $updates,
                'by_user' => array_values($userUpdates),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to check all updates', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to check all updates: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get detailed update information including changelog.
     *
     * @param  int  $installationId
     * @return array
     */
    public function getUpdateInfo(int $installationId): array
    {
        try {
            $updateCheck = $this->checkForUpdate($installationId);

            if (!$updateCheck['success']) {
                return $updateCheck;
            }

            if (!$updateCheck['has_update']) {
                return [
                    'success' => true,
                    'has_update' => false,
                    'message' => 'No updates available',
                ];
            }

            $installation = $updateCheck['installation'];
            $currentVersion = $installation->version;
            $extensionId = $installation->extension_id;

            // Get all newer versions with their changelogs
            $newerVersions = $this->versionRepository->getNewerVersions(
                $extensionId,
                $currentVersion->version
            );

            $changelogs = $newerVersions->map(function ($version) {
                return [
                    'version' => $version->version,
                    'release_date' => $version->release_date?->format('Y-m-d H:i:s'),
                    'changelog' => $version->changelog,
                    'file_size' => $version->formatted_file_size,
                    'downloads_count' => $version->downloads_count,
                    'laravel_version' => $version->laravel_version,
                    'crm_version' => $version->crm_version,
                    'php_version' => $version->php_version,
                ];
            })->values()->all();

            return [
                'success' => true,
                'has_update' => true,
                'extension' => [
                    'id' => $installation->extension->id,
                    'name' => $installation->extension->name,
                    'slug' => $installation->extension->slug,
                    'description' => $installation->extension->description,
                    'logo' => $installation->extension->logo,
                ],
                'current_version' => $currentVersion->version,
                'latest_version' => $updateCheck['latest_version'],
                'newer_versions' => $changelogs,
                'total_newer_versions' => count($changelogs),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get update info', [
                'installation_id' => $installationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get update info: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get cached update check for an installation.
     *
     * @param  int  $installationId
     * @param  bool  $forceRefresh
     * @return array
     */
    public function getCachedUpdateCheck(int $installationId, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'installation:' . $installationId;

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($installationId) {
            return $this->checkForUpdate($installationId);
        });
    }

    /**
     * Get cached update checks for a user.
     *
     * @param  int  $userId
     * @param  bool  $forceRefresh
     * @return array
     */
    public function getCachedUserUpdates(int $userId, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'user:' . $userId;

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            return $this->checkUserUpdates($userId);
        });
    }

    /**
     * Clear update check cache for an installation.
     *
     * @param  int  $installationId
     * @return bool
     */
    public function clearCache(int $installationId): bool
    {
        try {
            $installation = $this->installationRepository->findOrFail($installationId);

            // Clear installation cache
            Cache::forget(self::CACHE_PREFIX . 'installation:' . $installationId);

            // Clear user cache
            Cache::forget(self::CACHE_PREFIX . 'user:' . $installation->user_id);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear update cache', [
                'installation_id' => $installationId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear all update check caches.
     *
     * @return bool
     */
    public function clearAllCaches(): bool
    {
        try {
            // Note: This requires a cache store that supports tags or manual key tracking
            // For now, we'll clear by pattern if using Redis, otherwise rely on TTL
            $installations = $this->installationRepository->all();

            foreach ($installations as $installation) {
                Cache::forget(self::CACHE_PREFIX . 'installation:' . $installation->id);
                Cache::forget(self::CACHE_PREFIX . 'user:' . $installation->user_id);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear all update caches', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get installations that need updates.
     *
     * @param  int|null  $userId
     * @param  bool  $autoUpdateOnly
     * @return array
     */
    public function getInstallationsNeedingUpdate(?int $userId = null, bool $autoUpdateOnly = false): array
    {
        try {
            $query = $this->installationRepository
                ->resetScope()
                ->scopeQuery(function ($q) use ($userId, $autoUpdateOnly) {
                    $q = $q->active();

                    if ($userId) {
                        $q->where('user_id', $userId);
                    }

                    if ($autoUpdateOnly) {
                        $q->autoUpdateEnabled();
                    }

                    return $q->with(['extension', 'version', 'user']);
                });

            $installations = $query->all();

            $needingUpdate = [];

            foreach ($installations as $installation) {
                $updateCheck = $this->getCachedUpdateCheck($installation->id);

                if ($updateCheck['success'] && $updateCheck['has_update']) {
                    $needingUpdate[] = [
                        'installation' => $installation,
                        'current_version' => $updateCheck['current_version'],
                        'latest_version' => $updateCheck['latest_version'],
                        'update_info' => $updateCheck['update_info'],
                    ];
                }
            }

            return [
                'success' => true,
                'total' => count($needingUpdate),
                'installations' => $needingUpdate,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get installations needing update', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get installations needing update: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get update statistics.
     *
     * @return array
     */
    public function getUpdateStatistics(): array
    {
        try {
            $allInstallations = $this->installationRepository
                ->resetScope()
                ->scopeQuery(function ($q) {
                    return $q->active()->with(['extension', 'version']);
                })
                ->all();

            $totalInstallations = $allInstallations->count();
            $installationsWithUpdates = 0;
            $installationsUpToDate = 0;
            $autoUpdateEnabled = 0;

            foreach ($allInstallations as $installation) {
                $updateCheck = $this->getCachedUpdateCheck($installation->id);

                if ($updateCheck['success']) {
                    if ($updateCheck['has_update']) {
                        $installationsWithUpdates++;
                    } else {
                        $installationsUpToDate++;
                    }
                }

                if ($installation->auto_update_enabled) {
                    $autoUpdateEnabled++;
                }
            }

            // Get recently updated installations
            $recentlyUpdated = $this->installationRepository
                ->resetScope()
                ->scopeQuery(function ($q) {
                    return $q->active()
                        ->with(['extension', 'version'])
                        ->whereNotNull('updated_at_version')
                        ->where('updated_at_version', '>=', now()->subDays(30))
                        ->orderBy('updated_at_version', 'desc')
                        ->limit(10);
                })
                ->all();

            return [
                'success' => true,
                'statistics' => [
                    'total_installations' => $totalInstallations,
                    'installations_with_updates' => $installationsWithUpdates,
                    'installations_up_to_date' => $installationsUpToDate,
                    'auto_update_enabled' => $autoUpdateEnabled,
                    'update_percentage' => $totalInstallations > 0
                        ? round(($installationsWithUpdates / $totalInstallations) * 100, 2)
                        : 0,
                ],
                'recently_updated' => $recentlyUpdated->map(function ($installation) {
                    return [
                        'extension_name' => $installation->extension->name,
                        'version' => $installation->version->version,
                        'updated_at' => $installation->updated_at_version?->format('Y-m-d H:i:s'),
                        'user_name' => $installation->user->name ?? 'Unknown',
                    ];
                })->all(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get update statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get update statistics: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get formatted changelog for display.
     *
     * @param  int  $versionId
     * @return array
     */
    public function getFormattedChangelog(int $versionId): array
    {
        try {
            $version = $this->versionRepository->findOrFail($versionId);

            return [
                'success' => true,
                'version' => $version->version,
                'release_date' => $version->release_date?->format('Y-m-d H:i:s'),
                'changelog' => $version->changelog,
                'changelog_html' => $this->formatChangelogToHtml($version->changelog),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get formatted changelog', [
                'version_id' => $versionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get formatted changelog: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format changelog text to HTML.
     *
     * @param  string|null  $changelog
     * @return string
     */
    protected function formatChangelogToHtml(?string $changelog): string
    {
        if (!$changelog) {
            return '<p>No changelog available.</p>';
        }

        // Simple markdown-like formatting
        $html = e($changelog);

        // Convert headers (## Header)
        $html = preg_replace('/^## (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h2>$1</h2>', $html);

        // Convert bullet points
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $html);

        // Convert line breaks
        $html = nl2br($html);

        return $html;
    }
}
