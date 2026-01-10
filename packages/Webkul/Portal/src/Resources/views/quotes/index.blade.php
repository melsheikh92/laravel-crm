@extends('portal::layouts.master')

@section('title', 'My Quotes')

@section('content')
    <div class="mb-4">
        <h1 style="font-size: 1.5rem; font-weight: 700;">My Quotes</h1>
        <p style="color: var(--text-secondary);">View your quotes and deal status.</p>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #F9FAFB; border-bottom: 1px solid var(--border-color);">
                <tr>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Subject</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Total</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Created On</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Expires On</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);">Status</th>
                    <th class="px-4 py-4 text-sm font-medium" style="color: var(--text-secondary);"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotes as $quote)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td class="px-4 py-4 font-bold">{{ $quote->subject }}</td>
                        <td class="px-4 py-4">{{ core()->formatBasePrice($quote->grand_total) }}</td>
                        <td class="px-4 py-4 text-sm">{{ $quote->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-4 text-sm">
                            @if($quote->expired_at)
                                {{ $quote->expired_at->format('M d, Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $status = 'Pending';
                                $lead = $quote->leads->first();
                                if ($lead) {
                                    $status = $lead->status; // won, lost
                                }
                            @endphp
                            <span style="padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;
                                        @if(strtolower($status) == 'won') background-color: #DEF7EC; color: #03543F;
                                        @elseif(strtolower($status) == 'lost') background-color: #FDE8E8; color: #9B1C1C;
                                        @else background-color: #E0F2FE; color: #0369A1; @endif">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('portal.quotes.id', $quote->id) }}" class="btn btn-link text-sm">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm" style="color: var(--text-secondary);">No quotes
                            found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $quotes->links() }}
    </div>
@endsection