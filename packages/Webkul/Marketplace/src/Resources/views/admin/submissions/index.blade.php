<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.submissions.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.marketplace.submissions.index.breadcrumbs.before') !!}

                <x-admin::breadcrumbs name="marketplace.submissions" />

                {!! view_render_event('admin.marketplace.submissions.index.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    @lang('marketplace::app.admin.submissions.index.title')
                </div>
            </div>
        </div>

        {!! view_render_event('admin.marketplace.submissions.index.statistics.before', ['statistics' => $statistics]) !!}

        <!-- Statistics Cards -->
        @if(isset($statistics))
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <!-- Total Submissions -->
                <div class="box-shadow flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.submissions.statistics.total-submissions')
                    </div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">
                        {{ $statistics['total'] ?? 0 }}
                    </div>
                </div>

                <!-- Pending Review -->
                <div class="box-shadow flex flex-col gap-2 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <div class="text-sm text-yellow-700 dark:text-yellow-400">
                        @lang('marketplace::app.admin.submissions.statistics.pending-submissions')
                    </div>
                    <div class="text-2xl font-bold text-yellow-800 dark:text-yellow-300">
                        {{ $statistics['pending'] ?? 0 }}
                    </div>
                </div>

                <!-- Approved -->
                <div class="box-shadow flex flex-col gap-2 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <div class="text-sm text-green-700 dark:text-green-400">
                        @lang('marketplace::app.admin.submissions.statistics.approved-submissions')
                    </div>
                    <div class="text-2xl font-bold text-green-800 dark:text-green-300">
                        {{ $statistics['approved'] ?? 0 }}
                    </div>
                </div>

                <!-- Rejected -->
                <div class="box-shadow flex flex-col gap-2 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <div class="text-sm text-red-700 dark:text-red-400">
                        @lang('marketplace::app.admin.submissions.statistics.rejected-submissions')
                    </div>
                    <div class="text-2xl font-bold text-red-800 dark:text-red-300">
                        {{ $statistics['rejected'] ?? 0 }}
                    </div>
                </div>

                <!-- Average Review Time -->
                <div class="box-shadow flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.submissions.statistics.avg-review-time')
                    </div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">
                        {{ number_format($statistics['avg_review_time'] ?? 0, 1) }}
                        <span class="text-sm font-normal">@lang('marketplace::app.admin.submissions.statistics.hours')</span>
                    </div>
                </div>
            </div>
        @endif

        {!! view_render_event('admin.marketplace.submissions.index.statistics.after', ['statistics' => $statistics]) !!}

        {!! view_render_event('admin.marketplace.submissions.index.datagrid.before') !!}

        <x-admin::datagrid :src="route('admin.marketplace.submissions.index')">
            <!-- DataGrid Shimmer -->
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>

        {!! view_render_event('admin.marketplace.submissions.index.datagrid.after') !!}
    </div>
</x-admin::layouts>
