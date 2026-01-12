# Onboarding Wizard Screenshot Capture Guide

This document provides instructions for capturing screenshots to complete the onboarding wizard user guide.

## Overview

The user guide (`onboarding-wizard-user-guide.md`) contains 30+ screenshot placeholders that need to be captured. This guide provides detailed instructions for each screenshot, including:

- What to show in the screenshot
- How to set up the screen
- Recommended dimensions
- File naming conventions

## General Guidelines

### Screenshot Specifications

- **Format**: PNG (lossless compression)
- **Resolution**: High DPI / Retina (2x resolution recommended)
- **Dimensions**:
  - Full screen: 1920x1080px or 2560x1440px
  - Partial screen: Crop to relevant area with ~20px padding
- **Browser**: Use latest Chrome or Firefox
- **Window Size**: Maximize browser window or use consistent viewport (1440x900)
- **Color Mode**: Capture both light and dark mode where applicable

### Preparation

1. **Clean Installation**:
   - Use a fresh CRM installation
   - Clear browser cache and cookies
   - Use a clean user profile (no extensions)

2. **Sample Data**:
   - Use realistic but generic company name: "Acme Corporation"
   - Use generic email: "admin@example.com"
   - Use placeholder data that matches examples in the guide

3. **Browser Setup**:
   - Disable browser extensions
   - Set zoom to 100%
   - Use consistent browser chrome (show/hide consistently)

4. **Tools**:
   - Built-in browser screenshot tools
   - Or screenshot tools like Snagit, Skitch, or macOS Screenshot
   - Image editor for annotations (arrows, highlights)

### Annotations

Some screenshots benefit from annotations:
- **Red arrows**: Point to specific UI elements
- **Red boxes**: Highlight important areas
- **Blur**: Obscure sensitive information (if any)
- **Numbering**: For multi-step processes

### File Naming

All screenshot files should be placed in: `docs/images/onboarding/`

Use kebab-case naming matching the placeholders in the guide.

## Screenshot List

### 1. Auto-Trigger and Access

#### `onboarding-auto-trigger.png`
**Section**: Getting Started → Accessing the Onboarding Wizard → For New Installations

**What to show**:
- Browser window showing automatic redirect after first login
- URL bar showing `/onboarding` or `/onboarding/welcome`
- Welcome screen visible

**Setup**:
1. Create a fresh user account
2. Log in for the first time
3. Capture immediately after redirect
4. Show browser chrome with URL bar

**Dimensions**: Full screen (1920x1080)

**Annotations**: Optional arrow pointing to URL

---

#### `onboarding-settings-restart.png`
**Section**: Getting Started → Accessing the Onboarding Wizard → Manual Access

**What to show**:
- Settings page with Onboarding section visible
- Restart button clearly visible
- Progress summary showing completed status

**Setup**:
1. Complete the onboarding wizard first
2. Navigate to Settings → Other Settings → Onboarding
3. Ensure settings page is fully loaded
4. Capture full page or relevant section

**Dimensions**: Crop to settings content area (1200x800)

**Annotations**: Red box around "Restart Onboarding" button

---

### 2. Welcome Screen

#### `onboarding-welcome-screen.png`
**Section**: Step-by-Step Guide → Welcome Screen

**What to show**:
- Full welcome page
- All 5 steps listed with icons, descriptions, and time estimates
- Feature highlights (Quick Setup, Auto-Save, Flexible)
- "Get Started" button
- Optional: "Resume" link if applicable

**Setup**:
1. Start fresh onboarding or restart
2. Land on welcome screen (`/onboarding`)
3. Ensure all content is visible (may need to scroll and stitch)
4. Show complete page from top to bottom

**Dimensions**: Full page screenshot (may be tall, ~1920x2400)

**Annotations**: None

---

#### `onboarding-welcome-resume-link.png`
**Section**: Resuming the Wizard → From Welcome Screen

**What to show**:
- Bottom portion of welcome screen
- "Already started?" message
- "Resume from [Step Name]" link highlighted

**Setup**:
1. Start onboarding and complete 1-2 steps
2. Log out and log back in
3. Navigate to welcome screen
4. Capture bottom section showing resume link

