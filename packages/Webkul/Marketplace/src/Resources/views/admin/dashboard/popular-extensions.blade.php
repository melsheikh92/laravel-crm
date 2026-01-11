{!! view_render_event('marketplace.admin.dashboard.popular_extensions.before') !!}

@if($popularExtensions && $popularExtensions->count() > 0)
    <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <p class="text-base font-semibold dark:text-white">
                @lang('marketplace::app.admin.dashboard.popular-extensions.title')
            </p>

            <a
                href="{{ route('admin.marketplace.extensions.index') }}"
                class="text-sm font-medium text-blue-600 transition-all hover:underline dark:text-blue-500"
            >
                @lang('marketplace::app.admin.dashboard.view-all')
            </a>
        </div>

        <!-- Extensions List -->
        <div class="flex flex-col gap-2">
            @foreach($popularExtensions as $extension)
                <div class="flex items-center justify-between border-b border-gray-100 pb-2 last:border-0 dark:border-gray-800">
                    <div class="flex flex-1 flex-col gap-1">
                        <a
                            href="{{ route('admin.marketplace.extensions.show', $extension->id) }}"
                            class="text-sm font-medium text-gray-900 transition-all hover:text-blue-600 dark:text-white dark:hover:text-blue-500"
                        >
                            {{ $extension->name }}
                        </a>

                        <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <span>{{ $extension->author->name ?? 'Unknown' }}</span>
                            <span>•</span>
                            <span>{{ $extension->category->name ?? 'General' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                        <span class="icon-download text-sm"></span>
                        <span class="font-medium">{{ number_format($extension->downloads_count) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{!! view_render_event('marketplace.admin.dashboard.popular_extensions.after') !!}
