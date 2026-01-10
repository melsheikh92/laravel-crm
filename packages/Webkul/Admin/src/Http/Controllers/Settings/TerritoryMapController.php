<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Territory\Repositories\TerritoryRepository;

class TerritoryMapController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TerritoryRepository $territoryRepository
    ) {}

    /**
     * Get all territories as GeoJSON FeatureCollection.
     */
    public function geojson(): JsonResponse
    {
        $type = request('type');

        // Get territories based on filters
        if ($type && in_array($type, ['geographic', 'account-based'])) {
            $territories = $this->territoryRepository->getTerritoriesByType($type);
        } else {
            $territories = $this->territoryRepository->all();
        }

        // Filter to only include territories with boundaries
        $territories = $territories->filter(function ($territory) {
            return ! empty($territory->boundaries);
        });

        // Convert to GeoJSON FeatureCollection
        $features = $territories->map(function ($territory) {
            return $this->territoryToFeature($territory);
        })->values()->all();

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * Get a specific territory as GeoJSON Feature.
     */
    public function territory(int $id): JsonResponse
    {
        $territory = $this->territoryRepository->findOrFail($id);

        if (empty($territory->boundaries)) {
            return response()->json([
                'success' => false,
                'message' => trans('admin::app.settings.territories.map.no-boundaries'),
            ], 404);
        }

        $feature = $this->territoryToFeature($territory);

        return response()->json([
            'type'    => 'Feature',
            'feature' => $feature,
        ]);
    }

    /**
     * Get territories with assignment counts for map visualization.
     */
    public function withAssignments(): JsonResponse
    {
        $territories = $this->territoryRepository
            ->with(['assignments', 'owner'])
            ->scopeQuery(function ($query) {
                return $query->whereNotNull('boundaries')
                    ->where('boundaries', '!=', '[]');
            })
            ->all();

        // Convert to GeoJSON FeatureCollection with assignment data
        $features = $territories->map(function ($territory) {
            $assignmentCount = $territory->assignments->count();
            $leadCount = $territory->assignments->where('assignable_type', 'Webkul\Lead\Models\Lead')->count();
            $orgCount = $territory->assignments->where('assignable_type', 'Webkul\Contact\Models\Organization')->count();
            $personCount = $territory->assignments->where('assignable_type', 'Webkul\Contact\Models\Person')->count();

            $feature = $this->territoryToFeature($territory);

            // Add assignment statistics to properties
            $feature['properties']['assignment_count'] = $assignmentCount;
            $feature['properties']['lead_count'] = $leadCount;
            $feature['properties']['organization_count'] = $orgCount;
            $feature['properties']['person_count'] = $personCount;

            return $feature;
        })->values()->all();

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * Convert a Territory model to a GeoJSON Feature.
     *
     * @param  \Webkul\Territory\Contracts\Territory  $territory
     * @return array
     */
    protected function territoryToFeature($territory): array
    {
        // The boundaries field should contain GeoJSON geometry
        // If it's a complete GeoJSON object, extract the geometry
        $geometry = $territory->boundaries;

        // If boundaries contains a full GeoJSON feature/geometry object
        if (isset($geometry['type'])) {
            // If it's a Feature, extract the geometry
            if ($geometry['type'] === 'Feature' && isset($geometry['geometry'])) {
                $geometry = $geometry['geometry'];
            }
            // Otherwise assume it's already a geometry object (Polygon, MultiPolygon, etc.)
        } else {
            // If boundaries is just coordinates, wrap it in a default geometry
            $geometry = [
                'type'        => 'Polygon',
                'coordinates' => $geometry,
            ];
        }

        return [
            'type'       => 'Feature',
            'geometry'   => $geometry,
            'properties' => [
                'id'          => $territory->id,
                'name'        => $territory->name,
                'code'        => $territory->code,
                'description' => $territory->description,
                'type'        => $territory->type,
                'status'      => $territory->status,
                'owner_id'    => $territory->user_id,
                'owner_name'  => $territory->owner ? $territory->owner->name : null,
                'parent_id'   => $territory->parent_id,
                'parent_name' => $territory->parent ? $territory->parent->name : null,
            ],
        ];
    }
}
