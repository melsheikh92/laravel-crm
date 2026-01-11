<?php

namespace Webkul\Marketplace\Http\Controllers\Developer;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionTransactionRepository;
use Webkul\Marketplace\Services\RevenueCalculator;

class EarningsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionTransactionRepository $transactionRepository,
        protected ExtensionRepository $extensionRepository,
        protected RevenueCalculator $revenueCalculator
    ) {}

    /**
     * Display the earnings dashboard.
     */
    public function index(): View|JsonResponse
    {
        try {
            $userId = Auth::id();
            $statistics = $this->getEarningsStatistics($userId);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $statistics,
                ]);
            }

            return view('marketplace::developer.earnings.index', compact('statistics'));
        } catch (\Exception $e) {
            Log::error('Failed to load earnings dashboard: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.load-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.earnings.load-failed'));
        }
    }

    /**
     * Display a listing of transactions.
     */
    public function transactions(): View|JsonResponse
    {
        try {
            $userId = Auth::id();

            $transactions = $this->transactionRepository
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->forSeller($userId)
                        ->with(['extension', 'buyer'])
                        ->orderBy('created_at', 'desc');
                })
                ->paginate(15);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $transactions,
                ]);
            }

            return view('marketplace::developer.earnings.transactions', compact('transactions'));
        } catch (\Exception $e) {
            Log::error('Failed to load transactions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.transactions-load-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.earnings.transactions-load-failed'));
        }
    }

    /**
     * Display the specified transaction.
     */
    public function showTransaction(int $id): View|JsonResponse
    {
        try {
            $transaction = $this->transactionRepository
                ->with(['extension', 'buyer', 'seller'])
                ->findOrFail($id);

            // Ensure the transaction belongs to the current user
            if ($transaction->seller_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.unauthorized'),
                ], 403);
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $transaction,
                ]);
            }

            return view('marketplace::developer.earnings.transaction-detail', compact('transaction'));
        } catch (\Exception $e) {
            Log::error('Failed to load transaction: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.transaction-not-found'),
                ], 404);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.earnings.transaction-not-found'));
        }
    }

    /**
     * Generate revenue reports.
     */
    public function reports(): View|JsonResponse
    {
        try {
            $userId = Auth::id();
            $startDate = request()->input('start_date');
            $endDate = request()->input('end_date');

            $report = $this->revenueCalculator->generateSellerReport($userId, $startDate, $endDate);

            if (!$report['success']) {
                throw new \Exception($report['error'] ?? 'Failed to generate report');
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $report,
                ]);
            }

            return view('marketplace::developer.earnings.reports', compact('report'));
        } catch (\Exception $e) {
            Log::error('Failed to generate revenue report: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.report-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.earnings.report-failed'));
        }
    }

    /**
     * Get earnings statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $statistics = $this->getEarningsStatistics($userId);

            return new JsonResponse([
                'success' => true,
                'data'    => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get earnings statistics: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.earnings.statistics-failed'),
            ], 500);
        }
    }

    /**
     * Display payout history.
     */
    public function payoutHistory(): View|JsonResponse
    {
        try {
            $userId = Auth::id();

            // For now, we'll show all completed transactions as payout history
            // In a real implementation, this would query a payouts table
            $payouts = $this->transactionRepository
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->forSeller($userId)
                        ->completed()
                        ->with(['extension'])
                        ->orderBy('created_at', 'desc');
                })
                ->paginate(15);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $payouts,
                ]);
            }

            return view('marketplace::developer.earnings.payout-history', compact('payouts'));
        } catch (\Exception $e) {
            Log::error('Failed to load payout history: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.payout-history-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.earnings.payout-history-failed'));
        }
    }

    /**
     * Request a payout/withdrawal.
     */
    public function requestPayout(): JsonResponse
    {
        $this->validate(request(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $userId = Auth::id();
            $amount = request()->input('amount');

            // Calculate pending payouts
            $pendingPayouts = $this->revenueCalculator->calculatePendingPayouts($userId);

            if (!$pendingPayouts['success']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.payout-calculation-failed'),
                ], 400);
            }

            // Check if requested amount is available
            if ($amount > $pendingPayouts['pending_revenue']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.insufficient-balance'),
                ], 400);
            }

            // Check minimum payout amount
            $minimumPayout = config('marketplace.minimum_payout_amount', 50);
            if ($amount < $minimumPayout) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.minimum-payout-not-met', [
                        'amount' => $minimumPayout,
                    ]),
                ], 400);
            }

            Event::dispatch('marketplace.payout.request.before');

            // In a real implementation, this would create a payout request record
            // For now, we'll just log the request and return success
            $payoutRequest = [
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => request()->input('payment_method'),
                'notes' => request()->input('notes'),
                'status' => 'pending',
                'requested_at' => now(),
            ];

            Log::info('Payout request created', $payoutRequest);

            Event::dispatch('marketplace.payout.request.after', $payoutRequest);

            return new JsonResponse([
                'success' => true,
                'data'    => $payoutRequest,
                'message' => trans('marketplace::app.developer.earnings.payout-request-success'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to request payout: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.earnings.payout-request-failed'),
            ], 500);
        }
    }

    /**
     * Get earnings for a specific extension.
     */
    public function byExtension(int $extensionId): View|JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($extensionId);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.unauthorized'),
                ], 403);
            }

            $startDate = request()->input('start_date');
            $endDate = request()->input('end_date');

            $report = $this->revenueCalculator->generateExtensionReport($extensionId, $startDate, $endDate);

            if (!$report['success']) {
                throw new \Exception($report['error'] ?? 'Failed to generate extension report');
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'extension' => $extension,
                        'report'    => $report,
                    ],
                ]);
            }

            return view('marketplace::developer.earnings.by-extension', compact('extension', 'report'));
        } catch (\Exception $e) {
            Log::error('Failed to get extension earnings: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.earnings.extension-report-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.earnings.extension-report-failed'));
        }
    }

    /**
     * Get earnings statistics for the developer.
     *
     * @param  int  $userId
     * @return array
     */
    protected function getEarningsStatistics(int $userId): array
    {
        try {
            // Calculate total revenue
            $revenueData = $this->revenueCalculator->calculateSellerRevenue($userId);
            $totalRevenue = $revenueData['success'] ? $revenueData['total_revenue'] : 0;
            $totalTransactions = $revenueData['success'] ? $revenueData['total_transactions'] : 0;

            // Calculate pending payouts
            $pendingPayouts = $this->revenueCalculator->calculatePendingPayouts($userId);
            $pendingRevenue = $pendingPayouts['success'] ? $pendingPayouts['pending_revenue'] : 0;

            // Calculate recent revenue (last 30 days)
            $thirtyDaysAgo = now()->subDays(30)->toDateString();
            $today = now()->toDateString();
            $recentRevenueData = $this->revenueCalculator->calculateSellerRevenue($userId, $thirtyDaysAgo, $today);
            $recentRevenue = $recentRevenueData['success'] ? $recentRevenueData['total_revenue'] : 0;
            $recentTransactions = $recentRevenueData['success'] ? $recentRevenueData['total_transactions'] : 0;

            // Get transaction count by status
            $completedCount = $this->transactionRepository->forSeller($userId)->completed()->count();
            $refundedCount = $this->transactionRepository->forSeller($userId)->refunded()->count();

            // Calculate average transaction value
            $averageTransaction = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 2) : 0;

            // Get top extension by revenue
            $topExtension = $this->getTopExtensionByRevenue($userId);

            return [
                'total_revenue' => round($totalRevenue, 2),
                'total_transactions' => $totalTransactions,
                'pending_revenue' => round($pendingRevenue, 2),
                'recent_revenue' => round($recentRevenue, 2),
                'recent_transactions' => $recentTransactions,
                'completed_transactions' => $completedCount,
                'refunded_transactions' => $refundedCount,
                'average_transaction' => $averageTransaction,
                'top_extension' => $topExtension,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get earnings statistics: ' . $e->getMessage());

            return $this->getEmptyStatistics();
        }
    }

    /**
     * Get the top extension by revenue for the developer.
     *
     * @param  int  $userId
     * @return array|null
     */
    protected function getTopExtensionByRevenue(int $userId): ?array
    {
        try {
            $topExtension = \DB::table('extension_transactions')
                ->join('extensions', 'extension_transactions.extension_id', '=', 'extensions.id')
                ->where('extension_transactions.seller_id', $userId)
                ->where('extension_transactions.status', 'completed')
                ->select(
                    'extensions.id',
                    'extensions.name',
                    \DB::raw('SUM(extension_transactions.seller_revenue) as total_revenue'),
                    \DB::raw('COUNT(extension_transactions.id) as total_sales')
                )
                ->groupBy('extensions.id', 'extensions.name')
                ->orderByDesc('total_revenue')
                ->first();

            if (!$topExtension) {
                return null;
            }

            return [
                'id' => $topExtension->id,
                'name' => $topExtension->name,
                'total_revenue' => round($topExtension->total_revenue, 2),
                'total_sales' => $topExtension->total_sales,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get top extension by revenue: ' . $e->getMessage());
            return null;
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
            'total_revenue' => 0,
            'total_transactions' => 0,
            'pending_revenue' => 0,
            'recent_revenue' => 0,
            'recent_transactions' => 0,
            'completed_transactions' => 0,
            'refunded_transactions' => 0,
            'average_transaction' => 0,
            'top_extension' => null,
        ];
    }
}
