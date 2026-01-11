<?php

namespace Webkul\Marketplace\Listeners;

use Webkul\Marketplace\Services\MarketplaceCache;

class ClearExtensionCache
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        protected MarketplaceCache $cache
    ) {}

    /**
     * Handle the event when an extension is created.
     *
     * @param  object  $event
     * @return void
     */
    public function onExtensionCreated($event)
    {
        $this->cache->clearListings();
    }

    /**
     * Handle the event when an extension is updated.
     *
     * @param  object  $event
     * @return void
     */
    public function onExtensionUpdated($event)
    {
        if (isset($event->extension)) {
            $this->cache->clearExtension($event->extension->id);
        }

        $this->cache->clearListings();
    }

    /**
     * Handle the event when an extension is deleted.
     *
     * @param  object  $event
     * @return void
     */
    public function onExtensionDeleted($event)
    {
        if (isset($event->extension)) {
            $this->cache->clearExtension($event->extension->id);
        }

        $this->cache->clearListings();
    }

    /**
     * Handle the event when an extension review is created.
     *
     * @param  object  $event
     * @return void
     */
    public function onReviewCreated($event)
    {
        if (isset($event->review) && isset($event->review->extension_id)) {
            $this->cache->clearExtension($event->review->extension_id);
        }
    }

    /**
     * Handle the event when an extension review is updated.
     *
     * @param  object  $event
     * @return void
     */
    public function onReviewUpdated($event)
    {
        if (isset($event->review) && isset($event->review->extension_id)) {
            $this->cache->clearExtension($event->review->extension_id);
        }
    }

    /**
     * Handle the event when a category is created or updated.
     *
     * @param  object  $event
     * @return void
     */
    public function onCategoryChanged($event)
    {
        if (isset($event->category)) {
            $this->cache->clearCategory($event->category->id);
        }

        $this->cache->clearListings();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        // Extension events
        $events->listen(
            'marketplace.extension.create.after',
            [ClearExtensionCache::class, 'onExtensionCreated']
        );

        $events->listen(
            'marketplace.extension.update.after',
            [ClearExtensionCache::class, 'onExtensionUpdated']
        );

        $events->listen(
            'marketplace.extension.delete.after',
            [ClearExtensionCache::class, 'onExtensionDeleted']
        );

        // Review events
        $events->listen(
            'marketplace.review.create.after',
            [ClearExtensionCache::class, 'onReviewCreated']
        );

        $events->listen(
            'marketplace.review.update.after',
            [ClearExtensionCache::class, 'onReviewUpdated']
        );

        // Category events
        $events->listen(
            'marketplace.category.create.after',
            [ClearExtensionCache::class, 'onCategoryChanged']
        );

        $events->listen(
            'marketplace.category.update.after',
            [ClearExtensionCache::class, 'onCategoryChanged']
        );

        $events->listen(
            'marketplace.category.delete.after',
            [ClearExtensionCache::class, 'onCategoryChanged']
        );
    }
}
