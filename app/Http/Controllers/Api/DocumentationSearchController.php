<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\DocArticleRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DocumentationSearchController extends Controller
{
    /**
     * @var DocArticleRepository
     */
    protected $docArticleRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(DocArticleRepository $docArticleRepository)
    {
        $this->docArticleRepository = $docArticleRepository;
    }

    /**
     * Search documentation articles.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:2|max:255',
                'category_id' => 'nullable|integer|exists:doc_categories,id',
                'type' => 'nullable|string|in:getting-started,api-doc,feature-guide,troubleshooting',
                'difficulty_level' => 'nullable|string|in:beginner,intermediate,advanced',
                'has_video' => 'nullable|boolean',
                'visibility' => 'nullable|string|in:public,internal,customer_portal',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $query = $validated['query'];
            $limit = $validated['limit'] ?? 20;

            // Build filters array
            $filters = [
                'search' => $query,
                'status' => 'published',
            ];

            // Add optional filters
            if (isset($validated['category_id'])) {
                $filters['category_id'] = $validated['category_id'];
            }

            if (isset($validated['type'])) {
                $filters['type'] = $validated['type'];
            }

            if (isset($validated['difficulty_level'])) {
                $filters['difficulty_level'] = $validated['difficulty_level'];
            }

            if (isset($validated['has_video']) && filter_var($validated['has_video'], FILTER_VALIDATE_BOOLEAN)) {
                $filters['has_video'] = true;
            }

            if (isset($validated['visibility'])) {
                $filters['visibility'] = $validated['visibility'];
            }

            // Perform search
            $articlesQuery = $this->docArticleRepository->filter($filters);

            // Load relationships
            $articlesQuery->with(['category', 'author', 'tags']);

            // Limit results
            $articles = $articlesQuery->limit($limit)->get();

            // Format results for instant search
            $formattedResults = $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'type' => $article->type,
                    'category' => $article->category ? [
                        'id' => $article->category->id,
                        'name' => $article->category->name,
                        'slug' => $article->category->slug,
                    ] : null,
                    'difficulty_level' => $article->difficulty_level,
                    'reading_time_minutes' => $article->reading_time_minutes,
                    'has_video' => $article->hasVideo(),
                    'view_count' => $article->view_count,
                    'helpful_count' => $article->helpful_count,
                    'url' => route('docs.show', $article->id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'total' => $formattedResults->count(),
                    'results' => $formattedResults,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get popular articles for instant search suggestions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:50',
                'type' => 'nullable|string|in:getting-started,api-doc,feature-guide,troubleshooting',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $limit = $validated['limit'] ?? 10;

            // Build query
            $query = $this->docArticleRepository->getModel()
                ->published()
                ->public()
                ->with(['category', 'author']);

            // Filter by type if specified
            if (isset($validated['type'])) {
                $query->byType($validated['type']);
            }

            $articles = $query->orderBy('view_count', 'desc')
                ->limit($limit)
                ->get();

            $formattedResults = $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'type' => $article->type,
                    'category' => $article->category ? [
                        'id' => $article->category->id,
                        'name' => $article->category->name,
                    ] : null,
                    'view_count' => $article->view_count,
                    'url' => route('docs.show', $article->id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $formattedResults->count(),
                    'results' => $formattedResults,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get popular articles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get helpful articles for instant search suggestions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function helpful(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:50',
                'type' => 'nullable|string|in:getting-started,api-doc,feature-guide,troubleshooting',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $limit = $validated['limit'] ?? 10;

            // Build query
            $query = $this->docArticleRepository->getModel()
                ->published()
                ->public()
                ->with(['category', 'author']);

            // Filter by type if specified
            if (isset($validated['type'])) {
                $query->byType($validated['type']);
            }

            $articles = $query->orderBy('helpful_count', 'desc')
                ->limit($limit)
                ->get();

            $formattedResults = $articles->map(function ($article) {
                $totalVotes = $article->helpful_count + $article->not_helpful_count;
                $helpfulnessRatio = $totalVotes > 0
                    ? round(($article->helpful_count / $totalVotes) * 100, 2)
                    : 0;

                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'type' => $article->type,
                    'category' => $article->category ? [
                        'id' => $article->category->id,
                        'name' => $article->category->name,
                    ] : null,
                    'helpful_count' => $article->helpful_count,
                    'helpfulness_ratio' => $helpfulnessRatio,
                    'url' => route('docs.show', $article->id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $formattedResults->count(),
                    'results' => $formattedResults,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get helpful articles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get autocomplete suggestions for search input.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function autocomplete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:2|max:255',
                'limit' => 'nullable|integer|min:1|max:20',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $query = $validated['query'];
            $limit = $validated['limit'] ?? 10;

            // Search for matching titles
            $articles = $this->docArticleRepository->getModel()
                ->published()
                ->public()
                ->where('title', 'like', "%{$query}%")
                ->with(['category'])
                ->orderBy('view_count', 'desc')
                ->limit($limit)
                ->get(['id', 'title', 'slug', 'category_id']);

            $suggestions = $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'category' => $article->category ? $article->category->name : null,
                    'url' => route('docs.show', $article->id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'total' => $suggestions->count(),
                    'suggestions' => $suggestions,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Autocomplete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get articles by category for search navigation.
     *
     * @param Request $request
     * @param int $categoryId
     * @return JsonResponse
     */
    public function byCategory(Request $request, int $categoryId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:100',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $limit = $validated['limit'] ?? 50;

            $articles = $this->docArticleRepository->getModel()
                ->published()
                ->public()
                ->where('category_id', $categoryId)
                ->with(['category', 'author'])
                ->orderBy('title', 'asc')
                ->limit($limit)
                ->get();

            $formattedResults = $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'url' => route('docs.show', $article->id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'category_id' => $categoryId,
                    'total' => $formattedResults->count(),
                    'results' => $formattedResults,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get articles by category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get articles by type for search navigation.
     *
     * @param Request $request
     * @param string $type
     * @return JsonResponse
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:100',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Validate type
            $validTypes = ['getting-started', 'api-doc', 'feature-guide', 'troubleshooting'];
            if (!in_array($type, $validTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid article type',
                ], 422);
            }

            $limit = $validated['limit'] ?? 50;

            $articles = $this->docArticleRepository->getModel()
                ->published()
                ->public()
                ->byType($type)
                ->with(['category', 'author'])
                ->orderBy('title', 'asc')
                ->limit($limit)
                ->get();

            $formattedResults = $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'url' => route('docs.show', $article->id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'type' => $type,
                    'total' => $formattedResults->count(),
                    'results' => $formattedResults,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get articles by type: ' . $e->getMessage(),
            ], 500);
        }
    }
}
