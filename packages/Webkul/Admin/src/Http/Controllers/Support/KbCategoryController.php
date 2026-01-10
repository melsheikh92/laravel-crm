<?php

namespace Webkul\Admin\Http\Controllers\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\DataGrids\Support\KbCategoryDataGrid;
use Webkul\Support\Repositories\KbCategoryRepository;

class KbCategoryController extends Controller
{
    public function __construct(
        protected KbCategoryRepository $categoryRepository
    ) {
    }

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(KbCategoryDataGrid::class)->process();
        }

        return view('admin::support.kb.categories.index');
    }

    public function create(): View
    {
        // Get potential parent categories (exclude self effectively, but for create all are valid)
        $categories = $this->categoryRepository->getRootCategories();

        return view('admin::support.kb.categories.create', compact('categories'));
    }

    public function store(): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:kb_categories,id',
            'sort_order' => 'required|integer',
            'visibility' => 'required|in:public,internal,customer_portal',
            'is_active' => 'boolean',
        ]);

        $data = request()->all();
        $data['parent_id'] = $data['parent_id'] ?: null;

        $category = $this->categoryRepository->create($data);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.kb.categories.create-success'),
                'data' => $category,
            ]);
        }

        session()->flash('success', trans('admin::app.support.kb.categories.create-success'));

        return redirect()->route('admin.support.kb.categories.index');
    }

    public function edit(int $id): View
    {
        $category = $this->categoryRepository->findOrFail($id);
        $categories = $this->categoryRepository->getRootCategories();
        // Ideally exclude self and children from parent options, but simple for now.

        return view('admin::support.kb.categories.edit', compact('category', 'categories'));
    }

    public function update(int $id): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:kb_categories,id',
            'sort_order' => 'required|integer',
            'visibility' => 'required|in:public,internal,customer_portal',
            'is_active' => 'boolean',
        ]);

        $data = request()->all();
        $data['parent_id'] = $data['parent_id'] ?: null;

        $category = $this->categoryRepository->update($data, $id);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.kb.categories.update-success'),
                'data' => $category,
            ]);
        }

        session()->flash('success', trans('admin::app.support.kb.categories.update-success'));

        return redirect()->route('admin.support.kb.categories.index');
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->categoryRepository->delete($id);

            return response()->json([
                'message' => trans('admin::app.support.kb.categories.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('admin::app.support.knowledge-base.categories.delete-failed'),
            ], 400);
        }
    }
}
