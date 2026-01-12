<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-gray-50 dark:bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome to {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">
    <div class="flex min-h-full flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-2xl">
            <!-- Logo/Icon -->
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                    <span class="text-5xl">🚀</span>
                </div>
                <h1 class="mt-6 text-4xl font-bold text-gray-900 dark:text-white">
                    Welcome to {{ config('app.name') }}!
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Let's get you set up in just a few minutes. We'll guide you through the essential configuration steps.
                </p>
            </div>

            <!-- What to expect -->
            <div class="mt-10 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    What to expect:
                </h2>
                <div class="mt-4 space-y-4">
                    @foreach(config('onboarding.steps') as $stepId => $stepConfig)
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <span class="text-xl">
                                    @switch($stepConfig['icon'])
                                        @case('building') 🏢 @break
                                        @case('users') 👥 @break
                                        @case('filter') 📊 @break
                                        @case('envelope') ✉️ @break
                                        @case('database') 💾 @break
                                        @default 📋
                                    @endswitch
                                </span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">
                                    {{ $stepConfig['title'] }}
                                    @if($stepConfig['skippable'])
                                        <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            Optional
                                        </span>
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $stepConfig['description'] }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    ⏱️ About {{ $stepConfig['estimated_minutes'] }} minutes
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Features highlight -->
            <div class="mt-6 grid grid-cols-3 gap-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 text-center dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-2xl">⚡</div>
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Quick Setup</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">~15 minutes total</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 text-center dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-2xl">💾</div>
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Auto-Save</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Progress saved automatically</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 text-center dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-2xl">🎯</div>
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Flexible</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Skip optional steps</p>
                </div>
            </div>

            <!-- Start button -->
            <div class="mt-8 text-center">
                <a href="{{ route('onboarding.show', array_key_first(config('onboarding.steps'))) }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-8 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600">
                    Get Started
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    Already started?
                    @if(isset($progress) && $progress->current_step)
                        <a href="{{ route('onboarding.show', $progress->current_step) }}"
                           class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                            Resume from "{{ config("onboarding.steps.{$progress->current_step}.title") }}"
                        </a>
                    @endif
                </p>
            </div>
        </div>
    </div>
</body>
</html>
