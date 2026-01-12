@extends('onboarding.steps.base')

@section('form-fields')
    {{-- Info Panel --}}
    <x-onboarding.info-panel type="info" title="Why we need this">
        Your company information personalizes your CRM experience and appears in reports, email templates, and customer-facing documents.
    </x-onboarding.info-panel>

    {{-- Company Name --}}
    <div>
        <x-onboarding.field-help
            for="company_name"
            :label="config('onboarding.steps.company_setup.fields.company_name.label')"
            :required="true"
        >
            {{ config('onboarding.steps.company_setup.fields.company_name.help') }}
        </x-onboarding.field-help>
        <input
            type="text"
            name="company_name"
            id="company_name"
            value="{{ old('company_name', $defaultData['company_name'] ?? '') }}"
            placeholder="{{ config('onboarding.steps.company_setup.fields.company_name.placeholder') }}"
            required
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('company_name') border-red-500 @enderror"
        >
        @error('company_name')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Industry --}}
    <div>
        <x-onboarding.field-help
            for="industry"
            :label="config('onboarding.steps.company_setup.fields.industry.label')"
        >
            {{ config('onboarding.steps.company_setup.fields.industry.help') }}. This helps us provide industry-specific features and insights.
        </x-onboarding.field-help>
        <select
            name="industry"
            id="industry"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('industry') border-red-500 @enderror"
        >
            <option value="">Select an industry...</option>
            @foreach(config('onboarding.steps.company_setup.fields.industry.options', []) as $value => $label)
                <option value="{{ $value }}" {{ old('industry', $defaultData['industry'] ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('industry')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Company Size --}}
    <div>
        <x-onboarding.field-help
            for="company_size"
            :label="config('onboarding.steps.company_setup.fields.company_size.label')"
        >
            {{ config('onboarding.steps.company_setup.fields.company_size.help') }}. This helps optimize dashboard features and user limits.
        </x-onboarding.field-help>
        <select
            name="company_size"
            id="company_size"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('company_size') border-red-500 @enderror"
        >
            <option value="">Select company size...</option>
            @foreach(config('onboarding.steps.company_setup.fields.company_size.options', []) as $value => $label)
                <option value="{{ $value }}" {{ old('company_size', $defaultData['company_size'] ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('company_size')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Phone --}}
        <div>
            <x-onboarding.field-help
                for="phone"
                :label="config('onboarding.steps.company_setup.fields.phone.label')"
            >
                {{ config('onboarding.steps.company_setup.fields.phone.help') }}. Include country code for international numbers.
            </x-onboarding.field-help>
            <input
                type="tel"
                name="phone"
                id="phone"
                value="{{ old('phone', $defaultData['phone'] ?? '') }}"
                placeholder="{{ config('onboarding.steps.company_setup.fields.phone.placeholder') }}"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('phone') border-red-500 @enderror"
            >
            @error('phone')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Website --}}
        <div>
            <x-onboarding.field-help
                for="website"
                :label="config('onboarding.steps.company_setup.fields.website.label')"
            >
                {{ config('onboarding.steps.company_setup.fields.website.help') }}. Must include https:// or http://
            </x-onboarding.field-help>
            <input
                type="url"
                name="website"
                id="website"
                value="{{ old('website', $defaultData['website'] ?? '') }}"
                placeholder="{{ config('onboarding.steps.company_setup.fields.website.placeholder') }}"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('website') border-red-500 @enderror"
            >
            @error('website')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Address --}}
    <div>
        <x-onboarding.field-help
            for="address"
            :label="config('onboarding.steps.company_setup.fields.address.label')"
        >
            {{ config('onboarding.steps.company_setup.fields.address.help') }}. This appears on invoices and official documents.
        </x-onboarding.field-help>
        <textarea
            name="address"
            id="address"
            rows="3"
            placeholder="{{ config('onboarding.steps.company_setup.fields.address.placeholder') }}"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('address') border-red-500 @enderror"
        >{{ old('address', $defaultData['address'] ?? '') }}</textarea>
        @error('address')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Pro Tip --}}
    <x-onboarding.info-panel type="tip">
        You can update these details anytime from your company settings page. Only the company name is required to continue.
    </x-onboarding.info-panel>
@endsection
