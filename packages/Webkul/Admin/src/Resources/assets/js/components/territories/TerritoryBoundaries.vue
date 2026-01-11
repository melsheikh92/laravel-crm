<template>
    <div class="w-full">
        <div class="mb-4 flex items-center gap-4">
            <div v-if="!isDrawing && !points.length">
                <button 
                    type="button" 
                    class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                    @click="startDrawing"
                >
                    Start Drawing
                </button>
            </div>

            <div v-if="isDrawing" class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-300">
                    Click map to add points. Click the first point or "Finish" to close.
                </span>
                
                <button 
                    type="button" 
                    class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 disabled:opacity-50"
                    @click="finishDrawing"
                    :disabled="points.length < 3"
                >
                    Finish
                </button>

                <button 
                    type="button" 
                    class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    @click="cancelDrawing"
                >
                    Cancel
                </button>
            </div>

            <div v-if="!isDrawing && points.length" class="flex items-center gap-2">
                <button 
                    type="button" 
                    class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                    @click="clear"
                >
                    Clear Boundary
                </button>
                <button 
                    type="button" 
                    class="rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500"
                    @click="startDrawing"
                >
                    Redraw
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700" style="height: 500px; width: 100%;">
            <l-map 
                ref="map" 
                v-model:zoom="zoom" 
                v-model:center="center" 
                :use-global-leaflet="false"
                @click="mapClicked"
            >
                <l-tile-layer
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    layer-type="base"
                    name="OpenStreetMap"
                    attribution="&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors"
                ></l-tile-layer>

                <!-- Polygon (Filled) - shown when finished or valid -->
                <l-polygon 
                    v-if="points.length > 2" 
                    :lat-lngs="points" 
                    color="#2563eb"
                    :fill="true"
                    :fillOpacity="0.2"
                ></l-polygon>

                <!-- Polyline (Stroke) - shown while drawing to see connection -->
                <l-polyline
                    v-if="isDrawing && points.length > 0"
                    :lat-lngs="points"
                    color="#2563eb"
                    dash-array="5, 10"
                ></l-polyline>

                <l-marker 
                    v-for="(point, index) in points" 
                    :key="index" 
                    :lat-lng="point"
                    @click="handleMarkerClick(index)"
                ></l-marker>
            </l-map>
        </div>

        <!-- Hidden Input for Form Submission -->
        <input type="hidden" name="boundaries" :value="boundariesJson">
    </div>
</template>

<script>
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import { LMap, LTileLayer, LPolygon, LPolyline, LMarker } from "@vue-leaflet/vue-leaflet";

// Fix Leaflet's default icon path issues with webpack/vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

export default {
    name: 'TerritoryBoundaries',

    components: {
        LMap,
        LTileLayer,
        LPolygon,
        LPolyline,
        LMarker
    },

    props: {
        data: {
            type: [String, Object, Array],
            default: () => []
        }
    },

    data() {
        return {
            zoom: 2,
            center: [20, 0],
            points: [],
            isDrawing: false,
        };
    },

    computed: {
        boundariesJson() {
            if (this.points.length < 3) return '[]'; 
            return JSON.stringify(this.points);
        }
    },

    mounted() {
        console.log("TerritoryBoundaries component mounted");
        this.initializeData();
        
        // Force map invalidation to ensure it renders correctly after mount
        this.$nextTick(() => {
            if (this.$refs.map && this.$refs.map.leafletObject) {
                console.log("Invalidating map size");
                this.$refs.map.leafletObject.invalidateSize();
            }
        });
    },

    methods: {
        initializeData() {
            console.log("Initializing data with:", this.data);
            if (!this.data) return;

            try {
                let parsed = typeof this.data === 'string' ? JSON.parse(this.data) : this.data;

                if (!parsed || (Array.isArray(parsed) && parsed.length === 0)) return;

                let coords = parsed;

                if (parsed.type === 'FeatureCollection' && parsed.features) {
                    coords = parsed.features[0]?.geometry?.coordinates;
                } else if (parsed.type === 'Feature' && parsed.geometry) {
                    coords = parsed.geometry.coordinates;
                } else if (parsed.type === 'Polygon') {
                    coords = parsed.coordinates;
                }
                
                if (Array.isArray(coords) && coords.length > 0) {
                    if (Array.isArray(coords[0]) && coords[0].length === 2) {
                        this.points = coords;
                        this.center = this.points[0];
                        this.zoom = 10; 
                    }
                }
            } catch (e) {
                console.error("Error parsing territory boundaries:", e);
            }
        },

        startDrawing() {
            this.points = [];
            this.isDrawing = true;
        },

        mapClicked(e) {
            if (!this.isDrawing) return;
            
            const { lat, lng } = e.latlng;
            this.points.push([lat, lng]);
        },

        handleMarkerClick(index) {
            // If clicking the first point while drawing and we have enough points, close the loop
            if (this.isDrawing && index === 0 && this.points.length >= 3) {
                this.finishDrawing();
            }
        },

        finishDrawing() {
            if (this.points.length < 3) {
                alert("A territory must have at least 3 points.");
                return;
            }
            this.isDrawing = false;
        },

        cancelDrawing() {
            this.isDrawing = false;
            this.points = [];
        },

        clear() {
            if (confirm("Are you sure you want to clear the boundary?")) {
                this.points = [];
            }
        }
    }
}
</script>

