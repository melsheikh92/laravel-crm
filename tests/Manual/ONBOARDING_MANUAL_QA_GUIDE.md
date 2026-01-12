# Onboarding Wizard - Manual QA Testing Guide

## Overview

This guide provides comprehensive manual testing procedures for the Interactive Onboarding Wizard. It covers UI/UX, keyboard navigation, screen reader support, mobile responsiveness, and accessibility compliance.

## Table of Contents

1. [Pre-Testing Setup](#pre-testing-setup)
2. [UI/UX Testing](#uiux-testing)
3. [Keyboard Navigation Testing](#keyboard-navigation-testing)
4. [Screen Reader Testing](#screen-reader-testing)
5. [Mobile Responsiveness Testing](#mobile-responsiveness-testing)
6. [Cross-Browser Testing](#cross-browser-testing)
7. [Accessibility Compliance (WCAG 2.1 AA)](#accessibility-compliance-wcag-21-aa)
8. [Form Validation Testing](#form-validation-testing)
9. [Error Handling Testing](#error-handling-testing)
10. [Performance Testing](#performance-testing)

---

## Pre-Testing Setup

### Requirements

- **Browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **Mobile Devices**: iOS (iPhone/iPad), Android (Phone/Tablet)
- **Screen Readers**:
  - NVDA (Windows - free)
  - JAWS (Windows - commercial)
  - VoiceOver (macOS/iOS - built-in)
  - TalkBack (Android - built-in)
- **Testing Tools**:
  - Browser DevTools (responsive mode)
  - axe DevTools browser extension
  - WAVE browser extension
  - Lighthouse (Chrome DevTools)
  - Color contrast checker

### Test Environment Setup

1. **Create a fresh installation** or reset existing onboarding:
   ```bash
   php artisan migrate:fresh --seed
   php artisan onboarding:reset --user=test@example.com
   ```

2. **Create test user accounts**:
   - New user (never completed onboarding)
   - Partial progress user (completed 2-3 steps)
   - Completed user (finished all steps)

3. **Configure test settings** in `.env`:
   ```env
   ONBOARDING_ENABLED=true
   ONBOARDING_AUTO_TRIGGER=true
   ONBOARDING_ALLOW_SKIP=true
   ONBOARDING_ALLOW_RESTART=true
   ```

---

## UI/UX Testing

### Visual Design Testing

#### Welcome Page (`/onboarding`)

- [ ] **Layout**: Page is centered and well-balanced
- [ ] **Hero Section**: Rocket icon displays correctly
- [ ] **Welcome Message**: Clear and inviting heading
- [ ] **Step List**: All 5 steps listed with icons, titles, descriptions, time estimates
- [ ] **Optional Badges**: "Optional" badges appear on skippable steps (user_creation, pipeline_config, email_integration, sample_data)
- [ ] **Feature Highlights**: 3 feature cards (Quick Setup, Auto-Save, Flexible) display in grid
- [ ] **CTA Button**: "Get Started" button is prominent and clickable
- [ ] **Resume Functionality**: Shows resume message for users with partial progress
- [ ] **Dark Mode**: Toggle dark mode and verify all colors, contrasts, and readability

#### Step Pages (`/onboarding/step/{step}`)

**For each step** (company_setup, user_creation, pipeline_config, email_integration, sample_data):

- [ ] **Progress Indicator**: Shows all 5 steps with correct states (completed ✓, current highlighted, skipped ✗, pending)
- [ ] **Connector Lines**: Blue for completed connections, gray for pending
- [ ] **Progress Bar**: Displays correct percentage and animated transitions
- [ ] **Duration Display**: Shows time elapsed in minutes
- [ ] **Step Icon**: Correct icon displays in step container
- [ ] **Step Title**: Clear and matches config
- [ ] **Step Description**: Helpful description text
- [ ] **Estimated Time**: Shows expected completion time
- [ ] **Form Fields**: All fields render correctly with proper types
- [ ] **Required Indicators**: Red asterisks (*) on required fields
- [ ] **Help Text**: Field help text displays below inputs
- [ ] **Validation Errors**: Error messages display inline below fields with red borders
- [ ] **Navigation Buttons**:
  - Previous button (disabled on first step)
  - Skip button (only on skippable steps)
  - Continue button (changes to "Complete" on last step)
- [ ] **Help Sidebar**:
  - Sticky positioning on scroll
  - Contextual help text
  - Quick tips list
  - Resource links (clickable)
  - Pro tip callout
- [ ] **Dark Mode**: All elements visible and readable in dark mode

#### Completion Page (`/onboarding/complete`)

- [ ] **Confetti Animation**: Plays on page load (50 animated particles)
- [ ] **Success Icon**: Large green checkmark in circle
- [ ] **Congratulations Message**: Prominent heading
- [ ] **Completion Summary**: Shows correct counts for completed/skipped steps and duration
- [ ] **Configured Features List**: Lists all completed steps
- [ ] **Next Steps Cards**: 3 action cards display (Dashboard, Documentation, Invite Team)
- [ ] **Help Resources**: Support links visible
- [ ] **Main CTA**: "Go to Dashboard" button prominent
- [ ] **Restart Option**: Shows if `allow_restart` is enabled
- [ ] **Dark Mode**: All elements properly styled

### Interaction Testing

#### Form Interactions

- [ ] **Input Focus**: Fields highlight on focus with blue border
- [ ] **Placeholder Text**: Disappears when typing
- [ ] **Field Values**: Persist using `old()` helper after validation errors
- [ ] **Pre-filled Data**: Previous values load correctly when revisiting completed steps
- [ ] **Select Dropdowns**: Open/close smoothly, options selectable
- [ ] **Checkboxes**: Toggle on/off correctly
- [ ] **Radio Buttons**: Select one option at a time
- [ ] **Textarea**: Expands to fit content, scrollable for long text

#### Step-Specific Interactions

**Company Setup:**
- [ ] Industry dropdown works
- [ ] Company size dropdown works
- [ ] All text inputs accept text
- [ ] Phone input accepts numbers
- [ ] Website input validates URL format

**User Creation:**
- [ ] Email input validates email format
- [ ] Role dropdown works
- [ ] Send invitation checkbox toggles
- [ ] Info box explains password generation

**Pipeline Configuration:**
- [ ] Add stage button adds new stage inputs
- [ ] Remove stage button removes stages (prevents removing last stage)
- [ ] Drag handles work for reordering (desktop)
- [ ] Stage names update correctly
- [ ] Probability accepts 0-100 values
- [ ] Default stages load correctly

**Email Integration:**
- [ ] Provider selection (radio buttons) works
- [ ] Selecting provider auto-fills SMTP settings
- [ ] All fields editable after auto-fill
- [ ] Port input accepts numbers
- [ ] Encryption dropdown works
- [ ] Test connection checkbox toggles
- [ ] Quick reference guide displays provider settings

**Sample Data:**
- [ ] Import toggle shows/hides customization options
- [ ] Enabling import auto-checks all include options
- [ ] Individual include checkboxes toggle independently
- [ ] Sample data preview displays correctly
- [ ] Warning box visible
- [ ] Pro tip box visible

#### Navigation Testing

- [ ] **Next Button**: Validates and moves to next step
- [ ] **Previous Button**: Saves progress and goes back
- [ ] **Skip Button**: Shows confirmation, skips step
- [ ] **Step Indicators**: Clickable to jump to specific steps (if allowed)
- [ ] **Progress Bar**: Updates smoothly on step changes
- [ ] **Browser Back/Forward**: Handles navigation gracefully
- [ ] **Direct URL Access**: Can access `/onboarding/step/{step}` directly

### Visual Polish

- [ ] **Transitions**: Smooth animations on step changes (no jarring jumps)
- [ ] **Loading States**: Buttons show loading indicator during submission
- [ ] **Hover Effects**: Buttons, links, and interactive elements respond to hover
- [ ] **Button States**: Disabled buttons appear grayed out
- [ ] **Icons**: All icons render correctly (no missing symbols)
- [ ] **Spacing**: Consistent padding and margins throughout
- [ ] **Typography**: Readable font sizes, proper hierarchy
- [ ] **Colors**: Consistent color scheme matching design system
- [ ] **Shadows**: Subtle shadows on cards and containers
- [ ] **Borders**: Consistent border radius and colors

---

## Keyboard Navigation Testing

### Global Keyboard Support

Test **without using a mouse** - navigate using only the keyboard.

#### Tab Navigation

- [ ] **Tab Order**: Logical tab order through all interactive elements
- [ ] **Focus Indicators**: Clear visual focus outline on all focusable elements
- [ ] **Skip to Content**: Skip navigation links work (if implemented)
- [ ] **Focus Trap**: Focus stays within modal dialogs when open
- [ ] **No Focus Traps**: Can tab through entire page without getting stuck

#### Keyboard Shortcuts

Test these on all pages:

- [ ] **Tab**: Move focus forward
- [ ] **Shift+Tab**: Move focus backward
- [ ] **Enter**: Activate buttons, submit forms
- [ ] **Space**: Activate buttons, toggle checkboxes
- [ ] **Escape**: Close modals/dialogs
- [ ] **Arrow Keys**: Navigate between radio buttons, move through select options

### Page-Specific Keyboard Testing

#### Welcome Page

- [ ] Tab to "Get Started" button
- [ ] Press Enter to start wizard
- [ ] Tab through feature cards and step list

#### Step Pages

**For each step:**

- [ ] Tab through all form fields in logical order
- [ ] Tab to Previous/Skip/Continue buttons
- [ ] Use Enter to submit form
- [ ] Use Space to toggle checkboxes
- [ ] Arrow keys navigate radio button groups (email provider)
- [ ] Arrow keys navigate select dropdowns

**Pipeline Configuration:**
- [ ] Tab through stage inputs
- [ ] Arrow keys don't interfere with text input
- [ ] Tab to Add/Remove stage buttons
- [ ] Enter activates Add/Remove buttons

#### Completion Page

- [ ] Tab through action cards
- [ ] Tab to main CTA button
- [ ] Enter activates buttons

### Form Submission

- [ ] **Enter in Text Input**: Submits form (doesn't add newline)
- [ ] **Enter in Textarea**: Adds newline (doesn't submit)
- [ ] **Space on Checkbox**: Toggles without scrolling page
- [ ] **Shift+Enter**: No special behavior (standard Enter behavior)

---

## Screen Reader Testing

### Screen Reader Setup

#### Windows (NVDA - Free)

1. Download from [nvaccess.org](https://www.nvaccess.org/)
2. Install and launch NVDA
3. Common commands:
   - **Insert**: NVDA modifier key
   - **Insert+Down Arrow**: Start reading
   - **Insert+Up Arrow**: Read current line
   - **Tab**: Next interactive element
   - **H**: Next heading
   - **Insert+F7**: List all elements

#### macOS (VoiceOver - Built-in)

1. Enable: System Preferences > Accessibility > VoiceOver
2. Shortcut: Cmd+F5
3. Common commands:
   - **VO**: Cmd+Option (VoiceOver modifier)
   - **VO+A**: Start reading
   - **VO+Right/Left Arrow**: Move to next/previous item
   - **VO+Space**: Activate item
   - **VO+H**: Next heading

### Screen Reader Testing Checklist

#### Semantic Structure

- [ ] **Page Title**: Announces page title correctly ("Onboarding Wizard - Company Setup")
- [ ] **Headings**: All headings (h1-h6) read in logical order
- [ ] **Landmarks**: Page regions announced (header, main, footer, nav)
- [ ] **Lists**: Step lists and tips read as lists with item counts
- [ ] **Forms**: Form fields grouped logically

#### Form Accessibility

**For each form field:**

- [ ] **Label Association**: Label text announced when field receives focus
- [ ] **Required Fields**: "Required" announced for required fields
- [ ] **Field Type**: Correct type announced (text, email, number, select, checkbox)
- [ ] **Placeholder**: Placeholder text announced if no label
- [ ] **Help Text**: Help text associated via `aria-describedby`
- [ ] **Error Messages**: Errors announced immediately after field
- [ ] **Validation**: Validation state announced (invalid, required, etc.)

#### Interactive Elements

- [ ] **Buttons**: Purpose clearly announced ("Continue to next step", "Skip this step")
- [ ] **Links**: Link text descriptive (not "click here")
- [ ] **Checkboxes**: State announced (checked/unchecked)
- [ ] **Radio Buttons**: Group name and selected option announced
- [ ] **Select Dropdowns**: Selected value and total options announced
- [ ] **Disabled Elements**: Disabled state announced

#### Progress Indicators

- [ ] **Progress Bar**: Percentage announced (`role="progressbar" aria-valuenow="40"`)
- [ ] **Step Indicators**: Current step announced (`aria-current="step"`)
- [ ] **Step Status**: Status badges read correctly (Completed, Skipped, Current, Upcoming)
- [ ] **Step Numbers**: Steps numbered correctly (Step 1 of 5)

#### Dynamic Content

- [ ] **Loading States**: "Loading" or "Processing" announced
- [ ] **Success Messages**: Success confirmation read automatically
- [ ] **Error Messages**: Errors announced via `aria-live="polite"`
- [ ] **Validation Errors**: Real-time validation errors announced
- [ ] **Modal Dialogs**: Focus moves to modal, dialog role announced
- [ ] **Skip Confirmation**: Confirmation dialog accessible and readable

#### Navigation Announcements

- [ ] **Page Changes**: New page title announced on navigation
- [ ] **Step Changes**: New step announced when moving forward/back
- [ ] **Completion**: Completion page announces success
- [ ] **Help Sidebar**: Help content accessible and readable

### ARIA Attributes Verification

Check the HTML source for proper ARIA usage:

- [ ] **`aria-label`**: Used on icon-only buttons
- [ ] **`aria-labelledby`**: Associates labels with complex elements
- [ ] **`aria-describedby`**: Associates help text with inputs
- [ ] **`aria-live`**: On dynamic message containers
- [ ] **`aria-current="step"`**: On current step in stepper
- [ ] **`aria-required`**: On required fields
- [ ] **`aria-invalid`**: On fields with validation errors
- [ ] **`role="progressbar"`**: On progress bar
- [ ] **`role="alert"`**: On error messages
- [ ] **`aria-hidden="true"`**: On decorative icons

---

## Mobile Responsiveness Testing

### Devices to Test

#### iOS
- iPhone SE (small screen)
- iPhone 14/15 (medium screen)
- iPhone 14 Pro Max (large screen)
- iPad (tablet)
- iPad Pro (large tablet)

#### Android
- Samsung Galaxy S21 (medium screen)
- Google Pixel 7 (medium screen)
- Samsung Galaxy Tab (tablet)

### Viewport Sizes

Test using browser DevTools responsive mode:

- [ ] **320px** (iPhone SE)
- [ ] **375px** (iPhone 12/13)
- [ ] **390px** (iPhone 14)
- [ ] **414px** (iPhone Plus models)
- [ ] **768px** (iPad portrait)
- [ ] **1024px** (iPad landscape)
- [ ] **1280px** (desktop)
- [ ] **1920px** (large desktop)

### Responsive Design Checklist

#### Layout Adaptation

- [ ] **Single Column**: Content stacks vertically on mobile
- [ ] **Two Columns**: Forms use 2-column grid on tablet/desktop
- [ ] **Help Sidebar**: Moves below main content on mobile, sidebar on desktop
- [ ] **Progress Indicator**: Horizontal scrollable on small screens, full width on large
- [ ] **Button Layout**: Buttons stack on mobile, inline on desktop
- [ ] **Card Grids**: 1 column on mobile, 2-3 columns on tablet/desktop

#### Touch Interactions

- [ ] **Touch Targets**: All interactive elements minimum 44x44px
- [ ] **Button Spacing**: Adequate spacing between buttons (no accidental taps)
- [ ] **Tap Feedback**: Visual feedback on tap (color change, ripple effect)
- [ ] **Swipe Gestures**: No accidental form submissions from swipes
- [ ] **Pinch Zoom**: Page allows zoom (no `user-scalable=no`)
- [ ] **Double Tap**: No unintended actions from double taps

#### Mobile-Specific Features

- [ ] **Keyboard Types**:
  - Email fields show email keyboard
  - Number fields show number keyboard
  - URL fields show URL keyboard with .com shortcut
  - Phone fields show phone keyboard
- [ ] **Autocomplete**: Form autocomplete works on mobile browsers
- [ ] **Autofill**: Browser autofill suggestions appear
- [ ] **Copy/Paste**: Can copy/paste into fields
- [ ] **Selection**: Can select text in inputs

#### Visual Elements on Mobile

- [ ] **Text Size**: Readable without zooming (minimum 16px)
- [ ] **Line Height**: Adequate spacing for readability (1.5+)
- [ ] **Images**: Scale appropriately, no overflow
- [ ] **Icons**: Visible and recognizable at small sizes
- [ ] **Progress Bar**: Full width, readable percentage
- [ ] **Modals**: Full screen or appropriately sized
- [ ] **Navigation**: Mobile menu accessible (hamburger if implemented)

#### Performance on Mobile

- [ ] **Load Time**: Pages load within 3 seconds on 3G
- [ ] **Smooth Scrolling**: No lag or jank when scrolling
- [ ] **Animations**: Smooth transitions, no choppy animations
- [ ] **Form Submission**: No delays or freezing on submit
- [ ] **JavaScript**: All interactions work (drag-drop may differ)

### Orientation Testing

Test both portrait and landscape orientations:

- [ ] **Portrait Mode**: All content visible and usable
- [ ] **Landscape Mode**: Layout adapts, no horizontal scroll
- [ ] **Orientation Change**: Smooth transition between orientations
- [ ] **Form State**: Data persists after orientation change

### Mobile Browser Testing

Test on multiple mobile browsers:

- [ ] **Safari (iOS)**: Primary iOS browser
- [ ] **Chrome (iOS)**: Alternative iOS browser
- [ ] **Chrome (Android)**: Primary Android browser
- [ ] **Firefox (Android)**: Alternative Android browser
- [ ] **Samsung Internet**: Samsung default browser

### Tablet-Specific Testing

- [ ] **Layout**: Uses tablet-optimized layout (between mobile and desktop)
- [ ] **Touch Targets**: Appropriately sized for finger input
- [ ] **Sidebar**: May show on side or below depending on width
- [ ] **Form Fields**: Proper sizing for tablet keyboards

---

## Cross-Browser Testing

### Browsers to Test

Test on latest versions:

- [ ] **Chrome** (Windows, macOS, Linux)
- [ ] **Firefox** (Windows, macOS, Linux)
- [ ] **Safari** (macOS, iOS)
- [ ] **Edge** (Windows, macOS)
- [ ] **Opera** (optional)

### Browser-Specific Checks

#### Layout & Rendering

- [ ] **Flexbox**: Layouts render correctly
- [ ] **Grid**: Grid layouts work (if used)
- [ ] **Border Radius**: Rounded corners display
- [ ] **Shadows**: Box shadows render
- [ ] **Gradients**: Gradient backgrounds display
- [ ] **Transitions**: CSS transitions work smoothly

#### Form Elements

- [ ] **Input Styling**: Custom input styles apply
- [ ] **Select Dropdowns**: Styled selects work (or fallback to native)
- [ ] **Checkboxes**: Custom checkbox styles apply
- [ ] **Radio Buttons**: Custom radio styles apply
- [ ] **Date Pickers**: Native date pickers work
- [ ] **Number Inputs**: Spinner controls appear

#### JavaScript Functionality

- [ ] **Form Validation**: Client-side validation works
- [ ] **AJAX Submissions**: AJAX form submissions succeed
- [ ] **Drag-Drop**: Pipeline stage reordering works (desktop browsers)
- [ ] **Progress Updates**: Progress bar updates
- [ ] **Toggle Functions**: Show/hide functions work (sample data options)
- [ ] **Event Listeners**: All click/change/submit handlers work

#### Browser Console

Check for JavaScript errors:

- [ ] **No Console Errors**: JavaScript executes without errors
- [ ] **No 404s**: All assets load successfully
- [ ] **No Mixed Content**: HTTPS pages load all resources securely
- [ ] **Performance**: No performance warnings

---

## Accessibility Compliance (WCAG 2.1 AA)

### Automated Testing

Use these tools for initial accessibility scanning:

1. **axe DevTools** (browser extension)
   - Run on every page
   - Fix all critical and serious issues
   - Review moderate and minor issues

2. **WAVE** (browser extension)
   - Scan for errors, warnings, contrast issues
   - Verify ARIA usage

3. **Lighthouse** (Chrome DevTools)
   - Run accessibility audit
   - Aim for 90+ score
   - Address all failures

### WCAG 2.1 Level AA Requirements

#### Perceivable

**1.1 Text Alternatives**
- [ ] **Alt Text**: All images have descriptive alt text
- [ ] **Decorative Images**: Decorative images use `alt=""` or `aria-hidden="true"`
- [ ] **Icon Buttons**: Buttons with only icons have `aria-label`

**1.3 Adaptable**
- [ ] **Semantic HTML**: Proper use of headings, lists, tables
- [ ] **Heading Hierarchy**: No skipped heading levels (h1 → h2 → h3)
- [ ] **Form Labels**: All inputs have associated labels
- [ ] **Meaningful Sequence**: Content order makes sense when CSS disabled

**1.4 Distinguishable**
- [ ] **Color Contrast**:
  - Normal text: 4.5:1 minimum
  - Large text (18pt+): 3:1 minimum
  - UI components: 3:1 minimum
- [ ] **Color Not Sole Indicator**: Don't rely on color alone (use icons, text, patterns)
- [ ] **Text Resize**: Page usable at 200% zoom
- [ ] **Reflow**: No horizontal scroll at 320px width
- [ ] **Text Spacing**: Readable with increased spacing

#### Operable

**2.1 Keyboard Accessible**
- [ ] **Keyboard Navigation**: All functionality available via keyboard
- [ ] **No Keyboard Trap**: Can navigate away from all elements
- [ ] **Focus Visible**: Clear focus indicators on all interactive elements

**2.2 Enough Time**
- [ ] **No Time Limits**: No session timeouts during onboarding
- [ ] **Pause/Stop**: Can pause animations (confetti)

**2.4 Navigable**
- [ ] **Page Titles**: Unique and descriptive page titles
- [ ] **Focus Order**: Logical tab order
- [ ] **Link Purpose**: Link text describes destination
- [ ] **Multiple Ways**: Can navigate multiple ways (stepper, URLs)
- [ ] **Headings**: Headings describe topics

**2.5 Input Modalities**
- [ ] **Touch Targets**: Minimum 44x44px touch targets
- [ ] **Click/Tap**: All actions work with simple clicks/taps
- [ ] **Motion**: No motion-based controls

#### Understandable

**3.1 Readable**
- [ ] **Language**: Page language declared (`<html lang="en">`)
- [ ] **Clear Text**: Simple, understandable language

**3.2 Predictable**
- [ ] **Consistent Navigation**: Navigation consistent across pages
- [ ] **Consistent Identification**: UI components identified consistently
- [ ] **No Automatic Changes**: Focus doesn't trigger unexpected changes

**3.3 Input Assistance**
- [ ] **Error Identification**: Errors clearly identified and described
- [ ] **Labels/Instructions**: Clear labels and instructions for inputs
- [ ] **Error Suggestions**: Provide suggestions for fixing errors
- [ ] **Error Prevention**: Confirmation before completing wizard
- [ ] **Context Help**: Help text available for complex fields

#### Robust

**4.1 Compatible**
- [ ] **Valid HTML**: HTML validates (major errors fixed)
- [ ] **ARIA**: Proper ARIA usage (no conflicts)
- [ ] **Status Messages**: Status messages announced via `aria-live`

### Manual Accessibility Testing

Beyond automated tools, manually verify:

- [ ] **Keyboard-Only Navigation**: Complete entire wizard using only keyboard
- [ ] **Screen Reader Navigation**: Navigate using NVDA/VoiceOver
- [ ] **High Contrast Mode**: Usable in Windows High Contrast Mode
- [ ] **Dark Mode**: Fully functional and readable in dark mode
- [ ] **Reduced Motion**: Respects `prefers-reduced-motion` (if implemented)
- [ ] **Zoom**: Functional at 200% and 400% zoom
- [ ] **Text-Only**: Content understandable with CSS disabled

---

## Form Validation Testing

### Client-Side Validation

#### Required Field Validation

**For each step:**

- [ ] **Submit Empty Form**: Required fields show error messages
- [ ] **Error Display**: Errors appear inline below fields with red borders
- [ ] **Error Messages**: Clear, specific error messages (not generic "This field is required")
- [ ] **Multiple Errors**: Can display multiple errors at once
- [ ] **Error Removal**: Errors clear when field becomes valid

#### Field-Specific Validation

**Company Setup:**
- [ ] Company name: Required, max length validation
- [ ] Website: URL format validation
- [ ] Phone: Phone format validation (if implemented)

**User Creation:**
- [ ] Name: Required
- [ ] Email: Required, valid email format
- [ ] Duplicate email: Prevents duplicate user emails

**Pipeline Configuration:**
- [ ] Pipeline name: Required if creating pipeline
- [ ] Stage name: Required for each stage
- [ ] Probability: Number, 0-100 range
- [ ] Minimum stages: Prevents removing all stages

**Email Integration:**
- [ ] Port: Number, 1-65535 range
- [ ] Encryption: One of (tls, ssl, none)
- [ ] Connection test: Validates SMTP connection (if enabled)

**Sample Data:**
- [ ] No specific validation (all optional)

### Server-Side Validation

- [ ] **Bypass Client Validation**: Submit invalid data via browser console
- [ ] **Server Errors**: Server returns 422 with validation errors
- [ ] **Error Display**: Server errors displayed in UI
- [ ] **Old Input**: Form repopulates with old input after server error

### Validation UX

- [ ] **Inline Validation**: Real-time validation on blur (optional)
- [ ] **Submit Validation**: Always validates on submit
- [ ] **Focus on Error**: First invalid field receives focus
- [ ] **Error Summary**: Optional error summary at top of form
- [ ] **Success Feedback**: Success message after valid submission

---

## Error Handling Testing

### Network Errors

- [ ] **Connection Lost**: Graceful error when network disconnects
- [ ] **Slow Network**: Loading indicators during slow requests
- [ ] **Timeout**: Timeout errors handled gracefully

### Server Errors

- [ ] **500 Error**: Generic server error shows user-friendly message
- [ ] **403 Forbidden**: Permission errors handled
- [ ] **404 Not Found**: Invalid step URLs handled
- [ ] **422 Validation**: Validation errors displayed correctly

### JavaScript Errors

- [ ] **Console**: No JavaScript errors in browser console
- [ ] **Fallback**: Graceful degradation when JavaScript disabled
- [ ] **Try-Catch**: Errors caught and logged

### Edge Cases

- [ ] **Rapid Clicking**: Prevent double-submission
- [ ] **Browser Back**: Handle back button during wizard
- [ ] **Refresh**: Handle page refresh (restore progress)
- [ ] **Session Expiry**: Redirect to login if session expires
- [ ] **Concurrent Sessions**: Handle simultaneous sessions

---

## Performance Testing

### Page Load Performance

Use Chrome DevTools > Lighthouse:

- [ ] **Performance Score**: 80+ score
- [ ] **First Contentful Paint**: < 2 seconds
- [ ] **Time to Interactive**: < 3.5 seconds
- [ ] **Cumulative Layout Shift**: < 0.1 (no layout jumps)

### Asset Optimization

- [ ] **Image Sizes**: Images optimized for web
- [ ] **CSS**: Minified CSS
- [ ] **JavaScript**: Minified JavaScript
- [ ] **Lazy Loading**: Images lazy load (if applicable)

### Interaction Performance

- [ ] **Smooth Scrolling**: No jank during scroll
- [ ] **Smooth Animations**: 60fps animations
- [ ] **Button Response**: Immediate visual feedback
- [ ] **Form Submission**: < 2 second response time

---

## Test Reporting

Document all findings in the [QA Test Results Report](./ONBOARDING_QA_RESULTS.md).

For each issue found:
1. **Description**: Clear description of the issue
2. **Severity**: Critical, High, Medium, Low
3. **Steps to Reproduce**: Detailed reproduction steps
4. **Expected Behavior**: What should happen
5. **Actual Behavior**: What actually happens
6. **Screenshots**: Visual evidence
7. **Browser/Device**: Where issue occurs
8. **Accessibility Impact**: WCAG criteria affected (if applicable)

---

## Acceptance Criteria

The onboarding wizard passes manual QA when:

- [ ] **All UI/UX elements** render correctly across browsers and devices
- [ ] **Keyboard navigation** works for all interactive elements
- [ ] **Screen readers** can navigate and understand all content
- [ ] **Mobile devices** provide full functionality with appropriate layouts
- [ ] **WCAG 2.1 AA** compliance achieved (90+ Lighthouse score)
- [ ] **Form validation** works correctly client and server-side
- [ ] **Error handling** provides clear, actionable feedback
- [ ] **Performance** meets acceptable thresholds (80+ Lighthouse score)
- [ ] **No critical or high-severity bugs** remain
- [ ] **All acceptance criteria** from spec.md are met

---

## Additional Resources

- [WCAG 2.1 Quick Reference](https://www.w3.org/WAI/WCAG21/quickref/)
- [WebAIM Screen Reader Testing](https://webaim.org/articles/screenreader_testing/)
- [MDN Accessibility Guide](https://developer.mozilla.org/en-US/docs/Web/Accessibility)
- [Chrome DevTools Accessibility Reference](https://developers.google.com/web/tools/chrome-devtools/accessibility/reference)
- [Deque axe DevTools](https://www.deque.com/axe/devtools/)
- [WAVE Web Accessibility Evaluation Tool](https://wave.webaim.org/)

---

**Last Updated**: 2026-01-12
**Version**: 1.0
**Tester**: [Name]
**Test Date**: [Date]
