<?php

namespace Webkul\Admin\DataGrids\Collaboration;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ChannelDataGrid extends DataGrid
{
    /**
     * Default sort column of datagrid.
     *
     * @var ?string
     */
    protected $sortColumn = 'created_at';

    /**
     * Default sort order of datagrid.
     *
     * @var string
     */
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('chat_channels')
            ->leftJoin('users', 'chat_channels.created_by', '=', 'users.id')
            ->select(
                'chat_channels.id',
                'chat_channels.name',
                'chat_channels.description',
                'chat_channels.type',
                'chat_channels.created_at',
                'users.name as created_by_name',
            );

        $this->addFilter('id', 'chat_channels.id');
        $this->addFilter('name', 'chat_channels.name');
        $this->addFilter('type', 'chat_channels.type');
        $this->addFilter('created_at', 'chat_channels.created_at');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.collaboration.channels.index.datagrid.id') ?: 'ID',
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.collaboration.channels.index.datagrid.name') ?: 'Name',
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'description',
            'label' => trans('admin::app.collaboration.channels.index.datagrid.description') ?: 'Description',
            'type' => 'string',
            'sortable' => false,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => trans('admin::app.collaboration.channels.index.datagrid.type') ?: 'Type',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
        ]);


        $this->addColumn([
            'index' => 'created_by_name',
            'label' => trans('admin::app.collaboration.channels.index.datagrid.created_by') ?: 'Created By',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.collaboration.channels.index.datagrid.created_at') ?: 'Created At',
            'type' => 'datetime',
            'sortable' => true,
            'filterable' => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-eye',
            'title' => trans('admin::app.collaboration.channels.index.datagrid.view') ?: 'View',
            'method' => 'GET',
            'url' => fn($row) => route('admin.collaboration.channels.show', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-edit',
            'title' => trans('admin::app.collaboration.channels.index.datagrid.edit') ?: 'Edit',
            'method' => 'GET',
            'url' => fn($row) => route('admin.collaboration.channels.edit', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.collaboration.channels.index.datagrid.delete') ?: 'Delete',
            'method' => 'DELETE',
            'url' => fn($row) => route('admin.collaboration.channels.destroy', $row->id),
        ]);
    }
}


