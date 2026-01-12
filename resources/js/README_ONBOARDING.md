# Onboarding Wizard JavaScript

This file contains all the JavaScript functionality for the interactive onboarding wizard, including form validation, AJAX submissions, step transitions, and skip confirmations.

## Features

### 1. Client-Side Form Validation

- **Real-time validation** on field blur
- **Inline error messages** displayed below fields
- **Validation types supported:**
  - Required fields
  - Email format
  - URL format
  - Number ranges (min/max)
- **Visual feedback** with red borders for invalid fields
- **Auto-clear errors** when user corrects input

### 2. AJAX Form Submission

- **Opt-in AJAX mode** via `data-use-ajax="true"` attribute on form
- **Loading states** with spinner animation during submission
- **Success/error notifications** with auto-dismiss
- **Server-side validation** error display
- **Automatic redirects** after successful submission
- **Fallback** to standard form submission if AJAX is not enabled

### 3. Skip Confirmation Dialog

- **Custom modal** for skip confirmation
- **Prevents accidental skips** with user-friendly messaging
- **Keyboard-accessible** with proper focus management
- **Dark mode support**

### 4. Progress Tracking

- **Automatic progress updates** every 30 seconds
- **Real-time percentage** and completion count updates
- **API integration** with `/api/onboarding/progress` endpoint
- **Graceful error handling** if API calls fail

### 5. Unsaved Changes Warning

- **Tracks form modifications** automatically
- **Browser warning** when attempting to leave with unsaved changes
- **Resets flag** after successful submission
- **Prevents accidental data loss**

### 6. Notifications System

- **Three notification types:** success, error, info
- **Auto-dismiss after 5 seconds** with smooth animations
- **Manual close button** for user control
- **Stacked notifications** with proper z-index
- **Responsive** and mobile-friendly

## Usage

### Basic Usage

The wizard JavaScript automatically initializes when the page loads and detects `#wizard-step-form`:

```html
<form id="wizard-step-form" action="/onboarding/step/company_setup" method="POST">
    @csrf
    <!-- form fields here -->
</form>
```

### Enable AJAX Submission

Add the `data-use-ajax` attribute to enable AJAX mode:

```html
<form id="wizard-step-form"
      data-use-ajax="true"
      action="/onboarding/step/company_setup"
      method="POST">
    @csrf
    <!-- form fields here -->
</form>
```

### Progress Tracking

Add the `data-progress-tracker` attribute to enable automatic progress updates:

```html
<div data-progress-tracker>
    <span data-progress-percentage>50%</span>
    <span data-completed-count>3 of 5 completed</span>
</div>
```

### Skip Button

Skip confirmation works automatically for any form with `/skip` in the action:

```html
<form action="{{ route('onboarding.skip', $step) }}" method="POST">
    @csrf
    <button type="submit">Skip</button>
</form>
```

## API Helper

The script provides a global `window.onboardingApi` object for programmatic access:

### Validate Step Data

```javascript
const result = await window.onboardingApi.validateStep('company_setup', {
    company_name: 'Acme Corp',
    industry: 'Technology'
});

if (result.success) {
    console.log('Validation passed');
} else {
    console.error('Validation failed:', result.errors);
}
```

### Get Progress

```javascript
const result = await window.onboardingApi.getProgress();

if (result.success) {
    console.log('Progress:', result.data);
}
```

## Notification API

Show custom notifications programmatically:

```javascript
// Success notification
showNotification('Step completed successfully!', 'success');

// Error notification
showNotification('An error occurred.', 'error');

// Info notification
showNotification('Please wait...', 'info');
```

## Confirmation Dialog API

Show custom confirmation dialogs:

```javascript
const confirmed = await showConfirmDialog(
    'Delete this item?',
    'This action cannot be undone.',
    'Delete',
    'Cancel'
);

if (confirmed) {
    // User clicked "Delete"
} else {
    // User clicked "Cancel"
}
```

## Dependencies

- **Alpine.js** (v3.13.3+) - For reactive UI components in Blade templates
- **Axios** - Pre-configured for CSRF token handling (via bootstrap.js)

## Browser Support

- Modern browsers with ES2015+ support
- Graceful degradation to standard form submission if JavaScript is disabled
- Full support for dark mode via Tailwind CSS classes

## Integration with Alpine.js Components

The onboarding wizard uses Alpine.js in the following components:

### Tooltip Component
```blade
<x-onboarding.tooltip>
    This is a helpful tooltip
</x-onboarding.tooltip>
```

### Video Embed Component
```blade
<x-onboarding.video-embed
    url="https://youtube.com/watch?v=..."
    title="Tutorial Video"
/>
```

### Form Field Help
```blade
<x-onboarding.field-help
    for="company_name"
    required="true"
    tooltip="Enter your company's legal name"
>
    Company Name
</x-onboarding.field-help>
```

## Error Handling

All async operations include comprehensive error handling:

- **Network errors** are caught and displayed to the user
- **API errors** are logged to the console for debugging
- **Validation errors** are displayed inline with form fields
- **Loading states** are always restored, even on error

## Accessibility

- **ARIA labels** for all interactive elements
- **Keyboard navigation** fully supported
- **Screen reader** friendly with proper semantic HTML
- **Focus management** in modals and dialogs
- **Color contrast** meets WCAG AA standards

## Performance

- **Minimal DOM manipulation** for optimal performance
- **Debounced validation** to reduce unnecessary checks
- **Progressive enhancement** - works without JavaScript
- **Lazy initialization** - only loads when needed
- **Small bundle size** - ~16KB minified

## Testing

The validation functions can be tested independently:

```javascript
// Test email validation
const emailField = document.querySelector('input[type="email"]');
emailField.value = 'invalid-email';
const isValid = validateField(emailField); // returns false

emailField.value = 'valid@email.com';
const isValid2 = validateField(emailField); // returns true
```

## Future Enhancements

Potential improvements for future iterations:

- Add unit tests for validation functions
- Implement field-level async validation
- Add support for custom validation rules
- Improve progress tracking with WebSocket
- Add keyboard shortcuts for navigation
- Implement auto-save functionality
- Add analytics tracking for form interactions
