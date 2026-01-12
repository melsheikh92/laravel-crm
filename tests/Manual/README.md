# Manual Testing Documentation

This directory contains comprehensive manual testing guides and checklists for the Interactive Onboarding Wizard.

## Contents

### 1. ONBOARDING_MANUAL_QA_GUIDE.md
**Complete manual testing guide** covering all aspects of the onboarding wizard.

**Sections:**
- Pre-Testing Setup
- UI/UX Testing (Welcome, Steps, Completion)
- Keyboard Navigation Testing
- Screen Reader Testing
- Mobile Responsiveness Testing
- Cross-Browser Testing
- Accessibility Compliance (WCAG 2.1 AA)
- Form Validation Testing
- Error Handling Testing
- Performance Testing

**When to use:** Comprehensive QA testing before release

---

### 2. ONBOARDING_QA_RESULTS.md
**Test results template** for documenting findings from manual QA testing.

**Sections:**
- Executive Summary
- Test Results by Category
- Issues Log (Critical, High, Medium, Low)
- Acceptance Criteria Status
- Recommendations
- Sign-Off

**When to use:** During and after manual QA testing to document results

---

### 3. ACCESSIBILITY_CHECKLIST.md
**Quick accessibility testing checklist** for rapid accessibility verification.

**Sections:**
- Page-by-Page Testing (each step)
- Cross-Page Accessibility (progress indicator, sidebar, buttons)
- Mobile Accessibility
- Color & Contrast
- Focus Management
- Dynamic Content
- WCAG 2.1 AA Quick Check
- Common Issues to Watch For

**When to use:** Quick accessibility verification during development or QA

---

## Testing Workflow

### 1. Before Testing

1. **Review** [ONBOARDING_MANUAL_QA_GUIDE.md](./ONBOARDING_MANUAL_QA_GUIDE.md)
2. **Setup** testing environment:
   - Install browser extensions (axe, WAVE)
   - Setup screen readers (NVDA, VoiceOver)
   - Prepare test devices (mobile, tablet)
3. **Create** test user accounts (new, partial progress, completed)

### 2. During Testing

1. **Follow** [ONBOARDING_MANUAL_QA_GUIDE.md](./ONBOARDING_MANUAL_QA_GUIDE.md) systematically
2. **Use** [ACCESSIBILITY_CHECKLIST.md](./ACCESSIBILITY_CHECKLIST.md) for quick checks
3. **Document** findings in [ONBOARDING_QA_RESULTS.md](./ONBOARDING_QA_RESULTS.md)
4. **Take screenshots** of all issues
5. **Create** GitHub issues for bugs found

### 3. After Testing

1. **Complete** [ONBOARDING_QA_RESULTS.md](./ONBOARDING_QA_RESULTS.md)
2. **Review** results with development team
3. **Prioritize** issues (Critical → High → Medium → Low)
4. **Verify** fixes through re-testing
5. **Sign off** when acceptance criteria met

---

## Quick Start

### For Developers
```bash
# 1. Review accessibility checklist before development
cat tests/Manual/ACCESSIBILITY_CHECKLIST.md

# 2. Test during development
# - Run axe DevTools on each page
# - Test keyboard navigation
# - Verify form validation

# 3. Self-test before QA handoff
# - Complete accessibility checklist
# - Run Lighthouse audits
# - Test on mobile device
```

### For QA Testers
```bash
# 1. Read complete testing guide
cat tests/Manual/ONBOARDING_MANUAL_QA_GUIDE.md

# 2. Setup environment
# - Install browser extensions
# - Enable screen readers
# - Prepare devices

# 3. Execute tests systematically
# - Follow guide step-by-step
# - Document all findings
# - Take screenshots

# 4. Document results
# - Update ONBOARDING_QA_RESULTS.md
# - Create GitHub issues
# - Provide recommendations
```

