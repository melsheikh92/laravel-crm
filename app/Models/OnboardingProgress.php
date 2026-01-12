<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'onboarding_progress';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'current_step',
        'completed_steps',
        'skipped_steps',
        'is_completed',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'completed_steps' => 'array',
        'skipped_steps' => 'array',
        'is_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($progress) {
            // Set started_at timestamp if not set
            if (empty($progress->started_at)) {
                $progress->started_at = now();
            }

            // Initialize arrays if not set
            if (empty($progress->completed_steps)) {
                $progress->completed_steps = [];
            }

            if (empty($progress->skipped_steps)) {
                $progress->skipped_steps = [];
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the user that owns this onboarding progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */

    /**
     * Scope to get completed onboarding records.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope to get incomplete onboarding records.
     */
    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recently started onboarding.
     */
    public function scopeRecentlyStarted($query, int $limit = 100)
    {
        return $query->orderBy('started_at', 'desc')->limit($limit);
    }

    /**
     * Scope to get recently completed onboarding.
     */
    public function scopeRecentlyCompleted($query, int $limit = 100)
    {
        return $query->where('is_completed', true)
            ->orderBy('completed_at', 'desc')
            ->limit($limit);
    }

    /**
     * Scope to filter by date range when onboarding was started.
     */
    public function scopeStartedBetween($query, $startDate, $endDate = null)
    {
        $query->where('started_at', '>=', $startDate);

        if ($endDate) {
            $query->where('started_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to filter by date range when onboarding was completed.
     */
    public function scopeCompletedBetween($query, $startDate, $endDate = null)
    {
        $query->where('completed_at', '>=', $startDate);

        if ($endDate) {
            $query->where('completed_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to get onboarding by current step.
     */
    public function scopeByCurrentStep($query, string $step)
    {
        return $query->where('current_step', $step);
    }

    /**
     * Query methods
     */

    /**
     * Get onboarding progress for a specific user.
     */
    public static function getForUser($userId)
    {
        return static::where('user_id', $userId)->first();
    }

    /**
     * Get or create onboarding progress for a user.
     */
    public static function getOrCreateForUser($userId)
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'started_at' => now(),
                'completed_steps' => [],
                'skipped_steps' => [],
                'is_completed' => false,
            ]
        );
    }

    /**
     * Get completion statistics.
     */
    public static function getCompletionStats()
    {
        return [
            'total' => static::count(),
            'completed' => static::where('is_completed', true)->count(),
            'incomplete' => static::where('is_completed', false)->count(),
            'completion_rate' => static::count() > 0
                ? round((static::where('is_completed', true)->count() / static::count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get average completion time in hours.
     */
    public static function getAverageCompletionTime()
    {
        $completed = static::where('is_completed', true)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get();

        if ($completed->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        foreach ($completed as $progress) {
            $totalMinutes += $progress->started_at->diffInMinutes($progress->completed_at);
        }

        return round($totalMinutes / $completed->count() / 60, 2);
    }

    /**
     * Step management methods
     */

    /**
     * Mark a step as completed.
     */
    public function completeStep(string $step): bool
    {
        $completedSteps = $this->completed_steps ?? [];
        $skippedSteps = $this->skipped_steps ?? [];

        // Add to completed if not already there
        if (!in_array($step, $completedSteps)) {
            $completedSteps[] = $step;
        }

        // Remove from skipped if it was skipped before
        if (($key = array_search($step, $skippedSteps)) !== false) {
            unset($skippedSteps[$key]);
            $skippedSteps = array_values($skippedSteps);
        }

        $this->completed_steps = $completedSteps;
        $this->skipped_steps = $skippedSteps;

        return $this->save();
    }

    /**
     * Mark a step as skipped.
     */
    public function skipStep(string $step): bool
    {
        $skippedSteps = $this->skipped_steps ?? [];
        $completedSteps = $this->completed_steps ?? [];

        // Add to skipped if not already there
        if (!in_array($step, $skippedSteps)) {
            $skippedSteps[] = $step;
        }

        // Remove from completed if it was completed before
        if (($key = array_search($step, $completedSteps)) !== false) {
            unset($completedSteps[$key]);
            $completedSteps = array_values($completedSteps);
        }

        $this->skipped_steps = $skippedSteps;
        $this->completed_steps = $completedSteps;

        return $this->save();
    }

    /**
     * Set the current step.
     */
    public function setCurrentStep(string $step): bool
    {
        $this->current_step = $step;
        return $this->save();
    }

    /**
     * Mark the entire onboarding as complete.
     */
    public function markAsComplete(): bool
    {
        $this->is_completed = true;
        $this->completed_at = now();
        return $this->save();
    }

    /**
     * Reset the onboarding progress.
     */
    public function reset(): bool
    {
        $this->current_step = null;
        $this->completed_steps = [];
        $this->skipped_steps = [];
        $this->is_completed = false;
        $this->started_at = now();
        $this->completed_at = null;

        return $this->save();
    }

    /**
     * Progress calculation accessors
     */

    /**
     * Check if a specific step is completed.
     */
    public function isStepCompleted(string $step): bool
    {
        return in_array($step, $this->completed_steps ?? []);
    }

    /**
     * Check if a specific step is skipped.
     */
    public function isStepSkipped(string $step): bool
    {
        return in_array($step, $this->skipped_steps ?? []);
    }

    /**
     * Get the number of completed steps.
     */
    public function getCompletedStepsCount(): int
    {
        return count($this->completed_steps ?? []);
    }

    /**
     * Get the number of skipped steps.
     */
    public function getSkippedStepsCount(): int
    {
        return count($this->skipped_steps ?? []);
    }

    /**
     * Get the total number of processed steps (completed + skipped).
     */
    public function getProcessedStepsCount(): int
    {
        return $this->getCompletedStepsCount() + $this->getSkippedStepsCount();
    }

    /**
     * Calculate progress percentage based on total steps.
     */
    public function getProgressPercentage(int $totalSteps): float
    {
        if ($totalSteps === 0) {
            return 0;
        }

        return round(($this->getProcessedStepsCount() / $totalSteps) * 100, 2);
    }

    /**
     * Get the duration of onboarding in minutes.
     */
    public function getDurationInMinutes(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? now();
        return $this->started_at->diffInMinutes($endTime);
    }

    /**
     * Get the duration of onboarding in hours.
     */
    public function getDurationInHours(): ?float
    {
        $minutes = $this->getDurationInMinutes();
        return $minutes ? round($minutes / 60, 2) : null;
    }

    /**
     * Check if onboarding is in progress.
     */
    public function isInProgress(): bool
    {
        return !$this->is_completed && $this->started_at !== null;
    }

    /**
     * Get a human-readable description of the onboarding progress.
     */
    public function getDescription(): string
    {
        $userName = $this->user ? $this->user->name : 'Unknown User';
        $status = $this->is_completed ? 'completed' : 'in progress';

        if ($this->is_completed) {
            return "{$userName} completed onboarding";
        }

        $completedCount = $this->getCompletedStepsCount();
        $skippedCount = $this->getSkippedStepsCount();

        return "{$userName} onboarding {$status}: {$completedCount} completed, {$skippedCount} skipped";
    }

    /**
     * Get the remaining steps (not completed or skipped).
     */
    public function getRemainingSteps(array $allSteps): array
    {
        $processedSteps = array_merge(
            $this->completed_steps ?? [],
            $this->skipped_steps ?? []
        );

        return array_values(array_diff($allSteps, $processedSteps));
    }

    /**
     * Check if all required steps are completed (excluding skipped ones).
     */
    public function hasCompletedAllRequiredSteps(array $requiredSteps): bool
    {
        $completedSteps = $this->completed_steps ?? [];

        foreach ($requiredSteps as $step) {
            if (!in_array($step, $completedSteps)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get next step to complete based on available steps.
     */
    public function getNextStep(array $orderedSteps): ?string
    {
        $processedSteps = array_merge(
            $this->completed_steps ?? [],
            $this->skipped_steps ?? []
        );

        foreach ($orderedSteps as $step) {
            if (!in_array($step, $processedSteps)) {
                return $step;
            }
        }

        return null;
    }
}
