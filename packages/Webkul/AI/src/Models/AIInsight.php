<?php

namespace Webkul\AI\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\AI\Contracts\AIInsight as AIInsightContract;
use Webkul\Contact\Models\PersonProxy;
use Webkul\Lead\Models\LeadProxy;

class AIInsight extends Model implements AIInsightContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ai_insights';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'entity_type',
        'entity_id',
        'title',
        'description',
        'priority',
        'metadata',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'priority' => 'integer',
    ];

    /**
     * Scope to filter by entity type and ID.
     */
    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
            ->where('entity_id', $entityId);
    }

    /**
     * Scope to filter by insight type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to order by priority.
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc');
    }
}

