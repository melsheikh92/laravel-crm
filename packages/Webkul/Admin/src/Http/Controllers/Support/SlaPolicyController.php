<?php

namespace Webkul\Admin\Http\Controllers\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\DataGrids\Support\SlaPolicyDataGrid;
use Webkul\Support\Repositories\SlaPolicyRepository;
use Webkul\Support\Services\SlaService;

class SlaPolicyController extends Controller
{
    public function __construct(
        protected SlaPolicyRepository $policyRepository,
        protected SlaService $slaService
    ) {
    }

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(SlaPolicyDataGrid::class)->process();
        }

        return view('admin::support.sla.policies.index');
    }

    public function create(): View
    {
        return view('admin::support.sla.policies.create');
    }

    public function store(): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'rules' => 'required|array',
            'rules.*.priority' => 'required|in:low,normal,high,urgent',
            'rules.*.first_response_time' => 'required|integer|min:1',
            'rules.*.resolution_time' => 'required|integer|min:1',
        ]);

        $policy = $this->policyRepository->create(request()->all());

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.sla.create-success'),
                'data' => $policy,
            ]);
        }

        session()->flash('success', trans('admin::app.support.sla.create-success'));

        return redirect()->route('admin.support.sla.policies.index');
    }

    public function edit(int $id): View
    {
        $policy = $this->policyRepository->with(['rules', 'conditions'])->findOrFail($id);

        return view('admin::support.sla.policies.edit', compact('policy'));
    }

    public function update(int $id): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
        ]);

        $policy = $this->policyRepository->update(request()->all(), $id);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.sla.update-success'),
                'data' => $policy,
            ]);
        }

        session()->flash('success', trans('admin::app.support.sla.update-success'));

        return redirect()->route('admin.support.sla.policies.index');
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->policyRepository->delete($id);

            return response()->json([
                'message' => trans('admin::app.support.sla.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('admin::app.support.sla.delete-failed'),
            ], 400);
        }
    }

    public function metrics(): JsonResponse
    {
        $startDate = request('start_date');
        $endDate = request('end_date');

        $metrics = [
            'compliance' => $this->slaService->getComplianceMetrics($startDate, $endDate),
            'avg_response_time' => $this->slaService->getAverageResponseTime($startDate, $endDate),
            'avg_resolution_time' => $this->slaService->getAverageResolutionTime($startDate, $endDate),
        ];

        return response()->json($metrics);
    }
}
