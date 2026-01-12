/**
 * Onboarding Wizard JavaScript
 *
 * Handles form validation, AJAX submissions, step transitions,
 * and skip confirmations for the interactive onboarding wizard.
 */

document.addEventListener('DOMContentLoaded', function() {
    const wizardForm = document.getElementById('wizard-step-form');

    if (!wizardForm) {
        return; // Not on an onboarding page
    }

    // Initialize wizard functionality
    initializeFormValidation();
    initializeAjaxSubmission();
    initializeSkipConfirmation();
    initializeProgressTracking();
    initializeSaveBeforeLeave();
});

/**
 * Initialize client-side form validation
 */
function initializeFormValidation() {
    const form = document.getElementById('wizard-step-form');

    if (!form) return;

    // Add real-time validation on blur
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');

    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('border-red-500')) {
                validateField(this);
            }
        });
    });

    // Validate form before submission
    form.addEventListener('submit', function(e) {
        let isValid = true;

        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            showNotification('Please fix the validation errors before continuing.', 'error');
        }
    });
}

/**
 * Validate a single field
 */
function validateField(field) {
    const errorElement = field.parentElement.querySelector('.error-message');
    let isValid = true;
    let errorMessage = '';

    // Clear previous error styling
    field.classList.remove('border-red-500', 'dark:border-red-500');
    if (errorElement) {
        errorElement.remove();
    }

    // Check if field is required and empty
    if (field.hasAttribute('required') && !field.value.trim()) {
        isValid = false;
        errorMessage = 'This field is required.';
    }

    // Email validation
    if (field.type === 'email' && field.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        }
    }

    // URL validation
    if (field.type === 'url' && field.value.trim()) {
        try {
            new URL(field.value);
        } catch {
            isValid = false;
            errorMessage = 'Please enter a valid URL.';
        }
    }

    // Number validation
    if (field.type === 'number' && field.value.trim()) {
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        const value = parseFloat(field.value);

        if (min !== null && value < parseFloat(min)) {
            isValid = false;
            errorMessage = `Value must be at least ${min}.`;
        }
        if (max !== null && value > parseFloat(max)) {
            isValid = false;
            errorMessage = `Value must be at most ${max}.`;
        }
    }

    // Display error if invalid
    if (!isValid) {
        field.classList.add('border-red-500', 'dark:border-red-500');
        const error = document.createElement('p');
        error.className = 'mt-1 text-sm text-red-600 dark:text-red-400 error-message';
        error.textContent = errorMessage;
        field.parentElement.appendChild(error);
    }

    return isValid;
}

/**
 * Initialize AJAX form submission with loading states
 */
function initializeAjaxSubmission() {
    const form = document.getElementById('wizard-step-form');
    const submitButton = form ? form.querySelector('button[type="submit"]') : null;

    if (!form || !submitButton) return;

    const useAjax = form.dataset.useAjax === 'true';

    if (!useAjax) {
        // Even for standard form submission, add loading state
        form.addEventListener('submit', function() {
            if (window.setButtonLoadingState) {
                window.setButtonLoadingState(submitButton, true);
            }
        });
        return;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Show enhanced loading overlay
        const overlay = window.showLoadingOverlay ? window.showLoadingOverlay('Processing your data...') : null;

        // Set button loading state
        if (window.setButtonLoadingState) {
            window.setButtonLoadingState(submitButton, true);
        }

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            // Hide loading overlay
            if (window.hideLoadingOverlay) {
                window.hideLoadingOverlay();
            }

            if (data.success) {
                // Show success animation
                if (window.showSuccessAnimation) {
                    window.showSuccessAnimation(data.message || 'Step completed successfully!', () => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    });
                } else {
                    showNotification(data.message || 'Step completed successfully!', 'success');

                    // Redirect to next step or completion page
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 500);
                    }
                }
            } else {
                showNotification(data.message || 'An error occurred. Please try again.', 'error');

                // Display validation errors with shake animation
                if (data.errors) {
                    displayValidationErrors(data.errors);

                    // Scroll to first error
                    if (window.scrollToFirstError) {
                        window.scrollToFirstError();
                    }
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);

            // Hide loading overlay
            if (window.hideLoadingOverlay) {
                window.hideLoadingOverlay();
            }

            showNotification('An unexpected error occurred. Please try again.', 'error');
        } finally {
            // Restore button state
            if (window.setButtonLoadingState) {
                window.setButtonLoadingState(submitButton, false);
            } else {
                submitButton.disabled = false;
            }
        }
    });
}

/**
 * Display validation errors from server
 */
