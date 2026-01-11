<?php

namespace Webkul\Admin\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Repositories\TerritoryRuleRepository;

class TerritoryRuleApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TerritoryRuleRepository $territoryRuleRepository,
        protected TerritoryRepository $territoryRepository
    ) {}

    /**
     * Display a listing of territory rules.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->territoryRuleRepository->scopeQuery(function ($query) use ($request) {
            // Filter by territory_id
            if ($request->has('territory_id')) {
                $query->where('territory_id', $request->input('territory_id'));
            }

            // Filter by rule_type
            if ($request->has('rule_type')) {
                $query->where('rule_type', $request->input('rule_type'));
            }

            // Filter by is_active
            if ($request->has('is_active')) {
                $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
                $query->where('is_active', $isActive);
            }

            // Sort by priority
            $sortBy = $request->input('sort_by', 'priority');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            return $query;
        });

        $perPage = $request->input('per_page', 15);

        if ($perPage === 'all') {
            $rules = $query->with('territory')->get();

            return response()->json([
                'data' => $rules->map(function ($rule) {
                    return $this->formatRule($rule);
                }),
            ]);
        }

        $rules = $query->with('territory')->paginate($perPage);

        return response()->json([
            'data' => $rules->map(function ($rule) {
                return $this->formatRule($rule);
            }),
            'meta' => [
                'current_page' => $rules->currentPage(),
                'from'         => $rules->firstItem(),
                'last_page'    => $rules->lastPage(),
                'per_page'     => $rules->perPage(),
                'to'           => $rules->lastItem(),
                'total'        => $rules->total(),
            ],
        ]);
    }

    /**
     * Display the specified territory rule.
     */
    public function show(int $id): JsonResponse
    {
        $rule = $this->territoryRuleRepository
            ->with('territory')
            ->findOrFail($id);

        return response()->json([
            'data' => $this->formatRule($rule),
        ]);
    }

    /**
     * Store a newly created territory rule.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'territory_id' => 'required|exists:territories,id',
            'rule_type'    => 'required|in:geographic,industry,account_size,custom',
            'field_name'   => 'required|max:100',
            'operator'     => 'required|in:=,!=,>,>=,<,<=,in,not_in,contains,not_contains,starts_with,ends_with,is_null,is_not_null,between',
            'value'        => 'nullable|json',
            'priority'     => 'nullable|integer|min:0',
            'is_active'    => 'nullable|boolean',
        ]);

        Event::dispatch('settings.territory.rule.create.before');

        $rule = $this->territoryRuleRepository->create($validatedData);

        Event::dispatch('settings.territory.rule.create.after', $rule);

        return response()->json([
            'data'    => $this->formatRule($rule->load('territory')),
            'message' => trans('admin::app.settings.territories.rules.index.create-success'),
        ], 201);
    }

    /**
     * Update the specified territory rule.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'territory_id' => 'required|exists:territories,id',
            'rule_type'    => 'required|in:geographic,industry,account_size,custom',
            'field_name'   => 'required|max:100',
            'operator'     => 'required|in:=,!=,>,>=,<,<=,in,not_in,contains,not_contains,starts_with,ends_with,is_null,is_not_null,between',
            'value'        => 'nullable|json',
            'priority'     => 'nullable|integer|min:0',
            'is_active'    => 'nullable|boolean',
        ]);

        Event::dispatch('settings.territory.rule.update.before', $id);

        $rule = $this->territoryRuleRepository->update($validatedData, $id);

        Event::dispatch('settings.territory.rule.update.after', $rule);

        return response()->json([
            'data'    => $this->formatRule($rule->load('territory')),
            'message' => trans('admin::app.settings.territories.rules.index.update-success'),
        ]);
    }

    /**
     * Remove the specified territory rule.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $rule = $this->territoryRuleRepository->findOrFail($id);

            Event::dispatch('settings.territory.rule.delete.before', $id);

            $this->territoryRuleRepository->delete($id);

            Event::dispatch('settings.territory.rule.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.delete-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Toggle active status of a rule.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $rule = $this->territoryRuleRepository->findOrFail($id);

            $this->territoryRuleRepository->toggleActiveStatus($id);

            $rule->refresh();

            return response()->json([
                'data'    => $this->formatRule($rule),
                'message' => trans('admin::app.settings.territories.rules.index.toggle-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.toggle-failed'),
            ], 400);
        }
    }

    /**
     * Update priority of a rule.
     */
    public function updatePriority(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'priority' => 'required|integer|min:0',
        ]);

        try {
            $this->territoryRuleRepository->updatePriority($id, $validatedData['priority']);

            $rule = $this->territoryRuleRepository->findOrFail($id);

            return response()->json([
                'data'    => $this->formatRule($rule),
                'message' => trans('admin::app.settings.territories.rules.index.priority-updated'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.priority-failed'),
            ], 400);
        }
    }

    /**
     * Bulk update priorities of multiple rules.
     */
    public function bulkUpdatePriorities(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'priorities'   => 'required|array',
            'priorities.*' => 'required|array',
            'priorities.*.id' => 'required|exists:territory_rules,id',
            'priorities.*.priority' => 'required|integer|min:0',
        ]);

        try {
            Event::dispatch('settings.territory.rule.bulk-update.before');

            $this->territoryRuleRepository->bulkUpdatePriorities($validatedData['priorities']);

            Event::dispatch('settings.territory.rule.bulk-update.after');

            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.bulk-priority-updated'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.bulk-priority-failed'),
            ], 400);
        }
    }

    /**
     * Format territory rule data for API response.
     */
    protected function formatRule($rule): array
    {
        $data = [
            'id'           => $rule->id,
            'territory_id' => $rule->territory_id,
            'rule_type'    => $rule->rule_type,
            'field_name'   => $rule->field_name,
            'operator'     => $rule->operator,
            'value'        => $rule->value,
            'priority'     => $rule->priority,
            'is_active'    => $rule->is_active,
            'created_at'   => $rule->created_at?->toISOString(),
            'updated_at'   => $rule->updated_at?->toISOString(),
        ];

        // Add territory information if loaded
        if ($rule->relationLoaded('territory') && $rule->territory) {
            $data['territory'] = [
                'id'   => $rule->territory->id,
                'name' => $rule->territory->name,
                'code' => $rule->territory->code,
                'type' => $rule->territory->type,
            ];
        }

        return $data;
    }
}
