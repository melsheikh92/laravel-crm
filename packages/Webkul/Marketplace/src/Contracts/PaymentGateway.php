<?php

namespace Webkul\Marketplace\Contracts;

interface PaymentGateway
{
    /**
     * Initialize the payment gateway with configuration.
     *
     * @param  array  $config
     * @return void
     */
    public function initialize(array $config): void;

    /**
     * Create a payment intent/charge for an extension purchase.
     *
     * @param  array  $paymentData
     * @return array
     */
    public function createPayment(array $paymentData): array;

    /**
     * Process a refund for a completed transaction.
     *
     * @param  string  $transactionId
     * @param  float  $amount
     * @param  array  $options
     * @return array
     */
    public function refund(string $transactionId, float $amount, array $options = []): array;

    /**
     * Verify and process webhook events from the payment gateway.
     *
     * @param  array  $payload
     * @param  string|null  $signature
     * @return array
     */
    public function handleWebhook(array $payload, ?string $signature = null): array;

    /**
     * Retrieve payment status and details.
     *
     * @param  string  $transactionId
     * @return array
     */
    public function getPaymentStatus(string $transactionId): array;

    /**
     * Cancel a pending payment.
     *
     * @param  string  $transactionId
     * @return array
     */
    public function cancelPayment(string $transactionId): array;

    /**
     * Get the payment gateway name/identifier.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get supported payment methods for this gateway.
     *
     * @return array
     */
    public function getSupportedPaymentMethods(): array;

    /**
     * Validate payment gateway configuration.
     *
     * @param  array  $config
     * @return bool
     */
    public function validateConfiguration(array $config): bool;

    /**
     * Create a customer in the payment gateway.
     *
     * @param  array  $customerData
     * @return array
     */
    public function createCustomer(array $customerData): array;

    /**
     * Update customer information in the payment gateway.
     *
     * @param  string  $customerId
     * @param  array  $customerData
     * @return array
     */
    public function updateCustomer(string $customerId, array $customerData): array;

    /**
     * Retrieve customer details from the payment gateway.
     *
     * @param  string  $customerId
     * @return array
     */
    public function getCustomer(string $customerId): array;

    /**
     * Test the connection and credentials with the payment gateway.
     *
     * @return bool
     */
    public function testConnection(): bool;

    /**
     * Get the minimum transaction amount supported by the gateway.
     *
     * @param  string  $currency
     * @return float
     */
    public function getMinimumAmount(string $currency = 'USD'): float;

    /**
     * Get the maximum transaction amount supported by the gateway.
     *
     * @param  string  $currency
     * @return float
     */
    public function getMaximumAmount(string $currency = 'USD'): float;

    /**
     * Get supported currencies for this gateway.
     *
     * @return array
     */
    public function getSupportedCurrencies(): array;

    /**
     * Check if the gateway supports a specific currency.
     *
     * @param  string  $currency
     * @return bool
     */
    public function supportsCurrency(string $currency): bool;

    /**
     * Create a payout to a seller/developer account.
     *
     * @param  array  $payoutData
     * @return array
     */
    public function createPayout(array $payoutData): array;

    /**
     * Get payout status and details.
     *
     * @param  string  $payoutId
     * @return array
     */
    public function getPayoutStatus(string $payoutId): array;
}
