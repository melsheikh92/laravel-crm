<?php

namespace Webkul\Marketplace\Http\Controllers\Marketplace;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionReviewRepository;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;

class ReviewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionReviewRepository $reviewRepository,
        protected ExtensionInstallationRepository $installationRepository
    ) {}

    /**
     * Display the specified review.
     */
    public function show(int $id): View|JsonResponse
    {
        try {
            $review = $this->reviewRepository
                ->with(['user', 'extension'])
                ->findOrFail($id);

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => $review,
                ]);
            }

            return view('marketplace::marketplace.reviews.show', compact('review'));
        } catch (Exception $e) {
            Log::error('Failed to load review', [
                'review_id' => $id,
                'error'     => $e->getMessage(),
            ]);

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.reviews.load-failed'),
                ], 404);
            }

            return redirect()->back()
                ->with('error', trans('marketplace::app.marketplace.reviews.load-failed'));
        }
    }

    /**
     * Store a new review for an extension.
     */
    public function store(int $extension_id): JsonResponse|RedirectResponse
    {
        $this->validate(request(), [
            'title'       => 'required|string|max:200',
            'review_text' => 'required|string|max:2000|min:10',
            'rating'      => 'required|integer|min:1|max:5',
        ]);

        try {
            // Find the extension
            $extension = $this->extensionRepository->findOrFail($extension_id);

            // Check if extension is approved
            if (!$extension->isApproved()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.reviews.extension-not-approved'),
                    403
                );
            }

            // Check if user has already reviewed this extension
            if ($this->reviewRepository->hasUserReviewed(Auth::id(), $extension_id)) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.reviews.already-reviewed'),
                    400
                );
            }

            // Check if user has installed this extension (for verified purchase badge)
            $installation = $this->installationRepository->findOneWhere([
                'extension_id' => $extension_id,
                'user_id'      => Auth::id(),
            ]);

            $isVerifiedPurchase = $installation !== null;

            Event::dispatch('marketplace.review.create.before', $extension_id);

            // Create the review
            $review = $this->reviewRepository->create([
                'user_id'               => Auth::id(),
                'extension_id'          => $extension_id,
                'title'                 => request()->input('title'),
                'review_text'           => request()->input('review_text'),
                'rating'                => request()->input('rating'),
                'is_verified_purchase'  => $isVerifiedPurchase,
                'status'                => 'pending', // Reviews need moderation
                'helpful_count'         => 0,
            ]);

            Event::dispatch('marketplace.review.create.after', $review);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.reviews.create-success'),
                    'data'    => [
                        'review' => $review,
                    ],
                ]);
            }

            return redirect()->route('marketplace.extension.show', $extension->slug)
                ->with('success', trans('marketplace::app.marketplace.reviews.create-success'));
        } catch (Exception $e) {
            Log::error('Failed to create review', [
                'extension_id' => $extension_id,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.reviews.create-failed') . ': ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update an existing review.
     */
    public function update(int $id): JsonResponse|RedirectResponse
    {
        $this->validate(request(), [
            'title'       => 'required|string|max:200',
            'review_text' => 'required|string|max:2000|min:10',
            'rating'      => 'required|integer|min:1|max:5',
        ]);

        try {
            $review = $this->reviewRepository->findOrFail($id);

            // Verify ownership
            if ($review->user_id !== Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.reviews.unauthorized'),
                    403
                );
            }

            Event::dispatch('marketplace.review.update.before', $review);

            // Update the review
            $review = $this->reviewRepository->update([
                'title'       => request()->input('title'),
                'review_text' => request()->input('review_text'),
                'rating'      => request()->input('rating'),
                'status'      => 'pending', // Reviews need re-moderation after edit
            ], $id);

            Event::dispatch('marketplace.review.update.after', $review);

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.reviews.update-success'),
                    'data'    => [
                        'review' => $review,
                    ],
                ]);
            }

            return redirect()->route('marketplace.extension.show', $review->extension->slug)
                ->with('success', trans('marketplace::app.marketplace.reviews.update-success'));
        } catch (Exception $e) {
            Log::error('Failed to update review', [
                'review_id' => $id,
                'user_id'   => Auth::id(),
                'error'     => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.reviews.update-failed'),
                500
            );
        }
    }

    /**
     * Delete a review.
     */
    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        try {
            $review = $this->reviewRepository->findOrFail($id);

            // Verify ownership
            if ($review->user_id !== Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.reviews.unauthorized'),
                    403
                );
            }

            $extensionSlug = $review->extension->slug;

            Event::dispatch('marketplace.review.delete.before', $review);

            $this->reviewRepository->delete($id);

            Event::dispatch('marketplace.review.delete.after', $id);

            // Recalculate extension's average rating
            $review->extension->updateAverageRating();

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.reviews.delete-success'),
                ]);
            }

            return redirect()->route('marketplace.extension.show', $extensionSlug)
                ->with('success', trans('marketplace::app.marketplace.reviews.delete-success'));
        } catch (Exception $e) {
            Log::error('Failed to delete review', [
                'review_id' => $id,
                'user_id'   => Auth::id(),
                'error'     => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.reviews.delete-failed'),
                500
            );
        }
    }

    /**
     * Mark a review as helpful.
     */
    public function markHelpful(int $id): JsonResponse|RedirectResponse
    {
        try {
            $review = $this->reviewRepository->findOrFail($id);

            // Check if review is approved
            if (!$review->isApproved()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.reviews.not-approved'),
                    400
                );
            }

            // Check if user already marked this as helpful
            $alreadyMarked = DB::table('extension_review_helpful')
                ->where('review_id', $id)
                ->where('user_id', Auth::id())
                ->exists();

            if ($alreadyMarked) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.reviews.already-marked-helpful'),
                    400
                );
            }

            DB::beginTransaction();

            try {
                // Record that user marked this as helpful
                DB::table('extension_review_helpful')->insert([
                    'review_id'  => $id,
                    'user_id'    => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Increment helpful count
                $review->incrementHelpful();

                Event::dispatch('marketplace.review.helpful.after', $review);

                DB::commit();

                if (request()->ajax()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => trans('marketplace::app.marketplace.reviews.marked-helpful'),
                        'data'    => [
                            'review'        => $review->fresh(),
                            'helpful_count' => $review->helpful_count + 1,
                        ],
                    ]);
                }

                return redirect()->back()
                    ->with('success', trans('marketplace::app.marketplace.reviews.marked-helpful'));
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Failed to mark review as helpful', [
                'review_id' => $id,
                'user_id'   => Auth::id(),
                'error'     => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.reviews.mark-helpful-failed'),
                500
            );
        }
    }

    /**
     * Report a review for abuse.
     */
    public function report(int $id): JsonResponse|RedirectResponse
    {
        $this->validate(request(), [
            'reason' => 'required|string|max:500',
        ]);

        try {
            $review = $this->reviewRepository->findOrFail($id);

            // User cannot report their own review
            if ($review->user_id === Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.reviews.cannot-report-own'),
                    400
                );
            }

            // Check if user already reported this review
            $alreadyReported = DB::table('extension_review_reports')
                ->where('review_id', $id)
                ->where('reported_by', Auth::id())
                ->exists();

            if ($alreadyReported) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.reviews.already-reported'),
                    400
                );
            }

            DB::beginTransaction();

            try {
                // Record the abuse report
                DB::table('extension_review_reports')->insert([
                    'review_id'   => $id,
                    'reported_by' => Auth::id(),
                    'reason'      => request()->input('reason'),
                    'status'      => 'pending',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                // Flag the review if it receives multiple reports (e.g., 3 or more)
                $reportCount = DB::table('extension_review_reports')
                    ->where('review_id', $id)
                    ->count();

                if ($reportCount >= 3 && $review->status !== 'flagged') {
                    $review->flag();
                }

                Event::dispatch('marketplace.review.reported.after', [
                    'review'     => $review,
                    'reported_by' => Auth::id(),
                    'reason'     => request()->input('reason'),
                ]);

                DB::commit();

                if (request()->ajax()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => trans('marketplace::app.marketplace.reviews.report-success'),
                    ]);
                }

                return redirect()->back()
                    ->with('success', trans('marketplace::app.marketplace.reviews.report-success'));
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Failed to report review', [
                'review_id' => $id,
                'user_id'   => Auth::id(),
                'error'     => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.reviews.report-failed'),
                500
            );
        }
    }

    /**
     * Get current user's reviews.
     */
    public function myReviews(): View|JsonResponse
    {
        try {
            $reviews = $this->reviewRepository->getByUser(Auth::id(), false);

            if (request()->ajax()) {
                return new JsonResponse([
                    'data' => $reviews,
                ]);
            }

            return view('marketplace::marketplace.reviews.my-reviews', compact('reviews'));
        } catch (Exception $e) {
            Log::error('Failed to load user reviews', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            if (request()->ajax()) {
                return new JsonResponse([
                    'message' => trans('marketplace::app.marketplace.reviews.load-failed'),
                ], 500);
            }

            return redirect()->back()
                ->with('error', trans('marketplace::app.marketplace.reviews.load-failed'));
        }
    }

    /**
     * Return error response based on request type.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  array  $data
     * @return JsonResponse|RedirectResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400, array $data = []): JsonResponse|RedirectResponse
    {
        if (request()->ajax()) {
            return new JsonResponse(array_merge([
                'success' => false,
                'message' => $message,
            ], $data), $statusCode);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }
}