**Dimensions**: Crop to bottom section (1200x400)

**Annotations**: Red box or arrow pointing to resume link

---

### 3. Step 1: Company Setup

#### `onboarding-step1-company-setup.png`
**Section**: Step 1: Company Setup

**What to show**:
- Complete Company Setup form with all fields visible
- Progress indicator at top showing Step 1
- Help sidebar on right
- All 6 form fields (name, industry, size, phone, website, address)
- Info panel
- Navigation buttons at bottom

**Setup**:
1. Navigate to Step 1 (Company Setup)
2. Leave all fields empty
3. Show complete page (may need scroll + stitch)

**Dimensions**: Full page (1920x1400)

**Annotations**: None

---

#### `onboarding-step1-filled.png`
**Section**: Step 1: Company Setup → Step-by-Step Instructions

**What to show**:
- Same Company Setup form with sample data filled in
- Company Name: "Acme Corporation"
- Industry: "Technology" selected
- Company Size: "51-200" selected
- Phone: "+1 (555) 123-4567"
- Website: "https://www.acmecorp.com"
- Address: "123 Main Street, New York, NY 10001"

**Setup**:
1. Fill in all fields with sample data
2. Capture complete form

**Dimensions**: Full page (1920x1400)

**Annotations**: None (or optional checkmarks next to filled fields)

---

### 4. Step 2: User Creation

#### `onboarding-step2-user-creation.png`
**Section**: Step 2: User Creation

**What to show**:
- User Creation form with all fields empty
- Progress indicator showing Step 2
- Full Name field
- Email Address field
- Role dropdown (not expanded)
- Send Invitation checkbox
- Help sidebar
- Navigation buttons (Previous, Continue, Skip)

**Setup**:
1. Navigate to Step 2
2. Leave all fields empty
3. Capture full page

**Dimensions**: Full page (1920x1200)

**Annotations**: None

---

#### `onboarding-step2-filled.png`
**Section**: Step 2: User Creation → Step-by-Step Instructions

**What to show**:
- User Creation form with sample data
- Full Name: "Jane Smith"
- Email: "jane.smith@acmecorp.com"
- Role: "Sales Rep" selected
- Send Invitation: Checked

**Setup**:
1. Fill in fields with sample data
2. Check the invitation checkbox
3. Capture full page

**Dimensions**: Full page (1920x1200)

**Annotations**: Optional checkmark highlighting invitation checkbox

---

### 5. Step 3: Pipeline Configuration

#### `onboarding-step3-pipeline-config.png`
**Section**: Step 3: Pipeline Configuration

**What to show**:
- Pipeline configuration interface
- Progress indicator showing Step 3
- List of default pipeline stages
- Drag handles visible
- Add Stage button
- Help sidebar with pipeline tips

**Setup**:
1. Navigate to Step 3
2. Show default stages
3. Capture full page

**Dimensions**: Full page (1920x1200)

**Annotations**: Optional arrows pointing to drag handles

---

#### `onboarding-step3-default-stages.png`
**Section**: Step 3: Pipeline Configuration → Option 1: Use Default Pipeline Template

**What to show**:
- Close-up of default pipeline stages
- All 6 stages clearly visible:
  - New (10%)
  - Qualified (20%)
  - Proposal (40%)
  - Negotiation (60%)
  - Won (100%)
  - Lost (0%)

**Setup**:
1. Capture just the stages section
2. Ensure all stages and probabilities are readable

**Dimensions**: Crop to stages area (1200x600)

**Annotations**: None

---

#### `onboarding-step3-custom-stages.png`
**Section**: Step 3: Pipeline Configuration → Option 2: Customize Pipeline Stages

**What to show**:
- Pipeline with custom/edited stages
- At least one stage being edited (edit mode visible)
- Drag-and-drop in action (if possible)
- Different stage order or custom names

**Setup**:
1. Add or edit a stage (e.g., "Demo Scheduled")
2. Show edit interface
3. Capture during editing

**Dimensions**: Full page (1920x1200)

**Annotations**: Red box around stage being edited

---

