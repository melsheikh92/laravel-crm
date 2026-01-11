<?php

namespace Webkul\Marketplace\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class VersionApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionVersionRepository $versionRepository
    ) {}

    /**
     * Display the specified version.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            return new JsonResponse([
                'data' => $version,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Version not found', 404);
        }
    }

    /**
     * Check compatibility for the specified version.
     */
    public function checkCompatibility(int $id): JsonResponse
    {
        try {
            $version = $this->versionRepository->findOrFail($id);

            $laravelVersion = request()->input('laravel_version');
            $crmVersion = request()->input('crm_version');
            $phpVersion = request()->input('php_version');

            $isCompatible = $version->isCompatibleWith(
                $laravelVersion,
                $crmVersion,
                $phpVersion
            );

            return new JsonResponse([
                'data' => [
                    'version_id'      => $version->id,
                    'version'         => $version->version,
                    'is_compatible'   => $isCompatible,
                    'requirements'    => [
                        'laravel_version' => $version->laravel_version,
                        'crm_version'     => $version->crm_version,
                        'php_version'     => $version->php_version,
                    ],
                    'provided'        => [
                        'laravel_version' => $laravelVersion,
                        'crm_version'     => $crmVersion,
                        'php_version'     => $phpVersion,
                    ],
                ],
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to check compatibility', 404);
        }
    }

    /**
     * Get changelog for the specified version.
     */
    public function changelog(int $id): JsonResponse
    {
        try {
            $version = $this->versionRepository->findOrFail($id);

            return new JsonResponse([
                'data' => [
                    'version_id' => $version->id,
                    'version'    => $version->version,
                    'changelog'  => $version->changelog,
                ],
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Version not found', 404);
        }
    }

    /**
     * Store a newly created version.
     */
    public function store(int $extension_id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($extension_id);

            // Check if user owns the extension
            if ($extension->author_id !== auth()->guard('user')->id()) {
                return new JsonResponse([
                    'message' => 'Unauthorized to create version for this extension',
                ], 403);
            }

            $this->validate(request(), [
                'version'         => 'required|string|max:50',
                'changelog'       => 'nullable|string',
                'download_url'    => 'nullable|url',
                'file_size'       => 'nullable|integer|min:0',
                'laravel_version' => 'nullable|string|max:50',
                'crm_version'     => 'nullable|string|max:50',
                'php_version'     => 'nullable|string|max:50',
                'dependencies'    => 'nullable|json',
                'release_notes'   => 'nullable|string',
                'release_date'    => 'nullable|date',
            ]);

            // Check if version already exists
            if ($this->versionRepository->versionExists($extension_id, request()->input('version'))) {
                return new JsonResponse([
                    'message' => 'Version already exists for this extension',
                ], 422);
            }

            $data = request()->all();
            $data['extension_id'] = $extension_id;
            $data['status'] = 'pending';

            Event::dispatch('marketplace.version.create.before');

            $version = $this->versionRepository->create($data);

            Event::dispatch('marketplace.version.create.after', $version);

            return new JsonResponse([
                'data'    => $version,
                'message' => 'Version created successfully',
            ], 201);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to create version');
        }
    }

    /**
     * Update the specified version.
     */
    public function update(int $id): JsonResponse
    {
        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            // Check if user owns the extension
            if ($version->extension->author_id !== auth()->guard('user')->id()) {
                return new JsonResponse([
                    'message' => 'Unauthorized to update this version',
                ], 403);
            }

            $this->validate(request(), [
                'version'         => 'required|string|max:50',
                'changelog'       => 'nullable|string',
                'download_url'    => 'nullable|url',
                'file_size'       => 'nullable|integer|min:0',
                'laravel_version' => 'nullable|string|max:50',
                'crm_version'     => 'nullable|string|max:50',
                'php_version'     => 'nullable|string|max:50',
                'dependencies'    => 'nullable|json',
                'release_notes'   => 'nullable|string',
                'release_date'    => 'nullable|date',
            ]);

            // Check if version number is being changed and already exists
            $newVersion = request()->input('version');
            if ($newVersion !== $version->version &&
                $this->versionRepository->versionExists($version->extension_id, $newVersion, $id)) {
                return new JsonResponse([
                    'message' => 'Version already exists for this extension',
                ], 422);
            }

            Event::dispatch('marketplace.version.update.before', $id);

            $version = $this->versionRepository->update(request()->all(), $id);

            Event::dispatch('marketplace.version.update.after', $version);

            return new JsonResponse([
                'data'    => $version,
                'message' => 'Version updated successfully',
            ], 200);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to update version', 404);
        }
    }

    /**
     * Remove the specified version.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            // Check if user owns the extension
            if ($version->extension->author_id !== auth()->guard('user')->id()) {
                return new JsonResponse([
                    'message' => 'Unauthorized to delete this version',
                ], 403);
            }

            Event::dispatch('marketplace.version.delete.before', $id);

            $this->versionRepository->delete($id);

            Event::dispatch('marketplace.version.delete.after', $id);

            return new JsonResponse([
                'message' => 'Version deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to delete version', 404);
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
