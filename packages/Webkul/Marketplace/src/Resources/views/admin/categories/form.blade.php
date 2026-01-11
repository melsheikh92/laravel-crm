@props([
    'category' => null,
    'categories' => [],
])

<div class="flex gap-2.5 max-xl:flex-wrap">
    {!! view_render_event('admin.marketplace.categories.form.left.before', ['category' => $category]) !!}

    <!-- Left Panel -->
    <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('marketplace::app.admin.categories.form.general-information')
            </p>

            <!-- Name -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.admin.categories.form.name')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="name"
                    name="name"
                    :value="old('name', $category?->name)"
                    rules="required"
                    :label="trans('marketplace::app.admin.categories.form.name')"
                    :placeholder="trans('marketplace::app.admin.categories.form.name')"
                />

                <x-admin::form.control-group.error control-name="name" />
            </x-admin::form.control-group>

            <!-- Slug -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.admin.categories.form.slug')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="slug"
                    name="slug"
                    :value="old('slug', $category?->slug)"
                    rules="required"
                    :label="trans('marketplace::app.admin.categories.form.slug')"
                    :placeholder="trans('marketplace::app.admin.categories.form.slug')"
                />

                <x-admin::form.control-group.error control-name="slug" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.admin.categories.form.slug-hint')
                </p>
            </x-admin::form.control-group>

            <!-- Description -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.admin.categories.form.description')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    id="description"
                    name="description"
                    :value="old('description', $category?->description)"
                    rows="4"
                    :label="trans('marketplace::app.admin.categories.form.description')"
                    :placeholder="trans('marketplace::app.admin.categories.form.description')"
                />

                <x-admin::form.control-group.error control-name="description" />
            </x-admin::form.control-group>

            <!-- Icon -->
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.admin.categories.form.icon')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="icon"
                    name="icon"
                    :value="old('icon', $category?->icon)"
                    :label="trans('marketplace::app.admin.categories.form.icon')"
                    :placeholder="trans('marketplace::app.admin.categories.form.icon-placeholder')"
                />

                <x-admin::form.control-group.error control-name="icon" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.admin.categories.form.icon-hint')
                </p>
            </x-admin::form.control-group>
        </div>
    </div>

    {!! view_render_event('admin.marketplace.categories.form.left.after', ['category' => $category]) !!}

    {!! view_render_event('admin.marketplace.categories.form.right.before', ['category' => $category]) !!}

    <!-- Right Panel -->
    <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
        <!-- Hierarchy Settings -->
        <x-admin::accordion>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.categories.form.hierarchy')
                    </p>
                </div>
            </x-slot>

            <x-slot:content>
                <!-- Parent Category -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('marketplace::app.admin.categories.form.parent-category')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        id="parent_id"
                        name="parent_id"
                        :value="old('parent_id', $category?->parent_id)"
                        :label="trans('marketplace::app.admin.categories.form.parent-category')"
                    >
                        <option value="">@lang('marketplace::app.admin.categories.form.none-root')</option>

                        @foreach($categories as $cat)
                            <option
                                value="{{ $cat->id }}"
                                @if(old('parent_id', $category?->parent_id) == $cat->id) selected @endif
                            >
                                {{ str_repeat('— ', $cat->depth ?? 0) }}{{ $cat->name }}
                            </option>
                        @endforeach
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="parent_id" />

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.admin.categories.form.parent-hint')
                    </p>
                </x-admin::form.control-group>

                <!-- Sort Order -->
                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label>
                        @lang('marketplace::app.admin.categories.form.sort-order')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        :value="old('sort_order', $category?->sort_order ?? 0)"
                        min="0"
                        :label="trans('marketplace::app.admin.categories.form.sort-order')"
                        :placeholder="trans('marketplace::app.admin.categories.form.sort-order-placeholder')"
                    />

                    <x-admin::form.control-group.error control-name="sort_order" />

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.admin.categories.form.sort-order-hint')
                    </p>
                </x-admin::form.control-group>
            </x-slot>
        </x-admin::accordion>

        @if($category)
            <!-- Category Information -->
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.categories.form.information')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="flex flex-col gap-2">
                        <!-- Extensions Count -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.form.extensions-count')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->extensions()->count() }}
                            </span>
                        </div>

                        <!-- Children Count -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.form.children-count')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->children()->count() }}
                            </span>
                        </div>

                        @if($category->parent)
                            <!-- Parent -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.admin.categories.form.parent')
                                </span>
                                <span class="text-sm font-medium text-gray-800 dark:text-white">
                                    {{ $category->parent->name }}
                                </span>
                            </div>
                        @endif

                        <!-- Full Path -->
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.form.full-path')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->getFullPath() }}
                            </span>
                        </div>

                        <!-- Created At -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.form.created-at')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </x-slot>
            </x-admin::accordion>
        @endif
    </div>

    {!! view_render_event('admin.marketplace.categories.form.right.after', ['category' => $category]) !!}
</div>

@pushOnce('scripts')
    <script type="module">
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput && !slugInput.value) {
            nameInput.addEventListener('input', function() {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                slugInput.value = slug;
            });
        }
    </script>
@endPushOnce
