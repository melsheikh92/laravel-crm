<?php

namespace Webkul\Admin\Http\Controllers\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\DataGrids\Support\TicketCategoryDataGrid;
use Webkul\Support\Repositories\TicketCategoryRepository;

class TicketCategoryController extends Controller
{
    public function __construct(
        protected TicketCategoryRepository $categoryRepository
    ) {
    }

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(TicketCategoryDataGrid::class)->process();
        }

        return view('admin::support.categories.index');
    }

    public function create(): View
    {
        $categories = $this->categoryRepository->getRootCategories();

        return view('admin::support.categories.create', compact('categories'));
    }

    public function store(): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
        ]);

        $data = request()->all();

        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        $category = $this->categoryRepository->create($data);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.categories.create-success'),
                'data' => $category,
            ]);
        }

        session()->flash('success', trans('admin::app.support.categories.create-success'));

        return redirect()->route('admin.support.categories.index');
    }

    public function edit(int $id): View
    {
        $category = $this->categoryRepository->findOrFail($id);
        $categories = $this->categoryRepository->getRootCategories();

        return view('admin::support.categories.edit', compact('category', 'categories'));
    }

    public function update(int $id): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
        ]);

        $data = request()->all();

        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        $category = $this->categoryRepository->update($data, $id);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.categories.update-success'),
                'data' => $category,
            ]);
        }

        session()->flash('success', trans('admin::app.support.categories.update-success'));

        return redirect()->route('admin.support.categories.index');
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->categoryRepository->delete($id);

            return response()->json([
                'message' => trans('admin::app.support.categories.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('admin::app.support.categories.delete-failed'),
            ], 400);
        }
    }
}
