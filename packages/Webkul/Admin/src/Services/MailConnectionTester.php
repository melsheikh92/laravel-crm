<?php

namespace Webkul\Admin\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailConnectionTester
{
    /**
     * Test SMTP connection with provided configuration.
     *
     * @param  array  $config
     * @return array
     *
     * @throws Exception
     */
    public function testSmtp(array $config): array
    {
        $this->validateSmtpConfig($config);

        try {
            $mailConfig = [
                'transport'  => 'smtp',
                'host'       => $config['host'],
                'port'       => $config['port'],
                'encryption' => $config['encryption'] ?? null,
                'username'   => $config['username'],
                'password'   => $config['password'],
                'timeout'    => $config['timeout'] ?? 10,
            ];

            Config::set('mail.mailers.test_smtp', $mailConfig);
            Config::set('mail.from.address', $config['from_address']);
            Config::set('mail.from.name', $config['from_name']);

            // Test connection by sending a test email
            Mail::mailer('test_smtp')->raw('This is a test email to verify SMTP configuration.', function ($message) use ($config) {
                $message->to($config['from_address'])
                    ->subject('SMTP Configuration Test');
            });

            return [
                'success' => true,
                'message' => 'SMTP connection test successful.',
            ];
        } catch (Exception $exception) {
            return [
                'success' => false,
                'message' => 'SMTP connection test failed: '.$exception->getMessage(),
                'error'   => $exception->getMessage(),
            ];
        }
    }

    /**
     * Test IMAP connection with provided configuration.
     *
     * @param  array  $config
     * @return array
     *
     * @throws Exception
     */
    public function testImap(array $config): array
    {
        $this->validateImapConfig($config);

        try {
            // Create IMAP connection string
            $encryption = match ($config['encryption'] ?? 'tls') {
                'ssl'   => '/ssl',
                'tls'   => '/tls',
                'notls' => '/notls',
                default => '/tls',
            };

            $validateCert = ($config['validate_cert'] ?? true) ? '' : '/novalidate-cert';

            $mailbox = sprintf(
                '{%s:%s/imap%s%s}INBOX',
                $config['host'],
                $config['port'],
                $encryption,
                $validateCert
            );

            // Attempt to connect to IMAP server
            $connection = @imap_open(
                $mailbox,
                $config['username'],
                $config['password'],
                OP_READONLY,
                1
            );

            if ($connection === false) {
                $error = imap_last_error() ?: 'Unable to connect to IMAP server';

                return [
                    'success' => false,
                    'message' => 'IMAP connection test failed: '.$error,
                    'error'   => $error,
                ];
            }

            imap_close($connection);

            return [
                'success' => true,
                'message' => 'IMAP connection test successful.',
            ];
        } catch (Exception $exception) {
            return [
                'success' => false,
                'message' => 'IMAP connection test failed: '.$exception->getMessage(),
                'error'   => $exception->getMessage(),
            ];
        }
    }

    /**
     * Validate SMTP configuration.
     *
     * @param  array  $config
     * @return void
     *
     * @throws Exception
     */
    protected function validateSmtpConfig(array $config): void
    {
        $required = ['host', 'port', 'username', 'password', 'from_address', 'from_name'];

        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new Exception("Missing required SMTP configuration field: {$field}");
            }
        }

        if (! filter_var($config['from_address'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address in from_address field');
        }

        if (! is_numeric($config['port']) || $config['port'] < 1 || $config['port'] > 65535) {
            throw new Exception('Invalid port number. Must be between 1 and 65535');
        }

        if (isset($config['encryption']) && ! in_array($config['encryption'], ['tls', 'ssl', null])) {
            throw new Exception('Invalid encryption type. Must be either "tls" or "ssl"');
        }
    }

    /**
     * Validate IMAP configuration.
     *
     * @param  array  $config
     * @return void
     *
     * @throws Exception
     */
    protected function validateImapConfig(array $config): void
    {
        $required = ['host', 'port', 'username', 'password'];

        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new Exception("Missing required IMAP configuration field: {$field}");
            }
        }

        if (! is_numeric($config['port']) || $config['port'] < 1 || $config['port'] > 65535) {
            throw new Exception('Invalid port number. Must be between 1 and 65535');
        }

        if (isset($config['encryption']) && ! in_array($config['encryption'], ['tls', 'ssl', 'notls', null])) {
            throw new Exception('Invalid encryption type. Must be either "tls", "ssl", or "notls"');
        }
    }
}
