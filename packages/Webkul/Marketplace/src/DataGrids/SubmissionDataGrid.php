<?php

namespace Webkul\Marketplace\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class SubmissionDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('extension_submissions')
            ->addSelect(
                'extension_submissions.id',
                'extension_submissions.status',
                'extension_submissions.submitted_at',
                'extension_submissions.reviewed_at',
                'extension_submissions.security_scan_results',
                'extensions.name as extension_name',
                'extensions.type as extension_type',
                'extension_versions.version as version_number',
                'submitters.name as submitter_name',
                'reviewers.name as reviewer_name'
            )
            ->leftJoin('extensions', 'extension_submissions.extension_id', '=', 'extensions.id')
            ->leftJoin('extension_versions', 'extension_submissions.version_id', '=', 'extension_versions.id')
            ->leftJoin('users as submitters', 'extension_submissions.submitted_by', '=', 'submitters.id')
            ->leftJoin('users as reviewers', 'extension_submissions.reviewer_id', '=', 'reviewers.id');

        $this->addFilter('id', 'extension_submissions.id');
        $this->addFilter('status', 'extension_submissions.status');
        $this->addFilter('extension_name', 'extensions.name');
        $this->addFilter('submitter_name', 'submitters.name');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.id'),
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'extension_name',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.extension'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'extension_type',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.type'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'closure' => function ($row) {
                return ucfirst($row->extension_type);
            },
        ]);

        $this->addColumn([
            'index' => 'version_number',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.version'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index' => 'submitter_name',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.submitter'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.status'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                $statusClasses = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ];

                $class = $statusClasses[$row->status] ?? 'secondary';

                return '<span class="label label-' . $class . '">' . ucfirst($row->status) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'security_scan_results',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.security'),
            'type' => 'string',
            'sortable' => false,
            'closure' => function ($row) {
                if (!$row->security_scan_results) {
                    return '<span class="label label-secondary">Not Scanned</span>';
                }

                $results = json_decode($row->security_scan_results, true);

                if (isset($results['passed']) && $results['passed']) {
                    return '<span class="label label-success">Passed</span>';
                }

                $issueCount = isset($results['summary']['total_issues']) ? $results['summary']['total_issues'] : 0;

                return '<span class="label label-danger">Failed (' . $issueCount . ' issues)</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'submitted_at',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.submitted-at'),
            'type' => 'datetime',
            'sortable' => true,
            'searchable' => true,
            'filterable_type' => 'datetime_range',
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'reviewer_name',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.reviewer'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'closure' => function ($row) {
                return $row->reviewer_name ?? '-';
            },
        ]);

        $this->addColumn([
            'index' => 'reviewed_at',
            'label' => trans('marketplace::app.admin.submissions.index.datagrid.reviewed-at'),
            'type' => 'datetime',
            'sortable' => true,
            'searchable' => true,
            'filterable_type' => 'datetime_range',
            'filterable' => true,
            'closure' => function ($row) {
                return $row->reviewed_at ?? '-';
            },
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('marketplace.submissions.view')) {
            $this->addAction([
                'index' => 'view',
                'icon' => 'icon-eye',
                'title' => trans('marketplace::app.admin.submissions.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn($row) => route('admin.marketplace.submissions.show', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('marketplace.submissions.review')) {
            $this->addAction([
                'index' => 'review',
                'icon' => 'icon-settings',
                'title' => trans('marketplace::app.admin.submissions.index.datagrid.review'),
                'method' => 'GET',
                'url' => fn($row) => route('admin.marketplace.submissions.review', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('marketplace.submissions.review')) {
            $this->addMassAction([
                'icon' => 'icon-check',
                'title' => trans('marketplace::app.admin.submissions.index.datagrid.approve'),
                'method' => 'POST',
                'url' => route('admin.marketplace.submissions.mass_approve'),
            ]);

            $this->addMassAction([
                'icon' => 'icon-cancel',
                'title' => trans('marketplace::app.admin.submissions.index.datagrid.reject'),
                'method' => 'POST',
                'url' => route('admin.marketplace.submissions.mass_reject'),
            ]);
        }
    }
}
