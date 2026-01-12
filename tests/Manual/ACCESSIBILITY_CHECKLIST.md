# Onboarding Wizard - Accessibility Testing Checklist

## Quick Reference for Manual Accessibility Testing

This checklist provides a quick, practical guide for testing accessibility compliance during manual QA.

---

## Pre-Flight Check

### Tools Setup
- [ ] Install axe DevTools extension ([Chrome](https://chrome.google.com/webstore/detail/axe-devtools-web-accessib/lhdoppojpmngadmnindnejefpokejbdd) | [Firefox](https://addons.mozilla.org/en-US/firefox/addon/axe-devtools/))
- [ ] Install WAVE extension ([Chrome](https://chrome.google.com/webstore/detail/wave-evaluation-tool/jbbplnpkjmmeebjpijfedlgcdilocofh) | [Firefox](https://addons.mozilla.org/en-US/firefox/addon/wave-accessibility-tool/))
- [ ] Enable screen reader (NVDA/VoiceOver)
- [ ] Open browser DevTools (F12)
- [ ] Prepare color contrast checker

---

## Page-by-Page Testing

### Welcome Page (`/onboarding`)

#### Automated Scan
- [ ] Run axe DevTools scan → 0 critical/serious issues
- [ ] Run WAVE scan → 0 errors
- [ ] Run Lighthouse accessibility audit → 90+ score

#### Keyboard Navigation
- [ ] Tab through all interactive elements
- [ ] Enter activates "Get Started" button
- [ ] Focus visible on all elements
- [ ] Tab order logical (top to bottom, left to right)

#### Screen Reader
- [ ] Page title announced: "Onboarding Wizard - Welcome"
- [ ] Main heading (h1) announced: "Welcome to..."
- [ ] Step list read as list with 5 items
- [ ] Feature cards accessible
- [ ] Button purpose clear: "Get Started with Onboarding"

#### Visual
- [ ] Text contrast ≥ 4.5:1 (use contrast checker)
- [ ] Icons visible at 200% zoom
- [ ] No horizontal scroll at 320px width
- [ ] Dark mode: all text readable

---

### Company Setup Step

#### Automated Scan
- [ ] axe DevTools: 0 critical/serious issues
- [ ] WAVE: 0 errors, verify labels/ARIA
- [ ] Lighthouse: 90+ accessibility score

#### Keyboard Navigation
- [ ] Tab through: company name → industry → size → address → phone → website → Previous → Skip → Continue
- [ ] Enter submits form
- [ ] Focus indicators clear on all fields
- [ ] Skip link navigable

#### Screen Reader
- [ ] Page title: "Onboarding Wizard - Company Setup (Step 1 of 5)"
- [ ] Progress announced: "40% complete, Step 1 of 5"
- [ ] Required fields: "Company Name, required"
- [ ] Help text associated: "Enter your company's official name"
- [ ] Validation errors announced immediately
- [ ] Error format: "[Field] error: [message]"

#### Form Accessibility
- [ ] All inputs have `<label>` with `for` attribute
- [ ] Required fields have `required` attribute
- [ ] Required fields have `aria-required="true"`
- [ ] Error fields have `aria-invalid="true"`
- [ ] Error messages have `id` linked via `aria-describedby`
- [ ] Help text linked via `aria-describedby`

#### Visual
- [ ] Error fields: red border
- [ ] Error messages: red text below field
- [ ] Required asterisks: red color
- [ ] Focus: blue border (ring-blue-500)
- [ ] Contrast: all text ≥ 4.5:1
- [ ] 200% zoom: all content readable

---

### User Creation Step

#### Automated Scan
- [ ] axe DevTools: 0 critical/serious issues
- [ ] WAVE: 0 errors
- [ ] Lighthouse: 90+ accessibility score

#### Keyboard Navigation
- [ ] Tab through: name → email → role → send invitation → buttons
- [ ] Space toggles checkbox
- [ ] Arrow keys navigate role dropdown
- [ ] Enter submits form

#### Screen Reader
- [ ] Page title: "Onboarding Wizard - User Creation (Step 2 of 5)"
- [ ] Progress: "40% complete, Step 2 of 5"
- [ ] Checkbox: "Send invitation email, checkbox, not checked"
- [ ] Role dropdown: "Role, combo box, Manager selected, 1 of 4"
- [ ] Info panel read correctly

#### Form Accessibility
- [ ] Email input type="email"
- [ ] Email input autocomplete="email"
- [ ] Name input autocomplete="name"
- [ ] Checkbox properly associated with label
- [ ] Select has label and describedby

---

### Pipeline Configuration Step

#### Automated Scan
- [ ] axe DevTools: 0 critical/serious issues
- [ ] WAVE: 0 errors
- [ ] Lighthouse: 90+ accessibility score

#### Keyboard Navigation
- [ ] Tab through pipeline name and all stage fields
- [ ] Tab to Add/Remove stage buttons
- [ ] Enter activates Add/Remove buttons
- [ ] Drag-drop: keyboard alternative exists or skip allowed
- [ ] All stages accessible via keyboard

#### Screen Reader
- [ ] Page title: "Onboarding Wizard - Pipeline Configuration (Step 3 of 5)"
- [ ] Stages announced as list or group
- [ ] Stage fields: "Stage 1 Name, Stage 1 Probability"
- [ ] Add button: "Add new stage"
- [ ] Remove button: "Remove stage [name]"
- [ ] Probability: "Probability percentage, 0 to 100"

#### Form Accessibility
- [ ] Stage fields have unique IDs
- [ ] Stage fields have associated labels
- [ ] Add button has descriptive aria-label
- [ ] Remove buttons have aria-label with stage name
- [ ] Probability has min/max attributes
- [ ] Drag handles have aria-label (if keyboard accessible)

#### Visual
- [ ] Drag handles visible
- [ ] Stage list visually clear
- [ ] Buttons have hover states
- [ ] Focus on buttons clear

---

### Email Integration Step

#### Automated Scan
- [ ] axe DevTools: 0 critical/serious issues
- [ ] WAVE: 0 errors
- [ ] Lighthouse: 90+ accessibility score

#### Keyboard Navigation
- [ ] Arrow keys navigate provider radio buttons
- [ ] Tab through all SMTP fields
- [ ] Enter submits form
- [ ] Space toggles test connection checkbox

#### Screen Reader
- [ ] Page title: "Onboarding Wizard - Email Integration (Step 4 of 5)"
- [ ] Radio group: "Email Provider, radio group, 4 options"
- [ ] Each radio: "SMTP, radio button, not selected"
- [ ] Quick reference guide accessible
- [ ] Password field: "Password, password, secured input"

#### Form Accessibility
- [ ] Radio buttons in fieldset with legend
- [ ] Radio buttons have same name attribute
- [ ] Radio buttons have unique value attributes
- [ ] Password autocomplete="current-password" or "new-password"
- [ ] Port type="number" min="1" max="65535"
- [ ] SMTP host autocomplete="off"

---

### Sample Data Step

#### Automated Scan
- [ ] axe DevTools: 0 critical/serious issues
- [ ] WAVE: 0 errors
- [ ] Lighthouse: 90+ accessibility score

#### Keyboard Navigation
- [ ] Tab to import toggle checkbox
- [ ] Space toggles checkboxes
- [ ] Tab through all include checkboxes
- [ ] Toggle shows/hides options (verified with screen reader)

#### Screen Reader
- [ ] Page title: "Onboarding Wizard - Sample Data (Step 5 of 5)"
- [ ] Master checkbox: "Import sample data, checkbox, not checked"
- [ ] Include checkboxes announce state
- [ ] Sample preview section accessible
- [ ] Warning box announced

#### Form Accessibility
- [ ] All checkboxes properly labeled
- [ ] Checkboxes have unique IDs
- [ ] Hidden options have aria-hidden="true" (if applicable)
- [ ] Visible options don't have aria-hidden

---

### Completion Page

#### Automated Scan
- [ ] axe DevTools: 0 critical/serious issues
- [ ] WAVE: 0 errors
- [ ] Lighthouse: 90+ accessibility score

#### Keyboard Navigation
- [ ] Tab through action cards
- [ ] Tab to "Go to Dashboard" button
- [ ] Tab to Restart button (if shown)
- [ ] Enter activates buttons
- [ ] Confetti doesn't interfere with navigation

#### Screen Reader
- [ ] Page title: "Onboarding Complete!"
- [ ] Success message announced
- [ ] Completion summary read correctly
- [ ] Action cards accessible
- [ ] Confetti animation doesn't disrupt (aria-hidden or role="presentation")

#### Visual
- [ ] Success icon visible
- [ ] Confetti doesn't obscure text
- [ ] Completion stats readable
- [ ] CTA button prominent

---

## Cross-Page Accessibility

### Progress Indicator (All Steps)

#### Screen Reader
- [ ] Progress bar: `role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"`
- [ ] Progress text: "40% complete"
- [ ] Current step: `aria-current="step"`
- [ ] Step status announced: "Step 1, Completed" / "Step 2, Current" / "Step 3, Pending"
- [ ] Step links descriptive: "Go to Company Setup step"

#### Visual
- [ ] Completed steps: green checkmark
- [ ] Current step: blue highlight
- [ ] Skipped steps: gray X
- [ ] Pending steps: gray
- [ ] Connector lines: color coded
- [ ] High contrast: all states distinguishable

#### Keyboard
- [ ] Step links focusable (if clickable)
- [ ] Focus indicator clear
- [ ] Enter/Space activates step link

---

### Help Sidebar (All Steps)

#### Screen Reader
- [ ] Sidebar announced as complementary landmark or aside
- [ ] Heading: "Help & Resources"
- [ ] Tips list announced with count
- [ ] Resource links descriptive
- [ ] Pro tip announced (not just visual)

#### Keyboard
- [ ] All resource links focusable
- [ ] Links have clear focus indicator
- [ ] Enter activates links

---

### Navigation Buttons (All Steps)

#### Screen Reader
- [ ] Previous: "Go to previous step" or "Back to [Step Name]"
- [ ] Skip: "Skip this step" (only if allowed)
- [ ] Continue: "Continue to next step" or "Continue to [Step Name]"
- [ ] Complete: "Complete onboarding wizard"
- [ ] Disabled buttons: "disabled" announced

#### Visual
- [ ] Disabled buttons: grayed out (opacity-50)
- [ ] Enabled buttons: full color
- [ ] Hover: color change
- [ ] Focus: ring outline
- [ ] Icons: visible and clear

#### Keyboard
- [ ] All buttons focusable (except disabled)
- [ ] Enter/Space activates
- [ ] Disabled buttons not focusable

---

## Mobile Accessibility

### Touch Targets
- [ ] All buttons ≥ 44x44px
- [ ] Adequate spacing (8px minimum)
- [ ] No overlapping touch targets

### Zoom & Reflow
- [ ] Pinch zoom enabled (no `user-scalable=no`)
- [ ] Content reflows at 200% zoom
- [ ] No horizontal scroll at 320px width
- [ ] Text readable at 200% zoom

### Mobile Screen Reader (VoiceOver iOS / TalkBack Android)
- [ ] Swipe navigation works
- [ ] All elements accessible
- [ ] Form inputs activate keyboard
- [ ] Buttons activate correctly

---

## Color & Contrast

### Contrast Ratios (use WebAIM Contrast Checker)

#### Normal Text (< 18pt)
- [ ] Light mode: ≥ 4.5:1
- [ ] Dark mode: ≥ 4.5:1

#### Large Text (≥ 18pt or bold 14pt)
- [ ] Light mode: ≥ 3:1
- [ ] Dark mode: ≥ 3:1

#### UI Components (buttons, borders, icons)
- [ ] Light mode: ≥ 3:1
- [ ] Dark mode: ≥ 3:1

### Color Usage
- [ ] Color not sole indicator of:
  - Required fields (also asterisk)
  - Errors (also icon and text)
  - Current step (also highlight and text)
  - Completed steps (also checkmark)

---

## Focus Management

### Focus Indicators
- [ ] All focusable elements have visible focus
- [ ] Focus indicator ≥ 3:1 contrast
- [ ] Focus indicator doesn't disappear on click
- [ ] Custom focus styles (ring-2 ring-blue-500)

### Focus Order
- [ ] Tab order logical (top-to-bottom, left-to-right)
- [ ] No focus jumps or skips
- [ ] Modal focus trapped when open
- [ ] Focus returns after modal closes

### Focus States
- [ ] :focus styles defined
- [ ] :focus-visible used (keyboard only)
- [ ] No outline: none without replacement

---

## Dynamic Content

### Loading States
- [ ] Loading announced to screen readers (`aria-live="polite"`)
- [ ] Spinner has `aria-label="Loading"`
- [ ] Button text changes: "Continue" → "Processing..."

### Success/Error Messages
- [ ] Success: `role="status"` or `aria-live="polite"`
- [ ] Errors: `role="alert"` or `aria-live="assertive"`
- [ ] Messages announced automatically
- [ ] Messages don't disappear too quickly (5 seconds minimum)

### Form Validation
- [ ] Real-time errors announced
- [ ] Error summary at top (optional)
- [ ] Focus moves to first error on submit
- [ ] `aria-invalid="true"` on error fields
- [ ] `aria-describedby` links to error message

---

## Browser-Specific Checks

### Chrome
- [ ] Lighthouse Accessibility: 90+
- [ ] DevTools Accessibility Tree shows structure
- [ ] No console errors/warnings

### Firefox
- [ ] Accessibility Inspector shows no issues
- [ ] ARIA attributes recognized
- [ ] Forms work correctly

### Safari
- [ ] VoiceOver compatibility
- [ ] Form autofill works
- [ ] Focus indicators visible

---

## WCAG 2.1 AA Quick Check

### Level A (Must Pass All)
- [ ] 1.1.1: All images have alt text
- [ ] 1.3.1: Content structure semantic (headings, lists, forms)
- [ ] 2.1.1: All functionality keyboard accessible
- [ ] 2.1.2: No keyboard traps
- [ ] 3.3.1: Errors identified in text
- [ ] 3.3.2: Labels/instructions provided
- [ ] 4.1.2: All elements have name, role, value

### Level AA (Must Pass All)
- [ ] 1.4.3: Contrast ratio ≥ 4.5:1 (normal text), ≥ 3:1 (large text)
- [ ] 1.4.10: No horizontal scroll at 320px
- [ ] 1.4.11: UI component contrast ≥ 3:1
- [ ] 2.4.6: Headings and labels descriptive
- [ ] 2.4.7: Focus visible
- [ ] 3.2.3: Navigation consistent
- [ ] 3.3.3: Error suggestions provided
- [ ] 4.1.3: Status messages announced

---

## Common Issues to Watch For

### Forms
- ❌ Missing labels
- ❌ Placeholder as label (insufficient)
- ❌ No error messages
- ❌ Errors not associated with fields
- ❌ No required indicator

### Buttons
- ❌ No focus indicator
- ❌ Icon-only button without aria-label
- ❌ Disabled buttons using color only
- ❌ onClick div (should be <button>)

### Images
- ❌ Missing alt text
- ❌ Alt text says "image" (redundant)
- ❌ Decorative images with alt text
- ❌ Icon fonts without aria-hidden

### Navigation
- ❌ Keyboard trap in modal
- ❌ Illogical tab order
- ❌ Skip links missing
- ❌ No focus visible

### Content
- ❌ Low contrast text
- ❌ Color as only indicator
- ❌ Skipped heading levels
- ❌ No page title
- ❌ Generic link text ("click here")

---

## Sign-Off Criteria

Accessibility testing passes when:

- [x] **axe DevTools**: 0 critical/serious issues (moderate acceptable with justification)
- [x] **WAVE**: 0 errors
- [x] **Lighthouse**: 90+ accessibility score
- [x] **Keyboard**: Complete wizard using only keyboard
- [x] **Screen Reader**: Navigate with NVDA/VoiceOver successfully
- [x] **Mobile**: VoiceOver/TalkBack works correctly
- [x] **Contrast**: All text meets WCAG AA (4.5:1)
- [x] **WCAG 2.1 AA**: All criteria pass

---

## Testing Notes Template

```
Date: [Date]
Tester: [Name]
Page: [Page Name]
Tool: [axe / WAVE / Screen Reader / Keyboard]

Issue: [Description]
Severity: [Critical / High / Medium / Low]
WCAG: [Criterion number]
How to Reproduce: [Steps]
Expected: [Expected behavior]
Actual: [Actual behavior]
Screenshot: [Attach]
```

---

## Quick Reference Links

- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [WAVE Browser Extension](https://wave.webaim.org/extension/)
- [axe DevTools](https://www.deque.com/axe/devtools/)
- [WCAG 2.1 Quick Reference](https://www.w3.org/WAI/WCAG21/quickref/)
- [NVDA Download](https://www.nvaccess.org/download/)
- [Screen Reader Basics](https://webaim.org/articles/screenreader_testing/)

---

**Last Updated**: 2026-01-12
**Version**: 1.0
