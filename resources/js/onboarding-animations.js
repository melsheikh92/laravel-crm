/**
 * Onboarding Wizard Animations and Visual Effects
 *
 * Handles success animations, loading states, error feedback,
 * and smooth transitions for the onboarding wizard.
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeOnboardingAnimations();
});

/**
 * Initialize all onboarding animations
 */
function initializeOnboardingAnimations() {
    animatePageEntry();
    enhanceFormInputs();
    enhanceButtons();
    initializeStepTransitions();
}

/**
 * Animate page entry with fade-in effect
 */
function animatePageEntry() {
    // Add fade-in class to main content areas
    const mainContent = document.querySelector('main');
    if (mainContent) {
        mainContent.classList.add('onboarding-fade-in');
    }

    // Stagger animations for step items
    const stepItems = document.querySelectorAll('[data-step-item]');
    stepItems.forEach((item, index) => {
        item.classList.add(`onboarding-fade-in-delay-${Math.min(index + 1, 3)}`);
    });
}

/**
 * Enhance form inputs with smooth transitions
 */
function enhanceFormInputs() {
    const inputs = document.querySelectorAll('input, textarea, select');

    inputs.forEach(input => {
        // Add transition classes
        input.classList.add('onboarding-transition');

        // Add focus animations
        input.addEventListener('focus', function() {
            this.classList.add('onboarding-focus-ring');
        });

        input.addEventListener('blur', function() {
            this.classList.remove('onboarding-focus-ring');
        });

        // Add error shake animation
        input.addEventListener('invalid', function(e) {
            e.preventDefault();
            this.classList.add('onboarding-shake');
            setTimeout(() => {
                this.classList.remove('onboarding-shake');
            }, 500);
        });
    });
}

/**
 * Enhance buttons with ripple effect and loading states
 */
function enhanceButtons() {
    const buttons = document.querySelectorAll('button[type="submit"], .onboarding-button');

    buttons.forEach(button => {
        button.classList.add('onboarding-button');

        // Add click ripple effect
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('absolute', 'rounded-full', 'bg-white', 'opacity-50', 'animate-ping');

            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });
}

/**
 * Initialize step transition animations
 */
function initializeStepTransitions() {
    const stepContainer = document.querySelector('[data-step-container]');
    if (stepContainer) {
        stepContainer.classList.add('onboarding-slide-in');
    }
}

/**
 * Show loading overlay
 */
window.showLoadingOverlay = function(message = 'Processing...') {
    // Remove existing overlay if any
    const existingOverlay = document.querySelector('.onboarding-loading-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }

    const overlay = document.createElement('div');
    overlay.className = 'onboarding-loading-overlay';
    overlay.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 text-center">
            <div class="flex justify-center mb-4">
                <svg class="onboarding-spinner h-12 w-12 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <p class="text-lg font-medium text-gray-900 dark:text-white">${message}</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please wait...</p>
        </div>
    `;

    document.body.appendChild(overlay);
    return overlay;
};

/**
 * Hide loading overlay
 */
window.hideLoadingOverlay = function() {
    const overlay = document.querySelector('.onboarding-loading-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
    }
};

/**
 * Show success animation
 */
window.showSuccessAnimation = function(message = 'Success!', callback = null) {
    const overlay = document.createElement('div');
    overlay.className = 'onboarding-loading-overlay';
    overlay.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 text-center onboarding-success-pulse">
            <div class="flex justify-center mb-4">
                <div class="h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-500 onboarding-checkmark" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-dasharray: 100; stroke-dashoffset: 100;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <p class="text-xl font-bold text-gray-900 dark:text-white">${message}</p>
        </div>
    `;

    document.body.appendChild(overlay);

    // Trigger confetti
    createConfetti();

    // Auto-hide and execute callback
    setTimeout(() => {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.remove();
            if (callback) callback();
        }, 300);
    }, 2000);
};

/**
 * Create confetti animation
 */
function createConfetti() {
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

        // Remove after animation
        setTimeout(() => confetti.remove(), 5000);
    }
}

/**
 * Show error animation with shake effect
 */
