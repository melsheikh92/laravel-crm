# Extension Developer Guide

A comprehensive guide for creating, testing, and publishing extensions for Laravel CRM.

## Table of Contents

1. [Introduction](#introduction)
2. [Prerequisites](#prerequisites)
3. [Getting Started](#getting-started)
4. [Package Structure](#package-structure)
5. [Creating Your Extension](#creating-your-extension)
6. [Extension Metadata](#extension-metadata)
7. [Service Providers](#service-providers)
8. [Database Migrations](#database-migrations)
9. [Routes and Controllers](#routes-and-controllers)
10. [Views and Assets](#views-and-assets)
11. [Configuration](#configuration)
12. [Events and Listeners](#events-and-listeners)
13. [Testing Your Extension](#testing-your-extension)
14. [Packaging](#packaging)
15. [Submission Process](#submission-process)
16. [Security Review](#security-review)
17. [Best Practices](#best-practices)
18. [Troubleshooting](#troubleshooting)
19. [Examples](#examples)

---

## Introduction

The Laravel CRM Extension Marketplace allows developers to extend the platform's functionality by creating plugins, themes, and integrations. This guide will walk you through the entire process of creating and publishing a high-quality extension.

### Extension Types

- **Plugins**: Add new features or functionality (e.g., email campaigns, advanced reporting)
- **Themes**: Customize the visual appearance of the CRM
- **Integrations**: Connect Laravel CRM with third-party services (e.g., payment gateways, marketing tools)

---

## Prerequisites

Before you begin developing an extension, ensure you have:

### Required Knowledge
- **PHP**: Intermediate to advanced level
- **Laravel Framework**: Understanding of Laravel 10+ features
- **Composer**: Package management
- **Git**: Version control basics
- **MySQL/PostgreSQL**: Database fundamentals

### Software Requirements
- **PHP**: >= 8.1
- **Composer**: >= 2.0
- **Laravel**: >= 10.0
- **Laravel CRM**: >= 1.0 (check compatibility)
- **Node.js & NPM**: >= 16.x (for asset compilation)

### Recommended Tools
- **IDE**: PhpStorm, VS Code with PHP extensions
- **Git Client**: GitHub Desktop, SourceTree, or CLI
- **Database Client**: TablePlus, phpMyAdmin, or similar
- **API Testing**: Postman, Insomnia
- **Package Development**: Laravel Valet or Homestead

---

## Getting Started

### 1. Set Up Development Environment

```bash
# Clone Laravel CRM for local development
git clone https://github.com/hamzahllc/laravel-crm.git
cd laravel-crm

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Serve the application
php artisan serve
```

### 2. Create a Developer Account

1. Register at the [Developer Portal](https://laravel-crm.com/developer)
2. Complete your developer profile
3. Set up payment information (for paid extensions)
4. Generate API credentials for testing

### 3. Understand the Marketplace Structure

Laravel CRM uses a modular package structure located in:

```
packages/
└── [Vendor]/
    └── [Package]/
        ├── src/
        ├── composer.json
        └── README.md
```

---

## Package Structure

### Recommended Directory Structure

```
packages/YourVendor/YourPackage/
├── src/
│   ├── Config/
│   │   ├── menu.php           # Menu items
│   │   ├── acl.php            # Access control
│   │   └── settings.php       # Configuration
│   ├── Database/
│   │   ├── Migrations/
│   │   ├── Seeders/
│   │   └── Factories/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/          # Form requests
│   │   └── Middleware/
│   ├── Models/
│   ├── Repositories/
│   ├── Contracts/             # Interfaces
│   ├── Providers/
│   │   ├── PackageServiceProvider.php
│   │   └── EventServiceProvider.php
│   ├── Resources/
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   ├── js/
│   │   │   └── images/
│   │   ├── lang/
│   │   │   └── en/
│   │   └── views/
│   ├── Routes/
│   │   ├── web.php
│   │   └── api.php
│   └── Helpers/
├── tests/
│   ├── Unit/
│   └── Feature/
├── docs/
│   └── README.md
├── composer.json
├── package.json
├── webpack.mix.js
├── phpunit.xml
├── LICENSE
└── README.md
```

### Core Files Explained

#### composer.json
Defines package dependencies and autoloading:

```json
{
    "name": "yourvendor/yourpackage",
    "description": "Brief description of your extension",
    "type": "library",
    "license": "MIT",
    "version": "1.0.0",
    "authors": [
        {
            "name": "Your Name",
            "email": "you@example.com"
        }
    ],
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "hamzahllc/laravel-core": "^1.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "orchestra/testbench": "^8.0"
    },
    "autoload": {
        "psr-4": {
            "YourVendor\\YourPackage\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "YourVendor\\YourPackage\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "YourVendor\\YourPackage\\Providers\\PackageServiceProvider"
            ],
            "aliases": {}
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

#### README.md
Provide clear documentation:

```markdown
# Your Package Name

Brief description of what your extension does.

## Features
- Feature 1
- Feature 2

## Installation
Instructions for installing your package

## Configuration
How to configure the extension

## Usage
Examples of how to use the extension

## Support
Contact information and support links
```

---

## Creating Your Extension

### Step 1: Initialize Your Package

```bash
# Create package directory
mkdir -p packages/YourVendor/YourPackage/src
cd packages/YourVendor/YourPackage

# Initialize composer
composer init

# Create basic structure
mkdir -p src/{Config,Database/Migrations,Http/Controllers,Models,Providers,Resources/views,Routes}
```

### Step 2: Create the Service Provider

**src/Providers/PackageServiceProvider.php**

```php
<?php

namespace YourVendor\YourPackage\Providers;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/settings.php',
            'yourpackage'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'yourpackage');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'yourpackage');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/settings.php' => config_path('yourpackage.php'),
        ], 'config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/yourpackage'),
        ], 'views');

        // Publish assets
        $this->publishes([
            __DIR__ . '/../Resources/assets' => public_path('vendor/yourpackage'),
        ], 'assets');
    }
}
```

### Step 3: Define Routes

**src/Routes/web.php**

```php
<?php

use Illuminate\Support\Facades\Route;
use YourVendor\YourPackage\Http\Controllers\YourController;

Route::middleware(['web', 'auth'])
    ->prefix('yourpackage')
    ->name('yourpackage.')
    ->group(function () {
        Route::get('/', [YourController::class, 'index'])->name('index');
        Route::get('/create', [YourController::class, 'create'])->name('create');
        Route::post('/', [YourController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [YourController::class, 'edit'])->name('edit');
        Route::put('/{id}', [YourController::class, 'update'])->name('update');
        Route::delete('/{id}', [YourController::class, 'destroy'])->name('destroy');
    });
```

### Step 4: Create Models

**src/Models/YourModel.php**

```php
<?php

namespace YourVendor\YourPackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use YourVendor\YourPackage\Contracts\YourModel as YourModelContract;

class YourModel extends Model implements YourModelContract
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
```

### Step 5: Create Controllers

**src/Http/Controllers/YourController.php**

```php
<?php

namespace YourVendor\YourPackage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YourVendor\YourPackage\Repositories\YourRepository;

class YourController extends Controller
{
    public function __construct(
        protected YourRepository $repository
    ) {}

    public function index()
    {
        $items = $this->repository->all();

        return view('yourpackage::index', compact('items'));
    }

    public function create()
    {
        return view('yourpackage::create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $item = $this->repository->create($validated);

        return redirect()
            ->route('yourpackage.index')
            ->with('success', 'Item created successfully');
    }
}
```

---

## Extension Metadata

Each extension must include metadata for the marketplace. Create a `extension.json` file:

**extension.json**

```json
{
    "name": "Your Package Name",
    "slug": "your-package-name",
    "version": "1.0.0",
    "type": "plugin",
    "description": "Brief description of your extension",
    "long_description": "Detailed description with features and benefits...",
    "author": {
        "name": "Your Name",
        "email": "you@example.com",
        "website": "https://yourwebsite.com"
    },
    "requirements": {
        "php": "^8.1",
        "laravel": "^10.0",
        "laravel-crm": "^1.0"
    },
    "tags": ["crm", "integration", "productivity"],
    "price": 49.99,
    "license": "MIT",
    "documentation_url": "https://docs.yourextension.com",
    "demo_url": "https://demo.yourextension.com",
    "support_email": "support@yourextension.com",
    "repository_url": "https://github.com/yourvendor/yourpackage",
    "screenshots": [
        "screenshot1.png",
        "screenshot2.png"
    ],
    "dependencies": {
        "required": [],
        "optional": []
    },
    "permissions": [
        "view_data",
        "create_records",
        "manage_settings"
    ]
}
```

---

## Service Providers

### Registering Services

Use the service provider to register bindings, singletons, and configurations:

```php
public function register(): void
{
    // Register a singleton
    $this->app->singleton(YourService::class, function ($app) {
        return new YourService($app->make(SomeDependency::class));
    });

    // Bind interface to implementation
    $this->app->bind(
        YourModelContract::class,
        YourModel::class
    );

    // Register repository
    $this->app->bind(
        YourRepository::class,
        function ($app) {
            return new YourRepository($app->make(YourModel::class));
        }
    );
}
```

### Booting Services

```php
public function boot(): void
{
    // Register event listeners
    Event::listen(UserCreated::class, SendWelcomeEmail::class);

    // Register middleware
    $router = $this->app['router'];
    $router->aliasMiddleware('your.middleware', YourMiddleware::class);

    // Register commands (if any)
    if ($this->app->runningInConsole()) {
        $this->commands([
            YourCommand::class,
        ]);
    }

    // Register policies
    Gate::policy(YourModel::class, YourPolicy::class);
}
```

---

## Database Migrations

### Creating Migrations

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('your_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->json('settings')->nullable();

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('your_table');
    }
};
```

### Best Practices for Migrations

1. **Use descriptive names**: `2024_01_11_create_your_table.php`
2. **Always include down() method**: For rollback support
3. **Use proper foreign keys**: With cascade options
4. **Index frequently queried columns**: For performance
5. **Use appropriate column types**: To save space and ensure data integrity

---

## Routes and Controllers

### RESTful Controllers

Follow Laravel's RESTful conventions:

```php
Route::resource('items', ItemController::class);

// Generates:
// GET    /items           -> index
// GET    /items/create    -> create
// POST   /items           -> store
// GET    /items/{id}      -> show
// GET    /items/{id}/edit -> edit
// PUT    /items/{id}      -> update
// DELETE /items/{id}      -> destroy
```

### API Routes

**src/Routes/api.php**

```php
<?php

use Illuminate\Support\Facades\Route;
use YourVendor\YourPackage\Http\Controllers\Api\YourApiController;

Route::middleware(['api', 'auth:sanctum'])
    ->prefix('api/yourpackage')
    ->name('api.yourpackage.')
    ->group(function () {
        Route::get('/items', [YourApiController::class, 'index']);
        Route::post('/items', [YourApiController::class, 'store']);
        Route::get('/items/{id}', [YourApiController::class, 'show']);
        Route::put('/items/{id}', [YourApiController::class, 'update']);
        Route::delete('/items/{id}', [YourApiController::class, 'destroy']);
    });
```

---

## Views and Assets

### Blade Templates

**src/Resources/views/index.blade.php**

```blade
@extends('admin::layouts.master')

@section('page_title')
    {{ __('yourpackage::app.title') }}
@stop

@section('content-wrapper')
    <div class="content full-page">
        <div class="page-header">
            <h1 class="page-title">
                {{ __('yourpackage::app.title') }}
            </h1>

            <div class="page-action">
                <a href="{{ route('yourpackage.create') }}" class="btn btn-primary">
                    {{ __('yourpackage::app.create') }}
                </a>
            </div>
        </div>

        <div class="page-content">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Your content here -->
        </div>
    </div>
@stop
```

### Compiling Assets

**webpack.mix.js**

```javascript
const mix = require('laravel-mix');

mix.js('src/Resources/assets/js/app.js', 'public/js')
   .sass('src/Resources/assets/sass/app.scss', 'public/css')
   .setPublicPath('public')
   .version();
```

**package.json**

```json
{
  "private": true,
  "scripts": {
    "dev": "npm run development",
    "development": "mix",
    "watch": "mix watch",
    "hot": "mix watch --hot",
    "prod": "npm run production",
    "production": "mix --production"
  },
  "devDependencies": {
    "laravel-mix": "^6.0.0"
  }
}
```

---

## Configuration

### Creating Configuration Files

**src/Config/settings.php**

```php
<?php

return [
    'enabled' => env('YOURPACKAGE_ENABLED', true),

    'api' => [
        'key' => env('YOURPACKAGE_API_KEY'),
        'secret' => env('YOURPACKAGE_API_SECRET'),
        'endpoint' => env('YOURPACKAGE_ENDPOINT', 'https://api.example.com'),
    ],

    'cache' => [
        'enabled' => env('YOURPACKAGE_CACHE_ENABLED', true),
        'ttl' => env('YOURPACKAGE_CACHE_TTL', 3600),
    ],

    'features' => [
        'feature_a' => true,
        'feature_b' => false,
    ],
];
```

### Accessing Configuration

```php
// In your code
$apiKey = config('yourpackage.api.key');
$cacheEnabled = config('yourpackage.cache.enabled', false);
```

---

## Events and Listeners

### Defining Events

**src/Events/ItemCreated.php**

```php
<?php

namespace YourVendor\YourPackage\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use YourVendor\YourPackage\Models\YourModel;

class ItemCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public YourModel $item
    ) {}
}
```

### Creating Listeners

**src/Listeners/SendItemCreatedNotification.php**

```php
<?php

namespace YourVendor\YourPackage\Listeners;

use YourVendor\YourPackage\Events\ItemCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendItemCreatedNotification implements ShouldQueue
{
    public function handle(ItemCreated $event): void
    {
        // Send notification
        $event->item->user->notify(
            new ItemCreatedNotification($event->item)
        );
    }
}
```

### Registering Events

**src/Providers/EventServiceProvider.php**

```php
<?php

namespace YourVendor\YourPackage\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use YourVendor\YourPackage\Events\ItemCreated;
use YourVendor\YourPackage\Listeners\SendItemCreatedNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ItemCreated::class => [
            SendItemCreatedNotification::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
```

---

## Testing Your Extension

### Setting Up Tests

**phpunit.xml**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./src</directory>
        </include>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

### Writing Unit Tests

**tests/Unit/YourModelTest.php**

```php
<?php

namespace YourVendor\YourPackage\Tests\Unit;

use PHPUnit\Framework\TestCase;
use YourVendor\YourPackage\Models\YourModel;

class YourModelTest extends TestCase
{
    /** @test */
    public function it_can_create_a_model()
    {
        $model = new YourModel([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $this->assertEquals('Test Item', $model->name);
        $this->assertEquals('Test Description', $model->description);
    }

    /** @test */
    public function it_casts_status_to_boolean()
    {
        $model = new YourModel(['status' => 1]);

        $this->assertIsBool($model->status);
        $this->assertTrue($model->status);
    }
}
```

### Writing Feature Tests

**tests/Feature/YourControllerTest.php**

```php
<?php

namespace YourVendor\YourPackage\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use YourVendor\YourPackage\Models\YourModel;

class YourControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_index_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('yourpackage.index'));

        $response->assertStatus(200);
        $response->assertViewIs('yourpackage::index');
    }

    /** @test */
    public function user_can_create_item()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test Item',
            'description' => 'Test Description',
        ];

        $response = $this->actingAs($user)
            ->post(route('yourpackage.store'), $data);

        $response->assertRedirect(route('yourpackage.index'));
        $this->assertDatabaseHas('your_table', $data);
    }

    /** @test */
    public function validation_fails_with_missing_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('yourpackage.store'), [
                'description' => 'Test Description',
            ]);

        $response->assertSessionHasErrors('name');
    }
}
```

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite=Unit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage

# Run specific test
./vendor/bin/phpunit tests/Feature/YourControllerTest.php
```

---

## Packaging

### Step 1: Prepare for Distribution

1. **Clean up development files**:
```bash
# Remove dev dependencies
composer install --no-dev --optimize-autoloader

# Build production assets
npm run production

# Clear caches
php artisan cache:clear
php artisan config:clear
```

2. **Create a changelog**: Document all changes in `CHANGELOG.md`

```markdown
# Changelog

## [1.0.0] - 2024-01-11

### Added
- Initial release
- Feature A
- Feature B

### Fixed
- Bug fix 1

### Changed
- Improvement 1
```

3. **Update documentation**: Ensure README.md is complete and accurate

### Step 2: Create Distribution Package

```bash
# Create a zip archive
zip -r yourpackage-v1.0.0.zip \
    src/ \
    composer.json \
    package.json \
    README.md \
    LICENSE \
    CHANGELOG.md \
    extension.json \
    -x "*.git*" "node_modules/*" "vendor/*" "tests/*"
```

### Step 3: Generate Checksum

```bash
# Generate SHA-256 checksum
sha256sum yourpackage-v1.0.0.zip > yourpackage-v1.0.0.zip.sha256
```

### Step 4: Test Installation

1. Install in a fresh Laravel CRM instance
2. Verify all features work correctly
3. Check for conflicts with other extensions
4. Test on different PHP/Laravel versions

---

## Submission Process

### 1. Prepare Submission Materials

Required materials:
- [ ] Extension package (ZIP file)
- [ ] extension.json metadata file
- [ ] README.md with installation instructions
- [ ] CHANGELOG.md
- [ ] LICENSE file
- [ ] Screenshots (minimum 2, maximum 5)
- [ ] Logo/icon (256x256px PNG)
- [ ] Documentation URL
- [ ] Demo URL (optional but recommended)

### 2. Submit Through Developer Portal

1. **Login** to the [Developer Portal](https://laravel-crm.com/developer)

2. **Create New Extension**:
   - Navigate to "My Extensions" → "Submit New Extension"
   - Fill in the extension details
   - Upload the package ZIP file
   - Upload screenshots and logo
   - Set pricing (free or paid)

3. **Version Information**:
   - Version number (semantic versioning: MAJOR.MINOR.PATCH)
   - Changelog for this version
   - Compatibility information (PHP, Laravel, CRM versions)
   - Dependencies (if any)

4. **Review Requirements**:
   - Check all required fields are completed
   - Verify package uploads successfully
   - Confirm pricing and licensing

5. **Submit for Review**:
   - Click "Submit for Review"
   - Extension status changes to "Pending"
   - You'll receive a confirmation email

### 3. Review Process Timeline

- **Automated checks**: Immediate (< 5 minutes)
  - Package structure validation
  - Malware scanning
  - Dependency checking
  - Code quality analysis

- **Security review**: 1-3 business days
  - Manual code review
  - Vulnerability assessment
  - Permission audit

- **Functionality review**: 2-5 business days
  - Installation testing
  - Feature verification
  - Documentation review

- **Total estimated time**: 3-7 business days

### 4. Review Criteria

Your extension will be evaluated on:

1. **Code Quality** (30%)
   - Follows Laravel best practices
   - PSR-12 coding standards
   - Proper error handling
   - No deprecated functions

2. **Security** (30%)
   - No security vulnerabilities
   - Proper input validation
   - SQL injection prevention
   - XSS protection

3. **Functionality** (20%)
   - Works as described
   - No critical bugs
   - Proper error messages
   - Good user experience

4. **Documentation** (10%)
   - Clear installation instructions
   - Feature documentation
   - API documentation (if applicable)
   - Troubleshooting guide

5. **Compatibility** (10%)
   - Compatible with stated versions
   - No conflicts with core features
   - Follows CRM conventions

### 5. After Submission

You can track your submission status:
- **Pending**: Awaiting review
- **In Review**: Currently being reviewed
- **Approved**: Published to marketplace
- **Rejected**: Review failed (you'll receive detailed feedback)
- **Revision Required**: Changes needed before approval

### 6. Handling Rejection

If your extension is rejected:
1. Review the detailed feedback provided
2. Make necessary changes
3. Re-submit the updated version
4. Priority review for re-submissions (1-2 business days)

### 7. Publishing Updates

To publish an update:
1. Navigate to "My Extensions" → Select Extension
2. Click "Add New Version"
3. Upload updated package
4. Fill in changelog
5. Submit for review

Note: Updates go through the same review process but typically faster (1-3 business days).

---

## Security Review

### Security Checklist

Before submitting, ensure your extension passes these security checks:

#### Input Validation
- [ ] All user inputs are validated
- [ ] Form requests use validation rules
- [ ] File uploads are validated (type, size, extension)
- [ ] API inputs are sanitized

#### SQL Injection Prevention
- [ ] Use Eloquent ORM or Query Builder
- [ ] Never use raw queries with user input
- [ ] Use parameter binding for raw queries
- [ ] Validate all database inputs

#### XSS Protection
- [ ] Blade templates use `{{ }}` for output
- [ ] HTML is escaped unless explicitly needed
- [ ] User-generated content is sanitized
- [ ] Content Security Policy headers set

#### Authentication & Authorization
- [ ] Routes are protected with middleware
- [ ] Permissions are checked before actions
- [ ] API endpoints use authentication
- [ ] Session management is secure

#### File Security
- [ ] File uploads are stored securely
- [ ] File types are restricted
- [ ] Files are scanned for malware
- [ ] Private files are not web-accessible

#### Data Protection
- [ ] Sensitive data is encrypted
- [ ] Passwords use bcrypt/argon2
- [ ] API keys are stored in environment variables
- [ ] Personal data follows GDPR guidelines

#### Dependency Security
- [ ] Dependencies are up-to-date
- [ ] No known vulnerabilities in packages
- [ ] Composer lock file is included
- [ ] Only trusted packages are used

### Common Security Issues to Avoid

1. **Mass Assignment Vulnerabilities**
```php
// BAD
User::create($request->all());

// GOOD
User::create($request->validated());
```

2. **SQL Injection**
```php
// BAD
DB::select("SELECT * FROM users WHERE id = " . $request->id);

// GOOD
DB::select("SELECT * FROM users WHERE id = ?", [$request->id]);
```

3. **XSS Attacks**
```blade
<!-- BAD -->
{!! $userInput !!}

<!-- GOOD -->
{{ $userInput }}
```

4. **Unprotected Routes**
```php
// BAD
Route::get('/admin', [AdminController::class, 'index']);

// GOOD
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

---

## Best Practices

### Code Organization

1. **Follow SOLID Principles**
   - Single Responsibility
   - Open/Closed
   - Liskov Substitution
   - Interface Segregation
   - Dependency Inversion

2. **Use Repository Pattern**
```php
// Repository
class YourRepository
{
    public function __construct(
        protected YourModel $model
    ) {}

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }
}

// Controller
class YourController extends Controller
{
    public function __construct(
        protected YourRepository $repository
    ) {}

    public function index()
    {
        return $this->repository->all();
    }
}
```

3. **Use Form Requests for Validation**
```php
class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The item name is required.',
            'status.in' => 'Invalid status value.',
        ];
    }
}
```

### Performance Optimization

1. **Eager Loading**
```php
// BAD - N+1 query problem
$items = YourModel::all();
foreach ($items as $item) {
    echo $item->user->name;
}

// GOOD - Eager loading
$items = YourModel::with('user')->get();
foreach ($items as $item) {
    echo $item->user->name;
}
```

2. **Query Optimization**
```php
// Use select to limit columns
$items = YourModel::select('id', 'name', 'status')->get();

// Use chunk for large datasets
YourModel::chunk(100, function ($items) {
    foreach ($items as $item) {
        // Process item
    }
});

// Use pagination
$items = YourModel::paginate(15);
```

3. **Caching**
```php
use Illuminate\Support\Facades\Cache;

// Cache expensive operations
$value = Cache::remember('key', $seconds, function () {
    return DB::table('users')->get();
});

// Cache tags for grouped invalidation
Cache::tags(['users', 'items'])->put('key', $value, $seconds);
Cache::tags(['users'])->flush();
```

### Error Handling

```php
use Illuminate\Support\Facades\Log;

try {
    // Risky operation
    $result = $this->performOperation();
} catch (SpecificException $e) {
    // Handle specific exception
    Log::error('Operation failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);

    return response()->json([
        'message' => 'Operation failed. Please try again.',
    ], 500);
} catch (\Exception $e) {
    // Handle general exception
    Log::error('Unexpected error', [
        'error' => $e->getMessage(),
    ]);

    return response()->json([
        'message' => 'An unexpected error occurred.',
    ], 500);
}
```

### Internationalization

```php
// In your code
$message = __('yourpackage::app.messages.success');

// In language file: src/Resources/lang/en/app.php
return [
    'messages' => [
        'success' => 'Operation completed successfully',
        'error' => 'An error occurred',
    ],
];

// In Blade views
{{ __('yourpackage::app.title') }}
```

### Documentation

1. **PHPDoc Comments**
```php
/**
 * Create a new item.
 *
 * @param array $data The item data
 * @return YourModel The created item
 * @throws \Exception If creation fails
 */
public function create(array $data): YourModel
{
    return $this->model->create($data);
}
```

2. **README Structure**
   - Clear description
   - Installation steps
   - Configuration guide
   - Usage examples
   - API documentation
   - Troubleshooting
   - Contributing guidelines
   - License information

---

## Troubleshooting

### Common Issues

#### 1. Extension Not Loading

**Problem**: Extension doesn't appear after installation

**Solutions**:
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild autoload
composer dump-autoload

# Re-register providers
php artisan package:discover --anew
```

#### 2. Migrations Not Running

**Problem**: Database tables not created

**Solutions**:
```bash
# Check migration status
php artisan migrate:status

# Run migrations
php artisan migrate

# Force migration in production
php artisan migrate --force

# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

#### 3. Views Not Found

**Problem**: Blade views return 404

**Solutions**:
```php
// Verify view namespace is registered
$this->loadViewsFrom(__DIR__ . '/../Resources/views', 'yourpackage');

// Check view path
view('yourpackage::index'); // Correct
view('index'); // Incorrect

// Clear view cache
php artisan view:clear
```

#### 4. Routes Not Working

**Problem**: Routes return 404

**Solutions**:
```php
// Ensure routes are loaded in service provider
$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

// Clear route cache
php artisan route:clear

// List all routes to verify
php artisan route:list | grep yourpackage
```

#### 5. Assets Not Loading

**Problem**: CSS/JS files not found

**Solutions**:
```bash
# Publish assets
php artisan vendor:publish --tag=assets --force

# Verify public path
ls -la public/vendor/yourpackage

# Compile assets
npm run production
```

### Debugging Tips

1. **Enable Debug Mode**
```env
APP_DEBUG=true
APP_ENV=local
```

2. **Check Logs**
```bash
tail -f storage/logs/laravel.log
```

3. **Use Laravel Debugbar**
```bash
composer require barryvdh/laravel-debugbar --dev
```

4. **Dump and Die**
```php
dd($variable); // Dump and die
dump($variable); // Dump and continue
```

5. **Query Logging**
```php
DB::enableQueryLog();
// ... your queries ...
dd(DB::getQueryLog());
```

---

## Examples

### Example 1: Simple Plugin

A basic plugin that adds a custom dashboard widget.

**File Structure**:
```
packages/YourVendor/DashboardWidget/
├── src/
│   ├── Providers/
│   │   └── DashboardWidgetServiceProvider.php
│   ├── Resources/
│   │   └── views/
│   │       └── widget.blade.php
│   └── Http/
│       └── Controllers/
│           └── DashboardWidgetController.php
├── composer.json
└── README.md
```

**DashboardWidgetController.php**:
```php
<?php

namespace YourVendor\DashboardWidget\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\User;

class DashboardWidgetController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
        ];

        return view('dashboardwidget::widget', compact('stats'));
    }
}
```

### Example 2: Theme Extension

A theme that customizes the CRM appearance.

**File Structure**:
```
packages/YourVendor/CustomTheme/
├── src/
│   ├── Resources/
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   └── theme.css
│   │   │   └── images/
│   │   │       └── logo.png
│   │   └── views/
│   │       └── layouts/
│   │           └── master.blade.php
│   └── Providers/
│       └── ThemeServiceProvider.php
└── composer.json
```

### Example 3: Integration Plugin

An integration with a third-party email service.

**File Structure**:
```
packages/YourVendor/EmailIntegration/
├── src/
│   ├── Services/
│   │   └── EmailService.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── EmailController.php
│   ├── Models/
│   │   └── EmailCampaign.php
│   ├── Config/
│   │   └── email.php
│   └── Providers/
│       └── EmailIntegrationServiceProvider.php
└── composer.json
```

**EmailService.php**:
```php
<?php

namespace YourVendor\EmailIntegration\Services;

use GuzzleHttp\Client;

class EmailService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('email.api.endpoint'),
            'headers' => [
                'Authorization' => 'Bearer ' . config('email.api.key'),
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function sendCampaign(array $data): array
    {
        $response = $this->client->post('/campaigns', [
            'json' => $data,
        ]);

        return json_decode($response->getBody(), true);
    }
}
```

---

## Additional Resources

### Official Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Package Development](https://laravel.com/docs/packages)
- [Laravel CRM API Reference](https://laravel-crm.com/docs/api)

### Community
- [Laravel CRM Forums](https://forum.laravel-crm.com)
- [Developer Discord](https://discord.gg/laravel-crm-dev)
- [GitHub Discussions](https://github.com/hamzahllc/laravel-crm/discussions)

### Tools
- [Laravel Package Boilerplate](https://github.com/spatie/laravel-package-tools)
- [PHPStan](https://phpstan.org/) - Static Analysis
- [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) - Code Style
- [Larastan](https://github.com/nunomaduro/larastan) - Laravel Static Analysis

### Support
- **Technical Support**: developer-support@laravel-crm.com
- **Documentation Issues**: docs@laravel-crm.com
- **Security Issues**: security@laravel-crm.com

---

## License

This developer guide is provided under the MIT License. Extensions you create may use any OSI-approved license.

---

## Changelog

### Version 1.0.0 (2024-01-11)
- Initial developer guide release
- Complete package structure documentation
- Testing guidelines
- Submission process documentation
- Security best practices
- Code examples and troubleshooting

---

**Happy Coding! 🚀**

We're excited to see what you build for the Laravel CRM ecosystem. If you have questions or need help, don't hesitate to reach out to our developer community.
