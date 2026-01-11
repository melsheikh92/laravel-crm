<?php

return [
    /**
     * Platform fee percentage for revenue sharing.
     * This is the percentage of each transaction that goes to the platform.
     * The remaining percentage goes to the extension seller.
     *
     * Example: 30 means the platform takes 30% and the seller receives 70%
     */
    'platform_fee_percentage' => env('MARKETPLACE_PLATFORM_FEE_PERCENTAGE', 30),

    /**
     * Minimum payout amount for sellers.
     * Sellers can only request a payout when their balance reaches this amount.
     */
    'minimum_payout_amount' => env('MARKETPLACE_MINIMUM_PAYOUT_AMOUNT', 50),

    /**
     * Payout schedule in days.
     * How often payouts are processed (e.g., 7 for weekly, 30 for monthly).
     */
    'payout_schedule_days' => env('MARKETPLACE_PAYOUT_SCHEDULE_DAYS', 30),

    /**
     * Refund policy period in days.
     * How many days after purchase can customers request a refund.
     */
    'refund_period_days' => env('MARKETPLACE_REFUND_PERIOD_DAYS', 30),

    /**
     * Security scan settings.
     */
    'security' => [
        /**
         * Automatically scan extensions on submission.
         */
        'auto_scan' => env('MARKETPLACE_AUTO_SCAN', true),

        /**
         * Require manual review even if security scan passes.
         */
        'require_manual_review' => env('MARKETPLACE_REQUIRE_MANUAL_REVIEW', true),
    ],

    /**
     * Storage settings.
     */
    'storage' => [
        /**
         * Path where extension packages are stored.
         */
        'package_path' => storage_path('app/marketplace/packages'),

        /**
         * Path where extension backups are stored.
         */
        'backup_path' => storage_path('app/marketplace/backups'),

        /**
         * Maximum package size in megabytes.
         */
        'max_package_size' => env('MARKETPLACE_MAX_PACKAGE_SIZE', 50),
    ],

    /**
     * Cache settings.
     */
    'cache' => [
        /**
         * Enable caching for extension listings.
         */
        'enabled' => env('MARKETPLACE_CACHE_ENABLED', true),

        /**
         * Cache TTL in minutes.
         */
        'ttl' => env('MARKETPLACE_CACHE_TTL', 60),
    ],

    /**
     * Payment gateway settings.
     */
    'payment' => [
        /**
         * Default payment gateway to use.
         * Options: 'stripe'
         */
        'default_gateway' => env('MARKETPLACE_PAYMENT_GATEWAY', 'stripe'),

        /**
         * Payment gateway configurations.
         */
        'gateways' => [
            'stripe' => [
                'secret_key' => env('STRIPE_SECRET_KEY'),
                'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
                'currency' => env('STRIPE_CURRENCY', 'usd'),
                'test_mode' => env('STRIPE_TEST_MODE', false),
            ],
        ],

        /**
         * Default currency for transactions.
         */
        'default_currency' => env('MARKETPLACE_CURRENCY', 'USD'),
    ],
];
