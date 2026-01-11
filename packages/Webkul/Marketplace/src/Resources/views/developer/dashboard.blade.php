<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.dashboard.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.dashboard.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.dashboard.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.developer.dashboard.title')
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.developer.dashboard.description')
            </p>
        </div>

        {!! view_render_event('marketplace.developer.dashboard.header.left.after') !!}

        {!! view_render_event('marketplace.developer.dashboard.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.extensions.create') }}"
                class="primary-button"
            >
                @lang('marketplace::app.developer.dashboard.create-extension')
            </a>
        </div>

        {!! view_render_event('marketplace.developer.dashboard.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.dashboard.header.after') !!}

    {!! view_render_event('marketplace.developer.dashboard.content.before') !!}

    <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
        <!-- Left Section -->
        {!! view_render_event('marketplace.developer.dashboard.content.left.before') !!}

        <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
            <!-- Main Statistics Cards -->
            <div class="grid grid-cols-4 gap-4 max-lg:grid-cols-2 max-sm:grid-cols-1">
                <!-- Total Downloads -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.dashboard.stats.total-downloads')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-download text-2xl text-blue-600 dark:text-blue-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            {{ number_format($statistics['total_downloads']) }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($statistics['recent_downloads']) }} @lang('marketplace::app.developer.dashboard.stats.last-30-days')
                    </p>
                </div>

                <!-- Total Revenue -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.dashboard.stats.total-revenue')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-dollar-sign text-2xl text-green-600 dark:text-green-500"></span>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-500">
                            ${{ number_format($statistics['total_revenue'], 2) }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        ${{ number_format($statistics['recent_revenue'], 2) }} @lang('marketplace::app.developer.dashboard.stats.last-30-days')
                    </p>
                </div>

                <!-- Active Extensions -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.dashboard.stats.active-extensions')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-check-circle text-2xl text-emerald-600 dark:text-emerald-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            {{ $statistics['active_extensions'] }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $statistics['total_extensions'] }} @lang('marketplace::app.developer.dashboard.stats.total-extensions')
                    </p>
                </div>

                <!-- Pending Reviews -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.dashboard.stats.pending-reviews')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-clock text-2xl text-orange-600 dark:text-orange-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            {{ $statistics['pending_reviews'] }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.developer.dashboard.stats.awaiting-approval')
                    </p>
                </div>
            </div>

            <!-- Secondary Statistics Cards -->
            <div class="grid grid-cols-3 gap-4 max-lg:grid-cols-2 max-sm:grid-cols-1">
                <!-- Average Rating -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.dashboard.stats.average-rating')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-star text-2xl text-yellow-500 dark:text-yellow-400"></span>
                        <p class="text-xl font-bold dark:text-white">
                            {{ number_format($statistics['average_rating'], 1) }}
                        </p>
                        <span class="text-sm text-gray-500 dark:text-gray-400">/ 5.0</span>
                    </div>
                </div>

                <!-- Total Transactions -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.dashboard.stats.total-transactions')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-credit-card text-2xl text-purple-600 dark:text-purple-500"></span>
                        <p class="text-xl font-bold dark:text-white">
                            {{ number_format($statistics['total_transactions']) }}
                        </p>
                    </div>
                </div>

                <!-- Draft Extensions -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.dashboard.stats.draft-extensions')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-edit text-2xl text-gray-600 dark:text-gray-400"></span>
                        <p class="text-xl font-bold dark:text-white">
                            {{ $statistics['draft_extensions'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.dashboard.quick-actions.title')
                </p>

                <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                    <!-- Create Extension -->
                    <a
                        href="{{ route('developer.marketplace.extensions.create') }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-blue-400 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-500"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <span class="icon-plus text-xl"></span>
                        </span>
                        <div>
                            <p class="font-semibold dark:text-white">
                                @lang('marketplace::app.developer.dashboard.quick-actions.create-extension')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('marketplace::app.developer.dashboard.quick-actions.create-extension-desc')
                            </p>
                        </div>
                    </a>

                    <!-- View Extensions -->
                    <a
                        href="{{ route('developer.marketplace.extensions.index') }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-blue-400 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-500"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="icon-package text-xl"></span>
                        </span>
                        <div>
                            <p class="font-semibold dark:text-white">
                                @lang('marketplace::app.developer.dashboard.quick-actions.view-extensions')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('marketplace::app.developer.dashboard.quick-actions.view-extensions-desc')
                            </p>
                        </div>
                    </a>

                    <!-- View Earnings -->
                    <a
                        href="{{ route('developer.marketplace.earnings.index') }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-blue-400 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-500"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                            <span class="icon-dollar-sign text-xl"></span>
                        </span>
                        <div>
                            <p class="font-semibold dark:text-white">
                                @lang('marketplace::app.developer.dashboard.quick-actions.view-earnings')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('marketplace::app.developer.dashboard.quick-actions.view-earnings-desc')
                            </p>
                        </div>
                    </a>

                    <!-- View Submissions -->
                    <a
                        href="{{ route('developer.marketplace.submissions.index') }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-blue-400 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-500"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                            <span class="icon-file-text text-xl"></span>
                        </span>
                        <div>
                            <p class="font-semibold dark:text-white">
                                @lang('marketplace::app.developer.dashboard.quick-actions.view-submissions')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('marketplace::app.developer.dashboard.quick-actions.view-submissions-desc')
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        {!! view_render_event('marketplace.developer.dashboard.content.left.after') !!}

        <!-- Right Section -->
        {!! view_render_event('marketplace.developer.dashboard.content.right.before') !!}

        <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
            <!-- Recent Activity -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.dashboard.recent-activity.title')
                </p>

                <div class="space-y-4">
                    <!-- Downloads Activity -->
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <span class="icon-download text-sm"></span>
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-medium dark:text-white">
                                @lang('marketplace::app.developer.dashboard.recent-activity.downloads')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ number_format($statistics['recent_downloads']) }} @lang('marketplace::app.developer.dashboard.recent-activity.last-30-days')
                            </p>
                        </div>
                    </div>

                    <!-- Revenue Activity -->
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                            <span class="icon-dollar-sign text-sm"></span>
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-medium dark:text-white">
                                @lang('marketplace::app.developer.dashboard.recent-activity.revenue')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                ${{ number_format($statistics['recent_revenue'], 2) }} @lang('marketplace::app.developer.dashboard.recent-activity.last-30-days')
                            </p>
                        </div>
                    </div>

                    @if ($statistics['pending_reviews'] > 0)
                        <!-- Pending Reviews -->
                        <div class="flex items-start gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                                <span class="icon-clock text-sm"></span>
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-medium dark:text-white">
                                    @lang('marketplace::app.developer.dashboard.recent-activity.pending')
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $statistics['pending_reviews'] }} @lang('marketplace::app.developer.dashboard.recent-activity.awaiting-review')
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($statistics['rejected_extensions'] > 0)
                        <!-- Rejected Extensions -->
                        <div class="flex items-start gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                <span class="icon-x-circle text-sm"></span>
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-medium dark:text-white">
                                    @lang('marketplace::app.developer.dashboard.recent-activity.rejected')
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $statistics['rejected_extensions'] }} @lang('marketplace::app.developer.dashboard.recent-activity.need-attention')
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Performance Summary -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.dashboard.performance.title')
                </p>

                <div class="space-y-3">
                    <!-- Average Rating -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.dashboard.performance.average-rating')
                        </span>
                        <div class="flex items-center gap-1">
                            <span class="icon-star text-yellow-500"></span>
                            <span class="font-semibold dark:text-white">
                                {{ number_format($statistics['average_rating'], 1) }}
                            </span>
                        </div>
                    </div>

                    <!-- Total Extensions -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.dashboard.performance.total-extensions')
                        </span>
                        <span class="font-semibold dark:text-white">
                            {{ $statistics['total_extensions'] }}
                        </span>
                    </div>

                    <!-- Active Extensions -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.dashboard.performance.active')
                        </span>
                        <span class="font-semibold text-green-600 dark:text-green-500">
                            {{ $statistics['active_extensions'] }}
                        </span>
                    </div>

                    <!-- Draft Extensions -->
                    @if ($statistics['draft_extensions'] > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('marketplace::app.developer.dashboard.performance.draft')
                            </span>
                            <span class="font-semibold text-gray-600 dark:text-gray-400">
                                {{ $statistics['draft_extensions'] }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Help & Resources -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.dashboard.help.title')
                </p>

                <div class="space-y-3">
                    <a
                        href="#"
                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <span class="icon-book"></span>
                        @lang('marketplace::app.developer.dashboard.help.documentation')
                    </a>

                    <a
                        href="#"
                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <span class="icon-help-circle"></span>
                        @lang('marketplace::app.developer.dashboard.help.support')
                    </a>

                    <a
                        href="#"
                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <span class="icon-code"></span>
                        @lang('marketplace::app.developer.dashboard.help.api-reference')
                    </a>
                </div>
            </div>
        </div>

        {!! view_render_event('marketplace.developer.dashboard.content.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.dashboard.content.after') !!}
</x-admin::layouts>
