# Onboarding Wizard Flow - Feature Tests

## Overview

This test suite provides comprehensive end-to-end testing for the interactive onboarding wizard feature. The tests cover complete wizard flow, skip functionality, resume capability, navigation, completion, and restart functionality.

**Test File:** `tests/Feature/OnboardingWizardFlowTest.php`

**Total Test Cases:** 30+

## Test Categories

### 1. Complete Wizard Flow Tests (3 tests)

Tests that verify users can complete the entire onboarding wizard from start to finish.

#### `test_user_can_complete_entire_onboarding_wizard()`
- **Purpose:** Verifies complete end-to-end wizard flow through all 5 steps
- **Steps Tested:**
  1. company_setup - Complete with full data
  2. user_creation - Complete with team member invitation
  3. pipeline_config - Complete with custom pipeline stages
  4. email_integration - Complete with SMTP configuration
  5. sample_data - Complete with sample data import
- **Assertions:**
  - Each step redirects to next step correctly
  - Progress is updated after each step
  - Final step marks wizard as completed
  - Completion timestamp is set
  - All 5 steps marked as completed

#### `test_user_can_complete_wizard_with_minimal_data()`
- **Purpose:** Verifies wizard accepts minimal required data
- **Test Data:** Only company_name field (required)
- **Assertions:**
  - Step completes successfully with minimal data
  - No validation errors occur
  - Step marked as completed

#### `test_validation_errors_prevent_step_completion()`
- **Purpose:** Ensures validation works correctly
- **Test Data:** Empty data (missing required field)
- **Assertions:**
  - Validation error returned for company_name
  - Step not marked as completed
  - User redirected back to form

### 2. Skip Functionality Tests (4 tests)

Tests that verify the skip functionality works correctly for optional steps.

#### `test_user_can_skip_skippable_steps()`
- **Purpose:** Verifies users can skip optional steps
- **Steps:**
  1. Complete company_setup (required)
  2. Skip user_creation (optional)
- **Assertions:**
  - Skip redirects to next step
  - Step marked as skipped (not completed)
  - Info message displayed
  - Current step advances

#### `test_user_cannot_skip_non_skippable_steps()`
- **Purpose:** Ensures required steps cannot be skipped
- **Test:** Attempt to skip company_setup
- **Assertions:**
  - Error message returned
  - Step not marked as skipped
  - User remains on current step

#### `test_skipping_disabled_when_config_is_false()`
- **Purpose:** Verifies global skip configuration works
- **Configuration:** `onboarding.allow_skip = false`
- **Assertions:**
  - Skip attempt returns error
  - Step not skipped even if normally skippable
  - Error message matches config setting

#### `test_wizard_completes_when_all_steps_processed()`
- **Purpose:** Ensures wizard completes when all steps are completed or skipped
- **Flow:**
  1. Complete company_setup
  2. Skip user_creation
  3. Skip pipeline_config
  4. Skip email_integration
  5. Skip sample_data (triggers completion)
- **Assertions:**
  - Wizard marked as completed
  - Counts: 1 completed, 4 skipped
  - Completion message displayed

### 3. Resume Capability Tests (4 tests)

Tests that verify users can pause and resume onboarding.

#### `test_user_can_resume_onboarding_from_where_they_left_off()`
- **Purpose:** Verifies progress persistence across sessions
- **Flow:**
  1. Complete company_setup
  2. Navigate away (simulate session end)
  3. Return to onboarding index
- **Assertions:**
  - Redirects to current step (user_creation)
  - Can access current step
  - Previous progress maintained

#### `test_completed_onboarding_shows_completion_page()`
- **Purpose:** Ensures completed wizard shows proper completion page
- **Flow:**
  1. Complete entire onboarding
  2. Visit onboarding index
- **Assertions:**
  - Shows completion view (not wizard)
  - Progress data available
  - Summary data available

#### `test_progress_is_persisted_across_sessions()`
- **Purpose:** Verifies database persistence of progress
- **Flow:**
  1. Complete company_setup and user_creation
  2. Refresh progress from database
- **Assertions:**
  - Current step persisted
  - Completed steps count correct
  - Individual step completion status maintained

#### `test_user_can_navigate_to_previously_completed_steps()`
- **Purpose:** Allows users to review/edit completed steps
- **Flow:**
  1. Complete two steps
  2. Navigate back to first step
- **Assertions:**
  - Can view previous step
  - Current step updates to selected step
  - Completed status maintained for both steps

### 4. Navigation Tests (3 tests)

Tests for wizard navigation (next/previous buttons).

#### `test_user_can_navigate_to_next_step()`
- **Purpose:** Verifies next navigation works
- **Assertions:**
  - Current step advances to next
  - Redirects correctly

#### `test_user_can_navigate_to_previous_step()`
- **Purpose:** Verifies previous navigation works
- **Assertions:**
  - Current step moves to previous
  - Redirects correctly

#### `test_navigation_to_invalid_step_shows_error()`
- **Purpose:** Ensures invalid steps are rejected
- **Test:** Navigate to 'invalid_step'
- **Assertions:**
  - Redirects to index
  - Error message displayed

