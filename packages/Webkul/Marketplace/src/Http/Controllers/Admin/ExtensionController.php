<?php

namespace Webkul\Marketplace\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Marketplace\DataGrids\ExtensionDataGrid;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionCategoryRepository;

class ExtensionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionCategoryRepository $categoryRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(ExtensionDataGrid::class)->process();
        }

        $categories = $this->categoryRepository->all();

        return view('marketplace::admin.extensions.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|JsonResponse
    {
        $categories = $this->categoryRepository->all();

        return view('marketplace::admin.extensions.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:extensions,slug',
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'type' => 'required|in:plugin,theme,integration',
            'category_id' => 'nullable|integer|exists:extension_categories,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:draft,pending,approved,rejected,disabled',
            'featured' => 'boolean',
            'logo' => 'nullable|string',
            'documentation_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'repository_url' => 'nullable|url',
            'support_email' => 'nullable|email',
            'tags' => 'nullable|array',
        ]);

        $data = request()->all();

        $data['author_id'] = $data['author_id'] ?? auth()->guard('user')->id();

        Event::dispatch('marketplace.extension.create.before');

        $extension = $this->extensionRepository->create($data);

        Event::dispatch('marketplace.extension.create.after', $extension);

        return new JsonResponse([
            'data' => $extension,
            'message' => trans('marketplace::app.admin.extensions.index.create-success'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View|JsonResponse
    {
        $extension = $this->extensionRepository
            ->with(['author', 'category', 'versions', 'reviews'])
            ->findOrFail($id);

        if (request()->ajax()) {
            return new JsonResponse([
                'data' => $extension,
            ]);
        }

        return view('marketplace::admin.extensions.show', compact('extension'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|JsonResponse
    {
        $extension = $this->extensionRepository
            ->with(['author', 'category'])
            ->findOrFail($id);

        $categories = $this->categoryRepository->all();

        if (request()->ajax()) {
            return new JsonResponse([
                'data' => $extension,
                'categories' => $categories,
            ]);
        }

        return view('marketplace::admin.extensions.edit', compact('extension', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:extensions,slug,' . $id,
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'type' => 'required|in:plugin,theme,integration',
            'category_id' => 'nullable|integer|exists:extension_categories,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:draft,pending,approved,rejected,disabled',
            'featured' => 'boolean',
            'logo' => 'nullable|string',
            'documentation_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'repository_url' => 'nullable|url',
            'support_email' => 'nullable|email',
            'tags' => 'nullable|array',
        ]);

        $data = request()->all();

        Event::dispatch('marketplace.extension.update.before', $id);

        $extension = $this->extensionRepository->update($data, $id);

        Event::dispatch('marketplace.extension.update.after', $extension);

        return new JsonResponse([
            'data' => $extension,
            'message' => trans('marketplace::app.admin.extensions.index.update-success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            Event::dispatch('marketplace.extension.delete.before', $id);

            $this->extensionRepository->delete($id);

            Event::dispatch('marketplace.extension.delete.after', $id);

            return new JsonResponse([
                'message' => trans('marketplace::app.admin.extensions.index.delete-success'),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.extensions.index.delete-failed'),
            ], 500);
        }
    }

    /**
     * Enable the specified extension.
     */
    public function enable(int $id): JsonResponse
    {
        try {
            Event::dispatch('marketplace.extension.enable.before', $id);

            $extension = $this->extensionRepository->update([
                'status' => 'approved',
            ], $id);

            Event::dispatch('marketplace.extension.enable.after', $extension);

            return new JsonResponse([
                'data' => $extension,
                'message' => trans('marketplace::app.admin.extensions.index.enable-success'),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.extensions.index.enable-failed'),
            ], 500);
        }
    }

    /**
     * Disable the specified extension.
     */
    public function disable(int $id): JsonResponse
    {
        try {
            Event::dispatch('marketplace.extension.disable.before', $id);

            $extension = $this->extensionRepository->update([
                'status' => 'disabled',
            ], $id);

            Event::dispatch('marketplace.extension.disable.after', $extension);

            return new JsonResponse([
                'data' => $extension,
                'message' => trans('marketplace::app.admin.extensions.index.disable-success'),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.extensions.index.disable-failed'),
            ], 500);
        }
    }

    /**
     * Feature the specified extension.
     */
    public function feature(int $id): JsonResponse
    {
        try {
            Event::dispatch('marketplace.extension.feature.before', $id);

            $extension = $this->extensionRepository->update([
                'featured' => true,
            ], $id);

            Event::dispatch('marketplace.extension.feature.after', $extension);

            return new JsonResponse([
                'data' => $extension,
                'message' => trans('marketplace::app.admin.extensions.index.feature-success'),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.extensions.index.feature-failed'),
            ], 500);
        }
    }

    /**
     * Unfeature the specified extension.
     */
    public function unfeature(int $id): JsonResponse
    {
        try {
            Event::dispatch('marketplace.extension.unfeature.before', $id);

            $extension = $this->extensionRepository->update([
                'featured' => false,
            ], $id);

            Event::dispatch('marketplace.extension.unfeature.after', $extension);

            return new JsonResponse([
                'data' => $extension,
                'message' => trans('marketplace::app.admin.extensions.index.unfeature-success'),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('marketplace::app.admin.extensions.index.unfeature-failed'),
            ], 500);
        }
    }

    /**
     * Mass delete the specified resources.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $count = 0;

        $extensions = $this->extensionRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($extensions as $extension) {
            Event::dispatch('marketplace.extension.delete.before', $extension->id);

            $this->extensionRepository->delete($extension->id);

            Event::dispatch('marketplace.extension.delete.after', $extension->id);

            $count++;
        }

        if (!$count) {
            return response()->json([
                'message' => trans('marketplace::app.admin.extensions.index.mass-delete-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('marketplace::app.admin.extensions.index.mass-delete-success'),
        ]);
    }

    /**
     * Mass enable the specified resources.
     */
    public function massEnable(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $count = 0;
        $value = $massDestroyRequest->input('value');

        $extensions = $this->extensionRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($extensions as $extension) {
            $status = $value === 'enable' ? 'approved' : 'disabled';

            Event::dispatch('marketplace.extension.update.before', $extension->id);

            $this->extensionRepository->update([
                'status' => $status,
            ], $extension->id);

            Event::dispatch('marketplace.extension.update.after', $extension->id);

            $count++;
        }

        if (!$count) {
            return response()->json([
                'message' => trans('marketplace::app.admin.extensions.index.mass-update-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('marketplace::app.admin.extensions.index.mass-update-success'),
        ]);
    }

    /**
     * Mass disable the specified resources.
     */
    public function massDisable(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $count = 0;

        $extensions = $this->extensionRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($extensions as $extension) {
            Event::dispatch('marketplace.extension.disable.before', $extension->id);

            $this->extensionRepository->update([
                'status' => 'disabled',
            ], $extension->id);

            Event::dispatch('marketplace.extension.disable.after', $extension->id);

            $count++;
        }

        if (!$count) {
            return response()->json([
                'message' => trans('marketplace::app.admin.extensions.index.mass-disable-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('marketplace::app.admin.extensions.index.mass-disable-success'),
        ]);
    }
}
