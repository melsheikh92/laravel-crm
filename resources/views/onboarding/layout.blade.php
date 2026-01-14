<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-gray-50 dark:bg-gray-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Onboarding Wizard' }} - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/onboarding.css'])
</head>

<body class="h-full font-sans antialiased">
    <div class="flex min-h-full flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ config('app.name') }} Setup Wizard
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Let's get your CRM configured in just a few steps
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(isset($progressSummary))
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $progressSummary['completed_count'] }} of {{ $progressSummary['total_steps'] }} completed
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex gap-6">
                    <!-- Main Wizard Area (2/3 width) -->
                    <div class="flex-1">
                        <!-- Progress Indicator Component -->
                        <x-onboarding.progress-indicator :allSteps="$allSteps ?? []" :currentStep="$step ?? null"
                            :progressSummary="$progressSummary ?? []" />

                        <!-- Step Container -->
                        <div
                            class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        @if(isset($stepConfig))
                                            <div class="flex items-center gap-3">
                                                @if(isset($stepConfig['icon']))
                                                    <div
                                                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                                        <span class="text-2xl">
                                                            @match($stepConfig['icon'])
                                                            'building' => 🏢,
                                                            'users' => 👥,
                                                            'filter' => 📊,
                                                            'envelope' => ✉️,
                                                            'database' => 💾,
                                                            default => 📋
                                                            @endmatch
                                                        </span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                                        {{ $stepConfig['title'] }}
                                                    </h2>
                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $stepConfig['description'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @if(isset($stepConfig['estimated_minutes']))
                                        <div class="ml-4 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>~{{ $stepConfig['estimated_minutes'] }} min</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Step Content -->
                            <div class="p-6">
                                @if(session('success'))
                                    <div class="mb-6 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                        <div class="flex">
                                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-400">
                                                {{ session('success') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="mb-6 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                                        <div class="flex">
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <p class="ml-3 text-sm font-medium text-red-800 dark:text-red-400">
                                                {{ session('error') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @yield('step-content')
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <div>
                                        @if(isset($stepDetails) && !$stepDetails['is_first'])
                                            <form action="{{ route('onboarding.previous') }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                    Previous
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        @if(config('onboarding.allow_skip') && ($stepConfig['allow_skip'] ?? true))
                                            <form action="{{ route('onboarding.step.skip', $step) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                    Skip
                                                </button>
                                            </form>
                                        @endif

                                        <button type="submit" form="wizard-step-form"
                                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600">
                                            Continue
                                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar (1/3 width) -->
                    <div class="w-80 flex-shrink-0">
                        <!-- Helper/Context Sidebar -->
                        <div class="sticky top-8 space-y-6">
                            <!-- Support Card -->
                            <div
                                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <h3 class="flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                    <svg class="mr-2 h-5 w-5 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Need Help?
                                </h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $stepConfig['help_text'] ?? 'Our support team is standing by to help you get set up correctly.' }}
                                </p>

                                <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <h4
                                        class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Resources</h4>
                                    <ul class="mt-2 space-y-2">
                                        <li>
                                            <a href="#"
                                                class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.414l5 5a1 1 0 01.414 1.414V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Documentation
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white py-4 shadow-inner dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    @if(config('onboarding.allow_skip'))
                        <span class="mx-2 text-gray-300 dark:text-gray-600">|</span>
                        <a href="{{ route('onboarding.skip', $step) }}"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            Skip setup for now
                        </a>
                    @endif
                </p>
            </div>
        </footer>
    </div>

    <!-- Onboarding Scripts -->
    @vite(['resources/js/onboarding.js', 'resources/js/onboarding-animations.js'])

    @stack('scripts')
</body>

</html>