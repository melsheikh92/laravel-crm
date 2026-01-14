<?php

namespace Webkul\Admin\DataGrids\Documentation;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use App\Repositories\DocCategoryRepository;

class DocArticleDataGrid extends DataGrid
{
    public function __construct(
        protected DocCategoryRepository $categoryRepository
    ) {
    }

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('doc_articles')
            ->addSelect(
                'doc_articles.id',
                'doc_articles.title',
                'doc_articles.slug',
                'doc_articles.status',
                'doc_articles.visibility',
                'doc_articles.type',
                'doc_articles.difficulty_level',
                'doc_articles.view_count',
                'doc_articles.helpful_count',
                'doc_articles.not_helpful_count',
                'doc_articles.featured',
                'doc_articles.published_at',
                'doc_articles.created_at',
                'doc_categories.name as category_name',
                'users.name as author_name'
            )
            ->leftJoin('doc_categories', 'doc_articles.category_id', '=', 'doc_categories.id')
            ->leftJoin('users', 'doc_articles.author_id', '=', 'users.id')
            ->whereNull('doc_articles.deleted_at');

        $this->addFilter('id', 'doc_articles.id');
        $this->addFilter('title', 'doc_articles.title');
        $this->addFilter('status', 'doc_articles.status');
        $this->addFilter('visibility', 'doc_articles.visibility');
        $this->addFilter('type', 'doc_articles.type');
        $this->addFilter('difficulty_level', 'doc_articles.difficulty_level');
        $this->addFilter('category_name', 'doc_categories.id');
        $this->addFilter('featured', 'doc_articles.featured');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => 'Title',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $route = route('admin.docs.edit', $row->id);
                $featuredIcon = $row->featured ? '<i class="icon-star text-yellow-500 mr-1" title="Featured"></i>' : '';
                $videoIcon = !empty($row->video_url) ? '<i class="icon-video text-blue-500 mr-1" title="Has Video"></i>' : '';
                return "<a class='text-brandColor transition-all hover:underline' href='{$route}'>{$featuredIcon}{$videoIcon}{$row->title}</a>";
            },
        ]);

        $this->addColumn([
            'index' => 'category_name',
            'label' => 'Category',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->categoryRepository->all()
                ->map(fn($cat) => ['label' => $cat->name, 'value' => $cat->id])
                ->toArray(),
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => 'Type',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Getting Started', 'value' => 'getting_started'],
                ['label' => 'API Doc', 'value' => 'api_doc'],
                ['label' => 'Feature Guide', 'value' => 'feature_guide'],
                ['label' => 'Troubleshooting', 'value' => 'troubleshooting'],
            ],
            'closure' => function ($row) {
                $labels = [
                    'getting_started' => 'Getting Started',
                    'api_doc' => 'API Doc',
                    'feature_guide' => 'Feature Guide',
                    'troubleshooting' => 'Troubleshooting',
                ];
                return $labels[$row->type] ?? ucwords(str_replace('_', ' ', $row->type));
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
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
            'label' => 'Visibility',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Public', 'value' => 'public'],
                ['label' => 'Internal', 'value' => 'internal'],
                ['label' => 'Private', 'value' => 'private'],
            ],
            'closure' => fn($row) => ucwords(str_replace('_', ' ', $row->visibility)),
        ]);

        $this->addColumn([
            'index' => 'difficulty_level',
            'label' => 'Difficulty',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Beginner', 'value' => 'beginner'],
                ['label' => 'Intermediate', 'value' => 'intermediate'],
                ['label' => 'Advanced', 'value' => 'advanced'],
            ],
            'closure' => function ($row) {
                $colors = [
                    'beginner' => 'green',
                    'intermediate' => 'yellow',
                    'advanced' => 'red',
                ];
                $color = $colors[$row->difficulty_level] ?? 'gray';
                $level = $row->difficulty_level ? ucfirst($row->difficulty_level) : '--';
                return "<span class='badge badge-{$color}'>{$level}</span>";
            },
        ]);

        $this->addColumn([
            'index' => 'author_name',
            'label' => 'Author',
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'view_count',
            'label' => 'Views',
            'type' => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'helpful_count',
            'label' => 'Helpful',
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
            'label' => 'Published At',
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
            'title' => 'Edit',
            'method' => 'GET',
            'url' => fn($row) => route('admin.docs.edit', $row->id),
        ]);

        $this->addAction([
            'icon' => 'icon-delete',
            'title' => 'Delete',
            'method' => 'DELETE',
            'url' => fn($row) => route('admin.docs.destroy', $row->id),
        ]);
    }

    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => 'Delete',
            'method' => 'POST',
            'url' => route('admin.docs.mass-destroy'),
        ]);

        $this->addMassAction([
            'title' => 'Update Status',
            'url' => route('admin.docs.mass-update'),
            'method' => 'POST',
            'options' => [
                ['label' => 'Publish', 'value' => 'published'],
                ['label' => 'Draft', 'value' => 'draft'],
                ['label' => 'Archive', 'value' => 'archived'],
            ],
        ]);
    }
}
