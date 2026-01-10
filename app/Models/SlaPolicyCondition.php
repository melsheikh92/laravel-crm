<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPolicyCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'sla_policy_id',
        'condition_type',
        'condition_value',
    ];

    /**
     * Relationships
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }
}
