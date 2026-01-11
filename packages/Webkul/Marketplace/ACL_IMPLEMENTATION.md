# Marketplace ACL Implementation

This document describes the Access Control List (ACL) implementation for the Marketplace package.

## Overview

The Marketplace package implements a comprehensive ACL system to control access to marketplace features across three distinct user groups:
- **Regular Users**: Can browse, install, and review extensions
- **Developers**: Can create, manage, and publish extensions
- **Administrators**: Can manage all marketplace content, approve submissions, and handle revenue

## Architecture

### 1. ACL Configuration

All permissions are defined in `src/Config/acl.php`. This file is automatically merged with the application's ACL configuration by the service provider.

```php
// Example permission structure
[
    'key' => 'marketplace.install',
    'name' => 'marketplace::app.acl.install',
    'route' => [
        'marketplace.install.extension',
        'marketplace.install.uninstall',
    ],
    'sort' => 3,
]
```

### 2. Middleware Protection

The package uses Laravel's middleware to protect routes:

#### Admin Routes
- Protected by: `['web', 'admin_locale', 'user']`
- The `user` middleware is an alias for `BouncerMiddleware`
- Automatically checks ACL permissions based on route names

#### Developer Routes
- Protected by: `['web', 'user', 'developer']`
- `user` middleware: Checks ACL permissions
- `developer` middleware: Verifies developer status (approved)

#### Marketplace Routes
- Public routes: `['web']` - No authentication required
- Authenticated routes: `['web', 'user']` - Requires login and permissions

### 3. Authorization Layers

The marketplace implements three layers of authorization:

#### Layer 1: Route-Level (Middleware)
The `BouncerMiddleware` automatically checks if the authenticated user has permission to access a route based on the route name and ACL configuration.

```php
// In BouncerMiddleware
public function checkIfAuthorized()
{
    $roles = acl()->getRoles();
    if (isset($roles[Route::currentRouteName()])) {
        bouncer()->allow($roles[Route::currentRouteName()]);
    }
}
```

#### Layer 2: Role-Based (Developer Middleware)
The `DeveloperMiddleware` ensures users have developer status before accessing developer portal routes.

```php
public function handle(Request $request, Closure $next)
{
    if (!$user->isDeveloper()) {
        // Redirect based on status (pending/rejected/suspended)
    }
    return $next($request);
}
```

#### Layer 3: Ownership-Based (Controller)
Controllers verify resource ownership using inline checks or the `AuthorizesOwnership` trait.

```php
// Example: Developer can only edit their own extensions
if ($extension->author_id !== Auth::id()) {
    throw new AccessDeniedHttpException(
        trans('marketplace::app.errors.unauthorized-extension-access')
    );
}
```

## Permission Structure

### Marketplace User Permissions

| Permission Key | Routes Covered | Description |
|---------------|----------------|-------------|
| `marketplace` | `marketplace.browse.index` | Root marketplace access |
| `marketplace.browse` | Browse, search, filter routes | Browse extensions catalog |
| `marketplace.extensions` | Extension detail routes | View extension details |
| `marketplace.install` | Install, uninstall, manage | Install and manage extensions |
| `marketplace.my-extensions` | My extensions routes | Manage installed extensions |
| `marketplace.reviews` | Review CRUD routes | Create and manage reviews |
| `marketplace.payments` | Payment routes | Handle extension purchases |

### Developer Permissions

| Permission Key | Routes Covered | Description |
|---------------|----------------|-------------|
| `developer` | Dashboard routes | Access developer portal |
| `developer.extensions` | List, view extensions | View own extensions |
| `developer.extensions.create` | Create extension | Create new extensions |
| `developer.extensions.edit` | Edit, upload files | Modify own extensions |
| `developer.extensions.delete` | Delete extension | Delete own extensions |
| `developer.versions` | List, view versions | View extension versions |
| `developer.versions.create` | Create version | Create new versions |
| `developer.versions.edit` | Edit version | Modify versions |
| `developer.versions.delete` | Delete version | Delete versions |
| `developer.versions.download` | Download package | Download version files |
| `developer.submissions` | List, view submissions | View own submissions |
| `developer.submissions.create` | Submit for review | Submit versions |
| `developer.submissions.cancel` | Cancel submission | Cancel pending submissions |
| `developer.earnings` | Earnings, transactions | View earnings data |
| `developer.earnings.payout` | Request payout | Request payout |

