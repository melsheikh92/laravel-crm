<?php

namespace Webkul\Support\Services;

use Carbon\Carbon;
use App\Models\BusinessHours;
use App\Models\SlaBreach;
use Webkul\Support\Repositories\SlaPolicyRepository;

class SlaService
{
    public function __construct(
        protected SlaPolicyRepository $slaPolicyRepository
    ) {
    }

    /**
     * Calculate SLA deadlines for ticket
     */
    public function calculateDeadlines($ticket, $policy)
    {
        $rule = $policy->rules()
            ->where('priority', $ticket->priority)
            ->first();

        if (!$rule) {
            return [
                'first_response_due_at' => null,
                'resolution_due_at' => null,
            ];
        }

        $createdAt = $ticket->created_at ?? now();

        if ($policy->business_hours_only) {
            return [
                'first_response_due_at' => $this->addBusinessMinutes($createdAt, $rule->first_response_time),
                'resolution_due_at' => $this->addBusinessMinutes($createdAt, $rule->resolution_time),
            ];
        }

        return [
            'first_response_due_at' => $createdAt->copy()->addMinutes($rule->first_response_time),
            'resolution_due_at' => $createdAt->copy()->addMinutes($rule->resolution_time),
        ];
    }

    /**
     * Add business minutes to a datetime
     */
    protected function addBusinessMinutes(Carbon $start, int $minutes): Carbon
    {
        $businessHours = BusinessHours::active()->get();

        if ($businessHours->isEmpty()) {
            // If no business hours defined, treat as 24/7
            return $start->copy()->addMinutes($minutes);
        }

        $current = $start->copy();
        $remainingMinutes = $minutes;

        while ($remainingMinutes > 0) {
            $dayOfWeek = $current->dayOfWeek;
            $hours = $businessHours->where('day_of_week', $dayOfWeek)->first();

            if (!$hours) {
                // No business hours for this day, skip to next day
                $current->addDay()->setTime(0, 0);
                continue;
            }

            $startTime = Carbon::parse($hours->start_time);
            $endTime = Carbon::parse($hours->end_time);

            // Set current time to business hours start if before
            if ($current->format('H:i') < $startTime->format('H:i')) {
                $current->setTime($startTime->hour, $startTime->minute);
            }

            // If current time is after business hours, skip to next day
            if ($current->format('H:i') >= $endTime->format('H:i')) {
                $current->addDay()->setTime(0, 0);
                continue;
            }

            // Calculate minutes available in current business hours
            $currentEnd = $current->copy()->setTime($endTime->hour, $endTime->minute);
            $availableMinutes = $current->diffInMinutes($currentEnd);

            if ($availableMinutes >= $remainingMinutes) {
                // Can complete within current business hours
                $current->addMinutes($remainingMinutes);
                $remainingMinutes = 0;
            } else {
                // Use all available minutes and continue to next day
                $remainingMinutes -= $availableMinutes;
                $current->addDay()->setTime(0, 0);
            }
        }

        return $current;
    }

    /**
     * Check for SLA breach
     */
    public function checkForBreach($ticket)
    {
        if (!$ticket->sla_policy_id) {
            return;
        }

        $now = now();
        $breaches = [];

        // Check first response breach
        if (
            $ticket->first_response_due_at &&
            !$ticket->first_response_at &&
            $now->greaterThan($ticket->first_response_due_at)
        ) {

            $breaches[] = $this->recordBreach($ticket, 'first_response');
        }

        // Check resolution breach
        if (
            $ticket->resolution_due_at &&
            !in_array($ticket->status, ['resolved', 'closed']) &&
            $now->greaterThan($ticket->resolution_due_at)
        ) {

            $breaches[] = $this->recordBreach($ticket, 'resolution');
        }

        if (!empty($breaches)) {
            $ticket->update(['sla_breached' => true]);

            // Send breach notifications
            $this->sendBreachNotifications($ticket, $breaches);
        }

        return $breaches;
    }

    /**
     * Record SLA breach
     */
    protected function recordBreach($ticket, string $type)
    {
        $dueAt = $type === 'first_response'
            ? $ticket->first_response_due_at
            : $ticket->resolution_due_at;

        $breach = SlaBreach::firstOrCreate(
            [
                'ticket_id' => $ticket->id,
                'breach_type' => $type,
            ],
            [
                'sla_policy_id' => $ticket->sla_policy_id,
                'due_at' => $dueAt,
                'breached_at' => now(),
                'breach_duration' => now()->diffInMinutes($dueAt),
            ]
        );

        return $breach;
    }

    /**
     * Send breach notifications
     */
    protected function sendBreachNotifications($ticket, array $breaches)
    {
        // Notify assigned user
        if ($ticket->assignedTo) {
            // Send notification
        }

        // Notify managers/supervisors
        // Implementation depends on your user hierarchy
    }

    /**
     * Get SLA compliance metrics
     */
    public function getComplianceMetrics($startDate = null, $endDate = null)
    {
        $query = \App\Models\SupportTicket::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $breached = $query->where('sla_breached', true)->count();
        $compliant = $total - $breached;

        return [
            'total_tickets' => $total,
            'compliant_tickets' => $compliant,
            'breached_tickets' => $breached,
            'compliance_rate' => $total > 0 ? round(($compliant / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get average response time
     */
    public function getAverageResponseTime($startDate = null, $endDate = null)
    {
        $query = \App\Models\SupportTicket::whereNotNull('first_response_at');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $tickets = $query->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $totalMinutes = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->first_response_at);
        });

        return round($totalMinutes / $tickets->count(), 2);
    }

    /**
     * Get average resolution time
     */
    public function getAverageResolutionTime($startDate = null, $endDate = null)
    {
        $query = \App\Models\SupportTicket::whereNotNull('resolved_at');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $tickets = $query->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $totalMinutes = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->resolved_at);
        });

        return round($totalMinutes / $tickets->count(), 2);
    }
}
