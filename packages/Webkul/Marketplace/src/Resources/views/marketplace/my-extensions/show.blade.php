<x-admin::layouts>
    <x-slot:title>
        {{ $installation->extension->name }} - @lang('marketplace::app.marketplace.my-extensions.show.title')
    </x-slot>

    {!! view_render_event('marketplace.my_extensions.show.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.my_extensions.show.header.left.before') !!}

        <div class="flex items-center gap-3">
            <a
                href="{{ route('marketplace.my_extensions.index') }}"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900"
            >
                <span class="icon-arrow-left text-xl dark:text-white"></span>
            </a>

            <div class="grid gap-1.5">
                <div class="flex items-center gap-3">
                    <p class="text-2xl font-semibold dark:text-white">
                        {{ $installation->extension->name }}
                    </p>

                    <!-- Status Badge -->
                    <span class="rounded-full px-3 py-1 text-sm font-medium
                        @if($installation->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                        @elseif($installation->status === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                        @elseif($installation->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                        @elseif($installation->status === 'updating') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                        @else bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                        @endif">
                        {{ ucfirst($installation->status) }}
                    </span>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    @lang('marketplace::app.marketplace.my-extensions.show.installed-version'): {{ $installation->version->version ?? 'N/A' }}
                </p>
            </div>
        </div>

        {!! view_render_event('marketplace.my_extensions.show.header.left.after') !!}

        {!! view_render_event('marketplace.my_extensions.show.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <!-- Enable/Disable Toggle -->
            <label class="relative inline-flex cursor-pointer items-center">
                <input
                    type="checkbox"
                    class="peer sr-only"
                    {{ $installation->status === 'active' ? 'checked' : '' }}
                    onchange="toggleExtension({{ $installation->id }}, this.checked)"
                >
                <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:peer-focus:ring-blue-800"></div>
                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                    {{ $installation->status === 'active' ? __('marketplace::app.marketplace.my-extensions.show.enabled') : __('marketplace::app.marketplace.my-extensions.show.disabled') }}
                </span>
            </label>
        </div>

        {!! view_render_event('marketplace.my_extensions.show.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.my_extensions.show.header.after') !!}

    {!! view_render_event('marketplace.my_extensions.show.content.before') !!}

    <div class="flex gap-4 max-xl:flex-wrap">
        <!-- Main Content -->
        <div class="flex flex-1 flex-col gap-4">
            <!-- Update Available Banner -->
            @if($updateAvailable)
                {!! view_render_event('marketplace.my_extensions.show.update_banner.before') !!}

                <div class="rounded-lg border border-purple-200 bg-purple-50 p-6 dark:border-purple-800 dark:bg-purple-900/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-purple-600">
                            <span class="icon-arrow-up text-2xl text-white"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold dark:text-white">
                                @lang('marketplace::app.marketplace.my-extensions.show.update-banner.title')
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                @lang('marketplace::app.marketplace.my-extensions.show.update-banner.message', [
                                    'current' => $installation->version->version ?? 'N/A',
                                    'latest' => $latestVersion->version
                                ])
                            </p>
                            <div class="mt-4 flex gap-3">
                                <button
                                    onclick="updateExtension({{ $installation->id }})"
                                    class="primary-button flex items-center gap-2"
                                >
                                    <span class="icon-arrow-up"></span>
                                    @lang('marketplace::app.marketplace.my-extensions.show.update-banner.update-now')
                                </button>
                                <a
                                    href="{{ route('marketplace.extension.changelog', $installation->extension->slug) }}"
                                    class="secondary-button"
                                    target="_blank"
                                >
                                    @lang('marketplace::app.marketplace.my-extensions.show.update-banner.view-changelog')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {!! view_render_event('marketplace.my_extensions.show.update_banner.after') !!}
            @endif

            <!-- Extension Information -->
            {!! view_render_event('marketplace.my_extensions.show.extension_info.before') !!}

            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.marketplace.my-extensions.show.extension-info.title')
                </h2>

                <div class="flex gap-6">
                    @if($installation->extension->logo)
                        <img
                            src="{{ Storage::url($installation->extension->logo) }}"
                            alt="{{ $installation->extension->name }}"
                            class="h-24 w-24 rounded-lg object-cover"
                        />
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30">
                            <span class="icon-package text-4xl text-blue-600 dark:text-blue-400"></span>
                        </div>
                    @endif

                    <div class="flex-1">
                        <p class="text-gray-600 dark:text-gray-300">
                            {{ $installation->extension->description }}
                        </p>

                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @lang('marketplace::app.marketplace.my-extensions.show.extension-info.type')
                                </p>
                                <p class="mt-1 font-medium dark:text-white">
                                    {{ ucfirst($installation->extension->type) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @lang('marketplace::app.marketplace.my-extensions.show.extension-info.category')
                                </p>
                                <p class="mt-1 font-medium dark:text-white">
                                    {{ $installation->extension->category->name ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @lang('marketplace::app.marketplace.my-extensions.show.extension-info.author')
                                </p>
                                <p class="mt-1 font-medium dark:text-white">
                                    {{ $installation->extension->author->name ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @lang('marketplace::app.marketplace.my-extensions.show.extension-info.price')
                                </p>
                                <p class="mt-1 font-medium text-green-600 dark:text-green-400">
                                    @if($installation->extension->price > 0)
                                        ${{ number_format($installation->extension->price, 2) }}
                                    @else
                                        @lang('marketplace::app.marketplace.my-extensions.show.extension-info.free')
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {!! view_render_event('marketplace.my_extensions.show.extension_info.after') !!}

            <!-- Installation Details -->
            {!! view_render_event('marketplace.my_extensions.show.installation_details.before') !!}

            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-lg font-semibold dark:text-white">
                    @lang('marketplace::app.marketplace.my-extensions.show.installation-details.title')
                </h2>

                <div class="grid grid-cols-2 gap-6 md:grid-cols-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('marketplace::app.marketplace.my-extensions.show.installation-details.installed-at')
                        </p>
                        <p class="mt-1 font-medium dark:text-white">
                            {{ $installation->installed_at->format('M d, Y') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $installation->installed_at->diffForHumans() }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('marketplace::app.marketplace.my-extensions.show.installation-details.last-updated')
                        </p>
                        <p class="mt-1 font-medium dark:text-white">
                            {{ $installation->updated_at->format('M d, Y') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $installation->updated_at->diffForHumans() }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('marketplace::app.marketplace.my-extensions.show.installation-details.auto-update')
                        </p>
                        <div class="mt-1 flex items-center gap-2">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input
                                    type="checkbox"
                                    class="peer sr-only"
                                    {{ $installation->auto_update_enabled ? 'checked' : '' }}
                                    onchange="toggleAutoUpdate({{ $installation->id }}, this.checked)"
                                >
                                <div class="peer h-5 w-9 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-gray-700"></div>
                            </label>
                            <span class="text-sm font-medium dark:text-white">
                                {{ $installation->auto_update_enabled ? __('marketplace::app.marketplace.my-extensions.show.installation-details.enabled') : __('marketplace::app.marketplace.my-extensions.show.installation-details.disabled') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {!! view_render_event('marketplace.my_extensions.show.installation_details.after') !!}

            <!-- Version History -->
            @if($versionHistory && $versionHistory->isNotEmpty())
                {!! view_render_event('marketplace.my_extensions.show.version_history.before') !!}

                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-4 text-lg font-semibold dark:text-white">
                        @lang('marketplace::app.marketplace.my-extensions.show.version-history.title')
                    </h2>

                    <div class="space-y-4">
                        @foreach($versionHistory->take(5) as $version)
                            <div class="flex items-start gap-4 border-b border-gray-100 pb-4 last:border-0 last:pb-0 dark:border-gray-800">
                                <div class="flex-shrink-0">
                                    @if($installation->version && $version->id === $installation->version->id)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            <span class="icon-check text-xs"></span>
                                            @lang('marketplace::app.marketplace.my-extensions.show.version-history.current')
                                        </span>
                                    @elseif($latestVersion && $version->id === $latestVersion->id)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                            <span class="icon-star text-xs"></span>
                                            @lang('marketplace::app.marketplace.my-extensions.show.version-history.latest')
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                            v{{ $version->version }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium dark:text-white">
                                            {{ $version->version }}
                                        </p>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            • {{ $version->release_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                    @if($version->changelog)
                                        <p class="mt-1 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                                            {{ Str::limit(strip_tags($version->changelog), 150) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($versionHistory->count() > 5)
                        <div class="mt-4 text-center">
                            <a
                                href="{{ route('marketplace.extension.versions', $installation->extension->slug) }}"
                                class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                            >
                                @lang('marketplace::app.marketplace.my-extensions.show.version-history.view-all')
                            </a>
                        </div>
                    @endif
                </div>

                {!! view_render_event('marketplace.my_extensions.show.version_history.after') !!}
            @endif
        </div>

        <!-- Sidebar -->
        <div class="w-[360px] max-w-full shrink-0 max-xl:w-full">
            <!-- Quick Actions -->
            {!! view_render_event('marketplace.my_extensions.show.sidebar.actions.before') !!}

            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.marketplace.my-extensions.show.actions.title')
                </h3>

                <div class="space-y-3">
                    <a
                        href="{{ route('marketplace.extension.show', $installation->extension->slug) }}"
                        class="secondary-button flex w-full items-center justify-center gap-2"
                    >
                        <span class="icon-eye"></span>
                        @lang('marketplace::app.marketplace.my-extensions.show.actions.view-extension')
                    </a>

                    @if($installation->extension->documentation_url)
                        <a
                            href="{{ $installation->extension->documentation_url }}"
                            target="_blank"
                            class="secondary-button flex w-full items-center justify-center gap-2"
                        >
                            <span class="icon-book"></span>
                            @lang('marketplace::app.marketplace.my-extensions.show.actions.documentation')
                        </a>
                    @endif

                    @if($installation->extension->support_email)
                        <a
                            href="mailto:{{ $installation->extension->support_email }}"
                            class="secondary-button flex w-full items-center justify-center gap-2"
                        >
                            <span class="icon-mail"></span>
                            @lang('marketplace::app.marketplace.my-extensions.show.actions.support')
                        </a>
                    @endif

                    <button
                        onclick="confirmUninstall({{ $installation->id }}, '{{ $installation->extension->name }}')"
                        class="secondary-button flex w-full items-center justify-center gap-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                    >
                        <span class="icon-trash"></span>
                        @lang('marketplace::app.marketplace.my-extensions.show.actions.uninstall')
                    </button>
                </div>
            </div>

            {!! view_render_event('marketplace.my_extensions.show.sidebar.actions.after') !!}

            <!-- Extension Links -->
            @if($installation->extension->demo_url || $installation->extension->repository_url)
                {!! view_render_event('marketplace.my_extensions.show.sidebar.links.before') !!}

                <div class="mt-4 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-base font-semibold dark:text-white">
                        @lang('marketplace::app.marketplace.my-extensions.show.links.title')
                    </h3>

                    <div class="space-y-3">
                        @if($installation->extension->demo_url)
                            <a
                                href="{{ $installation->extension->demo_url }}"
                                target="_blank"
                                class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                            >
                                <span class="icon-external-link"></span>
                                @lang('marketplace::app.marketplace.my-extensions.show.links.demo')
                            </a>
                        @endif

                        @if($installation->extension->repository_url)
                            <a
                                href="{{ $installation->extension->repository_url }}"
                                target="_blank"
                                class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                            >
                                <span class="icon-github"></span>
                                @lang('marketplace::app.marketplace.my-extensions.show.links.repository')
                            </a>
                        @endif
                    </div>
                </div>

                {!! view_render_event('marketplace.my_extensions.show.sidebar.links.after') !!}
            @endif

            <!-- Installation Stats -->
            {!! view_render_event('marketplace.my_extensions.show.sidebar.stats.before') !!}

            <div class="mt-4 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.marketplace.my-extensions.show.stats.title')
                </h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.marketplace.my-extensions.show.stats.downloads')
                        </span>
                        <span class="font-medium dark:text-white">
                            {{ number_format($installation->extension->downloads_count) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.marketplace.my-extensions.show.stats.rating')
                        </span>
                        <span class="font-medium dark:text-white">
                            {{ number_format($installation->extension->average_rating, 1) }} ★
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.marketplace.my-extensions.show.stats.reviews')
                        </span>
                        <span class="font-medium dark:text-white">
                            {{ number_format($installation->extension->reviews_count) }}
                        </span>
                    </div>
                </div>
            </div>

            {!! view_render_event('marketplace.my_extensions.show.sidebar.stats.after') !!}
        </div>
    </div>

    {!! view_render_event('marketplace.my_extensions.show.content.after') !!}

    <!-- Uninstall Confirmation Modal -->
    <div id="uninstall-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <span class="icon-alert text-2xl text-red-600 dark:text-red-400"></span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold dark:text-white">
                        @lang('marketplace::app.marketplace.my-extensions.show.uninstall-modal.title')
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.my-extensions.show.uninstall-modal.subtitle')
                    </p>
                </div>
            </div>

            <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
                {!! __('marketplace::app.marketplace.my-extensions.show.uninstall-modal.message', ['name' => '<strong id="uninstall-extension-name"></strong>']) !!}
            </p>

            <div class="flex justify-end gap-3">
                <button
                    onclick="closeUninstallModal()"
                    class="secondary-button"
                >
                    @lang('marketplace::app.marketplace.my-extensions.show.uninstall-modal.cancel')
                </button>
                <button
                    onclick="performUninstall()"
                    class="primary-button bg-red-600 hover:bg-red-700"
                    id="confirm-uninstall-btn"
                >
                    @lang('marketplace::app.marketplace.my-extensions.show.uninstall-modal.confirm')
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let uninstallInstallationId = null;

            function confirmUninstall(installationId, extensionName) {
                uninstallInstallationId = installationId;
                document.getElementById('uninstall-extension-name').textContent = extensionName;
                document.getElementById('uninstall-modal').classList.remove('hidden');
                document.getElementById('uninstall-modal').classList.add('flex');
            }

            function closeUninstallModal() {
                uninstallInstallationId = null;
                document.getElementById('uninstall-modal').classList.add('hidden');
                document.getElementById('uninstall-modal').classList.remove('flex');
            }

            async function performUninstall() {
                if (!uninstallInstallationId) return;

                const btn = document.getElementById('confirm-uninstall-btn');
                btn.disabled = true;
                btn.innerHTML = '<span class="icon-spinner animate-spin"></span> Uninstalling...';

                try {
                    const response = await fetch(`/marketplace/install/installation/${uninstallInstallationId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        closeUninstallModal();
                        window.location.href = '{{ route('marketplace.my_extensions.index') }}';
                    } else {
                        alert(data.message || 'Failed to uninstall extension');
                        btn.disabled = false;
                        btn.textContent = 'Uninstall';
                    }
                } catch (error) {
                    console.error('Uninstall error:', error);
                    alert('An error occurred while uninstalling the extension');
                    btn.disabled = false;
                    btn.textContent = 'Uninstall';
                }
            }

            async function toggleExtension(installationId, enable) {
                const action = enable ? 'enable' : 'disable';

                try {
                    const response = await fetch(`/marketplace/install/installation/${installationId}/${action}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || `Failed to ${action} extension`);
                        window.location.reload();
                    }
                } catch (error) {
                    console.error(`Toggle ${action} error:`, error);
                    alert(`An error occurred while ${action}ing the extension`);
                    window.location.reload();
                }
            }

            async function toggleAutoUpdate(installationId, enable) {
                try {
                    const response = await fetch(`/marketplace/install/installation/${installationId}/auto-update`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ enabled: enable }),
                    });

                    const data = await response.json();

                    if (!data.success) {
                        alert(data.message || 'Failed to toggle auto-update');
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Toggle auto-update error:', error);
                    alert('An error occurred while toggling auto-update');
                    window.location.reload();
                }
            }

            async function updateExtension(installationId) {
                if (!confirm('Are you sure you want to update this extension?')) {
                    return;
                }

                try {
                    const response = await fetch(`/marketplace/install/installation/${installationId}/update`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to update extension');
                    }
                } catch (error) {
                    console.error('Update error:', error);
                    alert('An error occurred while updating the extension');
                }
            }
        </script>
    @endpush
</x-admin::layouts>
