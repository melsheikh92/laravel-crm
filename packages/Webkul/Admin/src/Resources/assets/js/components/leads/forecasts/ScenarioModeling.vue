<template>
    <div class="flex flex-col gap-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                {{ strings.title }}
            </h1>
            
            <div class="flex gap-2">
                <select 
                    v-model="periodType" 
                    @change="fetchScenarios"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                >
                    <option value="month">{{ strings.period_monthly }}</option>
                    <option value="quarter">{{ strings.period_quarterly }}</option>
                    <option value="year">{{ strings.period_yearly }}</option>
                </select>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center p-8">
            <div class="shimmer w-full h-64 rounded-lg"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 p-4 rounded-md border border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <span class="icon-error text-red-400"></span>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">{{ strings.error_title }}</h3>
                    <div class="mt-2 text-sm text-red-700">
                        {{ error }}
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Weighted Scenario -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ strings.weighted_forecast }}</h3>
                    <span class="p-2 bg-indigo-100 rounded-full dark:bg-indigo-900">
                        <span class="icon-stats-up text-indigo-600 dark:text-indigo-400"></span>
                    </span>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ formatCurrency(scenarios.weighted?.value || 0) }}
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ scenarios.weighted?.description }}
                </p>
            </div>

            <!-- Best Case Scenario -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border-l-4 border-emerald-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ strings.best_case }}</h3>
                    <span class="p-2 bg-emerald-100 rounded-full dark:bg-emerald-900">
                        <span class="icon-stats-up text-emerald-600 dark:text-emerald-400"></span>
                    </span>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ formatCurrency(scenarios.best_case?.value || 0) }}
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ scenarios.best_case?.description }}
                </p>
            </div>

            <!-- Worst Case Scenario -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border-l-4 border-amber-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ strings.worst_case }}</h3>
                    <span class="p-2 bg-amber-100 rounded-full dark:bg-amber-900">
                        <span class="icon-warning text-amber-600 dark:text-amber-400"></span>
                    </span>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ formatCurrency(scenarios.worst_case?.value || 0) }}
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ scenarios.worst_case?.description }}
                </p>
            </div>
        </div>

        <!-- Charts/Comparison Section -->
        <div v-if="!loading && !error" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ strings.pipeline_metrics }}</h3>
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="px-4 py-5 bg-gray-50 dark:bg-gray-800 shadow rounded-lg overflow-hidden sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ strings.total_open_leads }}</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ leadBreakdown.total_leads }}</dd>
                    </div>
                    <div class="px-4 py-5 bg-gray-50 dark:bg-gray-800 shadow rounded-lg overflow-hidden sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ strings.avg_deal_value }}</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ formatCurrency(leadBreakdown.average_value) }}</dd>
                    </div>
                </dl>
            </div>

             <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ strings.ai_recommendations }}</h3>
                <div v-if="recommendations.length > 0" class="space-y-4">
                    <div v-for="(rec, index) in recommendations" :key="index" class="flex gap-3">
                        <div class="flex-shrink-0">
                             <span class="icon-info text-blue-500"></span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ rec }}</p>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-500 italic">{{ strings.no_recommendations }}</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        endpoint: {
            type: String,
            required: true,
            default: '/admin/forecasts/analytics/scenarios'
        },
        userId: {
            type: [Number, String],
            default: null
        },
        strings: {
            type: Object,
            required: true,
            default: () => ({})
        }
    },

    data() {
        return {
            loading: true,
            error: null,
            periodType: 'month',
            scenarios: {},
            scenarioComparison: [],
            leadBreakdown: {
                total_leads: 0,
                total_value: 0,
                average_value: 0
            },
            recommendations: []
        }
    },

    mounted() {
        this.fetchScenarios();
    },

    methods: {
        async fetchScenarios() {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    period_type: this.periodType
                };
                
                if (this.userId) {
                    params.user_id = this.userId;
                }

                // Temporary workaround if props aren't passed perfectly, try to use default endpoint
                const url = this.endpoint || '/admin/forecasts/analytics/scenarios';
                
                const response = await this.$axios.get(url, { params });
                
                const data = response.data.data;
                this.scenarios = data.scenarios;
                this.scenarioComparison = data.scenario_comparison;
                this.leadBreakdown = data.lead_breakdown || { total_leads: 0, total_value: 0, average_value: 0 };
                this.recommendations = data.recommendations || [];
                
            } catch (err) {
                console.error('Error fetching scenarios:', err);
                this.error = err.response?.data?.message || this.strings.error_loading_scenarios || 'Failed to load scenario data.';
            } finally {
                this.loading = false;
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value || 0);
        }
    }
}
</script>
