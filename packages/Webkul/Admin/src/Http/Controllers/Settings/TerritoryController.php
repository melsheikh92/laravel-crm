<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Territory\Repositories\TerritoryRepository;
use Webkul\User\Repositories\UserRepository;

class TerritoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TerritoryRepository $territoryRepository,
        protected UserRepository $userRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(\Webkul\Admin\DataGrids\Settings\TerritoryDataGrid::class)->process();
        }

        return view('admin::settings.territories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $territories = $this->territoryRepository->all();

        $users = $this->userRepository->all();

        return view('admin::settings.territories.create', compact('territories', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
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

        $data = request()->only([
            'name',
            'code',
            'type',
            'status',
            'description',
            'parent_id',
            'user_id',
            'boundaries',
        ]);

        $territory = $this->territoryRepository->create($data);

        Event::dispatch('settings.territory.create.after', $territory);

        session()->flash('success', trans('admin::app.settings.territories.index.create-success'));

        return redirect()->route('admin.settings.territories.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $territory = $this->territoryRepository->findOrFail($id);

        // Get all territories except the current one and its descendants
        // to prevent circular hierarchy
        $descendants = $this->territoryRepository->getDescendants($id);
        $excludeIds = $descendants->pluck('id')->push($id)->toArray();

        $territories = $this->territoryRepository
            ->scopeQuery(function ($query) use ($excludeIds) {
                return $query->whereNotIn('id', $excludeIds);
            })
            ->all();

        $users = $this->userRepository->all();

        return view('admin::settings.territories.edit', compact('territory', 'territories', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): RedirectResponse
    {
        $this->validate(request(), [
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
        if (request('parent_id')) {
            $descendants = $this->territoryRepository->getDescendants($id);
            $excludeIds = $descendants->pluck('id')->push($id)->toArray();

            if (in_array(request('parent_id'), $excludeIds)) {
                session()->flash('error', trans('admin::app.settings.territories.index.invalid-parent'));

                return redirect()->back();
            }
        }

        Event::dispatch('settings.territory.update.before', $id);

        $data = request()->only([
            'name',
            'code',
            'type',
            'status',
            'description',
            'parent_id',
            'user_id',
            'boundaries',
        ]);

        $territory = $this->territoryRepository->update($data, $id);

        Event::dispatch('settings.territory.update.after', $territory);

        session()->flash('success', trans('admin::app.settings.territories.index.update-success'));

        return redirect()->route('admin.settings.territories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $territory = $this->territoryRepository->findOrFail($id);

        try {
            Event::dispatch('settings.territory.delete.before', $id);

            $this->territoryRepository->delete($id);

            Event::dispatch('settings.territory.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.territories.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.territories.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Display territory hierarchy view.
     */
    public function hierarchy(): View
    {
        $territories = $this->territoryRepository->getHierarchy();

        return view('admin::settings.territories.hierarchy', compact('territories'));
    }
}
