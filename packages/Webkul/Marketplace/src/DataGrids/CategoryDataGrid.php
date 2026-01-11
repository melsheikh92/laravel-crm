<?php

namespace Webkul\Marketplace\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CategoryDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('extension_categories')
            ->addSelect(
                'extension_categories.id',
                'extension_categories.name',
                'extension_categories.slug',
                'extension_categories.description',
                'extension_categories.icon',
                'extension_categories.sort_order',
                'extension_categories.parent_id',
                'extension_categories.created_at',
                'parent_category.name as parent_name',
                DB::raw('(SELECT COUNT(*) FROM extensions WHERE extensions.category_id = extension_categories.id) as extensions_count'),
                DB::raw('(SELECT COUNT(*) FROM extension_categories as children WHERE children.parent_id = extension_categories.id) as children_count')
            )
            ->leftJoin('extension_categories as parent_category', 'extension_categories.parent_id', '=', 'parent_category.id');

        $this->addFilter('id', 'extension_categories.id');
        $this->addFilter('name', 'extension_categories.name');
        $this->addFilter('slug', 'extension_categories.slug');
        $this->addFilter('parent_name', 'parent_category.name');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'    => 'id',
            'label'    => trans('marketplace::app.admin.categories.index.datagrid.id'),
            'type'     => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('marketplace::app.admin.categories.index.datagrid.name'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'slug',
            'label'      => trans('marketplace::app.admin.categories.index.datagrid.slug'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'parent_name',
            'label'      => trans('marketplace::app.admin.categories.index.datagrid.parent'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
            'closure'    => function ($row) {
                return $row->parent_name ?? trans('marketplace::app.admin.categories.index.datagrid.root');
            },
        ]);

        $this->addColumn([
            'index'      => 'extensions_count',
            'label'      => trans('marketplace::app.admin.categories.index.datagrid.extensions-count'),
            'type'       => 'string',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'children_count',
            'label'      => trans('marketplace::app.admin.categories.index.datagrid.children-count'),
            'type'       => 'string',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('marketplace::app.admin.categories.index.datagrid.sort-order'),
            'type'       => 'string',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('marketplace::app.admin.categories.index.datagrid.created-at'),
            'type'            => 'date',
            'sortable'        => true,
            'searchable'      => true,
            'filterable_type' => 'date_range',
            'filterable'      => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('marketplace.categories.view')) {
            $this->addAction([
                'index'  => 'view',
                'icon'   => 'icon-eye',
                'title'  => trans('marketplace::app.admin.categories.index.datagrid.view'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.marketplace.categories.show', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('marketplace.categories.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('marketplace::app.admin.categories.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.marketplace.categories.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('marketplace.categories.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('marketplace::app.admin.categories.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.marketplace.categories.destroy', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('marketplace.categories.delete')) {
            $this->addMassAction([
                'icon'   => 'icon-delete',
                'title'  => trans('marketplace::app.admin.categories.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.marketplace.categories.mass_destroy'),
            ]);
        }
    }
}
