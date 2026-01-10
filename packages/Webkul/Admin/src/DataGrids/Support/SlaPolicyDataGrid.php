<?php

namespace Webkul\Admin\DataGrids\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class SlaPolicyDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('sla_policies')
            ->addSelect(
                'sla_policies.id',
                'sla_policies.name',
                'sla_policies.description',
                'sla_policies.is_active',
                'sla_policies.is_default',
                'sla_policies.business_hours_only',
                'sla_policies.created_at'
            );

        $this->addFilter('id', 'sla_policies.id');
        $this->addFilter('name', 'sla_policies.name');
        $this->addFilter('is_active', 'sla_policies.is_active');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.support.sla.index.datagrid.id'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.support.sla.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'description',
            'label' => trans('admin::app.support.sla.index.datagrid.description'),
            'type' => 'string',
            'sortable' => false,
            'closure' => fn($row) => $row->description ? substr($row->description, 0, 50) . '...' : '--',
        ]);

        $this->addColumn([
            'index' => 'is_default',
            'label' => trans('admin::app.support.sla.index.datagrid.default'),
            'type' => 'boolean',
            'sortable' => true,
            'closure' => fn($row) => $row->is_default
                ? "<span class='badge badge-blue'>Default</span>"
                : '--',
        ]);

        $this->addColumn([
            'index' => 'business_hours_only',
            'label' => trans('admin::app.support.sla.index.datagrid.business-hours'),
            'type' => 'boolean',
            'sortable' => true,
            'closure' => fn($row) => $row->business_hours_only ? 'Yes' : 'No',
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => trans('admin::app.support.sla.index.datagrid.status'),
            'type' => 'boolean',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn($row) => $row->is_active
                ? "<span class='badge badge-green'>Active</span>"
                : "<span class='badge badge-red'>Inactive</span>",
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.support.sla.index.datagrid.created-at'),
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-edit',
            'title' => trans('admin::app.support.sla.index.datagrid.edit'),
            'method' => 'GET',
            'url' => fn($row) => route('admin.support.sla.policies.edit', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.support.sla.index.datagrid.delete'),
            'method' => 'DELETE',
            'url' => fn($row) => route('admin.support.sla.policies.destroy', $row->id),
        ]);
    }
}
