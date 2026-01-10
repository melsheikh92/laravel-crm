<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.kb.categories.index.title')
        </x-slot>

        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="support.kb.categories.index" />
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.support.kb.categories.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.support.kb.categories.create') }}" class="primary-button">
                    @lang('admin::app.support.kb.categories.index.create-btn')
                </a>
            </div>
        </div>

        <div class="mt-4">
            <x-admin::datagrid :src="route('admin.support.kb.categories.index')" />
        </div>
</x-admin::layouts>