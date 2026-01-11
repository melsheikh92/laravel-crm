<?php

namespace Webkul\Marketplace\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('extension_categories')->delete();

        $now = Carbon::now();

        $defaultLocale = $parameters['locale'] ?? config('app.locale');

        DB::table('extension_categories')->insert([
            // Root Categories
            [
                'id'          => 1,
                'name'        => trans('marketplace::app.seeders.categories.integrations.name', [], $defaultLocale),
                'slug'        => 'integrations',
                'description' => trans('marketplace::app.seeders.categories.integrations.description', [], $defaultLocale),
                'icon'        => 'icon-link',
                'sort_order'  => 1,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 2,
                'name'        => trans('marketplace::app.seeders.categories.themes.name', [], $defaultLocale),
                'slug'        => 'themes',
                'description' => trans('marketplace::app.seeders.categories.themes.description', [], $defaultLocale),
                'icon'        => 'icon-palette',
                'sort_order'  => 2,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 3,
                'name'        => trans('marketplace::app.seeders.categories.productivity.name', [], $defaultLocale),
                'slug'        => 'productivity',
                'description' => trans('marketplace::app.seeders.categories.productivity.description', [], $defaultLocale),
                'icon'        => 'icon-trending-up',
                'sort_order'  => 3,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 4,
                'name'        => trans('marketplace::app.seeders.categories.analytics.name', [], $defaultLocale),
                'slug'        => 'analytics',
                'description' => trans('marketplace::app.seeders.categories.analytics.description', [], $defaultLocale),
                'icon'        => 'icon-bar-chart',
                'sort_order'  => 4,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 5,
                'name'        => trans('marketplace::app.seeders.categories.communication.name', [], $defaultLocale),
                'slug'        => 'communication',
                'description' => trans('marketplace::app.seeders.categories.communication.description', [], $defaultLocale),
                'icon'        => 'icon-message-circle',
                'sort_order'  => 5,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 6,
                'name'        => trans('marketplace::app.seeders.categories.sales-marketing.name', [], $defaultLocale),
                'slug'        => 'sales-marketing',
                'description' => trans('marketplace::app.seeders.categories.sales-marketing.description', [], $defaultLocale),
                'icon'        => 'icon-trending-up',
                'sort_order'  => 6,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 7,
                'name'        => trans('marketplace::app.seeders.categories.finance.name', [], $defaultLocale),
                'slug'        => 'finance',
                'description' => trans('marketplace::app.seeders.categories.finance.description', [], $defaultLocale),
                'icon'        => 'icon-dollar-sign',
                'sort_order'  => 7,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 8,
                'name'        => trans('marketplace::app.seeders.categories.customer-support.name', [], $defaultLocale),
                'slug'        => 'customer-support',
                'description' => trans('marketplace::app.seeders.categories.customer-support.description', [], $defaultLocale),
                'icon'        => 'icon-headphones',
                'sort_order'  => 8,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 9,
                'name'        => trans('marketplace::app.seeders.categories.development.name', [], $defaultLocale),
                'slug'        => 'development',
                'description' => trans('marketplace::app.seeders.categories.development.description', [], $defaultLocale),
                'icon'        => 'icon-code',
                'sort_order'  => 9,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 10,
                'name'        => trans('marketplace::app.seeders.categories.automation.name', [], $defaultLocale),
                'slug'        => 'automation',
                'description' => trans('marketplace::app.seeders.categories.automation.description', [], $defaultLocale),
                'icon'        => 'icon-zap',
                'sort_order'  => 10,
                'parent_id'   => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // Subcategories for Integrations
            [
                'id'          => 11,
                'name'        => trans('marketplace::app.seeders.categories.integrations-email.name', [], $defaultLocale),
                'slug'        => 'integrations-email',
                'description' => trans('marketplace::app.seeders.categories.integrations-email.description', [], $defaultLocale),
                'icon'        => 'icon-mail',
                'sort_order'  => 1,
                'parent_id'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 12,
                'name'        => trans('marketplace::app.seeders.categories.integrations-payment.name', [], $defaultLocale),
                'slug'        => 'integrations-payment',
                'description' => trans('marketplace::app.seeders.categories.integrations-payment.description', [], $defaultLocale),
                'icon'        => 'icon-credit-card',
                'sort_order'  => 2,
                'parent_id'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 13,
                'name'        => trans('marketplace::app.seeders.categories.integrations-social.name', [], $defaultLocale),
                'slug'        => 'integrations-social',
                'description' => trans('marketplace::app.seeders.categories.integrations-social.description', [], $defaultLocale),
                'icon'        => 'icon-share-2',
                'sort_order'  => 3,
                'parent_id'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 14,
                'name'        => trans('marketplace::app.seeders.categories.integrations-calendar.name', [], $defaultLocale),
                'slug'        => 'integrations-calendar',
                'description' => trans('marketplace::app.seeders.categories.integrations-calendar.description', [], $defaultLocale),
                'icon'        => 'icon-calendar',
                'sort_order'  => 4,
                'parent_id'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // Subcategories for Communication
            [
                'id'          => 15,
                'name'        => trans('marketplace::app.seeders.categories.communication-chat.name', [], $defaultLocale),
                'slug'        => 'communication-chat',
                'description' => trans('marketplace::app.seeders.categories.communication-chat.description', [], $defaultLocale),
                'icon'        => 'icon-message-square',
                'sort_order'  => 1,
                'parent_id'   => 5,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 16,
                'name'        => trans('marketplace::app.seeders.categories.communication-voip.name', [], $defaultLocale),
                'slug'        => 'communication-voip',
                'description' => trans('marketplace::app.seeders.categories.communication-voip.description', [], $defaultLocale),
                'icon'        => 'icon-phone',
                'sort_order'  => 2,
                'parent_id'   => 5,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 17,
                'name'        => trans('marketplace::app.seeders.categories.communication-video.name', [], $defaultLocale),
                'slug'        => 'communication-video',
                'description' => trans('marketplace::app.seeders.categories.communication-video.description', [], $defaultLocale),
                'icon'        => 'icon-video',
                'sort_order'  => 3,
                'parent_id'   => 5,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // Subcategories for Development
            [
                'id'          => 18,
                'name'        => trans('marketplace::app.seeders.categories.development-api.name', [], $defaultLocale),
                'slug'        => 'development-api',
                'description' => trans('marketplace::app.seeders.categories.development-api.description', [], $defaultLocale),
                'icon'        => 'icon-server',
                'sort_order'  => 1,
                'parent_id'   => 9,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 19,
                'name'        => trans('marketplace::app.seeders.categories.development-testing.name', [], $defaultLocale),
                'slug'        => 'development-testing',
                'description' => trans('marketplace::app.seeders.categories.development-testing.description', [], $defaultLocale),
                'icon'        => 'icon-check-circle',
                'sort_order'  => 2,
                'parent_id'   => 9,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 20,
                'name'        => trans('marketplace::app.seeders.categories.development-debugging.name', [], $defaultLocale),
                'slug'        => 'development-debugging',
                'description' => trans('marketplace::app.seeders.categories.development-debugging.description', [], $defaultLocale),
                'icon'        => 'icon-bug',
                'sort_order'  => 3,
                'parent_id'   => 9,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }
}
