<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.revenue.transactions.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.marketplace.revenue.transactions.breadcrumbs.before') !!}

                <x-admin::breadcrumbs name="marketplace.revenue.transactions" />

                {!! view_render_event('admin.marketplace.revenue.transactions.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    @lang('marketplace::app.admin.revenue.transactions.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                {!! view_render_event('admin.marketplace.revenue.transactions.actions.before') !!}

                <a
                    href="{{ route('admin.marketplace.revenue.index') }}"
                    class="secondary-button"
                >
                    <span class="icon-arrow-left text-2xl"></span>
                    @lang('marketplace::app.admin.revenue.index.title')
                </a>

                {!! view_render_event('admin.marketplace.revenue.transactions.actions.after') !!}
            </div>
        </div>

        {!! view_render_event('admin.marketplace.revenue.transactions.datagrid.before') !!}

        <x-admin::datagrid :src="route('admin.marketplace.revenue.transactions')">
            <!-- DataGrid Shimmer -->
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>

        {!! view_render_event('admin.marketplace.revenue.transactions.datagrid.after') !!}
    </div>
</x-admin::layouts>
