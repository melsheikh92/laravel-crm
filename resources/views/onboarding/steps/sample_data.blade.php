@extends('onboarding.steps.base')

@section('form-fields')
    {{-- Import Sample Data Toggle --}}
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start">
            <div class="flex h-5 items-center">
                <input
                    type="checkbox"
                    name="import_sample_data"
                    id="import_sample_data"
                    value="1"
                    {{ old('import_sample_data', $defaultData['import_sample_data'] ?? config('onboarding.steps.sample_data.fields.import_sample_data.default')) ? 'checked' : '' }}
                    onchange="toggleSampleDataOptions()"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 @error('import_sample_data') border-red-500 @enderror"
                >
            </div>
            <div class="ml-3">
                <label for="import_sample_data" class="text-base font-medium text-gray-900 dark:text-white">
                    {{ config('onboarding.steps.sample_data.fields.import_sample_data.label') }}
                </label>
                @if(config('onboarding.steps.sample_data.fields.import_sample_data.help'))
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ config('onboarding.steps.sample_data.fields.import_sample_data.help') }}
                    </p>
                @endif
                @error('import_sample_data')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Sample Data Options --}}
    <div id="sample-data-options" class="space-y-4" style="display: {{ old('import_sample_data', $defaultData['import_sample_data'] ?? false) ? 'block' : 'none' }};">
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">
                Select Data to Import
            </h3>

            <div class="space-y-4">
                {{-- Include Companies --}}
                <label class="flex items-start rounded-lg border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                    <div class="flex h-5 items-center">
                        <input
                            type="checkbox"
                            name="include_companies"
                            id="include_companies"
                            value="1"
                            {{ old('include_companies', $defaultData['include_companies'] ?? config('onboarding.steps.sample_data.fields.include_companies.default')) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800"
                        >
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ config('onboarding.steps.sample_data.fields.include_companies.label') }}
                        </div>
                        @if(config('onboarding.steps.sample_data.fields.include_companies.help'))
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ config('onboarding.steps.sample_data.fields.include_companies.help') }}
                            </p>
                        @endif
                    </div>
                </label>

                {{-- Include Contacts --}}
                <label class="flex items-start rounded-lg border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                    <div class="flex h-5 items-center">
                        <input
                            type="checkbox"
                            name="include_contacts"
                            id="include_contacts"
                            value="1"
                            {{ old('include_contacts', $defaultData['include_contacts'] ?? config('onboarding.steps.sample_data.fields.include_contacts.default')) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800"
                        >
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ config('onboarding.steps.sample_data.fields.include_contacts.label') }}
                        </div>
                        @if(config('onboarding.steps.sample_data.fields.include_contacts.help'))
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ config('onboarding.steps.sample_data.fields.include_contacts.help') }}
                            </p>
                        @endif
                    </div>
                </label>

                {{-- Include Deals --}}
                <label class="flex items-start rounded-lg border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                    <div class="flex h-5 items-center">
                        <input
                            type="checkbox"
                            name="include_deals"
                            id="include_deals"
                            value="1"
                            {{ old('include_deals', $defaultData['include_deals'] ?? config('onboarding.steps.sample_data.fields.include_deals.default')) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800"
                        >
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ config('onboarding.steps.sample_data.fields.include_deals.label') }}
                        </div>
                        @if(config('onboarding.steps.sample_data.fields.include_deals.help'))
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ config('onboarding.steps.sample_data.fields.include_deals.help') }}
                            </p>
                        @endif
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- Sample Data Preview --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
            <svg class="h-5 w-5 text-blue-600 dark:text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
            </svg>
            What's Included
        </h3>

        <div class="space-y-3">
            @foreach(config('onboarding.steps.sample_data.sample_data_includes', []) as $item)
                <div class="flex items-start gap-2">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Warning Box --}}
    <x-onboarding.info-panel type="warning" title="Note about Sample Data">
        Sample data is for demonstration purposes only. You can easily delete it later from the settings panel. It helps you understand how the CRM works before adding real customer information.
    </x-onboarding.info-panel>

    {{-- Benefits Box --}}
    <x-onboarding.info-panel type="tip" title="💡 Pro Tip">
        If you're new to CRM systems, we highly recommend importing sample data. It's the best way to explore features, test workflows, and train your team before working with real customer data.
    </x-onboarding.info-panel>
@endsection

@push('scripts')
<script>
function toggleSampleDataOptions() {
    const checkbox = document.getElementById('import_sample_data');
    const options = document.getElementById('sample-data-options');

    if (checkbox.checked) {
        options.style.display = 'block';
        // Auto-check all options when enabling import
        document.getElementById('include_companies').checked = true;
        document.getElementById('include_contacts').checked = true;
        document.getElementById('include_deals').checked = true;
    } else {
        options.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleSampleDataOptions();
});
</script>
@endpush
