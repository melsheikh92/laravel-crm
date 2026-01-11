<?php

namespace Webkul\Marketplace\Exceptions;

use Exception;

class NetworkException extends Exception
{
    /**
     * Create a new exception instance for connection timeout.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function timeout(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Connection timeout. Please check your internet connection and try again.',
            3001,
            $previous
        );
    }

    /**
     * Create a new exception instance for connection refused.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function connectionRefused(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Connection refused. The server may be temporarily unavailable.',
            3002,
            $previous
        );
    }

    /**
     * Create a new exception instance for DNS resolution failure.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function dnsResolutionFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Unable to resolve server address. Please check your internet connection.',
            3003,
            $previous
        );
    }

    /**
     * Create a new exception instance for SSL/TLS errors.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function sslError(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'SSL certificate verification failed. This may be a security risk.',
            3004,
            $previous
        );
    }

    /**
     * Create a new exception instance for rate limiting.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function rateLimitExceeded(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Too many requests. Please wait a moment and try again.',
            3005,
            $previous
        );
    }

    /**
     * Create a new exception instance for server errors.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function serverError(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Server error occurred. Please try again later.',
            3006,
            $previous
        );
    }
}
