<div class="sticky top-4 flex flex-col gap-4">
    {!! view_render_event('marketplace.browse.filters.before') !!}

    <!-- Filters Header -->
    <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-base font-semibold dark:text-white">
            Filters
        </h3>
        @if(request()->hasAny(['category_id', 'type', 'price_min', 'price_max', 'is_free', 'min_rating']))
            <a
                href="{{ route('marketplace.browse.index') }}"
                class="text-sm text-blue-600 hover:underline dark:text-blue-400"
            >
                @lang('marketplace::app.marketplace.browse.clear-filters')
            </a>
        @endif
    </div>

    <!-- Category Filter -->
    {!! view_render_event('marketplace.browse.filters.category.before') !!}

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <h4 class="mb-3 text-sm font-semibold dark:text-white">
            @lang('marketplace::app.marketplace.browse.filter-by-category')
        </h4>

        <div class="space-y-2 max-h-64 overflow-y-auto">
            <!-- All Categories Option -->
            <a
                href="{{ route('marketplace.browse.index') }}"
                class="flex items-center justify-between rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ !request('category_id') && !isset($category) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
            >
                <span class="text-sm {{ !request('category_id') && !isset($category) ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                    @lang('marketplace::app.marketplace.browse.all-categories')
                </span>
            </a>

            @foreach($categories as $cat)
                <a
                    href="{{ route('marketplace.browse.category', $cat->slug) }}"
                    class="flex items-center justify-between rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ (isset($category) && $category->id === $cat->id) || request('category_id') == $cat->id ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                >
                    <span class="text-sm {{ (isset($category) && $category->id === $cat->id) || request('category_id') == $cat->id ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $cat->name }}
                    </span>
                    @if($cat->extensions_count > 0)
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            {{ $cat->extensions_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {!! view_render_event('marketplace.browse.filters.category.after') !!}

    <!-- Type Filter -->
    {!! view_render_event('marketplace.browse.filters.type.before') !!}

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <h4 class="mb-3 text-sm font-semibold dark:text-white">
            @lang('marketplace::app.marketplace.browse.filter-by-type')
        </h4>

        <div class="space-y-2">
            @php
                $types = [
                    'plugin' => 'Plugins',
                    'theme' => 'Themes',
                    'integration' => 'Integrations',
                ];
            @endphp

            @foreach($types as $typeKey => $typeLabel)
                <a
                    href="{{ route('marketplace.browse.type', $typeKey) }}"
                    class="flex items-center justify-between rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ (isset($type) && $type === $typeKey) || request('type') === $typeKey ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                >
                    <span class="text-sm {{ (isset($type) && $type === $typeKey) || request('type') === $typeKey ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $typeLabel }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {!! view_render_event('marketplace.browse.filters.type.after') !!}

    <!-- Price Filter -->
    {!! view_render_event('marketplace.browse.filters.price.before') !!}

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <h4 class="mb-3 text-sm font-semibold dark:text-white">
            @lang('marketplace::app.marketplace.browse.filter-by-price')
        </h4>

        <div class="space-y-2">
            <!-- Free Only -->
            <a
                href="{{ route('marketplace.browse.free') }}"
                class="flex items-center rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ request()->is('marketplace/free') ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
            >
                <span class="text-sm {{ request()->is('marketplace/free') ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                    @lang('marketplace::app.marketplace.browse.show-free-only')
                </span>
            </a>

            <!-- Paid Only -->
            <a
                href="{{ route('marketplace.browse.paid') }}"
                class="flex items-center rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ request()->is('marketplace/paid') ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
            >
                <span class="text-sm {{ request()->is('marketplace/paid') ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                    @lang('marketplace::app.marketplace.browse.show-paid-only')
                </span>
            </a>

            <!-- Custom Price Range -->
            <div class="mt-3 space-y-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                <form action="{{ route('marketplace.browse.index') }}" method="GET">
                    <!-- Preserve existing filters -->
                    @if(request('category_id'))
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                    @endif
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">Min</label>
                            <input
                                type="number"
                                name="price_min"
                                value="{{ request('price_min') }}"
                                placeholder="$0"
                                min="0"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">Max</label>
                            <input
                                type="number"
                                name="price_max"
                                value="{{ request('price_max') }}"
                                placeholder="Any"
                                min="0"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    </div>
                    <button type="submit" class="secondary-button mt-2 w-full text-sm">
                        @lang('marketplace::app.marketplace.browse.apply-filters')
                    </button>
                </form>
            </div>
        </div>
    </div>

    {!! view_render_event('marketplace.browse.filters.price.after') !!}

    <!-- Rating Filter -->
    {!! view_render_event('marketplace.browse.filters.rating.before') !!}

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <h4 class="mb-3 text-sm font-semibold dark:text-white">
            @lang('marketplace::app.marketplace.browse.filter-by-rating')
        </h4>

        <div class="space-y-2">
            @for($i = 5; $i >= 1; $i--)
                @php
                    $currentUrl = url()->current();
                    $params = array_merge(request()->query(), ['min_rating' => $i]);
                    $filterUrl = $currentUrl . '?' . http_build_query($params);
                @endphp
                <a
                    href="{{ $filterUrl }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ request('min_rating') == $i ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                >
                    <div class="flex items-center gap-1">
                        @for($j = 1; $j <= 5; $j++)
                            <span class="icon-star {{ $j <= $i ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' }} text-sm"></span>
                        @endfor
                    </div>
                    <span class="text-sm {{ request('min_rating') == $i ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                        & Up
                    </span>
                </a>
            @endfor
        </div>
    </div>

    {!! view_render_event('marketplace.browse.filters.rating.after') !!}

    <!-- Quick Links -->
    {!! view_render_event('marketplace.browse.filters.quick-links.before') !!}

    @if(isset($statistics))
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h4 class="mb-3 text-sm font-semibold dark:text-white">
                Quick Links
            </h4>

            <div class="space-y-2">
                <a
                    href="{{ route('marketplace.browse.featured') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ request()->is('marketplace/featured') ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                >
                    <span class="icon-star text-sm text-yellow-500"></span>
                    <span class="text-sm {{ request()->is('marketplace/featured') ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                        @lang('marketplace::app.marketplace.browse.featured.title')
                    </span>
                </a>

                <a
                    href="{{ route('marketplace.browse.popular') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ request()->is('marketplace/popular') ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                >
                    <span class="icon-trending-up text-sm text-green-600 dark:text-green-400"></span>
                    <span class="text-sm {{ request()->is('marketplace/popular') ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                        @lang('marketplace::app.marketplace.browse.popular.title')
                    </span>
                </a>

                <a
                    href="{{ route('marketplace.browse.recent') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 {{ request()->is('marketplace/recent') ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                >
                    <span class="icon-clock text-sm text-blue-600 dark:text-blue-400"></span>
                    <span class="text-sm {{ request()->is('marketplace/recent') ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                        @lang('marketplace::app.marketplace.browse.recent.title')
                    </span>
                </a>
            </div>
        </div>
    @endif

    {!! view_render_event('marketplace.browse.filters.quick-links.after') !!}

    {!! view_render_event('marketplace.browse.filters.after') !!}
</div>