function displayValidationErrors(errors) {
    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.add('border-red-500', 'dark:border-red-500');

            // Add shake animation
            if (window.showErrorAnimation) {
                window.showErrorAnimation(field);
            } else {
                field.classList.add('onboarding-shake');
                setTimeout(() => field.classList.remove('onboarding-shake'), 500);
            }

            const errorElement = field.parentElement.querySelector('.error-message');
            if (errorElement) {
                errorElement.remove();
            }

            const error = document.createElement('p');
            error.className = 'mt-1 text-sm text-red-600 dark:text-red-400 error-message onboarding-fade-in';
            error.textContent = Array.isArray(errors[fieldName]) ? errors[fieldName][0] : errors[fieldName];
            field.parentElement.appendChild(error);
        }
    });
}

/**
 * Initialize skip confirmation dialog
 */
function initializeSkipConfirmation() {
    const skipButtons = document.querySelectorAll('form[action*="/skip"] button[type="submit"]');

    skipButtons.forEach(button => {
        const form = button.closest('form');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            showConfirmDialog(
                'Skip this step?',
                'You can always come back and complete this step later from your settings.',
                'Skip',
                'Cancel'
            ).then(confirmed => {
                if (confirmed) {
                    // Submit the skip form
                    form.submit();
                }
            });
        });
    });
}

/**
 * Initialize progress tracking
 */
function initializeProgressTracking() {
    // Update progress indicator periodically
    const progressIndicator = document.querySelector('[data-progress-tracker]');

    if (!progressIndicator) return;

    // Poll for progress updates every 30 seconds
    setInterval(async () => {
        try {
            const response = await fetch('/api/onboarding/progress', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                updateProgressDisplay(data.data);
            }
        } catch (error) {
            console.error('Failed to fetch progress:', error);
        }
    }, 30000);
}

/**
 * Update progress display
 */
function updateProgressDisplay(progressData) {
    const percentageElement = document.querySelector('[data-progress-percentage]');
    const completedCountElement = document.querySelector('[data-completed-count]');

    if (percentageElement) {
        percentageElement.textContent = Math.round(progressData.percentage) + '%';
    }

    if (completedCountElement) {
        completedCountElement.textContent = `${progressData.completed_count} of ${progressData.total_steps} completed`;
    }
}

/**
 * Initialize save before leaving warning
 */
function initializeSaveBeforeLeave() {
    const form = document.getElementById('wizard-step-form');

    if (!form) return;

    let formModified = false;

    // Track form changes
    form.addEventListener('input', function() {
        formModified = true;
    });

    // Reset flag on successful submission
    form.addEventListener('submit', function() {
        formModified = false;
    });

    // Warn before leaving
    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            const message = 'You have unsaved changes. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Use enhanced notification if available
    if (window.showNotificationEnhanced) {
        return window.showNotificationEnhanced(message, type);
    }

    // Fallback to basic notification
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 max-w-sm rounded-lg shadow-lg p-4 transition-all duration-300 transform onboarding-notification-slide-in ${
        type === 'success' ? 'bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-400' :
        type === 'error' ? 'bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-400' :
        'bg-blue-50 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
    }`;

    const icon = type === 'success' ?
        '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' :
        type === 'error' ?
        '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>' :
        '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';

    notification.innerHTML = `
        <div class="flex items-start">
            ${icon}
            <p class="ml-3 text-sm font-medium">${message}</p>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex h-8 w-8 hover:bg-gray-100 dark:hover:bg-gray-700 onboarding-transition" onclick="this.parentElement.parentElement.remove()">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

/**
 * Show confirmation dialog
 */
function showConfirmDialog(title, message, confirmText = 'Confirm', cancelText = 'Cancel') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';

        overlay.innerHTML = `
            <div class="max-w-md w-full mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">${title}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">${message}</p>
                <div class="flex justify-end gap-3">
                    <button type="button" class="cancel-btn px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        ${cancelText}
                    </button>
                    <button type="button" class="confirm-btn px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        ${confirmText}
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const cancelBtn = overlay.querySelector('.cancel-btn');
        const confirmBtn = overlay.querySelector('.confirm-btn');

        cancelBtn.addEventListener('click', () => {
            overlay.remove();
            resolve(false);
        });

        confirmBtn.addEventListener('click', () => {
            overlay.remove();
            resolve(true);
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.remove();
                resolve(false);
            }
        });
    });
}

/**
 * AJAX Helper for API calls
 */
window.onboardingApi = {
    async validateStep(step, data) {
        try {
            const formData = new FormData();
            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });

            const response = await fetch(`/api/onboarding/step/${step}/validate`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            return await response.json();
        } catch (error) {
            console.error('Validation error:', error);
            return { success: false, message: 'Validation failed' };
        }
    },

    async getProgress() {
        try {
            const response = await fetch('/api/onboarding/progress', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            return await response.json();
        } catch (error) {
            console.error('Progress fetch error:', error);
            return { success: false, message: 'Failed to fetch progress' };
        }
    },
};
