<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.tickets.index.title')
        </x-slot>

        <!-- Header -->
        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="support.tickets" />

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.support.tickets.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Export -->
                <x-admin::datagrid.export :src="route('admin.support.tickets.index')" />

                <!-- Create Ticket Button -->
                <a href="{{ route('admin.support.tickets.create') }}" class="primary-button">
                    @lang('admin::app.support.tickets.index.create-btn')
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        @if(isset($statistics))
            <div class="mt-4 flex gap-4">
                <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Tickets</div>
                    <div class="text-2xl font-bold dark:text-white">{{ $statistics['total'] ?? 0 }}</div>
                </div>
                <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Open</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $statistics['open'] ?? 0 }}</div>
                </div>
                <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">In Progress</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $statistics['in_progress'] ?? 0 }}</div>
                </div>
                <div class="flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">SLA Breached</div>
                    <div class="text-2xl font-bold text-red-600">{{ $statistics['breached'] ?? 0 }}</div>
                </div>
            </div>
        @endif

        <!-- DataGrid -->
        <div class="mt-4">
            <x-admin::datagrid :src="route('admin.support.tickets.index')" />
        </div>
</x-admin::layouts>