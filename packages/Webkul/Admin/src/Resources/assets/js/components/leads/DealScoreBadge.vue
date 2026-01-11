<template>
    <!-- Shimmer Loader -->
    <div v-if="isLoading" class="flex items-center gap-2">
        <div class="light-shimmer-bg dark:shimmer h-8 w-24 rounded-md"></div>
    </div>

    <!-- Deal Score Badge -->
    <div v-else-if="score" class="flex items-center gap-2">
        <!-- Score Badge with Popover Trigger -->
        <div class="relative">
            <button
                type="button"
                class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-semibold transition-all hover:shadow-md"
                :class="getBadgeColorClass()"
                @click="toggleBreakdown"
            >
                <span class="text-xs font-medium">Deal Score</span>
                <span class="text-lg font-bold">{{ Math.round(score.score) }}</span>
            </button>

            <!-- Score Breakdown Popover -->
            <div
                v-if="showBreakdown"
                class="absolute top-full left-0 z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Header -->
                <div class="mb-3 flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Score Breakdown
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
                        Overall Score
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
                            {{ Math.round(score.score) }}
                        </span>
                    </div>
                </div>

                <!-- Win Probability -->
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Win Probability
                    </span>
                    <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                        {{ Math.round(score.win_probability) }}%
                    </span>
                </div>

                <!-- Score Factors -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Score Factors
                    </p>

                    <!-- Engagement Score -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            Engagement
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-blue-500"
                                    :style="{ width: score.engagement_score + '%' }"
                                ></div>
                            </div>
                            <span class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ Math.round(score.engagement_score) }}
                            </span>
                        </div>
                    </div>

                    <!-- Velocity Score -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            Velocity
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-purple-500"
                                    :style="{ width: score.velocity_score + '%' }"
                                ></div>
                            </div>
                            <span class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ Math.round(score.velocity_score) }}
                            </span>
                        </div>
                    </div>

                    <!-- Value Score -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            Value
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-green-500"
                                    :style="{ width: score.value_score + '%' }"
                                ></div>
                            </div>
                            <span class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ Math.round(score.value_score) }}
                            </span>
                        </div>
                    </div>

                    <!-- Historical Pattern Score -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            Historical Pattern
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-orange-500"
                                    :style="{ width: score.historical_pattern_score + '%' }"
                                ></div>
                            </div>
                            <span class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ Math.round(score.historical_pattern_score) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Generated At -->
                <div class="mt-3 border-t border-gray-200 pt-2 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Generated {{ formatTimestamp(score.generated_at) }}
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
                        <span v-if="isCalculating">Recalculating...</span>
                        <span v-else>Recalculate Score</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Win Probability Badge -->
        <div
            class="rounded-md px-2 py-1 text-xs font-medium"
            :class="getWinProbabilityClass()"
        >
            {{ Math.round(score.win_probability) }}% Win
        </div>
    </div>

    <!-- No Score Message -->
    <div v-else class="flex items-center gap-2">
        <button
            type="button"
            class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 transition-all hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
            :disabled="isCalculating"
            @click="recalculateScore"
        >
            <span v-if="isCalculating">Calculating Score...</span>
            <span v-else>Calculate Deal Score</span>
        </button>
    </div>
</template>

<script>
export default {
    name: 'DealScoreBadge',

    props: {
        /**
         * Lead ID for fetching the deal score
         */
        leadId: {
            type: Number,
            required: true,
        },

        /**
         * API endpoint for fetching the score
         */
        scoreEndpoint: {
            type: String,
            required: false,
            default: null,
        },

        /**
         * API endpoint for calculating the score
         */
        calculateEndpoint: {
            type: String,
            required: false,
            default: null,
        },

        /**
         * Auto-load score on mount
         */
        autoLoad: {
            type: Boolean,
            default: true,
        },
    },

    emits: ['score-loaded', 'score-calculated', 'error'],

    data() {
        return {
            isLoading: false,
            isCalculating: false,
            score: null,
            showBreakdown: false,
        };
    },

    computed: {
        /**
         * Get the endpoint for fetching score
         */
        getScoreEndpoint() {
            return this.scoreEndpoint || `/admin/leads/${this.leadId}/score`;
        },

        /**
         * Get the endpoint for calculating score
         */
        getCalculateEndpoint() {
            return this.calculateEndpoint || `/admin/leads/${this.leadId}/score/calculate`;
        },
    },

    mounted() {
        if (this.autoLoad) {
            this.loadScore();
        }

        // Close breakdown when clicking outside
        document.addEventListener('click', this.handleClickOutside);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },

    methods: {
        /**
         * Load the deal score from API
         */
        loadScore() {
            this.isLoading = true;

            this.$axios
                .get(this.getScoreEndpoint)
                .then((response) => {
                    this.score = response.data.data;
                    this.$emit('score-loaded', this.score);
                })
                .catch((error) => {
                    if (error.response?.status === 404) {
                        this.score = null;
                    } else {
                        this.$emit('error', error);
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        /**
         * Recalculate the deal score
         */
        recalculateScore() {
            this.isCalculating = true;

            this.$axios
                .post(this.getCalculateEndpoint)
                .then((response) => {
                    this.score = response.data.data;
                    this.$emitter.emit('add-flash', {
                        type: 'success',
                        message: response.data.message || 'Deal score calculated successfully.',
                    });
                    this.$emit('score-calculated', this.score);
                })
                .catch((error) => {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message:
                            error.response?.data?.message ||
                            'Failed to calculate deal score.',
                    });
                    this.$emit('error', error);
                })
                .finally(() => {
                    this.isCalculating = false;
                });
        },

        /**
         * Toggle score breakdown visibility
         */
        toggleBreakdown(event) {
            event.stopPropagation();
            this.showBreakdown = !this.showBreakdown;
        },

        /**
         * Handle click outside to close breakdown
         */
        handleClickOutside(event) {
            const badge = this.$el;
            if (badge && !badge.contains(event.target)) {
                this.showBreakdown = false;
            }
        },

        /**
         * Get badge color class based on priority
         */
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

        /**
         * Get win probability badge class
         */
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

        /**
         * Get score bar color class
         */
        getScoreBarColor(score) {
            if (score >= 80) {
                return 'bg-green-500';
            } else if (score >= 50) {
                return 'bg-yellow-500';
            } else {
                return 'bg-red-500';
            }
        },

        /**
         * Get score text color class
         */
        getScoreTextColor(score) {
            if (score >= 80) {
                return 'text-green-600 dark:text-green-400';
            } else if (score >= 50) {
                return 'text-yellow-600 dark:text-yellow-400';
            } else {
                return 'text-red-600 dark:text-red-400';
            }
        },

        /**
         * Format timestamp to relative time
         */
        formatTimestamp(timestamp) {
            if (!timestamp) {
                return '';
            }

            const date = new Date(timestamp);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) {
                return 'just now';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            } else {
                const days = Math.floor(diffInSeconds / 86400);
                return `${days} day${days > 1 ? 's' : ''} ago`;
            }
        },
    },
};
</script>
