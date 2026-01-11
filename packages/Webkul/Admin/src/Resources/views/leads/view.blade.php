<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.view.title', ['title' => $lead->title])
        </x-slot>

        <!-- Content -->
        <div class="relative flex gap-4 max-lg:flex-wrap">
            <!-- Left Panel -->
            {!! view_render_event('admin.leads.view.left.before', ['lead' => $lead]) !!}

            <div
                class="max-lg:min-w-full max-lg:max-w-full [&>div:last-child]:border-b-0 lg:sticky lg:top-[73px] flex min-w-[394px] max-w-[394px] flex-col self-start rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <!-- Lead Information -->
                <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
                    <!-- Breadcrumb's -->
                    <div class="flex items-center justify-between">
                        <x-admin::breadcrumbs name="leads.view" :entity="$lead" />
                    </div>

                    <div class="mb-2">
                        @if (($days = $lead->rotten_days) > 0)
                            @php
                                $lead->tags->prepend([
                                    'name' => '<span class="icon-rotten text-base"></span>' . trans('admin::app.leads.view.rotten-days', ['days' => $days]),
                                    'color' => '#FEE2E2'
                                ]);
                            @endphp
                        @endif

                        {!! view_render_event('admin.leads.view.tags.before', ['lead' => $lead]) !!}

                        <!-- Tags -->
                        <x-admin::tags :attach-endpoint="route('admin.leads.tags.attach', $lead->id)"
                            :detach-endpoint="route('admin.leads.tags.detach', $lead->id)" :added-tags="$lead->tags" />

                        {!! view_render_event('admin.leads.view.tags.after', ['lead' => $lead]) !!}
                    </div>


                    {!! view_render_event('admin.leads.view.title.before', ['lead' => $lead]) !!}

                    <!-- Title -->
                    <h1 class="text-lg font-bold dark:text-white">
                        {{ $lead->title }}
                    </h1>

                    {!! view_render_event('admin.leads.view.title.after', ['lead' => $lead]) !!}

                    {!! view_render_event('admin.leads.view.deal_score.before', ['lead' => $lead]) !!}

                    <!-- Deal Score Badge -->
                    <v-deal-score-badge :lead-id="{{ $lead->id }}"></v-deal-score-badge>

                    {!! view_render_event('admin.leads.view.deal_score.after', ['lead' => $lead]) !!}

                    <!-- Activity Actions -->
                    <div class="flex flex-wrap gap-2">
                        {!! view_render_event('admin.leads.view.actions.before', ['lead' => $lead]) !!}

                        @if (bouncer()->hasPermission('mail.compose'))
                            <!-- Mail Activity Action -->
                            <x-admin::activities.actions.mail :entity="$lead" entity-control-name="lead_id" />
                        @endif

                        <!-- WhatsApp Activity Action -->
                        <x-admin::activities.actions.whatsapp :entity="$lead" entity-control-name="lead_id" />


                        @if (bouncer()->hasPermission('activities.create'))
                            <!-- File Activity Action -->
                            <x-admin::activities.actions.file :entity="$lead" entity-control-name="lead_id" />

                            <!-- Note Activity Action -->
                            <x-admin::activities.actions.note :entity="$lead" entity-control-name="lead_id" />

                            <!-- Activity Action -->
                            <x-admin::activities.actions.activity :entity="$lead" entity-control-name="lead_id" />
                        @endif

                        {!! view_render_event('admin.leads.view.actions.after', ['lead' => $lead]) !!}
                    </div>
                </div>

                <!-- Lead Attributes -->
                @include ('admin::leads.view.attributes')

                <!-- Contact Person -->
                @include ('admin::leads.view.person')

                <!-- AI Insights -->
                @include ('admin::leads.view.insights')
            </div>

            {!! view_render_event('admin.leads.view.left.after', ['lead' => $lead]) !!}

            {!! view_render_event('admin.leads.view.right.before', ['lead' => $lead]) !!}

            <!-- Right Panel -->
            <div class="flex w-full flex-col gap-4 rounded-lg">
                <!-- Stages Navigation -->
                @include ('admin::leads.view.stages')

                <!-- Activities -->
                {!! view_render_event('admin.leads.view.activities.before', ['lead' => $lead]) !!}

                <x-admin::activities :endpoint="route('admin.leads.activities.index', $lead->id)"
                    :email-detach-endpoint="route('admin.leads.emails.detach', $lead->id)"
                    :activeType="request()->query('from') === 'quotes' ? 'quotes' : 'all'" :extra-types="[
                    ['name' => 'description', 'label' => trans('admin::app.leads.view.tabs.description')],
                    ['name' => 'products', 'label' => trans('admin::app.leads.view.tabs.products')],
                    ['name' => 'quotes', 'label' => trans('admin::app.leads.view.tabs.quotes')],
                ]">
                    <!-- Products -->
                    <x-slot:products>
                        @include ('admin::leads.view.products')
                        </x-slot>

                        <!-- Quotes -->
                        <x-slot:quotes>
                            @include ('admin::leads.view.quotes')
                            </x-slot>

                            <!-- Description -->
                            <x-slot:description>
                                <div class="p-4 dark:text-white">
                                    {{ $lead->description }}
                                </div>
                                </x-slot>
                </x-admin::activities>

                {!! view_render_event('admin.leads.view.activities.after', ['lead' => $lead]) !!}
            </div>

            {!! view_render_event('admin.leads.view.right.after', ['lead' => $lead]) !!}
        </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-deal-score-badge-template"
        >
            <!-- Shimmer Loader -->
            <template v-if="isLoading">
                <div class="flex items-center gap-2">
                    <div class="light-shimmer-bg dark:shimmer h-8 w-24 rounded-md"></div>
                </div>
            </template>

            <!-- Deal Score Badge -->
            <template v-else-if="score">
                <div class="flex items-center gap-2">
                    <!-- Score Badge with Popover Trigger -->
                    <div class="relative" ref="badgeContainer">
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-semibold transition-all hover:shadow-md"
                            :class="getBadgeColorClass()"
                            @click="toggleBreakdown"
                        >
                            <span class="text-xs font-medium">@lang('admin::app.leads.view.deal-score')</span>
                            <span class="text-lg font-bold">@{{ Math.round(score.score) }}</span>
                        </button>

                        <!-- Score Breakdown Popover -->
                        <div
                            v-if="showBreakdown"
                            class="absolute top-full left-0 z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        >
                            <!-- Header -->
                            <div class="mb-3 flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.leads.view.score-breakdown')
                                </h4>
                                <button
                                    type="button"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                    @click="showBreakdown = false"
                                >
                                    <span class="text-lg">&times;</span>
                                </button>
                            </div>

                            <!-- Overall Score -->
                            <div class="mb-3 flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.leads.view.overall-score')
                                </span>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div
                                            class="h-full rounded-full transition-all"
                                            :class="getScoreBarColor(score.score)"
                                            :style="{ width: score.score + '%' }"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-bold" :class="getScoreTextColor(score.score)">
                                        @{{ Math.round(score.score) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Win Probability -->
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.leads.view.win-probability')
                                </span>
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    @{{ Math.round(score.win_probability) }}%
                                </span>
                            </div>

                            <!-- Score Factors -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.leads.view.score-factors')
                                </p>

                                <!-- Engagement Score -->
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        @lang('admin::app.leads.view.engagement')
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div
                                                class="h-full rounded-full bg-blue-500"
                                                :style="{ width: score.engagement_score + '%' }"
                                            ></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">
                                            @{{ Math.round(score.engagement_score) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Velocity Score -->
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        @lang('admin::app.leads.view.velocity')
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div
                                                class="h-full rounded-full bg-purple-500"
                                                :style="{ width: score.velocity_score + '%' }"
                                            ></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">
                                            @{{ Math.round(score.velocity_score) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Value Score -->
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        @lang('admin::app.leads.view.value')
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div
                                                class="h-full rounded-full bg-green-500"
                                                :style="{ width: score.value_score + '%' }"
                                            ></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">
                                            @{{ Math.round(score.value_score) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Historical Pattern Score -->
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        @lang('admin::app.leads.view.historical-pattern')
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div
                                                class="h-full rounded-full bg-orange-500"
                                                :style="{ width: score.historical_pattern_score + '%' }"
                                            ></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">
                                            @{{ Math.round(score.historical_pattern_score) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Generated At -->
                            <div class="mt-3 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.leads.view.generated') @{{ formatTimestamp(score.generated_at) }}
                                </p>
                            </div>

                            <!-- Recalculate Button -->
                            <div class="mt-3">
                                <button
                                    type="button"
                                    class="w-full rounded-md bg-blue-600 px-3 py-2 text-xs font-medium text-white transition-all hover:bg-blue-700 disabled:bg-gray-400"
                                    :disabled="isCalculating"
                                    @click="recalculateScore"
                                >
                                    <span v-if="isCalculating">@lang('admin::app.leads.view.recalculating')</span>
                                    <span v-else>@lang('admin::app.leads.view.recalculate-score')</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Win Probability Badge -->
                    <div
                        class="rounded-md px-2 py-1 text-xs font-medium"
                        :class="getWinProbabilityClass()"
                    >
                        @{{ Math.round(score.win_probability) }}% @lang('admin::app.leads.view.win')
                    </div>
                </div>
            </template>

            <!-- No Score Message -->
            <template v-else>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 transition-all hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                        :disabled="isCalculating"
                        @click="recalculateScore"
                    >
                        <span v-if="isCalculating">@lang('admin::app.leads.view.calculating-score')</span>
                        <span v-else>@lang('admin::app.leads.view.calculate-deal-score')</span>
                    </button>
                </div>
            </template>
        </script>

        <script type="module">
            app.component('v-deal-score-badge', {
                template: '#v-deal-score-badge-template',

                props: {
                    leadId: {
                        type: Number,
                        required: true,
                    },

                    scoreEndpoint: {
                        type: String,
                        required: false,
                        default: null,
                    },

                    calculateEndpoint: {
                        type: String,
                        required: false,
                        default: null,
                    },

                    autoLoad: {
                        type: Boolean,
                        default: true,
                    },
                },

                data() {
                    return {
                        isLoading: false,
                        isCalculating: false,
                        score: null,
                        showBreakdown: false,
                    };
                },

                computed: {
                    getScoreEndpoint() {
                        return this.scoreEndpoint || `/admin/leads/${this.leadId}/score`;
                    },

                    getCalculateEndpoint() {
                        return this.calculateEndpoint || `/admin/leads/${this.leadId}/score/calculate`;
                    },
                },

                mounted() {
                    if (this.autoLoad) {
                        this.loadScore();
                    }

                    document.addEventListener('click', this.handleClickOutside);
                },

                beforeUnmount() {
                    document.removeEventListener('click', this.handleClickOutside);
                },

                methods: {
                    loadScore() {
                        this.isLoading = true;

                        this.$axios.get(this.getScoreEndpoint)
                            .then(response => {
                                this.score = response.data.data;
                            })
                            .catch(error => {
                                if (error.response?.status !== 404) {
                                    console.error('Failed to load deal score:', error);
                                }
                            })
                            .finally(() => {
                                this.isLoading = false;
                            });
                    },

                    recalculateScore() {
                        this.isCalculating = true;

                        this.$axios.post(this.getCalculateEndpoint)
                            .then(response => {
                                this.score = response.data.data;
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message || '@lang('admin::app.leads.view.score-calculated-success')',
                                });
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || '@lang('admin::app.leads.view.score-calculation-failed')',
                                });
                            })
                            .finally(() => {
                                this.isCalculating = false;
                            });
                    },

                    toggleBreakdown(event) {
                        event.stopPropagation();
                        this.showBreakdown = !this.showBreakdown;
                    },

                    handleClickOutside(event) {
                        if (this.$refs.badgeContainer && !this.$refs.badgeContainer.contains(event.target)) {
                            this.showBreakdown = false;
                        }
                    },

                    getBadgeColorClass() {
                        if (!this.score) {
                            return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                        }

                        const score = this.score.score;
                        if (score >= 80) {
                            return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                        } else if (score >= 50) {
                            return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
                        } else {
                            return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                        }
                    },

                    getWinProbabilityClass() {
                        if (!this.score) {
                            return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                        }

                        const probability = this.score.win_probability;
                        if (probability >= 70) {
                            return 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400';
                        } else if (probability >= 40) {
                            return 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400';
                        } else {
                            return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                        }
                    },

                    getScoreBarColor(score) {
                        if (score >= 80) {
                            return 'bg-green-500';
                        } else if (score >= 50) {
                            return 'bg-yellow-500';
                        } else {
                            return 'bg-red-500';
                        }
                    },

                    getScoreTextColor(score) {
                        if (score >= 80) {
                            return 'text-green-600 dark:text-green-400';
                        } else if (score >= 50) {
                            return 'text-yellow-600 dark:text-yellow-400';
                        } else {
                            return 'text-red-600 dark:text-red-400';
                        }
                    },

                    formatTimestamp(timestamp) {
                        if (!timestamp) {
                            return '';
                        }

                        const date = new Date(timestamp);
                        const now = new Date();
                        const diffInSeconds = Math.floor((now - date) / 1000);

                        if (diffInSeconds < 60) {
                            return '@lang('admin::app.leads.view.just-now')';
                        } else if (diffInSeconds < 3600) {
                            const minutes = Math.floor(diffInSeconds / 60);
                            return `${minutes} @lang('admin::app.leads.view.minutes-ago')`;
                        } else if (diffInSeconds < 86400) {
                            const hours = Math.floor(diffInSeconds / 3600);
                            return `${hours} @lang('admin::app.leads.view.hours-ago')`;
                        } else {
                            const days = Math.floor(diffInSeconds / 86400);
                            return `${days} @lang('admin::app.leads.view.days-ago')`;
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>