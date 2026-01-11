<x-admin::layouts>
    <x-slot:title>
        {{ $extension->name }} - @lang('marketplace::app.marketplace.detail.title')
    </x-slot>

    {!! view_render_event('marketplace.extension.detail.before', ['extension' => $extension]) !!}

    <div class="mb-5">
        {!! view_render_event('marketplace.extension.detail.header.before', ['extension' => $extension]) !!}

        <!-- Breadcrumbs -->
        <div class="mb-4">
            <nav class="flex text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('marketplace.browse.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                    @lang('marketplace::app.marketplace.detail.breadcrumbs.home')
                </a>
                <span class="mx-2">/</span>
                <a href="{{ route('marketplace.browse.category', $extension->category->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                    {{ $extension->category->name }}
                </a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-white">{{ $extension->name }}</span>
            </nav>
        </div>

        {!! view_render_event('marketplace.extension.detail.header.after', ['extension' => $extension]) !!}
    </div>

    <div class="flex gap-6 max-lg:flex-wrap">
        <!-- Main Content -->
        <div class="flex-1 min-w-0">
            {!! view_render_event('marketplace.extension.detail.main.before', ['extension' => $extension]) !!}

            <!-- Extension Header -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex gap-6 max-md:flex-col">
                    <!-- Extension Logo -->
                    <div class="flex-shrink-0">
                        @if($extension->logo)
                            <img
                                src="{{ Storage::url($extension->logo) }}"
                                alt="{{ $extension->name }}"
                                class="h-32 w-32 rounded-lg object-cover"
                            />
                        @else
                            <div class="flex h-32 w-32 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30">
                                <span class="icon-package text-5xl text-blue-600 dark:text-blue-400"></span>
                            </div>
                        @endif
                    </div>

                    <!-- Extension Info -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4 max-md:flex-col">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h1 class="text-3xl font-bold dark:text-white">
                                        {{ $extension->name }}
                                    </h1>
                                    @if($extension->is_featured)
                                        <span class="flex items-center gap-1 rounded-full bg-gradient-to-r from-yellow-400 to-orange-400 px-3 py-1 text-sm font-medium text-white">
                                            <span class="icon-star text-sm"></span>
                                            @lang('marketplace::app.marketplace.detail.featured')
                                        </span>
                                    @endif
                                </div>

                                <p class="text-lg text-gray-600 dark:text-gray-300 mb-4">
                                    {{ $extension->description }}
                                </p>

                                <div class="flex flex-wrap items-center gap-4">
                                    <!-- Author -->
                                    <div class="flex items-center gap-2">
                                        <span class="icon-user text-gray-500 dark:text-gray-400"></span>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('marketplace::app.marketplace.detail.by-author', ['author' => $extension->author->name])
                                        </span>
                                    </div>

                                    <!-- Type -->
                                    <div class="flex items-center gap-2">
                                        <span class="icon-tag text-gray-500 dark:text-gray-400"></span>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ ucfirst($extension->type) }}
                                        </span>
                                    </div>

                                    <!-- Category -->
                                    <a
                                        href="{{ route('marketplace.browse.category', $extension->category->slug) }}"
                                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        <span class="icon-category text-gray-500 dark:text-gray-400"></span>
                                        {{ $extension->category->name }}
                                    </a>
                                </div>
                            </div>

                            <!-- Price & Action -->
                            <div class="flex flex-col items-end gap-3 max-md:items-start max-md:w-full">
                                <div class="text-right max-md:text-left">
                                    @if($extension->price > 0)
                                        <p class="text-4xl font-bold text-green-600 dark:text-green-400">
                                            ${{ number_format($extension->price, 2) }}
                                        </p>
                                    @else
                                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                            @lang('marketplace::app.marketplace.detail.free')
                                        </p>
                                    @endif
                                </div>

                                @if($isInstalled)
                                    <button
                                        type="button"
                                        class="secondary-button w-full min-w-[200px]"
                                        disabled
                                    >
                                        <span class="icon-checkmark mr-2"></span>
                                        @lang('marketplace::app.marketplace.detail.installed')
                                    </button>
                                @else
                                    @auth
                                        @if($extension->price > 0)
                                            <form action="{{ route('marketplace.install.extension', $extension->id) }}" method="POST" class="w-full">
                                                @csrf
                                                <button type="submit" class="primary-button w-full min-w-[200px]">
                                                    <span class="icon-cart mr-2"></span>
                                                    @lang('marketplace::app.marketplace.detail.purchase')
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('marketplace.install.extension', $extension->id) }}" method="POST" class="w-full">
                                                @csrf
                                                <button type="submit" class="primary-button w-full min-w-[200px]">
                                                    <span class="icon-download mr-2"></span>
                                                    @lang('marketplace::app.marketplace.detail.install')
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('admin.session.create') }}" class="primary-button w-full min-w-[200px] text-center">
                                            <span class="icon-login mr-2"></span>
                                            @lang('marketplace::app.marketplace.detail.login-to-install')
                                        </a>
                                    @endauth
                                @endif

                                @if(!$isCompatible && count($compatibilityIssues) > 0)
                                    <p class="text-sm text-red-600 dark:text-red-400">
                                        <span class="icon-warning mr-1"></span>
                                        @lang('marketplace::app.marketplace.detail.incompatible')
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Bar -->
                <div class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-200 pt-6 dark:border-gray-700 md:grid-cols-4">
                    <!-- Downloads -->
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($extension->downloads_count) }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.marketplace.detail.downloads')
                        </p>
                    </div>

                    <!-- Rating -->
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="icon-star text-2xl text-yellow-500"></span>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($extension->average_rating, 1) }}
                            </p>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ number_format($extension->reviews_count) }} @lang('marketplace::app.marketplace.detail.reviews')
                        </p>
                    </div>

                    <!-- Version -->
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $latestVersion?->version ?? 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.marketplace.detail.latest-version')
                        </p>
                    </div>

                    <!-- Updated -->
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $extension->updated_at->diffForHumans() }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.marketplace.detail.last-updated')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Screenshots Carousel -->
            @if($extension->screenshots && count($extension->screenshots) > 0)
                <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    {!! view_render_event('marketplace.extension.detail.screenshots.before', ['extension' => $extension]) !!}

                    <h2 class="mb-4 text-xl font-bold dark:text-white">
                        @lang('marketplace::app.marketplace.detail.screenshots')
                    </h2>

                    <div x-data="screenshotCarousel()" class="relative">
                        <!-- Main Screenshot -->
                        <div class="overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                            <img
                                :src="currentScreenshot"
                                :alt="'Screenshot ' + (currentIndex + 1)"
                                class="h-[500px] w-full object-contain"
                            />
                        </div>

                        <!-- Navigation Arrows -->
                        @if(count($extension->screenshots) > 1)
                            <button
                                @click="previous"
                                class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-3 text-white transition-all hover:bg-black/70"
                            >
                                <span class="icon-arrow-left text-2xl"></span>
                            </button>
                            <button
                                @click="next"
                                class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-3 text-white transition-all hover:bg-black/70"
                            >
                                <span class="icon-arrow-right text-2xl"></span>
                            </button>
                        @endif

                        <!-- Thumbnails -->
                        @if(count($extension->screenshots) > 1)
                            <div class="mt-4 flex gap-2 overflow-x-auto pb-2">
                                @foreach($extension->screenshots as $index => $screenshot)
                                    <button
                                        @click="currentIndex = {{ $index }}"
                                        :class="currentIndex === {{ $index }} ? 'ring-2 ring-blue-500' : 'opacity-60 hover:opacity-100'"
                                        class="flex-shrink-0 overflow-hidden rounded-lg transition-all"
                                    >
                                        <img
                                            src="{{ Storage::url($screenshot) }}"
                                            alt="Screenshot {{ $index + 1 }}"
                                            class="h-20 w-32 object-cover"
                                        />
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <!-- Screenshot Counter -->
                        <div class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                            <span x-text="currentIndex + 1"></span> / {{ count($extension->screenshots) }}
                        </div>
                    </div>

                    {!! view_render_event('marketplace.extension.detail.screenshots.after', ['extension' => $extension]) !!}
                </div>
            @endif

            <!-- Description -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                {!! view_render_event('marketplace.extension.detail.description.before', ['extension' => $extension]) !!}

                <h2 class="mb-4 text-xl font-bold dark:text-white">
                    @lang('marketplace::app.marketplace.detail.description')
                </h2>

                <div class="prose prose-blue max-w-none dark:prose-invert">
                    {!! nl2br(e($extension->long_description ?: $extension->description)) !!}
                </div>

                {!! view_render_event('marketplace.extension.detail.description.after', ['extension' => $extension]) !!}
            </div>

            <!-- Version History -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                {!! view_render_event('marketplace.extension.detail.versions.before', ['extension' => $extension]) !!}

                <h2 class="mb-4 text-xl font-bold dark:text-white">
                    @lang('marketplace::app.marketplace.detail.version-history')
                </h2>

                @if($versions->count() > 0)
                    <div class="space-y-4">
                        @foreach($versions->take(5) as $version)
                            <div class="border-l-4 border-blue-500 bg-gray-50 p-4 dark:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">
                                            {{ $version->version }}
                                            @if($loop->first)
                                                <span class="ml-2 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    @lang('marketplace::app.marketplace.detail.latest')
                                                </span>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $version->release_date ? $version->release_date->format('F d, Y') : 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ number_format($version->downloads) }} downloads
                                    </div>
                                </div>
                                @if($version->changelog)
                                    <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                        {!! nl2br(e($version->changelog)) !!}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($versions->count() > 5)
                        <div class="mt-4 text-center">
                            <button
                                type="button"
                                class="text-blue-600 hover:underline dark:text-blue-400"
                                @click="loadAllVersions"
                            >
                                @lang('marketplace::app.marketplace.detail.view-all-versions')
                            </button>
                        </div>
                    @endif
                @else
                    <p class="text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.detail.no-versions')
                    </p>
                @endif

                {!! view_render_event('marketplace.extension.detail.versions.after', ['extension' => $extension]) !!}
            </div>

            <!-- Reviews Section -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                {!! view_render_event('marketplace.extension.detail.reviews.before', ['extension' => $extension]) !!}

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold dark:text-white">
                        @lang('marketplace::app.marketplace.detail.reviews-ratings')
                    </h2>

                    @auth
                        @if(!$hasReviewed && !$isInstalled)
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.marketplace.detail.install-to-review')
                            </p>
                        @elseif(!$hasReviewed)
                            <button
                                type="button"
                                class="secondary-button"
                                @click="$refs.reviewModal.open()"
                            >
                                @lang('marketplace::app.marketplace.detail.write-review')
                            </button>
                        @endif
                    @endauth
                </div>

                <!-- Rating Summary -->
                <div class="mb-6 grid gap-6 md:grid-cols-2">
                    <!-- Overall Rating -->
                    <div class="flex items-center gap-6">
                        <div class="text-center">
                            <p class="text-5xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($extension->average_rating, 1) }}
                            </p>
                            <div class="mt-2 flex justify-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="icon-star {{ $i <= round($extension->average_rating) ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' }}"></span>
                                @endfor
                            </div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($extension->reviews_count) }} @lang('marketplace::app.marketplace.detail.reviews')
                            </p>
                        </div>
                    </div>

                    <!-- Rating Distribution -->
                    <div class="space-y-2">
                        @for($rating = 5; $rating >= 1; $rating--)
                            @php
                                $count = $reviewStatistics['rating_distribution'][$rating] ?? 0;
                                $percentage = $extension->reviews_count > 0 ? ($count / $extension->reviews_count) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400 w-8">{{ $rating }} <span class="icon-star text-xs text-yellow-500"></span></span>
                                <div class="flex-1 h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                                    <div class="h-2 bg-yellow-500 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 w-12 text-right">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Reviews List -->
                @if($reviews->count() > 0)
                    <div class="space-y-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                        @foreach($reviews as $review)
                            @include('marketplace::marketplace.partials.review-item', ['review' => $review])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                @else
                    <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                        <p class="text-center text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.marketplace.detail.no-reviews')
                        </p>
                    </div>
                @endif

                {!! view_render_event('marketplace.extension.detail.reviews.after', ['extension' => $extension]) !!}
            </div>

            {!! view_render_event('marketplace.extension.detail.main.after', ['extension' => $extension]) !!}
        </div>

        <!-- Sidebar -->
        <div class="w-full lg:w-[320px] lg:sticky lg:top-4 lg:self-start">
            {!! view_render_event('marketplace.extension.detail.sidebar.before', ['extension' => $extension]) !!}

            <!-- Compatibility Information -->
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-semibold dark:text-white">
                    @lang('marketplace::app.marketplace.detail.compatibility')
                </h3>

                @if($latestVersion && $compatibilityInfo)
                    <div class="space-y-3 text-sm">
                        @if($compatibilityInfo['laravel_version'])
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Laravel:</span>
                                <span class="font-medium dark:text-white">{{ $compatibilityInfo['laravel_version'] }}</span>
                            </div>
                        @endif
                        @if($compatibilityInfo['crm_version'])
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">CRM:</span>
                                <span class="font-medium dark:text-white">{{ $compatibilityInfo['crm_version'] }}</span>
                            </div>
                        @endif
                        @if($compatibilityInfo['php_version'])
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">PHP:</span>
                                <span class="font-medium dark:text-white">{{ $compatibilityInfo['php_version'] }}</span>
                            </div>
                        @endif

                        @if(!$isCompatible && count($compatibilityIssues) > 0)
                            <div class="mt-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                                <p class="font-medium text-red-800 dark:text-red-300 mb-2">
                                    <span class="icon-warning mr-1"></span>
                                    @lang('marketplace::app.marketplace.detail.compatibility-issues')
                                </p>
                                <ul class="list-disc list-inside space-y-1 text-xs text-red-700 dark:text-red-400">
                                    @foreach($compatibilityIssues as $issue)
                                        <li>{{ $issue['message'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="mt-4 rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                    <span class="icon-checkmark mr-1"></span>
                                    @lang('marketplace::app.marketplace.detail.compatible')
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.detail.no-compatibility-info')
                    </p>
                @endif
            </div>

            <!-- Additional Links -->
            <div class="mt-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-semibold dark:text-white">
                    @lang('marketplace::app.marketplace.detail.resources')
                </h3>

                <div class="space-y-2">
                    @if($extension->documentation_url)
                        <a
                            href="{{ $extension->documentation_url }}"
                            target="_blank"
                            class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            <span class="icon-book"></span>
                            @lang('marketplace::app.marketplace.detail.documentation')
                        </a>
                    @endif

                    @if($extension->demo_url)
                        <a
                            href="{{ $extension->demo_url }}"
                            target="_blank"
                            class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            <span class="icon-eye"></span>
                            @lang('marketplace::app.marketplace.detail.demo')
                        </a>
                    @endif

                    @if($extension->repository_url)
                        <a
                            href="{{ $extension->repository_url }}"
                            target="_blank"
                            class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            <span class="icon-github"></span>
                            @lang('marketplace::app.marketplace.detail.repository')
                        </a>
                    @endif

                    @if($extension->support_email)
                        <a
                            href="mailto:{{ $extension->support_email }}"
                            class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            <span class="icon-mail"></span>
                            @lang('marketplace::app.marketplace.detail.support')
                        </a>
                    @endif
                </div>
            </div>

            <!-- Tags -->
            @if($extension->tags && count($extension->tags) > 0)
                <div class="mt-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold dark:text-white">
                        @lang('marketplace::app.marketplace.detail.tags')
                    </h3>

                    <div class="flex flex-wrap gap-2">
                        @foreach($extension->tags as $tag)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Related Extensions -->
            @if($relatedExtensions->count() > 0)
                <div class="mt-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 font-semibold dark:text-white">
                        @lang('marketplace::app.marketplace.detail.related')
                    </h3>

                    <div class="space-y-3">
                        @foreach($relatedExtensions->take(3) as $related)
                            <a
                                href="{{ route('marketplace.extension.show', $related->slug) }}"
                                class="flex gap-3 rounded-lg border border-gray-200 p-3 transition-all hover:shadow-md dark:border-gray-700"
                            >
                                @if($related->logo)
                                    <img
                                        src="{{ Storage::url($related->logo) }}"
                                        alt="{{ $related->name }}"
                                        class="h-12 w-12 flex-shrink-0 rounded object-cover"
                                    />
                                @else
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded bg-gray-100 dark:bg-gray-800">
                                        <span class="icon-package text-lg text-gray-500"></span>
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <h4 class="truncate text-sm font-medium dark:text-white">
                                        {{ $related->name }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="icon-star text-xs text-yellow-500"></span>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ number_format($related->average_rating, 1) }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {!! view_render_event('marketplace.extension.detail.sidebar.after', ['extension' => $extension]) !!}
        </div>
    </div>

    {!! view_render_event('marketplace.extension.detail.after', ['extension' => $extension]) !!}

    @push('scripts')
        <script>
            function screenshotCarousel() {
                return {
                    currentIndex: 0,
                    screenshots: @json(array_map(fn($s) => Storage::url($s), $extension->screenshots ?? [])),

                    get currentScreenshot() {
                        return this.screenshots[this.currentIndex];
                    },

                    next() {
                        this.currentIndex = (this.currentIndex + 1) % this.screenshots.length;
                    },

                    previous() {
                        this.currentIndex = (this.currentIndex - 1 + this.screenshots.length) % this.screenshots.length;
                    }
                }
            }
        </script>
    @endpush
</x-admin::layouts>
