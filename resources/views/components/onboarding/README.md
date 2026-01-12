# Onboarding Help System Components

This directory contains reusable Blade components for the contextual help system used throughout the onboarding wizard.

## Components Overview

### 1. Tooltip Component (`tooltip.blade.php`)

Displays a help icon with a tooltip that appears on hover or focus.

**Usage:**
```blade
<x-onboarding.tooltip>
    This is helpful information that appears in a tooltip
</x-onboarding.tooltip>
```

**With custom position:**
```blade
<x-onboarding.tooltip position="top">
    Tooltip content here
</x-onboarding.tooltip>
```

**Available positions:** `top`, `bottom`, `left`, `right` (default: `top`)

**Features:**
- Accessible (keyboard navigation supported)
- Dark mode compatible
- Smooth transitions
- Positioned automatically with arrow pointer
- 264px width for readability

---

### 2. Info Panel Component (`info-panel.blade.php`)

Displays an informational panel with icon, optional title, and content.

**Basic Usage:**
```blade
<x-onboarding.info-panel>
    This is some helpful information
</x-onboarding.info-panel>
```

**With type and title:**
```blade
<x-onboarding.info-panel type="warning" title="Important Note">
    Warning message here
</x-onboarding.info-panel>
```

**Available types:**
- `info` (default) - Blue theme, information icon
- `success` - Green theme, checkmark icon
- `warning` - Yellow theme, warning triangle icon
- `error` - Red theme, error X icon
- `tip` - Purple theme, lightbulb icon

**Examples:**

```blade
{{-- Information panel --}}
<x-onboarding.info-panel type="info" title="Why we need this">
    Your company information personalizes your CRM experience.
</x-onboarding.info-panel>

{{-- Success message --}}
<x-onboarding.info-panel type="success" title="All set!">
    Your configuration has been saved successfully.
</x-onboarding.info-panel>

{{-- Warning --}}
<x-onboarding.info-panel type="warning">
    Sample data is for demonstration purposes only.
</x-onboarding.info-panel>

{{-- Pro tip --}}
<x-onboarding.info-panel type="tip" title="💡 Pro Tip">
    You can update these details anytime from settings.
</x-onboarding.info-panel>
```

---

### 3. Video Embed Component (`video-embed.blade.php`)

Embeds video tutorials with responsive aspect ratio and optional lazy loading.

**Basic Usage:**
```blade
<x-onboarding.video-embed
    url="https://www.youtube.com/embed/VIDEO_ID"
    title="Setup Tutorial"
/>
```

**With lazy loading (recommended):**
```blade
<x-onboarding.video-embed
    url="https://www.youtube.com/embed/VIDEO_ID"
    title="Setup Tutorial"
    thumbnail="/path/to/thumbnail.jpg"
/>
```

**Props:**
- `url` (required) - Video embed URL (YouTube, Vimeo, etc.)
- `title` (optional) - Video title for accessibility (default: "Video Tutorial")
- `thumbnail` (optional) - Thumbnail image path for lazy loading

**Features:**
- 16:9 responsive aspect ratio
- Lazy loading with thumbnail (saves bandwidth)
- Play button overlay on thumbnail
- Autoplay when clicked (lazy loading mode)
- Rounded corners and dark background
- Fullscreen support

**Video URL Examples:**
```
YouTube: https://www.youtube.com/embed/VIDEO_ID
Vimeo: https://player.vimeo.com/video/VIDEO_ID
```

---

### 4. Field Help Component (`field-help.blade.php`)

Combines a form label with an inline help tooltip for form fields.

**Usage:**
```blade
<x-onboarding.field-help
    for="company_name"
    :label="config('onboarding.steps.company_setup.fields.company_name.label')"
    :required="true"
>
    Your official company or business name that will appear in documents
</x-onboarding.field-help>
```

**Without tooltip (just label):**
```blade
<x-onboarding.field-help
    for="company_name"
    label="Company Name"
    :required="true"
/>
```

