# Wizard Step Implementation Tests

## Overview

This document provides a comprehensive overview of the unit tests for all wizard step implementations in the Interactive Onboarding Wizard feature. Each wizard step has its own dedicated test suite that covers validation, data storage, completion detection, and error handling.

## Test Files

All step implementation tests are located in `tests/Unit/Services/` directory:

1. **CompanySetupStepTest.php** - Tests for Company Setup step
2. **UserCreationStepTest.php** - Tests for User Creation step
3. **PipelineConfigurationStepTest.php** - Tests for Pipeline Configuration step
4. **EmailIntegrationStepTest.php** - Tests for Email Integration step
5. **SampleDataImportStepTest.php** - Tests for Sample Data Import step

## Test Coverage Summary

### Total Statistics
- **Total Test Files**: 5
- **Total Test Methods**: 98+
- **Lines of Test Code**: ~2,500+

### Per-Step Breakdown

| Step Implementation | Test File | Test Count | Key Areas Covered |
|-------------------|-----------|------------|-------------------|
| CompanySetupStep | CompanySetupStepTest.php | 13 | Configuration, validation, storage, retrieval, completion |
| UserCreationStep | UserCreationStepTest.php | 19 | Configuration, validation, user creation, role assignment, email invitations |
| PipelineConfigurationStep | PipelineConfigurationStepTest.php | 22 | Configuration, validation, pipeline/stage creation, updates, ordering |
| EmailIntegrationStep | EmailIntegrationStepTest.php | 21 | Configuration, validation, email config storage, providers, connection testing |
| SampleDataImportStep | SampleDataImportStepTest.php | 23 | Configuration, validation, sample data import, dependencies, conditional imports |

---

## 1. CompanySetupStep Tests (13 tests)

**File**: `tests/Unit/Services/CompanySetupStepTest.php`

**Purpose**: Validate the company setup step that collects and stores company information (name, industry, size, address, phone, website).

### Test Methods

1. **it_has_correct_step_configuration** - Validates step ID, title, skippable flag, and estimated time
2. **it_validates_company_data** - Tests validation with valid complete data
3. **it_fails_validation_without_required_company_name** - Ensures company_name is required
4. **it_executes_and_stores_company_data** - Tests data storage in core_config table
5. **it_stores_minimal_required_company_data** - Tests with only required fields (company_name)
6. **it_updates_existing_company_data** - Tests updating previously saved data
7. **it_retrieves_default_data_when_previously_completed** - Tests form pre-filling from saved data
8. **it_returns_empty_default_data_when_not_completed** - Tests empty state for new setup
9. **it_detects_completion_status** - Tests hasBeenCompleted() method
10. **it_stores_completion_metadata** - Tests completed_at and completed_by storage
11. **it_has_correct_validation_rules** - Validates rules match config
12. **it_renders_step_view** - Tests view rendering
13. **it_rolls_back_transaction_on_error** - Tests error handling and rollback

### Data Validation Coverage
- ✓ Required fields (company_name)
- ✓ Optional fields (industry, size, address, phone, website)
- ✓ Field formats (URL, phone)
- ✓ Field length limits

### Storage Coverage
- ✓ Data stored in core_config table with 'onboarding.company.*' prefix
- ✓ All 6 fields stored correctly
- ✓ Completion metadata (completed_at, completed_by)
- ✓ Update existing data without duplication

---

## 2. UserCreationStep Tests (19 tests)

**File**: `tests/Unit/Services/UserCreationStepTest.php`

**Purpose**: Validate the user creation step that creates team members with role assignment and optional invitation emails.

### Test Methods

