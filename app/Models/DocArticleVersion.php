<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\User\Models\User;

class DocArticleVersion extends Model
{
    protected $fillable = [
        'article_id',
        'title',
        'content',
        'version_number',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    public $timestamps = false;

    /**
     * Get the article that owns the version.
     */
    public function article()
    {
        return $this->belongsTo(DocArticle::class);
    }

    /**
     * Get the user who created the version.
     */
    public function creator()
    {
        return $this->belongsTo(\Webkul\User\Models\User::class, 'created_by');
    }
}
