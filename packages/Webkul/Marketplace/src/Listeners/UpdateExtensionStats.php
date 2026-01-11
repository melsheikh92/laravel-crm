<?php

namespace Webkul\Marketplace\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Marketplace\Events\ExtensionInstalled;
use Webkul\Marketplace\Events\ExtensionReviewed;
use Webkul\Marketplace\Events\ExtensionUninstalled;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class UpdateExtensionStats
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionVersionRepository $versionRepository
    ) {}

    /**
     * Handle extension installed event.
     */
    public function handleExtensionInstalled(ExtensionInstalled $event): void
    {
        try {
            $installation = $event->installation;

            if (!$installation->extension_id) {
                return;
            }

            // Increment downloads count for the extension
            DB::table('marketplace_extensions')
                ->where('id', $installation->extension_id)
                ->increment('downloads_count');

            // Increment downloads count for the version
            if ($installation->version_id) {
                DB::table('marketplace_extension_versions')
                    ->where('id', $installation->version_id)
                    ->increment('downloads_count');
            }
        } catch (\Exception $e) {
            Log::error('Failed to update extension stats on installation', [
                'installation_id' => $event->installation->id ?? null,
                'extension_id' => $event->installation->extension_id ?? null,
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

            if (!$installation->extension_id) {
                return;
            }

            // Decrement active installations count (if we're tracking it)
            // This could be a custom counter or calculated from installations table
            // For now, we'll just log the uninstallation
            Log::info('Extension uninstalled', [
                'installation_id' => $installation->id,
                'extension_id' => $installation->extension_id,
                'user_id' => $installation->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update extension stats on uninstallation', [
                'installation_id' => $event->installation->id ?? null,
                'extension_id' => $event->installation->extension_id ?? null,
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

            if (!$review->extension_id) {
                return;
            }

            // Recalculate average rating for the extension
            $avgRating = DB::table('marketplace_extension_reviews')
                ->where('extension_id', $review->extension_id)
                ->avg('rating');

            // Update the extension's average rating
            $this->extensionRepository->update([
                'average_rating' => round($avgRating, 2),
            ], $review->extension_id);
        } catch (\Exception $e) {
            Log::error('Failed to update extension stats on review', [
                'review_id' => $event->review->id ?? null,
                'extension_id' => $event->review->extension_id ?? null,
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
            ExtensionUninstalled::class => 'handleExtensionUninstalled',
            ExtensionReviewed::class => 'handleExtensionReviewed',
        ];
    }
}