**Props:**
- `for` (required) - Form field ID for label association
- `label` (required) - Label text
- `required` (optional) - Shows red asterisk if true (default: false)
- **Slot content** - Tooltip text (if provided)

**Features:**
- Proper label-input association
- Required field indicator (red asterisk)
- Inline help tooltip icon
- Accessible (ARIA compliant)
- Dark mode compatible

---

## Configuration

### Video Tutorial URLs

To enable video tutorials in the help sidebar, add video URLs to `config/onboarding.php`:

```php
'steps' => [
    'company_setup' => [
        // ... other config
        'video_url' => env('ONBOARDING_VIDEO_COMPANY_SETUP', null),
        'video_thumbnail' => null,
    ],
],
```

Set environment variables in `.env`:
```env
ONBOARDING_VIDEO_COMPANY_SETUP=https://www.youtube.com/embed/VIDEO_ID
```

### Enable/Disable Video Tutorials

In `config/onboarding.php`:
```php
'ui' => [
    'show_video_tutorials' => env('ONBOARDING_SHOW_VIDEO_TUTORIALS', false),
],
```

In `.env`:
```env
ONBOARDING_SHOW_VIDEO_TUTORIALS=true
```

---

## Best Practices

### 1. Use Appropriate Component Types

- **Tooltip** - For short, field-level help (1-2 sentences)
- **Info Panel** - For multi-sentence explanations or important notices
- **Video** - For complex features that benefit from visual demonstration
- **Field Help** - For form labels with contextual help

### 2. Writing Tooltip Content

✅ **Good:**
- "Your official company name that appears on invoices"
- "Select your industry to unlock relevant features"

❌ **Avoid:**
- Very long paragraphs (use info panel instead)
- Technical jargon without explanation
- Redundant information already in the label

### 3. Writing Info Panel Content

✅ **Good:**
- Clear, actionable information
- Proper use of type (warning for cautions, tip for recommendations)
- Include titles for context

❌ **Avoid:**
- Multiple panels of the same type in sequence
- Overly technical language
- Redundant information

### 4. Video Integration

✅ **Best practices:**
- Always provide a thumbnail for lazy loading
- Keep videos short (2-5 minutes)
- Ensure videos have captions for accessibility
- Use descriptive titles

---

## Examples from the Wizard

### Company Setup Step

```blade
{{-- Info panel at top --}}
<x-onboarding.info-panel type="info" title="Why we need this">
    Your company information personalizes your CRM experience.
</x-onboarding.info-panel>

{{-- Field with tooltip --}}
<x-onboarding.field-help
    for="company_name"
    :label="config('onboarding.steps.company_setup.fields.company_name.label')"
    :required="true"
>
    {{ config('onboarding.steps.company_setup.fields.company_name.help') }}
</x-onboarding.field-help>

{{-- Pro tip at bottom --}}
<x-onboarding.info-panel type="tip">
    You can update these details anytime from settings.
</x-onboarding.info-panel>
```

### Sample Data Step

```blade
{{-- Warning about sample data --}}
<x-onboarding.info-panel type="warning" title="Note about Sample Data">
    Sample data is for demonstration purposes only.
</x-onboarding.info-panel>

{{-- Recommendation --}}
<x-onboarding.info-panel type="tip" title="💡 Pro Tip">
    If you're new to CRM systems, we highly recommend importing sample data.
</x-onboarding.info-panel>
```

---

## Accessibility Features

All components include:
- Proper ARIA labels and roles
- Keyboard navigation support
- Screen reader compatibility
- Semantic HTML structure
- Sufficient color contrast (WCAG AA compliant)

---

## Dark Mode

All components automatically adapt to dark mode using Tailwind's `dark:` variants. No additional configuration needed.

---

## Dependencies

- **Tailwind CSS** - For styling
- **Alpine.js** - For tooltip interactions (x-data, x-show)
- **Heroicons** - SVG icons (inline in components)

---

## Support

For issues or questions about these components, please:
1. Check this documentation
2. Review the component source code
3. Test in both light and dark modes
4. Verify Alpine.js is loaded on the page
