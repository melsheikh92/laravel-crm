<?php

namespace Webkul\Marketplace\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class InstallationApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionInstallationRepository $installationRepository,
        protected ExtensionRepository $extensionRepository,
        protected ExtensionVersionRepository $versionRepository
    ) {}

    /**
     * Display a listing of user's installations.
     */
    public function index(): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();
            $perPage = request()->input('per_page', 15);

            $installations = $this->installationRepository
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->where('user_id', $userId)
                        ->with(['extension', 'version'])
                        ->orderBy('created_at', 'desc');
                })
                ->paginate($perPage);

            return new JsonResponse([
                'data' => $installations->items(),
                'meta' => [
                    'current_page' => $installations->currentPage(),
                    'from'         => $installations->firstItem(),
                    'to'           => $installations->lastItem(),
                    'per_page'     => $installations->perPage(),
                    'total'        => $installations->total(),
                    'last_page'    => $installations->lastPage(),
                ],
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve installations');
        }
    }

    /**
     * Display the specified installation.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $installation = $this->installationRepository
                ->with(['extension', 'version', 'user'])
                ->findOrFail($id);

            // Check if user owns the installation
            if ($installation->user_id !== $userId) {
                return new JsonResponse([
                    'message' => 'Unauthorized to view this installation',
                ], 403);
            }

            return new JsonResponse([
                'data' => $installation,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Installation not found', 404);
        }
    }

    /**
     * Install an extension.
     */
    public function install(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'extension_id' => 'required|integer|exists:extensions,id',
                'version_id'   => 'nullable|integer|exists:extension_versions,id',
            ]);

            $userId = auth()->guard('user')->id();
            $extensionId = request()->input('extension_id');
            $versionId = request()->input('version_id');

            // Check if extension exists and is approved
            $extension = $this->extensionRepository->findOrFail($extensionId);

            if ($extension->status !== 'approved') {
                return new JsonResponse([
                    'message' => 'Extension is not available for installation',
                ], 422);
            }

            // Check if already installed
            $existingInstallation = $this->installationRepository->findOneWhere([
                'user_id'      => $userId,
                'extension_id' => $extensionId,
            ]);

            if ($existingInstallation) {
                return new JsonResponse([
                    'message' => 'Extension is already installed',
                ], 422);
            }

            // If no version specified, get the latest compatible version
            if (! $versionId) {
                $latestVersion = $this->versionRepository->getLatestVersion($extensionId);

                if (! $latestVersion) {
                    return new JsonResponse([
                        'message' => 'No compatible version found for installation',
                    ], 422);
                }

                $versionId = $latestVersion->id;
            }

            $version = $this->versionRepository->findOrFail($versionId);

            // Verify version belongs to the extension
            if ($version->extension_id !== $extensionId) {
                return new JsonResponse([
                    'message' => 'Invalid version for the specified extension',
                ], 422);
            }

            Event::dispatch('marketplace.installation.install.before', [$extensionId, $userId]);

            $installation = $this->installationRepository->create([
                'user_id'            => $userId,
                'extension_id'       => $extensionId,
                'version_id'         => $versionId,
                'status'             => 'active',
                'installed_at'       => now(),
                'auto_update'        => request()->input('auto_update', false),
                'installation_notes' => request()->input('installation_notes'),
            ]);

            // Increment downloads count
            $extension->increment('downloads_count');
            $version->increment('downloads_count');

            Event::dispatch('marketplace.installation.install.after', $installation);

            return new JsonResponse([
                'data'    => $installation->load(['extension', 'version']),
                'message' => 'Extension installed successfully',
            ], 201);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to install extension');
        }
    }

    /**
     * Update the specified installation.
     */
    public function update(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $installation = $this->installationRepository->findOrFail($id);

            // Check if user owns the installation
            if ($installation->user_id !== $userId) {
                return new JsonResponse([
                    'message' => 'Unauthorized to update this installation',
                ], 403);
            }

            $this->validate(request(), [
                'version_id'         => 'nullable|integer|exists:extension_versions,id',
                'installation_notes' => 'nullable|string',
            ]);

            $data = request()->only(['version_id', 'installation_notes']);

            // If version is being changed, verify it belongs to the same extension
            if (! empty($data['version_id'])) {
                $newVersion = $this->versionRepository->findOrFail($data['version_id']);

                if ($newVersion->extension_id !== $installation->extension_id) {
                    return new JsonResponse([
                        'message' => 'Invalid version for this extension',
                    ], 422);
                }

                $data['updated_at'] = now();
            }

            Event::dispatch('marketplace.installation.update.before', $id);

            $installation = $this->installationRepository->update($data, $id);

            Event::dispatch('marketplace.installation.update.after', $installation);

            return new JsonResponse([
                'data'    => $installation->load(['extension', 'version']),
                'message' => 'Installation updated successfully',
            ], 200);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to update installation', 404);
        }
    }

    /**
     * Uninstall an extension.
     */
    public function uninstall(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $installation = $this->installationRepository->findOrFail($id);

            // Check if user owns the installation
            if ($installation->user_id !== $userId) {
                return new JsonResponse([
                    'message' => 'Unauthorized to uninstall this extension',
                ], 403);
            }

            Event::dispatch('marketplace.installation.uninstall.before', $id);

            $this->installationRepository->delete($id);

            Event::dispatch('marketplace.installation.uninstall.after', $id);

            return new JsonResponse([
                'message' => 'Extension uninstalled successfully',
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to uninstall extension', 404);
        }
    }

    /**
     * Enable the specified installation.
     */
    public function enable(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $installation = $this->installationRepository->findOrFail($id);

            // Check if user owns the installation
            if ($installation->user_id !== $userId) {
                return new JsonResponse([
                    'message' => 'Unauthorized to enable this installation',
                ], 403);
            }

            Event::dispatch('marketplace.installation.enable.before', $id);

            $installation = $this->installationRepository->update([
                'status' => 'active',
            ], $id);

            Event::dispatch('marketplace.installation.enable.after', $installation);

            return new JsonResponse([
                'data'    => $installation,
                'message' => 'Installation enabled successfully',
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to enable installation', 404);
        }
    }

    /**
     * Disable the specified installation.
     */
    public function disable(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $installation = $this->installationRepository->findOrFail($id);

            // Check if user owns the installation
            if ($installation->user_id !== $userId) {
                return new JsonResponse([
                    'message' => 'Unauthorized to disable this installation',
                ], 403);
            }

            Event::dispatch('marketplace.installation.disable.before', $id);

            $installation = $this->installationRepository->update([
                'status' => 'inactive',
            ], $id);

            Event::dispatch('marketplace.installation.disable.after', $installation);

            return new JsonResponse([
                'data'    => $installation,
                'message' => 'Installation disabled successfully',
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to disable installation', 404);
        }
    }

    /**
     * Toggle auto-update for the specified installation.
     */
    public function toggleAutoUpdate(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $installation = $this->installationRepository->findOrFail($id);

            // Check if user owns the installation
            if ($installation->user_id !== $userId) {
                return new JsonResponse([
                    'message' => 'Unauthorized to modify this installation',
                ], 403);
            }

            $installation = $this->installationRepository->update([
                'auto_update' => ! $installation->auto_update,
            ], $id);

            return new JsonResponse([
                'data'    => $installation,
                'message' => 'Auto-update toggled successfully',
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to toggle auto-update', 404);
        }
    }

    /**
     * Check for updates for all user installations.
     */
    public function checkUpdates(): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $installations = $this->installationRepository->findWhere([
                'user_id' => $userId,
                'status'  => 'active',
            ]);

            $updates = [];

            foreach ($installations as $installation) {
                $currentVersion = $installation->version->version;
                $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);

                if ($latestVersion && version_compare($latestVersion->version, $currentVersion, '>')) {
                    $updates[] = [
                        'installation_id'   => $installation->id,
                        'extension_id'      => $installation->extension_id,
                        'extension_name'    => $installation->extension->name,
                        'current_version'   => $currentVersion,
                        'latest_version'    => $latestVersion->version,
                        'latest_version_id' => $latestVersion->id,
                        'release_date'      => $latestVersion->release_date,
                        'changelog'         => $latestVersion->changelog,
                    ];
                }
            }

            return new JsonResponse([
                'data' => [
                    'updates_available' => count($updates),
                    'updates'           => $updates,
                ],
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to check for updates');
        }
    }

    /**
     * Get available updates for user installations.
     */
    public function availableUpdates(): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $installations = $this->installationRepository->findWhere([
                'user_id' => $userId,
                'status'  => 'active',
            ]);

            $updates = [];

            foreach ($installations as $installation) {
                $currentVersion = $installation->version->version;
                $hasUpdate = $this->versionRepository->hasNewerVersion(
                    $installation->extension_id,
                    $currentVersion
                );

                if ($hasUpdate) {
                    $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);

                    $updates[] = [
                        'installation_id' => $installation->id,
                        'extension_id'    => $installation->extension_id,
                        'extension_name'  => $installation->extension->name,
                        'current_version' => $currentVersion,
                        'latest_version'  => $latestVersion->version,
                        'auto_update'     => $installation->auto_update,
                    ];
                }
            }

            return new JsonResponse([
                'data' => $updates,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to get available updates');
        }
    }

    /**
     * Handle exceptions and return formatted JSON response.
     */
    protected function handleException(Exception $exception, string $defaultMessage = 'An error occurred', int $defaultCode = 500): JsonResponse
    {
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return new JsonResponse([
                'message' => $defaultMessage,
            ], 404);
        }

        return new JsonResponse([
            'message' => $defaultMessage,
            'error'   => config('app.debug') ? $exception->getMessage() : null,
        ], $defaultCode);
    }
}
