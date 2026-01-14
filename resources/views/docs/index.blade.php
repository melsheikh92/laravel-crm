@extends('docs.layouts.portal')

@section('title', 'Documentation')

@section('content')
    <div class="docs-content">
        <!-- Hero Section -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold mb-2" style="font-size: 2rem;">Welcome to the Documentation</h1>
            <p style="color: var(--text-secondary); font-size: 1.125rem;">
                Comprehensive guides, API reference, and resources to help you get started with our CRM.
            </p>
        </div>

        <!-- Quick Start Cards -->
        <div class="flex flex-col gap-4 mb-6">
            <div class="card">
                <h2 class="text-xl font-bold mb-2">Getting Started</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    New to our CRM? Start here to learn the basics and get up and running in less than 30 minutes.
                </p>
                <a href="#quick-start" class="btn btn-primary">Get Started →</a>
            </div>

            <div class="card">
                <h2 class="text-xl font-bold mb-2">Feature Guides</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    Explore detailed guides for Leads, Contacts, Products, Quotes, and other core features.
                </p>
                <a href="#features" class="btn btn-link">Explore Features →</a>
            </div>

            <div class="card">
                <h2 class="text-xl font-bold mb-2">API Reference</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    Integrate with our RESTful API. Complete documentation with examples for all endpoints.
                </p>
                <a href="#api-reference" class="btn btn-link">View API Docs →</a>
            </div>
        </div>

        <!-- Search Results (if searching) -->
        @if(request('query'))
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-4">Search Results for "{{ request('query') }}"</h2>

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
                        <p style="color: var(--text-secondary);">No results found for "{{ request('query') }}"</p>
                        <p class="text-sm mt-2" style="color: var(--text-secondary);">Try different keywords or browse the categories in the sidebar.</p>
                    </div>
                @endforelse

                @if($articles->hasPages())
                    <div class="mt-4">
                        {{ $articles->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Popular Articles -->
        @if(!request('query'))
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-4">Popular Articles</h2>
                <div class="flex flex-col gap-4">
                    <div class="card">
                        <h3 class="text-lg font-semibold mb-2">
                            <a href="#installation">Installation Guide</a>
                        </h3>
                        <p style="color: var(--text-secondary);">
                            Learn how to install and configure the CRM on your server.
                        </p>
                    </div>

                    <div class="card">
                        <h3 class="text-lg font-semibold mb-2">
                            <a href="#user-management">User Management</a>
                        </h3>
                        <p style="color: var(--text-secondary);">
                            Create and manage users, roles, and permissions.
                        </p>
                    </div>

                    <div class="card">
                        <h3 class="text-lg font-semibold mb-2">
                            <a href="#api-overview">API Overview</a>
                        </h3>
                        <p style="color: var(--text-secondary);">
                            Get started with our API for integrations and custom applications.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