1. **it_has_correct_step_configuration** - Validates step configuration
2. **it_validates_user_data** - Tests validation with valid data
3. **it_fails_validation_without_required_name** - Ensures name is required
4. **it_fails_validation_without_required_email** - Ensures email is required
5. **it_fails_validation_with_invalid_email** - Tests email format validation
6. **it_executes_and_creates_user** - Tests user creation in database
7. **it_creates_user_with_minimal_data** - Tests with only required fields
8. **it_assigns_correct_role_to_user** - Tests role assignment logic
9. **it_sends_invitation_email_when_requested** - Tests email sending via Mail queue
10. **it_does_not_send_invitation_email_when_not_requested** - Tests email not sent when disabled
11. **it_stores_completion_metadata** - Tests metadata storage in core_config
12. **it_retrieves_default_data_when_previously_completed** - Tests form pre-filling
13. **it_returns_empty_default_data_when_not_completed** - Tests empty state
14. **it_detects_completion_status** - Tests completion detection
15. **it_has_correct_validation_rules** - Validates rules
16. **it_renders_step_view** - Tests view rendering
17. **it_rolls_back_transaction_on_error** - Tests error handling (duplicate email)
18. **it_assigns_default_role_when_role_not_specified** - Tests role fallback logic
19. **it_continues_after_email_failure** - Tests graceful email error handling

### Data Validation Coverage
- ✓ Required fields (name, email)
- ✓ Email format validation
- ✓ Optional fields (role, send_invitation)
- ✓ Duplicate email handling

### Feature Coverage
- ✓ User creation via UserRepository
- ✓ Password auto-generation (16 characters)
- ✓ Role assignment with intelligent fallback
- ✓ Laravel events (settings.user.create.before/after)
- ✓ Invitation email via UserCreatedNotification (queued)
- ✓ Graceful email failure handling
- ✓ Database transactions and rollback

---

## 3. PipelineConfigurationStep Tests (22 tests)

**File**: `tests/Unit/Services/PipelineConfigurationStepTest.php`

**Purpose**: Validate the pipeline configuration step that sets up sales pipeline stages with drag-drop ordering.

### Test Methods

1. **it_has_correct_step_configuration** - Validates step configuration
2. **it_validates_pipeline_data** - Tests validation with complete data
3. **it_validates_empty_data_for_defaults** - Tests default stage usage
4. **it_fails_validation_with_invalid_stage_probability** - Tests probability range (0-100)
5. **it_fails_validation_with_missing_stage_name** - Ensures stage names are required
6. **it_creates_pipeline_with_custom_stages** - Tests custom stage creation
7. **it_creates_pipeline_with_default_stages_when_no_stages_provided** - Tests default templates
8. **it_creates_pipeline_with_default_name_when_no_name_provided** - Tests name fallback
9. **it_updates_existing_pipeline** - Tests pipeline updates
10. **it_stores_completion_metadata** - Tests metadata storage
11. **it_retrieves_default_data_when_previously_completed** - Tests form pre-filling
12. **it_returns_default_configuration_when_not_completed** - Tests default config
13. **it_detects_completion_status** - Tests completion detection
14. **it_has_correct_validation_rules** - Validates rules
15. **it_renders_step_view** - Tests view rendering
16. **it_generates_stage_codes_from_names** - Tests slug generation (e.g., "New Lead" → "new_lead")
17. **it_creates_default_pipeline_when_skipped** - Tests auto-creation on skip
18. **it_does_not_create_duplicate_pipeline_on_skip** - Tests skip with existing pipeline
19. **it_rolls_back_transaction_on_error** - Tests error handling
20. **it_sets_pipeline_as_default** - Tests is_default flag
21. **it_handles_stages_with_missing_probability** - Tests default probability (50)
22. **it_handles_stages_with_missing_order** - Tests auto-incrementing sort_order

### Data Validation Coverage
- ✓ Pipeline name (optional, has default)
- ✓ Stages array validation
- ✓ Stage name (required)
- ✓ Stage probability (0-100 range)
- ✓ Stage order for drag-drop

### Feature Coverage
- ✓ Pipeline creation via PipelineRepository
- ✓ Stage creation with proper relationships
- ✓ Default stage templates (6 stages: New, Qualified, Proposal, Negotiation, Won, Lost)
- ✓ Stage code generation (slug format)
- ✓ Drag-drop ordering support (sort_order field)
- ✓ Pipeline updates
- ✓ Auto-creation on skip
- ✓ Default pipeline flag (is_default)

---

## 4. EmailIntegrationStep Tests (21 tests)

**File**: `tests/Unit/Services/EmailIntegrationStepTest.php`

**Purpose**: Validate the email integration step that configures SMTP/IMAP settings for various email providers.

### Test Methods

