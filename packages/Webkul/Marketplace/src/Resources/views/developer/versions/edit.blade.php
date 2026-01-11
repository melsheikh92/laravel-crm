<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.versions.edit.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.versions.edit.form.before', ['extension' => $version->extension, 'version' => $version]) !!}

    <x-admin::form
        :action="route('developer.marketplace.versions.update', $version->id)"
        method="PUT"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('marketplace.developer.versions.edit.breadcrumbs.before', ['extension' => $version->extension, 'version' => $version]) !!}

                    <x-admin::breadcrumbs
                        name="marketplace.developer.versions.edit"
                        :entity="$version"
                    />

                    {!! view_render_event('marketplace.developer.versions.edit.breadcrumbs.after', ['extension' => $version->extension, 'version' => $version]) !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.developer.versions.edit.title')
                    </div>

                    @if($version->status === 'approved')
                        <div class="rounded-lg bg-orange-50 p-3 text-sm text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">
                            <span class="icon-warning mr-1"></span>
                            @lang('marketplace::app.developer.versions.edit.approved-warning')
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('marketplace.developer.versions.edit.save_button.before', ['extension' => $version->extension, 'version' => $version]) !!}

                    <a
                        href="{{ route('developer.marketplace.versions.index', $version->extension_id) }}"
                        class="secondary-button"
                    >
                        @lang('marketplace::app.developer.versions.edit.cancel-btn')
                    </a>

                    @if($version->status !== 'approved')
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('marketplace::app.developer.versions.edit.save-btn')
                        </button>
                    @endif

                    {!! view_render_event('marketplace.developer.versions.edit.save_button.after', ['extension' => $version->extension, 'version' => $version]) !!}
                </div>
            </div>

            @include('marketplace::developer.versions.form', [
                'extension' => $version->extension,
                'version' => $version
            ])
        </div>
    </x-admin::form>

    {!! view_render_event('marketplace.developer.versions.edit.form.after', ['extension' => $version->extension, 'version' => $version]) !!}
</x-admin::layouts>
