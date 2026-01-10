@extends('portal::layouts.master')

@section('title', 'Quote Details #' . $quote->id)

@section('content')
    <div class="mb-4">
        <a href="{{ route('portal.quotes.index') }}" class="btn btn-link" style="padding-left: 0;">&larr; Back to Quotes</a>
    </div>

    <div class="card mb-4">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $quote->subject }}</h1>
                <p style="color: var(--text-secondary);">
                    Quote #{{ $quote->id }} &bull; Created on {{ $quote->created_at->format('M d, Y') }}
                </p>
            </div>
            <div>
                @php
                    $status = 'Pending';
                    $lead = $quote->leads->first();
                    if ($lead) {
                        $status = $lead->status;
                    }
                @endphp
                <span style="padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 500; font-size: 0.875rem;
                                @if (strtolower($status) == 'won') background-color: #DEF7EC; color: #03543F;
                                @elseif(strtolower($status) == 'lost') background-color: #FDE8E8; color: #9B1C1C;
                                @else background-color: #E0F2FE; color: #0369A1; @endif">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
            </div>
        </div>

        <div class="flex justify-between" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <div>
                <h3 class="font-bold mb-2">Details</h3>
                <p><strong>Expiration Date:</strong> {{ $quote->expired_at ? $quote->expired_at->format('M d, Y') : 'N/A' }}
                </p>
                <p><strong>Subtotal:</strong> {{ core()->formatBasePrice($quote->sub_total) }}</p>
                <p><strong>Tax:</strong> {{ core()->formatBasePrice($quote->tax_amount) }}</p>
                <p><strong>Grand Total:</strong> {{ core()->formatBasePrice($quote->grand_total) }}</p>
            </div>

            @if(!empty($quote->items))
                <div style="width: 50%;">
                    <h3 class="font-bold mb-2">Line Items</h3>
                    <table class="w-full">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid var(--border-color);">
                                <th class="py-2">Product</th>
                                <th class="py-2">Quantity</th>
                                <th class="py-2">Price</th>
                                <th class="py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quote->items as $item)
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td class="py-2">{{ $item->name }}</td>
                                    <td class="py-2">{{ $item->quantity }}</td>
                                    <td class="py-2">{{ core()->formatBasePrice($item->price) }}</td>
                                    <td class="py-2">{{ core()->formatBasePrice($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="mt-4" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <p><strong>Billing Address:</strong></p>
            @if($quote->billing_address)
                <p>{{ $quote->billing_address['address1'] ?? '' }}<br>
                    {{ $quote->billing_address['city'] ?? '' }} {{ $quote->billing_address['state'] ?? '' }}
                    {{ $quote->billing_address['postcode'] ?? '' }}<br>
                    {{ $quote->billing_address['country'] ?? '' }}</p>
            @else
                <p>N/A</p>
            @endif
        </div>
    </div>
@endsection