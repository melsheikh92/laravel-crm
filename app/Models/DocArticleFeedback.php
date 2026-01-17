<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\User\Models\User;

class DocArticleFeedback extends Model
{
    protected $fillable = [
        'article_id',
        'user_id',
        'is_helpful',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    public $timestamps = false;

    /**
     * Get the article that owns the feedback.
     */
    public function article()
    {
        return $this->belongsTo(DocArticle::class);
    }

    /**
     * Get the user who provided feedback.
     */
    public function user()
    {
        return $this->belongsTo(\Webkul\User\Models\User::class);
    }
}
