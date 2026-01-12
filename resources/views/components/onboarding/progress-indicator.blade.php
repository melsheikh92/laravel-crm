{{--
    Onboarding Progress Indicator Component

    Displays a visual progress bar/stepper showing completed, current, and upcoming steps
    in the onboarding wizard process.

    @props array $allSteps - Array of step IDs in order
    @props string|null $currentStep - The current active step ID
    @props array $progressSummary - Progress summary with completed_steps, skipped_steps, percentage, etc.

    Features:
    - Horizontal stepper with step numbers/icons
    - Visual states: completed (✓), current (highlighted), skipped (✗), pending
    - Connecting lines between steps
    - Animated progress bar with percentage
    - Duration tracking display
    - Accessible with ARIA labels
    - Responsive design with dark mode support
    - Smooth transitions and hover effects

    Usage:
    <x-onboarding.progress-indicator
        :allSteps="['company_setup', 'user_creation', 'pipeline_config']"
        :currentStep="'user_creation'"
        :progressSummary="[
            'completed_steps' => ['company_setup'],
            'skipped_steps' => [],
            'percentage' => 33.3,
            'duration_minutes' => 5
        ]"
    />
--}}

@props([
    'allSteps' => [],
    'currentStep' => null,
    'progressSummary' => [],
])

<div class="mb-8">
    @if(!empty($allSteps) && $currentStep)
        <!-- Progress Stepper -->
        <nav aria-label="Progress">
            <ol role="list" class="flex items-center">
                @foreach($allSteps as $index => $stepId)
                    @php
                        $stepConfig = config("onboarding.steps.{$stepId}");
                        $stepNumber = $index + 1;
                        $isCurrent = $stepId === $currentStep;
                        $isCompleted = isset($progressSummary['completed_steps']) && in_array($stepId, $progressSummary['completed_steps']);
                        $isSkipped = isset($progressSummary['skipped_steps']) && in_array($stepId, $progressSummary['skipped_steps']);
                    @endphp
                    <li class="relative flex-1 {{ $index > 0 ? 'pl-8' : '' }}">
                        @if($index > 0)
                            <!-- Connector Line -->
                            <div class="absolute top-5 left-0 h-0.5 w-full -z-10 transition-colors duration-300
                                {{ ($isCompleted || $isSkipped) ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                            </div>
                        @endif

                        <a href="{{ route('onboarding.step', $stepId) }}"
                           class="group flex flex-col items-center"
                           aria-label="{{ $stepConfig['title'] ?? 'Step ' . $stepNumber }}"
                           aria-current="{{ $isCurrent ? 'step' : 'false' }}">
                            <!-- Step Circle -->
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all duration-300
                                {{ $isCurrent ? 'border-blue-600 bg-blue-600 dark:border-blue-500 dark:bg-blue-500 shadow-lg scale-110' : '' }}
                                {{ $isCompleted && !$isCurrent ? 'border-blue-600 bg-blue-600 dark:border-blue-500 dark:bg-blue-500' : '' }}
                                {{ $isSkipped && !$isCurrent ? 'border-gray-400 bg-gray-400 dark:border-gray-500 dark:bg-gray-500' : '' }}
                                {{ !$isCurrent && !$isCompleted && !$isSkipped ? 'border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800' : '' }}
                                group-hover:border-blue-500 group-hover:shadow-md">
                                @if($isCompleted && !$isCurrent)
                                    <!-- Checkmark Icon -->
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($isSkipped && !$isCurrent)
                                    <!-- Skip/X Icon -->
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <!-- Step Number -->
                                    <span class="text-sm font-medium transition-colors duration-300
                                        {{ $isCurrent ? 'text-white' : 'text-gray-500 dark:text-gray-400 group-hover:text-blue-600' }}">
                                        {{ $stepNumber }}
                                    </span>
                                @endif
                            </span>

                            <!-- Step Label -->
                            <span class="mt-2 text-xs font-medium text-center transition-colors duration-300
                                {{ $isCurrent ? 'text-blue-600 dark:text-blue-500' : 'text-gray-500 dark:text-gray-400 group-hover:text-blue-600' }}">
                                {{ $stepConfig['short_title'] ?? $stepConfig['title'] ?? 'Step ' . $stepNumber }}
                            </span>

                            <!-- Status Badge (for screen readers) -->
                            <span class="sr-only">
                                @if($isCurrent)
                                    (Current Step)
                                @elseif($isCompleted)
                                    (Completed)
                                @elseif($isSkipped)
                                    (Skipped)
                                @else
                                    (Upcoming)
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>

        <!-- Progress Bar -->
        <div class="mt-6">
            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                <div class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500 ease-out dark:from-blue-400 dark:to-blue-500"
                     style="width: {{ $progressSummary['percentage'] ?? 0 }}%"
                     role="progressbar"
                     aria-valuenow="{{ round($progressSummary['percentage'] ?? 0) }}"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     aria-label="Overall progress">
                </div>
            </div>
            <div class="mt-2 flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold">{{ round($progressSummary['percentage'] ?? 0) }}%</span> complete
                </p>
                @if(isset($progressSummary['duration_minutes']) && $progressSummary['duration_minutes'] > 0)
                    <p class="text-sm text-gray-500 dark:text-gray-500">
                        <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $progressSummary['duration_minutes'] }} min elapsed
                    </p>
                @endif
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                No progress information available
            </p>
        </div>
    @endif
</div>
