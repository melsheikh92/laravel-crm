<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-gray-50 dark:bg-gray-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Setup Complete - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/onboarding.css', 'resources/js/onboarding.js', 'resources/js/onboarding-animations.js'])
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
                </div>
            @endif

            <!-- Next Steps -->
            <div
                class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    What's next?
                </h2>
                <div class="mt-4 space-y-3">
                    <a href="{{ config('onboarding.completion.redirect_to', '/admin/dashboard') }}"
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

                    <a href="https://webkul.com/crm/docs" target="_blank"
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

                    <a href="{{ route('admin.settings.users.index') }}"
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
        </div>
    </div>
</body>

</html>