<?php

namespace Webkul\Marketplace\Http\Controllers\Marketplace;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Contracts\PaymentGateway;
use Webkul\Marketplace\Exceptions\PaymentException;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionTransactionRepository;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Services\Payment\StripeGateway;

class PaymentController extends Controller
{
    /**
     * Payment gateway instance.
     *
     * @var PaymentGateway
     */
    protected PaymentGateway $gateway;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionTransactionRepository $transactionRepository,
        protected ExtensionInstallationRepository $installationRepository
    ) {
        // Initialize default payment gateway (Stripe)
        $this->gateway = new StripeGateway();
        $this->gateway->initialize([]);
    }

    /**
     * Initiate payment for an extension purchase.
     */
    public function initiatePayment(Request $request, int $extensionId): JsonResponse
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|string|in:stripe,paypal',
                'payment_token' => 'nullable|string',
                'return_url' => 'nullable|url',
            ]);

            $extension = $this->extensionRepository->findOrFail($extensionId);

            // Verify extension is available for purchase
            if (!$extension->isApproved()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.extension-not-available'),
                ], 400);
            }

            // Check if extension is free
            if ($extension->isFree()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.extension-is-free'),
                ], 400);
            }

            // Check if user has already purchased this extension
            $existingTransaction = $this->transactionRepository->findOneWhere([
                'extension_id' => $extension->id,
                'buyer_id' => Auth::id(),
                'status' => 'completed',
            ]);

            if ($existingTransaction) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.already-purchased'),
                    'data' => [
                        'transaction' => $existingTransaction,
                    ],
                ], 400);
            }

            // Calculate fees
            $platformFeePercentage = config('marketplace.platform_fee_percentage', 30);
            $amount = $extension->price;
            $platformFee = ($amount * $platformFeePercentage) / 100;
            $sellerRevenue = $amount - $platformFee;

            // Create pending transaction record
            $transaction = $this->transactionRepository->create([
                'transaction_id' => 'TXN_' . strtoupper(uniqid()),
                'extension_id' => $extension->id,
                'buyer_id' => Auth::id(),
                'seller_id' => $extension->author_id,
                'amount' => $amount,
                'platform_fee' => $platformFee,
                'seller_revenue' => $sellerRevenue,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            // Initialize payment with gateway
            $paymentMethod = $request->payment_method;

            // Set appropriate gateway
            if ($paymentMethod === 'stripe') {
                $this->gateway = new StripeGateway();
                $this->gateway->initialize([]);
            }

            // Prepare payment data
            $paymentData = [
                'amount' => $amount,
                'currency' => config('marketplace.currency', 'USD'),
                'description' => "Purchase of {$extension->name}",
                'metadata' => [
                    'extension_id' => $extension->id,
                    'buyer_id' => Auth::id(),
                    'seller_id' => $extension->author_id,
                    'transaction_id' => $transaction->id,
                ],
                'payment_method' => $request->payment_token,
                'return_url' => $request->return_url ?? route('marketplace.payment.callback', ['transaction_id' => $transaction->id]),
            ];

            // Create payment intent with error handling
            try {
                $paymentResult = $this->gateway->createPayment($paymentData);

                if (!$paymentResult['success']) {
                    // Mark transaction as failed
                    $transaction->markAsFailed($paymentResult['error'] ?? 'Payment initialization failed');

                    // Determine the specific error type
                    $errorMessage = $this->getPaymentErrorMessage($paymentResult['error'] ?? '');

                    return new JsonResponse([
                        'success' => false,
                        'message' => $errorMessage,
                    ], 400);
                }
            } catch (Exception $e) {
                // Mark transaction as failed
                $transaction->markAsFailed($e->getMessage());

                Log::error('Payment gateway error', [
                    'extension_id' => $extensionId,
                    'error' => $e->getMessage(),
                ]);

                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.errors.connection-failed'),
                ], 500);
            }

            // Update transaction with gateway transaction ID
            $transaction->update([
                'transaction_id' => $paymentResult['transaction_id'],
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'gateway_response' => $paymentResult,
                ]),
            ]);

            return new JsonResponse([
                'success' => true,
                'message' => trans('marketplace::app.marketplace.payment.initialized'),
                'data' => [
                    'transaction' => $transaction->fresh(),
                    'payment' => [
                        'transaction_id' => $paymentResult['transaction_id'],
                        'client_secret' => $paymentResult['client_secret'] ?? null,
                        'status' => $paymentResult['status'],
                        'next_action' => $paymentResult['next_action'] ?? null,
                    ],
                ],
            ]);
        } catch (PaymentException $e) {
            Log::error('Payment initialization failed', [
                'extension_id' => $extensionId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ], 400);
        } catch (Exception $e) {
            Log::error('Unexpected payment error', [
                'extension_id' => $extensionId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.marketplace.payment.initialization-failed'),
            ], 500);
        }
    }

    /**
     * Handle payment callback/redirect from payment gateway.
     */
    public function handleCallback(Request $request, int $transactionId): RedirectResponse
    {
        try {
            $transaction = $this->transactionRepository->with(['extension', 'buyer'])->findOrFail($transactionId);

            // Verify ownership
            if ($transaction->buyer_id !== Auth::id()) {
                return redirect()->route('marketplace.browse.index')
                    ->with('error', trans('marketplace::app.marketplace.payment.unauthorized'));
            }

            // Check payment status with gateway
            $paymentStatus = $this->gateway->getPaymentStatus($transaction->transaction_id);

            if (!$paymentStatus['success']) {
                return redirect()->route('marketplace.extension.show', ['slug' => $transaction->extension->slug])
                    ->with('error', trans('marketplace::app.marketplace.payment.status-check-failed'));
            }

            // Update transaction status based on gateway response
            if ($paymentStatus['status'] === 'completed') {
                $transaction->markAsCompleted();

                return redirect()->route('marketplace.extension.show', ['slug' => $transaction->extension->slug])
                    ->with('success', trans('marketplace::app.marketplace.payment.success'));
            } elseif ($paymentStatus['status'] === 'failed') {
                $transaction->markAsFailed('Payment failed at gateway');

                return redirect()->route('marketplace.extension.show', ['slug' => $transaction->extension->slug])
                    ->with('error', trans('marketplace::app.marketplace.payment.failed'));
            }

            // Payment is still pending
            return redirect()->route('marketplace.extension.show', ['slug' => $transaction->extension->slug])
                ->with('info', trans('marketplace::app.marketplace.payment.pending'));
        } catch (Exception $e) {
            Log::error('Payment callback handling failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('marketplace.browse.index')
                ->with('error', trans('marketplace::app.marketplace.payment.callback-failed'));
        }
    }

    /**
     * Handle webhook events from payment gateway.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            // Get webhook signature from header
            $signature = $request->header('Stripe-Signature');

            // Get raw payload
            $payload = $request->all();

            // Verify and process webhook
            $webhookResult = $this->gateway->handleWebhook($payload, $signature);

            if (!$webhookResult['success']) {
                Log::warning('Webhook processing failed', [
                    'error' => $webhookResult['error'] ?? 'Unknown error',
                    'payload' => $payload,
                ]);

                return new JsonResponse([
                    'success' => false,
                    'message' => $webhookResult['error'] ?? 'Webhook processing failed',
                ], 400);
            }

            // Process webhook event based on action
            $eventType = $webhookResult['event_type'];
            $action = $webhookResult['action'] ?? null;

            if ($action === 'payment_succeeded') {
                $this->processPaymentSuccess($webhookResult);
            } elseif ($action === 'payment_failed') {
                $this->processPaymentFailure($webhookResult);
            } elseif ($action === 'charge_refunded') {
                $this->processRefundWebhook($webhookResult);
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Webhook handling failed',
            ], 500);
        }
    }

    /**
     * Process refund for a completed transaction.
     */
    public function processRefund(Request $request, int $transactionId): JsonResponse
    {
        try {
            $this->validate($request, [
                'reason' => 'nullable|string|max:500',
                'amount' => 'nullable|numeric|min:0.01',
            ]);

            $transaction = $this->transactionRepository->with(['extension', 'buyer', 'seller'])->findOrFail($transactionId);

            // Verify authorization (only admin or buyer can request refund)
            if (!auth()->guard('user')->user()->hasRole('admin') && $transaction->buyer_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.unauthorized'),
                ], 403);
            }

            // Verify transaction is completed
            if (!$transaction->isCompleted()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.cannot-refund-non-completed'),
                ], 400);
            }

            // Check if already refunded
            if ($transaction->isRefunded()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.already-refunded'),
                ], 400);
            }

            // Determine refund amount (full refund if not specified)
            $refundAmount = $request->amount ?? $transaction->amount;

            // Validate refund amount
            if ($refundAmount > $transaction->amount) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.refund-amount-exceeds-transaction'),
                ], 400);
            }

            // Process refund with gateway
            $refundResult = $this->gateway->refund(
                $transaction->transaction_id,
                $refundAmount,
                [
                    'currency' => config('marketplace.currency', 'USD'),
                    'reason' => $request->reason,
                    'metadata' => [
                        'transaction_id' => $transaction->id,
                        'refunded_by' => Auth::id(),
                    ],
                ]
            );

            if (!$refundResult['success']) {
                Log::error('Refund processing failed', [
                    'transaction_id' => $transactionId,
                    'error' => $refundResult['error'] ?? 'Unknown error',
                ]);

                return new JsonResponse([
                    'success' => false,
                    'message' => $refundResult['error'] ?? trans('marketplace::app.marketplace.payment.refund-failed'),
                ], 400);
            }

            // Update transaction status
            DB::beginTransaction();

            try {
                $transaction->markAsRefunded($request->reason);

                // Update transaction metadata with refund details
                $transaction->update([
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'refund' => [
                            'refund_id' => $refundResult['refund_id'],
                            'amount' => $refundAmount,
                            'reason' => $request->reason,
                            'refunded_at' => now()->toIso8601String(),
                            'refunded_by' => Auth::id(),
                        ],
                    ]),
                ]);

                // Disable the installation if it exists
                $installation = $this->installationRepository->findOneWhere([
                    'extension_id' => $transaction->extension_id,
                    'user_id' => $transaction->buyer_id,
                ]);

                if ($installation && $installation->isActive()) {
                    $installation->deactivate();
                }

                DB::commit();

                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.payment.refund-success'),
                    'data' => [
                        'transaction' => $transaction->fresh(),
                        'refund' => [
                            'refund_id' => $refundResult['refund_id'],
                            'amount' => $refundAmount,
                            'status' => $refundResult['status'],
                        ],
                    ],
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'transaction_id' => $transactionId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.marketplace.payment.refund-failed') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status for a transaction.
     */
    public function getPaymentStatus(int $transactionId): JsonResponse
    {
        try {
            $transaction = $this->transactionRepository->with(['extension', 'buyer', 'seller'])->findOrFail($transactionId);

            // Verify authorization
            if ($transaction->buyer_id !== Auth::id() && !auth()->guard('user')->user()->hasRole('admin')) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.unauthorized'),
                ], 403);
            }

            // Get status from gateway if transaction is pending
            if ($transaction->isPending()) {
                $gatewayStatus = $this->gateway->getPaymentStatus($transaction->transaction_id);

                if ($gatewayStatus['success']) {
                    // Update local transaction status if it changed
                    if ($gatewayStatus['status'] === 'completed' && !$transaction->isCompleted()) {
                        $transaction->markAsCompleted();
                    } elseif ($gatewayStatus['status'] === 'failed' && !$transaction->hasFailed()) {
                        $transaction->markAsFailed('Payment failed at gateway');
                    }
                }
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'transaction' => $transaction->fresh(),
                    'status' => $transaction->status,
                    'is_completed' => $transaction->isCompleted(),
                    'is_pending' => $transaction->isPending(),
                    'is_failed' => $transaction->hasFailed(),
                    'is_refunded' => $transaction->isRefunded(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get payment status', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.marketplace.payment.status-check-failed'),
            ], 500);
        }
    }

    /**
     * Cancel a pending payment.
     */
    public function cancelPayment(int $transactionId): JsonResponse
    {
        try {
            $transaction = $this->transactionRepository->findOrFail($transactionId);

            // Verify ownership
            if ($transaction->buyer_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.unauthorized'),
                ], 403);
            }

            // Verify transaction is pending
            if (!$transaction->isPending()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.payment.cannot-cancel-non-pending'),
                ], 400);
            }

            // Cancel payment at gateway
            $cancelResult = $this->gateway->cancelPayment($transaction->transaction_id);

            if (!$cancelResult['success']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $cancelResult['error'] ?? trans('marketplace::app.marketplace.payment.cancellation-failed'),
                ], 400);
            }

            // Update transaction status
            $transaction->markAsCancelled('Cancelled by user');

            return new JsonResponse([
                'success' => true,
                'message' => trans('marketplace::app.marketplace.payment.cancelled'),
                'data' => [
                    'transaction' => $transaction->fresh(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Payment cancellation failed', [
                'transaction_id' => $transactionId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.marketplace.payment.cancellation-failed'),
            ], 500);
        }
    }

    /**
     * Process successful payment from webhook.
     *
     * @param  array  $webhookData
     * @return void
     */
    protected function processPaymentSuccess(array $webhookData): void
    {
        try {
            $transactionId = $webhookData['metadata']['transaction_id'] ?? null;

            if (!$transactionId) {
                Log::warning('Payment success webhook missing transaction_id', $webhookData);
                return;
            }

            $transaction = $this->transactionRepository->find($transactionId);

            if (!$transaction) {
                Log::warning('Transaction not found for payment success webhook', [
                    'transaction_id' => $transactionId,
                ]);
                return;
            }

            // Update transaction status
            if (!$transaction->isCompleted()) {
                $transaction->markAsCompleted();

                Log::info('Payment completed via webhook', [
                    'transaction_id' => $transaction->id,
                    'extension_id' => $transaction->extension_id,
                    'buyer_id' => $transaction->buyer_id,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to process payment success webhook', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
            ]);
        }
    }

    /**
     * Process failed payment from webhook.
     *
     * @param  array  $webhookData
     * @return void
     */
    protected function processPaymentFailure(array $webhookData): void
    {
        try {
            $transactionId = $webhookData['metadata']['transaction_id'] ?? null;

            if (!$transactionId) {
                Log::warning('Payment failure webhook missing transaction_id', $webhookData);
                return;
            }

            $transaction = $this->transactionRepository->find($transactionId);

            if (!$transaction) {
                Log::warning('Transaction not found for payment failure webhook', [
                    'transaction_id' => $transactionId,
                ]);
                return;
            }

            // Update transaction status
            if (!$transaction->hasFailed()) {
                $errorMessage = $webhookData['error'] ?? 'Payment failed at gateway';
                $transaction->markAsFailed($errorMessage);

                Log::info('Payment failed via webhook', [
                    'transaction_id' => $transaction->id,
                    'error' => $errorMessage,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to process payment failure webhook', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
            ]);
        }
    }

    /**
     * Process refund from webhook.
     *
     * @param  array  $webhookData
     * @return void
     */
    protected function processRefundWebhook(array $webhookData): void
    {
        try {
            // Find transaction by charge_id or transaction_id
            $chargeId = $webhookData['charge_id'] ?? null;

            if (!$chargeId) {
                Log::warning('Refund webhook missing charge_id', $webhookData);
                return;
            }

            // Find transaction by gateway transaction ID
            $transaction = $this->transactionRepository->findOneWhere([
                'transaction_id' => $chargeId,
            ]);

            if (!$transaction) {
                Log::warning('Transaction not found for refund webhook', [
                    'charge_id' => $chargeId,
                ]);
                return;
            }

            // Update transaction status
            if (!$transaction->isRefunded()) {
                $transaction->markAsRefunded('Refunded via webhook');

                // Disable the installation if it exists
                $installation = $this->installationRepository->findOneWhere([
                    'extension_id' => $transaction->extension_id,
                    'user_id' => $transaction->buyer_id,
                ]);

                if ($installation && $installation->isActive()) {
                    $installation->deactivate();
                }

                Log::info('Refund processed via webhook', [
                    'transaction_id' => $transaction->id,
                    'amount' => $webhookData['amount_refunded'] ?? $transaction->amount,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to process refund webhook', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
            ]);
        }
    }

    /**
     * Get user-friendly error message based on payment gateway error.
     *
     * @param  string  $gatewayError
     * @return string
     */
    protected function getPaymentErrorMessage(string $gatewayError): string
    {
        // Map common payment gateway errors to user-friendly messages
        $errorMap = [
            'card_declined' => trans('marketplace::app.marketplace.payment.errors.declined'),
            'insufficient_funds' => trans('marketplace::app.marketplace.payment.errors.insufficient-funds'),
            'invalid_card' => trans('marketplace::app.marketplace.payment.errors.invalid-card'),
            'expired_card' => trans('marketplace::app.marketplace.payment.errors.card-expired'),
            'incorrect_cvc' => trans('marketplace::app.marketplace.payment.errors.security-code-invalid'),
            'processing_error' => trans('marketplace::app.marketplace.payment.errors.processing-failed'),
            'rate_limit' => trans('marketplace::app.marketplace.network.errors.rate-limit'),
        ];

        // Check if error message contains any known error types
        $lowercaseError = strtolower($gatewayError);
        foreach ($errorMap as $key => $message) {
            if (str_contains($lowercaseError, $key)) {
                return $message;
            }
        }

        // Default to generic processing failed message
        return trans('marketplace::app.marketplace.payment.errors.processing-failed');
    }
}
