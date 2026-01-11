<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Compliance Mode
    |--------------------------------------------------------------------------
    |
    | Enable or disable compliance features globally. When enabled, audit
    | logging, consent management, and data retention policies will be
    | enforced according to the configuration below.
    |
    */

    'enabled' => env('COMPLIANCE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Audit Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure audit logging behavior including which events to log, how
    | long to retain logs, and what data should be masked for privacy.
    |
    */

    'audit_logging' => [
        'enabled' => env('COMPLIANCE_AUDIT_ENABLED', true),

        // Events that should be automatically logged
        'events' => [
            'created',
            'updated',
            'deleted',
            'restored',
            'viewed',
            'exported',
        ],

        // Events that should NOT be logged (takes precedence over 'events')
        'excluded_events' => [
            // Add events to exclude, e.g., 'viewed' to reduce log volume
        ],

        // Models that should NOT be audited
        'excluded_models' => [
            // Add model classes to exclude from auditing
        ],

        // Fields that should be masked in audit logs for security
        'masked_fields' => [
            'password',
            'password_confirmation',
            'token',
            'secret',
            'api_key',
            'credit_card',
            'ssn',
        ],

        // Number of days to retain audit logs (0 = indefinite)
        'retention_days' => env('COMPLIANCE_AUDIT_RETENTION_DAYS', 2555), // ~7 years for SOC 2

        // Whether to capture IP addresses in audit logs
        'capture_ip' => env('COMPLIANCE_AUDIT_CAPTURE_IP', true),

        // Whether to capture user agent strings in audit logs
        'capture_user_agent' => env('COMPLIANCE_AUDIT_CAPTURE_USER_AGENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Management Configuration
    |--------------------------------------------------------------------------
    |
    | Configure consent types and their purposes for GDPR compliance. Define
    | which consent types are required and how they should be managed.
    |
    */

    'consent' => [
        'enabled' => env('COMPLIANCE_CONSENT_ENABLED', true),

        // Defined consent types and their purposes
        'types' => [
            'terms_of_service' => [
                'required' => true,
                'description' => 'Acceptance of Terms of Service',
                'purpose' => 'Legal agreement for platform usage',
            ],
            'privacy_policy' => [
                'required' => true,
                'description' => 'Acceptance of Privacy Policy',
                'purpose' => 'Data processing and privacy rights acknowledgment',
            ],
            'marketing' => [
                'required' => false,
                'description' => 'Marketing Communications',
                'purpose' => 'Receiving promotional emails and newsletters',
            ],
            'analytics' => [
                'required' => false,
                'description' => 'Analytics and Performance',
                'purpose' => 'Usage analytics and performance monitoring',
            ],
            'third_party_sharing' => [
                'required' => false,
                'description' => 'Third-Party Data Sharing',
                'purpose' => 'Sharing data with integrated third-party services',
            ],
        ],

        // Whether to require explicit consent (opt-in) vs assumed consent (opt-out)
        'require_explicit_consent' => env('COMPLIANCE_EXPLICIT_CONSENT', true),

        // Number of days to retain consent records (0 = indefinite)
        'retention_days' => env('COMPLIANCE_CONSENT_RETENTION_DAYS', 2555), // ~7 years

        // Whether to capture IP addresses when recording consent
        'capture_ip' => env('COMPLIANCE_CONSENT_CAPTURE_IP', true),

        // Whether to capture user agent strings when recording consent
        'capture_user_agent' => env('COMPLIANCE_CONSENT_CAPTURE_USER_AGENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention Policies
    |--------------------------------------------------------------------------
    |
    | Define default data retention policies for different model types. This
    | helps ensure compliance with data minimization principles and legal
    | requirements like GDPR, HIPAA, and SOC 2.
    |
    */

    'data_retention' => [
        'enabled' => env('COMPLIANCE_DATA_RETENTION_ENABLED', true),

        // Default retention periods (in days) for different model types
        'policies' => [
            'audit_logs' => [
                'retention_days' => 2555, // ~7 years for SOC 2
                'delete_after_days' => 2555,
            ],
            'consent_records' => [
                'retention_days' => 2555, // ~7 years
                'delete_after_days' => 2555,
            ],
            'deleted_users' => [
                'retention_days' => 30, // Grace period before permanent deletion
                'delete_after_days' => 30,
            ],
            'support_tickets' => [
                'retention_days' => 2190, // ~6 years
                'delete_after_days' => 2190,
            ],
        ],

        // Grace period before permanent deletion (days)
        'grace_period_days' => env('COMPLIANCE_GRACE_PERIOD_DAYS', 30),

        // Whether to automatically delete expired data
        'auto_delete' => env('COMPLIANCE_AUTO_DELETE', false),

        // Whether to anonymize instead of delete when possible
        'prefer_anonymization' => env('COMPLIANCE_PREFER_ANONYMIZATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Field-Level Encryption Configuration
    |--------------------------------------------------------------------------
    |
    | Configure encryption settings for sensitive data fields. Uses Laravel's
    | encryption system with additional key rotation capabilities.
    |
    */

    'encryption' => [
        'enabled' => env('COMPLIANCE_ENCRYPTION_ENABLED', true),

        // Encryption algorithm (AES-256-CBC recommended)
        'algorithm' => env('COMPLIANCE_ENCRYPTION_ALGORITHM', 'AES-256-CBC'),

        // Fields that should be encrypted (by model)
        'encrypted_fields' => [
            'User' => [
                // Add sensitive user fields that should be encrypted
                // Example: 'ssn', 'date_of_birth', 'phone'
            ],
            'SupportTicket' => [
                // Add sensitive ticket fields that should be encrypted
                // Example: 'customer_data', 'payment_info'
            ],
        ],

        // Whether to automatically decrypt when accessed
        'auto_decrypt' => env('COMPLIANCE_AUTO_DECRYPT', true),

        // Key rotation settings
        'key_rotation' => [
            'enabled' => env('COMPLIANCE_KEY_ROTATION_ENABLED', false),
            'rotation_days' => env('COMPLIANCE_KEY_ROTATION_DAYS', 90),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GDPR Configuration
    |--------------------------------------------------------------------------
    |
    | Configure GDPR-specific features including right to erasure (right to
    | be forgotten), data portability, and data subject access requests.
    |
    */

    'gdpr' => [
        'enabled' => env('COMPLIANCE_GDPR_ENABLED', true),

        // Right to Erasure (Right to be Forgotten)
        'right_to_erasure' => [
            'enabled' => env('COMPLIANCE_RIGHT_TO_ERASURE_ENABLED', true),

            // Number of days to process deletion requests
            'processing_days' => env('COMPLIANCE_ERASURE_PROCESSING_DAYS', 30),

            // Whether to send confirmation email after deletion
            'send_confirmation' => env('COMPLIANCE_ERASURE_CONFIRMATION', true),

            // Whether to anonymize instead of hard delete
            'anonymize_data' => env('COMPLIANCE_ANONYMIZE_DATA', true),

            // Models that should be deleted/anonymized on erasure request
            'erasable_models' => [
                'User',
                'ConsentRecord',
                'SupportTicket',
            ],

            // Models that should be kept for legal/compliance reasons
            'retained_models' => [
                'AuditLog',
                'DataDeletionRequest',
            ],
        ],

        // Right to Data Portability
        'data_portability' => [
            'enabled' => env('COMPLIANCE_DATA_PORTABILITY_ENABLED', true),

            // Formats available for data export
            'export_formats' => ['json', 'csv', 'pdf'],

            // Default export format
            'default_format' => env('COMPLIANCE_EXPORT_FORMAT', 'json'),

            // Whether to include audit logs in export
            'include_audit_logs' => env('COMPLIANCE_EXPORT_INCLUDE_AUDITS', false),

            // Storage configuration for exported files
            'storage_disk' => env('COMPLIANCE_EXPORT_STORAGE_DISK', 'local'),
            'storage_path' => env('COMPLIANCE_EXPORT_STORAGE_PATH', 'exports/user-data'),
        ],

        // Data Subject Access Requests (DSAR)
        'dsar' => [
            'enabled' => env('COMPLIANCE_DSAR_ENABLED', true),

            // Number of days to fulfill DSAR (GDPR requires 30 days)
            'response_days' => env('COMPLIANCE_DSAR_RESPONSE_DAYS', 30),

            // Whether to require identity verification for DSAR
            'require_verification' => env('COMPLIANCE_DSAR_VERIFICATION', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HIPAA Configuration
    |--------------------------------------------------------------------------
    |
    | Configure HIPAA-specific compliance features for healthcare data
    | protection (if applicable to your use case).
    |
    */

    'hipaa' => [
        'enabled' => env('COMPLIANCE_HIPAA_ENABLED', false),

        // Enhanced audit logging for PHI access
        'phi_audit' => [
            'enabled' => env('COMPLIANCE_HIPAA_PHI_AUDIT', false),
            'retention_days' => 2190, // 6 years minimum
        ],

        // Encryption requirements for PHI
        'phi_encryption' => [
            'enabled' => env('COMPLIANCE_HIPAA_PHI_ENCRYPTION', false),
            'require_at_rest' => true,
            'require_in_transit' => true,
        ],

        // Access controls for PHI
        'access_controls' => [
            'enabled' => env('COMPLIANCE_HIPAA_ACCESS_CONTROLS', false),
            'require_mfa' => false,
            'session_timeout_minutes' => 15,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SOC 2 Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SOC 2-specific compliance features for security, availability,
    | processing integrity, confidentiality, and privacy.
    |
    */

    'soc2' => [
        'enabled' => env('COMPLIANCE_SOC2_ENABLED', false),

        // Security monitoring and logging
        'security' => [
            'log_authentication_events' => true,
            'log_authorization_failures' => true,
            'log_data_access' => true,
            'audit_retention_days' => 2555, // ~7 years
        ],

        // Change management tracking
        'change_management' => [
            'enabled' => env('COMPLIANCE_SOC2_CHANGE_MGMT', false),
            'require_approval' => false,
            'track_deployments' => false,
        ],

        // Incident response
        'incident_response' => [
            'enabled' => env('COMPLIANCE_SOC2_INCIDENT_RESPONSE', false),
            'notification_email' => env('COMPLIANCE_INCIDENT_EMAIL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance Reporting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure compliance report generation including formats, scheduling,
    | and distribution settings.
    |
    */

    'reporting' => [
        'enabled' => env('COMPLIANCE_REPORTING_ENABLED', true),

        // Available report formats
        'formats' => [
            'csv' => true,
            'json' => true,
            'pdf' => env('COMPLIANCE_REPORTING_PDF', false), // Requires dompdf or similar
        ],

        // Report types
        'report_types' => [
            'audit_log',
            'consent_summary',
            'data_retention',
            'deletion_requests',
            'access_requests',
        ],

        // Scheduled reports
        'scheduled_reports' => [
            'enabled' => env('COMPLIANCE_SCHEDULED_REPORTS', false),
            'frequency' => env('COMPLIANCE_REPORT_FREQUENCY', 'monthly'), // daily, weekly, monthly
            'recipients' => explode(',', env('COMPLIANCE_REPORT_RECIPIENTS', '')),
        ],

        // Report retention
        'retention_days' => env('COMPLIANCE_REPORT_RETENTION_DAYS', 2555), // ~7 years
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notifications for compliance-related events such as data
    | deletion requests, consent withdrawals, and policy violations.
    |
    */

    'notifications' => [
        'enabled' => env('COMPLIANCE_NOTIFICATIONS_ENABLED', true),

        // Email addresses for compliance notifications
        'compliance_officers' => explode(',', env('COMPLIANCE_OFFICER_EMAILS', '')),

        // Events that trigger notifications
        'notify_on' => [
            'data_deletion_request' => true,
            'data_export_completed' => true,
            'consent_withdrawal' => false,
            'retention_policy_triggered' => false,
            'audit_anomaly' => true,
        ],
    ],

];
