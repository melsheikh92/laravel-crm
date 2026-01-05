@php
    $_title = trans('admin::app.support.knowledge-base.index.title') !== 'admin::app.support.knowledge-base.index.title' 
        ? trans('admin::app.support.knowledge-base.index.title') 
        : 'Knowledge Base';
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Feature Under Development
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                The Knowledge Base feature is currently being developed and will be available soon.
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500">
                This feature will allow you to create and manage knowledge base articles to help your customers find answers.
            </p>
        </div>
    </div>
</x-admin::layouts>

