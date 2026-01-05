
<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.whatsapp-template.create.title')
    </x-slot>

    {!! view_render_event('admin.settings.whatsapp_template.create.form.before') !!}

    <x-admin::form
        :action="route('whatsapp.templates.store')"
        method="POST"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.whatsapp_template.create.breadcrumbs.before') !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs name="settings.whatsapp_templates.create" />

                    {!! view_render_event('admin.settings.whatsapp_template.create.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.whatsapp-template.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <!-- Create button -->
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.whatsapp_template.create.save_button.before') !!}

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.whatsapp-template.create.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.whatsapp_template.create.save_button.after') !!}
                    </div>
                </div>
            </div>

            <div class="flex gap-2.5 max-xl:flex-wrap">
                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.whatsapp-template.create.template-content')
                            </p>
                        </div>

                        {!! view_render_event('admin.settings.whatsapp_template.create.header.before') !!}

                        <!-- Header -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.whatsapp-template.create.header')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="header"
                                id="header"
                                :value="old('header')"
                                :label="trans('admin::app.settings.whatsapp-template.create.header')"
                                :placeholder="trans('admin::app.settings.whatsapp-template.create.header-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="header"/>
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.whatsapp_template.create.header.after') !!}

                        {!! view_render_event('admin.settings.whatsapp_template.create.body.before') !!}

                        <!-- Body -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.whatsapp-template.create.body')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="body"
                                name="body"
                                rows="6"
                                rules="required"
                                :value="old('body')"
                                :label="trans('admin::app.settings.whatsapp-template.create.body')"
                                :placeholder="trans('admin::app.settings.whatsapp-template.create.body-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="body" />
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.whatsapp_template.create.body.after') !!}

                        {!! view_render_event('admin.settings.whatsapp_template.create.footer.before') !!}

                        <!-- Footer -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.whatsapp-template.create.footer')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="footer"
                                id="footer"
                                :value="old('footer')"
                                :label="trans('admin::app.settings.whatsapp-template.create.footer')"
                                :placeholder="trans('admin::app.settings.whatsapp-template.create.footer-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="footer"/>
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.whatsapp_template.create.footer.after') !!}
                    </div>
                </div>

                <!-- Right sub-component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    {!! view_render_event('admin.settings.whatsapp_template.create.accordion.general.before') !!}

                    <!-- General Information -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <div class="flex items-center justify-between">
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.whatsapp-template.create.general')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content>
                            <!-- Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.whatsapp-template.create.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    id="name"
                                    :value="old('name')"
                                    rules="required"
                                    :label="trans('admin::app.settings.whatsapp-template.create.name')"
                                    :placeholder="trans('admin::app.settings.whatsapp-template.create.name-placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <!-- Language -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.whatsapp-template.create.language')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="language"
                                    id="language"
                                    rules="required"
                                    :value="old('language', 'en')"
                                    :label="trans('admin::app.settings.whatsapp-template.create.language')"
                                >
                                    <option value="en">English</option>
                                    <option value="es">Spanish</option>
                                    <option value="pt">Portuguese</option>
                                    <option value="ar">Arabic</option>
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                    <option value="it">Italian</option>
                                    <option value="zh">Chinese</option>
                                    <option value="ja">Japanese</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="language" />
                            </x-admin::form.control-group>

                            <!-- Category -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.whatsapp-template.create.category')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="category"
                                    id="category"
                                    rules="required"
                                    :value="old('category', 'MARKETING')"
                                    :label="trans('admin::app.settings.whatsapp-template.create.category')"
                                >
                                    <option value="MARKETING">@lang('admin::app.settings.whatsapp-template.create.category-marketing')</option>
                                    <option value="UTILITY">@lang('admin::app.settings.whatsapp-template.create.category-utility')</option>
                                    <option value="AUTHENTICATION">@lang('admin::app.settings.whatsapp-template.create.category-authentication')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="category" />
                            </x-admin::form.control-group>

                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.whatsapp-template.create.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="status"
                                    id="status"
                                    rules="required"
                                    :value="old('status', 'PENDING')"
                                    :label="trans('admin::app.settings.whatsapp-template.create.status')"
                                >
                                    <option value="APPROVED">@lang('admin::app.settings.whatsapp-template.create.status-approved')</option>
                                    <option value="PENDING">@lang('admin::app.settings.whatsapp-template.create.status-pending')</option>
                                    <option value="REJECTED">@lang('admin::app.settings.whatsapp-template.create.status-rejected')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            <!-- Meta Template ID -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.whatsapp-template.create.meta-template-id')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="meta_template_id"
                                    id="meta_template_id"
                                    :value="old('meta_template_id')"
                                    :label="trans('admin::app.settings.whatsapp-template.create.meta-template-id')"
                                    :placeholder="trans('admin::app.settings.whatsapp-template.create.meta-template-id-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="meta_template_id" />
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>

                    {!! view_render_event('admin.settings.whatsapp_template.create.accordion.general.after') !!}
                </div>
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.settings.whatsapp_template.create.form.after') !!}
</x-admin::layouts>
