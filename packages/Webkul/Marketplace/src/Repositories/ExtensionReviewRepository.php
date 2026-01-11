<?php

namespace Webkul\Marketplace\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Marketplace\Contracts\ExtensionReview;

class ExtensionReviewRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'title',
        'review_text',
        'rating',
        'status',
        'is_verified_purchase',
        'user_id',
        'extension_id',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return ExtensionReview::class;
    }

    /**
     * Get all reviews for an extension.
     *
     * @param  int  $extensionId
     * @param  bool  $approvedOnly
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByExtension($extensionId, $approvedOnly = true, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($extensionId, $approvedOnly) {
            $query = $query->where('extension_id', $extensionId)
                ->with(['user']);

            if ($approvedOnly) {
                $query->approved();
            }

            return $query->orderBy('created_at', 'desc');
        })->paginate($perPage);
    }

    /**
     * Get approved reviews only.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApproved($columns = ['*'])
    {
        return $this->scopeQuery(function ($query) use ($columns) {
            return $query->approved()
                ->with(['user', 'extension'])
                ->select($columns);
        })->all();
    }

    /**
     * Get pending reviews awaiting moderation.
     *
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPending($extensionId = null)
    {
        return $this->scopeQuery(function ($query) use ($extensionId) {
            $query = $query->pending()
                ->with(['user', 'extension']);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->orderBy('created_at', 'asc');
        })->all();
    }

    /**
     * Get reviews by a specific user.
     *
     * @param  int  $userId
     * @param  bool  $approvedOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUser($userId, $approvedOnly = true)
    {
        return $this->scopeQuery(function ($query) use ($userId, $approvedOnly) {
            $query = $query->where('user_id', $userId)
                ->with(['extension']);

            if ($approvedOnly) {
                $query->approved();
            }

            return $query->orderBy('created_at', 'desc');
        })->all();
    }

    /**
     * Get reviews filtered by rating.
     *
     * @param  int  $rating
     * @param  int|null  $extensionId
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByRating($rating, $extensionId = null, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($rating, $extensionId) {
            $query = $query->approved()
                ->byRating($rating)
                ->with(['user', 'extension']);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->orderBy('created_at', 'desc');
        })->paginate($perPage);
    }

    /**
     * Get most helpful reviews.
     *
     * @param  int  $limit
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMostHelpful($limit = 10, $extensionId = null)
    {
        return $this->scopeQuery(function ($query) use ($limit, $extensionId) {
            $query = $query->approved()
                ->mostHelpful()
                ->with(['user', 'extension'])
                ->where('helpful_count', '>', 0);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->limit($limit);
        })->all();
    }

    /**
     * Get verified purchase reviews.
     *
     * @param  int|null  $extensionId
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getVerifiedPurchases($extensionId = null, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($extensionId) {
            $query = $query->approved()
                ->verifiedPurchase()
                ->with(['user', 'extension']);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->orderBy('created_at', 'desc');
        })->paginate($perPage);
    }

    /**
     * Calculate average rating for an extension.
     *
     * @param  int  $extensionId
     * @return float
     */
    public function calculateAverageRating($extensionId)
    {
        $average = $this->scopeQuery(function ($query) use ($extensionId) {
            return $query->approved()
                ->where('extension_id', $extensionId);
        })->avg('rating');

        return round($average ?? 0, 2);
    }

    /**
     * Get rating distribution for an extension.
     *
     * @param  int  $extensionId
     * @return array
     */
    public function getRatingDistribution($extensionId)
    {
        $reviews = $this->findWhere([
            'extension_id' => $extensionId,
            'status'       => 'approved',
        ]);

        return [
            '5_star' => $reviews->where('rating', 5)->count(),
            '4_star' => $reviews->where('rating', 4)->count(),
            '3_star' => $reviews->where('rating', 3)->count(),
            '2_star' => $reviews->where('rating', 2)->count(),
            '1_star' => $reviews->where('rating', 1)->count(),
        ];
    }

    /**
     * Get review statistics for an extension.
     *
     * @param  int  $extensionId
     * @return array
     */
    public function getStatistics($extensionId)
    {
        $reviews = $this->findWhere([
            'extension_id' => $extensionId,
            'status'       => 'approved',
        ]);

        $distribution = $this->getRatingDistribution($extensionId);

        return [
            'total_reviews'        => $reviews->count(),
            'average_rating'       => $this->calculateAverageRating($extensionId),
            'verified_purchases'   => $reviews->where('is_verified_purchase', true)->count(),
            'total_helpful_count'  => $reviews->sum('helpful_count'),
            'rating_distribution'  => $distribution,
            'pending_reviews'      => $this->count([
                'extension_id' => $extensionId,
                'status'       => 'pending',
            ]),
        ];
    }

    /**
     * Check if a user has already reviewed an extension.
     *
     * @param  int  $userId
     * @param  int  $extensionId
     * @return bool
     */
    public function hasUserReviewed($userId, $extensionId)
    {
        return $this->count([
            'user_id'      => $userId,
            'extension_id' => $extensionId,
        ]) > 0;
    }

    /**
     * Get user's review for an extension.
     *
     * @param  int  $userId
     * @param  int  $extensionId
     * @return \Webkul\Marketplace\Contracts\ExtensionReview|null
     */
    public function getUserReview($userId, $extensionId)
    {
        return $this->findOneWhere([
            'user_id'      => $userId,
            'extension_id' => $extensionId,
        ]);
    }

    /**
     * Get recent reviews.
     *
     * @param  int  $limit
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecent($limit = 10, $extensionId = null)
    {
        return $this->scopeQuery(function ($query) use ($limit, $extensionId) {
            $query = $query->approved()
                ->with(['user', 'extension']);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->orderBy('created_at', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Filter reviews by multiple criteria.
     *
     * @param  array  $filters
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function filter($filters, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($filters) {
            $query = $query->with(['user', 'extension']);

            // Filter by extension
            if (! empty($filters['extension_id'])) {
                $query->where('extension_id', $filters['extension_id']);
            }

            // Filter by user
            if (! empty($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            // Filter by status
            if (! empty($filters['status'])) {
                $query->where('status', $filters['status']);
            } else {
                // Default to approved only if not specified
                $query->approved();
            }

            // Filter by rating
            if (! empty($filters['rating'])) {
                $query->byRating($filters['rating']);
            }

            // Filter by minimum rating
            if (! empty($filters['min_rating'])) {
                $query->where('rating', '>=', $filters['min_rating']);
            }

            // Filter by verified purchases
            if (isset($filters['verified_purchase']) && $filters['verified_purchase']) {
                $query->verifiedPurchase();
            }

            // Search in review text
            if (! empty($filters['search'])) {
                $term = $filters['search'];
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('review_text', 'like', "%{$term}%");
                });
            }

            // Sort
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';

            if ($sortBy === 'helpful') {
                $query->mostHelpful();
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            return $query;
        })->paginate($perPage);
    }

    /**
     * Search reviews by term.
     *
     * @param  string  $term
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search($term, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($term) {
            return $query->approved()
                ->with(['user', 'extension'])
                ->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('review_text', 'like', "%{$term}%");
                })
                ->orderBy('created_at', 'desc');
        })->paginate($perPage);
    }

    /**
     * Get reviews by status.
     *
     * @param  string  $status
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStatus($status, $extensionId = null)
    {
        return $this->scopeQuery(function ($query) use ($status, $extensionId) {
            $query = $query->where('status', $status)
                ->with(['user', 'extension']);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->orderBy('created_at', 'desc');
        })->all();
    }

    /**
     * Get flagged reviews for moderation.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFlagged()
    {
        return $this->getByStatus('flagged');
    }

    /**
     * Get rejected reviews.
     *
     * @param  int|null  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRejected($extensionId = null)
    {
        return $this->getByStatus('rejected', $extensionId);
    }

    /**
     * Get reviews by multiple IDs.
     *
     * @param  array  $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByIds(array $ids)
    {
        return $this->scopeQuery(function ($query) use ($ids) {
            return $query->with(['user', 'extension'])
                ->whereIn('id', $ids);
        })->all();
    }

    /**
     * Count reviews for an extension.
     *
     * @param  int  $extensionId
     * @param  bool  $approvedOnly
     * @return int
     */
    public function countByExtension($extensionId, $approvedOnly = true)
    {
        $conditions = ['extension_id' => $extensionId];

        if ($approvedOnly) {
            $conditions['status'] = 'approved';
        }

        return $this->count($conditions);
    }

    /**
     * Get reviews with high ratings (4 or 5 stars).
     *
     * @param  int|null  $extensionId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHighRated($extensionId = null, $limit = 10)
    {
        return $this->scopeQuery(function ($query) use ($extensionId, $limit) {
            $query = $query->approved()
                ->with(['user', 'extension'])
                ->whereIn('rating', [4, 5]);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->orderBy('rating', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Get reviews with low ratings (1 or 2 stars).
     *
     * @param  int|null  $extensionId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowRated($extensionId = null, $limit = 10)
    {
        return $this->scopeQuery(function ($query) use ($extensionId, $limit) {
            $query = $query->approved()
                ->with(['user', 'extension'])
                ->whereIn('rating', [1, 2]);

            if ($extensionId) {
                $query->where('extension_id', $extensionId);
            }

            return $query->orderBy('rating', 'asc')
                ->orderBy('created_at', 'desc')
                ->limit($limit);
        })->all();
    }

    /**
     * Bulk approve reviews.
     *
     * @param  array  $reviewIds
     * @return int
     */
    public function bulkApprove(array $reviewIds)
    {
        $reviews = $this->findWhereIn('id', $reviewIds);

        foreach ($reviews as $review) {
            $review->approve();
        }

        return $reviews->count();
    }

    /**
     * Bulk reject reviews.
     *
     * @param  array  $reviewIds
     * @return int
     */
    public function bulkReject(array $reviewIds)
    {
        $reviews = $this->findWhereIn('id', $reviewIds);

        foreach ($reviews as $review) {
            $review->reject();
        }

        return $reviews->count();
    }

    /**
     * Delete reviews for an extension.
     *
     * @param  int  $extensionId
     * @return int
     */
    public function deleteByExtension($extensionId)
    {
        return $this->deleteWhere(['extension_id' => $extensionId]);
    }

    /**
     * Delete reviews by a user.
     *
     * @param  int  $userId
     * @return int
     */
    public function deleteByUser($userId)
    {
        return $this->deleteWhere(['user_id' => $userId]);
    }
}
