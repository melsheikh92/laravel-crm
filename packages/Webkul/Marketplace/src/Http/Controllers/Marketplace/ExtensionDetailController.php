<?php

namespace Webkul\Marketplace\Http\Controllers\Marketplace;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionReviewRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;

class ExtensionDetailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionReviewRepository $reviewRepository,
        protected ExtensionVersionRepository $versionRepository,
        protected ExtensionInstallationRepository $installationRepository
    ) {}

    /**
     * Display the extension detail page.
     */
    public function show(string $slug): View|JsonResponse
    {
        try {
            // Find extension by slug
            $extension = $this->extensionRepository->findBySlug($slug);

            if (!$extension) {
                if (request()->ajax()) {
                    return new JsonResponse([
                        'message' => trans('marketplace::app.marketplace.detail.not-found'),
                    ], 404);
                }

                return redirect()->route('marketplace.browse.index')
                    ->with('error', trans('marketplace::app.marketplace.detail.not-found'));
            }

            // Only show approved extensions to marketplace users
            if ($extension->status !== 'approved') {
                if (request()->ajax()) {
                    return new JsonResponse([
                        'message' => trans('marketplace::app.marketplace.detail.not-available'),
                    ], 403);
                }

                return redirect()->route('marketplace.browse.index')
                    ->with('error', trans('marketplace::app.marketplace.detail.not-available'));
            }

            // Get version history
            $versions = $this->versionRepository->getByExtension($extension->id, true);

            // Get latest version
            $latestVersion = $this->versionRepository->getLatestVersion($extension->id);

            // Get reviews with pagination
            $perPage = request()->get('reviews_per_page', 10);
            $reviews = $this->reviewRepository->getByExtension($extension->id, true, $perPage);

            // Get review statistics
            $reviewStatistics = $this->reviewRepository->getStatistics($extension->id);

            // Get compatibility info from latest version
            $compatibilityInfo = $latestVersion ? [
                'laravel_version' => $latestVersion->laravel_version,
                'crm_version'     => $latestVersion->crm_version,
                'php_version'     => $latestVersion->php_version,
                'dependencies'    => $latestVersion->dependencies,
            ] : null;

            // Check if user has installed this extension
            $isInstalled = false;
            $userInstallation = null;

            if (Auth::check()) {
                $userInstallation = $this->installationRepository->findOneWhere([
                    'extension_id' => $extension->id,
                    'user_id'      => Auth::id(),
                ]);
                $isInstalled = $userInstallation !== null;
            }

            // Check if user has already reviewed
            $userReview = null;
            $hasReviewed = false;

            if (Auth::check()) {
                $userReview = $this->reviewRepository->getUserReview(Auth::id(), $extension->id);
                $hasReviewed = $userReview !== null;
            }

            // Check compatibility with current system
            $isCompatible = false;
            $compatibilityIssues = [];

            if ($latestVersion) {
                $systemInfo = $this->getSystemInfo();
                $isCompatible = $latestVersion->isCompatibleWith(
                    $systemInfo['laravel_version'],
                    $systemInfo['crm_version'],
                    $systemInfo['php_version']
                );

                if (!$isCompatible) {
                    $compatibilityIssues = $this->getCompatibilityIssues(
                        $latestVersion,
                        $systemInfo
                    );
                }
            }

            // Get related extensions (same category)
            $relatedExtensions = $this->extensionRepository
                ->scopeQuery(function ($query) use ($extension) {
                    return $query->approved()
                        ->where('category_id', $extension->category_id)
                        ->where('id', '!=', $extension->id)
                        ->with(['author', 'category', 'versions'])
                        ->orderBy('downloads_count', 'desc')
                        ->limit(6);
                })
                ->all();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extension'           => $extension,
                        'versions'            => $versions,
                        'latest_version'      => $latestVersion,
                        'reviews'             => $reviews,
                        'review_statistics'   => $reviewStatistics,
                        'compatibility_info'  => $compatibilityInfo,
                        'is_installed'        => $isInstalled,
                        'user_installation'   => $userInstallation,
                        'has_reviewed'        => $hasReviewed,
                        'user_review'         => $userReview,
                        'is_compatible'       => $isCompatible,
                        'compatibility_issues' => $compatibilityIssues,
                        'related_extensions'  => $relatedExtensions,
                    ],
                ]);
            }

            return view('marketplace::marketplace.detail', compact(
                'extension',
                'versions',
                'latestVersion',
                'reviews',
                'reviewStatistics',
                'compatibilityInfo',
                'isInstalled',
                'userInstallation',
                'hasReviewed',
                'userReview',
                'isCompatible',
                'compatibilityIssues',
                'relatedExtensions'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load extension detail page: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.detail.load-failed'),
                ], 500);
            }

            return redirect()->route('marketplace.browse.index')
                ->with('error', trans('marketplace::app.marketplace.detail.load-failed'));
        }
    }

    /**
     * Get version history with changelogs.
     */
    public function versions(string $slug): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findBySlug($slug);

            if (!$extension || $extension->status !== 'approved') {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.detail.not-found'),
                ], 404);
            }

            $limit = request()->get('limit', 10);
            $versions = $this->versionRepository->getChangelogHistory($extension->id, $limit);

            return new JsonResponse([
                'data' => [
                    'versions' => $versions,
                    'total'    => $versions->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load version history: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.marketplace.detail.versions-load-failed'),
            ], 500);
        }
    }

    /**
     * Get reviews with filtering options.
     */
    public function reviews(string $slug): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findBySlug($slug);

            if (!$extension || $extension->status !== 'approved') {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.detail.not-found'),
                ], 404);
            }

            $filters = [
                'extension_id'      => $extension->id,
                'rating'            => request()->get('rating'),
                'verified_purchase' => request()->get('verified_purchase'),
                'sort_by'           => request()->get('sort_by', 'created_at'),
                'sort_order'        => request()->get('sort_order', 'desc'),
            ];

            $perPage = request()->get('per_page', 10);
            $reviews = $this->reviewRepository->filter($filters, $perPage);

            return new JsonResponse([
                'data' => [
                    'reviews' => $reviews,
                    'total'   => $reviews->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load reviews: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.marketplace.detail.reviews-load-failed'),
            ], 500);
        }
    }

    /**
     * Get compatibility information for current system.
     */
    public function compatibility(string $slug): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findBySlug($slug);

            if (!$extension || $extension->status !== 'approved') {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.detail.not-found'),
                ], 404);
            }

            $systemInfo = $this->getSystemInfo();
            $latestVersion = $this->versionRepository->getLatestVersion($extension->id);

            if (!$latestVersion) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.detail.no-version-available'),
                ], 404);
            }

            $isCompatible = $latestVersion->isCompatibleWith(
                $systemInfo['laravel_version'],
                $systemInfo['crm_version'],
                $systemInfo['php_version']
            );

            $compatibilityIssues = [];
            if (!$isCompatible) {
                $compatibilityIssues = $this->getCompatibilityIssues($latestVersion, $systemInfo);
            }

            // Get compatible versions
            $compatibleVersions = $this->versionRepository->getCompatibleVersions(
                $extension->id,
                $systemInfo['laravel_version'],
                $systemInfo['crm_version'],
                $systemInfo['php_version']
            );

            return new JsonResponse([
                'data' => [
                    'system_info'          => $systemInfo,
                    'latest_version'       => $latestVersion,
                    'is_compatible'        => $isCompatible,
                    'compatibility_issues' => $compatibilityIssues,
                    'compatible_versions'  => $compatibleVersions,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check compatibility: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.marketplace.detail.compatibility-check-failed'),
            ], 500);
        }
    }

    /**
     * Get installation status for current user.
     */
    public function installationStatus(string $slug): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.detail.login-required'),
                ], 401);
            }

            $extension = $this->extensionRepository->findBySlug($slug);

            if (!$extension || $extension->status !== 'approved') {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.detail.not-found'),
                ], 404);
            }

            $installation = $this->installationRepository->findOneWhere([
                'extension_id' => $extension->id,
                'user_id'      => Auth::id(),
            ]);

            $isInstalled = $installation !== null;
            $canUpdate = false;
            $latestVersion = null;

            if ($isInstalled && $installation->version) {
                $latestVersion = $this->versionRepository->getLatestVersion($extension->id);
                if ($latestVersion) {
                    $canUpdate = version_compare($latestVersion->version, $installation->version, '>');
                }
            }

            return new JsonResponse([
                'data' => [
                    'is_installed'     => $isInstalled,
                    'installation'     => $installation,
                    'can_update'       => $canUpdate,
                    'latest_version'   => $latestVersion,
                    'current_version'  => $installation?->version,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check installation status: ' . $e->getMessage());

            return new JsonResponse([
                'message' => trans('marketplace::app.marketplace.detail.installation-status-failed'),
            ], 500);
        }
    }

    /**
     * Get system information for compatibility checking.
     *
     * @return array
     */
    protected function getSystemInfo(): array
    {
        return [
            'laravel_version' => app()->version(),
            'crm_version'     => config('app.version', '1.0.0'),
            'php_version'     => PHP_VERSION,
        ];
    }

    /**
     * Get compatibility issues between extension version and system.
     *
     * @param  mixed  $version
     * @param  array  $systemInfo
     * @return array
     */
    protected function getCompatibilityIssues($version, array $systemInfo): array
    {
        $issues = [];

        // Check Laravel version compatibility
        if ($version->laravel_version && !$this->versionMatches($systemInfo['laravel_version'], $version->laravel_version)) {
            $issues[] = [
                'type'     => 'laravel',
                'required' => $version->laravel_version,
                'current'  => $systemInfo['laravel_version'],
                'message'  => trans('marketplace::app.marketplace.detail.incompatible-laravel', [
                    'required' => $version->laravel_version,
                    'current'  => $systemInfo['laravel_version'],
                ]),
            ];
        }

        // Check CRM version compatibility
        if ($version->crm_version && !$this->versionMatches($systemInfo['crm_version'], $version->crm_version)) {
            $issues[] = [
                'type'     => 'crm',
                'required' => $version->crm_version,
                'current'  => $systemInfo['crm_version'],
                'message'  => trans('marketplace::app.marketplace.detail.incompatible-crm', [
                    'required' => $version->crm_version,
                    'current'  => $systemInfo['crm_version'],
                ]),
            ];
        }

        // Check PHP version compatibility
        if ($version->php_version && !$this->versionMatches($systemInfo['php_version'], $version->php_version)) {
            $issues[] = [
                'type'     => 'php',
                'required' => $version->php_version,
                'current'  => $systemInfo['php_version'],
                'message'  => trans('marketplace::app.marketplace.detail.incompatible-php', [
                    'required' => $version->php_version,
                    'current'  => $systemInfo['php_version'],
                ]),
            ];
        }

        return $issues;
    }

    /**
     * Check if version matches requirement.
     *
     * @param  string  $current
     * @param  string  $required
     * @return bool
     */
    protected function versionMatches(string $current, string $required): bool
    {
        // Handle version constraints like "^8.0", ">=7.4", "~5.8"
        if (preg_match('/^[\^~><=]+/', $required)) {
            // For complex version constraints, we'd need a proper constraint parser
            // For now, just do basic comparison
            $cleanRequired = preg_replace('/^[\^~><=]+/', '', $required);
            return version_compare($current, $cleanRequired, '>=');
        }

        // Exact version or wildcard
        return version_compare($current, $required, '>=');
    }
}
