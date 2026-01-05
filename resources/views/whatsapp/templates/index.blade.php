<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.whatsapp-template.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.whatsapp_template.index.breadcrumbs.before') !!}

                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.whatsapp_templates" />

                {!! view_render_event('admin.settings.whatsapp_template.index.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.whatsapp-template.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Create button for WhatsApp template -->
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.settings.whatsapp_template.index.create_button.before') !!}

                    @if (bouncer()->hasPermission('settings.automation.whatsapp_templates.create'))
                        <a
                            href="{{ route('whatsapp.templates.create') }}"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.whatsapp-template.index.create-btn')
                        </a>
                    @endif

                    {!! view_render_event('admin.settings.whatsapp_template.index.create_button.after') !!}
                </div>
            </div>
        </div>

        {!! view_render_event('admin.settings.whatsapp_template.index.datagrid.before') !!}

        <!-- DataGrid -->
        <x-admin::datagrid :src="route('whatsapp.templates.index')">
            <!-- DataGrid Shimmer -->
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>

        {!! view_render_event('admin.settings.whatsapp_template.index.datagrid.after') !!}
    </div>
</x-admin::layouts>
