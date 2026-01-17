<?php

namespace App\Repositories;

use Webkul\Core\Eloquent\Repository;
use App\Models\DocCategory;

class DocCategoryRepository extends Repository
{
    /**
     * Searchable fields
     */
    protected $fieldSearchable = [
        'name',
        'description',
    ];

    /**
     * Specify model class name
     */
    public function model()
    {
        return DocCategory::class;
    }

    /**
     * Get model instance
     */
    public function getModel($data = [])
    {
        return $this->model;
    }

    /**
     * Create category
     */
    public function create(array $data)
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        return parent::create($data);
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
        return $this->model->rootCategories()->with(['children'])->get();
    }

    /**
     * Get active root categories
     */
    public function getActiveRootCategories()
    {
        return $this->model->rootCategories()->active()->with(['children' => function ($query) {
            $query->active();
        }])->get();
    }

    /**
     * Get public categories
     */
    public function getPublicCategories()
    {
        return $this->model->active()->public()->orderBy('sort_order')->get();
    }

    /**
     * Get category tree
     */
    public function getCategoryTree()
    {
        return $this->model->rootCategories()
            ->active()
            ->with(['children' => function ($query) {
                $query->active()->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Search categories
     */
    public function search($query)
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
        })->get();
    }
}
