<?php

namespace Webkul\Support\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;
use App\Models\SupportTicket;

class SupportTicketRepository extends Repository
{
    /**
     * Searchable fields
     */
    protected $fieldSearchable = [
        'ticket_number',
        'subject',
        'description',
        'status',
        'priority',
        'customer.name',
        'assignedTo.name',
        'category.name',
    ];

    /**
     * Specify model class name
     */
    public function model()
    {
        return SupportTicket::class;
    }

    /**
     * Create ticket
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            // Set created_by if not provided
            if (empty($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }

            // Create the ticket
            $ticket = parent::create($data);

            // Add initial message if provided
            if (!empty($data['initial_message'])) {
                $ticket->messages()->create([
                    'user_id' => auth()->id(),
                    'message' => $data['initial_message'],
                    'is_internal' => false,
                    'is_from_customer' => false,
                ]);
            }

            // Add tags if provided
            if (!empty($data['tags'])) {
                $ticket->tags()->sync($data['tags']);
            }

            // Add watchers if provided
            if (!empty($data['watchers'])) {
                $ticket->watchers()->sync($data['watchers']);
            }

            DB::commit();

            return $ticket->fresh(['category', 'assignedTo', 'customer', 'messages']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update ticket
     */
    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $ticket = $this->findOrFail($id);

            // Update basic fields
            $ticket = parent::update($data, $id);

            // Update tags if provided
            if (isset($data['tags'])) {
                $ticket->tags()->sync($data['tags']);
            }

            // Update watchers if provided
            if (isset($data['watchers'])) {
                $ticket->watchers()->sync($data['watchers']);
            }

            // Check if status changed to resolved/closed
            if (isset($data['status'])) {
                if ($data['status'] === 'resolved' && !$ticket->resolved_at) {
                    $ticket->update(['resolved_at' => now()]);
                }

                if ($data['status'] === 'closed' && !$ticket->closed_at) {
                    $ticket->update(['closed_at' => now()]);
                }
            }

            DB::commit();

            return $ticket->fresh(['category', 'assignedTo', 'customer', 'messages']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add message to ticket
     */
    public function addMessage($ticketId, array $data)
    {
        $ticket = $this->findOrFail($ticketId);

        $message = $ticket->messages()->create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'message' => $data['message'],
            'is_internal' => $data['is_internal'] ?? false,
            'is_from_customer' => $data['is_from_customer'] ?? false,
        ]);

        // Update first response time if this is the first response
        if (!$ticket->first_response_at && !($data['is_from_customer'] ?? false)) {
            $ticket->update(['first_response_at' => now()]);
        }

        return $message;
    }

    /**
     * Assign ticket to user
     */
    public function assignTo($ticketId, $userId)
    {
        return $this->update(['assigned_to' => $userId], $ticketId);
    }

    /**
     * Change ticket status
     */
    public function changeStatus($ticketId, string $status)
    {
        return $this->update(['status' => $status], $ticketId);
    }

    /**
     * Get tickets by status
     */
    public function getByStatus(string $status)
    {
        return $this->findWhere(['status' => $status]);
    }

    /**
     * Get assigned tickets
     */
    public function getAssignedTickets($userId)
    {
        return $this->findWhere(['assigned_to' => $userId]);
    }

    /**
     * Get unassigned tickets
     */
    public function getUnassignedTickets()
    {
        return $this->findWhere(['assigned_to' => null]);
    }

    /**
     * Get overdue tickets
     */
    public function getOverdueTickets()
    {
        return $this->model
            ->where('resolution_due_at', '<', now())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->get();
    }

    /**
     * Get breached tickets
     */
    public function getBreachedTickets()
    {
        return $this->findWhere(['sla_breached' => true]);
    }

    /**
     * Get ticket statistics
     */
    public function getStatistics()
    {
        return [
            'total' => $this->model->count(),
            'open' => $this->model->where('status', 'open')->count(),
            'in_progress' => $this->model->where('status', 'in_progress')->count(),
            'resolved' => $this->model->where('status', 'resolved')->count(),
            'closed' => $this->model->where('status', 'closed')->count(),
            'overdue' => $this->model->where('resolution_due_at', '<', now())
                ->whereNotIn('status', ['resolved', 'closed'])->count(),
            'breached' => $this->model->where('sla_breached', true)->count(),
        ];
    }
}
