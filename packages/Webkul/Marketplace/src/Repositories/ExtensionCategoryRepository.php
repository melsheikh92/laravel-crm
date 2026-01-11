<?php

namespace Webkul\Marketplace\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\Marketplace\Contracts\ExtensionCategory;
use Webkul\Marketplace\Services\MarketplaceCache;

class ExtensionCategoryRepository extends Repository
{
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
        return ExtensionCategory::class;
    }

    /**
     * Get hierarchical tree data for categories.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTreeData()
    {
        return $this->scopeQuery(function ($query) {
            return $query->root()->with('children');
        })->get();
    }

    /**
     * Get the next sort order for a given parent.
     *
     * @param  int|null  $parentId
     * @return int
     */
    public function getNextSortOrder($parentId = null): int
    {
        $query = $this->model->newQuery();

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        $maxSortOrder = $query->max('sort_order');

        return $maxSortOrder ? $maxSortOrder + 1 : 0;
    }

    /**
     * Get categories excluding a specific category and its descendants.
     *
     * @param  int  $categoryId
     * @return \Illuminate\Support\Collection
     */
    public function getCategoriesExcluding(int $categoryId)
    {
        $category = $this->with('descendants')->findOrFail($categoryId);

        // Get IDs to exclude (category itself and all descendants)
        $excludeIds = [$categoryId];
        $descendants = $category->descendants;

        if ($descendants->count() > 0) {
            $excludeIds = array_merge($excludeIds, $this->getAllDescendantIds($descendants));
        }

        return $this->scopeQuery(function ($query) use ($excludeIds) {
            return $query->whereNotIn('id', $excludeIds)->orderBy('sort_order');
        })->get();
    }

    /**
     * Get all descendant IDs recursively.
     *
     * @param  \Illuminate\Support\Collection  $descendants
     * @return array
     */
    protected function getAllDescendantIds($descendants): array
    {
        $ids = [];

        foreach ($descendants as $descendant) {
            $ids[] = $descendant->id;

            if ($descendant->children && $descendant->children->count() > 0) {
                $ids = array_merge($ids, $this->getAllDescendantIds($descendant->children));
            }
        }

        return $ids;
    }

    /**
     * Get root categories (no parent).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoot()
    {
        return $this->scopeQuery(function ($query) {
            return $query->root();
        })->get();
    }

    /**
     * Get categories ordered by sort_order.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOrdered()
    {
        return $this->cache->getCategories(function () {
            return $this->scopeQuery(function ($query) {
                return $query->ordered()->withCount('extensions');
            })->get();
        });
    }

    /**
     * Get categories with their children.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWithChildren()
    {
        return $this->scopeQuery(function ($query) {
            return $query->withChildren();
        })->get();
    }

    /**
     * Get category by slug.
     *
     * @param  string  $slug
     * @return \Webkul\Marketplace\Contracts\ExtensionCategory|null
     */
    public function findBySlug(string $slug)
    {
        return $this->findOneWhere(['slug' => $slug]);
    }

    /**
     * Check if a slug exists.
     *
     * @param  string  $slug
     * @param  int|null  $excludeId
     * @return bool
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get categories by IDs.
     *
     * @param  array  $ids
     * @return \Illuminate\Support\Collection
     */
    public function getByIds(array $ids)
    {
        return $this->findWhereIn('id', $ids);
    }
}
