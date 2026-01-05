<!-- AI Insights Section -->
<v-lead-insights lead-id="{{ $lead->id }}"></v-lead-insights>

@pushOnce('scripts')
<script type="text/x-template" id="v-lead-insights-template">
    <div class="flex w-full flex-col gap-2 border-b border-gray-200 p-4 dark:border-gray-800">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold dark:text-white">
                ✨ @lang('admin::app.ai.insights.title')
            </h3>
            <button
                @click="generateInsights"
                :disabled="isGenerating"
                class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
                :class="isGenerating 
                    ? 'bg-gray-200 text-gray-500 cursor-not-allowed dark:bg-gray-800 dark:text-gray-400' 
                    : 'bg-blue-600 text-white hover:bg-blue-700'"
                :style="!isGenerating ? 'background-color: #2563eb !important; color: white !important;' : ''"
            >
                <span v-if="!isGenerating" style="color: white !important;">@lang('admin::app.ai.insights.generate')</span>
                <span v-else>@lang('admin::app.ai.insights.generating')...</span>
            </button>
        </div>

        <div v-if="isLoading" class="text-sm text-gray-500 dark:text-gray-400 py-4">
            @lang('admin::app.ai.insights.loading')...
        </div>

        <div v-else-if="insights.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-4">
            @lang('admin::app.ai.insights.no-insights')
        </div>

        <div v-else class="flex flex-col gap-3">
            <div
                v-for="insight in insights"
                :key="insight.id"
                class="rounded-lg border p-3 transition-shadow hover:shadow-md"
                :class="getPriorityClass(insight.priority)"
            >
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold dark:text-white">@{{ insight.title }}</span>
                        <span 
                            class="px-2 py-0.5 text-xs rounded-full"
                            :class="getTypeClass(insight.type)"
                        >
                            @{{ getTypeLabel(insight.type) }}
                        </span>
                    </div>
                    <span 
                        v-if="insight.priority >= 7"
                        class="px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                    >
                        @lang('admin::app.ai.insights.high-priority')
                    </span>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    @{{ insight.description }}
                </p>
            </div>
        </div>
    </div>
</script>

<script type="module">
    app.component('v-lead-insights', {
        template: '#v-lead-insights-template',

        props: {
            leadId: {
                type: Number,
                required: true,
            },
        },

        data() {
            return {
                insights: [],
                isLoading: false,
                isGenerating: false,
            };
        },

        mounted() {
            this.loadInsights();
        },

        methods: {
            async loadInsights() {
                this.isLoading = true;
                try {
                    const response = await this.$axios.get(`{{ url('admin/ai/insights/lead') }}/${this.leadId}`);

                    if (response.data.data) {
                        this.insights = response.data.data;
                    }
                } catch (error) {
                    console.error('Error loading insights:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            async generateInsights() {
                if (this.isGenerating) return;

                this.isGenerating = true;
                try {
                    const response = await this.$axios.post(`{{ url('admin/ai/insights/lead') }}/${this.leadId}/generate`);

                    this.$emitter.emit('add-flash', {
                        type: 'success',
                        message: response.data.message || 'Insights generated successfully'
                    });
                    
                    await this.loadInsights();
                } catch (error) {
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message: error.response?.data?.message || 'Failed to generate insights'
                    });
                } finally {
                    this.isGenerating = false;
                }
            },

            getPriorityClass(priority) {
                if (priority >= 7) {
                    return 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20';
                } else if (priority >= 5) {
                    return 'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20';
                }
                return 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
            },

            getTypeClass(type) {
                const classes = {
                    'lead_scoring': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                    'relationship': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                    'opportunity': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                };
                return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
            },

            getTypeLabel(type) {
                const labels = {
                    'lead_scoring': "@lang('admin::app.ai.insights.type.lead-scoring')",
                    'relationship': "@lang('admin::app.ai.insights.type.relationship')",
                    'opportunity': "@lang('admin::app.ai.insights.type.opportunity')",
                };
                return labels[type] || type;
            },
        },
    });
</script>
@endPushOnce

