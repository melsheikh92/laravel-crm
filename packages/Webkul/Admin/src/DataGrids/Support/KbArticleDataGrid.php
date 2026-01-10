<?php

namespace Webkul\Admin\DataGrids\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Support\Repositories\KbCategoryRepository;

class KbArticleDataGrid extends DataGrid
{
    public function __construct(
        protected KbCategoryRepository $categoryRepository
    ) {
    }

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('kb_articles')
            ->addSelect(
                'kb_articles.id',
                'kb_articles.title',
                'kb_articles.status',
                'kb_articles.visibility',
                'kb_articles.view_count',
                'kb_articles.helpful_count',
                'kb_articles.not_helpful_count',
                'kb_articles.published_at',
                'kb_articles.created_at',
                'kb_categories.name as category_name',
                'users.name as author_name'
            )
            ->leftJoin('kb_categories', 'kb_articles.category_id', '=', 'kb_categories.id')
            ->leftJoin('users', 'kb_articles.author_id', '=', 'users.id')
            ->whereNull('kb_articles.deleted_at');

        $this->addFilter('id', 'kb_articles.id');
        $this->addFilter('title', 'kb_articles.title');
        $this->addFilter('status', 'kb_articles.status');
        $this->addFilter('visibility', 'kb_articles.visibility');
        $this->addFilter('category_name', 'kb_categories.id');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.support.kb.index.datagrid.id'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => trans('admin::app.support.kb.index.datagrid.title'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $route = route('admin.support.kb.articles.edit', $row->id);
                return "<a class='text-brandColor transition-all hover:underline' href='{$route}'>{$row->title}</a>";
            },
        ]);

        $this->addColumn([
            'index' => 'category_name',
            'label' => trans('admin::app.support.kb.index.datagrid.category'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->categoryRepository->getActiveCategories()
                ->map(fn($cat) => ['label' => $cat->name, 'value' => $cat->id])
                ->toArray(),
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.support.kb.index.datagrid.status'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Draft', 'value' => 'draft'],
                ['label' => 'Published', 'value' => 'published'],
                ['label' => 'Archived', 'value' => 'archived'],
            ],
            'closure' => function ($row) {
                $colors = [
                    'draft' => 'gray',
                    'published' => 'green',
                    'archived' => 'orange',
                ];
                $color = $colors[$row->status] ?? 'gray';
                return "<span class='badge badge-{$color}'>" . ucfirst($row->status) . "</span>";
            },
        ]);

        $this->addColumn([
            'index' => 'visibility',
            'label' => trans('admin::app.support.kb.index.datagrid.visibility'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Public', 'value' => 'public'],
                ['label' => 'Internal', 'value' => 'internal'],
                ['label' => 'Customer Portal', 'value' => 'customer_portal'],
            ],
            'closure' => fn($row) => ucwords(str_replace('_', ' ', $row->visibility)),
        ]);

        $this->addColumn([
            'index' => 'author_name',
            'label' => trans('admin::app.support.kb.index.datagrid.author'),
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'view_count',
            'label' => trans('admin::app.support.kb.index.datagrid.views'),
            'type' => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'helpful_count',
            'label' => trans('admin::app.support.kb.index.datagrid.helpful'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => function ($row) {
                $total = $row->helpful_count + $row->not_helpful_count;
                if ($total === 0)
                    return '0 (0%)';
                $percentage = round(($row->helpful_count / $total) * 100);
                return "{$row->helpful_count} ({$percentage}%)";
            },
        ]);

        $this->addColumn([
            'index' => 'published_at',
            'label' => trans('admin::app.support.kb.index.datagrid.published-at'),
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => fn($row) => $row->published_at ?? '--',
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-edit',
            'title' => trans('admin::app.support.kb.index.datagrid.edit'),
            'method' => 'GET',
            'url' => fn($row) => route('admin.support.kb.articles.edit', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.support.kb.index.datagrid.delete'),
            'method' => 'DELETE',
            'url' => fn($row) => route('admin.support.kb.articles.destroy', $row->id),
        ]);
    }

    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.support.kb.index.datagrid.mass-delete'),
            'method' => 'POST',
            'url' => route('admin.support.kb.articles.mass_destroy'),
        ]);

        $this->addMassAction([
            'title' => trans('admin::app.support.kb.index.datagrid.mass-publish'),
            'url' => route('admin.support.kb.articles.mass_update'),
            'method' => 'POST',
            'options' => [
                ['label' => 'Publish', 'value' => 'published'],
                ['label' => 'Draft', 'value' => 'draft'],
                ['label' => 'Archive', 'value' => 'archived'],
            ],
        ]);
    }
}
