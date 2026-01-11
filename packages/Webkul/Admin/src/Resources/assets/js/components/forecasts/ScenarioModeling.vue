<template>
    <!-- Shimmer -->
    <div v-if="isLoading" class="flex flex-col gap-4">
        <div class="light-shimmer-bg dark:shimmer h-[200px] rounded-lg"></div>
        <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
            <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
            <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
        </div>
    </div>

    <!-- Scenario Modeling Content -->
    <div v-else class="flex flex-col gap-4">
        <!-- Header and Reset Controls -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <div class="flex flex-col gap-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Scenario Modeling
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Adjust assumptions to see how they impact your forecast
                    </p>
                </div>

                <button
                    @click="resetAssumptions"
                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 transition-all hover:border-gray-400 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-700"
                >
                    Reset to Defaults
                </button>
            </div>
        </div>

        <!-- Assumptions Controls -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h4 class="mb-4 text-md font-semibold text-gray-900 dark:text-white">
                Adjust Assumptions
            </h4>

            <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1">
                <!-- Conversion Rate Adjustment -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Conversion Rate Multiplier
                        </label>
                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            {{ assumptions.conversionMultiplier.toFixed(2) }}x
                        </span>
                    </div>
                    <input
                        v-model.number="assumptions.conversionMultiplier"
                        type="range"
                        min="0.5"
                        max="2.0"
                        step="0.1"
                        class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                        @input="recalculateScenarios"
                    />
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>0.5x (Conservative)</span>
                        <span>2.0x (Optimistic)</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        Adjusts the probability of deals converting to closed-won
                    </p>
                </div>

                <!-- Deal Value Adjustment -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Deal Value Multiplier
                        </label>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ assumptions.valueMultiplier.toFixed(2) }}x
                        </span>
                    </div>
                    <input
                        v-model.number="assumptions.valueMultiplier"
                        type="range"
                        min="0.5"
                        max="2.0"
                        step="0.1"
                        class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                        @input="recalculateScenarios"
                    />
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>0.5x (Lower)</span>
                        <span>2.0x (Higher)</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        Adjusts the expected value of each deal
                    </p>
                </div>

                <!-- Win Rate Adjustment -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Win Rate Adjustment
                        </label>
                        <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                            {{ formatPercentage(assumptions.winRateAdjustment) }}
                        </span>
                    </div>
                    <input
                        v-model.number="assumptions.winRateAdjustment"
                        type="range"
                        min="-30"
                        max="30"
                        step="5"
                        class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                        @input="recalculateScenarios"
                    />
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>-30%</span>
                        <span>+30%</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        Adjusts the overall win rate based on market conditions
                    </p>
                </div>

                <!-- Velocity Factor -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Deal Velocity Factor
                        </label>
                        <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                            {{ assumptions.velocityFactor.toFixed(2) }}x
                        </span>
                    </div>
                    <input
                        v-model.number="assumptions.velocityFactor"
                        type="range"
                        min="0.5"
                        max="2.0"
                        step="0.1"
                        class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                        @input="recalculateScenarios"
                    />
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>0.5x (Slower)</span>
                        <span>2.0x (Faster)</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        Adjusts how quickly deals move through the pipeline
                    </p>
                </div>
            </div>
        </div>

        <!-- Scenario Results -->
        <div class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
            <!-- Worst Case -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                            Worst Case
                        </p>
                        <span
                            v-if="showChangeIndicators"
                            :class="getChangeColor(modeled.worst_case - baseline.worst_case)"
                            class="text-xs font-semibold"
                        >
                            {{ formatChange(modeled.worst_case - baseline.worst_case) }}
                        </span>
                    </div>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                        {{ formatCurrency(modeled.worst_case) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pessimistic scenario
                    </p>
                </div>
            </div>

            <!-- Likely Case -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                            Likely Case
                        </p>
                        <span
                            v-if="showChangeIndicators"
                            :class="getChangeColor(modeled.weighted - baseline.weighted)"
                            class="text-xs font-semibold"
                        >
                            {{ formatChange(modeled.weighted - baseline.weighted) }}
                        </span>
                    </div>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ formatCurrency(modeled.weighted) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Most likely outcome
                    </p>
                </div>
            </div>

            <!-- Best Case -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                            Best Case
                        </p>
                        <span
                            v-if="showChangeIndicators"
                            :class="getChangeColor(modeled.best_case - baseline.best_case)"
                            class="text-xs font-semibold"
                        >
                            {{ formatChange(modeled.best_case - baseline.best_case) }}
                        </span>
                    </div>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ formatCurrency(modeled.best_case) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Optimistic scenario
                    </p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
            <!-- Scenario Comparison Chart -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Scenario Comparison
                </h3>
                <canvas :id="chartIds.comparison" class="w-full"></canvas>
            </div>

            <!-- Impact Analysis Chart -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Impact Analysis
                </h3>
                <canvas :id="chartIds.impact" class="w-full"></canvas>
            </div>
        </div>

        <!-- Insights -->
        <div
            v-if="insights.length > 0"
            class="box-shadow rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20"
        >
            <h4 class="mb-2 text-sm font-semibold text-blue-900 dark:text-blue-300">
                Scenario Insights
            </h4>
            <ul class="list-inside list-disc space-y-1">
                <li
                    v-for="(insight, index) in insights"
                    :key="index"
                    class="text-sm text-blue-800 dark:text-blue-400"
                >
                    {{ insight }}
                </li>
            </ul>
        </div>

        <!-- Impact Summary -->
        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Impact Summary
                </h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
                    <div class="flex flex-col gap-1">
                        <p class="text-xs text-gray-600 dark:text-gray-300">Total Impact</p>
                        <p
                            class="text-2xl font-bold"
                            :class="getChangeColor(getTotalImpact())"
                        >
                            {{ formatCurrency(getTotalImpact()) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Change from baseline
                        </p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <p class="text-xs text-gray-600 dark:text-gray-300">Scenario Range</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ formatCurrency(modeled.best_case - modeled.worst_case) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Best to worst case spread
                        </p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <p class="text-xs text-gray-600 dark:text-gray-300">Risk/Reward Ratio</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {{ getRiskRewardRatio() }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Upside vs downside potential
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ScenarioModeling',

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
         * Period type (week, month, quarter)
         */
        periodType: {
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
            }),
        },
    },

    emits: ['scenarios-updated', 'data-loaded', 'error'],

    data() {
        return {
            isLoading: true,
            baseline: {
                weighted: 0,
                best_case: 0,
                worst_case: 0,
            },
            modeled: {
                weighted: 0,
                best_case: 0,
                worst_case: 0,
            },
            assumptions: {
                conversionMultiplier: 1.0,
                valueMultiplier: 1.0,
                winRateAdjustment: 0,
                velocityFactor: 1.0,
            },
            baselineAssumptions: {
                conversionMultiplier: 1.0,
                valueMultiplier: 1.0,
                winRateAdjustment: 0,
                velocityFactor: 1.0,
            },
            insights: [],
            comparisonChart: null,
            impactChart: null,
            chartIds: {
                comparison: `scenario-comparison-${this._uid}`,
                impact: `impact-analysis-${this._uid}`,
            },
        };
    },

    computed: {
        /**
         * Whether to show change indicators
         */
        showChangeIndicators() {
            return (
                this.assumptions.conversionMultiplier !== 1.0 ||
                this.assumptions.valueMultiplier !== 1.0 ||
                this.assumptions.winRateAdjustment !== 0 ||
                this.assumptions.velocityFactor !== 1.0
            );
        },
    },

    mounted() {
        this.loadData();
    },

    beforeUnmount() {
        if (this.comparisonChart) {
            this.comparisonChart.destroy();
        }
        if (this.impactChart) {
            this.impactChart.destroy();
        }
    },

    methods: {
        /**
         * Load baseline scenario data from API
         */
        loadData() {
            this.isLoading = true;

            this.$axios
                .get(this.endpoints.scenarios, {
                    params: {
                        user_id: this.userId,
                        period_type: this.periodType,
                    },
                })
                .then((response) => {
                    const scenarios = response.data.data.scenarios;

                    this.baseline = {
                        weighted: scenarios.weighted?.value || 0,
                        best_case: scenarios.best_case?.value || 0,
                        worst_case: scenarios.worst_case?.value || 0,
                    };

                    this.modeled = { ...this.baseline };

                    this.$emit('data-loaded', {
                        baseline: this.baseline,
                        modeled: this.modeled,
                    });

                    this.generateInitialInsights();
                })
                .catch((error) => {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message:
                            error.response?.data?.message ||
                            'Failed to load scenario data.',
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
         * Reset assumptions to defaults
         */
        resetAssumptions() {
            this.assumptions = { ...this.baselineAssumptions };
            this.modeled = { ...this.baseline };
            this.generateInitialInsights();
            this.renderCharts();

            this.$emitter.emit('add-flash', {
                type: 'success',
                message: 'Assumptions reset to default values.',
            });
        },

        /**
         * Recalculate scenarios based on current assumptions
         */
        recalculateScenarios() {
            const conversionFactor =
                this.assumptions.conversionMultiplier *
                (1 + this.assumptions.winRateAdjustment / 100);

            const valueFactor = this.assumptions.valueMultiplier;

            const velocityImpact = this.assumptions.velocityFactor;

            this.modeled = {
                weighted: this.baseline.weighted * conversionFactor * valueFactor,
                best_case:
                    this.baseline.best_case *
                    valueFactor *
                    Math.min(velocityImpact, 1.5),
                worst_case:
                    this.baseline.worst_case *
                    conversionFactor *
                    valueFactor *
                    Math.max(1 / velocityImpact, 0.7),
            };

            this.generateInsights();
            this.renderCharts();

            this.$emit('scenarios-updated', {
                modeled: this.modeled,
                assumptions: this.assumptions,
            });
        },

        /**
         * Generate initial insights
         */
        generateInitialInsights() {
            this.insights = [];

            const spread = this.baseline.best_case - this.baseline.worst_case;
            const upside = this.baseline.best_case - this.baseline.weighted;
            const downside = this.baseline.weighted - this.baseline.worst_case;

            if (spread > 0) {
                const spreadPct = (spread / this.baseline.weighted) * 100;
                if (spreadPct > 100) {
                    this.insights.push(
                        'High variability in scenarios - consider refining deal qualification.'
                    );
                }
            }

            if (upside > downside * 1.5) {
                this.insights.push(
                    'Significant upside potential - focus on closing high-value opportunities.'
                );
            }
        },

        /**
         * Generate insights based on adjusted scenarios
         */
        generateInsights() {
            this.insights = [];

            const weightedChange = this.modeled.weighted - this.baseline.weighted;
            const changePercent = (weightedChange / this.baseline.weighted) * 100;

            if (Math.abs(changePercent) > 50) {
                this.insights.push(
                    `Significant ${changePercent > 0 ? 'increase' : 'decrease'} of ${Math.abs(changePercent).toFixed(1)}% in likely case forecast.`
                );
            }

            if (this.assumptions.conversionMultiplier > 1.3) {
                this.insights.push(
                    'High conversion multiplier - ensure this is realistic based on historical data.'
                );
            }

            if (this.assumptions.conversionMultiplier < 0.7) {
                this.insights.push(
                    'Low conversion multiplier - consider if market conditions justify this pessimism.'
                );
            }

            if (this.assumptions.winRateAdjustment > 15) {
                this.insights.push(
                    'Optimistic win rate adjustment - validate assumptions with recent performance trends.'
                );
            }

            if (this.assumptions.winRateAdjustment < -15) {
                this.insights.push(
                    'Conservative win rate adjustment - ensure this aligns with current pipeline quality.'
                );
            }

            const spread = this.modeled.best_case - this.modeled.worst_case;
            if (spread < this.baseline.best_case - this.baseline.worst_case) {
                this.insights.push('Scenario range has narrowed - forecast has become more predictable.');
            }

            if (this.insights.length === 0) {
                this.insights.push('Adjustments are within reasonable ranges.');
            }
        },

        /**
         * Render all charts
         */
        renderCharts() {
            this.renderComparisonChart();
            this.renderImpactChart();
        },

        /**
         * Render the scenario comparison chart
         */
        renderComparisonChart() {
            if (this.comparisonChart) {
                this.comparisonChart.destroy();
            }

            const baselineData = [
                this.baseline.worst_case,
                this.baseline.weighted,
                this.baseline.best_case,
            ];
            const modeledData = [
                this.modeled.worst_case,
                this.modeled.weighted,
                this.modeled.best_case,
            ];

            this.comparisonChart = new Chart(
                document.getElementById(this.chartIds.comparison),
                {
                    type: 'bar',
                    data: {
                        labels: ['Worst Case', 'Likely Case', 'Best Case'],
                        datasets: [
                            {
                                label: 'Baseline',
                                data: baselineData,
                                backgroundColor: [
                                    'rgba(239, 68, 68, 0.5)',
                                    'rgba(59, 130, 246, 0.5)',
                                    'rgba(34, 197, 94, 0.5)',
                                ],
                                borderColor: [
                                    'rgba(239, 68, 68, 1)',
                                    'rgba(59, 130, 246, 1)',
                                    'rgba(34, 197, 94, 1)',
                                ],
                                borderWidth: 1,
                            },
                            {
                                label: 'Modeled',
                                data: modeledData,
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
                                borderWidth: 2,
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
                }
            );
        },

        /**
         * Render the impact analysis chart
         */
        renderImpactChart() {
            if (this.impactChart) {
                this.impactChart.destroy();
            }

            const impacts = [
                ((this.modeled.weighted - this.baseline.weighted) / this.baseline.weighted) * 100 || 0,
                ((this.modeled.best_case - this.baseline.best_case) / this.baseline.best_case) * 100 || 0,
                ((this.modeled.worst_case - this.baseline.worst_case) / this.baseline.worst_case) * 100 || 0,
            ];

            const colors = impacts.map((impact) => {
                if (impact > 0) {
                    return {
                        bg: 'rgba(34, 197, 94, 0.8)',
                        border: 'rgba(34, 197, 94, 1)',
                    };
                } else if (impact < 0) {
                    return {
                        bg: 'rgba(239, 68, 68, 0.8)',
                        border: 'rgba(239, 68, 68, 1)',
                    };
                }
                return {
                    bg: 'rgba(156, 163, 175, 0.8)',
                    border: 'rgba(156, 163, 175, 1)',
                };
            });

            this.impactChart = new Chart(document.getElementById(this.chartIds.impact), {
                type: 'bar',
                data: {
                    labels: ['Likely Case', 'Best Case', 'Worst Case'],
                    datasets: [
                        {
                            label: 'Impact (%)',
                            data: impacts,
                            backgroundColor: colors.map((c) => c.bg),
                            borderColor: colors.map((c) => c.border),
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
                            border: {
                                dash: [8, 4],
                            },
                            ticks: {
                                callback: function (value) {
                                    return value.toFixed(1) + '%';
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
         * Get total impact across all scenarios
         */
        getTotalImpact() {
            return this.modeled.weighted - this.baseline.weighted;
        },

        /**
         * Get risk/reward ratio
         */
        getRiskRewardRatio() {
            const upside = this.modeled.best_case - this.modeled.weighted;
            const downside = this.modeled.weighted - this.modeled.worst_case;

            if (downside === 0) {
                return 'N/A';
            }

            return (upside / downside).toFixed(2);
        },

        /**
         * Get color class for change values
         */
        getChangeColor(change) {
            if (change > 0) {
                return 'text-green-600 dark:text-green-400';
            } else if (change < 0) {
                return 'text-red-600 dark:text-red-400';
            }
            return 'text-gray-600 dark:text-gray-400';
        },

        /**
         * Format change value with sign
         */
        formatChange(change) {
            const sign = change > 0 ? '+' : '';
            return sign + this.formatCurrency(change);
        },

        /**
         * Format percentage with sign
         */
        formatPercentage(value) {
            const sign = value > 0 ? '+' : '';
            return `${sign}${value}%`;
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

<style scoped>
/* Custom range slider styling */
input[type='range']::-webkit-slider-thumb {
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
}

input[type='range']::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
    border: none;
}

input[type='range']::-webkit-slider-runnable-track {
    width: 100%;
    height: 8px;
    cursor: pointer;
    background: #e5e7eb;
    border-radius: 4px;
}

input[type='range']::-moz-range-track {
    width: 100%;
    height: 8px;
    cursor: pointer;
    background: #e5e7eb;
    border-radius: 4px;
}

.dark input[type='range']::-webkit-slider-runnable-track {
    background: #374151;
}

.dark input[type='range']::-moz-range-track {
    background: #374151;
}
</style>
