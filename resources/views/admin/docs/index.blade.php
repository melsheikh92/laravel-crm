<x-admin::layouts>
    <x-slot:title>
        Documentation
    </x-slot>

    <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        <div class="flex flex-col gap-2">
            <x-admin::breadcrumbs name="admin.docs" />
            <div class="text-xl font-bold dark:text-white">
                Documentation
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export :src="route('admin.docs.index')" />
            <a href="{{ route('admin.docs.create') }}" class="primary-button">
                Create Article
            </a>
        </div>
    </div>

    @if(isset($stats))
        <div class="mt-4 flex gap-4">
            <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Articles</div>
                <div class="text-2xl font-bold dark:text-white">{{ $stats['total'] ?? 0 }}</div>
            </div>
            <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400">Published</div>
                <div class="text-2xl font-bold text-green-600">{{ $stats['published'] ?? 0 }}</div>
            </div>
            <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400">Draft</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['draft'] ?? 0 }}</div>
            </div>
            <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400">Public</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['public'] ?? 0 }}</div>
            </div>
        </div>
    @endif

    <div class="mt-4">
        <x-admin::datagrid :src="route('admin.docs.index')" />
    </div>
</x-admin::layouts>
