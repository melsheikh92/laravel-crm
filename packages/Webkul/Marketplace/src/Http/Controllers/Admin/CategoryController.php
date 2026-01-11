<?php

namespace Webkul\Marketplace\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Marketplace\DataGrids\CategoryDataGrid;
use Webkul\Marketplace\Repositories\ExtensionCategoryRepository;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionCategoryRepository $categoryRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(CategoryDataGrid::class)->process();
        }

        return view('marketplace::admin.categories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|JsonResponse
    {
        $categories = $this->categoryRepository->getTreeData();

        return view('marketplace::admin.categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:extension_categories,slug',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:255',
            'parent_id'   => 'nullable|integer|exists:extension_categories,id',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $data = request()->all();

        // Set default sort_order if not provided
        if (! isset($data['sort_order'])) {
            $data['sort_order'] = $this->categoryRepository->getNextSortOrder($data['parent_id'] ?? null);
        }

        Event::dispatch('marketplace.category.create.before');

        $category = $this->categoryRepository->create($data);

        Event::dispatch('marketplace.category.create.after', $category);

        return new JsonResponse([
            'data'    => $category,
            'message' => trans('marketplace::app.admin.categories.create-success'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View|JsonResponse
    {
        $category = $this->categoryRepository
            ->with(['parent', 'children', 'extensions'])
            ->findOrFail($id);

        if (request()->ajax()) {
            return new JsonResponse([
                'data' => $category,
            ]);
        }

        return view('marketplace::admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|JsonResponse
    {
        $category = $this->categoryRepository
            ->with(['parent'])
            ->findOrFail($id);

        // Get categories excluding current category and its descendants to prevent circular references
        $categories = $this->categoryRepository->getCategoriesExcluding($id);

        if (request()->ajax()) {
            return new JsonResponse([
                'data'       => $category,
                'categories' => $categories,
            ]);
        }

        return view('marketplace::admin.categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:extension_categories,slug,'.$id,
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:255',
            'parent_id'   => 'nullable|integer|exists:extension_categories,id',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $data = request()->all();

        // Prevent setting self as parent
        if (isset($data['parent_id']) && $data['parent_id'] == $id) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.categories.self-parent-error'),
            ], 422);
        }

        // Prevent circular reference (setting a descendant as parent)
        if (isset($data['parent_id'])) {
            $category = $this->categoryRepository->findOrFail($id);
            $descendantIds = $category->descendants()->pluck('id')->toArray();

            if (in_array($data['parent_id'], $descendantIds)) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.admin.categories.circular-reference-error'),
                ], 422);
            }
        }

        Event::dispatch('marketplace.category.update.before', $id);

        $category = $this->categoryRepository->update($data, $id);

        Event::dispatch('marketplace.category.update.after', $category);

        return new JsonResponse([
            'data'    => $category,
            'message' => trans('marketplace::app.admin.categories.update-success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->findOrFail($id);

            // Check if category has extensions
            if ($category->extensions()->count() > 0) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.admin.categories.has-extensions-error'),
                ], 422);
            }

            // Check if category has children
            if ($category->hasChildren()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.admin.categories.has-children-error'),
                ], 422);
            }

            Event::dispatch('marketplace.category.delete.before', $id);

            $this->categoryRepository->delete($id);

            Event::dispatch('marketplace.category.delete.after', $id);

            return new JsonResponse([
                'message' => trans('marketplace::app.admin.categories.delete-success'),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.categories.delete-failed'),
            ], 500);
        }
    }

    /**
     * Reorder categories.
     */
    public function reorder(): JsonResponse
    {
        $this->validate(request(), [
            'categories' => 'required|array',
            'categories.*.id' => 'required|integer|exists:extension_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
            'categories.*.parent_id' => 'nullable|integer|exists:extension_categories,id',
        ]);

        try {
            Event::dispatch('marketplace.category.reorder.before');

            $categories = request('categories');

            foreach ($categories as $categoryData) {
                $this->categoryRepository->update([
                    'sort_order' => $categoryData['sort_order'],
                    'parent_id'  => $categoryData['parent_id'] ?? null,
                ], $categoryData['id']);
            }

            Event::dispatch('marketplace.category.reorder.after', $categories);

            return new JsonResponse([
                'message' => trans('marketplace::app.admin.categories.reorder-success'),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.categories.reorder-failed'),
            ], 500);
        }
    }

    /**
     * Get hierarchical tree data for categories.
     */
    public function getTreeData(): JsonResponse
    {
        try {
            $treeData = $this->categoryRepository->getTreeData();

            return new JsonResponse([
                'data' => $treeData,
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.categories.tree-data-failed'),
            ], 500);
        }
    }

    /**
     * Mass delete the specified resources.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $count = 0;
        $errors = [];

        $categories = $this->categoryRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($categories as $category) {
            // Check if category has extensions
            if ($category->extensions()->count() > 0) {
                $errors[] = trans('marketplace::app.admin.categories.category-has-extensions', ['name' => $category->name]);
                continue;
            }

            // Check if category has children
            if ($category->hasChildren()) {
                $errors[] = trans('marketplace::app.admin.categories.category-has-children', ['name' => $category->name]);
                continue;
            }

            Event::dispatch('marketplace.category.delete.before', $category->id);

            $this->categoryRepository->delete($category->id);

            Event::dispatch('marketplace.category.delete.after', $category->id);

            $count++;
        }

        if (! $count) {
            return response()->json([
                'message' => trans('marketplace::app.admin.categories.mass-delete-failed'),
                'errors'  => $errors,
            ], 400);
        }

        $message = trans('marketplace::app.admin.categories.mass-delete-success', ['count' => $count]);

        if (count($errors) > 0) {
            $message .= ' ' . trans('marketplace::app.admin.categories.mass-delete-partial', ['failed' => count($errors)]);
        }

        return response()->json([
            'message' => $message,
            'errors'  => $errors,
        ]);
    }
}
