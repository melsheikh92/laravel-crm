<?php

namespace Webkul\Admin\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Contact\Repositories\OrganizationRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Repositories\TerritoryAssignmentRepository;
use Webkul\Territory\Services\TerritoryAssignmentService;

class TerritoryAssignmentApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TerritoryAssignmentRepository $assignmentRepository,
        protected TerritoryAssignmentService $assignmentService,
        protected TerritoryRepository $territoryRepository,
        protected LeadRepository $leadRepository,
        protected OrganizationRepository $organizationRepository,
        protected PersonRepository $personRepository
    ) {}

    /**
     * Display a listing of territory assignments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->assignmentRepository->scopeQuery(function ($query) use ($request) {
            // Filter by territory_id
            if ($request->has('territory_id')) {
                $query->where('territory_id', $request->input('territory_id'));
            }

            // Filter by assignable_type
            if ($request->has('assignable_type')) {
                $query->where('assignable_type', $request->input('assignable_type'));
            }

            // Filter by assignment_type
            if ($request->has('assignment_type')) {
                $query->where('assignment_type', $request->input('assignment_type'));
            }

            // Filter by assigned_by
            if ($request->has('assigned_by')) {
                $query->where('assigned_by', $request->input('assigned_by'));
            }

            // Sort
            $sortBy = $request->input('sort_by', 'assigned_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            return $query;
        });

        $perPage = $request->input('per_page', 15);

        if ($perPage === 'all') {
            $assignments = $query->with(['territory', 'assignable', 'assignedBy'])->get();

            return response()->json([
                'data' => $assignments->map(function ($assignment) {
                    return $this->formatAssignment($assignment);
                }),
            ]);
        }

        $assignments = $query->with(['territory', 'assignable', 'assignedBy'])->paginate($perPage);

        return response()->json([
            'data' => $assignments->map(function ($assignment) {
                return $this->formatAssignment($assignment);
            }),
            'meta' => [
                'current_page' => $assignments->currentPage(),
                'from'         => $assignments->firstItem(),
                'last_page'    => $assignments->lastPage(),
                'per_page'     => $assignments->perPage(),
                'to'           => $assignments->lastItem(),
                'total'        => $assignments->total(),
            ],
        ]);
    }

    /**
     * Display the specified territory assignment.
     */
    public function show(int $id): JsonResponse
    {
        $assignment = $this->assignmentRepository
            ->with(['territory', 'assignable', 'assignedBy'])
            ->findOrFail($id);

        return response()->json([
            'data' => $this->formatAssignment($assignment),
        ]);
    }

    /**
     * Store a newly created manual assignment.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'territory_id'       => 'required|exists:territories,id',
            'assignable_type'    => 'required|in:lead,organization,person',
            'assignable_id'      => 'required|integer|min:1',
            'transfer_ownership' => 'nullable|boolean',
        ]);

        $assignableType = $this->getAssignableTypeClass($validatedData['assignable_type']);
        $assignableId = $validatedData['assignable_id'];

        // Validate that the assignable entity exists
        $entity = $this->getEntity($assignableType, $assignableId);

        if (! $entity) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.entity-not-found'),
            ], 404);
        }

        Event::dispatch('settings.territory.assignment.create.before');

        $assignment = $this->assignmentService->manualAssign(
            $entity,
            $validatedData['territory_id'],
            auth()->guard('user')->user()->id ?? null
        );

        // Transfer ownership if requested
        if ($request->input('transfer_ownership', true)) {
            $territory = $this->territoryRepository->find($validatedData['territory_id']);

            if ($territory && $territory->user_id) {
                $entity->user_id = $territory->user_id;
                $entity->save();
            }
        }

        Event::dispatch('settings.territory.assignment.create.after', $assignment);

        return response()->json([
            'data'    => $this->formatAssignment($assignment->load(['territory', 'assignedBy'])),
            'message' => trans('admin::app.settings.territories.assignments.index.create-success'),
        ], 201);
    }

    /**
     * Remove the specified territory assignment.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $assignment = $this->assignmentRepository->findOrFail($id);

            Event::dispatch('settings.territory.assignment.delete.before', $id);

            $this->assignmentRepository->delete($id);

            Event::dispatch('settings.territory.assignment.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.delete-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Reassign an entity to a different territory.
     */
    public function reassign(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'assignable_type'    => 'required|in:lead,organization,person',
            'assignable_id'      => 'required|integer|min:1',
            'territory_id'       => 'required|exists:territories,id',
            'transfer_ownership' => 'nullable|boolean',
        ]);

        $assignableType = $this->getAssignableTypeClass($validatedData['assignable_type']);
        $assignableId = $validatedData['assignable_id'];

        // Validate that the assignable entity exists
        $entity = $this->getEntity($assignableType, $assignableId);

        if (! $entity) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.entity-not-found'),
            ], 404);
        }

        Event::dispatch('settings.territory.assignment.reassign.before');

        $assignment = $this->assignmentService->reassign(
            $entity,
            $validatedData['territory_id'],
            $request->input('transfer_ownership', true)
        );

        Event::dispatch('settings.territory.assignment.reassign.after', $assignment);

        return response()->json([
            'data'    => $this->formatAssignment($assignment->load(['territory', 'assignedBy'])),
            'message' => trans('admin::app.settings.territories.assignments.reassign.success'),
        ]);
    }

    /**
     * Bulk reassign multiple entities.
     */
    public function bulkReassign(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'assignment_ids'     => 'required|array|min:1',
            'assignment_ids.*'   => 'exists:territory_assignments,id',
            'territory_id'       => 'required|exists:territories,id',
            'transfer_ownership' => 'nullable|boolean',
        ]);

        try {
            Event::dispatch('settings.territory.assignment.bulk-reassign.before');

            $entities = [];
            foreach ($validatedData['assignment_ids'] as $assignmentId) {
                $assignment = $this->assignmentRepository->findOrFail($assignmentId);
                if ($assignment->assignable) {
                    $entities[] = $assignment->assignable;
                }
            }

            $this->assignmentService->bulkReassign(
                $entities,
                $validatedData['territory_id'],
                $request->input('transfer_ownership', true)
            );

            Event::dispatch('settings.territory.assignment.bulk-reassign.after');

            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.bulk-reassign-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.bulk-reassign-failed'),
            ], 400);
        }
    }

    /**
     * Get assignment history for an entity.
     */
    public function history(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'assignable_type' => 'required|in:lead,organization,person',
            'assignable_id'   => 'required|integer|min:1',
        ]);

        $assignableType = $this->getAssignableTypeClass($validatedData['assignable_type']);
        $assignableId = $validatedData['assignable_id'];

        // Validate that the assignable entity exists
        $entity = $this->getEntity($assignableType, $assignableId);

        if (! $entity) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.entity-not-found'),
            ], 404);
        }

        $history = $this->assignmentService->getAssignmentHistory($entity);

        return response()->json([
            'data' => $history->map(function ($assignment) {
                return $this->formatAssignment($assignment);
            }),
        ]);
    }

    /**
     * Get current territory assignment for an entity.
     */
    public function current(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'assignable_type' => 'required|in:lead,organization,person',
            'assignable_id'   => 'required|integer|min:1',
        ]);

        $assignableType = $this->getAssignableTypeClass($validatedData['assignable_type']);
        $assignableId = $validatedData['assignable_id'];

        // Validate that the assignable entity exists
        $entity = $this->getEntity($assignableType, $assignableId);

        if (! $entity) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.entity-not-found'),
            ], 404);
        }

        $assignment = $this->assignmentService->getCurrentTerritory($entity);

        if (! $assignment) {
            return response()->json([
                'data' => null,
                'message' => trans('admin::app.settings.territories.assignments.index.no-assignment'),
            ]);
        }

        return response()->json([
            'data' => $this->formatAssignment($assignment),
        ]);
    }

    /**
     * Format territory assignment data for API response.
     */
    protected function formatAssignment($assignment): array
    {
        $data = [
            'id'              => $assignment->id,
            'territory_id'    => $assignment->territory_id,
            'assignable_type' => $assignment->assignable_type,
            'assignable_id'   => $assignment->assignable_id,
            'assigned_by'     => $assignment->assigned_by,
            'assignment_type' => $assignment->assignment_type,
            'assigned_at'     => $assignment->assigned_at?->toISOString(),
            'created_at'      => $assignment->created_at?->toISOString(),
            'updated_at'      => $assignment->updated_at?->toISOString(),
        ];

        // Add territory information if loaded
        if ($assignment->relationLoaded('territory') && $assignment->territory) {
            $data['territory'] = [
                'id'   => $assignment->territory->id,
                'name' => $assignment->territory->name,
                'code' => $assignment->territory->code,
                'type' => $assignment->territory->type,
            ];
        }

        // Add assignable information if loaded
        if ($assignment->relationLoaded('assignable') && $assignment->assignable) {
            $data['assignable'] = [
                'id'   => $assignment->assignable->id,
                'name' => $assignment->assignable->name ?? $assignment->assignable->title ?? 'N/A',
                'type' => class_basename($assignment->assignable_type),
            ];
        }

        // Add assigned by user information if loaded
        if ($assignment->relationLoaded('assignedBy') && $assignment->assignedBy) {
            $data['assigned_by_user'] = [
                'id'   => $assignment->assignedBy->id,
                'name' => $assignment->assignedBy->name,
                'email' => $assignment->assignedBy->email,
            ];
        }

        return $data;
    }

    /**
     * Get assignable type class mapping.
     */
    protected function getAssignableTypeClass(string $type): string
    {
        $typeMap = [
            'lead'         => \Webkul\Lead\Models\Lead::class,
            'organization' => \Webkul\Contact\Models\Organization::class,
            'person'       => \Webkul\Contact\Models\Person::class,
        ];

        return $typeMap[$type] ?? '';
    }

    /**
     * Get entity by type and ID.
     */
    protected function getEntity(string $type, int $id)
    {
        switch ($type) {
            case \Webkul\Lead\Models\Lead::class:
                return $this->leadRepository->find($id);

            case \Webkul\Contact\Models\Organization::class:
                return $this->organizationRepository->find($id);

            case \Webkul\Contact\Models\Person::class:
                return $this->personRepository->find($id);

            default:
                return null;
        }
    }
}
