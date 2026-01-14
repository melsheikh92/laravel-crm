@props(['allSteps', 'currentStep', 'progressSummary'])

<div class="mb-8 p-4 bg-white rounded-lg shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Setup Progress</h3>
        <span class="text-sm font-bold text-blue-600 dark:text-blue-400" data-progress-percentage>
            {{ $progressSummary['percentage'] ?? 0 }}%
        </span>
    </div>

    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-6">
        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500 ease-out"
            style="width: {{ $progressSummary['percentage'] ?? 0 }}%">
        </div>
    </div>

    <div class="space-y-4">
        @foreach($allSteps as $stepKey)
            @php
                $stepConfig = config("onboarding.steps.{$stepKey}");
                $isCompleted = in_array($stepKey, $progressSummary['completed_steps'] ?? []);
                $isCurrent = $stepKey === $currentStep;
                $isSkipped = false; // logic for skipped if needed
            @endphp

            <a href="{{ $isCompleted || $isCurrent ? route('onboarding.step', $stepKey) : '#' }}"
                class="flex items-center group {{ ($isCompleted || $isCurrent) ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}">

                <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full border-2 mr-3
                        {{ $isCompleted ? 'bg-green-100 border-green-500 text-green-600 dark:bg-green-900/30 dark:text-green-400' : '' }}
                        {{ $isCurrent ? 'bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                        {{ !$isCompleted && !$isCurrent ? 'bg-white border-gray-300 text-gray-400 dark:bg-gray-800 dark:border-gray-600' : '' }}
                    ">
                    @if($isCompleted)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <span class="text-xs font-semibold">
                            <!-- Index logic if needed, or just icon -->
                            {{ array_search($stepKey, $allSteps) + 1 }}
                        </span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <p
                        class="text-sm font-medium {{ $isCurrent ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $stepConfig['title'] }}
                    </p>
                    <p class="text-xs text-gray-500 truncate dark:text-gray-400">
                        {{ $stepConfig['description'] }}
                    </p>
                </div>

                @if($isCurrent)
                    <div class="ml-2">
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                            Current
                        </span>
                    </div>
                @endif
            </a>
        @endforeach
    </div>
</div>