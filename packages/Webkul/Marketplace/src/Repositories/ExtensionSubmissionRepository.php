<?php

namespace Webkul\Marketplace\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Marketplace\Contracts\ExtensionSubmission;

class ExtensionSubmissionRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'status',
        'review_notes',
        'extension.name',
        'version.version',
        'submitter.name',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return ExtensionSubmission::class;
    }

    /**
     * Get pending submissions.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPending($columns = ['*'])
    {
        return $this->scopeQuery(function ($query) use ($columns) {
            return $query->pending()
                ->with(['extension', 'version', 'submitter'])
                ->orderBy('submitted_at', 'asc')
                ->select($columns);
        })->all();
    }

    /**
     * Get approved submissions.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApproved($columns = ['*'])
    {
        return $this->scopeQuery(function ($query) use ($columns) {
            return $query->approved()
                ->with(['extension', 'version', 'submitter', 'reviewer'])
                ->orderBy('reviewed_at', 'desc')
                ->select($columns);
        })->all();
    }

    /**
     * Get rejected submissions.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRejected($columns = ['*'])
    {
        return $this->scopeQuery(function ($query) use ($columns) {
            return $query->rejected()
                ->with(['extension', 'version', 'submitter', 'reviewer'])
                ->orderBy('reviewed_at', 'desc')
                ->select($columns);
        })->all();
    }

    /**
     * Get submissions by extension.
     *
     * @param  int  $extensionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByExtension($extensionId)
    {
        return $this->scopeQuery(function ($query) use ($extensionId) {
            return $query->forExtension($extensionId)
                ->with(['version', 'submitter', 'reviewer'])
                ->orderBy('submitted_at', 'desc');
        })->all();
    }

    /**
     * Get submissions by submitter.
     *
     * @param  int  $submitterId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySubmitter($submitterId)
    {
        return $this->scopeQuery(function ($query) use ($submitterId) {
            return $query->bySubmitter($submitterId)
                ->with(['extension', 'version', 'reviewer'])
                ->orderBy('submitted_at', 'desc');
        })->all();
    }

    /**
     * Get recent submissions.
     *
     * @param  int  $days
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecent($days = 7, $limit = null)
    {
        return $this->scopeQuery(function ($query) use ($days, $limit) {
            $query = $query->recent($days)
                ->with(['extension', 'version', 'submitter'])
                ->orderBy('submitted_at', 'desc');

            if ($limit) {
                $query->limit($limit);
            }

            return $query;
        })->all();
    }

    /**
     * Get pending submissions count.
     *
     * @return int
     */
    public function getPendingCount()
    {
        return $this->model->pending()->count();
    }

    /**
     * Get submissions with security issues.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWithSecurityIssues()
    {
        return $this->scopeQuery(function ($query) {
            return $query->whereNotNull('security_scan_results')
                ->where('status', 'pending')
                ->with(['extension', 'version', 'submitter'])
                ->orderBy('submitted_at', 'asc');
        })->all();
    }

    /**
     * Get submissions by status.
     *
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStatus($status)
    {
        return $this->scopeQuery(function ($query) use ($status) {
            return $query->where('status', $status)
                ->with(['extension', 'version', 'submitter', 'reviewer'])
                ->orderBy('submitted_at', 'desc');
        })->all();
    }

    /**
     * Get statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        $total = $this->model->count();
        $pending = $this->model->pending()->count();
        $approved = $this->model->approved()->count();
        $rejected = $this->model->rejected()->count();

        // Average review turnaround time (in hours) for reviewed submissions
        $avgTurnaroundTime = $this->model
            ->whereNotNull('reviewed_at')
            ->whereNotNull('submitted_at')
            ->get()
            ->avg(function ($submission) {
                return $submission->getReviewTurnaroundTime();
            });

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'avg_turnaround_hours' => $avgTurnaroundTime ? round($avgTurnaroundTime, 2) : null,
        ];
    }

    /**
     * Filter submissions.
     *
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function filter(array $filters)
    {
        return $this->scopeQuery(function ($query) use ($filters) {
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['extension_id'])) {
                $query->forExtension($filters['extension_id']);
            }

            if (isset($filters['submitter_id'])) {
                $query->bySubmitter($filters['submitter_id']);
            }

            if (isset($filters['reviewer_id'])) {
                $query->byReviewer($filters['reviewer_id']);
            }

            if (isset($filters['has_security_issues']) && $filters['has_security_issues']) {
                $query->whereNotNull('security_scan_results');
            }

            if (isset($filters['submitted_from'])) {
                $query->where('submitted_at', '>=', $filters['submitted_from']);
            }

            if (isset($filters['submitted_to'])) {
                $query->where('submitted_at', '<=', $filters['submitted_to']);
            }

            return $query->with(['extension', 'version', 'submitter', 'reviewer'])
                ->orderBy('submitted_at', 'desc');
        })->all();
    }

    /**
     * Get submissions by IDs.
     *
     * @param  array  $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByIds(array $ids)
    {
        return $this->scopeQuery(function ($query) use ($ids) {
            return $query->whereIn('id', $ids)
                ->with(['extension', 'version', 'submitter', 'reviewer']);
        })->all();
    }
}
