<?php

namespace Webkul\Integration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Integration\Contracts\Integration as IntegrationContract;

class Integration extends Model implements IntegrationContract
{
    protected $table = 'integrations';

    protected $fillable = [
        'name',
        'slug',
        'provider',
        'description',
        'icon',
        'category',
        'config',
        'status',
        'is_installed',
        'installed_at',
        'installed_by',
        'version',
        'settings_url',
        'webhook_url',
    ];

    protected $casts = [
        'config' => 'array',
        'is_installed' => 'boolean',
        'installed_at' => 'datetime',
    ];
}

