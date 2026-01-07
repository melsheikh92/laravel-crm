<?php

namespace Webkul\Admin\Helpers;

class MailConfig
{
    /**
     * Get SMTP configuration from database with fallback to config defaults.
     *
     * @return array
     */
    public function getSmtpConfiguration(): array
    {
        return [
            'host'         => core()->getConfigData('email.smtp.account.host') ?? config('mail.mailers.smtp.host'),
            'port'         => core()->getConfigData('email.smtp.account.port') ?? config('mail.mailers.smtp.port'),
            'encryption'   => core()->getConfigData('email.smtp.account.encryption') ?? config('mail.mailers.smtp.encryption'),
            'username'     => core()->getConfigData('email.smtp.account.username') ?? config('mail.mailers.smtp.username'),
            'password'     => core()->getConfigData('email.smtp.account.password') ?? config('mail.mailers.smtp.password'),
            'from_address' => core()->getConfigData('email.smtp.account.from_address') ?? config('mail.from.address'),
            'from_name'    => core()->getConfigData('email.smtp.account.from_name') ?? config('mail.from.name'),
        ];
    }

    /**
     * Get IMAP configuration from database with fallback to config defaults.
     *
     * @return array
     */
    public function getImapConfiguration(): array
    {
        return [
            'host'          => core()->getConfigData('email.imap.account.host') ?? config('imap.accounts.default.host'),
            'port'          => core()->getConfigData('email.imap.account.port') ?? config('imap.accounts.default.port'),
            'encryption'    => core()->getConfigData('email.imap.account.encryption') ?? config('imap.accounts.default.encryption'),
            'validate_cert' => core()->getConfigData('email.imap.account.validate_cert') ?? config('imap.accounts.default.validate_cert'),
            'username'      => core()->getConfigData('email.imap.account.username') ?? config('imap.accounts.default.username'),
            'password'      => core()->getConfigData('email.imap.account.password') ?? config('imap.accounts.default.password'),
        ];
    }

    /**
     * Get all mail configuration (both SMTP and IMAP).
     *
     * @return array
     */
    public function getAllConfiguration(): array
    {
        return [
            'smtp' => $this->getSmtpConfiguration(),
            'imap' => $this->getImapConfiguration(),
        ];
    }

    /**
     * Check if SMTP configuration is available.
     *
     * @return bool
     */
    public function hasSmtpConfiguration(): bool
    {
        $config = $this->getSmtpConfiguration();

        return ! empty($config['host']) && ! empty($config['port']);
    }

    /**
     * Check if IMAP configuration is available.
     *
     * @return bool
     */
    public function hasImapConfiguration(): bool
    {
        $config = $this->getImapConfiguration();

        return ! empty($config['host']) && ! empty($config['port']);
    }

    /**
     * Get a specific SMTP configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getSmtpConfig(string $key, mixed $default = null): mixed
    {
        $config = $this->getSmtpConfiguration();

        return $config[$key] ?? $default;
    }

    /**
     * Get a specific IMAP configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getImapConfig(string $key, mixed $default = null): mixed
    {
        $config = $this->getImapConfiguration();

        return $config[$key] ?? $default;
    }
}