1. **it_has_correct_step_configuration** - Validates step configuration
2. **it_validates_email_configuration_data** - Tests validation with complete data
3. **it_accepts_minimal_data_since_all_fields_optional** - Tests all fields optional
4. **it_executes_and_stores_email_configuration** - Tests data storage
5. **it_updates_existing_email_configuration** - Tests configuration updates
6. **it_stores_completion_metadata** - Tests metadata storage
7. **it_retrieves_default_data_when_previously_completed** - Tests form pre-filling
8. **it_excludes_password_from_default_data_for_security** - Tests password not retrieved
9. **it_returns_empty_default_data_when_not_completed** - Tests empty state
10. **it_detects_completion_status** - Tests completion detection
11. **it_has_correct_validation_rules** - Validates rules
12. **it_renders_step_view** - Tests view rendering
13. **it_rolls_back_transaction_on_error** - Tests error handling
14. **it_stores_configuration_for_smtp_provider** - Tests SMTP provider
15. **it_stores_configuration_for_gmail_provider** - Tests Gmail provider
16. **it_stores_configuration_for_outlook_provider** - Tests Outlook provider
17. **it_stores_configuration_for_sendgrid_provider** - Tests SendGrid provider
18. **it_stores_configuration_with_tls_encryption** - Tests TLS encryption
19. **it_stores_configuration_with_ssl_encryption** - Tests SSL encryption
20. **it_handles_empty_optional_fields** - Tests null/empty field handling
21. **it_validates_port_range** - Tests port validation (1-65535)

### Data Validation Coverage
- ✓ All fields optional (skippable step)
- ✓ Email provider selection
- ✓ SMTP host format
- ✓ Port range (1-65535)
- ✓ Username format
- ✓ Password handling
- ✓ Encryption methods (tls, ssl, none)

### Feature Coverage
- ✓ Multiple email providers (SMTP, Gmail, Outlook, SendGrid)
- ✓ All encryption types (TLS, SSL, none)
- ✓ Common SMTP ports (25, 465, 587, 2525)
- ✓ Optional connection testing
- ✓ Password security (not retrieved in getDefaultData)
- ✓ Empty/null field handling
- ✓ Configuration storage in core_config

---

## 5. SampleDataImportStep Tests (23 tests)

**File**: `tests/Unit/Services/SampleDataImportStepTest.php`

**Purpose**: Validate the sample data import step that imports sample organizations, contacts, and deals with customization options.

### Test Methods

1. **it_has_correct_step_configuration** - Validates step configuration
2. **it_validates_sample_data_preferences** - Tests validation
3. **it_stores_preferences_without_importing_when_import_disabled** - Tests preference storage
4. **it_imports_all_sample_data_when_requested** - Tests complete import
5. **it_imports_only_companies_when_requested** - Tests selective import
6. **it_imports_companies_and_contacts_when_requested** - Tests partial import
7. **it_skips_contacts_import_when_companies_not_included** - Tests dependency handling
8. **it_skips_deals_import_when_contacts_not_included** - Tests dependency handling
9. **it_creates_pipeline_if_none_exists** - Tests auto-creation of dependencies
10. **it_uses_existing_pipeline_if_available** - Tests existing pipeline usage
11. **it_creates_sources_and_types_if_none_exist** - Tests auto-creation
12. **it_stores_completion_metadata** - Tests metadata storage
13. **it_retrieves_default_data_when_previously_completed** - Tests form pre-filling
14. **it_returns_default_values_when_not_completed** - Tests default values
15. **it_detects_completion_status** - Tests completion detection
16. **it_has_correct_validation_rules** - Validates rules
17. **it_renders_step_view** - Tests view rendering
18. **it_rolls_back_transaction_on_error** - Tests error handling
19. **it_imports_expected_number_of_organizations** - Tests 5 companies created
20. **it_imports_multiple_persons_per_organization** - Tests 2-3 contacts per company
21. **it_imports_deals_with_various_pipeline_stages** - Tests deal distribution
22. **it_stores_import_counts_in_config** - Tests count tracking
23. **it_updates_preferences_when_run_multiple_times** - Tests re-run handling

### Data Validation Coverage
- ✓ Import flag (boolean)
- ✓ Include options (companies, contacts, deals)
- ✓ Conditional validation

