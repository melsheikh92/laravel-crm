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
                        <x-onboarding.progress-indicator
                            :allSteps="$allSteps ?? []"
                            :currentStep="$step ?? null"
                            :progressSummary="$progressSummary ?? []"
                        />

                        <!-- Step Container -->
                        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        @if(isset($stepConfig))
                                            <div class="flex items-center gap-3">
                                                @if(isset($stepConfig['icon']))
                                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                                        <span class="text-2xl">
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
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
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                    </svg>
                                                    Previous
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        @if(isset($stepConfig) && $stepConfig['skippable'] && config('onboarding.allow_skip'))
                                            <form action="{{ route('onboarding.step.skip', $step) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                    Skip
                                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="submit" form="wizard-step-form" class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600">
                                            @if(isset($stepDetails) && $stepDetails['is_last'])
                                                Complete Setup
                                            @else
                                                Continue
                                            @endif
                                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Sidebar (1/3 width) -->
                    <aside class="w-80 flex-shrink-0">
                        <div class="sticky top-8 space-y-6">
                            {{-- Video Tutorial (if available) --}}
                            @if(isset($stepConfig['video_url']) && $stepConfig['video_url'] && config('onboarding.ui.show_video_tutorials'))
                                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                    <div class="mb-3 flex items-center gap-2 text-blue-600 dark:text-blue-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <h3 class="text-sm font-semibold">Video Tutorial</h3>
                                    </div>
                                    <x-onboarding.video-embed
                                        :url="$stepConfig['video_url']"
                                        :title="$stepConfig['title'] . ' Tutorial'"
                                        :thumbnail="$stepConfig['video_thumbnail'] ?? null"
                                    />
                                </div>
                            @endif

                            {{-- Help Information --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-500">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                    </svg>
                                    <h3 class="text-lg font-semibold">Need Help?</h3>
                                </div>

                                @if(isset($stepConfig['help_text']))
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $stepConfig['help_text'] }}
                                        </p>
                                    </div>
                                @endif

                            @if(isset($stepConfig['help_tips']) && count($stepConfig['help_tips']) > 0)
                                <div class="mt-6">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Quick Tips:
                                    </h4>
                                    <ul class="mt-3 space-y-2">
                                        @foreach($stepConfig['help_tips'] as $tip)
                                            <li class="flex items-start gap-2">
                                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-600 dark:text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $tip }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Resources
                                </h4>
                                <ul class="mt-3 space-y-2">
                                    <li>
                                        <a href="#" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                            Documentation
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Video Tutorial
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                            Get Support
                                        </a>
                                    </li>
                                </ul>
                            </div>

                                <div class="mt-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                                    <p class="text-xs text-blue-800 dark:text-blue-400">
                                        💡 <strong>Pro Tip:</strong> You can always restart this wizard later from your settings menu.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white shadow-sm dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    Need help? Contact support or
                    @if(config('onboarding.allow_restart'))
                        <a href="#" class="text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                            restart the wizard
                        </a>
                    @endif
                </p>
            </div>
        </footer>
    </div>

    <!-- Onboarding Scripts -->
    @vite(['resources/js/onboarding.js', 'resources/js/onboarding-animations.js'])

    @stack('scripts')

    <!-- Initialize animations on page load -->
    <script>
        // Add data attributes for animations
        document.addEventListener('DOMContentLoaded', function() {
            // Mark step container for transitions
            const stepContainer = document.querySelector('.rounded-lg.border.border-gray-200');
            if (stepContainer) {
                stepContainer.setAttribute('data-step-container', 'true');
            }

            // Animate flash messages
            const successMessage = document.querySelector('.bg-green-50');
            const errorMessage = document.querySelector('.bg-red-50');

            if (successMessage) {
                successMessage.classList.add('onboarding-fade-in');
            }

            if (errorMessage) {
                errorMessage.classList.add('onboarding-fade-in', 'onboarding-shake');
            }

            // Scroll to first error if present
            if (errorMessage) {
                setTimeout(() => {
                    window.smoothScrollTo && window.smoothScrollTo(errorMessage, 100);
                }, 100);
            }
        });
    </script>
</body>
</html>
