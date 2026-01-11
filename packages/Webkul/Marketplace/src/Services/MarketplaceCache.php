<?php

namespace Webkul\Marketplace\Services;

use Illuminate\Support\Facades\Cache;

class MarketplaceCache
{
    /**
     * Cache prefix for marketplace.
     */
    protected const CACHE_PREFIX = 'marketplace:';

    /**
     * Default cache TTL in seconds (1 hour).
     */
    protected const DEFAULT_TTL = 3600;

    /**
     * Cache featured extensions.
     *
     * @param  int  $limit
     * @param  callable  $callback
     * @return mixed
     */
    public function getFeatured($limit, callable $callback)
    {
        $cacheKey = $this->getCacheKey('featured', compact('limit'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache popular extensions by downloads.
     *
     * @param  int  $limit
     * @param  callable  $callback
     * @return mixed
     */
    public function getPopularByDownloads($limit, callable $callback)
    {
        $cacheKey = $this->getCacheKey('popular.downloads', compact('limit'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache popular extensions by rating.
     *
     * @param  int  $limit
     * @param  callable  $callback
     * @return mixed
     */
    public function getPopularByRating($limit, callable $callback)
    {
        $cacheKey = $this->getCacheKey('popular.rating', compact('limit'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache recently added extensions.
     *
     * @param  int  $limit
     * @param  callable  $callback
     * @return mixed
     */
    public function getRecentlyAdded($limit, callable $callback)
    {
        $cacheKey = $this->getCacheKey('recent', compact('limit'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache free extensions.
     *
     * @param  int  $limit
     * @param  callable  $callback
     * @return mixed
     */
    public function getFree($limit, callable $callback)
    {
        $cacheKey = $this->getCacheKey('free', compact('limit'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache paid extensions.
     *
     * @param  int  $limit
     * @param  callable  $callback
     * @return mixed
     */
    public function getPaid($limit, callable $callback)
    {
        $cacheKey = $this->getCacheKey('paid', compact('limit'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache trending extensions.
     *
     * @param  int  $days
     * @param  int  $limit
     * @param  callable  $callback
     * @return mixed
     */
    public function getTrending($days, $limit, callable $callback)
    {
        $cacheKey = $this->getCacheKey('trending', compact('days', 'limit'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache related extensions.
     *
     * @param  int  $extensionId
     * @param  int  $limit
     * @param  callable  $callback
     * @return mixed
     */
    public function getRelated($extensionId, $limit, callable $callback)
    {
        $cacheKey = $this->getCacheKey('related', compact('extensionId', 'limit'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache extension statistics.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function getStatistics(callable $callback)
    {
        $cacheKey = $this->getCacheKey('statistics');

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache extension categories.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function getCategories(callable $callback)
    {
        $cacheKey = $this->getCacheKey('categories');

        return Cache::remember($cacheKey, self::DEFAULT_TTL * 2, $callback);
    }

    /**
     * Cache extension by slug.
     *
     * @param  string  $slug
     * @param  callable  $callback
     * @return mixed
     */
    public function getBySlug($slug, callable $callback)
    {
        $cacheKey = $this->getCacheKey('slug', compact('slug'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache extensions by category.
     *
     * @param  int  $categoryId
     * @param  int  $page
     * @param  int  $perPage
     * @param  callable  $callback
     * @return mixed
     */
    public function getByCategory($categoryId, $page, $perPage, callable $callback)
    {
        $cacheKey = $this->getCacheKey('category', compact('categoryId', 'page', 'perPage'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Cache extensions by type.
     *
     * @param  string  $type
     * @param  int  $page
     * @param  int  $perPage
     * @param  callable  $callback
     * @return mixed
     */
    public function getByType($type, $page, $perPage, callable $callback)
    {
        $cacheKey = $this->getCacheKey('type', compact('type', 'page', 'perPage'));

        return Cache::remember($cacheKey, self::DEFAULT_TTL, $callback);
    }

    /**
     * Clear all marketplace cache.
     *
     * @return void
     */
    public function clearAll()
    {
        Cache::tags($this->getCacheTags())->flush();
    }

    /**
     * Clear cache for a specific extension.
     *
     * @param  int  $extensionId
     * @return void
     */
    public function clearExtension($extensionId)
    {
        $patterns = [
            $this->getCacheKey('slug', ['slug' => '*']),
            $this->getCacheKey('related', ['extensionId' => $extensionId, 'limit' => '*']),
            $this->getCacheKey('featured', ['limit' => '*']),
            $this->getCacheKey('popular.downloads', ['limit' => '*']),
            $this->getCacheKey('popular.rating', ['limit' => '*']),
            $this->getCacheKey('recent', ['limit' => '*']),
            $this->getCacheKey('free', ['limit' => '*']),
            $this->getCacheKey('paid', ['limit' => '*']),
            $this->getCacheKey('trending', ['days' => '*', 'limit' => '*']),
            $this->getCacheKey('statistics'),
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        // Also clear tag-based cache
        Cache::tags(['marketplace_extension_' . $extensionId])->flush();
    }

    /**
     * Clear cache for a category.
     *
     * @param  int  $categoryId
     * @return void
     */
    public function clearCategory($categoryId)
    {
        Cache::tags(['marketplace_category_' . $categoryId])->flush();
        Cache::forget($this->getCacheKey('categories'));
    }

    /**
     * Clear listings cache (featured, popular, recent, etc.).
     *
     * @return void
     */
    public function clearListings()
    {
        $patterns = [
            'featured',
            'popular.downloads',
            'popular.rating',
            'recent',
            'free',
            'paid',
            'trending',
            'statistics',
        ];

        foreach ($patterns as $pattern) {
            // Clear all variations by using tags
            Cache::tags(['marketplace_listings'])->flush();
        }
    }

    /**
     * Generate cache key.
     *
     * @param  string  $type
     * @param  array  $params
     * @return string
     */
    protected function getCacheKey($type, array $params = [])
    {
        $key = self::CACHE_PREFIX . $type;

        if (! empty($params)) {
            ksort($params);
            $key .= ':' . md5(json_encode($params));
        }

        return $key;
    }

    /**
     * Get cache tags for marketplace.
     *
     * @return array
     */
    protected function getCacheTags()
    {
        return ['marketplace', 'marketplace_listings'];
    }

    /**
     * Warm up the cache with common queries.
     *
     * @param  \Webkul\Marketplace\Repositories\ExtensionRepository  $repository
     * @return void
     */
    public function warmUp($repository)
    {
        // Cache featured extensions
        $this->getFeatured(10, function () use ($repository) {
            return $repository->getFeatured(10);
        });

        // Cache popular by downloads
        $this->getPopularByDownloads(10, function () use ($repository) {
            return $repository->getPopularByDownloads(10);
        });

        // Cache popular by rating
        $this->getPopularByRating(10, function () use ($repository) {
            return $repository->getPopularByRating(10);
        });

        // Cache recently added
        $this->getRecentlyAdded(10, function () use ($repository) {
            return $repository->getRecentlyAdded(10);
        });

        // Cache statistics
        $this->getStatistics(function () use ($repository) {
            return $repository->getStatistics();
        });
    }
}
