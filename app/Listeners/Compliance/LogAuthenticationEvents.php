<?php

namespace App\Listeners\Compliance;

use App\Services\Compliance\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Config;

class LogAuthenticationEvents
{
    /**
     * The audit logger instance.
     *
     * @var AuditLogger
     */
    protected $auditLogger;

    /**
     * Create the event listener.
     *
     * @param AuditLogger $auditLogger
     * @return void
     */
    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Handle login events.
     *
     * @param Login $event
     * @return void
     */
    public function handleLogin(Login $event): void
    {
        if (!$this->shouldLogAuthenticationEvents()) {
            return;
        }

        $this->auditLogger->logCustomEvent(
            'login',
            $event->user,
            $event->user->id,
            [],
            [
                'guard' => $event->guard,
                'remember' => property_exists($event, 'remember') ? $event->remember : false,
            ],
            ['authentication', 'login', 'security'],
            $event->user->id
        );
    }

    /**
     * Handle logout events.
     *
     * @param Logout $event
     * @return void
     */
    public function handleLogout(Logout $event): void
    {
        if (!$this->shouldLogAuthenticationEvents()) {
            return;
        }

        $this->auditLogger->logCustomEvent(
            'logout',
            $event->user,
            $event->user->id,
            [],
            [
                'guard' => $event->guard,
            ],
            ['authentication', 'logout', 'security'],
            $event->user->id
        );
    }

    /**
     * Handle failed authentication attempts.
     *
     * @param Failed $event
     * @return void
     */
    public function handleFailed(Failed $event): void
    {
        if (!$this->shouldLogAuthenticationEvents()) {
            return;
        }

        // For failed attempts, we may not have a user ID if they provided invalid credentials
        $userId = $event->user ? $event->user->id : null;

        // Mask password in credentials
        $credentials = $event->credentials;
        $maskedCredentials = $credentials;
        if (isset($maskedCredentials['password'])) {
            $maskedCredentials['password'] = '***MASKED***';
        }

        $this->auditLogger->logCustomEvent(
            'login_failed',
            $event->user ?: 'App\Models\User',
            $userId,
            [],
            [
                'guard' => $event->guard,
                'credentials' => $maskedCredentials,
            ],
            ['authentication', 'login_failed', 'security', 'failed_attempt'],
            null // No user ID for failed login
        );
    }

    /**
     * Check if authentication events should be logged.
     *
     * @return bool
     */
    protected function shouldLogAuthenticationEvents(): bool
    {
        // Check if compliance is enabled
        if (!Config::get('compliance.enabled', true)) {
            return false;
        }

        // Check if audit logging is enabled
        if (!Config::get('compliance.audit_logging.enabled', true)) {
            return false;
        }

        // Check if SOC 2 authentication logging is enabled
        if (Config::get('compliance.soc2.enabled', false)) {
            return Config::get('compliance.soc2.security.log_authentication_events', true);
        }

        // Default to true if compliance and audit logging are enabled
        return true;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
        ];
    }
}
