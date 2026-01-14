<?php

return [
    'enabled' => env('ONBOARDING_ENABLED', true),
    'auto_trigger' => env('ONBOARDING_AUTO_TRIGGER', true),
    'allow_skip' => env('ONBOARDING_ALLOW_SKIP', true),
    'allow_restart' => env('ONBOARDING_ALLOW_RESTART', true),

    'steps' => [
        'company_setup' => [
            'title' => 'Company Setup',
            'description' => 'Set up your company profile and basic information',
            'icon' => 'building',
            'view' => 'onboarding.steps.company_setup',
            'handler' => \App\Services\Onboarding\Steps\CompanySetupStep::class,
            'estimated_minutes' => 3,
            'allow_skip' => false,
            'help_text' => 'Enter your company details. The more we know, the better we can customize your CRM.',
            'fields' => [
                'company_name' => [
                    'label' => 'Company Name',
                    'placeholder' => 'Enter your company name',
                    'help' => 'This will be displayed on invoices and reports',
                    'required' => true,
                ],
                'industry' => [
                    'label' => 'Industry',
                    'options' => [
                        'technology' => 'Technology',
                        'retail' => 'Retail',
                        'finance' => 'Finance',
                        'healthcare' => 'Healthcare',
                        'education' => 'Education',
                        'manufacturing' => 'Manufacturing',
                        'service' => 'Service Industry',
                        'other' => 'Other',
                    ],
                    'help' => 'Helps us tailor feature recommendations',
                ],
                'company_size' => [
                    'label' => 'Company Size',
                    'options' => [
                        '1-10' => '1-10 employees',
                        '11-50' => '11-50 employees',
                        '51-200' => '51-200 employees',
                        '201-500' => '201-500 employees',
                        '500+' => '500+ employees',
                    ],
                    'help' => 'Used to optimize your account limits',
                ],
                'phone' => [
                    'label' => 'Phone Number',
                    'placeholder' => '+1 (555) 000-0000',
                    'help' => 'Primary contact number',
                ],
                'website' => [
                    'label' => 'Website',
                    'placeholder' => 'https://example.com',
                    'help' => 'Your company website URL',
                ],
                'address' => [
                    'label' => 'Address',
                    'placeholder' => 'Enter your business address',
                    'help' => 'Primary business location',
                ],
            ]
        ],
        'user_creation' => [
            'title' => 'Add Team Members',
            // ... truncated for brevity, assuming standard config structure ...
            'description' => 'Invite your team to collaborate',
            'icon' => 'users',
            'view' => 'onboarding.steps.user_creation',
            'handler' => \App\Services\Onboarding\Steps\UserCreationStep::class, // Need to make sure this class exists too!
            'estimated_minutes' => 2,
        ],
        'pipeline_config' => [
            'title' => 'Configure Sales Pipeline',
            'description' => 'Customize your sales stages',
            'icon' => 'filter',
            'view' => 'onboarding.steps.pipeline_config',
            'estimated_minutes' => 5,
        ],
        'email_integration' => [
            'title' => 'Email Integration',
            'description' => 'Connect your email account',
            'icon' => 'envelope',
            'view' => 'onboarding.steps.email_integration',
            'estimated_minutes' => 3,
        ],
        'sample_data' => [
            'title' => 'Import Sample Data',
            'description' => 'Start with pre-populated data',
            'icon' => 'database',
            'view' => 'onboarding.steps.sample_data',
            'estimated_minutes' => 1,
        ],
        // ... add other steps as needed
    ],

    'validation' => [
        'company_setup' => [
            'company_name' => 'required|string|max:255',
            'industry' => 'nullable|string',
            'company_size' => 'nullable|string',
        ],
        // ... other validation rules
    ],

    'completion' => [
        'send_welcome_email' => env('ONBOARDING_SEND_WELCOME_EMAIL', true),
        'redirect_to' => env('ONBOARDING_COMPLETION_REDIRECT', '/admin/dashboard'),
        'show_completion_modal' => env('ONBOARDING_SHOW_COMPLETION_MODAL', true),
        'completion_message' => 'Congratulations! You\'ve successfully set up your CRM. You\'re now ready to start managing your customer relationships.',
    ],
];
