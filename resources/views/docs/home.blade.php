@extends('docs.layouts.portal')

@section('title', 'Documentation Home')

@section('content')
    <div class="docs-content">
        <!-- Hero Section -->
        <div class="docs-hero" style="margin-bottom: 3rem;">
            <div style="background: linear-gradient(135deg, hsl(var(--primary-light)) 0%, hsl(var(--bg-color)) 100%); border-radius: 1.5rem; padding: 3rem 2rem; text-align: center; border: 1px solid var(--border-color);">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border-radius: 2rem; font-size: 0.875rem; font-weight: 600; color: hsl(var(--primary)); margin-bottom: 1.5rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    <span>Comprehensive Documentation</span>
                </div>

                <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; color: hsl(var(--primary-dark));">
                    Welcome to the Documentation
                </h1>

                <p style="font-size: 1.125rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto 2rem; line-height: 1.6;">
                    Everything you need to get started with our CRM. Explore guides, API reference, tutorials, and troubleshooting resources.
                </p>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="#getting-started" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: hsl(var(--primary)); color: white; border-radius: 0.5rem; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                        </svg>
                        Get Started
                    </a>
                    <a href="#categories" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: white; color: hsl(var(--primary)); border: 2px solid hsl(var(--primary)); border-radius: 0.5rem; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                        Browse Topics
                    </a>
                </div>
            </div>
        </div>

        <!-- Getting Started Section -->
        @if($gettingStartedArticles->count() > 0)
        <section id="getting-started" style="margin-bottom: 4rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                <div style="width: 48px; height: 48px; background: hsl(var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: hsl(var(--primary));">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                </div>
                <div>
                    <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: hsl(var(--primary-dark));">Getting Started</h2>
                    <p style="color: var(--text-secondary); margin: 0;">Quick start guides to help you get up and running in less than 30 minutes</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                @foreach($gettingStartedArticles as $article)
                <div class="card" style="margin-bottom: 0; transition: all 0.3s ease; cursor: pointer;">
                    <a href="{{ route('docs.show', $article->id) }}" style="text-decoration: none; color: inherit;">
                        @if($article->icon)
                        <div style="width: 48px; height: 48px; background: hsl(var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: hsl(var(--primary)); margin-bottom: 1rem; font-size: 1.5rem;">
                            {{ $article->icon }}
                        </div>
                        @else
                        <div style="width: 48px; height: 48px; background: hsl(var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: hsl(var(--primary)); margin-bottom: 1rem;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        @endif

                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: hsl(var(--primary-dark));">
                            {{ $article->title }}
                        </h3>

                        @if($article->excerpt)
                        <p style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1rem;">
                            {{ \Illuminate\Support\Str::limit(strip_tags($article->excerpt), 100) }}
                        </p>
                        @endif

                        <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.75rem; color: var(--text-secondary);">
                            @if($article->reading_time_minutes)
                            <span style="display: flex; align-items: center; gap: 0.25rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                {{ $article->reading_time_minutes }} min read
                            </span>
                            @endif
                            @if($article->hasVideo())
                            <span style="display: flex; align-items: center; gap: 0.25rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                                Video
                            </span>
                            @endif
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Categories Section -->
        @if($categories->count() > 0)
        <section id="categories" style="margin-bottom: 4rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                <div style="width: 48px; height: 48px; background: hsl(var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: hsl(var(--primary));">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
                <div>
                    <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: hsl(var(--primary-dark));">Browse by Category</h2>
                    <p style="color: var(--text-secondary); margin: 0;">Explore documentation organized by topic</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                @foreach($categories as $category)
                <div class="card" style="margin-bottom: 0; transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden;">
                    <a href="#" style="text-decoration: none; color: inherit;">
                        @if($category->icon)
                        <div style="width: 48px; height: 48px; background: hsl(var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: hsl(var(--primary)); margin-bottom: 1rem; font-size: 1.5rem;">
                            {{ $category->icon }}
                        </div>
                        @else
                        <div style="width: 48px; height: 48px; background: hsl(var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: hsl(var(--primary)); margin-bottom: 1rem;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        @endif

                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: hsl(var(--primary-dark));">
                            {{ $category->name }}
                        </h3>

                        @if($category->description)
                        <p style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1rem;">
                            {{ \Illuminate\Support\Str::limit($category->description, 100) }}
                        </p>
                        @endif

                        @if($category->children && $category->children->count() > 0)
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                            <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.5rem; font-weight: 600;">
                                {{ $category->children->count() }} {{ $category->children->count() === 1 ? 'Subcategory' : 'Subcategories' }}
                            </p>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                @foreach($category->children->take(3) as $child)
                                <span style="display: inline-block; padding: 0.25rem 0.5rem; background: hsl(var(--primary-light)); color: hsl(var(--primary)); border-radius: 0.25rem; font-size: 0.75rem;">
                                    {{ $child->name }}
                                </span>
                                @endforeach
                                @if($category->children->count() > 3)
                                <span style="display: inline-block; padding: 0.25rem 0.5rem; background: var(--bg-color); color: var(--text-secondary); border-radius: 0.25rem; font-size: 0.75rem;">
                                    +{{ $category->children->count() - 3 }} more
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div style="position: absolute; top: 1rem; right: 1rem; color: hsl(var(--primary)); opacity: 0; transition: opacity 0.2s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </div>
                    </a>

                    <style>
                        .card:hover .card[style*="position: absolute"] {
                            opacity: 1;
                        }
                    </style>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Search Results (if searching) -->
        @if(request('query'))
        <section style="margin-bottom: 4rem;">
            <div style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; color: hsl(var(--primary-dark));">
                    Search Results for "{{ request('query') }}"
                </h2>
                <p style="color: var(--text-secondary);">{{ $articles->total() }} {{ $articles->total() === 1 ? 'result' : 'results' }} found</p>
            </div>

            @forelse($articles as $article)
            <div class="card">
                <h3 class="text-lg font-bold mb-2">
                    <a href="{{ route('docs.show', $article->id) }}">{{ $article->title }}</a>
                </h3>
                <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                    {{ \Illuminate\Support\Str::limit(strip_tags($article->content), 200) }}
                </p>
                <div class="text-sm" style="color: var(--text-secondary);">
                    <span>{{ $article->view_count ?? 0 }} views</span> •
                    <span>Updated {{ $article->updated_at?->diffForHumans() ?? 'Recently' }}</span>
                </div>
            </div>
            @empty
            <div class="card text-center">
                <div style="margin-bottom: 1rem;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--text-secondary);">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <p style="color: var(--text-secondary); font-size: 1rem; margin-bottom: 0.5rem;">No results found for "{{ request('query') }}"</p>
                <p class="text-sm" style="color: var(--text-secondary);">Try different keywords or browse the categories above.</p>
            </div>
            @endforelse

            @if($articles->hasPages())
            <div style="margin-top: 2rem;">
                {{ $articles->appends(request()->query())->links() }}
            </div>
            @endif
        </section>
        @endif

        <!-- Popular Articles (if not searching) -->
        @if(!request('query') && $popularArticles->count() > 0)
        <section>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                <div style="width: 48px; height: 48px; background: hsl(var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: hsl(var(--primary));">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: hsl(var(--primary-dark));">Popular Articles</h2>
                    <p style="color: var(--text-secondary); margin: 0;">Most viewed documentation articles</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($popularArticles as $article)
                <div class="card" style="margin-bottom: 0; display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; background: hsl(var(--primary-light)); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: hsl(var(--primary)); flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div style="flex: 1;">
                        <h3 class="text-lg font-semibold mb-2">
                            <a href="{{ route('docs.show', $article->id) }}" style="color: hsl(var(--primary-dark)); text-decoration: none;">
                                {{ $article->title }}
                            </a>
                        </h3>
                        <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">
                            {{ \Illuminate\Support\Str::limit(strip_tags($article->excerpt ?? $article->content), 120) }}
                        </p>
                        <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-secondary);">
                            <span>{{ $article->view_count ?? 0 }} views</span>
                            @if($article->reading_time_minutes)
                            <span> • {{ $article->reading_time_minutes }} min read</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
@endsection
