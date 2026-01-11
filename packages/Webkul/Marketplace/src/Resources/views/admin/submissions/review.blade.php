<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.admin.submissions.review.title')
    </x-slot>

    <div class="flex gap-4 max-lg:flex-wrap">
        <!-- Left Panel - Extension Info -->
        {!! view_render_event('admin.marketplace.submissions.review.left.before', ['submission' => $submission]) !!}

        <div class="max-lg:min-w-full max-lg:max-w-full [&>div:last-child]:border-b-0 lg:sticky lg:top-[73px] flex min-w-[394px] max-w-[394px] flex-col self-start rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <!-- Header -->
            <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                <x-admin::breadcrumbs
                    name="marketplace.submissions.review"
                    :entity="$submission"
                />

                <h3 class="text-lg font-bold dark:text-white">
                    @lang('marketplace::app.admin.submissions.review.title')
                </h3>
            </div>

            <!-- Extension Information -->
            <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('marketplace::app.admin.submissions.review.extension-info')
                </h4>

                <!-- Extension Name -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.submissions.show.extension-name')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ $submission->extension->name ?? 'N/A' }}
                    </span>
                </div>

                <!-- Version -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        @lang('marketplace::app.admin.submissions.show.version')
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ $submission->version->version ?? 'N/A' }}
                    </span>
                </div>

                <!-- Type -->
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
                        {{ $submission->getTimeSinceSubmission() }}
                    </span>
                </div>
            </div>

            <!-- Security Status -->
            <div class="flex w-full flex-col gap-2 p-4">
                <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('marketplace::app.admin.submissions.review.security-status')
                </h4>

                @if($submission->security_scan_results)
                    @if($submission->hasPassedSecurityScan())
                        <div class="flex items-center gap-2 rounded bg-green-50 p-3 dark:bg-green-900/20">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 text-white">
                                ✓
                            </span>
                            <span class="text-sm font-medium text-green-700 dark:text-green-400">
                                @lang('marketplace::app.admin.submissions.show.security-passed')
                            </span>
                        </div>
                    @elseif($submission->hasSecurityIssues())
                        <div class="rounded border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white">
                                    !
                                </span>
                                <span class="text-sm font-medium text-red-700 dark:text-red-400">
                                    @lang('marketplace::app.admin.submissions.show.security-failed')
                                </span>
                            </div>
                            <p class="text-xs text-red-600 dark:text-red-400">
                                @lang('marketplace::app.admin.submissions.review.security-issues-warning')
                            </p>
                            <div class="mt-2 text-xs text-red-600 dark:text-red-400">
                                {{ $submission->getSecurityIssuesCount() }} @lang('marketplace::app.admin.submissions.show.issues-found')
                            </div>
                        </div>
                    @endif
                @else
                    <div class="rounded bg-gray-50 p-3 dark:bg-gray-800/50">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @lang('marketplace::app.admin.submissions.show.security-not-run')
                        </p>
                    </div>
                @endif

                <a
                    href="{{ route('admin.marketplace.submissions.show', $submission->id) }}"
                    class="secondary-button mt-2"
                >
                    @lang('marketplace::app.admin.submissions.show.view-details-btn')
                </a>
            </div>
        </div>

        {!! view_render_event('admin.marketplace.submissions.review.left.after', ['submission' => $submission]) !!}

        {!! view_render_event('admin.marketplace.submissions.review.right.before', ['submission' => $submission]) !!}

        <!-- Right Panel - Review Form -->
        <div class="flex w-full flex-col gap-4 rounded-lg">
            @if(!$submission->isPending())
                <div class="box-shadow rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <p class="text-sm font-medium text-yellow-700 dark:text-yellow-400">
                        @lang('marketplace::app.admin.submissions.review.cannot-review-reviewed')
                    </p>
                </div>
            @endif

            <!-- Extension Description -->
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

            <!-- Version Changelog -->
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

            <!-- Review Decision Form -->
            @if($submission->isPending())
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('marketplace::app.admin.submissions.review.review-decision')
                        </h3>

                        <!-- Review Notes -->
                        <div class="mb-4">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('marketplace::app.admin.submissions.review.review-notes-label')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="review_notes"
                                    id="review_notes"
                                    rows="6"
                                    :placeholder="trans('marketplace::app.admin.submissions.review.review-notes-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="review_notes" />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @lang('marketplace::app.admin.submissions.review.review-notes-optional')
                                </p>
                            </x-admin::form.control-group>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="primary-button bg-green-600 hover:bg-green-700"
                                @click="$refs.approveModal.toggle()"
                            >
                                @lang('marketplace::app.admin.submissions.review.approve-btn')
                            </button>

                            <button
                                type="button"
                                class="primary-button bg-red-600 hover:bg-red-700"
                                @click="$refs.rejectModal.toggle()"
                            >
                                @lang('marketplace::app.admin.submissions.review.reject-btn')
                            </button>

                            <a
                                href="{{ route('admin.marketplace.submissions.index') }}"
                                class="secondary-button"
                            >
                                @lang('marketplace::app.admin.submissions.review.cancel-btn')
                            </a>
                        </div>
                    </div>

                    <!-- Approve Confirmation Modal -->
                    <form
                        @submit="handleSubmit($event, approveSubmission)"
                        ref="approveForm"
                    >
                        <x-admin::modal ref="approveModal">
                            <x-slot:header>
                                @lang('marketplace::app.admin.submissions.review.approve-btn')
                            </x-slot>

                            <x-slot:content>
                                <p class="text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.admin.submissions.review.confirm-approve')
                                </p>

                                @if($submission->hasSecurityIssues())
                                    <div class="mt-4 rounded border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                                        <p class="text-sm text-red-700 dark:text-red-400">
                                            @lang('marketplace::app.admin.submissions.review.security-issues-warning')
                                        </p>
                                    </div>
                                @endif
                            </x-slot>

                            <x-slot:footer>
                                <div class="flex items-center gap-x-2.5">
                                    <button
                                        type="submit"
                                        class="primary-button bg-green-600 hover:bg-green-700"
                                    >
                                        @lang('marketplace::app.admin.submissions.review.approve-btn')
                                    </button>

                                    <button
                                        type="button"
                                        class="secondary-button"
                                        @click="$refs.approveModal.close()"
                                    >
                                        @lang('marketplace::app.admin.submissions.review.cancel-btn')
                                    </button>
                                </div>
                            </x-slot>
                        </x-admin::modal>
                    </form>

                    <!-- Reject Confirmation Modal -->
                    <form
                        @submit="handleSubmit($event, rejectSubmission)"
                        ref="rejectForm"
                    >
                        <x-admin::modal ref="rejectModal">
                            <x-slot:header>
                                @lang('marketplace::app.admin.submissions.review.reject-btn')
                            </x-slot>

                            <x-slot:content>
                                <p class="text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.admin.submissions.review.confirm-reject')
                                </p>

                                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                    @lang('marketplace::app.admin.submissions.review.review-notes-required')
                                </p>
                            </x-slot>

                            <x-slot:footer>
                                <div class="flex items-center gap-x-2.5">
                                    <button
                                        type="submit"
                                        class="primary-button bg-red-600 hover:bg-red-700"
                                    >
                                        @lang('marketplace::app.admin.submissions.review.reject-btn')
                                    </button>

                                    <button
                                        type="button"
                                        class="secondary-button"
                                        @click="$refs.rejectModal.close()"
                                    >
                                        @lang('marketplace::app.admin.submissions.review.cancel-btn')
                                    </button>
                                </div>
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            @endif
        </div>

        {!! view_render_event('admin.marketplace.submissions.review.right.after', ['submission' => $submission]) !!}
    </div>

    @pushOnce('scripts')
        <script type="module">
            app.component('x-admin-submission-review', {
                data() {
                    return {
                        reviewNotes: '',
                    };
                },

                methods: {
                    async approveSubmission(params, { setErrors }) {
                        try {
                            const reviewNotes = document.getElementById('review_notes').value;

                            const response = await this.$axios.post(
                                '{{ route('admin.marketplace.submissions.approve', $submission->id) }}',
                                { review_notes: reviewNotes }
                            );

                            if (response.data.message) {
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });
                            }

                            this.$refs.approveModal.close();

                            setTimeout(() => {
                                window.location.href = '{{ route('admin.marketplace.submissions.index') }}';
                            }, 1000);
                        } catch (error) {
                            if (error.response?.data?.message) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response.data.message
                                });
                            }

                            setErrors(error.response?.data?.errors || {});
                        }
                    },

                    async rejectSubmission(params, { setErrors }) {
                        try {
                            const reviewNotes = document.getElementById('review_notes').value;

                            if (!reviewNotes || reviewNotes.trim() === '') {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: '@lang('marketplace::app.admin.submissions.review.review-notes-required')'
                                });
                                return;
                            }

                            const response = await this.$axios.post(
                                '{{ route('admin.marketplace.submissions.reject', $submission->id) }}',
                                { review_notes: reviewNotes }
                            );

                            if (response.data.message) {
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });
                            }

                            this.$refs.rejectModal.close();

                            setTimeout(() => {
                                window.location.href = '{{ route('admin.marketplace.submissions.index') }}';
                            }, 1000);
                        } catch (error) {
                            if (error.response?.data?.message) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response.data.message
                                });
                            }

                            setErrors(error.response?.data?.errors || {});
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
