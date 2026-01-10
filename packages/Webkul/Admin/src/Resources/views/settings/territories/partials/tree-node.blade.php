@php
    $hasChildren = $territory->children && $territory->children->isNotEmpty();
    $indent = $level * 2.5; // 2.5rem per level
@endphp

<div class="territory-node" style="margin-left: {{ $indent }}rem;">
    <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
        <div class="flex flex-1 items-center gap-4">
            <!-- Expand/Collapse Icon -->
            @if ($hasChildren)
                <button
                    type="button"
                    class="toggle-children flex h-6 w-6 items-center justify-center rounded transition-transform hover:bg-gray-200 dark:hover:bg-gray-600"
                    onclick="toggleChildren(this)"
                >
                    <svg class="h-4 w-4 text-gray-600 dark:text-gray-300 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            @else
                <div class="h-6 w-6 flex items-center justify-center">
                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            @endif

            <!-- Territory Icon -->
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if ($territory->type === 'geographic')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    @endif
                </svg>
            </div>

            <!-- Territory Information -->
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $territory->name }}
                    </h3>
                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ $territory->code }}
                    </span>
                </div>
                <div class="mt-1 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <!-- Type Badge -->
                    <span class="inline-flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        {{ ucfirst(str_replace('-', ' ', $territory->type)) }}
                    </span>

                    <!-- Owner -->
                    @if ($territory->owner)
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ $territory->owner->name }}
                        </span>
                    @endif

                    <!-- Children Count -->
                    @if ($hasChildren)
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            {{ $territory->children->count() }} {{ $territory->children->count() === 1 ? 'sub-territory' : 'sub-territories' }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Status Badge -->
            <div>
                @if ($territory->status === 'active')
                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                        <svg class="mr-1 h-2 w-2 fill-current" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        @lang('admin::app.settings.territories.hierarchy.active')
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                        <svg class="mr-1 h-2 w-2 fill-current" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        @lang('admin::app.settings.territories.hierarchy.inactive')
                    </span>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="ml-4 flex items-center gap-2">
            @if (bouncer()->hasPermission('settings.territories.view'))
                <a
                    href="{{ route('admin.settings.territories.edit', $territory->id) }}"
                    class="flex h-8 w-8 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600"
                    title="@lang('admin::app.settings.territories.hierarchy.view-btn')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
            @endif

            @if (bouncer()->hasPermission('settings.territories.edit'))
                <a
                    href="{{ route('admin.settings.territories.edit', $territory->id) }}"
                    class="flex h-8 w-8 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600"
                    title="@lang('admin::app.settings.territories.hierarchy.edit-btn')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </a>
            @endif
        </div>
    </div>

    <!-- Children (collapsed by default) -->
    @if ($hasChildren)
        <div class="children mt-2 space-y-2" style="display: none;">
            @foreach ($territory->children as $child)
                @include('admin::settings.territories.partials.tree-node', ['territory' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
            function toggleChildren(button) {
                const node = button.closest('.territory-node');
                const children = node.querySelector(':scope > .children');
                const icon = button.querySelector('svg');

                if (children) {
                    if (children.style.display === 'none') {
                        children.style.display = 'block';
                        icon.style.transform = 'rotate(180deg)';
                    } else {
                        children.style.display = 'none';
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            }

            // Expand all territories on page load (optional)
            document.addEventListener('DOMContentLoaded', function() {
                // Optionally auto-expand first level
                // document.querySelectorAll('.territory-node > .toggle-children').forEach(button => {
                //     if (button.closest('.territory-node').style.marginLeft === '0rem') {
                //         toggleChildren(button);
                //     }
                // });
            });
        </script>
    @endpush
@endonce