### 6. Step 4: Email Integration

#### `onboarding-step4-email-integration.png`
**Section**: Step 4: Email Integration

**What to show**:
- Email integration form
- Progress indicator showing Step 4
- Provider dropdown (not expanded)
- All SMTP fields visible
- Test connection checkbox
- Quick setup guide section
- Help sidebar

**Setup**:
1. Navigate to Step 4
2. Leave provider as default or "SMTP"
3. Leave all fields empty
4. Capture full page

**Dimensions**: Full page (1920x1400)

**Annotations**: None

---

#### `onboarding-step4-gmail.png`
**Section**: Step 4: Email Integration → Option 1: Quick Setup with Gmail

**What to show**:
- Provider dropdown showing "Gmail" selected
- Pre-filled SMTP host (smtp.gmail.com)
- Pre-filled SMTP port (587)
- Pre-filled encryption (TLS)
- Email and password fields (empty)
- Test connection checkbox

**Setup**:
1. Select "Gmail" from provider dropdown
2. Show auto-filled fields
3. Leave credentials empty
4. Capture full page

**Dimensions**: Full page (1920x1400)

**Annotations**: Highlight the pre-filled fields

---

#### `onboarding-step4-outlook.png`
**Section**: Step 4: Email Integration → Option 2: Quick Setup with Outlook

**What to show**:
- Provider dropdown showing "Outlook" selected
- Pre-filled Outlook SMTP settings
- smtp.office365.com visible
- Port 587
- TLS encryption

**Setup**:
1. Select "Outlook" from dropdown
2. Show pre-filled Outlook settings
3. Capture form

**Dimensions**: Full page (1920x1400)

**Annotations**: None

---

#### `onboarding-step4-smtp.png`
**Section**: Step 4: Email Integration → Option 3: Manual SMTP Configuration

**What to show**:
- Provider dropdown showing "SMTP (Generic)" selected
- All manual SMTP fields visible
- No pre-filled values
- All field labels clear

**Setup**:
1. Select "SMTP (Generic)"
2. Leave all fields empty
3. Capture form

**Dimensions**: Full page (1920x1400)

**Annotations**: None

---

#### `onboarding-step4-error.png`
**Section**: Troubleshooting → Email Connection Test Fails

**What to show**:
- Email integration form
- Error message displayed (red alert box)
- Error text: "Connection failed: Authentication error"
- Form still visible above error

**Setup**:
1. Enter incorrect SMTP credentials deliberately
2. Enable "Test connection"
3. Click Continue
4. Capture error message

**Dimensions**: Crop to form + error (1200x800)

**Annotations**: Red arrow pointing to error message

---

### 7. Step 5: Sample Data Import

#### `onboarding-step5-sample-data.png`
**Section**: Step 5: Sample Data Import

**What to show**:
- Sample data import page
- Progress indicator showing Step 5 (final step)
- Three checkbox options:
  - Import sample companies
  - Import sample contacts
  - Import sample deals
- Info panel explaining what will be imported
- Warning panel about deleting later
- Navigation buttons

**Setup**:
1. Navigate to Step 5
2. Leave all checkboxes in default state (checked)
3. Capture full page

**Dimensions**: Full page (1920x1200)

**Annotations**: None

---

#### `onboarding-step5-all-selected.png`
**Section**: Step 5: Sample Data Import → Option 1: Import All Sample Data

**What to show**:
- All three checkboxes checked
- Preview list of sample companies visible
- Info panel showing import details

**Setup**:
1. Ensure all checkboxes are checked
2. Expand any preview sections
3. Capture

**Dimensions**: Full page (1920x1200)

**Annotations**: Green checkmarks next to selected options

---

#### `onboarding-step5-selective.png`
**Section**: Step 5: Sample Data Import → Option 2: Selective Import

**What to show**:
- Only "Import sample companies" checked
- Other checkboxes unchecked or disabled
- Warning message about dependencies (if shown)

**Setup**:
1. Uncheck contacts and deals
2. Show dependency behavior
3. Capture

**Dimensions**: Full page (1920x1200)

**Annotations**: Highlight disabled checkboxes

