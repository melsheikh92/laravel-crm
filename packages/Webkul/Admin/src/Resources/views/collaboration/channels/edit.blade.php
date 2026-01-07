@php
    $_title = trans('admin::app.collaboration.channels.edit.title') !== 'admin::app.collaboration.channels.edit.title'
        ? trans('admin::app.collaboration.channels.edit.title')
        : 'Edit Channel';

    $_name = trans('admin::app.collaboration.channels.edit.name') !== 'admin::app.collaboration.channels.edit.name'
        ? trans('admin::app.collaboration.channels.edit.name')
        : 'Channel Name';

    $_description = trans('admin::app.collaboration.channels.edit.description') !== 'admin::app.collaboration.channels.edit.description'
        ? trans('admin::app.collaboration.channels.edit.description')
        : 'Description';

    $_type = trans('admin::app.collaboration.channels.edit.type') !== 'admin::app.collaboration.channels.edit.type'
        ? trans('admin::app.collaboration.channels.edit.type')
        : 'Channel Type';

    $_type_group = trans('admin::app.collaboration.channels.edit.type-group') !== 'admin::app.collaboration.channels.edit.type-group'
        ? trans('admin::app.collaboration.channels.edit.type-group')
        : 'Group';

    $_type_direct = trans('admin::app.collaboration.channels.edit.type-direct') !== 'admin::app.collaboration.channels.edit.type-direct'
        ? trans('admin::app.collaboration.channels.edit.type-direct')
        : 'Direct';

    $_cancel = trans('admin::app.collaboration.channels.edit.cancel') !== 'admin::app.collaboration.channels.edit.cancel'
        ? trans('admin::app.collaboration.channels.edit.cancel')
        : 'Cancel';

    $_save = trans('admin::app.collaboration.channels.edit.save-btn') !== 'admin::app.collaboration.channels.edit.save-btn'
        ? trans('admin::app.collaboration.channels.edit.save-btn')
        : 'Update Channel';
@endphp

<x-admin::layouts>
    <x-slot:title>
        {{ $_title }}
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap mb-6">
        <div class="flex gap-2.5 items-center">
            <p class="text-2xl dark:text-white">{{ $_title }}</p>
        </div>
    </div>

    <div class="flex gap-6 max-xl:flex-wrap">
        <div class="flex flex-col gap-6 flex-1 max-xl:flex-auto">
            <x-admin::form :action="route('admin.collaboration.channels.update', $channel->id)" method="PUT">
                <div
                    class="px-6 py-10 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 box-shadow">
                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            {{ $_name }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="text" name="name" rules="required|max:255"
                            :value="old('name', $channel->name)" :label="$_name" :placeholder="$_name" />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Type -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            {{ $_type }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="select" name="type" rules="required"
                            :value="old('type', $channel->type)" :label="$_type">
                            <option value="group" {{ $channel->type === 'group' ? 'selected' : '' }}>{{ $_type_group }}
                            </option>
                            <option value="direct" {{ $channel->type === 'direct' ? 'selected' : '' }}>{{ $_type_direct }}
                            </option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="type" />
                    </x-admin::form.control-group>

                    <!-- Description -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            {{ $_description }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="textarea" name="description"
                            :value="old('description', $channel->description)" :label="$_description"
                            :placeholder="$_description" />

                        <x-admin::form.control-group.error control-name="description" />
                    </x-admin::form.control-group>

                    <!-- Form Actions -->
                    <div class="flex gap-2.5 items-center justify-end mt-6">
                        <a href="{{ route('admin.collaboration.channels.index') }}">
                            <button type="button" class="secondary-button">
                                {{ $_cancel }}
                            </button>
                        </a>

                        <button type="submit" class="primary-button">
                            {{ $_save }}
                        </button>
                    </div>
                </div>
            </x-admin::form>
        </div>
    </div>
</x-admin::layouts>