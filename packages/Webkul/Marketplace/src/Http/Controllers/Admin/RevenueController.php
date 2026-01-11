<?php

namespace Webkul\Marketplace\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\DataGrids\TransactionDataGrid;
use Webkul\Marketplace\Repositories\ExtensionTransactionRepository;
use Webkul\Marketplace\Services\RevenueCalculator;

class RevenueController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionTransactionRepository $transactionRepository,
        protected RevenueCalculator $revenueCalculator
    ) {}

    /**
     * Display the revenue dashboard.
     */
    public function index(): View|JsonResponse
    {
        try {
            $statistics = $this->revenueCalculator->getRevenueStatistics();

            if (! $statistics['success']) {
                if (request()->ajax()) {
                    return new JsonResponse([
                        'error' => $statistics['error'] ?? 'Failed to load statistics',
                    ], 500);
                }

                $statistics = [
                    'total_revenue' => 0,
                    'platform_revenue' => 0,
                    'seller_revenue' => 0,
                    'total_transactions' => 0,
                ];
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => $statistics,
                ]);
            }

            return view('marketplace::admin.revenue.index', compact('statistics'));
        } catch (\Exception $e) {
            Log::error('Revenue dashboard error: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'error' => trans('marketplace::app.admin.revenue.index.load-failed'),
                ], 500);
            }

            return view('marketplace::admin.revenue.index', [
                'statistics' => [
                    'total_revenue' => 0,
                    'platform_revenue' => 0,
                    'seller_revenue' => 0,
                    'total_transactions' => 0,
                ],
            ]);
        }
    }

    /**
     * Display a listing of transactions.
     */
    public function transactions(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(TransactionDataGrid::class)->process();
        }

        return view('marketplace::admin.revenue.transactions');
    }

    /**
     * Display the specified transaction.
     */
    public function show(int $id): View|JsonResponse
    {
        try {
            $transaction = $this->transactionRepository
                ->with(['extension', 'buyer', 'seller'])
                ->findOrFail($id);

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => $transaction,
                ]);
            }

            return view('marketplace::admin.revenue.show', compact('transaction'));
        } catch (\Exception $e) {
            Log::error('Transaction detail error: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'error' => trans('marketplace::app.admin.revenue.transaction-not-found'),
                ], 404);
            }

            abort(404);
        }
    }

    /**
     * Generate platform revenue report.
     */
    public function platformReport(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $startDate = request('start_date');
            $endDate = request('end_date');

            $report = $this->revenueCalculator->getPlatformRevenueReport($startDate, $endDate);

            if (! $report['success']) {
                return new JsonResponse([
                    'error' => $report['error'] ?? 'Failed to generate report',
                ], 500);
            }

            return new JsonResponse([
                'data' => $report,
                'message' => trans('marketplace::app.admin.revenue.report-generated'),
            ]);
        } catch (\Exception $e) {
            Log::error('Platform report error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => trans('marketplace::app.admin.revenue.report-failed'),
            ], 500);
        }
    }

    /**
     * Generate seller-specific revenue report.
     */
    public function sellerReport(int $sellerId): JsonResponse
    {
        try {
            $this->validate(request(), [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $startDate = request('start_date');
            $endDate = request('end_date');

            $report = $this->revenueCalculator->getSellerRevenueReport($sellerId, $startDate, $endDate);

            if (! $report['success']) {
                return new JsonResponse([
                    'error' => $report['error'] ?? 'Failed to generate seller report',
                ], 500);
            }

            return new JsonResponse([
                'data' => $report,
                'message' => trans('marketplace::app.admin.revenue.report-generated'),
            ]);
        } catch (\Exception $e) {
            Log::error('Seller report error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => trans('marketplace::app.admin.revenue.report-failed'),
            ], 500);
        }
    }

    /**
     * Generate extension-specific revenue report.
     */
    public function extensionReport(int $extensionId): JsonResponse
    {
        try {
            $this->validate(request(), [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $startDate = request('start_date');
            $endDate = request('end_date');

            $report = $this->revenueCalculator->getExtensionRevenueReport($extensionId, $startDate, $endDate);

            if (! $report['success']) {
                return new JsonResponse([
                    'error' => $report['error'] ?? 'Failed to generate extension report',
                ], 500);
            }

            return new JsonResponse([
                'data' => $report,
                'message' => trans('marketplace::app.admin.revenue.report-generated'),
            ]);
        } catch (\Exception $e) {
            Log::error('Extension report error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => trans('marketplace::app.admin.revenue.report-failed'),
            ], 500);
        }
    }

    /**
     * Process a refund for a transaction.
     */
    public function processRefund(int $id): JsonResponse
    {
        try {
            $this->validate(request(), [
                'reason' => 'nullable|string|max:500',
            ]);

            $reason = request('reason');

            Event::dispatch('marketplace.transaction.refund.before', $id);

            $result = $this->revenueCalculator->processRefund($id, $reason);

            if (! $result['success']) {
                return new JsonResponse([
                    'error' => $result['error'] ?? 'Failed to process refund',
                ], 400);
            }

            Event::dispatch('marketplace.transaction.refund.after', $result['refund_transaction']);

            return new JsonResponse([
                'data' => $result,
                'message' => trans('marketplace::app.admin.revenue.refund-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Refund processing error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => trans('marketplace::app.admin.revenue.refund-failed'),
            ], 500);
        }
    }

    /**
     * Get revenue statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $startDate = request('start_date');
            $endDate = request('end_date');

            $statistics = $this->revenueCalculator->getRevenueStatistics($startDate, $endDate);

            if (! $statistics['success']) {
                return new JsonResponse([
                    'error' => $statistics['error'] ?? 'Failed to get statistics',
                ], 500);
            }

            return new JsonResponse([
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Revenue statistics error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => trans('marketplace::app.admin.revenue.statistics-failed'),
            ], 500);
        }
    }

    /**
     * Get top sellers.
     */
    public function topSellers(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'limit' => 'nullable|integer|min:1|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $limit = request('limit', 10);
            $startDate = request('start_date');
            $endDate = request('end_date');

            $platformReport = $this->revenueCalculator->getPlatformRevenueReport($startDate, $endDate);

            if (! $platformReport['success']) {
                return new JsonResponse([
                    'error' => $platformReport['error'] ?? 'Failed to get top sellers',
                ], 500);
            }

            $topSellers = collect($platformReport['top_sellers'])->take($limit);

            return new JsonResponse([
                'data' => $topSellers,
            ]);
        } catch (\Exception $e) {
            Log::error('Top sellers error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => trans('marketplace::app.admin.revenue.top-sellers-failed'),
            ], 500);
        }
    }

    /**
     * Get top extensions by revenue.
     */
    public function topExtensions(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'limit' => 'nullable|integer|min:1|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $limit = request('limit', 10);
            $startDate = request('start_date');
            $endDate = request('end_date');

            $platformReport = $this->revenueCalculator->getPlatformRevenueReport($startDate, $endDate);

            if (! $platformReport['success']) {
                return new JsonResponse([
                    'error' => $platformReport['error'] ?? 'Failed to get top extensions',
                ], 500);
            }

            $topExtensions = collect($platformReport['top_extensions'])->take($limit);

            return new JsonResponse([
                'data' => $topExtensions,
            ]);
        } catch (\Exception $e) {
            Log::error('Top extensions error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => trans('marketplace::app.admin.revenue.top-extensions-failed'),
            ], 500);
        }
    }

    /**
     * Update revenue sharing settings.
     */
    public function updateSettings(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'platform_fee_percentage' => 'required|numeric|min:0|max:100',
            ]);

            $platformFeePercentage = request('platform_fee_percentage');

            Event::dispatch('marketplace.revenue.settings.update.before');

            // Update the config file
            $configPath = config_path('marketplace.php');

            if (file_exists($configPath)) {
                $config = include $configPath;
                $config['platform_fee_percentage'] = $platformFeePercentage;

                $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
                file_put_contents($configPath, $content);

                // Clear config cache
                \Illuminate\Support\Facades\Artisan::call('config:clear');
            }

            Event::dispatch('marketplace.revenue.settings.update.after');

            return new JsonResponse([
                'data' => [
                    'platform_fee_percentage' => $platformFeePercentage,
                ],
                'message' => trans('marketplace::app.admin.revenue.settings-updated'),
            ]);
        } catch (\Exception $e) {
            Log::error('Revenue settings update error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => trans('marketplace::app.admin.revenue.settings-update-failed'),
            ], 500);
        }
    }
}
