<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.versions.create.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.versions.create.form.before', ['extension' => $extension]) !!}

    <x-admin::form
        :action="route('developer.marketplace.versions.store', $extension->id)"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('marketplace.developer.versions.create.breadcrumbs.before', ['extension' => $extension]) !!}

                    <x-admin::breadcrumbs name="marketplace.developer.versions.create" />

                    {!! view_render_event('marketplace.developer.versions.create.breadcrumbs.after', ['extension' => $extension]) !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.developer.versions.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('marketplace.developer.versions.create.save_button.before', ['extension' => $extension]) !!}

                    <a
                        href="{{ route('developer.marketplace.versions.index', $extension->id) }}"
                        class="secondary-button"
                    >
                        @lang('marketplace::app.developer.versions.create.cancel-btn')
                    </a>

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('marketplace::app.developer.versions.create.save-btn')
                    </button>

                    {!! view_render_event('marketplace.developer.versions.create.save_button.after', ['extension' => $extension]) !!}
                </div>
            </div>

            @include('marketplace::developer.versions.form', ['extension' => $extension])
        </div>
    </x-admin::form>

    {!! view_render_event('marketplace.developer.versions.create.form.after', ['extension' => $extension]) !!}
</x-admin::layouts>
