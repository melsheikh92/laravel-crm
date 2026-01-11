<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.revenue.index.title')
        </x-slot>

        <div class="flex flex-col gap-4">
            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.marketplace.revenue.index.breadcrumbs.before') !!}

                    <x-admin::breadcrumbs name="marketplace.revenue" />

                    {!! view_render_event('admin.marketplace.revenue.index.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.admin.revenue.index.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.marketplace.revenue.index.actions.before') !!}

                    <a href="{{ route('admin.marketplace.revenue.transactions') }}" class="primary-button">
                        @lang('marketplace::app.admin.revenue.index.actions.view-transactions')
                    </a>

                    {!! view_render_event('admin.marketplace.revenue.index.actions.after') !!}
                </div>
            </div>

            {!! view_render_event('admin.marketplace.revenue.index.statistics.before', ['statistics' => $statistics]) !!}

            <!-- Statistics Cards -->
            @if(isset($statistics))
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Gross Revenue -->
                    <div
                        class="box-shadow flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.revenue.index.statistics.total-gross-revenue')
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-white">
                            ${{ number_format($statistics['total_gross_revenue'] ?? 0, 2) }}
                        </div>
                    </div>

                    <!-- Total Platform Fees -->
                    <div
                        class="box-shadow flex flex-col gap-2 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        <div class="text-sm text-blue-700 dark:text-blue-400">
                            @lang('marketplace::app.admin.revenue.index.statistics.total-platform-fees')
                        </div>
                        <div class="text-2xl font-bold text-blue-800 dark:text-blue-300">
                            ${{ number_format($statistics['total_platform_fees'] ?? 0, 2) }}
                        </div>
                    </div>

                    <!-- Total Seller Revenue -->
                    <div
                        class="box-shadow flex flex-col gap-2 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                        <div class="text-sm text-green-700 dark:text-green-400">
                            @lang('marketplace::app.admin.revenue.index.statistics.total-seller-revenue')
                        </div>
                        <div class="text-2xl font-bold text-green-800 dark:text-green-300">
                            ${{ number_format($statistics['total_seller_revenue'] ?? 0, 2) }}
                        </div>
                    </div>

                    <!-- Net Revenue -->
                    <div
                        class="box-shadow flex flex-col gap-2 rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-800 dark:bg-purple-900/20">
                        <div class="text-sm text-purple-700 dark:text-purple-400">
                            @lang('marketplace::app.admin.revenue.index.statistics.net-revenue')
                        </div>
                        <div class="text-2xl font-bold text-purple-800 dark:text-purple-300">
                            ${{ number_format($statistics['net_revenue'] ?? 0, 2) }}
                        </div>
                    </div>

                    <!-- Total Transactions -->
                    <div
                        class="box-shadow flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.revenue.index.statistics.total-transactions')
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-white">
                            {{ number_format($statistics['total_transactions'] ?? 0) }}
                        </div>
                    </div>

                    <!-- Average Transaction -->
                    <div
                        class="box-shadow flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.revenue.index.statistics.average-transaction')
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-white">
                            ${{ number_format($statistics['average_transaction'] ?? 0, 2) }}
                        </div>
                    </div>

                    <!-- Total Refunds -->
                    <div
                        class="box-shadow flex flex-col gap-2 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="text-sm text-red-700 dark:text-red-400">
                            @lang('marketplace::app.admin.revenue.index.statistics.total-refunds')
                        </div>
                        <div class="text-2xl font-bold text-red-800 dark:text-red-300">
                            ${{ number_format($statistics['total_refunds'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            @endif

            {!! view_render_event('admin.marketplace.revenue.index.statistics.after', ['statistics' => $statistics]) !!}

            {!! view_render_event('admin.marketplace.revenue.index.charts.before') !!}

            <!-- Charts Section -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Revenue Overview Chart -->
                <div
                    class="box-shadow flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.revenue.index.charts.revenue-overview')
                        </h3>
                    </div>

                    <div class="flex h-64 items-center justify-center text-gray-500 dark:text-gray-400">
                        @php
                            $chartLabels = ['Gross Revenue', 'Platform Fees', 'Seller Revenue', 'Refunds'];
                            $chartDatasets = [
                                [
                                    'label' => 'Amount ($)',
                                    'data' => [
                                        $statistics['total_gross_revenue'] ?? 0,
                                        $statistics['total_platform_fees'] ?? 0,
                                        $statistics['total_seller_revenue'] ?? 0,
                                        $statistics['total_refunds'] ?? 0,
                                    ],
                                    'backgroundColor' => [
                                        'rgba(107, 114, 128, 0.8)',
                                        'rgba(59, 130, 246, 0.8)',
                                        'rgba(34, 197, 94, 0.8)',
                                        'rgba(239, 68, 68, 0.8)',
                                    ],
                                    'borderColor' => [
                                        'rgb(107, 114, 128)',
                                        'rgb(59, 130, 246)',
                                        'rgb(34, 197, 94)',
                                        'rgb(239, 68, 68)',
                                    ],
                                    'borderWidth' => 1,
                                ]
                            ];
                        @endphp

                        <v-charts-bar :labels='@json($chartLabels)' :datasets='@json($chartDatasets)'
                            :aspect-ratio="2"></v-charts-bar>
                    </div>
                </div>

                <!-- Revenue Trends Placeholder -->
                <div
                    class="box-shadow flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.revenue.index.charts.revenue-trends')
                        </h3>
                    </div>

                    <div class="flex h-64 items-center justify-center text-gray-500 dark:text-gray-400">
                        <v-revenue-trends-chart></v-revenue-trends-chart>
                    </div>
                </div>

                <!-- Top Sellers Placeholder -->
                <div
                    class="box-shadow flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.revenue.index.charts.top-sellers')
                        </h3>
                    </div>

                    <div class="flex h-64 items-center justify-center text-gray-500 dark:text-gray-400">
                        <v-top-sellers-chart></v-top-sellers-chart>
                    </div>
                </div>

                <!-- Top Extensions Placeholder -->
                <div
                    class="box-shadow flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.revenue.index.charts.top-extensions')
                        </h3>
                    </div>

                    <div class="flex h-64 items-center justify-center text-gray-500 dark:text-gray-400">
                        <v-top-extensions-chart></v-top-extensions-chart>
                    </div>
                </div>
            </div>

            {!! view_render_event('admin.marketplace.revenue.index.charts.after') !!}
        </div>

        @pushOnce('scripts')
            <script type="text/x-template" id="v-revenue-trends-chart-template">
                <div class="flex flex-col items-center justify-center gap-2">
                    <span class="icon-information text-4xl"></span>
                    <p>Revenue trends chart will be available with historical data</p>
                </div>
            </script>

            <script type="module">
                app.component('v-revenue-trends-chart', {
                    template: '#v-revenue-trends-chart-template',
                });
            </script>

            <script type="text/x-template" id="v-top-sellers-chart-template">
                <div class="flex flex-col items-center justify-center gap-2">
                    <span class="icon-users text-4xl"></span>
                    <p>Top sellers ranking will be displayed here</p>
                </div>
            </script>

            <script type="module">
                app.component('v-top-sellers-chart', {
                    template: '#v-top-sellers-chart-template',
                });
            </script>

            <script type="text/x-template" id="v-top-extensions-chart-template">
                <div class="flex flex-col items-center justify-center gap-2">
                    <span class="icon-package text-4xl"></span>
                    <p>Top performing extensions will be shown here</p>
                </div>
            </script>

            <script type="module">
                app.component('v-top-extensions-chart', {
                    template: '#v-top-extensions-chart-template',
                });
            </script>
        @endPushOnce
</x-admin::layouts>