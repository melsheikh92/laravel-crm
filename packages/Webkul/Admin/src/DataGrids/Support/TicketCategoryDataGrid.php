<?php

namespace Webkul\Admin\DataGrids\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TicketCategoryDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('ticket_categories')
            ->addSelect(
                'ticket_categories.id',
                'ticket_categories.name',
                'ticket_categories.description',
                'ticket_categories.is_active',
                'ticket_categories.created_at',
                'parent.name as parent_name'
            )
            ->leftJoin('ticket_categories as parent', 'ticket_categories.parent_id', '=', 'parent.id');

        $this->addFilter('id', 'ticket_categories.id');
        $this->addFilter('name', 'ticket_categories.name');
        $this->addFilter('is_active', 'ticket_categories.is_active');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.support.categories.index.id'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.support.categories.index.name'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'parent_name',
            'label' => trans('admin::app.support.categories.index.parent'),
            'type' => 'string',
            'sortable' => true,
            'closure' => fn($row) => $row->parent_name ?? '--',
        ]);

        $this->addColumn([
            'index' => 'description',
            'label' => trans('admin::app.support.categories.index.description'),
            'type' => 'string',
            'sortable' => false,
            'closure' => fn($row) => $row->description ? substr($row->description, 0, 50) . '...' : '--',
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => trans('admin::app.support.categories.index.status'),
            'type' => 'boolean',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn($row) => $row->is_active
                ? "<span class='badge badge-green'>Active</span>"
                : "<span class='badge badge-red'>Inactive</span>",
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.support.categories.index.created-at'),
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
            'title' => trans('admin::app.support.categories.index.edit'),
            'method' => 'GET',
            'url' => fn($row) => route('admin.support.categories.edit', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.support.categories.index.delete'),
            'method' => 'DELETE',
            'url' => fn($row) => route('admin.support.categories.destroy', $row->id),
        ]);
    }
}
