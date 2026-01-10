<?php

namespace Webkul\Admin\DataGrids\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class KbCategoryDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('kb_categories')
            ->addSelect(
                'kb_categories.id',
                'kb_categories.name',
                'kb_categories.description',
                'kb_categories.sort_order',
                'kb_categories.visibility',
                'kb_categories.is_active',
                'kb_categories.created_at',
                'parent.name as parent_name'
            )
            ->leftJoin('kb_categories as parent', 'kb_categories.parent_id', '=', 'parent.id');

        $this->addFilter('id', 'kb_categories.id');
        $this->addFilter('name', 'kb_categories.name');
        $this->addFilter('is_active', 'kb_categories.is_active');
        $this->addFilter('visibility', 'kb_categories.visibility');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.support.kb.categories.datagrid.id'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.support.kb.categories.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'parent_name',
            'label' => trans('admin::app.support.kb.categories.datagrid.parent'),
            'type' => 'string',
            'sortable' => true,
            'closure' => fn($row) => $row->parent_name ?? '--',
        ]);

        $this->addColumn([
            'index' => 'description',
            'label' => trans('admin::app.support.kb.categories.datagrid.description'),
            'type' => 'string',
            'sortable' => false,
            'closure' => fn($row) => $row->description ? substr(strip_tags($row->description), 0, 50) . '...' : '--',
        ]);

        $this->addColumn([
            'index' => 'sort_order',
            'label' => trans('admin::app.support.kb.categories.datagrid.sort-order'),
            'type' => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'visibility',
            'label' => trans('admin::app.support.kb.categories.datagrid.visibility'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => trans('admin::app.support.kb.categories.datagrid.status'),
            'type' => 'boolean',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn($row) => $row->is_active
                ? "<span class='badge badge-green'>Active</span>"
                : "<span class='badge badge-red'>Inactive</span>",
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-edit',
            'title' => trans('admin::app.support.kb.categories.datagrid.edit'),
            'method' => 'GET',
            'url' => fn($row) => route('admin.support.kb.categories.edit', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.support.kb.categories.datagrid.delete'),
            'method' => 'DELETE',
            'url' => fn($row) => route('admin.support.kb.categories.destroy', $row->id),
        ]);
    }
}
