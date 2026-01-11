<x-admin::layouts>
    <x-slot:title>
        {{ $extension->name }}
    </x-slot>

    {!! view_render_event('marketplace.developer.extensions.show.header.before', ['extension' => $extension]) !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.extensions.show.header.left.before', ['extension' => $extension]) !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                {{ $extension->name }}
            </p>
            <div class="flex items-center gap-2">
                <span class="rounded-full px-2 py-1 text-xs font-medium
                    @if($extension->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                    @elseif($extension->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                    @elseif($extension->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                    @endif">
                    {{ ucfirst($extension->status) }}
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-300">
                    {{ ucfirst($extension->type) }}
                </span>
            </div>
        </div>

        {!! view_render_event('marketplace.developer.extensions.show.header.left.after', ['extension' => $extension]) !!}

        {!! view_render_event('marketplace.developer.extensions.show.header.right.before', ['extension' => $extension]) !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.extensions.edit', $extension->id) }}"
                class="secondary-button"
            >
                @lang('marketplace::app.developer.extensions.show.edit-btn')
            </a>

            @if($extension->status === 'draft')
                <button
                    type="button"
                    class="danger-button"
                    @click="$refs.deleteExtensionModal.open()"
                >
                    @lang('marketplace::app.developer.extensions.show.delete-btn')
                </button>
            @endif
        </div>

        {!! view_render_event('marketplace.developer.extensions.show.header.right.after', ['extension' => $extension]) !!}
    </div>

    {!! view_render_event('marketplace.developer.extensions.show.header.after', ['extension' => $extension]) !!}

    {!! view_render_event('marketplace.developer.extensions.show.content.before', ['extension' => $extension]) !!}

    <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
        <!-- Left Section -->
        {!! view_render_event('marketplace.developer.extensions.show.content.left.before', ['extension' => $extension]) !!}

        <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-4 gap-4 max-lg:grid-cols-2 max-sm:grid-cols-1">
                <!-- Downloads -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.extensions.show.stats.downloads')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-download text-2xl text-blue-600 dark:text-blue-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            {{ number_format($extension->downloads_count) }}
                        </p>
                    </div>
                </div>

                <!-- Rating -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.extensions.show.stats.rating')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-star text-2xl text-yellow-500 dark:text-yellow-400"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            {{ number_format($extension->average_rating, 1) }}
                        </p>
                        <span class="text-sm text-gray-500 dark:text-gray-400">/ 5.0</span>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.extensions.show.stats.reviews')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-message-square text-2xl text-purple-600 dark:text-purple-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            {{ $extension->reviews()->count() }}
                        </p>
                    </div>
                </div>

                <!-- Versions -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.extensions.show.stats.versions')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-package text-2xl text-green-600 dark:text-green-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            {{ $extension->versions()->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Extension Details -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start gap-4">
                    @if($extension->logo)
                        <img
                            src="{{ Storage::url($extension->logo) }}"
                            alt="{{ $extension->name }}"
                            class="h-24 w-24 rounded-lg object-cover"
                        />
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                            <span class="icon-package text-4xl text-gray-400 dark:text-gray-600"></span>
                        </div>
                    @endif

                    <div class="flex-1">
                        <h2 class="text-xl font-semibold dark:text-white">
                            {{ $extension->name }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ $extension->description }}
                        </p>

                        <div class="mt-3 flex items-center gap-4">
                            <div class="flex items-center gap-1">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.developer.extensions.show.category'):
                                </span>
                                <span class="text-sm font-medium dark:text-white">
                                    {{ $extension->category?->name }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.developer.extensions.show.price'):
                                </span>
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                    @if($extension->price > 0)
                                        ${{ number_format($extension->price, 2) }}
                                    @else
                                        @lang('marketplace::app.developer.extensions.show.free')
                                    @endif
                                </span>
                            </div>

                            @if($extension->tags)
                                <div class="flex items-center gap-1">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        @lang('marketplace::app.developer.extensions.show.tags'):
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($extension->tags as $tag)
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-800 dark:text-gray-300">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($extension->long_description)
                    <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
                        <h3 class="mb-2 text-base font-semibold dark:text-white">
                            @lang('marketplace::app.developer.extensions.show.description')
                        </h3>
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            {{ $extension->long_description }}
                        </div>
                    </div>
                @endif

                @if($extension->documentation_url || $extension->demo_url || $extension->repository_url || $extension->support_email)
                    <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
                        <h3 class="mb-3 text-base font-semibold dark:text-white">
                            @lang('marketplace::app.developer.extensions.show.links')
                        </h3>
                        <div class="grid grid-cols-2 gap-3 max-sm:grid-cols-1">
                            @if($extension->documentation_url)
                                <a
                                    href="{{ $extension->documentation_url }}"
                                    target="_blank"
                                    class="flex items-center gap-2 text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    <span class="icon-book"></span>
                                    @lang('marketplace::app.developer.extensions.show.documentation')
                                </a>
                            @endif

                            @if($extension->demo_url)
                                <a
                                    href="{{ $extension->demo_url }}"
                                    target="_blank"
                                    class="flex items-center gap-2 text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    <span class="icon-external-link"></span>
                                    @lang('marketplace::app.developer.extensions.show.demo')
                                </a>
                            @endif

                            @if($extension->repository_url)
                                <a
                                    href="{{ $extension->repository_url }}"
                                    target="_blank"
                                    class="flex items-center gap-2 text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    <span class="icon-github"></span>
                                    @lang('marketplace::app.developer.extensions.show.repository')
                                </a>
                            @endif

                            @if($extension->support_email)
                                <a
                                    href="mailto:{{ $extension->support_email }}"
                                    class="flex items-center gap-2 text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    <span class="icon-mail"></span>
                                    @lang('marketplace::app.developer.extensions.show.support')
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Recent Versions -->
            @if($extension->versions->isNotEmpty())
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold dark:text-white">
                            @lang('marketplace::app.developer.extensions.show.recent-versions')
                        </h3>
                        <a
                            href="{{ route('developer.marketplace.versions.index', $extension->id) }}"
                            class="text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            @lang('marketplace::app.developer.extensions.show.view-all-versions')
                        </a>
                    </div>

                    <div class="space-y-3">
                        @foreach($extension->versions->take(5) as $version)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold dark:text-white">
                                            v{{ $version->version }}
                                        </span>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                            @if($version->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($version->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                            @endif">
                                            {{ ucfirst($version->status) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $version->changelog ?? 'No changelog' }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                        {{ $version->created_at->format('M d, Y') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('developer.marketplace.versions.show', $version->id) }}"
                                        class="secondary-button"
                                    >
                                        @lang('marketplace::app.developer.extensions.show.view')
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Reviews -->
            @if($extension->reviews->isNotEmpty())
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold dark:text-white">
                            @lang('marketplace::app.developer.extensions.show.recent-reviews')
                        </h3>
                    </div>

                    <div class="space-y-4">
                        @foreach($extension->reviews->take(5) as $review)
                            <div class="border-b border-gray-200 pb-4 last:border-b-0 dark:border-gray-800">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold dark:text-white">
                                                {{ $review->user->name }}
                                            </span>
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="icon-star text-sm {{ $i <= $review->rating ? 'text-yellow-500' : 'text-gray-300' }}"></span>
                                                @endfor
                                            </div>
                                        </div>
                                        @if($review->title)
                                            <p class="mt-1 font-medium dark:text-white">
                                                {{ $review->title }}
                                            </p>
                                        @endif
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $review->review_text }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                            {{ $review->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {!! view_render_event('marketplace.developer.extensions.show.content.left.after', ['extension' => $extension]) !!}

        <!-- Right Section -->
        {!! view_render_event('marketplace.developer.extensions.show.content.right.before', ['extension' => $extension]) !!}

        <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
            <!-- Quick Actions -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.extensions.show.quick-actions.title')
                </p>

                <div class="flex flex-col gap-2">
                    <a
                        href="{{ route('developer.marketplace.versions.create', $extension->id) }}"
                        class="secondary-button text-center"
                    >
                        @lang('marketplace::app.developer.extensions.show.quick-actions.add-version')
                    </a>

                    <a
                        href="{{ route('developer.marketplace.versions.index', $extension->id) }}"
                        class="secondary-button text-center"
                    >
                        @lang('marketplace::app.developer.extensions.show.quick-actions.manage-versions')
                    </a>

                    <a
                        href="{{ route('developer.marketplace.submissions.by_extension', $extension->id) }}"
                        class="secondary-button text-center"
                    >
                        @lang('marketplace::app.developer.extensions.show.quick-actions.view-submissions')
                    </a>

                    <a
                        href="{{ route('developer.marketplace.extensions.analytics', $extension->id) }}"
                        class="secondary-button text-center"
                    >
                        @lang('marketplace::app.developer.extensions.show.quick-actions.view-analytics')
                    </a>
                </div>
            </div>

            <!-- Information -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.extensions.show.information.title')
                </p>

                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.extensions.show.information.slug')
                        </span>
                        <span class="text-sm font-medium dark:text-white">
                            {{ $extension->slug }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.extensions.show.information.created')
                        </span>
                        <span class="text-sm font-medium dark:text-white">
                            {{ $extension->created_at->format('M d, Y') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.developer.extensions.show.information.updated')
                        </span>
                        <span class="text-sm font-medium dark:text-white">
                            {{ $extension->updated_at->format('M d, Y') }}
                        </span>
                    </div>

                    @if($extension->featured)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.developer.extensions.show.information.featured')
                            </span>
                            <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                @lang('marketplace::app.developer.extensions.show.information.yes')
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {!! view_render_event('marketplace.developer.extensions.show.content.right.after', ['extension' => $extension]) !!}
    </div>

    {!! view_render_event('marketplace.developer.extensions.show.content.after', ['extension' => $extension]) !!}

    <!-- Delete Confirmation Modal -->
    <x-admin::form
        method="DELETE"
        :action="route('developer.marketplace.extensions.destroy', $extension->id)"
    >
        <x-admin::modal ref="deleteExtensionModal">
            <x-slot:header>
                <h3 class="text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.developer.extensions.show.delete-modal.title')
                </h3>
            </x-slot>

            <x-slot:content>
                <p class="text-gray-600 dark:text-gray-300">
                    @lang('marketplace::app.developer.extensions.show.delete-modal.message')
                </p>
                <p class="mt-2 font-semibold dark:text-white">
                    {{ $extension->name }}
                </p>
            </x-slot>

            <x-slot:footer>
                <button
                    type="button"
                    class="secondary-button"
                    @click="$refs.deleteExtensionModal.close()"
                >
                    @lang('marketplace::app.developer.extensions.show.delete-modal.cancel')
                </button>

                <button
                    type="submit"
                    class="danger-button"
                >
                    @lang('marketplace::app.developer.extensions.show.delete-modal.confirm')
                </button>
            </x-slot>
        </x-admin::modal>
    </x-admin::form>
</x-admin::layouts>
