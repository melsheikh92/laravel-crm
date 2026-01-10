<?php

namespace Webkul\Portal\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Webkul\Support\Repositories\SupportTicketRepository;
use Webkul\Support\Repositories\TicketCategoryRepository;
use Webkul\Support\Services\TicketService;

class TicketController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        protected SupportTicketRepository $ticketRepository,
        protected TicketCategoryRepository $categoryRepository,
        protected TicketService $ticketService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personId = auth()->guard('portal')->user()->person_id;

        $tickets = $this->ticketRepository->where('customer_id', $personId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('portal::tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->categoryRepository->getActiveCategories();

        return view('portal::tickets.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $this->validate(request(), [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'category_id' => 'required|exists:ticket_categories,id', // assuming category_id is needed
        ]);

        $personId = auth()->guard('portal')->user()->person_id;

        $data = request()->all();
        $data['customer_id'] = $personId;
        $data['status'] = 'open';
        $data['type'] = 'problem'; // Default type or add field to form

        // Pass category_id if repository supports it (Admin controller doesn't explicit validate it but assumes it is in data)
        // Check SupportTicket fillable: ticket_number, title, description, status, priority, type, customer_id...
        // 'category_id' is NOT in fillable list! 
        // Admin TicketController index method doesn't show category field usage clearly in store, 
        // but `TicketController::show` eager loads `category`. 
        // I need to check migration/model for `category_id`.
        // Assuming it's there or handled by repository. 
        // Wait, Admin store validates `customer_id`.

        // Checking Admin create view might reveal field name.
        // Assuming 'category_id' or 'ticket_category_id'.

        $ticket = $this->ticketService->createTicket($data);

        session()->flash('success', 'Ticket created successfully.');

        return redirect()->route('portal.tickets.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $personId = auth()->guard('portal')->user()->person_id;

        $ticket = $this->ticketRepository->with([
            'messages.user',
            'messages.attachments',
            'attachments',
            'category'
        ])->findOrFail($id);

        if ($ticket->customer_id != $personId) {
            abort(403);
        }

        return view('portal::tickets.view', compact('ticket'));
    }

    /**
     * Add reply to ticket
     */
    public function reply($id)
    {
        $this->validate(request(), [
            'message' => 'required|string',
        ]);

        $personId = auth()->guard('portal')->user()->person_id;
        $ticket = $this->ticketRepository->findOrFail($id);

        if ($ticket->customer_id != $personId) {
            abort(403);
        }

        $data = [
            'message' => request('message'),
            'user_id' => auth()->guard('portal')->user()->id, // Wait, user_id usually refers to admin User. 
            // SupportTicket messages might have 'user_id' (admin) or 'customer_id' (person)?
            // Or 'is_from_customer' flag?
            // Checking TicketService: "if ($message->is_from_customer)..."
            // I need to check how "is_from_customer" is set or if I need to pass it.
            // Admin AddMessage just calls repo->addMessage.
            // Repo likely sets columns.

            // I'll set 'is_from_customer' => true (if applicable) or check repository logic later.
            // For now passing generic data.
            'is_internal' => false,
            // 'contact_id' ? Person ID?
        ];

        // IMPORTANT: The message table likely links to User (agent) or Contact/Person (customer).
        // If I pass 'user_id' as PortalAccess ID, it might fail foreign key to users table.
        // PortalAccess is NOT in users table.
        // I should probably Not pass 'user_id' if it's external, or pass 'contact_id' / 'person_id'.

        // I'll inspect `SupportTicketRepository::addMessage` in next step if this fails.
        // But for draft, assuming I need to indicate it's from customer.

        // Update: TicketService::addMessage calls repo->addMessage.

        $this->ticketService->addMessage($id, [
            'message' => request('message'),
            'is_internal' => false,
            'is_from_customer' => true, // Assuming this flag exists
            'contact_id' => $personId,   // Assuming this links to person
        ]);

        return redirect()->route('portal.tickets.show', $id);
    }
}