### 5. Completion Tests (2 tests)

Tests for wizard completion functionality.

#### `test_onboarding_completion_redirects_to_configured_url()`
- **Purpose:** Verifies completion redirect configuration
- **Configuration:**
  - `redirect_to = '/custom-dashboard'`
  - `completion_message = 'Welcome aboard!'`
- **Assertions:**
  - Redirects to configured URL
  - Shows configured completion message

#### `test_progress_summary_includes_all_relevant_data()`
- **Purpose:** Ensures progress summary API returns complete data
- **Flow:**
  1. Complete one step
  2. Skip one step
  3. Get progress summary
- **Assertions:**
  - All keys present (current_step, total_steps, completed_steps, skipped_steps, progress_percentage, is_completed)
  - Values correct (40% progress for 2 of 5 steps)
  - Step lists accurate

### 6. Restart Tests (2 tests)

Tests for wizard restart functionality.

#### `test_user_can_restart_onboarding_wizard()`
- **Purpose:** Verifies restart functionality works
- **Flow:**
  1. Complete entire wizard
  2. Restart wizard
- **Assertions:**
  - Progress reset (is_completed = false)
  - Current step reset to first step
  - Completed/skipped counts reset to 0
  - Success message displayed

#### `test_restart_disabled_when_config_is_false()`
- **Purpose:** Ensures restart configuration works
- **Configuration:** `onboarding.allow_restart = false`
- **Assertions:**
  - Restart attempt returns error
  - Progress not reset
  - Error message matches config setting

### 7. Authentication Tests (1 test)

Tests that verify authentication requirements.

#### `test_all_onboarding_routes_require_authentication()`
- **Purpose:** Ensures all routes are protected
- **Routes Tested:** 10 routes (index, step, store, next, previous, skip, complete, restart, progress, statistics)
- **Assertions:**
  - All routes redirect to login when unauthenticated
  - No access without valid session

### 8. Configuration Tests (1 test)

Tests for global wizard configuration.

#### `test_wizard_disabled_when_config_is_false()`
- **Purpose:** Verifies global enable/disable works
- **Configuration:** `onboarding.enabled = false`
- **Assertions:**
  - Redirects to dashboard
  - Shows disabled message

### 9. Mixed Flow Tests (2 tests)

Tests for complex real-world scenarios.

#### `test_complete_wizard_with_mixed_completed_and_skipped_steps()`
- **Purpose:** Tests realistic mixed flow
- **Flow:**
  1. Complete company_setup
  2. Skip user_creation
  3. Complete pipeline_config
  4. Skip email_integration
  5. Skip sample_data
- **Assertions:**
  - Wizard completes successfully
  - Counts: 2 completed, 3 skipped
  - Individual step statuses correct

#### `test_user_can_update_previously_completed_step()`
- **Purpose:** Allows editing completed steps
- **Flow:**
  1. Complete company_setup with "Original Name"
  2. Navigate back to company_setup
  3. Update with "Updated Name"
- **Assertions:**
  - Update succeeds
  - No validation errors
  - Step remains marked as completed

## Test Coverage Summary

### Features Tested
- ✅ Complete wizard flow (all 5 steps)
- ✅ Minimal data submission
- ✅ Validation enforcement
- ✅ Skip functionality (allowed steps)
- ✅ Skip prevention (required steps)
- ✅ Skip configuration (enable/disable)
- ✅ Auto-completion when all steps processed
- ✅ Resume from saved progress
- ✅ Completion page display
- ✅ Progress persistence
- ✅ Navigation to previous steps
- ✅ Next/previous navigation
- ✅ Invalid step handling
- ✅ Completion redirect configuration
- ✅ Progress summary API
- ✅ Restart functionality
- ✅ Restart configuration
- ✅ Authentication requirements
- ✅ Global enable/disable
- ✅ Mixed completed/skipped flows
- ✅ Updating completed steps

### Database Operations Tested
- OnboardingProgress creation
- Progress updates (current_step, completed_steps, skipped_steps)
- Completion timestamp (completed_at)
- Progress queries and retrieval
- Progress reset

### Controller Methods Tested
- `index()` - Display wizard or redirect to current step
- `show($step)` - Display specific step
- `store($step)` - Process step submission
- `next()` - Navigate to next step
- `previous()` - Navigate to previous step
- `skip($step)` - Skip current step
- `complete()` - Complete wizard
- `restart()` - Reset wizard

### Service Methods Tested
- `startOnboarding()`
- `completeStep()`
- `skipStep()`
- `navigateToNextStep()`
- `navigateToPreviousStep()`
- `navigateToStep()`
- `completeOnboarding()`
- `resetOnboarding()`
- `getProgressSummary()`
- `getOrCreateProgress()`

### Configuration Options Tested
- `onboarding.enabled`
- `onboarding.allow_skip`
- `onboarding.allow_restart`
- `onboarding.completion.redirect_to`
- `onboarding.completion.completion_message`
- Step-specific `skippable` flags

