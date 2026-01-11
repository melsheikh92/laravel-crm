<?php

return [
    /**
     * Marketplace - User Permissions.
     */
    [
        'key' => 'marketplace',
        'name' => 'marketplace::app.acl.marketplace',
        'route' => 'marketplace.browse.index',
        'sort' => 9,
    ],
    [
        'key' => 'marketplace.browse',
        'name' => 'marketplace::app.acl.browse',
        'route' => [
            'marketplace.browse.index',
            'marketplace.browse.search',
            'marketplace.browse.category',
            'marketplace.browse.type',
            'marketplace.browse.featured',
            'marketplace.browse.popular',
            'marketplace.browse.recent',
            'marketplace.browse.free',
            'marketplace.browse.paid',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'marketplace.extensions',
        'name' => 'marketplace::app.acl.view-extensions',
        'route' => [
            'marketplace.extension.show',
            'marketplace.extension.versions',
            'marketplace.extension.reviews',
            'marketplace.extension.changelog',
            'marketplace.extension.compatibility',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'marketplace.install',
        'name' => 'marketplace::app.acl.install',
        'route' => [
            'marketplace.install.extension',
            'marketplace.install.status',
            'marketplace.install.check_compatibility',
            'marketplace.install.update',
            'marketplace.install.uninstall',
            'marketplace.install.enable',
            'marketplace.install.disable',
            'marketplace.install.toggle_auto_update',
        ],
        'sort' => 3,
    ],
    [
        'key' => 'marketplace.my-extensions',
        'name' => 'marketplace::app.acl.my-extensions',
        'route' => [
            'marketplace.my_extensions.index',
            'marketplace.my_extensions.show',
            'marketplace.my_extensions.settings',
            'marketplace.my_extensions.update_settings',
            'marketplace.my_extensions.updates',
            'marketplace.my_extensions.check_updates',
        ],
        'sort' => 4,
    ],
    [
        'key' => 'marketplace.reviews',
        'name' => 'marketplace::app.acl.reviews',
        'route' => [
            'marketplace.reviews.store',
            'marketplace.reviews.show',
            'marketplace.reviews.update',
            'marketplace.reviews.destroy',
            'marketplace.reviews.helpful',
            'marketplace.reviews.report',
            'marketplace.reviews.my_reviews',
        ],
        'sort' => 5,
    ],
    [
        'key' => 'marketplace.payments',
        'name' => 'marketplace::app.acl.payments',
        'route' => [
            'marketplace.payment.initiate',
            'marketplace.payment.callback',
            'marketplace.payment.status',
            'marketplace.payment.cancel',
            'marketplace.payment.refund',
        ],
        'sort' => 6,
    ],

    /**
     * Developer Portal Permissions.
     */
    [
        'key' => 'developer',
        'name' => 'marketplace::app.acl.developer',
        'route' => [
            'developer.marketplace.dashboard',
            'developer.marketplace.dashboard.statistics',
        ],
        'sort' => 10,
    ],
    [
        'key' => 'developer.extensions',
        'name' => 'marketplace::app.acl.extensions',
        'route' => [
            'developer.marketplace.extensions.index',
            'developer.marketplace.extensions.show',
            'developer.marketplace.extensions.analytics',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'developer.extensions.create',
        'name' => 'marketplace::app.acl.create',
        'route' => [
            'developer.marketplace.extensions.create',
            'developer.marketplace.extensions.store',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'developer.extensions.edit',
        'name' => 'marketplace::app.acl.edit',
        'route' => [
            'developer.marketplace.extensions.edit',
            'developer.marketplace.extensions.update',
            'developer.marketplace.extensions.upload_logo',
            'developer.marketplace.extensions.upload_screenshots',
            'developer.marketplace.extensions.delete_screenshot',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'developer.extensions.delete',
        'name' => 'marketplace::app.acl.delete',
        'route' => 'developer.marketplace.extensions.destroy',
        'sort' => 3,
    ],
    [
        'key' => 'developer.versions',
        'name' => 'marketplace::app.acl.versions',
        'route' => [
            'developer.marketplace.versions.index',
            'developer.marketplace.versions.show',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'developer.versions.create',
        'name' => 'marketplace::app.acl.create',
        'route' => [
            'developer.marketplace.versions.create',
            'developer.marketplace.versions.store',
            'developer.marketplace.versions.upload_package',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'developer.versions.edit',
        'name' => 'marketplace::app.acl.edit',
        'route' => [
            'developer.marketplace.versions.edit',
            'developer.marketplace.versions.update',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'developer.versions.delete',
        'name' => 'marketplace::app.acl.delete',
        'route' => 'developer.marketplace.versions.destroy',
        'sort' => 3,
    ],
    [
        'key' => 'developer.versions.download',
        'name' => 'marketplace::app.acl.download',
        'route' => 'developer.marketplace.versions.download',
        'sort' => 4,
    ],
    [
        'key' => 'developer.submissions',
        'name' => 'marketplace::app.acl.submissions',
        'route' => [
            'developer.marketplace.submissions.index',
            'developer.marketplace.submissions.show',
            'developer.marketplace.submissions.by_extension',
            'developer.marketplace.submissions.pending_count',
        ],
        'sort' => 3,
    ],
    [
        'key' => 'developer.submissions.create',
        'name' => 'marketplace::app.acl.submit',
        'route' => [
            'developer.marketplace.submissions.submit',
            'developer.marketplace.submissions.resubmit',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'developer.submissions.cancel',
        'name' => 'marketplace::app.acl.cancel',
        'route' => 'developer.marketplace.submissions.cancel',
        'sort' => 2,
    ],
    [
        'key' => 'developer.earnings',
        'name' => 'marketplace::app.acl.earnings',
        'route' => [
            'developer.marketplace.earnings.index',
            'developer.marketplace.earnings.transactions',
            'developer.marketplace.earnings.transactions.show',
            'developer.marketplace.earnings.reports',
            'developer.marketplace.earnings.statistics',
            'developer.marketplace.earnings.payout_history',
            'developer.marketplace.earnings.by_extension',
        ],
        'sort' => 4,
    ],
    [
        'key' => 'developer.earnings.payout',
        'name' => 'marketplace::app.acl.request-payout',
        'route' => 'developer.marketplace.earnings.request_payout',
        'sort' => 1,
    ],

    /**
     * Admin - Marketplace Management Permissions.
     */
    [
        'key' => 'settings.marketplace',
        'name' => 'marketplace::app.acl.marketplace-management',
        'route' => 'admin.marketplace.extensions.index',
        'sort' => 500,
    ],
    [
        'key' => 'settings.marketplace.extensions',
        'name' => 'marketplace::app.acl.extensions',
        'route' => [
            'admin.marketplace.extensions.index',
            'admin.marketplace.extensions.show',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'settings.marketplace.extensions.create',
        'name' => 'marketplace::app.acl.create',
        'route' => [
            'admin.marketplace.extensions.create',
            'admin.marketplace.extensions.store',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'settings.marketplace.extensions.edit',
        'name' => 'marketplace::app.acl.edit',
        'route' => [
            'admin.marketplace.extensions.edit',
            'admin.marketplace.extensions.update',
            'admin.marketplace.extensions.enable',
            'admin.marketplace.extensions.disable',
            'admin.marketplace.extensions.feature',
            'admin.marketplace.extensions.unfeature',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'settings.marketplace.extensions.delete',
        'name' => 'marketplace::app.acl.delete',
        'route' => [
            'admin.marketplace.extensions.destroy',
            'admin.marketplace.extensions.mass_destroy',
            'admin.marketplace.extensions.mass_enable',
            'admin.marketplace.extensions.mass_disable',
        ],
        'sort' => 3,
    ],
    [
        'key' => 'settings.marketplace.submissions',
        'name' => 'marketplace::app.acl.submissions',
        'route' => [
            'admin.marketplace.submissions.index',
            'admin.marketplace.submissions.show',
            'admin.marketplace.submissions.pending_count',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'settings.marketplace.submissions.review',
        'name' => 'marketplace::app.acl.review',
        'route' => [
            'admin.marketplace.submissions.review',
            'admin.marketplace.submissions.approve',
            'admin.marketplace.submissions.reject',
            'admin.marketplace.submissions.mass_approve',
            'admin.marketplace.submissions.mass_reject',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'settings.marketplace.submissions.security',
        'name' => 'marketplace::app.acl.security-scan',
        'route' => [
            'admin.marketplace.submissions.security_scan',
            'admin.marketplace.submissions.security_scan_results',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'settings.marketplace.categories',
        'name' => 'marketplace::app.acl.categories',
        'route' => [
            'admin.marketplace.categories.index',
            'admin.marketplace.categories.show',
            'admin.marketplace.categories.tree_data',
        ],
        'sort' => 3,
    ],
    [
        'key' => 'settings.marketplace.categories.create',
        'name' => 'marketplace::app.acl.create',
        'route' => [
            'admin.marketplace.categories.create',
            'admin.marketplace.categories.store',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'settings.marketplace.categories.edit',
        'name' => 'marketplace::app.acl.edit',
        'route' => [
            'admin.marketplace.categories.edit',
            'admin.marketplace.categories.update',
            'admin.marketplace.categories.reorder',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'settings.marketplace.categories.delete',
        'name' => 'marketplace::app.acl.delete',
        'route' => [
            'admin.marketplace.categories.destroy',
            'admin.marketplace.categories.mass_destroy',
        ],
        'sort' => 3,
    ],
    [
        'key' => 'settings.marketplace.developer-applications',
        'name' => 'marketplace::app.acl.developer-applications',
        'route' => [
            'admin.marketplace.developer-applications.index',
            'admin.marketplace.developer-applications.show',
            'admin.marketplace.developer-applications.pending_count',
        ],
        'sort' => 4,
    ],
    [
        'key' => 'settings.marketplace.developer-applications.manage',
        'name' => 'marketplace::app.acl.manage',
        'route' => [
            'admin.marketplace.developer-applications.approve',
            'admin.marketplace.developer-applications.reject',
            'admin.marketplace.developer-applications.suspend',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'settings.marketplace.revenue',
        'name' => 'marketplace::app.acl.revenue',
        'route' => [
            'admin.marketplace.revenue.index',
            'admin.marketplace.revenue.statistics',
        ],
        'sort' => 5,
    ],
    [
        'key' => 'settings.marketplace.revenue.transactions',
        'name' => 'marketplace::app.acl.transactions',
        'route' => [
            'admin.marketplace.revenue.transactions',
            'admin.marketplace.revenue.transactions.show',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'settings.marketplace.revenue.transactions.refund',
        'name' => 'marketplace::app.acl.refund',
        'route' => 'admin.marketplace.revenue.transactions.refund',
        'sort' => 1,
    ],
    [
        'key' => 'settings.marketplace.revenue.reports',
        'name' => 'marketplace::app.acl.reports',
        'route' => [
            'admin.marketplace.revenue.reports',
            'admin.marketplace.revenue.reports.platform',
            'admin.marketplace.revenue.reports.seller',
            'admin.marketplace.revenue.reports.extension',
            'admin.marketplace.revenue.top_sellers',
            'admin.marketplace.revenue.top_extensions',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'settings.marketplace.revenue.settings',
        'name' => 'marketplace::app.acl.settings',
        'route' => 'admin.marketplace.revenue.settings.update',
        'sort' => 3,
    ],
];