window.showErrorAnimation = function(element, message = null) {
    if (element) {
        element.classList.add('onboarding-shake', 'onboarding-error-pulse');

        setTimeout(() => {
            element.classList.remove('onboarding-shake', 'onboarding-error-pulse');
        }, 1000);
    }

    if (message) {
        showNotification(message, 'error');
    }
};

/**
 * Enhanced notification with slide-in animation
 */
window.showNotificationEnhanced = function(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 max-w-sm rounded-lg shadow-lg p-4 onboarding-notification-slide-in ${
        type === 'success' ? 'bg-green-50 text-green-800 border border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800' :
        type === 'error' ? 'bg-red-50 text-red-800 border border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800' :
        type === 'warning' ? 'bg-yellow-50 text-yellow-800 border border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800' :
        'bg-blue-50 text-blue-800 border border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800'
    }`;

    const icons = {
        success: '<svg class="h-5 w-5 text-green-600 dark:text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        error: '<svg class="h-5 w-5 text-red-600 dark:text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
        warning: '<svg class="h-5 w-5 text-yellow-600 dark:text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
        info: '<svg class="h-5 w-5 text-blue-600 dark:text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
    };

    notification.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                ${icons[type] || icons.info}
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <button type="button" class="ml-4 inline-flex flex-shrink-0 rounded-lg p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" onclick="this.closest('.onboarding-notification-slide-in').remove()">
                <span class="sr-only">Close</span>
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto-remove with fade out
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, duration);

    return notification;
};

/**
 * Animate step completion in progress indicator
 */
window.animateStepCompletion = function(stepElement) {
    if (stepElement) {
        const checkmark = stepElement.querySelector('[data-step-checkmark]');
        if (checkmark) {
            checkmark.classList.add('onboarding-step-complete');
        }

        stepElement.classList.add('onboarding-celebrate');
        setTimeout(() => {
            stepElement.classList.remove('onboarding-celebrate');
        }, 500);
    }
};

/**
 * Show loading state on button
 */
window.setButtonLoadingState = function(button, loading = true, text = null) {
    if (!button) return;

    if (loading) {
        button.dataset.originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <svg class="onboarding-spinner inline-block h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${text || 'Processing...'}
        `;
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || 'Continue';
        delete button.dataset.originalText;
    }
};

/**
 * Smooth scroll to element
 */
window.smoothScrollTo = function(element, offset = 0) {
    if (!element) return;

    const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
    const offsetPosition = elementPosition - offset;

    window.scrollTo({
        top: offsetPosition,
        behavior: 'smooth'
    });
};

/**
 * Scroll to first error
 */
window.scrollToFirstError = function() {
    const firstError = document.querySelector('.border-red-500, .error-message');
    if (firstError) {
        smoothScrollTo(firstError, 100);

        // Add attention shake
        firstError.classList.add('onboarding-shake');
        setTimeout(() => {
            firstError.classList.remove('onboarding-shake');
        }, 500);
    }
};

/**
 * Enhanced form validation with visual feedback
 */
window.validateFormWithAnimation = function(form) {
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    let firstInvalidField = null;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500', 'dark:border-red-500', 'onboarding-shake');

            if (!firstInvalidField) {
                firstInvalidField = input;
            }

            setTimeout(() => {
                input.classList.remove('onboarding-shake');
            }, 500);
        }
    });

    if (!isValid && firstInvalidField) {
        smoothScrollTo(firstInvalidField, 100);
        firstInvalidField.focus();
    }

    return isValid;
};

/**
 * Animate number counting
 */
window.animateCounter = function(element, start, end, duration = 1000) {
    if (!element) return;

    const range = end - start;
    const increment = range / (duration / 16); // 60fps
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.round(current);
    }, 16);
};

// Export functions for global use
window.onboardingAnimations = {
    showLoadingOverlay,
    hideLoadingOverlay,
    showSuccessAnimation,
    showErrorAnimation,
    showNotificationEnhanced,
    animateStepCompletion,
    setButtonLoadingState,
    smoothScrollTo,
    scrollToFirstError,
    validateFormWithAnimation,
    animateCounter
};
