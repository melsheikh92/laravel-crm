@php
    $_title = trans('admin::app.support.sla.index.title') !== 'admin::app.support.sla.index.title' 
        ? trans('admin::app.support.sla.index.title') 
        : 'SLA Management';
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Feature Under Development
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                The SLA Management feature is currently being developed and will be available soon.
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500">
                This feature will allow you to configure Service Level Agreements and track compliance for support tickets.
            </p>
        </div>
    </div>
</x-admin::layouts>

