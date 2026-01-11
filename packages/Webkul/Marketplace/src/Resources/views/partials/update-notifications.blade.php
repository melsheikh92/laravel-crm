{{-- Extension Update Notifications Dropdown --}}
<div>
    <x-admin::dropdown position="bottom-right">
        <x-slot:toggle>
            <!-- Notification Bell with Badge -->
            <button class="relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-full transition-all hover:bg-gray-100 dark:hover:bg-gray-950">
                <span class="icon-bell text-2xl text-gray-600 dark:text-gray-300"></span>

                <!-- Badge for update count -->
                @php
                    $userId = Auth::id();
                    $installationRepository = app(\Webkul\Marketplace\Repositories\ExtensionInstallationRepository::class);
                    $versionRepository = app(\Webkul\Marketplace\Repositories\ExtensionVersionRepository::class);

                    $installations = $installationRepository
                        ->with(['version'])
                        ->scopeQuery(function ($query) use ($userId) {
                            return $query->where('user_id', $userId)
                                ->whereIn('status', ['active', 'inactive']);
                        })
                        ->all();

                    $updateCount = 0;
                    $updatesAvailable = [];

                    foreach ($installations as $installation) {
                        if (!$installation->version) {
                            continue;
                        }

                        $latestVersion = $versionRepository->getLatestVersion($installation->extension_id);

                        if ($latestVersion && version_compare($latestVersion->version, $installation->version->version, '>')) {
                            $updateCount++;
                            $updatesAvailable[] = [
                                'installation' => $installation,
                                'current_version' => $installation->version->version,
                                'latest_version' => $latestVersion->version,
                                'release_date' => $latestVersion->release_date,
                            ];
                        }
                    }
                @endphp

                @if($updateCount > 0)
                    <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-semibold text-white">
                        {{ $updateCount > 9 ? '9+' : $updateCount }}
                    </span>
                @endif
            </button>
        </x-slot>

        <!-- Dropdown Content -->
        <x-slot:content class="mt-2 !p-0">
            <div class="w-80 max-h-96 overflow-y-auto">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-semibold dark:text-white">
                            @lang('marketplace::app.marketplace.notifications.updates-title')
                        </h3>
                        @if($updateCount > 0)
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('marketplace::app.marketplace.notifications.updates-count', ['count' => $updateCount])
                            </p>
                        @endif
                    </div>

                    @if($updateCount > 0)
                        <a
                            href="{{ route('marketplace.my_extensions.index') }}"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            @lang('marketplace::app.marketplace.notifications.view-all')
                        </a>
                    @endif
                </div>

                <!-- Updates List -->
                <div class="py-2">
                    @if($updateCount > 0)
                        @foreach($updatesAvailable as $index => $update)
                            @if($index < 5)
                                <a
                                    href="{{ route('marketplace.my_extensions.show', $update['installation']->id) }}"
                                    class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-950"
                                >
                                    <!-- Extension Logo -->
                                    <div class="flex-shrink-0">
                                        @if($update['installation']->extension->logo)
                                            <img
                                                src="{{ Storage::url($update['installation']->extension->logo) }}"
                                                alt="{{ $update['installation']->extension->name }}"
                                                class="h-10 w-10 rounded-lg object-cover"
                                            />
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30">
                                                <span class="icon-package text-xl text-blue-600 dark:text-blue-400"></span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Update Details -->
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold dark:text-white truncate">
                                            {{ $update['installation']->extension->name }}
                                        </h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            v{{ $update['current_version'] }} → v{{ $update['latest_version'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">
                                            {{ $update['release_date']->diffForHumans() }}
                                        </p>
                                    </div>

                                    <!-- Update Badge -->
                                    <div class="flex-shrink-0">
                                        <span class="flex items-center gap-1 rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                            <span class="icon-arrow-up text-xs"></span>
                                            Update
                                        </span>
                                    </div>
                                </a>
                            @endif
                        @endforeach

                        @if($updateCount > 5)
                            <!-- Show more link -->
                            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                                <a
                                    href="{{ route('marketplace.my_extensions.index') }}"
                                    class="flex items-center justify-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    @lang('marketplace::app.marketplace.notifications.view-more', ['count' => $updateCount - 5])
                                    <span class="icon-arrow-right text-xs"></span>
                                </a>
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="flex flex-col items-center justify-center px-4 py-8">
                            <span class="icon-check-circle text-4xl text-green-600 dark:text-green-400"></span>
                            <p class="mt-3 text-sm font-medium dark:text-white">
                                @lang('marketplace::app.marketplace.notifications.no-updates-title')
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                                @lang('marketplace::app.marketplace.notifications.no-updates-description')
                            </p>
                            <a
                                href="{{ route('marketplace.my_extensions.index') }}"
                                class="mt-4 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                @lang('marketplace::app.marketplace.notifications.manage-extensions')
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </x-slot>
    </x-admin::dropdown>
</div>
