<template>
    <!-- Shimmer -->
    <div v-if="isLoading" class="flex flex-col gap-4">
        <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
        <div class="flex gap-4">
            <div class="light-shimmer-bg dark:shimmer h-[300px] flex-1 rounded-lg"></div>
            <div class="light-shimmer-bg dark:shimmer h-[300px] flex-1 rounded-lg"></div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="flex flex-col gap-4">
        <!-- Period Selector -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        Period Type
                    </label>
                    <select
                        v-model="filters.periodType"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        @change="loadData"
                    >
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                        <option value="quarter">Quarter</option>
                    </select>
                </div>

                <div v-if="currentForecast" class="flex items-center gap-2">
                    <span class="text-xs text-gray-600 dark:text-gray-300">Confidence:</span>
                    <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                        {{ currentForecast.confidence_score }}%
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
                    {{ scenario.label }}
                </button>
            </div>

            <!-- Scenario Content -->
            <div class="p-6">
                <div class="flex flex-col gap-4">
                    <!-- Scenario Value Card -->
                    <div class="flex flex-col items-center gap-2 rounded-lg border border-gray-200 p-6 dark:border-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ getScenarioDescription(activeScenario) }}
                        </p>
                        <p
                            class="text-4xl font-bold"
                            :class="getScenarioColor(activeScenario)"
                        >
                            {{ formatCurrency(getScenarioValue(activeScenario)) }}
                        </p>
                    </div>

                    <!-- Comparison Metrics -->
                    <div v-if="scenarioData" class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
                        <div class="flex flex-col gap-1 rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-300">Upside Potential</p>
                            <p class="text-xl font-bold text-green-600">
                                {{ formatCurrency(scenarioData.scenario_comparison?.upside_potential || 0) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                +{{ scenarioData.scenario_comparison?.upside_percentage || 0 }}%
                            </p>
                        </div>

                        <div class="flex flex-col gap-1 rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-300">Downside Risk</p>
                            <p class="text-xl font-bold text-red-600">
                                {{ formatCurrency(scenarioData.scenario_comparison?.downside_risk || 0) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                -{{ scenarioData.scenario_comparison?.downside_percentage || 0 }}%
                            </p>
                        </div>

                        <div class="flex flex-col gap-1 rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-300">Total Spread</p>
                            <p class="text-xl font-bold text-blue-600">
                                {{ formatCurrency(scenarioData.scenario_comparison?.total_spread || 0) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Range</p>
                        </div>
                    </div>

                    <!-- Recommendations -->
                    <div
                        v-if="scenarioData?.recommendations?.length"
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20"
                    >
                        <p class="mb-2 text-sm font-semibold text-blue-900 dark:text-blue-300">
                            Recommendations
                        </p>
                        <ul class="list-inside list-disc space-y-1">
                            <li
                                v-for="(recommendation, index) in scenarioData.recommendations"
                                :key="index"
                                class="text-sm text-blue-800 dark:text-blue-400"
                            >
                                {{ recommendation }}
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
                    Forecast Trends
                </h3>
                <canvas :id="chartIds.trends" class="w-full"></canvas>
            </div>

            <!-- Scenario Comparison Chart -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Scenario Comparison
                </h3>
                <canvas :id="chartIds.scenario" class="w-full"></canvas>
            </div>
        </div>

        <!-- Lead Breakdown (if available) -->
        <div
            v-if="scenarioData?.lead_breakdown"
            class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
        >
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                Pipeline Summary
            </h3>
            <div class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
                <div class="flex flex-col gap-1">
                    <p class="text-xs text-gray-600 dark:text-gray-300">Total Leads</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ scenarioData.lead_breakdown.total_leads }}
                    </p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-xs text-gray-600 dark:text-gray-300">Total Value</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ formatCurrency(scenarioData.lead_breakdown.total_value) }}
                    </p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-xs text-gray-600 dark:text-gray-300">Average Value</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ formatCurrency(scenarioData.lead_breakdown.average_value) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ForecastDashboard',

    props: {
        /**
         * User ID for filtering forecasts
         */
        userId: {
            type: Number,
            required: false,
            default: null,
        },

        /**
         * Initial period type (week, month, quarter)
         */
        initialPeriodType: {
            type: String,
            default: 'month',
            validator: (value) => ['week', 'month', 'quarter'].includes(value),
        },

        /**
         * API endpoints configuration
         */
        endpoints: {
            type: Object,
            default: () => ({
                scenarios: '/admin/forecasts/analytics/scenarios',
                trends: '/admin/forecasts/analytics/trends',
                generate: '/admin/forecasts/generate',
            }),
        },
    },

    emits: ['forecast-generated', 'data-loaded', 'error'],

    data() {
        return {
            isLoading: true,
            currentForecast: null,
            scenarioData: null,
            trendsData: null,
            activeScenario: 'weighted',
            filters: {
                periodType: this.initialPeriodType,
                userId: this.userId,
            },
            scenarios: [
                { key: 'weighted', label: 'Likely Case' },
                { key: 'best_case', label: 'Best Case' },
                { key: 'worst_case', label: 'Worst Case' },
            ],
            trendsChart: null,
            scenarioChart: null,
            chartIds: {
                trends: `forecast-trends-${this._uid}`,
                scenario: `scenario-comparison-${this._uid}`,
            },
        };
    },

    mounted() {
        this.loadData();
    },

    beforeUnmount() {
        if (this.trendsChart) {
            this.trendsChart.destroy();
        }
        if (this.scenarioChart) {
            this.scenarioChart.destroy();
        }
    },

    methods: {
        /**
         * Load all dashboard data
         */
        loadData() {
            this.isLoading = true;

            Promise.all([
                this.loadScenarios(),
                this.loadTrends(),
            ])
                .then(() => {
                    this.$emit('data-loaded', {
                        scenarios: this.scenarioData,
                        trends: this.trendsData,
                    });
                })
                .catch((error) => {
                    this.$emit('error', error);
                })
                .finally(() => {
                    this.isLoading = false;
                    setTimeout(() => {
                        this.renderCharts();
                    }, 100);
                });
        },

        /**
         * Load scenario data from API
         */
        loadScenarios() {
            return this.$axios
                .get(this.endpoints.scenarios, {
                    params: {
                        user_id: this.filters.userId,
                        period_type: this.filters.periodType,
                    },
                })
                .then((response) => {
                    this.scenarioData = response.data.data;
                })
                .catch((error) => {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message:
                            error.response?.data?.message ||
                            'Failed to load forecast scenarios.',
                    });
                    throw error;
                });
        },

        /**
         * Load trends data from API
         */
        loadTrends() {
            return this.$axios
                .get(this.endpoints.trends, {
                    params: {
                        user_id: this.filters.userId,
                        months: 6,
                    },
                })
                .then((response) => {
                    this.trendsData = response.data.data;
                })
                .catch((error) => {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message:
                            error.response?.data?.message ||
                            'Failed to load forecast trends.',
                    });
                    throw error;
                });
        },

        /**
         * Generate a new forecast
         */
        generateForecast() {
            return this.$axios
                .post(this.endpoints.generate, {
                    user_id: this.filters.userId,
                    period_type: this.filters.periodType,
                })
                .then((response) => {
                    this.$emitter.emit('add-flash', {
                        type: 'success',
                        message: 'Forecast generated successfully.',
                    });
                    this.currentForecast = response.data.data;
                    this.$emit('forecast-generated', this.currentForecast);
                    this.loadData();
                })
                .catch((error) => {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message:
                            error.response?.data?.message ||
                            'Failed to generate forecast.',
                    });
                    this.$emit('error', error);
                });
        },

        /**
         * Render all charts
         */
        renderCharts() {
            this.renderTrendsChart();
            this.renderScenarioChart();
        },

        /**
         * Render the forecast trends line chart
         */
        renderTrendsChart() {
            if (this.trendsChart) {
                this.trendsChart.destroy();
            }

            if (!this.trendsData?.trends || this.trendsData.trends.length === 0) {
                return;
            }

            const labels = this.trendsData.trends.map((t) => t.period);
            const forecastData = this.trendsData.trends.map((t) => t.total_forecast || 0);
            const actualData = this.trendsData.trends.map((t) => t.total_won_value || 0);

            this.trendsChart = new Chart(document.getElementById(this.chartIds.trends), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Forecast',
                            data: forecastData,
                            borderColor: 'rgba(59, 130, 246, 0.8)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                        },
                        {
                            label: 'Actual',
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

        /**
         * Render the scenario comparison bar chart
         */
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

            this.scenarioChart = new Chart(document.getElementById(this.chartIds.scenario), {
                type: 'bar',
                data: {
                    labels: ['Worst Case', 'Likely Case', 'Best Case'],
                    datasets: [
                        {
                            label: 'Forecast Value',
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
                        },
                    ],
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

        /**
         * Get the value for a specific scenario
         */
        getScenarioValue(scenario) {
            if (!this.scenarioData?.scenarios) {
                return 0;
            }
            return this.scenarioData.scenarios[scenario]?.value || 0;
        },

        /**
         * Get the description for a specific scenario
         */
        getScenarioDescription(scenario) {
            if (!this.scenarioData?.scenarios) {
                return '';
            }
            return this.scenarioData.scenarios[scenario]?.description || '';
        },

        /**
         * Get the color class for a specific scenario
         */
        getScenarioColor(scenario) {
            const colors = {
                weighted: 'text-blue-600 dark:text-blue-400',
                best_case: 'text-green-600 dark:text-green-400',
                worst_case: 'text-red-600 dark:text-red-400',
            };
            return colors[scenario] || 'text-gray-900 dark:text-white';
        },

        /**
         * Format a number as currency
         */
        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(value || 0);
        },
    },
};
</script>
