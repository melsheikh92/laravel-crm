@php
    $_title = trans('admin::app.collaboration.channels.create.title') !== 'admin::app.collaboration.channels.create.title' 
        ? trans('admin::app.collaboration.channels.create.title') 
        : 'Create Channel';
    
    $_name = trans('admin::app.collaboration.channels.create.name') !== 'admin::app.collaboration.channels.create.name' 
        ? trans('admin::app.collaboration.channels.create.name') 
        : 'Channel Name';
    
    $_description = trans('admin::app.collaboration.channels.create.description') !== 'admin::app.collaboration.channels.create.description' 
        ? trans('admin::app.collaboration.channels.create.description') 
        : 'Description';
    
    $_type = trans('admin::app.collaboration.channels.create.type') !== 'admin::app.collaboration.channels.create.type' 
        ? trans('admin::app.collaboration.channels.create.type') 
        : 'Channel Type';
    
    $_type_group = trans('admin::app.collaboration.channels.create.type-group') !== 'admin::app.collaboration.channels.create.type-group' 
        ? trans('admin::app.collaboration.channels.create.type-group') 
        : 'Group';
    
    $_type_direct = trans('admin::app.collaboration.channels.create.type-direct') !== 'admin::app.collaboration.channels.create.type-direct' 
        ? trans('admin::app.collaboration.channels.create.type-direct') 
        : 'Direct';
    
    $_cancel = trans('admin::app.collaboration.channels.create.cancel') !== 'admin::app.collaboration.channels.create.cancel' 
        ? trans('admin::app.collaboration.channels.create.cancel') 
        : 'Cancel';
    
    $_save = trans('admin::app.collaboration.channels.create.save-btn') !== 'admin::app.collaboration.channels.create.save-btn' 
        ? trans('admin::app.collaboration.channels.create.save-btn') 
        : 'Create Channel';
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
            <x-admin::form :action="route('admin.collaboration.channels.store')">
                <div class="px-6 py-10 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 box-shadow">
                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            {{ $_name }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            rules="required|max:255"
                            :value="old('name')"
                            :label="$_name"
                            :placeholder="$_name"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Type -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            {{ $_type }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="type"
                            rules="required"
                            :value="old('type', 'group')"
                            :label="$_type"
                        >
                            <option value="group">{{ $_type_group }}</option>
                            <option value="direct">{{ $_type_direct }}</option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="type" />
                    </x-admin::form.control-group>

                    <!-- Description -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            {{ $_description }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="description"
                            :value="old('description')"
                            :label="$_description"
                            :placeholder="$_description"
                        />

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

