@extends('portal::layouts.master')

@section('title', $article->title)

@section('content')
    <div class="mb-4">
        <a href="{{ route('portal.kb.index') }}" class="btn btn-link">&larr; Back to Knowledge Base</a>
    </div>

    <div class="card">
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $article->title }}</h1>

        <div class="flex gap-4 text-sm mb-6"
            style="color: var(--text-secondary); border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <span>Updated {{ $article->updated_at->format('M d, Y') }}</span>
            <span>•</span>
            <span>{{ $article->view_count }} views</span>
        </div>

        <div class="article-content" style="line-height: 1.7;">
            {!! $article->content !!}
        </div>

        <div class="mt-8 pt-4" style="border-top: 1px solid var(--border-color);">
            <p class="text-sm text-center" style="color: var(--text-secondary);">Was this article helpful?</p>
            @if(session('voted'))
                <div class="text-center mt-2">
                    <span style="color: var(--success-color); font-weight: 500;">Thanks for your feedback!</span>
                </div>
            @else
                <div class="text-center mt-2" style="display: flex; justify-content: center; gap: 1rem;">
                    <form action="{{ route('portal.kb.vote', $article->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="vote" value="1">
                        <button type="submit" class="btn btn-link">Yes</button>
                    </form>

                    <form action="{{ route('portal.kb.vote', $article->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="vote" value="0">
                        <button type="submit" class="btn btn-link">No</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection