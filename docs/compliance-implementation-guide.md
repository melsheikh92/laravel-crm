# Compliance Implementation Guide

This guide provides comprehensive instructions for implementing and configuring the compliance features in the Laravel CRM system, including GDPR, HIPAA, and SOC 2 compliance capabilities.

## Table of Contents

1. [Introduction](#introduction)
2. [Installation & Setup](#installation--setup)
3. [Configuration Overview](#configuration-overview)
4. [Audit Logging](#audit-logging)
5. [Consent Management](#consent-management)
6. [Data Retention Policies](#data-retention-policies)
7. [Field-Level Encryption](#field-level-encryption)
8. [GDPR Rights Implementation](#gdpr-rights-implementation)
9. [Compliance Dashboard](#compliance-dashboard)
10. [Best Practices](#best-practices)
11. [Common Scenarios](#common-scenarios)
12. [Troubleshooting](#troubleshooting)

---

## Introduction

The Laravel CRM compliance features provide a comprehensive framework for meeting regulatory requirements including:

- **GDPR** (General Data Protection Regulation)
- **HIPAA** (Health Insurance Portability and Accountability Act)
- **SOC 2** (Service Organization Control 2)

### Key Features

- **Audit Logging**: Complete audit trail of all data access and changes
- **Consent Management**: GDPR-compliant consent tracking and management
- **Data Retention**: Automated data retention and cleanup policies
- **Field Encryption**: AES-256 encryption for sensitive data fields
- **Right to Erasure**: GDPR right-to-be-forgotten implementation
- **Data Portability**: Export user data in machine-readable formats
- **Compliance Dashboard**: Real-time compliance metrics and reporting

---

## Installation & Setup

### Step 1: Run Migrations

First, run the compliance migrations to set up the required database tables:

```bash
php artisan migrate
```

This will create the following tables:
- `audit_logs` - Stores audit trail entries
- `consent_records` - Tracks user consents
- `data_retention_policies` - Defines retention rules
- `data_deletion_requests` - Manages GDPR deletion requests

### Step 2: Publish Configuration

The compliance configuration is already available at `config/compliance.php`. Review and customize it for your needs:

```bash
php artisan config:clear
```

### Step 3: Seed Default Policies

Run the seeder to create default data retention policies:

```bash
php artisan db:seed --class=ComplianceSeeder
```

This will create default retention policies for:
- Audit logs (7 years)
- Consent records (7 years)
- Deleted users (30 days grace period)
- Support tickets (6 years)

### Step 4: Configure Environment Variables

Add the following to your `.env` file:

```env
# Compliance Mode
COMPLIANCE_ENABLED=true

# Audit Logging
COMPLIANCE_AUDIT_ENABLED=true
COMPLIANCE_AUDIT_RETENTION_DAYS=2555
COMPLIANCE_AUDIT_CAPTURE_IP=true
COMPLIANCE_AUDIT_CAPTURE_USER_AGENT=true

# Consent Management
COMPLIANCE_CONSENT_ENABLED=true
COMPLIANCE_EXPLICIT_CONSENT=true

# Data Retention
COMPLIANCE_DATA_RETENTION_ENABLED=true
COMPLIANCE_AUTO_DELETE=false
COMPLIANCE_PREFER_ANONYMIZATION=true

# Field Encryption
COMPLIANCE_ENCRYPTION_ENABLED=true
COMPLIANCE_AUTO_DECRYPT=true

# GDPR
COMPLIANCE_GDPR_ENABLED=true
COMPLIANCE_RIGHT_TO_ERASURE_ENABLED=true
COMPLIANCE_DATA_PORTABILITY_ENABLED=true
COMPLIANCE_ANONYMIZE_DATA=true

# HIPAA (if applicable)
COMPLIANCE_HIPAA_ENABLED=false

# SOC 2 (if applicable)
COMPLIANCE_SOC2_ENABLED=false

# Compliance Reporting
COMPLIANCE_REPORTING_ENABLED=true
COMPLIANCE_REPORTING_PDF=false

# Notifications
COMPLIANCE_NOTIFICATIONS_ENABLED=true
COMPLIANCE_OFFICER_EMAILS=compliance@example.com
```

### Step 5: Schedule Data Cleanup

Add the data cleanup command to your scheduler. It's already configured in `app/Console/Kernel.php` to run daily at 2:00 AM:

```php
$schedule->command('cleanup:expired-data')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
```

---

## Configuration Overview

The compliance configuration file (`config/compliance.php`) is organized into several sections:

### Compliance Mode

```php
'enabled' => env('COMPLIANCE_ENABLED', true),
```

Master switch for all compliance features. When disabled, all compliance functionality is turned off.

### Audit Logging Configuration

```php
'audit_logging' => [
    'enabled' => true,
    'events' => ['created', 'updated', 'deleted', 'restored', 'viewed', 'exported'],
    'excluded_events' => [],
    'excluded_models' => [],
    'masked_fields' => ['password', 'password_confirmation', 'token', 'secret'],
    'retention_days' => 2555, // ~7 years
    'capture_ip' => true,
    'capture_user_agent' => true,
],
```

### Consent Management Configuration

```php
'consent' => [
    'enabled' => true,
    'types' => [
        'terms_of_service' => ['required' => true, 'description' => '...'],
        'privacy_policy' => ['required' => true, 'description' => '...'],
        'marketing' => ['required' => false, 'description' => '...'],
        'analytics' => ['required' => false, 'description' => '...'],
    ],
    'require_explicit_consent' => true,
],
```

### Data Retention Configuration

```php
'data_retention' => [
    'enabled' => true,
    'policies' => [
        'audit_logs' => ['retention_days' => 2555, 'delete_after_days' => 2555],
        'deleted_users' => ['retention_days' => 30, 'delete_after_days' => 30],
    ],
    'auto_delete' => false,
    'prefer_anonymization' => true,
],
```

### Encryption Configuration

```php
'encryption' => [
    'enabled' => true,
    'algorithm' => 'AES-256-CBC',
    'encrypted_fields' => [
        'User' => [],
        'SupportTicket' => [],
    ],
    'auto_decrypt' => true,
],
```

---

## Audit Logging

Audit logging provides a complete trail of all data access and changes for compliance purposes.

### Implementing Audit Logging

#### Method 1: Using the Auditable Trait (Automatic)

Add the `Auditable` trait to any model that needs automatic audit logging:

```php
<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use Auditable;

    // Optional: Specify fields to mask in audit logs
    protected $auditMaskedFields = [
        'password',
        'credit_card',
        'ssn',
    ];
}
```

The trait automatically logs:
- `created` - When a new record is created
- `updated` - When a record is updated
- `deleted` - When a record is deleted
- `restored` - When a soft-deleted record is restored

#### Method 2: Using the AuditLogger Service (Manual)

For custom events or more control, use the `AuditLogger` service:

```php
<?php

use App\Services\Compliance\AuditLogger;

class CustomerController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function show(Customer $customer)
    {
        // Log data access
        $this->auditLogger->logAccess(
            auditable: $customer,
            tags: ['customer_view', 'sensitive_data']
        );

        return view('customers.show', compact('customer'));
    }

    public function export(Customer $customer)
    {
        // Log data export
        $this->auditLogger->logExport(
            auditable: $customer,
            exportType: 'pdf',
            tags: ['gdpr', 'data_export']
        );

        return $customer->exportToPdf();
    }
}
```

#### AuditLogger Methods

```php
// Log data access
$auditLogger->logAccess($model, $tags = []);

// Log data change
$auditLogger->logChange($model, $oldValues, $newValues, $tags = []);

// Log data deletion
$auditLogger->logDeletion($model, $tags = []);

// Log data export
$auditLogger->logExport($model, $exportType, $tags = []);

// Log custom event
$auditLogger->logCustomEvent($event, $model, $data = [], $tags = []);
```

### Querying Audit Logs

```php
use App\Models\AuditLog;

// Get all audit logs for a model
$logs = AuditLog::forModel($customer)->get();

// Get logs for a specific event
$logs = AuditLog::byEvent('updated')->get();

// Get logs within a date range
$logs = AuditLog::between('2024-01-01', '2024-12-31')->get();

// Get logs by user
$logs = AuditLog::byUser($user)->get();

// Get logs with specific tags
$logs = AuditLog::withTags(['sensitive_data', 'gdpr'])->get();

// Get recent activity
$logs = AuditLog::recent(7)->get(); // Last 7 days
```

### Audit Event Listeners

The system automatically logs authentication events:

- User login
- User logout
- Failed login attempts

These are configured in `app/Listeners/Compliance/LogAuthenticationEvents.php`.

To log custom events, create an event and listener:

```php
// app/Events/SensitiveDataAccessed.php
<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;

class SensitiveDataAccessed
{
    public function __construct(
        public Model $model,
        public string $action,
        public array $metadata = []
    ) {}
}

// In your controller
event(new SensitiveDataAccessed($customer, 'view', ['ip' => request()->ip()]));
```

---

## Consent Management

Consent management enables GDPR-compliant tracking of user consents and permissions.

### Recording Consent

#### Using ConsentManager Service

```php
<?php

use App\Services\Compliance\ConsentManager;

class RegistrationController extends Controller
{
    public function __construct(
        protected ConsentManager $consentManager
    ) {}

    public function store(Request $request)
    {
        // Create user
        $user = User::create($request->validated());

        // Record required consents
        $this->consentManager->recordConsent(
            consentType: 'terms_of_service',
            user: $user,
            metadata: ['version' => '2.1', 'ip' => $request->ip()]
        );

        $this->consentManager->recordConsent(
            consentType: 'privacy_policy',
            user: $user
        );

        // Record optional consent if given
        if ($request->boolean('marketing_consent')) {
            $this->consentManager->recordConsent(
                consentType: 'marketing',
                user: $user
            );
        }

        return redirect()->route('dashboard');
    }
}
```

#### Recording Multiple Consents

```php
// Record multiple consents at once
$this->consentManager->recordMultipleConsents(
    user: $user,
    consents: [
        'terms_of_service',
        'privacy_policy',
        'marketing',
    ]
);
```

### Withdrawing Consent

```php
// Withdraw a specific consent
$this->consentManager->withdrawConsent(
    consentType: 'marketing',
    user: $user,
    metadata: ['reason' => 'User unsubscribed via email link']
);

// Withdraw all consents
$this->consentManager->withdrawAllConsents($user);
```

### Checking Consent

```php
// Check if user has active consent
if ($this->consentManager->checkConsent('marketing', $user)) {
    // Send marketing email
}

// Check if user has all required consents
if ($this->consentManager->hasRequiredConsents($user)) {
    // Proceed with operation
}

// Get missing required consents
$missing = $this->consentManager->getMissingRequiredConsents($user);
// Returns: ['terms_of_service', 'privacy_policy']
```

### Consent Verification Middleware

Protect routes with consent verification:

```php
// In routes/web.php
Route::middleware(['auth', 'consent:marketing'])->group(function () {
    Route::get('/marketing-preferences', [PreferencesController::class, 'marketing']);
});

// Check multiple consent types
Route::middleware(['auth', 'consent:marketing,analytics'])->group(function () {
    Route::get('/analytics-dashboard', [AnalyticsController::class, 'index']);
});

// Check all required consents
Route::middleware(['auth', 'consent'])->group(function () {
    Route::get('/account', [AccountController::class, 'index']);
});
```

When consent is missing, the middleware will:
- For web requests: Redirect to `consent.required` route
- For API requests: Return JSON response with 403 status

### Consent API Endpoints

```php
// Get available consent types
GET /api/consent/types

// Get user's consents
GET /api/consent

// Record consent
POST /api/consent
{
    "consent_type": "marketing",
    "metadata": {"source": "preferences_page"}
}

// Withdraw consent
DELETE /api/consent/{id}

// Check required consents
GET /api/consent/check-required
```

See the [Compliance API Documentation](compliance-api.md) for complete API reference.

---

## Data Retention Policies

Data retention policies automate the lifecycle of data according to compliance requirements.

### Creating Retention Policies

#### Using the Database Seeder

Default policies are created via `ComplianceSeeder`:

```php
php artisan db:seed --class=ComplianceSeeder
```

#### Programmatically

```php
use App\Models\DataRetentionPolicy;

// Create a retention policy
DataRetentionPolicy::create([
    'model_type' => 'App\\Models\\SupportTicket',
    'retention_period_days' => 2190, // 6 years
    'delete_after_days' => 2190,
    'conditions' => [
        'status' => 'closed',
    ],
    'is_active' => true,
]);

// Create policy for deleted users with grace period
DataRetentionPolicy::create([
    'model_type' => 'App\\Models\\User',
    'retention_period_days' => 30,
    'delete_after_days' => 30,
    'conditions' => [
        'deleted_at' => ['operator' => 'not_null'],
    ],
    'is_active' => true,
]);
```

### Condition Syntax

Policies support flexible condition matching:

```php
// Simple equality
'conditions' => [
    'status' => 'closed',
]

// Comparison operators
'conditions' => [
    'created_at' => ['operator' => 'lt', 'value' => '2020-01-01'],
]

// Multiple values (IN)
'conditions' => [
    'status' => ['operator' => 'in', 'value' => ['closed', 'archived']],
]

// Not in
'conditions' => [
    'status' => ['operator' => 'not_in', 'value' => ['active', 'pending']],
]

// Greater than / Less than
'conditions' => [
    'priority' => ['operator' => 'gte', 'value' => 5],
]

// Check for null/not null
'conditions' => [
    'deleted_at' => ['operator' => 'not_null'],
]
```

### Applying Retention Policies

#### Manual Application

```php
use App\Services\Compliance\DataRetentionService;

$service = app(DataRetentionService::class);

// Apply all active policies (dry-run mode)
$results = $service->applyPolicies(dryRun: true);

foreach ($results as $result) {
    echo "Policy ID {$result['policy_id']}: ";
    echo "{$result['deleted']} records deleted, ";
    echo "{$result['anonymized']} records anonymized\n";
}

// Apply policies for real
$results = $service->applyPolicies(dryRun: false);

// Delete expired data immediately (override auto_delete config)
$results = $service->deleteExpiredData(force: true);
```

#### Automatic Scheduled Cleanup

The system automatically runs cleanup daily at 2:00 AM via the scheduled command:

```bash
# Run manually
php artisan cleanup:expired-data

# Dry-run to preview changes
php artisan cleanup:expired-data --dry-run

# Force deletion (override auto_delete config)
php artisan cleanup:expired-data --force

# Filter by model
php artisan cleanup:expired-data --model="App\Models\SupportTicket"

# Show statistics only
php artisan cleanup:expired-data --stats
```

### Querying Expired Data

```php
use App\Services\Compliance\DataRetentionService;

$service = app(DataRetentionService::class);

// Get all expired records
$expired = $service->getExpiredRecords();

// Get expired records for specific model
$expired = $service->getExpiredRecords('App\\Models\\SupportTicket');

// Get only deletable records (past delete_after_days)
$deletable = $service->getExpiredRecords(null, true);

// Check if specific record is expired
if ($service->isRecordExpired($ticket)) {
    // Handle expired record
}

// Get days until expiration
$days = $service->getDaysUntilExpiration($ticket);
// Returns: 45 (positive = future, negative = already expired)
```

---

## Field-Level Encryption

Field-level encryption protects sensitive data at rest using AES-256-CBC encryption.

### Implementing Encryption

#### Using the Encryptable Trait

Add the `Encryptable` trait to models with sensitive fields:

```php
<?php

namespace App\Models;

use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use Encryptable;

    /**
     * The attributes that should be encrypted.
     */
    protected $encrypted = [
        'email',
        'phone',
        'ssn',
        'credit_card',
    ];
}
```

The trait automatically:
- Encrypts fields when saving to database
- Decrypts fields when retrieving from database
- Handles array/JSON conversion properly

#### Using FieldEncryption Service

For manual encryption/decryption:

```php
use App\Services\Compliance\FieldEncryption;

$encryption = app(FieldEncryption::class);

// Encrypt a value
$encrypted = $encryption->encrypt('sensitive data');

// Decrypt a value
$decrypted = $encryption->decrypt($encrypted);

// Encrypt multiple values
$encrypted = $encryption->encrypt([
    'ssn' => '123-45-6789',
    'credit_card' => '4111-1111-1111-1111',
]);

// Check if a value is encrypted
if ($encryption->isEncryptedValue($value)) {
    $decrypted = $encryption->decrypt($value);
}
```

### Configuration

Configure encryption settings in `config/compliance.php`:

```php
'encryption' => [
    'enabled' => true,
    'algorithm' => 'AES-256-CBC',
    'auto_decrypt' => true,

    'encrypted_fields' => [
        'User' => ['email', 'phone'],
        'SupportTicket' => ['subject', 'description'],
        'Customer' => ['ssn', 'credit_card'],
    ],
],
```

### Encryption Key Rotation

Rotate encryption keys for enhanced security:

```bash
# Generate a new APP_KEY
php artisan key:generate --show

# Rotate keys with dry-run to preview
php artisan encryption:rotate-keys --old-key="base64:OLD_KEY_HERE" --dry-run

# Rotate keys for real
php artisan encryption:rotate-keys --old-key="base64:OLD_KEY_HERE"

# Rotate keys for specific model only
php artisan encryption:rotate-keys --old-key="base64:OLD_KEY_HERE" --model=User

# Process in smaller batches for large datasets
php artisan encryption:rotate-keys --old-key="base64:OLD_KEY_HERE" --batch-size=50

# Show encryption statistics
php artisan encryption:rotate-keys --stats
```

**Important:** Always backup your database before rotating keys!

### Best Practices

1. **Searchability**: Encrypted fields cannot be searched efficiently. Consider:
   - Using hashed versions for searchable fields
   - Encrypting only truly sensitive fields
   - Using database-level encryption for searchable sensitive data

2. **Performance**: Encryption/decryption adds overhead. Minimize by:
   - Only encrypting necessary fields
   - Using `auto_decrypt => true` for automatic handling
   - Caching decrypted values when appropriate

3. **Key Management**:
   - Store `APP_KEY` securely (never commit to Git)
   - Use environment variables for keys
   - Implement regular key rotation (e.g., every 90 days)
   - Keep old keys accessible for decryption during rotation

---

## GDPR Rights Implementation

The system provides comprehensive GDPR rights implementation including Right to Erasure and Data Portability.

### Right to Erasure (Right to be Forgotten)

#### Requesting Data Deletion

Users can request deletion of their data:

```php
use App\Services\Compliance\RightToErasureService;

class ProfileController extends Controller
{
    public function __construct(
        protected RightToErasureService $erasureService
    ) {}

    public function requestDeletion(Request $request)
    {
        $deletionRequest = $this->erasureService->requestDeletion(
            user: auth()->user(),
            reason: $request->input('reason')
        );

        return redirect()->back()->with('success',
            'Your deletion request has been submitted and will be processed within 30 days.'
        );
    }
}
```

#### Processing Deletion Requests

Compliance officers can process requests:

```php
use App\Models\DataDeletionRequest;
use App\Services\Compliance\RightToErasureService;

$request = DataDeletionRequest::findOrFail($id);

// Process the request
$result = app(RightToErasureService::class)->processRequest($request);

if ($result['success']) {
    echo "Deleted {$result['deleted_count']} records\n";
    echo "Anonymized {$result['anonymized_count']} records\n";
}
```

#### Anonymization vs Deletion

By default, the system **anonymizes** data instead of hard-deleting it to preserve referential integrity:

```php
// config/compliance.php
'gdpr' => [
    'right_to_erasure' => [
        'anonymize_data' => true, // Anonymize instead of delete
        'erasable_models' => [
            'User',
            'ConsentRecord',
            'SupportTicket',
        ],
        'retained_models' => [
            'AuditLog', // Keep for compliance
            'DataDeletionRequest', // Keep for compliance
        ],
    ],
],
```

Anonymization replaces sensitive data with placeholder values:
- Email: `deleted_user_{id}@anonymized.local`
- Name: `Deleted User`
- Phone: `000-000-0000`
- Other fields: `null` or empty strings

### Data Portability (Data Export)

#### Exporting User Data

Users can export their data in multiple formats:

```php
use App\Services\Compliance\RightToErasureService;

class ProfileController extends Controller
{
    public function exportData(Request $request)
    {
        $format = $request->input('format', 'json'); // json, csv, or pdf

        $data = app(RightToErasureService::class)->exportUserData(
            user: auth()->user(),
            format: $format,
            includeAuditLogs: false
        );

        return response()->download($data['file_path'], $data['filename']);
    }
}
```

#### Async Export with Job Queue

For large datasets, use the queued job:

```php
use App\Jobs\ExportUserDataJob;

// Dispatch export job
ExportUserDataJob::dispatch(
    userId: auth()->id(),
    format: 'json',
    includeAuditLogs: false
);

// User will receive notification when export is complete
return redirect()->back()->with('success',
    'Your data export has been queued. You will receive an email when it is ready.'
);
```

#### Export Format Examples

**JSON Export:**
```json
{
  "user": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-01T00:00:00Z"
  },
  "consents": [
    {
      "type": "marketing",
      "given_at": "2024-01-01T00:00:00Z",
      "status": "active"
    }
  ],
  "support_tickets": [
    {
      "id": 456,
      "subject": "Help request",
      "created_at": "2024-01-15T00:00:00Z"
    }
  ]
}
```

**CSV Export:** Separate CSV files for each data type in a ZIP archive.

**PDF Export:** Formatted PDF document with all user data.

### API Endpoints

```php
// Request data deletion
POST /api/deletion-requests
{
    "reason": "I want to delete my account"
}

// Export user data (async)
POST /api/deletion-requests/export
{
    "format": "json",
    "include_audit_logs": false
}

// Export user data (sync)
GET /api/deletion-requests/export-sync?format=json

// Check deletion request status
GET /api/deletion-requests
```

---

## Compliance Dashboard

The compliance dashboard provides real-time monitoring and reporting of compliance metrics.

### Accessing the Dashboard

Navigate to `/compliance/dashboard` (requires authentication).

### Dashboard Sections

#### 1. Compliance Status Overview

Shows overall compliance status for:
- **GDPR Compliance**: Consent management, data retention, right to erasure
- **HIPAA Compliance**: PHI encryption, access controls, audit logging
- **SOC 2 Compliance**: Security monitoring, change management, incident response

Each section displays:
- Status badge (Compliant, Warnings, Issues)
- List of any compliance issues
- Quick actions to resolve issues

#### 2. Metrics Summary

Key metrics displayed:
- Total audit logs (with retention period)
- Active consents / Total consents
- Expired records awaiting deletion
- Encrypted models and fields

#### 3. Audit Activity

Recent audit activity broken down by:
- Event type (created, updated, deleted, etc.)
- Model type
- User activity
- Time-based filtering

#### 4. Consent Breakdown

Consent statistics showing:
- Consent rates by type
- Active vs withdrawn consents
- Required consent compliance percentage

#### 5. Export Options

Generate compliance reports in multiple formats:
- CSV for data analysis
- JSON for programmatic access
- PDF for formal compliance reviews

### Filtering Data

Use the filter form to narrow down audit logs:

```php
// Date range
From: 2024-01-01
To: 2024-12-31

// Event type
Event: updated

// Model type
Model: App\Models\User

// User
User ID: 123

// IP Address
IP: 192.168.1.1

// Tags
Tags: sensitive_data,gdpr
```

### Exporting Reports

#### Web Interface

1. Navigate to `/compliance/audit-logs`
2. Apply desired filters
3. Click "Export as CSV", "Export as JSON", or "Export as PDF"
4. Report downloads with filters applied

#### Programmatically

```php
use App\Services\Compliance\AuditReportGenerator;

$generator = app(AuditReportGenerator::class);

// Generate CSV report
$csvPath = $generator->generate(
    format: 'csv',
    options: [
        'from_date' => '2024-01-01',
        'to_date' => '2024-12-31',
        'event' => 'updated',
    ]
);

// Generate PDF report
$pdfPath = $generator->generate(
    format: 'pdf',
    options: [
        'from_date' => '2024-01-01',
        'to_date' => '2024-12-31',
        'orientation' => 'landscape',
    ]
);

// Get summary statistics
$summary = $generator->getSummary([
    'from_date' => '2024-01-01',
    'to_date' => '2024-12-31',
]);
```

---

## Best Practices

### Security Best Practices

#### 1. Protect Sensitive Configuration

Never commit sensitive data to version control:

```bash
# .gitignore
.env
.env.local
.env.*.local
```

Use environment variables for all sensitive configuration:

```env
APP_KEY=base64:...
COMPLIANCE_OFFICER_EMAILS=officer1@example.com,officer2@example.com
COMPLIANCE_INCIDENT_EMAIL=security@example.com
```

#### 2. Implement Access Controls

Restrict compliance dashboard access to authorized users only:

```php
// app/Http/Middleware/ComplianceOfficer.php
public function handle($request, Closure $next)
{
    if (!$request->user()?->hasRole('compliance_officer')) {
        abort(403, 'Unauthorized access to compliance features');
    }

    return $next($request);
}

// routes/web.php
Route::middleware(['auth', 'compliance_officer'])->group(function () {
    Route::get('/compliance/dashboard', [ComplianceController::class, 'dashboard']);
    Route::get('/compliance/audit-logs', [ComplianceController::class, 'auditLogs']);
});
```

#### 3. Enable Encryption

Always enable encryption for sensitive data:

```php
// config/compliance.php
'encryption' => [
    'enabled' => true,
    'algorithm' => 'AES-256-CBC',
    'auto_decrypt' => true,
],
```

And specify encrypted fields:

```php
protected $encrypted = [
    'email',
    'phone',
    'ssn',
    'credit_card',
    'date_of_birth',
];
```

#### 4. Regular Key Rotation

Implement regular encryption key rotation:

```bash
# Every 90 days
php artisan encryption:rotate-keys --old-key="base64:OLD_KEY"
```

Set up a reminder or calendar event to ensure this happens regularly.

### GDPR Best Practices

#### 1. Explicit Consent

Always require explicit consent for data processing:

```php
'consent' => [
    'require_explicit_consent' => true,
],
```

Never use pre-checked boxes or assumed consent.

#### 2. Granular Consent Types

Define specific consent types for different purposes:

```php
'types' => [
    'terms_of_service' => ['required' => true],
    'privacy_policy' => ['required' => true],
    'marketing' => ['required' => false],
    'analytics' => ['required' => false],
    'third_party_sharing' => ['required' => false],
],
```

#### 3. Easy Consent Withdrawal

Make it easy for users to withdraw consent:

```php
// In user profile
<form method="POST" action="{{ route('consent.withdraw') }}">
    @csrf
    <input type="hidden" name="consent_type" value="marketing">
    <button type="submit">Unsubscribe from Marketing</button>
</form>
```

#### 4. Timely Deletion Requests

Process deletion requests within 30 days (GDPR requirement):

```php
'gdpr' => [
    'right_to_erasure' => [
        'processing_days' => 30,
        'send_confirmation' => true,
    ],
],
```

Monitor overdue requests:

```php
$overdueRequests = DataDeletionRequest::query()
    ->where('status', 'pending')
    ->where('requested_at', '<', now()->subDays(30))
    ->get();
```

### Audit Logging Best Practices

#### 1. Log All Sensitive Operations

Log access to any sensitive or personal data:

```php
public function show(Customer $customer)
{
    // Log the access
    app(AuditLogger::class)->logAccess($customer, ['pii_access']);

    return view('customers.show', compact('customer'));
}
```

#### 2. Use Descriptive Tags

Use tags to categorize audit events:

```php
$this->auditLogger->logExport(
    auditable: $customer,
    exportType: 'pdf',
    tags: ['gdpr', 'data_portability', 'customer_request']
);
```

#### 3. Mask Sensitive Fields

Configure masked fields to prevent logging sensitive data:

```php
'audit_logging' => [
    'masked_fields' => [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'api_key',
        'credit_card',
        'ssn',
        'cvv',
        'pin',
    ],
],
```

#### 4. Regular Audit Reviews

Schedule regular reviews of audit logs:

```bash
# Monthly compliance review
php artisan compliance:generate-report --from-date="2024-01-01" --format=pdf
```

### Data Retention Best Practices

#### 1. Define Clear Policies

Create retention policies based on legal requirements:

```php
// GDPR: Personal data should not be kept longer than necessary
// SOC 2: Audit logs should be kept for 7 years
// HIPAA: Medical records for 6 years

'policies' => [
    'audit_logs' => ['retention_days' => 2555], // 7 years
    'consent_records' => ['retention_days' => 2555], // 7 years
    'support_tickets' => ['retention_days' => 2190], // 6 years
    'deleted_users' => ['retention_days' => 30], // 30 days grace
],
```

#### 2. Use Anonymization

Prefer anonymization over deletion to maintain referential integrity:

```php
'data_retention' => [
    'prefer_anonymization' => true,
],
```

#### 3. Test with Dry-Run

Always test retention policies with dry-run before applying:

```bash
php artisan cleanup:expired-data --dry-run --stats
```

#### 4. Monitor Expired Data

Regularly check for expired data awaiting deletion:

```php
$expired = app(DataRetentionService::class)->getExpiredRecords();

if ($expired->count() > 1000) {
    // Alert compliance officer
    Mail::to(config('compliance.notifications.compliance_officers'))
        ->send(new ExpiredDataAlert($expired->count()));
}
```

---

## Common Scenarios

### Scenario 1: New User Registration with Consent

```php
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
        'terms_accepted' => 'required|accepted',
        'privacy_accepted' => 'required|accepted',
        'marketing_consent' => 'boolean',
    ]);

    // Create user
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    // Record required consents
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('terms_of_service', $user, metadata: [
        'version' => '2.1',
        'accepted_at' => now()->toDateTimeString(),
    ]);

    $consentManager->recordConsent('privacy_policy', $user, metadata: [
        'version' => '1.5',
        'accepted_at' => now()->toDateTimeString(),
    ]);

    // Record optional marketing consent if given
    if ($validated['marketing_consent'] ?? false) {
        $consentManager->recordConsent('marketing', $user);
    }

    // Log the registration
    app(AuditLogger::class)->logCustomEvent(
        event: 'user_registered',
        auditable: $user,
        tags: ['registration', 'new_user']
    );

    // Authenticate and redirect
    auth()->login($user);

    return redirect()->route('dashboard');
}
```

### Scenario 2: Viewing Sensitive Customer Data

```php
public function show(Customer $customer)
{
    // Check if user has permission
    $this->authorize('view', $customer);

    // Log the access for compliance
    app(AuditLogger::class)->logAccess(
        auditable: $customer,
        tags: ['customer_view', 'pii_access', 'gdpr']
    );

    // Decrypt sensitive fields (automatic with Encryptable trait)
    // The encrypted fields are automatically decrypted when accessed
    $customer->load(['supportTickets', 'consents']);

    // Emit event for additional tracking if needed
    event(new SensitiveDataAccessed($customer, 'view', [
        'user_id' => auth()->id(),
        'ip' => request()->ip(),
    ]));

    return view('customers.show', compact('customer'));
}
```

### Scenario 3: Handling User Deletion Request

```php
public function deleteAccount(Request $request)
{
    $user = auth()->user();

    // Validate deletion reason
    $validated = $request->validate([
        'reason' => 'required|string|max:500',
        'confirm_password' => 'required|current_password',
    ]);

    // Create deletion request
    $deletionRequest = app(RightToErasureService::class)->requestDeletion(
        user: $user,
        reason: $validated['reason']
    );

    // Log the request
    app(AuditLogger::class)->logCustomEvent(
        event: 'deletion_requested',
        auditable: $user,
        data: ['request_id' => $deletionRequest->id],
        tags: ['gdpr', 'right_to_erasure', 'user_request']
    );

    // Log user out
    auth()->logout();

    return redirect()->route('home')->with('success',
        'Your account deletion request has been submitted. ' .
        'Your data will be deleted within 30 days. ' .
        'You will receive a confirmation email when the process is complete.'
    );
}
```

### Scenario 4: Exporting User Data (GDPR Data Portability)

```php
public function exportMyData(Request $request)
{
    $user = auth()->user();

    $validated = $request->validate([
        'format' => 'required|in:json,csv,pdf',
        'include_audit_logs' => 'boolean',
    ]);

    // Check if user has an active export (prevent abuse)
    $recentExport = $user->dataExports()
        ->where('created_at', '>', now()->subHour())
        ->exists();

    if ($recentExport) {
        return back()->withErrors([
            'export' => 'You can only request one export per hour.'
        ]);
    }

    // Dispatch async export job
    ExportUserDataJob::dispatch(
        userId: $user->id,
        format: $validated['format'],
        includeAuditLogs: $validated['include_audit_logs'] ?? false
    );

    // Log the export request
    app(AuditLogger::class)->logCustomEvent(
        event: 'data_export_requested',
        auditable: $user,
        data: ['format' => $validated['format']],
        tags: ['gdpr', 'data_portability', 'user_request']
    );

    return back()->with('success',
        'Your data export has been queued. ' .
        'You will receive an email with a download link when it is ready.'
    );
}
```

### Scenario 5: Processing Scheduled Data Cleanup

```php
// This runs automatically via scheduler, but can be manually triggered

php artisan cleanup:expired-data

// Or in code (e.g., for custom cleanup logic):

use App\Services\Compliance\DataRetentionService;

$service = app(DataRetentionService::class);

// Preview what would be deleted
$preview = $service->applyPolicies(dryRun: true);

Log::info('Data retention preview:', $preview);

// If preview looks good, apply for real
if ($shouldApplyPolicies) {
    $results = $service->applyPolicies(dryRun: false);

    // Log the cleanup
    app(AuditLogger::class)->logCustomEvent(
        event: 'data_retention_applied',
        auditable: null,
        data: [
            'results' => $results,
            'total_deleted' => collect($results)->sum('deleted'),
            'total_anonymized' => collect($results)->sum('anonymized'),
        ],
        tags: ['data_retention', 'scheduled_cleanup', 'compliance']
    );

    // Notify compliance officer
    if (config('compliance.notifications.enabled')) {
        Mail::to(config('compliance.notifications.compliance_officers'))
            ->send(new DataCleanupCompleted($results));
    }
}
```

### Scenario 6: Implementing Marketing Consent Check

```php
public function sendMarketingEmail(User $user, string $campaignId)
{
    $consentManager = app(ConsentManager::class);

    // Check if user has active marketing consent
    if (!$consentManager->checkConsent('marketing', $user)) {
        Log::info('Marketing email not sent - no consent', [
            'user_id' => $user->id,
            'campaign_id' => $campaignId,
        ]);

        return false;
    }

    // Send the email
    Mail::to($user)->send(new MarketingCampaign($campaignId));

    // Log the marketing communication
    app(AuditLogger::class)->logCustomEvent(
        event: 'marketing_email_sent',
        auditable: $user,
        data: [
            'campaign_id' => $campaignId,
            'consent_verified' => true,
        ],
        tags: ['marketing', 'email', 'consent_verified']
    );

    return true;
}
```

---

## Troubleshooting

### Common Issues

#### Issue 1: Audit Logs Not Being Created

**Symptoms:**
- Models with `Auditable` trait don't create audit logs
- No entries in `audit_logs` table

**Solutions:**

1. Check if audit logging is enabled:
   ```php
   // config/compliance.php
   'audit_logging' => [
       'enabled' => true,
   ],
   ```

2. Verify the model isn't excluded:
   ```php
   'audit_logging' => [
       'excluded_models' => [
           // Make sure your model isn't listed here
       ],
   ],
   ```

3. Check if the event is excluded:
   ```php
   'audit_logging' => [
       'excluded_events' => [
           // Make sure the event isn't listed here
       ],
   ],
   ```

4. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

5. Verify the trait is properly imported:
   ```php
   use App\Traits\Auditable;

   class YourModel extends Model
   {
       use Auditable;
   }
   ```

#### Issue 2: Encrypted Fields Not Decrypting

**Symptoms:**
- Encrypted data shows as encrypted string instead of original value
- Getting encryption errors when accessing fields

**Solutions:**

1. Verify encryption is enabled:
   ```php
   // config/compliance.php
   'encryption' => [
       'enabled' => true,
       'auto_decrypt' => true,
   ],
   ```

2. Check the `APP_KEY` is set correctly:
   ```bash
   php artisan key:generate
   ```

3. Verify the field is in the `$encrypted` array:
   ```php
   protected $encrypted = [
       'email',
       'phone',
   ];
   ```

4. Clear config cache:
   ```bash
   php artisan config:clear
   ```

5. If you recently rotated keys, ensure the new key is set:
   ```env
   APP_KEY=base64:NEW_KEY_HERE
   ```

#### Issue 3: Consent Middleware Blocking Access

**Symptoms:**
- Users being redirected even though they have consent
- 403 errors on consent-protected routes

**Solutions:**

1. Check if the user has the required consent:
   ```php
   $consentManager = app(ConsentManager::class);
   $hasConsent = $consentManager->checkConsent('marketing', $user);
   ```

2. Verify the consent type matches:
   ```php
   // routes/web.php
   Route::middleware(['auth', 'consent:marketing']) // Must match consent type
   ```

3. Check if consent was withdrawn:
   ```php
   $consents = ConsentRecord::where('user_id', $user->id)
       ->where('consent_type', 'marketing')
       ->get();
   ```

4. Verify required consents in config:
   ```php
   'consent' => [
       'types' => [
           'marketing' => [
               'required' => false, // Should match your use case
           ],
       ],
   ],
   ```

#### Issue 4: Data Retention Not Deleting Records

**Symptoms:**
- Expired data not being deleted
- `cleanup:expired-data` command not working

**Solutions:**

1. Check if auto-delete is enabled:
   ```php
   // config/compliance.php
   'data_retention' => [
       'auto_delete' => true, // Must be true for automatic deletion
   ],
   ```

   Or use `--force` flag:
   ```bash
   php artisan cleanup:expired-data --force
   ```

2. Verify retention policies exist:
   ```php
   DataRetentionPolicy::where('is_active', true)->get();
   ```

3. Run with dry-run to see what would be deleted:
   ```bash
   php artisan cleanup:expired-data --dry-run --stats
   ```

4. Check policy conditions:
   ```php
   $policy = DataRetentionPolicy::find($policyId);
   dd($policy->conditions);
   ```

5. Verify the cron scheduler is running:
   ```bash
   php artisan schedule:list
   php artisan schedule:run
   ```

#### Issue 5: PDF Report Generation Fails

**Symptoms:**
- PDF export returns error
- "PDF support not available" message

**Solutions:**

1. Ensure dompdf is installed:
   ```bash
   composer require barryvdh/laravel-dompdf
   ```

2. Enable PDF in config:
   ```php
   // config/compliance.php
   'reporting' => [
       'formats' => [
           'pdf' => true,
       ],
   ],
   ```

3. Set environment variable:
   ```env
   COMPLIANCE_REPORTING_PDF=true
   ```

4. Clear config cache:
   ```bash
   php artisan config:clear
   ```

#### Issue 6: Large Dataset Performance Issues

**Symptoms:**
- Slow data exports
- Timeout errors during key rotation
- Memory exhaustion during cleanup

**Solutions:**

1. Use batch processing for key rotation:
   ```bash
   php artisan encryption:rotate-keys --batch-size=50 --old-key="..."
   ```

2. Use async jobs for data exports:
   ```php
   ExportUserDataJob::dispatch($userId, $format);
   ```

3. Increase PHP memory limit:
   ```ini
   ; php.ini
   memory_limit = 512M
   ```

4. Increase PHP execution time:
   ```ini
   ; php.ini
   max_execution_time = 300
   ```

5. Process in smaller chunks:
   ```php
   // Instead of:
   $records->delete();

   // Use:
   $records->chunk(100, function ($chunk) {
       $chunk->each->delete();
   });
   ```

### Debugging Tips

#### Enable Debug Logging

Add logging to track compliance operations:

```php
// config/logging.php
'channels' => [
    'compliance' => [
        'driver' => 'daily',
        'path' => storage_path('logs/compliance.log'),
        'level' => 'debug',
        'days' => 30,
    ],
],

// In your code
Log::channel('compliance')->debug('Audit log created', [
    'model' => $model->getMorphClass(),
    'event' => $event,
    'user_id' => auth()->id(),
]);
```

#### Check Queue Workers

If async jobs aren't processing:

```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Monitor queue in real-time
php artisan queue:listen --verbose
```

#### Verify Database Tables

Ensure all tables exist:

```bash
php artisan migrate:status
```

If migrations are pending:

```bash
php artisan migrate
```

#### Test Compliance Services

Create a test route to verify services:

```php
// routes/web.php (remove after testing)
Route::get('/test-compliance', function () {
    $results = [];

    // Test audit logging
    $results['audit_logging'] = config('compliance.audit_logging.enabled');

    // Test consent management
    $consentManager = app(\App\Services\Compliance\ConsentManager::class);
    $results['consent_manager'] = $consentManager !== null;

    // Test encryption
    $encryption = app(\App\Services\Compliance\FieldEncryption::class);
    $encrypted = $encryption->encrypt('test');
    $decrypted = $encryption->decrypt($encrypted);
    $results['encryption'] = $decrypted === 'test';

    return response()->json($results);
});
```

### Getting Help

If you're still experiencing issues:

1. Check the Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. Enable query logging to debug database issues:
   ```php
   DB::enableQueryLog();
   // ... your code ...
   dd(DB::getQueryLog());
   ```

3. Review the compliance configuration:
   ```bash
   php artisan config:show compliance
   ```

4. Verify service bindings:
   ```bash
   php artisan tinker
   >>> app(\App\Services\Compliance\AuditLogger::class)
   ```

---

## Conclusion

This implementation guide provides comprehensive coverage of the Laravel CRM compliance features. By following the patterns and best practices outlined here, you can implement GDPR, HIPAA, and SOC 2 compliant features in your application.

### Key Takeaways

1. **Audit Everything**: Use the `Auditable` trait and `AuditLogger` service to maintain comprehensive audit trails
2. **Explicit Consent**: Always require explicit, granular consent for data processing
3. **Data Minimization**: Only collect and retain data that is necessary
4. **Encryption**: Protect sensitive data with field-level encryption
5. **User Rights**: Implement GDPR rights (erasure, portability) to respect user privacy
6. **Regular Monitoring**: Use the compliance dashboard to monitor metrics and identify issues
7. **Automation**: Leverage scheduled jobs for data cleanup and retention
8. **Documentation**: Maintain clear records of compliance measures and policies

### Next Steps

1. Review and customize `config/compliance.php` for your specific requirements
2. Add `Auditable` and `Encryptable` traits to sensitive models
3. Configure consent types appropriate for your application
4. Set up data retention policies based on your legal requirements
5. Test all compliance features in a staging environment
6. Train staff on compliance procedures and dashboard usage
7. Schedule regular compliance reviews and audits

### Additional Resources

- [Compliance API Documentation](compliance-api.md) - Complete API reference
- [GDPR Compliance Checklist](gdpr-compliance-checklist.md) - GDPR requirements mapping
- [Laravel Documentation](https://laravel.com/docs) - Laravel framework documentation
- [GDPR Official Text](https://gdpr-info.eu/) - Official GDPR regulation text

---

**Document Version:** 1.0
**Last Updated:** January 2026
**Maintained By:** Compliance Team
