{{--
    Tooltip Component

    Displays a help icon with a tooltip on hover

    Usage:
    <x-onboarding.tooltip>
        This is helpful information that appears in a tooltip
    </x-onboarding.tooltip>

    Or with custom position:
    <x-onboarding.tooltip position="top">
        Tooltip content here
    </x-onboarding.tooltip>
--}}

@props(['position' => 'top'])

<div class="group relative inline-block" x-data="{ show: false }">
    <button
        type="button"
        @mouseenter="show = true"
        @mouseleave="show = false"
        @focus="show = true"
        @blur="show = false"
        class="inline-flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 dark:focus:text-gray-300"
        aria-label="Help information"
    >
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
        </svg>
    </button>

    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute z-50 w-64 rounded-lg bg-gray-900 px-3 py-2 text-sm text-white shadow-lg dark:bg-gray-700
            @if($position === 'top')
                bottom-full left-1/2 mb-2 -translate-x-1/2
            @elseif($position === 'bottom')
                top-full left-1/2 mt-2 -translate-x-1/2
            @elseif($position === 'left')
                right-full top-1/2 mr-2 -translate-y-1/2
            @elseif($position === 'right')
                left-full top-1/2 ml-2 -translate-y-1/2
            @endif"
        style="display: none;"
        role="tooltip"
    >
        {{ $slot }}

        {{-- Arrow --}}
        <div class="absolute h-2 w-2 rotate-45 bg-gray-900 dark:bg-gray-700
            @if($position === 'top')
                -bottom-1 left-1/2 -translate-x-1/2
            @elseif($position === 'bottom')
                -top-1 left-1/2 -translate-x-1/2
            @elseif($position === 'left')
                -right-1 top-1/2 -translate-y-1/2
            @elseif($position === 'right')
                -left-1 top-1/2 -translate-y-1/2
            @endif">
        </div>
    </div>
</div>
