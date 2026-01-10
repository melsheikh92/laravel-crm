<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.territory-assignments.history.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.territories.assignments.history.breadcrumbs.before') !!}

                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.territories.assignments.history" />

                {!! view_render_event('admin.settings.territories.assignments.history.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.territory-assignments.history.title')
                </div>

                <!-- Entity Context -->
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    @lang('admin::app.settings.territory-assignments.history.entity'):
                    <span class="font-semibold">
                        @if(isset($entity->title))
                            {{ $entity->title }}
                        @elseif(isset($entity->name))
                            {{ $entity->name }}
                        @else
                            @lang('admin::app.settings.territory-assignments.history.unknown')
                        @endif
                    </span>
                    <span class="text-gray-500">
                        (
                        @if(str_contains($assignableType, 'Lead'))
                            @lang('admin::app.settings.territory-assignments.index.lead')
                        @elseif(str_contains($assignableType, 'Organization'))
                            @lang('admin::app.settings.territory-assignments.index.organization')
                        @elseif(str_contains($assignableType, 'Person'))
                            @lang('admin::app.settings.territory-assignments.index.person')
                        @endif
                        )
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                {!! view_render_event('admin.settings.territories.assignments.history.back_button.before') !!}

                <!-- Back button -->
                <a
                    href="{{ route('admin.settings.territories.assignments.index') }}"
                    class="secondary-button"
                >
                    @lang('admin::app.settings.territory-assignments.history.back-btn')
                </a>

                {!! view_render_event('admin.settings.territories.assignments.history.back_button.after') !!}
            </div>
        </div>

        {!! view_render_event('admin.settings.territories.assignments.history.content.before') !!}

        <!-- History Content -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            @if($history->isEmpty())
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <span class="icon-history text-3xl text-gray-400"></span>
                    </div>

                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.territory-assignments.history.empty-title')
                    </p>

                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        @lang('admin::app.settings.territory-assignments.history.empty-info')
                    </p>
                </div>
            @else
                <!-- History Timeline -->
                <div class="p-6">
                    <div class="relative">
                        <!-- Timeline Line -->
                        <div class="absolute left-4 top-8 h-full w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                        <!-- History Items -->
                        <div class="space-y-6">
                            @foreach($history as $assignment)
                                <div class="relative flex gap-4">
                                    <!-- Timeline Dot -->
                                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-4 border-white bg-blue-500 dark:border-gray-900">
                                        <span class="icon-territory text-xs text-white"></span>
                                    </div>

                                    <!-- History Card -->
                                    <div class="flex-1 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-800 dark:text-white">
                                                    {{ $assignment->territory->name ?? trans('admin::app.settings.territory-assignments.history.deleted-territory') }}
                                                </h4>

                                                <div class="mt-2 space-y-1">
                                                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                                        <span class="icon-user"></span>
                                                        <span>
                                                            @lang('admin::app.settings.territory-assignments.history.assigned-by'):
                                                            @if($assignment->assignedBy)
                                                                {{ $assignment->assignedBy->name }}
                                                            @else
                                                                @lang('admin::app.settings.territory-assignments.index.system')
                                                            @endif
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                                        <span class="icon-settings"></span>
                                                        <span>
                                                            @lang('admin::app.settings.territory-assignments.history.assignment-type'):
                                                            @if($assignment->isManual())
                                                                <span class="font-medium text-blue-600 dark:text-blue-400">
                                                                    @lang('admin::app.settings.territory-assignments.index.manual')
                                                                </span>
                                                            @else
                                                                <span class="font-medium text-green-600 dark:text-green-400">
                                                                    @lang('admin::app.settings.territory-assignments.index.automatic')
                                                                </span>
                                                            @endif
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                                        <span class="icon-clock"></span>
                                                        <span>
                                                            {{ $assignment->assigned_at->format('M d, Y \a\t g:i A') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Assignment Type Badge -->
                                            <div class="ml-4">
                                                @if($assignment->isManual())
                                                    <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        @lang('admin::app.settings.territory-assignments.index.manual')
                                                    </span>
                                                @else
                                                    <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        @lang('admin::app.settings.territory-assignments.index.automatic')
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {!! view_render_event('admin.settings.territories.assignments.history.content.after') !!}
    </div>
</x-admin::layouts>
