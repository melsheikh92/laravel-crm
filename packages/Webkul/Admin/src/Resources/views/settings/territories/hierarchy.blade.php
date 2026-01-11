<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territories.hierarchy.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.territories.hierarchy.breadcrumbs.before') !!}

                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.territories" />

                {!! view_render_event('admin.settings.territories.hierarchy.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    <!-- Title -->
                    @lang('admin::app.settings.territories.hierarchy.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.settings.territories.hierarchy.back_button.before') !!}

                    <!-- Back to List button -->
                    <a
                        href="{{ route('admin.settings.territories.index') }}"
                        class="secondary-button"
                    >
                        @lang('admin::app.settings.territories.hierarchy.back-btn')
                    </a>

                    {!! view_render_event('admin.settings.territories.hierarchy.back_button.after') !!}

                    {!! view_render_event('admin.settings.territories.hierarchy.create_button.before') !!}

                    @if (bouncer()->hasPermission('settings.territories.create'))
                        <!-- Create button Territories -->
                        <a
                            href="{{ route('admin.settings.territories.create') }}"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.territories.hierarchy.create-btn')
                        </a>
                    @endif

                    {!! view_render_event('admin.settings.territories.hierarchy.create_button.after') !!}
                </div>
            </div>
        </div>

        {!! view_render_event('admin.settings.territories.hierarchy.tree.before') !!}

        <!-- Territory Hierarchy Tree -->
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            @if ($territories->isEmpty())
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="mb-4">
                        <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-gray-600 dark:text-gray-300">
                        @lang('admin::app.settings.territories.hierarchy.empty-title')
                    </p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        @lang('admin::app.settings.territories.hierarchy.empty-info')
                    </p>
                    @if (bouncer()->hasPermission('settings.territories.create'))
                        <a
                            href="{{ route('admin.settings.territories.create') }}"
                            class="primary-button mt-4"
                        >
                            @lang('admin::app.settings.territories.hierarchy.create-first-btn')
                        </a>
                    @endif
                </div>
            @else
                <!-- Territory Tree -->
                <div class="p-6">
                    <div class="space-y-2">
                        @foreach ($territories as $territory)
                            @include('admin::settings.territories.partials.tree-node', ['territory' => $territory, 'level' => 0])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {!! view_render_event('admin.settings.territories.hierarchy.tree.after') !!}
    </div>
</x-admin::layouts>
