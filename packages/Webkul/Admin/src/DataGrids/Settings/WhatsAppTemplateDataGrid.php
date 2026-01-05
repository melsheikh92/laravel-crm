<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class WhatsAppTemplateDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('whatsapp_templates')
            ->addSelect(
                'whatsapp_templates.id',
                'whatsapp_templates.name',
                'whatsapp_templates.language',
                'whatsapp_templates.status',
                'whatsapp_templates.category',
            );

        $this->addFilter('id', 'whatsapp_templates.id');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.settings.whatsapp-template.index.datagrid.id'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.settings.whatsapp-template.index.datagrid.name'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'    => 'language',
            'label'    => trans('admin::app.settings.whatsapp-template.index.datagrid.language'),
            'type'     => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'    => 'status',
            'label'    => trans('admin::app.settings.whatsapp-template.index.datagrid.status'),
            'type'     => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'    => 'category',
            'label'    => trans('admin::app.settings.whatsapp-template.index.datagrid.category'),
            'type'     => 'string',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('settings.automation.whatsapp_templates.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.settings.whatsapp-template.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('whatsapp.templates.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.automation.whatsapp_templates.delete')) {
            $this->addAction([
                'index'          => 'delete',
                'icon'           => 'icon-delete',
                'title'          => trans('admin::app.settings.whatsapp-template.index.datagrid.delete'),
                'method'         => 'DELETE',
                'url'            => fn ($row) => route('whatsapp.templates.destroy', $row->id),
            ]);
        }
    }
}
