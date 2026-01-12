<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.onboarding.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header Section -->
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.onboarding" />

                <div class="text-xl font-bold dark:text-gray-300">
                    @lang('admin::app.settings.onboarding.index.title')
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="grid gap-6">
                <!-- Description -->
                <p class="text-gray-600 dark:text-gray-300">
                    @lang('admin::app.settings.onboarding.index.description')
                </p>

                <!-- Current Status Card -->
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.onboarding.index.current-status')
                    </h3>

                    @if($progress)
                        @if($progress->is_completed)
                            <!-- Completed Status -->
                            <div class="mb-4 flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                    <i class="icon-checkbox-outline text-2xl text-green-600 dark:text-green-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white">
                                        @lang('admin::app.settings.onboarding.index.completed')
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @lang('admin::app.settings.onboarding.index.completed-on'):
                                        {{ $progress->completed_at->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <!-- In Progress Status -->
                            <div class="mb-4 flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                    <i class="icon-processing text-2xl text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white">
                                        @lang('admin::app.settings.onboarding.index.in-progress')
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @lang('admin::app.settings.onboarding.index.started-on'):
                                        {{ $progress->started_at->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-2">
                                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>@lang('admin::app.settings.onboarding.index.progress-percentage', ['percentage' => round($progressSummary['progress_percentage'] ?? 0)])</span>
                                    <span>@lang('admin::app.settings.onboarding.index.steps-completed', [
                                        'completed' => $progressSummary['completed_steps_count'] ?? 0,
                                        'total' => $progressSummary['total_steps'] ?? 5
                                    ])</span>
                                </div>
                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500"
                                         style="width: {{ round($progressSummary['progress_percentage'] ?? 0) }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- Not Started Status -->
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                <i class="icon-information text-2xl text-gray-600 dark:text-gray-400"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.onboarding.index.not-started')
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Restart Button -->
                @if(config('onboarding.allow_restart', true))
                    <div class="flex justify-end">
                        <button
                            type="button"
                            class="primary-button"
                            @click="$refs.restartModal.toggle()"
                        >
                            @lang('admin::app.settings.onboarding.index.restart-btn')
                        </button>
                    </div>
                @else
                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-700 dark:bg-yellow-900">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            @lang('admin::app.settings.onboarding.index.restart-disabled')
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Restart Confirmation Modal -->
    <x-admin::modal ref="restartModal">
        <x-slot:header>
            <p class="text-lg font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.onboarding.index.restart-btn')
            </p>
        </x-slot>

        <x-slot:content>
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <i class="icon-warning text-2xl text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <div>
                    <p class="text-gray-700 dark:text-gray-300">
                        @lang('admin::app.settings.onboarding.index.restart-warning')
                    </p>
                </div>
            </div>
        </x-slot>

        <x-slot:footer>
            <div class="flex gap-2">
                <button
                    type="button"
                    class="secondary-button"
                    @click="$refs.restartModal.toggle()"
                >
                    @lang('admin::app.settings.onboarding.index.cancel')
                </button>

                <x-admin::form
                    :action="route('onboarding.restart')"
                    method="POST"
                >
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('admin::app.settings.onboarding.index.confirm-restart')
                    </button>
                </x-admin::form>
            </div>
        </x-slot:footer>
    </x-admin::modal>
</x-admin::layouts>
