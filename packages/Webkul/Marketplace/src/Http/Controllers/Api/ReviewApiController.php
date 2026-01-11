<?php

namespace Webkul\Marketplace\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionReviewRepository;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;

class ReviewApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionReviewRepository $reviewRepository,
        protected ExtensionRepository $extensionRepository,
        protected ExtensionInstallationRepository $installationRepository
    ) {}

    /**
     * Get user's reviews.
     */
    public function myReviews(): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();
            $reviews = $this->reviewRepository->getByUser($userId, false);

            return new JsonResponse([
                'data' => $reviews,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve reviews');
        }
    }

    /**
     * Display the specified review.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $review = $this->reviewRepository
                ->with(['user', 'extension'])
                ->findOrFail($id);

            return new JsonResponse([
                'data' => $review,
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Review not found', 404);
        }
    }

    /**
     * Store a newly created review.
     */
    public function store(int $extension_id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            // Check if extension exists
            $extension = $this->extensionRepository->findOrFail($extension_id);

            // Check if user has already reviewed this extension
            if ($this->reviewRepository->hasUserReviewed($userId, $extension_id)) {
                return new JsonResponse([
                    'message' => 'You have already reviewed this extension',
                ], 422);
            }

            $this->validate(request(), [
                'rating'      => 'required|integer|min:1|max:5',
                'title'       => 'nullable|string|max:255',
                'review_text' => 'required|string|min:10',
            ]);

            // Check if user has purchased/installed the extension (for verified purchase flag)
            $installation = $this->installationRepository->findOneWhere([
                'user_id'      => $userId,
                'extension_id' => $extension_id,
            ]);

            $isVerifiedPurchase = $installation !== null;

            Event::dispatch('marketplace.review.create.before');

            $review = $this->reviewRepository->create([
                'user_id'              => $userId,
                'extension_id'         => $extension_id,
                'rating'               => request()->input('rating'),
                'title'                => request()->input('title'),
                'review_text'          => request()->input('review_text'),
                'is_verified_purchase' => $isVerifiedPurchase,
                'status'               => 'pending', // Reviews need moderation
            ]);

            Event::dispatch('marketplace.review.create.after', $review);

            return new JsonResponse([
                'data'    => $review,
                'message' => 'Review submitted successfully and is pending moderation',
            ], 201);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to create review');
        }
    }

    /**
     * Update the specified review.
     */
    public function update(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $review = $this->reviewRepository->findOrFail($id);

            // Check if user owns the review
            if ($review->user_id !== $userId) {
                return new JsonResponse([
                    'message' => 'Unauthorized to update this review',
                ], 403);
            }

            $this->validate(request(), [
                'rating'      => 'required|integer|min:1|max:5',
                'title'       => 'nullable|string|max:255',
                'review_text' => 'required|string|min:10',
            ]);

            Event::dispatch('marketplace.review.update.before', $id);

            $review = $this->reviewRepository->update([
                'rating'      => request()->input('rating'),
                'title'       => request()->input('title'),
                'review_text' => request()->input('review_text'),
                'status'      => 'pending', // Re-moderation required after edit
            ], $id);

            Event::dispatch('marketplace.review.update.after', $review);

            return new JsonResponse([
                'data'    => $review,
                'message' => 'Review updated successfully and is pending re-moderation',
            ], 200);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to update review', 404);
        }
    }

    /**
     * Remove the specified review.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $review = $this->reviewRepository->findOrFail($id);

            // Check if user owns the review
            if ($review->user_id !== $userId) {
                return new JsonResponse([
                    'message' => 'Unauthorized to delete this review',
                ], 403);
            }

            Event::dispatch('marketplace.review.delete.before', $id);

            $this->reviewRepository->delete($id);

            Event::dispatch('marketplace.review.delete.after', $id);

            return new JsonResponse([
                'message' => 'Review deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to delete review', 404);
        }
    }

    /**
     * Mark a review as helpful.
     */
    public function markHelpful(int $id): JsonResponse
    {
        try {
            $review = $this->reviewRepository->findOrFail($id);

            // Increment helpful count
            $review->increment('helpful_count');

            return new JsonResponse([
                'data'    => $review->fresh(),
                'message' => 'Review marked as helpful',
            ], 200);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to mark review as helpful', 404);
        }
    }

    /**
     * Report a review for moderation.
     */
    public function report(int $id): JsonResponse
    {
        try {
            $userId = auth()->guard('user')->id();

            $review = $this->reviewRepository->findOrFail($id);

            $this->validate(request(), [
                'reason' => 'required|string|max:500',
            ]);

            // Update review status to flagged
            Event::dispatch('marketplace.review.report.before', $id);

            $review = $this->reviewRepository->update([
                'status' => 'flagged',
            ], $id);

            Event::dispatch('marketplace.review.report.after', [
                'review'       => $review,
                'reported_by'  => $userId,
                'reason'       => request()->input('reason'),
            ]);

            return new JsonResponse([
                'message' => 'Review reported successfully and will be reviewed by moderators',
            ], 200);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to report review', 404);
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
