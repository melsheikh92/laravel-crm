@extends('portal::layouts.master')

@section('title', 'Knowledge Base')

@section('content')
    <div class="mb-4">
        <h1 style="font-size: 1.5rem; font-weight: 700;">Knowledge Base</h1>
        <p style="color: var(--text-secondary);">Find answers to common questions.</p>
    </div>

    <div class="card mb-4" style="background-color: var(--primary-color); color: white;">
        <form method="GET" action="{{ route('portal.kb.index') }}">
            <div class="form-group mb-0">
                <input type="text" name="query" class="form-control" placeholder="Search for articles..."
                    value="{{ request('query') }}" style="border: none; padding: 1rem; border-radius: 0.375rem;">
            </div>
        </form>
    </div>

    @if(request('query'))
        <div class="mb-4">
            <h2 class="text-lg font-bold">Search Results for "{{ request('query') }}"</h2>
        </div>
    @endif

    <div class="flex flex-col gap-4">
        @forelse($articles as $article)
            <div class="card">
                <h3 class="font-bold text-lg">
                    <a href="{{ route('portal.kb.show', $article->id) }}"
                        style="text-decoration: none; color: var(--primary-color);">
                        {{ $article->title }}
                    </a>
                </h3>
                <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                    {{ Str::limit(strip_tags($article->content), 150) }}
                </p>
                <div class="mt-2 text-sm" style="color: var(--text-secondary);">
                    <span>{{ $article->view_count }} views</span> •
                    <span>Updated {{ $article->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <div class="card text-center">
                <p style="color: var(--text-secondary);">No articles found.</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $articles->appends(request()->query())->links() }}
        </div>
    </div>
@endsection