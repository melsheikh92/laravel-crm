<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocArticleAttachment extends Model
{
    protected $fillable = [
        'article_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'download_count',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * Get the article that owns the attachment.
     */
    public function article()
    {
        return $this->belongsTo(DocArticle::class);
    }
}
