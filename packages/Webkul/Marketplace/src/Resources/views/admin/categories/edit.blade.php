<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.categories.edit.title')
    </x-slot>

    {!! view_render_event('admin.marketplace.categories.edit.form.before', ['category' => $category]) !!}

    <x-admin::form
        :action="route('admin.marketplace.categories.update', $category->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.marketplace.categories.edit.breadcrumbs.before', ['category' => $category]) !!}

                    <x-admin::breadcrumbs
                        name="marketplace.categories.edit"
                        :entity="$category"
                    />

                    {!! view_render_event('admin.marketplace.categories.edit.breadcrumbs.after', ['category' => $category]) !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.admin.categories.edit.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.marketplace.categories.edit.save_button.before', ['category' => $category]) !!}

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('marketplace::app.admin.categories.edit.save-btn')
                        </button>

                        {!! view_render_event('admin.marketplace.categories.edit.save_button.after', ['category' => $category]) !!}
                    </div>
                </div>
            </div>

            @include('marketplace::admin.categories.form', [
                'category' => $category,
                'categories' => $categories
            ])
        </div>
    </x-admin::form>

    {!! view_render_event('admin.marketplace.categories.edit.form.after', ['category' => $category]) !!}
</x-admin::layouts>
