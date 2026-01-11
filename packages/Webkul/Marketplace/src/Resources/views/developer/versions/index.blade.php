<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.versions.index.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.versions.index.header.before', ['extension' => $extension]) !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.versions.index.header.left.before', ['extension' => $extension]) !!}

        <div class="grid gap-1.5">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('developer.marketplace.extensions.show', $extension->id) }}"
                    class="text-blue-600 hover:underline dark:text-blue-400"
                >
                    {{ $extension->name }}
                </a>
                <span class="text-gray-400">/</span>
                <p class="text-2xl font-semibold dark:text-white">
                    @lang('marketplace::app.developer.versions.index.title')
                </p>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.developer.versions.index.description')
            </p>
        </div>

        {!! view_render_event('marketplace.developer.versions.index.header.left.after', ['extension' => $extension]) !!}

        {!! view_render_event('marketplace.developer.versions.index.header.right.before', ['extension' => $extension]) !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.extensions.show', $extension->id) }}"
                class="secondary-button"
            >
                @lang('marketplace::app.developer.versions.index.back-btn')
            </a>

            <a
                href="{{ route('developer.marketplace.versions.create', $extension->id) }}"
                class="primary-button"
            >
                @lang('marketplace::app.developer.versions.index.create-btn')
            </a>
        </div>

        {!! view_render_event('marketplace.developer.versions.index.header.right.after', ['extension' => $extension]) !!}
    </div>

    {!! view_render_event('marketplace.developer.versions.index.header.after', ['extension' => $extension]) !!}

    {!! view_render_event('marketplace.developer.versions.index.content.before', ['extension' => $extension]) !!}

    <div class="flex flex-col gap-4">
        @if($versions->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-12 dark:border-gray-800 dark:bg-gray-900">
                <span class="icon-package text-6xl text-gray-400 dark:text-gray-600"></span>

                <p class="mt-4 text-xl font-semibold dark:text-white">
                    @lang('marketplace::app.developer.versions.index.empty.title')
                </p>

                <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.developer.versions.index.empty.description')
                </p>

                <a
                    href="{{ route('developer.marketplace.versions.create', $extension->id) }}"
                    class="primary-button mt-6"
                >
                    @lang('marketplace::app.developer.versions.index.empty.create-first')
                </a>
            </div>
        @else
            <!-- Versions List -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    @lang('marketplace::app.developer.versions.index.table.version')
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    @lang('marketplace::app.developer.versions.index.table.status')
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    @lang('marketplace::app.developer.versions.index.table.compatibility')
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    @lang('marketplace::app.developer.versions.index.table.package')
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    @lang('marketplace::app.developer.versions.index.table.release-date')
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    @lang('marketplace::app.developer.versions.index.table.actions')
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($versions as $version)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-950">
                                    <!-- Version Number -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1">
                                            <span class="font-semibold text-gray-900 dark:text-white">
                                                v{{ $version->version }}
                                            </span>
                                            @if($version->changelog)
                                                <span class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                                    {{ Str::limit(strip_tags($version->changelog), 50) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                                            @if($version->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($version->status === 'pending') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                            @elseif($version->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                            @endif">
                                            {{ ucfirst($version->status) }}
                                        </span>
                                    </td>

                                    <!-- Compatibility -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1 text-xs text-gray-600 dark:text-gray-300">
                                            @if($version->laravel_version)
                                                <span>Laravel: {{ $version->laravel_version }}</span>
                                            @endif
                                            @if($version->crm_version)
                                                <span>CRM: {{ $version->crm_version }}</span>
                                            @endif
                                            @if($version->php_version)
                                                <span>PHP: {{ $version->php_version }}</span>
                                            @endif
                                            @if(!$version->laravel_version && !$version->crm_version && !$version->php_version)
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Package -->
                                    <td class="px-4 py-3">
                                        @if($version->file_path)
                                            <div class="flex items-center gap-2">
                                                <span class="icon-download text-green-600 dark:text-green-400"></span>
                                                <div class="text-xs">
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        {{ number_format($version->file_size / 1024 / 1024, 2) }} MB
                                                    </p>
                                                    @if($version->checksum)
                                                        <p class="text-gray-500 dark:text-gray-400">
                                                            {{ Str::limit($version->checksum, 8) }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-red-600 dark:text-red-400">
                                                @lang('marketplace::app.developer.versions.index.table.no-package')
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Release Date -->
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $version->release_date ? $version->release_date->format('M d, Y') : '—' }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a
                                                href="{{ route('developer.marketplace.versions.show', $version->id) }}"
                                                class="icon-view cursor-pointer text-xl text-gray-600 transition-all hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400"
                                                title="@lang('marketplace::app.developer.versions.index.table.view')"
                                            ></a>

                                            @if($version->status !== 'approved')
                                                <a
                                                    href="{{ route('developer.marketplace.versions.edit', $version->id) }}"
                                                    class="icon-edit cursor-pointer text-xl text-gray-600 transition-all hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400"
                                                    title="@lang('marketplace::app.developer.versions.index.table.edit')"
                                                ></a>

                                                <form
                                                    action="{{ route('developer.marketplace.versions.destroy', $version->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('@lang('marketplace::app.developer.versions.index.table.delete-confirm')')"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="icon-delete cursor-pointer text-xl text-gray-600 transition-all hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400"
                                                        title="@lang('marketplace::app.developer.versions.index.table.delete')"
                                                    ></button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                                    @lang('marketplace::app.developer.versions.index.table.locked')
                                                </span>
                                            @endif

                                            @if($version->file_path)
                                                <a
                                                    href="{{ route('developer.marketplace.versions.download_package', $version->id) }}"
                                                    class="icon-download cursor-pointer text-xl text-gray-600 transition-all hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400"
                                                    title="@lang('marketplace::app.developer.versions.index.table.download')"
                                                ></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($versions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="border-t border-gray-200 p-4 dark:border-gray-800">
                        {{ $versions->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    {!! view_render_event('marketplace.developer.versions.index.content.after', ['extension' => $extension]) !!}
</x-admin::layouts>
