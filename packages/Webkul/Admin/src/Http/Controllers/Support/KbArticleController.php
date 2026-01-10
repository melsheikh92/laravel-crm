<?php

namespace Webkul\Admin\Http\Controllers\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\DataGrids\Support\KbArticleDataGrid;
use Webkul\Support\Repositories\KbArticleRepository;
use Webkul\Support\Repositories\KbCategoryRepository;
use Webkul\Support\Services\KnowledgeBaseService;
use Webkul\Tag\Repositories\TagRepository;

class KbArticleController extends Controller
{
    public function __construct(
        protected KbArticleRepository $articleRepository,
        protected KbCategoryRepository $categoryRepository,
        protected KnowledgeBaseService $kbService,
        protected TagRepository $tagRepository
    ) {
    }

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(KbArticleDataGrid::class)->process();
        }

        $statistics = $this->kbService->getStatistics();

        return view('admin::support.kb.articles.index', compact('statistics'));
    }

    public function create(): View
    {
        $categories = $this->categoryRepository->getActiveCategories();
        $tags = $this->tagRepository->all();

        return view('admin::support.kb.articles.create', compact('categories', 'tags'));
    }

    public function store(): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:kb_categories,id',
            'status' => 'required|in:draft,published,archived',
            'visibility' => 'required|in:public,internal,customer_portal',
        ]);

        $article = $this->kbService->createArticle(request()->all());

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.kb.create-success'),
                'data' => $article,
            ]);
        }

        session()->flash('success', trans('admin::app.support.kb.create-success'));

        return redirect()->route('admin.support.kb.articles.index');
    }

    public function edit(int $id): View
    {
        $article = $this->articleRepository->with(['category', 'tags', 'versions'])->findOrFail($id);
        $categories = $this->categoryRepository->getActiveCategories();
        $tags = $this->tagRepository->all();

        return view('admin::support.kb.articles.edit', compact('article', 'categories', 'tags'));
    }

    public function update(int $id): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:kb_categories,id',
        ]);

        $article = $this->kbService->updateArticle($id, request()->all());

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.kb.update-success'),
                'data' => $article,
            ]);
        }

        session()->flash('success', trans('admin::app.support.kb.update-success'));

        return redirect()->route('admin.support.kb.articles.index');
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->articleRepository->delete($id);

            return response()->json([
                'message' => trans('admin::app.support.kb.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('admin::app.support.kb.delete-failed'),
            ], 400);
        }
    }

    public function publish(int $id): JsonResponse
    {
        $article = $this->kbService->publishArticle($id);

        return response()->json([
            'message' => trans('admin::app.support.kb.published-success'),
            'data' => $article,
        ]);
    }

    public function unpublish(int $id): JsonResponse
    {
        $article = $this->kbService->unpublishArticle($id);

        return response()->json([
            'message' => trans('admin::app.support.kb.unpublished-success'),
            'data' => $article,
        ]);
    }

    public function search(): JsonResponse
    {
        $query = request('q');
        $visibility = request('visibility');

        $articles = $this->kbService->searchArticles($query, $visibility);

        return response()->json($articles);
    }
}
