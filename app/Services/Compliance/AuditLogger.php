<?php

namespace App\Services\Compliance;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log a data access event (e.g., viewing sensitive information).
     *
     * @param Model|string $auditable The model instance or model class being accessed
     * @param int|null $auditableId The ID of the model (required if $auditable is a string)
     * @param array $metadata Additional metadata about the access (e.g., fields accessed)
     * @param array $tags Additional tags for the audit log
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return AuditLog|null The created audit log or null if auditing is disabled
     */
    public function logAccess(
        Model|string $auditable,
        ?int $auditableId = null,
        array $metadata = [],
        array $tags = [],
        ?int $userId = null
    ): ?AuditLog {
        if (!$this->isAuditingEnabled()) {
            return null;
        }

        [$auditableType, $auditableId] = $this->resolveAuditable($auditable, $auditableId);

        return $this->createAuditLog([
            'user_id' => $userId ?? Auth::id(),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'event' => 'viewed',
            'old_values' => [],
            'new_values' => $this->maskSensitiveFields($metadata),
            'tags' => $this->buildTags($auditableType, 'viewed', $tags),
        ]);
    }

    /**
     * Log a data change event.
     *
     * @param Model|string $auditable The model instance or model class being changed
     * @param int|null $auditableId The ID of the model (required if $auditable is a string)
     * @param array $oldValues The values before the change
     * @param array $newValues The values after the change
     * @param array $tags Additional tags for the audit log
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return AuditLog|null The created audit log or null if auditing is disabled
     */
    public function logChange(
        Model|string $auditable,
        ?int $auditableId = null,
        array $oldValues = [],
        array $newValues = [],
        array $tags = [],
        ?int $userId = null
    ): ?AuditLog {
        if (!$this->isAuditingEnabled()) {
            return null;
        }

        [$auditableType, $auditableId] = $this->resolveAuditable($auditable, $auditableId);

        return $this->createAuditLog([
            'user_id' => $userId ?? Auth::id(),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'event' => 'updated',
            'old_values' => $this->maskSensitiveFields($oldValues),
            'new_values' => $this->maskSensitiveFields($newValues),
            'tags' => $this->buildTags($auditableType, 'updated', $tags),
        ]);
    }

    /**
     * Log a data deletion event.
     *
     * @param Model|string $auditable The model instance or model class being deleted
     * @param int|null $auditableId The ID of the model (required if $auditable is a string)
     * @param array $deletedData The data that was deleted
     * @param array $tags Additional tags for the audit log
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return AuditLog|null The created audit log or null if auditing is disabled
     */
    public function logDeletion(
        Model|string $auditable,
        ?int $auditableId = null,
        array $deletedData = [],
        array $tags = [],
        ?int $userId = null
    ): ?AuditLog {
        if (!$this->isAuditingEnabled()) {
            return null;
        }

        [$auditableType, $auditableId] = $this->resolveAuditable($auditable, $auditableId);

        return $this->createAuditLog([
            'user_id' => $userId ?? Auth::id(),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'event' => 'deleted',
            'old_values' => $this->maskSensitiveFields($deletedData),
            'new_values' => [],
            'tags' => $this->buildTags($auditableType, 'deleted', $tags),
        ]);
    }

    /**
     * Log a data export event.
     *
     * @param Model|string $auditable The model instance or model class being exported
     * @param int|null $auditableId The ID of the model (required if $auditable is a string)
     * @param array $exportDetails Details about the export (e.g., format, fields)
     * @param array $tags Additional tags for the audit log
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return AuditLog|null The created audit log or null if auditing is disabled
     */
    public function logExport(
        Model|string $auditable,
        ?int $auditableId = null,
        array $exportDetails = [],
        array $tags = [],
        ?int $userId = null
    ): ?AuditLog {
        if (!$this->isAuditingEnabled()) {
            return null;
        }

        [$auditableType, $auditableId] = $this->resolveAuditable($auditable, $auditableId);

        return $this->createAuditLog([
            'user_id' => $userId ?? Auth::id(),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'event' => 'exported',
            'old_values' => [],
            'new_values' => $this->maskSensitiveFields($exportDetails),
            'tags' => $this->buildTags($auditableType, 'exported', $tags),
        ]);
    }

    /**
     * Log a custom audit event.
     *
     * @param string $event The event name
     * @param Model|string $auditable The model instance or model class
     * @param int|null $auditableId The ID of the model (required if $auditable is a string)
     * @param array $oldValues The values before the event
     * @param array $newValues The values after the event
     * @param array $tags Additional tags for the audit log
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return AuditLog|null The created audit log or null if auditing is disabled
     */
    public function logCustomEvent(
        string $event,
        Model|string $auditable,
        ?int $auditableId = null,
        array $oldValues = [],
        array $newValues = [],
        array $tags = [],
        ?int $userId = null
    ): ?AuditLog {
        if (!$this->isAuditingEnabled()) {
            return null;
        }

        [$auditableType, $auditableId] = $this->resolveAuditable($auditable, $auditableId);

        return $this->createAuditLog([
            'user_id' => $userId ?? Auth::id(),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'event' => $event,
            'old_values' => $this->maskSensitiveFields($oldValues),
            'new_values' => $this->maskSensitiveFields($newValues),
            'tags' => $this->buildTags($auditableType, $event, $tags),
        ]);
    }

    /**
     * Create an audit log entry.
     *
     * @param array $data The audit log data
     * @return AuditLog The created audit log
     */
    protected function createAuditLog(array $data): AuditLog
    {
        // IP address and user agent are automatically captured by the AuditLog model
        return AuditLog::create($data);
    }

    /**
     * Check if audit logging is enabled.
     *
     * @return bool
     */
    protected function isAuditingEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.audit_logging.enabled', true);
    }

    /**
     * Resolve the auditable type and ID from the given parameter.
     *
     * @param Model|string $auditable
     * @param int|null $auditableId
     * @return array [auditableType, auditableId]
     */
    protected function resolveAuditable(Model|string $auditable, ?int $auditableId = null): array
    {
        if ($auditable instanceof Model) {
            return [get_class($auditable), $auditable->getKey()];
        }

        return [$auditable, $auditableId];
    }

    /**
     * Build tags array for the audit log.
     *
     * @param string $auditableType
     * @param string $event
     * @param array $additionalTags
     * @return array
     */
    protected function buildTags(string $auditableType, string $event, array $additionalTags = []): array
    {
        $tags = [
            class_basename($auditableType),
            $event,
        ];

        return array_unique(array_merge($tags, $additionalTags));
    }

    /**
     * Mask sensitive fields in the given values.
     *
     * @param array $values
     * @return array
     */
    protected function maskSensitiveFields(array $values): array
    {
        $maskedFields = Config::get('compliance.audit_logging.masked_fields', []);
        $masked = $values;

        foreach ($maskedFields as $field) {
            if (isset($masked[$field])) {
                $masked[$field] = '***MASKED***';
            }
        }

        return $masked;
    }
}