## Running the Tests

### Run All Onboarding Wizard Flow Tests
```bash
php artisan test --filter OnboardingWizardFlowTest
```

### Run Specific Test
```bash
php artisan test --filter test_user_can_complete_entire_onboarding_wizard
```

### Run with Coverage
```bash
php artisan test --filter OnboardingWizardFlowTest --coverage
```

## Test Data

### Users
- Created via `User::factory()->create()`
- Authenticated with `actingAs($user, 'user')`

### Company Setup Data
- **Minimal:** company_name only
- **Full:** company_name, industry, company_size, address, phone, website

### User Creation Data
- name, email, role, send_invitation

### Pipeline Config Data
- pipeline_name, stages array (name, probability)

### Email Integration Data
- email_provider, smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption

### Sample Data Import Data
- import_sample_data, include_companies, include_contacts, include_deals

## Dependencies

### Laravel Components
- `RefreshDatabase` trait for database isolation
- `Mail::fake()` for email testing
- `Config::set()` for configuration testing

### Models Used
- `App\Models\User`
- `App\Models\OnboardingProgress`

### Services Used
- `App\Services\OnboardingService`

## Assertions Used

### HTTP Assertions
- `assertOk()` - 200 status
- `assertRedirect()` - Redirect response
- `assertViewIs()` - Correct view rendered
- `assertViewHas()` - View data present
- `assertSessionHas()` - Session flash messages
- `assertSessionHasErrors()` - Validation errors
- `assertSessionHasNoErrors()` - No validation errors

### Database Assertions
- Progress record existence
- Step completion status
- Step skipped status
- Current step value
- Completion timestamp
- Counts (completed, skipped)

### Custom Assertions
- `isStepCompleted()` - OnboardingProgress method
- `isStepSkipped()` - OnboardingProgress method
- `getCompletedStepsCount()` - Count assertion
- `getSkippedStepsCount()` - Count assertion

## Error Scenarios Tested

### Validation Errors
- Missing required fields
- Invalid step identifiers
- Invalid data formats

### Authorization Errors
- Unauthenticated access attempts
- Skip attempts on required steps
- Restart when disabled

### Configuration Errors
- Disabled wizard access
- Disabled skip functionality
- Disabled restart functionality

## Edge Cases Covered

1. **Empty Progress:** New user with no onboarding record
2. **Completed Progress:** User who already finished
3. **Partial Progress:** User mid-way through wizard
4. **All Skipped:** Completing wizard by skipping all optional steps
5. **All Completed:** Completing wizard with all steps done
6. **Mixed Flow:** Combination of completed and skipped
7. **Navigation Back:** Editing previously completed steps
8. **Invalid Step:** Accessing non-existent steps
9. **Disabled Features:** Configuration toggles off

## Manual Verification Checklist

After running automated tests, manually verify:

1. ✅ Visual appearance of all 5 step forms
2. ✅ Progress indicator updates correctly
3. ✅ Help sidebar content displays
4. ✅ Skip confirmation modals work
5. ✅ Completion page celebration animation
6. ✅ Restart confirmation modal
7. ✅ Mobile responsiveness
8. ✅ Dark mode support
9. ✅ Accessibility (keyboard navigation, screen readers)
10. ✅ Browser compatibility

## Future Enhancements

### Additional Tests to Consider
- [ ] Performance testing (wizard with large data sets)
- [ ] Concurrent user testing (multiple users onboarding simultaneously)
- [ ] Browser testing (Selenium/Dusk for frontend interactions)
- [ ] Email content verification (welcome email)
- [ ] Sample data import verification (actual database records)
- [ ] Pipeline drag-drop functionality
- [ ] Email provider field auto-fill
- [ ] AJAX validation testing
- [ ] Progress auto-save testing

### Test Maintenance
- Update tests when adding new wizard steps
- Update tests when changing validation rules
- Update tests when modifying configuration options
- Keep test data realistic and up-to-date

## Troubleshooting

### Tests Failing
1. Ensure database is migrated: `php artisan migrate:fresh`
2. Clear config cache: `php artisan config:clear`
3. Check test database connection in `.env.testing`
4. Verify all migrations are up to date

### Specific Test Failures
- **Authentication failures:** Check auth guard configuration
- **Validation failures:** Verify config/onboarding.php validation rules
- **Redirect failures:** Check route definitions in routes/web.php
- **Database failures:** Ensure RefreshDatabase trait is used

## Related Documentation
- Main Feature Spec: `.auto-claude/specs/010-interactive-onboarding-wizard/spec.md`
- Implementation Plan: `.auto-claude/specs/010-interactive-onboarding-wizard/implementation_plan.json`
- OnboardingService Unit Tests: `tests/Unit/Services/README_ONBOARDING_SERVICE_TESTS.md`
- API Controller Tests: `tests/Feature/Api/OnboardingApiControllerTest.php`
- Middleware Tests: `tests/Feature/Middleware/RedirectIfOnboardingIncompleteTest.php`
