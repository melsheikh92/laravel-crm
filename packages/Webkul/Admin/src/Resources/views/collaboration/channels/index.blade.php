@php
    $_title = trans('admin::app.collaboration.channels.index.title') !== 'admin::app.collaboration.channels.index.title' 
        ? trans('admin::app.collaboration.channels.index.title') 
        : 'Channels';
@endphp

<x-admin::layouts>
    <x-slot:title>
        {{ $_title }}
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap mb-6">
        <div class="flex gap-2.5 items-center">
            <p class="text-2xl dark:text-white">{{ $_title }}</p>
        </div>

        <div class="flex gap-2.5 items-center">
            <a href="{{ route('admin.collaboration.channels.create') }}">
                <button class="primary-button">
                    {{ trans('admin::app.collaboration.channels.index.create-btn') !== 'admin::app.collaboration.channels.index.create-btn' ? trans('admin::app.collaboration.channels.index.create-btn') : 'Create Channel' }}
                </button>
            </a>
        </div>
    </div>

    <x-admin::datagrid src="{{ route('admin.collaboration.channels.index') }}"></x-admin::datagrid>
</x-admin::layouts>

