@extends('portal::layouts.master')

@section('title', 'Ticket #' . $ticket->id)

@section('content')
    <div class="mb-4">
        <a href="{{ route('portal.tickets.index') }}" class="btn btn-link">&larr; Back to Tickets</a>
    </div>

    <div class="flex flex-col gap-4">
        <!-- Ticket Header -->
        <div class="card">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700;">{{ $ticket->title ?? $ticket->subject }}</h1>
                    <!-- Access subject or title based on model -->
                    <p style="color: var(--text-secondary);">Ticket #{{ $ticket->id }} • Created
                        {{ $ticket->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="text-right">
                    <span style="padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500;
                        @if($ticket->status == 'open') background-color: #E0F2FE; color: #0369A1;
                        @elseif($ticket->status == 'solved') background-color: #DEF7EC; color: #03543F;
                        @elseif($ticket->status == 'closed') background-color: #F3F4F6; color: #374151;
                        @else background-color: #FEF3C7; color: #92400E; @endif">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                </div>
            </div>

            <div class="flex gap-4 mb-4" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <div>
                    <span class="text-sm font-bold" style="color: var(--text-secondary);">Category:</span>
                    <span>{{ $ticket->category->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-sm font-bold" style="color: var(--text-secondary);">Priority:</span>
                    <span style="text-transform: capitalize;">{{ $ticket->priority }}</span>
                </div>
            </div>

            <div style="background-color: var(--bg-color); padding: 1rem; border-radius: 0.5rem;">
                <p style="white-space: pre-wrap;">{{ $ticket->description }}</p>
            </div>
        </div>

        <!-- Conversation -->
        <h2 class="font-bold text-lg mt-4">Conversation History</h2>

        @foreach($ticket->messages as $message)
            <div class="card" style="margin-left: {{ $message->is_from_customer ? '2rem' : '0' }}; margin-right: {{ $message->is_from_customer ? '0' : '2rem' }}; 
                     border-left: {{ $message->is_from_customer ? 'none' : '4px solid var(--primary-color)' }}; 
                     border-right: {{ $message->is_from_customer ? '4px solid var(--secondary-color)' : 'none' }};">
                <div class="flex justify-between mb-2">
                    <span class="font-bold">
                        @if($message->is_from_customer)
                            You
                        @else
                            {{ $message->user->name ?? 'Support Agent' }}
                        @endif
                    </span>
                    <span class="text-sm"
                        style="color: var(--text-secondary);">{{ $message->created_at->diffForHumans() }}</span>
                </div>
                <div style="white-space: pre-wrap;">{{ $message->message }}</div>
            </div>
        @endforeach

        <!-- Reply Form -->
        @if($ticket->status != 'closed')
            <div class="card mt-4">
                <h3 class="font-bold mb-4">Add Reply</h3>
                <form method="POST" action="{{ route('portal.tickets.reply', $ticket->id) }}">
                    @csrf
                    <div class="form-group">
                        <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="4" required
                            placeholder="Type your reply here..."></textarea>
                        @error('message')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Send Reply</button>
                    </div>
                </form>
            </div>
        @else
            <div class="alert text-center" style="background-color: #F3F4F6;">
                This ticket is closed and cannot be replied to.
            </div>
        @endif
    </div>
@endsection