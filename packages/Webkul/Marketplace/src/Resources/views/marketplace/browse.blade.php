<x-admin::layouts>
    <x-slot:title>
        @if(isset($category))
            @lang('marketplace::app.marketplace.browse.category.title', ['category' => $category->name])
        @elseif(isset($type))
            @lang('marketplace::app.marketplace.browse.type.title', ['type' => ucfirst($type)])
        @elseif(isset($searchTerm))
            @lang('marketplace::app.marketplace.browse.search.title')
        @elseif(request()->is('marketplace/featured'))
            @lang('marketplace::app.marketplace.browse.featured.title')
        @elseif(request()->is('marketplace/popular'))
            @lang('marketplace::app.marketplace.browse.popular.title')
        @elseif(request()->is('marketplace/recent'))
            @lang('marketplace::app.marketplace.browse.recent.title')
        @elseif(request()->is('marketplace/free'))
            @lang('marketplace::app.marketplace.browse.free.title')
        @elseif(request()->is('marketplace/paid'))
            @lang('marketplace::app.marketplace.browse.paid.title')
        @else
            @lang('marketplace::app.marketplace.browse.index.title')
        @endif
    </x-slot>

    {!! view_render_event('marketplace.browse.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.browse.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @if(isset($category))
                    @lang('marketplace::app.marketplace.browse.category.title', ['category' => $category->name])
                @elseif(isset($type))
                    @lang('marketplace::app.marketplace.browse.type.title', ['type' => ucfirst($type)])
                @elseif(isset($searchTerm))
                    @lang('marketplace::app.marketplace.browse.search.title')
                @elseif(request()->is('marketplace/featured'))
                    @lang('marketplace::app.marketplace.browse.featured.title')
                @elseif(request()->is('marketplace/popular'))
                    @lang('marketplace::app.marketplace.browse.popular.title')
                @elseif(request()->is('marketplace/recent'))
                    @lang('marketplace::app.marketplace.browse.recent.title')
                @elseif(request()->is('marketplace/free'))
                    @lang('marketplace::app.marketplace.browse.free.title')
                @elseif(request()->is('marketplace/paid'))
                    @lang('marketplace::app.marketplace.browse.paid.title')
                @else
                    @lang('marketplace::app.marketplace.browse.index.title')
                @endif
            </p>
            @if(isset($searchTerm))
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $extensions->total() }} results found for "{{ $searchTerm }}"
                </p>
            @endif
        </div>

        {!! view_render_event('marketplace.browse.header.left.after') !!}

        {!! view_render_event('marketplace.browse.header.right.before') !!}

        <!-- View Toggle -->
        <div class="flex items-center gap-x-2.5">
            <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-800">
                <button
                    @click="viewMode = 'grid'"
                    :class="viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 dark:bg-gray-900 dark:text-gray-300'"
                    class="flex items-center justify-center px-3 py-2 transition-all rounded-l-lg"
                    title="Grid View"
                >
                    <span class="icon-grid text-xl"></span>
                </button>
                <button
                    @click="viewMode = 'list'"
                    :class="viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 dark:bg-gray-900 dark:text-gray-300'"
                    class="flex items-center justify-center px-3 py-2 transition-all rounded-r-lg border-l border-gray-200 dark:border-gray-800"
                    title="List View"
                >
                    <span class="icon-list text-xl"></span>
                </button>
            </div>
        </div>

        {!! view_render_event('marketplace.browse.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.browse.header.after') !!}

    {!! view_render_event('marketplace.browse.content.before') !!}

    <div class="mt-3.5 flex gap-4 max-xl:flex-wrap" x-data="{ viewMode: 'grid' }">
        <!-- Filters Sidebar -->
        {!! view_render_event('marketplace.browse.content.sidebar.before') !!}

        <div class="w-[280px] max-w-full shrink-0 max-xl:w-full">
            @include('marketplace::marketplace.partials.filters', ['categories' => $categories, 'statistics' => $statistics ?? null])
        </div>

        {!! view_render_event('marketplace.browse.content.sidebar.after') !!}

        <!-- Main Content -->
        {!! view_render_event('marketplace.browse.content.main.before') !!}

        <div class="flex flex-1 flex-col gap-4">
            <!-- Search Bar -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <form action="{{ route('marketplace.browse.search') }}" method="GET" class="flex gap-3">
                    <div class="flex-1">
                        <input
                            type="text"
                            name="q"
                            value="{{ $searchTerm ?? request('search', '') }}"
                            placeholder="@lang('marketplace::app.marketplace.browse.search-placeholder')"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                    <button type="submit" class="primary-button flex items-center gap-2">
                        <span class="icon-search"></span>
                        Search
                    </button>
                </form>
            </div>

            <!-- Featured Extensions Section (Only on main browse page) -->
            @if(!isset($category) && !isset($type) && !isset($searchTerm) && request()->is('marketplace') && isset($statistics))
                {!! view_render_event('marketplace.browse.content.featured.before') !!}

                <div class="rounded-lg border border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50 p-6 dark:border-gray-800 dark:from-blue-900/20 dark:to-purple-900/20">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold dark:text-white">
                                @lang('marketplace::app.marketplace.browse.featured.title')
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Hand-picked extensions by our team
                            </p>
                        </div>
                        <a
                            href="{{ route('marketplace.browse.featured') }}"
                            class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                        >
                            View All
                        </a>
                    </div>
                </div>

                {!! view_render_event('marketplace.browse.content.featured.after') !!}
            @endif

            <!-- Sort and Results Info -->
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Showing {{ $extensions->firstItem() ?? 0 }} - {{ $extensions->lastItem() ?? 0 }} of {{ $extensions->total() }} extensions
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.marketplace.browse.sort-by'):
                    </label>
                    <select
                        onchange="window.location.href = updateQueryParam('sort_by', this.value)"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>
                            @lang('marketplace::app.marketplace.browse.sort-recent')
                        </option>
                        <option value="downloads" {{ request('sort_by') === 'downloads' ? 'selected' : '' }}>
                            @lang('marketplace::app.marketplace.browse.sort-popularity')
                        </option>
                        <option value="rating" {{ request('sort_by') === 'rating' ? 'selected' : '' }}>
                            @lang('marketplace::app.marketplace.browse.sort-rating')
                        </option>
                        <option value="price_asc" {{ request('sort_by') === 'price_asc' ? 'selected' : '' }}>
                            @lang('marketplace::app.marketplace.browse.sort-price-low')
                        </option>
                        <option value="price_desc" {{ request('sort_by') === 'price_desc' ? 'selected' : '' }}>
                            @lang('marketplace::app.marketplace.browse.sort-price-high')
                        </option>
                    </select>
                </div>
            </div>

            <!-- Extensions Grid/List -->
            @if($extensions->isEmpty())
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-12 dark:border-gray-800 dark:bg-gray-900">
                    <span class="icon-package text-6xl text-gray-400 dark:text-gray-600"></span>

                    <p class="mt-4 text-xl font-semibold dark:text-white">
                        @lang('marketplace::app.marketplace.browse.no-results')
                    </p>

                    <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
                        Try adjusting your filters or search terms
                    </p>

                    @if(request()->has('search') || request()->has('category_id') || request()->has('type'))
                        <a
                            href="{{ route('marketplace.browse.index') }}"
                            class="primary-button mt-6"
                        >
                            @lang('marketplace::app.marketplace.browse.clear-filters')
                        </a>
                    @endif
                </div>
            @else
                <!-- Grid View -->
                <div x-show="viewMode === 'grid'" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($extensions as $extension)
                        @include('marketplace::marketplace.partials.extension-card', ['extension' => $extension, 'view' => 'grid'])
                    @endforeach
                </div>

                <!-- List View -->
                <div x-show="viewMode === 'list'" class="flex flex-col gap-4">
                    @foreach($extensions as $extension)
                        @include('marketplace::marketplace.partials.extension-card', ['extension' => $extension, 'view' => 'list'])
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $extensions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {!! view_render_event('marketplace.browse.content.main.after') !!}
    </div>

    {!! view_render_event('marketplace.browse.content.after') !!}

    @push('scripts')
        <script>
            function updateQueryParam(key, value) {
                const url = new URL(window.location.href);
                url.searchParams.set(key, value);
                return url.toString();
            }
        </script>
    @endpush
</x-admin::layouts>
