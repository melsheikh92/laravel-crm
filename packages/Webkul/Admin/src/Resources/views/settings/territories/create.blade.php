<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territories.create.title')
    </x-slot>

    {!! view_render_event('admin.settings.territories.create.form.before') !!}

    <x-admin::form
        :action="route('admin.settings.territories.store')"
        method="POST"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.territories.create.breadcrumbs.before') !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs name="settings.territories.create" />

                    {!! view_render_event('admin.settings.territories.create.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.territories.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.territories.create.save_button.before') !!}

                        <!-- Create button for Territory -->
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.territories.create.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.territories.create.save_button.after') !!}
                    </div>
                </div>
            </div>

            <!-- Body content -->
            <div class="flex gap-2.5 max-xl:flex-wrap">
                {!! view_render_event('admin.settings.territories.create.left.before') !!}

                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.territories.create.geographic-boundaries')
                        </p>

                        {!! view_render_event('admin.settings.territories.create.form.boundaries.before') !!}

                        <!-- Geographic Boundaries -->
                        <v-territory-boundaries>
                            <div class="shimmer h-64 w-full rounded-md"></div>
                        </v-territory-boundaries>

                        {!! view_render_event('admin.settings.territories.create.form.boundaries.after') !!}
                    </div>
                </div>

                {!! view_render_event('admin.settings.territories.create.left.after') !!}

                {!! view_render_event('admin.settings.territories.create.right.before') !!}

                <!-- Right sub-component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <div class="flex items-center justify-between">
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.territories.create.general')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.settings.territories.create.form.name.before') !!}

                            <!-- Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.create.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="name"
                                    name="name"
                                    rules="required"
                                    value="{{ old('name') }}"
                                    :label="trans('admin::app.settings.territories.create.name')"
                                    :placeholder="trans('admin::app.settings.territories.create.name')"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.create.form.name.after') !!}

                            {!! view_render_event('admin.settings.territories.create.form.code.before') !!}

                            <!-- Code -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.create.code')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="code"
                                    name="code"
                                    rules="required"
                                    value="{{ old('code') }}"
                                    :label="trans('admin::app.settings.territories.create.code')"
                                    :placeholder="trans('admin::app.settings.territories.create.code')"
                                />

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.create.form.code.after') !!}

                            {!! view_render_event('admin.settings.territories.create.form.type.before') !!}

                            <!-- Type -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.create.type')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="type"
                                    name="type"
                                    rules="required"
                                    value="{{ old('type') }}"
                                    :label="trans('admin::app.settings.territories.create.type')"
                                >
                                    <option value="">@lang('admin::app.settings.territories.create.select-type')</option>
                                    <option value="geographic">@lang('admin::app.settings.territories.create.geographic')</option>
                                    <option value="account-based">@lang('admin::app.settings.territories.create.account-based')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="type" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.create.form.type.after') !!}

                            {!! view_render_event('admin.settings.territories.create.form.parent_id.before') !!}

                            <!-- Parent Territory -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.territories.create.parent-territory')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="parent_id"
                                    name="parent_id"
                                    value="{{ old('parent_id') }}"
                                    :label="trans('admin::app.settings.territories.create.parent-territory')"
                                >
                                    <option value="">@lang('admin::app.settings.territories.create.none')</option>
                                    @foreach ($territories as $territory)
                                        <option value="{{ $territory->id }}">{{ $territory->name }}</option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="parent_id" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.create.form.parent_id.after') !!}

                            {!! view_render_event('admin.settings.territories.create.form.user_id.before') !!}

                            <!-- Owner -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.create.owner')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="user_id"
                                    name="user_id"
                                    rules="required"
                                    value="{{ old('user_id') }}"
                                    :label="trans('admin::app.settings.territories.create.owner')"
                                >
                                    <option value="">@lang('admin::app.settings.territories.create.select-owner')</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="user_id" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.create.form.user_id.after') !!}

                            {!! view_render_event('admin.settings.territories.create.form.status.before') !!}

                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.create.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="status"
                                    name="status"
                                    rules="required"
                                    value="{{ old('status', 'active') }}"
                                    :label="trans('admin::app.settings.territories.create.status')"
                                >
                                    <option value="active">@lang('admin::app.settings.territories.create.active')</option>
                                    <option value="inactive">@lang('admin::app.settings.territories.create.inactive')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.create.form.status.after') !!}

                            {!! view_render_event('admin.settings.territories.create.form.description.before') !!}

                            <!-- Description -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.territories.create.description')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="description"
                                    name="description"
                                    :value="old('description')"
                                    :label="trans('admin::app.settings.territories.create.description')"
                                    :placeholder="trans('admin::app.settings.territories.create.description')"
                                />

                                <x-admin::form.control-group.error control-name="description" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.create.form.description.after') !!}
                        </x-slot>
                    </x-admin::accordion>
                </div>

                {!! view_render_event('admin.settings.territories.create.right.after') !!}
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.settings.territories.create.form.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-territory-boundaries-template"
        >
            <div>
                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label>
                        @lang('admin::app.settings.territories.create.boundaries-json')
                    </x-admin::form.control-group.label>

                    <p class="mb-2 text-xs text-gray-600 dark:text-gray-300">
                        @lang('admin::app.settings.territories.create.boundaries-info')
                    </p>

                    <textarea
                        ref="boundaries"
                        id="boundaries"
                        name="boundaries"
                        class="hidden"
                    >@{{ boundaries }}</textarea>

                    <x-admin::form.control-group.error control-name="boundaries" />
                </x-admin::form.control-group>
            </div>
        </script>

        <script type="module">
            app.component('v-territory-boundaries', {
                template: '#v-territory-boundaries-template',

                data() {
                    return {
                        boundaries: '{{ old('boundaries', '{}') }}',
                        codeMirrorInstance: null,
                    };
                },

                mounted() {
                    this.initializeEditor();

                    this.$emitter.on('change-theme', (theme) => this.updateEditorTheme());
                },

                methods: {
                    /**
                     * Initialize CodeMirror editor.
                     */
                    initializeEditor() {
                        this.$nextTick(() => {
                            this.codeMirrorInstance = CodeMirror.fromTextArea(this.$refs.boundaries, {
                                lineNumbers: true,
                                mode: 'application/json',
                                styleActiveLine: true,
                                lint: true,
                                theme: document.documentElement.classList.contains('dark') ? 'ayu-dark' : 'default',
                            });

                            this.codeMirrorInstance.on('changes', () => {
                                this.boundaries = this.codeMirrorInstance.getValue();
                            });
                        });
                    },

                    /**
                     * Update editor theme when app theme changes.
                     */
                    updateEditorTheme() {
                        if (this.codeMirrorInstance) {
                            const theme = document.documentElement.classList.contains('dark') ? 'ayu-dark' : 'default';
                            this.codeMirrorInstance.setOption('theme', theme);
                        }
                    },
                },
            });
        </script>

        <!-- Code mirror script CDN -->
        <script
            type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.30.0/codemirror.js"
        ></script>

        <script
            type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.30.0/mode/javascript/javascript.js"
        ></script>
    @endPushOnce

    @pushOnce('styles')
        <!-- Code mirror style cdn -->
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.4/codemirror.css"
        />

        <!-- Dark theme css -->
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.3/theme/ayu-dark.min.css"
        />
    @endPushOnce
</x-admin::layouts>
