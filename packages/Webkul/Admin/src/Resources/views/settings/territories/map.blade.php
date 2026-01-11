<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.territories.map.title')
    </x-slot>

    <!-- Head Details Section -->
    {!! view_render_event('admin.settings.territories.map.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('admin.settings.territories.map.header.left.before') !!}

        <div class="grid gap-1.5">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.territories.map" />

                <p class="text-2xl font-semibold dark:text-white">
                    @lang('admin::app.settings.territories.map.title')
                </p>
            </div>
        </div>

        {!! view_render_event('admin.settings.territories.map.header.left.after') !!}

        <!-- Actions -->
        {!! view_render_event('admin.settings.territories.map.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('settings.territories.view'))
                <a
                    href="{{ route('admin.settings.territories.index') }}"
                    class="secondary-button"
                >
                    @lang('admin::app.settings.territories.map.back-btn')
                </a>
            @endif
        </div>

        {!! view_render_event('admin.settings.territories.map.header.right.after') !!}
    </div>

    {!! view_render_event('admin.settings.territories.map.header.after') !!}

    <!-- Body Component -->
    {!! view_render_event('admin.settings.territories.map.content.before') !!}

    <v-territory-map>
        <!-- Shimmer -->
        <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
                <div class="light-shimmer-bg dark:shimmer h-[600px] rounded-lg"></div>
            </div>

            <div class="flex w-[320px] max-w-full flex-col gap-4 max-sm:w-full">
                <div class="light-shimmer-bg dark:shimmer h-[200px] rounded-lg"></div>
                <div class="light-shimmer-bg dark:shimmer h-[380px] rounded-lg"></div>
            </div>
        </div>
    </v-territory-map>

    {!! view_render_event('admin.settings.territories.map.content.after') !!}

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
            id="v-territory-map-template"
        >
            <!-- Shimmer -->
            <template v-if="isLoading">
                <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
                    <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
                        <div class="light-shimmer-bg dark:shimmer h-[600px] rounded-lg"></div>
                    </div>

                    <div class="flex w-[320px] max-w-full flex-col gap-4 max-sm:w-full">
                        <div class="light-shimmer-bg dark:shimmer h-[200px] rounded-lg"></div>
                        <div class="light-shimmer-bg dark:shimmer h-[380px] rounded-lg"></div>
                    </div>
                </div>
            </template>

            <!-- Map Content -->
            <template v-else>
                <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
                    <!-- Left Section - Map -->
                    <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
                        <!-- Map Container -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold dark:text-white">
                                        @lang('admin::app.settings.territories.map.map-title')
                                    </h3>

                                    <!-- Territory Type Filter -->
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.settings.territories.map.filter-by-type'):
                                        </label>
                                        <select
                                            v-model="typeFilter"
                                            @change="loadTerritories"
                                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                        >
                                            <option value="">@lang('admin::app.settings.territories.map.all-types')</option>
                                            <option value="geographic">@lang('admin::app.settings.territories.map.geographic')</option>
                                            <option value="account-based">@lang('admin::app.settings.territories.map.account-based')</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4">
                                <div
                                    :id="$.uid + '_map'"
                                    class="h-[550px] w-full"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section - Territory List & Stats -->
                    <div class="flex w-[320px] max-w-full flex-col gap-4 max-sm:w-full">
                        <!-- Map Statistics -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h3 class="text-base font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.map.statistics')
                                </h3>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.settings.territories.map.total-territories')
                                        </span>
                                        <span class="text-lg font-bold dark:text-white">
                                            @{{ statistics.total_territories }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.settings.territories.map.total-assignments')
                                        </span>
                                        <span class="text-lg font-bold text-blue-600">
                                            @{{ statistics.total_assignments }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.settings.territories.map.total-leads')
                                        </span>
                                        <span class="text-lg font-bold text-green-600">
                                            @{{ statistics.total_leads }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.settings.territories.map.total-organizations')
                                        </span>
                                        <span class="text-lg font-bold text-purple-600">
                                            @{{ statistics.total_organizations }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.settings.territories.map.total-persons')
                                        </span>
                                        <span class="text-lg font-bold text-orange-600">
                                            @{{ statistics.total_persons }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Territory List -->
                        <div class="box-shadow rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                <h3 class="text-base font-semibold dark:text-white">
                                    @lang('admin::app.settings.territories.map.territories-list')
                                </h3>
                            </div>
                            <div class="max-h-[360px] overflow-y-auto p-4">
                                <div
                                    v-for="feature in features"
                                    :key="feature.properties.id"
                                    class="mb-2 cursor-pointer rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                    @click="centerOnTerritory(feature)"
                                >
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="font-medium dark:text-white">
                                            @{{ feature.properties.name }}
                                        </span>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="feature.properties.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                                        >
                                            @{{ feature.properties.status }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        <div>@lang('admin::app.settings.territories.map.code'): @{{ feature.properties.code }}</div>
                                        <div v-if="feature.properties.assignment_count !== undefined">
                                            @lang('admin::app.settings.territories.map.assignments'): @{{ feature.properties.assignment_count }}
                                        </div>
                                    </div>
                                </div>
                                <div v-if="features.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.settings.territories.map.no-territories')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </script>

        <script type="module">
            app.component('v-territory-map', {
                template: '#v-territory-map-template',

                data() {
                    return {
                        isLoading: true,
                        map: null,
                        geoJsonLayer: null,
                        features: [],
                        typeFilter: '',
                        statistics: {
                            total_territories: 0,
                            total_assignments: 0,
                            total_leads: 0,
                            total_organizations: 0,
                            total_persons: 0,
                        },
                    }
                },

                mounted() {
                    this.initializeMap();
                    this.loadTerritories();
                },

                methods: {
                    /**
                     * Initialize Leaflet map.
                     */
                    initializeMap() {
                        // Create map instance
                        this.map = L.map(this.$.uid + '_map').setView([20, 0], 2);

                        // Add OpenStreetMap tile layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                            maxZoom: 18,
                        }).addTo(this.map);

                        // Add legend
                        this.addLegend();
                    },

                    /**
                     * Load territories from API.
                     */
                    async loadTerritories() {
                        this.isLoading = true;

                        try {
                            const params = new URLSearchParams();
                            if (this.typeFilter) {
                                params.append('type', this.typeFilter);
                            }

                            const response = await this.$axios.get(
                                "{{ route('admin.settings.territories.map.with_assignments') }}" +
                                (params.toString() ? '?' + params.toString() : '')
                            );

                            const data = response.data;

                            // Store features
                            this.features = data.features;

                            // Calculate statistics
                            this.calculateStatistics();

                            // Remove existing GeoJSON layer
                            if (this.geoJsonLayer) {
                                this.map.removeLayer(this.geoJsonLayer);
                            }

                            // Add GeoJSON layer with territories
                            this.geoJsonLayer = L.geoJSON(data, {
                                style: this.getFeatureStyle,
                                onEachFeature: this.onEachFeature,
                            }).addTo(this.map);

                            // Fit map to show all territories
                            if (this.features.length > 0) {
                                this.map.fitBounds(this.geoJsonLayer.getBounds(), {
                                    padding: [50, 50]
                                });
                            }

                            this.isLoading = false;
                        } catch (error) {
                            this.isLoading = false;
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || '@lang('admin::app.settings.territories.map.error-loading-data')',
                            });
                        }
                    },

                    /**
                     * Calculate statistics from features.
                     */
                    calculateStatistics() {
                        this.statistics.total_territories = this.features.length;
                        this.statistics.total_assignments = 0;
                        this.statistics.total_leads = 0;
                        this.statistics.total_organizations = 0;
                        this.statistics.total_persons = 0;

                        this.features.forEach(feature => {
                            const props = feature.properties;
                            this.statistics.total_assignments += props.assignment_count || 0;
                            this.statistics.total_leads += props.lead_count || 0;
                            this.statistics.total_organizations += props.organization_count || 0;
                            this.statistics.total_persons += props.person_count || 0;
                        });
                    },

                    /**
                     * Get style for a feature based on its properties.
                     */
                    getFeatureStyle(feature) {
                        const isActive = feature.properties.status === 'active';
                        const assignmentCount = feature.properties.assignment_count || 0;

                        // Color based on assignment density
                        let fillColor = '#94a3b8'; // Gray for no assignments
                        if (assignmentCount > 50) {
                            fillColor = '#dc2626'; // Red for high density
                        } else if (assignmentCount > 20) {
                            fillColor = '#f97316'; // Orange for medium-high density
                        } else if (assignmentCount > 10) {
                            fillColor = '#eab308'; // Yellow for medium density
                        } else if (assignmentCount > 0) {
                            fillColor = '#22c55e'; // Green for low density
                        }

                        return {
                            fillColor: fillColor,
                            weight: 2,
                            opacity: isActive ? 1 : 0.5,
                            color: isActive ? '#1e293b' : '#64748b',
                            dashArray: isActive ? '' : '3',
                            fillOpacity: 0.5,
                        };
                    },

                    /**
                     * Bind popup and events to each feature.
                     */
                    onEachFeature(feature, layer) {
                        const props = feature.properties;

                        // Create popup content
                        const popupContent = `
                            <div class="territory-popup">
                                <h3>${props.name}</h3>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.map.code'):</span>
                                    <span class="info-value">${props.code}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.map.type'):</span>
                                    <span class="info-value">${props.type}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.map.status'):</span>
                                    <span class="info-value">${props.status}</span>
                                </div>
                                ${props.owner_name ? `
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.map.owner'):</span>
                                    <span class="info-value">${props.owner_name}</span>
                                </div>
                                ` : ''}
                                ${props.assignment_count !== undefined ? `
                                <hr style="margin: 0.5rem 0; border-color: #e5e7eb;">
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.map.total-assignments'):</span>
                                    <span class="info-value">${props.assignment_count}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.map.leads'):</span>
                                    <span class="info-value">${props.lead_count || 0}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.map.organizations'):</span>
                                    <span class="info-value">${props.organization_count || 0}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">@lang('admin::app.settings.territories.map.persons'):</span>
                                    <span class="info-value">${props.person_count || 0}</span>
                                </div>
                                ` : ''}
                            </div>
                        `;

                        layer.bindPopup(popupContent);

                        // Highlight on hover
                        layer.on({
                            mouseover: (e) => {
                                e.target.setStyle({
                                    weight: 3,
                                    fillOpacity: 0.7
                                });
                            },
                            mouseout: (e) => {
                                this.geoJsonLayer.resetStyle(e.target);
                            }
                        });
                    },

                    /**
                     * Center map on a specific territory.
                     */
                    centerOnTerritory(feature) {
                        // Find the layer corresponding to this feature
                        this.geoJsonLayer.eachLayer((layer) => {
                            if (layer.feature.properties.id === feature.properties.id) {
                                // Center and zoom to the layer
                                this.map.fitBounds(layer.getBounds(), {
                                    padding: [50, 50],
                                    maxZoom: 10
                                });

                                // Open popup
                                layer.openPopup();
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
                                <h4 style="margin: 0 0 5px 0; font-weight: 600;">@lang('admin::app.settings.territories.map.assignment-density')</h4>
                                <div><i style="background: #dc2626"></i> 50+ @lang('admin::app.settings.territories.map.assignments')</div>
                                <div><i style="background: #f97316"></i> 21-50 @lang('admin::app.settings.territories.map.assignments')</div>
                                <div><i style="background: #eab308"></i> 11-20 @lang('admin::app.settings.territories.map.assignments')</div>
                                <div><i style="background: #22c55e"></i> 1-10 @lang('admin::app.settings.territories.map.assignments')</div>
                                <div><i style="background: #94a3b8"></i> @lang('admin::app.settings.territories.map.no-assignments')</div>
                            `;
                            return div;
                        };

                        legend.addTo(this.map);
                    },
                },

                beforeUnmount() {
                    // Clean up map instance
                    if (this.map) {
                        this.map.remove();
                    }
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
