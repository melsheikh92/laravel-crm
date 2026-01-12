@extends('onboarding.steps.base')

@section('form-fields')
    {{-- Pipeline Name --}}
    <div>
        <label for="pipeline_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ config('onboarding.steps.pipeline_config.fields.pipeline_name.label') }}
        </label>
        <input
            type="text"
            name="pipeline_name"
            id="pipeline_name"
            value="{{ old('pipeline_name', $defaultData['pipeline_name'] ?? config('onboarding.steps.pipeline_config.fields.pipeline_name.default')) }}"
            placeholder="{{ config('onboarding.steps.pipeline_config.fields.pipeline_name.placeholder') }}"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm @error('pipeline_name') border-red-500 @enderror"
        >
        @if(config('onboarding.steps.pipeline_config.fields.pipeline_name.help'))
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ config('onboarding.steps.pipeline_config.fields.pipeline_name.help') }}
            </p>
        @endif
        @error('pipeline_name')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Pipeline Stages --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ config('onboarding.steps.pipeline_config.fields.stages.label') }}
        </label>
        @if(config('onboarding.steps.pipeline_config.fields.stages.help'))
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ config('onboarding.steps.pipeline_config.fields.stages.help') }}
            </p>
        @endif

        <div id="stages-container" class="mt-4 space-y-3">
            @php
                $oldStages = old('stages', $defaultData['stages'] ?? config('onboarding.steps.pipeline_config.default_stages', []));
            @endphp

            @foreach($oldStages as $index => $stage)
                <div class="stage-item rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800" data-index="{{ $index }}">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 cursor-move pt-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                            </svg>
                        </div>

                        <div class="flex-1 space-y-3">
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Stage Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="stages[{{ $index }}][name]"
                                        value="{{ $stage['name'] ?? '' }}"
                                        required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                                    >
                                </div>

                                <div class="w-32">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Probability (%) <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        name="stages[{{ $index }}][probability]"
                                        value="{{ $stage['probability'] ?? 0 }}"
                                        min="0"
                                        max="100"
                                        required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                                    >
                                </div>
                            </div>

                            <input type="hidden" name="stages[{{ $index }}][order]" value="{{ $stage['order'] ?? $index + 1 }}" class="stage-order">
                        </div>

                        <button
                            type="button"
                            onclick="removeStage(this)"
                            class="flex-shrink-0 rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        @error('stages')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @error('stages.*')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <button
            type="button"
            onclick="addStage()"
            class="mt-4 inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
        >
            <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Add Stage
        </button>
    </div>

    {{-- Info Box --}}
    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
        <div class="flex">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <p class="text-sm text-blue-800 dark:text-blue-400">
                    You can drag and drop stages to reorder them. Probability percentages help with revenue forecasting.
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let stageIndex = {{ count($oldStages) }};

function addStage() {
    const container = document.getElementById('stages-container');
    const stageHtml = `
        <div class="stage-item rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800" data-index="${stageIndex}">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 cursor-move pt-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                    </svg>
                </div>

                <div class="flex-1 space-y-3">
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Stage Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="stages[${stageIndex}][name]"
                                value=""
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                            >
                        </div>

                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Probability (%) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="stages[${stageIndex}][probability]"
                                value="0"
                                min="0"
                                max="100"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                            >
                        </div>
                    </div>

                    <input type="hidden" name="stages[${stageIndex}][order]" value="${stageIndex + 1}" class="stage-order">
                </div>

                <button
                    type="button"
                    onclick="removeStage(this)"
                    class="flex-shrink-0 rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', stageHtml);
    stageIndex++;
    updateStageOrders();
}

function removeStage(button) {
    const stageItem = button.closest('.stage-item');
    stageItem.remove();
    updateStageOrders();
}

function updateStageOrders() {
    const stages = document.querySelectorAll('.stage-item');
    stages.forEach((stage, index) => {
        const orderInput = stage.querySelector('.stage-order');
        if (orderInput) {
            orderInput.value = index + 1;
        }
    });
}

// Simple drag and drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('stages-container');
    let draggedElement = null;

    container.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('stage-item')) {
            draggedElement = e.target;
            e.target.style.opacity = '0.5';
        }
    });

    container.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('stage-item')) {
            e.target.style.opacity = '';
            updateStageOrders();
        }
    });

    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(container, e.clientY);
        if (afterElement == null) {
            container.appendChild(draggedElement);
        } else {
            container.insertBefore(draggedElement, afterElement);
        }
    });

    // Make stage items draggable
    container.querySelectorAll('.stage-item').forEach(item => {
        item.setAttribute('draggable', 'true');
    });
});

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.stage-item:not(.dragging)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}
</script>
@endpush
