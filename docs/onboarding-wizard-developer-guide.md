# Onboarding Wizard Developer Guide

A comprehensive guide for developers to extend, customize, and maintain the Laravel CRM Interactive Onboarding Wizard.

## Table of Contents

1. [Introduction](#introduction)
2. [Architecture Overview](#architecture-overview)
3. [Prerequisites](#prerequisites)
4. [Adding New Wizard Steps](#adding-new-wizard-steps)
5. [Customizing Existing Steps](#customizing-existing-steps)
6. [Extending the System](#extending-the-system)
7. [Configuration Reference](#configuration-reference)
8. [API Reference](#api-reference)
9. [Frontend Integration](#frontend-integration)
10. [Testing](#testing)
11. [Best Practices](#best-practices)
12. [Troubleshooting](#troubleshooting)
13. [Examples](#examples)

---

## Introduction

The Interactive Onboarding Wizard is a flexible, extensible system for guiding new users through initial CRM setup. This guide explains how to add custom steps, modify existing behavior, and integrate the wizard with your extensions.

### Key Features

- **Step-based architecture**: Modular design for easy extension
- **Contract-driven**: WizardStepContract ensures consistent behavior
- **Configuration-first**: Define steps in config/onboarding.php
- **AJAX-enabled**: Built-in API endpoints for dynamic interactions
- **Progress tracking**: Automatic state management and persistence
- **Middleware integration**: Auto-trigger for new installations

### Use Cases

- Add custom setup steps for your extensions
- Modify wizard flow based on user role or subscription
- Integrate third-party services during onboarding
- Collect additional data during initial setup
- Customize UI for white-label deployments

---

## Architecture Overview

### Core Components

```
app/
├── Contracts/
│   └── WizardStepContract.php          # Interface for all wizard steps
├── Services/
│   ├── OnboardingService.php           # Core wizard logic
│   └── Onboarding/
│       ├── AbstractWizardStep.php      # Base class for steps
│       └── Steps/                      # Individual step implementations
│           ├── CompanySetupStep.php
│           ├── UserCreationStep.php
│           ├── PipelineConfigurationStep.php
│           ├── EmailIntegrationStep.php
│           └── SampleDataImportStep.php
├── Http/
│   ├── Controllers/
│   │   ├── OnboardingController.php    # Web controller
│   │   └── Api/
│   │       └── OnboardingApiController.php  # API endpoints
│   └── Middleware/
│       └── RedirectIfOnboardingIncomplete.php  # Auto-trigger
├── Models/
│   └── OnboardingProgress.php          # Progress tracking model
└── Notifications/
    └── OnboardingComplete.php          # Completion email

resources/views/
├── onboarding/
│   ├── layout.blade.php                # Main wizard layout
│   ├── index.blade.php                 # Welcome page
│   ├── complete.blade.php              # Completion page
│   ├── step.blade.php                  # Step router
│   └── steps/                          # Step views
│       ├── base.blade.php
│       ├── company_setup.blade.php
│       ├── user_creation.blade.php
│       ├── pipeline_config.blade.php
│       ├── email_integration.blade.php
│       └── sample_data.blade.php
└── components/onboarding/              # Reusable components
    ├── progress-indicator.blade.php
    ├── tooltip.blade.php
    ├── info-panel.blade.php
    ├── video-embed.blade.php
    └── field-help.blade.php

config/
└── onboarding.php                      # Wizard configuration

database/
├── migrations/
│   └── 2026_01_12_000000_create_onboarding_progress_table.php
└── seeders/
    └── OnboardingSampleDataSeeder.php

routes/
├── web.php                             # Web routes
└── api.php                             # API routes
```

### Data Flow

```
User Request
    ↓
RedirectIfOnboardingIncomplete Middleware (auto-trigger)
    ↓
OnboardingController (web) or OnboardingApiController (AJAX)
    ↓
OnboardingService (business logic)
    ↓
Step Implementation (CompanySetupStep, etc.)
    ↓
Data Storage (CoreConfig, Database)
    ↓
OnboardingProgress Model (track state)
    ↓
Response (redirect, JSON, or view)
```

### Step Lifecycle

1. **Display**: Controller shows step form with default data
2. **Submit**: User submits form data
3. **Validate**: Step validates data against rules
4. **Execute**: Step processes and stores data
5. **Complete**: Service marks step complete
6. **Navigate**: Redirect to next step or completion

---

## Prerequisites

### Required Knowledge

- **PHP**: Object-oriented programming, namespaces, traits
- **Laravel**: Service providers, facades, Eloquent, validation
- **Blade**: Templating, components, directives
- **JavaScript**: ES6+, Alpine.js (optional)
- **Database**: Migrations, models, relationships

### Software Requirements

- **PHP**: >= 8.1
- **Laravel**: >= 9.0
- **Laravel CRM**: >= 1.0
- **Composer**: >= 2.0

---

## Adding New Wizard Steps

Follow this step-by-step guide to add a custom wizard step.

### Step 1: Create Step Implementation Class

Create a new class extending `AbstractWizardStep`:

```php
<?php

namespace App\Services\Onboarding\Steps;

use App\Contracts\WizardStepContract;
use App\Repositories\CoreConfigRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomSetupStep extends AbstractWizardStep
{
    /**
     * CoreConfig repository for storing data
     */
    protected CoreConfigRepository $coreConfigRepository;

    /**
     * Constructor
     */
    public function __construct(CoreConfigRepository $coreConfigRepository)
    {
        $this->coreConfigRepository = $coreConfigRepository;

        // Load configuration from config/onboarding.php
        $config = config('onboarding.steps.custom_setup', []);

        // Set step properties
        $this->stepId = 'custom_setup';
        $this->title = $config['title'] ?? 'Custom Setup';
        $this->description = $config['description'] ?? 'Configure custom settings';
        $this->icon = $config['icon'] ?? 'settings';
        $this->estimatedMinutes = $config['estimated_minutes'] ?? 5;
        $this->skippable = $config['skippable'] ?? true;
        $this->helpText = $config['help_text'] ?? '';
        $this->helpTips = $config['help_tips'] ?? [];
        $this->validationRules = config('onboarding.validation.custom_setup', []);
    }

    /**
     * Execute the step with provided data
     *
     * @param array $data Step data from form submission
     * @param int|null $userId User ID (optional, uses auth user if null)
     * @return bool Success status
     * @throws Exception
     */
    public function execute(array $data, ?int $userId = null): bool
    {
        DB::beginTransaction();

        try {
            // Get user ID
            $userId = $userId ?? auth()->id();

            // Process and store custom data
            foreach ($data as $key => $value) {
                if (!empty($value)) {
                    $this->saveConfigValue(
                        "onboarding.custom_setup.{$key}",
                        $value,
                        $userId
                    );
                }
            }

            // Store completion metadata
            $this->saveConfigValue(
                'onboarding.custom_setup.completed_at',
                now()->toDateTimeString(),
                $userId
            );

            $this->saveConfigValue(
                'onboarding.custom_setup.completed_by',
                $userId,
                $userId
            );

            DB::commit();

            Log::info('Custom setup step completed successfully', [
                'user_id' => $userId,
                'data_keys' => array_keys($data),
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            $this->logError('Failed to execute custom setup step', $e, [
                'user_id' => $userId,
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Get default data for pre-filling the form
     *
     * @param int|null $userId User ID
     * @return array Default data
     */
    public function getDefaultData(?int $userId = null): array
    {
        $userId = $userId ?? auth()->id();

        // Retrieve previously saved data from core_config
        $customSetting1 = $this->coreConfigRepository->findWhere([
            'code' => 'onboarding.custom_setup.custom_setting_1',
        ])->first()?->value;

        $customSetting2 = $this->coreConfigRepository->findWhere([
            'code' => 'onboarding.custom_setup.custom_setting_2',
        ])->first()?->value;

        return [
            'custom_setting_1' => $customSetting1,
            'custom_setting_2' => $customSetting2,
        ];
    }

    /**
     * Check if step has been completed
     *
     * @param int|null $userId User ID
     * @return bool True if completed
     */
    public function hasBeenCompleted(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        $completedAt = $this->coreConfigRepository->findWhere([
            'code' => 'onboarding.custom_setup.completed_at',
        ])->first();

        return !is_null($completedAt);
    }

    /**
     * Save a config value
     *
     * @param string $code Config code
     * @param mixed $value Config value
     * @param int $userId User ID
     * @return void
     */
    protected function saveConfigValue(string $code, $value, int $userId): void
    {
        $existing = $this->coreConfigRepository->findWhere(['code' => $code])->first();

        if ($existing) {
            $this->coreConfigRepository->update(['value' => $value], $existing->id);
        } else {
            $this->coreConfigRepository->create([
                'code' => $code,
                'value' => $value,
            ]);
        }
    }
}
```

### Step 2: Add Configuration

Add step configuration to `config/onboarding.php`:

```php
'steps' => [
    // ... existing steps ...

    'custom_setup' => [
        'title' => 'Custom Setup',
        'short_title' => 'Custom',
        'description' => 'Configure custom settings for your organization',
        'icon' => 'settings',
        'order' => 6,  // Order in wizard sequence
        'estimated_minutes' => 5,
        'skippable' => true,
        'help_text' => 'Configure custom settings to tailor the CRM to your needs.',
        'help_tips' => [
            'Setting 1 determines how X works',
            'Setting 2 affects Y functionality',
            'You can change these later in settings',
        ],
        'fields' => [
            [
                'name' => 'custom_setting_1',
                'label' => 'Custom Setting 1',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Enter setting value',
                'help' => 'This setting controls X',
            ],
            [
                'name' => 'custom_setting_2',
                'label' => 'Custom Setting 2',
                'type' => 'select',
                'required' => false,
                'options' => [
                    'option1' => 'Option 1',
                    'option2' => 'Option 2',
                    'option3' => 'Option 3',
                ],
                'help' => 'Choose how Y should behave',
            ],
        ],
    ],
],

'validation' => [
    // ... existing validations ...

    'custom_setup' => [
        'custom_setting_1' => 'required|string|max:255',
        'custom_setting_2' => 'nullable|string|in:option1,option2,option3',
    ],
],
```

### Step 3: Register Step in OnboardingService

Update `app/Services/OnboardingService.php` to include your new step:

```php
protected array $steps = [
    'company_setup',
    'user_creation',
    'pipeline_config',
    'email_integration',
    'sample_data',
    'custom_setup',  // Add your step
];
```

### Step 4: Register Step in OnboardingController

Update `app/Http/Controllers/OnboardingController.php` to inject your step:

```php
use App\Services\Onboarding\Steps\CustomSetupStep;

public function __construct(
    protected OnboardingService $onboardingService,
    protected CompanySetupStep $companySetupStep,
    protected UserCreationStep $userCreationStep,
    protected PipelineConfigurationStep $pipelineConfigurationStep,
    protected EmailIntegrationStep $emailIntegrationStep,
    protected SampleDataImportStep $sampleDataImportStep,
    protected CustomSetupStep $customSetupStep,  // Add your step
) {
    $this->middleware('auth:user');

    // Map step IDs to implementation classes
    $this->stepImplementations = [
        'company_setup' => $this->companySetupStep,
        'user_creation' => $this->userCreationStep,
        'pipeline_config' => $this->pipelineConfigurationStep,
        'email_integration' => $this->emailIntegrationStep,
        'sample_data' => $this->sampleDataImportStep,
        'custom_setup' => $this->customSetupStep,  // Add your step
    ];
}
```

Do the same for `app/Http/Controllers/Api/OnboardingApiController.php`.

### Step 5: Create Blade View

Create `resources/views/onboarding/steps/custom_setup.blade.php`:

```blade
@extends('onboarding.steps.base')

@section('form-fields')
<div class="space-y-6">
    {{-- Custom Setting 1 --}}
    <div>
        <x-onboarding.field-help
            for="custom_setting_1"
            :label="__('Custom Setting 1')"
            :required="true"
            :tooltip="__('This setting controls X')"
        />
        <input
            type="text"
            id="custom_setting_1"
            name="custom_setting_1"
            value="{{ old('custom_setting_1', $defaultData['custom_setting_1'] ?? '') }}"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('custom_setting_1') border-red-500 @enderror"
            placeholder="{{ __('Enter setting value') }}"
            required
        >
        @error('custom_setting_1')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Custom Setting 2 --}}
    <div>
        <label for="custom_setting_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('Custom Setting 2') }}
        </label>
        <select
            id="custom_setting_2"
            name="custom_setting_2"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        >
            <option value="">{{ __('Select an option') }}</option>
            <option value="option1" {{ old('custom_setting_2', $defaultData['custom_setting_2'] ?? '') === 'option1' ? 'selected' : '' }}>
                {{ __('Option 1') }}
            </option>
            <option value="option2" {{ old('custom_setting_2', $defaultData['custom_setting_2'] ?? '') === 'option2' ? 'selected' : '' }}>
                {{ __('Option 2') }}
            </option>
            <option value="option3" {{ old('custom_setting_2', $defaultData['custom_setting_2'] ?? '') === 'option3' ? 'selected' : '' }}>
                {{ __('Option 3') }}
            </option>
        </select>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Choose how Y should behave') }}
        </p>
    </div>

    {{-- Info Panel --}}
    <x-onboarding.info-panel type="tip" :title="__('Pro Tip')">
        {{ __('These settings can be changed later in your account settings.') }}
    </x-onboarding.info-panel>
</div>
@endsection
```

### Step 6: Write Tests

Create `tests/Unit/Services/CustomSetupStepTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use App\Repositories\CoreConfigRepository;
use App\Services\Onboarding\Steps\CustomSetupStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomSetupStepTest extends TestCase
{
    use RefreshDatabase;

    protected CustomSetupStep $step;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\Models\User::factory()->create();
        $this->step = app(CustomSetupStep::class);
    }

    /** @test */
    public function it_has_correct_configuration()
    {
        $this->assertEquals('custom_setup', $this->step->getStepId());
        $this->assertEquals('Custom Setup', $this->step->getTitle());
        $this->assertTrue($this->step->canSkip());
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->step->validate([], $this->user->id);
    }

    /** @test */
    public function it_stores_custom_data()
    {
        $data = [
            'custom_setting_1' => 'Test Value',
            'custom_setting_2' => 'option1',
        ];

        $result = $this->step->execute($data, $this->user->id);

        $this->assertTrue($result);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.custom_setup.custom_setting_1',
            'value' => 'Test Value',
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.custom_setup.custom_setting_2',
            'value' => 'option1',
        ]);
    }

    /** @test */
    public function it_retrieves_default_data()
    {
        // Store some data first
        $coreConfigRepo = app(CoreConfigRepository::class);
        $coreConfigRepo->create([
            'code' => 'onboarding.custom_setup.custom_setting_1',
            'value' => 'Existing Value',
        ]);

        $defaultData = $this->step->getDefaultData($this->user->id);

        $this->assertEquals('Existing Value', $defaultData['custom_setting_1']);
    }

    /** @test */
    public function it_detects_completion_status()
    {
        $this->assertFalse($this->step->hasBeenCompleted($this->user->id));

        $data = [
            'custom_setting_1' => 'Test Value',
        ];
        $this->step->execute($data, $this->user->id);

        $this->assertTrue($this->step->hasBeenCompleted($this->user->id));
    }
}
```

### Step 7: Run Tests and Verify

```bash
# Run your new tests
php artisan test --filter CustomSetupStepTest

# Check the wizard includes your step
php artisan tinker
>>> app(\App\Services\OnboardingService::class)->getSteps();
# Should include 'custom_setup'

# Test in browser
php artisan serve
# Navigate to /onboarding and complete the wizard
```

---

## Customizing Existing Steps

### Modifying Step Behavior

You can extend existing steps to customize their behavior:

```php
<?php

namespace App\Services\Onboarding\Steps;

use App\Services\Onboarding\Steps\CompanySetupStep as BaseCompanySetupStep;

class CustomCompanySetupStep extends BaseCompanySetupStep
{
    /**
     * Override execute method to add custom logic
     */
    public function execute(array $data, ?int $userId = null): bool
    {
        // Add custom pre-processing
        $data = $this->preprocessData($data);

        // Call parent execute
        $result = parent::execute($data, $userId);

        // Add custom post-processing
        $this->postProcess($data, $userId);

        return $result;
    }

    /**
     * Custom preprocessing
     */
    protected function preprocessData(array $data): array
    {
        // Transform data before storage
        if (isset($data['company_name'])) {
            $data['company_name'] = strtoupper($data['company_name']);
        }

        return $data;
    }

    /**
     * Custom post-processing
     */
    protected function postProcess(array $data, int $userId): void
    {
        // Trigger custom events, notifications, etc.
        event(new \App\Events\CompanySetupCompleted($userId, $data));
    }
}
```

Then update `OnboardingController` to use your custom class:

```php
public function __construct(
    protected OnboardingService $onboardingService,
    protected CustomCompanySetupStep $companySetupStep,  // Use custom class
    // ... other steps
) {
    // ...
}
```

### Customizing Step Views

Create a custom view by copying an existing one:

```bash
cp resources/views/onboarding/steps/company_setup.blade.php \
   resources/views/onboarding/steps/company_setup_custom.blade.php
```

Then modify your step class to use the custom view:

```php
public function getViewPath(): string
{
    return 'onboarding.steps.company_setup_custom';
}
```

### Adding Custom Validation

Add custom validation rules in your step class:

```php
protected function getCustomValidationRules(): array
{
    return [
        'company_name' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                if (Company::where('name', $value)->exists()) {
                    $fail('A company with this name already exists.');
                }
            },
        ],
    ];
}

public function validate(array $data, ?int $userId = null): void
{
    $rules = array_merge(
        $this->validationRules,
        $this->getCustomValidationRules()
    );

    $validator = \Validator::make($data, $rules);

    if ($validator->fails()) {
        throw new \Illuminate\Validation\ValidationException($validator);
    }
}
```

---

## Extending the System

### Adding Lifecycle Hooks

The wizard supports lifecycle hooks for custom logic:

```php
class CustomSetupStep extends AbstractWizardStep
{
    /**
     * Called before step execution
     */
    public function onBeforeExecute(array $data, ?int $userId = null): void
    {
        // Custom logic before execution
        Log::info('About to execute custom setup', ['user_id' => $userId]);
    }

    /**
     * Called after successful completion
     */
    public function onComplete(?int $userId = null): void
    {
        parent::onComplete($userId);

        // Send notification
        $user = \App\Models\User::find($userId);
        $user->notify(new \App\Notifications\CustomSetupCompleted());

        // Trigger event
        event(new \App\Events\OnboardingStepCompleted('custom_setup', $userId));
    }

    /**
     * Called when step is skipped
     */
    public function onSkip(?int $userId = null): void
    {
        parent::onSkip($userId);

        // Apply default settings
        $this->applyDefaults($userId);
    }
}
```

### Creating Custom Events

Create events for wizard milestones:

```php
<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnboardingStepCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $stepId,
        public int $userId,
        public ?array $data = null
    ) {}
}
```

Listen to events:

```php
<?php

namespace App\Listeners;

use App\Events\OnboardingStepCompleted;
use Illuminate\Support\Facades\Log;

class TrackOnboardingProgress
{
    public function handle(OnboardingStepCompleted $event): void
    {
        // Track in analytics
        Log::info('Onboarding step completed', [
            'step' => $event->stepId,
            'user_id' => $event->userId,
        ]);

        // Update external systems
        // Send to analytics platform
        // Etc.
    }
}
```

Register in `EventServiceProvider`:

```php
protected $listen = [
    \App\Events\OnboardingStepCompleted::class => [
        \App\Listeners\TrackOnboardingProgress::class,
    ],
];
```

### Conditional Step Display

Show/hide steps based on conditions:

```php
// In OnboardingService
public function getAvailableSteps(?int $userId = null): array
{
    $userId = $userId ?? auth()->id();
    $user = \App\Models\User::find($userId);

    $allSteps = $this->getSteps();

    // Filter steps based on user role, subscription, etc.
    return array_filter($allSteps, function($step) use ($user) {
        // Only show email integration for admins
        if ($step === 'email_integration' && !$user->isAdmin()) {
            return false;
        }

        // Only show custom setup for enterprise users
        if ($step === 'custom_setup' && !$user->hasEnterpriseSubscription()) {
            return false;
        }

        return true;
    });
}
```

### Adding Middleware

Create custom middleware for onboarding requirements:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireOnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $progress = $user->onboardingProgress;

        if (!$progress || !$progress->is_completed) {
            return redirect()->route('onboarding.index')
                ->with('error', 'Please complete onboarding first.');
        }

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... existing middleware
    'onboarding.complete' => \App\Http\Middleware\RequireOnboardingComplete::class,
];
```

Use in routes:

```php
Route::middleware(['auth:user', 'onboarding.complete'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/contacts', [ContactController::class, 'index']);
    // ... other routes that require completed onboarding
});
```

---

## Configuration Reference

### Global Settings

```php
// config/onboarding.php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Wizard
    |--------------------------------------------------------------------------
    */
    'enabled' => env('ONBOARDING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Auto-trigger for New Users
    |--------------------------------------------------------------------------
    */
    'auto_trigger' => env('ONBOARDING_AUTO_TRIGGER', true),

    /*
    |--------------------------------------------------------------------------
    | Allow Skipping Steps
    |--------------------------------------------------------------------------
    */
    'allow_skip' => env('ONBOARDING_ALLOW_SKIP', true),

    /*
    |--------------------------------------------------------------------------
    | Allow Restart
    |--------------------------------------------------------------------------
    */
    'allow_restart' => env('ONBOARDING_ALLOW_RESTART', true),

    /*
    |--------------------------------------------------------------------------
    | Wizard Steps
    |--------------------------------------------------------------------------
    */
    'steps' => [
        'step_id' => [
            'title' => 'Step Title',
            'short_title' => 'Short',
            'description' => 'Step description',
            'icon' => 'icon-name',
            'order' => 1,
            'estimated_minutes' => 5,
            'skippable' => true,
            'help_text' => 'Help text for sidebar',
            'help_tips' => [
                'Tip 1',
                'Tip 2',
            ],
            'video_url' => env('ONBOARDING_STEP_VIDEO_URL'),
            'video_thumbnail' => env('ONBOARDING_STEP_VIDEO_THUMBNAIL'),
            'fields' => [
                [
                    'name' => 'field_name',
                    'label' => 'Field Label',
                    'type' => 'text|select|textarea|checkbox',
                    'required' => true,
                    'placeholder' => 'Placeholder text',
                    'help' => 'Field help text',
                    'options' => [],  // For select fields
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'step_id' => [
            'field_name' => 'required|string|max:255',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Completion Settings
    |--------------------------------------------------------------------------
    */
    'completion' => [
        'send_email' => env('ONBOARDING_SEND_COMPLETION_EMAIL', true),
        'redirect_to' => env('ONBOARDING_COMPLETION_REDIRECT', '/dashboard'),
        'completion_message' => 'Congratulations! Your CRM is set up.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Progress Settings
    |--------------------------------------------------------------------------
    */
    'progress' => [
        'show_progress_bar' => true,
        'show_step_numbers' => true,
        'show_time_estimates' => true,
        'auto_save' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'theme' => 'default',
        'show_help_sidebar' => true,
        'show_video_tutorials' => env('ONBOARDING_SHOW_VIDEOS', false),
        'enable_animations' => true,
    ],
];
```

### Environment Variables

```env
# .env

# Onboarding Configuration
ONBOARDING_ENABLED=true
ONBOARDING_AUTO_TRIGGER=true
ONBOARDING_ALLOW_SKIP=true
ONBOARDING_ALLOW_RESTART=true

# Completion
ONBOARDING_SEND_COMPLETION_EMAIL=true
ONBOARDING_COMPLETION_REDIRECT=/dashboard

# Videos (optional)
ONBOARDING_SHOW_VIDEOS=false
ONBOARDING_COMPANY_SETUP_VIDEO_URL=https://youtube.com/watch?v=...
ONBOARDING_COMPANY_SETUP_VIDEO_THUMBNAIL=https://img.youtube.com/...
```

---

## API Reference

### OnboardingService Methods

```php
// Get or create progress for user
$progress = $onboardingService->getOrCreateProgress($userId);

// Get existing progress
$progress = $onboardingService->getProgress($userId);

// Start onboarding
$progress = $onboardingService->startOnboarding($userId);

// Get all steps
$steps = $onboardingService->getSteps();

// Get step details
$details = $onboardingService->getStepDetails('company_setup');

// Navigate to next step
$nextStep = $onboardingService->navigateToNextStep($userId);

// Navigate to previous step
$prevStep = $onboardingService->navigateToPreviousStep($userId);

// Navigate to specific step
$onboardingService->navigateToStep('pipeline_config', $userId);

// Complete a step
$onboardingService->completeStep('company_setup', $data, $userId);

// Skip a step
$onboardingService->skipStep('email_integration', $userId);

// Complete onboarding
$onboardingService->completeOnboarding($userId);

// Reset onboarding
$onboardingService->resetOnboarding($userId);

// Get progress summary
$summary = $onboardingService->getProgressSummary($userId);

// Get completion statistics
$stats = $onboardingService->getCompletionStatistics();
```

### OnboardingProgress Model Methods

```php
// Get progress percentage
$percentage = $progress->getProgressPercentage();

// Get completed steps count
$count = $progress->getCompletedStepsCount();

// Get duration in minutes
$minutes = $progress->getDurationInMinutes();

// Check if step is completed
$isCompleted = $progress->isStepCompleted('company_setup');

// Check if in progress
$inProgress = $progress->isInProgress();

// Get next step
$nextStep = $progress->getNextStep();

// Get remaining steps
$remaining = $progress->getRemainingSteps();
```

### API Endpoints

```php
// Get progress (AJAX)
GET /api/onboarding/progress
Response: { success: true, data: { percentage: 60, completed: 3, ... } }

// Get statistics (Admin)
GET /api/onboarding/statistics
Response: { success: true, data: { completion_rate: 85, average_time: 12.5 } }

// Validate step data
POST /api/onboarding/step/{step}/validate
Body: { field1: 'value1', field2: 'value2' }
Response: { success: true, message: 'Validation passed' }

// Update step
POST /api/onboarding/step/{step}
Body: { field1: 'value1', field2: 'value2' }
Response: { success: true, data: { next_step: 'user_creation', progress: {...} } }

// Skip step
POST /api/onboarding/step/{step}/skip
Response: { success: true, data: { next_step: 'pipeline_config', progress: {...} } }

// Navigate next
POST /api/onboarding/next
Response: { success: true, data: { current_step: 'user_creation', progress: {...} } }

// Navigate previous
POST /api/onboarding/previous
Response: { success: true, data: { current_step: 'company_setup', progress: {...} } }

// Complete wizard
POST /api/onboarding/complete
Response: { success: true, data: { redirect_url: '/dashboard' } }

// Restart wizard
POST /api/onboarding/restart
Response: { success: true, data: { first_step: 'company_setup' } }
```

---

## Frontend Integration

### Using AJAX API

```javascript
// resources/js/onboarding.js

// Get current progress
async function getProgress() {
    const response = await fetch('/api/onboarding/progress');
    const data = await response.json();

    if (data.success) {
        updateProgressBar(data.data.percentage);
    }
}

// Validate step before submission
async function validateStep(step, formData) {
    const response = await fetch(`/api/onboarding/step/${step}/validate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    });

    return await response.json();
}

// Submit step via AJAX
async function submitStep(step, formData) {
    const response = await fetch(`/api/onboarding/step/${step}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    });

    const data = await response.json();

    if (data.success) {
        // Redirect to next step or show success
        window.location.href = `/onboarding/step/${data.data.next_step}`;
    } else {
        // Show errors
        displayErrors(data.errors);
    }
}

// Skip step
async function skipStep(step) {
    const response = await fetch(`/api/onboarding/step/${step}/skip`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });

    const data = await response.json();

    if (data.success) {
        window.location.href = `/onboarding/step/${data.data.next_step}`;
    }
}
```

### Using Blade Components

```blade
{{-- Progress Indicator --}}
<x-onboarding.progress-indicator
    :allSteps="$allSteps"
    :currentStep="$step"
    :progressSummary="$progressSummary"
/>

{{-- Tooltip Help --}}
<x-onboarding.tooltip
    :content="__('This field is used for...')"
    position="top"
/>

{{-- Info Panel --}}
<x-onboarding.info-panel type="info" :title="__('Important')">
    {{ __('Remember to save your changes.') }}
</x-onboarding.info-panel>

{{-- Field with Help --}}
<x-onboarding.field-help
    for="company_name"
    :label="__('Company Name')"
    :required="true"
    :tooltip="__('Your organization name')"
/>

{{-- Video Embed --}}
<x-onboarding.video-embed
    :url="$stepConfig['video_url']"
    :thumbnail="$stepConfig['video_thumbnail']"
    :title="__('How to set up your company')"
/>
```

### Custom JavaScript Interactions

```javascript
// Add custom validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('wizard-step-form');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Custom validation
        if (!validateCustomRules()) {
            return false;
        }

        // Show loading state
        showLoadingSpinner();

        // Submit form
        this.submit();
    });
});

// Auto-save progress
let autoSaveTimeout;
document.querySelectorAll('input, select, textarea').forEach(field => {
    field.addEventListener('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            autoSaveProgress();
        }, 2000);
    });
});

async function autoSaveProgress() {
    // Save current form state
    const formData = new FormData(document.getElementById('wizard-step-form'));
    const data = Object.fromEntries(formData);

    await fetch('/api/onboarding/auto-save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    });

    showNotification('Progress saved');
}
```

---

## Testing

### Unit Tests

```php
// tests/Unit/Services/OnboardingServiceTest.php

class OnboardingServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_progress_for_new_user()
    {
        $user = User::factory()->create();
        $service = app(OnboardingService::class);

        $progress = $service->getOrCreateProgress($user->id);

        $this->assertInstanceOf(OnboardingProgress::class, $progress);
        $this->assertEquals($user->id, $progress->user_id);
        $this->assertEquals('company_setup', $progress->current_step);
    }

    /** @test */
    public function it_completes_step_and_advances()
    {
        $user = User::factory()->create();
        $service = app(OnboardingService::class);
        $service->startOnboarding($user->id);

        $service->completeStep('company_setup', ['company_name' => 'Test'], $user->id);

        $progress = $service->getProgress($user->id);
        $this->assertTrue($progress->isStepCompleted('company_setup'));
        $this->assertEquals('user_creation', $progress->current_step);
    }

    /** @test */
    public function it_calculates_progress_percentage()
    {
        $user = User::factory()->create();
        $service = app(OnboardingService::class);
        $service->startOnboarding($user->id);

        // Complete 2 out of 5 steps
        $service->completeStep('company_setup', ['company_name' => 'Test'], $user->id);
        $service->completeStep('user_creation', ['name' => 'John', 'email' => 'john@test.com'], $user->id);

        $summary = $service->getProgressSummary($user->id);
        $this->assertEquals(40, $summary['percentage']);  // 2/5 = 40%
    }
}
```

### Feature Tests

```php
// tests/Feature/OnboardingWizardTest.php

class OnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_complete_full_wizard()
    {
        $user = User::factory()->create();

        // Start wizard
        $response = $this->actingAs($user)->get('/onboarding');
        $response->assertOk();

        // Complete company setup
        $response = $this->post('/onboarding/step/company_setup', [
            'company_name' => 'Acme Corp',
            'industry' => 'technology',
        ]);
        $response->assertRedirect('/onboarding/step/user_creation');

        // Skip user creation
        $response = $this->post('/onboarding/step/user_creation/skip');
        $response->assertRedirect('/onboarding/step/pipeline_config');

        // Continue through all steps...

        // Verify completion
        $progress = $user->onboardingProgress->fresh();
        $this->assertTrue($progress->is_completed);
    }

    /** @test */
    public function middleware_redirects_incomplete_users()
    {
        $user = User::factory()->create();
        OnboardingProgress::factory()->create([
            'user_id' => $user->id,
            'current_step' => 'company_setup',
            'is_completed' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/onboarding/step/company_setup');
    }
}
```

### Browser Tests (Dusk)

```php
// tests/Browser/OnboardingTest.php

class OnboardingTest extends DuskTestCase
{
    /** @test */
    public function user_can_complete_wizard_with_ui()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                    ->visit('/onboarding')
                    ->assertSee('Welcome to Laravel CRM')
                    ->click('@get-started-button')
                    ->waitForRoute('onboarding.step', ['company_setup'])
                    ->type('company_name', 'Test Company')
                    ->select('industry', 'technology')
                    ->click('@continue-button')
                    ->waitForRoute('onboarding.step', ['user_creation'])
                    ->assertSee('Add Team Member');
        });
    }
}
```

---

## Best Practices

### Code Organization

1. **One step per file**: Keep step implementations separate
2. **Use repositories**: Don't query models directly in steps
3. **Validate early**: Use validation rules from config
4. **Log important events**: Use Laravel's Log facade
5. **Handle errors gracefully**: Use try-catch and transactions

### Performance

1. **Lazy load relationships**: Only load what you need
2. **Cache configuration**: Config is cached in production
3. **Queue emails**: Use queued notifications
4. **Optimize queries**: Use eager loading for relationships
5. **Use indexes**: Database indexes on foreign keys

### Security

1. **Validate all input**: Never trust user input
2. **Use CSRF protection**: All forms must have @csrf
3. **Sanitize output**: Use Blade's {{ }} auto-escaping
4. **Check permissions**: Verify user can access wizard
5. **Protect sensitive data**: Don't log passwords or tokens

### Maintainability

1. **Follow PSR standards**: Use consistent code style
2. **Write comprehensive tests**: Aim for 80%+ coverage
3. **Document your code**: Use DocBlocks on all methods
4. **Keep config centralized**: Use config/onboarding.php
5. **Version your changes**: Commit often with clear messages

### User Experience

1. **Show progress clearly**: Use progress indicator
2. **Provide helpful tips**: Use contextual help
3. **Allow skipping**: Don't force non-critical steps
4. **Save progress automatically**: Don't lose user work
5. **Celebrate completion**: Show success message

---

## Troubleshooting

### Common Issues

#### Steps Not Appearing

**Problem**: New step doesn't show in wizard

**Solution**:
1. Check step is added to `OnboardingService::$steps` array
2. Verify configuration exists in `config/onboarding.php`
3. Clear config cache: `php artisan config:clear`
4. Check step order in configuration

#### Validation Errors

**Problem**: Validation always fails

**Solution**:
1. Check validation rules in `config/onboarding.php`
2. Verify field names match between view and config
3. Inspect Laravel log for validation errors
4. Use `dd($request->all())` to debug form data

#### Progress Not Saving

**Problem**: User progress resets

**Solution**:
1. Check database migration ran: `php artisan migrate:status`
2. Verify OnboardingProgress model exists
3. Check user relationship in User model
4. Inspect database for progress records

#### Middleware Loops

**Problem**: Infinite redirect loop

**Solution**:
1. Verify onboarding routes are excluded in middleware
2. Check `$except` array in `RedirectIfOnboardingIncomplete`
3. Ensure completion sets `is_completed = true`
4. Clear all caches: `php artisan optimize:clear`

#### Step Implementation Not Found

**Problem**: "Class not found" error

**Solution**:
1. Check class namespace matches file location
2. Run `composer dump-autoload`
3. Verify class is injected in controller constructor
4. Check class extends `AbstractWizardStep`

### Debug Mode

Enable detailed logging for troubleshooting:

```php
// In OnboardingService

protected function debugLog(string $message, array $context = []): void
{
    if (config('app.debug')) {
        Log::debug('[Onboarding] ' . $message, $context);
    }
}

public function completeStep(string $step, array $data, $userId = null): bool
{
    $this->debugLog('Completing step', ['step' => $step, 'user_id' => $userId]);

    // ... rest of method
}
```

### Testing Locally

```bash
# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Create test user
php artisan tinker
>>> User::factory()->create(['email' => 'test@example.com']);

# Test onboarding
# Visit: http://localhost:8000/onboarding
```

---

## Examples

### Example: Custom API Integration Step

Complete example integrating with a third-party API:

```php
<?php

namespace App\Services\Onboarding\Steps;

use App\Contracts\WizardStepContract;
use App\Repositories\CoreConfigRepository;
use App\Services\ExternalApiService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiIntegrationStep extends AbstractWizardStep
{
    protected CoreConfigRepository $coreConfigRepository;
    protected ExternalApiService $apiService;

    public function __construct(
        CoreConfigRepository $coreConfigRepository,
        ExternalApiService $apiService
    ) {
        $this->coreConfigRepository = $coreConfigRepository;
        $this->apiService = $apiService;

        $config = config('onboarding.steps.api_integration', []);

        $this->stepId = 'api_integration';
        $this->title = $config['title'] ?? 'API Integration';
        $this->description = $config['description'] ?? 'Connect your external services';
        $this->icon = $config['icon'] ?? 'link';
        $this->estimatedMinutes = $config['estimated_minutes'] ?? 5;
        $this->skippable = $config['skippable'] ?? true;
        $this->helpText = $config['help_text'] ?? '';
        $this->helpTips = $config['help_tips'] ?? [];
        $this->validationRules = config('onboarding.validation.api_integration', []);
    }

    public function execute(array $data, ?int $userId = null): bool
    {
        DB::beginTransaction();

        try {
            $userId = $userId ?? auth()->id();

            // Test API connection
            $connectionTest = $this->apiService->testConnection(
                $data['api_key'],
                $data['api_secret']
            );

            if (!$connectionTest['success']) {
                throw new Exception('API connection failed: ' . $connectionTest['error']);
            }

            // Store API credentials securely
            $this->saveConfigValue('onboarding.api_integration.api_key',
                encrypt($data['api_key']), $userId);
            $this->saveConfigValue('onboarding.api_integration.api_secret',
                encrypt($data['api_secret']), $userId);
            $this->saveConfigValue('onboarding.api_integration.api_endpoint',
                $data['api_endpoint'], $userId);

            // Sync initial data
            if ($data['sync_now'] ?? false) {
                $this->apiService->syncInitialData($userId);
            }

            // Store completion metadata
            $this->saveConfigValue('onboarding.api_integration.completed_at',
                now()->toDateTimeString(), $userId);
            $this->saveConfigValue('onboarding.api_integration.completed_by',
                $userId, $userId);

            DB::commit();

            Log::info('API integration step completed successfully', [
                'user_id' => $userId,
                'endpoint' => $data['api_endpoint'],
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            $this->logError('Failed to execute API integration step', $e, [
                'user_id' => $userId,
            ]);

            throw $e;
        }
    }

    public function getDefaultData(?int $userId = null): array
    {
        $userId = $userId ?? auth()->id();

        $endpoint = $this->coreConfigRepository->findWhere([
            'code' => 'onboarding.api_integration.api_endpoint',
        ])->first()?->value;

        // Don't return encrypted credentials for security
        return [
            'api_endpoint' => $endpoint,
            'sync_now' => false,
        ];
    }

    public function hasBeenCompleted(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        $completedAt = $this->coreConfigRepository->findWhere([
            'code' => 'onboarding.api_integration.completed_at',
        ])->first();

        return !is_null($completedAt);
    }

    protected function saveConfigValue(string $code, $value, int $userId): void
    {
        $existing = $this->coreConfigRepository->findWhere(['code' => $code])->first();

        if ($existing) {
            $this->coreConfigRepository->update(['value' => $value], $existing->id);
        } else {
            $this->coreConfigRepository->create([
                'code' => $code,
                'value' => $value,
            ]);
        }
    }
}
```

### Example: Multi-Page Step

For complex steps requiring multiple pages:

```php
class AdvancedPipelineStep extends AbstractWizardStep
{
    protected int $currentPage = 1;
    protected int $totalPages = 3;

    public function render(?int $userId = null, array $extraData = []): string
    {
        $page = request()->get('page', 1);
        $viewPath = "onboarding.steps.advanced_pipeline_page_{$page}";

        return view($viewPath, array_merge([
            'step' => $this,
            'defaultData' => $this->getDefaultData($userId),
            'currentPage' => $page,
            'totalPages' => $this->totalPages,
        ], $extraData))->render();
    }

    public function execute(array $data, ?int $userId = null): bool
    {
        // Combine data from all pages
        $allData = array_merge(
            $this->getStoredPageData(1, $userId),
            $this->getStoredPageData(2, $userId),
            $data  // Current page data
        );

        // Process complete data
        return parent::execute($allData, $userId);
    }

    protected function getStoredPageData(int $page, ?int $userId): array
    {
        // Retrieve temporarily stored data from previous pages
        return session("onboarding.advanced_pipeline.page_{$page}", []);
    }
}
```

---

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Onboarding User Guide](./onboarding-wizard-user-guide.md)
- [API Documentation](../api/onboarding.md)
- [GitHub Repository](https://github.com/your-repo/laravel-crm)

---

## Support

For questions or issues:

- **Documentation**: Read this guide thoroughly
- **Issues**: [GitHub Issues](https://github.com/your-repo/laravel-crm/issues)
- **Community**: [Discord Server](https://discord.gg/laravel-crm)
- **Email**: support@laravel-crm.com

---

## Contributing

We welcome contributions! To contribute:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests
5. Submit a pull request

Please follow the coding standards and include tests with your PR.

---

## Changelog

### Version 1.0.0 (2026-01-12)

- Initial release of onboarding wizard
- 5 core steps implemented
- Progress tracking and analytics
- AJAX API support
- Comprehensive documentation

---

**Last Updated**: 2026-01-12
**Version**: 1.0.0
**Author**: Laravel CRM Development Team
