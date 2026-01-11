<?php

return [
    /**
     * Marketplace.
     */
    [
        'key' => 'marketplace',
        'name' => 'marketplace::app.layouts.marketplace',
        'route' => 'marketplace.browse.index',
        'sort' => 9,
        'icon-class' => 'icon-product',
    ],
    [
        'key' => 'marketplace.browse',
        'name' => 'marketplace::app.layouts.browse',
        'route' => 'marketplace.browse.index',
        'sort' => 1,
        'icon-class' => '',
    ],
    [
        'key' => 'marketplace.my-extensions',
        'name' => 'marketplace::app.layouts.my-extensions',
        'route' => 'marketplace.my_extensions.index',
        'sort' => 2,
        'icon-class' => '',
    ],
    [
        'key' => 'marketplace.become-developer',
        'name' => 'marketplace::app.layouts.become-developer',
        'route' => 'marketplace.developer-registration.create',
        'sort' => 3,
        'icon-class' => '',
    ],

    /**
     * Developer Portal.
     */
    [
        'key' => 'developer',
        'name' => 'marketplace::app.layouts.developer',
        'route' => 'developer.dashboard.index',
        'sort' => 10,
        'icon-class' => 'icon-configuration',
    ],
    [
        'key' => 'developer.dashboard',
        'name' => 'marketplace::app.layouts.dashboard',
        'route' => 'developer.dashboard.index',
        'sort' => 1,
        'icon-class' => '',
    ],
    [
        'key' => 'developer.extensions',
        'name' => 'marketplace::app.layouts.my-extensions',
        'route' => 'developer.extensions.index',
        'sort' => 2,
        'icon-class' => '',
    ],
    [
        'key' => 'developer.submissions',
        'name' => 'marketplace::app.layouts.submissions',
        'route' => 'developer.submissions.index',
        'sort' => 3,
        'icon-class' => '',
    ],
    [
        'key' => 'developer.earnings',
        'name' => 'marketplace::app.layouts.earnings',
        'route' => 'developer.earnings.index',
        'sort' => 4,
        'icon-class' => '',
    ],

    /**
     * Admin - Marketplace Management.
     */
    [
        'key' => 'settings.marketplace',
        'name' => 'marketplace::app.layouts.marketplace-management',
        'info' => 'marketplace::app.layouts.marketplace-management-info',
        'route' => 'admin.marketplace.extensions.index',
        'sort' => 5,
        'icon-class' => '',
    ],
    [
        'key' => 'settings.marketplace.extensions',
        'name' => 'marketplace::app.layouts.extensions',
        'info' => 'marketplace::app.layouts.extensions-info',
        'route' => 'admin.marketplace.extensions.index',
        'sort' => 1,
        'icon-class' => 'icon-product',
    ],
    [
        'key' => 'settings.marketplace.submissions',
        'name' => 'marketplace::app.layouts.review-submissions',
        'info' => 'marketplace::app.layouts.submissions-info',
        'route' => 'admin.marketplace.submissions.index',
        'sort' => 2,
        'icon-class' => 'icon-note',
    ],
    [
        'key' => 'settings.marketplace.categories',
        'name' => 'marketplace::app.layouts.categories',
        'info' => 'marketplace::app.layouts.categories-info',
        'route' => 'admin.marketplace.categories.index',
        'sort' => 3,
        'icon-class' => 'icon-settings-group',
    ],
    [
        'key' => 'settings.marketplace.revenue',
        'name' => 'marketplace::app.layouts.revenue',
        'info' => 'marketplace::app.layouts.revenue-info',
        'route' => 'admin.marketplace.revenue.index',
        'sort' => 4,
        'icon-class' => 'icon-product',
    ],
    [
        'key' => 'settings.marketplace.developer-applications',
        'name' => 'marketplace::app.layouts.developer-applications',
        'info' => 'marketplace::app.layouts.developer-applications-info',
        'route' => 'admin.marketplace.developer-applications.index',
        'sort' => 5,
        'icon-class' => 'icon-user',
    ],
];
