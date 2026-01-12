# Onboarding Wizard Documentation

This directory contains comprehensive documentation for the Laravel CRM Interactive Onboarding Wizard.

## 📚 Documentation Files

### User Documentation

#### [Onboarding Wizard User Guide](./onboarding-wizard-user-guide.md)
**Audience**: End users, administrators, team members

Complete user guide for completing the onboarding wizard, including:
- Introduction and overview
- Step-by-step instructions for all 5 wizard steps
- How to resume, skip, and restart the wizard
- Tips, best practices, and troubleshooting
- Comprehensive FAQ (50+ questions)

**When to use**:
- New users completing onboarding for the first time
- Administrators training team members
- Reference when stuck on a particular step
- Troubleshooting onboarding issues

---

#### [Screenshot Capture Guide](./onboarding-wizard-screenshot-guide.md)
**Audience**: Technical writers, documentation team, QA engineers

Detailed instructions for capturing screenshots to complete the user guide, including:
- 30+ screenshot specifications
- Setup instructions for each screenshot
- Recommended dimensions and formats
- File naming conventions
- Quality checklist

**When to use**:
- Capturing screenshots for the user guide
- Updating screenshots after UI changes
- Creating training materials or videos
- Ensuring consistent visual documentation

---

#### [Onboarding Wizard Developer Guide](./onboarding-wizard-developer-guide.md)
**Audience**: Developers, extension authors, technical teams

Comprehensive developer guide for extending and customizing the onboarding wizard, including:
- Architecture overview and core components
- Step-by-step guide to adding new wizard steps
- Customizing existing steps and behavior
- Extending the system with hooks and events
- Configuration reference and API documentation
- Frontend integration (AJAX, Blade components)
- Testing strategies and examples
- Best practices and troubleshooting

**When to use**:
- Adding custom setup steps for your extensions
- Modifying wizard flow based on business requirements
- Integrating third-party services during onboarding
- Understanding the wizard architecture
- Contributing to the onboarding system

---

## 🎯 Quick Start

### For End Users

1. **Start the wizard**: Log in to your CRM account as a new user
2. **Follow the guide**: Open [Onboarding Wizard User Guide](./onboarding-wizard-user-guide.md)
3. **Complete steps**: Work through the 5 setup steps at your own pace
4. **Get help**: Use the FAQ section if you encounter issues

### For Administrators

1. **Review the guide**: Familiarize yourself with the user guide
2. **Customize configuration**: Edit `config/onboarding.php` to adjust wizard settings
3. **Train your team**: Share the user guide with new team members
4. **Monitor completion**: Check admin dashboard for onboarding statistics

### For Documentation Team

1. **Review screenshot guide**: Read the [Screenshot Capture Guide](./onboarding-wizard-screenshot-guide.md)
2. **Set up environment**: Prepare clean CRM installation with sample data
3. **Capture screenshots**: Follow specifications for each of the 30+ screenshots
4. **Update guide**: Replace screenshot placeholders with actual images
5. **Optimize files**: Compress images and verify quality

### For Developers

1. **Read developer guide**: Open [Onboarding Wizard Developer Guide](./onboarding-wizard-developer-guide.md)
2. **Understand architecture**: Review core components and data flow
3. **Add custom steps**: Follow step-by-step guide to create new wizard steps
4. **Extend functionality**: Use hooks, events, and middleware for custom behavior
5. **Write tests**: Ensure all changes are covered by unit and feature tests

---

## 📖 Onboarding Wizard Overview

### What It Does

The Interactive Onboarding Wizard is a guided, step-by-step setup experience that helps new users:
- Configure their CRM instance quickly (~15 minutes)
- Set up essential features without reading extensive documentation
- Import sample data to explore the system
- Get started with confidence

### The 5 Steps

1. **Company Setup** (Required, ~3 minutes)
   - Configure company profile and basic information
   - Fields: Name, industry, size, address, phone, website

2. **User Creation** (Optional, ~2 minutes)
   - Add first team member with role assignment
   - Send invitation email automatically

3. **Pipeline Configuration** (Optional, ~5 minutes)
   - Set up sales pipeline stages
   - Customize or use default template
   - Define win probabilities

4. **Email Integration** (Optional, ~4 minutes)
   - Connect email account (SMTP, Gmail, Outlook, SendGrid)
   - Test connection before saving
   - Enable email communication from CRM

5. **Sample Data Import** (Optional, ~1 minute)
   - Import realistic sample data
   - Options: Companies, contacts, deals
   - Great for learning and training

### Key Features

