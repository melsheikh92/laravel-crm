<?php

namespace Webkul\Integration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Integration\Contracts\IntegrationLog as IntegrationLogContract;

class IntegrationLog extends Model implements IntegrationLogContract
{
    protected $table = 'integration_logs';

    protected $fillable = [
        'integration_id',
        'level',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}

