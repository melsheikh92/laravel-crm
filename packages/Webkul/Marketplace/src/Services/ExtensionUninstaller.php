<?php

namespace Webkul\Marketplace\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;

class ExtensionUninstaller
{
    /**
     * Path where extensions are stored.
     */
    protected string $extensionsPath;

    /**
     * Path for backup storage.
     */
    protected string $backupPath;

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionInstallationRepository $extensionInstallationRepository,
        protected CompatibilityChecker $compatibilityChecker
    ) {
        $this->extensionsPath = base_path('packages');
        $this->backupPath = storage_path('app/extensions/backups');
    }

    /**
     * Uninstall an extension.
     *
     * @param  int  $installationId
     * @param  bool  $keepData
     * @return array
     */
    public function uninstall(int $installationId, bool $keepData = false): array
    {
        DB::beginTransaction();

        $backupCreated = null;
        $packagePath = null;

        try {
            // Get installation record with relationships
            $installation = $this->extensionInstallationRepository
                ->with(['extension', 'version', 'user'])
                ->findOrFail($installationId);

            // Mark as uninstalling
            $installation->markAsUninstalling();

            // Get package details from composer.json
            $packagePath = $this->getPackagePath($installation->extension);

            if (!$packagePath || !File::exists($packagePath)) {
                throw new Exception('Extension package directory not found');
            }

            // Create backup before uninstalling (in case user wants to rollback)
            $backupCreated = $this->createBackup($packagePath);

            // Parse composer.json to get package information
            $composerData = $this->compatibilityChecker->parseComposerJson($packagePath);
            if (!$composerData['success']) {
                throw new Exception('Failed to parse composer.json');
            }

            $packageName = $composerData['data']['name'];

            // Run down migrations if not keeping data
            if (!$keepData) {
                $migrationResult = $this->runDownMigrations($packagePath);
                if (!$migrationResult['success']) {
                    throw new Exception($migrationResult['error']);
                }
            }

            // Update root composer.json (remove autoload entries)
            $composerUpdateResult = $this->updateComposerJson($packageName, $packagePath, 'remove');
            if (!$composerUpdateResult['success']) {
                throw new Exception($composerUpdateResult['error']);
            }

            // Remove package files
            $this->removePackageFiles($packagePath);

            // Clean up database records
            $this->cleanupDatabaseRecords($installation, $keepData);

            // Clear cache
            $this->clearCache();

            // Delete installation record
            $this->extensionInstallationRepository->delete($installationId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Extension uninstalled successfully',
                'backup_path' => $backupCreated,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            // Log error
            Log::error('Extension uninstallation failed', [
                'installation_id' => $installationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Restore from backup if exists
            if ($backupCreated && $packagePath) {
                $this->restoreFromBackup($backupCreated, $packagePath);
            }

            // Update installation record status
            if (isset($installation)) {
                $this->extensionInstallationRepository->update([
                    'status' => 'active',
                    'installation_notes' => 'Uninstallation failed: ' . $e->getMessage(),
                ], $installationId);
            }

            return [
                'success' => false,
                'error' => 'Uninstallation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Uninstall extension by extension ID.
     *
     * @param  int  $extensionId
     * @param  int  $userId
     * @param  bool  $keepData
     * @return array
     */
    public function uninstallByExtension(int $extensionId, int $userId, bool $keepData = false): array
    {
        try {
            // Find installation record
            $installation = $this->extensionInstallationRepository
                ->findWhere([
                    'extension_id' => $extensionId,
                    'user_id' => $userId,
                ])
                ->first();

            if (!$installation) {
                return [
                    'success' => false,
                    'error' => 'Extension installation not found',
                ];
            }

            return $this->uninstall($installation->id, $keepData);
        } catch (Exception $e) {
            Log::error('Extension uninstallation by extension ID failed', [
                'extension_id' => $extensionId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Uninstallation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get package path from extension.
     *
     * @param  mixed  $extension
     * @return string|null
     */
    protected function getPackagePath($extension): ?string
    {
        try {
            // Try to find package in packages directory
            $possiblePaths = File::glob($this->extensionsPath . '/*/*');

            foreach ($possiblePaths as $path) {
                if (File::exists($path . '/composer.json')) {
                    $composerData = $this->compatibilityChecker->parseComposerJson($path);

                    if ($composerData['success']) {
                        $packageName = $composerData['data']['name'] ?? null;

                        // Match by package name stored in extension
                        if ($packageName === $extension->slug ||
                            str_replace('/', '-', $packageName) === $extension->slug) {
                            return $path;
                        }
                    }
                }
            }

            // Fallback: construct path from slug
            $slugParts = explode('-', $extension->slug);
            if (count($slugParts) >= 2) {
                $vendor = ucfirst($slugParts[0]);
                $package = str_replace('-', '', ucwords(implode('-', array_slice($slugParts, 1)), '-'));
                $constructedPath = $this->extensionsPath . '/' . $vendor . '/' . $package;

                if (File::exists($constructedPath)) {
                    return $constructedPath;
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to locate package path', [
                'extension' => $extension->slug,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Run down migrations for the package.
     *
     * @param  string  $packagePath
     * @return array
     */
    protected function runDownMigrations(string $packagePath): array
    {
        try {
            $migrationsPath = $packagePath . '/src/Database/Migrations';

            if (!File::exists($migrationsPath)) {
                // No migrations to rollback
                return [
                    'success' => true,
                    'message' => 'No migrations found',
                ];
            }

            // Get migration files
            $migrationFiles = File::files($migrationsPath);

            if (empty($migrationFiles)) {
                return [
                    'success' => true,
                    'message' => 'No migrations to rollback',
                ];
            }

            // Run rollback for all migrations in this path
            Artisan::call('migrate:rollback', [
                '--path' => str_replace(base_path() . '/', '', $migrationsPath),
                '--force' => true,
            ]);

            return [
                'success' => true,
                'message' => 'Migrations rolled back successfully',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Migration rollback failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update root composer.json.
     *
     * @param  string  $packageName
     * @param  string  $packagePath
     * @param  string  $action
     * @return array
     */
    protected function updateComposerJson(string $packageName, string $packagePath, string $action = 'remove'): array
    {
        try {
            $rootComposerPath = base_path('composer.json');
            $rootComposer = json_decode(File::get($rootComposerPath), true);

            // Get package composer data
            $packageComposerData = $this->compatibilityChecker->parseComposerJson($packagePath);

            if (!$packageComposerData['success'] || !isset($packageComposerData['data']['autoload']['psr-4'])) {
                return [
                    'success' => true,
                    'message' => 'No autoload entries to remove',
                ];
            }

            $psr4 = $packageComposerData['data']['autoload']['psr-4'];
            $modified = false;

            // Remove PSR-4 autoload entries
            foreach ($psr4 as $namespace => $path) {
                if (isset($rootComposer['autoload']['psr-4'][$namespace])) {
                    unset($rootComposer['autoload']['psr-4'][$namespace]);
                    $modified = true;
                }
            }

            // Save composer.json if modified
            if ($modified) {
                File::put($rootComposerPath, json_encode($rootComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                // Run composer dump-autoload
                Artisan::call('optimize:clear');
            }

            return [
                'success' => true,
                'message' => 'Composer.json updated successfully',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Composer update failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Remove package files from packages directory.
     *
     * @param  string  $packagePath
     * @return void
     */
    protected function removePackageFiles(string $packagePath): void
    {
        try {
            if (File::exists($packagePath)) {
                File::deleteDirectory($packagePath);
            }
        } catch (Exception $e) {
            Log::error('Failed to remove package files', [
                'package_path' => $packagePath,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to remove package files: ' . $e->getMessage());
        }
    }

    /**
     * Clean up database records related to the extension.
     *
     * @param  mixed  $installation
     * @param  bool  $keepData
     * @return void
     */
    protected function cleanupDatabaseRecords($installation, bool $keepData): void
    {
        try {
            if ($keepData) {
                // Only mark as inactive, keep the records
                return;
            }

            // Additional cleanup could be done here if needed
            // For example, removing extension-specific data
            // This is intentionally minimal to avoid data loss

            // Note: The installation record itself is deleted in the main uninstall method
        } catch (Exception $e) {
            Log::warning('Database cleanup encountered issues', [
                'installation_id' => $installation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear application cache.
     *
     * @return void
     */
    protected function clearCache(): void
    {
        try {
            Artisan::call('optimize:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        } catch (Exception $e) {
            Log::warning('Cache clearing failed during uninstallation', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create backup of package before uninstallation.
     *
     * @param  string  $packagePath
     * @return string
     */
    protected function createBackup(string $packagePath): string
    {
        try {
            $this->ensureBackupDirectory();

            $backupPath = $this->backupPath . '/' . basename($packagePath) . '_uninstall_' . time();
            File::copyDirectory($packagePath, $backupPath);

            return $backupPath;
        } catch (Exception $e) {
            Log::warning('Failed to create backup during uninstallation', [
                'package_path' => $packagePath,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Restore package from backup.
     *
     * @param  string  $backupPath
     * @param  string  $targetPath
     * @return void
     */
    protected function restoreFromBackup(string $backupPath, string $targetPath): void
    {
        try {
            if (File::exists($backupPath)) {
                // Remove failed installation files
                if (File::exists($targetPath)) {
                    File::deleteDirectory($targetPath);
                }

                // Restore from backup
                File::copyDirectory($backupPath, $targetPath);

                // Keep backup for reference
                Log::info('Package restored from backup', [
                    'backup_path' => $backupPath,
                    'target_path' => $targetPath,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to restore from backup', [
                'backup_path' => $backupPath,
                'target_path' => $targetPath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ensure backup directory exists.
     *
     * @return void
     */
    protected function ensureBackupDirectory(): void
    {
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Delete old backups to free up space.
     *
     * @param  int  $daysOld
     * @return array
     */
    public function cleanupOldBackups(int $daysOld = 30): array
    {
        try {
            $this->ensureBackupDirectory();

            $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
            $directories = File::directories($this->backupPath);
            $deleted = 0;

            foreach ($directories as $directory) {
                // Check directory modification time
                if (File::lastModified($directory) < $cutoffTime) {
                    File::deleteDirectory($directory);
                    $deleted++;
                }
            }

            return [
                'success' => true,
                'message' => "Deleted {$deleted} old backup(s)",
                'deleted_count' => $deleted,
            ];
        } catch (Exception $e) {
            Log::error('Backup cleanup failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Backup cleanup failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of available backups.
     *
     * @return array
     */
    public function getBackups(): array
    {
        try {
            $this->ensureBackupDirectory();

            $directories = File::directories($this->backupPath);
            $backups = [];

            foreach ($directories as $directory) {
                $backups[] = [
                    'path' => $directory,
                    'name' => basename($directory),
                    'size' => $this->getDirectorySize($directory),
                    'created_at' => File::lastModified($directory),
                ];
            }

            // Sort by creation time (newest first)
            usort($backups, function ($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });

            return [
                'success' => true,
                'backups' => $backups,
            ];
        } catch (Exception $e) {
            Log::error('Failed to retrieve backups', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to retrieve backups: ' . $e->getMessage(),
                'backups' => [],
            ];
        }
    }

    /**
     * Get directory size in bytes.
     *
     * @param  string  $directory
     * @return int
     */
    protected function getDirectorySize(string $directory): int
    {
        try {
            $size = 0;
            $files = File::allFiles($directory);

            foreach ($files as $file) {
                $size += $file->getSize();
            }

            return $size;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Restore an extension from a backup.
     *
     * @param  string  $backupName
     * @return array
     */
    public function restoreFromBackupByName(string $backupName): array
    {
        try {
            $backupPath = $this->backupPath . '/' . $backupName;

            if (!File::exists($backupPath)) {
                return [
                    'success' => false,
                    'error' => 'Backup not found',
                ];
            }

            // Extract package name from backup name
            $packageName = preg_replace('/_uninstall_\d+$/', '', $backupName);
            $targetPath = $this->extensionsPath . '/' . $packageName;

            // Copy backup to packages directory
            if (File::exists($targetPath)) {
                return [
                    'success' => false,
                    'error' => 'Package directory already exists',
                ];
            }

            File::copyDirectory($backupPath, $targetPath);

            // Run migrations
            $migrationsPath = $targetPath . '/src/Database/Migrations';
            if (File::exists($migrationsPath)) {
                Artisan::call('migrate', [
                    '--path' => str_replace(base_path() . '/', '', $migrationsPath),
                    '--force' => true,
                ]);
            }

            // Update composer.json
            $composerData = $this->compatibilityChecker->parseComposerJson($targetPath);
            if ($composerData['success']) {
                $this->updateComposerJsonForRestore($composerData['data']['name'], $targetPath);
            }

            // Clear cache
            $this->clearCache();

            return [
                'success' => true,
                'message' => 'Extension restored from backup successfully',
                'target_path' => $targetPath,
            ];
        } catch (Exception $e) {
            Log::error('Restore from backup failed', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Restore failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update composer.json when restoring from backup.
     *
     * @param  string  $packageName
     * @param  string  $packagePath
     * @return void
     */
    protected function updateComposerJsonForRestore(string $packageName, string $packagePath): void
    {
        try {
            $rootComposerPath = base_path('composer.json');
            $rootComposer = json_decode(File::get($rootComposerPath), true);

            // Get package composer data
            $packageComposerData = $this->compatibilityChecker->parseComposerJson($packagePath);

            if ($packageComposerData['success'] && isset($packageComposerData['data']['autoload']['psr-4'])) {
                $psr4 = $packageComposerData['data']['autoload']['psr-4'];

                foreach ($psr4 as $namespace => $path) {
                    $relativePath = str_replace(base_path() . '/', '', $packagePath . '/' . $path);
                    $rootComposer['autoload']['psr-4'][$namespace] = $relativePath;
                }

                File::put($rootComposerPath, json_encode($rootComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                Artisan::call('optimize:clear');
            }
        } catch (Exception $e) {
            Log::error('Composer update during restore failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
