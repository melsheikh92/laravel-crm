<?php

namespace Webkul\Admin\DataGrids\Collaboration;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class NotificationDataGrid extends DataGrid
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
        $userId = auth()->guard('user')->id();

        $queryBuilder = DB::table('notifications')
            ->select(
                'notifications.id',
                'notifications.type',
                'notifications.title',
                'notifications.message',
                'notifications.read_at',
                'notifications.created_at',
            )
            ->where('notifications.user_id', $userId);

        $this->addFilter('id', 'notifications.id');
        $this->addFilter('type', 'notifications.type');
        $this->addFilter('title', 'notifications.title');
        $this->addFilter('read_at', 'notifications.read_at');
        $this->addFilter('created_at', 'notifications.created_at');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.collaboration.notifications.index.datagrid.id') ?: 'ID',
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'type',
            'label'      => trans('admin::app.collaboration.notifications.index.datagrid.type') ?: 'Type',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => trans('admin::app.collaboration.notifications.index.datagrid.title') ?: 'Title',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'message',
            'label'      => trans('admin::app.collaboration.notifications.index.datagrid.message') ?: 'Message',
            'type'       => 'string',
            'sortable'   => false,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'read_at',
            'label'      => trans('admin::app.collaboration.notifications.index.datagrid.status') ?: 'Status',
            'type'       => 'boolean',
            'sortable'   => true,
            'filterable' => true,
            'closure'    => function ($row) {
                if ($row->read_at) {
                    return '<span class="badge badge-success">' . (trans('admin::app.collaboration.notifications.index.datagrid.read') ?: 'Read') . '</span>';
                }

                return '<span class="badge badge-warning">' . (trans('admin::app.collaboration.notifications.index.datagrid.unread') ?: 'Unread') . '</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.collaboration.notifications.index.datagrid.created_at') ?: 'Created At',
            'type'       => 'datetime',
            'sortable'   => true,
            'filterable' => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        $this->addAction([
            'icon'   => 'icon-eye',
            'title'  => trans('admin::app.collaboration.notifications.index.datagrid.mark-as-read') ?: 'Mark as Read',
            'method' => 'POST',
            'url'    => fn ($row) => route('admin.collaboration.notifications.read', $row->id),
        ]);
    }
}

