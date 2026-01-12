<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-gray-50 dark:bg-gray-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Setup Complete - {{ config('app.name') }}</title>


</head>

<body class="h-full font-sans antialiased">
    <div class="flex min-h-full flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-2xl">
            <!-- Success Icon -->
            <div class="text-center">
                <div
                    class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <svg class="h-16 w-16 text-green-600 dark:text-green-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="mt-6 text-4xl font-bold text-gray-900 dark:text-white">
                    🎉 Setup Complete!
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Congratulations! Your CRM is now ready to use. Let's get started with your sales journey.
                </p>
            </div>

            <!-- Completion Summary -->
            @if(isset($progress))
                <div
                    class="mt-10 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        What you've accomplished:
                    </h2>
                    <div class="mt-4 grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-500">
                                {{ $progress->getCompletedStepsCount() }}
                            </div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Steps Completed
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-600 dark:text-gray-400">
                                {{ $progress->getSkippedStepsCount() }}
                            </div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Steps Skipped
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600 dark:text-green-500">
                                {{ $progress->getDurationInMinutes() }}
                            </div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Minutes Spent
                            </div>
                        </div>
                    </div>

                    <!-- Completed Steps List -->
                    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Configured:
                        </h3>
                        <ul class="mt-3 space-y-2">
                            @foreach($progress->completed_steps ?? [] as $stepId)
                                @php
                                    $stepConfig = config("onboarding.steps.{$stepId}");
                                @endphp
                                @if($stepConfig)
                                    <li class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-500" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $stepConfig['title'] }}
                                        </span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Next Steps -->
            <div
                class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    What's next?
                </h2>
                <div class="mt-4 space-y-3">
                    <a href="{{ config('onboarding.completion.redirect_to', '/') }}"
                        class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                        <div
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <span class="text-xl">🏠</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                Go to Dashboard
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Start managing your leads, contacts, and deals
                            </p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="#"
                        class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                        <div
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                            <span class="text-xl">📚</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                Explore Documentation
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Learn about advanced features and best practices
                            </p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="#"
                        class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                        <div
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                            <span class="text-xl">👥</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                Invite More Team Members
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Add your team and start collaborating
                            </p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Help Resources -->
            <div class="mt-6 rounded-lg bg-blue-50 p-6 dark:bg-blue-900/20">
                <div class="flex items-start gap-3">
                    <svg class="h-6 w-6 flex-shrink-0 text-blue-600 dark:text-blue-500" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-medium text-blue-900 dark:text-blue-400">
                            Need Help?
                        </h3>
                        <p class="mt-1 text-sm text-blue-800 dark:text-blue-500">
                            Our support team is here to help you succeed. Contact us anytime or visit our help center
                            for guides and tutorials.
                        </p>
                        <div class="mt-3 flex gap-3">
                            <a href="#"
                                class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                                Contact Support →
                            </a>
                            <a href="#"
                                class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                                Help Center →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main CTA -->
            <div class="mt-8 text-center">
                <a href="{{ config('onboarding.completion.redirect_to', '/') }}"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-8 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600">
                    Start Using {{ config('app.name') }}
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                @if(config('onboarding.allow_restart'))
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Want to change something?
                        <a href="{{ route('onboarding.restart') }}"
                            onclick="return confirm('Are you sure you want to restart the setup wizard? Your current configuration will remain, but you can update settings.')"
                            class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-500 dark:hover:text-blue-400">
                            Restart the wizard
                        </a>
                    </p>
                @endif
            </div>
        </div>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/onboarding.css', 'resources/js/onboarding.js', 'resources/js/onboarding-animations.js'])

    <script>
        // Create enhanced confetti animation on page load
        document.addEventListener('DOMContentLoaded', function () {
            // Add fade-in animation to main content
            const mainContent = document.querySelector('.w-full.max-w-2xl');
            if (mainContent) {
                mainContent.classList.add('onboarding-fade-in');
            }

            // Add success pulse to icon
            const successIcon = document.querySelector('.rounded-full.bg-green-100');
            if (successIcon) {
                successIcon.classList.add('onboarding-success-pulse');
            }

            // Create confetti using enhanced animation
            setTimeout(() => {
                const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722'];
                const confettiCount = 50;

                for (let i = 0; i < confettiCount; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'onboarding-confetti';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDelay = Math.random() * 0.5 + 's';
                    confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                    confetti.style.top = '-10px';

                    document.body.appendChild(confetti);

                    // Remove confetti after animation
                    setTimeout(() => confetti.remove(), 5000);
                }
            }, 200); // Slight delay for better effect

            // Animate counter numbers
            const counters = document.querySelectorAll('.text-3xl.font-bold');
            counters.forEach((counter, index) => {
                const target = parseInt(counter.textContent);
                if (!isNaN(target)) {
                    counter.textContent = '0';
                    setTimeout(() => {
                        if (window.animateCounter) {
                            window.animateCounter(counter, 0, target, 1000);
                        }
                    }, 300 + (index * 100));
                }
            });

            // Add celebrate animation to title
            const title = document.querySelector('.text-4xl.font-bold');
            if (title) {
                setTimeout(() => {
                    title.classList.add('onboarding-celebrate');
                }, 500);
            }
        });
    </script>
</body>

</html>