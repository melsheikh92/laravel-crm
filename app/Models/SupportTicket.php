<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\Contact\Models\Person;
use Webkul\Tag\Models\Tag;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes, Auditable, Encryptable;

    /**
     * The attributes that should be encrypted.
     *
     * @var array
     */
    protected $encrypted = [
        'title',
        'description',
    ];

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'status',
        'priority',
        'type',
        'customer_id',
        'assigned_to',
        'sla_id',
        'sla_due_at',
        'sla_breached',
        'resolved_at',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'sla_breached' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });
    }

    /**
     * Generate unique ticket number
     */
    public static function generateTicketNumber(): string
    {
        $year = date('Y');
        $lastTicket = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastTicket ? (int) substr($lastTicket->ticket_number, -4) + 1 : 1;

        return 'TKT-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ticket_tags', 'ticket_id', 'tag_id');
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_watchers', 'ticket_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'contact_id');
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeOverdue($query)
    {
        return $query->where('resolution_due_at', '<', now())
            ->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeBreached($query)
    {
        return $query->where('sla_breached', true);
    }

    /**
     * Helper methods
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function isOverdue(): bool
    {
        return $this->sla_due_at &&
            $this->sla_due_at->isPast() &&
            !$this->isClosed();
    }

    public function isBreached(): bool
    {
        return $this->sla_breached;
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'open' => 'blue',
            'in_progress' => 'yellow',
            'waiting_customer' => 'orange',
            'waiting_internal' => 'purple',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getPriorityColor(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'normal' => 'blue',
            'low' => 'gray',
            default => 'gray',
        };
    }
}
