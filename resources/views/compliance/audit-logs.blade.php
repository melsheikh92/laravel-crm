<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        Audit Logs
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header Section -->
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="compliance.audit-logs" />

                <div class="text-xl font-bold dark:text-white">
                    Audit Logs
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Back to Dashboard Button -->
                <a
                    href="{{ route('compliance.dashboard') }}"
                    class="secondary-button"
                >
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Summary Statistics -->
        @if(isset($summary))
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Logs</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">
                    {{ number_format($summary['total_records'] ?? 0) }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Event Types</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">
                    {{ count($summary['by_event'] ?? []) }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Model Types</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">
                    {{ count($summary['by_model'] ?? []) }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Unique Users</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">
                    {{ count($summary['by_user'] ?? []) }}
                </p>
            </div>
        </div>
        @endif

        <!-- Filters -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Filter Audit Logs
            </h2>
            <form method="GET" action="{{ route('compliance.audit-logs') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4">
                <!-- Start Date -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        Start Date
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        value="{{ $filters['start_date'] ?? '' }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <!-- End Date -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        End Date
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        value="{{ $filters['end_date'] ?? '' }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <!-- Event Type -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        Event Type
                    </label>
                    <input
                        type="text"
                        name="event"
                        value="{{ $filters['event'] ?? '' }}"
                        placeholder="e.g., created, updated"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <!-- Model Type -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        Model Type
                    </label>
                    <input
                        type="text"
                        name="model_type"
                        value="{{ $filters['model_type'] ?? '' }}"
                        placeholder="e.g., App\Models\User"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <!-- User ID -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        User ID
                    </label>
                    <input
                        type="number"
                        name="user_id"
                        value="{{ $filters['user_id'] ?? '' }}"
                        placeholder="Enter user ID"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <!-- IP Address -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        IP Address
                    </label>
                    <input
                        type="text"
                        name="ip_address"
                        value="{{ $filters['ip_address'] ?? '' }}"
                        placeholder="e.g., 192.168.1.1"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <!-- Tags -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        Tags
                    </label>
                    <input
                        type="text"
                        name="tags"
                        value="{{ $filters['tags'] ?? '' }}"
                        placeholder="Enter tag"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <!-- Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="primary-button flex-1">
                        Apply Filters
                    </button>
                    <a href="{{ route('compliance.audit-logs') }}" class="secondary-button">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Audit Logs Table -->
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                Timestamp
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                Event
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                Model
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                User
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                IP Address
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                Tags
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($auditLogs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-800 dark:text-white">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ $log->event }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                                <div class="max-w-xs truncate" title="{{ $log->auditable_type }}">
                                    {{ class_basename($log->auditable_type) }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    ID: {{ $log->auditable_id }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                                @if($log->user)
                                    <div>{{ $log->user->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        ID: {{ $log->user_id }}
                                    </div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">System</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-800 dark:text-white">
                                {{ $log->ip_address ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($log->tags && is_array($log->tags) && count($log->tags) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($log->tags as $tag)
                                            <span class="inline-flex rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No audit logs found matching your criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($auditLogs->hasPages())
            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $auditLogs->links() }}
            </div>
            @endif
        </div>

        <!-- Export Options -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Export Filtered Results
            </h2>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Export the current filtered audit logs for compliance reporting.
            </p>
            <div class="flex gap-2">
                <form action="{{ route('compliance.export-audit-report') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="format" value="csv">
                    @foreach($filters as $key => $value)
                        @if($value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="secondary-button">
                        Export CSV
                    </button>
                </form>
                <form action="{{ route('compliance.export-audit-report') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="format" value="json">
                    @foreach($filters as $key => $value)
                        @if($value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="secondary-button">
                        Export JSON
                    </button>
                </form>
                <form action="{{ route('compliance.export-audit-report') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="format" value="pdf">
                    @foreach($filters as $key => $value)
                        @if($value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="secondary-button">
                        Export PDF
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin::layouts>
