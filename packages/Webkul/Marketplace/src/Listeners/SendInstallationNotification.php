<?php

namespace Webkul\Marketplace\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\Collaboration\Services\NotificationService;
use Webkul\Marketplace\Events\ExtensionInstalled;
use Webkul\Marketplace\Events\ExtensionUninstalled;
use Webkul\Marketplace\Events\ExtensionUpdated;
use Webkul\Marketplace\Events\ExtensionReviewed;
use Webkul\Marketplace\Events\SubmissionApproved;
use Webkul\Marketplace\Events\SubmissionRejected;

class SendInstallationNotification
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle extension installed event.
     */
    public function handleExtensionInstalled(ExtensionInstalled $event): void
    {
        try {
            $installation = $event->installation;

            if (!$installation->extension || !$installation->user) {
                return;
            }

            // Notify the user about successful installation
            $this->notificationService->create([
                'user_id' => $installation->user_id,
                'type' => 'extension_installed',
                'title' => 'Extension Installed Successfully',
                'message' => "The extension '{$installation->extension->name}' has been installed successfully.",
                'data' => [
                    'extension_id' => $installation->extension_id,
                    'installation_id' => $installation->id,
                    'extension_name' => $installation->extension->name,
                    'version' => $installation->version->version ?? null,
                ],
            ]);

            // Notify the extension author about new installation
            if ($installation->extension->author_id) {
                $this->notificationService->create([
                    'user_id' => $installation->extension->author_id,
                    'type' => 'extension_new_installation',
                    'title' => 'New Installation',
                    'message' => "Your extension '{$installation->extension->name}' was installed by {$installation->user->name}.",
                    'data' => [
                        'extension_id' => $installation->extension_id,
                        'installation_id' => $installation->id,
                        'extension_name' => $installation->extension->name,
                        'installer_name' => $installation->user->name,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send extension installed notification', [
                'installation_id' => $event->installation->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle extension updated event.
     */
    public function handleExtensionUpdated(ExtensionUpdated $event): void
    {
        try {
            $installation = $event->installation;

            if (!$installation->extension || !$installation->user) {
                return;
            }

            // Notify the user about successful update
            $this->notificationService->create([
                'user_id' => $installation->user_id,
                'type' => 'extension_updated',
                'title' => 'Extension Updated Successfully',
                'message' => "The extension '{$installation->extension->name}' has been updated from version {$event->oldVersion->version} to {$event->newVersion->version}.",
                'data' => [
                    'extension_id' => $installation->extension_id,
                    'installation_id' => $installation->id,
                    'extension_name' => $installation->extension->name,
                    'old_version' => $event->oldVersion->version,
                    'new_version' => $event->newVersion->version,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send extension updated notification', [
                'installation_id' => $event->installation->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle extension uninstalled event.
     */
    public function handleExtensionUninstalled(ExtensionUninstalled $event): void
    {
        try {
            $installation = $event->installation;

            if (!$installation->extension || !$installation->user) {
                return;
            }

            // Notify the user about successful uninstallation
            $this->notificationService->create([
                'user_id' => $installation->user_id,
                'type' => 'extension_uninstalled',
                'title' => 'Extension Uninstalled',
                'message' => "The extension '{$installation->extension->name}' has been uninstalled.",
                'data' => [
                    'extension_id' => $installation->extension_id,
                    'extension_name' => $installation->extension->name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send extension uninstalled notification', [
                'installation_id' => $event->installation->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle extension reviewed event.
     */
    public function handleExtensionReviewed(ExtensionReviewed $event): void
    {
        try {
            $review = $event->review;

            if (!$review->extension || !$review->extension->author) {
                return;
            }

            // Notify the extension author about new review
            $this->notificationService->create([
                'user_id' => $review->extension->author_id,
                'type' => 'extension_new_review',
                'title' => 'New Review Received',
                'message' => "Your extension '{$review->extension->name}' received a new {$review->rating}-star review from {$review->user->name}.",
                'data' => [
                    'extension_id' => $review->extension_id,
                    'review_id' => $review->id,
                    'extension_name' => $review->extension->name,
                    'reviewer_name' => $review->user->name ?? 'Anonymous',
                    'rating' => $review->rating,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send extension reviewed notification', [
                'review_id' => $event->review->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle submission approved event.
     */
    public function handleSubmissionApproved(SubmissionApproved $event): void
    {
        try {
            $submission = $event->submission;

            if (!$submission->extension || !$submission->submitter) {
                return;
            }

            // Notify the submitter about approval
            $version = $submission->version->version ?? 'N/A';
            $this->notificationService->create([
                'user_id' => $submission->submitted_by,
                'type' => 'submission_approved',
                'title' => 'Submission Approved',
                'message' => "Your submission for '{$submission->extension->name}' version {$version} has been approved and is now available in the marketplace.",
                'data' => [
                    'extension_id' => $submission->extension_id,
                    'submission_id' => $submission->id,
                    'extension_name' => $submission->extension->name,
                    'version' => $submission->version->version ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send submission approved notification', [
                'submission_id' => $event->submission->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle submission rejected event.
     */
    public function handleSubmissionRejected(SubmissionRejected $event): void
    {
        try {
            $submission = $event->submission;

            if (!$submission->extension || !$submission->submitter) {
                return;
            }

            // Notify the submitter about rejection
            $this->notificationService->create([
                'user_id' => $submission->submitted_by,
                'type' => 'submission_rejected',
                'title' => 'Submission Rejected',
                'message' => "Your submission for '{$submission->extension->name}' has been rejected. Reason: {$submission->rejection_reason}",
                'data' => [
                    'extension_id' => $submission->extension_id,
                    'submission_id' => $submission->id,
                    'extension_name' => $submission->extension->name,
                    'rejection_reason' => $submission->rejection_reason,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send submission rejected notification', [
                'submission_id' => $event->submission->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array
     */
    public function subscribe($events): array
    {
        return [
            ExtensionInstalled::class => 'handleExtensionInstalled',
            ExtensionUpdated::class => 'handleExtensionUpdated',
            ExtensionUninstalled::class => 'handleExtensionUninstalled',
            ExtensionReviewed::class => 'handleExtensionReviewed',
            SubmissionApproved::class => 'handleSubmissionApproved',
            SubmissionRejected::class => 'handleSubmissionRejected',
        ];
    }
}
