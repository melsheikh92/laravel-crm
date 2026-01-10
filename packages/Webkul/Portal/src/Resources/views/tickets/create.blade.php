@extends('portal::layouts.master')

@section('title', 'Create Ticket')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 style="font-size: 1.5rem; font-weight: 700;">Create Ticket</h1>
        <a href="{{ route('portal.tickets.index') }}" class="btn btn-link">Back to Tickets</a>
    </div>

    <div class="card w-full" style="max-width: 800px; margin: 0 auto;">
        <form method="POST" action="{{ route('portal.tickets.store') }}">
            @csrf

            <div class="form-group">
                <label for="subject" class="form-label">Subject</label>
                <input id="subject" type="text" class="form-control @error('subject') is-invalid @enderror" name="subject"
                    value="{{ old('subject') }}" required autofocus placeholder="Brief summary of the issue">
                @error('subject')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="flex gap-4">
                <div class="form-group w-full">
                    <label for="category_id" class="form-label">Category</label>
                    <select id="category_id" class="form-control @error('category_id') is-invalid @enderror"
                        name="category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group w-full">
                    <label for="priority" class="form-label">Priority</label>
                    <select id="priority" class="form-control @error('priority') is-invalid @enderror" name="priority"
                        required>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                    name="description" rows="6" required
                    placeholder="Detailed explanation of the problem">{{ old('description') }}</textarea>
                @error('description')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="text-right" style="text-align: right;">
                <button type="submit" class="btn btn-primary">Submit Ticket</button>
            </div>
        </form>
    </div>
@endsection