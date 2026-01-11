<?php

namespace Webkul\Marketplace\Http\Controllers\Developer;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionSubmissionRepository;
use Webkul\Marketplace\Services\RevenueCalculator;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionSubmissionRepository $submissionRepository,
        protected RevenueCalculator $revenueCalculator
    ) {}

    /**
     * Display the developer dashboard.
     */
    public function index(): View|JsonResponse
    {
        try {
            $statistics = $this->getDeveloperStatistics();

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data' => $statistics,
                ]);
            }

            return view('marketplace::developer.dashboard', compact('statistics'));
        } catch (\Exception $e) {
            Log::error('Failed to load developer dashboard: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Failed to load dashboard',
                ], 500);
            }

            // Return view with empty statistics
            $statistics = $this->getEmptyStatistics();

            return view('marketplace::developer.dashboard', compact('statistics'));
        }
    }

    /**
     * Get developer statistics for AJAX requests.
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->getDeveloperStatistics();

            return new JsonResponse([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get developer statistics: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to load statistics',
            ], 500);
        }
    }

    /**
     * Get developer statistics.
     *
     * @return array
     */
    protected function getDeveloperStatistics(): array
    {
        $userId = Auth::id();

        // Get all extensions by this developer
        $extensions = $this->extensionRepository->getByAuthor($userId);

        // Calculate total downloads
        $totalDownloads = $extensions->sum('downloads_count');

        // Count active (approved) extensions
        $activeExtensions = $extensions->where('status', 'approved')->count();

        // Count total extensions (all statuses)
        $totalExtensions = $extensions->count();

        // Count pending submissions
        $pendingReviews = $this->submissionRepository->scopeQuery(function ($query) use ($userId) {
            return $query->bySubmitter($userId)->pending();
        })->count();

        // Calculate total revenue
        $revenueData = $this->revenueCalculator->calculateSellerRevenue($userId);
        $totalRevenue = $revenueData['success'] ? $revenueData['total_revenue'] : 0;
        $totalTransactions = $revenueData['success'] ? $revenueData['total_transactions'] : 0;

        // Calculate average rating across all extensions
        $averageRating = $extensions->where('status', 'approved')->avg('average_rating') ?? 0;

        // Get recent activity counts
        $recentDownloads = $this->getRecentDownloads($extensions);
        $recentRevenue = $this->getRecentRevenue($userId);

        return [
            'total_downloads' => $totalDownloads,
            'total_revenue' => round($totalRevenue, 2),
            'active_extensions' => $activeExtensions,
            'pending_reviews' => $pendingReviews,
            'total_extensions' => $totalExtensions,
            'total_transactions' => $totalTransactions,
            'average_rating' => round($averageRating, 2),
            'recent_downloads' => $recentDownloads,
            'recent_revenue' => $recentRevenue,
            'draft_extensions' => $extensions->where('status', 'draft')->count(),
            'rejected_extensions' => $extensions->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Get recent downloads (last 30 days).
     *
     * @param  \Illuminate\Support\Collection  $extensions
     * @return int
     */
    protected function getRecentDownloads($extensions): int
    {
        try {
            $extensionIds = $extensions->pluck('id')->toArray();

            if (empty($extensionIds)) {
                return 0;
            }

            // Get downloads from the last 30 days by checking installation records
            $thirtyDaysAgo = now()->subDays(30);

            return \DB::table('extension_installations')
                ->whereIn('extension_id', $extensionIds)
                ->where('installed_at', '>=', $thirtyDaysAgo)
                ->count();
        } catch (\Exception $e) {
            Log::warning('Failed to get recent downloads: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get recent revenue (last 30 days).
     *
     * @param  int  $userId
     * @return float
     */
    protected function getRecentRevenue(int $userId): float
    {
        try {
            $thirtyDaysAgo = now()->subDays(30)->toDateString();
            $today = now()->toDateString();

            $revenueData = $this->revenueCalculator->calculateSellerRevenue(
                $userId,
                $thirtyDaysAgo,
                $today
            );

            return $revenueData['success'] ? round($revenueData['total_revenue'], 2) : 0;
        } catch (\Exception $e) {
            Log::warning('Failed to get recent revenue: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get empty statistics structure.
     *
     * @return array
     */
    protected function getEmptyStatistics(): array
    {
        return [
            'total_downloads' => 0,
            'total_revenue' => 0,
            'active_extensions' => 0,
            'pending_reviews' => 0,
            'total_extensions' => 0,
            'total_transactions' => 0,
            'average_rating' => 0,
            'recent_downloads' => 0,
            'recent_revenue' => 0,
            'draft_extensions' => 0,
            'rejected_extensions' => 0,
        ];
    }
}
