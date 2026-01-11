<?php

namespace Webkul\Marketplace\Services\Payment;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Marketplace\Contracts\PaymentGateway;

class StripeGateway implements PaymentGateway
{
    /**
     * Stripe API version to use.
     */
    const API_VERSION = '2023-10-16';

    /**
     * Stripe API base URL.
     */
    const API_BASE_URL = 'https://api.stripe.com/v1';

    /**
     * Configuration array for the gateway.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Stripe secret key.
     *
     * @var string|null
     */
    protected ?string $secretKey = null;

    /**
     * Stripe webhook secret.
     *
     * @var string|null
     */
    protected ?string $webhookSecret = null;

    /**
     * Currency to use for transactions.
     *
     * @var string
     */
    protected string $currency = 'usd';

    /**
     * Initialize the payment gateway with configuration.
     *
     * @param  array  $config
     * @return void
     */
    public function initialize(array $config): void
    {
        $this->config = array_merge([
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'usd'),
            'test_mode' => env('STRIPE_TEST_MODE', false),
        ], $config);

        $this->secretKey = $this->config['secret_key'];
        $this->webhookSecret = $this->config['webhook_secret'];
        $this->currency = strtolower($this->config['currency']);

        if (!$this->secretKey) {
            throw new Exception('Stripe secret key is required');
        }
    }

    /**
     * Create a payment intent/charge for an extension purchase.
     *
     * @param  array  $paymentData
     * @return array
     */
    public function createPayment(array $paymentData): array
    {
        try {
            $this->validatePaymentData($paymentData);

            $amount = $paymentData['amount'];
            $currency = $paymentData['currency'] ?? $this->currency;

            // Convert amount to smallest currency unit (cents for USD)
            $amountInCents = $this->convertToSmallestUnit($amount, $currency);

            $payload = [
                'amount' => $amountInCents,
                'currency' => strtolower($currency),
                'description' => $paymentData['description'] ?? 'Extension purchase',
                'metadata' => array_merge([
                    'extension_id' => $paymentData['extension_id'] ?? null,
                    'buyer_id' => $paymentData['buyer_id'] ?? null,
                    'seller_id' => $paymentData['seller_id'] ?? null,
                ], $paymentData['metadata'] ?? []),
            ];

            // Add customer if provided
            if (!empty($paymentData['customer_id'])) {
                $payload['customer'] = $paymentData['customer_id'];
            }

            // Add payment method if provided
            if (!empty($paymentData['payment_method'])) {
                $payload['payment_method'] = $paymentData['payment_method'];
                $payload['confirm'] = true;
                $payload['automatic_payment_methods'] = ['enabled' => false];
            } else {
                $payload['automatic_payment_methods'] = ['enabled' => true];
            }

            // Add return URL for redirect-based payment methods
            if (!empty($paymentData['return_url'])) {
                $payload['return_url'] = $paymentData['return_url'];
            }

            // Create payment intent
            $response = $this->makeRequest('POST', '/payment_intents', $payload);

            if ($response['success']) {
                $intent = $response['data'];

                return [
                    'success' => true,
                    'transaction_id' => $intent['id'],
                    'status' => $this->mapStripeStatus($intent['status']),
                    'amount' => $amount,
                    'currency' => $currency,
                    'client_secret' => $intent['client_secret'] ?? null,
                    'next_action' => $intent['next_action'] ?? null,
                    'payment_method' => $intent['payment_method'] ?? null,
                    'raw_response' => $intent,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to create payment',
            ];
        } catch (Exception $e) {
            Log::error('Stripe payment creation failed', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process a refund for a completed transaction.
     *
     * @param  string  $transactionId
     * @param  float  $amount
     * @param  array  $options
     * @return array
     */
    public function refund(string $transactionId, float $amount, array $options = []): array
    {
        try {
            $currency = $options['currency'] ?? $this->currency;
            $amountInCents = $this->convertToSmallestUnit($amount, $currency);

            $payload = [
                'payment_intent' => $transactionId,
                'amount' => $amountInCents,
            ];

            if (!empty($options['reason'])) {
                $payload['reason'] = $options['reason'];
            }

            if (!empty($options['metadata'])) {
                $payload['metadata'] = $options['metadata'];
            }

            $response = $this->makeRequest('POST', '/refunds', $payload);

            if ($response['success']) {
                $refund = $response['data'];

                return [
                    'success' => true,
                    'refund_id' => $refund['id'],
                    'status' => $refund['status'],
                    'amount' => $amount,
                    'currency' => $currency,
                    'raw_response' => $refund,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to process refund',
            ];
        } catch (Exception $e) {
            Log::error('Stripe refund failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
                'amount' => $amount,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify and process webhook events from the payment gateway.
     *
     * @param  array  $payload
     * @param  string|null  $signature
     * @return array
     */
    public function handleWebhook(array $payload, ?string $signature = null): array
    {
        try {
            // Verify webhook signature if webhook secret is configured
            if ($this->webhookSecret && $signature) {
                if (!$this->verifyWebhookSignature($payload, $signature)) {
                    return [
                        'success' => false,
                        'error' => 'Invalid webhook signature',
                    ];
                }
            }

            $event = $payload;
            $eventType = $event['type'] ?? null;
            $eventData = $event['data']['object'] ?? [];

            if (!$eventType) {
                return [
                    'success' => false,
                    'error' => 'Missing event type',
                ];
            }

            // Process different event types
            $result = match ($eventType) {
                'payment_intent.succeeded' => $this->handlePaymentSuccess($eventData),
                'payment_intent.payment_failed' => $this->handlePaymentFailed($eventData),
                'payment_intent.canceled' => $this->handlePaymentCanceled($eventData),
                'charge.refunded' => $this->handleRefund($eventData),
                'customer.created' => $this->handleCustomerCreated($eventData),
                'customer.updated' => $this->handleCustomerUpdated($eventData),
                'payout.paid' => $this->handlePayoutPaid($eventData),
                'payout.failed' => $this->handlePayoutFailed($eventData),
                default => [
                    'success' => true,
                    'message' => "Event type {$eventType} received but not handled",
                    'event_type' => $eventType,
                ],
            };

            Log::info('Stripe webhook processed', [
                'event_type' => $eventType,
                'result' => $result,
            ]);

            return array_merge($result, [
                'event_type' => $eventType,
                'event_id' => $event['id'] ?? null,
            ]);
        } catch (Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve payment status and details.
     *
     * @param  string  $transactionId
     * @return array
     */
    public function getPaymentStatus(string $transactionId): array
    {
        try {
            $response = $this->makeRequest('GET', "/payment_intents/{$transactionId}");

            if ($response['success']) {
                $intent = $response['data'];

                return [
                    'success' => true,
                    'transaction_id' => $intent['id'],
                    'status' => $this->mapStripeStatus($intent['status']),
                    'amount' => $this->convertFromSmallestUnit($intent['amount'], $intent['currency']),
                    'currency' => strtoupper($intent['currency']),
                    'payment_method' => $intent['payment_method'] ?? null,
                    'created_at' => $intent['created'] ?? null,
                    'raw_response' => $intent,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to retrieve payment status',
            ];
        } catch (Exception $e) {
            Log::error('Failed to retrieve Stripe payment status', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a pending payment.
     *
     * @param  string  $transactionId
     * @return array
     */
    public function cancelPayment(string $transactionId): array
    {
        try {
            $response = $this->makeRequest('POST', "/payment_intents/{$transactionId}/cancel");

            if ($response['success']) {
                $intent = $response['data'];

                return [
                    'success' => true,
                    'transaction_id' => $intent['id'],
                    'status' => 'cancelled',
                    'raw_response' => $intent,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to cancel payment',
            ];
        } catch (Exception $e) {
            Log::error('Failed to cancel Stripe payment', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the payment gateway name/identifier.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'stripe';
    }

    /**
     * Get supported payment methods for this gateway.
     *
     * @return array
     */
    public function getSupportedPaymentMethods(): array
    {
        return [
            'card',
            'bank_transfer',
            'alipay',
            'wechat_pay',
            'ideal',
            'sepa_debit',
            'sofort',
            'giropay',
            'bancontact',
            'eps',
            'p24',
            'klarna',
            'afterpay_clearpay',
            'affirm',
        ];
    }

    /**
     * Validate payment gateway configuration.
     *
     * @param  array  $config
     * @return bool
     */
    public function validateConfiguration(array $config): bool
    {
        return !empty($config['secret_key']) &&
               (empty($config['webhook_secret']) || is_string($config['webhook_secret']));
    }

    /**
     * Create a customer in the payment gateway.
     *
     * @param  array  $customerData
     * @return array
     */
    public function createCustomer(array $customerData): array
    {
        try {
            $payload = [
                'email' => $customerData['email'] ?? null,
                'name' => $customerData['name'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'description' => $customerData['description'] ?? null,
                'metadata' => $customerData['metadata'] ?? [],
            ];

            // Add address if provided
            if (!empty($customerData['address'])) {
                $payload['address'] = $customerData['address'];
            }

            // Filter out null values
            $payload = array_filter($payload, fn($value) => $value !== null);

            $response = $this->makeRequest('POST', '/customers', $payload);

            if ($response['success']) {
                $customer = $response['data'];

                return [
                    'success' => true,
                    'customer_id' => $customer['id'],
                    'email' => $customer['email'],
                    'name' => $customer['name'],
                    'raw_response' => $customer,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to create customer',
            ];
        } catch (Exception $e) {
            Log::error('Failed to create Stripe customer', [
                'error' => $e->getMessage(),
                'customer_data' => $customerData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update customer information in the payment gateway.
     *
     * @param  string  $customerId
     * @param  array  $customerData
     * @return array
     */
    public function updateCustomer(string $customerId, array $customerData): array
    {
        try {
            $payload = array_filter([
                'email' => $customerData['email'] ?? null,
                'name' => $customerData['name'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'description' => $customerData['description'] ?? null,
                'metadata' => $customerData['metadata'] ?? null,
                'address' => $customerData['address'] ?? null,
            ], fn($value) => $value !== null);

            $response = $this->makeRequest('POST', "/customers/{$customerId}", $payload);

            if ($response['success']) {
                $customer = $response['data'];

                return [
                    'success' => true,
                    'customer_id' => $customer['id'],
                    'raw_response' => $customer,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to update customer',
            ];
        } catch (Exception $e) {
            Log::error('Failed to update Stripe customer', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve customer details from the payment gateway.
     *
     * @param  string  $customerId
     * @return array
     */
    public function getCustomer(string $customerId): array
    {
        try {
            $response = $this->makeRequest('GET', "/customers/{$customerId}");

            if ($response['success']) {
                $customer = $response['data'];

                return [
                    'success' => true,
                    'customer_id' => $customer['id'],
                    'email' => $customer['email'] ?? null,
                    'name' => $customer['name'] ?? null,
                    'phone' => $customer['phone'] ?? null,
                    'raw_response' => $customer,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to retrieve customer',
            ];
        } catch (Exception $e) {
            Log::error('Failed to retrieve Stripe customer', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test the connection and credentials with the payment gateway.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/balance');

            return $response['success'];
        } catch (Exception $e) {
            Log::error('Stripe connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the minimum transaction amount supported by the gateway.
     *
     * @param  string  $currency
     * @return float
     */
    public function getMinimumAmount(string $currency = 'USD'): float
    {
        // Stripe minimum amounts vary by currency
        // These are the most common ones
        $minimums = [
            'USD' => 0.50,
            'EUR' => 0.50,
            'GBP' => 0.30,
            'CAD' => 0.50,
            'AUD' => 0.50,
            'JPY' => 50.00,
            'MXN' => 10.00,
            'NOK' => 3.00,
            'SEK' => 3.00,
            'DKK' => 2.50,
        ];

        return $minimums[strtoupper($currency)] ?? 0.50;
    }

    /**
     * Get the maximum transaction amount supported by the gateway.
     *
     * @param  string  $currency
     * @return float
     */
    public function getMaximumAmount(string $currency = 'USD'): float
    {
        // Stripe doesn't have a hard maximum, but we'll set reasonable limits
        return 999999.99;
    }

    /**
     * Get supported currencies for this gateway.
     *
     * @return array
     */
    public function getSupportedCurrencies(): array
    {
        return [
            'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CNY', 'INR',
            'MXN', 'BRL', 'CHF', 'NOK', 'SEK', 'DKK', 'PLN', 'SGD',
            'HKD', 'NZD', 'KRW', 'TRY', 'RUB', 'ZAR', 'AED', 'SAR',
        ];
    }

    /**
     * Check if the gateway supports a specific currency.
     *
     * @param  string  $currency
     * @return bool
     */
    public function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->getSupportedCurrencies());
    }

    /**
     * Create a payout to a seller/developer account.
     *
     * @param  array  $payoutData
     * @return array
     */
    public function createPayout(array $payoutData): array
    {
        try {
            $amount = $payoutData['amount'];
            $currency = $payoutData['currency'] ?? $this->currency;
            $amountInCents = $this->convertToSmallestUnit($amount, $currency);

            $payload = [
                'amount' => $amountInCents,
                'currency' => strtolower($currency),
                'description' => $payoutData['description'] ?? 'Extension sales payout',
                'metadata' => $payoutData['metadata'] ?? [],
            ];

            // Add destination (connected account) if provided
            if (!empty($payoutData['destination'])) {
                $payload['destination'] = $payoutData['destination'];
            }

            $response = $this->makeRequest('POST', '/payouts', $payload);

            if ($response['success']) {
                $payout = $response['data'];

                return [
                    'success' => true,
                    'payout_id' => $payout['id'],
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => $payout['status'],
                    'arrival_date' => $payout['arrival_date'] ?? null,
                    'raw_response' => $payout,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to create payout',
            ];
        } catch (Exception $e) {
            Log::error('Failed to create Stripe payout', [
                'error' => $e->getMessage(),
                'payout_data' => $payoutData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get payout status and details.
     *
     * @param  string  $payoutId
     * @return array
     */
    public function getPayoutStatus(string $payoutId): array
    {
        try {
            $response = $this->makeRequest('GET', "/payouts/{$payoutId}");

            if ($response['success']) {
                $payout = $response['data'];

                return [
                    'success' => true,
                    'payout_id' => $payout['id'],
                    'amount' => $this->convertFromSmallestUnit($payout['amount'], $payout['currency']),
                    'currency' => strtoupper($payout['currency']),
                    'status' => $payout['status'],
                    'arrival_date' => $payout['arrival_date'] ?? null,
                    'raw_response' => $payout,
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to retrieve payout status',
            ];
        } catch (Exception $e) {
            Log::error('Failed to retrieve Stripe payout status', [
                'error' => $e->getMessage(),
                'payout_id' => $payoutId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Make an HTTP request to the Stripe API.
     *
     * @param  string  $method
     * @param  string  $endpoint
     * @param  array  $data
     * @return array
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $url = self::API_BASE_URL . $endpoint;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Stripe-Version' => self::API_VERSION,
            ])->asForm();

            $httpResponse = match (strtoupper($method)) {
                'GET' => $response->get($url, $data),
                'POST' => $response->post($url, $data),
                'PUT' => $response->put($url, $data),
                'DELETE' => $response->delete($url, $data),
                default => throw new Exception("Unsupported HTTP method: {$method}"),
            };

            if ($httpResponse->successful()) {
                return [
                    'success' => true,
                    'data' => $httpResponse->json(),
                ];
            }

            $errorData = $httpResponse->json();
            $errorMessage = $errorData['error']['message'] ?? 'Unknown Stripe error';

            Log::warning('Stripe API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $httpResponse->status(),
                'error' => $errorMessage,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'error_data' => $errorData,
            ];
        } catch (Exception $e) {
            Log::error('Stripe API request exception', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify webhook signature.
     *
     * @param  array  $payload
     * @param  string  $signature
     * @return bool
     */
    protected function verifyWebhookSignature(array $payload, string $signature): bool
    {
        try {
            $payloadJson = json_encode($payload);
            $elements = explode(',', $signature);
            $signatureHash = null;
            $timestamp = null;

            foreach ($elements as $element) {
                [$key, $value] = explode('=', $element, 2);
                if ($key === 't') {
                    $timestamp = $value;
                } elseif ($key === 'v1') {
                    $signatureHash = $value;
                }
            }

            if (!$timestamp || !$signatureHash) {
                return false;
            }

            // Verify timestamp is recent (within 5 minutes)
            if (abs(time() - (int) $timestamp) > 300) {
                return false;
            }

            // Compute expected signature
            $signedPayload = $timestamp . '.' . $payloadJson;
            $expectedHash = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

            return hash_equals($expectedHash, $signatureHash);
        } catch (Exception $e) {
            Log::error('Webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate payment data before creating a payment.
     *
     * @param  array  $data
     * @return void
     * @throws Exception
     */
    protected function validatePaymentData(array $data): void
    {
        if (empty($data['amount']) || $data['amount'] <= 0) {
            throw new Exception('Invalid payment amount');
        }

        $currency = $data['currency'] ?? $this->currency;
        if (!$this->supportsCurrency($currency)) {
            throw new Exception("Currency {$currency} is not supported");
        }

        $amount = $data['amount'];
        $minAmount = $this->getMinimumAmount($currency);
        $maxAmount = $this->getMaximumAmount($currency);

        if ($amount < $minAmount) {
            throw new Exception("Amount must be at least {$minAmount} {$currency}");
        }

        if ($amount > $maxAmount) {
            throw new Exception("Amount cannot exceed {$maxAmount} {$currency}");
        }
    }

    /**
     * Convert amount to smallest currency unit (e.g., cents).
     *
     * @param  float  $amount
     * @param  string  $currency
     * @return int
     */
    protected function convertToSmallestUnit(float $amount, string $currency): int
    {
        $currency = strtoupper($currency);

        // Zero-decimal currencies (no cents)
        $zeroDecimalCurrencies = ['JPY', 'KRW', 'CLP', 'VND'];

        if (in_array($currency, $zeroDecimalCurrencies)) {
            return (int) round($amount);
        }

        // Most currencies use 2 decimal places (cents)
        return (int) round($amount * 100);
    }

    /**
     * Convert amount from smallest currency unit.
     *
     * @param  int  $amount
     * @param  string  $currency
     * @return float
     */
    protected function convertFromSmallestUnit(int $amount, string $currency): float
    {
        $currency = strtoupper($currency);

        // Zero-decimal currencies
        $zeroDecimalCurrencies = ['JPY', 'KRW', 'CLP', 'VND'];

        if (in_array($currency, $zeroDecimalCurrencies)) {
            return (float) $amount;
        }

        return $amount / 100;
    }

    /**
     * Map Stripe payment status to internal status.
     *
     * @param  string  $stripeStatus
     * @return string
     */
    protected function mapStripeStatus(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'succeeded' => 'completed',
            'processing' => 'pending',
            'requires_payment_method', 'requires_confirmation', 'requires_action' => 'pending',
            'canceled' => 'cancelled',
            'failed' => 'failed',
            default => $stripeStatus,
        };
    }

    /**
     * Handle successful payment webhook event.
     *
     * @param  array  $eventData
     * @return array
     */
    protected function handlePaymentSuccess(array $eventData): array
    {
        return [
            'success' => true,
            'action' => 'payment_succeeded',
            'transaction_id' => $eventData['id'],
            'amount' => $this->convertFromSmallestUnit($eventData['amount'], $eventData['currency']),
            'currency' => strtoupper($eventData['currency']),
            'metadata' => $eventData['metadata'] ?? [],
        ];
    }

    /**
     * Handle failed payment webhook event.
     *
     * @param  array  $eventData
     * @return array
     */
    protected function handlePaymentFailed(array $eventData): array
    {
        return [
            'success' => true,
            'action' => 'payment_failed',
            'transaction_id' => $eventData['id'],
            'error' => $eventData['last_payment_error']['message'] ?? 'Payment failed',
            'metadata' => $eventData['metadata'] ?? [],
        ];
    }

    /**
     * Handle canceled payment webhook event.
     *
     * @param  array  $eventData
     * @return array
     */
    protected function handlePaymentCanceled(array $eventData): array
    {
        return [
            'success' => true,
            'action' => 'payment_canceled',
            'transaction_id' => $eventData['id'],
            'metadata' => $eventData['metadata'] ?? [],
        ];
    }

    /**
     * Handle refund webhook event.
     *
     * @param  array  $eventData
     * @return array
     */
    protected function handleRefund(array $eventData): array
    {
        return [
            'success' => true,
            'action' => 'charge_refunded',
            'charge_id' => $eventData['id'],
            'amount_refunded' => $this->convertFromSmallestUnit($eventData['amount_refunded'], $eventData['currency']),
            'currency' => strtoupper($eventData['currency']),
        ];
    }

    /**
     * Handle customer created webhook event.
     *
     * @param  array  $eventData
     * @return array
     */
    protected function handleCustomerCreated(array $eventData): array
    {
        return [
            'success' => true,
            'action' => 'customer_created',
            'customer_id' => $eventData['id'],
            'email' => $eventData['email'] ?? null,
        ];
    }

    /**
     * Handle customer updated webhook event.
     *
     * @param  array  $eventData
     * @return array
     */
    protected function handleCustomerUpdated(array $eventData): array
    {
        return [
            'success' => true,
            'action' => 'customer_updated',
            'customer_id' => $eventData['id'],
            'email' => $eventData['email'] ?? null,
        ];
    }

    /**
     * Handle payout paid webhook event.
     *
     * @param  array  $eventData
     * @return array
     */
    protected function handlePayoutPaid(array $eventData): array
    {
        return [
            'success' => true,
            'action' => 'payout_paid',
            'payout_id' => $eventData['id'],
            'amount' => $this->convertFromSmallestUnit($eventData['amount'], $eventData['currency']),
            'currency' => strtoupper($eventData['currency']),
        ];
    }

    /**
     * Handle payout failed webhook event.
     *
     * @param  array  $eventData
     * @return array
     */
    protected function handlePayoutFailed(array $eventData): array
    {
        return [
            'success' => true,
            'action' => 'payout_failed',
            'payout_id' => $eventData['id'],
            'failure_message' => $eventData['failure_message'] ?? 'Payout failed',
        ];
    }
}
