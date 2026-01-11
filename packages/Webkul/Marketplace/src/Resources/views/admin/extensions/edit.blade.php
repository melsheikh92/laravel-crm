<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.extensions.edit.title')
        </x-slot>

        <x-admin::form :action="route('admin.marketplace.extensions.update', $extension->id)" method="PUT"
            enctype="multipart/form-data">
            <div class="flex justify-between items-center">
                <p class="text-xl text-gray-800 dark:text-white font-bold">
                    @lang('marketplace::app.admin.extensions.edit.title')
                </p>

                <div class="flex gap-x-2.5 items-center">
                    <a href="{{ route('admin.marketplace.extensions.index') }}"
                        class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.extensions.show.cancel')
                    </a>

                    <button type="submit" class="primary-button">
                        @lang('marketplace::app.admin.extensions.edit.save-btn')
                    </button>
                </div>
            </div>

            <div class="flex gap-4 mt-7">
                <!-- Use the form component, passing extension -->
                @include('marketplace::admin.extensions.form', ['extension' => $extension])
            </div>
        </x-admin::form>
</x-admin::layouts>