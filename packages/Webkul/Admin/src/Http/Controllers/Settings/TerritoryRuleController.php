<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\Territory\Repositories\TerritoryRuleRepository;

class TerritoryRuleController extends Controller
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
     * Display a listing of the resource.
     */
    public function index(int $territoryId): View|JsonResponse
    {
        $territory = $this->territoryRepository->findOrFail($territoryId);

        if (request()->ajax()) {
            return datagrid(\Webkul\Admin\DataGrids\Settings\TerritoryRuleDataGrid::class)->process();
        }

        return view('admin::settings.territories.rules.index', compact('territory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(int $territoryId): View
    {
        $territory = $this->territoryRepository->findOrFail($territoryId);

        return view('admin::settings.territories.rules.create', compact('territory'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(int $territoryId): RedirectResponse
    {
        $territory = $this->territoryRepository->findOrFail($territoryId);

        $this->validate(request(), [
            'rule_type'  => 'required|in:geographic,industry,account_size,custom',
            'field_name' => 'required|max:100',
            'operator'   => 'required|in:=,!=,>,>=,<,<=,in,not_in,contains,not_contains,starts_with,ends_with,is_null,is_not_null,between',
            'value'      => 'nullable|json',
            'priority'   => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        Event::dispatch('settings.territory.rule.create.before');

        $data = request()->only([
            'rule_type',
            'field_name',
            'operator',
            'value',
            'priority',
            'is_active',
        ]);

        $data['territory_id'] = $territoryId;

        $rule = $this->territoryRuleRepository->create($data);

        Event::dispatch('settings.territory.rule.create.after', $rule);

        session()->flash('success', trans('admin::app.settings.territories.rules.index.create-success'));

        return redirect()->route('admin.settings.territories.rules.index', $territoryId);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $territoryId, int $id): View
    {
        $territory = $this->territoryRepository->findOrFail($territoryId);

        $rule = $this->territoryRuleRepository->findOrFail($id);

        return view('admin::settings.territories.rules.edit', compact('territory', 'rule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $territoryId, int $id): RedirectResponse
    {
        $territory = $this->territoryRepository->findOrFail($territoryId);

        $this->validate(request(), [
            'rule_type'  => 'required|in:geographic,industry,account_size,custom',
            'field_name' => 'required|max:100',
            'operator'   => 'required|in:=,!=,>,>=,<,<=,in,not_in,contains,not_contains,starts_with,ends_with,is_null,is_not_null,between',
            'value'      => 'nullable|json',
            'priority'   => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        Event::dispatch('settings.territory.rule.update.before', $id);

        $data = request()->only([
            'rule_type',
            'field_name',
            'operator',
            'value',
            'priority',
            'is_active',
        ]);

        $rule = $this->territoryRuleRepository->update($data, $id);

        Event::dispatch('settings.territory.rule.update.after', $rule);

        session()->flash('success', trans('admin::app.settings.territories.rules.index.update-success'));

        return redirect()->route('admin.settings.territories.rules.index', $territoryId);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $territoryId, int $id): JsonResponse
    {
        $rule = $this->territoryRuleRepository->findOrFail($id);

        try {
            Event::dispatch('settings.territory.rule.delete.before', $id);

            $this->territoryRuleRepository->delete($id);

            Event::dispatch('settings.territory.rule.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Toggle rule active status.
     */
    public function toggleStatus(int $territoryId, int $id): JsonResponse
    {
        $rule = $this->territoryRuleRepository->findOrFail($id);

        try {
            Event::dispatch('settings.territory.rule.update.before', $id);

            $rule = $this->territoryRuleRepository->toggleActiveStatus($id);

            Event::dispatch('settings.territory.rule.update.after', $rule);

            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.toggle-success'),
                'is_active' => $rule->is_active,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.toggle-failed'),
            ], 400);
        }
    }

    /**
     * Update rule priority.
     */
    public function updatePriority(int $territoryId, int $id): JsonResponse
    {
        $this->validate(request(), [
            'priority' => 'required|integer|min:0',
        ]);

        $rule = $this->territoryRuleRepository->findOrFail($id);

        try {
            Event::dispatch('settings.territory.rule.update.before', $id);

            $rule = $this->territoryRuleRepository->updatePriority($id, request('priority'));

            Event::dispatch('settings.territory.rule.update.after', $rule);

            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.priority-success'),
                'priority' => $rule->priority,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.priority-failed'),
            ], 400);
        }
    }

    /**
     * Bulk update rule priorities.
     */
    public function bulkUpdatePriorities(int $territoryId): JsonResponse
    {
        $this->validate(request(), [
            'priorities' => 'required|array',
            'priorities.*' => 'required|integer|min:0',
        ]);

        try {
            Event::dispatch('settings.territory.rule.bulk-update.before');

            $this->territoryRuleRepository->bulkUpdatePriorities(request('priorities'));

            Event::dispatch('settings.territory.rule.bulk-update.after');

            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.bulk-priority-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.rules.index.bulk-priority-failed'),
            ], 400);
        }
    }
}
