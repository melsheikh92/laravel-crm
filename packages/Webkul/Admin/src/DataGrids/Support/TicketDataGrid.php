<?php

namespace Webkul\Admin\DataGrids\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Support\Repositories\TicketCategoryRepository;
use Webkul\User\Repositories\UserRepository;

class TicketDataGrid extends DataGrid
{
    public function __construct(
        protected TicketCategoryRepository $categoryRepository,
        protected UserRepository $userRepository
    ) {
    }

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('support_tickets')
            ->addSelect(
                'support_tickets.id',
                'support_tickets.ticket_number',
                'support_tickets.subject',
                'support_tickets.status',
                'support_tickets.priority',
                'support_tickets.created_at',
                'support_tickets.sla_breached',
                'ticket_categories.name as category_name',
                'users.name as assigned_to_name',
                'persons.name as customer_name'
            )
            ->leftJoin('ticket_categories', 'support_tickets.category_id', '=', 'ticket_categories.id')
            ->leftJoin('users', 'support_tickets.assigned_to', '=', 'users.id')
            ->leftJoin('persons', 'support_tickets.customer_id', '=', 'persons.id');

        $this->addFilter('id', 'support_tickets.id');
        $this->addFilter('ticket_number', 'support_tickets.ticket_number');
        $this->addFilter('subject', 'support_tickets.subject');
        $this->addFilter('status', 'support_tickets.status');
        $this->addFilter('priority', 'support_tickets.priority');
        $this->addFilter('category_name', 'ticket_categories.id');
        $this->addFilter('assigned_to_name', 'users.id');
        $this->addFilter('customer_name', 'persons.name');
        $this->addFilter('created_at', 'support_tickets.created_at');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.support.tickets.index.datagrid.id'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'ticket_number',
            'label' => trans('admin::app.support.tickets.index.datagrid.ticket-number'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'subject',
            'label' => trans('admin::app.support.tickets.index.datagrid.subject'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $route = route('admin.support.tickets.show', $row->id);
                return "<a class='text-brandColor transition-all hover:underline' href='{$route}'>{$row->subject}</a>";
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.support.tickets.index.datagrid.status'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Open', 'value' => 'open'],
                ['label' => 'In Progress', 'value' => 'in_progress'],
                ['label' => 'Waiting Customer', 'value' => 'waiting_customer'],
                ['label' => 'Waiting Internal', 'value' => 'waiting_internal'],
                ['label' => 'Resolved', 'value' => 'resolved'],
                ['label' => 'Closed', 'value' => 'closed'],
            ],
            'closure' => function ($row) {
                $colors = [
                    'open' => 'blue',
                    'in_progress' => 'yellow',
                    'waiting_customer' => 'orange',
                    'waiting_internal' => 'purple',
                    'resolved' => 'green',
                    'closed' => 'gray',
                ];
                $color = $colors[$row->status] ?? 'gray';
                $label = ucwords(str_replace('_', ' ', $row->status));
                return "<span class='badge badge-{$color}'>{$label}</span>";
            },
        ]);

        $this->addColumn([
            'index' => 'priority',
            'label' => trans('admin::app.support.tickets.index.datagrid.priority'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Low', 'value' => 'low'],
                ['label' => 'Normal', 'value' => 'normal'],
                ['label' => 'High', 'value' => 'high'],
                ['label' => 'Urgent', 'value' => 'urgent'],
            ],
            'closure' => function ($row) {
                $colors = [
                    'urgent' => 'red',
                    'high' => 'orange',
                    'normal' => 'blue',
                    'low' => 'gray',
                ];
                $color = $colors[$row->priority] ?? 'gray';
                return "<span class='badge badge-{$color}'>" . ucfirst($row->priority) . "</span>";
            },
        ]);

        $this->addColumn([
            'index' => 'category_name',
            'label' => trans('admin::app.support.tickets.index.datagrid.category'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->categoryRepository->getActiveCategories()
                ->map(fn($cat) => ['label' => $cat->name, 'value' => $cat->id])
                ->toArray(),
            'closure' => fn($row) => $row->category_name ?? '--',
        ]);

        $this->addColumn([
            'index' => 'assigned_to_name',
            'label' => trans('admin::app.support.tickets.index.datagrid.assigned-to'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => UserRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'id',
                ],
            ],
            'closure' => fn($row) => $row->assigned_to_name ?? 'Unassigned',
        ]);

        $this->addColumn([
            'index' => 'customer_name',
            'label' => trans('admin::app.support.tickets.index.datagrid.customer'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'sla_breached',
            'label' => trans('admin::app.support.tickets.index.datagrid.sla-status'),
            'type' => 'boolean',
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if ($row->sla_breached) {
                    return "<span class='badge badge-red'>Breached</span>";
                }
                return "<span class='badge badge-green'>Compliant</span>";
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.support.tickets.index.datagrid.created-at'),
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-eye',
            'title' => trans('admin::app.support.tickets.index.datagrid.view'),
            'method' => 'GET',
            'url' => fn($row) => route('admin.support.tickets.show', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-edit',
            'title' => trans('admin::app.support.tickets.index.datagrid.edit'),
            'method' => 'GET',
            'url' => fn($row) => route('admin.support.tickets.edit', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.support.tickets.index.datagrid.delete'),
            'method' => 'DELETE',
            'url' => fn($row) => route('admin.support.tickets.destroy', $row->id),
        ]);
    }

    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.support.tickets.index.datagrid.mass-delete'),
            'method' => 'POST',
            'url' => route('admin.support.tickets.mass_destroy'),
        ]);

        $this->addMassAction([
            'title' => trans('admin::app.support.tickets.index.datagrid.mass-update-status'),
            'url' => route('admin.support.tickets.mass_update'),
            'method' => 'POST',
            'options' => [
                ['label' => 'Open', 'value' => 'open'],
                ['label' => 'In Progress', 'value' => 'in_progress'],
                ['label' => 'Resolved', 'value' => 'resolved'],
                ['label' => 'Closed', 'value' => 'closed'],
            ],
        ]);
    }
}
