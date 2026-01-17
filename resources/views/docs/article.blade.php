@extends('portal::layouts.master')

@section('title', $article->title)

@section('content')
    <div class="mb-4">
        @if($article->category)
            <a href="{{ route('docs.index') }}" class="btn btn-link">&larr; Back to Documentation</a>
        @else
            <a href="{{ route('docs.index') }}" class="btn btn-link">&larr; Back to Documentation</a>
        @endif
    </div>

    <div class="card">
        @if($article->category)
            <div class="mb-3">
                <span class="badge badge-info">{{ $article->category->name }}</span>
                @if($article->difficulty_level)
                    <span class="badge badge-secondary">{{ ucfirst($article->difficulty_level) }}</span>
                @endif
            </div>
        @endif

        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $article->title }}</h1>

        <div class="flex gap-4 text-sm mb-6"
            style="color: var(--text-secondary); border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <span>Updated {{ $article->updated_at->format('M d, Y') }}</span>
            <span>•</span>
            <span>{{ $article->view_count }} views</span>
            @if($article->reading_time_minutes)
                <span>•</span>
                <span>{{ $article->reading_time_minutes }} min read</span>
            @endif
        </div>

        @if($article->hasVideo())
            <div class="mb-6" style="text-align: center;">
                <iframe
                    src="{{ $article->video_embed_url }}"
                    style="width: 100%; max-width: 800px; height: 450px; border: none; border-radius: 8px;"
                    allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                </iframe>
            </div>
        @endif

        @if($article->sections && $article->sections->count() > 0)
            <div class="doc-layout" style="display: flex; gap: 2rem;">
                <!-- Table of Contents Sidebar -->
                <div class="doc-sidebar" style="width: 250px; flex-shrink: 0;">
                    <div class="sticky-top" style="position: sticky; top: 1rem;">
                        <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-secondary);">
                            Contents
                        </h4>
                        <nav style="font-size: 0.875rem;">
                            <ul style="list-style: none; padding-left: 0; margin: 0;">
                                @foreach($article->sections->where('level', 1) as $section)
                                    <li style="margin-bottom: 0.5rem;">
                                        <a href="#{{ $section->anchor_id }}"
                                           style="color: var(--primary-color); text-decoration: none; display: block; padding: 0.25rem 0; transition: color 0.2s;"
                                           onmouseover="this.style.color='var(--primary-hover)'"
                                           onmouseout="this.style.color='var(--primary-color)'">
                                            {{ $section->title }}
                                        </a>
                                        @if($section->children && $section->children->count() > 0)
                                            <ul style="list-style: none; padding-left: 1rem; margin: 0.25rem 0 0 0;">
                                                @foreach($section->children as $child)
                                                    <li style="margin-bottom: 0.25rem;">
                                                        <a href="#{{ $child->anchor_id }}"
                                                           style="color: var(--text-secondary); text-decoration: none; display: block; padding: 0.25rem 0; transition: color 0.2s;"
                                                           onmouseover="this.style.color='var(--primary-color)'"
                                                           onmouseout="this.style.color='var(--text-secondary)'">
                                                            {{ $child->title }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="doc-content" style="flex: 1; min-width: 0;">
                    @if($article->excerpt)
                        <div class="article-excerpt mb-4" style="font-size: 1.125rem; line-height: 1.6; color: var(--text-secondary); padding: 1rem; background-color: var(--light-bg); border-left: 3px solid var(--primary-color); border-radius: 4px;">
                            {{ $article->excerpt }}
                        </div>
                    @endif

                    <div class="article-content" style="line-height: 1.7;">
                        @foreach($article->sections->sortBy('sort_order') as $section)
                            <div id="{{ $section->anchor_id }}" class="doc-section" style="margin-bottom: 2rem;">
                                @if($section->level == 1)
                                    <h2 style="font-size: 1.75rem; font-weight: 600; margin-bottom: 1rem; padding-top: 1rem;">
                                        {{ $section->title }}
                                    </h2>
                                @elseif($section->level == 2)
                                    <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 0.75rem; padding-top: 0.75rem;">
                                        {{ $section->title }}
                                    </h3>
                                @else
                                    <h4 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; padding-top: 0.5rem;">
                                        {{ $section->title }}
                                    </h4>
                                @endif

                                <div style="line-height: 1.7;">
                                    {!! $section->content !!}
                                </div>
                            </div>
                        @endforeach

                        @if(!empty($article->content))
                            <div class="article-extra-content" style="margin-top: 2rem;">
                                {!! $article->content !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- Article without sections -->
            @if($article->excerpt)
                <div class="article-excerpt mb-4" style="font-size: 1.125rem; line-height: 1.6; color: var(--text-secondary); padding: 1rem; background-color: var(--light-bg); border-left: 3px solid var(--primary-color); border-radius: 4px;">
                    {{ $article->excerpt }}
                </div>
            @endif

            <div class="article-content" style="line-height: 1.7;">
                {!! $article->content !!}
            </div>
        @endif

        @if($article->tags && $article->tags->count() > 0)
            <div class="mt-6 pt-4" style="border-top: 1px solid var(--border-color);">
                <div class="flex flex-wrap gap-2">
                    @foreach($article->tags as $tag)
                        <span class="badge badge-secondary">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-8 pt-4" style="border-top: 1px solid var(--border-color);">
            <p class="text-sm text-center" style="color: var(--text-secondary);">Was this article helpful?</p>
            @if(session('voted'))
                <div class="text-center mt-2">
                    <span style="color: var(--success-color); font-weight: 500;">Thanks for your feedback!</span>
                </div>
            @else
                <div class="text-center mt-2" style="display: flex; justify-content: center; gap: 1rem;">
                    <form action="{{ route('docs.vote', $article->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="vote" value="1">
                        <button type="submit" class="btn btn-link">👍 Yes</button>
                    </form>

                    <form action="{{ route('docs.vote', $article->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="vote" value="0">
                        <button type="submit" class="btn btn-link">👎 No</button>
                    </form>
                </div>
            @endif

            @if($article->helpful_count + $article->not_helpful_count > 0)
                <div class="text-center mt-2">
                    <span class="text-sm" style="color: var(--text-secondary);">
                        {{ $article->helpful_count }} of {{ $article->helpful_count + $article->not_helpful_count }} people found this helpful
                    </span>
                </div>
            @endif
        </div>

        @if($article->attachments && $article->attachments->count() > 0)
            <div class="mt-6 pt-4" style="border-top: 1px solid var(--border-color);">
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Attachments</h4>
                <ul style="list-style: none; padding-left: 0;">
                    @foreach($article->attachments as $attachment)
                        <li style="margin-bottom: 0.5rem;">
                            <a href="{{ $attachment->url }}" target="_blank" class="btn btn-link" style="padding: 0.5rem;">
                                📎 {{ $attachment->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <style>
        /* Smooth scrolling for anchor links */
        html {
            scroll-behavior: smooth;
        }

        /* Highlight active section in TOC */
        .doc-sidebar a.active {
            font-weight: 600;
            color: var(--primary-color) !important;
        }

        /* Responsive layout */
        @media (max-width: 768px) {
            .doc-layout {
                flex-direction: column;
            }

            .doc-sidebar {
                width: 100%;
                margin-bottom: 1rem;
            }

            .doc-sidebar .sticky-top {
                position: static;
            }
        }
    </style>

    <script>
        // Highlight active section in table of contents
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.doc-section[id]');
            const navLinks = document.querySelectorAll('.doc-sidebar a[href^="#"]');

            function highlightNavLink() {
                let scrollY = window.pageYOffset;

                sections.forEach(section => {
                    const sectionHeight = section.offsetHeight;
                    const sectionTop = section.offsetTop - 100;
                    const sectionId = section.getAttribute('id');

                    if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                        navLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === '#' + sectionId) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            }

            window.addEventListener('scroll', highlightNavLink);
            highlightNavLink();
        });
    </script>
@endsection
