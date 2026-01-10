<?php

namespace Webkul\Support\Repositories;

use Webkul\Core\Eloquent\Repository;
use App\Models\TicketCategory;

class TicketCategoryRepository extends Repository
{
    /**
     * Specify model class name
     */
    public function model()
    {
        return TicketCategory::class;
    }

    /**
     * Get active categories
     */
    public function getActiveCategories()
    {
        return $this->model->active()->get();
    }

    /**
     * Get root categories
     */
    public function getRootCategories()
    {
        return $this->model->rootCategories()->active()->get();
    }

    /**
     * Get category tree
     */
    public function getCategoryTree()
    {
        return $this->model->rootCategories()
            ->active()
            ->with('children')
            ->get();
    }

    /**
     * Get categories for dropdown
     */
    public function getCategoriesForDropdown()
    {
        $categories = $this->model->active()->get();

        return $categories->mapWithKeys(function ($category) {
            $prefix = $category->parent_id ? '-- ' : '';
            return [$category->id => $prefix . $category->name];
        });
    }
}
