@if($view === 'grid')
    <!-- Grid Card View -->
    <div class="flex flex-col rounded-lg border border-gray-200 bg-white transition-all hover:shadow-lg dark:border-gray-800 dark:bg-gray-900">
        <!-- Extension Logo/Header -->
        <div class="relative border-b border-gray-200 p-4 dark:border-gray-800">
            <a href="{{ route('marketplace.extension.show', $extension->slug) }}" class="flex items-center gap-4">
                @if($extension->logo)
                    <img
                        src="{{ Storage::url($extension->logo) }}"
                        alt="{{ $extension->name }}"
                        class="h-16 w-16 rounded-lg object-cover"
                    />
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30">
                        <span class="icon-package text-2xl text-blue-600 dark:text-blue-400"></span>
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold truncate dark:text-white">
                        {{ $extension->name }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ ucfirst($extension->type) }}
                    </p>
                </div>
            </a>

            <!-- Featured Badge -->
            @if($extension->is_featured)
                <span class="absolute right-4 top-4 flex items-center gap-1 rounded-full bg-gradient-to-r from-yellow-400 to-orange-400 px-2 py-1 text-xs font-medium text-white">
                    <span class="icon-star text-xs"></span>
                    Featured
                </span>
            @endif
        </div>

        <!-- Extension Details -->
        <div class="flex-1 p-4">
            <p class="line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                {{ $extension->description }}
            </p>

            <div class="mt-4 grid grid-cols-2 gap-4">
                <!-- Downloads -->
                <div class="flex items-center gap-2">
                    <span class="icon-download text-blue-600 dark:text-blue-400"></span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Downloads
                        </p>
                        <p class="text-sm font-semibold dark:text-white">
                            {{ number_format($extension->downloads_count) }}
                        </p>
                    </div>
                </div>

                <!-- Rating -->
                <div class="flex items-center gap-2">
                    <span class="icon-star text-yellow-500"></span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Rating
                        </p>
                        <p class="text-sm font-semibold dark:text-white">
                            {{ number_format($extension->average_rating, 1) }}
                            <span class="text-xs text-gray-400">({{ $extension->reviews_count }})</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Category -->
            <div class="mt-4">
                <a
                    href="{{ route('marketplace.browse.category', $extension->category->slug) }}"
                    class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    <span class="icon-tag text-xs"></span>
                    {{ $extension->category->name }}
                </a>
            </div>
        </div>

        <!-- Price and Action -->
        <div class="flex items-center justify-between border-t border-gray-200 p-4 dark:border-gray-800">
            <div>
                @if($extension->price > 0)
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        ${{ number_format($extension->price, 2) }}
                    </p>
                @else
                    <p class="text-lg font-bold text-green-600 dark:text-green-400">
                        Free
                    </p>
                @endif
            </div>

            <a
                href="{{ route('marketplace.extension.show', $extension->slug) }}"
                class="primary-button"
            >
                View Details
            </a>
        </div>
    </div>
@else
    <!-- List Card View -->
    <div class="flex flex-col rounded-lg border border-gray-200 bg-white transition-all hover:shadow-lg dark:border-gray-800 dark:bg-gray-900 md:flex-row">
        <!-- Extension Logo -->
        <div class="relative flex-shrink-0 border-b border-gray-200 p-6 dark:border-gray-800 md:border-b-0 md:border-r">
            <a href="{{ route('marketplace.extension.show', $extension->slug) }}" class="flex justify-center md:block">
                @if($extension->logo)
                    <img
                        src="{{ Storage::url($extension->logo) }}"
                        alt="{{ $extension->name }}"
                        class="h-24 w-24 rounded-lg object-cover"
                    />
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30">
                        <span class="icon-package text-4xl text-blue-600 dark:text-blue-400"></span>
                    </div>
                @endif
            </a>

            <!-- Featured Badge -->
            @if($extension->is_featured)
                <span class="absolute left-6 top-6 flex items-center gap-1 rounded-full bg-gradient-to-r from-yellow-400 to-orange-400 px-2 py-1 text-xs font-medium text-white">
                    <span class="icon-star text-xs"></span>
                    Featured
                </span>
            @endif
        </div>

        <!-- Extension Details -->
        <div class="flex flex-1 flex-col p-6">
            <div class="flex flex-1 flex-col">
                <div class="mb-3 flex items-start justify-between">
                    <div class="flex-1">
                        <a href="{{ route('marketplace.extension.show', $extension->slug) }}">
                            <h3 class="text-xl font-semibold dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                {{ $extension->name }}
                            </h3>
                        </a>
                        <div class="mt-1 flex items-center gap-3">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst($extension->type) }}
                            </span>
                            <span class="text-gray-300 dark:text-gray-600">•</span>
                            <a
                                href="{{ route('marketplace.browse.category', $extension->category->slug) }}"
                                class="text-sm text-blue-600 hover:underline dark:text-blue-400"
                            >
                                {{ $extension->category->name }}
                            </a>
                        </div>
                    </div>

                    <div class="text-right">
                        @if($extension->price > 0)
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                ${{ number_format($extension->price, 2) }}
                            </p>
                        @else
                            <p class="text-xl font-bold text-green-600 dark:text-green-400">
                                Free
                            </p>
                        @endif
                    </div>
                </div>

                <p class="line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ $extension->description }}
                </p>

                <!-- Stats -->
                <div class="mt-4 flex flex-wrap items-center gap-6">
                    <!-- Downloads -->
                    <div class="flex items-center gap-2">
                        <span class="icon-download text-blue-600 dark:text-blue-400"></span>
                        <span class="text-sm font-medium dark:text-white">
                            {{ number_format($extension->downloads_count) }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            downloads
                        </span>
                    </div>

                    <!-- Rating -->
                    <div class="flex items-center gap-2">
                        <span class="icon-star text-yellow-500"></span>
                        <span class="text-sm font-semibold dark:text-white">
                            {{ number_format($extension->average_rating, 1) }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            ({{ number_format($extension->reviews_count) }} reviews)
                        </span>
                    </div>

                    <!-- Last Updated -->
                    <div class="flex items-center gap-2">
                        <span class="icon-clock text-gray-400"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Updated {{ $extension->updated_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-4 flex gap-3">
                <a
                    href="{{ route('marketplace.extension.show', $extension->slug) }}"
                    class="primary-button"
                >
                    View Details
                </a>
                @if($extension->price == 0)
                    <button
                        onclick="window.location.href = '{{ route('marketplace.extension.show', $extension->slug) }}'"
                        class="secondary-button"
                    >
                        Install Now
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
