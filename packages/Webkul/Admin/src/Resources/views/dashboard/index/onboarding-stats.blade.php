{!! view_render_event('admin.dashboard.index.onboarding_stats.before') !!}

<!-- Onboarding Stats Vue Component -->
<v-dashboard-onboarding-stats>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.index.over-all />
</v-dashboard-onboarding-stats>

{!! view_render_event('admin.dashboard.index.onboarding_stats.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-onboarding-stats-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.index.over-all />
        </template>

        <!-- Onboarding Stats Section -->
        <template v-else>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.dashboard.index.onboarding-stats.title')
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @lang('admin::app.dashboard.index.onboarding-stats.description')
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1 mb-4">
                <!-- Completion Rate Card -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('admin::app.dashboard.index.onboarding-stats.completion-rate')
                    </p>

                    <div class="flex gap-2">
                        <p class="text-xl font-bold dark:text-gray-300">
                            @{{ report.statistics.completion_rate.formatted_total }}
                        </p>

                        <div class="flex items-center gap-0.5">
                            <span
                                class="text-base !font-semibold text-green-500"
                                :class="[report.statistics.completion_rate.progress < 0 ? 'icon-stats-down text-red-500 dark:!text-red-500' : 'icon-stats-up text-green-500 dark:!text-green-500']"
                            ></span>

                            <p
                                class="text-xs font-semibold text-green-500"
                                :class="[report.statistics.completion_rate.progress < 0 ?  'text-red-500' : 'text-green-500']"
                            >
                                @{{ Math.abs(report.statistics.completion_rate.progress.toFixed(2)) }}%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Average Completion Time Card -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('admin::app.dashboard.index.onboarding-stats.average-time')
                    </p>

                    <div class="flex gap-2">
                        <p class="text-xl font-bold dark:text-gray-300">
                            @{{ report.statistics.average_time.formatted_total }}
                        </p>

                        <div class="flex items-center gap-0.5">
                            <span
                                class="text-base !font-semibold text-green-500"
                                :class="[report.statistics.average_time.progress < 0 ? 'icon-stats-down text-red-500 dark:!text-red-500' : 'icon-stats-up text-green-500 dark:!text-green-500']"
                            ></span>

                            <p
                                class="text-xs font-semibold text-green-500"
                                :class="[report.statistics.average_time.progress < 0 ?  'text-red-500' : 'text-green-500']"
                            >
                                @{{ Math.abs(report.statistics.average_time.progress.toFixed(2)) }}%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Total Started Card -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('admin::app.dashboard.index.onboarding-stats.total-started')
                    </p>

                    <div class="flex gap-2">
                        <p class="text-xl font-bold dark:text-gray-300">
                            @{{ report.statistics.total_started.formatted_total }}
                        </p>

                        <div class="flex items-center gap-0.5">
                            <span
                                class="text-base !font-semibold text-green-500"
                                :class="[report.statistics.total_started.progress < 0 ? 'icon-stats-down text-red-500 dark:!text-red-500' : 'icon-stats-up text-green-500 dark:!text-green-500']"
                            ></span>

                            <p
                                class="text-xs font-semibold text-green-500"
                                :class="[report.statistics.total_started.progress < 0 ?  'text-red-500' : 'text-green-500']"
                            >
                                @{{ Math.abs(report.statistics.total_started.progress.toFixed(2)) }}%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Total Completed Card -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('admin::app.dashboard.index.onboarding-stats.total-completed')
                    </p>

                    <div class="flex gap-2">
                        <p class="text-xl font-bold dark:text-gray-300">
                            @{{ report.statistics.total_completed.formatted_total }}
                        </p>

                        <div class="flex items-center gap-0.5">
                            <span
                                class="text-base !font-semibold text-green-500"
                                :class="[report.statistics.total_completed.progress < 0 ? 'icon-stats-down text-red-500 dark:!text-red-500' : 'icon-stats-up text-green-500 dark:!text-green-500']"
                            ></span>

                            <p
                                class="text-xs font-semibold text-green-500"
                                :class="[report.statistics.total_completed.progress < 0 ?  'text-red-500' : 'text-green-500']"
                            >
                                @{{ Math.abs(report.statistics.total_completed.progress.toFixed(2)) }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Analytics -->
            <div v-if="Object.keys(report.statistics.step_analytics).length > 0" class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.dashboard.index.onboarding-stats.step-analytics-title')
                    </h4>
                </div>

                <div class="p-4">
                    <div class="space-y-3">
                        <div
                            v-for="(step, key) in report.statistics.step_analytics"
                            :key="key"
                            class="flex items-center justify-between"
                        >
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        @{{ step.title }}
                                    </span>
                                    <div class="flex gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        <span>
                                            <span class="text-green-600 dark:text-green-400 font-semibold">@{{ step.completion_rate }}%</span>
                                            @lang('admin::app.dashboard.index.onboarding-stats.completed')
                                        </span>
                                        <span v-if="step.skip_rate > 0">
                                            <span class="text-orange-600 dark:text-orange-400 font-semibold">@{{ step.skip_rate }}%</span>
                                            @lang('admin::app.dashboard.index.onboarding-stats.skipped')
                                        </span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    <div
                                        class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: step.completion_rate + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-8">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                        <span class="icon-information text-2xl text-gray-400"></span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('admin::app.dashboard.index.onboarding-stats.no-data')
                    </p>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-onboarding-stats', {
            template: '#v-dashboard-onboarding-stats-template',

            data() {
                return {
                    report: {
                        statistics: {
                            completion_rate: { current: 0, previous: 0, progress: 0, formatted_total: '0%' },
                            average_time: { current: 0, previous: 0, progress: 0, formatted_total: '0h' },
                            total_started: { current: 0, previous: 0, progress: 0, formatted_total: '0' },
                            total_completed: { current: 0, previous: 0, progress: 0, formatted_total: '0' },
                            step_analytics: {}
                        }
                    },

                    isLoading: true,
                }
            },

            mounted() {
                this.getStats({});

                this.$emitter.on('reporting-filter-updated', this.getStats);
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;

                    var filters = Object.assign({}, filters);

                    filters.type = 'onboarding-stats';

                    this.$axios.get("{{ route('admin.dashboard.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;

                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error fetching onboarding stats:', error);
                            this.isLoading = false;
                        });
                },
            }
        });
    </script>
@endPushOnce
