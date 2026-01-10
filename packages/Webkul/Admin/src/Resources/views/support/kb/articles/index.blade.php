<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.kb.index.title')
        </x-slot>

        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="support.kb.articles" />
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.support.kb.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <x-admin::datagrid.export :src="route('admin.support.kb.articles.index')" />
                <a href="{{ route('admin.support.kb.articles.create') }}" class="primary-button">
                    @lang('admin::app.support.kb.index.create-btn')
                </a>
            </div>
        </div>

        @if(isset($statistics))
            <div class="mt-4 flex gap-4">
                <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Articles</div>
                    <div class="text-2xl font-bold dark:text-white">{{ $statistics['total_articles'] ?? 0 }}</div>
                </div>
                <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Published</div>
                    <div class="text-2xl font-bold text-green-600">{{ $statistics['published_articles'] ?? 0 }}</div>
                </div>
                <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Views</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $statistics['total_views'] ?? 0 }}</div>
                </div>
                <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Avg Helpfulness</div>
                    <div class="text-2xl font-bold text-purple-600">{{ $statistics['average_helpfulness'] ?? 0 }}%</div>
                </div>
            </div>
        @endif

        <div class="mt-4">
            <x-admin::datagrid :src="route('admin.support.kb.articles.index')" />
        </div>
</x-admin::layouts>