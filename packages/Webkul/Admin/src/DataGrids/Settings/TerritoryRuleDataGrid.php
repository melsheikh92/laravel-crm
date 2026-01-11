<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TerritoryRuleDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('territory_rules')
            ->addSelect(
                'territory_rules.id',
                'territory_rules.rule_type',
                'territory_rules.field_name',
                'territory_rules.operator',
                'territory_rules.value',
                'territory_rules.priority',
                'territory_rules.is_active',
                DB::raw('territories.name as territory_name')
            )
            ->leftJoin('territories', 'territory_rules.territory_id', '=', 'territories.id');

        $this->addFilter('id', 'territory_rules.id');
        $this->addFilter('rule_type', 'territory_rules.rule_type');
        $this->addFilter('field_name', 'territory_rules.field_name');
        $this->addFilter('operator', 'territory_rules.operator');
        $this->addFilter('is_active', 'territory_rules.is_active');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.settings.territory-rules.index.datagrid.id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'territory_name',
            'label'      => trans('admin::app.settings.territory-rules.index.datagrid.territory'),
            'type'       => 'string',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'rule_type',
            'label'      => trans('admin::app.settings.territory-rules.index.datagrid.rule-type'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($value) => trans('admin::app.settings.territory-rules.index.datagrid.'.$value->rule_type),
        ]);

        $this->addColumn([
            'index'      => 'field_name',
            'label'      => trans('admin::app.settings.territory-rules.index.datagrid.field'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'operator',
            'label'      => trans('admin::app.settings.territory-rules.index.datagrid.operator'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'    => 'value',
            'label'    => trans('admin::app.settings.territory-rules.index.datagrid.value'),
            'type'     => 'string',
            'sortable' => false,
            'closure'  => function ($value) {
                $decoded = json_decode($value->value, true);

                if (is_array($decoded)) {
                    return implode(', ', array_slice($decoded, 0, 3)) . (count($decoded) > 3 ? '...' : '');
                }

                return is_string($decoded) ? $decoded : json_encode($decoded);
            },
        ]);

        $this->addColumn([
            'index'      => 'priority',
            'label'      => trans('admin::app.settings.territory-rules.index.datagrid.priority'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'is_active',
            'label'      => trans('admin::app.settings.territory-rules.index.datagrid.status'),
            'type'       => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($value) => $value->is_active
                ? trans('admin::app.settings.territory-rules.index.datagrid.active')
                : trans('admin::app.settings.territory-rules.index.datagrid.inactive'),
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.territories.rules.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.settings.territory-rules.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.settings.territories.rules.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.territories.rules.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.settings.territory-rules.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.settings.territories.rules.delete', $row->id),
            ]);
        }
    }
}
