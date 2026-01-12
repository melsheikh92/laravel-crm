@extends('onboarding.steps.base')

@section('form-fields')
    {{-- Full Name --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ config('onboarding.steps.user_creation.fields.name.label') }}
            <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $defaultData['name'] ?? '') }}"
            placeholder="{{ config('onboarding.steps.user_creation.fields.name.placeholder') }}"
            required
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('name') border-red-500 @enderror"
        >
        @if(config('onboarding.steps.user_creation.fields.name.help'))
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ config('onboarding.steps.user_creation.fields.name.help') }}
            </p>
        @endif
        @error('name')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email Address --}}
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ config('onboarding.steps.user_creation.fields.email.label') }}
            <span class="text-red-500">*</span>
        </label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email', $defaultData['email'] ?? '') }}"
            placeholder="{{ config('onboarding.steps.user_creation.fields.email.placeholder') }}"
            required
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('email') border-red-500 @enderror"
        >
        @if(config('onboarding.steps.user_creation.fields.email.help'))
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ config('onboarding.steps.user_creation.fields.email.help') }}
            </p>
        @endif
        @error('email')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Role --}}
    <div>
        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ config('onboarding.steps.user_creation.fields.role.label') }}
        </label>
        <select
            name="role"
            id="role"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('role') border-red-500 @enderror"
        >
            <option value="">Select a role...</option>
            @foreach(config('onboarding.steps.user_creation.fields.role.options', []) as $value => $label)
                <option value="{{ $value }}" {{ old('role', $defaultData['role'] ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @if(config('onboarding.steps.user_creation.fields.role.help'))
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ config('onboarding.steps.user_creation.fields.role.help') }}
            </p>
        @endif
        @error('role')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Send Invitation Email --}}
    <div class="flex items-start">
        <div class="flex h-5 items-center">
            <input
                type="checkbox"
                name="send_invitation"
                id="send_invitation"
                value="1"
                {{ old('send_invitation', $defaultData['send_invitation'] ?? config('onboarding.steps.user_creation.fields.send_invitation.default')) ? 'checked' : '' }}
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 @error('send_invitation') border-red-500 @enderror"
            >
        </div>
        <div class="ml-3 text-sm">
            <label for="send_invitation" class="font-medium text-gray-700 dark:text-gray-300">
                {{ config('onboarding.steps.user_creation.fields.send_invitation.label') }}
            </label>
            @if(config('onboarding.steps.user_creation.fields.send_invitation.help'))
                <p class="text-gray-500 dark:text-gray-400">
                    {{ config('onboarding.steps.user_creation.fields.send_invitation.help') }}
                </p>
            @endif
            @error('send_invitation')
                <p class="mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Info Box --}}
    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
        <div class="flex">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <p class="text-sm text-blue-800 dark:text-blue-400">
                    A secure password will be automatically generated for this user. They will receive an email with instructions to set their own password.
                </p>
            </div>
        </div>
    </div>
@endsection
