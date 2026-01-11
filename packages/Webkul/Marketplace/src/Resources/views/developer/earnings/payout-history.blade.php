<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.earnings.payout-history.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.earnings.payout-history.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.earnings.payout-history.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.developer.earnings.payout-history.title')
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.developer.earnings.payout-history.description')
            </p>
        </div>

        {!! view_render_event('marketplace.developer.earnings.payout-history.header.left.after') !!}

        {!! view_render_event('marketplace.developer.earnings.payout-history.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.earnings.index') }}"
                class="secondary-button"
            >
                @lang('marketplace::app.developer.earnings.payout-history.back-to-dashboard')
            </a>
        </div>

        {!! view_render_event('marketplace.developer.earnings.payout-history.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.earnings.payout-history.header.after') !!}

    {!! view_render_event('marketplace.developer.earnings.payout-history.content.before') !!}

    <div class="flex flex-col gap-4">
        @if($payouts->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-12 dark:border-gray-800 dark:bg-gray-900">
                <span class="icon-dollar-sign text-6xl text-gray-400 dark:text-gray-600"></span>

                <p class="mt-4 text-xl font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.payout-history.empty.title')
                </p>

                <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.earnings.payout-history.empty.description')
                </p>

                <a
                    href="{{ route('developer.marketplace.earnings.index') }}"
                    class="primary-button mt-6"
                >
                    @lang('marketplace::app.developer.earnings.payout-history.empty.back')
                </a>
            </div>
        @else
            <!-- Payouts Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.payout-history.table.id')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.payout-history.table.extension')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.payout-history.table.amount')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.payout-history.table.payout')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.payout-history.table.status')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.payout-history.table.date')
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($payouts as $payout)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <!-- Transaction ID -->
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                            #{{ $payout->id }}
                                        </span>
                                    </td>

                                    <!-- Extension -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="icon-package text-blue-600 dark:text-blue-400"></span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $payout->extension->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Transaction Amount -->
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            ${{ number_format($payout->amount, 2) }}
                                        </span>
                                    </td>

                                    <!-- Payout Amount -->
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-semibold text-green-600 dark:text-green-500">
                                            ${{ number_format($payout->seller_revenue, 2) }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            {{ ucfirst($payout->status) }}
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ $payout->created_at->format('M d, Y') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Footer -->
                <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex gap-6">
                            <div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.developer.earnings.payout-history.summary.total-payouts')
                                </span>
                                <span class="ml-2 text-sm font-semibold text-green-600 dark:text-green-500">
                                    ${{ number_format($payouts->sum('seller_revenue'), 2) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.developer.earnings.payout-history.summary.count')
                                </span>
                                <span class="ml-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $payouts->total() }}
                                </span>
                            </div>
                        </div>

                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.payout-history.summary.showing')
                            {{ $payouts->firstItem() ?? 0 }} - {{ $payouts->lastItem() ?? 0 }}
                            @lang('marketplace::app.developer.earnings.payout-history.summary.of')
                            {{ $payouts->total() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>

    {!! view_render_event('marketplace.developer.earnings.payout-history.content.after') !!}
</x-admin::layouts>
