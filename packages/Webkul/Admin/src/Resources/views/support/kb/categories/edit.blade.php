<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.support.kb.categories.edit.title')
    </x-slot>

    <x-admin::form :action="route('admin.support.kb.categories.update', $category->id)" method="PUT">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="support.kb.categories.edit" :entity="$category" />
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.support.kb.categories.edit.title')
                </div>
            </div>

            <button type="submit" class="primary-button">
                @lang('admin::app.support.kb.categories.edit.update-btn')
            </button>
        </div>

        <div class="mt-3.5">
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.support.kb.categories.create.general')
                </p>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.support.kb.categories.create.name')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="name"
                        :value="old('name', $category->name)"
                        rules="required"
                    />
                    <x-admin::form.control-group.error control-name="name" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('admin::app.support.kb.categories.create.description')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="description"
                        :value="old('description', $category->description)"
                    />
                    <x-admin::form.control-group.error control-name="description" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('admin::app.support.kb.categories.create.parent')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="parent_id"
                        :value="old('parent_id', $category->parent_id)"
                    >
                        <option value="">@lang('admin::app.support.kb.categories.create.select-parent')</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </x-admin::form.control-group.control>
                    <x-admin::form.control-group.error control-name="parent_id" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.support.kb.categories.create.sort-order')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="sort_order"
                        rules="required|numeric"
                        :value="old('sort_order', $category->sort_order)"
                    />
                    <x-admin::form.control-group.error control-name="sort_order" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.support.kb.categories.create.visibility')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="visibility"
                        rules="required"
                        :value="old('visibility', $category->visibility)"
                    >
                        <option value="public">Public</option>
                        <option value="internal">Internal</option>
                        <option value="customer_portal">Customer Portal</option>
                    </x-admin::form.control-group.control>
                    <x-admin::form.control-group.error control-name="visibility" />
                </x-admin::form.control-group>

                <x-admin::form.control-group class="mt-4 flex items-center justify-between">
                    <x-admin::form.control-group.label>
                        @lang('admin::app.support.kb.categories.create.visible-in-menu')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="switch"
                        name="is_active"
                        value="1"
                        :checked="old('is_active', $category->is_active)"
                    />
                    <x-admin::form.control-group.error control-name="is_active" />
                </x-admin::form.control-group>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
