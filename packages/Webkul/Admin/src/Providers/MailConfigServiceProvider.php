<?php

namespace Webkul\Admin\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (!$this->shouldLoadMailConfig()) {
            return;
        }

        $this->loadMailConfigFromDatabase();
    }

    /**
     * Check if mail configuration should be loaded from database.
     *
     * @return bool
     */
    protected function shouldLoadMailConfig(): bool
    {
        try {
            return Schema::hasTable('core_config');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Load mail configuration from database and override runtime config.
     *
     * @return void
     */
    protected function loadMailConfigFromDatabase(): void
    {
        try {
            $this->loadSmtpConfiguration();
            $this->loadImapConfiguration();
        } catch (\Exception $e) {
            // Silently fail if there's an error loading config
            // This ensures the application can still boot even if config is corrupted
        }
    }

    /**
     * Load SMTP configuration from database.
     *
     * @return void
     */
    protected function loadSmtpConfiguration(): void
    {
        $smtpHost = app('core')->getConfigData('email.smtp.account.host');
        $smtpPort = app('core')->getConfigData('email.smtp.account.port');
        $smtpEncryption = app('core')->getConfigData('email.smtp.account.encryption');
        $smtpUsername = app('core')->getConfigData('email.smtp.account.username');
        $smtpPassword = app('core')->getConfigData('email.smtp.account.password');
        $fromAddress = app('core')->getConfigData('email.smtp.account.from_address');
        $fromName = app('core')->getConfigData('email.smtp.account.from_name');

        if ($smtpHost) {
            Config::set('mail.mailers.smtp.host', $smtpHost);
        }

        if ($smtpPort) {
            Config::set('mail.mailers.smtp.port', $smtpPort);
        }

        if ($smtpEncryption) {
            Config::set('mail.mailers.smtp.encryption', $smtpEncryption);
        }

        if ($smtpUsername) {
            Config::set('mail.mailers.smtp.username', $smtpUsername);
        }

        if ($smtpPassword) {
            Config::set('mail.mailers.smtp.password', $smtpPassword);
        }

        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
        }

        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }
    }

    /**
     * Load IMAP configuration from database.
     *
     * @return void
     */
    protected function loadImapConfiguration(): void
    {
        $imapHost = app('core')->getConfigData('email.imap.account.host');
        $imapPort = app('core')->getConfigData('email.imap.account.port');
        $imapEncryption = app('core')->getConfigData('email.imap.account.encryption');
        $imapValidateCert = app('core')->getConfigData('email.imap.account.validate_cert');
        $imapUsername = app('core')->getConfigData('email.imap.account.username');
        $imapPassword = app('core')->getConfigData('email.imap.account.password');

        if ($imapHost) {
            Config::set('imap.accounts.default.host', $imapHost);
        }

        if ($imapPort) {
            Config::set('imap.accounts.default.port', $imapPort);
        }

        if ($imapEncryption) {
            Config::set('imap.accounts.default.encryption', $imapEncryption);
        }

        if (!is_null($imapValidateCert)) {
            Config::set('imap.accounts.default.validate_cert', (bool) $imapValidateCert);
        }

        if ($imapUsername) {
            Config::set('imap.accounts.default.username', $imapUsername);
        }

        if ($imapPassword) {
            Config::set('imap.accounts.default.password', $imapPassword);
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
