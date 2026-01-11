<?php

namespace Webkul\Marketplace\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\Collaboration\Services\NotificationService;
use Webkul\Marketplace\Events\SubmissionApproved;
use Webkul\Marketplace\Events\ExtensionUpdated;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Services\UpdateNotifier;

class TriggerUpdateChecks
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected UpdateNotifier $updateNotifier,
        protected ExtensionInstallationRepository $installationRepository,
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle submission approved event - notify users with installed extensions about new version.
     */
    public function handleSubmissionApproved(SubmissionApproved $event): void
    {
        try {
            $submission = $event->submission;

            if (!$submission->extension || !$submission->version) {
                return;
            }

            // Only process if this is a new version submission
            if ($submission->submission_type !== 'new_version') {
                return;
            }

            // Find all users who have this extension installed
            $installations = $this->installationRepository
                ->resetScope()
                ->scopeQuery(function ($query) use ($submission) {
                    return $query->where('extension_id', $submission->extension_id)
                        ->where('status', 'active')
                        ->with(['user', 'version']);
                })
                ->all();

            foreach ($installations as $installation) {
                try {
                    // Clear the update cache for this installation
                    $this->updateNotifier->clearCache($installation->id);

                    // Check if this version is newer than the installed version
                    if ($installation->version &&
                        version_compare($submission->version->version, $installation->version->version, '>')) {

                        // Notify the user about the new version
                        $this->notificationService->create([
                            'user_id' => $installation->user_id,
                            'type' => 'extension_update_available',
                            'title' => 'Extension Update Available',
                            'message' => "A new version ({$submission->version->version}) of '{$submission->extension->name}' is now available. You're currently on version {$installation->version->version}.",
                            'data' => [
                                'extension_id' => $submission->extension_id,
                                'extension_name' => $submission->extension->name,
                                'current_version' => $installation->version->version,
                                'new_version' => $submission->version->version,
                                'installation_id' => $installation->id,
                                'changelog' => $submission->version->changelog,
                            ],
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process update notification for installation', [
                        'installation_id' => $installation->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to trigger update checks on submission approval', [
                'submission_id' => $event->submission->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle extension updated event - clear update cache.
     */
    public function handleExtensionUpdated(ExtensionUpdated $event): void
    {
        try {
            $installation = $event->installation;

            // Clear the update cache for this installation
            $this->updateNotifier->clearCache($installation->id);

            // Log the update check trigger
            Log::info('Update cache cleared for installation', [
                'installation_id' => $installation->id,
                'extension_id' => $installation->extension_id,
                'old_version' => $event->oldVersion->version,
                'new_version' => $event->newVersion->version,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear update cache on extension update', [
                'installation_id' => $event->installation->id ?? null,
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
            SubmissionApproved::class => 'handleSubmissionApproved',
            ExtensionUpdated::class => 'handleExtensionUpdated',
        ];
    }
}
