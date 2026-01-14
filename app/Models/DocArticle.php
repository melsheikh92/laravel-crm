<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Webkul\Tag\Models\Tag;

class DocArticle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'type',
        'difficulty_level',
        'video_url',
        'video_type',
        'reading_time_minutes',
        'status',
        'visibility',
        'view_count',
        'helpful_count',
        'not_helpful_count',
        'author_id',
        'published_at',
    ];

    protected $casts = [
        'reading_time_minutes' => 'integer',
        'view_count' => 'integer',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }

            if (empty($article->excerpt) && !empty($article->content)) {
                $article->excerpt = Str::limit(strip_tags($article->content), 200);
            }

            if (empty($article->reading_time_minutes) && !empty($article->content)) {
                $article->reading_time_minutes = $article->calculateReadingTime();
            }
        });

        static::updating(function ($article) {
            // Recalculate reading time if content changes
            if ($article->isDirty('content')) {
                $article->reading_time_minutes = $article->calculateReadingTime();
            }

            // Create version on update
            if ($article->isDirty(['title', 'content'])) {
                $article->createVersion();
            }
        });
    }

    /**
     * Relationships
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DocCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(DocSection::class, 'article_id')->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocArticleVersion::class, 'article_id')->orderBy('version_number', 'desc');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'doc_article_tags');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DocArticleAttachment::class, 'article_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(DocArticleFeedback::class, 'article_id');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeInternal($query)
    {
        return $query->where('visibility', 'internal');
    }

    public function scopeCustomerPortal($query)
    {
        return $query->whereIn('visibility', ['public', 'customer_portal']);
    }

    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('view_count', 'desc')->limit($limit);
    }

    public function scopeHelpful($query, int $limit = 10)
    {
        return $query->orderBy('helpful_count', 'desc')->limit($limit);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDifficulty($query, string $level)
    {
        return $query->where('difficulty_level', $level);
    }

    public function scopeWithVideo($query)
    {
        return $query->whereNotNull('video_url')->where('video_url', '!=', '');
    }

    public function scopeGettingStarted($query)
    {
        return $query->byType('getting-started');
    }

    public function scopeApiDocs($query)
    {
        return $query->byType('api-doc');
    }

    public function scopeFeatureGuides($query)
    {
        return $query->byType('feature-guide');
    }

    public function scopeTroubleshooting($query)
    {
        return $query->byType('troubleshooting');
    }

    /**
     * Helper methods
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
            $this->published_at &&
            $this->published_at->isPast();
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function markAsHelpful(): void
    {
        $this->increment('helpful_count');
    }

    public function markAsNotHelpful(): void
    {
        $this->increment('not_helpful_count');
    }

    public function getHelpfulnessRatioAttribute(): float
    {
        $total = $this->helpful_count + $this->not_helpful_count;

        if ($total === 0) {
            return 0;
        }

        return round(($this->helpful_count / $total) * 100, 2);
    }

    public function hasVideo(): bool
    {
        return !empty($this->video_url);
    }

    public function getVideoEmbedUrlAttribute(): string
    {
        if (empty($this->video_url)) {
            return '';
        }

        if ($this->video_type === 'youtube') {
            return $this->getYoutubeEmbedUrl($this->video_url);
        }

        if ($this->video_type === 'vimeo') {
            return $this->getVimeoEmbedUrl($this->video_url);
        }

        return $this->video_url;
    }

    protected function getYoutubeEmbedUrl(string $url): string
    {
        $videoId = '';

        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : '';
    }

    protected function getVimeoEmbedUrl(string $url): string
    {
        $videoId = '';

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://player.vimeo.com/video/{$videoId}" : '';
    }

    protected function calculateReadingTime(): int
    {
        if (empty($this->content)) {
            return 0;
        }

        $wordCount = str_word_count(strip_tags($this->content));
        $wordsPerMinute = 200;

        return max(1, (int) ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Create article version
     */
    protected function createVersion(): void
    {
        $lastVersion = $this->versions()->first();
        $versionNumber = $lastVersion ? $lastVersion->version_number + 1 : 1;

        DocArticleVersion::create([
            'article_id' => $this->id,
            'title' => $this->getOriginal('title'),
            'content' => $this->getOriginal('content'),
            'version_number' => $versionNumber,
            'created_by' => auth()->id(),
        ]);
    }
}
