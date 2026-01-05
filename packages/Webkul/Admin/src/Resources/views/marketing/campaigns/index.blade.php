<x-admin::layouts>
    <x-slot:title>
        {{ trans('admin::app.marketing.campaigns.index.title') !== 'admin::app.marketing.campaigns.index.title' ? trans('admin::app.marketing.campaigns.index.title') : 'Email Campaigns' }}
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <div class="flex gap-2.5 items-center">
            <p class="text-2xl dark:text-white">{{ trans('admin::app.marketing.campaigns.index.title') !== 'admin::app.marketing.campaigns.index.title' ? trans('admin::app.marketing.campaigns.index.title') : 'Email Campaigns' }}</p>
        </div>

        <div class="flex gap-2.5 items-center">
            <a href="{{ route('admin.marketing.campaigns.create') }}">
                <button class="primary-button">
                    {{ trans('admin::app.marketing.campaigns.index.create-btn') !== 'admin::app.marketing.campaigns.index.create-btn' ? trans('admin::app.marketing.campaigns.index.create-btn') : 'Create Campaign' }}
                </button>
            </a>
        </div>
    </div>

    <x-admin::datagrid src="{{ route('admin.marketing.campaigns.index') }}"></x-admin::datagrid>
</x-admin::layouts>