### Admin Permissions

| Permission Key | Routes Covered | Description |
|---------------|----------------|-------------|
| `settings.marketplace` | Marketplace management root | Access marketplace admin |
| `settings.marketplace.extensions` | List, view all extensions | View all extensions |
| `settings.marketplace.extensions.create` | Create extension | Create extensions |
| `settings.marketplace.extensions.edit` | Edit, enable, disable, feature | Modify any extension |
| `settings.marketplace.extensions.delete` | Delete, mass operations | Delete extensions |
| `settings.marketplace.submissions` | List, view submissions | View all submissions |
| `settings.marketplace.submissions.review` | Approve, reject | Review submissions |
| `settings.marketplace.submissions.security` | Security scan | Run security scans |
| `settings.marketplace.categories` | List, view categories | View categories |
| `settings.marketplace.categories.create` | Create category | Create categories |
| `settings.marketplace.categories.edit` | Edit, reorder | Modify categories |
| `settings.marketplace.categories.delete` | Delete categories | Delete categories |
| `settings.marketplace.developer-applications` | List, view applications | View developer applications |
| `settings.marketplace.developer-applications.manage` | Approve, reject, suspend | Manage developers |
| `settings.marketplace.revenue` | Revenue dashboard | View revenue data |
| `settings.marketplace.revenue.transactions` | View transactions | View all transactions |
| `settings.marketplace.revenue.transactions.refund` | Process refunds | Issue refunds |
| `settings.marketplace.revenue.reports` | All reports | View reports |
| `settings.marketplace.revenue.settings` | Update settings | Configure revenue settings |

## Ownership Validation

The `AuthorizesOwnership` trait provides helper methods for ownership validation:

```php
use Webkul\Marketplace\Http\Controllers\Concerns\AuthorizesOwnership;

class ExtensionController extends Controller
{
    use AuthorizesOwnership;

    public function update(int $id)
    {
        $extension = $this->extensionRepository->findOrFail($id);

        // Throws AccessDeniedHttpException if not owner
        $this->authorizeExtensionOwnership($extension);

        // Or check without throwing
        if (!$this->ownsExtension($extension)) {
            // Custom handling
        }
    }
}
```

## Helper Functions

### bouncer()
Global helper that returns the Bouncer instance for permission checking.

```php
// Check if user has permission
if (bouncer()->hasPermission('marketplace.install')) {
    // User can install extensions
}

// Enforce permission (throws 401 if unauthorized)
bouncer()->allow('marketplace.install');
```

### acl()
Global helper that returns ACL configuration.

```php
// Get all role-route mappings
$roles = acl()->getRoles();
```

## Public Routes

The following routes are intentionally public (no authentication required):
- All `marketplace.browse.*` routes (browsing catalog)
- All `marketplace.extension.*` routes (viewing extension details)
- `marketplace.webhooks.payment` (payment gateway callbacks)

## Best Practices

1. **Always use middleware protection**: Don't rely solely on controller checks
2. **Verify ownership**: Always check resource ownership in controllers for user-generated content
3. **Use appropriate permissions**: Group related actions under the same permission key
4. **Provide clear error messages**: Use translation keys for unauthorized access messages
5. **Test permission scenarios**: Verify both allowed and denied access cases

## Testing ACL

To test ACL permissions:

1. Create users with different roles
2. Assign specific permissions to each role
3. Test route access for each permission level
4. Verify ownership checks work correctly
5. Test mass operations permissions

## Troubleshooting

### Route not protected
1. Check that route name matches ACL configuration
2. Verify middleware is applied to route
3. Ensure ACL config is merged in service provider

### Unauthorized access allowed
1. Check if user role has 'all' permission type (bypasses checks)
2. Verify ownership validation in controller
3. Check middleware order (user middleware must run)

### Authorized access denied
1. Verify route name exactly matches ACL config
2. Check user has required permission in database
3. Ensure bouncer service is registered
