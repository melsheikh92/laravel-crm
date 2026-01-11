# Developer Role/Permission System Integration

This document explains how to integrate the Developer Role/Permission system into your Laravel CRM application.

## Overview

The Developer Role/Permission system allows users to register as developers, enabling them to:
- Submit extensions to the marketplace
- Manage their extensions and versions
- Track earnings from paid extensions
- View analytics and statistics

## Database Migration

The system adds the following fields to the `users` table:

- `is_developer` (boolean): Flag to indicate if user is registered as a developer
- `developer_status` (enum): Current status (pending, approved, rejected, suspended)
- `developer_bio` (text): Developer biography
- `developer_company` (string): Company name
- `developer_website` (string): Website URL
- `developer_support_email` (string): Support email address
- `developer_social_links` (json): Social media profile links
- `developer_registered_at` (timestamp): Registration timestamp
- `developer_approved_at` (timestamp): Approval timestamp

Run the migration:
```bash
php artisan migrate
```

## User Model Integration

### Step 1: Add the Trait to User Model

Add the `HasDeveloperProfile` trait to your User model:

```php
<?php

namespace Webkul\User\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Webkul\Marketplace\Models\Traits\HasDeveloperProfile;
use Webkul\User\Contracts\User as UserContract;

class User extends Authenticatable implements UserContract
{
    use HasApiTokens, Notifiable, HasDeveloperProfile;

    // ... rest of your User model
}
```

### Step 2: Add Fillable Fields (Optional)

If you want these fields to be mass-assignable, add them to the `$fillable` array:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    // ... other fields
    'is_developer',
    'developer_status',
    'developer_bio',
    'developer_company',
    'developer_website',
    'developer_support_email',
    'developer_social_links',
];
```

### Step 3: Add Casts (Optional)

Add JSON casting for social links:

```php
protected $casts = [
    // ... other casts
    'developer_social_links' => 'array',
];
```

## Available Methods

Once the trait is added, you can use the following methods:

### Checking Developer Status

```php
$user = Auth::user();

// Check if user is an approved developer
if ($user->isDeveloper()) {
    // User is an approved developer
}

// Check if user has pending application
if ($user->hasPendingDeveloperApplication()) {
    // Application is under review
}

// Check if application was rejected
if ($user->isDeveloperRejected()) {
    // Application was rejected
}

// Check if developer account is suspended
if ($user->isDeveloperSuspended()) {
    // Account is suspended
}
```

### Managing Developer Profile

```php
// Register as developer
$user->registerAsDeveloper([
    'bio' => 'Experienced Laravel developer...',
    'company' => 'Acme Inc.',
    'website' => 'https://example.com',
    'support_email' => 'support@example.com',
    'social_links' => [
        'github' => 'https://github.com/username',
        'twitter' => 'https://twitter.com/username',
        'linkedin' => 'https://linkedin.com/in/username',
    ],
]);

// Update developer profile
$user->updateDeveloperProfile([
    'bio' => 'Updated bio...',
    // ... other fields
]);

// Approve developer (admin action)
$user->approveDeveloper();

// Reject developer (admin action)
$user->rejectDeveloper();

// Suspend developer (admin action)
$user->suspendDeveloper();
```

### Accessing Developer Data

```php
// Get all extensions by this developer
$extensions = $user->developedExtensions;

// Get developer statistics
$downloadsCount = $user->getDeveloperDownloadsCount();
$averageRating = $user->getDeveloperAverageRating();
$extensionsCount = $user->getDeveloperExtensionsCount();
$approvedCount = $user->getDeveloperApprovedExtensionsCount();
```

### Query Scopes

```php
use Webkul\User\Models\User;

// Get all developers
$developers = User::developers()->get();

// Get approved developers only
$approvedDevelopers = User::approvedDevelopers()->get();