- ✅ **Auto-trigger**: Automatically appears for new users
- ✅ **Auto-save**: Progress saved automatically at each step
- ✅ **Resume capability**: Pause and continue anytime
- ✅ **Skip option**: Optional steps can be skipped
- ✅ **Contextual help**: Help tooltips and sidebar on every step
- ✅ **Progress tracking**: Visual progress indicator and statistics
- ✅ **Mobile responsive**: Works on desktop, tablet, and mobile
- ✅ **Dark mode**: Full dark mode support
- ✅ **Accessible**: WCAG 2.1 AA compliant

---

## 🔧 Configuration

### Environment Variables

```env
# Enable/disable onboarding wizard globally
ONBOARDING_ENABLED=true

# Auto-trigger for new users
ONBOARDING_AUTO_TRIGGER=true

# Allow users to skip optional steps
ONBOARDING_ALLOW_SKIP=true

# Allow users to restart wizard after completion
ONBOARDING_ALLOW_RESTART=true

# Video tutorial URLs (optional)
ONBOARDING_VIDEO_COMPANY_SETUP=https://www.youtube.com/embed/...
ONBOARDING_VIDEO_USER_CREATION=https://www.youtube.com/embed/...
ONBOARDING_VIDEO_PIPELINE_CONFIG=https://www.youtube.com/embed/...
ONBOARDING_VIDEO_EMAIL_INTEGRATION=https://www.youtube.com/embed/...
ONBOARDING_VIDEO_SAMPLE_DATA=https://www.youtube.com/embed/...
```

### Configuration File

Edit `config/onboarding.php` to customize:
- Step metadata (titles, descriptions, icons)
- Field configurations for each step
- Validation rules
- Help text and tips
- Skippability settings
- Completion redirect URL
- UI customization

### Database

The wizard uses the `onboarding_progress` table to track:
- User's current step
- Completed steps (JSON)
- Skipped steps (JSON)
- Completion status
- Started and completed timestamps

---

## 🎨 UI Components

The wizard includes reusable Blade components:

### Layout Components
- `onboarding.layout` - Main wizard layout with progress indicator
- `onboarding.progress-indicator` - Visual stepper component

### Form Components
- `onboarding.field-help` - Form labels with tooltip help
- `onboarding.info-panel` - Contextual information boxes
- `onboarding.tooltip` - Help tooltips with hover/click
- `onboarding.video-embed` - Embedded video tutorials

### Step Views
- `onboarding.steps.company_setup`
- `onboarding.steps.user_creation`
- `onboarding.steps.pipeline_config`
- `onboarding.steps.email_integration`
- `onboarding.steps.sample_data`

---

## 🚀 Routes

### Web Routes

```php
// Main routes
GET  /onboarding                        // Welcome screen
GET  /onboarding/step/{step}            // Show specific step
POST /onboarding/step/{step}            // Submit step data

// Navigation routes
POST /onboarding/next                   // Move to next step
POST /onboarding/previous               // Move to previous step

// Action routes
POST /onboarding/skip/{step}            // Skip a step
POST /onboarding/complete               // Mark as complete
POST /onboarding/restart                // Restart wizard

// AJAX routes (for progress tracking)
GET  /onboarding/progress               // Get current progress
GET  /onboarding/statistics             // Get onboarding stats
POST /onboarding/validate/{step}        // Validate step data
```

### API Routes

```php
// All routes under /api/onboarding prefix
GET  /api/onboarding/progress           // Get progress (JSON)
GET  /api/onboarding/statistics         // Get stats (JSON)
POST /api/onboarding/validate/{step}    // Validate step
POST /api/onboarding/update/{step}      // Update step
POST /api/onboarding/skip/{step}        // Skip step
POST /api/onboarding/next               // Next step
POST /api/onboarding/previous           // Previous step
POST /api/onboarding/complete           // Complete wizard
POST /api/onboarding/restart            // Restart wizard
```

---

## 📊 Admin Dashboard Integration

### Onboarding Statistics Widget

Administrators can view onboarding metrics in the dashboard:

**Metrics Displayed**:
- **Completion Rate**: Percentage of users who completed onboarding
- **Average Completion Time**: Mean time to complete all steps
- **Total Started**: Number of users who started onboarding
- **Total Completed**: Number of users who finished

**Step Analytics**:
- Completion count per step
- Skip count per step
- Visual progress bars

**Access**: Dashboard → Onboarding Stats Widget

---

## 🔐 Permissions

### ACL Configuration

