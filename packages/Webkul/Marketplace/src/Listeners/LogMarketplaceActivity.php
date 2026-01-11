<?php

namespace Webkul\Marketplace\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\Marketplace\Events\ExtensionInstalled;
use Webkul\Marketplace\Events\ExtensionUninstalled;
use Webkul\Marketplace\Events\ExtensionUpdated;
use Webkul\Marketplace\Events\ExtensionReviewed;
use Webkul\Marketplace\Events\ExtensionSubmitted;
use Webkul\Marketplace\Events\SubmissionApproved;
use Webkul\Marketplace\Events\SubmissionRejected;

class LogMarketplaceActivity
{
    /**
     * Handle extension installed event.
     */
    public function handleExtensionInstalled(ExtensionInstalled $event): void
    {
        $this->logActivity('extension_installed', $event->installation, [
            'extension_id' => $event->installation->extension_id,
            'extension_name' => $event->installation->extension->name ?? 'Unknown',
            'user_id' => $event->installation->user_id,
            'user_name' => $event->installation->user->name ?? 'Unknown',
            'version' => $event->installation->version->version ?? null,
            'installation_id' => $event->installation->id,
        ]);
    }

    /**
     * Handle extension updated event.
     */
    public function handleExtensionUpdated(ExtensionUpdated $event): void
    {
        $this->logActivity('extension_updated', $event->installation, [
            'extension_id' => $event->installation->extension_id,
            'extension_name' => $event->installation->extension->name ?? 'Unknown',
            'user_id' => $event->installation->user_id,
            'user_name' => $event->installation->user->name ?? 'Unknown',
            'old_version' => $event->oldVersion->version,
            'new_version' => $event->newVersion->version,
            'installation_id' => $event->installation->id,
        ]);
    }

    /**
     * Handle extension uninstalled event.
     */
    public function handleExtensionUninstalled(ExtensionUninstalled $event): void
    {
        $this->logActivity('extension_uninstalled', $event->installation, [
            'extension_id' => $event->installation->extension_id,
            'extension_name' => $event->installation->extension->name ?? 'Unknown',
            'user_id' => $event->installation->user_id,
            'user_name' => $event->installation->user->name ?? 'Unknown',
            'version' => $event->installation->version->version ?? null,
            'installation_id' => $event->installation->id,
        ]);
    }

    /**
     * Handle extension reviewed event.
     */
    public function handleExtensionReviewed(ExtensionReviewed $event): void
    {
        $this->logActivity('extension_reviewed', $event->review, [
            'extension_id' => $event->review->extension_id,
            'extension_name' => $event->review->extension->name ?? 'Unknown',
            'review_id' => $event->review->id,
            'rating' => $event->review->rating,
            'user_id' => $event->review->user_id,
            'user_name' => $event->review->user->name ?? 'Unknown',
        ]);
    }

    /**
     * Handle extension submitted event.
     */
    public function handleExtensionSubmitted(ExtensionSubmitted $event): void
    {
        $this->logActivity('extension_submitted', $event->submission, [
            'extension_id' => $event->submission->extension_id,
            'extension_name' => $event->submission->extension->name ?? 'Unknown',
            'submission_id' => $event->submission->id,
            'submission_type' => $event->submission->submission_type,
            'submitted_by' => $event->submission->submitted_by,
            'submitter_name' => $event->submission->submitter->name ?? 'Unknown',
            'version' => $event->submission->version->version ?? null,
        ]);
    }

    /**
     * Handle submission approved event.
     */
    public function handleSubmissionApproved(SubmissionApproved $event): void
    {
        $this->logActivity('submission_approved', $event->submission, [
            'extension_id' => $event->submission->extension_id,
            'extension_name' => $event->submission->extension->name ?? 'Unknown',
            'submission_id' => $event->submission->id,
            'submission_type' => $event->submission->submission_type,
            'submitted_by' => $event->submission->submitted_by,
            'submitter_name' => $event->submission->submitter->name ?? 'Unknown',
            'reviewed_by' => $event->submission->reviewed_by,
            'reviewer_name' => $event->submission->reviewer->name ?? 'Unknown',
            'version' => $event->submission->version->version ?? null,
        ]);
    }

    /**
     * Handle submission rejected event.
     */
    public function handleSubmissionRejected(SubmissionRejected $event): void
    {
        $this->logActivity('submission_rejected', $event->submission, [
            'extension_id' => $event->submission->extension_id,
            'extension_name' => $event->submission->extension->name ?? 'Unknown',
            'submission_id' => $event->submission->id,
            'submission_type' => $event->submission->submission_type,
            'submitted_by' => $event->submission->submitted_by,
            'submitter_name' => $event->submission->submitter->name ?? 'Unknown',
            'reviewed_by' => $event->submission->reviewed_by,
            'reviewer_name' => $event->submission->reviewer->name ?? 'Unknown',
            'rejection_reason' => $event->submission->rejection_reason,
            'version' => $event->submission->version->version ?? null,
        ]);
    }

    /**
     * Log marketplace activity to the application log.
     *
     * @param  string  $action
     * @param  mixed  $subject
     * @param  array  $context
     * @return void
     */
    protected function logActivity(string $action, $subject, array $context = []): void
    {
        try {
            Log::channel('daily')->info("Marketplace Activity: {$action}", array_merge([
                'action' => $action,
                'subject_type' => get_class($subject),
                'subject_id' => $subject->id ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ], $context));
        } catch (\Exception $e) {
            Log::error('Failed to log marketplace activity', [
                'action' => $action,
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
            ExtensionSubmitted::class => 'handleExtensionSubmitted',
            SubmissionApproved::class => 'handleSubmissionApproved',
            SubmissionRejected::class => 'handleSubmissionRejected',
        ];
    }
}
