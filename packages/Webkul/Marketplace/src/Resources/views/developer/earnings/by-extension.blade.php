<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.earnings.by-extension.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.earnings.by-extension.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.earnings.by-extension.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.developer.earnings.by-extension.title')
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ $extension->name }}
            </p>
        </div>

        {!! view_render_event('marketplace.developer.earnings.by-extension.header.left.after') !!}

        {!! view_render_event('marketplace.developer.earnings.by-extension.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.earnings.index') }}"
                class="secondary-button"
            >
                @lang('marketplace::app.developer.earnings.by-extension.back')
            </a>
        </div>

        {!! view_render_event('marketplace.developer.earnings.by-extension.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.earnings.by-extension.header.after') !!}

    {!! view_render_event('marketplace.developer.earnings.by-extension.content.before') !!}

    <div class="flex gap-4 max-xl:flex-wrap">
        <!-- Left Section -->
        <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
            <!-- Date Range Filter -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <form method="GET" action="{{ route('developer.marketplace.earnings.by_extension', $extension->id) }}" class="flex items-end gap-4 max-sm:flex-col">
                    <div class="flex-1">
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.by-extension.filters.start-date')
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
                            @lang('marketplace::app.developer.earnings.by-extension.filters.end-date')
                        </label>
                        <input
                            type="date"
                            name="end_date"
                            value="{{ request('end_date', now()->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>

                    <button type="submit" class="primary-button max-sm:w-full">
                        @lang('marketplace::app.developer.earnings.by-extension.filters.apply')
                    </button>
                </form>
            </div>

            @if(isset($report) && $report['success'])
                <!-- Revenue Statistics -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Total Revenue -->
                    <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.by-extension.stats.total-revenue')
                        </p>
                        <div class="flex items-center gap-2">
                            <span class="icon-dollar-sign text-2xl text-green-600 dark:text-green-500"></span>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-500">
                                ${{ number_format($report['total_revenue'] ?? 0, 2) }}
                            </p>
                        </div>
                    </div>

                    <!-- Total Sales -->
                    <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.by-extension.stats.total-sales')
                        </p>
                        <div class="flex items-center gap-2">
                            <span class="icon-shopping-bag text-2xl text-blue-600 dark:text-blue-500"></span>
                            <p class="text-2xl font-bold dark:text-white">
                                {{ number_format($report['total_transactions'] ?? 0) }}
                            </p>
                        </div>
                    </div>

                    <!-- Average Sale -->
                    <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.by-extension.stats.average-sale')
                        </p>
                        <div class="flex items-center gap-2">
                            <span class="icon-trending-up text-2xl text-purple-600 dark:text-purple-500"></span>
                            <p class="text-2xl font-bold dark:text-white">
                                ${{ number_format($report['average_transaction'] ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold dark:text-white">
                        @lang('marketplace::app.developer.earnings.by-extension.chart.title')
                    </h3>

                    <div class="flex h-64 items-center justify-center text-gray-500 dark:text-gray-400">
                        <div class="text-center">
                            <span class="icon-bar-chart text-6xl"></span>
                            <p class="mt-2">
                                @lang('marketplace::app.developer.earnings.by-extension.chart.placeholder')
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                @if(isset($report['recent_transactions']) && count($report['recent_transactions']) > 0)
                    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold dark:text-white">
                                @lang('marketplace::app.developer.earnings.by-extension.transactions.title')
                            </h3>
                            <a
                                href="{{ route('developer.marketplace.earnings.transactions') }}"
                                class="text-sm text-blue-600 hover:underline dark:text-blue-400"
                            >
                                @lang('marketplace::app.developer.earnings.by-extension.transactions.view-all')
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                            @lang('marketplace::app.developer.earnings.by-extension.transactions.date')
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                            @lang('marketplace::app.developer.earnings.by-extension.transactions.buyer')
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                            @lang('marketplace::app.developer.earnings.by-extension.transactions.amount')
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                            @lang('marketplace::app.developer.earnings.by-extension.transactions.revenue')
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                            @lang('marketplace::app.developer.earnings.by-extension.transactions.status')
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($report['recent_transactions'] as $transaction)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="px-4 py-3">
                                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $transaction['date'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $transaction['buyer'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm font-medium dark:text-white">
                                                    ${{ number_format($transaction['amount'], 2) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm font-semibold text-green-600 dark:text-green-500">
                                                    ${{ number_format($transaction['revenue'], 2) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                    @if($transaction['status'] === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                                    @elseif($transaction['status'] === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                                    @endif">
                                                    {{ ucfirst($transaction['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @else
                <!-- No Data Available -->
                <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-12 dark:border-gray-800 dark:bg-gray-900">
                    <span class="icon-bar-chart text-6xl text-gray-400 dark:text-gray-600"></span>

                    <p class="mt-4 text-xl font-semibold dark:text-white">
                        @lang('marketplace::app.developer.earnings.by-extension.empty.title')
                    </p>

                    <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.developer.earnings.by-extension.empty.description')
                    </p>
                </div>
            @endif
        </div>

        <!-- Right Section - Extension Info -->
        <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
            <!-- Extension Details -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.by-extension.extension.title')
                </p>

                <div class="space-y-4">
                    <!-- Logo and Name -->
                    <div class="flex items-center gap-3">
                        @if($extension->logo)
                            <img
                                src="{{ Storage::url($extension->logo) }}"
                                alt="{{ $extension->name }}"
                                class="h-16 w-16 rounded-lg object-cover"
                            />
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-purple-600">
                                <span class="icon-package text-2xl text-white"></span>
                            </div>
                        @endif

                        <div class="flex-1">
                            <p class="font-semibold dark:text-white">
                                {{ $extension->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ ucfirst($extension->type) }}
                            </p>
                        </div>
                    </div>

                    <!-- Extension Stats -->
                    <div class="space-y-2 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('marketplace::app.developer.earnings.by-extension.extension.price')
                            </span>
                            <span class="font-semibold text-green-600 dark:text-green-500">
                                @if($extension->price > 0)
                                    ${{ number_format($extension->price, 2) }}
                                @else
                                    @lang('marketplace::app.developer.earnings.by-extension.extension.free')
                                @endif
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('marketplace::app.developer.earnings.by-extension.extension.downloads')
                            </span>
                            <span class="font-semibold dark:text-white">
                                {{ number_format($extension->downloads_count) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('marketplace::app.developer.earnings.by-extension.extension.rating')
                            </span>
                            <div class="flex items-center gap-1">
                                <span class="icon-star text-yellow-500"></span>
                                <span class="font-semibold dark:text-white">
                                    {{ number_format($extension->average_rating, 1) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('marketplace::app.developer.earnings.by-extension.extension.status')
                            </span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                @if($extension->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                @elseif($extension->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                @endif">
                                {{ ucfirst($extension->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                        <a
                            href="{{ route('developer.marketplace.extensions.show', $extension->id) }}"
                            class="flex items-center justify-center gap-2 rounded-lg border border-blue-600 px-4 py-2 text-sm font-medium text-blue-600 transition-colors hover:bg-blue-50 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-900/20"
                        >
                            <span class="icon-external-link"></span>
                            @lang('marketplace::app.developer.earnings.by-extension.extension.view-details')
                        </a>
                    </div>
                </div>
            </div>

            <!-- Performance Tips -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.by-extension.tips.title')
                </p>

                <div class="space-y-3">
                    <div class="flex items-start gap-2">
                        <span class="icon-lightbulb text-yellow-500"></span>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.by-extension.tips.tip1')
                        </p>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="icon-lightbulb text-yellow-500"></span>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.by-extension.tips.tip2')
                        </p>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="icon-lightbulb text-yellow-500"></span>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.by-extension.tips.tip3')
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {!! view_render_event('marketplace.developer.earnings.by-extension.content.after') !!}
</x-admin::layouts>
