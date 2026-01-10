<?php

namespace Webkul\Admin\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Repositories\TerritoryRuleRepository;
use Webkul\Territory\Repositories\TerritoryAssignmentRepository;

class TerritoryApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TerritoryRepository $territoryRepository,
        protected TerritoryRuleRepository $territoryRuleRepository,
        protected TerritoryAssignmentRepository $territoryAssignmentRepository
    ) {}

    /**
     * Display a listing of territories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->territoryRepository->scopeQuery(function ($query) use ($request) {
            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->input('type'));
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            // Filter by parent_id
            if ($request->has('parent_id')) {
                if ($request->input('parent_id') === 'null' || $request->input('parent_id') === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->input('parent_id'));
                }
            }

            // Search by name or code
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            }

            // Sort
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            return $query;
        });

        $perPage = $request->input('per_page', 15);

        if ($perPage === 'all') {
            $territories = $query->with(['parent', 'owner', 'children'])->get();

            return response()->json([
                'data' => $territories->map(function ($territory) {
                    return $this->formatTerritory($territory);
                }),
            ]);
        }

        $territories = $query->with(['parent', 'owner', 'children'])->paginate($perPage);

        return response()->json([
            'data' => $territories->map(function ($territory) {
                return $this->formatTerritory($territory);
            }),
            'meta' => [
                'current_page' => $territories->currentPage(),
                'from'         => $territories->firstItem(),
                'last_page'    => $territories->lastPage(),
                'per_page'     => $territories->perPage(),
                'to'           => $territories->lastItem(),
                'total'        => $territories->total(),
            ],
        ]);
    }

    /**
     * Display the specified territory.
     */
    public function show(int $id): JsonResponse
    {
        $territory = $this->territoryRepository
            ->with(['parent', 'owner', 'children', 'users', 'rules', 'assignments'])
            ->findOrFail($id);

        return response()->json([
            'data' => $this->formatTerritory($territory, true),
        ]);
    }

    /**
     * Store a newly created territory.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name'        => 'required|max:100',
            'code'        => 'required|unique:territories,code|max:50',
            'type'        => 'required|in:geographic,account-based',
            'status'      => 'required|in:active,inactive',
            'description' => 'nullable|max:500',
            'parent_id'   => 'nullable|exists:territories,id',
            'user_id'     => 'required|exists:users,id',
            'boundaries'  => 'nullable|json',
        ]);

        Event::dispatch('settings.territory.create.before');

        $territory = $this->territoryRepository->create($validatedData);

        Event::dispatch('settings.territory.create.after', $territory);

        return response()->json([
            'data'    => $this->formatTerritory($territory->load(['parent', 'owner'])),
            'message' => trans('admin::app.settings.territories.index.create-success'),
        ], 201);
    }

    /**
     * Update the specified territory.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'name'        => 'required|max:100',
            'code'        => 'required|max:50|unique:territories,code,'.$id,
            'type'        => 'required|in:geographic,account-based',
            'status'      => 'required|in:active,inactive',
            'description' => 'nullable|max:500',
            'parent_id'   => 'nullable|exists:territories,id',
            'user_id'     => 'required|exists:users,id',
            'boundaries'  => 'nullable|json',
        ]);

        // Prevent setting parent to self or descendant
        if ($request->has('parent_id') && $request->input('parent_id')) {
            $descendants = $this->territoryRepository->getDescendants($id);
            $excludeIds = $descendants->pluck('id')->push($id)->toArray();

            if (in_array($request->input('parent_id'), $excludeIds)) {
                return response()->json([
                    'message' => trans('admin::app.settings.territories.index.invalid-parent'),
                ], 422);
            }
        }

        Event::dispatch('settings.territory.update.before', $id);

        $territory = $this->territoryRepository->update($validatedData, $id);

        Event::dispatch('settings.territory.update.after', $territory);

        return response()->json([
            'data'    => $this->formatTerritory($territory->load(['parent', 'owner'])),
            'message' => trans('admin::app.settings.territories.index.update-success'),
        ]);
    }

    /**
     * Remove the specified territory.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $territory = $this->territoryRepository->findOrFail($id);

            Event::dispatch('settings.territory.delete.before', $id);

            $this->territoryRepository->delete($id);

            Event::dispatch('settings.territory.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.territories.index.delete-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Get territory hierarchy.
     */
    public function hierarchy(): JsonResponse
    {
        $territories = $this->territoryRepository->getHierarchy();

        return response()->json([
            'data' => $territories->map(function ($territory) {
                return $this->formatTerritoryHierarchy($territory);
            }),
        ]);
    }

    /**
     * Get root territories (territories without parent).
     */
    public function roots(): JsonResponse
    {
        $territories = $this->territoryRepository->getRootTerritories();

        return response()->json([
            'data' => $territories->map(function ($territory) {
                return $this->formatTerritory($territory);
            }),
        ]);
    }

    /**
     * Get children of a specific territory.
     */
    public function children(int $id): JsonResponse
    {
        $territory = $this->territoryRepository->findOrFail($id);
        $children = $this->territoryRepository->getChildTerritories($id);

        return response()->json([
            'data' => $children->map(function ($territory) {
                return $this->formatTerritory($territory);
            }),
        ]);
    }

    /**
     * Get all descendants of a specific territory.
     */
    public function descendants(int $id): JsonResponse
    {
        $territory = $this->territoryRepository->findOrFail($id);
        $descendants = $this->territoryRepository->getDescendants($id);

        return response()->json([
            'data' => $descendants->map(function ($territory) {
                return $this->formatTerritory($territory);
            }),
        ]);
    }

    /**
     * Get territory rules.
     */
    public function rules(int $id): JsonResponse
    {
        $territory = $this->territoryRepository->findOrFail($id);
        $rules = $this->territoryRuleRepository->getRulesByTerritory($id);

        return response()->json([
            'data' => $rules->map(function ($rule) {
                return [
                    'id'            => $rule->id,
                    'territory_id'  => $rule->territory_id,
                    'rule_type'     => $rule->rule_type,
                    'field_name'    => $rule->field_name,
                    'operator'      => $rule->operator,
                    'value'         => $rule->value,
                    'priority'      => $rule->priority,
                    'is_active'     => $rule->is_active,
                    'created_at'    => $rule->created_at?->toISOString(),
                    'updated_at'    => $rule->updated_at?->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * Get territory assignments.
     */
    public function assignments(int $id): JsonResponse
    {
        $territory = $this->territoryRepository->findOrFail($id);
        $assignments = $this->territoryAssignmentRepository->getAssignmentsByTerritory($id);

        return response()->json([
            'data' => $assignments->map(function ($assignment) {
                return [
                    'id'              => $assignment->id,
                    'territory_id'    => $assignment->territory_id,
                    'assignable_type' => $assignment->assignable_type,
                    'assignable_id'   => $assignment->assignable_id,
                    'assignable'      => $assignment->assignable ? [
                        'id'   => $assignment->assignable->id,
                        'name' => $assignment->assignable->name ?? $assignment->assignable->title ?? 'N/A',
                        'type' => class_basename($assignment->assignable_type),
                    ] : null,
                    'assigned_by'     => $assignment->assigned_by,
                    'assigned_by_user' => $assignment->assignedBy ? [
                        'id'   => $assignment->assignedBy->id,
                        'name' => $assignment->assignedBy->name,
                    ] : null,
                    'assignment_type' => $assignment->assignment_type,
                    'assigned_at'     => $assignment->assigned_at?->toISOString(),
                    'created_at'      => $assignment->created_at?->toISOString(),
                    'updated_at'      => $assignment->updated_at?->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * Get territory statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $territory = $this->territoryRepository->findOrFail($id);

        $stats = [
            'total_rules'       => $this->territoryRuleRepository->getRulesByTerritory($id)->count(),
            'active_rules'      => $this->territoryRuleRepository->getActiveRulesByTerritory($id)->count(),
            'total_assignments' => $this->territoryAssignmentRepository->getAssignmentCount($id),
            'manual_assignments' => $this->territoryAssignmentRepository->getManualAssignmentCount($id),
            'automatic_assignments' => $this->territoryAssignmentRepository->getAutomaticAssignmentCount($id),
            'children_count'    => $territory->children->count(),
            'has_children'      => $territory->hasChildren(),
            'is_active'         => $territory->status === 'active',
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Format territory data for API response.
     */
    protected function formatTerritory($territory, bool $detailed = false): array
    {
        $data = [
            'id'          => $territory->id,
            'name'        => $territory->name,
            'code'        => $territory->code,
            'description' => $territory->description,
            'type'        => $territory->type,
            'status'      => $territory->status,
            'parent_id'   => $territory->parent_id,
            'user_id'     => $territory->user_id,
            'boundaries'  => $territory->boundaries,
            'created_at'  => $territory->created_at?->toISOString(),
            'updated_at'  => $territory->updated_at?->toISOString(),
            'deleted_at'  => $territory->deleted_at?->toISOString(),
        ];

        // Add parent information
        if ($territory->relationLoaded('parent') && $territory->parent) {
            $data['parent'] = [
                'id'   => $territory->parent->id,
                'name' => $territory->parent->name,
                'code' => $territory->parent->code,
            ];
        }

        // Add owner information
        if ($territory->relationLoaded('owner') && $territory->owner) {
            $data['owner'] = [
                'id'    => $territory->owner->id,
                'name'  => $territory->owner->name,
                'email' => $territory->owner->email,
            ];
        }

        // Add children information
        if ($territory->relationLoaded('children')) {
            $data['children_count'] = $territory->children->count();
            $data['has_children'] = $territory->hasChildren();
        }

        // Add detailed information if requested
        if ($detailed) {
            if ($territory->relationLoaded('users')) {
                $data['users'] = $territory->users->map(function ($user) {
                    return [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                        'role'  => $user->pivot->role ?? null,
                    ];
                });
            }

            if ($territory->relationLoaded('rules')) {
                $data['rules_count'] = $territory->rules->count();
                $data['active_rules_count'] = $territory->rules->where('is_active', true)->count();
            }

            if ($territory->relationLoaded('assignments')) {
                $data['assignments_count'] = $territory->assignments->count();
                $data['manual_assignments_count'] = $territory->assignments->where('assignment_type', 'manual')->count();
                $data['automatic_assignments_count'] = $territory->assignments->where('assignment_type', 'automatic')->count();
            }
        }

        return $data;
    }

    /**
     * Format territory hierarchy data for API response.
     */
    protected function formatTerritoryHierarchy($territory): array
    {
        $data = $this->formatTerritory($territory);

        if ($territory->relationLoaded('children') && $territory->children->isNotEmpty()) {
            $data['children'] = $territory->children->map(function ($child) {
                return $this->formatTerritoryHierarchy($child);
            });
        }

        return $data;
    }
}
