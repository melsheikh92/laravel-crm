<?php

namespace Webkul\Marketplace\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Webkul\Marketplace\Contracts\Extension;
use Webkul\Marketplace\Contracts\ExtensionVersion;
use Webkul\Marketplace\Contracts\ExtensionSubmission;
use Webkul\Marketplace\Contracts\ExtensionReview;

/**
 * Trait for authorizing resource ownership in marketplace controllers.
 *
 * This trait provides methods to check if the current user owns a resource
 * and can perform actions on it. It's used primarily in Developer portal
 * controllers to ensure developers can only modify their own content.
 */
trait AuthorizesOwnership
{
    /**
     * Authorize that the current user owns the extension.
     *
     * @param  \Webkul\Marketplace\Contracts\Extension  $extension
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function authorizeExtensionOwnership(Extension $extension): void
    {
        if ($extension->author_id !== Auth::id()) {
            throw new AccessDeniedHttpException(
                trans('marketplace::app.errors.unauthorized-extension-access')
            );
        }
    }

    /**
     * Authorize that the current user owns the extension version through its extension.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionVersion  $version
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function authorizeVersionOwnership(ExtensionVersion $version): void
    {
        if ($version->extension && $version->extension->author_id !== Auth::id()) {
            throw new AccessDeniedHttpException(
                trans('marketplace::app.errors.unauthorized-version-access')
            );
        }
    }

    /**
     * Authorize that the current user owns the submission through its extension.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionSubmission  $submission
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function authorizeSubmissionOwnership(ExtensionSubmission $submission): void
    {
        if ($submission->extension && $submission->extension->author_id !== Auth::id()) {
            throw new AccessDeniedHttpException(
                trans('marketplace::app.errors.unauthorized-submission-access')
            );
        }
    }

    /**
     * Authorize that the current user owns the review.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionReview  $review
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function authorizeReviewOwnership(ExtensionReview $review): void
    {
        if ($review->user_id !== Auth::id()) {
            throw new AccessDeniedHttpException(
                trans('marketplace::app.errors.unauthorized-review-access')
            );
        }
    }

    /**
     * Check if the current user owns the extension.
     *
     * @param  \Webkul\Marketplace\Contracts\Extension  $extension
     * @return bool
     */
    protected function ownsExtension(Extension $extension): bool
    {
        return $extension->author_id === Auth::id();
    }

    /**
     * Check if the current user owns the extension version through its extension.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionVersion  $version
     * @return bool
     */
    protected function ownsVersion(ExtensionVersion $version): bool
    {
        return $version->extension && $version->extension->author_id === Auth::id();
    }

    /**
     * Check if the current user owns the submission through its extension.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionSubmission  $submission
     * @return bool
     */
    protected function ownsSubmission(ExtensionSubmission $submission): bool
    {
        return $submission->extension && $submission->extension->author_id === Auth::id();
    }

    /**
     * Check if the current user owns the review.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionReview  $review
     * @return bool
     */
    protected function ownsReview(ExtensionReview $review): bool
    {
        return $review->user_id === Auth::id();
    }
}
