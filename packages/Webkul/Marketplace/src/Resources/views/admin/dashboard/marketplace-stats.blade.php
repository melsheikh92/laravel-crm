{!! view_render_event('marketplace.admin.dashboard.stats.before') !!}

@if(isset($marketplaceStats))
    <div class="grid grid-cols-3 gap-4 max-md:grid-cols-2 max-sm:grid-cols-1">
        <!-- Total Extensions -->
        <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.admin.dashboard.stats.total-extensions')
            </p>

            <div class="flex gap-2">
                <p class="text-xl font-bold dark:text-gray-300">
                    {{ $marketplaceStats['total_extensions'] }}
                </p>
            </div>

            <div class="text-xs text-gray-600 dark:text-gray-400">
                {{ $marketplaceStats['approved_extensions'] }} @lang('marketplace::app.admin.dashboard.stats.approved')
            </div>
        </div>

        <!-- Total Installations -->
        <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.admin.dashboard.stats.total-installations')
            </p>

            <div class="flex gap-2">
                <p class="text-xl font-bold dark:text-gray-300">
                    {{ $marketplaceStats['total_installations'] }}
                </p>
            </div>

            <div class="text-xs text-gray-600 dark:text-gray-400">
                {{ $marketplaceStats['active_installations'] }} @lang('marketplace::app.admin.dashboard.stats.active')
            </div>
        </div>

        <!-- Pending Submissions -->
        <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.admin.dashboard.stats.pending-submissions')
            </p>

            <div class="flex gap-2">
                <p class="text-xl font-bold dark:text-gray-300">
                    {{ $marketplaceStats['pending_submissions'] }}
                </p>
            </div>

            @if($marketplaceStats['pending_submissions'] > 0)
                <a
                    href="{{ route('admin.marketplace.submissions.index') }}"
                    class="text-xs font-medium text-blue-600 transition-all hover:underline dark:text-blue-500"
                >
                    @lang('marketplace::app.admin.dashboard.stats.review-submissions')
                </a>
            @else
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    @lang('marketplace::app.admin.dashboard.stats.all-reviewed')
                </div>
            @endif
        </div>
    </div>
@endif

{!! view_render_event('marketplace.admin.dashboard.stats.after') !!}