### For Accessibility Specialists
```bash
# 1. Run automated scans
# - axe DevTools on all pages
# - WAVE on all pages
# - Lighthouse accessibility audit

# 2. Manual accessibility testing
# - Keyboard-only navigation
# - Screen reader testing (NVDA/VoiceOver)
# - Mobile screen readers (TalkBack/VoiceOver)

# 3. Use quick checklist
cat tests/Manual/ACCESSIBILITY_CHECKLIST.md

# 4. Verify WCAG 2.1 AA compliance
# - Check all criteria in checklist
# - Document any failures
# - Provide remediation guidance
```

---

## Testing Tools

### Required Tools

**Browser Extensions:**
- [axe DevTools](https://www.deque.com/axe/devtools/) - Automated accessibility scanning
- [WAVE](https://wave.webaim.org/extension/) - Visual accessibility feedback
- Chrome DevTools (built-in) - Lighthouse, responsive testing

**Screen Readers:**
- [NVDA](https://www.nvaccess.org/) (Windows - Free)
- VoiceOver (macOS/iOS - Built-in)
- TalkBack (Android - Built-in)
- [JAWS](https://www.freedomscientific.com/products/software/jaws/) (Windows - Commercial, optional)

**Other Tools:**
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- Browser DevTools > Network (throttling)
- Browser DevTools > Device Mode (responsive testing)

### Optional Tools
- [Pa11y](https://pa11y.org/) - Command-line accessibility testing
- [Accessibility Insights](https://accessibilityinsights.io/) - Microsoft's accessibility tool
- [Color Oracle](https://colororacle.org/) - Color blindness simulator
- [HeadingsMap](https://addons.mozilla.org/en-US/firefox/addon/headingsmap/) - Heading structure visualizer

---

## Test Coverage

### Automated Testing ✅
- Unit tests (OnboardingService, Steps) - COMPLETE
- Feature tests (Wizard flow) - COMPLETE
- Integration tests (API endpoints) - COMPLETE

### Manual Testing ⏳
- UI/UX testing - **PENDING**
- Keyboard navigation - **PENDING**
- Screen reader testing - **PENDING**
- Mobile responsiveness - **PENDING**
- Cross-browser testing - **PENDING**
- Accessibility (WCAG 2.1 AA) - **PENDING**
- Form validation - **PENDING**
- Error handling - **PENDING**
- Performance - **PENDING**

---

## Acceptance Criteria

Manual QA passes when:

1. **UI/UX**
   - All pages render correctly across browsers
   - Dark mode fully functional
   - Responsive design works on all devices
   - Animations smooth and performant

2. **Accessibility**
   - WCAG 2.1 AA compliance achieved
   - Lighthouse accessibility score 90+
   - axe DevTools: 0 critical/serious issues
   - Keyboard-only navigation works
   - Screen readers can navigate successfully

3. **Functionality**
   - All forms validate correctly
   - Error handling provides clear feedback
   - Progress saves and resumes correctly
   - Auto-trigger works for new users
   - Can restart from settings

4. **Performance**
   - Lighthouse performance score 80+
   - Pages load < 3 seconds on 3G
   - Smooth animations (60fps)
   - No layout shifts

---

## Related Documentation

### Implementation Documentation
- `app/Services/OnboardingService.php` - Core service implementation
- `config/onboarding.php` - Wizard configuration
- `resources/views/onboarding/` - Blade templates

### Test Documentation
- `tests/Unit/Services/OnboardingServiceTest.php` - Unit tests
- `tests/Feature/OnboardingWizardFlowTest.php` - Feature tests
- `tests/Feature/Api/OnboardingApiControllerTest.php` - API tests

### Spec Documentation
- `.auto-claude/specs/010-interactive-onboarding-wizard/spec.md` - Feature specification
- `.auto-claude/specs/010-interactive-onboarding-wizard/implementation_plan.json` - Implementation plan

---

## Contact

For questions about manual testing:
- Review [ONBOARDING_MANUAL_QA_GUIDE.md](./ONBOARDING_MANUAL_QA_GUIDE.md) first
- Check [ACCESSIBILITY_CHECKLIST.md](./ACCESSIBILITY_CHECKLIST.md) for specific criteria
- Refer to [WCAG 2.1 Quick Reference](https://www.w3.org/WAI/WCAG21/quickref/) for standards

---

**Last Updated**: 2026-01-12
**Status**: Ready for Manual QA Testing
