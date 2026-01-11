<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.extensions.edit.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.extensions.edit.form.before', ['extension' => $extension]) !!}

    <x-admin::form
        :action="route('developer.marketplace.extensions.update', $extension->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('marketplace.developer.extensions.edit.breadcrumbs.before', ['extension' => $extension]) !!}

                    <x-admin::breadcrumbs
                        name="marketplace.developer.extensions.edit"
                        :entity="$extension"
                    />

                    {!! view_render_event('marketplace.developer.extensions.edit.breadcrumbs.after', ['extension' => $extension]) !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.developer.extensions.edit.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('marketplace.developer.extensions.edit.save_button.before', ['extension' => $extension]) !!}

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('marketplace::app.developer.extensions.edit.save-btn')
                    </button>

                    {!! view_render_event('marketplace.developer.extensions.edit.save_button.after', ['extension' => $extension]) !!}
                </div>
            </div>

            @include('marketplace::developer.extensions.form', [
                'extension' => $extension,
                'categories' => $categories
            ])
        </div>
    </x-admin::form>

    {!! view_render_event('marketplace.developer.extensions.edit.form.after', ['extension' => $extension]) !!}
</x-admin::layouts>
