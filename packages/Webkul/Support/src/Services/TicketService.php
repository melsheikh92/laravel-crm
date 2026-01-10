<?php

namespace Webkul\Support\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Webkul\Support\Repositories\SupportTicketRepository;
use Webkul\Support\Repositories\SlaPolicyRepository;
use App\Models\BusinessHours;

class TicketService
{
    public function __construct(
        protected SupportTicketRepository $ticketRepository,
        protected SlaPolicyRepository $slaPolicyRepository,
        protected SlaService $slaService
    ) {
    }

    /**
     * Create new ticket
     */
    public function createTicket(array $data)
    {
        // Create the ticket
        $ticket = $this->ticketRepository->create($data);

        // Apply SLA policy
        $this->applySlaPolicy($ticket);

        // Send notifications
        $this->sendTicketCreatedNotifications($ticket);

        return $ticket;
    }

    /**
     * Update ticket
     */
    public function updateTicket($id, array $data)
    {
        $ticket = $this->ticketRepository->findOrFail($id);
        $oldStatus = $ticket->status;
        $oldAssignee = $ticket->assigned_to;

        // Update the ticket
        $ticket = $this->ticketRepository->update($data, $id);

        // Send notifications if status or assignee changed
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $this->sendStatusChangedNotifications($ticket, $oldStatus);
        }

        if (isset($data['assigned_to']) && $data['assigned_to'] !== $oldAssignee) {
            $this->sendAssignmentNotifications($ticket);
        }

        return $ticket;
    }

    /**
     * Add message to ticket
     */
    public function addMessage($ticketId, array $data)
    {
        $message = $this->ticketRepository->addMessage($ticketId, $data);
        $ticket = $this->ticketRepository->findOrFail($ticketId);

        // Send notifications
        $this->sendNewMessageNotifications($ticket, $message);

        return $message;
    }

    /**
     * Assign ticket to user
     */
    public function assignTicket($ticketId, $userId)
    {
        $ticket = $this->ticketRepository->assignTo($ticketId, $userId);

        // Send assignment notification
        $this->sendAssignmentNotifications($ticket);

        return $ticket;
    }

    /**
     * Change ticket status
     */
    public function changeStatus($ticketId, string $status)
    {
        $ticket = $this->ticketRepository->findOrFail($ticketId);
        $oldStatus = $ticket->status;

        $ticket = $this->ticketRepository->changeStatus($ticketId, $status);

        // Send status change notification
        $this->sendStatusChangedNotifications($ticket, $oldStatus);

        return $ticket;
    }

    /**
     * Close ticket
     */
    public function closeTicket($ticketId, $reason = null)
    {
        return $this->changeStatus($ticketId, 'closed');
    }

    /**
     * Resolve ticket
     */
    public function resolveTicket($ticketId, $resolution = null)
    {
        $ticket = $this->changeStatus($ticketId, 'resolved');

        if ($resolution) {
            $this->addMessage($ticketId, [
                'message' => $resolution,
                'is_internal' => false,
            ]);
        }

        return $ticket;
    }

    /**
     * Apply SLA policy to ticket
     */
    protected function applySlaPolicy($ticket)
    {
        // Get applicable SLA policy
        $policy = $this->slaPolicyRepository->getPolicyForTicket($ticket);

        if (!$policy) {
            return;
        }

        // Calculate SLA deadlines
        $deadlines = $this->slaService->calculateDeadlines($ticket, $policy);

        // Update ticket with SLA information
        $this->ticketRepository->update([
            'sla_policy_id' => $policy->id,
            'first_response_due_at' => $deadlines['first_response_due_at'],
            'resolution_due_at' => $deadlines['resolution_due_at'],
        ], $ticket->id);
    }

    /**
     * Check for SLA breaches
     */
    public function checkSlaBreaches()
    {
        $tickets = $this->ticketRepository->model
            ->whereNotNull('sla_policy_id')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->get();

        foreach ($tickets as $ticket) {
            $this->slaService->checkForBreach($ticket);
        }
    }

    /**
     * Send ticket created notifications
     */
    protected function sendTicketCreatedNotifications($ticket)
    {
        // Notify customer
        if ($ticket->customer) {
            // Send email to customer
        }

        // Notify assigned user
        if ($ticket->assignedTo) {
            // Send notification to assigned user
        }

        // Notify watchers
        foreach ($ticket->watchers as $watcher) {
            // Send notification to watcher
        }
    }

    /**
     * Send status changed notifications
     */
    protected function sendStatusChangedNotifications($ticket, $oldStatus)
    {
        // Notify customer
        if ($ticket->customer) {
            // Send email to customer
        }

        // Notify watchers
        foreach ($ticket->watchers as $watcher) {
            // Send notification to watcher
        }
    }

    /**
     * Send assignment notifications
     */
    protected function sendAssignmentNotifications($ticket)
    {
        if ($ticket->assignedTo) {
            // Send notification to newly assigned user
        }
    }

    /**
     * Send new message notifications
     */
    protected function sendNewMessageNotifications($ticket, $message)
    {
        // If message is from customer, notify assigned user and watchers
        if ($message->is_from_customer) {
            if ($ticket->assignedTo) {
                // Notify assigned user
            }

            foreach ($ticket->watchers as $watcher) {
                // Notify watcher
            }
        } else {
            // If message is from agent, notify customer
            if ($ticket->customer && !$message->is_internal) {
                // Notify customer
            }
        }
    }

    /**
     * Get ticket statistics
     */
    public function getStatistics($userId = null)
    {
        $stats = $this->ticketRepository->getStatistics();

        if ($userId) {
            $stats['assigned_to_me'] = $this->ticketRepository
                ->getAssignedTickets($userId)
                ->count();
        }

        return $stats;
    }

    /**
     * Auto-assign ticket
     */
    public function autoAssignTicket($ticketId)
    {
        $ticket = $this->ticketRepository->findOrFail($ticketId);

        // Simple round-robin assignment
        // In production, this would be more sophisticated
        $users = \App\Models\User::where('is_active', true)->get();

        if ($users->isEmpty()) {
            return $ticket;
        }

        // Get user with least tickets
        $userWithLeastTickets = $users->sortBy(function ($user) {
            return $user->assignedTickets()->count();
        })->first();

        return $this->assignTicket($ticketId, $userWithLeastTickets->id);
    }
}
