<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Contact\Repositories\OrganizationRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Services\TerritoryAssignmentService;

class TerritoryAssignmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TerritoryAssignmentService $assignmentService,
        protected TerritoryRepository $territoryRepository,
        protected LeadRepository $leadRepository,
        protected OrganizationRepository $organizationRepository,
        protected PersonRepository $personRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(\Webkul\Admin\DataGrids\Settings\TerritoryAssignmentDataGrid::class)->process();
        }

        $territories = $this->territoryRepository->getActiveTerritories();

        return view('admin::settings.territories.assignments.index', compact('territories'));
    }

    /**
     * Show the form for creating a new manual assignment.
     */
    public function create(): View
    {
        $territories = $this->territoryRepository->getActiveTerritories();

        $assignableTypes = $this->getAssignableTypes();

        return view('admin::settings.territories.assignments.create', compact('territories', 'assignableTypes'));
    }

    /**
     * Store a newly created manual assignment.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'territory_id'     => 'required|exists:territories,id',
            'assignable_type'  => 'required|in:lead,organization,person',
            'assignable_id'    => 'required|integer|min:1',
            'transfer_ownership' => 'nullable|boolean',
        ]);

        $assignableType = $this->getAssignableTypeClass(request('assignable_type'));
        $assignableId = request('assignable_id');

        // Validate that the assignable entity exists
        $entity = $this->getEntity($assignableType, $assignableId);

        if (! $entity) {
            session()->flash('error', trans('admin::app.settings.territories.assignments.index.entity-not-found'));

            return redirect()->back();
        }

        Event::dispatch('settings.territory.assignment.create.before');

        $assignment = $this->assignmentService->manualAssign(
            $entity,
            request('territory_id'),
            auth()->guard('user')->user()->id
        );

        // Transfer ownership if requested
        if (request('transfer_ownership', true)) {
            $territory = $this->territoryRepository->find(request('territory_id'));

            if ($territory && $territory->user_id) {
                $entity->user_id = $territory->user_id;
                $entity->save();
            }
        }

        Event::dispatch('settings.territory.assignment.create.after', $assignment);

        session()->flash('success', trans('admin::app.settings.territories.assignments.index.create-success'));

        return redirect()->route('admin.settings.territories.assignments.index');
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Get the assignment first to retrieve the entity
            $assignableType = request('assignable_type');
            $assignableId = request('assignable_id');

            if (! $assignableType || ! $assignableId) {
                return response()->json([
                    'message' => trans('admin::app.settings.territories.assignments.index.delete-failed'),
                ], 400);
            }

            $assignableTypeClass = $this->getAssignableTypeClass($assignableType);
            $entity = $this->getEntity($assignableTypeClass, $assignableId);

            if (! $entity) {
                return response()->json([
                    'message' => trans('admin::app.settings.territories.assignments.index.entity-not-found'),
                ], 404);
            }

            Event::dispatch('settings.territory.assignment.delete.before', $id);

            $this->assignmentService->unassign($entity);

            Event::dispatch('settings.territory.assignment.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Show the form for reassigning an entity.
     */
    public function reassign(): View
    {
        $this->validate(request(), [
            'assignable_type' => 'required|in:lead,organization,person',
            'assignable_id'   => 'required|integer|min:1',
        ]);

        $assignableType = $this->getAssignableTypeClass(request('assignable_type'));
        $assignableId = request('assignable_id');

        $entity = $this->getEntity($assignableType, $assignableId);

        if (! $entity) {
            session()->flash('error', trans('admin::app.settings.territories.assignments.index.entity-not-found'));

            return redirect()->route('admin.settings.territories.assignments.index');
        }

        $currentTerritory = $this->assignmentService->getCurrentTerritory($entity);
        $territories = $this->territoryRepository->getActiveTerritories();

        return view('admin::settings.territories.assignments.reassign', compact(
            'entity',
            'currentTerritory',
            'territories',
            'assignableType',
            'assignableId'
        ));
    }

    /**
     * Store a reassignment.
     */
    public function storeReassignment(): RedirectResponse
    {
        $this->validate(request(), [
            'territory_id'       => 'required|exists:territories,id',
            'assignable_type'    => 'required|in:lead,organization,person',
            'assignable_id'      => 'required|integer|min:1',
            'transfer_ownership' => 'nullable|boolean',
        ]);

        $assignableType = $this->getAssignableTypeClass(request('assignable_type'));
        $assignableId = request('assignable_id');

        $entity = $this->getEntity($assignableType, $assignableId);

        if (! $entity) {
            session()->flash('error', trans('admin::app.settings.territories.assignments.index.entity-not-found'));

            return redirect()->back();
        }

        Event::dispatch('settings.territory.assignment.reassign.before');

        $assignment = $this->assignmentService->reassign(
            $entity,
            request('territory_id'),
            auth()->guard('user')->user()->id,
            request('transfer_ownership', true)
        );

        Event::dispatch('settings.territory.assignment.reassign.after', $assignment);

        session()->flash('success', trans('admin::app.settings.territories.assignments.index.reassign-success'));

        return redirect()->route('admin.settings.territories.assignments.index');
    }

    /**
     * Bulk reassign multiple entities to a territory.
     */
    public function bulkReassign(): JsonResponse
    {
        $this->validate(request(), [
            'territory_id'       => 'required|exists:territories,id',
            'entities'           => 'required|array|min:1',
            'entities.*.type'    => 'required|in:lead,organization,person',
            'entities.*.id'      => 'required|integer|min:1',
            'transfer_ownership' => 'nullable|boolean',
        ]);

        try {
            $entities = [];

            // Fetch all entities
            foreach (request('entities') as $entityData) {
                $assignableType = $this->getAssignableTypeClass($entityData['type']);
                $entity = $this->getEntity($assignableType, $entityData['id']);

                if ($entity) {
                    $entities[] = $entity;
                }
            }

            if (empty($entities)) {
                return response()->json([
                    'message' => trans('admin::app.settings.territories.assignments.index.no-entities-found'),
                ], 400);
            }

            Event::dispatch('settings.territory.assignment.bulk-reassign.before');

            $assignments = $this->assignmentService->bulkReassign(
                $entities,
                request('territory_id'),
                auth()->guard('user')->user()->id,
                request('transfer_ownership', true)
            );

            Event::dispatch('settings.territory.assignment.bulk-reassign.after', $assignments);

            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.bulk-reassign-success', [
                    'count' => count($assignments),
                ]),
                'count'   => count($assignments),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.assignments.index.bulk-reassign-failed'),
            ], 400);
        }
    }

    /**
     * Show assignment history for an entity.
     */
    public function history(): View
    {
        $this->validate(request(), [
            'assignable_type' => 'required|in:lead,organization,person',
            'assignable_id'   => 'required|integer|min:1',
        ]);

        $assignableType = $this->getAssignableTypeClass(request('assignable_type'));
        $assignableId = request('assignable_id');

        $entity = $this->getEntity($assignableType, $assignableId);

        if (! $entity) {
            session()->flash('error', trans('admin::app.settings.territories.assignments.index.entity-not-found'));

            return redirect()->route('admin.settings.territories.assignments.index');
        }

        $history = $this->assignmentService->getAssignmentHistory($entity);

        return view('admin::settings.territories.assignments.history', compact('entity', 'history', 'assignableType'));
    }

    /**
     * Get assignable types for the dropdown.
     *
     * @return array
     */
    protected function getAssignableTypes(): array
    {
        return [
            'lead'         => trans('admin::app.settings.territories.assignments.index.lead'),
            'organization' => trans('admin::app.settings.territories.assignments.index.organization'),
            'person'       => trans('admin::app.settings.territories.assignments.index.person'),
        ];
    }

    /**
     * Get the full class name for assignable type.
     *
     * @param  string  $type
     * @return string
     */
    protected function getAssignableTypeClass(string $type): string
    {
        return match ($type) {
            'lead'         => 'Webkul\Lead\Contracts\Lead',
            'organization' => 'Webkul\Contact\Contracts\Organization',
            'person'       => 'Webkul\Contact\Contracts\Person',
            default        => throw new \InvalidArgumentException("Invalid assignable type: {$type}"),
        };
    }

    /**
     * Get entity by type and ID.
     *
     * @param  string  $type
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getEntity(string $type, int $id)
    {
        return match ($type) {
            'Webkul\Lead\Contracts\Lead'                => $this->leadRepository->find($id),
            'Webkul\Contact\Contracts\Organization'     => $this->organizationRepository->find($id),
            'Webkul\Contact\Contracts\Person'           => $this->personRepository->find($id),
            default                                      => null,
        };
    }
}
