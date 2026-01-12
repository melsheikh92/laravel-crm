<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Onboarding Wizard Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the interactive onboarding wizard that guides
    | new users through initial setup. The wizard helps users configure their
    | CRM instance, create their first users, set up pipelines, integrate
    | email, and optionally import sample data.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Onboarding Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the onboarding wizard globally. When enabled, new
    | users will be automatically redirected to the wizard on first login.
    |
    */

    'enabled' => env('ONBOARDING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Auto-trigger on New Installation
    |--------------------------------------------------------------------------
    |
    | When enabled, the wizard will automatically activate for new
    | installations and first-time users who haven't completed onboarding.
    |
    */

    'auto_trigger' => env('ONBOARDING_AUTO_TRIGGER', true),

    /*
    |--------------------------------------------------------------------------
    | Allow Skip
    |--------------------------------------------------------------------------
    |
    | Allow users to skip individual steps in the wizard. Recommended to
    | keep this enabled for better user experience.
    |
    */

    'allow_skip' => env('ONBOARDING_ALLOW_SKIP', true),

    /*
    |--------------------------------------------------------------------------
    | Allow Restart
    |--------------------------------------------------------------------------
    |
    | Allow users to restart the onboarding wizard from the settings menu
    | after they have already completed it.
    |
    */

    'allow_restart' => env('ONBOARDING_ALLOW_RESTART', true),

    /*
    |--------------------------------------------------------------------------
    | Wizard Steps
    |--------------------------------------------------------------------------
    |
    | Define all wizard steps with their metadata, including title,
    | description, help text, icons, and whether they can be skipped.
    |
    */

    'steps' => [
        'company_setup' => [
            'title' => 'Company Setup',
            'short_title' => 'Company',
            'description' => 'Set up your company profile and basic information',
            'icon' => 'building',
            'order' => 1,
            'skippable' => false,
            'estimated_minutes' => 3,
            'help_text' => 'Enter your company details to personalize your CRM experience. This information will be used in reports, email templates, and customer-facing documents.',
            'help_tips' => [
                'Company name will appear in email signatures and documents',
                'Industry selection helps us provide relevant features',
                'Company size helps optimize your dashboard layout',
            ],
            'video_url' => env('ONBOARDING_VIDEO_COMPANY_SETUP', null),
            'video_thumbnail' => null,
            'fields' => [
                'company_name' => [
                    'label' => 'Company Name',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Acme Corporation',
                    'help' => 'Your official company or business name',
                ],
                'industry' => [
                    'label' => 'Industry',
                    'type' => 'select',
                    'required' => false,
                    'options' => [
                        'technology' => 'Technology',
                        'healthcare' => 'Healthcare',
                        'finance' => 'Finance',
                        'retail' => 'Retail',
                        'manufacturing' => 'Manufacturing',
                        'real_estate' => 'Real Estate',
                        'consulting' => 'Consulting',
                        'education' => 'Education',
                        'other' => 'Other',
                    ],
                    'help' => 'Select the industry that best describes your business',
                ],
                'company_size' => [
                    'label' => 'Company Size',
                    'type' => 'select',
                    'required' => false,
                    'options' => [
                        '1-10' => '1-10 employees',
                        '11-50' => '11-50 employees',
                        '51-200' => '51-200 employees',
                        '201-500' => '201-500 employees',
                        '501-1000' => '501-1,000 employees',
                        '1001+' => '1,001+ employees',
                    ],
                    'help' => 'Approximate number of employees in your organization',
                ],
                'address' => [
                    'label' => 'Address',
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => '123 Main Street, Suite 100',
                    'help' => 'Your company\'s primary business address',
                ],
                'phone' => [
                    'label' => 'Phone',
                    'type' => 'tel',
                    'required' => false,
                    'placeholder' => '+1 (555) 123-4567',
                    'help' => 'Main business phone number',
                ],
                'website' => [
                    'label' => 'Website',
                    'type' => 'url',
                    'required' => false,
                    'placeholder' => 'https://example.com',
                    'help' => 'Company website URL',
                ],
            ],
        ],

        'user_creation' => [
            'title' => 'Add Team Members',
            'short_title' => 'Team',
            'description' => 'Invite your first team member to collaborate',
            'icon' => 'users',
            'order' => 2,
            'skippable' => true,
            'estimated_minutes' => 2,
            'help_text' => 'Add team members who will use the CRM. You can invite more users later from the settings panel. Each user will receive an invitation email to set up their account.',
            'help_tips' => [
                'You can add more team members later',
                'Each user will receive an email invitation',
                'Assign appropriate roles based on responsibilities',
            ],
            'video_url' => env('ONBOARDING_VIDEO_USER_CREATION', null),
            'video_thumbnail' => null,
            'fields' => [
                'name' => [
                    'label' => 'Full Name',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'John Doe',
                    'help' => 'Team member\'s full name',
                ],
                'email' => [
                    'label' => 'Email Address',
                    'type' => 'email',
                    'required' => true,
                    'placeholder' => 'john@example.com',
                    'help' => 'Work email address for the invitation',
                ],
                'role' => [
                    'label' => 'Role',
                    'type' => 'select',
                    'required' => false,
                    'options' => [
                        'admin' => 'Administrator',
                        'manager' => 'Manager',
                        'sales_rep' => 'Sales Representative',
                        'support' => 'Support Agent',
                    ],
                    'help' => 'User role determines access permissions',
                ],
                'send_invitation' => [
                    'label' => 'Send invitation email',
                    'type' => 'checkbox',
                    'required' => false,
                    'default' => true,
                    'help' => 'Send an email invitation to this user',
                ],
            ],
        ],

        'pipeline_config' => [
            'title' => 'Configure Sales Pipeline',
            'short_title' => 'Pipeline',
            'description' => 'Set up your sales stages and workflow',
            'icon' => 'filter',
            'order' => 3,
            'skippable' => true,
            'estimated_minutes' => 4,
            'help_text' => 'Define the stages of your sales process. You can customize these stages to match your business workflow. Default stages are provided but can be modified to fit your needs.',
            'help_tips' => [
                'Each stage should represent a distinct phase in your sales process',
                'Probability percentages help with revenue forecasting',
                'You can reorder stages by dragging and dropping',
                'Default pipeline will be created if you skip this step',
            ],
            'video_url' => env('ONBOARDING_VIDEO_PIPELINE_CONFIG', null),
            'video_thumbnail' => null,
            'default_stages' => [
                ['name' => 'New', 'probability' => 10, 'order' => 1],
                ['name' => 'Qualified', 'probability' => 25, 'order' => 2],
                ['name' => 'Proposal', 'probability' => 50, 'order' => 3],
                ['name' => 'Negotiation', 'probability' => 75, 'order' => 4],
                ['name' => 'Won', 'probability' => 100, 'order' => 5],
                ['name' => 'Lost', 'probability' => 0, 'order' => 6],
            ],
            'fields' => [
                'pipeline_name' => [
                    'label' => 'Pipeline Name',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'Sales Pipeline',
                    'default' => 'Default Sales Pipeline',
                    'help' => 'Name for your sales pipeline',
                ],
                'stages' => [
                    'label' => 'Pipeline Stages',
                    'type' => 'repeater',
                    'required' => false,
                    'help' => 'Define the stages of your sales process',
                ],
            ],
        ],

        'email_integration' => [
            'title' => 'Email Integration',
            'short_title' => 'Email',
            'description' => 'Connect your email for seamless communication',
            'icon' => 'envelope',
            'order' => 4,
            'skippable' => true,
            'estimated_minutes' => 5,
            'help_text' => 'Connect your email account to send and receive emails directly from the CRM. You can configure SMTP settings or use a third-party email provider integration.',
            'help_tips' => [
                'Email integration enables sending quotes and invoices',
                'You can sync emails with customer records',
                'Multiple email accounts can be added later',
                'Skip this step if you want to configure email later',
            ],
            'video_url' => env('ONBOARDING_VIDEO_EMAIL_INTEGRATION', null),
            'video_thumbnail' => null,
            'providers' => [
                'smtp' => [
                    'label' => 'Custom SMTP',
                    'description' => 'Use your own SMTP server settings',
                    'icon' => 'server',
                ],
                'gmail' => [
                    'label' => 'Gmail',
                    'description' => 'Connect using Gmail/Google Workspace',
                    'icon' => 'google',
                ],
                'outlook' => [
                    'label' => 'Outlook',
                    'description' => 'Connect using Microsoft Outlook',
                    'icon' => 'microsoft',
                ],
                'sendgrid' => [
                    'label' => 'SendGrid',
                    'description' => 'Use SendGrid email service',
                    'icon' => 'send',
                ],
            ],
            'fields' => [
                'email_provider' => [
                    'label' => 'Email Provider',
                    'type' => 'select',
                    'required' => false,
                    'help' => 'Choose your email service provider',
                ],
                'smtp_host' => [
                    'label' => 'SMTP Host',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'smtp.example.com',
                    'help' => 'SMTP server hostname',
                ],
                'smtp_port' => [
                    'label' => 'SMTP Port',
                    'type' => 'number',
                    'required' => false,
                    'placeholder' => '587',
                    'default' => 587,
                    'help' => 'SMTP server port (usually 587 or 465)',
                ],
                'smtp_username' => [
                    'label' => 'Username',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'your-email@example.com',
                    'help' => 'SMTP authentication username',
                ],
                'smtp_password' => [
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => false,
                    'help' => 'SMTP authentication password',
                ],
                'smtp_encryption' => [
                    'label' => 'Encryption',
                    'type' => 'select',
                    'required' => false,
                    'options' => [
                        'tls' => 'TLS',
                        'ssl' => 'SSL',
                        'none' => 'None',
                    ],
                    'default' => 'tls',
                    'help' => 'Email encryption method',
                ],
                'test_connection' => [
                    'label' => 'Test connection before saving',
                    'type' => 'checkbox',
                    'required' => false,
                    'default' => true,
                    'help' => 'Verify email settings work before proceeding',
                ],
            ],
        ],

        'sample_data' => [
            'title' => 'Import Sample Data',
            'short_title' => 'Sample Data',
            'description' => 'Load sample data to explore CRM features',
            'icon' => 'database',
            'order' => 5,
            'skippable' => true,
            'estimated_minutes' => 2,
            'help_text' => 'Import sample companies, contacts, and deals to help you understand how the CRM works. This is recommended for new users who want to explore features before adding real data.',
            'help_tips' => [
                'Sample data includes companies, contacts, and deals',
                'You can delete sample data at any time',
                'Great way to test features before adding real data',
                'All sample data is clearly marked for easy identification',
            ],
            'video_url' => env('ONBOARDING_VIDEO_SAMPLE_DATA', null),
            'video_thumbnail' => null,
            'sample_data_includes' => [
                '5 sample companies with complete profiles',
                '10-15 sample contacts with job titles and contact info',
                '10 sample deals across different pipeline stages',
                'Sample lead sources and types',
            ],
            'fields' => [
                'import_sample_data' => [
                    'label' => 'Import sample data',
                    'type' => 'checkbox',
                    'required' => false,
                    'default' => false,
                    'help' => 'Load sample data to explore CRM features',
                ],
                'include_companies' => [
                    'label' => 'Include sample companies',
                    'type' => 'checkbox',
                    'required' => false,
                    'default' => true,
                    'help' => 'Import sample organization records',
                ],
                'include_contacts' => [
                    'label' => 'Include sample contacts',
                    'type' => 'checkbox',
                    'required' => false,
                    'default' => true,
                    'help' => 'Import sample person/contact records',
                ],
                'include_deals' => [
                    'label' => 'Include sample deals',
                    'type' => 'checkbox',
                    'required' => false,
                    'default' => true,
                    'help' => 'Import sample deal/opportunity records',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Completion Rewards
    |--------------------------------------------------------------------------
    |
    | Configure what happens when users complete the onboarding wizard.
    |
    */

    'completion' => [
        'send_welcome_email' => env('ONBOARDING_SEND_WELCOME_EMAIL', true),
        'redirect_to' => env('ONBOARDING_COMPLETION_REDIRECT', '/admin/dashboard'),
        'show_completion_modal' => env('ONBOARDING_SHOW_COMPLETION_MODAL', true),
        'completion_message' => 'Congratulations! You\'ve successfully set up your CRM. You\'re now ready to start managing your customer relationships.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Progress Tracking
    |--------------------------------------------------------------------------
    |
    | Configure how onboarding progress is tracked and displayed.
    |
    */

    'progress' => [
        'show_progress_bar' => env('ONBOARDING_SHOW_PROGRESS_BAR', true),
        'show_step_numbers' => env('ONBOARDING_SHOW_STEP_NUMBERS', true),
        'show_time_estimate' => env('ONBOARDING_SHOW_TIME_ESTIMATE', true),
        'save_progress_automatically' => env('ONBOARDING_AUTO_SAVE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics & Reporting
    |--------------------------------------------------------------------------
    |
    | Configure analytics and reporting for onboarding metrics.
    |
    */

    'analytics' => [
        'enabled' => env('ONBOARDING_ANALYTICS_ENABLED', true),
        'track_completion_rate' => true,
        'track_step_completion_times' => true,
        'track_skip_rates' => true,
        'track_drop_off_points' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Define validation rules for each step. These rules are used when
    | validating step completion data.
    |
    */

    'validation' => [
        'company_setup' => [
            'company_name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'company_size' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
        ],
        'user_creation' => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'nullable|string|max:50',
            'send_invitation' => 'nullable|boolean',
        ],
        'pipeline_config' => [
            'pipeline_name' => 'nullable|string|max:255',
            'stages' => 'nullable|array',
            'stages.*.name' => 'required|string|max:100',
            'stages.*.probability' => 'required|integer|min:0|max:100',
        ],
        'email_integration' => [
            'email_provider' => 'nullable|string|max:50',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl,none',
        ],
        'sample_data' => [
            'import_sample_data' => 'nullable|boolean',
            'include_companies' => 'nullable|boolean',
            'include_contacts' => 'nullable|boolean',
            'include_deals' => 'nullable|boolean',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Customization
    |--------------------------------------------------------------------------
    |
    | Customize the appearance and behavior of the onboarding wizard.
    |
    */

    'ui' => [
        'theme' => env('ONBOARDING_THEME', 'light'),
        'primary_color' => env('ONBOARDING_PRIMARY_COLOR', '#3b82f6'),
        'show_help_sidebar' => env('ONBOARDING_SHOW_HELP_SIDEBAR', true),
        'show_video_tutorials' => env('ONBOARDING_SHOW_VIDEO_TUTORIALS', false),
        'animation_enabled' => env('ONBOARDING_ANIMATIONS_ENABLED', true),
    ],

];
