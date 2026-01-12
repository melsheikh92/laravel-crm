{{--
    Info Panel Component

    Displays an informational panel with icon and optional title

    Usage:
    <x-onboarding.info-panel>
        This is some helpful information
    </x-onboarding.info-panel>

    With custom type and title:
    <x-onboarding.info-panel type="warning" title="Important Note">
        Warning message here
    </x-onboarding.info-panel>

    Types: info (default), success, warning, error, tip
--}}

@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'info' => [
            'container' => 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
            'icon' => 'text-blue-600 dark:text-blue-400',
            'title' => 'text-blue-900 dark:text-blue-300',
            'text' => 'text-blue-800 dark:text-blue-400',
        ],
        'success' => [
            'container' => 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
            'icon' => 'text-green-600 dark:text-green-400',
            'title' => 'text-green-900 dark:text-green-300',
            'text' => 'text-green-800 dark:text-green-400',
        ],
        'warning' => [
            'container' => 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800',
            'icon' => 'text-yellow-600 dark:text-yellow-400',
            'title' => 'text-yellow-900 dark:text-yellow-300',
            'text' => 'text-yellow-800 dark:text-yellow-400',
        ],
        'error' => [
            'container' => 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
            'icon' => 'text-red-600 dark:text-red-400',
            'title' => 'text-red-900 dark:text-red-300',
            'text' => 'text-red-800 dark:text-red-400',
        ],
        'tip' => [
            'container' => 'bg-purple-50 border-purple-200 dark:bg-purple-900/20 dark:border-purple-800',
            'icon' => 'text-purple-600 dark:text-purple-400',
            'title' => 'text-purple-900 dark:text-purple-300',
            'text' => 'text-purple-800 dark:text-purple-400',
        ],
    ];

    $currentStyle = $styles[$type] ?? $styles['info'];

    $icons = [
        'info' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>',
        'success' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
        'warning' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
        'error' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
        'tip' => '<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
    ];

    $currentIcon = $icons[$type] ?? $icons['info'];
    $iconStrokeOrFill = $type === 'tip' ? 'stroke="currentColor" fill="none"' : 'fill="currentColor"';
@endphp

<div class="rounded-lg border p-4 {{ $currentStyle['container'] }}" role="alert">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 {{ $currentStyle['icon'] }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" {!! $iconStrokeOrFill !!}>
                {!! $currentIcon !!}
            </svg>
        </div>
        <div class="ml-3 flex-1">
            @if($title)
                <h3 class="text-sm font-semibold {{ $currentStyle['title'] }}">
                    {{ $title }}
                </h3>
                <div class="mt-2 text-sm {{ $currentStyle['text'] }}">
                    {{ $slot }}
                </div>
            @else
                <div class="text-sm {{ $currentStyle['text'] }}">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
