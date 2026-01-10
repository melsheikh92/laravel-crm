<?php

namespace Webkul\Admin\DataGrids\Marketing;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CampaignDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('email_campaigns')
            ->leftJoin('users', 'email_campaigns.user_id', '=', 'users.id')
            ->select(
                'email_campaigns.id',
                'email_campaigns.name',
                'email_campaigns.subject',
                'email_campaigns.status',
                'email_campaigns.scheduled_at',
                'email_campaigns.sent_count',
                'email_campaigns.failed_count',
                'email_campaigns.created_at',
                'users.name as user_name',
            );

        $this->addFilter('id', 'email_campaigns.id');
        $this->addFilter('name', 'email_campaigns.name');
        $this->addFilter('status', 'email_campaigns.status');
        $this->addFilter('created_at', 'email_campaigns.created_at');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => $this->getTranslation('id', 'ID'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => $this->getTranslation('name', 'Name'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'subject',
            'label' => $this->getTranslation('subject', 'Subject'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => $this->getTranslation('status', 'Status'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                $status = $row->status ?? '';
                if (empty($status)) {
                    return '--';
                }

                $translationKey = 'admin::app.marketing.campaigns.status.' . $status;
                $translation = trans($translationKey);

                // If translation returns the key itself (meaning translation not found), capitalize the status
                if ($translation === $translationKey) {
                    return ucfirst($status);
                }

                return $translation;
            },
        ]);

        $this->addColumn([
            'index' => 'sent_count',
            'label' => $this->getTranslation('sent', 'Sent'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'failed_count',
            'label' => $this->getTranslation('failed', 'Failed'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'user_name',
            'label' => $this->getTranslation('created_by', 'Created By'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => $this->getTranslation('created_at', 'Created At'),
            'type' => 'datetime',
            'sortable' => true,
            'filterable' => true,
        ]);
    }

    /**
     * Get translation with fallback.
     */
    protected function getTranslation(string $key, string $fallback): string
    {
        $translationKey = 'admin::app.marketing.campaigns.index.datagrid.' . $key;
        $translation = trans($translationKey);

        // If translation returns the key itself, use fallback
        return $translation !== $translationKey ? $translation : $fallback;
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-eye',
            'title' => $this->getTranslation('view', 'View'),
            'method' => 'GET',
            'url' => fn($row) => route('admin.marketing.campaigns.show', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-edit',
            'title' => $this->getTranslation('edit', 'Edit'),
            'method' => 'GET',
            'url' => fn($row) => route('admin.marketing.campaigns.edit', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => $this->getTranslation('delete', 'Delete'),
            'method' => 'DELETE',
            'url' => fn($row) => route('admin.marketing.campaigns.destroy', $row->id),
        ]);
    }
}

