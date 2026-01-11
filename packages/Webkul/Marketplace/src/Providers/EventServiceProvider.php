<?php

namespace Webkul\Marketplace\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Webkul\Marketplace\Events\ExtensionInstalled;
use Webkul\Marketplace\Events\ExtensionUninstalled;
use Webkul\Marketplace\Events\ExtensionUpdated;
use Webkul\Marketplace\Events\ExtensionReviewed;
use Webkul\Marketplace\Events\ExtensionSubmitted;
use Webkul\Marketplace\Events\SubmissionApproved;
use Webkul\Marketplace\Events\SubmissionRejected;
use Webkul\Marketplace\Listeners\SendInstallationNotification;
use Webkul\Marketplace\Listeners\UpdateExtensionStats;
use Webkul\Marketplace\Listeners\LogMarketplaceActivity;
use Webkul\Marketplace\Listeners\TriggerUpdateChecks;
use Webkul\Marketplace\Listeners\ClearExtensionCache;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ExtensionInstalled::class => [
            SendInstallationNotification::class . '@handleExtensionInstalled',
            UpdateExtensionStats::class . '@handleExtensionInstalled',
            LogMarketplaceActivity::class . '@handleExtensionInstalled',
            ClearExtensionCache::class . '@onExtensionCreated',
        ],

        ExtensionUpdated::class => [
            SendInstallationNotification::class . '@handleExtensionUpdated',
            LogMarketplaceActivity::class . '@handleExtensionUpdated',
            TriggerUpdateChecks::class . '@handleExtensionUpdated',
            ClearExtensionCache::class . '@onExtensionUpdated',
        ],

        ExtensionUninstalled::class => [
            SendInstallationNotification::class . '@handleExtensionUninstalled',
            UpdateExtensionStats::class . '@handleExtensionUninstalled',
            LogMarketplaceActivity::class . '@handleExtensionUninstalled',
            ClearExtensionCache::class . '@onExtensionDeleted',
        ],

        ExtensionReviewed::class => [
            SendInstallationNotification::class . '@handleExtensionReviewed',
            UpdateExtensionStats::class . '@handleExtensionReviewed',
            LogMarketplaceActivity::class . '@handleExtensionReviewed',
            ClearExtensionCache::class . '@onReviewCreated',
        ],

        ExtensionSubmitted::class => [
            LogMarketplaceActivity::class . '@handleExtensionSubmitted',
        ],

        SubmissionApproved::class => [
            SendInstallationNotification::class . '@handleSubmissionApproved',
            LogMarketplaceActivity::class . '@handleSubmissionApproved',
            TriggerUpdateChecks::class . '@handleSubmissionApproved',
            ClearExtensionCache::class . '@onExtensionUpdated',
        ],

        SubmissionRejected::class => [
            SendInstallationNotification::class . '@handleSubmissionRejected',
            LogMarketplaceActivity::class . '@handleSubmissionRejected',
            ClearExtensionCache::class . '@onExtensionUpdated',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
