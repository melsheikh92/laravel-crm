<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territory-assignments.create.title')
    </x-slot>

    {!! view_render_event('admin.settings.territories.assignments.create.form.before') !!}

    <x-admin::form
        :action="route('admin.settings.territories.assignments.store')"
        method="POST"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.territories.assignments.create.breadcrumbs.before') !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs name="settings.territories.assignments.create" />

                    {!! view_render_event('admin.settings.territories.assignments.create.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.territory-assignments.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.territories.assignments.create.save_button.before') !!}

                        <!-- Create button for Assignment -->
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.territory-assignments.create.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.territories.assignments.create.save_button.after') !!}
                    </div>
                </div>
            </div>

            <!-- Body content -->
            <div class="flex gap-2.5 max-xl:flex-wrap">
                {!! view_render_event('admin.settings.territories.assignments.create.card.before') !!}

                <!-- Main content -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.territory-assignments.create.assignment-details')
                        </p>

                        {!! view_render_event('admin.settings.territories.assignments.create.form.territory_id.before') !!}

                        <!-- Territory -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.territory-assignments.create.territory')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                id="territory_id"
                                name="territory_id"
                                rules="required"
                                value="{{ old('territory_id') }}"
                                :label="trans('admin::app.settings.territory-assignments.create.territory')"
                            >
                                <option value="">@lang('admin::app.settings.territory-assignments.create.select-territory')</option>
                                @foreach($territories as $territory)
                                    <option value="{{ $territory->id }}">{{ $territory->name }}</option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="territory_id" />
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.territories.assignments.create.form.territory_id.after') !!}

                        {!! view_render_event('admin.settings.territories.assignments.create.form.assignable_type.before') !!}

                        <!-- Entity Type -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.territory-assignments.create.entity-type')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                id="assignable_type"
                                name="assignable_type"
                                rules="required"
                                value="{{ old('assignable_type') }}"
                                :label="trans('admin::app.settings.territory-assignments.create.entity-type')"
                            >
                                <option value="">@lang('admin::app.settings.territory-assignments.create.select-entity-type')</option>
                                @foreach($assignableTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="assignable_type" />
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.territories.assignments.create.form.assignable_type.after') !!}

                        {!! view_render_event('admin.settings.territories.assignments.create.form.assignable_id.before') !!}

                        <!-- Entity ID -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.territory-assignments.create.entity-id')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="assignable_id"
                                name="assignable_id"
                                rules="required|numeric|min:1"
                                value="{{ old('assignable_id') }}"
                                :label="trans('admin::app.settings.territory-assignments.create.entity-id')"
                                :placeholder="trans('admin::app.settings.territory-assignments.create.entity-id-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="assignable_id" />
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.territories.assignments.create.form.assignable_id.after') !!}

                        {!! view_render_event('admin.settings.territories.assignments.create.form.transfer_ownership.before') !!}

                        <!-- Transfer Ownership -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.territory-assignments.create.transfer-ownership')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                id="transfer_ownership"
                                name="transfer_ownership"
                                value="1"
                                :label="trans('admin::app.settings.territory-assignments.create.transfer-ownership')"
                                :checked="old('transfer_ownership', true)"
                            />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.territory-assignments.create.transfer-ownership-info')
                            </p>

                            <x-admin::form.control-group.error control-name="transfer_ownership" />
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.territories.assignments.create.form.transfer_ownership.after') !!}
                    </div>
                </div>

                {!! view_render_event('admin.settings.territories.assignments.create.card.after') !!}
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.settings.territories.assignments.create.form.after') !!}
</x-admin::layouts>
