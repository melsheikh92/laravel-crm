<?php

namespace Webkul\Marketplace\Exceptions;

use Exception;

class PaymentException extends Exception
{
    /**
     * Create a new exception instance for payment processing failure.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function processingFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Payment processing failed. Please try again or use a different payment method.',
            2001,
            $previous
        );
    }

    /**
     * Create a new exception instance for declined payment.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function declined(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Your payment was declined. Please check your card details and try again.',
            2002,
            $previous
        );
    }

    /**
     * Create a new exception instance for insufficient funds.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function insufficientFunds(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Insufficient funds. Please use a different payment method.',
            2003,
            $previous
        );
    }

    /**
     * Create a new exception instance for gateway timeout.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function gatewayTimeout(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Payment gateway timeout. Please try again in a few moments.',
            2004,
            $previous
        );
    }

    /**
     * Create a new exception instance for invalid token.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function invalidToken(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Invalid payment token. Please refresh the page and try again.',
            2005,
            $previous
        );
    }

    /**
     * Create a new exception instance for refund failure.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function refundFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Refund processing failed. Please contact support for assistance.',
            2006,
            $previous
        );
    }

    /**
     * Create a new exception instance for gateway connection failure.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function connectionFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Unable to connect to payment gateway. Please check your internet connection and try again.',
            2007,
            $previous
        );
    }

    /**
     * Create a new exception instance for duplicate transaction.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function duplicateTransaction(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'This transaction has already been processed.',
            2008,
            $previous
        );
    }
}
