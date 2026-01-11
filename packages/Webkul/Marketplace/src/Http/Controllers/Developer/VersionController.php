<?php

namespace Webkul\Marketplace\Http\Controllers\Developer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class VersionController extends Controller
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
     * Display a listing of versions for an extension.
     */
    public function index(int $extensionId): View|JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($extensionId);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            $versions = $this->versionRepository
                ->getByExtension($extensionId, false);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'extension' => $extension,
                        'versions'  => $versions,
                    ],
                ]);
            }

            return view('marketplace::developer.versions.index', compact('extension', 'versions'));
        } catch (\Exception $e) {
            Log::error('Failed to load extension versions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.load-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.versions.load-failed'));
        }
    }

    /**
     * Show the form for creating a new version.
     */
    public function create(int $extensionId): View|JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($extensionId);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success'   => true,
                    'extension' => $extension,
                ]);
            }

            return view('marketplace::developer.versions.create', compact('extension'));
        } catch (\Exception $e) {
            Log::error('Failed to load create version form: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.extension-not-found'),
                ], 404);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.versions.extension-not-found'));
        }
    }

    /**
     * Store a newly created version in storage.
     */
    public function store(int $extensionId): JsonResponse
    {
        $this->validate(request(), [
            'version'        => 'required|string|regex:/^\d+\.\d+\.\d+$/',
            'changelog'      => 'nullable|string',
            'laravel_version' => 'nullable|string|max:50',
            'crm_version'    => 'nullable|string|max:50',
            'php_version'    => 'nullable|string|max:50',
            'dependencies'   => 'nullable|array',
            'release_date'   => 'nullable|date',
        ]);

        try {
            $extension = $this->extensionRepository->findOrFail($extensionId);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            // Check if version already exists
            if ($this->versionRepository->versionExists($extensionId, request()->input('version'))) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.version-exists'),
                ], 400);
            }

            $data = request()->all();

            // Set extension_id
            $data['extension_id'] = $extensionId;

            // Set status to pending by default
            $data['status'] = 'pending';

            Event::dispatch('marketplace.version.create.before');

            $version = $this->versionRepository->create($data);

            Event::dispatch('marketplace.version.create.after', $version);

            return new JsonResponse([
                'success' => true,
                'data'    => $version,
                'message' => trans('marketplace::app.developer.versions.create-success'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create version: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.versions.create-failed'),
            ], 500);
        }
    }

    /**
     * Display the specified version.
     */
    public function show(int $id): View|JsonResponse
    {
        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            // Ensure the version belongs to an extension owned by the current user
            if ($version->extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $version,
                ]);
            }

            return view('marketplace::developer.versions.show', compact('version'));
        } catch (\Exception $e) {
            Log::error('Failed to load version: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.not-found'),
                ], 404);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.versions.not-found'));
        }
    }

    /**
     * Show the form for editing the specified version.
     */
    public function edit(int $id): View|JsonResponse
    {
        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            // Ensure the version belongs to an extension owned by the current user
            if ($version->extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            // Don't allow editing approved versions
            if ($version->status === 'approved') {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.edit-approved-error'),
                ], 400);
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $version,
                ]);
            }

            return view('marketplace::developer.versions.edit', compact('version'));
        } catch (\Exception $e) {
            Log::error('Failed to load version for editing: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.not-found'),
                ], 404);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.versions.not-found'));
        }
    }

    /**
     * Update the specified version in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'version'         => 'required|string|regex:/^\d+\.\d+\.\d+$/',
            'changelog'       => 'nullable|string',
            'laravel_version' => 'nullable|string|max:50',
            'crm_version'     => 'nullable|string|max:50',
            'php_version'     => 'nullable|string|max:50',
            'dependencies'    => 'nullable|array',
            'release_date'    => 'nullable|date',
        ]);

        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            // Ensure the version belongs to an extension owned by the current user
            if ($version->extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            // Don't allow editing approved versions
            if ($version->status === 'approved') {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.edit-approved-error'),
                ], 400);
            }

            // Check if version number is being changed and already exists
            if (request()->input('version') !== $version->version) {
                if ($this->versionRepository->versionExists(
                    $version->extension_id,
                    request()->input('version'),
                    $id
                )) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => trans('marketplace::app.developer.versions.version-exists'),
                    ], 400);
                }
            }

            $data = request()->all();

            Event::dispatch('marketplace.version.update.before', $id);

            $version = $this->versionRepository->update($data, $id);

            Event::dispatch('marketplace.version.update.after', $version);

            return new JsonResponse([
                'success' => true,
                'data'    => $version,
                'message' => trans('marketplace::app.developer.versions.update-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update version: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.versions.update-failed'),
            ], 500);
        }
    }

    /**
     * Remove the specified version from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            // Ensure the version belongs to an extension owned by the current user
            if ($version->extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            // Don't allow deletion of approved versions
            if ($version->status === 'approved') {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.delete-approved-error'),
                ], 400);
            }

            Event::dispatch('marketplace.version.delete.before', $id);

            // Delete package file if exists
            if ($version->file_path) {
                $this->deleteFile($version->file_path);
            }

            $this->versionRepository->delete($id);

            Event::dispatch('marketplace.version.delete.after', $id);

            return new JsonResponse([
                'success' => true,
                'message' => trans('marketplace::app.developer.versions.delete-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete version: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.versions.delete-failed'),
            ], 500);
        }
    }

    /**
     * Upload package file for the version.
     */
    public function uploadPackage(int $id): JsonResponse
    {
        $this->validate(request(), [
            'package' => 'required|file|mimes:zip|max:51200',
        ]);

        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            // Ensure the version belongs to an extension owned by the current user
            if ($version->extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            // Don't allow uploading to approved versions
            if ($version->status === 'approved') {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.upload-approved-error'),
                ], 400);
            }

            // Delete old package if exists
            if ($version->file_path) {
                $this->deleteFile($version->file_path);
            }

            $file = request()->file('package');

            // Upload new package
            $filePath = $this->uploadFile($file, 'packages');

            // Calculate file size and checksum
            $fileSize = $file->getSize();
            $checksum = md5_file($file->getRealPath());

            Event::dispatch('marketplace.version.package.upload.before', $version);

            $version = $this->versionRepository->update([
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'checksum'  => $checksum,
            ], $id);

            Event::dispatch('marketplace.version.package.upload.after', $version);

            return new JsonResponse([
                'success' => true,
                'data'    => $version,
                'message' => trans('marketplace::app.developer.versions.package-upload-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload package: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.versions.package-upload-failed'),
            ], 500);
        }
    }

    /**
     * Download package file for the version.
     */
    public function downloadPackage(int $id): BinaryFileResponse|JsonResponse
    {
        try {
            $version = $this->versionRepository
                ->with(['extension'])
                ->findOrFail($id);

            // Ensure the version belongs to an extension owned by the current user
            if ($version->extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.unauthorized'),
                ], 403);
            }

            // Check if package file exists
            if (!$version->file_path) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.package-not-found'),
                ], 404);
            }

            // Check if file exists in storage
            if (!Storage::disk('public')->exists($version->file_path)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.versions.package-not-found'),
                ], 404);
            }

            $filePath = Storage::disk('public')->path($version->file_path);
            $fileName = $version->extension->slug . '-' . $version->version . '.zip';

            return response()->download($filePath, $fileName);
        } catch (\Exception $e) {
            Log::error('Failed to download package: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.versions.package-download-failed'),
            ], 500);
        }
    }

    /**
     * Upload a file to storage.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder
     * @return string
     */
    protected function uploadFile(UploadedFile $file, string $folder): string
    {
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = "marketplace/{$folder}/" . $filename;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Delete a file from storage.
     *
     * @param  string  $path
     * @return bool
     */
    protected function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}
