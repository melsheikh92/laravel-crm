@extends('onboarding.steps.base')

@section('form-fields')
    {{-- Email Provider Selection --}}
    <div>
        <label for="email_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ config('onboarding.steps.email_integration.fields.email_provider.label') }}
        </label>
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach(config('onboarding.steps.email_integration.providers', []) as $provider => $providerConfig)
                <label class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm hover:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-500">
                    <input
                        type="radio"
                        name="email_provider"
                        value="{{ $provider }}"
                        {{ old('email_provider', $defaultData['email_provider'] ?? '') == $provider ? 'checked' : '' }}
                        class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 focus:ring-blue-500"
                        onchange="updateProviderFields('{{ $provider }}')"
                    >
                    <div class="ml-3 flex flex-col">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">
                            {{ $providerConfig['label'] }}
                        </span>
                        <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                            {{ $providerConfig['description'] }}
                        </span>
                    </div>
                </label>
            @endforeach
        </div>
        @if(config('onboarding.steps.email_integration.fields.email_provider.help'))
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ config('onboarding.steps.email_integration.fields.email_provider.help') }}
            </p>
        @endif
        @error('email_provider')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- SMTP Configuration Fields --}}
    <div id="smtp-fields" class="space-y-6">
        {{-- SMTP Host --}}
        <div>
            <label for="smtp_host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ config('onboarding.steps.email_integration.fields.smtp_host.label') }}
            </label>
            <input
                type="text"
                name="smtp_host"
                id="smtp_host"
                value="{{ old('smtp_host', $defaultData['smtp_host'] ?? '') }}"
                placeholder="{{ config('onboarding.steps.email_integration.fields.smtp_host.placeholder') }}"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('smtp_host') border-red-500 @enderror"
            >
            @if(config('onboarding.steps.email_integration.fields.smtp_host.help'))
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ config('onboarding.steps.email_integration.fields.smtp_host.help') }}
                </p>
            @endif
            @error('smtp_host')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            {{-- SMTP Port --}}
            <div>
                <label for="smtp_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ config('onboarding.steps.email_integration.fields.smtp_port.label') }}
                </label>
                <input
                    type="number"
                    name="smtp_port"
                    id="smtp_port"
                    value="{{ old('smtp_port', $defaultData['smtp_port'] ?? config('onboarding.steps.email_integration.fields.smtp_port.default')) }}"
                    placeholder="{{ config('onboarding.steps.email_integration.fields.smtp_port.placeholder') }}"
                    min="1"
                    max="65535"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('smtp_port') border-red-500 @enderror"
                >
                @if(config('onboarding.steps.email_integration.fields.smtp_port.help'))
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ config('onboarding.steps.email_integration.fields.smtp_port.help') }}
                    </p>
                @endif
                @error('smtp_port')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- SMTP Encryption --}}
            <div>
                <label for="smtp_encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ config('onboarding.steps.email_integration.fields.smtp_encryption.label') }}
                </label>
                <select
                    name="smtp_encryption"
                    id="smtp_encryption"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('smtp_encryption') border-red-500 @enderror"
                >
                    @foreach(config('onboarding.steps.email_integration.fields.smtp_encryption.options', []) as $value => $label)
                        <option value="{{ $value }}" {{ old('smtp_encryption', $defaultData['smtp_encryption'] ?? config('onboarding.steps.email_integration.fields.smtp_encryption.default')) == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @if(config('onboarding.steps.email_integration.fields.smtp_encryption.help'))
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ config('onboarding.steps.email_integration.fields.smtp_encryption.help') }}
                    </p>
                @endif
                @error('smtp_encryption')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- SMTP Username --}}
        <div>
            <label for="smtp_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ config('onboarding.steps.email_integration.fields.smtp_username.label') }}
            </label>
            <input
                type="text"
                name="smtp_username"
                id="smtp_username"
                value="{{ old('smtp_username', $defaultData['smtp_username'] ?? '') }}"
                placeholder="{{ config('onboarding.steps.email_integration.fields.smtp_username.placeholder') }}"
                autocomplete="username"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('smtp_username') border-red-500 @enderror"
            >
            @if(config('onboarding.steps.email_integration.fields.smtp_username.help'))
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ config('onboarding.steps.email_integration.fields.smtp_username.help') }}
                </p>
            @endif
            @error('smtp_username')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- SMTP Password --}}
        <div>
            <label for="smtp_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ config('onboarding.steps.email_integration.fields.smtp_password.label') }}
            </label>
            <input
                type="password"
                name="smtp_password"
                id="smtp_password"
                value="{{ old('smtp_password', $defaultData['smtp_password'] ?? '') }}"
                autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('smtp_password') border-red-500 @enderror"
            >
            @if(config('onboarding.steps.email_integration.fields.smtp_password.help'))
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ config('onboarding.steps.email_integration.fields.smtp_password.help') }}
                </p>
            @endif
            @error('smtp_password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Test Connection Checkbox --}}
        <div class="flex items-start">
            <div class="flex h-5 items-center">
                <input
                    type="checkbox"
                    name="test_connection"
                    id="test_connection"
                    value="1"
                    {{ old('test_connection', $defaultData['test_connection'] ?? config('onboarding.steps.email_integration.fields.test_connection.default')) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 @error('test_connection') border-red-500 @enderror"
                >
            </div>
            <div class="ml-3 text-sm">
                <label for="test_connection" class="font-medium text-gray-700 dark:text-gray-300">
                    {{ config('onboarding.steps.email_integration.fields.test_connection.label') }}
                </label>
                @if(config('onboarding.steps.email_integration.fields.test_connection.help'))
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ config('onboarding.steps.email_integration.fields.test_connection.help') }}
                    </p>
                @endif
                @error('test_connection')
                    <p class="mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Common Email Providers Quick Setup Guide --}}
    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
        <h4 class="mb-2 text-sm font-semibold text-blue-900 dark:text-blue-300">
            📚 Common Provider Settings
        </h4>
        <div class="space-y-2 text-xs text-blue-800 dark:text-blue-400">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <div>
                    <strong>Gmail:</strong> smtp.gmail.com, Port 587, TLS
                </div>
                <div>
                    <strong>Outlook:</strong> smtp.office365.com, Port 587, TLS
                </div>
                <div>
                    <strong>SendGrid:</strong> smtp.sendgrid.net, Port 587, TLS
                </div>
                <div>
                    <strong>Mailgun:</strong> smtp.mailgun.org, Port 587, TLS
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function updateProviderFields(provider) {
    const smtpFields = document.getElementById('smtp-fields');
    const hostInput = document.getElementById('smtp_host');
    const portInput = document.getElementById('smtp_port');
    const encryptionSelect = document.getElementById('smtp_encryption');

    // Show SMTP fields for all providers
    smtpFields.style.display = 'block';

    // Pre-fill fields based on provider
    const providerSettings = {
        'gmail': {
            host: 'smtp.gmail.com',
            port: 587,
            encryption: 'tls'
        },
        'outlook': {
            host: 'smtp.office365.com',
            port: 587,
            encryption: 'tls'
        },
        'sendgrid': {
            host: 'smtp.sendgrid.net',
            port: 587,
            encryption: 'tls'
        },
        'smtp': {
            host: '',
            port: 587,
            encryption: 'tls'
        }
    };

    if (providerSettings[provider]) {
        if (!hostInput.value || Object.values(providerSettings).some(s => s.host === hostInput.value)) {
            hostInput.value = providerSettings[provider].host;
        }
        if (!portInput.value || Object.values(providerSettings).some(s => s.port == portInput.value)) {
            portInput.value = providerSettings[provider].port;
        }
        encryptionSelect.value = providerSettings[provider].encryption;
    }
}

// Initialize on page load if a provider is already selected
document.addEventListener('DOMContentLoaded', function() {
    const selectedProvider = document.querySelector('input[name="email_provider"]:checked');
    if (selectedProvider) {
        updateProviderFields(selectedProvider.value);
    }
});
</script>
@endpush
