@php
    $_title = trans('admin::app.support.tickets.index.title') !== 'admin::app.support.tickets.index.title' 
        ? trans('admin::app.support.tickets.index.title') 
        : 'Support Tickets';
@endphp

<x-admin::layouts>
    <x-slot:title>
        {{ $_title }}
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap mb-6">
        <div class="flex gap-2.5 items-center">
            <p class="text-2xl dark:text-white">{{ $_title }}</p>
        </div>
    </div>

    <div class="flex items-center justify-center min-h-[400px]">
        <div class="text-center max-w-md">
            <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Feature Under Development
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                The Support Tickets feature is currently being developed and will be available soon.
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500">
                This feature will include ticket management, SLA tracking, and customer support workflows.
            </p>
        </div>
    </div>
</x-admin::layouts>

