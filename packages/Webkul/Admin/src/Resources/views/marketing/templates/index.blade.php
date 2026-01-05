<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.marketing.templates.index.title')
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <div class="flex gap-2.5 items-center">
            <p class="text-2xl dark:text-white">@lang('admin::app.marketing.templates.index.title')</p>
        </div>

        <div class="flex gap-2.5 items-center">
            <a href="{{ route('admin.marketing.templates.create') }}">
                <button class="primary-button">
                    @lang('admin::app.marketing.templates.index.create-btn')
                </button>
            </a>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" id="templates-list">
        <!-- Templates will be loaded here -->
    </div>
</x-admin::layouts>

@push('scripts')
<script type="module">
    app.component('v-templates-list', {
        template: '#templates-list-template',
        data() {
            return {
                templates: [],
                isLoading: true,
            };
        },
        mounted() {
            this.loadTemplates();
        },
        methods: {
            async loadTemplates() {
                try {
                    const response = await this.$axios.get("{{ route('admin.marketing.templates.index') }}");
                    this.templates = response.data.data;
                } catch (error) {
                    console.error('Error loading templates:', error);
                } finally {
                    this.isLoading = false;
                }
            },
        },
    });
</script>
@endpush

