<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.categories.index.title')
        </x-slot>

        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="support.categories.index" />
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.support.categories.index.title')
                </div>
            </div>

            <a href="{{ route('admin.support.categories.create') }}" class="primary-button">
                @lang('admin::app.support.categories.index.create-btn')
            </a>
        </div>

        <div class="mt-4">
            <x-admin::datagrid :src="route('admin.support.categories.index')" />
        </div>
</x-admin::layouts>