<?php

namespace Webkul\Marketplace\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionTransactionRepository;

class RevenueCalculator
{
    /**
     * Default platform fee percentage.
     */
    const DEFAULT_PLATFORM_FEE_PERCENTAGE = 30;

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected ExtensionTransactionRepository $transactionRepository,
        protected ExtensionRepository $extensionRepository
    ) {}

    /**
     * Calculate revenue split for a transaction amount.
     *
     * @param  float  $amount
     * @param  float|null  $platformFeePercentage
     * @return array
     */
    public function calculateRevenueSplit(float $amount, ?float $platformFeePercentage = null): array
    {
        if ($amount < 0) {
            return [
                'success' => false,
                'error' => 'Amount cannot be negative',
            ];
        }

        $platformFeePercentage = $platformFeePercentage ?? $this->getPlatformFeePercentage();

        if ($platformFeePercentage < 0 || $platformFeePercentage > 100) {
            return [
                'success' => false,
                'error' => 'Platform fee percentage must be between 0 and 100',
            ];
        }

        $platformFee = round(($amount * $platformFeePercentage) / 100, 2);
        $sellerRevenue = round($amount - $platformFee, 2);

        return [
            'success' => true,
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'platform_fee_percentage' => $platformFeePercentage,
            'seller_revenue' => $sellerRevenue,
            'seller_revenue_percentage' => 100 - $platformFeePercentage,
        ];
    }

    /**
     * Process a refund and adjust revenue accordingly.
     *
     * @param  int  $transactionId
     * @param  string|null  $reason
     * @return array
     */
    public function processRefund(int $transactionId, ?string $reason = null): array
    {
        try {
            $transaction = $this->transactionRepository->findOrFail($transactionId);

            if ($transaction->isRefunded()) {
                return [
                    'success' => false,
                    'error' => 'Transaction is already refunded',
                ];
            }

            if (!$transaction->isCompleted()) {
                return [
                    'success' => false,
                    'error' => 'Only completed transactions can be refunded',
                ];
            }

            DB::beginTransaction();

            try {
                // Create a refund transaction record
                $refundTransaction = $this->transactionRepository->create([
                    'transaction_id' => 'REFUND-' . $transaction->transaction_id,
                    'amount' => -$transaction->amount,
                    'platform_fee' => -$transaction->platform_fee,
                    'seller_revenue' => -$transaction->seller_revenue,
                    'payment_method' => $transaction->payment_method,
                    'status' => 'refunded',
                    'notes' => $reason ?? 'Refund processed',
                    'extension_id' => $transaction->extension_id,
                    'buyer_id' => $transaction->buyer_id,
                    'seller_id' => $transaction->seller_id,
                    'metadata' => [
                        'original_transaction_id' => $transaction->id,
                        'refund_date' => now()->toIso8601String(),
                    ],
                ]);

                // Mark original transaction as refunded
                $transaction->markAsRefunded($reason);

                DB::commit();

                return [
                    'success' => true,
                    'original_transaction' => $transaction,
                    'refund_transaction' => $refundTransaction,
                    'refund_amount' => abs($refundTransaction->amount),
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process refund: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate revenue report for a specific seller.
     *
     * @param  int  $sellerId
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @return array
     */
    public function generateSellerReport(int $sellerId, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $query = $this->transactionRepository
                ->forSeller($sellerId)
                ->completed();

            if ($startDate) {
                $query = $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query = $query->where('created_at', '<=', $endDate);
            }

            $transactions = $query->with(['extension', 'buyer'])->get();

            $totalRevenue = $transactions->sum('seller_revenue');
            $totalTransactions = $transactions->count();
            $totalGrossSales = $transactions->sum('amount');
            $totalPlatformFees = $transactions->sum('platform_fee');

            // Calculate refunds
            $refundQuery = $this->transactionRepository
                ->forSeller($sellerId)
                ->refunded();

            if ($startDate) {
                $refundQuery = $refundQuery->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $refundQuery = $refundQuery->where('created_at', '<=', $endDate);
            }

            $refunds = $refundQuery->get();
            $totalRefunds = abs($refunds->sum('amount'));
            $refundCount = $refunds->count();

            // Group by extension
            $byExtension = $transactions->groupBy('extension_id')->map(function ($extensionTransactions) {
                $extension = $extensionTransactions->first()->extension;

                return [
                    'extension_id' => $extension->id ?? null,
                    'extension_name' => $extension->name ?? 'Unknown',
                    'total_sales' => $extensionTransactions->count(),
                    'gross_revenue' => $extensionTransactions->sum('amount'),
                    'net_revenue' => $extensionTransactions->sum('seller_revenue'),
                    'platform_fees' => $extensionTransactions->sum('platform_fee'),
                ];
            })->values();

            // Group by month
            $byMonth = $transactions->groupBy(function ($transaction) {
                return $transaction->created_at->format('Y-m');
            })->map(function ($monthTransactions, $month) {
                return [
                    'month' => $month,
                    'total_sales' => $monthTransactions->count(),
                    'gross_revenue' => $monthTransactions->sum('amount'),
                    'net_revenue' => $monthTransactions->sum('seller_revenue'),
                    'platform_fees' => $monthTransactions->sum('platform_fee'),
                ];
            })->values();

            return [
                'success' => true,
                'seller_id' => $sellerId,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_transactions' => $totalTransactions,
                    'total_gross_sales' => round($totalGrossSales, 2),
                    'total_platform_fees' => round($totalPlatformFees, 2),
                    'total_revenue' => round($totalRevenue, 2),
                    'total_refunds' => round($totalRefunds, 2),
                    'refund_count' => $refundCount,
                    'net_revenue' => round($totalRevenue - $totalRefunds, 2),
                    'average_transaction' => $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 2) : 0,
                ],
                'by_extension' => $byExtension,
                'by_month' => $byMonth,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate seller revenue report', [
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate report: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate platform revenue report.
     *
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @return array
     */
    public function generatePlatformReport(?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $query = $this->transactionRepository->completed();

            if ($startDate) {
                $query = $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query = $query->where('created_at', '<=', $endDate);
            }

            $transactions = $query->with(['extension', 'seller', 'buyer'])->get();

            $totalPlatformFees = $transactions->sum('platform_fee');
            $totalTransactions = $transactions->count();
            $totalGrossSales = $transactions->sum('amount');
            $totalSellerRevenue = $transactions->sum('seller_revenue');

            // Calculate refunds
            $refundQuery = $this->transactionRepository->refunded();

            if ($startDate) {
                $refundQuery = $refundQuery->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $refundQuery = $refundQuery->where('created_at', '<=', $endDate);
            }

            $refunds = $refundQuery->get();
            $totalRefunds = abs($refunds->sum('amount'));
            $totalRefundedPlatformFees = abs($refunds->sum('platform_fee'));
            $refundCount = $refunds->count();

            // Group by payment method
            $byPaymentMethod = $transactions->groupBy('payment_method')->map(function ($methodTransactions, $method) {
                return [
                    'payment_method' => $method,
                    'total_transactions' => $methodTransactions->count(),
                    'gross_revenue' => $methodTransactions->sum('amount'),
                    'platform_fees' => $methodTransactions->sum('platform_fee'),
                ];
            })->values();

            // Top sellers
            $topSellers = $transactions->groupBy('seller_id')->map(function ($sellerTransactions) {
                $seller = $sellerTransactions->first()->seller;

                return [
                    'seller_id' => $seller->id ?? null,
                    'seller_name' => $seller->name ?? 'Unknown',
                    'total_sales' => $sellerTransactions->count(),
                    'gross_revenue' => $sellerTransactions->sum('amount'),
                    'platform_fees_generated' => $sellerTransactions->sum('platform_fee'),
                ];
            })->sortByDesc('platform_fees_generated')->take(10)->values();

            // Top extensions
            $topExtensions = $transactions->groupBy('extension_id')->map(function ($extensionTransactions) {
                $extension = $extensionTransactions->first()->extension;

                return [
                    'extension_id' => $extension->id ?? null,
                    'extension_name' => $extension->name ?? 'Unknown',
                    'total_sales' => $extensionTransactions->count(),
                    'gross_revenue' => $extensionTransactions->sum('amount'),
                    'platform_fees_generated' => $extensionTransactions->sum('platform_fee'),
                ];
            })->sortByDesc('gross_revenue')->take(10)->values();

            // Group by month
            $byMonth = $transactions->groupBy(function ($transaction) {
                return $transaction->created_at->format('Y-m');
            })->map(function ($monthTransactions, $month) {
                return [
                    'month' => $month,
                    'total_transactions' => $monthTransactions->count(),
                    'gross_revenue' => $monthTransactions->sum('amount'),
                    'platform_fees' => $monthTransactions->sum('platform_fee'),
                    'seller_revenue' => $monthTransactions->sum('seller_revenue'),
                ];
            })->values();

            return [
                'success' => true,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_transactions' => $totalTransactions,
                    'total_gross_sales' => round($totalGrossSales, 2),
                    'total_platform_fees' => round($totalPlatformFees, 2),
                    'total_seller_revenue' => round($totalSellerRevenue, 2),
                    'total_refunds' => round($totalRefunds, 2),
                    'refund_count' => $refundCount,
                    'net_platform_fees' => round($totalPlatformFees - $totalRefundedPlatformFees, 2),
                    'average_platform_fee' => $totalTransactions > 0 ? round($totalPlatformFees / $totalTransactions, 2) : 0,
                    'average_transaction' => $totalTransactions > 0 ? round($totalGrossSales / $totalTransactions, 2) : 0,
                ],
                'by_payment_method' => $byPaymentMethod,
                'top_sellers' => $topSellers,
                'top_extensions' => $topExtensions,
                'by_month' => $byMonth,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate platform revenue report', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate report: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate revenue report for a specific extension.
     *
     * @param  int  $extensionId
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @return array
     */
    public function generateExtensionReport(int $extensionId, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $extension = $this->extensionRepository->findOrFail($extensionId);

            $query = $this->transactionRepository
                ->forExtension($extensionId)
                ->completed();

            if ($startDate) {
                $query = $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query = $query->where('created_at', '<=', $endDate);
            }

            $transactions = $query->with(['buyer'])->get();

            $totalRevenue = $transactions->sum('amount');
            $totalTransactions = $transactions->count();
            $totalPlatformFees = $transactions->sum('platform_fee');
            $totalSellerRevenue = $transactions->sum('seller_revenue');

            // Calculate refunds
            $refundQuery = $this->transactionRepository
                ->forExtension($extensionId)
                ->refunded();

            if ($startDate) {
                $refundQuery = $refundQuery->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $refundQuery = $refundQuery->where('created_at', '<=', $endDate);
            }

            $refunds = $refundQuery->get();
            $totalRefunds = abs($refunds->sum('amount'));
            $refundCount = $refunds->count();

            // Group by month
            $byMonth = $transactions->groupBy(function ($transaction) {
                return $transaction->created_at->format('Y-m');
            })->map(function ($monthTransactions, $month) {
                return [
                    'month' => $month,
                    'total_sales' => $monthTransactions->count(),
                    'gross_revenue' => $monthTransactions->sum('amount'),
                    'platform_fees' => $monthTransactions->sum('platform_fee'),
                    'seller_revenue' => $monthTransactions->sum('seller_revenue'),
                ];
            })->values();

            return [
                'success' => true,
                'extension_id' => $extensionId,
                'extension_name' => $extension->name,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_transactions' => $totalTransactions,
                    'total_gross_revenue' => round($totalRevenue, 2),
                    'total_platform_fees' => round($totalPlatformFees, 2),
                    'total_seller_revenue' => round($totalSellerRevenue, 2),
                    'total_refunds' => round($totalRefunds, 2),
                    'refund_count' => $refundCount,
                    'net_revenue' => round($totalRevenue - $totalRefunds, 2),
                    'average_transaction' => $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 2) : 0,
                ],
                'by_month' => $byMonth,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate extension revenue report', [
                'extension_id' => $extensionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate report: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate total revenue for a seller.
     *
     * @param  int  $sellerId
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @return array
     */
    public function calculateSellerRevenue(int $sellerId, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $query = $this->transactionRepository
                ->forSeller($sellerId)
                ->completed();

            if ($startDate) {
                $query = $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query = $query->where('created_at', '<=', $endDate);
            }

            $totalRevenue = $query->sum('seller_revenue');
            $totalTransactions = $query->count();

            return [
                'success' => true,
                'seller_id' => $sellerId,
                'total_revenue' => round($totalRevenue, 2),
                'total_transactions' => $totalTransactions,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to calculate seller revenue: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate pending payouts for a seller.
     *
     * @param  int  $sellerId
     * @return array
     */
    public function calculatePendingPayouts(int $sellerId): array
    {
        try {
            // Get completed transactions that haven't been paid out yet
            // This would typically check against a payouts table, but for now
            // we'll assume all completed transactions are pending payout
            $pendingRevenue = $this->transactionRepository
                ->forSeller($sellerId)
                ->completed()
                ->sum('seller_revenue');

            $transactionCount = $this->transactionRepository
                ->forSeller($sellerId)
                ->completed()
                ->count();

            return [
                'success' => true,
                'seller_id' => $sellerId,
                'pending_revenue' => round($pendingRevenue, 2),
                'transaction_count' => $transactionCount,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to calculate pending payouts: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get the platform fee percentage from configuration.
     *
     * @return float
     */
    protected function getPlatformFeePercentage(): float
    {
        return config('marketplace.platform_fee_percentage', self::DEFAULT_PLATFORM_FEE_PERCENTAGE);
    }

    /**
     * Set custom platform fee percentage in configuration.
     *
     * @param  float  $percentage
     * @return bool
     */
    public function setPlatformFeePercentage(float $percentage): bool
    {
        if ($percentage < 0 || $percentage > 100) {
            return false;
        }

        config(['marketplace.platform_fee_percentage' => $percentage]);

        return true;
    }

    /**
     * Get revenue statistics summary.
     *
     * @return array
     */
    public function getRevenueStatistics(): array
    {
        try {
            $totalTransactions = $this->transactionRepository->completed()->count();
            $totalGrossRevenue = $this->transactionRepository->completed()->sum('amount');
            $totalPlatformFees = $this->transactionRepository->completed()->sum('platform_fee');
            $totalSellerRevenue = $this->transactionRepository->completed()->sum('seller_revenue');
            $totalRefunds = abs($this->transactionRepository->refunded()->sum('amount'));

            return [
                'success' => true,
                'statistics' => [
                    'total_transactions' => $totalTransactions,
                    'total_gross_revenue' => round($totalGrossRevenue, 2),
                    'total_platform_fees' => round($totalPlatformFees, 2),
                    'total_seller_revenue' => round($totalSellerRevenue, 2),
                    'total_refunds' => round($totalRefunds, 2),
                    'net_revenue' => round($totalGrossRevenue - $totalRefunds, 2),
                    'average_transaction' => $totalTransactions > 0 ? round($totalGrossRevenue / $totalTransactions, 2) : 0,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to get revenue statistics: ' . $e->getMessage(),
            ];
        }
    }
}
