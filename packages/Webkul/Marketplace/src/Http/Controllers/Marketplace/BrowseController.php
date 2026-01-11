<?php

namespace Webkul\Marketplace\Http\Controllers\Marketplace;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionCategoryRepository;

class BrowseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionCategoryRepository $categoryRepository
    ) {}

    /**
     * Display the marketplace browse page with filters.
     */
    public function index(): View|JsonResponse
    {
        try {
            // Get filter parameters from request
            $filters = $this->getFiltersFromRequest();

            // Get paginated extensions based on filters
            $extensions = $this->extensionRepository->filter(
                $filters,
                request()->get('per_page', 15)
            );

            // Get categories for filters
            $categories = $this->categoryRepository->getOrdered();

            // Get statistics for sidebar
            $statistics = $this->extensionRepository->getStatistics();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions'  => $extensions,
                        'categories'  => $categories,
                        'statistics'  => $statistics,
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories', 'statistics'));
        } catch (\Exception $e) {
            Log::error('Failed to load marketplace browse page: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.load-failed'));
        }
    }

    /**
     * Search extensions by term.
     */
    public function search(): View|JsonResponse
    {
        try {
            $searchTerm = request()->get('q', '');
            $perPage = request()->get('per_page', 15);

            if (empty($searchTerm)) {
                if (request()->ajax()) {
                    return new JsonResponse([
                        'data' => [
                            'extensions' => [],
                            'total'      => 0,
                        ],
                    ]);
                }

                return redirect()->route('marketplace.browse.index');
            }

            $extensions = $this->extensionRepository->search($searchTerm, $perPage);
            $categories = $this->categoryRepository->getOrdered();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions'   => $extensions,
                        'search_term'  => $searchTerm,
                        'total'        => $extensions->total(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories', 'searchTerm'));
        } catch (\Exception $e) {
            Log::error('Failed to search extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.search-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.search-failed'));
        }
    }

    /**
     * Browse extensions by category.
     */
    public function byCategory(string $category_slug): View|JsonResponse
    {
        try {
            $category = $this->categoryRepository->findBySlug($category_slug);

            if (!$category) {
                if (request()->ajax()) {
                    return new JsonResponse([
                        'message' => trans('marketplace::app.marketplace.browse.category-not-found'),
                    ], 404);
                }

                return redirect()->route('marketplace.browse.index')
                    ->with('error', trans('marketplace::app.marketplace.browse.category-not-found'));
            }

            $perPage = request()->get('per_page', 15);
            $extensions = $this->extensionRepository->getByCategory($category->id, $perPage);
            $categories = $this->categoryRepository->getOrdered();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions' => $extensions,
                        'category'   => $category,
                        'total'      => $extensions->total(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories', 'category'));
        } catch (\Exception $e) {
            Log::error('Failed to load category extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.load-failed'));
        }
    }

    /**
     * Browse extensions by type.
     */
    public function byType(string $type): View|JsonResponse
    {
        try {
            // Validate type
            if (!in_array($type, ['plugin', 'theme', 'integration'])) {
                if (request()->ajax()) {
                    return new JsonResponse([
                        'message' => trans('marketplace::app.marketplace.browse.invalid-type'),
                    ], 400);
                }

                return redirect()->route('marketplace.browse.index')
                    ->with('error', trans('marketplace::app.marketplace.browse.invalid-type'));
            }

            $perPage = request()->get('per_page', 15);
            $extensions = $this->extensionRepository->getByType($type, $perPage);
            $categories = $this->categoryRepository->getOrdered();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions' => $extensions,
                        'type'       => $type,
                        'total'      => $extensions->total(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories', 'type'));
        } catch (\Exception $e) {
            Log::error('Failed to load type extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.load-failed'));
        }
    }

    /**
     * Browse featured extensions.
     */
    public function featured(): View|JsonResponse
    {
        try {
            $limit = request()->get('limit', 10);
            $extensions = $this->extensionRepository->getFeatured($limit);
            $categories = $this->categoryRepository->getOrdered();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions' => $extensions,
                        'total'      => $extensions->count(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories'));
        } catch (\Exception $e) {
            Log::error('Failed to load featured extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.load-failed'));
        }
    }

    /**
     * Browse popular extensions.
     */
    public function popular(): View|JsonResponse
    {
        try {
            $limit = request()->get('limit', 20);
            $sortBy = request()->get('sort_by', 'downloads'); // downloads or rating

            $extensions = $sortBy === 'rating'
                ? $this->extensionRepository->getPopularByRating($limit)
                : $this->extensionRepository->getPopularByDownloads($limit);

            $categories = $this->categoryRepository->getOrdered();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions' => $extensions,
                        'sort_by'    => $sortBy,
                        'total'      => $extensions->count(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories', 'sortBy'));
        } catch (\Exception $e) {
            Log::error('Failed to load popular extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.load-failed'));
        }
    }

    /**
     * Browse recently added extensions.
     */
    public function recent(): View|JsonResponse
    {
        try {
            $limit = request()->get('limit', 20);
            $extensions = $this->extensionRepository->getRecentlyAdded($limit);
            $categories = $this->categoryRepository->getOrdered();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions' => $extensions,
                        'total'      => $extensions->count(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories'));
        } catch (\Exception $e) {
            Log::error('Failed to load recent extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.load-failed'));
        }
    }

    /**
     * Browse free extensions.
     */
    public function free(): View|JsonResponse
    {
        try {
            $limit = request()->get('limit', 20);
            $extensions = $this->extensionRepository->getFree($limit);
            $categories = $this->categoryRepository->getOrdered();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions' => $extensions,
                        'total'      => $extensions->count(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories'));
        } catch (\Exception $e) {
            Log::error('Failed to load free extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.load-failed'));
        }
    }

    /**
     * Browse paid extensions.
     */
    public function paid(): View|JsonResponse
    {
        try {
            $limit = request()->get('limit', 20);
            $extensions = $this->extensionRepository->getPaid($limit);
            $categories = $this->categoryRepository->getOrdered();

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => [
                        'extensions' => $extensions,
                        'total'      => $extensions->count(),
                    ],
                ]);
            }

            return view('marketplace::marketplace.browse', compact('extensions', 'categories'));
        } catch (\Exception $e) {
            Log::error('Failed to load paid extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.browse.load-failed'),
                ], 500);
            }

            return back()->with('error', trans('marketplace::app.marketplace.browse.load-failed'));
        }
    }

    /**
     * Get filters from request.
     *
     * @return array
     */
    protected function getFiltersFromRequest(): array
    {
        return [
            'search'      => request()->get('search'),
            'type'        => request()->get('type'),
            'category_id' => request()->get('category_id'),
            'price_min'   => request()->get('price_min'),
            'price_max'   => request()->get('price_max'),
            'is_free'     => request()->get('is_free') !== null ? (bool) request()->get('is_free') : null,
            'min_rating'  => request()->get('min_rating'),
            'featured'    => request()->get('featured') !== null ? (bool) request()->get('featured') : null,
            'sort_by'     => request()->get('sort_by', 'created_at'),
            'sort_order'  => request()->get('sort_order', 'desc'),
        ];
    }
}
