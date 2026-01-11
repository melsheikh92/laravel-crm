<?php

namespace Webkul\Marketplace\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Marketplace\Contracts\Extension;
use Webkul\Marketplace\Services\MarketplaceCache;

class ExtensionRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'name',
        'slug',
        'description',
        'long_description',
        'type',
        'status',
        'tags',
        'author.name',
        'category.name',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        Container $container,
        protected MarketplaceCache $cache
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
        return Extension::class;
    }

    /**
     * Get approved extensions.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApproved($columns = ['*'])
    {
        return $this->scopeQuery(function ($query) use ($columns) {
            return $query->approved()->select($columns);
        })->all();
    }

    /**
     * Get featured extensions.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeatured($limit = 10)
    {
        return $this->cache->getFeatured($limit, function () use ($limit) {
            return $this->scopeQuery(function ($query) use ($limit) {
                return $query->approved()
                    ->featured()
                    ->with(['author:id,name,email', 'category:id,name,slug'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit);
            })->all();
        });
    }

    /**
     * Get popular extensions by downloads count.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularByDownloads($limit = 10)
    {
        return $this->cache->getPopularByDownloads($limit, function () use ($limit) {
            return $this->scopeQuery(function ($query) use ($limit) {
                return $query->approved()
                    ->with(['author:id,name,email', 'category:id,name,slug'])
                    ->orderBy('downloads_count', 'desc')
                    ->limit($limit);
            })->all();
        });
    }

    /**
     * Get popular extensions by rating.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularByRating($limit = 10)
    {
        return $this->cache->getPopularByRating($limit, function () use ($limit) {
            return $this->scopeQuery(function ($query) use ($limit) {
                return $query->approved()
                    ->with(['author:id,name,email', 'category:id,name,slug'])
                    ->where('average_rating', '>', 0)
                    ->orderBy('average_rating', 'desc')
                    ->orderBy('downloads_count', 'desc')
                    ->limit($limit);
            })->all();
        });
    }

    /**
     * Get recently added extensions.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentlyAdded($limit = 10)
    {
        return $this->cache->getRecentlyAdded($limit, function () use ($limit) {
            return $this->scopeQuery(function ($query) use ($limit) {
                return $query->approved()
                    ->with(['author:id,name,email', 'category:id,name,slug'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit);
            })->all();
        });
    }

    /**
     * Get extensions by category.
     *
     * @param  int  $categoryId
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByCategory($categoryId, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($categoryId) {
            return $query->approved()
                ->with(['author:id,name,email', 'category:id,name,slug'])
                ->where('category_id', $categoryId)
                ->orderBy('created_at', 'desc');
        })->paginate($perPage);
    }

    /**
     * Get extensions by type.
     *
     * @param  string  $type
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByType($type, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($type) {
            return $query->approved()
                ->with(['author:id,name,email', 'category:id,name,slug'])
                ->ofType($type)
                ->orderBy('created_at', 'desc');
        })->paginate($perPage);
    }

    /**
     * Get extensions by author.
     *
     * @param  int  $authorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByAuthor($authorId)
    {
        return $this->scopeQuery(function ($query) use ($authorId) {
            return $query->with(['author:id,name,email', 'category:id,name,slug'])
                ->where('author_id', $authorId)
                ->orderBy('created_at', 'desc');
        })->all();
    }

    /**
     * Search extensions by term.
     *
     * @param  string  $term
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search($term, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($term) {
            return $query->approved()
                ->with(['author:id,name,email', 'category:id,name,slug'])
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('long_description', 'like', "%{$term}%")
                        ->orWhere('tags', 'like', "%{$term}%");
                })
                ->orderBy('downloads_count', 'desc');
        })->paginate($perPage);
    }

    /**
     * Filter extensions by multiple criteria.
     *
     * @param  array  $filters
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function filter($filters, $perPage = 15)
    {
        return $this->scopeQuery(function ($query) use ($filters) {
            $query = $query->approved()->with(['author:id,name,email', 'category:id,name,slug']);

            // Filter by type
            if (! empty($filters['type'])) {
                $query->ofType($filters['type']);
            }

            // Filter by category
            if (! empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            // Filter by price range
            if (isset($filters['price_min'])) {
                $query->where('price', '>=', $filters['price_min']);
            }

            if (isset($filters['price_max'])) {
                $query->where('price', '<=', $filters['price_max']);
            }

            // Filter by free/paid
            if (isset($filters['is_free'])) {
                if ($filters['is_free']) {
                    $query->where('price', 0);
                } else {
                    $query->where('price', '>', 0);
                }
            }

            // Filter by rating
            if (! empty($filters['min_rating'])) {
                $query->where('average_rating', '>=', $filters['min_rating']);
            }

            // Filter by featured
            if (! empty($filters['featured'])) {
                $query->featured();
            }

            // Search term
            if (! empty($filters['search'])) {
                $term = $filters['search'];
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('long_description', 'like', "%{$term}%")
                        ->orWhere('tags', 'like', "%{$term}%");
                });
            }

            // Sort
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';

            return $query->orderBy($sortBy, $sortOrder);
        })->paginate($perPage);
    }

    /**
     * Get free extensions.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFree($limit = 10)
    {
        return $this->cache->getFree($limit, function () use ($limit) {
            return $this->scopeQuery(function ($query) use ($limit) {
                return $query->approved()
                    ->with(['author:id,name,email', 'category:id,name,slug'])
                    ->where('price', 0)
                    ->orderBy('downloads_count', 'desc')
                    ->limit($limit);
            })->all();
        });
    }

    /**
     * Get paid extensions.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPaid($limit = 10)
    {
        return $this->cache->getPaid($limit, function () use ($limit) {
            return $this->scopeQuery(function ($query) use ($limit) {
                return $query->approved()
                    ->with(['author:id,name,email', 'category:id,name,slug'])
                    ->where('price', '>', 0)
                    ->orderBy('downloads_count', 'desc')
                    ->limit($limit);
            })->all();
        });
    }

    /**
     * Get related extensions (same category).
     *
     * @param  int  $extensionId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelated($extensionId, $limit = 5)
    {
        return $this->cache->getRelated($extensionId, $limit, function () use ($extensionId, $limit) {
            $extension = $this->find($extensionId);

            if (! $extension) {
                return collect([]);
            }

            return $this->scopeQuery(function ($query) use ($extension, $extensionId, $limit) {
                return $query->approved()
                    ->with(['author:id,name,email', 'category:id,name,slug'])
                    ->where('category_id', $extension->category_id)
                    ->where('id', '!=', $extensionId)
                    ->orderBy('average_rating', 'desc')
                    ->limit($limit);
            })->all();
        });
    }

    /**
     * Get extensions statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        return $this->cache->getStatistics(function () {
            return [
                'total_extensions'     => $this->count(['status' => 'approved']),
                'total_downloads'      => $this->sum('downloads_count'),
                'average_rating'       => round($this->avg('average_rating'), 2),
                'free_extensions'      => $this->count(['status' => 'approved', 'price' => 0]),
                'paid_extensions'      => $this->scopeQuery(function ($query) {
                    return $query->approved()->where('price', '>', 0);
                })->count(),
                'featured_extensions'  => $this->count(['status' => 'approved', 'featured' => true]),
            ];
        });
    }

    /**
     * Get extensions by multiple IDs.
     *
     * @param  array  $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByIds(array $ids)
    {
        return $this->scopeQuery(function ($query) use ($ids) {
            return $query->with(['author:id,name,email', 'category:id,name,slug'])
                ->whereIn('id', $ids);
        })->all();
    }

    /**
     * Find extension by slug.
     *
     * @param  string  $slug
     * @return \Webkul\Marketplace\Contracts\Extension|null
     */
    public function findBySlug($slug)
    {
        return $this->cache->getBySlug($slug, function () use ($slug) {
            return $this->with([
                'author:id,name,email',
                'category:id,name,slug',
                'versions' => function ($query) {
                    $query->approved()->orderBy('release_date', 'desc')->limit(5);
                },
                'reviews' => function ($query) {
                    $query->approved()->orderBy('created_at', 'desc')->limit(10);
                },
                'reviews.user:id,name',
            ])->findOneWhere(['slug' => $slug]);
        });
    }

    /**
     * Check if extension exists by slug.
     *
     * @param  string  $slug
     * @param  int|null  $excludeId
     * @return bool
     */
    public function existsBySlug($slug, $excludeId = null)
    {
        $query = $this->scopeQuery(function ($q) use ($slug, $excludeId) {
            $q = $q->where('slug', $slug);

            if ($excludeId) {
                $q->where('id', '!=', $excludeId);
            }

            return $q;
        });

        return $query->count() > 0;
    }

    /**
     * Get trending extensions (high downloads in recent period).
     *
     * @param  int  $days
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrending($days = 7, $limit = 10)
    {
        return $this->cache->getTrending($days, $limit, function () use ($days, $limit) {
            return $this->scopeQuery(function ($query) use ($days, $limit) {
                return $query->approved()
                    ->with(['author:id,name,email', 'category:id,name,slug'])
                    ->where('created_at', '>=', now()->subDays($days))
                    ->orderBy('downloads_count', 'desc')
                    ->limit($limit);
            })->all();
        });
    }
}
