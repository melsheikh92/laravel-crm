<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.territories.coverage.title')
    </x-slot>

    <!-- Head Details Section -->
    {!! view_render_event('admin.settings.territories.coverage.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('admin.settings.territories.coverage.header.left.before') !!}

        <div class="grid gap-1.5">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.territories.map.coverage" />

                <p class="text-2xl font-semibold dark:text-white">
                    @lang('admin::app.settings.territories.coverage.title')
                </p>
            </div>
        </div>

        {!! view_render_event('admin.settings.territories.coverage.header.left.after') !!}

        <!-- Actions -->
        {!! view_render_event('admin.settings.territories.coverage.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('settings.territories.view'))
                <a
                    href="{{ route('admin.settings.territories.map.index') }}"
                    class="secondary-button"
                >
                    @lang('admin::app.settings.territories.coverage.back-to-map')
                </a>
            @endif
        </div>

        {!! view_render_event('admin.settings.territories.coverage.header.right.after') !!}
    </div>

    {!! view_render_event('admin.settings.territories.coverage.header.after') !!}

    <!-- Body Component -->
    {!! view_render_event('admin.settings.territories.coverage.content.before') !!}

    <v-territory-coverage>
        <!-- Shimmer -->
        <div class="mt-3.5 flex flex-col gap-4">
            <div class="grid grid-cols-4 gap-4 max-lg:grid-cols-2 max-sm:grid-cols-1">
                <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
            </div>
            <div class="light-shimmer-bg dark:shimmer h-[600px] rounded-lg"></div>
        </div>
    </v-territory-coverage>

    {!! view_render_event('admin.settings.territories.coverage.content.after') !!}

    @pushOnce('styles')
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        />
        <style>
            .leaflet-container {
                height: 100%;
                width: 100%;
                border-radius: 0.5rem;
            }
            .territory-popup {
                font-family: inherit;
            }
            .territory-popup h3 {
                font-size: 1rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }
            .territory-popup .info-row {
                display: flex;
                justify-content: space-between;
                padding: 0.25rem 0;
                font-size: 0.875rem;
            }
            .territory-popup .info-label {
                color: #6B7280;
                font-weight: 500;
            }
            .territory-popup .info-value {
                font-weight: 600;
            }
            .legend {
                line-height: 1.5;
                color: #555;
                background: white;
                padding: 10px;
                border-radius: 5px;
                box-shadow: 0 0 15px rgba(0,0,0,0.2);
            }
            .legend i {
                width: 18px;
                height: 18px;
                float: left;
                margin-right: 8px;
                opacity: 0.7;
            }
        </style>
    @endPushOnce

    @pushOnce('scripts')
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        >
        </script>

        <script
            type="text/x-template"
            id="v-territory-coverage-template"
        >
            <!-- Shimmer -->
            <template v-if="isLoading">
                <div class="mt-3.5 flex flex-col gap-4">
                    <div class="grid grid-cols-4 gap-4 max-lg:grid-cols-2 max-sm:grid-cols-1">
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[120px] rounded-lg"></div>
                    </div>
                    <div class="light-shimmer-bg dark:shimmer h-[600px] rounded-lg"></div>
                </div>
            </template>

            <!-- Content -->
            <template v-else>
                <div class="mt-3.5 flex flex-col gap-4">
                    <!-- Coverage Statistics Cards -->
                    <div class="grid grid-cols-4 gap-4 max-lg:grid-cols-2 max-sm:grid-cols-1">
                        <!-- Total Territories -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-4">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.territories.coverage.total-territories')
                            </div>
                            <div class="text-3xl font-bold dark:text-white">
                                @{{ statistics.total_territories || 0 }}
                            </div>
                        </div>

                        <!-- Territories with Coverage -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-4">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.territories.coverage.with-coverage')
                            </div>
                            <div class="text-3xl font-bold text-green-600">
                                @{{ statistics.territories_with_coverage || 0 }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                @{{ coveragePercentage }}% @lang('admin::app.settings.territories.coverage.coverage-rate')
                            </div>
                        </div>

                        <!-- Coverage Gaps -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-4">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.territories.coverage.coverage-gaps')
                            </div>
                            <div class="text-3xl font-bold text-red-600">
                                @{{ statistics.territories_without_coverage || 0 }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                @lang('admin::app.settings.territories.coverage.need-attention')
                            </div>
                        </div>

                        <!-- Average Density -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-4">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.territories.coverage.avg-density')
                            </div>
                            <div class="text-3xl font-bold text-blue-600">
                                @{{ avgDensity }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                @lang('admin::app.settings.territories.coverage.entities-per-territory')
                            </div>
                        </div>
                    </div>

                    <!-- Coverage Map -->
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.coverage.density-map')
                                </h3>

                                <!-- Visualization Controls -->
                                <div class="flex items-center gap-4">
                                    <!-- Density Layer Toggle -->
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            v-model="showDensityLayer"
                                            @change="updateMap"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.settings.territories.coverage.show-density')
                                        </span>
                                    </label>

                                    <!-- Coverage Filter -->
                                    <select
                                        v-model="coverageFilter"
                                        @change="updateMap"
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    >
                                        <option value="all">@lang('admin::app.settings.territories.coverage.all-territories')</option>
                                        <option value="with-coverage">@lang('admin::app.settings.territories.coverage.with-coverage-only')</option>
                                        <option value="gaps">@lang('admin::app.settings.territories.coverage.gaps-only')</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <div
                                :id="$.uid + '_coverage_map'"
                                class="h-[550px] w-full"
                            ></div>
                        </div>
                    </div>

                    <!-- Coverage Analysis Grid -->
                    <div class="grid grid-cols-2 gap-4 max-lg:grid-cols-1">
                        <!-- Top Density Territories -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h3 class="text-base font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.coverage.highest-density')
                                </h3>
                            </div>
                            <div class="p-4">
                                <div
                                    v-for="(territory, index) in topDensityTerritories"
                                    :key="territory.properties.id"
                                    class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                            @{{ index + 1 }}
                                        </div>
                                        <div>
                                            <div class="font-medium dark:text-white">@{{ territory.properties.name }}</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">@{{ territory.properties.code }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-blue-600">@{{ territory.properties.total_entities }}</div>
                                        <div class="text-xs text-gray-500">@lang('admin::app.settings.territories.coverage.entities')</div>
                                    </div>
                                </div>
                                <div v-if="topDensityTerritories.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.settings.territories.coverage.no-data')
                                </div>
                            </div>
                        </div>

                        <!-- Coverage Gaps -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h3 class="text-base font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.coverage.coverage-gaps-list')
                                </h3>
                            </div>
                            <div class="max-h-[400px] overflow-y-auto p-4">
                                <div
                                    v-for="territory in coverageGaps"
                                    :key="territory.properties.id"
                                    class="mb-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950"
                                >
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-medium text-red-800 dark:text-red-300">@{{ territory.properties.name }}</div>
                                            <div class="text-xs text-red-600 dark:text-red-400">@{{ territory.properties.code }}</div>
                                        </div>
                                        <div class="text-sm font-medium text-red-600">
                                            @lang('admin::app.settings.territories.coverage.no-coverage')
                                        </div>
                                    </div>
                                </div>
                                <div v-if="coverageGaps.length === 0" class="rounded-lg border border-green-200 bg-green-50 p-4 text-center dark:border-green-900 dark:bg-green-950">
                                    <div class="text-sm font-medium text-green-800 dark:text-green-300">
                                        @lang('admin::app.settings.territories.coverage.no-gaps')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </script>

        <script type="module">
            app.component('v-territory-coverage', {
                template: '#v-territory-coverage-template',

                data() {
                    return {
                        isLoading: true,
                        map: null,
                        geoJsonLayer: null,
                        features: [],
                        statistics: {},
                        showDensityLayer: true,
                        coverageFilter: 'all',
                    }
                },

                computed: {
                    /**
                     * Calculate coverage percentage.
                     */
                    coveragePercentage() {
                        if (!this.statistics.total_territories) return 0;
                        return Math.round((this.statistics.territories_with_coverage / this.statistics.total_territories) * 100);
                    },

                    /**
                     * Calculate average density formatted.
                     */
                    avgDensity() {
                        return this.statistics.average_density ? Math.round(this.statistics.average_density * 10) / 10 : 0;
                    },

                    /**
                     * Get top 5 territories by density.
                     */
                    topDensityTerritories() {
                        return [...this.features]
                            .sort((a, b) => b.properties.total_entities - a.properties.total_entities)
                            .slice(0, 5);
                    },

                    /**
                     * Get territories with no coverage.
                     */
                    coverageGaps() {
                        return this.features.filter(f => f.properties.total_entities === 0);
                    },

                    /**
                     * Get filtered features based on coverage filter.
                     */
                    filteredFeatures() {
                        if (this.coverageFilter === 'with-coverage') {
                            return this.features.filter(f => f.properties.total_entities > 0);
                        } else if (this.coverageFilter === 'gaps') {
                            return this.features.filter(f => f.properties.total_entities === 0);
                        }
                        return this.features;
                    },
                },

                mounted() {
                    this.initializeMap();
                    this.loadCoverageData();
                },

                methods: {
                    /**
                     * Initialize Leaflet map.
                     */
                    initializeMap() {
                        this.map = L.map(this.$.uid + '_coverage_map').setView([20, 0], 2);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                            maxZoom: 18,
                        }).addTo(this.map);

                        this.addLegend();
                    },

                    /**
                     * Load coverage data from API.
                     */
                    async loadCoverageData() {
                        this.isLoading = true;

                        try {
                            const response = await this.$axios.get("{{ route('admin.settings.territories.map.coverage_data') }}");
                            const data = response.data;

                            this.features = data.features;
                            this.statistics = data.statistics;

                            this.updateMap();
                            this.isLoading = false;
                        } catch (error) {
                            this.isLoading = false;
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || '@lang('admin::app.settings.territories.coverage.error-loading')',
                            });
                        }
                    },

                    /**
                     * Update map with current filters.
                     */
                    updateMap() {
                        if (this.geoJsonLayer) {
                            this.map.removeLayer(this.geoJsonLayer);
                        }

                        const featuresToShow = this.filteredFeatures;

                        if (featuresToShow.length > 0) {
                            this.geoJsonLayer = L.geoJSON({
                                type: 'FeatureCollection',
                                features: featuresToShow,
                            }, {
                                style: this.getFeatureStyle,
                                onEachFeature: this.onEachFeature,
                            }).addTo(this.map);

                            this.map.fitBounds(this.geoJsonLayer.getBounds(), {
                                padding: [50, 50]
                            });
                        }
                    },

                    /**
                     * Get style for a feature based on density.
                     */
                    getFeatureStyle(feature) {
                        const density = feature.properties.total_entities || 0;
                        const isActive = feature.properties.status === 'active';

                        let fillColor = '#94a3b8'; // Gray for no coverage

                        if (this.showDensityLayer && density > 0) {
                            // Gradient from green (low) to red (high)
                            if (density > 100) {
                                fillColor = '#7f1d1d'; // Dark red for very high density
                            } else if (density > 50) {
                                fillColor = '#dc2626'; // Red for high density
                            } else if (density > 30) {
                                fillColor = '#f97316'; // Orange for medium-high
                            } else if (density > 15) {
                                fillColor = '#eab308'; // Yellow for medium
                            } else if (density > 5) {
                                fillColor = '#84cc16'; // Light green for low-medium
                            } else {
                                fillColor = '#22c55e'; // Green for low density
                            }
                        }

                        return {
                            fillColor: fillColor,
                            weight: 2,
                            opacity: isActive ? 1 : 0.5,
                            color: isActive ? '#1e293b' : '#64748b',
                            dashArray: isActive ? '' : '3',
                            fillOpacity: 0.6,
                        };
                    },

                    /**
                     * Bind popup and events to each feature.
                     */
                    onEachFeature(feature, layer) {
                        const props = feature.properties;

                        const popupContent = `
                            <div class="territory-popup">
                                <h3>${props.name}</h3>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.coverage.code'):</span>
                                    <span class="info-value">${props.code}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.coverage.status'):</span>
                                    <span class="info-value">${props.status}</span>
                                </div>
                                <hr style="margin: 0.5rem 0; border-color: #e5e7eb;">
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.coverage.total-entities'):</span>
                                    <span class="info-value">${props.total_entities}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.coverage.leads'):</span>
                                    <span class="info-value">${props.lead_count || 0}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.coverage.organizations'):</span>
                                    <span class="info-value">${props.organization_count || 0}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.coverage.persons'):</span>
                                    <span class="info-value">${props.person_count || 0}</span>
                                </div>
                                <hr style="margin: 0.5rem 0; border-color: #e5e7eb;">
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.coverage.density-score'):</span>
                                    <span class="info-value">${props.density_score}</span>
                                </div>
                            </div>
                        `;

                        layer.bindPopup(popupContent);

                        layer.on({
                            mouseover: (e) => {
                                e.target.setStyle({
                                    weight: 3,
                                    fillOpacity: 0.8
                                });
                            },
                            mouseout: (e) => {
                                this.geoJsonLayer.resetStyle(e.target);
                            }
                        });
                    },

                    /**
                     * Add legend to the map.
                     */
                    addLegend() {
                        const legend = L.control({ position: 'bottomright' });

                        legend.onAdd = function (map) {
                            const div = L.DomUtil.create('div', 'legend');
                            div.innerHTML = `
                                <h4 style="margin: 0 0 5px 0; font-weight: 600;">@lang('admin::app.settings.territories.coverage.entity-density')</h4>
                                <div><i style="background: #7f1d1d"></i> 100+ @lang('admin::app.settings.territories.coverage.entities')</div>
                                <div><i style="background: #dc2626"></i> 51-100 @lang('admin::app.settings.territories.coverage.entities')</div>
                                <div><i style="background: #f97316"></i> 31-50 @lang('admin::app.settings.territories.coverage.entities')</div>
                                <div><i style="background: #eab308"></i> 16-30 @lang('admin::app.settings.territories.coverage.entities')</div>
                                <div><i style="background: #84cc16"></i> 6-15 @lang('admin::app.settings.territories.coverage.entities')</div>
                                <div><i style="background: #22c55e"></i> 1-5 @lang('admin::app.settings.territories.coverage.entities')</div>
                                <div><i style="background: #94a3b8"></i> @lang('admin::app.settings.territories.coverage.no-coverage')</div>
                            `;
                            return div;
                        };

                        legend.addTo(this.map);
                    },
                },

                beforeUnmount() {
                    if (this.map) {
                        this.map.remove();
                    }
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
