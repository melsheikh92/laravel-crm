<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.earnings.transactions.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.earnings.transactions.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.earnings.transactions.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.developer.earnings.transactions.title')
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.developer.earnings.transactions.description')
            </p>
        </div>

        {!! view_render_event('marketplace.developer.earnings.transactions.header.left.after') !!}

        {!! view_render_event('marketplace.developer.earnings.transactions.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.earnings.index') }}"
                class="secondary-button"
            >
                @lang('marketplace::app.developer.earnings.transactions.back-to-dashboard')
            </a>
        </div>

        {!! view_render_event('marketplace.developer.earnings.transactions.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.earnings.transactions.header.after') !!}

    {!! view_render_event('marketplace.developer.earnings.transactions.content.before') !!}

    <div class="flex flex-col gap-4">
        @if($transactions->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-12 dark:border-gray-800 dark:bg-gray-900">
                <span class="icon-credit-card text-6xl text-gray-400 dark:text-gray-600"></span>

                <p class="mt-4 text-xl font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.transactions.empty.title')
                </p>

                <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.earnings.transactions.empty.description')
                </p>

                <a
                    href="{{ route('developer.marketplace.earnings.index') }}"
                    class="primary-button mt-6"
                >
                    @lang('marketplace::app.developer.earnings.transactions.empty.back')
                </a>
            </div>
        @else
            <!-- Transactions Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.transactions.table.id')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.transactions.table.extension')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.transactions.table.buyer')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.transactions.table.amount')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.transactions.table.your-share')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.transactions.table.status')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.transactions.table.date')
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-600 dark:text-gray-300">
                                    @lang('marketplace::app.developer.earnings.transactions.table.actions')
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($transactions as $transaction)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <!-- Transaction ID -->
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                            #{{ $transaction->id }}
                                        </span>
                                    </td>

                                    <!-- Extension -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="icon-package text-blue-600 dark:text-blue-400"></span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $transaction->extension->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Buyer -->
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ $transaction->buyer->name ?? 'Unknown' }}
                                        </span>
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </td>

                                    <!-- Seller Revenue -->
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-semibold text-green-600 dark:text-green-500">
                                            ${{ number_format($transaction->seller_revenue, 2) }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            @if($transaction->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($transaction->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                            @elseif($transaction->status === 'refunded') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                            @endif">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ $transaction->created_at->format('M d, Y') }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <a
                                            href="{{ route('developer.marketplace.earnings.transactions.show', $transaction->id) }}"
                                            class="text-blue-600 hover:underline dark:text-blue-400"
                                        >
                                            @lang('marketplace::app.developer.earnings.transactions.table.view')
                                        </a>
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
                                    @lang('marketplace::app.developer.earnings.transactions.summary.total-amount')
                                </span>
                                <span class="ml-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    ${{ number_format($transactions->sum('amount'), 2) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.developer.earnings.transactions.summary.your-total')
                                </span>
                                <span class="ml-2 text-sm font-semibold text-green-600 dark:text-green-500">
                                    ${{ number_format($transactions->sum('seller_revenue'), 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.transactions.summary.showing')
                            {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }}
                            @lang('marketplace::app.developer.earnings.transactions.summary.of')
                            {{ $transactions->total() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    {!! view_render_event('marketplace.developer.earnings.transactions.content.after') !!}
</x-admin::layouts>
