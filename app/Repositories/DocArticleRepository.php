<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;
use App\Models\DocArticle;

class DocArticleRepository extends Repository
{
    /**
     * Searchable fields
     */
    protected $fieldSearchable = [
        'title',
        'content',
        'excerpt',
        'category.name',
        'author.name',
    ];

    /**
     * Specify model class name
     */
    public function model()
    {
        return DocArticle::class;
    }

    /**
     * Get model instance
     */
    public function getModel($data = [])
    {
        return $this->model;
    }

    /**
     * Create article
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            // Set author if not provided
            if (empty($data['author_id'])) {
                $data['author_id'] = auth()->id();
            }

            // Set published_at if status is published
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $article = parent::create($data);

            // Add tags if provided
            if (!empty($data['tags'])) {
                $article->tags()->sync($data['tags']);
            }

            // Create initial version
            $article->versions()->create([
                'title' => $article->title,
                'content' => $article->content,
                'version_number' => 1,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            DB::commit();

            return $article->fresh(['category', 'author', 'tags', 'versions']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update article
     */
    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $article = $this->findOrFail($id);

            // Set published_at if status changed to published
            if (isset($data['status']) && $data['status'] === 'published' && !$article->published_at) {
                $data['published_at'] = now();
            }

            $article = parent::update($data, $id);

            // Update tags if provided
            if (isset($data['tags'])) {
                $article->tags()->sync($data['tags']);
            }

            DB::commit();

            return $article->fresh(['category', 'author', 'tags', 'versions']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get published articles
     */
    public function getPublishedArticles($categoryId = null)
    {
        $query = $this->model->published()->with(['category', 'author']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get public articles
     */
    public function getPublicArticles($categoryId = null)
    {
        $query = $this->model->published()->public()->with(['category', 'author']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get popular articles
     */
    public function getPopularArticles($limit = 10)
    {
        return $this->model->published()->popular($limit)->get();
    }

    /**
     * Get helpful articles
     */
    public function getHelpfulArticles($limit = 10)
    {
        return $this->model->published()->helpful($limit)->get();
    }

    /**
     * Search articles
     */
    public function search($query, $visibility = null)
    {
        $articles = $this->model->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%");
            });

        if ($visibility) {
            $articles->where('visibility', $visibility);
        }

        return $articles->with(['category', 'author'])->get();
    }

    /**
     * Get articles by type
     */
    public function getByType($type, $publishedOnly = true)
    {
        $query = $this->model->with(['category', 'author']);

        if ($publishedOnly) {
            $query->published();
        }

        return $query->byType($type)->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get articles by difficulty level
     */
    public function getByDifficulty($level, $publishedOnly = true)
    {
        $query = $this->model->with(['category', 'author']);

        if ($publishedOnly) {
            $query->published();
        }

        return $query->byDifficulty($level)->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get articles with videos
     */
    public function getWithVideo($publishedOnly = true)
    {
        $query = $this->model->with(['category', 'author']);

        if ($publishedOnly) {
            $query->published();
        }

        return $query->withVideo()->orderBy('published_at', 'desc')->get();
    }

    /**
     * Filter articles
     */
    public function filter(array $filters)
    {
        $query = $this->model->with(['category', 'author', 'tags']);

        // Status filter
        if (isset($filters['status'])) {
            if ($filters['status'] === 'published') {
                $query->published();
            } elseif ($filters['status'] === 'draft') {
                $query->draft();
            }
        }

        // Visibility filter
        if (isset($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        // Category filter
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Type filter
        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        // Difficulty filter
        if (isset($filters['difficulty_level'])) {
            $query->byDifficulty($filters['difficulty_level']);
        }

        // Video filter
        if (isset($filters['has_video']) && $filters['has_video']) {
            $query->withVideo();
        }

        // Search query
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('content', 'like', "%{$filters['search']}%")
                    ->orWhere('excerpt', 'like', "%{$filters['search']}%");
            });
        }

        // Tag filter
        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }

        // Author filter
        if (isset($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        // Ordering
        $orderBy = $filters['order_by'] ?? 'published_at';
        $orderDir = $filters['order_dir'] ?? 'desc';

        // Validate order_by column
        $validColumns = ['published_at', 'created_at', 'title', 'view_count', 'helpful_count'];
        if (in_array($orderBy, $validColumns)) {
            $query->orderBy($orderBy, $orderDir);
        }

        return $query;
    }

    /**
     * Increment article views
     */
    public function incrementViews($id)
    {
        $article = $this->findOrFail($id);
        $article->incrementViews();
        return $article;
    }

    /**
     * Add feedback
     */
    public function addFeedback($id, array $data)
    {
        $article = $this->findOrFail($id);

        $feedback = $article->feedback()->create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'is_helpful' => $data['is_helpful'],
            'comment' => $data['comment'] ?? null,
            'created_at' => now(),
        ]);

        // Update article counts
        if ($data['is_helpful']) {
            $article->markAsHelpful();
        } else {
            $article->markAsNotHelpful();
        }

        return $feedback;
    }

    /**
     * Publish article
     */
    public function publish($id)
    {
        return $this->update([
            'status' => 'published',
            'published_at' => now(),
        ], $id);
    }

    /**
     * Unpublish article
     */
    public function unpublish($id)
    {
        return $this->update([
            'status' => 'draft',
        ], $id);
    }

    /**
     * Get getting started articles
     */
    public function getGettingStartedArticles()
    {
        return $this->model->published()
            ->gettingStarted()
            ->with(['category', 'author'])
            ->orderBy('sort_order')
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Get API documentation articles
     */
    public function getApiDocs()
    {
        return $this->model->published()
            ->apiDocs()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Get feature guides
     */
    public function getFeatureGuides()
    {
        return $this->model->published()
            ->featureGuides()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Get troubleshooting articles
     */
    public function getTroubleshootingArticles()
    {
        return $this->model->published()
            ->troubleshooting()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->get();
    }
}
