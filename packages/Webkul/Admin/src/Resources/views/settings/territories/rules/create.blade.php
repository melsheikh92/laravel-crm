<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territories.rules.create.title')
    </x-slot>

    {!! view_render_event('admin.settings.territories.rules.create.form.before') !!}

    <x-admin::form
        :action="route('admin.settings.territories.rules.store', $territory->id)"
        method="POST"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.territories.rules.create.breadcrumbs.before') !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs name="settings.territories.rules.create" />

                    {!! view_render_event('admin.settings.territories.rules.create.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.territories.rules.create.title')
                    </div>

                    <!-- Territory Context -->
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('admin::app.settings.territories.rules.create.territory'): <span class="font-semibold">{{ $territory->name }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.territories.rules.create.save_button.before') !!}

                        <!-- Create button for Rule -->
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.territories.rules.create.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.territories.rules.create.save_button.after') !!}
                    </div>
                </div>
            </div>

            <!-- Body content -->
            <div class="flex gap-2.5 max-xl:flex-wrap">
                {!! view_render_event('admin.settings.territories.rules.create.left.before') !!}

                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.territories.rules.create.rule-value')
                        </p>

                        {!! view_render_event('admin.settings.territories.rules.create.form.value.before') !!}

                        <!-- Rule Value (JSON Editor) -->
                        <v-rule-value>
                            <div class="shimmer h-64 w-full rounded-md"></div>
                        </v-rule-value>

                        {!! view_render_event('admin.settings.territories.rules.create.form.value.after') !!}
                    </div>
                </div>

                {!! view_render_event('admin.settings.territories.rules.create.left.after') !!}

                {!! view_render_event('admin.settings.territories.rules.create.right.before') !!}

                <!-- Right sub-component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <div class="flex items-center justify-between">
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.territories.rules.create.general')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.settings.territories.rules.create.form.rule_type.before') !!}

                            <!-- Rule Type -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.rules.create.rule-type')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="rule_type"
                                    name="rule_type"
                                    rules="required"
                                    value="{{ old('rule_type') }}"
                                    :label="trans('admin::app.settings.territories.rules.create.rule-type')"
                                >
                                    <option value="">@lang('admin::app.settings.territories.rules.create.select-rule-type')</option>
                                    <option value="geographic">@lang('admin::app.settings.territories.rules.create.geographic')</option>
                                    <option value="industry">@lang('admin::app.settings.territories.rules.create.industry')</option>
                                    <option value="account_size">@lang('admin::app.settings.territories.rules.create.account-size')</option>
                                    <option value="custom">@lang('admin::app.settings.territories.rules.create.custom')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="rule_type" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.rules.create.form.rule_type.after') !!}

                            {!! view_render_event('admin.settings.territories.rules.create.form.field_name.before') !!}

                            <!-- Field Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.rules.create.field-name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="field_name"
                                    name="field_name"
                                    rules="required"
                                    value="{{ old('field_name') }}"
                                    :label="trans('admin::app.settings.territories.rules.create.field-name')"
                                    :placeholder="trans('admin::app.settings.territories.rules.create.field-name-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="field_name" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.rules.create.form.field_name.after') !!}

                            {!! view_render_event('admin.settings.territories.rules.create.form.operator.before') !!}

                            <!-- Operator -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.rules.create.operator')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="operator"
                                    name="operator"
                                    rules="required"
                                    value="{{ old('operator') }}"
                                    :label="trans('admin::app.settings.territories.rules.create.operator')"
                                >
                                    <option value="">@lang('admin::app.settings.territories.rules.create.select-operator')</option>
                                    <option value="=">=</option>
                                    <option value="!=">!=</option>
                                    <option value=">">></option>
                                    <option value=">=">=</option>
                                    <option value="<"><</option>
                                    <option value="<="><=</option>
                                    <option value="in">in</option>
                                    <option value="not_in">not in</option>
                                    <option value="contains">contains</option>
                                    <option value="not_contains">not contains</option>
                                    <option value="starts_with">starts with</option>
                                    <option value="ends_with">ends with</option>
                                    <option value="is_null">is null</option>
                                    <option value="is_not_null">is not null</option>
                                    <option value="between">between</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="operator" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.rules.create.form.operator.after') !!}

                            {!! view_render_event('admin.settings.territories.rules.create.form.priority.before') !!}

                            <!-- Priority -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.territories.rules.create.priority')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="priority"
                                    name="priority"
                                    value="{{ old('priority', '0') }}"
                                    :label="trans('admin::app.settings.territories.rules.create.priority')"
                                    :placeholder="trans('admin::app.settings.territories.rules.create.priority-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="priority" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.rules.create.form.priority.after') !!}

                            {!! view_render_event('admin.settings.territories.rules.create.form.is_active.before') !!}

                            <!-- Is Active -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.territories.rules.create.is-active')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="is_active"
                                    name="is_active"
                                    value="1"
                                    :label="trans('admin::app.settings.territories.rules.create.is-active')"
                                    :checked="old('is_active', true)"
                                />

                                <x-admin::form.control-group.error control-name="is_active" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.rules.create.form.is_active.after') !!}
                        </x-slot>
                    </x-admin::accordion>
                </div>

                {!! view_render_event('admin.settings.territories.rules.create.right.after') !!}
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.settings.territories.rules.create.form.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-rule-value-template"
        >
            <div>
                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label>
                        @lang('admin::app.settings.territories.rules.create.value-json')
                    </x-admin::form.control-group.label>

                    <p class="mb-2 text-xs text-gray-600 dark:text-gray-300">
                        @lang('admin::app.settings.territories.rules.create.value-info')
                    </p>

                    <textarea
                        ref="value"
                        id="value"
                        name="value"
                        class="hidden"
                    >@{{ value }}</textarea>

                    <x-admin::form.control-group.error control-name="value" />
                </x-admin::form.control-group>
            </div>
        </script>

        <script type="module">
            app.component('v-rule-value', {
                template: '#v-rule-value-template',

                data() {
                    return {
                        value: '{{ old('value', '[]') }}',
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
                            this.codeMirrorInstance = CodeMirror.fromTextArea(this.$refs.value, {
                                lineNumbers: true,
                                mode: 'application/json',
                                styleActiveLine: true,
                                lint: true,
                                theme: document.documentElement.classList.contains('dark') ? 'ayu-dark' : 'default',
                            });

                            this.codeMirrorInstance.on('changes', () => {
                                this.value = this.codeMirrorInstance.getValue();
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
