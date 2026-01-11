<?php

namespace Webkul\Marketplace\Exceptions;

use Exception;

class InstallationException extends Exception
{
    /**
     * Create a new exception instance for download failure.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function downloadFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Failed to download extension package. Please check your internet connection and try again.',
            1001,
            $previous
        );
    }

    /**
     * Create a new exception instance for extraction failure.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function extractionFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Failed to extract extension package. The file may be corrupted.',
            1002,
            $previous
        );
    }

    /**
     * Create a new exception instance for validation failure.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function validationFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Extension package validation failed. Please ensure the package is properly formatted.',
            1003,
            $previous
        );
    }

    /**
     * Create a new exception instance for compatibility issues.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function incompatible(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Extension is not compatible with your system. Please check the requirements.',
            1004,
            $previous
        );
    }

    /**
     * Create a new exception instance for migration failures.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function migrationFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Failed to run database migrations. Your database has been rolled back to the previous state.',
            1005,
            $previous
        );
    }

    /**
     * Create a new exception instance for file system failures.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function fileSystemError(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Failed to write extension files. Please check directory permissions.',
            1006,
            $previous
        );
    }

    /**
     * Create a new exception instance for rollback failures.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function rollbackFailed(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'Installation failed and rollback encountered errors. Please contact support.',
            1007,
            $previous
        );
    }

    /**
     * Create a new exception instance for already installed.
     *
     * @param  string  $message
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function alreadyInstalled(string $message = '', ?Exception $previous = null): static
    {
        return new static(
            $message ?: 'This extension is already installed.',
            1008,
            $previous
        );
    }
}
