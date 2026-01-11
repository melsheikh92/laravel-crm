<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.forecasts.accuracy.title')
        </x-slot>

        <!-- Header -->
        {!! view_render_event('admin.leads.forecasts.accuracy.header.before') !!}

        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            {!! view_render_event('admin.leads.forecasts.accuracy.header.left.before') !!}

            <div class="flex flex-col gap-2">
                <!-- Breadcrumb's -->
                <x-admin::breadcrumbs name="forecasts.accuracy" />

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.leads.forecasts.accuracy.title')
                </div>
            </div>

            {!! view_render_event('admin.leads.forecasts.accuracy.header.left.after') !!}

            {!! view_render_event('admin.leads.forecasts.accuracy.header.right.before') !!}

            <div class="flex items-center gap-x-2.5">
                <!-- Refresh Button -->
                <button type="button" class="secondary-button" @click="$refs.accuracyReport.loadData()">
                    @lang('admin::app.leads.forecasts.accuracy.refresh-btn')
                </button>
            </div>

            {!! view_render_event('admin.leads.forecasts.accuracy.header.right.after') !!}
        </div>

        {!! view_render_event('admin.leads.forecasts.accuracy.header.after') !!}

        {!! view_render_event('admin.leads.forecasts.accuracy.content.before') !!}

        <!-- Content -->
        <div class="mt-3.5">
            <v-accuracy-report ref="accuracyReport">
                <!-- Shimmer -->
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-4 gap-4 max-md:grid-cols-2">
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                    </div>
                    <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
                    <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
                </div>
            </v-accuracy-report>
        </div>

        {!! view_render_event('admin.leads.forecasts.accuracy.content.after') !!}

        @pushOnce('scripts')
            <script type="module" src="{{ vite()->asset('js/chart.js') }}">
            </script>

            <script type="text/x-template" id="v-accuracy-report-template">
                <!-- Shimmer -->
            <template v-if="isLoading">
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-4 gap-4 max-md:grid-cols-2">
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                    </div>
                    <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
                    <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
                </div>
            </template>

            <!-- Report Content -->
            <template v-else>
                <div class="flex flex-col gap-4">
                    <!-- Filters -->
                    <div
                        class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-4 max-sm:flex-wrap">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.accuracy.period-type')
                                </label>
                                <select v-model="filters.periodType"
                                    class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                                    @change="loadData">
                                    <option value="">@lang('admin::app.leads.forecasts.accuracy.all-periods')</option>
                                    <option value="week">@lang('admin::app.leads.forecasts.accuracy.week')</option>
                                    <option value="month">@lang('admin::app.leads.forecasts.accuracy.month')</option>
                                    <option value="quarter">@lang('admin::app.leads.forecasts.accuracy.quarter')</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Accuracy Metrics Cards -->
                    <div class="grid grid-cols-4 gap-4 max-md:grid-cols-2 max-sm:grid-cols-1">
                        <!-- Average Accuracy -->
                        <div
                            class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex flex-col gap-2">
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.accuracy.average-accuracy')
                                </p>
                                <p class="text-3xl font-bold" :class="getAccuracyColor(metrics.average_accuracy)">
                                    @{{ metrics.average_accuracy }}%
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.leads.forecasts.accuracy.forecasts-analyzed', ['count' => '@{{ metrics.total_forecasts }}'])
                                </p>
                            </div>
                        </div>

                        <!-- Accuracy Rate -->
                        <div
                            class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex flex-col gap-2">
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.accuracy.accuracy-rate')
                                </p>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                    @{{ metrics.accuracy_rate }}%
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ metrics.accurate_count }} @lang('admin::app.leads.forecasts.accuracy.within-10-pct')
                                </p>
                            </div>
                        </div>

                        <!-- Average Variance -->
                        <div
                            class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex flex-col gap-2">
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.accuracy.average-variance')
                                </p>
                                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                                    @{{ metrics.average_variance_pct }}%
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ formatCurrency(metrics.average_variance) }}
                                    @lang('admin::app.leads.forecasts.accuracy.average-diff')
                                </p>
                            </div>
                        </div>

                        <!-- Forecast Bias -->
                        <div
                            class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex flex-col gap-2">
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.leads.forecasts.accuracy.forecast-bias')
                                </p>
                                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                                    @{{ getBiasLabel() }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ metrics.over_forecasted_count }} @lang('admin::app.leads.forecasts.accuracy.over') /
                                    @{{ metrics.under_forecasted_count }} @lang('admin::app.leads.forecasts.accuracy.under')
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Forecast vs Actual Chart -->
                    <div
                        class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            @lang('admin::app.leads.forecasts.accuracy.forecast-vs-actual')
                        </h3>
                        <canvas :id="$.uid + '_comparison_chart'" class="w-full"></canvas>
                    </div>

                    <!-- Variance Trends Chart -->
                    <div
                        class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            @lang('admin::app.leads.forecasts.accuracy.variance-trends')
                        </h3>
                        <canvas :id="$.uid + '_variance_chart'" class="w-full"></canvas>
                    </div>

                    <!-- Detailed Forecasts Table -->
                    <div
                        class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                @lang('admin::app.leads.forecasts.accuracy.detailed-comparison')
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.leads.forecasts.accuracy.period')
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.leads.forecasts.accuracy.forecast')
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.leads.forecasts.accuracy.actual')
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.leads.forecasts.accuracy.variance')
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.leads.forecasts.accuracy.accuracy')
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="forecast in forecastData" :key="forecast.id"
                                        class="border-b border-gray-200 dark:border-gray-800">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            @{{ formatPeriod(forecast) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                            @{{ formatCurrency(forecast.weighted_forecast) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                            @{{ formatCurrency(forecast.latest_actual?.actual_value || 0) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold"
                                            :class="getVarianceColor(forecast.latest_actual?.variance_percentage || 0)">
                                            @{{ formatVariance(forecast.latest_actual?.variance_percentage || 0) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold"
                                            :class="getAccuracyColor(getAccuracyFromVariance(forecast.latest_actual?.variance_percentage || 0))">
                                            @{{ getAccuracyFromVariance(forecast.latest_actual?.variance_percentage || 0)
                                            }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
            </script>

            <script type="module">
                app.component('v-accuracy-report', {
                    template: '#v-accuracy-report-template',

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
                                periodType: '',
                                userId: {{ auth()->guard('user')->user()->id ?? 'null' }},
                            },
                            comparisonChart: null,
                            varianceChart: null,
                        };
                    },

                    mounted() {
                        this.loadData();
                    },

                    methods: {
                        loadData() {
                            this.isLoading = true;

                            const params = {
                                user_id: this.filters.userId,
                            };

                            if (this.filters.periodType) {
                                params.period_type = this.filters.periodType;
                            }

                            this.$axios.get("{{ route('admin.forecasts.accuracy') }}", { params })
                                .then(response => {
                                    this.forecastData = response.data.data;
                                    this.metrics = response.data.metrics;
                                })
                                .catch(error => {
                                    this.$emitter.emit('add-flash', {
                                        type: 'error',
                                        message: error.response?.data?.message || '@lang('admin::app.leads.forecasts.accuracy.error-loading-data')',
                                    });
                                })
                                .finally(() => {
                                    this.isLoading = false;
                                    setTimeout(() => {
                                        this.renderCharts();
                                    }, 100);
                                });
                        },

                        renderCharts() {
                            this.renderComparisonChart();
                            this.renderVarianceChart();
                        },

                        renderComparisonChart() {
                            if (this.comparisonChart) {
                                this.comparisonChart.destroy();
                            }

                            if (this.forecastData.length === 0) {
                                return;
                            }

                            const labels = this.forecastData.map(f => this.formatPeriod(f));
                            const forecastData = this.forecastData.map(f => f.weighted_forecast || 0);
                            const actualData = this.forecastData.map(f => f.latest_actual?.actual_value || 0);

                            this.comparisonChart = new Chart(document.getElementById(this.$.uid + '_comparison_chart'), {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [
                                        {
                                            label: '@lang('admin::app.leads.forecasts.accuracy.forecast')',
                                            data: forecastData,
                                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                            borderColor: 'rgba(59, 130, 246, 1)',
                                            borderWidth: 1,
                                        },
                                        {
                                            label: '@lang('admin::app.leads.forecasts.accuracy.actual')',
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
                            });
                        },

                        renderVarianceChart() {
                            if (this.varianceChart) {
                                this.varianceChart.destroy();
                            }

                            if (this.forecastData.length === 0) {
                                return;
                            }

                            const labels = this.forecastData.map(f => this.formatPeriod(f));
                            const varianceData = this.forecastData.map(f => f.latest_actual?.variance_percentage || 0);

                            this.varianceChart = new Chart(document.getElementById(this.$.uid + '_variance_chart'), {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [
                                        {
                                            label: '@lang('admin::app.leads.forecasts.accuracy.variance-pct')',
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

                        formatPeriod(forecast) {
                            const start = new Date(forecast.period_start);
                            const end = new Date(forecast.period_end);
                            const formatter = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            return `${formatter.format(start)} - ${formatter.format(end)}`;
                        },

                        formatCurrency(value) {
                            return new Intl.NumberFormat('en-US', {
                                style: 'currency',
                                currency: 'USD',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0,
                            }).format(value || 0);
                        },

                        formatVariance(variance) {
                            if (variance === null || variance === undefined || isNaN(variance)) {
                                return '0.00%';
                            }
                            const numVariance = parseFloat(variance);
                            const sign = numVariance > 0 ? '+' : '';
                            return `${sign}${numVariance.toFixed(2)}%`;
                        },

                        getAccuracyFromVariance(variance) {
                            if (variance === null || variance === undefined || isNaN(variance)) {
                                return '0.00';
                            }
                            const numVariance = parseFloat(variance);
                            return Math.max(0, 100 - Math.abs(numVariance)).toFixed(2);
                        },

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

                        getVarianceColor(variance) {
                            const absVariance = Math.abs(variance);
                            if (absVariance <= 10) {
                                return 'text-green-600 dark:text-green-400';
                            } else if (absVariance <= 25) {
                                return 'text-orange-600 dark:text-orange-400';
                            }
                            return 'text-red-600 dark:text-red-400';
                        },

                        getBiasLabel() {
                            const over = this.metrics.over_forecasted_count;
                            const under = this.metrics.under_forecasted_count;

                            if (over === under) {
                                return '@lang('admin::app.leads.forecasts.accuracy.balanced')';
                            } else if (over > under) {
                                return '@lang('admin::app.leads.forecasts.accuracy.optimistic')';
                            }
                            return '@lang('admin::app.leads.forecasts.accuracy.conservative')';
                        },
                    },
                });
            </script>
        @endPushOnce
</x-admin::layouts>