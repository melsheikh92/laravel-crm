<?php

namespace Webkul\Marketplace\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;
use Webkul\Marketplace\Repositories\ExtensionReviewRepository;

class ExtensionApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionVersionRepository $versionRepository,
        protected ExtensionReviewRepository $reviewRepository
    ) {}

    /**
     * Display a listing of the extensions.
     */
    public function index(): JsonResponse
    {
        try {
            $perPage = request()->input('per_page', 15);
            $filters = request()->only([
                'type',
                'category_id',
                'price_min',
                'price_max',
                'is_free',
                'min_rating',
                'featured',
                'search',
                'sort_by',
                'sort_order',
            ]);

            $extensions = $this->extensionRepository->filter($filters, $perPage);

            return new JsonResponse([
                'data' => $extensions->items(),
                'meta' => [
                    'current_page' => $extensions->currentPage(),
                    'from'         => $extensions->firstItem(),
                    'to'           => $extensions->lastItem(),
                    'per_page'     => $extensions->perPage(),
                    'total'        => $extensions->total(),
                    'last_page'    => $extensions->lastPage(),
                ],
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve extensions');
        }
    }

    /**
     * Display the specified extension.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository
                ->with(['author', 'category', 'versions', 'reviews'])
                ->findOrFail($id);

            $stats = $this->extensionRepository
                ->scopeQuery(fn($query) => $query->where('id', $id))
                ->first();

            return new JsonResponse([
                'data' => $extension,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Extension not found', 404);
        }
    }

    /**
     * Display extension by slug.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findBySlug($slug);

            if (! $extension) {
                return new JsonResponse([
                    'message' => 'Extension not found',
                ], 404);
            }

            return new JsonResponse([
                'data' => $extension,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve extension');
        }
    }

    /**
     * Get versions for the specified extension.
     */
    public function versions(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);
            $versions = $this->versionRepository->getByExtension($id, true);

            return new JsonResponse([
                'data' => $versions,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve extension versions', 404);
        }
    }

    /**
     * Get reviews for the specified extension.
     */
    public function reviews(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);
            $perPage = request()->input('per_page', 15);
            $reviews = $this->reviewRepository->getByExtension($id, true, $perPage);

            return new JsonResponse([
                'data' => $reviews->items(),
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'from'         => $reviews->firstItem(),
                    'to'           => $reviews->lastItem(),
                    'per_page'     => $reviews->perPage(),
                    'total'        => $reviews->total(),
                    'last_page'    => $reviews->lastPage(),
                ],
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve extension reviews', 404);
        }
    }

    /**
     * Get statistics for the specified extension.
     */
    public function stats(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);
            $versionStats = $this->versionRepository->getStatistics($id);
            $reviewStats = $this->reviewRepository->getStatistics($id);

            return new JsonResponse([
                'data' => [
                    'extension' => [
                        'id'              => $extension->id,
                        'name'            => $extension->name,
                        'downloads_count' => $extension->downloads_count,
                        'average_rating'  => $extension->average_rating,
                        'reviews_count'   => $extension->reviews_count,
                    ],
                    'versions' => $versionStats,
                    'reviews'  => $reviewStats,
                ],
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve extension statistics', 404);
        }
    }

    /**
     * Search extensions.
     */
    public function search(): JsonResponse
    {
        try {
            $term = request()->input('q', '');
            $perPage = request()->input('per_page', 15);

            if (empty($term)) {
                return new JsonResponse([
                    'message' => 'Search term is required',
                ], 400);
            }

            $extensions = $this->extensionRepository->search($term, $perPage);

            return new JsonResponse([
                'data' => $extensions->items(),
                'meta' => [
                    'current_page' => $extensions->currentPage(),
                    'from'         => $extensions->firstItem(),
                    'to'           => $extensions->lastItem(),
                    'per_page'     => $extensions->perPage(),
                    'total'        => $extensions->total(),
                    'last_page'    => $extensions->lastPage(),
                ],
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Search failed');
        }
    }

    /**
     * Get featured extensions.
     */
    public function featured(): JsonResponse
    {
        try {
            $limit = request()->input('limit', 10);
            $extensions = $this->extensionRepository->getFeatured($limit);

            return new JsonResponse([
                'data' => $extensions,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve featured extensions');
        }
    }

    /**
     * Get popular extensions.
     */
    public function popular(): JsonResponse
    {
        try {
            $limit = request()->input('limit', 10);
            $sortBy = request()->input('sort_by', 'downloads'); // downloads or rating

            if ($sortBy === 'rating') {
                $extensions = $this->extensionRepository->getPopularByRating($limit);
            } else {
                $extensions = $this->extensionRepository->getPopularByDownloads($limit);
            }

            return new JsonResponse([
                'data' => $extensions,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve popular extensions');
        }
    }

    /**
     * Store a newly created extension.
     */
    public function store(): JsonResponse
    {
        try {
            $this->validate(request(), [
                'name'              => 'required|string|max:255',
                'slug'              => 'required|string|max:255|unique:extensions,slug',
                'description'       => 'nullable|string',
                'long_description'  => 'nullable|string',
                'type'              => 'required|in:plugin,theme,integration',
                'category_id'       => 'nullable|integer|exists:extension_categories,id',
                'price'             => 'required|numeric|min:0',
                'logo'              => 'nullable|string',
                'documentation_url' => 'nullable|url',
                'demo_url'          => 'nullable|url',
                'repository_url'    => 'nullable|url',
                'support_email'     => 'nullable|email',
                'tags'              => 'nullable|array',
            ]);

            $data = request()->all();
            $data['author_id'] = auth()->guard('user')->id();
            $data['status'] = 'draft';

            Event::dispatch('marketplace.extension.create.before');

            $extension = $this->extensionRepository->create($data);

            Event::dispatch('marketplace.extension.create.after', $extension);

            return new JsonResponse([
                'data'    => $extension,
                'message' => 'Extension created successfully',
            ], 201);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to create extension');
        }
    }

    /**
     * Update the specified extension.
     */
    public function update(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Check if user owns the extension
            if ($extension->author_id !== auth()->guard('user')->id()) {
                return new JsonResponse([
                    'message' => 'Unauthorized to update this extension',
                ], 403);
            }

            $this->validate(request(), [
                'name'              => 'required|string|max:255',
                'slug'              => 'required|string|max:255|unique:extensions,slug,'.$id,
                'description'       => 'nullable|string',
                'long_description'  => 'nullable|string',
                'type'              => 'required|in:plugin,theme,integration',
                'category_id'       => 'nullable|integer|exists:extension_categories,id',
                'price'             => 'required|numeric|min:0',
                'logo'              => 'nullable|string',
                'documentation_url' => 'nullable|url',
                'demo_url'          => 'nullable|url',
                'repository_url'    => 'nullable|url',
                'support_email'     => 'nullable|email',
                'tags'              => 'nullable|array',
            ]);

            Event::dispatch('marketplace.extension.update.before', $id);

            $extension = $this->extensionRepository->update(request()->all(), $id);

            Event::dispatch('marketplace.extension.update.after', $extension);

            return new JsonResponse([
                'data'    => $extension,
                'message' => 'Extension updated successfully',
            ], 200);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to update extension', 404);
        }
    }

    /**
     * Remove the specified extension.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Check if user owns the extension
            if ($extension->author_id !== auth()->guard('user')->id()) {
                return new JsonResponse([
                    'message' => 'Unauthorized to delete this extension',
                ], 403);
            }

            Event::dispatch('marketplace.extension.delete.before', $id);

            $this->extensionRepository->delete($id);

            Event::dispatch('marketplace.extension.delete.after', $id);

            return new JsonResponse([
                'message' => 'Extension deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to delete extension', 404);
        }
    }

    /**
     * Handle exceptions and return formatted JSON response.
     */
    protected function handleException(Exception $exception, string $defaultMessage = 'An error occurred', int $defaultCode = 500): JsonResponse
    {
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return new JsonResponse([
                'message' => $defaultMessage,
            ], 404);
        }

        return new JsonResponse([
            'message' => $defaultMessage,
            'error'   => config('app.debug') ? $exception->getMessage() : null,
        ], $defaultCode);
    }
}
