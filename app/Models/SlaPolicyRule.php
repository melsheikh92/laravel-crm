<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPolicyRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'sla_policy_id',
        'priority',
        'first_response_time',
        'resolution_time',
    ];

    /**
     * Relationships
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }
}
