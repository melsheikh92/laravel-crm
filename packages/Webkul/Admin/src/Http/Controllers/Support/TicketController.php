<?php

namespace Webkul\Admin\Http\Controllers\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\DataGrids\Support\TicketDataGrid;
use Webkul\Support\Repositories\SupportTicketRepository;
use Webkul\Support\Repositories\TicketCategoryRepository;
use Webkul\Support\Services\TicketService;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\User\Repositories\UserRepository;
use Webkul\Tag\Repositories\TagRepository;

class TicketController extends Controller
{
    public function __construct(
        protected SupportTicketRepository $ticketRepository,
        protected TicketCategoryRepository $categoryRepository,
        protected TicketService $ticketService,
        protected PersonRepository $personRepository,
        protected UserRepository $userRepository,
        protected TagRepository $tagRepository
    ) {
    }

    /**
     * Display a listing of tickets
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(TicketDataGrid::class)->process();
        }

        $statistics = $this->ticketService->getStatistics(auth()->id());

        return view('admin::support.tickets.index', compact('statistics'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create(): View
    {
        $categories = $this->categoryRepository->getActiveCategories();
        $customers = $this->personRepository->all();
        $users = $this->userRepository->all();
        $tags = $this->tagRepository->all();

        return view('admin::support.tickets.create', compact('categories', 'users', 'tags', 'customers'));
    }

    /**
     * Store a newly created ticket
     */
    public function store(): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'customer_id' => 'required|exists:persons,id',
        ]);

        $data = request()->all();

        $ticket = $this->ticketService->createTicket($data);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.tickets.create-success'),
                'data' => $ticket,
            ]);
        }

        session()->flash('success', trans('admin::app.support.tickets.create-success'));

        return redirect()->route('admin.support.tickets.index');
    }

    /**
     * Display the specified ticket
     */
    public function show(int $id): View
    {
        $ticket = $this->ticketRepository->with([
            'category',
            'assignedTo',
            'customer',
            'contact',
            'messages.user',
            'messages.attachments',
            'attachments',
            'tags',
            'watchers',
            'slaPolicy',
        ])->findOrFail($id);

        $users = $this->userRepository->all();

        return view('admin::support.tickets.show', compact('ticket', 'users'));
    }

    /**
     * Show the form for editing the ticket
     */
    public function edit(int $id): View
    {
        $ticket = $this->ticketRepository->findOrFail($id);
        $categories = $this->categoryRepository->getActiveCategories();
        $users = $this->userRepository->all();
        $tags = $this->tagRepository->all();

        return view('admin::support.tickets.edit', compact('ticket', 'categories', 'users', 'tags'));
    }

    /**
     * Update the specified ticket
     */
    public function update(int $id): RedirectResponse|JsonResponse
    {
        $this->validate(request(), [
            'subject' => 'sometimes|required|string|max:255',
            'priority' => 'sometimes|required|in:low,normal,high,urgent',
            'status' => 'sometimes|required|in:open,in_progress,waiting_customer,waiting_internal,resolved,closed',
        ]);

        $data = request()->all();

        $ticket = $this->ticketService->updateTicket($id, $data);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.support.tickets.update-success'),
                'data' => $ticket,
            ]);
        }

        session()->flash('success', trans('admin::app.support.tickets.update-success'));

        return redirect()->route('admin.support.tickets.show', $id);
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->ticketRepository->delete($id);

            return response()->json([
                'message' => trans('admin::app.support.tickets.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('admin::app.support.tickets.delete-failed'),
            ], 400);
        }
    }

    /**
     * Add message to ticket
     */
    public function addMessage(int $id): JsonResponse
    {
        $this->validate(request(), [
            'message' => 'required|string',
        ]);

        $data = request()->all();

        $message = $this->ticketService->addMessage($id, $data);

        return response()->json([
            'message' => trans('admin::app.support.tickets.message-added'),
            'data' => $message->load('user', 'attachments'),
        ]);
    }

    /**
     * Assign ticket to user
     */
    public function assign(int $id): JsonResponse
    {
        $this->validate(request(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket = $this->ticketService->assignTicket($id, request('assigned_to'));

        return response()->json([
            'message' => trans('admin::app.support.tickets.assigned-success'),
            'data' => $ticket,
        ]);
    }

    /**
     * Change ticket status
     */
    public function changeStatus(int $id): JsonResponse
    {
        $this->validate(request(), [
            'status' => 'required|in:open,in_progress,waiting_customer,waiting_internal,resolved,closed',
        ]);

        $ticket = $this->ticketService->changeStatus($id, request('status'));

        return response()->json([
            'message' => trans('admin::app.support.tickets.status-changed'),
            'data' => $ticket,
        ]);
    }

    /**
     * Close ticket
     */
    public function close(int $id): JsonResponse
    {
        $ticket = $this->ticketService->closeTicket($id, request('reason'));

        return response()->json([
            'message' => trans('admin::app.support.tickets.closed-success'),
            'data' => $ticket,
        ]);
    }

    /**
     * Resolve ticket
     */
    public function resolve(int $id): JsonResponse
    {
        $ticket = $this->ticketService->resolveTicket($id, request('resolution'));

        return response()->json([
            'message' => trans('admin::app.support.tickets.resolved-success'),
            'data' => $ticket,
        ]);
    }

    /**
     * Upload attachment
     */
    public function uploadAttachment(int $id): JsonResponse
    {
        $this->validate(request(), [
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = request()->file('file');
        $path = $file->store('ticket-attachments', 'public');

        $ticket = $this->ticketRepository->findOrFail($id);

        $attachment = $ticket->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => trans('admin::app.support.tickets.attachment-uploaded'),
            'data' => $attachment,
        ]);
    }

    /**
     * Get ticket statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->ticketService->getStatistics(auth()->id());

        return response()->json($stats);
    }

    /**
     * Mass update tickets
     */
    public function massUpdate(): JsonResponse
    {
        $this->validate(request(), [
            'indices' => 'required|array',
            'field' => 'required|in:status,priority,assigned_to',
            'value' => 'required',
        ]);

        $indices = request('indices');
        $field = request('field');
        $value = request('value');

        try {
            foreach ($indices as $id) {
                $this->ticketService->updateTicket($id, [$field => $value]);
            }

            return response()->json([
                'message' => trans('admin::app.support.tickets.mass-update-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('admin::app.support.tickets.mass-update-failed'),
            ], 400);
        }
    }

    /**
     * Mass delete tickets
     */
    public function massDestroy(): JsonResponse
    {
        $this->validate(request(), [
            'indices' => 'required|array',
        ]);

        try {
            foreach (request('indices') as $id) {
                $this->ticketRepository->delete($id);
            }

            return response()->json([
                'message' => trans('admin::app.support.tickets.mass-delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('admin::app.support.tickets.mass-delete-failed'),
            ], 400);
        }
    }
}
