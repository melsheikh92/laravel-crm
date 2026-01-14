<?php

namespace App\Http\Controllers;

use App\Models\DocArticle;
use App\Repositories\DocArticleRepository;
use App\Repositories\DocCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Documentation\DocArticleDataGrid;

class AdminDocumentationController extends Controller
{
    /**
     * @var DocArticleRepository
     */
    protected $docArticleRepository;

    /**
     * @var DocCategoryRepository
     */
    protected $docCategoryRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        DocArticleRepository $docArticleRepository,
        DocCategoryRepository $docCategoryRepository
    ) {
        $this->docArticleRepository = $docArticleRepository;
        $this->docCategoryRepository = $docCategoryRepository;

        // Add auth middleware to protect admin endpoints
        $this->middleware('auth');
    }

    /**
     * Display a listing of the documentation articles.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datagrid(DocArticleDataGrid::class)->process();
        }

        // Get statistics
        $stats = [
            'total' => $this->docArticleRepository->getModel()->count(),
            'published' => $this->docArticleRepository->getModel()->where('status', 'published')->count(),
            'draft' => $this->docArticleRepository->getModel()->where('status', 'draft')->count(),
            'public' => $this->docArticleRepository->getModel()->where('visibility', 'public')->count(),
            'internal' => $this->docArticleRepository->getModel()->where('visibility', 'internal')->count(),
        ];

        return view('admin.docs.index', compact('stats'));
    }

    /**
     * Show the form for creating a new documentation article.
     *
     * @return View
     */
    public function create(): View
    {
        $categories = $this->docCategoryRepository->all();

        return view('admin.docs.create', compact('categories'));
    }

    /**
     * Store a newly created documentation article in storage.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:doc_articles,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:doc_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'type' => 'nullable|in:getting-started,api-doc,feature-guide,troubleshooting,tutorial',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'video_url' => 'nullable|url|max:500',
            'video_type' => 'nullable|in:youtube,vimeo',
            'visibility' => 'nullable|in:public,internal,private',
            'status' => 'nullable|in:draft,published,archived',
            'featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        try {
            Event::dispatch('docs.article.create.before');

            $article = $this->docArticleRepository->create($validated);

            Event::dispatch('docs.article.create.after', $article);

            if ($request->ajax()) {
                return response()->json([
                    'data' => $article,
                    'message' => 'Documentation article created successfully.',
                ], 201);
            }

            return redirect()
                ->route('admin.docs.edit', $article->id)
                ->with('success', 'Documentation article created successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to create article: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create article: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified documentation article.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $article = $this->docArticleRepository->findOrFail($id);

        return view('admin.docs.show', compact('article'));
    }

    /**
     * Show the form for editing the specified documentation article.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $article = $this->docArticleRepository->findOrFail($id);
        $categories = $this->docCategoryRepository->all();

        return view('admin.docs.edit', compact('article', 'categories'));
    }

    /**
     * Update the specified documentation article in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|JsonResponse
     */
    public function update(Request $request, int $id)
    {
        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:doc_articles,slug,' . $id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:doc_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'type' => 'nullable|in:getting-started,api-doc,feature-guide,troubleshooting,tutorial',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'video_url' => 'nullable|url|max:500',
            'video_type' => 'nullable|in:youtube,vimeo',
            'visibility' => 'nullable|in:public,internal,private',
            'status' => 'nullable|in:draft,published,archived',
            'featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        try {
            Event::dispatch('docs.article.update.before', $id);

            $article = $this->docArticleRepository->update($validated, $id);

            Event::dispatch('docs.article.update.after', $article);

            if ($request->ajax()) {
                return response()->json([
                    'data' => $article,
                    'message' => 'Documentation article updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.docs.edit', $article->id)
                ->with('success', 'Documentation article updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to update article: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update article: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified documentation article from storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, int $id)
    {
        try {
            Event::dispatch('docs.article.delete.before', $id);

            $article = $this->docArticleRepository->findOrFail($id);
            $this->docArticleRepository->delete($id);

            Event::dispatch('docs.article.delete.after', $id);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Documentation article deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.docs.index')
                ->with('success', 'Documentation article deleted successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to delete article: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete article: ' . $e->getMessage());
        }
    }

    /**
     * Publish the specified documentation article.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|JsonResponse
     */
    public function publish(Request $request, int $id)
    {
        try {
            $article = $this->docArticleRepository->publish($id);

            if ($request->ajax()) {
                return response()->json([
                    'data' => $article,
                    'message' => 'Documentation article published successfully.',
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Documentation article published successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to publish article: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to publish article: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish the specified documentation article.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|JsonResponse
     */
    public function unpublish(Request $request, int $id)
    {
        try {
            $article = $this->docArticleRepository->unpublish($id);

            if ($request->ajax()) {
                return response()->json([
                    'data' => $article,
                    'message' => 'Documentation article unpublished successfully.',
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Documentation article unpublished successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to unpublish article: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to unpublish article: ' . $e->getMessage());
        }
    }

    /**
     * Mass delete documentation articles.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function massDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:doc_articles,id',
        ]);

        try {
            $count = 0;
            foreach ($validated['ids'] as $id) {
                $this->docArticleRepository->delete($id);
                $count++;
            }

            if ($request->ajax()) {
                return response()->json([
                    'message' => "Successfully deleted {$count} articles.",
                ]);
            }

            return redirect()
                ->route('admin.docs.index')
                ->with('success', "Successfully deleted {$count} articles.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to delete articles: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete articles: ' . $e->getMessage());
        }
    }

    /**
     * Mass update documentation articles.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function massUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:doc_articles,id',
            'value' => 'required|string|in:draft,published,archived',
        ]);

        try {
            $count = 0;
            foreach ($validated['ids'] as $id) {
                $this->docArticleRepository->update([
                    'status' => $validated['value'],
                ], $id);
                $count++;
            }

            if ($request->ajax()) {
                return response()->json([
                    'message' => "Successfully updated {$count} articles.",
                ]);
            }

            return redirect()
                ->route('admin.docs.index')
                ->with('success', "Successfully updated {$count} articles.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to update articles: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to update articles: ' . $e->getMessage());
        }
    }

    /**
     * Get documentation statistics (for AJAX requests).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total' => $this->docArticleRepository->getModel()->count(),
                'published' => $this->docArticleRepository->getModel()->where('status', 'published')->count(),
                'draft' => $this->docArticleRepository->getModel()->where('status', 'draft')->count(),
                'archived' => $this->docArticleRepository->getModel()->where('status', 'archived')->count(),
                'public' => $this->docArticleRepository->getModel()->where('visibility', 'public')->count(),
                'internal' => $this->docArticleRepository->getModel()->where('visibility', 'internal')->count(),
                'private' => $this->docArticleRepository->getModel()->where('visibility', 'private')->count(),
                'with_video' => $this->docArticleRepository->getModel()->whereNotNull('video_url')->count(),
                'featured' => $this->docArticleRepository->getModel()->where('featured', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}
