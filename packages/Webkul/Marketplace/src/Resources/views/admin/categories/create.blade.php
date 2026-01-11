<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.categories.create.title')
    </x-slot>

    {!! view_render_event('admin.marketplace.categories.create.form.before') !!}

    <x-admin::form
        :action="route('admin.marketplace.categories.store')"
        enctype="multipart/form-data"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.marketplace.categories.create.breadcrumbs.before') !!}

                    <x-admin::breadcrumbs name="marketplace.categories.create" />

                    {!! view_render_event('admin.marketplace.categories.create.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.admin.categories.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.marketplace.categories.create.save_button.before') !!}

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('marketplace::app.admin.categories.create.save-btn')
                        </button>

                        {!! view_render_event('admin.marketplace.categories.create.save_button.after') !!}
                    </div>
                </div>
            </div>

            @include('marketplace::admin.categories.form', ['categories' => $categories])
        </div>
    </x-admin::form>

    {!! view_render_event('admin.marketplace.categories.create.form.after') !!}
</x-admin::layouts>
