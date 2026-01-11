<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.extensions.index.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.extensions.index.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.extensions.index.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.developer.extensions.index.title')
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.developer.extensions.index.description')
            </p>
        </div>

        {!! view_render_event('marketplace.developer.extensions.index.header.left.after') !!}

        {!! view_render_event('marketplace.developer.extensions.index.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.extensions.create') }}"
                class="primary-button"
            >
                @lang('marketplace::app.developer.extensions.index.create-btn')
            </a>
        </div>

        {!! view_render_event('marketplace.developer.extensions.index.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.extensions.index.header.after') !!}

    {!! view_render_event('marketplace.developer.extensions.index.content.before') !!}

    <div class="flex flex-col gap-4">
        @if($extensions->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-12 dark:border-gray-800 dark:bg-gray-900">
                <span class="icon-package text-6xl text-gray-400 dark:text-gray-600"></span>

                <p class="mt-4 text-xl font-semibold dark:text-white">
                    @lang('marketplace::app.developer.extensions.index.empty.title')
                </p>

                <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.extensions.index.empty.description')
                </p>

                <a
                    href="{{ route('developer.marketplace.extensions.create') }}"
                    class="primary-button mt-6"
                >
                    @lang('marketplace::app.developer.extensions.index.empty.create-first')
                </a>
            </div>
        @else
            <!-- Extensions Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($extensions as $extension)
                    <div class="flex flex-col rounded-lg border border-gray-200 bg-white transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                        <!-- Extension Logo/Header -->
                        <div class="relative flex items-center gap-4 border-b border-gray-200 p-4 dark:border-gray-800">
                            @if($extension->logo)
                                <img
                                    src="{{ Storage::url($extension->logo) }}"
                                    alt="{{ $extension->name }}"
                                    class="h-16 w-16 rounded-lg object-cover"
                                />
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                                    <span class="icon-package text-2xl text-gray-400 dark:text-gray-600"></span>
                                </div>
                            @endif

                            <div class="flex-1">
                                <h3 class="text-base font-semibold dark:text-white">
                                    {{ $extension->name }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ ucfirst($extension->type) }}
                                </p>
                            </div>

                            <!-- Status Badge -->
                            <span class="absolute right-4 top-4 rounded-full px-2 py-1 text-xs font-medium
                                @if($extension->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                @elseif($extension->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                @elseif($extension->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                @endif">
                                {{ ucfirst($extension->status) }}
                            </span>
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
                                            @lang('marketplace::app.developer.extensions.index.downloads')
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
                                            @lang('marketplace::app.developer.extensions.index.rating')
                                        </p>
                                        <p class="text-sm font-semibold dark:text-white">
                                            {{ number_format($extension->average_rating, 1) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="mt-4">
                                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                    @if($extension->price > 0)
                                        ${{ number_format($extension->price, 2) }}
                                    @else
                                        @lang('marketplace::app.developer.extensions.index.free')
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 border-t border-gray-200 p-4 dark:border-gray-800">
                            <a
                                href="{{ route('developer.marketplace.extensions.show', $extension->id) }}"
                                class="secondary-button flex-1 text-center"
                            >
                                @lang('marketplace::app.developer.extensions.index.view')
                            </a>

                            <a
                                href="{{ route('developer.marketplace.extensions.edit', $extension->id) }}"
                                class="primary-button flex-1 text-center"
                            >
                                @lang('marketplace::app.developer.extensions.index.edit')
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $extensions->links() }}
            </div>
        @endif
    </div>

    {!! view_render_event('marketplace.developer.extensions.index.content.after') !!}
</x-admin::layouts>
