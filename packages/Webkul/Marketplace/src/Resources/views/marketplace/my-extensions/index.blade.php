<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.marketplace.my-extensions.index.title')
    </x-slot>

    {!! view_render_event('marketplace.my_extensions.index.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.my_extensions.index.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.marketplace.my-extensions.index.title')
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.marketplace.my-extensions.index.description')
            </p>
        </div>

        {!! view_render_event('marketplace.my_extensions.index.header.left.after') !!}

        {!! view_render_event('marketplace.my_extensions.index.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <button
                onclick="checkForUpdates()"
                class="secondary-button flex items-center gap-2"
                id="check-updates-btn"
            >
                <span class="icon-refresh"></span>
                @lang('marketplace::app.marketplace.my-extensions.index.check-updates')
            </button>

            <a
                href="{{ route('marketplace.browse.index') }}"
                class="primary-button"
            >
                @lang('marketplace::app.marketplace.my-extensions.index.browse-marketplace')
            </a>
        </div>

        {!! view_render_event('marketplace.my_extensions.index.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.my_extensions.index.header.after') !!}

    {!! view_render_event('marketplace.my_extensions.index.statistics.before') !!}

    <!-- Statistics Cards -->
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Total Installations -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <span class="icon-package text-2xl text-blue-600 dark:text-blue-400"></span>
                </div>
                <div>
                    <p class="text-2xl font-bold dark:text-white">{{ $installations->total() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.my-extensions.index.stats.total')
                    </p>
                </div>
            </div>
        </div>

        <!-- Active Extensions -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <span class="icon-check text-2xl text-green-600 dark:text-green-400"></span>
                </div>
                <div>
                    <p class="text-2xl font-bold dark:text-white">{{ $groupedInstallations['active'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.my-extensions.index.stats.active')
                    </p>
                </div>
            </div>
        </div>

        <!-- Inactive Extensions -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                    <span class="icon-pause text-2xl text-gray-600 dark:text-gray-400"></span>
                </div>
                <div>
                    <p class="text-2xl font-bold dark:text-white">{{ $groupedInstallations['inactive'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.my-extensions.index.stats.inactive')
                    </p>
                </div>
            </div>
        </div>

        <!-- Updates Available -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <span class="icon-arrow-up text-2xl text-purple-600 dark:text-purple-400"></span>
                </div>
                <div>
                    <p class="text-2xl font-bold dark:text-white">{{ $updatesAvailable }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.my-extensions.index.stats.updates')
                    </p>
                </div>
            </div>
        </div>

        <!-- Failed Installations -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                    <span class="icon-alert text-2xl text-red-600 dark:text-red-400"></span>
                </div>
                <div>
                    <p class="text-2xl font-bold dark:text-white">{{ $groupedInstallations['failed'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.my-extensions.index.stats.failed')
                    </p>
                </div>
            </div>
        </div>
    </div>

    {!! view_render_event('marketplace.my_extensions.index.statistics.after') !!}

    {!! view_render_event('marketplace.my_extensions.index.content.before') !!}

    <!-- Installed Extensions List -->
    <div class="flex flex-col gap-4">
        @if($installations->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-12 dark:border-gray-800 dark:bg-gray-900">
                <span class="icon-package text-6xl text-gray-400 dark:text-gray-600"></span>

                <p class="mt-4 text-xl font-semibold dark:text-white">
                    @lang('marketplace::app.marketplace.my-extensions.index.empty.title')
                </p>

                <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
                    @lang('marketplace::app.marketplace.my-extensions.index.empty.description')
                </p>

                <a
                    href="{{ route('marketplace.browse.index') }}"
                    class="primary-button mt-6"
                >
                    @lang('marketplace::app.marketplace.my-extensions.index.empty.browse-marketplace')
                </a>
            </div>
        @else
            <!-- Extension Cards -->
            <div class="grid grid-cols-1 gap-4">
                @foreach($installations as $installation)
                    <div class="flex flex-col rounded-lg border border-gray-200 bg-white transition-all dark:border-gray-800 dark:bg-gray-900 md:flex-row">
                        <!-- Extension Logo -->
                        <div class="flex-shrink-0 border-b border-gray-200 p-6 dark:border-gray-800 md:border-b-0 md:border-r">
                            <a href="{{ route('marketplace.my_extensions.show', $installation->id) }}" class="flex justify-center md:block">
                                @if($installation->extension->logo)
                                    <img
                                        src="{{ Storage::url($installation->extension->logo) }}"
                                        alt="{{ $installation->extension->name }}"
                                        class="h-20 w-20 rounded-lg object-cover"
                                    />
                                @else
                                    <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30">
                                        <span class="icon-package text-3xl text-blue-600 dark:text-blue-400"></span>
                                    </div>
                                @endif
                            </a>
                        </div>

                        <!-- Extension Details -->
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('marketplace.my_extensions.show', $installation->id) }}">
                                            <h3 class="text-xl font-semibold dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $installation->extension->name }}
                                            </h3>
                                        </a>

                                        <!-- Status Badge -->
                                        <span class="rounded-full px-2 py-1 text-xs font-medium
                                            @if($installation->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($installation->status === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                            @elseif($installation->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                            @elseif($installation->status === 'updating') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                            @else bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                            @endif">
                                            {{ ucfirst($installation->status) }}
                                        </span>

                                        <!-- Update Badge -->
                                        @php
                                            $latestVersion = \Webkul\Marketplace\Repositories\ExtensionVersionRepository::class;
                                            $versionRepo = app($latestVersion);
                                            $latest = $versionRepo->getLatestVersion($installation->extension_id);
                                            $hasUpdate = $latest && $installation->version && version_compare($latest->version, $installation->version->version, '>');
                                        @endphp

                                        @if($hasUpdate)
                                            <span class="flex items-center gap-1 rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                                <span class="icon-arrow-up text-xs"></span>
                                                @lang('marketplace::app.marketplace.my-extensions.index.update-available')
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-1 flex items-center gap-3">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ ucfirst($installation->extension->type) }}
                                        </span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            @lang('marketplace::app.marketplace.my-extensions.index.version'): {{ $installation->version->version ?? 'N/A' }}
                                        </span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            @lang('marketplace::app.marketplace.my-extensions.index.installed'): {{ $installation->installed_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <p class="mt-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $installation->extension->description }}
                                    </p>
                                </div>

                                <!-- Enable/Disable Toggle -->
                                <div class="flex items-center gap-2">
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input
                                            type="checkbox"
                                            class="peer sr-only"
                                            {{ $installation->status === 'active' ? 'checked' : '' }}
                                            onchange="toggleExtension({{ $installation->id }}, this.checked)"
                                        >
                                        <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:peer-focus:ring-blue-800"></div>
                                    </label>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 flex flex-wrap gap-3">
                                @if($hasUpdate)
                                    <button
                                        onclick="updateExtension({{ $installation->id }})"
                                        class="primary-button flex items-center gap-2"
                                    >
                                        <span class="icon-arrow-up"></span>
                                        @lang('marketplace::app.marketplace.my-extensions.index.update')
                                    </button>
                                @endif

                                <a
                                    href="{{ route('marketplace.my_extensions.show', $installation->id) }}"
                                    class="secondary-button"
                                >
                                    @lang('marketplace::app.marketplace.my-extensions.index.view-details')
                                </a>

                                <a
                                    href="{{ route('marketplace.extension.show', $installation->extension->slug) }}"
                                    class="secondary-button"
                                >
                                    @lang('marketplace::app.marketplace.my-extensions.index.extension-page')
                                </a>

                                <button
                                    onclick="confirmUninstall({{ $installation->id }}, '{{ $installation->extension->name }}')"
                                    class="secondary-button flex items-center gap-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                >
                                    <span class="icon-trash"></span>
                                    @lang('marketplace::app.marketplace.my-extensions.index.uninstall')
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $installations->links() }}
            </div>
        @endif
    </div>

    {!! view_render_event('marketplace.my_extensions.index.content.after') !!}

    <!-- Uninstall Confirmation Modal -->
    <div id="uninstall-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <span class="icon-alert text-2xl text-red-600 dark:text-red-400"></span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold dark:text-white">
                        @lang('marketplace::app.marketplace.my-extensions.index.uninstall-modal.title')
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.marketplace.my-extensions.index.uninstall-modal.subtitle')
                    </p>
                </div>
            </div>

            <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.marketplace.my-extensions.index.uninstall-modal.message', ['name' => '<strong id="uninstall-extension-name"></strong>'])
            </p>

            <div class="flex justify-end gap-3">
                <button
                    onclick="closeUninstallModal()"
                    class="secondary-button"
                >
                    @lang('marketplace::app.marketplace.my-extensions.index.uninstall-modal.cancel')
                </button>
                <button
                    onclick="performUninstall()"
                    class="primary-button bg-red-600 hover:bg-red-700"
                    id="confirm-uninstall-btn"
                >
                    @lang('marketplace::app.marketplace.my-extensions.index.uninstall-modal.confirm')
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
                        window.location.reload();
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

            async function checkForUpdates() {
                const btn = document.getElementById('check-updates-btn');
                btn.disabled = true;
                btn.innerHTML = '<span class="icon-spinner animate-spin"></span> Checking...';

                try {
                    const response = await fetch('/marketplace/my-extensions/updates/check', {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        if (data.data.update_count > 0) {
                            window.location.reload();
                        }
                    } else {
                        alert(data.message || 'Failed to check for updates');
                    }
                } catch (error) {
                    console.error('Check updates error:', error);
                    alert('An error occurred while checking for updates');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<span class="icon-refresh"></span> Check for Updates';
                }
            }
        </script>
    @endpush
</x-admin::layouts>
