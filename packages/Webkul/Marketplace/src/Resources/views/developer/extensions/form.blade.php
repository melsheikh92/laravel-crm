@props([
    'extension' => null,
    'categories' => [],
])

<div class="flex gap-2.5 max-xl:flex-wrap">
    {!! view_render_event('marketplace.developer.extensions.form.left.before', ['extension' => $extension]) !!}

    <!-- Left Panel -->
    <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
        <!-- Basic Information -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('marketplace::app.developer.extensions.form.basic-information')
            </p>

            <!-- Name -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.developer.extensions.form.name')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="name"
                    name="name"
                    :value="old('name', $extension?->name)"
                    rules="required"
                    :label="trans('marketplace::app.developer.extensions.form.name')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.name-placeholder')"
                />

                <x-admin::form.control-group.error control-name="name" />
            </x-admin::form.control-group>

            <!-- Slug -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.developer.extensions.form.slug')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="slug"
                    name="slug"
                    :value="old('slug', $extension?->slug)"
                    rules="required"
                    :label="trans('marketplace::app.developer.extensions.form.slug')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.slug-placeholder')"
                />

                <x-admin::form.control-group.error control-name="slug" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.extensions.form.slug-hint')
                </p>
            </x-admin::form.control-group>

            <!-- Short Description -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('marketplace::app.developer.extensions.form.description')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    id="description"
                    name="description"
                    :value="old('description', $extension?->description)"
                    rows="3"
                    rules="required"
                    :label="trans('marketplace::app.developer.extensions.form.description')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.description-placeholder')"
                />

                <x-admin::form.control-group.error control-name="description" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.extensions.form.description-hint')
                </p>
            </x-admin::form.control-group>

            <!-- Long Description -->
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.extensions.form.long-description')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    id="long_description"
                    name="long_description"
                    :value="old('long_description', $extension?->long_description)"
                    rows="8"
                    :label="trans('marketplace::app.developer.extensions.form.long-description')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.long-description-placeholder')"
                />

                <x-admin::form.control-group.error control-name="long_description" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.extensions.form.long-description-hint')
                </p>
            </x-admin::form.control-group>
        </div>

        <!-- Media -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('marketplace::app.developer.extensions.form.media')
            </p>

            <!-- Logo -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.extensions.form.logo')
                </x-admin::form.control-group.label>

                @if($extension?->logo)
                    <div class="mb-2">
                        <img
                            src="{{ Storage::url($extension->logo) }}"
                            alt="{{ $extension->name }}"
                            class="h-24 w-24 rounded-lg object-cover"
                        />
                    </div>
                @endif

                <x-admin::form.control-group.control
                    type="file"
                    id="logo"
                    name="logo"
                    accept="image/jpeg,image/png,image/jpg,image/gif"
                    :label="trans('marketplace::app.developer.extensions.form.logo')"
                />

                <x-admin::form.control-group.error control-name="logo" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.extensions.form.logo-hint')
                </p>
            </x-admin::form.control-group>

            <!-- Tags -->
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.extensions.form.tags')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    id="tags"
                    name="tags"
                    :value="old('tags', $extension?->tags ? implode(', ', $extension->tags) : '')"
                    :label="trans('marketplace::app.developer.extensions.form.tags')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.tags-placeholder')"
                />

                <x-admin::form.control-group.error control-name="tags" />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.extensions.form.tags-hint')
                </p>
            </x-admin::form.control-group>
        </div>

        <!-- Links & Support -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('marketplace::app.developer.extensions.form.links-support')
            </p>

            <!-- Documentation URL -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.extensions.form.documentation-url')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="url"
                    id="documentation_url"
                    name="documentation_url"
                    :value="old('documentation_url', $extension?->documentation_url)"
                    :label="trans('marketplace::app.developer.extensions.form.documentation-url')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.documentation-url-placeholder')"
                />

                <x-admin::form.control-group.error control-name="documentation_url" />
            </x-admin::form.control-group>

            <!-- Demo URL -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.extensions.form.demo-url')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="url"
                    id="demo_url"
                    name="demo_url"
                    :value="old('demo_url', $extension?->demo_url)"
                    :label="trans('marketplace::app.developer.extensions.form.demo-url')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.demo-url-placeholder')"
                />

                <x-admin::form.control-group.error control-name="demo_url" />
            </x-admin::form.control-group>

            <!-- Repository URL -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.extensions.form.repository-url')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="url"
                    id="repository_url"
                    name="repository_url"
                    :value="old('repository_url', $extension?->repository_url)"
                    :label="trans('marketplace::app.developer.extensions.form.repository-url')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.repository-url-placeholder')"
                />

                <x-admin::form.control-group.error control-name="repository_url" />
            </x-admin::form.control-group>

            <!-- Support Email -->
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label>
                    @lang('marketplace::app.developer.extensions.form.support-email')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="email"
                    id="support_email"
                    name="support_email"
                    :value="old('support_email', $extension?->support_email)"
                    :label="trans('marketplace::app.developer.extensions.form.support-email')"
                    :placeholder="trans('marketplace::app.developer.extensions.form.support-email-placeholder')"
                />

                <x-admin::form.control-group.error control-name="support_email" />
            </x-admin::form.control-group>
        </div>
    </div>

    {!! view_render_event('marketplace.developer.extensions.form.left.after', ['extension' => $extension]) !!}

    {!! view_render_event('marketplace.developer.extensions.form.right.before', ['extension' => $extension]) !!}

    <!-- Right Panel -->
    <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
        <!-- Classification -->
        <x-admin::accordion>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.developer.extensions.form.classification')
                    </p>
                </div>
            </x-slot>

            <x-slot:content>
                <!-- Type -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('marketplace::app.developer.extensions.form.type')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        id="type"
                        name="type"
                        :value="old('type', $extension?->type)"
                        rules="required"
                        :label="trans('marketplace::app.developer.extensions.form.type')"
                    >
                        <option value="">@lang('marketplace::app.developer.extensions.form.select-type')</option>
                        <option value="plugin" @if(old('type', $extension?->type) == 'plugin') selected @endif>
                            @lang('marketplace::app.developer.extensions.form.type-plugin')
                        </option>
                        <option value="theme" @if(old('type', $extension?->type) == 'theme') selected @endif>
                            @lang('marketplace::app.developer.extensions.form.type-theme')
                        </option>
                        <option value="integration" @if(old('type', $extension?->type) == 'integration') selected @endif>
                            @lang('marketplace::app.developer.extensions.form.type-integration')
                        </option>
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="type" />
                </x-admin::form.control-group>

                <!-- Category -->
                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label class="required">
                        @lang('marketplace::app.developer.extensions.form.category')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        id="category_id"
                        name="category_id"
                        :value="old('category_id', $extension?->category_id)"
                        rules="required"
                        :label="trans('marketplace::app.developer.extensions.form.category')"
                    >
                        <option value="">@lang('marketplace::app.developer.extensions.form.select-category')</option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @if(old('category_id', $extension?->category_id) == $category->id) selected @endif
                            >
                                {{ str_repeat('— ', $category->depth ?? 0) }}{{ $category->name }}
                            </option>
                        @endforeach
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="category_id" />
                </x-admin::form.control-group>
            </x-slot>
        </x-admin::accordion>

        <!-- Pricing -->
        <x-admin::accordion>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.developer.extensions.form.pricing')
                    </p>
                </div>
            </x-slot>

            <x-slot:content>
                <!-- Price -->
                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label class="required">
                        @lang('marketplace::app.developer.extensions.form.price')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="number"
                        id="price"
                        name="price"
                        :value="old('price', $extension?->price ?? 0)"
                        min="0"
                        step="0.01"
                        rules="required"
                        :label="trans('marketplace::app.developer.extensions.form.price')"
                        :placeholder="trans('marketplace::app.developer.extensions.form.price-placeholder')"
                    />

                    <x-admin::form.control-group.error control-name="price" />

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.developer.extensions.form.price-hint')
                    </p>
                </x-admin::form.control-group>
            </x-slot>
        </x-admin::accordion>

        @if($extension)
            <!-- Extension Statistics -->
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.developer.extensions.form.statistics')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="flex flex-col gap-3">
                        <!-- Status -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.extensions.form.status')
                            </span>
                            <span class="rounded-full px-2 py-1 text-xs font-medium
                                @if($extension->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                @elseif($extension->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                @elseif($extension->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                @endif">
                                {{ ucfirst($extension->status) }}
                            </span>
                        </div>

                        <!-- Downloads -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.extensions.form.downloads')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ number_format($extension->downloads_count) }}
                            </span>
                        </div>

                        <!-- Average Rating -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.extensions.form.average-rating')
                            </span>
                            <div class="flex items-center gap-1">
                                <span class="icon-star text-yellow-500"></span>
                                <span class="text-sm font-medium text-gray-800 dark:text-white">
                                    {{ number_format($extension->average_rating, 1) }}
                                </span>
                            </div>
                        </div>

                        <!-- Reviews Count -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.extensions.form.reviews')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $extension->reviews()->count() }}
                            </span>
                        </div>

                        <!-- Versions Count -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.extensions.form.versions')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $extension->versions()->count() }}
                            </span>
                        </div>

                        <!-- Created At -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.extensions.form.created-at')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $extension->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        <!-- Updated At -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.extensions.form.updated-at')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $extension->updated_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </x-slot>
            </x-admin::accordion>

            <!-- Quick Actions -->
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.developer.extensions.form.quick-actions')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="flex flex-col gap-2">
                        <a
                            href="{{ route('developer.marketplace.versions.index', $extension->id) }}"
                            class="secondary-button text-center"
                        >
                            @lang('marketplace::app.developer.extensions.form.manage-versions')
                        </a>

                        <a
                            href="{{ route('developer.marketplace.submissions.by_extension', $extension->id) }}"
                            class="secondary-button text-center"
                        >
                            @lang('marketplace::app.developer.extensions.form.view-submissions')
                        </a>

                        <a
                            href="{{ route('developer.marketplace.extensions.analytics', $extension->id) }}"
                            class="secondary-button text-center"
                        >
                            @lang('marketplace::app.developer.extensions.form.view-analytics')
                        </a>
                    </div>
                </x-slot>
            </x-admin::accordion>
        @endif
    </div>

    {!! view_render_event('marketplace.developer.extensions.form.right.after', ['extension' => $extension]) !!}
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
