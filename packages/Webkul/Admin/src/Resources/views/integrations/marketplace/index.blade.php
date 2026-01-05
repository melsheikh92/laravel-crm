@php
    $_title = trans('admin::app.integrations.marketplace.index.title') !== 'admin::app.integrations.marketplace.index.title' 
        ? trans('admin::app.integrations.marketplace.index.title') 
        : 'Integrations Marketplace';
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Feature Under Development
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                The Integrations Marketplace feature is currently being developed and will be available soon.
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500">
                This feature will allow you to browse and install integrations with third-party services like Stripe, Google Calendar, and more.
            </p>
        </div>
    </div>
</x-admin::layouts>

