<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.territories.analytics.title')
    </x-slot>

    <!-- Head Details Section -->
    {!! view_render_event('admin.settings.territories.analytics.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('admin.settings.territories.analytics.header.left.before') !!}

        <div class="grid gap-1.5">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.territories.analytics" />

                <p class="text-2xl font-semibold dark:text-white">
                    @lang('admin::app.settings.territories.analytics.title')
                </p>
            </div>
        </div>

        {!! view_render_event('admin.settings.territories.analytics.header.left.after') !!}

        <!-- Actions -->
        {!! view_render_event('admin.settings.territories.analytics.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('settings.territories.view'))
                <a
                    href="{{ route('admin.settings.territories.index') }}"
                    class="secondary-button"
                >
                    @lang('admin::app.settings.territories.analytics.back-btn')
                </a>
            @endif
        </div>

        {!! view_render_event('admin.settings.territories.analytics.header.right.after') !!}
    </div>

    {!! view_render_event('admin.settings.territories.analytics.header.after') !!}

    <!-- Body Component -->
    {!! view_render_event('admin.settings.territories.analytics.content.before') !!}

    <v-territory-analytics>
        <!-- Shimmer -->
        <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
                <!-- Overview Stats Shimmer -->
                <div class="grid grid-cols-4 gap-4 max-md:grid-cols-2 max-sm:grid-cols-1">
                    <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                    <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                    <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                    <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                </div>

                <!-- Chart Shimmer -->
                <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
            </div>

            <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
                <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
                <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
            </div>
        </div>
    </v-territory-analytics>

    {!! view_render_event('admin.settings.territories.analytics.content.after') !!}

    @pushOnce('scripts')
        <script
            type="module"
            src="{{ vite()->asset('js/chart.js') }}"
        >
        </script>

        <script
            type="text/x-template"
            id="v-territory-analytics-template"
        >
            <!-- Shimmer -->
            <template v-if="isLoading">
                <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
                    <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
                        <!-- Overview Stats Shimmer -->
                        <div class="grid grid-cols-4 gap-4 max-md:grid-cols-2 max-sm:grid-cols-1">
                            <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                            <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                            <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                            <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        </div>

                        <!-- Chart Shimmer -->
                        <div class="light-shimmer-bg dark:shimmer h-[400px] rounded-lg"></div>
                    </div>

                    <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
                        <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[300px] rounded-lg"></div>
                    </div>
                </div>
            </template>

            <!-- Analytics Content -->
            <template v-else>
                <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
                    <!-- Left Section -->
                    <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
                        <!-- Overview Stats -->
                        <div class="grid grid-cols-4 gap-4 max-md:grid-cols-2 max-sm:grid-cols-1">
                            <!-- Total Leads Card -->
                            <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.territories.analytics.total-leads')
                                </p>
                                <p class="text-2xl font-bold dark:text-white">
                                    @{{ overview.total_leads || 0 }}
                                </p>
                            </div>

                            <!-- Won Leads Card -->
                            <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.territories.analytics.won-leads')
                                </p>
                                <p class="text-2xl font-bold text-green-600">
                                    @{{ overview.won_leads || 0 }}
                                </p>
                            </div>

                            <!-- Conversion Rate Card -->
                            <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.territories.analytics.avg-conversion-rate')
                                </p>
                                <p class="text-2xl font-bold text-blue-600">
                                    @{{ overview.average_conversion_rate ? overview.average_conversion_rate.toFixed(2) : 0 }}%
                                </p>
                            </div>

                            <!-- Total Revenue Card -->
                            <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.territories.analytics.total-revenue')
                                </p>
                                <p class="text-2xl font-bold text-purple-600">
                                    @{{ formatCurrency(overview.total_revenue || 0) }}
                                </p>
                            </div>
                        </div>

                        <!-- Performance by Territory Chart -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.analytics.performance-by-territory')
                                </h3>
                            </div>

                            <canvas
                                :id="$.uid + '_territory_chart'"
                                class="w-full"
                            ></canvas>
                        </div>

                        <!-- Territory Performance Table -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h3 class="text-lg font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.analytics.territory-details')
                                </h3>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-950">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.settings.territories.analytics.territory-name')
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.settings.territories.analytics.leads')
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.settings.territories.analytics.won')
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.settings.territories.analytics.conversion-rate')
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.settings.territories.analytics.revenue')
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="territory in territories"
                                            :key="territory.territory_id"
                                            class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-950"
                                        >
                                            <td class="px-4 py-3 text-sm dark:text-gray-300">
                                                @{{ territory.name }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm dark:text-gray-300">
                                                @{{ territory.total_leads }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-green-600">
                                                @{{ territory.won_leads }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-blue-600">
                                                @{{ territory.conversion_rate.toFixed(2) }}%
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-purple-600">
                                                @{{ formatCurrency(territory.revenue) }}
                                            </td>
                                        </tr>
                                        <tr v-if="territories.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                                @lang('admin::app.settings.territories.analytics.no-data')
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
                        <!-- Top Territories by Revenue -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h3 class="text-base font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.analytics.top-by-revenue')
                                </h3>
                            </div>
                            <div class="p-4">
                                <div
                                    v-for="(territory, index) in topRevenue"
                                    :key="'revenue-' + territory.territory_id"
                                    class="mb-3 last:mb-0"
                                >
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-100 text-xs font-semibold text-purple-600 dark:bg-purple-900 dark:text-purple-300">
                                                @{{ index + 1 }}
                                            </span>
                                            <span class="text-sm font-medium dark:text-gray-300">@{{ territory.name }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-purple-600">
                                            @{{ formatCurrency(territory.revenue) }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="topRevenue.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.settings.territories.analytics.no-data')
                                </div>
                            </div>
                        </div>

                        <!-- Top Territories by Conversion Rate -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h3 class="text-base font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.analytics.top-by-conversion')
                                </h3>
                            </div>
                            <div class="p-4">
                                <div
                                    v-for="(territory, index) in topConversion"
                                    :key="'conversion-' + territory.territory_id"
                                    class="mb-3 last:mb-0"
                                >
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                                                @{{ index + 1 }}
                                            </span>
                                            <span class="text-sm font-medium dark:text-gray-300">@{{ territory.name }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-blue-600">
                                            @{{ territory.conversion_rate.toFixed(2) }}%
                                        </span>
                                    </div>
                                </div>
                                <div v-if="topConversion.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.settings.territories.analytics.no-data')
                                </div>
                            </div>
                        </div>

                        <!-- Top Territories by Lead Count -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h3 class="text-base font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.analytics.top-by-leads')
                                </h3>
                            </div>
                            <div class="p-4">
                                <div
                                    v-for="(territory, index) in topLeads"
                                    :key="'leads-' + territory.territory_id"
                                    class="mb-3 last:mb-0"
                                >
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-100 text-xs font-semibold text-green-600 dark:bg-green-900 dark:text-green-300">
                                                @{{ index + 1 }}
                                            </span>
                                            <span class="text-sm font-medium dark:text-gray-300">@{{ territory.name }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-green-600">
                                            @{{ territory.total_leads }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="topLeads.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.settings.territories.analytics.no-data')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </script>

        <script type="module">
            app.component('v-territory-analytics', {
                template: '#v-territory-analytics-template',

                data() {
                    return {
                        isLoading: true,
                        overview: {},
                        territories: [],
                        topRevenue: [],
                        topConversion: [],
                        topLeads: [],
                        chart: undefined,
                    }
                },

                mounted() {
                    this.fetchAnalytics();
                },

                methods: {
                    async fetchAnalytics() {
                        this.isLoading = true;

                        try {
                            // Fetch all analytics data in parallel
                            const [overviewResponse, territoriesResponse, topRevenueResponse, topConversionResponse, topLeadsResponse] = await Promise.all([
                                this.$axios.get("{{ route('admin.settings.territories.analytics.overview') }}"),
                                this.$axios.get("{{ route('admin.settings.territories.analytics.all-territories') }}"),
                                this.$axios.get("{{ route('admin.settings.territories.analytics.top.revenue') }}?limit=5"),
                                this.$axios.get("{{ route('admin.settings.territories.analytics.top.conversion-rate') }}?limit=5"),
                                this.$axios.get("{{ route('admin.settings.territories.analytics.top.lead-count') }}?limit=5"),
                            ]);

                            this.overview = overviewResponse.data.data;
                            this.territories = territoriesResponse.data.data;
                            this.topRevenue = topRevenueResponse.data.data;
                            this.topConversion = topConversionResponse.data.data;
                            this.topLeads = topLeadsResponse.data.data;

                            this.isLoading = false;

                            // Wait for DOM update before creating chart
                            this.$nextTick(() => {
                                this.prepareChart();
                            });
                        } catch (error) {
                            this.isLoading = false;
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || '@lang('admin::app.settings.territories.analytics.error-loading-data')',
                            });
                        }
                    },

                    prepareChart() {
                        if (this.chart) {
                            this.chart.destroy();
                        }

                        const territoryNames = this.territories.map(t => t.name);
                        const revenues = this.territories.map(t => t.revenue);
                        const conversionRates = this.territories.map(t => t.conversion_rate);

                        this.chart = new Chart(document.getElementById(this.$.uid + '_territory_chart'), {
                            type: 'bar',

                            data: {
                                labels: territoryNames,
                                datasets: [
                                    {
                                        label: '@lang('admin::app.settings.territories.analytics.revenue')',
                                        data: revenues,
                                        backgroundColor: 'rgba(147, 51, 234, 0.8)',
                                        yAxisID: 'y',
                                    },
                                    {
                                        label: '@lang('admin::app.settings.territories.analytics.conversion-rate')',
                                        data: conversionRates,
                                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                        yAxisID: 'y1',
                                    }
                                ],
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 2,

                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.datasetIndex === 0) {
                                                    // Revenue
                                                    label += new Intl.NumberFormat('en-US', {
                                                        style: 'currency',
                                                        currency: 'USD',
                                                    }).format(context.parsed.y);
                                                } else {
                                                    // Conversion rate
                                                    label += context.parsed.y.toFixed(2) + '%';
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },

                                scales: {
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: '@lang('admin::app.settings.territories.analytics.revenue')',
                                        },
                                        ticks: {
                                            callback: function(value) {
                                                return '$' + value.toLocaleString();
                                            }
                                        }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        beginAtZero: true,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: '@lang('admin::app.settings.territories.analytics.conversion-rate')',
                                        },
                                        ticks: {
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        },
                                        grid: {
                                            drawOnChartArea: false,
                                        },
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45
                                        }
                                    }
                                },
                            }
                        });
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(value);
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
