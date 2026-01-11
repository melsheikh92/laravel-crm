<?php

namespace App\Listeners\Compliance;

use App\Events\SensitiveDataAccessed;
use App\Services\Compliance\AuditLogger;
use Illuminate\Support\Facades\Config;

class LogSensitiveDataAccess
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
     * Handle the event.
     *
     * @param SensitiveDataAccessed $event
     * @return void
     */
    public function handle(SensitiveDataAccessed $event): void
    {
        if (!$this->shouldLogSensitiveDataAccess()) {
            return;
        }

        $metadata = [
            'fields_accessed' => $event->fields ?? [],
            'access_type' => $event->accessType ?? 'viewed',
        ];

        // Merge additional metadata if provided
        if (property_exists($event, 'metadata') && is_array($event->metadata)) {
            $metadata = array_merge($metadata, $event->metadata);
        }

        // Merge additional tags if provided
        $tags = ['sensitive_data_access', 'data_access', 'security'];
        if (property_exists($event, 'tags') && is_array($event->tags)) {
            $tags = array_merge($tags, $event->tags);
        }

        $this->auditLogger->logAccess(
            $event->model,
            $event->model->id,
            $metadata,
            $tags,
            property_exists($event, 'userId') ? $event->userId : null
        );
    }

    /**
     * Check if sensitive data access should be logged.
     *
     * @return bool
     */
    protected function shouldLogSensitiveDataAccess(): bool
    {
        // Check if compliance is enabled
        if (!Config::get('compliance.enabled', true)) {
            return false;
        }

        // Check if audit logging is enabled
        if (!Config::get('compliance.audit_logging.enabled', true)) {
            return false;
        }

        // Check if SOC 2 data access logging is enabled
        if (Config::get('compliance.soc2.enabled', false)) {
            return Config::get('compliance.soc2.security.log_data_access', true);
        }

        // Check if HIPAA PHI audit is enabled
        if (Config::get('compliance.hipaa.enabled', false)) {
            return Config::get('compliance.hipaa.phi_audit.enabled', false);
        }

        // Default to true if compliance and audit logging are enabled
        return true;
    }
}
