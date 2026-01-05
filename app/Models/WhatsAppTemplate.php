<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\User\Models\UserProxy;

class WhatsAppTemplate extends Model
{
    /**
     * Define table name of property
     *
     * @var string
     */
    protected $table = 'whatsapp_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'language',
        'status',
        'category',
        'body',
        'header',
        'footer',
        'buttons',
        'meta_template_id',
        'user_id',
    ];

    /**
     * Cast attributes to native types.
     *
     * @var array
     */
    protected $casts = [
        'buttons' => 'array',
    ];

    /**
     * Get the user that owns the template.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
