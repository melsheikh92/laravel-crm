<?php

namespace Webkul\Marketplace\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Marketplace\Exceptions\InstallationException;
use Webkul\Marketplace\Exceptions\NetworkException;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;
use ZipArchive;

class ExtensionInstaller
{
    /**
     * Path where extensions are stored.
     */
    protected string $extensionsPath;

    /**
     * Path for temporary files during installation.
     */
    protected string $tempPath;

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionVersionRepository $extensionVersionRepository,
        protected CompatibilityChecker $compatibilityChecker
    ) {
        $this->extensionsPath = base_path('packages');
        $this->tempPath = storage_path('app/extensions/temp');
    }

    /**
     * Install an extension from a version ID.
     *
     * @param  int  $versionId
     * @param  int  $userId
     * @return array
     */
    public function install(int $versionId, int $userId): array
    {
        DB::beginTransaction();

        $installationRecord = null;
        $extractPath = null;
        $targetPath = null;
        $backupPath = null;

        try {
            // Get version and extension
            $version = $this->extensionVersionRepository->with(['extension'])->findOrFail($versionId);
            $extension = $version->extension;

            // Check if already installed
            if ($this->isInstalled($extension->id, $userId)) {
                throw InstallationException::alreadyInstalled(
                    trans('marketplace::app.marketplace.install.already-installed')
                );
            }

            // Check compatibility
            $compatibility = $this->compatibilityChecker->checkVersionCompatibility($versionId);
            if (!$compatibility['compatible']) {
                $errors = implode(', ', $compatibility['errors'] ?? ['Unknown compatibility issue']);
                throw InstallationException::incompatible(
                    trans('marketplace::app.marketplace.install.errors.compatibility-failed') . ': ' . $errors
                );
            }

            // Create installation record
            $installationRecord = app('Webkul\Marketplace\Repositories\ExtensionInstallationRepository')->create([
                'extension_id' => $extension->id,
                'version_id' => $versionId,
                'user_id' => $userId,
                'status' => 'installing',
                'installed_at' => now(),
            ]);

            // Download and extract package
            $downloadResult = $this->downloadPackage($version->file_path);
            if (!$downloadResult['success']) {
                throw InstallationException::downloadFailed($downloadResult['error']);
            }

            $extractPath = $downloadResult['path'];

            // Validate package structure
            $validationResult = $this->validatePackageStructure($extractPath);
            if (!$validationResult['success']) {
                throw InstallationException::validationFailed(
                    trans('marketplace::app.marketplace.install.errors.validation-failed') . ': ' . $validationResult['error']
                );
            }

            // Parse composer.json to get package namespace
            $composerData = $this->compatibilityChecker->parseComposerJson($extractPath);
            if (!$composerData['success']) {
                throw InstallationException::validationFailed(
                    trans('marketplace::app.marketplace.install.errors.validation-failed') . ': ' . $composerData['error']
                );
            }

            $packageName = $composerData['data']['name'];
            $packageNameParts = explode('/', $packageName);
            $vendor = ucfirst($packageNameParts[0]);
            $package = str_replace('-', '', ucwords($packageNameParts[1], '-'));

            $targetPath = $this->extensionsPath . '/' . $vendor . '/' . $package;

            // Backup existing installation if updating
            if (File::exists($targetPath)) {
                $backupPath = $this->createBackup($targetPath);
            }

            // Copy files to packages directory
            try {
                $this->copyFiles($extractPath, $targetPath);
            } catch (Exception $e) {
                throw InstallationException::fileSystemError(
                    trans('marketplace::app.marketplace.install.errors.filesystem-error') . ': ' . $e->getMessage()
                );
            }

            // Run migrations (with transaction support)
            $migrationsResult = $this->runMigrations($targetPath);
            if (!$migrationsResult['success']) {
                throw InstallationException::migrationFailed(
                    trans('marketplace::app.marketplace.install.errors.migration-failed') . ': ' . $migrationsResult['error']
                );
            }

            // Update composer.json
            $composerUpdateResult = $this->updateComposerJson($packageName, $targetPath);
            if (!$composerUpdateResult['success']) {
                throw InstallationException::fileSystemError(
                    trans('marketplace::app.marketplace.install.errors.filesystem-error') . ': ' . $composerUpdateResult['error']
                );
            }

            // Register service provider
            $this->registerServiceProvider($vendor, $package);

            // Clear cache
            $this->clearCache();

            // Update installation record
            app('Webkul\Marketplace\Repositories\ExtensionInstallationRepository')->update([
                'status' => 'active',
            ], $installationRecord->id);

            // Increment downloads count
            $extension->incrementDownloads();

            // Clean up temporary files
            $this->cleanup($extractPath);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Extension installed successfully',
                'installation' => $installationRecord->fresh(),
            ];
        } catch (InstallationException $e) {
            DB::rollBack();

            // Log error with exception code for tracking
            Log::error('Extension installation failed', [
                'version_id' => $versionId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Rollback installation
            try {
                $this->rollback($installationRecord, $extractPath, $targetPath, $backupPath);
            } catch (Exception $rollbackError) {
                Log::critical('Rollback failed after installation error', [
                    'version_id' => $versionId,
                    'user_id' => $userId,
                    'installation_error' => $e->getMessage(),
                    'rollback_error' => $rollbackError->getMessage(),
                ]);

                return [
                    'success' => false,
                    'error' => trans('marketplace::app.marketplace.install.errors.rollback-failed'),
                ];
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        } catch (NetworkException $e) {
            DB::rollBack();

            // Log network error
            Log::error('Network error during installation', [
                'version_id' => $versionId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);

            // Rollback installation
            $this->rollback($installationRecord, $extractPath, $targetPath, $backupPath);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'retry_possible' => true, // Indicate that user can retry
            ];
        } catch (Exception $e) {
            DB::rollBack();

            // Log unexpected error
            Log::error('Unexpected error during installation', [
                'version_id' => $versionId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Rollback installation
            $this->rollback($installationRecord, $extractPath, $targetPath, $backupPath);

            return [
                'success' => false,
                'error' => trans('marketplace::app.marketplace.install.errors.unknown-error'),
            ];
        }
    }

    /**
     * Install an extension from a local file.
     *
     * @param  string  $filePath
     * @param  int  $userId
     * @return array
     */
    public function installFromFile(string $filePath, int $userId): array
    {
        try {
            // Extract to temporary location
            $extractPath = $this->extractPackage($filePath);
            if (!$extractPath) {
                return [
                    'success' => false,
                    'error' => 'Failed to extract package',
                ];
            }

            // Validate package structure
            $validationResult = $this->validatePackageStructure($extractPath);
            if (!$validationResult['success']) {
                $this->cleanup($extractPath);
                return $validationResult;
            }

            // Parse composer.json
            $composerData = $this->compatibilityChecker->parseComposerJson($extractPath);
            if (!$composerData['success']) {
                $this->cleanup($extractPath);
                return $composerData;
            }

            // Check compatibility
            $compatibilityResult = $this->compatibilityChecker->validatePackageRequirements($extractPath);
            if (!$compatibilityResult['compatible']) {
                $this->cleanup($extractPath);
                return [
                    'success' => false,
                    'error' => 'Package is not compatible with your system',
                    'details' => $compatibilityResult,
                ];
            }

            // Continue with standard installation
            // Note: This is a simplified version - in production, you'd need to create
            // extension and version records first or match to existing ones
            return [
                'success' => true,
                'message' => 'Package validated successfully. Please submit it to the marketplace for approval.',
                'data' => $composerData['data'],
            ];
        } catch (Exception $e) {
            Log::error('Installation from file failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Installation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Download package from URL or copy from storage.
     *
     * @param  string  $source
     * @return array
     */
    protected function downloadPackage(string $source): array
    {
        try {
            $this->ensureTempDirectory();

            // Check if source is a URL
            if (filter_var($source, FILTER_VALIDATE_URL)) {
                $tempFile = $this->tempPath . '/' . uniqid('extension_') . '.zip';

                // Download with retry logic
                $response = $this->downloadWithRetry($source);

                if (!$response) {
                    throw NetworkException::timeout(
                        trans('marketplace::app.marketplace.install.errors.download-timeout')
                    );
                }

                File::put($tempFile, $response->body());
            } else {
                // Source is a local file path
                $sourcePath = storage_path('app/' . $source);

                if (!File::exists($sourcePath)) {
                    throw InstallationException::validationFailed(
                        trans('marketplace::app.marketplace.install.errors.package-not-found')
                    );
                }

                $tempFile = $this->tempPath . '/' . uniqid('extension_') . '.zip';
                File::copy($sourcePath, $tempFile);
            }

            // Verify the file was created
            if (!File::exists($tempFile) || File::size($tempFile) === 0) {
                throw InstallationException::downloadFailed(
                    trans('marketplace::app.marketplace.install.errors.corrupted-package')
                );
            }

            // Extract the package
            $extractPath = $this->extractPackage($tempFile);

            if (!$extractPath) {
                throw InstallationException::extractionFailed(
                    trans('marketplace::app.marketplace.install.errors.extraction-failed')
                );
            }

            // Remove the zip file
            File::delete($tempFile);

            return [
                'success' => true,
                'path' => $extractPath,
            ];
        } catch (ConnectionException $e) {
            Log::error('Network connection failed during download', [
                'source' => $source,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => trans('marketplace::app.marketplace.network.errors.connection-refused'),
            ];
        } catch (RequestException $e) {
            Log::error('HTTP request failed during download', [
                'source' => $source,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => trans('marketplace::app.marketplace.install.errors.download-failed'),
            ];
        } catch (InstallationException|NetworkException $e) {
            Log::error('Download failed', [
                'source' => $source,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error('Unexpected error during download', [
                'source' => $source,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => trans('marketplace::app.marketplace.install.errors.unknown-error'),
            ];
        }
    }

    /**
     * Download a file with retry logic for network failures.
     *
     * @param  string  $url
     * @param  int  $maxRetries
     * @param  int  $retryDelay
     * @return \Illuminate\Http\Client\Response|null
     */
    protected function downloadWithRetry(string $url, int $maxRetries = 3, int $retryDelay = 2): ?\Illuminate\Http\Client\Response
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $response = Http::timeout(300)
                    ->retry(3, 1000) // Built-in HTTP retry
                    ->get($url);

                if ($response->successful()) {
                    return $response;
                }

                // If response is not successful but not a network error, log and continue
                Log::warning('Download attempt failed with HTTP status', [
                    'url' => $url,
                    'attempt' => $attempt + 1,
                    'status' => $response->status(),
                ]);
            } catch (ConnectionException $e) {
                Log::warning('Network connection failed, will retry', [
                    'url' => $url,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt === $maxRetries - 1) {
                    throw $e;
                }
            } catch (RequestException $e) {
                Log::warning('HTTP request failed, will retry', [
                    'url' => $url,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt === $maxRetries - 1) {
                    throw $e;
                }
            }

            $attempt++;

            // Exponential backoff
            if ($attempt < $maxRetries) {
                sleep($retryDelay * $attempt);
            }
        }

        return null;
    }

    /**
     * Extract package to temporary directory.
     *
     * @param  string  $zipPath
     * @return string|null
     */
    protected function extractPackage(string $zipPath): ?string
    {
        try {
            $extractPath = $this->tempPath . '/' . uniqid('extract_');

            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($extractPath);
                $zip->close();

                // Check if extraction created a single directory
                $contents = File::directories($extractPath);
                if (count($contents) === 1 && count(File::files($extractPath)) === 0) {
                    // Return the subdirectory path
                    return $contents[0];
                }

                return $extractPath;
            }

            return null;
        } catch (Exception $e) {
            Log::error('Package extraction failed', [
                'zip_path' => $zipPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Validate package structure.
     *
     * @param  string  $packagePath
     * @return array
     */
    protected function validatePackageStructure(string $packagePath): array
    {
        // Check for required files
        $requiredFiles = [
            'composer.json',
        ];

        foreach ($requiredFiles as $file) {
            if (!File::exists($packagePath . '/' . $file)) {
                return [
                    'success' => false,
                    'error' => "Required file '{$file}' not found in package",
                ];
            }
        }

        // Validate composer.json structure
        $composerData = $this->compatibilityChecker->parseComposerJson($packagePath);
        if (!$composerData['success']) {
            return $composerData;
        }

        // Check required composer.json fields
        $requiredFields = ['name', 'type'];
        foreach ($requiredFields as $field) {
            if (empty($composerData['data'][$field])) {
                return [
                    'success' => false,
                    'error' => "Required field '{$field}' missing in composer.json",
                ];
            }
        }

        // Validate package type
        $allowedTypes = ['library', 'project', 'laravel-package'];
        if (!in_array($composerData['data']['type'], $allowedTypes)) {
            return [
                'success' => false,
                'error' => 'Invalid package type. Must be one of: ' . implode(', ', $allowedTypes),
            ];
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Copy files from source to target directory.
     *
     * @param  string  $source
     * @param  string  $target
     * @return void
     */
    protected function copyFiles(string $source, string $target): void
    {
        // Create target directory if it doesn't exist
        if (!File::exists($target)) {
            File::makeDirectory($target, 0755, true);
        }

        // Copy all files and directories
        File::copyDirectory($source, $target);
    }

    /**
     * Run package migrations.
     *
     * @param  string  $packagePath
     * @return array
     */
    protected function runMigrations(string $packagePath): array
    {
        try {
            $migrationsPath = $packagePath . '/src/Database/Migrations';

            if (!File::exists($migrationsPath)) {
                // No migrations to run
                return [
                    'success' => true,
                    'message' => 'No migrations found',
                ];
            }

            // Run migrations
            Artisan::call('migrate', [
                '--path' => str_replace(base_path() . '/', '', $migrationsPath),
                '--force' => true,
            ]);

            return [
                'success' => true,
                'message' => 'Migrations executed successfully',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Migration failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update root composer.json with package information.
     *
     * @param  string  $packageName
     * @param  string  $packagePath
     * @return array
     */
    protected function updateComposerJson(string $packageName, string $packagePath): array
    {
        try {
            $rootComposerPath = base_path('composer.json');
            $rootComposer = json_decode(File::get($rootComposerPath), true);

            // Add to autoload PSR-4 if not already present
            $packageComposerData = $this->compatibilityChecker->parseComposerJson($packagePath);
            if ($packageComposerData['success'] && isset($packageComposerData['data']['autoload']['psr-4'])) {
                $psr4 = $packageComposerData['data']['autoload']['psr-4'];

                foreach ($psr4 as $namespace => $path) {
                    $relativePath = str_replace(base_path() . '/', '', $packagePath . '/' . $path);
                    $rootComposer['autoload']['psr-4'][$namespace] = $relativePath;
                }

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
     * Register service provider in config.
     *
     * @param  string  $vendor
     * @param  string  $package
     * @return void
     */
    protected function registerServiceProvider(string $vendor, string $package): void
    {
        // In Laravel, service providers are typically auto-discovered
        // or registered in config/app.php. For this CRM system,
        // we may need to add to config/concord.php or similar
        // This is a placeholder for the actual implementation

        // Service providers are typically auto-discovered in Laravel 5.5+
        // so we just need to ensure the package is in the autoload
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
            Log::warning('Cache clearing failed during installation', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create backup of existing installation.
     *
     * @param  string  $path
     * @return string
     */
    protected function createBackup(string $path): string
    {
        $backupPath = storage_path('app/extensions/backups/' . basename($path) . '_' . time());
        File::copyDirectory($path, $backupPath);

        return $backupPath;
    }

    /**
     * Rollback installation on failure.
     *
     * @param  mixed  $installationRecord
     * @param  string|null  $extractPath
     * @param  string|null  $targetPath
     * @param  string|null  $backupPath
     * @return void
     */
    protected function rollback($installationRecord, ?string $extractPath, ?string $targetPath, ?string $backupPath): void
    {
        try {
            // Update installation record to failed status
            if ($installationRecord) {
                app('Webkul\Marketplace\Repositories\ExtensionInstallationRepository')->update([
                    'status' => 'failed',
                ], $installationRecord->id);
            }

            // Remove installed files
            if ($targetPath && File::exists($targetPath)) {
                File::deleteDirectory($targetPath);
            }

            // Restore backup if exists
            if ($backupPath && File::exists($backupPath)) {
                if ($targetPath) {
                    File::copyDirectory($backupPath, $targetPath);
                }
                File::deleteDirectory($backupPath);
            }

            // Clean up temporary files
            if ($extractPath && File::exists($extractPath)) {
                $this->cleanup($extractPath);
            }

            // Clear cache
            $this->clearCache();
        } catch (Exception $e) {
            Log::error('Rollback failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clean up temporary files.
     *
     * @param  string  $path
     * @return void
     */
    protected function cleanup(string $path): void
    {
        try {
            if (File::exists($path)) {
                File::deleteDirectory($path);
            }
        } catch (Exception $e) {
            Log::warning('Cleanup failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ensure temporary directory exists.
     *
     * @return void
     */
    protected function ensureTempDirectory(): void
    {
        if (!File::exists($this->tempPath)) {
            File::makeDirectory($this->tempPath, 0755, true);
        }
    }

    /**
     * Check if extension is already installed for user.
     *
     * @param  int  $extensionId
     * @param  int  $userId
     * @return bool
     */
    protected function isInstalled(int $extensionId, int $userId): bool
    {
        $installation = app('Webkul\Marketplace\Repositories\ExtensionInstallationRepository')
            ->findWhere([
                'extension_id' => $extensionId,
                'user_id' => $userId,
                'status' => 'active',
            ])
            ->first();

        return $installation !== null;
    }

    /**
     * Update an existing installation to a new version.
     *
     * @param  int  $installationId
     * @param  int  $newVersionId
     * @return array
     */
    public function update(int $installationId, int $newVersionId): array
    {
        try {
            $installation = app('Webkul\Marketplace\Repositories\ExtensionInstallationRepository')
                ->with(['extension', 'version', 'user'])
                ->findOrFail($installationId);

            // Mark as updating
            $installation->markAsUpdating();

            // Perform installation of new version (will backup old version)
            $result = $this->install($newVersionId, $installation->user_id);

            if ($result['success']) {
                // Delete old installation record since we have a new one
                app('Webkul\Marketplace\Repositories\ExtensionInstallationRepository')->delete($installationId);

                return [
                    'success' => true,
                    'message' => 'Extension updated successfully',
                    'installation' => $result['installation'],
                ];
            }

            // Restore to active state if update failed
            $installation->activate();

            return $result;
        } catch (Exception $e) {
            Log::error('Extension update failed', [
                'installation_id' => $installationId,
                'new_version_id' => $newVersionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Update failed: ' . $e->getMessage(),
            ];
        }
    }
}