```php
// Admin ACL
'settings' => [
    'other_settings' => [
        'onboarding' => [
            'index'   => 'settings.other_settings.onboarding.index',
            'restart' => 'settings.other_settings.onboarding.restart',
        ],
    ],
],
```

### Required Permissions

- **View Onboarding Settings**: `settings.other_settings.onboarding.index`
- **Restart Wizard**: `settings.other_settings.onboarding.restart`
- **Access Wizard**: All authenticated users (automatically granted)

---

## ✉️ Email Notifications

### Onboarding Complete Email

Sent automatically when user completes the wizard (if email is configured).

**Template**: `app/Notifications/OnboardingComplete.php`
**View**: `resources/views/emails/onboarding/complete.blade.php`

**Contents**:
- Personalized greeting
- Completion statistics
- Next steps recommendations
- Quick start guide for key features
- Resource links (docs, tutorials, support)
- Dashboard CTA button

**Configuration**:
```php
// config/onboarding.php
'completion' => [
    'send_email' => env('ONBOARDING_SEND_COMPLETION_EMAIL', true),
],
```

---

## 🧪 Testing

### Test Files

- `tests/Unit/Services/OnboardingServiceTest.php` - Service unit tests (60+ tests)
- `tests/Feature/OnboardingWizardFlowTest.php` - End-to-end flow tests (30+ tests)
- `tests/Unit/Services/Steps/*StepTest.php` - Individual step tests (98+ tests)
- `tests/Feature/Api/OnboardingApiControllerTest.php` - API tests (17+ tests)

### Running Tests

```bash
# Run all onboarding tests
php artisan test --filter=Onboarding

# Run specific test file
php artisan test tests/Feature/OnboardingWizardFlowTest.php

# Run with coverage
php artisan test --filter=Onboarding --coverage

# Run specific test method
php artisan test --filter=test_user_can_complete_entire_wizard_flow
```

---

## 📱 Mobile Support

The wizard is fully responsive and works on:
- ✅ Desktop (1920x1080+)
- ✅ Laptop (1440x900)
- ✅ Tablet (768x1024)
- ✅ Mobile (375x667+)

**Recommendations**:
- Desktop/laptop for best experience
- Tablet is acceptable
- Mobile works but forms are easier on larger screens

---

## ♿ Accessibility

The wizard follows WCAG 2.1 AA guidelines:

- ✅ **Keyboard Navigation**: Full keyboard support (Tab, Enter, Esc)
- ✅ **Screen Reader**: ARIA labels, roles, and landmarks
- ✅ **Focus Management**: Visible focus indicators
- ✅ **Color Contrast**: Sufficient contrast ratios (4.5:1+)
- ✅ **Semantic HTML**: Proper heading hierarchy
- ✅ **Form Labels**: All inputs properly labeled
- ✅ **Error Identification**: Clear error messages

**Testing**:
- Tested with NVDA (Windows)
- Tested with VoiceOver (macOS, iOS)
- Tested with TalkBack (Android)
- Keyboard-only navigation verified

---

## 🌐 Internationalization (i18n)

### Translation Files

Translations are stored in:
- `packages/Webkul/Admin/src/Resources/lang/en/app.php` (Admin UI)
- `config/onboarding.php` (Step metadata - can be translated)

### Adding New Languages

1. Copy English translations to new language file
2. Translate step titles, descriptions, help text
3. Update configuration for language-specific content
4. Test wizard in new language

---

## 🐛 Troubleshooting

### Common Issues

