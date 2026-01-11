<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.earnings.transaction-detail.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.earnings.transaction-detail.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.earnings.transaction-detail.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.developer.earnings.transaction-detail.title') #{{ $transaction->id }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.developer.earnings.transaction-detail.description')
            </p>
        </div>

        {!! view_render_event('marketplace.developer.earnings.transaction-detail.header.left.after') !!}

        {!! view_render_event('marketplace.developer.earnings.transaction-detail.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.earnings.transactions') }}"
                class="secondary-button"
            >
                @lang('marketplace::app.developer.earnings.transaction-detail.back')
            </a>
        </div>

        {!! view_render_event('marketplace.developer.earnings.transaction-detail.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.earnings.transaction-detail.header.after') !!}

    {!! view_render_event('marketplace.developer.earnings.transaction-detail.content.before') !!}

    <div class="flex gap-4 max-xl:flex-wrap">
        <!-- Left Section - Transaction Details -->
        <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
            <!-- Transaction Overview -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold dark:text-white">
                        @lang('marketplace::app.developer.earnings.transaction-detail.overview.title')
                    </h3>

                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                        @if($transaction->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                        @elseif($transaction->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                        @elseif($transaction->status === 'refunded') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                        @endif">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Transaction ID -->
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.overview.transaction-id')
                        </p>
                        <p class="mt-1 font-mono text-sm font-medium dark:text-white">
                            #{{ $transaction->id }}
                        </p>
                    </div>

                    <!-- Extension -->
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.overview.extension')
                        </p>
                        <p class="mt-1 text-sm font-medium dark:text-white">
                            {{ $transaction->extension->name ?? 'N/A' }}
                        </p>
                    </div>

                    <!-- Transaction Date -->
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.overview.date')
                        </p>
                        <p class="mt-1 text-sm font-medium dark:text-white">
                            {{ $transaction->created_at->format('M d, Y H:i A') }}
                        </p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.overview.payment-method')
                        </p>
                        <p class="mt-1 text-sm font-medium dark:text-white">
                            {{ ucfirst($transaction->payment_method ?? 'N/A') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Financial Breakdown -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.transaction-detail.financial.title')
                </h3>

                <div class="space-y-3">
                    <!-- Gross Amount -->
                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.transaction-detail.financial.gross-amount')
                        </span>
                        <span class="text-base font-semibold dark:text-white">
                            ${{ number_format($transaction->amount, 2) }}
                        </span>
                    </div>

                    <!-- Platform Fee -->
                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.transaction-detail.financial.platform-fee')
                            <span class="text-xs">({{ number_format($transaction->platform_fee_percentage, 1) }}%)</span>
                        </span>
                        <span class="text-base font-medium text-red-600 dark:text-red-400">
                            -${{ number_format($transaction->platform_fee, 2) }}
                        </span>
                    </div>

                    <!-- Your Earnings -->
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-base font-semibold text-gray-800 dark:text-gray-200">
                            @lang('marketplace::app.developer.earnings.transaction-detail.financial.your-earnings')
                        </span>
                        <span class="text-xl font-bold text-green-600 dark:text-green-500">
                            ${{ number_format($transaction->seller_revenue, 2) }}
                        </span>
                    </div>
                </div>

                @if($transaction->status === 'refunded' && $transaction->refund_amount)
                    <div class="mt-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-red-800 dark:text-red-300">
                                @lang('marketplace::app.developer.earnings.transaction-detail.financial.refund-amount')
                            </span>
                            <span class="text-base font-semibold text-red-800 dark:text-red-300">
                                ${{ number_format($transaction->refund_amount, 2) }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Buyer Information -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.transaction-detail.buyer.title')
                </h3>

                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Name -->
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.buyer.name')
                        </p>
                        <p class="mt-1 text-sm font-medium dark:text-white">
                            {{ $transaction->buyer->name ?? 'Unknown' }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.buyer.email')
                        </p>
                        <p class="mt-1 text-sm font-medium dark:text-white">
                            {{ $transaction->buyer->email ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Additional Notes -->
            @if($transaction->notes)
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold dark:text-white">
                        @lang('marketplace::app.developer.earnings.transaction-detail.notes.title')
                    </h3>

                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $transaction->notes }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Right Sidebar -->
        <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
            <!-- Quick Stats -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.transaction-detail.stats.title')
                </p>

                <div class="space-y-3">
                    <!-- Amount -->
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.stats.total-amount')
                        </p>
                        <p class="mt-1 text-lg font-bold dark:text-white">
                            ${{ number_format($transaction->amount, 2) }}
                        </p>
                    </div>

                    <!-- Your Earnings -->
                    <div class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                        <p class="text-xs text-green-700 dark:text-green-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.stats.your-earnings')
                        </p>
                        <p class="mt-1 text-lg font-bold text-green-700 dark:text-green-400">
                            ${{ number_format($transaction->seller_revenue, 2) }}
                        </p>
                    </div>

                    <!-- Platform Fee -->
                    <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                        <p class="text-xs text-blue-700 dark:text-blue-400">
                            @lang('marketplace::app.developer.earnings.transaction-detail.stats.platform-fee')
                        </p>
                        <p class="mt-1 text-lg font-bold text-blue-700 dark:text-blue-400">
                            ${{ number_format($transaction->platform_fee, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Extension Details -->
            @if($transaction->extension)
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold dark:text-white">
                        @lang('marketplace::app.developer.earnings.transaction-detail.extension.title')
                    </p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="icon-package text-blue-600 dark:text-blue-400"></span>
                            <div class="flex-1">
                                <p class="text-sm font-medium dark:text-white">
                                    {{ $transaction->extension->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ ucfirst($transaction->extension->type) }}
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('developer.marketplace.extensions.show', $transaction->extension->id) }}"
                            class="flex items-center justify-center gap-2 rounded-lg border border-blue-600 px-3 py-2 text-sm font-medium text-blue-600 transition-colors hover:bg-blue-50 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-900/20"
                        >
                            <span class="icon-external-link text-sm"></span>
                            @lang('marketplace::app.developer.earnings.transaction-detail.extension.view')
                        </a>
                    </div>
                </div>
            @endif

            <!-- Timeline -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.transaction-detail.timeline.title')
                </p>

                <div class="space-y-4">
                    <!-- Created -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                            <span class="icon-plus text-sm text-blue-600 dark:text-blue-400"></span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium dark:text-white">
                                @lang('marketplace::app.developer.earnings.transaction-detail.timeline.created')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $transaction->created_at->format('M d, Y H:i A') }}
                            </p>
                        </div>
                    </div>

                    @if($transaction->status === 'completed')
                        <!-- Completed -->
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                <span class="icon-check text-sm text-green-600 dark:text-green-400"></span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium dark:text-white">
                                    @lang('marketplace::app.developer.earnings.transaction-detail.timeline.completed')
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $transaction->updated_at->format('M d, Y H:i A') }}
                                </p>
                            </div>
                        </div>
                    @elseif($transaction->status === 'refunded')
                        <!-- Refunded -->
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                <span class="icon-rotate-ccw text-sm text-red-600 dark:text-red-400"></span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium dark:text-white">
                                    @lang('marketplace::app.developer.earnings.transaction-detail.timeline.refunded')
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $transaction->updated_at->format('M d, Y H:i A') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {!! view_render_event('marketplace.developer.earnings.transaction-detail.content.after') !!}
</x-admin::layouts>
