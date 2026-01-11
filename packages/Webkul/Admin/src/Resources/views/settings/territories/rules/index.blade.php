<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territories.rules.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.territories.rules.index.breadcrumbs.before') !!}

                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.territories.rules" />

                {!! view_render_event('admin.settings.territories.rules.index.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    <!-- Title -->
                    @lang('admin::app.settings.territories.rules.index.title')
                </div>

                <!-- Territory Context -->
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    @lang('admin::app.settings.territories.rules.index.territory'): <span class="font-semibold">{{ $territory->name }}</span>
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.settings.territories.rules.index.back_button.before') !!}

                    <!-- Back button -->
                    <a
                        href="{{ route('admin.settings.territories.index') }}"
                        class="secondary-button"
                    >
                        @lang('admin::app.settings.territories.rules.index.back-btn')
                    </a>

                    {!! view_render_event('admin.settings.territories.rules.index.back_button.after') !!}

                    {!! view_render_event('admin.settings.territories.rules.index.create_button.before') !!}

                    @if (bouncer()->hasPermission('settings.territories.rules.create'))
                        <!-- Create button for Rules -->
                        <a
                            href="{{ route('admin.settings.territories.rules.create', $territory->id) }}"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.territories.rules.index.create-btn')
                        </a>
                    @endif

                    {!! view_render_event('admin.settings.territories.rules.index.create_button.after') !!}
                </div>
            </div>
        </div>

        {!! view_render_event('admin.settings.territories.rules.index.datagrid.before') !!}

        <!-- DataGrid -->
        <x-admin::datagrid :src="route('admin.settings.territories.rules.index', $territory->id)">
            <!-- DataGrid Shimmer -->
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>

        {!! view_render_event('admin.settings.territories.rules.index.datagrid.after') !!}
    </div>
</x-admin::layouts>
