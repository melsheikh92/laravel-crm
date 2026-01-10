<?php

namespace Webkul\Support\Repositories;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;
use App\Models\KbArticle;

class KbArticleRepository extends Repository
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
        return KbArticle::class;
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
}
