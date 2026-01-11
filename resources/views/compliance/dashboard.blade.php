<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        Compliance Dashboard
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header Section -->
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="compliance.dashboard" />

                <div class="text-xl font-bold dark:text-white">
                    Compliance Dashboard
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Export Audit Report Button -->
                <a
                    href="{{ route('compliance.audit-logs') }}"
                    class="secondary-button"
                >
                    View Audit Logs
                </a>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('compliance.dashboard') }}" class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        Start Date
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        value="{{ request('start_date', $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate) }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <div class="flex-1">
                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white">
                        End Date
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        value="{{ request('end_date', $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate) }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                <button type="submit" class="primary-button">
                    Apply Filter
                </button>
            </form>
        </div>

        <!-- Compliance Status Overview -->
        @if(isset($metrics['compliance_status']))
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Compliance Status
            </h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Overall Status -->
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                Overall Status
                            </h3>
                            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">
                                {{ ucfirst($metrics['compliance_status']['status']) }}
                            </p>
                        </div>
                        <div class="text-3xl">
                            @if($metrics['compliance_status']['status'] === 'compliant')
                                <span class="text-green-500">✓</span>
                            @elseif($metrics['compliance_status']['status'] === 'warning')
                                <span class="text-yellow-500">⚠</span>
                            @else
                                <span class="text-red-500">✗</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Issues -->
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                Issues
                            </h3>
                            <p class="mt-1 text-2xl font-bold text-red-600">
                                {{ count($metrics['compliance_status']['issues']) }}
                            </p>
                        </div>
                        <div class="text-3xl">
                            <span class="text-red-500">✗</span>
                        </div>
                    </div>
                </div>

                <!-- Warnings -->
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                Warnings
                            </h3>
                            <p class="mt-1 text-2xl font-bold text-yellow-600">
                                {{ count($metrics['compliance_status']['warnings']) }}
                            </p>
                        </div>
                        <div class="text-3xl">
                            <span class="text-yellow-500">⚠</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issues & Warnings List -->
            @if(count($metrics['compliance_status']['issues']) > 0 || count($metrics['compliance_status']['warnings']) > 0)
            <div class="mt-4">
                @foreach($metrics['compliance_status']['issues'] as $issue)
                    <div class="mb-2 rounded bg-red-50 p-2 dark:bg-red-900/20">
                        <p class="text-xs font-medium text-red-800 dark:text-red-400">
                            ✗ {{ $issue }}
                        </p>
                    </div>
                @endforeach
                @foreach($metrics['compliance_status']['warnings'] as $warning)
                    <div class="mb-2 rounded bg-yellow-50 p-2 dark:bg-yellow-900/20">
                        <p class="text-xs font-medium text-yellow-800 dark:text-yellow-400">
                            ⚠ {{ $warning }}
                        </p>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Audit Logs -->
            @if(isset($metrics['audit_logging']))
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Audit Logs
                    </h3>
                    <span class="text-2xl">📝</span>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">
                    {{ number_format($metrics['audit_logging']['total_logs'] ?? 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Total entries in period
                </p>
            </div>
            @endif

            <!-- Consents -->
            @if(isset($metrics['consent_management']))
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Active Consents
                    </h3>
                    <span class="text-2xl">✅</span>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">
                    {{ number_format($metrics['consent_management']['active_consents'] ?? 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ number_format($metrics['consent_management']['consent_rate'] ?? 0, 1) }}% consent rate
                </p>
            </div>
            @endif

            <!-- Data Retention -->
            @if(isset($metrics['data_retention']))
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Records to Review
                    </h3>
                    <span class="text-2xl">🗄️</span>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">
                    {{ number_format($metrics['data_retention']['expired_records'] ?? 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ number_format($metrics['data_retention']['deletable_records'] ?? 0) }} ready for deletion
                </p>
            </div>
            @endif

            <!-- Encryption -->
            @if(isset($metrics['encryption']))
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Encrypted Models
                    </h3>
                    <span class="text-2xl">🔒</span>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">
                    {{ $metrics['encryption']['total_encrypted_models'] ?? 0 }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $metrics['encryption']['config']['algorithm'] ?? 'N/A' }}
                </p>
            </div>
            @endif
        </div>

        <!-- Audit Log Activity -->
        @if(isset($metrics['audit_logging']['by_event']))
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Audit Activity by Event Type
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="pb-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Event Type</th>
                            <th class="pb-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metrics['audit_logging']['by_event'] as $event => $count)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-2 text-sm text-gray-800 dark:text-white">
                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ $event }}
                                </span>
                            </td>
                            <td class="py-2 text-right text-sm font-medium text-gray-800 dark:text-white">
                                {{ number_format($count) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Consent Breakdown -->
        @if(isset($metrics['consent_management']['by_type']))
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Consent Breakdown by Type
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="pb-2 text-left text-sm font-medium text-gray-600 dark:text-gray-400">Consent Type</th>
                            <th class="pb-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Active</th>
                            <th class="pb-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">Withdrawn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metrics['consent_management']['by_type'] as $type => $stats)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-2 text-sm text-gray-800 dark:text-white">
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </td>
                            <td class="py-2 text-right text-sm text-green-600 dark:text-green-400">
                                {{ number_format($stats['active'] ?? 0) }}
                            </td>
                            <td class="py-2 text-right text-sm text-red-600 dark:text-red-400">
                                {{ number_format($stats['withdrawn'] ?? 0) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Export Options -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Export Audit Reports
            </h2>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Generate compliance reports in various formats for audit and review purposes.
            </p>
            <div class="flex gap-2">
                <form action="{{ route('compliance.export-audit-report') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="format" value="csv">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <button type="submit" class="secondary-button">
                        Export CSV
                    </button>
                </form>
                <form action="{{ route('compliance.export-audit-report') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="format" value="json">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <button type="submit" class="secondary-button">
                        Export JSON
                    </button>
                </form>
                <form action="{{ route('compliance.export-audit-report') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="format" value="pdf">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <button type="submit" class="secondary-button">
                        Export PDF
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin::layouts>
