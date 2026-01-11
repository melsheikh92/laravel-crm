<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.extensions.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.marketplace.extensions.index.breadcrumbs.before') !!}

                <x-admin::breadcrumbs name="marketplace.extensions" />

                {!! view_render_event('admin.marketplace.extensions.index.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    @lang('marketplace::app.admin.extensions.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.marketplace.extensions.index.create_button.before') !!}

                    @if (bouncer()->hasPermission('marketplace.extensions.create'))
                        <a
                            href="{{ route('admin.marketplace.extensions.create') }}"
                            class="primary-button"
                        >
                            @lang('marketplace::app.admin.extensions.index.create-btn')
                        </a>
                    @endif

                    {!! view_render_event('admin.marketplace.extensions.index.create_button.after') !!}
                </div>
            </div>
        </div>

        {!! view_render_event('admin.marketplace.extensions.index.datagrid.before') !!}

        <x-admin::datagrid :src="route('admin.marketplace.extensions.index')">
            <!-- DataGrid Shimmer -->
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>

        {!! view_render_event('admin.marketplace.extensions.index.datagrid.after') !!}
    </div>
</x-admin::layouts>
