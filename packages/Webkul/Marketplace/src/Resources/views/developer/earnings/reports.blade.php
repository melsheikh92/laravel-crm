<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.earnings.reports.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.earnings.reports.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.earnings.reports.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.developer.earnings.reports.title')
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.developer.earnings.reports.description')
            </p>
        </div>

        {!! view_render_event('marketplace.developer.earnings.reports.header.left.after') !!}

        {!! view_render_event('marketplace.developer.earnings.reports.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.earnings.index') }}"
                class="secondary-button"
            >
                @lang('marketplace::app.developer.earnings.reports.back-to-dashboard')
            </a>
        </div>

        {!! view_render_event('marketplace.developer.earnings.reports.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.earnings.reports.header.after') !!}

    {!! view_render_event('marketplace.developer.earnings.reports.content.before') !!}

    <div class="flex flex-col gap-4">
        <!-- Date Range Filter -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('developer.marketplace.earnings.reports') }}" class="flex items-end gap-4 max-sm:flex-col">
                <div class="flex-1">
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.reports.filters.start-date')
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div class="flex-1">
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.reports.filters.end-date')
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        value="{{ request('end_date', now()->format('Y-m-d')) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <button type="submit" class="primary-button max-sm:w-full">
                    @lang('marketplace::app.developer.earnings.reports.filters.generate')
                </button>
            </form>
        </div>

        @if(isset($report) && $report['success'])
            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Revenue -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.reports.stats.total-revenue')
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="icon-dollar-sign text-2xl text-green-600 dark:text-green-500"></span>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-500">
                            ${{ number_format($report['total_revenue'] ?? 0, 2) }}
                        </p>
                    </div>
                </div>

                <!-- Total Transactions -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.reports.stats.total-transactions')
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="icon-shopping-bag text-2xl text-blue-600 dark:text-blue-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            {{ number_format($report['total_transactions'] ?? 0) }}
                        </p>
                    </div>
                </div>

                <!-- Average Transaction -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.reports.stats.average-transaction')
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="icon-trending-up text-2xl text-purple-600 dark:text-purple-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            ${{ number_format($report['average_transaction'] ?? 0, 2) }}
                        </p>
                    </div>
                </div>

                <!-- Total Platform Fees -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.reports.stats.platform-fees')
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="icon-percent text-2xl text-orange-600 dark:text-orange-500"></span>
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-500">
                            ${{ number_format($report['total_platform_fees'] ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Revenue by Extension -->
            @if(isset($report['extensions_breakdown']) && count($report['extensions_breakdown']) > 0)
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold dark:text-white">
                        @lang('marketplace::app.developer.earnings.reports.breakdown.title')
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                        @lang('marketplace::app.developer.earnings.reports.breakdown.extension')
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                        @lang('marketplace::app.developer.earnings.reports.breakdown.sales')
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                        @lang('marketplace::app.developer.earnings.reports.breakdown.revenue')
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                        @lang('marketplace::app.developer.earnings.reports.breakdown.average')
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($report['extensions_breakdown'] as $extension)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-package text-blue-600 dark:text-blue-400"></span>
                                                <span class="text-sm font-medium dark:text-white">
                                                    {{ $extension['name'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                                {{ number_format($extension['sales']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm font-semibold text-green-600 dark:text-green-500">
                                                ${{ number_format($extension['revenue'], 2) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                                ${{ number_format($extension['average'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Revenue Chart -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.reports.chart.title')
                </h3>

                <div class="flex h-64 items-center justify-center text-gray-500 dark:text-gray-400">
                    <div class="text-center">
                        <span class="icon-bar-chart text-6xl"></span>
                        <p class="mt-2">
                            @lang('marketplace::app.developer.earnings.reports.chart.placeholder')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold dark:text-white">
                            @lang('marketplace::app.developer.earnings.reports.export.title')
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.reports.export.description')
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button class="secondary-button">
                            <span class="icon-download mr-2"></span>
                            @lang('marketplace::app.developer.earnings.reports.export.pdf')
                        </button>
                        <button class="secondary-button">
                            <span class="icon-download mr-2"></span>
                            @lang('marketplace::app.developer.earnings.reports.export.csv')
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- No Report Generated -->
            <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-12 dark:border-gray-800 dark:bg-gray-900">
                <span class="icon-file-text text-6xl text-gray-400 dark:text-gray-600"></span>

                <p class="mt-4 text-xl font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.reports.empty.title')
                </p>

                <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.earnings.reports.empty.description')
                </p>
            </div>
        @endif
    </div>

    {!! view_render_event('marketplace.developer.earnings.reports.content.after') !!}
</x-admin::layouts>
