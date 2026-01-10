@extends('portal::layouts.master')

@section('title', 'My Tickets')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 style="font-size: 1.5rem; font-weight: 700;">My Tickets</h1>
        <a href="{{ route('portal.tickets.create') }}" class="btn btn-primary">Create Ticket</a>
    </div>

    @if(session('success'))
        <div class="alert" style="background-color: #DEF7EC; color: #03543F; border: 1px solid #BCF0DA;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #F9FAFB; border-bottom: 1px solid var(--border-color);">
                <tr>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Ticket ID</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Subject</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Status</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Last Update</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td class="px-4 py-4">#{{ $ticket->id }}</td>
                        <td class="px-4 py-4 text-sm font-bold">{{ $ticket->title ?? $ticket->subject }}</td>
                        <td class="px-4 py-4">
                            <span style="padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;
                                        @if($ticket->status == 'open') background-color: #E0F2FE; color: #0369A1;
                                        @elseif($ticket->status == 'solved') background-color: #DEF7EC; color: #03543F;
                                        @elseif($ticket->status == 'closed') background-color: #F3F4F6; color: #374151;
                                        @else background-color: #FEF3C7; color: #92400E; @endif">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm" style="color: var(--text-secondary);">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('portal.tickets.show', $ticket->id) }}" class="btn btn-link text-sm">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-sm" style="color: var(--text-secondary);">No tickets
                            found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
@endsection