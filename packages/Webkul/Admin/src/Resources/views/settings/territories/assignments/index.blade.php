<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territory-assignments.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.territories.assignments.index.breadcrumbs.before') !!}

                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.territories.assignments" />

                {!! view_render_event('admin.settings.territories.assignments.index.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    <!-- Title -->
                    @lang('admin::app.settings.territory-assignments.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.settings.territories.assignments.index.bulk_reassign_button.before') !!}

                    @if (bouncer()->hasPermission('settings.territories.assignments.edit'))
                        <!-- Bulk Reassign button -->
                        <button
                            type="button"
                            class="secondary-button"
                            @click="$refs.bulkReassignModal.open()"
                        >
                            @lang('admin::app.settings.territory-assignments.index.bulk-reassign-btn')
                        </button>
                    @endif

                    {!! view_render_event('admin.settings.territories.assignments.index.bulk_reassign_button.after') !!}

                    {!! view_render_event('admin.settings.territories.assignments.index.create_button.before') !!}

                    @if (bouncer()->hasPermission('settings.territories.assignments.create'))
                        <!-- Create button for Assignment -->
                        <a
                            href="{{ route('admin.settings.territories.assignments.create') }}"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.territory-assignments.index.create-btn')
                        </a>
                    @endif

                    {!! view_render_event('admin.settings.territories.assignments.index.create_button.after') !!}
                </div>
            </div>
        </div>

        {!! view_render_event('admin.settings.territories.assignments.index.datagrid.before') !!}

        <!-- DataGrid -->
        <x-admin::datagrid :src="route('admin.settings.territories.assignments.index')">
            <!-- DataGrid Shimmer -->
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>

        {!! view_render_event('admin.settings.territories.assignments.index.datagrid.after') !!}
    </div>

    <!-- Bulk Reassign Modal -->
    <x-admin::form
        v-slot="{ meta, errors, handleSubmit }"
        as="div"
    >
        <form @submit="handleSubmit($event, bulkReassign)">
            <x-admin::modal ref="bulkReassignModal">
                <x-slot:header>
                    @lang('admin::app.settings.territory-assignments.index.bulk-reassign-title')
                </x-slot>

                <x-slot:content>
                    <!-- Territory Selection -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.territory-assignments.index.territory')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            id="bulk_territory_id"
                            name="territory_id"
                            rules="required"
                            :label="trans('admin::app.settings.territory-assignments.index.territory')"
                            v-model="bulkReassignData.territory_id"
                        >
                            <option value="">@lang('admin::app.settings.territory-assignments.index.select-territory')</option>
                            @foreach($territories as $territory)
                                <option value="{{ $territory->id }}">{{ $territory->name }}</option>
                            @endforeach
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="territory_id" />
                    </x-admin::form.control-group>

                    <!-- Transfer Ownership -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.territory-assignments.index.transfer-ownership')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="switch"
                            id="bulk_transfer_ownership"
                            name="transfer_ownership"
                            value="1"
                            :label="trans('admin::app.settings.territory-assignments.index.transfer-ownership')"
                            v-model="bulkReassignData.transfer_ownership"
                            :checked="true"
                        />

                        <x-admin::form.control-group.error control-name="transfer_ownership" />
                    </x-admin::form.control-group>

                    <!-- Entity Selection -->
                    <div class="mt-4">
                        <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.territory-assignments.index.selected-entities')
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.territory-assignments.index.bulk-reassign-info')
                        </p>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('admin::app.settings.territory-assignments.index.bulk-reassign-submit')
                    </button>
                </x-slot:footer>
            </x-admin::modal>
        </form>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="module">
            app.component('x-admin-layouts', {
                data() {
                    return {
                        bulkReassignData: {
                            territory_id: '',
                            transfer_ownership: true,
                            entities: [],
                        },
                    };
                },

                methods: {
                    bulkReassign(params) {
                        // Get selected rows from DataGrid
                        const selectedRows = this.$refs.datagrid?.getSelectedRows();

                        if (!selectedRows || selectedRows.length === 0) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: '@lang('admin::app.settings.territory-assignments.index.no-entities-selected')',
                            });

                            this.$refs.bulkReassignModal.close();

                            return;
                        }

                        // Format entities for the request
                        this.bulkReassignData.entities = selectedRows.map(row => ({
                            type: this.getEntityType(row.assignable_type),
                            id: row.assignable_id,
                        }));

                        // Send request
                        this.$axios
                            .post('{{ route('admin.settings.territories.assignments.bulk-reassign') }}', this.bulkReassignData)
                            .then(response => {
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message,
                                });

                                this.$refs.bulkReassignModal.close();

                                // Reload DataGrid
                                window.location.reload();
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || '@lang('admin::app.settings.territory-assignments.index.bulk-reassign-failed')',
                                });
                            });
                    },

                    getEntityType(assignableType) {
                        if (assignableType.includes('Lead')) {
                            return 'lead';
                        } else if (assignableType.includes('Organization')) {
                            return 'organization';
                        } else if (assignableType.includes('Person')) {
                            return 'person';
                        }

                        return '';
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
