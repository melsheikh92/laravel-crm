<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.categories.show.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.marketplace.categories.show.breadcrumbs.before', ['category' => $category]) !!}

                <x-admin::breadcrumbs
                    name="marketplace.categories.show"
                    :entity="$category"
                />

                {!! view_render_event('admin.marketplace.categories.show.breadcrumbs.after', ['category' => $category]) !!}

                <div class="text-xl font-bold dark:text-white">
                    {{ $category->name }}
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                {!! view_render_event('admin.marketplace.categories.show.edit_button.before', ['category' => $category]) !!}

                @if (bouncer()->hasPermission('marketplace.categories.edit'))
                    <a
                        href="{{ route('admin.marketplace.categories.edit', $category->id) }}"
                        class="primary-button"
                    >
                        @lang('marketplace::app.admin.categories.show.edit-btn')
                    </a>
                @endif

                {!! view_render_event('admin.marketplace.categories.show.edit_button.after', ['category' => $category]) !!}
            </div>
        </div>

        <div class="flex gap-4 max-lg:flex-wrap">
            {!! view_render_event('admin.marketplace.categories.show.left.before', ['category' => $category]) !!}

            <!-- Left Panel - Category Details -->
            <div class="flex flex-1 flex-col gap-4">
                <!-- Basic Information -->
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.categories.show.basic-info')
                    </h3>

                    <div class="flex flex-col gap-3">
                        <!-- Name -->
                        <div class="flex items-start gap-3">
                            <span class="min-w-[120px] text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.show.name')
                            </span>
                            <div class="flex items-center gap-2">
                                @if($category->icon)
                                    <span class="text-2xl" v-html="{{ json_encode($category->icon) }}"></span>
                                @endif
                                <span class="text-sm font-medium text-gray-800 dark:text-white">
                                    {{ $category->name }}
                                </span>
                            </div>
                        </div>

                        <!-- Slug -->
                        <div class="flex items-start gap-3">
                            <span class="min-w-[120px] text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.show.slug')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->slug }}
                            </span>
                        </div>

                        <!-- Description -->
                        @if($category->description)
                            <div class="flex items-start gap-3">
                                <span class="min-w-[120px] text-sm text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.admin.categories.show.description')
                                </span>
                                <span class="text-sm text-gray-800 dark:text-white">
                                    {{ $category->description }}
                                </span>
                            </div>
                        @endif

                        <!-- Full Path -->
                        <div class="flex items-start gap-3">
                            <span class="min-w-[120px] text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.show.full-path')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->getFullPath() }}
                            </span>
                        </div>

                        <!-- Sort Order -->
                        <div class="flex items-start gap-3">
                            <span class="min-w-[120px] text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.show.sort-order')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->sort_order }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Extensions -->
                @if($category->extensions->count() > 0)
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.categories.show.extensions') ({{ $category->extensions->count() }})
                        </h3>

                        <div class="flex flex-col gap-2">
                            @foreach($category->extensions as $extension)
                                <a
                                    href="{{ route('admin.marketplace.extensions.show', $extension->id) }}"
                                    class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                                >
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800 dark:text-white">
                                            {{ $extension->name }}
                                        </p>
                                        @if($extension->description)
                                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                                {{ Str::limit($extension->description, 100) }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($extension->type) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Child Categories -->
                @if($category->children->count() > 0)
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.categories.show.subcategories') ({{ $category->children->count() }})
                        </h3>

                        <div class="flex flex-col gap-2">
                            @foreach($category->children as $child)
                                <a
                                    href="{{ route('admin.marketplace.categories.show', $child->id) }}"
                                    class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                                >
                                    @if($child->icon)
                                        <span class="text-2xl" v-html="{{ json_encode($child->icon) }}"></span>
                                    @else
                                        <span class="icon-folder text-2xl text-gray-400"></span>
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800 dark:text-white">
                                            {{ $child->name }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ $child->extensions_count ?? 0 }} @lang('marketplace::app.admin.categories.show.extensions-count')
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {!! view_render_event('admin.marketplace.categories.show.left.after', ['category' => $category]) !!}

            {!! view_render_event('admin.marketplace.categories.show.right.before', ['category' => $category]) !!}

            <!-- Right Panel - Statistics & Info -->
            <div class="flex w-[360px] max-w-full flex-col gap-4 max-sm:w-full">
                <!-- Statistics -->
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.categories.show.statistics')
                    </h3>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.show.extensions-count')
                            </span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white">
                                {{ $category->extensions->count() }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.show.subcategories-count')
                            </span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white">
                                {{ $category->children->count() }}
                            </span>
                        </div>

                        @if(!$category->isRoot())
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.admin.categories.show.depth')
                                </span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-white">
                                    {{ $category->getDepth() }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Hierarchy -->
                @if($category->parent)
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.categories.show.hierarchy')
                        </h3>

                        <div class="flex flex-col gap-2">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.admin.categories.show.parent')
                                </span>
                                <a
                                    href="{{ route('admin.marketplace.categories.show', $category->parent->id) }}"
                                    class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    {{ $category->parent->name }}
                                </a>
                            </div>

                            @if($category->ancestors()->count() > 1)
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        @lang('marketplace::app.admin.categories.show.breadcrumb')
                                    </span>
                                    <span class="text-sm text-gray-800 dark:text-white">
                                        @foreach($category->ancestors()->reverse() as $ancestor)
                                            <a href="{{ route('admin.marketplace.categories.show', $ancestor->id) }}" class="text-blue-600 hover:underline dark:text-blue-400">{{ $ancestor->name }}</a>
                                            @if(!$loop->last) / @endif
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Metadata -->
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.categories.show.metadata')
                    </h3>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.show.created-at')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.categories.show.updated-at')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $category->updated_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {!! view_render_event('admin.marketplace.categories.show.right.after', ['category' => $category]) !!}
        </div>
    </div>
</x-admin::layouts>
