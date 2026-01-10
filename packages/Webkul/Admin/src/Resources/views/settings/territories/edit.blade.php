<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territories.edit.title')
    </x-slot>

    {!! view_render_event('admin.settings.territories.edit.form.before', ['territory' => $territory]) !!}

    <x-admin::form
        :action="route('admin.settings.territories.update', $territory->id)"
        method="PUT"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.territories.edit.breadcrumbs.before', ['territory' => $territory]) !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs
                        name="settings.territories.edit"
                        :entity="$territory"
                    />

                    {!! view_render_event('admin.settings.territories.edit.breadcrumbs.after', ['territory' => $territory]) !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.territories.edit.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.territories.edit.save_button.before', ['territory' => $territory]) !!}

                        <!-- Save button -->
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.territories.edit.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.territories.edit.save_button.after', ['territory' => $territory]) !!}
                    </div>
                </div>
            </div>

            <!-- Body content -->
            <div class="flex gap-2.5 max-xl:flex-wrap">
                {!! view_render_event('admin.settings.territories.edit.left.before', ['territory' => $territory]) !!}

                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.territories.edit.geographic-boundaries')
                        </p>

                        {!! view_render_event('admin.settings.territories.edit.form.boundaries.before', ['territory' => $territory]) !!}

                        <!-- Geographic Boundaries -->
                        <v-territory-boundaries>
                            <div class="shimmer h-64 w-full rounded-md"></div>
                        </v-territory-boundaries>

                        {!! view_render_event('admin.settings.territories.edit.form.boundaries.after', ['territory' => $territory]) !!}
                    </div>
                </div>

                {!! view_render_event('admin.settings.territories.edit.left.after', ['territory' => $territory]) !!}

                {!! view_render_event('admin.settings.territories.edit.right.before', ['territory' => $territory]) !!}

                <!-- Right sub-component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <div class="flex items-center justify-between">
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.territories.edit.general')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.settings.territories.edit.form.name.before', ['territory' => $territory]) !!}

                            <!-- Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.edit.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="name"
                                    name="name"
                                    rules="required"
                                    value="{{ old('name') ?: $territory->name }}"
                                    :label="trans('admin::app.settings.territories.edit.name')"
                                    :placeholder="trans('admin::app.settings.territories.edit.name')"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.edit.form.name.after', ['territory' => $territory]) !!}

                            {!! view_render_event('admin.settings.territories.edit.form.code.before', ['territory' => $territory]) !!}

                            <!-- Code -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.edit.code')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="code"
                                    name="code"
                                    rules="required"
                                    value="{{ old('code') ?: $territory->code }}"
                                    :label="trans('admin::app.settings.territories.edit.code')"
                                    :placeholder="trans('admin::app.settings.territories.edit.code')"
                                />

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.edit.form.code.after', ['territory' => $territory]) !!}

                            {!! view_render_event('admin.settings.territories.edit.form.type.before', ['territory' => $territory]) !!}

                            <!-- Type -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.edit.type')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="type"
                                    name="type"
                                    rules="required"
                                    value="{{ old('type') ?: $territory->type }}"
                                    :label="trans('admin::app.settings.territories.edit.type')"
                                >
                                    <option value="">@lang('admin::app.settings.territories.edit.select-type')</option>
                                    <option value="geographic">@lang('admin::app.settings.territories.edit.geographic')</option>
                                    <option value="account-based">@lang('admin::app.settings.territories.edit.account-based')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="type" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.edit.form.type.after', ['territory' => $territory]) !!}

                            {!! view_render_event('admin.settings.territories.edit.form.parent_id.before', ['territory' => $territory]) !!}

                            <!-- Parent Territory -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.territories.edit.parent-territory')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="parent_id"
                                    name="parent_id"
                                    value="{{ old('parent_id') ?: $territory->parent_id }}"
                                    :label="trans('admin::app.settings.territories.edit.parent-territory')"
                                >
                                    <option value="">@lang('admin::app.settings.territories.edit.none')</option>
                                    @foreach ($territories as $territoryItem)
                                        <option value="{{ $territoryItem->id }}">{{ $territoryItem->name }}</option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="parent_id" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.edit.form.parent_id.after', ['territory' => $territory]) !!}

                            {!! view_render_event('admin.settings.territories.edit.form.user_id.before', ['territory' => $territory]) !!}

                            <!-- Owner -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.edit.owner')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="user_id"
                                    name="user_id"
                                    rules="required"
                                    value="{{ old('user_id') ?: $territory->user_id }}"
                                    :label="trans('admin::app.settings.territories.edit.owner')"
                                >
                                    <option value="">@lang('admin::app.settings.territories.edit.select-owner')</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="user_id" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.edit.form.user_id.after', ['territory' => $territory]) !!}

                            {!! view_render_event('admin.settings.territories.edit.form.status.before', ['territory' => $territory]) !!}

                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.territories.edit.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="status"
                                    name="status"
                                    rules="required"
                                    value="{{ old('status') ?: $territory->status }}"
                                    :label="trans('admin::app.settings.territories.edit.status')"
                                >
                                    <option value="active">@lang('admin::app.settings.territories.edit.active')</option>
                                    <option value="inactive">@lang('admin::app.settings.territories.edit.inactive')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.edit.form.status.after', ['territory' => $territory]) !!}

                            {!! view_render_event('admin.settings.territories.edit.form.description.before', ['territory' => $territory]) !!}

                            <!-- Description -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.territories.edit.description')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="description"
                                    name="description"
                                    :value="old('description') ?: $territory->description"
                                    :label="trans('admin::app.settings.territories.edit.description')"
                                    :placeholder="trans('admin::app.settings.territories.edit.description')"
                                />

                                <x-admin::form.control-group.error control-name="description" />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.settings.territories.edit.form.description.after', ['territory' => $territory]) !!}
                        </x-slot>
                    </x-admin::accordion>
                </div>

                {!! view_render_event('admin.settings.territories.edit.right.after', ['territory' => $territory]) !!}
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.settings.territories.edit.form.after', ['territory' => $territory]) !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-territory-boundaries-template"
        >
            <div>
                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label>
                        @lang('admin::app.settings.territories.edit.boundaries-json')
                    </x-admin::form.control-group.label>

                    <p class="mb-2 text-xs text-gray-600 dark:text-gray-300">
                        @lang('admin::app.settings.territories.edit.boundaries-info')
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
                        boundaries: @json(old('boundaries') ?: (is_array($territory->boundaries) ? json_encode($territory->boundaries) : ($territory->boundaries ?: '{}'))),
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