---

### 8. Completion Screen

#### `onboarding-complete.png`
**Section**: Completion Screen

**What to show**:
- Full completion screen
- Success icon with confetti (capture during animation if possible)
- "Setup Complete!" heading
- Completion summary with statistics
- List of completed steps
- "What's next?" section with action cards
- Help resources
- Main CTA button
- Restart option (if visible)

**Setup**:
1. Complete entire wizard
2. Land on completion screen
3. Capture immediately (for confetti animation)
4. May need full page screenshot

**Dimensions**: Full page (1920x2000+)

**Annotations**: None

---

#### `onboarding-complete-summary.png`
**Section**: Completion Screen → What You'll See

**What to show**:
- Close-up of completion summary section
- Three statistics cards:
  - Steps Completed
  - Steps Skipped
  - Minutes Spent
- Configured features list with checkmarks

**Setup**:
1. Crop to just the summary section
2. Ensure all numbers are visible
3. Show realistic statistics (e.g., 3 completed, 2 skipped, 12 minutes)

**Dimensions**: Crop to summary (1000x600)

**Annotations**: None

---

#### `onboarding-completion-email.png`
**Section**: Completion Screen → Completion Email

**What to show**:
- Email inbox view OR email template preview
- Subject line: "Welcome to [CRM Name] - Setup Complete! 🎉"
- Email body with completion stats
- Next steps section
- Quick start guide
- Resources links
- Dashboard CTA button

**Setup**:
1. Check email after completing wizard
2. Open welcome email
3. Capture full email body
4. OR capture email template preview from admin panel

**Dimensions**: Email viewport (800x1200)

**Annotations**: None

---

### 9. Progress and Navigation

#### `onboarding-auto-resume.png`
**Section**: Resuming the Wizard → Automatic Redirect

**What to show**:
- Login screen or dashboard redirect
- Flash message: "Resuming onboarding from [Step Name]"
- Or direct landing on incomplete step
- URL showing onboarding route

**Setup**:
1. Start wizard, complete 1-2 steps
2. Log out
3. Log back in
4. Capture redirect in action or flash message

**Dimensions**: Full screen (1920x1080)

**Annotations**: Highlight flash message

---

#### `onboarding-progress-with-skipped.png`
**Section**: Skipping Steps → Skipped Steps in Progress Indicator

**What to show**:
- Progress indicator at top of a step
- Mix of completed, current, skipped, and pending steps
- Visual indicators:
  - Green checkmark for completed
  - Blue circle for current
  - Gray X for skipped
  - Gray circle for pending

**Setup**:
1. Complete steps 1-2
2. Skip step 3
3. Land on step 4
4. Capture progress indicator

**Dimensions**: Crop to progress indicator (1920x200)

**Annotations**: Labels pointing to each state

---

#### `onboarding-skip-button.png`
**Section**: Skipping Steps → How to Skip a Step

**What to show**:
- Bottom navigation section of a step
- Three buttons visible:
  - Previous (left)
  - Continue (center-right)
  - Skip (right)
- Skip button highlighted/emphasized

**Setup**:
1. Navigate to any skippable step (e.g., User Creation)
2. Scroll to bottom navigation
3. Capture buttons

**Dimensions**: Crop to navigation area (1200x200)

**Annotations**: Red arrow or box around Skip button

---

#### `onboarding-skip-confirmation.png`
**Section**: Skipping Steps → How to Skip a Step

**What to show**:
- Modal dialog for skip confirmation
- Title: "Skip this step?"
- Message explaining what happens when skipped
- Cancel and Confirm buttons

**Setup**:
1. Click Skip button
2. Capture modal dialog
3. Show background dimmed

**Dimensions**: Modal view (800x400)

**Annotations**: None

---

### 10. Restart Functionality

#### `onboarding-restart-settings.png`
**Section**: Restarting the Wizard → From Settings

**What to show**:
- Settings → Onboarding page
- Current progress summary:
  - Completion status badge
  - Completed steps count
  - Skipped steps count
  - Progress percentage
  - Progress bar
- "Restart Onboarding" button
- Warning text about restart

