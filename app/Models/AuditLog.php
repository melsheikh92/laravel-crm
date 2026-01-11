<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'tags',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($auditLog) {
            // Automatically capture IP address if enabled and not set
            if (config('compliance.audit_logging.capture_ip', true) && empty($auditLog->ip_address)) {
                $auditLog->ip_address = Request::ip();
            }

            // Automatically capture user agent if enabled and not set
            if (config('compliance.audit_logging.capture_user_agent', true) && empty($auditLog->user_agent)) {
                $auditLog->user_agent = Request::userAgent();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic relationship).
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */

    /**
     * Scope to filter by event type.
     */
    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to filter by multiple event types.
     */
    public function scopeByEvents($query, array $events)
    {
        return $query->whereIn('event', $events);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by auditable type.
     */
    public function scopeByAuditableType($query, string $type)
    {
        return $query->where('auditable_type', $type);
    }

    /**
     * Scope to filter by auditable model (type and ID).
     */
    public function scopeByAuditable($query, string $type, $id)
    {
        return $query->where('auditable_type', $type)
            ->where('auditable_id', $id);
    }

    /**
     * Scope to filter by model instance.
     */
    public function scopeForModel($query, Model $model)
    {
        return $query->where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate = null)
    {
        $query->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to get recent audit logs.
     */
    public function scopeRecent($query, int $limit = 100)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope to filter by tag.
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope to filter by multiple tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }

        return $query;
    }

    /**
     * Scope to filter by IP address.
     */
    public function scopeByIpAddress($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope to get created events.
     */
    public function scopeCreated($query)
    {
        return $query->where('event', 'created');
    }

    /**
     * Scope to get updated events.
     */
    public function scopeUpdated($query)
    {
        return $query->where('event', 'updated');
    }

    /**
     * Scope to get deleted events.
     */
    public function scopeDeleted($query)
    {
        return $query->where('event', 'deleted');
    }

    /**
     * Scope to get viewed events.
     */
    public function scopeViewed($query)
    {
        return $query->where('event', 'viewed');
    }

    /**
     * Scope to get exported events.
     */
    public function scopeExported($query)
    {
        return $query->where('event', 'exported');
    }

    /**
     * Scope to get restored events.
     */
    public function scopeRestored($query)
    {
        return $query->where('event', 'restored');
    }

    /**
     * Query methods
     */

    /**
     * Get all audit logs for a specific model instance.
     */
    public static function forModel(Model $model)
    {
        return static::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit logs for a user's actions.
     */
    public static function byUserActions($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit logs within a date range.
     */
    public static function getByDateRange($startDate, $endDate = null)
    {
        $query = static::where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get recent audit activity.
     */
    public static function getRecentActivity(int $limit = 100)
    {
        return static::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Helper methods
     */

    /**
     * Get the changes made in this audit log.
     */
    public function getChanges(): array
    {
        $changes = [];

        if (!empty($this->old_values) && !empty($this->new_values)) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;

                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Check if this audit log has field changes.
     */
    public function hasFieldChanges(): bool
    {
        return !empty($this->getChanges());
    }

    /**
     * Get a human-readable description of the audit event.
     */
    public function getDescription(): string
    {
        $userName = $this->user ? $this->user->name : 'System';
        $modelName = class_basename($this->auditable_type);
        $action = ucfirst($this->event);

        return "{$userName} {$this->event} {$modelName} #{$this->auditable_id}";
    }

    /**
     * Add a tag to this audit log.
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];

        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }
    }

    /**
     * Remove a tag from this audit log.
     */
    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];

        if (($key = array_search($tag, $tags)) !== false) {
            unset($tags[$key]);
            $this->tags = array_values($tags);
            $this->save();
        }
    }

    /**
     * Check if this audit log has a specific tag.
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    /**
     * Mask sensitive fields in values.
     */
    protected static function maskSensitiveFields(array $values): array
    {
        $maskedFields = config('compliance.audit_logging.masked_fields', []);
        $masked = $values;

        foreach ($maskedFields as $field) {
            if (isset($masked[$field])) {
                $masked[$field] = '***MASKED***';
            }
        }

        return $masked;
    }

    /**
     * Get the event icon for display purposes.
     */
    public function getEventIcon(): string
    {
        return match ($this->event) {
            'created' => 'plus-circle',
            'updated' => 'edit',
            'deleted' => 'trash',
            'restored' => 'arrow-counterclockwise',
            'viewed' => 'eye',
            'exported' => 'download',
            default => 'file-text',
        };
    }

    /**
     * Get the event color for display purposes.
     */
    public function getEventColor(): string
    {
        return match ($this->event) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'restored' => 'purple',
            'viewed' => 'gray',
            'exported' => 'orange',
            default => 'gray',
        };
    }
}
