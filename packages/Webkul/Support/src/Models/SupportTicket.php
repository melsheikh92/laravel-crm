<?php

namespace Webkul\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Support\Contracts\SupportTicket as SupportTicketContract;

class SupportTicket extends Model implements SupportTicketContract
{
    protected $table = 'support_tickets';

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
}

