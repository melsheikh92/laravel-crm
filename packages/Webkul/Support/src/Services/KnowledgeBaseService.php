<?php

namespace Webkul\Support\Services;

use Illuminate\Support\Facades\Storage;
use Webkul\Support\Repositories\KbArticleRepository;
use Webkul\Support\Repositories\KbCategoryRepository;

class KnowledgeBaseService
{
    public function __construct(
        protected KbArticleRepository $articleRepository,
        protected KbCategoryRepository $categoryRepository
    ) {
    }

    /**
     * Create article
     */
    public function createArticle(array $data)
    {
        $article = $this->articleRepository->create($data);

        // Handle file attachments if provided
        if (!empty($data['attachments'])) {
            $this->handleAttachments($article, $data['attachments']);
        }

        return $article;
    }

    /**
     * Update article
     */
    public function updateArticle($id, array $data)
    {
        $article = $this->articleRepository->update($data, $id);

        // Handle file attachments if provided
        if (isset($data['attachments'])) {
            $this->handleAttachments($article, $data['attachments']);
        }

        return $article;
    }

    /**
     * Publish article
     */
    public function publishArticle($id)
    {
        return $this->articleRepository->publish($id);
    }

    /**
     * Unpublish article
     */
    public function unpublishArticle($id)
    {
        return $this->articleRepository->unpublish($id);
    }

    /**
     * Search articles
     */
    public function searchArticles($query, $visibility = null)
    {
        return $this->articleRepository->search($query, $visibility);
    }

    /**
     * Get article with view increment
     */
    public function getArticle($id, $incrementView = true)
    {
        $article = $this->articleRepository->findOrFail($id);

        if ($incrementView && $article->isPublished()) {
            $this->articleRepository->incrementViews($id);
        }

        return $article;
    }

    /**
     * Submit article feedback
     */
    public function submitFeedback($articleId, array $data)
    {
        return $this->articleRepository->addFeedback($articleId, $data);
    }

    /**
     * Get popular articles
     */
    public function getPopularArticles($limit = 10, $visibility = null)
    {
        $articles = $this->articleRepository->getPopularArticles($limit);

        if ($visibility) {
            $articles = $articles->where('visibility', $visibility);
        }

        return $articles;
    }

    /**
     * Get helpful articles
     */
    public function getHelpfulArticles($limit = 10, $visibility = null)
    {
        $articles = $this->articleRepository->getHelpfulArticles($limit);

        if ($visibility) {
            $articles = $articles->where('visibility', $visibility);
        }

        return $articles;
    }

    /**
     * Get articles by category
     */
    public function getArticlesByCategory($categoryId, $visibility = null)
    {
        if ($visibility) {
            return $this->articleRepository->getModel()
                ->where('category_id', $categoryId)
                ->where('visibility', $visibility)
                ->published()
                ->get();
        }

        return $this->articleRepository->getPublishedArticles($categoryId);
    }

    /**
     * Get category tree
     */
    public function getCategoryTree($visibility = null)
    {
        return $this->categoryRepository->getCategoryTree($visibility);
    }

    /**
     * Handle file attachments
     */
    protected function handleAttachments($article, array $attachments)
    {
        foreach ($attachments as $attachment) {
            if ($attachment instanceof \Illuminate\Http\UploadedFile) {
                $path = $attachment->store('kb-articles', 'public');

                $article->attachments()->create([
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $attachment->getSize(),
                    'mime_type' => $attachment->getMimeType(),
                ]);
            }
        }
    }

    /**
     * Get knowledge base statistics
     */
    public function getStatistics()
    {
        return [
            'total_articles' => $this->articleRepository->getModel()->count(),
            'published_articles' => $this->articleRepository->getModel()->published()->count(),
            'draft_articles' => $this->articleRepository->getModel()->draft()->count(),
            'total_categories' => $this->categoryRepository->getModel()->count(),
            'total_views' => $this->articleRepository->getModel()->sum('view_count'),
            'average_helpfulness' => $this->getAverageHelpfulness(),
        ];
    }

    /**
     * Get average helpfulness ratio
     */
    protected function getAverageHelpfulness()
    {
        $articles = $this->articleRepository->getModel()
            ->where(function ($query) {
                $query->where('helpful_count', '>', 0)
                    ->orWhere('not_helpful_count', '>', 0);
            })
            ->get();

        if ($articles->isEmpty()) {
            return 0;
        }

        $totalRatio = $articles->sum(function ($article) {
            return $article->helpfulness_ratio;
        });

        return round($totalRatio / $articles->count(), 2);
    }

    /**
     * Get related articles
     */
    public function getRelatedArticles($articleId, $limit = 5)
    {
        $article = $this->articleRepository->findOrFail($articleId);

        // Get articles from same category
        $related = $this->articleRepository->getModel()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $articleId)
            ->published()
            ->limit($limit)
            ->get();

        // If not enough, get popular articles
        if ($related->count() < $limit) {
            $additional = $this->articleRepository
                ->getPopularArticles($limit - $related->count())
                ->where('id', '!=', $articleId);

            $related = $related->merge($additional);
        }

        return $related;
    }
}