**Setup**:
1. Complete wizard
2. Navigate to Settings → Other Settings → Onboarding
3. Show complete settings page
4. Capture

**Dimensions**: Full settings area (1200x800)

**Annotations**: Red box around Restart button

---

#### `onboarding-restart-modal.png`
**Section**: Restarting the Wizard → From Settings

**What to show**:
- Restart confirmation modal
- Warning icon
- Title: "Restart Onboarding Wizard?"
- Warning message about what restart does
- Cancel and Restart buttons

**Setup**:
1. Click Restart Onboarding button
2. Capture modal dialog
3. Show dimmed background

**Dimensions**: Modal view (800x400)

**Annotations**: None

---

### 11. Troubleshooting

#### `troubleshooting-validation-errors.png`
**Section**: Troubleshooting → Progress Not Saving → Form Validation Error

**What to show**:
- Form with validation errors
- Red error messages below fields
- Fields with red borders
- Multiple errors visible
- Continue button (showing errors prevent submission)

**Setup**:
1. Leave required fields empty
2. Try to submit (click Continue)
3. Capture validation errors

**Dimensions**: Crop to form with errors (1200x800)

**Annotations**: Red arrows pointing to error messages

---

## Screenshot Capture Workflow

### Step-by-Step Process

1. **Set up environment**:
   ```bash
   # Fresh database
   php artisan migrate:fresh --seed

   # Create admin user
   php artisan tinker
   >>> User::factory()->create(['email' => 'admin@example.com', 'name' => 'Admin User'])

   # Clear cache
   php artisan config:clear
   php artisan view:clear
   ```

2. **Prepare browser**:
   - Open Chrome in incognito mode
   - Set window size: 1440x900 (or maximize)
   - Zoom: 100%
   - Clear any previous data

3. **Capture workflow**:
   - Log in as new user
   - Progress through wizard in order
   - Capture each screenshot at appropriate time
   - Use consistent data across all screenshots

4. **Organize files**:
   ```
   docs/images/onboarding/
   ├── onboarding-auto-trigger.png
   ├── onboarding-welcome-screen.png
   ├── onboarding-step1-company-setup.png
   ├── onboarding-step1-filled.png
   ├── ... (all other screenshots)
   ```

5. **Optimize images**:
   - Use PNG format
   - Compress without losing quality (TinyPNG, ImageOptim)
   - Verify all text is readable
   - Check file sizes (<500KB per image recommended)

6. **Update guide**:
   - Replace placeholders with actual image references:
     ```markdown
     **Screenshot**: `onboarding-welcome-screen.png`
     <!-- Replace with: -->
     ![Onboarding Welcome Screen](images/onboarding/onboarding-welcome-screen.png)
     ```

## Quality Checklist

Before finalizing screenshots:

- [ ] All screenshots captured at consistent resolution
- [ ] No sensitive data visible (use sample data)
- [ ] Text is readable and crisp
- [ ] UI is fully loaded (no loading spinners)
- [ ] Consistent browser chrome (always show or hide URL bar)
- [ ] No browser extensions visible
- [ ] No notification badges or popups
- [ ] Proper focus state (no developer tools open)
- [ ] Annotations are clear and not obtrusive
- [ ] File names match placeholders exactly
- [ ] Images are optimized (<500KB each)
- [ ] Both light and dark mode captured (if applicable)

## Alternative: Video Walkthrough

Consider creating a video walkthrough in addition to screenshots:

- **Duration**: 10-15 minutes
- **Format**: MP4, 1080p
- **Narration**: Voice-over explaining each step
- **Editing**: Add captions and highlights
- **Chapters**: Mark each of the 5 steps
- **Host**: YouTube, Vimeo, or self-hosted

Video file: `docs/videos/onboarding-wizard-walkthrough.mp4`

## Maintenance

Screenshots should be updated when:
- UI design changes significantly
- New features are added to the wizard
- Form fields are added/removed
- User flow is modified
- Rebranding occurs

**Recommended review**: Quarterly or with major releases

---

**Document Version**: 1.0
**Last Updated**: January 2026
**Related**: onboarding-wizard-user-guide.md
