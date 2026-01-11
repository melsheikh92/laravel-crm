<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

trait Auditable
{
    /**
     * Boot the Auditable trait.
     */
    public static function bootAuditable()
    {
        // Check if audit logging is enabled
        if (!Config::get('compliance.audit_logging.enabled', true)) {
            return;
        }

        // Check if this model is excluded from auditing
        if (static::isAuditingExcluded()) {
            return;
        }

        // Register event listeners for created, updated, deleted, and restored events
        static::created(function ($model) {
            $model->auditEvent('created');
        });

        static::updated(function ($model) {
            $model->auditEvent('updated');
        });

        static::deleted(function ($model) {
            $model->auditEvent('deleted');
        });

        // Support soft deletes restoration
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->auditEvent('restored');
            });
        }
    }

    /**
     * Create an audit log entry for the given event.
     *
     * @param string $event
     * @return void
     */
    protected function auditEvent(string $event): void
    {
        // Check if audit logging is enabled
        if (!Config::get('compliance.audit_logging.enabled', true)) {
            return;
        }

        // Check if this specific event should be logged
        if (!$this->shouldAuditEvent($event)) {
            return;
        }

        // Get old and new values
        $oldValues = $this->getAuditOldValues($event);
        $newValues = $this->getAuditNewValues($event);

        // Mask sensitive fields
        $oldValues = $this->maskSensitiveFields($oldValues);
        $newValues = $this->maskSensitiveFields($newValues);

        // Create audit log entry
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'tags' => $this->getAuditTags($event),
        ]);
    }

    /**
     * Check if auditing is excluded for this model.
     *
     * @return bool
     */
    protected static function isAuditingExcluded(): bool
    {
        $excludedModels = Config::get('compliance.audit_logging.excluded_models', []);

        return in_array(static::class, $excludedModels);
    }

    /**
     * Check if the given event should be audited.
     *
     * @param string $event
     * @return bool
     */
    protected function shouldAuditEvent(string $event): bool
    {
        // Check if event is in the excluded list
        $excludedEvents = Config::get('compliance.audit_logging.excluded_events', []);
        if (in_array($event, $excludedEvents)) {
            return false;
        }

        // Check if event is in the allowed list
        $allowedEvents = Config::get('compliance.audit_logging.events', [
            'created', 'updated', 'deleted', 'restored', 'viewed', 'exported'
        ]);

        return in_array($event, $allowedEvents);
    }

    /**
     * Get old values for the audit log.
     *
     * @param string $event
     * @return array
     */
    protected function getAuditOldValues(string $event): array
    {
        // For created events, there are no old values
        if ($event === 'created') {
            return [];
        }

        // For updated events, only return the original values of changed attributes
        if ($event === 'updated') {
            $oldValues = [];
            foreach ($this->getDirty() as $key => $value) {
                $oldValues[$key] = $this->getOriginal($key);
            }
            return $oldValues;
        }

        // For deleted and restored events, use current attributes
        if (in_array($event, ['deleted', 'restored'])) {
            return $this->getAttributes();
        }

        return [];
    }

    /**
     * Get new values for the audit log.
     *
     * @param string $event
     * @return array
     */
    protected function getAuditNewValues(string $event): array
    {
        // For deleted events, there are no new values
        if ($event === 'deleted') {
            return [];
        }

        // For updated events, only return changed attributes
        if ($event === 'updated') {
            return $this->getDirty();
        }

        // For created and restored events, use current attributes
        return $this->getAttributes();
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

        // Allow models to define their own sensitive fields
        if (property_exists($this, 'auditMaskedFields')) {
            foreach ($this->auditMaskedFields as $field) {
                if (isset($masked[$field])) {
                    $masked[$field] = '***MASKED***';
                }
            }
        }

        return $masked;
    }

    /**
     * Get tags for the audit log entry.
     *
     * @param string $event
     * @return array
     */
    protected function getAuditTags(string $event): array
    {
        $tags = [];

        // Add model class as a tag
        $tags[] = class_basename($this);

        // Add event as a tag
        $tags[] = $event;

        // Allow models to define custom tags
        if (method_exists($this, 'getCustomAuditTags')) {
            $customTags = $this->getCustomAuditTags($event);
            if (is_array($customTags)) {
                $tags = array_merge($tags, $customTags);
            }
        }

        return array_unique($tags);
    }

    /**
     * Manually log a custom audit event.
     *
     * @param string $event
     * @param array $oldValues
     * @param array $newValues
     * @param array $tags
     * @return void
     */
    public function auditCustomEvent(string $event, array $oldValues = [], array $newValues = [], array $tags = []): void
    {
        // Check if audit logging is enabled
        if (!Config::get('compliance.audit_logging.enabled', true)) {
            return;
        }

        // Mask sensitive fields
        $oldValues = $this->maskSensitiveFields($oldValues);
        $newValues = $this->maskSensitiveFields($newValues);

        // Merge with default tags
        $defaultTags = $this->getAuditTags($event);
        $tags = array_unique(array_merge($defaultTags, $tags));

        // Create audit log entry
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'tags' => $tags,
        ]);
    }

    /**
     * Get all audit logs for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Get the most recent audit log for this model.
     *
     * @return AuditLog|null
     */
    public function latestAuditLog()
    {
        return $this->auditLogs()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get audit logs for a specific event type.
     *
     * @param string $event
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function auditLogsByEvent(string $event)
    {
        return $this->auditLogs()->where('event', $event)->orderBy('created_at', 'desc')->get();
    }
}
