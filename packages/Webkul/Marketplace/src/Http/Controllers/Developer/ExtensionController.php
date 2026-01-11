<?php

namespace Webkul\Marketplace\Http\Controllers\Developer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
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
    ) {}

    /**
     * Display a listing of the developer's extensions.
     */
    public function index(): View|JsonResponse
    {
        try {
            $userId = Auth::id();

            $extensions = $this->extensionRepository
                ->scopeQuery(function ($query) use ($userId) {
                    return $query->where('author_id', $userId)
                        ->with(['category', 'versions'])
                        ->orderBy('created_at', 'desc');
                })
                ->paginate(15);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $extensions,
                ]);
            }

            return view('marketplace::developer.extensions.index', compact('extensions'));
        } catch (\Exception $e) {
            Log::error('Failed to load developer extensions: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.load-failed'),
                ], 500);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.extensions.load-failed'));
        }
    }

    /**
     * Show the form for creating a new extension.
     */
    public function create(): View|JsonResponse
    {
        $categories = $this->categoryRepository->all();

        if (request()->ajax()) {
            return new JsonResponse([
                'success'    => true,
                'categories' => $categories,
            ]);
        }

        return view('marketplace::developer.extensions.create', compact('categories'));
    }

    /**
     * Store a newly created extension in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name'              => 'required|string|max:255',
            'slug'              => 'required|string|max:255|unique:extensions,slug',
            'description'       => 'required|string|max:500',
            'long_description'  => 'nullable|string',
            'type'              => 'required|in:plugin,theme,integration',
            'category_id'       => 'required|integer|exists:extension_categories,id',
            'price'             => 'required|numeric|min:0',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentation_url' => 'nullable|url',
            'demo_url'          => 'nullable|url',
            'repository_url'    => 'nullable|url',
            'support_email'     => 'nullable|email',
            'tags'              => 'nullable|array',
        ]);

        try {
            $data = request()->all();

            // Set author_id to current user
            $data['author_id'] = Auth::id();

            // Set status to draft by default
            $data['status'] = 'draft';

            // Handle logo upload
            if (request()->hasFile('logo')) {
                $data['logo'] = $this->uploadFile(request()->file('logo'), 'logos');
            }

            Event::dispatch('marketplace.extension.create.before');

            $extension = $this->extensionRepository->create($data);

            Event::dispatch('marketplace.extension.create.after', $extension);

            return new JsonResponse([
                'success' => true,
                'data'    => $extension,
                'message' => trans('marketplace::app.developer.extensions.create-success'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create extension: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.extensions.create-failed'),
            ], 500);
        }
    }

    /**
     * Display the specified extension.
     */
    public function show(int $id): View|JsonResponse
    {
        try {
            $extension = $this->extensionRepository
                ->with(['category', 'versions', 'reviews'])
                ->findOrFail($id);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.unauthorized'),
                ], 403);
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => $extension,
                ]);
            }

            return view('marketplace::developer.extensions.show', compact('extension'));
        } catch (\Exception $e) {
            Log::error('Failed to load extension: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.not-found'),
                ], 404);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.extensions.not-found'));
        }
    }

    /**
     * Show the form for editing the specified extension.
     */
    public function edit(int $id): View|JsonResponse
    {
        try {
            $extension = $this->extensionRepository
                ->with(['category'])
                ->findOrFail($id);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.unauthorized'),
                ], 403);
            }

            $categories = $this->categoryRepository->all();

            if (request()->ajax()) {
                return new JsonResponse([
                    'success'    => true,
                    'data'       => $extension,
                    'categories' => $categories,
                ]);
            }

            return view('marketplace::developer.extensions.edit', compact('extension', 'categories'));
        } catch (\Exception $e) {
            Log::error('Failed to load extension for editing: ' . $e->getMessage());

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.not-found'),
                ], 404);
            }

            return redirect()->back()->with('error', trans('marketplace::app.developer.extensions.not-found'));
        }
    }

    /**
     * Update the specified extension in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name'              => 'required|string|max:255',
            'slug'              => 'required|string|max:255|unique:extensions,slug,'.$id,
            'description'       => 'required|string|max:500',
            'long_description'  => 'nullable|string',
            'type'              => 'required|in:plugin,theme,integration',
            'category_id'       => 'required|integer|exists:extension_categories,id',
            'price'             => 'required|numeric|min:0',
            'documentation_url' => 'nullable|url',
            'demo_url'          => 'nullable|url',
            'repository_url'    => 'nullable|url',
            'support_email'     => 'nullable|email',
            'tags'              => 'nullable|array',
        ]);

        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.unauthorized'),
                ], 403);
            }

            $data = request()->all();

            Event::dispatch('marketplace.extension.update.before', $id);

            $extension = $this->extensionRepository->update($data, $id);

            Event::dispatch('marketplace.extension.update.after', $extension);

            return new JsonResponse([
                'success' => true,
                'data'    => $extension,
                'message' => trans('marketplace::app.developer.extensions.update-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update extension: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.extensions.update-failed'),
            ], 500);
        }
    }

    /**
     * Remove the specified extension from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.unauthorized'),
                ], 403);
            }

            // Don't allow deletion of approved extensions
            if ($extension->status === 'approved') {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.delete-approved-error'),
                ], 400);
            }

            Event::dispatch('marketplace.extension.delete.before', $id);

            // Delete associated files
            if ($extension->logo) {
                $this->deleteFile($extension->logo);
            }

            if ($extension->screenshots) {
                foreach ($extension->screenshots as $screenshot) {
                    $this->deleteFile($screenshot);
                }
            }

            $this->extensionRepository->delete($id);

            Event::dispatch('marketplace.extension.delete.after', $id);

            return new JsonResponse([
                'success' => true,
                'message' => trans('marketplace::app.developer.extensions.delete-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete extension: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.extensions.delete-failed'),
            ], 500);
        }
    }

    /**
     * Upload logo for the extension.
     */
    public function uploadLogo(int $id): JsonResponse
    {
        $this->validate(request(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.unauthorized'),
                ], 403);
            }

            // Delete old logo if exists
            if ($extension->logo) {
                $this->deleteFile($extension->logo);
            }

            // Upload new logo
            $logoPath = $this->uploadFile(request()->file('logo'), 'logos');

            $extension = $this->extensionRepository->update([
                'logo' => $logoPath,
            ], $id);

            return new JsonResponse([
                'success' => true,
                'data'    => $extension,
                'message' => trans('marketplace::app.developer.extensions.logo-upload-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload logo: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.extensions.logo-upload-failed'),
            ], 500);
        }
    }

    /**
     * Upload screenshots for the extension.
     */
    public function uploadScreenshots(int $id): JsonResponse
    {
        $this->validate(request(), [
            'screenshots'   => 'required|array|min:1|max:5',
            'screenshots.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.unauthorized'),
                ], 403);
            }

            $screenshots = $extension->screenshots ?? [];

            // Check if we exceed the maximum number of screenshots
            if (count($screenshots) + count(request()->file('screenshots')) > 5) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.max-screenshots-error'),
                ], 400);
            }

            // Upload new screenshots
            foreach (request()->file('screenshots') as $file) {
                $screenshots[] = $this->uploadFile($file, 'screenshots');
            }

            $extension = $this->extensionRepository->update([
                'screenshots' => $screenshots,
            ], $id);

            return new JsonResponse([
                'success' => true,
                'data'    => $extension,
                'message' => trans('marketplace::app.developer.extensions.screenshots-upload-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload screenshots: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.extensions.screenshots-upload-failed'),
            ], 500);
        }
    }

    /**
     * Delete a screenshot from the extension.
     */
    public function deleteScreenshot(int $id, int $screenshotId): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.unauthorized'),
                ], 403);
            }

            $screenshots = $extension->screenshots ?? [];

            // Check if screenshot exists
            if (!isset($screenshots[$screenshotId])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.screenshot-not-found'),
                ], 404);
            }

            // Delete the file
            $this->deleteFile($screenshots[$screenshotId]);

            // Remove from array
            unset($screenshots[$screenshotId]);
            $screenshots = array_values($screenshots);

            $extension = $this->extensionRepository->update([
                'screenshots' => $screenshots,
            ], $id);

            return new JsonResponse([
                'success' => true,
                'data'    => $extension,
                'message' => trans('marketplace::app.developer.extensions.screenshot-delete-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete screenshot: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.extensions.screenshot-delete-failed'),
            ], 500);
        }
    }

    /**
     * Get analytics for the extension.
     */
    public function analytics(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository
                ->with(['reviews', 'installations'])
                ->findOrFail($id);

            // Ensure the extension belongs to the current user
            if ($extension->author_id !== Auth::id()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.developer.extensions.unauthorized'),
                ], 403);
            }

            // Calculate analytics
            $analytics = [
                'total_downloads'      => $extension->downloads_count,
                'total_reviews'        => $extension->reviews->count(),
                'average_rating'       => round($extension->average_rating, 2),
                'rating_distribution'  => $this->getRatingDistribution($extension->reviews),
                'active_installations' => $extension->installations->where('status', 'active')->count(),
                'total_installations'  => $extension->installations->count(),
                'recent_downloads'     => $this->getRecentDownloads($extension->id),
                'revenue'              => $this->getExtensionRevenue($extension->id),
            ];

            return new JsonResponse([
                'success' => true,
                'data'    => $analytics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get extension analytics: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.developer.extensions.analytics-failed'),
            ], 500);
        }
    }

    /**
     * Upload a file to storage.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder
     * @return string
     */
    protected function uploadFile(UploadedFile $file, string $folder): string
    {
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = "marketplace/{$folder}/" . $filename;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Delete a file from storage.
     *
     * @param  string  $path
     * @return bool
     */
    protected function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Get rating distribution for reviews.
     *
     * @param  \Illuminate\Support\Collection  $reviews
     * @return array
     */
    protected function getRatingDistribution($reviews): array
    {
        $distribution = [
            '5' => 0,
            '4' => 0,
            '3' => 0,
            '2' => 0,
            '1' => 0,
        ];

        foreach ($reviews as $review) {
            $rating = (string) $review->rating;
            if (isset($distribution[$rating])) {
                $distribution[$rating]++;
            }
        }

        return $distribution;
    }

    /**
     * Get recent downloads (last 30 days).
     *
     * @param  int  $extensionId
     * @return int
     */
    protected function getRecentDownloads(int $extensionId): int
    {
        try {
            $thirtyDaysAgo = now()->subDays(30);

            return \DB::table('extension_installations')
                ->where('extension_id', $extensionId)
                ->where('installed_at', '>=', $thirtyDaysAgo)
                ->count();
        } catch (\Exception $e) {
            Log::warning('Failed to get recent downloads: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get revenue for the extension.
     *
     * @param  int  $extensionId
     * @return float
     */
    protected function getExtensionRevenue(int $extensionId): float
    {
        try {
            return \DB::table('extension_transactions')
                ->where('extension_id', $extensionId)
                ->where('status', 'completed')
                ->sum('seller_revenue');
        } catch (\Exception $e) {
            Log::warning('Failed to get extension revenue: ' . $e->getMessage());
            return 0;
        }
    }
}
