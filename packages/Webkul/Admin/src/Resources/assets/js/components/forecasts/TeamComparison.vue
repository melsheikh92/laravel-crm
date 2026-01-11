<template>
    <!-- Shimmer -->
    <div v-if="isLoading" class="flex flex-col gap-4">
        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
        <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
        <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
            <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
            <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
        </div>
    </div>

    <!-- Team Comparison Content -->
    <div v-else class="flex flex-col gap-4">
        <!-- Filters and Summary -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
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
                            <option value="week">Week</option>
                            <option value="month">Month</option>
                            <option value="quarter">Quarter</option>
                        </select>
                    </div>
                </div>

                <div v-if="summary" class="flex items-center gap-4">
                    <div class="flex flex-col items-end">
                        <span class="text-xs text-gray-600 dark:text-gray-300">Team Total</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            {{ formatCurrency(summary.total_forecast) }}
                        </span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-xs text-gray-600 dark:text-gray-300">Team Members</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ summary.member_count }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Team Leaderboard
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Ranked by forecast value and confidence
                </p>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                <div
                    v-for="(member, index) in teamMembers"
                    :key="member.user_id"
                    class="flex items-center justify-between p-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    <!-- Rank and User Info -->
                    <div class="flex items-center gap-4">
                        <!-- Rank Badge -->
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full font-bold"
                            :class="getRankBadgeClass(index + 1)"
                        >
                            {{ index + 1 }}
                        </div>

                        <!-- User Details -->
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ member.user_name }}
                            </span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">
                                {{ member.forecast_count }} forecast{{ member.forecast_count > 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center gap-6 max-sm:flex-col max-sm:items-end max-sm:gap-2">
                        <!-- Forecast Value -->
                        <div class="flex flex-col items-end">
                            <span class="text-xs text-gray-600 dark:text-gray-300">Forecast</span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ formatCurrency(member.total_forecast) }}
                            </span>
                        </div>

                        <!-- Contribution % -->
                        <div class="flex flex-col items-end">
                            <span class="text-xs text-gray-600 dark:text-gray-300">Contribution</span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ member.contribution_percentage }}%
                            </span>
                        </div>

                        <!-- Confidence Score -->
                        <div class="flex flex-col items-end">
                            <span class="text-xs text-gray-600 dark:text-gray-300">Confidence</span>
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="getConfidenceBarColor(member.avg_confidence)"
                                        :style="{ width: member.avg_confidence + '%' }"
                                    ></div>
                                </div>
                                <span
                                    class="text-sm font-semibold"
                                    :class="getConfidenceTextColor(member.avg_confidence)"
                                >
                                    {{ Math.round(member.avg_confidence) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="teamMembers.length === 0"
                    class="flex flex-col items-center justify-center p-12"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        No team forecasts available for the selected period.
                    </p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
            <!-- Contribution Breakdown Chart -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Contribution Breakdown
                </h3>
                <canvas :id="chartIds.contribution" class="w-full"></canvas>
            </div>

            <!-- Performance Comparison Chart -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Performance Comparison
                </h3>
                <canvas :id="chartIds.performance" class="w-full"></canvas>
            </div>
        </div>

        <!-- Scenario Breakdown by Member -->
        <div
            v-if="scenarioBreakdown.length > 0"
            class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Scenario Analysis by Member
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Member
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Likely Case
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Best Case
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Worst Case
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Range
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="member in scenarioBreakdown"
                            :key="member.user_id"
                            class="border-b border-gray-200 dark:border-gray-800"
                        >
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ member.user_name }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-blue-600 dark:text-blue-400">
                                {{ formatCurrency(member.weighted_forecast) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-green-600 dark:text-green-400">
                                {{ formatCurrency(member.best_case) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-red-600 dark:text-red-400">
                                {{ formatCurrency(member.worst_case) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ formatCurrency(member.best_case - member.worst_case) }}
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
    name: 'TeamComparison',

    props: {
        /**
         * Team ID for filtering forecasts (optional, defaults to user's team)
         */
        teamId: {
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
                comparison: '/admin/forecasts/analytics/comparison',
            }),
        },
    },

    emits: ['data-loaded', 'error'],

    data() {
        return {
            isLoading: true,
            teamMembers: [],
            scenarioBreakdown: [],
            summary: null,
            filters: {
                periodType: this.initialPeriodType,
                teamId: this.teamId,
            },
            contributionChart: null,
            performanceChart: null,
            chartIds: {
                contribution: `team-contribution-${this._uid}`,
                performance: `team-performance-${this._uid}`,
            },
        };
    },

    mounted() {
        this.loadData();
    },

    beforeUnmount() {
        if (this.contributionChart) {
            this.contributionChart.destroy();
        }
        if (this.performanceChart) {
            this.performanceChart.destroy();
        }
    },

    methods: {
        /**
         * Load team comparison data from API
         */
        loadData() {
            this.isLoading = true;

            const params = {
                period_type: this.filters.periodType,
            };

            if (this.filters.teamId) {
                params.team_id = this.filters.teamId;
            }

            this.$axios
                .get(this.endpoints.comparison, { params })
                .then((response) => {
                    this.teamMembers = response.data.data.team_members || [];
                    this.scenarioBreakdown = response.data.data.scenario_breakdown || [];
                    this.summary = response.data.data.summary;

                    this.$emit('data-loaded', {
                        teamMembers: this.teamMembers,
                        scenarioBreakdown: this.scenarioBreakdown,
                        summary: this.summary,
                    });
                })
                .catch((error) => {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message:
                            error.response?.data?.message ||
                            'Failed to load team comparison data.',
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
            this.renderContributionChart();
            this.renderPerformanceChart();
        },

        /**
         * Render the contribution breakdown pie chart
         */
        renderContributionChart() {
            if (this.contributionChart) {
                this.contributionChart.destroy();
            }

            if (this.teamMembers.length === 0) {
                return;
            }

            const labels = this.teamMembers.map((m) => m.user_name);
            const data = this.teamMembers.map((m) => m.total_forecast);
            const colors = this.generateColors(this.teamMembers.length);

            this.contributionChart = new Chart(
                document.getElementById(this.chartIds.contribution),
                {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Forecast Contribution',
                                data: data,
                                backgroundColor: colors.background,
                                borderColor: colors.border,
                                borderWidth: 2,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 1.5,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const label = context.label || '';
                                        const value = this.formatCurrency(context.parsed);
                                        const percentage = this.teamMembers[context.dataIndex].contribution_percentage;
                                        return `${label}: ${value} (${percentage}%)`;
                                    },
                                },
                            },
                        },
                    },
                }
            );
        },

        /**
         * Render the performance comparison horizontal bar chart
         */
        renderPerformanceChart() {
            if (this.performanceChart) {
                this.performanceChart.destroy();
            }

            if (this.teamMembers.length === 0) {
                return;
            }

            const labels = this.teamMembers.map((m) => m.user_name);
            const forecastData = this.teamMembers.map((m) => m.total_forecast);
            const confidenceData = this.teamMembers.map((m) => m.avg_confidence);

            this.performanceChart = new Chart(
                document.getElementById(this.chartIds.performance),
                {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Forecast Value',
                                data: forecastData,
                                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Confidence %',
                                data: confidenceData,
                                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: 1,
                                yAxisID: 'y1',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 1.5,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                            },
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                beginAtZero: true,
                                border: {
                                    dash: [8, 4],
                                },
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                max: 100,
                                border: {
                                    dash: [8, 4],
                                },
                                grid: {
                                    drawOnChartArea: false,
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
         * Get rank badge color class
         */
        getRankBadgeClass(rank) {
            if (rank === 1) {
                return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
            } else if (rank === 2) {
                return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
            } else if (rank === 3) {
                return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
            }
            return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        },

        /**
         * Get confidence bar color class
         */
        getConfidenceBarColor(confidence) {
            if (confidence >= 80) {
                return 'bg-green-500';
            } else if (confidence >= 60) {
                return 'bg-blue-500';
            } else if (confidence >= 40) {
                return 'bg-yellow-500';
            }
            return 'bg-red-500';
        },

        /**
         * Get confidence text color class
         */
        getConfidenceTextColor(confidence) {
            if (confidence >= 80) {
                return 'text-green-600 dark:text-green-400';
            } else if (confidence >= 60) {
                return 'text-blue-600 dark:text-blue-400';
            } else if (confidence >= 40) {
                return 'text-yellow-600 dark:text-yellow-400';
            }
            return 'text-red-600 dark:text-red-400';
        },

        /**
         * Generate colors for charts
         */
        generateColors(count) {
            const baseColors = [
                { bg: 'rgba(59, 130, 246, 0.8)', border: 'rgba(59, 130, 246, 1)' },
                { bg: 'rgba(34, 197, 94, 0.8)', border: 'rgba(34, 197, 94, 1)' },
                { bg: 'rgba(249, 115, 22, 0.8)', border: 'rgba(249, 115, 22, 1)' },
                { bg: 'rgba(168, 85, 247, 0.8)', border: 'rgba(168, 85, 247, 1)' },
                { bg: 'rgba(239, 68, 68, 0.8)', border: 'rgba(239, 68, 68, 1)' },
                { bg: 'rgba(236, 72, 153, 0.8)', border: 'rgba(236, 72, 153, 1)' },
                { bg: 'rgba(14, 165, 233, 0.8)', border: 'rgba(14, 165, 233, 1)' },
                { bg: 'rgba(132, 204, 22, 0.8)', border: 'rgba(132, 204, 22, 1)' },
            ];

            const background = [];
            const border = [];

            for (let i = 0; i < count; i++) {
                const color = baseColors[i % baseColors.length];
                background.push(color.bg);
                border.push(color.border);
            }

            return { background, border };
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
