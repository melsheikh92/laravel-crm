<template>
    <!-- Shimmer -->
    <div v-if="isLoading" class="flex flex-col gap-4">
        <div class="grid grid-cols-4 gap-4 max-md:grid-cols-2">
            <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
            <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
            <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
            <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
        </div>
        <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
        <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
    </div>

    <!-- Report Content -->
    <div v-else class="flex flex-col gap-4">
        <!-- Filters -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-4 max-sm:flex-wrap">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        Period Type
                    </label>
                    <select
                        v-model="filters.periodType"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        @change="loadData"
                    >
                        <option value="">All Periods</option>
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                        <option value="quarter">Quarter</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Accuracy Metrics Cards -->
        <div class="grid grid-cols-4 gap-4 max-md:grid-cols-2 max-sm:grid-cols-1">
            <!-- Average Accuracy -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-2">
                    <p class="text-xs text-gray-600 dark:text-gray-300">Average Accuracy</p>
                    <p class="text-3xl font-bold" :class="getAccuracyColor(metrics.average_accuracy)">
                        {{ metrics.average_accuracy }}%
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ metrics.total_forecasts }} forecasts analyzed
                    </p>
                </div>
            </div>

            <!-- Accuracy Rate -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-2">
                    <p class="text-xs text-gray-600 dark:text-gray-300">Accuracy Rate</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ metrics.accuracy_rate }}%
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ metrics.accurate_count }} within 10%
                    </p>
                </div>
            </div>

            <!-- Average Variance -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-2">
                    <p class="text-xs text-gray-600 dark:text-gray-300">Average Variance</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                        {{ metrics.average_variance_pct }}%
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ formatCurrency(metrics.average_variance) }} average diff
                    </p>
                </div>
            </div>

            <!-- Forecast Bias -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-2">
                    <p class="text-xs text-gray-600 dark:text-gray-300">Forecast Bias</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                        {{ getBiasLabel() }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ metrics.over_forecasted_count }} over / {{ metrics.under_forecasted_count }}
                        under
                    </p>
                </div>
            </div>
        </div>

        <!-- Forecast vs Actual Chart -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                Forecast vs Actual Comparison
            </h3>
            <canvas :id="chartIds.comparison" class="w-full"></canvas>
        </div>

        <!-- Variance Trends Chart -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                Variance Trends Over Time
            </h3>
            <canvas :id="chartIds.variance" class="w-full"></canvas>
        </div>

        <!-- Detailed Forecasts Table -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Detailed Comparison
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Period
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Forecast
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Actual
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Variance
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Accuracy
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="forecast in forecastData"
                            :key="forecast.id"
                            class="border-b border-gray-200 dark:border-gray-800"
                        >
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                {{ formatPeriod(forecast) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ formatCurrency(forecast.weighted_forecast) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ formatCurrency(forecast.latest_actual?.actual_value || 0) }}
                            </td>
                            <td
                                class="px-4 py-3 text-right text-sm font-semibold"
                                :class="getVarianceColor(forecast.latest_actual?.variance_percentage || 0)"
                            >
                                {{ formatVariance(forecast.latest_actual?.variance_percentage || 0) }}
                            </td>
                            <td
                                class="px-4 py-3 text-right text-sm font-semibold"
                                :class="
                                    getAccuracyColor(
                                        getAccuracyFromVariance(
                                            forecast.latest_actual?.variance_percentage || 0
                                        )
                                    )
                                "
                            >
                                {{
                                    getAccuracyFromVariance(
                                        forecast.latest_actual?.variance_percentage || 0
                                    )
                                }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AccuracyReport',

    props: {
        /**
         * User ID for filtering accuracy reports
         */
        userId: {
            type: Number,
            required: false,
            default: null,
        },

        /**
         * Initial period type filter
         */
        initialPeriodType: {
            type: String,
            default: '',
            validator: (value) => ['', 'week', 'month', 'quarter'].includes(value),
        },

        /**
         * API endpoints configuration
         */
        endpoints: {
            type: Object,
            default: () => ({
                accuracy: '/admin/forecasts/accuracy',
            }),
        },
    },

    emits: ['data-loaded', 'error'],

    data() {
        return {
            isLoading: true,
            forecastData: [],
            metrics: {
                total_forecasts: 0,
                average_accuracy: 0,
                average_variance: 0,
                average_variance_pct: 0,
                over_forecasted_count: 0,
                under_forecasted_count: 0,
                accurate_count: 0,
                accuracy_rate: 0,
            },
            filters: {
                periodType: this.initialPeriodType,
                userId: this.userId,
            },
            comparisonChart: null,
            varianceChart: null,
            chartIds: {
                comparison: `forecast-comparison-${this._uid}`,
                variance: `variance-trends-${this._uid}`,
            },
        };
    },

    mounted() {
        this.loadData();
    },

    beforeUnmount() {
        if (this.comparisonChart) {
            this.comparisonChart.destroy();
        }
        if (this.varianceChart) {
            this.varianceChart.destroy();
        }
    },

    methods: {
        /**
         * Load accuracy data from API
         */
        loadData() {
            this.isLoading = true;

            const params = {
                user_id: this.filters.userId,
            };

            if (this.filters.periodType) {
                params.period_type = this.filters.periodType;
            }

            this.$axios
                .get(this.endpoints.accuracy, { params })
                .then((response) => {
                    this.forecastData = response.data.data;
                    this.metrics = response.data.metrics;
                    this.$emit('data-loaded', {
                        forecasts: this.forecastData,
                        metrics: this.metrics,
                    });
                })
                .catch((error) => {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message:
                            error.response?.data?.message || 'Failed to load accuracy data.',
                    });
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
         * Render all charts
         */
        renderCharts() {
            this.renderComparisonChart();
            this.renderVarianceChart();
        },

        /**
         * Render the forecast vs actual comparison bar chart
         */
        renderComparisonChart() {
            if (this.comparisonChart) {
                this.comparisonChart.destroy();
            }

            if (this.forecastData.length === 0) {
                return;
            }

            const labels = this.forecastData.map((f) => this.formatPeriod(f));
            const forecastData = this.forecastData.map((f) => f.weighted_forecast || 0);
            const actualData = this.forecastData.map((f) => f.latest_actual?.actual_value || 0);

            this.comparisonChart = new Chart(
                document.getElementById(this.chartIds.comparison),
                {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Forecast',
                                data: forecastData,
                                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1,
                            },
                            {
                                label: 'Actual',
                                data: actualData,
                                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 2.5,
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
                }
            );
        },

        /**
         * Render the variance trends line chart
         */
        renderVarianceChart() {
            if (this.varianceChart) {
                this.varianceChart.destroy();
            }

            if (this.forecastData.length === 0) {
                return;
            }

            const labels = this.forecastData.map((f) => this.formatPeriod(f));
            const varianceData = this.forecastData.map(
                (f) => f.latest_actual?.variance_percentage || 0
            );

            this.varianceChart = new Chart(document.getElementById(this.chartIds.variance), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Variance %',
                            data: varianceData,
                            borderColor: 'rgba(249, 115, 22, 0.8)',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            tension: 0.4,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 3,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                        },
                    },
                    scales: {
                        y: {
                            border: {
                                dash: [8, 4],
                            },
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
                                },
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
         * Format forecast period for display
         */
        formatPeriod(forecast) {
            const start = new Date(forecast.period_start);
            const end = new Date(forecast.period_end);
            const formatter = new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            });
            return `${formatter.format(start)} - ${formatter.format(end)}`;
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

        /**
         * Format variance with sign
         */
        formatVariance(variance) {
            const sign = variance > 0 ? '+' : '';
            return `${sign}${variance.toFixed(2)}%`;
        },

        /**
         * Calculate accuracy from variance percentage
         */
        getAccuracyFromVariance(variance) {
            return Math.max(0, 100 - Math.abs(variance)).toFixed(2);
        },

        /**
         * Get color class for accuracy score
         */
        getAccuracyColor(accuracy) {
            if (accuracy >= 90) {
                return 'text-green-600 dark:text-green-400';
            } else if (accuracy >= 75) {
                return 'text-blue-600 dark:text-blue-400';
            } else if (accuracy >= 60) {
                return 'text-orange-600 dark:text-orange-400';
            }
            return 'text-red-600 dark:text-red-400';
        },

        /**
         * Get color class for variance
         */
        getVarianceColor(variance) {
            const absVariance = Math.abs(variance);
            if (absVariance <= 10) {
                return 'text-green-600 dark:text-green-400';
            } else if (absVariance <= 25) {
                return 'text-orange-600 dark:text-orange-400';
            }
            return 'text-red-600 dark:text-red-400';
        },

        /**
         * Get bias label based on over/under forecasting
         */
        getBiasLabel() {
            const over = this.metrics.over_forecasted_count;
            const under = this.metrics.under_forecasted_count;

            if (over === under) {
                return 'Balanced';
            } else if (over > under) {
                return 'Optimistic';
            }
            return 'Conservative';
        },
    },
};
</script>
