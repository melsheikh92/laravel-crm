<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataDeletionRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'email',
        'requested_at',
        'processed_at',
        'status',
        'notes',
        'processed_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            // Set requested_at timestamp if not set
            if (empty($request->requested_at)) {
                $request->requested_at = now();
            }

            // Set default status if not set
            if (empty($request->status)) {
                $request->status = 'pending';
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the user who requested data deletion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who processed the deletion request.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope to get pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get processing requests.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope to get completed requests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get failed requests.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get cancelled requests.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to get processed requests (completed, failed, or cancelled).
     */
    public function scopeProcessed($query)
    {
        return $query->whereNotNull('processed_at');
    }

    /**
     * Scope to get unprocessed requests (pending or processing).
     */
    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by multiple statuses.
     */
    public function scopeByStatuses($query, array $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by email.
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope to filter by date range when request was made.
     */
    public function scopeRequestedBetween($query, $startDate, $endDate = null)
    {
        $query->where('requested_at', '>=', $startDate);

        if ($endDate) {
            $query->where('requested_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to filter by date range when request was processed.
     */
    public function scopeProcessedBetween($query, $startDate, $endDate = null)
    {
        $query->where('processed_at', '>=', $startDate);

        if ($endDate) {
            $query->where('processed_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to get recently requested deletions.
     */
    public function scopeRecentlyRequested($query, int $limit = 100)
    {
        return $query->orderBy('requested_at', 'desc')->limit($limit);
    }

    /**
     * Scope to get recently processed deletions.
     */
    public function scopeRecentlyProcessed($query, int $limit = 100)
    {
        return $query->whereNotNull('processed_at')
            ->orderBy('processed_at', 'desc')
            ->limit($limit);
    }

    /**
     * Scope to get overdue requests (pending for more than specified days).
     */
    public function scopeOverdue($query, int $days = 30)
    {
        $overdueDate = now()->subDays($days);
        return $query->where('status', 'pending')
            ->where('requested_at', '<', $overdueDate);
    }

    /**
     * Query methods
     */

    /**
     * Get all pending requests for a specific user.
     */
    public static function getPendingForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    /**
     * Get all requests for a specific user.
     */
    public static function getForUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    /**
     * Get all requests for a specific email.
     */
    public static function getByEmail(string $email)
    {
        return static::where('email', $email)
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    /**
     * Get deletion request statistics by status.
     */
    public static function getStatsByStatus()
    {
        return static::selectRaw('status,
            COUNT(*) as total,
            MIN(requested_at) as oldest_request,
            MAX(requested_at) as newest_request')
            ->groupBy('status')
            ->get();
    }

    /**
     * Get overdue requests requiring attention.
     */
    public static function getOverdueRequests(int $days = 30)
    {
        $overdueDate = now()->subDays($days);
        return static::where('status', 'pending')
            ->where('requested_at', '<', $overdueDate)
            ->orderBy('requested_at', 'asc')
            ->get();
    }

    /**
     * Helper methods
     */

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the request is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the request is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the request is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if the request has been processed.
     */
    public function isProcessed(): bool
    {
        return !is_null($this->processed_at);
    }

    /**
     * Mark the request as processing.
     */
    public function markAsProcessing(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = 'processing';
        return $this->save();
    }

    /**
     * Mark the request as completed.
     */
    public function markAsCompleted($processedBy = null, ?string $notes = null): bool
    {
        $this->status = 'completed';
        $this->processed_at = now();

        if ($processedBy) {
            $this->processed_by = is_object($processedBy) ? $processedBy->id : $processedBy;
        }

        if ($notes) {
            $this->notes = $notes;
        }

        return $this->save();
    }

    /**
     * Mark the request as failed.
     */
    public function markAsFailed($processedBy = null, ?string $notes = null): bool
    {
        $this->status = 'failed';
        $this->processed_at = now();

        if ($processedBy) {
            $this->processed_by = is_object($processedBy) ? $processedBy->id : $processedBy;
        }

        if ($notes) {
            $this->notes = $notes;
        }

        return $this->save();
    }

    /**
     * Cancel the request.
     */
    public function cancel($processedBy = null, ?string $notes = null): bool
    {
        if ($this->isProcessed()) {
            return false;
        }

        $this->status = 'cancelled';
        $this->processed_at = now();

        if ($processedBy) {
            $this->processed_by = is_object($processedBy) ? $processedBy->id : $processedBy;
        }

        if ($notes) {
            $this->notes = $notes;
        }

        return $this->save();
    }

    /**
     * Get the processing duration in hours.
     */
    public function getProcessingDurationInHours(): ?float
    {
        if (!$this->isProcessed()) {
            return null;
        }

        return $this->requested_at->diffInHours($this->processed_at, true);
    }

    /**
     * Get the processing duration in days.
     */
    public function getProcessingDurationInDays(): ?int
    {
        if (!$this->isProcessed()) {
            return null;
        }

        return $this->requested_at->diffInDays($this->processed_at);
    }

    /**
     * Get the days since request was made.
     */
    public function getDaysSinceRequest(): int
    {
        return $this->requested_at->diffInDays(now());
    }

    /**
     * Check if the request is overdue (pending for more than specified days).
     */
    public function isOverdue(int $days = 30): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        return $this->getDaysSinceRequest() > $days;
    }

    /**
     * Get a human-readable description of the deletion request.
     */
    public function getDescription(): string
    {
        $status = ucfirst($this->status);
        return "Data deletion request for {$this->email} - {$status}";
    }

    /**
     * Add notes to the deletion request.
     */
    public function addNotes(string $notes): void
    {
        $existingNotes = $this->notes ?? '';
        $timestamp = now()->toDateTimeString();
        $separator = empty($existingNotes) ? '' : "\n---\n";

        $this->notes = $existingNotes . $separator . "[{$timestamp}] {$notes}";
        $this->save();
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the status color for display purposes.
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the status icon for display purposes.
     */
    public function getStatusIcon(): string
    {
        return match ($this->status) {
            'pending' => 'clock',
            'processing' => 'gear',
            'completed' => 'check-circle',
            'failed' => 'x-circle',
            'cancelled' => 'slash-circle',
            default => 'file-text',
        };
    }
}
