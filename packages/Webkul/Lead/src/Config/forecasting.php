<?php

return [
    /**
     * Default forecast period type.
     * Options: 'week', 'month', 'quarter'
     */
    'default_period' => env('FORECAST_DEFAULT_PERIOD', 'month'),

    /**
     * Forecast confidence thresholds.
     * These thresholds determine the reliability rating of forecasts.
     */
    'confidence_thresholds' => [
        /**
         * High confidence threshold (e.g., >= 80%).
         * Forecasts above this threshold are considered highly reliable.
         */
        'high' => env('FORECAST_CONFIDENCE_HIGH', 80),

        /**
         * Medium confidence threshold (e.g., >= 60%).
         * Forecasts between medium and high are considered moderately reliable.
         */
        'medium' => env('FORECAST_CONFIDENCE_MEDIUM', 60),

        /**
         * Low confidence threshold (e.g., < 60%).
         * Forecasts below medium threshold are considered low confidence.
         */
        'low' => env('FORECAST_CONFIDENCE_LOW', 40),
    ],

    /**
     * Deal scoring weights.
     * These weights determine how different factors contribute to the overall deal score.
     * Total should equal 100.
     */
    'scoring_weights' => [
        /**
         * Weight for engagement score (emails, activities, interactions).
         * Higher engagement typically indicates more interest and higher win probability.
         */
        'engagement' => env('FORECAST_WEIGHT_ENGAGEMENT', 30),

        /**
         * Weight for velocity score (deal progression speed).
         * Faster-moving deals through stages typically have higher close rates.
         */
        'velocity' => env('FORECAST_WEIGHT_VELOCITY', 25),

        /**
         * Weight for deal value score.
         * Larger deals may receive different prioritization based on strategy.
         */
        'value' => env('FORECAST_WEIGHT_VALUE', 20),

        /**
         * Weight for historical pattern matching.
         * Deals similar to previously won deals score higher.
         */
        'historical_patterns' => env('FORECAST_WEIGHT_HISTORICAL', 15),

        /**
         * Weight for stage probability.
         * Current stage's typical conversion rate.
         */
        'stage_probability' => env('FORECAST_WEIGHT_STAGE', 10),
    ],

    /**
     * Scenario modeling adjustments.
     * Multipliers applied to base forecast for different scenarios.
     */
    'scenarios' => [
        /**
         * Best case scenario multiplier.
         * Assumes optimal conditions and higher close rates.
         */
        'best_case_multiplier' => env('FORECAST_BEST_CASE_MULTIPLIER', 1.0),

        /**
         * Worst case scenario multiplier.
         * Conservative estimate using historical minimum performance.
         */
        'worst_case_multiplier' => env('FORECAST_WORST_CASE_MULTIPLIER', 0.5),

        /**
         * Likely case uses weighted forecast (no multiplier needed).
         */
    ],

    /**
     * Historical analysis settings.
     */
    'historical' => [
        /**
         * Lookback period in months for historical analysis.
         * How far back to analyze data for conversion rates.
         */
        'lookback_months' => env('FORECAST_HISTORICAL_LOOKBACK', 12),

        /**
         * Minimum sample size for statistical confidence.
         * Minimum number of deals required to calculate reliable conversion rates.
         */
        'minimum_sample_size' => env('FORECAST_MIN_SAMPLE_SIZE', 10),

        /**
         * Refresh frequency in days.
         * How often to recalculate historical conversion rates.
         */
        'refresh_frequency_days' => env('FORECAST_HISTORICAL_REFRESH_DAYS', 7),
    ],

    /**
     * Deal velocity settings.
     */
    'velocity' => [
        /**
         * Fast deal threshold in days.
         * Deals moving faster than this are considered high velocity.
         */
        'fast_threshold_days' => env('FORECAST_VELOCITY_FAST_DAYS', 7),

        /**
         * Slow deal threshold in days.
         * Deals moving slower than this are considered low velocity.
         */
        'slow_threshold_days' => env('FORECAST_VELOCITY_SLOW_DAYS', 30),

        /**
         * Stale deal threshold in days.
         * Deals with no activity for this period are flagged as at-risk.
         */
        'stale_threshold_days' => env('FORECAST_VELOCITY_STALE_DAYS', 60),
    ],

    /**
     * Engagement scoring settings.
     */
    'engagement' => [
        /**
         * High engagement threshold.
         * Number of activities/emails in the period that indicates high engagement.
         */
        'high_threshold' => env('FORECAST_ENGAGEMENT_HIGH', 10),

        /**
         * Medium engagement threshold.
         */
        'medium_threshold' => env('FORECAST_ENGAGEMENT_MEDIUM', 5),

        /**
         * Activity types and their weights for engagement scoring.
         */
        'activity_weights' => [
            'email' => 1.0,
            'call' => 1.5,
            'meeting' => 2.0,
            'demo' => 2.5,
        ],
    ],

    /**
     * Caching settings for forecast data.
     */
    'cache' => [
        /**
         * Enable caching for forecast calculations.
         */
        'enabled' => env('FORECAST_CACHE_ENABLED', true),

        /**
         * Cache TTL (time-to-live) in minutes.
         * Forecast data will be cached for this duration.
         */
        'ttl_minutes' => env('FORECAST_CACHE_TTL', 15),

        /**
         * Cache key prefix for forecast data.
         */
        'prefix' => 'forecast_',
    ],

    /**
     * Deal scoring job settings.
     */
    'scoring_job' => [
        /**
         * Enable automatic daily recalculation of deal scores.
         */
        'enabled' => env('FORECAST_SCORING_JOB_ENABLED', true),

        /**
         * Time of day to run scoring job (24-hour format).
         */
        'schedule_time' => env('FORECAST_SCORING_JOB_TIME', '02:00'),

        /**
         * Only score deals updated within this many days.
         * Older inactive deals may be skipped to improve performance.
         */
        'recency_threshold_days' => env('FORECAST_SCORING_RECENCY_DAYS', 90),
    ],

    /**
     * Forecast accuracy tracking settings.
     */
    'accuracy' => [
        /**
         * Enable automatic forecast accuracy tracking.
         */
        'enabled' => env('FORECAST_ACCURACY_ENABLED', true),

        /**
         * Variance threshold for alerts (percentage).
         * Alert when actual varies from forecast by more than this percentage.
         */
        'variance_alert_threshold' => env('FORECAST_VARIANCE_ALERT', 20),

        /**
         * Number of periods to keep for accuracy trending.
         */
        'history_periods' => env('FORECAST_ACCURACY_HISTORY', 12),
    ],

    /**
     * Win probability calculation settings.
     */
    'win_probability' => [
        /**
         * High probability threshold (e.g., >= 70%).
         * Deals above this are considered very likely to close.
         */
        'high_threshold' => env('FORECAST_WIN_PROB_HIGH', 70),

        /**
         * Medium probability threshold (e.g., >= 40%).
         */
        'medium_threshold' => env('FORECAST_WIN_PROB_MEDIUM', 40),

        /**
         * Low probability threshold (e.g., < 40%).
         * Deals below this may need additional attention or nurturing.
         */
        'low_threshold' => env('FORECAST_WIN_PROB_LOW', 40),

        /**
         * Factors that boost win probability calculation.
         */
        'boost_factors' => [
            /**
             * Boost multiplier for deals with recent activity.
             */
            'recent_activity' => env('FORECAST_WIN_PROB_BOOST_ACTIVITY', 1.1),

            /**
             * Boost multiplier for high-value deals.
             */
            'high_value' => env('FORECAST_WIN_PROB_BOOST_VALUE', 1.05),

            /**
             * Boost multiplier for fast-moving deals.
             */
            'fast_velocity' => env('FORECAST_WIN_PROB_BOOST_VELOCITY', 1.15),
        ],
    ],

    /**
     * Data quality settings.
     */
    'data_quality' => [
        /**
         * Warn when forecast is based on insufficient data.
         */
        'warn_insufficient_data' => env('FORECAST_WARN_INSUFFICIENT_DATA', true),

        /**
         * Minimum number of historical deals required for reliable forecasts.
         */
        'minimum_historical_deals' => env('FORECAST_MIN_HISTORICAL_DEALS', 20),

        /**
         * Minimum pipeline value required to generate forecast.
         */
        'minimum_pipeline_value' => env('FORECAST_MIN_PIPELINE_VALUE', 1000),
    ],
];
