<?php

namespace Webkul\Marketplace\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\DataGrid\DataGrid;

class ExtensionDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('extensions')
            ->addSelect(
                'extensions.id',
                'extensions.name',
                'extensions.slug',
                'extensions.type',
                'extensions.price',
                'extensions.status',
                'extensions.downloads_count',
                'extensions.average_rating',
                'extensions.featured',
                'extensions.logo',
                'extensions.created_at',
                'users.name as author_name',
                'extension_categories.name as category_name'
            )
            ->leftJoin('users', 'extensions.author_id', '=', 'users.id')
            ->leftJoin('extension_categories', 'extensions.category_id', '=', 'extension_categories.id');

        $this->addFilter('id', 'extensions.id');
        $this->addFilter('name', 'extensions.name');
        $this->addFilter('type', 'extensions.type');
        $this->addFilter('status', 'extensions.status');
        $this->addFilter('featured', 'extensions.featured');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'    => 'id',
            'label'    => trans('marketplace::app.admin.extensions.index.datagrid.id'),
            'type'     => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.name'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
            'closure'    => function ($row) {
                return [
                    'image' => $row->logo ? Storage::url($row->logo) : null,
                    'name'  => $row->name,
                ];
            },
        ]);

        $this->addColumn([
            'index'      => 'type',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.type'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
            'closure'    => function ($row) {
                return ucfirst($row->type);
            },
        ]);

        $this->addColumn([
            'index'      => 'category_name',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.category'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'author_name',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.author'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'price',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.price'),
            'type'       => 'string',
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->price > 0
                    ? core()->formatBasePrice($row->price)
                    : trans('marketplace::app.admin.extensions.index.datagrid.free');
            },
        ]);

        $this->addColumn([
            'index'      => 'downloads_count',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.downloads'),
            'type'       => 'string',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'average_rating',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.rating'),
            'type'       => 'string',
            'sortable'   => true,
            'closure'    => function ($row) {
                return number_format($row->average_rating, 1);
            },
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.status'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
            'closure'    => function ($row) {
                return ucfirst($row->status);
            },
        ]);

        $this->addColumn([
            'index'      => 'featured',
            'label'      => trans('marketplace::app.admin.extensions.index.datagrid.featured'),
            'type'       => 'boolean',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('marketplace::app.admin.extensions.index.datagrid.created-at'),
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
        if (bouncer()->hasPermission('marketplace.extensions.view')) {
            $this->addAction([
                'index'  => 'view',
                'icon'   => 'icon-eye',
                'title'  => trans('marketplace::app.admin.extensions.index.datagrid.view'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.marketplace.extensions.show', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('marketplace.extensions.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('marketplace::app.admin.extensions.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.marketplace.extensions.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('marketplace.extensions.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('marketplace::app.admin.extensions.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.marketplace.extensions.destroy', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('marketplace.extensions.delete')) {
            $this->addMassAction([
                'icon'   => 'icon-delete',
                'title'  => trans('marketplace::app.admin.extensions.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.marketplace.extensions.mass_destroy'),
            ]);
        }

        if (bouncer()->hasPermission('marketplace.extensions.edit')) {
            $this->addMassAction([
                'title'   => trans('marketplace::app.admin.extensions.index.datagrid.update-status'),
                'method'  => 'POST',
                'url'     => route('admin.marketplace.extensions.mass_enable'),
                'options' => [
                    [
                        'label' => trans('marketplace::app.admin.extensions.index.datagrid.enable'),
                        'value' => 'enable',
                    ],
                    [
                        'label' => trans('marketplace::app.admin.extensions.index.datagrid.disable'),
                        'value' => 'disable',
                    ],
                ],
            ]);
        }
    }
}
