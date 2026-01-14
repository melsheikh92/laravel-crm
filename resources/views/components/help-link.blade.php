@props([
    'slug' => null,
    'id' => null,
    'label' => 'Help',
    'class' => null,
])

@if($slug || $id)
    <a
        href="{{ $slug ? route('docs.show', $slug) : route('docs.show', $id) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition-colors hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 {{ $class }}"
        title="{{ $label }}"
    >
        <span class="icon-help-circle text-lg"></span>

        @if($label !== 'Help')
            <span>{{ $label }}</span>
        @endif
    </a>
@endif
