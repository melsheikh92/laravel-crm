{!! view_render_event('marketplace.admin.dashboard.recent_installations.before') !!}

@if($recentInstallations && $recentInstallations->count() > 0)
    <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <p class="text-base font-semibold dark:text-white">
                @lang('marketplace::app.admin.dashboard.recent-installations.title')
            </p>

            <a
                href="{{ route('admin.marketplace.extensions.index') }}"
                class="text-sm font-medium text-blue-600 transition-all hover:underline dark:text-blue-500"
            >
                @lang('marketplace::app.admin.dashboard.view-all')
            </a>
        </div>

        <!-- Installations List -->
        <div class="flex flex-col gap-2">
            @foreach($recentInstallations as $installation)
                <div class="flex items-center justify-between border-b border-gray-100 pb-2 last:border-0 dark:border-gray-800">
                    <div class="flex flex-1 flex-col gap-1">
                        <a
                            href="{{ route('admin.marketplace.extensions.show', $installation->extension_id) }}"
                            class="text-sm font-medium text-gray-900 transition-all hover:text-blue-600 dark:text-white dark:hover:text-blue-500"
                        >
                            {{ $installation->extension->name ?? 'Unknown Extension' }}
                        </a>

                        <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <span>{{ $installation->user->name ?? 'Unknown User' }}</span>
                            <span>•</span>
                            <span>{{ $installation->installed_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <span class="rounded px-2 py-1 text-xs font-medium
                            {{ $installation->status === 'active' ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300' : '' }}
                            {{ $installation->status === 'inactive' ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' : '' }}
                        ">
                            {{ ucfirst($installation->status) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{!! view_render_event('marketplace.admin.dashboard.recent_installations.after') !!}
