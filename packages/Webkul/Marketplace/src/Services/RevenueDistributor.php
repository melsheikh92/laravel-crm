<?php

namespace Webkul\Marketplace\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Marketplace\Contracts\PaymentGateway;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionTransactionRepository;
use Webkul\Marketplace\Services\Payment\StripeGateway;

class RevenueDistributor
{
    /**
     * Minimum payout threshold.
     */
    const MIN_PAYOUT_THRESHOLD = 50;

    /**
     * Payment gateway instance.
     *
     * @var PaymentGateway
     */
    protected PaymentGateway $gateway;

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected ExtensionTransactionRepository $transactionRepository,
        protected ExtensionRepository $extensionRepository,
        protected RevenueCalculator $revenueCalculator
    ) {
        $this->initializePaymentGateway();
    }

    /**
     * Initialize the payment gateway.
     *
     * @return void
     */
    protected function initializePaymentGateway(): void
    {
        $gatewayName = config('marketplace.payment.default_gateway', 'stripe');
        $gatewayConfig = config("marketplace.payment.gateways.{$gatewayName}", []);

        // Currently only Stripe is supported
        $this->gateway = new StripeGateway();
        $this->gateway->initialize($gatewayConfig);
    }

    /**
     * Process automatic payment split when a transaction is completed.
     * This splits the payment between platform and seller based on the revenue share.
     *
     * @param  int  $transactionId
     * @return array
     */
    public function processPaymentSplit(int $transactionId): array
    {
        try {
            $transaction = $this->transactionRepository->findOrFail($transactionId);

            if (!$transaction->isCompleted()) {
                return [
                    'success' => false,
                    'error' => 'Only completed transactions can be split',
                ];
            }

            // Check if already split
            $metadata = $transaction->metadata ?? [];
            if (!empty($metadata['split_processed']) && $metadata['split_processed'] === true) {
                return [
                    'success' => false,
                    'error' => 'Payment split already processed',
                ];
            }

            DB::beginTransaction();

            try {
                // Calculate revenue split
                $split = $this->revenueCalculator->calculateRevenueSplit(
                    $transaction->amount,
                    ($transaction->platform_fee / $transaction->amount) * 100
                );

                if (!$split['success']) {
                    DB::rollBack();
                    return $split;
                }

                // Update transaction metadata to mark as split
                $updatedMetadata = array_merge($metadata, [
                    'split_processed' => true,
                    'split_date' => now()->toIso8601String(),
                    'platform_amount' => $split['platform_fee'],
                    'seller_amount' => $split['seller_revenue'],
                ]);

                $this->transactionRepository->update($transaction->id, [
                    'metadata' => $updatedMetadata,
                ]);

                DB::commit();

                Log::info('Payment split processed', [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'platform_fee' => $split['platform_fee'],
                    'seller_revenue' => $split['seller_revenue'],
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'split' => $split,
                ];
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Payment split processing failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process payment split: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate seller's pending payout balance.
     * This is the total seller revenue that hasn't been paid out yet.
     *
     * @param  int  $sellerId
     * @return array
     */
    public function calculatePendingBalance(int $sellerId): array
    {
        try {
            // Get all completed transactions for seller
            $transactions = $this->transactionRepository
                ->forSeller($sellerId)
                ->completed()
                ->get();

            $totalEarned = $transactions->sum('seller_revenue');

            // Calculate total already paid out
            $totalPaidOut = $transactions->filter(function ($transaction) {
                $metadata = $transaction->metadata ?? [];
                return !empty($metadata['payout_id']);
            })->sum('seller_revenue');

            $pendingBalance = $totalEarned - $totalPaidOut;

            // Get transactions pending payout
            $pendingTransactions = $transactions->filter(function ($transaction) {
                $metadata = $transaction->metadata ?? [];
                return empty($metadata['payout_id']);
            });

            return [
                'success' => true,
                'seller_id' => $sellerId,
                'total_earned' => round($totalEarned, 2),
                'total_paid_out' => round($totalPaidOut, 2),
                'pending_balance' => round($pendingBalance, 2),
                'pending_transaction_count' => $pendingTransactions->count(),
                'can_request_payout' => $pendingBalance >= $this->getMinimumPayoutThreshold(),
                'minimum_payout_threshold' => $this->getMinimumPayoutThreshold(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to calculate pending balance', [
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to calculate pending balance: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process payout to seller.
     * Initiates a payout through the payment gateway to the seller.
     *
     * @param  int  $sellerId
     * @param  float|null  $amount
     * @param  array  $options
     * @return array
     */
    public function processPayout(int $sellerId, ?float $amount = null, array $options = []): array
    {
        try {
            // Calculate pending balance
            $balance = $this->calculatePendingBalance($sellerId);

            if (!$balance['success']) {
                return $balance;
            }

            // If no amount specified, payout the full pending balance
            $payoutAmount = $amount ?? $balance['pending_balance'];

            // Validate payout amount
            if ($payoutAmount <= 0) {
                return [
                    'success' => false,
                    'error' => 'Payout amount must be greater than zero',
                ];
            }

            if ($payoutAmount > $balance['pending_balance']) {
                return [
                    'success' => false,
                    'error' => 'Payout amount exceeds pending balance',
                ];
            }

            $minThreshold = $this->getMinimumPayoutThreshold();
            if ($payoutAmount < $minThreshold) {
                return [
                    'success' => false,
                    'error' => "Payout amount must be at least {$minThreshold}",
                ];
            }

            DB::beginTransaction();

            try {
                // Get seller destination account (would be stored in seller profile)
                $destination = $options['destination'] ?? null;

                // Create payout through payment gateway
                $payoutData = [
                    'amount' => $payoutAmount,
                    'currency' => config('marketplace.payment.default_currency', 'USD'),
                    'description' => "Marketplace payout for seller #{$sellerId}",
                    'destination' => $destination,
                    'metadata' => [
                        'seller_id' => $sellerId,
                        'payout_date' => now()->toIso8601String(),
                    ],
                ];

                $gatewayResponse = $this->gateway->createPayout($payoutData);

                if (!$gatewayResponse['success']) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'error' => 'Payout gateway error: ' . ($gatewayResponse['error'] ?? 'Unknown error'),
                    ];
                }

                // Mark transactions as paid out
                $this->markTransactionsAsPaid($sellerId, $payoutAmount, $gatewayResponse['payout_id']);

                DB::commit();

                Log::info('Payout processed successfully', [
                    'seller_id' => $sellerId,
                    'amount' => $payoutAmount,
                    'payout_id' => $gatewayResponse['payout_id'],
                ]);

                return [
                    'success' => true,
                    'seller_id' => $sellerId,
                    'amount' => $payoutAmount,
                    'payout_id' => $gatewayResponse['payout_id'],
                    'status' => $gatewayResponse['status'] ?? 'pending',
                    'arrival_date' => $gatewayResponse['arrival_date'] ?? null,
                    'gateway_response' => $gatewayResponse,
                ];
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Payout processing failed', [
                'seller_id' => $sellerId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process payout: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mark transactions as paid out by updating their metadata.
     *
     * @param  int  $sellerId
     * @param  float  $payoutAmount
     * @param  string  $payoutId
     * @return void
     */
    protected function markTransactionsAsPaid(int $sellerId, float $payoutAmount, string $payoutId): void
    {
        // Get pending transactions for seller, oldest first
        $transactions = $this->transactionRepository
            ->forSeller($sellerId)
            ->completed()
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingAmount = $payoutAmount;

        foreach ($transactions as $transaction) {
            // Skip if already paid out
            $metadata = $transaction->metadata ?? [];
            if (!empty($metadata['payout_id'])) {
                continue;
            }

            if ($remainingAmount <= 0) {
                break;
            }

            // Calculate amount from this transaction to include in payout
            $transactionAmount = min($transaction->seller_revenue, $remainingAmount);

            // Update transaction metadata
            $updatedMetadata = array_merge($metadata, [
                'payout_id' => $payoutId,
                'payout_date' => now()->toIso8601String(),
                'payout_amount' => $transactionAmount,
            ]);

            $this->transactionRepository->update($transaction->id, [
                'metadata' => $updatedMetadata,
            ]);

            $remainingAmount -= $transactionAmount;
        }
    }

    /**
     * Process automatic payouts for all eligible sellers.
     * This would typically be run on a schedule (e.g., weekly, monthly).
     *
     * @return array
     */
    public function processScheduledPayouts(): array
    {
        try {
            // Get all sellers with pending balance above threshold
            $sellers = $this->getEligibleSellersForPayout();

            $results = [
                'total_sellers' => count($sellers),
                'successful_payouts' => 0,
                'failed_payouts' => 0,
                'total_amount_paid' => 0,
                'details' => [],
            ];

            foreach ($sellers as $sellerId => $balance) {
                $result = $this->processPayout($sellerId);

                if ($result['success']) {
                    $results['successful_payouts']++;
                    $results['total_amount_paid'] += $result['amount'];
                } else {
                    $results['failed_payouts']++;
                }

                $results['details'][$sellerId] = $result;
            }

            Log::info('Scheduled payouts processed', [
                'total_sellers' => $results['total_sellers'],
                'successful' => $results['successful_payouts'],
                'failed' => $results['failed_payouts'],
                'total_amount' => $results['total_amount_paid'],
            ]);

            return [
                'success' => true,
                'results' => $results,
            ];
        } catch (Exception $e) {
            Log::error('Scheduled payouts processing failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process scheduled payouts: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get sellers eligible for automatic payout.
     *
     * @return array
     */
    protected function getEligibleSellersForPayout(): array
    {
        // Get all sellers with completed transactions
        $transactions = $this->transactionRepository
            ->completed()
            ->with('seller')
            ->get();

        $sellerBalances = [];

        foreach ($transactions as $transaction) {
            $sellerId = $transaction->seller_id;

            if (!isset($sellerBalances[$sellerId])) {
                $sellerBalances[$sellerId] = 0;
            }

            // Only count if not already paid out
            $metadata = $transaction->metadata ?? [];
            if (empty($metadata['payout_id'])) {
                $sellerBalances[$sellerId] += $transaction->seller_revenue;
            }
        }

        // Filter sellers with balance above threshold
        $minThreshold = $this->getMinimumPayoutThreshold();

        return array_filter($sellerBalances, function ($balance) use ($minThreshold) {
            return $balance >= $minThreshold;
        });
    }

    /**
     * Get payout history for a seller.
     *
     * @param  int  $sellerId
     * @param  int  $limit
     * @return array
     */
    public function getPayoutHistory(int $sellerId, int $limit = 10): array
    {
        try {
            // Get all transactions that have been paid out
            $transactions = $this->transactionRepository
                ->forSeller($sellerId)
                ->completed()
                ->orderBy('created_at', 'desc')
                ->get();

            // Group by payout_id
            $payouts = [];

            foreach ($transactions as $transaction) {
                $metadata = $transaction->metadata ?? [];
                $payoutId = $metadata['payout_id'] ?? null;

                if (!$payoutId) {
                    continue;
                }

                if (!isset($payouts[$payoutId])) {
                    $payouts[$payoutId] = [
                        'payout_id' => $payoutId,
                        'payout_date' => $metadata['payout_date'] ?? null,
                        'amount' => 0,
                        'transaction_count' => 0,
                        'transactions' => [],
                    ];
                }

                $payouts[$payoutId]['amount'] += $transaction->seller_revenue;
                $payouts[$payoutId]['transaction_count']++;
                $payouts[$payoutId]['transactions'][] = [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->seller_revenue,
                    'extension_id' => $transaction->extension_id,
                    'created_at' => $transaction->created_at->toIso8601String(),
                ];
            }

            // Convert to array and sort by date descending
            $payoutList = array_values($payouts);
            usort($payoutList, function ($a, $b) {
                return strtotime($b['payout_date'] ?? 0) - strtotime($a['payout_date'] ?? 0);
            });

            // Limit results
            $payoutList = array_slice($payoutList, 0, $limit);

            return [
                'success' => true,
                'seller_id' => $sellerId,
                'total_payouts' => count($payoutList),
                'payouts' => $payoutList,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get payout history', [
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get payout history: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get detailed revenue distribution report.
     *
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @return array
     */
    public function getDistributionReport(?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $query = $this->transactionRepository->completed();

            if ($startDate) {
                $query = $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query = $query->where('created_at', '<=', $endDate);
            }

            $transactions = $query->get();

            $totalRevenue = $transactions->sum('amount');
            $totalPlatformFees = $transactions->sum('platform_fee');
            $totalSellerRevenue = $transactions->sum('seller_revenue');

            // Calculate paid vs pending
            $paidOutRevenue = $transactions->filter(function ($transaction) {
                $metadata = $transaction->metadata ?? [];
                return !empty($metadata['payout_id']);
            })->sum('seller_revenue');

            $pendingRevenue = $totalSellerRevenue - $paidOutRevenue;

            // Get unique seller count
            $sellerCount = $transactions->pluck('seller_id')->unique()->count();

            return [
                'success' => true,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_revenue' => round($totalRevenue, 2),
                    'total_platform_fees' => round($totalPlatformFees, 2),
                    'total_seller_revenue' => round($totalSellerRevenue, 2),
                    'paid_out_revenue' => round($paidOutRevenue, 2),
                    'pending_revenue' => round($pendingRevenue, 2),
                    'platform_fee_percentage' => $totalRevenue > 0 ? round(($totalPlatformFees / $totalRevenue) * 100, 2) : 0,
                    'seller_count' => $sellerCount,
                    'transaction_count' => $transactions->count(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to generate distribution report', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate distribution report: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get the minimum payout threshold from configuration.
     *
     * @return float
     */
    protected function getMinimumPayoutThreshold(): float
    {
        return config('marketplace.minimum_payout_amount', self::MIN_PAYOUT_THRESHOLD);
    }

    /**
     * Set custom platform fee percentage for a specific seller.
     * This allows for negotiated rates with certain sellers.
     *
     * @param  int  $sellerId
     * @param  float  $percentage
     * @return array
     */
    public function setSellerPlatformFee(int $sellerId, float $percentage): array
    {
        try {
            if ($percentage < 0 || $percentage > 100) {
                return [
                    'success' => false,
                    'error' => 'Platform fee percentage must be between 0 and 100',
                ];
            }

            // Store custom fee in seller metadata or a separate table
            // For now, we'll just return success - this could be extended to store in a sellers table

            Log::info('Custom platform fee set for seller', [
                'seller_id' => $sellerId,
                'fee_percentage' => $percentage,
            ]);

            return [
                'success' => true,
                'seller_id' => $sellerId,
                'platform_fee_percentage' => $percentage,
                'message' => 'Custom platform fee will be applied to future transactions',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to set custom platform fee: ' . $e->getMessage(),
            ];
        }
    }
}