See the **Troubleshooting** section in [User Guide](./onboarding-wizard-user-guide.md#troubleshooting) for:
- Progress not saving
- Cannot skip optional steps
- Email connection test fails
- Sample data import fails
- Wizard doesn't auto-trigger
- Cannot restart wizard
- Browser-specific issues
- Mobile issues

### Debug Mode

Enable debug logging:
```php
// config/onboarding.php
'debug' => env('ONBOARDING_DEBUG', false),
```

Check logs:
```bash
tail -f storage/logs/laravel.log | grep -i onboarding
```

---

## 📝 Developer Resources

### Related Documentation

- **API Documentation**: `docs/TERRITORY_API_DOCS.md` (similar pattern)
- **Implementation Guide**: `docs/compliance-implementation-guide.md` (architecture reference)
- **Developer Guide**: `packages/Webkul/Marketplace/docs/DEVELOPER-GUIDE.md` (extending features)

### Service Classes

- `app/Services/OnboardingService.php` - Core wizard logic
- `app/Services/Onboarding/Steps/*Step.php` - Individual step implementations
- `app/Contracts/WizardStepContract.php` - Step interface
- `app/Services/Onboarding/AbstractWizardStep.php` - Base step class

### Controllers

- `app/Http/Controllers/OnboardingController.php` - Web routes
- `app/Http/Controllers/Api/OnboardingApiController.php` - API endpoints
- `packages/Webkul/Admin/src/Http/Controllers/Settings/OnboardingController.php` - Admin settings

### Models

- `app/Models/OnboardingProgress.php` - Progress tracking model

### Middleware

- `app/Http/Middleware/RedirectIfOnboardingIncomplete.php` - Auto-trigger logic

---

## 🎥 Video Tutorials

### Creating Video Content

The wizard supports embedded video tutorials on each step. To add videos:

1. Create video tutorials for each step
2. Upload to YouTube, Vimeo, or self-host
3. Add video URLs to environment variables:
   ```env
   ONBOARDING_VIDEO_COMPANY_SETUP=https://www.youtube.com/embed/xxxxx
   ONBOARDING_VIDEO_USER_CREATION=https://www.youtube.com/embed/xxxxx
   # ... etc
   ```
4. Videos appear in help sidebar automatically

**Recommended Format**:
- Duration: 2-5 minutes per step
- Resolution: 1080p
- Format: MP4 (for self-hosted)
- Narration: Clear voice-over explaining each field
- Editing: Add captions and highlights

---

## 📈 Analytics

### Tracking Metrics

The wizard tracks:
- Total users who started onboarding
- Total users who completed onboarding
- Completion rate (percentage)
- Average completion time
- Time spent per step
- Steps completed vs. skipped
- Drop-off points

### Viewing Analytics

1. Navigate to **Admin Dashboard**
2. Find **Onboarding Stats** widget
3. View metrics and charts

### Exporting Data

```php
use App\Models\OnboardingProgress;

// Get all progress records
$progress = OnboardingProgress::all();

// Get completion statistics
$stats = OnboardingProgress::getStatistics();

// Export to CSV
// (Implementation may vary)
```

---

## 🔄 Maintenance

### Regular Tasks

**Monthly**:
- Review completion rates and drop-off points
- Analyze user feedback and support tickets
- Update help text based on common issues

**Quarterly**:
- Review and update screenshots (if UI changed)
- Test wizard end-to-end on all browsers
- Update video tutorials if steps changed

**Yearly**:
- Comprehensive documentation review
- Accessibility audit
- Performance optimization

### Version Control

When making changes:
1. Update relevant documentation
2. Capture new screenshots if UI changed
3. Update version numbers in docs
4. Test thoroughly before deploying
5. Announce changes to users

---

## 🤝 Contributing

### Documentation Improvements

To improve this documentation:
1. Fork the repository
2. Make changes to relevant .md files
3. Capture/update screenshots following the guide
4. Submit pull request with description
5. Documentation team will review

### Reporting Issues

Found a problem with the wizard or documentation?
1. Check the [FAQ](./onboarding-wizard-user-guide.md#faq) first
2. Search existing issues
3. Create new issue with:
   - Clear description
   - Steps to reproduce
   - Expected vs. actual behavior
   - Screenshots if applicable
   - Browser and version

---

## 📞 Support

### Getting Help

- **Documentation**: Read the [User Guide](./onboarding-wizard-user-guide.md)
- **FAQ**: Check the [FAQ section](./onboarding-wizard-user-guide.md#faq)
- **Support Tickets**: Submit via Settings → Support
- **Community Forum**: Ask questions and share tips
- **Email**: support@example.com

### For Developers

- **GitHub Issues**: Report bugs and request features
- **Developer Guide**: See related developer documentation
- **API Documentation**: Check API endpoint specifications
- **Code Examples**: Review test files for usage examples

---

## 📄 License

This onboarding wizard is part of Laravel CRM and is subject to the project's license terms.

---

## 📋 Document Information

- **Created**: January 2026
- **Version**: 1.0
- **Last Updated**: January 2026
- **Maintained By**: Documentation Team
- **Related Feature**: Interactive Onboarding Wizard (Spec #010)

---

## ✅ Checklist for Documentation Completion

- [x] User guide written
- [x] Screenshot guide created
- [x] README overview completed
- [ ] Screenshots captured (30+ images)
- [ ] Screenshots added to user guide
- [ ] Video tutorials created (optional)
- [ ] Translations added (if multi-language support)
- [ ] Documentation reviewed by team
- [ ] User testing completed
- [ ] Final proofreading

**Status**: User guide and screenshot guide complete. Screenshots need to be captured using the screenshot guide instructions.

---

**End of README**
