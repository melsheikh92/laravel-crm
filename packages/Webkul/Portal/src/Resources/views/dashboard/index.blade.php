@extends('portal::layouts.master')

@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-col gap-4">
        <div class="card">
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">Welcome,
                {{ auth()->guard('portal')->user()->person->name }}!
            </h1>
            <p style="color: var(--text-secondary);">Here is an overview of your support requests and recent activity.</p>
        </div>

        <div class="flex gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
            <div class="card">
                <h2 class="font-bold text-sm"
                    style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 1rem;">
                    Open Tickets</h2>
                <div style="font-size: 2rem; font-weight: 700;">{{ $openTicketsCount }}</div>
                <a href="{{ route('portal.tickets.index') }}" class="btn btn-link mt-4" style="display: inline-block;">View
                    All Tickets &rarr;</a>
            </div>

            <div class="card">
                <h2 class="font-bold text-sm"
                    style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 1rem;">
                    Recent Articles</h2>

                @if($recentArticles->count() > 0)
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @foreach($recentArticles as $article)
                            <li class="mb-2">
                                <a href="{{ route('portal.kb.show', $article->id) }}" class="btn btn-link"
                                    style="padding: 0; font-weight: normal;">{{ $article->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p style="color: var(--text-secondary);">No recently viewed articles.</p>
                @endif

                <a href="{{ route('portal.kb.index') }}" class="btn btn-link mt-4" style="display: inline-block;">Browse
                    Knowledge Base &rarr;</a>
            </div>
        </div>
    </div>
@endsection