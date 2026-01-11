<?php

namespace Webkul\Marketplace\Http\Controllers\Marketplace;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class MyExtensionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionInstallationRepository $installationRepository,
        protected ExtensionVersionRepository $versionRepository
    ) {}

    /**
     * Display user's installed extensions.
     */
    public function index(): View|JsonResponse
    {
        try {
            $userId = Auth::id();

            // Get all installations for the current user with relationships
            $installations = $this->installationRepository
                ->with(['extension', 'version', 'extension.category'])
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->where('user_id', $userId)
                        ->orderBy('installed_at', 'desc');
                })
                ->paginate(request()->get('per_page', 15));

            // Get installations grouped by status
            $groupedInstallations = $this->getInstallationsGroupedByStatus($userId);

            // Get count of available updates
            $updatesAvailable = $this->getUpdatesAvailableCount($userId);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'installations'          => $installations,
                        'grouped_installations'  => $groupedInstallations,
                        'updates_available'      => $updatesAvailable,
                    ],
                ]);
            }

            return view('marketplace::marketplace.my-extensions.index', compact(
                'installations',
                'groupedInstallations',
                'updatesAvailable'
            ));
        } catch (Exception $e) {
            Log::error('Failed to load user extensions', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.my-extensions.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.my-extensions.load-failed'));
        }
    }

    /**
     * Display details of a specific installation.
     */
    public function show(int $installation_id): View|JsonResponse
    {
        try {
            $installation = $this->installationRepository
                ->with(['extension', 'version', 'extension.category', 'extension.author'])
                ->findOrFail($installation_id);

            // Verify ownership
            if ($installation->user_id !== Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.my-extensions.unauthorized'),
                    403
                );
            }

            // Check if update is available
            $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);
            $updateAvailable = false;

            if ($latestVersion && $installation->version) {
                $updateAvailable = version_compare(
                    $latestVersion->version,
                    $installation->version->version,
                    '>'
                );
            }

            // Get version history for this extension
            $versionHistory = $this->versionRepository->getByExtension($installation->extension_id, true);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'installation'      => $installation,
                        'update_available'  => $updateAvailable,
                        'latest_version'    => $latestVersion,
                        'version_history'   => $versionHistory,
                    ],
                ]);
            }

            return view('marketplace::marketplace.my-extensions.show', compact(
                'installation',
                'updateAvailable',
                'latestVersion',
                'versionHistory'
            ));
        } catch (Exception $e) {
            Log::error('Failed to load extension installation details', [
                'installation_id' => $installation_id,
                'user_id'         => Auth::id(),
                'error'           => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.my-extensions.load-failed'),
                500
            );
        }
    }

    /**
     * Check for updates on all installed extensions.
     */
    public function checkUpdates(): JsonResponse
    {
        try {
            $userId = Auth::id();

            // Get all active installations for the user
            $installations = $this->installationRepository
                ->with(['extension', 'version'])
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->where('user_id', $userId)
                        ->whereIn('status', ['active', 'inactive']);
                })
                ->all();

            $updatesAvailable = [];
            $upToDate = [];

            foreach ($installations as $installation) {
                $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);

                if (!$latestVersion || !$installation->version) {
                    continue;
                }

                $hasUpdate = version_compare(
                    $latestVersion->version,
                    $installation->version->version,
                    '>'
                );

                if ($hasUpdate) {
                    $updatesAvailable[] = [
                        'installation_id'    => $installation->id,
                        'extension_id'       => $installation->extension_id,
                        'extension_name'     => $installation->extension->name,
                        'current_version'    => $installation->version->version,
                        'latest_version'     => $latestVersion->version,
                        'release_date'       => $latestVersion->release_date,
                        'changelog'          => $latestVersion->changelog,
                    ];
                } else {
                    $upToDate[] = [
                        'installation_id'    => $installation->id,
                        'extension_id'       => $installation->extension_id,
                        'extension_name'     => $installation->extension->name,
                        'current_version'    => $installation->version->version,
                    ];
                }
            }

            return new JsonResponse([
                'success' => true,
                'data'    => [
                    'total_installations'  => $installations->count(),
                    'updates_available'    => $updatesAvailable,
                    'up_to_date'           => $upToDate,
                    'update_count'         => count($updatesAvailable),
                    'checked_at'           => now()->toIso8601String(),
                ],
                'message' => count($updatesAvailable) > 0
                    ? trans('marketplace::app.marketplace.my-extensions.updates-found', ['count' => count($updatesAvailable)])
                    : trans('marketplace::app.marketplace.my-extensions.all-up-to-date'),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to check for updates', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.marketplace.my-extensions.check-updates-failed'),
            ], 500);
        }
    }

    /**
     * Get installations grouped by status.
     */
    public function byStatus(string $status): View|JsonResponse
    {
        try {
            $userId = Auth::id();

            // Validate status
            if (!in_array($status, ['active', 'inactive', 'failed', 'updating', 'uninstalling'])) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.my-extensions.invalid-status'),
                    400
                );
            }

            $installations = $this->installationRepository
                ->with(['extension', 'version', 'extension.category'])
                ->scopeQuery(function ($query) use ($userId, $status) {
                    return $query->where('user_id', $userId)
                        ->where('status', $status)
                        ->orderBy('installed_at', 'desc');
                })
                ->paginate(request()->get('per_page', 15));

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'status'        => $status,
                        'installations' => $installations,
                        'total'         => $installations->total(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.my-extensions.by-status', compact('installations', 'status'));
        } catch (Exception $e) {
            Log::error('Failed to load extensions by status', [
                'user_id' => Auth::id(),
                'status'  => $status,
                'error'   => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.my-extensions.load-failed'),
                500
            );
        }
    }

    /**
     * Get installations with available updates.
     */
    public function withUpdates(): View|JsonResponse
    {
        try {
            $userId = Auth::id();

            // Get all active installations
            $installations = $this->installationRepository
                ->with(['extension', 'version'])
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->where('user_id', $userId)
                        ->whereIn('status', ['active', 'inactive']);
                })
                ->all();

            $installationsWithUpdates = collect();

            foreach ($installations as $installation) {
                $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);

                if (!$latestVersion || !$installation->version) {
                    continue;
                }

                $hasUpdate = version_compare(
                    $latestVersion->version,
                    $installation->version->version,
                    '>'
                );

                if ($hasUpdate) {
                    $installation->latest_available_version = $latestVersion;
                    $installationsWithUpdates->push($installation);
                }
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'installations' => $installationsWithUpdates,
                        'total'         => $installationsWithUpdates->count(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.my-extensions.updates', compact('installationsWithUpdates'));
        } catch (Exception $e) {
            Log::error('Failed to load extensions with updates', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.my-extensions.load-failed'),
                500
            );
        }
    }

    /**
     * Get statistics for user's extensions.
     */
    public function statistics(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $installations = $this->installationRepository
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })
                ->all();

            $groupedByStatus = $installations->groupBy('status');
            $groupedByAutoUpdate = $installations->groupBy('auto_update_enabled');

            // Count updates available
            $updatesCount = 0;
            foreach ($installations as $installation) {
                if ($installation->needsUpdate()) {
                    $updatesCount++;
                }
            }

            return new JsonResponse([
                'success' => true,
                'data'    => [
                    'total_installations'     => $installations->count(),
                    'active_installations'    => $groupedByStatus->get('active', collect())->count(),
                    'inactive_installations'  => $groupedByStatus->get('inactive', collect())->count(),
                    'failed_installations'    => $groupedByStatus->get('failed', collect())->count(),
                    'auto_update_enabled'     => $groupedByAutoUpdate->get(true, collect())->count(),
                    'auto_update_disabled'    => $groupedByAutoUpdate->get(false, collect())->count(),
                    'updates_available'       => $updatesCount,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get extension statistics', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.marketplace.my-extensions.statistics-failed'),
            ], 500);
        }
    }

    /**
     * Get installations grouped by status for the user.
     *
     * @param  int  $userId
     * @return array
     */
    protected function getInstallationsGroupedByStatus(int $userId): array
    {
        $installations = $this->installationRepository
            ->scopeQuery(function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->all();

        $grouped = $installations->groupBy('status');

        return [
            'active'        => $grouped->get('active', collect())->count(),
            'inactive'      => $grouped->get('inactive', collect())->count(),
            'failed'        => $grouped->get('failed', collect())->count(),
            'updating'      => $grouped->get('updating', collect())->count(),
            'uninstalling'  => $grouped->get('uninstalling', collect())->count(),
        ];
    }

    /**
     * Get count of installations with available updates.
     *
     * @param  int  $userId
     * @return int
     */
    protected function getUpdatesAvailableCount(int $userId): int
    {
        $installations = $this->installationRepository
            ->with(['version'])
            ->scopeQuery(function ($query) use ($userId) {
                return $query->where('user_id', $userId)
                    ->whereIn('status', ['active', 'inactive']);
            })
            ->all();

        $count = 0;
        foreach ($installations as $installation) {
            if (!$installation->version) {
                continue;
            }

            $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);

            if ($latestVersion && version_compare($latestVersion->version, $installation->version->version, '>')) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Return error response based on request type.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @return JsonResponse|RedirectResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (request()->ajax()) {
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], $statusCode);
        }

        return redirect()->back()
            ->with('error', $message);
    }
}
