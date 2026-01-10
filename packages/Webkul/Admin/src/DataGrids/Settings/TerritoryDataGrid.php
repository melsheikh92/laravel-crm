<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TerritoryDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('territories')
            ->addSelect(
                'territories.id',
                'territories.name',
                'territories.type',
                'territories.status',
                DB::raw('parent_territories.name as parent_name'),
                DB::raw('users.name as owner_name')
            )
            ->leftJoin('territories as parent_territories', 'territories.parent_id', '=', 'parent_territories.id')
            ->leftJoin('users', 'territories.user_id', '=', 'users.id');

        $this->addFilter('id', 'territories.id');
        $this->addFilter('name', 'territories.name');
        $this->addFilter('type', 'territories.type');
        $this->addFilter('status', 'territories.status');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.settings.territories.index.datagrid.id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.settings.territories.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'type',
            'label'      => trans('admin::app.settings.territories.index.datagrid.type'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($value) => trans('admin::app.settings.territories.index.datagrid.'.$value->type),
        ]);

        $this->addColumn([
            'index'    => 'parent_name',
            'label'    => trans('admin::app.settings.territories.index.datagrid.parent'),
            'type'     => 'string',
            'sortable' => true,
            'closure'  => fn ($value) => $value->parent_name ?: trans('admin::app.settings.territories.index.datagrid.none'),
        ]);

        $this->addColumn([
            'index'    => 'owner_name',
            'label'    => trans('admin::app.settings.territories.index.datagrid.owner'),
            'type'     => 'string',
            'sortable' => true,
            'closure'  => fn ($value) => $value->owner_name ?: trans('admin::app.settings.territories.index.datagrid.unassigned'),
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('admin::app.settings.territories.index.datagrid.status'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($value) => trans('admin::app.settings.territories.index.datagrid.'.$value->status),
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.territories.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.settings.territories.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.settings.territories.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.territories.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.settings.territories.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.settings.territories.delete', $row->id),
            ]);
        }
    }
}
