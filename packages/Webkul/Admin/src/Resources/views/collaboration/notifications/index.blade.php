@php
    $_title = trans('admin::app.collaboration.notifications.index.title') !== 'admin::app.collaboration.notifications.index.title' 
        ? trans('admin::app.collaboration.notifications.index.title') 
        : 'Notifications';
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

    <x-admin::datagrid src="{{ route('admin.collaboration.notifications.index') }}"></x-admin::datagrid>
</x-admin::layouts>