### Feature Coverage
- ✓ Sample organizations (5 companies with addresses)
- ✓ Sample contacts (10-15 with job titles, emails, phones)
- ✓ Sample deals (10 with values, stages, dates)
- ✓ Conditional imports based on dependencies
- ✓ Auto-creation of pipeline if missing
- ✓ Auto-creation of sources and types
- ✓ Import count tracking
- ✓ Customization options (include flags)
- ✓ Re-run capability

---

## Common Test Patterns

All step implementation tests follow these common patterns:

### 1. Test Structure
- **setUp()** method initializes dependencies and test user
- Uses RefreshDatabase trait for database isolation
- Creates service instances with repository injection
- Uses Laravel factories for test data

### 2. Configuration Tests
- Validates step ID, title, description
- Verifies skippable flag
- Checks estimated time
- Validates icon identifier

### 3. Validation Tests
- Tests with valid complete data
- Tests with minimal required data
- Tests required field validation
- Tests field format validation
- Tests field range validation

### 4. Execution Tests
- Tests data storage in database
- Tests storage location (core_config, users table, etc.)
- Tests data format and structure
- Tests relationships and foreign keys

### 5. Completion Tests
- Tests completion metadata storage
- Tests hasBeenCompleted() detection
- Tests completion status tracking

### 6. Default Data Tests
- Tests getDefaultData() retrieval
- Tests form pre-filling
- Tests empty state for new setup
- Tests data format for UI consumption

### 7. Error Handling Tests
- Tests validation exceptions
- Tests database transaction rollback
- Tests error logging
- Tests graceful degradation

### 8. View Tests
- Tests view path rendering
- Tests view name correctness

---

## Running the Tests

### Run All Step Tests
```bash
php artisan test tests/Unit/Services/CompanySetupStepTest.php
php artisan test tests/Unit/Services/UserCreationStepTest.php
php artisan test tests/Unit/Services/PipelineConfigurationStepTest.php
php artisan test tests/Unit/Services/EmailIntegrationStepTest.php
php artisan test tests/Unit/Services/SampleDataImportStepTest.php
```

### Run All Tests in Directory
```bash
php artisan test tests/Unit/Services/
```

### Run Specific Test Method
```bash
php artisan test --filter=it_executes_and_stores_company_data
```

### Run with Coverage (if configured)
```bash
php artisan test --coverage tests/Unit/Services/
```

---

## Test Data

### Sample Test User
All tests create a test user using Laravel factory:
```php
$this->testUser = User::factory()->create();
```

### Sample Company Data
```php
[
    'company_name' => 'Acme Corporation',
    'industry' => 'technology',
    'company_size' => '51-200',
    'address' => '123 Main St, City, Country',
    'phone' => '+1234567890',
    'website' => 'https://example.com',
]
```

### Sample User Data
```php
[
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'role' => 'admin',
    'send_invitation' => true,
]
```

### Sample Pipeline Data
```php
[
    'pipeline_name' => 'Sales Pipeline',
    'stages' => [
        ['name' => 'New', 'probability' => 10, 'order' => 1],
        ['name' => 'Qualified', 'probability' => 25, 'order' => 2],
        // ... more stages
    ],
]
```

### Sample Email Configuration
```php
[
    'email_provider' => 'smtp',
    'smtp_host' => 'smtp.example.com',
    'smtp_port' => 587,
    'smtp_username' => 'user@example.com',
    'smtp_password' => 'password',
    'smtp_encryption' => 'tls',
]
```

### Sample Data Import Preferences
```php
[
    'import_sample_data' => true,
    'include_companies' => true,
    'include_contacts' => true,
    'include_deals' => true,
]
```

---

## Dependencies

