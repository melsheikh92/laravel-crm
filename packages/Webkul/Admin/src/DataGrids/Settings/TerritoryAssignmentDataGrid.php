<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TerritoryAssignmentDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('territory_assignments')
            ->addSelect(
                'territory_assignments.id',
                'territory_assignments.assignable_type',
                'territory_assignments.assignable_id',
                'territory_assignments.assignment_type',
                'territory_assignments.assigned_at',
                DB::raw('territories.name as territory_name'),
                DB::raw('users.name as assigned_by_name'),
                DB::raw('CASE
                    WHEN territory_assignments.assignable_type = "Webkul\\\\Lead\\\\Models\\\\Lead" THEN leads.title
                    WHEN territory_assignments.assignable_type = "Webkul\\\\Contact\\\\Models\\\\Organization" THEN organizations.name
                    WHEN territory_assignments.assignable_type = "Webkul\\\\Contact\\\\Models\\\\Person" THEN CONCAT(persons.name, " (Person)")
                    ELSE "Unknown"
                END as assignable_name')
            )
            ->leftJoin('territories', 'territory_assignments.territory_id', '=', 'territories.id')
            ->leftJoin('users', 'territory_assignments.assigned_by', '=', 'users.id')
            ->leftJoin('leads', function ($join) {
                $join->on('territory_assignments.assignable_id', '=', 'leads.id')
                     ->where('territory_assignments.assignable_type', '=', 'Webkul\\Lead\\Models\\Lead');
            })
            ->leftJoin('organizations', function ($join) {
                $join->on('territory_assignments.assignable_id', '=', 'organizations.id')
                     ->where('territory_assignments.assignable_type', '=', 'Webkul\\Contact\\Models\\Organization');
            })
            ->leftJoin('persons', function ($join) {
                $join->on('territory_assignments.assignable_id', '=', 'persons.id')
                     ->where('territory_assignments.assignable_type', '=', 'Webkul\\Contact\\Models\\Person');
            });

        $this->addFilter('id', 'territory_assignments.id');
        $this->addFilter('assignable_type', 'territory_assignments.assignable_type');
        $this->addFilter('assignment_type', 'territory_assignments.assignment_type');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.settings.territory-assignments.index.datagrid.id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'territory_name',
            'label'      => trans('admin::app.settings.territory-assignments.index.datagrid.territory'),
            'type'       => 'string',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'    => 'assignable_name',
            'label'    => trans('admin::app.settings.territory-assignments.index.datagrid.assignable'),
            'type'     => 'string',
            'sortable' => false,
            'closure'  => function ($value) {
                $type = '';

                if (str_contains($value->assignable_type, 'Lead')) {
                    $type = trans('admin::app.settings.territory-assignments.index.datagrid.lead');
                } elseif (str_contains($value->assignable_type, 'Organization')) {
                    $type = trans('admin::app.settings.territory-assignments.index.datagrid.organization');
                } elseif (str_contains($value->assignable_type, 'Person')) {
                    $type = trans('admin::app.settings.territory-assignments.index.datagrid.person');
                }

                return $value->assignable_name . ' (' . $type . ')';
            },
        ]);

        $this->addColumn([
            'index'    => 'assigned_by_name',
            'label'    => trans('admin::app.settings.territory-assignments.index.datagrid.assigned-by'),
            'type'     => 'string',
            'sortable' => true,
            'closure'  => fn ($value) => $value->assigned_by_name ?: trans('admin::app.settings.territory-assignments.index.datagrid.system'),
        ]);

        $this->addColumn([
            'index'      => 'assignment_type',
            'label'      => trans('admin::app.settings.territory-assignments.index.datagrid.assignment-type'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($value) => trans('admin::app.settings.territory-assignments.index.datagrid.'.$value->assignment_type),
        ]);

        $this->addColumn([
            'index'      => 'assigned_at',
            'label'      => trans('admin::app.settings.territory-assignments.index.datagrid.assigned-at'),
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.territories.assignments.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.settings.territory-assignments.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.settings.territories.assignments.delete', $row->id),
            ]);
        }
    }
}
