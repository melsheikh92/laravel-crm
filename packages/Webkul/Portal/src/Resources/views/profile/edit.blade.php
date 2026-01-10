@extends('portal::layouts.master')

@section('title', 'My Profile')

@section('content')
    <div class="mb-4">
        <h1 style="font-size: 1.5rem; font-weight: 700;">My Profile</h1>
        <p style="color: var(--text-secondary);">Manage your contact information.</p>
    </div>

    @if(session('success'))
        <div class="alert" style="background-color: #DEF7EC; color: #03543F; border: 1px solid #BCF0DA;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card w-full" style="max-width: 600px;">
        <form method="POST" action="{{ route('portal.profile.update') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                    value="{{ old('name', $person->name) }}" required>
                @error('name')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                {{-- Display Portal Access email or Person email. Person emails is array. --}}
                <input id="email" type="email" class="form-control" value="{{ auth()->guard('portal')->user()->email }}"
                    disabled style="background-color: #F3F4F6; cursor: not-allowed;">
                <p class="text-sm mt-1" style="color: var(--text-secondary);">Contact support to change your email address.
                </p>
            </div>

            <div class="form-group">
                <label for="contact_numbers" class="form-label">Phone Number</label>
                @php
                    $phone = '';
                    $contactNumbers = $person->contact_numbers; // Assign to variable first to avoid indirect modification notice
                    if (!empty($contactNumbers) && is_array($contactNumbers)) {
                        $first = reset($contactNumbers); // Now safe
                        $phone = $first['value'] ?? '';
                    } elseif (!empty($contactNumbers) && is_string($contactNumbers)) {
                        // Handle case where it might be string (though validation/casting should prevent)
                        $decoded = json_decode($contactNumbers, true);
                        $phone = $decoded[0]['value'] ?? '';
                    }
                @endphp
                <input id="contact_numbers" type="text" class="form-control @error('contact_numbers') is-invalid @enderror"
                    name="contact_numbers" value="{{ old('contact_numbers', $phone) }}" placeholder="e.g. +1234567890">
                @error('contact_numbers')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
@endsection