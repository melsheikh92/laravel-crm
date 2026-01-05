<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.marketing.templates.create.title')
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <div class="flex gap-2.5 items-center">
            <p class="text-2xl dark:text-white">@lang('admin::app.marketing.templates.create.title')</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.marketing.templates.store') }}" @submit.prevent="onSubmit">
        @csrf

        <div class="mt-4 p-[16px] bg-white dark:bg-gray-900 rounded-[4px] box-shadow">
            <div class="mb-4">
                <label class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.marketing.campaigns.create.name')
                    <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    class="mt-1.5 w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                    v-model="template.name"
                    required
                />
            </div>

            <div class="mb-4">
                <label class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.marketing.campaigns.create.subject')
                    <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    name="subject"
                    class="mt-1.5 w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                    v-model="template.subject"
                    required
                />
            </div>

            <div class="mb-4">
                <label class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.marketing.campaigns.create.content')
                    <span class="text-red-600">*</span>
                </label>
                <textarea
                    name="content"
                    rows="15"
                    class="mt-1.5 w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                    v-model="template.content"
                    required
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                    Available variables: {{name}}, {{company}}, {{email}}, {{phone}}
                </p>
            </div>
        </div>

        <div class="flex gap-x-2.5 justify-end items-center mt-4">
            <a href="{{ route('admin.marketing.templates.index') }}">
                <button type="button" class="secondary-button">
                    @lang('admin::app.marketing.campaigns.create.cancel')
                </button>
            </a>

            <button type="submit" class="primary-button">
                @lang('admin::app.marketing.campaigns.create.save-btn')
            </button>
        </div>
    </form>
</x-admin::layouts>

@push('scripts')
<script type="module">
    app.component('v-template-form', {
        data() {
            return {
                template: {
                    name: '',
                    subject: '',
                    content: '',
                },
            };
        },
        methods: {
            async onSubmit() {
                try {
                    const response = await this.$axios.post("{{ route('admin.marketing.templates.store') }}", this.template);
                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                    window.location.href = "{{ route('admin.marketing.templates.index') }}";
                } catch (error) {
                    this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Failed to create template' });
                }
            },
        },
    });
</script>
@endpush

