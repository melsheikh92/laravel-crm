<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.forecasts.index.title')
    </x-slot>

    <!-- Header -->
    {!! view_render_event('admin.leads.forecasts.index.header.before') !!}

    <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        {!! view_render_event('admin.leads.forecasts.index.header.left.before') !!}

        <div class="flex flex-col gap-2">
            <!-- Breadcrumb's -->
            <x-admin::breadcrumbs name="forecasts" />

            <div class="text-xl font-bold dark:text-white">
                @lang('admin::app.leads.forecasts.index.title')
            </div>
        </div>

        {!! view_render_event('admin.leads.forecasts.index.header.left.after') !!}

        {!! view_render_event('admin.leads.forecasts.index.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <!-- Generate Forecast Button -->
            <button
                type="button"
                class="primary-button"
                @click="$refs.forecastDashboard.generateForecast()"
            >
                @lang('admin::app.leads.forecasts.index.generate-btn')
            </button>
        </div>

        {!! view_render_event('admin.leads.forecasts.index.header.right.after') !!}
    </div>

    {!! view_render_event('admin.leads.forecasts.index.header.after') !!}

    {!! view_render_event('admin.leads.forecasts.index.content.before') !!}

    <!-- Content -->
    <div class="mt-3.5">
        <v-forecast-dashboard ref="forecastDashboard">
            <!-- Shimmer -->
            <div class="flex flex-col gap-4">
                <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
                <div class="flex gap-4">
                    <div class="light-shimmer-bg dark:shimmer h-[300px] flex-1 rounded-lg"></div>
                    <div class="light-shimmer-bg dark:shimmer h-[300px] flex-1 rounded-lg"></div>
                </div>
            </div>
        </v-forecast-dashboard>
    </div>

    {!! view_render_event('admin.leads.forecasts.index.content.after') !!}

    @pushOnce('scripts')
        <script
            type="module"
            src="{{ vite()->asset('js/chart.js') }}"
        >
        </script>

        <script
            type="text/x-template"
            id="v-forecast-dashboard-template"
        >
            <!-- Shimmer -->
            <template v-if="isLoading">
                <div class="flex flex-col gap-4">
                    <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
                    <div class="flex gap-4">
                        <div class="light-shimmer-bg dark:shimmer h-[300px] flex-1 rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[300px] flex-1 rounded-lg"></div>
                    </div>
                </div>
            </template>

            <!-- Dashboard Content -->
            <template v-else>
                <div class="flex flex-col gap-4">
                    <!-- Period Selector -->
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.index.period-type')
                                </label>
                                <select
                                    v-model="filters.periodType"
                                    class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                                    @change="loadData"
                                >
                                    <option value="week">@lang('admin::app.leads.forecasts.index.week')</option>
                                    <option value="month">@lang('admin::app.leads.forecasts.index.month')</option>
                                    <option value="quarter">@lang('admin::app.leads.forecasts.index.quarter')</option>
                                </select>
                            </div>

                            <div v-if="currentForecast" class="flex items-center gap-2">
                                <span class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.index.confidence')
                                </span>
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    @{{ currentForecast.confidence_score }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Scenario Tabs and Summary -->
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <!-- Tabs -->
                        <div class="flex border-b border-gray-200 dark:border-gray-800">
                            <button
                                v-for="scenario in scenarios"
                                :key="scenario.key"
                                @click="activeScenario = scenario.key"
                                :class="[
                                    'flex-1 px-6 py-4 text-sm font-medium transition-all',
                                    activeScenario === scenario.key
                                        ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400'
                                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'
                                ]"
                            >
                                @{{ scenario.label }}
                            </button>
                        </div>

                        <!-- Scenario Content -->
                        <div class="p-6">
                            <div class="flex flex-col gap-4">
                                <!-- Scenario Value Card -->
                                <div class="flex flex-col items-center gap-2 rounded-lg border border-gray-200 p-6 dark:border-gray-800">
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        @{{ getScenarioDescription(activeScenario) }}
                                    </p>
                                    <p
                                        class="text-4xl font-bold"
                                        :class="getScenarioColor(activeScenario)"
                                    >
                                        @{{ formatCurrency(getScenarioValue(activeScenario)) }}
                                    </p>
                                </div>

                                <!-- Comparison Metrics -->
                                <div v-if="scenarioData" class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
                                    <div class="flex flex-col gap-1 rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                        <p class="text-xs text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.leads.forecasts.index.upside-potential')
                                        </p>
                                        <p class="text-xl font-bold text-green-600">
                                            @{{ formatCurrency(scenarioData.scenario_comparison?.upside_potential || 0) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            +@{{ scenarioData.scenario_comparison?.upside_percentage || 0 }}%
                                        </p>
                                    </div>

                                    <div class="flex flex-col gap-1 rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                        <p class="text-xs text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.leads.forecasts.index.downside-risk')
                                        </p>
                                        <p class="text-xl font-bold text-red-600">
                                            @{{ formatCurrency(scenarioData.scenario_comparison?.downside_risk || 0) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            -@{{ scenarioData.scenario_comparison?.downside_percentage || 0 }}%
                                        </p>
                                    </div>

                                    <div class="flex flex-col gap-1 rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                        <p class="text-xs text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.leads.forecasts.index.total-spread')
                                        </p>
                                        <p class="text-xl font-bold text-blue-600">
                                            @{{ formatCurrency(scenarioData.scenario_comparison?.total_spread || 0) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            @lang('admin::app.leads.forecasts.index.range')
                                        </p>
                                    </div>
                                </div>

                                <!-- Recommendations -->
                                <div v-if="scenarioData?.recommendations?.length" class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                                    <p class="mb-2 text-sm font-semibold text-blue-900 dark:text-blue-300">
                                        @lang('admin::app.leads.forecasts.index.recommendations')
                                    </p>
                                    <ul class="list-inside list-disc space-y-1">
                                        <li
                                            v-for="(recommendation, index) in scenarioData.recommendations"
                                            :key="index"
                                            class="text-sm text-blue-800 dark:text-blue-400"
                                        >
                                            @{{ recommendation }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trend Charts -->
                    <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                        <!-- Forecast Trends Chart -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                                @lang('admin::app.leads.forecasts.index.forecast-trends')
                            </h3>
                            <canvas
                                :id="$.uid + '_trends_chart'"
                                class="w-full"
                            ></canvas>
                        </div>

                        <!-- Scenario Comparison Chart -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                                @lang('admin::app.leads.forecasts.index.scenario-comparison')
                            </h3>
                            <canvas
                                :id="$.uid + '_scenario_chart'"
                                class="w-full"
                            ></canvas>
                        </div>
                    </div>

                    <!-- Lead Breakdown (if available) -->
                    <div v-if="scenarioData?.lead_breakdown" class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            @lang('admin::app.leads.forecasts.index.pipeline-summary')
                        </h3>
                        <div class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
                            <div class="flex flex-col gap-1">
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.index.total-leads')
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    @{{ scenarioData.lead_breakdown.total_leads }}
                                </p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.index.total-value')
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    @{{ formatCurrency(scenarioData.lead_breakdown.total_value) }}
                                </p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.index.average-value')
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    @{{ formatCurrency(scenarioData.lead_breakdown.average_value) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </script>

        <script type="module">
            app.component('v-forecast-dashboard', {
                template: '#v-forecast-dashboard-template',

                data() {
                    return {
                        isLoading: true,
                        currentForecast: null,
                        scenarioData: null,
                        trendsData: null,
                        activeScenario: 'weighted',
                        filters: {
                            periodType: 'month',
                            userId: {{ auth()->guard('user')->user()->id ?? 'null' }},
                        },
                        scenarios: [
                            { key: 'weighted', label: '@lang('admin::app.leads.forecasts.index.likely-case')' },
                            { key: 'best_case', label: '@lang('admin::app.leads.forecasts.index.best-case')' },
                            { key: 'worst_case', label: '@lang('admin::app.leads.forecasts.index.worst-case')' },
                        ],
                        trendsChart: null,
                        scenarioChart: null,
                    };
                },

                mounted() {
                    this.loadData();
                },

                methods: {
                    loadData() {
                        this.isLoading = true;

                        Promise.all([
                            this.loadScenarios(),
                            this.loadTrends(),
                        ]).finally(() => {
                            this.isLoading = false;
                            setTimeout(() => {
                                this.renderCharts();
                            }, 100);
                        });
                    },

                    loadScenarios() {
                        return this.$axios.get("{{ route('admin.forecasts.analytics.scenarios') }}", {
                            params: {
                                user_id: this.filters.userId,
                                period_type: this.filters.periodType,
                            }
                        })
                        .then(response => {
                            this.scenarioData = response.data.data;
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || '@lang('admin::app.leads.forecasts.index.error-loading-scenarios')',
                            });
                        });
                    },

                    loadTrends() {
                        return this.$axios.get("{{ route('admin.forecasts.analytics.trends') }}", {
                            params: {
                                user_id: this.filters.userId,
                                months: 6,
                            }
                        })
                        .then(response => {
                            this.trendsData = response.data.data;
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || '@lang('admin::app.leads.forecasts.index.error-loading-trends')',
                            });
                        });
                    },

                    generateForecast() {
                        this.$axios.post("{{ route('admin.forecasts.generate') }}", {
                            user_id: this.filters.userId,
                            period_type: this.filters.periodType,
                        })
                        .then(response => {
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: '@lang('admin::app.leads.forecasts.index.forecast-generated')',
                            });
                            this.currentForecast = response.data.data;
                            this.loadData();
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || '@lang('admin::app.leads.forecasts.index.error-generating-forecast')',
                            });
                        });
                    },

                    renderCharts() {
                        this.renderTrendsChart();
                        this.renderScenarioChart();
                    },

                    renderTrendsChart() {
                        if (this.trendsChart) {
                            this.trendsChart.destroy();
                        }

                        if (!this.trendsData?.trends || this.trendsData.trends.length === 0) {
                            return;
                        }

                        const labels = this.trendsData.trends.map(t => t.period);
                        const forecastData = this.trendsData.trends.map(t => t.total_forecast || 0);
                        const actualData = this.trendsData.trends.map(t => t.total_won_value || 0);

                        this.trendsChart = new Chart(document.getElementById(this.$.uid + '_trends_chart'), {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: '@lang('admin::app.leads.forecasts.index.forecast')',
                                        data: forecastData,
                                        borderColor: 'rgba(59, 130, 246, 0.8)',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        tension: 0.4,
                                    },
                                    {
                                        label: '@lang('admin::app.leads.forecasts.index.actual')',
                                        data: actualData,
                                        borderColor: 'rgba(34, 197, 94, 0.8)',
                                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                        tension: 0.4,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 2,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                    },
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        border: {
                                            dash: [8, 4],
                                        },
                                    },
                                    x: {
                                        border: {
                                            dash: [8, 4],
                                        },
                                    },
                                },
                            },
                        });
                    },

                    renderScenarioChart() {
                        if (this.scenarioChart) {
                            this.scenarioChart.destroy();
                        }

                        if (!this.scenarioData?.scenarios) {
                            return;
                        }

                        const scenarios = this.scenarioData.scenarios;
                        const data = [
                            scenarios.worst_case?.value || 0,
                            scenarios.weighted?.value || 0,
                            scenarios.best_case?.value || 0,
                        ];

                        this.scenarioChart = new Chart(document.getElementById(this.$.uid + '_scenario_chart'), {
                            type: 'bar',
                            data: {
                                labels: [
                                    '@lang('admin::app.leads.forecasts.index.worst-case')',
                                    '@lang('admin::app.leads.forecasts.index.likely-case')',
                                    '@lang('admin::app.leads.forecasts.index.best-case')',
                                ],
                                datasets: [{
                                    label: '@lang('admin::app.leads.forecasts.index.forecast-value')',
                                    data: data,
                                    backgroundColor: [
                                        'rgba(239, 68, 68, 0.8)',
                                        'rgba(59, 130, 246, 0.8)',
                                        'rgba(34, 197, 94, 0.8)',
                                    ],
                                    borderColor: [
                                        'rgba(239, 68, 68, 1)',
                                        'rgba(59, 130, 246, 1)',
                                        'rgba(34, 197, 94, 1)',
                                    ],
                                    borderWidth: 1,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 2,
                                plugins: {
                                    legend: {
                                        display: false,
                                    },
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        border: {
                                            dash: [8, 4],
                                        },
                                    },
                                    x: {
                                        border: {
                                            dash: [8, 4],
                                        },
                                    },
                                },
                            },
                        });
                    },

                    getScenarioValue(scenario) {
                        if (!this.scenarioData?.scenarios) {
                            return 0;
                        }
                        return this.scenarioData.scenarios[scenario]?.value || 0;
                    },

                    getScenarioDescription(scenario) {
                        if (!this.scenarioData?.scenarios) {
                            return '';
                        }
                        return this.scenarioData.scenarios[scenario]?.description || '';
                    },

                    getScenarioColor(scenario) {
                        const colors = {
                            weighted: 'text-blue-600 dark:text-blue-400',
                            best_case: 'text-green-600 dark:text-green-400',
                            worst_case: 'text-red-600 dark:text-red-400',
                        };
                        return colors[scenario] || 'text-gray-900 dark:text-white';
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(value || 0);
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
