<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territory-assignments.reassign.title')
    </x-slot>

    {!! view_render_event('admin.settings.territories.assignments.reassign.form.before') !!}

    <x-admin::form
        :action="route('admin.settings.territories.assignments.store-reassignment')"
        method="POST"
    >
        <input type="hidden" name="assignable_type" value="{{ request('assignable_type') }}" />
        <input type="hidden" name="assignable_id" value="{{ request('assignable_id') }}" />

        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.territories.assignments.reassign.breadcrumbs.before') !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs name="settings.territories.assignments.reassign" />

                    {!! view_render_event('admin.settings.territories.assignments.reassign.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.territory-assignments.reassign.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.territories.assignments.reassign.save_button.before') !!}

                        <!-- Reassign button -->
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.territory-assignments.reassign.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.territories.assignments.reassign.save_button.after') !!}
                    </div>
                </div>
            </div>

            <!-- Body content -->
            <div class="flex gap-2.5 max-xl:flex-wrap">
                {!! view_render_event('admin.settings.territories.assignments.reassign.card.before') !!}

                <!-- Main content -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <!-- Entity Information -->
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.territory-assignments.reassign.entity-info')
                        </p>

                        <div class="mb-4 rounded-md bg-gray-50 p-4 dark:bg-gray-800">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        @lang('admin::app.settings.territory-assignments.reassign.entity-type')
                                    </p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                                        @if(str_contains($assignableType, 'Lead'))
                                            @lang('admin::app.settings.territory-assignments.index.lead')
                                        @elseif(str_contains($assignableType, 'Organization'))
                                            @lang('admin::app.settings.territory-assignments.index.organization')
                                        @elseif(str_contains($assignableType, 'Person'))
                                            @lang('admin::app.settings.territory-assignments.index.person')
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        @lang('admin::app.settings.territory-assignments.reassign.entity-name')
                                    </p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                                        @if(isset($entity->title))
                                            {{ $entity->title }}
                                        @elseif(isset($entity->name))
                                            {{ $entity->name }}
                                        @else
                                            @lang('admin::app.settings.territory-assignments.reassign.unknown')
                                        @endif
                                    </p>
                                </div>

                                @if($currentTerritory)
                                    <div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            @lang('admin::app.settings.territory-assignments.reassign.current-territory')
                                        </p>
                                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                                            {{ $currentTerritory->name }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {!! view_render_event('admin.settings.territories.assignments.reassign.form.territory_id.before') !!}

                        <!-- New Territory -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.territory-assignments.reassign.new-territory')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                id="territory_id"
                                name="territory_id"
                                rules="required"
                                value="{{ old('territory_id') }}"
                                :label="trans('admin::app.settings.territory-assignments.reassign.new-territory')"
                            >
                                <option value="">@lang('admin::app.settings.territory-assignments.reassign.select-territory')</option>
                                @foreach($territories as $territory)
                                    <option
                                        value="{{ $territory->id }}"
                                        @if($currentTerritory && $territory->id == $currentTerritory->id) disabled @endif
                                    >
                                        {{ $territory->name }}
                                        @if($currentTerritory && $territory->id == $currentTerritory->id)
                                            (@lang('admin::app.settings.territory-assignments.reassign.current'))
                                        @endif
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="territory_id" />
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.territories.assignments.reassign.form.territory_id.after') !!}

                        {!! view_render_event('admin.settings.territories.assignments.reassign.form.transfer_ownership.before') !!}

                        <!-- Transfer Ownership -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.territory-assignments.reassign.transfer-ownership')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                id="transfer_ownership"
                                name="transfer_ownership"
                                value="1"
                                :label="trans('admin::app.settings.territory-assignments.reassign.transfer-ownership')"
                                :checked="old('transfer_ownership', true)"
                            />

                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.territory-assignments.reassign.transfer-ownership-info')
                            </p>

                            <x-admin::form.control-group.error control-name="transfer_ownership" />
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.territories.assignments.reassign.form.transfer_ownership.after') !!}
                    </div>
                </div>

                {!! view_render_event('admin.settings.territories.assignments.reassign.card.after') !!}
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.settings.territories.assignments.reassign.form.after') !!}
</x-admin::layouts>