### Required Packages
- PHPUnit (Laravel's test framework)
- Laravel Testing Utilities (RefreshDatabase, DatabaseTransactions)
- Mockery (for mocking facades)

### Required Models
- User (Laravel's default user model)
- CoreConfig (for configuration storage)
- Organization, Person (for sample contacts)
- Lead, Pipeline, Stage, Source, Type (for sample deals)

### Required Repositories
- CoreConfigRepository
- UserRepository
- RoleRepository
- PipelineRepository
- StageRepository

### Required Services
- All step implementations (CompanySetupStep, UserCreationStep, etc.)
- OnboardingService (indirectly tested)

---

## Test Environment Setup

### Database Configuration
Tests use SQLite in-memory database by default (configured in phpunit.xml):
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Mail Configuration
Tests use Mail::fake() to prevent actual email sending:
```php
Mail::fake();
```

### Configuration Override
Tests use Config::set() to override configuration:
```php
Config::set('onboarding.enabled', true);
Config::set('onboarding.allow_skip', true);
```

---

## Manual Verification Checklist

After running all tests, manually verify:

### 1. Test Execution
- [ ] All 5 test files execute without syntax errors
- [ ] All ~98 test methods pass successfully
- [ ] No database constraint violations
- [ ] No memory leaks or performance issues

### 2. Coverage Verification
Each test file should cover:
- [ ] Step configuration validation
- [ ] Required field validation
- [ ] Optional field handling
- [ ] Data storage in correct location
- [ ] Completion metadata tracking
- [ ] Default data retrieval
- [ ] Error handling and rollback
- [ ] View rendering

### 3. Integration Points
- [ ] Tests use actual repository classes (not mocks)
- [ ] Tests use actual database operations
- [ ] Tests verify database state after operations
- [ ] Tests clean up after themselves (RefreshDatabase)

### 4. Code Quality
- [ ] Tests follow PHPUnit naming conventions
- [ ] Tests have descriptive method names
- [ ] Tests use proper assertions
- [ ] Tests have appropriate comments
- [ ] Tests follow project patterns

---

## Troubleshooting

### Tests Fail with Database Errors
**Solution**: Ensure RefreshDatabase trait is used and migrations are up to date:
```bash
php artisan migrate:fresh
php artisan test
```

### Tests Fail with Class Not Found
**Solution**: Regenerate autoload files:
```bash
composer dump-autoload
php artisan test
```

### Tests Fail with Configuration Issues
**Solution**: Clear configuration cache:
```bash
php artisan config:clear
php artisan test
```

### Specific Test Fails
**Solution**: Run the test in isolation to see detailed error:
```bash
php artisan test --filter=test_method_name
```

---

## Future Enhancements

### Additional Test Coverage
- [ ] Add integration tests with OnboardingService
- [ ] Add browser tests for form submissions (Laravel Dusk)
- [ ] Add performance tests for sample data import
- [ ] Add tests for concurrent user operations

### Test Improvements
- [ ] Add test data builders for complex objects
- [ ] Add custom assertions for common patterns
- [ ] Add test helpers for repetitive operations
- [ ] Add database seeders for test data

### Documentation
- [ ] Add examples for common test scenarios
- [ ] Add troubleshooting guide for common issues
- [ ] Add performance benchmarks
- [ ] Add code coverage reports

---

## Maintenance Notes

### When Adding New Fields
1. Update validation tests with new field
2. Update storage tests to verify new field
3. Update default data tests to include new field
4. Update test data samples

### When Modifying Validation Rules
1. Update validation tests with new rules
2. Update failure tests for new constraints
3. Update test data to match new rules

### When Changing Database Schema
1. Update migration tests if applicable
2. Update storage tests with new schema
3. Update assertions for new relationships
4. Refresh test database

### When Adding New Step
1. Create new test file following pattern
2. Add to this README documentation
3. Update statistics and counts
4. Add to test runner commands

---

## Related Documentation

- [OnboardingService Tests](README_ONBOARDING_SERVICE_TESTS.md) - Service layer tests
- [Wizard Flow Tests](../Feature/README_ONBOARDING_WIZARD_FLOW_TESTS.md) - End-to-end flow tests
- [Implementation Plan](../../../.auto-claude/specs/010-interactive-onboarding-wizard/implementation_plan.json) - Feature specifications
- [Build Progress](../../../.auto-claude/specs/010-interactive-onboarding-wizard/build-progress.txt) - Development progress

---

## Conclusion

This comprehensive test suite ensures that all wizard step implementations:
- ✓ Have correct configuration
- ✓ Validate data properly
- ✓ Store data in the correct location
- ✓ Handle errors gracefully
- ✓ Support form pre-filling
- ✓ Track completion status
- ✓ Follow project patterns

**Total Coverage**: 98+ test methods across 5 step implementations, providing comprehensive validation and data storage testing for the Interactive Onboarding Wizard feature.
