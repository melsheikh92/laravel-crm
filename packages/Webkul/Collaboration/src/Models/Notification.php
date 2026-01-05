<?php

namespace Webkul\Collaboration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Collaboration\Contracts\Notification as NotificationContract;
use Webkul\User\Models\UserProxy;

class Notification extends Model implements NotificationContract
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }
}

