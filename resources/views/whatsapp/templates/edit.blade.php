
<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.whatsapp-template.edit.title')
    </x-slot>

    {!! view_render_event('admin.settings.whatsapp_template.edit.form.before') !!}

    <x-admin::form
        :action="route('whatsapp.templates.update', $template->id)"
        method="PUT"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.whatsapp_template.edit.breadcrumbs.before') !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs
                        name="settings.whatsapp_templates.edit"
                        :entity="$template"
                    />

                    {!! view_render_event('admin.settings.whatsapp_template.edit.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.whatsapp-template.edit.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <!-- Save button -->
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.whatsapp_template.edit.save_button.before') !!}

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.whatsapp-template.edit.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.whatsapp_template.edit.save_button.after') !!}
                    </div>
                </div>
            </div>

            <div class="flex gap-2.5 max-xl:flex-wrap">
                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.whatsapp-template.edit.template-content')
                            </p>
                        </div>

                        {!! view_render_event('admin.settings.whatsapp_template.edit.header.before') !!}

                        <!-- Header -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.whatsapp-template.edit.header')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="header"
                                id="header"
                                :value="old('header') ?? $template->header"
                                :label="trans('admin::app.settings.whatsapp-template.edit.header')"
                                :placeholder="trans('admin::app.settings.whatsapp-template.edit.header-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="header"/>
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.whatsapp_template.edit.header.after') !!}

                        {!! view_render_event('admin.settings.whatsapp_template.edit.body.before') !!}

                        <!-- Body -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.whatsapp-template.edit.body')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="body"
                                name="body"
                                rows="6"
                                rules="required"
                                :value="old('body') ?? $template->body"
                                :label="trans('admin::app.settings.whatsapp-template.edit.body')"
                                :placeholder="trans('admin::app.settings.whatsapp-template.edit.body-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="body" />
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.whatsapp_template.edit.body.after') !!}

                        {!! view_render_event('admin.settings.whatsapp_template.edit.footer.before') !!}

                        <!-- Footer -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.whatsapp-template.edit.footer')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="footer"
                                id="footer"
                                :value="old('footer') ?? $template->footer"
                                :label="trans('admin::app.settings.whatsapp-template.edit.footer')"
                                :placeholder="trans('admin::app.settings.whatsapp-template.edit.footer-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="footer"/>
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.whatsapp_template.edit.footer.after') !!}
                    </div>
                </div>

                <!-- Right sub-component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    {!! view_render_event('admin.settings.whatsapp_template.edit.accordion.general.before') !!}

                    <!-- General Information -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <div class="flex items-center justify-between">
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.whatsapp-template.edit.general')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content>
                            <!-- Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.whatsapp-template.edit.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    id="name"
                                    :value="old('name') ?? $template->name"
                                    rules="required"
                                    :label="trans('admin::app.settings.whatsapp-template.edit.name')"
                                    :placeholder="trans('admin::app.settings.whatsapp-template.edit.name-placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <!-- Language -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.whatsapp-template.edit.language')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="language"
                                    id="language"
                                    rules="required"
                                    :value="old('language') ?? $template->language"
                                    :label="trans('admin::app.settings.whatsapp-template.edit.language')"
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
                                    @lang('admin::app.settings.whatsapp-template.edit.category')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="category"
                                    id="category"
                                    rules="required"
                                    :value="old('category') ?? $template->category"
                                    :label="trans('admin::app.settings.whatsapp-template.edit.category')"
                                >
                                    <option value="MARKETING">@lang('admin::app.settings.whatsapp-template.edit.category-marketing')</option>
                                    <option value="UTILITY">@lang('admin::app.settings.whatsapp-template.edit.category-utility')</option>
                                    <option value="AUTHENTICATION">@lang('admin::app.settings.whatsapp-template.edit.category-authentication')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="category" />
                            </x-admin::form.control-group>

                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.whatsapp-template.edit.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="status"
                                    id="status"
                                    rules="required"
                                    :value="old('status') ?? $template->status"
                                    :label="trans('admin::app.settings.whatsapp-template.edit.status')"
                                >
                                    <option value="APPROVED">@lang('admin::app.settings.whatsapp-template.edit.status-approved')</option>
                                    <option value="PENDING">@lang('admin::app.settings.whatsapp-template.edit.status-pending')</option>
                                    <option value="REJECTED">@lang('admin::app.settings.whatsapp-template.edit.status-rejected')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            <!-- Meta Template ID -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.whatsapp-template.edit.meta-template-id')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="meta_template_id"
                                    id="meta_template_id"
                                    :value="old('meta_template_id') ?? $template->meta_template_id"
                                    :label="trans('admin::app.settings.whatsapp-template.edit.meta-template-id')"
                                    :placeholder="trans('admin::app.settings.whatsapp-template.edit.meta-template-id-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="meta_template_id" />
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>

                    {!! view_render_event('admin.settings.whatsapp_template.edit.accordion.general.after') !!}
                </div>
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.settings.whatsapp_template.edit.form.after') !!}
</x-admin::layouts>