// Get pending developer applications
$pendingApplications = User::pendingDevelopers()->get();
```

## Routes

### User Routes (Marketplace)

- `GET /marketplace/developer-registration` - Show registration form
- `POST /marketplace/developer-registration` - Submit registration
- `GET /marketplace/developer-registration/edit` - Edit developer profile
- `PUT /marketplace/developer-registration` - Update developer profile
- `GET /marketplace/developer-registration/status` - Get registration status (API)

### Admin Routes

- `GET /admin/marketplace/developer-applications` - List all applications
- `GET /admin/marketplace/developer-applications/{id}` - View application details
- `POST /admin/marketplace/developer-applications/{id}/approve` - Approve application
- `POST /admin/marketplace/developer-applications/{id}/reject` - Reject application
- `POST /admin/marketplace/developer-applications/{id}/suspend` - Suspend developer
- `GET /admin/marketplace/developer-applications/pending/count` - Get pending count (API)

## Middleware

The `developer` middleware protects developer portal routes and ensures only approved developers can access them:

```php
Route::middleware(['developer'])->group(function () {
    // Developer-only routes
});
```

The middleware is automatically applied to all developer portal routes.

## Permissions (ACL)

The following permissions are available in the ACL system:

### User Permissions
- `marketplace.browse` - Browse marketplace
- `marketplace.install` - Install extensions
- `marketplace.review` - Write reviews

### Developer Permissions
- `developer` - Access developer portal
- `developer.extensions` - Manage extensions
- `developer.extensions.create` - Create extensions
- `developer.extensions.edit` - Edit extensions
- `developer.extensions.delete` - Delete extensions
- `developer.versions` - Manage versions
- `developer.submissions` - Submit for review
- `developer.earnings` - View earnings

### Admin Permissions
- `settings.marketplace` - Marketplace management
- `settings.marketplace.extensions` - Manage all extensions
- `settings.marketplace.submissions` - Review submissions
- `settings.marketplace.categories` - Manage categories
- `settings.marketplace.revenue` - View revenue

## Workflow

1. **User Registration**: User applies to become a developer via registration form
2. **Status: Pending**: Application awaits admin review
3. **Admin Review**: Admin approves or rejects the application
4. **Status: Approved**: Developer gains access to developer portal
5. **Access Control**: Developer middleware ensures only approved developers can access developer features
6. **Suspension**: Admin can suspend developer accounts if needed

## Notifications (Future Enhancement)

Consider implementing notifications for:
- Developer application submitted
- Developer application approved
- Developer application rejected
- Developer account suspended

## Example: Developer Dashboard Access

```php
use Illuminate\Support\Facades\Auth;

class DeveloperDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Middleware already checked developer status
        // Safe to access developer features here

        $extensions = $user->developedExtensions()
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_downloads' => $user->getDeveloperDownloadsCount(),
            'average_rating' => $user->getDeveloperAverageRating(),
            'total_extensions' => $user->getDeveloperExtensionsCount(),
        ];

        return view('marketplace::developer.dashboard', compact('extensions', 'stats'));
    }
}
```

## Testing

Test developer functionality:

```php
use Tests\TestCase;
use Webkul\User\Models\User;

class DeveloperTest extends TestCase
{
    public function test_user_can_register_as_developer()
    {
        $user = User::factory()->create();

        $result = $user->registerAsDeveloper([
            'bio' => 'Test bio with more than fifty characters to pass validation rules',
            'support_email' => 'support@test.com',
        ]);

        $this->assertTrue($result);
        $this->assertTrue($user->is_developer);
        $this->assertEquals('pending', $user->developer_status);
    }

    public function test_admin_can_approve_developer()
    {
        $user = User::factory()->create();
        $user->registerAsDeveloper(['bio' => 'Test bio...', 'support_email' => 'test@test.com']);

        $user->approveDeveloper();

        $this->assertTrue($user->isDeveloper());
        $this->assertEquals('approved', $user->developer_status);
    }
}
```

## Security Considerations

1. **Status Validation**: Always check developer status before allowing access to developer features
2. **Middleware Protection**: Use the `developer` middleware on all developer routes
3. **Admin Actions**: Only admins should be able to approve, reject, or suspend developers
4. **Email Verification**: Consider requiring email verification before allowing developer registration
5. **Rate Limiting**: Implement rate limiting on developer registration to prevent abuse

## Troubleshooting

### Trait not found error
Make sure you've added the `use` statement at the top of your User model:
```php
use Webkul\Marketplace\Models\Traits\HasDeveloperProfile;
```

### Methods not available
Ensure the trait is added to the class definition:
```php
class User extends Authenticatable implements UserContract
{
    use HasApiTokens, Notifiable, HasDeveloperProfile;
    // ...
}
```

### Middleware not working
Check that the middleware is registered in the service provider and applied to the routes.

## Support

For issues or questions, please contact the development team.
