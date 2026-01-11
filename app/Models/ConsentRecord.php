<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class ConsentRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'consent_type',
        'purpose',
        'given_at',
        'withdrawn_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'given_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($consentRecord) {
            // Automatically capture IP address if enabled and not set
            if (config('compliance.consent.capture_ip', true) && empty($consentRecord->ip_address)) {
                $consentRecord->ip_address = Request::ip();
            }

            // Automatically capture user agent if enabled and not set
            if (config('compliance.consent.capture_user_agent', true) && empty($consentRecord->user_agent)) {
                $consentRecord->user_agent = Request::userAgent();
            }

            // Set given_at timestamp if not set
            if (empty($consentRecord->given_at)) {
                $consentRecord->given_at = now();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the user that gave the consent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */

    /**
     * Scope to get active consents (not withdrawn).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('withdrawn_at');
    }

    /**
     * Scope to get withdrawn consents.
     */
    public function scopeWithdrawn($query)
    {
        return $query->whereNotNull('withdrawn_at');
    }

    /**
     * Scope to filter by consent type.
     */
    public function scopeByType($query, string $consentType)
    {
        return $query->where('consent_type', $consentType);
    }

    /**
     * Scope to filter by multiple consent types.
     */
    public function scopeByTypes($query, array $consentTypes)
    {
        return $query->whereIn('consent_type', $consentTypes);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range when consent was given.
     */
    public function scopeGivenBetween($query, $startDate, $endDate = null)
    {
        $query->where('given_at', '>=', $startDate);

        if ($endDate) {
            $query->where('given_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to filter by date range when consent was withdrawn.
     */
    public function scopeWithdrawnBetween($query, $startDate, $endDate = null)
    {
        $query->where('withdrawn_at', '>=', $startDate);

        if ($endDate) {
            $query->where('withdrawn_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to get recently given consents.
     */
    public function scopeRecentlyGiven($query, int $limit = 100)
    {
        return $query->orderBy('given_at', 'desc')->limit($limit);
    }

    /**
     * Scope to get recently withdrawn consents.
     */
    public function scopeRecentlyWithdrawn($query, int $limit = 100)
    {
        return $query->whereNotNull('withdrawn_at')
            ->orderBy('withdrawn_at', 'desc')
            ->limit($limit);
    }

    /**
     * Scope to get expired consents (withdrawn before a certain date).
     */
    public function scopeExpiredBefore($query, $date)
    {
        return $query->whereNotNull('withdrawn_at')
            ->where('withdrawn_at', '<', $date);
    }

    /**
     * Scope to filter by IP address.
     */
    public function scopeByIpAddress($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Query methods
     */

    /**
     * Get all active consents for a specific user.
     */
    public static function getActiveForUser($userId)
    {
        return static::where('user_id', $userId)
            ->whereNull('withdrawn_at')
            ->orderBy('given_at', 'desc')
            ->get();
    }

    /**
     * Get consent history for a specific user.
     */
    public static function getHistoryForUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('given_at', 'desc')
            ->get();
    }

    /**
     * Get all consents of a specific type.
     */
    public static function getByType(string $consentType)
    {
        return static::where('consent_type', $consentType)
            ->orderBy('given_at', 'desc')
            ->get();
    }

    /**
     * Get active consents of a specific type for a user.
     */
    public static function getActiveByType($userId, string $consentType)
    {
        return static::where('user_id', $userId)
            ->where('consent_type', $consentType)
            ->whereNull('withdrawn_at')
            ->orderBy('given_at', 'desc')
            ->get();
    }

    /**
     * Get consent statistics by type.
     */
    public static function getStatsByType()
    {
        return static::selectRaw('consent_type,
            COUNT(*) as total,
            SUM(CASE WHEN withdrawn_at IS NULL THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN withdrawn_at IS NOT NULL THEN 1 ELSE 0 END) as withdrawn')
            ->groupBy('consent_type')
            ->get();
    }

    /**
     * Helper methods
     */

    /**
     * Check if the consent is active (not withdrawn).
     */
    public function isActive(): bool
    {
        return is_null($this->withdrawn_at);
    }

    /**
     * Check if the consent is withdrawn.
     */
    public function isWithdrawn(): bool
    {
        return !is_null($this->withdrawn_at);
    }

    /**
     * Withdraw the consent.
     */
    public function withdraw(): bool
    {
        if ($this->isWithdrawn()) {
            return false;
        }

        $this->withdrawn_at = now();
        return $this->save();
    }

    /**
     * Reinstate a withdrawn consent.
     */
    public function reinstate(): bool
    {
        if ($this->isActive()) {
            return false;
        }

        $this->withdrawn_at = null;
        return $this->save();
    }

    /**
     * Get the duration of the consent in days.
     */
    public function getDurationInDays(): ?int
    {
        if ($this->isWithdrawn()) {
            return $this->given_at->diffInDays($this->withdrawn_at);
        }

        return $this->given_at->diffInDays(now());
    }

    /**
     * Get a human-readable description of the consent record.
     */
    public function getDescription(): string
    {
        $userName = $this->user ? $this->user->name : 'Unknown User';
        $status = $this->isActive() ? 'active' : 'withdrawn';

        return "{$userName} gave {$status} consent for {$this->consent_type}";
    }

    /**
     * Check if consent was given within a specific period.
     */
    public function wasGivenWithinDays(int $days): bool
    {
        return $this->given_at->isAfter(now()->subDays($days));
    }

    /**
     * Check if consent was withdrawn within a specific period.
     */
    public function wasWithdrawnWithinDays(int $days): bool
    {
        if (!$this->isWithdrawn()) {
            return false;
        }

        return $this->withdrawn_at->isAfter(now()->subDays($days));
    }

    /**
     * Add metadata to the consent record.
     */
    public function addMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * Get metadata value by key.
     */
    public function getMetadata(string $key, $default = null)
    {
        return ($this->metadata ?? [])[$key] ?? $default;
    }

    /**
     * Check if the consent has specific metadata key.
     */
    public function hasMetadata(string $key): bool
    {
        return isset(($this->metadata ?? [])[$key]);
    }

    /**
     * Get the consent type label for display.
     */
    public function getTypeLabel(): string
    {
        $types = config('compliance.consent.types', []);
        $typeConfig = $types[$this->consent_type] ?? null;

        if ($typeConfig && isset($typeConfig['description'])) {
            return $typeConfig['description'];
        }

        return ucwords(str_replace('_', ' ', $this->consent_type));
    }
}
