<?php

namespace Webkul\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Marketplace\Contracts\ExtensionSubmission as ExtensionSubmissionContract;
use Webkul\User\Models\UserProxy;

class ExtensionSubmission extends Model implements ExtensionSubmissionContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'extension_id',
        'version_id',
        'submitted_by',
        'status',
        'reviewer_id',
        'review_notes',
        'security_scan_results',
        'submitted_at',
        'reviewed_at',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'security_scan_results' => 'array',
        'submitted_at'          => 'datetime',
        'reviewed_at'           => 'datetime',
    ];

    /**
     * Get the extension that was submitted.
     */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(ExtensionProxy::modelClass());
    }

    /**
     * Get the version that was submitted.
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(ExtensionVersionProxy::modelClass());
    }

    /**
     * Get the user who submitted the extension.
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'submitted_by');
    }

    /**
     * Get the reviewer who reviewed the submission.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'reviewer_id');
    }

    /**
     * Scope a query to only include pending submissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved submissions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected submissions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to filter by extension.
     */
    public function scopeForExtension($query, $extensionId)
    {
        return $query->where('extension_id', $extensionId);
    }

    /**
     * Scope a query to filter by version.
     */
    public function scopeForVersion($query, $versionId)
    {
        return $query->where('version_id', $versionId);
    }

    /**
     * Scope a query to filter by submitter.
     */
    public function scopeBySubmitter($query, $submitterId)
    {
        return $query->where('submitted_by', $submitterId);
    }

    /**
     * Scope a query to filter by reviewer.
     */
    public function scopeByReviewer($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    /**
     * Scope a query to get recent submissions.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('submitted_at', '>=', now()->subDays($days));
    }

    /**
     * Check if submission is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if submission is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if submission is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the submission.
     */
    public function approve(int $reviewerId, string $notes = null): void
    {
        $this->status = 'approved';
        $this->reviewer_id = $reviewerId;
        $this->review_notes = $notes;
        $this->reviewed_at = now();
        $this->save();

        // Update extension and version status to approved
        if ($this->extension) {
            $this->extension->update(['status' => 'approved']);
        }
        if ($this->version) {
            $this->version->update(['status' => 'approved']);
        }
    }

    /**
     * Reject the submission.
     */
    public function reject(int $reviewerId, string $notes): void
    {
        $this->status = 'rejected';
        $this->reviewer_id = $reviewerId;
        $this->review_notes = $notes;
        $this->reviewed_at = now();
        $this->save();

        // Update extension and version status to rejected
        if ($this->extension) {
            $this->extension->update(['status' => 'rejected']);
        }
        if ($this->version) {
            $this->version->update(['status' => 'rejected']);
        }
    }

    /**
     * Check if submission has security issues.
     */
    public function hasSecurityIssues(): bool
    {
        if (! $this->security_scan_results) {
            return false;
        }

        return isset($this->security_scan_results['issues'])
            && count($this->security_scan_results['issues']) > 0;
    }

    /**
     * Get the number of security issues found.
     */
    public function getSecurityIssuesCount(): int
    {
        if (! $this->security_scan_results || ! isset($this->security_scan_results['issues'])) {
            return 0;
        }

        return count($this->security_scan_results['issues']);
    }

    /**
     * Get critical security issues.
     */
    public function getCriticalSecurityIssues(): array
    {
        if (! $this->security_scan_results || ! isset($this->security_scan_results['issues'])) {
            return [];
        }

        return array_filter($this->security_scan_results['issues'], function ($issue) {
            return isset($issue['severity']) && $issue['severity'] === 'critical';
        });
    }

    /**
     * Check if submission has passed security scan.
     */
    public function hasPassedSecurityScan(): bool
    {
        if (! $this->security_scan_results) {
            return false;
        }

        return isset($this->security_scan_results['passed'])
            && $this->security_scan_results['passed'] === true;
    }

    /**
     * Get time since submission.
     */
    public function getTimeSinceSubmission(): string
    {
        if (! $this->submitted_at) {
            return 'Not submitted';
        }

        return $this->submitted_at->diffForHumans();
    }

    /**
     * Get time since review.
     */
    public function getTimeSinceReview(): ?string
    {
        if (! $this->reviewed_at) {
            return null;
        }

        return $this->reviewed_at->diffForHumans();
    }

    /**
     * Get review turnaround time in hours.
     */
    public function getReviewTurnaroundTime(): ?float
    {
        if (! $this->submitted_at || ! $this->reviewed_at) {
            return null;
        }

        return $this->submitted_at->diffInHours($this->reviewed_at);
    }

    /**
     * Create a new factory instance for the model.
     * Note: This model typically doesn't use factories as submissions are created through the application flow.
     * This method is provided for testing purposes only.
     */
    protected static function newFactory()
    {
        throw new \BadMethodCallException('ExtensionSubmission does not have a factory. Create submissions using ExtensionSubmission::create()');
    }
}
