<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.submissions.show.title')
    </x-slot>

    <div class="flex gap-4 max-lg:flex-wrap">
        <!-- Left Panel -->
        {!! view_render_event('admin.marketplace.submissions.show.left.before', ['submission' => $submission]) !!}

        <div class="max-lg:min-w-full max-lg:max-w-full [&>div:last-child]:border-b-0 lg:sticky lg:top-[73px] flex min-w-[394px] max-w-[394px] flex-col self-start rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <!-- Submission Information -->
            <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                <!-- Breadcrumbs -->
                <div class="flex items-center justify-between">
                    <x-admin::breadcrumbs
                        name="marketplace.submissions.show"
                        :entity="$submission"
                    />
                </div>

                {!! view_render_event('admin.marketplace.submissions.show.left.title.before', ['submission' => $submission]) !!}

                <!-- Title -->
                <h3 class="text-lg font-bold dark:text-white">
                    {{ $submission->extension->name ?? 'N/A' }}
                </h3>

                {!! view_render_event('admin.marketplace.submissions.show.left.title.after', ['submission' => $submission]) !!}

                <!-- Version -->
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @lang('marketplace::app.admin.submissions.show.version'): {{ $submission->version->version ?? 'N/A' }}
                </p>

                {!! view_render_event('admin.marketplace.submissions.show.left.actions.before', ['submission' => $submission]) !!}

                <!-- Action Buttons -->
                @if($submission->isPending() && bouncer()->hasPermission('marketplace.submissions.review'))
                    <div class="flex flex-wrap gap-2 pt-2">
                        <a
                            href="{{ route('admin.marketplace.submissions.review', $submission->id) }}"
                            class="primary-button"
                        >
                            @lang('marketplace::app.admin.submissions.show.review-btn')
                        </a>

                        @if(!$submission->security_scan_results)
                            <button
                                type="button"
                                class="secondary-button"
                                @click="runSecurityScan({{ $submission->id }})"
                            >
                                @lang('marketplace::app.admin.submissions.show.run-scan-btn')
                            </button>
                        @endif
                    </div>
                @endif

                <div class="pt-2">
                    <a
                        href="{{ route('admin.marketplace.submissions.index') }}"
                        class="secondary-button"
                    >
                        @lang('marketplace::app.admin.submissions.show.back-btn')
                    </a>
                </div>

                {!! view_render_event('admin.marketplace.submissions.show.left.actions.after', ['submission' => $submission]) !!}
            </div>

            <!-- General Information -->
            <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('marketplace::app.admin.submissions.show.general-info')
                </h4>

                <!-- Extension Type -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.submissions.show.type')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ ucfirst($submission->extension->type ?? 'N/A') }}
                    </span>
                </div>

                <!-- Submitter -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.submissions.show.submitter')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ $submission->submitter->name ?? 'N/A' }}
                    </span>
                </div>

                <!-- Submitted At -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.submissions.show.submitted-at')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y H:i') : 'N/A' }}
                    </span>
                </div>

                <!-- Status -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.submissions.show.status')
                    </span>
                    <span class="rounded px-2 py-1 text-xs font-medium
                        @if($submission->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                        @elseif($submission->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                        @endif
                    ">
                        {{ ucfirst($submission->status) }}
                    </span>
                </div>

                <!-- Reviewer -->
                @if($submission->reviewer)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.submissions.show.reviewer')
                        </span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                            {{ $submission->reviewer->name }}
                        </span>
                    </div>
                @endif

                <!-- Reviewed At -->
                @if($submission->reviewed_at)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.submissions.show.reviewed-at')
                        </span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                            {{ $submission->reviewed_at->format('M d, Y H:i') }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Version Compatibility -->
            @if($submission->version)
                <div class="flex w-full flex-col gap-2 p-4">
                    <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.submissions.show.compatibility')
                    </h4>

                    @if($submission->version->laravel_version_min || $submission->version->laravel_version_max)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.submissions.show.laravel-version')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $submission->version->laravel_version_min ?? '*' }} - {{ $submission->version->laravel_version_max ?? '*' }}
                            </span>
                        </div>
                    @endif

                    @if($submission->version->crm_version_min || $submission->version->crm_version_max)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.submissions.show.crm-version')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $submission->version->crm_version_min ?? '*' }} - {{ $submission->version->crm_version_max ?? '*' }}
                            </span>
                        </div>
                    @endif

                    @if($submission->version->php_version_min)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.submissions.show.php-version')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $submission->version->php_version_min }}+
                            </span>
                        </div>
                    @endif

                    @if($submission->version->file_path)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @lang('marketplace::app.admin.submissions.show.file-size')
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $submission->version->formatted_file_size ?? 'N/A' }}
                            </span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {!! view_render_event('admin.marketplace.submissions.show.left.after', ['submission' => $submission]) !!}

        {!! view_render_event('admin.marketplace.submissions.show.right.before', ['submission' => $submission]) !!}

        <!-- Right Panel -->
        <div class="flex w-full flex-col gap-4 rounded-lg">
            {!! view_render_event('admin.marketplace.submissions.show.right.extension.before', ['submission' => $submission]) !!}

            <!-- Extension Details -->
            @if($submission->extension)
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.submissions.show.extension-details')
                    </h3>
                    <div class="prose prose-sm max-w-none dark:prose-invert text-gray-600 dark:text-gray-400">
                        @if($submission->extension->description)
                            <p>{{ $submission->extension->description }}</p>
                        @endif

                        @if($submission->extension->long_description)
                            <div class="mt-4">
                                {!! nl2br(e($submission->extension->long_description)) !!}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {!! view_render_event('admin.marketplace.submissions.show.right.extension.after', ['submission' => $submission]) !!}

            {!! view_render_event('admin.marketplace.submissions.show.right.version.before', ['submission' => $submission]) !!}

            <!-- Version Details -->
            @if($submission->version && $submission->version->changelog)
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.submissions.show.changelog')
                    </h3>
                    <div class="prose prose-sm max-w-none dark:prose-invert text-gray-600 dark:text-gray-400">
                        {!! nl2br(e($submission->version->changelog)) !!}
                    </div>
                </div>
            @endif

            {!! view_render_event('admin.marketplace.submissions.show.right.version.after', ['submission' => $submission]) !!}

            {!! view_render_event('admin.marketplace.submissions.show.right.security.before', ['submission' => $submission]) !!}

            <!-- Security Scan Results -->
            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.submissions.show.security-scan')
                    </h3>

                    @if($submission->security_scan_results)
                        @if($submission->hasPassedSecurityScan())
                            <span class="rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                                @lang('marketplace::app.admin.submissions.show.security-passed')
                            </span>
                        @elseif($submission->hasSecurityIssues())
                            <span class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                                @lang('marketplace::app.admin.submissions.show.security-failed')
                            </span>
                        @endif
                    @else
                        <span class="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                            @lang('marketplace::app.admin.submissions.show.security-not-run')
                        </span>
                    @endif
                </div>

                @if($submission->security_scan_results)
                    @php
                        $issues = $submission->security_scan_results['issues'] ?? [];
                        $criticalIssues = array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'critical');
                        $highIssues = array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'high');
                        $mediumIssues = array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'medium');
                        $lowIssues = array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'low');
                    @endphp

                    @if($submission->hasSecurityIssues())
                        <!-- Issues Summary -->
                        <div class="space-y-2">
                            @if(count($criticalIssues) > 0)
                                <div class="flex items-center justify-between rounded bg-red-50 p-3 dark:bg-red-900/20">
                                    <span class="text-sm font-medium text-red-700 dark:text-red-400">
                                        @lang('marketplace::app.admin.submissions.show.critical-issues')
                                    </span>
                                    <span class="rounded bg-red-100 px-2 py-1 text-xs font-bold text-red-800 dark:bg-red-900 dark:text-red-300">
                                        {{ count($criticalIssues) }} @lang('marketplace::app.admin.submissions.show.issues-found')
                                    </span>
                                </div>
                            @endif

                            @if(count($highIssues) > 0)
                                <div class="flex items-center justify-between rounded bg-orange-50 p-3 dark:bg-orange-900/20">
                                    <span class="text-sm font-medium text-orange-700 dark:text-orange-400">
                                        @lang('marketplace::app.admin.submissions.show.high-issues')
                                    </span>
                                    <span class="rounded bg-orange-100 px-2 py-1 text-xs font-bold text-orange-800 dark:bg-orange-900 dark:text-orange-300">
                                        {{ count($highIssues) }} @lang('marketplace::app.admin.submissions.show.issues-found')
                                    </span>
                                </div>
                            @endif

                            @if(count($mediumIssues) > 0)
                                <div class="flex items-center justify-between rounded bg-yellow-50 p-3 dark:bg-yellow-900/20">
                                    <span class="text-sm font-medium text-yellow-700 dark:text-yellow-400">
                                        @lang('marketplace::app.admin.submissions.show.medium-issues')
                                    </span>
                                    <span class="rounded bg-yellow-100 px-2 py-1 text-xs font-bold text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                        {{ count($mediumIssues) }} @lang('marketplace::app.admin.submissions.show.issues-found')
                                    </span>
                                </div>
                            @endif

                            @if(count($lowIssues) > 0)
                                <div class="flex items-center justify-between rounded bg-blue-50 p-3 dark:bg-blue-900/20">
                                    <span class="text-sm font-medium text-blue-700 dark:text-blue-400">
                                        @lang('marketplace::app.admin.submissions.show.low-issues')
                                    </span>
                                    <span class="rounded bg-blue-100 px-2 py-1 text-xs font-bold text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ count($lowIssues) }} @lang('marketplace::app.admin.submissions.show.issues-found')
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Detailed Issues List -->
                        <div class="mt-4 space-y-3">
                            @foreach($issues as $issue)
                                <div class="rounded border border-gray-200 p-3 dark:border-gray-700">
                                    <div class="mb-2 flex items-start justify-between">
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ $issue['type'] ?? 'Unknown Issue' }}
                                        </span>
                                        <span class="rounded px-2 py-1 text-xs font-medium
                                            @if(($issue['severity'] ?? '') === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                            @elseif(($issue['severity'] ?? '') === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300
                                            @elseif(($issue['severity'] ?? '') === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                            @endif
                                        ">
                                            {{ ucfirst($issue['severity'] ?? 'low') }}
                                        </span>
                                    </div>
                                    @if(isset($issue['message']))
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $issue['message'] }}</p>
                                    @endif
                                    @if(isset($issue['file']))
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                            {{ $issue['file'] }}@if(isset($issue['line'])):{{ $issue['line'] }}@endif
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded bg-green-50 p-4 text-center dark:bg-green-900/20">
                            <p class="text-sm font-medium text-green-700 dark:text-green-400">
                                @lang('marketplace::app.admin.submissions.show.no-issues')
                            </p>
                        </div>
                    @endif
                @else
                    <div class="rounded bg-gray-50 p-4 text-center dark:bg-gray-800/50">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.submissions.show.no-security-scan')
                        </p>
                    </div>
                @endif
            </div>

            {!! view_render_event('admin.marketplace.submissions.show.right.security.after', ['submission' => $submission]) !!}

            {!! view_render_event('admin.marketplace.submissions.show.right.review.before', ['submission' => $submission]) !!}

            <!-- Review Notes -->
            @if($submission->review_notes)
                <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.admin.submissions.show.review-notes')
                    </h3>
                    <div class="prose prose-sm max-w-none dark:prose-invert text-gray-600 dark:text-gray-400">
                        {!! nl2br(e($submission->review_notes)) !!}
                    </div>
                </div>
            @endif

            {!! view_render_event('admin.marketplace.submissions.show.right.review.after', ['submission' => $submission]) !!}
        </div>

        {!! view_render_event('admin.marketplace.submissions.show.right.after', ['submission' => $submission]) !!}
    </div>

    @pushOnce('scripts')
        <script type="module">
            function runSecurityScan(submissionId) {
                this.$axios.post(`{{ route('admin.marketplace.submissions.index') }}/${submissionId}/security-scan`)
                    .then(response => {
                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data.message
                        });
                        window.location.reload();
                    })
                    .catch(error => {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: error.response?.data?.message || 'Security scan failed'
                        });
                    });
            }

            window.runSecurityScan = runSecurityScan;
        </script>
    @endPushOnce
</x-admin::layouts>
