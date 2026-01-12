{{--
    Field Help Component

    Combines a form label with an inline help tooltip

    Usage:
    <x-onboarding.field-help
        for="company_name"
        label="Company Name"
        :required="true"
    >
        Your official company or business name that will appear in documents
    </x-onboarding.field-help>

    Without tooltip (just label):
    <x-onboarding.field-help
        for="company_name"
        label="Company Name"
        :required="true"
    />
--}}

@props(['for', 'label', 'required' => false])

<label for="{{ $for }}" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
    <span>{{ $label }}</span>

    @if($required)
        <span class="text-red-500" aria-label="Required field">*</span>
    @endif

    @if(!empty(trim($slot)))
        <x-onboarding.tooltip>
            {{ $slot }}
        </x-onboarding.tooltip>
    @endif
</label>
