<?php

namespace Webkul\Support\Repositories;

use Webkul\Core\Eloquent\Repository;
use App\Models\KbCategory;

class KbCategoryRepository extends Repository
{
    /**
     * Specify model class name
     */
    public function model()
    {
        return KbCategory::class;
    }

    /**
     * Get model instance
     */
    public function getModel($data = [])
    {
        return $this->model;
    }

    /**
     * Get active categories
     */
    public function getActiveCategories()
    {
        return $this->model->active()->orderBy('sort_order')->get();
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
    public function getCategoryTree($visibility = null)
    {
        $query = $this->model->rootCategories()->active()->with('children');

        if ($visibility) {
            $query->where('visibility', $visibility);
        }

        return $query->get();
    }

    /**
     * Get public categories
     */
    public function getPublicCategories()
    {
        return $this->model->public()->active()->orderBy('sort_order')->get();
    }

    /**
     * Get categories for customer portal
     */
    public function getCustomerPortalCategories()
    {
        return $this->model->customerPortal()->active()->orderBy('sort_order')->get();
    }

    /**
     * Reorder categories
     */
    public function reorder(array $order)
    {
        foreach ($order as $index => $categoryId) {
            $this->update(['sort_order' => $index], $categoryId);
        }
    }
}
