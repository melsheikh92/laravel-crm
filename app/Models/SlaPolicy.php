<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_default',
        'business_hours_only',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'business_hours_only' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function rules(): HasMany
    {
        return $this->hasMany(SlaPolicyRule::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(SlaPolicyCondition::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function breaches(): HasMany
    {
        return $this->hasMany(SlaBreach::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get SLA time for priority
     */
    public function getTimeForPriority(string $priority, string $type = 'first_response'): ?int
    {
        $rule = $this->rules()->where('priority', $priority)->first();

        if (!$rule) {
            return null;
        }

        return $type === 'first_response'
            ? $rule->first_response_time
            : $rule->resolution_time;
    }
}
