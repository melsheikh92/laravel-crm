{{--
    Video Embed Component

    Embeds a video tutorial with responsive aspect ratio

    Usage:
    <x-onboarding.video-embed
        url="https://www.youtube.com/embed/VIDEO_ID"
        title="Setup Tutorial"
    />

    Or with thumbnail for lazy loading:
    <x-onboarding.video-embed
        url="https://www.youtube.com/embed/VIDEO_ID"
        title="Setup Tutorial"
        thumbnail="/path/to/thumbnail.jpg"
    />
--}}

@props(['url', 'title' => 'Video Tutorial', 'thumbnail' => null])

@if($thumbnail)
    {{-- Lazy loading with thumbnail --}}
    <div class="relative overflow-hidden rounded-lg bg-gray-900" x-data="{ loaded: false }">
        {{-- Thumbnail --}}
        <div x-show="!loaded" class="relative aspect-video cursor-pointer" @click="loaded = true">
            <img
                src="{{ $thumbnail }}"
                alt="{{ $title }}"
                class="h-full w-full object-cover"
            >
            <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors hover:bg-black/40">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-600 shadow-lg transition-transform hover:scale-110">
                    <svg class="ml-1 h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Video iframe --}}
        <div x-show="loaded" x-cloak class="aspect-video">
            <iframe
                class="h-full w-full"
                :src="'{{ $url }}?autoplay=1'"
                title="{{ $title }}"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
            ></iframe>
        </div>
    </div>
@else
    {{-- Direct embed without lazy loading --}}
    <div class="aspect-video overflow-hidden rounded-lg bg-gray-900">
        <iframe
            class="h-full w-full"
            src="{{ $url }}"
            title="{{ $title }}"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
        ></iframe>
    </div>
@endif
