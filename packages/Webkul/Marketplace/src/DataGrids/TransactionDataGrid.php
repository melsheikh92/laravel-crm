<?php

namespace Webkul\Marketplace\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TransactionDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('extension_transactions')
            ->addSelect(
                'extension_transactions.id',
                'extension_transactions.transaction_id',
                'extension_transactions.amount',
                'extension_transactions.platform_fee',
                'extension_transactions.seller_revenue',
                'extension_transactions.payment_method',
                'extension_transactions.status',
                'extension_transactions.created_at',
                'extensions.name as extension_name',
                'buyers.name as buyer_name',
                'sellers.name as seller_name'
            )
            ->leftJoin('extensions', 'extension_transactions.extension_id', '=', 'extensions.id')
            ->leftJoin('users as buyers', 'extension_transactions.buyer_id', '=', 'buyers.id')
            ->leftJoin('users as sellers', 'extension_transactions.seller_id', '=', 'sellers.id');

        $this->addFilter('id', 'extension_transactions.id');
        $this->addFilter('transaction_id', 'extension_transactions.transaction_id');
        $this->addFilter('status', 'extension_transactions.status');
        $this->addFilter('payment_method', 'extension_transactions.payment_method');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'    => 'id',
            'label'    => trans('marketplace::app.admin.revenue.transactions.datagrid.id'),
            'type'     => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'transaction_id',
            'label'      => trans('marketplace::app.admin.revenue.transactions.datagrid.transaction-id'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'extension_name',
            'label'      => trans('marketplace::app.admin.revenue.transactions.datagrid.extension'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'buyer_name',
            'label'      => trans('marketplace::app.admin.revenue.transactions.datagrid.buyer'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'seller_name',
            'label'      => trans('marketplace::app.admin.revenue.transactions.datagrid.seller'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'    => 'amount',
            'label'    => trans('marketplace::app.admin.revenue.transactions.datagrid.amount'),
            'type'     => 'string',
            'sortable' => true,
            'closure'  => function ($row) {
                return '$' . number_format($row->amount, 2);
            },
        ]);

        $this->addColumn([
            'index'    => 'platform_fee',
            'label'    => trans('marketplace::app.admin.revenue.transactions.datagrid.platform-fee'),
            'type'     => 'string',
            'sortable' => true,
            'closure'  => function ($row) {
                return '$' . number_format($row->platform_fee, 2);
            },
        ]);

        $this->addColumn([
            'index'    => 'seller_revenue',
            'label'    => trans('marketplace::app.admin.revenue.transactions.datagrid.seller-revenue'),
            'type'     => 'string',
            'sortable' => true,
            'closure'  => function ($row) {
                return '$' . number_format($row->seller_revenue, 2);
            },
        ]);

        $this->addColumn([
            'index'      => 'payment_method',
            'label'      => trans('marketplace::app.admin.revenue.transactions.datagrid.payment-method'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
            'closure'    => function ($row) {
                return ucfirst($row->payment_method);
            },
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('marketplace::app.admin.revenue.transactions.datagrid.status'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
            'closure'    => function ($row) {
                $statusClasses = [
                    'completed' => 'badge-success',
                    'pending'   => 'badge-warning',
                    'failed'    => 'badge-danger',
                    'refunded'  => 'badge-info',
                    'cancelled' => 'badge-secondary',
                ];

                $statusClass = $statusClasses[$row->status] ?? 'badge-secondary';

                return [
                    'badge' => $statusClass,
                    'label' => ucfirst($row->status),
                ];
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('marketplace::app.admin.revenue.transactions.datagrid.created-at'),
            'type'       => 'date_range',
            'sortable'   => true,
            'searchable' => false,
            'filterable' => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('marketplace.revenue.view')) {
            $this->addAction([
                'icon'   => 'icon-eye',
                'title'  => trans('marketplace::app.admin.revenue.transactions.datagrid.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.marketplace.revenue.show', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('marketplace.revenue.refund')) {
            $this->addAction([
                'icon'   => 'icon-refresh',
                'title'  => trans('marketplace::app.admin.revenue.transactions.datagrid.refund'),
                'method' => 'POST',
                'url'    => function ($row) {
                    return route('admin.marketplace.revenue.refund', $row->id);
                },
                'condition' => function ($row) {
                    return $row->status === 'completed';
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        // No mass actions for transactions - they should be handled individually
    }
}
