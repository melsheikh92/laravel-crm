<x-admin::layouts>
    <x-slot:title>
        @lang('marketplace::app.developer.earnings.index.title')
    </x-slot>

    {!! view_render_event('marketplace.developer.earnings.index.header.before') !!}

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('marketplace.developer.earnings.index.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('marketplace::app.developer.earnings.index.title')
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('marketplace::app.developer.earnings.index.description')
            </p>
        </div>

        {!! view_render_event('marketplace.developer.earnings.index.header.left.after') !!}

        {!! view_render_event('marketplace.developer.earnings.index.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('developer.marketplace.earnings.payout_history') }}"
                class="secondary-button"
            >
                @lang('marketplace::app.developer.earnings.index.payout-history')
            </a>

            <button
                type="button"
                class="primary-button"
                @click="$refs.withdrawalModal.open()"
            >
                @lang('marketplace::app.developer.earnings.index.request-payout')
            </button>
        </div>

        {!! view_render_event('marketplace.developer.earnings.index.header.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.earnings.index.header.after') !!}

    {!! view_render_event('marketplace.developer.earnings.index.content.before') !!}

    <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
        <!-- Left Section -->
        {!! view_render_event('marketplace.developer.earnings.index.content.left.before') !!}

        <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
            <!-- Main Revenue Statistics -->
            <div class="grid grid-cols-3 gap-4 max-lg:grid-cols-2 max-sm:grid-cols-1">
                <!-- Total Revenue -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.index.stats.total-revenue')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-dollar-sign text-2xl text-green-600 dark:text-green-500"></span>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-500">
                            ${{ number_format($statistics['total_revenue'], 2) }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $statistics['total_transactions'] }} @lang('marketplace::app.developer.earnings.index.stats.total-transactions')
                    </p>
                </div>

                <!-- Pending Revenue -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.index.stats.pending-revenue')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-clock text-2xl text-orange-600 dark:text-orange-500"></span>
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-500">
                            ${{ number_format($statistics['pending_revenue'], 2) }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.developer.earnings.index.stats.available-for-withdrawal')
                    </p>
                </div>

                <!-- Average Transaction -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.index.stats.average-transaction')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-trending-up text-2xl text-blue-600 dark:text-blue-500"></span>
                        <p class="text-2xl font-bold dark:text-white">
                            ${{ number_format($statistics['average_transaction'], 2) }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @lang('marketplace::app.developer.earnings.index.stats.per-sale')
                    </p>
                </div>
            </div>

            <!-- Recent Activity Cards -->
            <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                <!-- Recent Revenue (Last 30 Days) -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.index.stats.recent-revenue')
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="icon-bar-chart text-2xl text-emerald-600 dark:text-emerald-500"></span>
                        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-500">
                            ${{ number_format($statistics['recent_revenue'], 2) }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $statistics['recent_transactions'] }} @lang('marketplace::app.developer.earnings.index.stats.transactions-last-30-days')
                    </p>
                </div>

                <!-- Transaction Status -->
                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white px-4 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('marketplace::app.developer.earnings.index.stats.transaction-status')
                    </p>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="icon-check-circle text-xl text-green-600 dark:text-green-400"></span>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @lang('marketplace::app.developer.earnings.index.stats.completed')
                                </p>
                                <p class="text-lg font-bold dark:text-white">
                                    {{ number_format($statistics['completed_transactions']) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="icon-rotate-ccw text-xl text-red-600 dark:text-red-400"></span>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @lang('marketplace::app.developer.earnings.index.stats.refunded')
                                </p>
                                <p class="text-lg font-bold dark:text-white">
                                    {{ number_format($statistics['refunded_transactions']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Extension Card -->
            @if($statistics['top_extension'])
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-base font-semibold dark:text-white">
                            @lang('marketplace::app.developer.earnings.index.top-extension.title')
                        </p>
                        <a
                            href="{{ route('developer.marketplace.earnings.by_extension', $statistics['top_extension']['id']) }}"
                            class="text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            @lang('marketplace::app.developer.earnings.index.top-extension.view-details')
                        </a>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-purple-600">
                            <span class="icon-package text-2xl text-white"></span>
                        </div>

                        <div class="flex-1">
                            <p class="text-base font-semibold dark:text-white">
                                {{ $statistics['top_extension']['name'] }}
                            </p>
                            <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <span>
                                    <span class="icon-dollar-sign text-xs"></span>
                                    ${{ number_format($statistics['top_extension']['total_revenue'], 2) }} @lang('marketplace::app.developer.earnings.index.top-extension.revenue')
                                </span>
                                <span>
                                    <span class="icon-shopping-bag text-xs"></span>
                                    {{ number_format($statistics['top_extension']['total_sales']) }} @lang('marketplace::app.developer.earnings.index.top-extension.sales')
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.index.quick-actions.title')
                </p>

                <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                    <!-- View Transactions -->
                    <a
                        href="{{ route('developer.marketplace.earnings.transactions') }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-blue-400 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-500"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <span class="icon-list text-xl"></span>
                        </span>
                        <div>
                            <p class="font-semibold dark:text-white">
                                @lang('marketplace::app.developer.earnings.index.quick-actions.view-transactions')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('marketplace::app.developer.earnings.index.quick-actions.view-transactions-desc')
                            </p>
                        </div>
                    </a>

                    <!-- View Reports -->
                    <a
                        href="{{ route('developer.marketplace.earnings.reports') }}"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-blue-400 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-500"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                            <span class="icon-file-text text-xl"></span>
                        </span>
                        <div>
                            <p class="font-semibold dark:text-white">
                                @lang('marketplace::app.developer.earnings.index.quick-actions.view-reports')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('marketplace::app.developer.earnings.index.quick-actions.view-reports-desc')
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        {!! view_render_event('marketplace.developer.earnings.index.content.left.after') !!}

        <!-- Right Section -->
        {!! view_render_event('marketplace.developer.earnings.index.content.right.before') !!}

        <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
            <!-- Withdrawal Information -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.index.withdrawal.title')
                </p>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.index.withdrawal.available')
                        </span>
                        <span class="font-semibold text-green-600 dark:text-green-500">
                            ${{ number_format($statistics['pending_revenue'], 2) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.index.withdrawal.minimum')
                        </span>
                        <span class="font-semibold dark:text-white">
                            ${{ number_format(config('marketplace.minimum_payout_amount', 50), 2) }}
                        </span>
                    </div>

                    @if($statistics['pending_revenue'] >= config('marketplace.minimum_payout_amount', 50))
                        <button
                            type="button"
                            class="primary-button w-full"
                            @click="$refs.withdrawalModal.open()"
                        >
                            @lang('marketplace::app.developer.earnings.index.withdrawal.request-now')
                        </button>
                    @else
                        <div class="rounded-lg bg-orange-50 p-3 dark:bg-orange-900/20">
                            <p class="text-xs text-orange-800 dark:text-orange-300">
                                @lang('marketplace::app.developer.earnings.index.withdrawal.minimum-not-met')
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Earnings Info -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.index.info.title')
                </p>

                <div class="space-y-3">
                    <div class="flex items-start gap-2">
                        <span class="icon-info text-blue-600 dark:text-blue-400"></span>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.index.info.processing-time')
                        </p>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="icon-info text-blue-600 dark:text-blue-400"></span>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.index.info.revenue-share')
                        </p>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="icon-info text-blue-600 dark:text-blue-400"></span>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('marketplace::app.developer.earnings.index.info.payment-methods')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Help & Support -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold dark:text-white">
                    @lang('marketplace::app.developer.earnings.index.help.title')
                </p>

                <div class="space-y-3">
                    <a
                        href="#"
                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <span class="icon-book"></span>
                        @lang('marketplace::app.developer.earnings.index.help.payout-guide')
                    </a>

                    <a
                        href="#"
                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <span class="icon-help-circle"></span>
                        @lang('marketplace::app.developer.earnings.index.help.faq')
                    </a>

                    <a
                        href="#"
                        class="flex items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <span class="icon-mail"></span>
                        @lang('marketplace::app.developer.earnings.index.help.contact-support')
                    </a>
                </div>
            </div>
        </div>

        {!! view_render_event('marketplace.developer.earnings.index.content.right.after') !!}
    </div>

    {!! view_render_event('marketplace.developer.earnings.index.content.after') !!}

    <!-- Withdrawal Request Modal -->
    <x-admin::form
        v-slot="{ meta, errors, handleSubmit }"
        as="div"
    >
        <form
            @submit="handleSubmit($event, requestPayout)"
            ref="withdrawalForm"
        >
            <x-admin::modal ref="withdrawalModal">
                <x-slot:header>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('marketplace::app.developer.earnings.index.modal.title')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="space-y-4">
                        <!-- Available Balance -->
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                @lang('marketplace::app.developer.earnings.index.modal.available-balance')
                            </p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-500">
                                ${{ number_format($statistics['pending_revenue'], 2) }}
                            </p>
                        </div>

                        <!-- Amount -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('marketplace::app.developer.earnings.index.modal.amount')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="amount"
                                id="amount"
                                rules="required|numeric|min:{{ config('marketplace.minimum_payout_amount', 50) }}|max:{{ $statistics['pending_revenue'] }}"
                                :value="$statistics['pending_revenue']"
                                :placeholder="trans('marketplace::app.developer.earnings.index.modal.amount-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="amount" />
                        </x-admin::form.control-group>

                        <!-- Payment Method -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('marketplace::app.developer.earnings.index.modal.payment-method')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="payment_method"
                                id="payment_method"
                            >
                                <option value="bank_transfer">
                                    @lang('marketplace::app.developer.earnings.index.modal.bank-transfer')
                                </option>
                                <option value="paypal">
                                    @lang('marketplace::app.developer.earnings.index.modal.paypal')
                                </option>
                                <option value="stripe">
                                    @lang('marketplace::app.developer.earnings.index.modal.stripe')
                                </option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="payment_method" />
                        </x-admin::form.control-group>

                        <!-- Notes -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('marketplace::app.developer.earnings.index.modal.notes')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="notes"
                                id="notes"
                                rows="3"
                                :placeholder="trans('marketplace::app.developer.earnings.index.modal.notes-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="notes" />
                        </x-admin::form.control-group>

                        <!-- Info Message -->
                        <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                @lang('marketplace::app.developer.earnings.index.modal.info')
                            </p>
                        </div>
                    </div>
                </x-slot>

                <x-slot:footer>
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('marketplace::app.developer.earnings.index.modal.submit')
                    </button>
                </x-slot>
            </x-admin::modal>
        </form>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="module">
            app.component('v-earnings-dashboard', {
                methods: {
                    requestPayout(params, { resetForm, setErrors }) {
                        this.$axios.post("{{ route('developer.marketplace.earnings.request_payout') }}", params)
                            .then(response => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                this.$refs.withdrawalModal.close();
                                resetForm();
                                window.location.reload();
                            })
                            .catch(error => {
                                if (error.response?.status === 422) {
                                    setErrors(error.response.data.errors);
                                } else {
                                    this.$emitter.emit('add-flash', {
                                        type: 'error',
                                        message: error.response?.data?.message || '@lang('marketplace::app.developer.earnings.index.modal.error')'
                                    });
                                }
                            });
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
