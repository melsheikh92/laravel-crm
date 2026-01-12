# Onboarding Middleware Documentation

## Overview

The `RedirectIfOnboardingIncomplete` middleware automatically detects new installations and redirects authenticated users to the onboarding wizard if they haven't completed the setup process.

## How It Works

The middleware is registered in the `web` middleware group in `app/Http/Kernel.php` and runs on every web request. It follows this logic:

1. **Skip if onboarding is disabled**: Checks `config('onboarding.enabled')` - if false, allows request to continue
2. **Skip if auto-trigger is disabled**: Checks `config('onboarding.auto_trigger')` - if false, allows request to continue
3. **Skip for guest users**: Unauthenticated users are not redirected
4. **Skip for excluded routes**: Certain routes are excluded from the redirect (see below)
5. **Skip for API requests**: JSON/API requests are not redirected
6. **Check onboarding progress**:
   - If user has no `OnboardingProgress` record, one is created and user is redirected to wizard
   - If user has incomplete onboarding (`is_completed = false`), they are redirected to continue
   - If user has completed onboarding, request continues normally

## Excluded Routes

The following route patterns are excluded from onboarding redirect:

- `onboarding/*` - All onboarding wizard routes
- `api/*` - All API routes
- `login` - Login page
- `logout` - Logout action
- `register` - Registration page
- `password/*` - Password reset routes
- `consent/*` - Consent/GDPR routes
- `landing` - Landing page
- `demo-request` - Demo request page

## Redirect Behavior

### New Users (No Progress)
Redirected to: `route('onboarding.index')` (Welcome page)
Message: "Welcome! Let's get your CRM set up."

### Resuming Users (Has Current Step)
Redirected to: `route('onboarding.step', $currentStep)`
Message: "Please complete the setup wizard to continue."

### Intended URL Storage
When redirecting, the middleware stores the original URL in the session (`onboarding.intended_url`) so users can be redirected back after completing onboarding. This does not apply to dashboard or home routes.

## Configuration

The middleware respects the following configuration options from `config/onboarding.php`:

```php
'enabled' => env('ONBOARDING_ENABLED', true),
'auto_trigger' => env('ONBOARDING_AUTO_TRIGGER', true),
```

## Disabling the Middleware

### Globally
Set in `.env`:
```
ONBOARDING_ENABLED=false
```

### Auto-trigger Only
Set in `.env`:
```
ONBOARDING_AUTO_TRIGGER=false
```

This allows the onboarding wizard to be manually accessed but not automatically triggered.

### Remove from Middleware Stack
Remove from `app/Http/Kernel.php`:
```php
// Remove this line from the 'web' middleware group
\App\Http\Middleware\RedirectIfOnboardingIncomplete::class,
```

## Manual Verification Checklist

To verify the middleware works correctly:

1. **Test New User Flow**:
   - Create a new user account
   - Log in
   - Verify redirect to `/onboarding`
   - Verify `onboarding_progress` record is created

2. **Test Incomplete Onboarding**:
   - Create user with incomplete onboarding (set `current_step`)
   - Log in
   - Verify redirect to `/onboarding/step/{current_step}`

3. **Test Completed Onboarding**:
   - Create user with `is_completed = true` in onboarding_progress
   - Log in
   - Verify access to dashboard without redirect

4. **Test Excluded Routes**:
   - Without completing onboarding, access `/login`, `/api/*`, `/onboarding/*`
   - Verify no redirects occur

5. **Test Configuration**:
   - Set `ONBOARDING_ENABLED=false`
   - Log in as new user
   - Verify no redirect to onboarding
   - Set `ONBOARDING_AUTO_TRIGGER=false`
   - Verify no redirect to onboarding

6. **Test Intended URL**:
   - As incomplete user, access `/compliance/dashboard`
   - Verify redirect to onboarding
   - Complete onboarding
   - Verify redirect back to `/compliance/dashboard`

## Testing

Comprehensive tests are available in `tests/Feature/Middleware/RedirectIfOnboardingIncompleteTest.php`.

Run tests with:
```bash
php artisan test --filter=RedirectIfOnboardingIncompleteTest
```

## Logging

The middleware logs important events:

- **New user detected**: When creating initial onboarding progress
- **Redirecting to resume**: When user has incomplete onboarding
- **Redirecting to start**: When new user is redirected to wizard

All logs use the `info` or `debug` level and include user ID and relevant context.

## Integration with Onboarding System

The middleware works seamlessly with:

- `OnboardingProgress` model - Tracks user progress
- `OnboardingService` - Manages wizard logic
- `OnboardingController` - Handles wizard routes
- Onboarding configuration in `config/onboarding.php`

## Troubleshooting

**Issue**: Users stuck in redirect loop
- **Solution**: Check that onboarding routes are in the `$except` array

**Issue**: Middleware not redirecting
- **Solution**: Verify it's registered in `web` middleware group in Kernel.php

**Issue**: API routes being redirected
- **Solution**: Ensure API routes use `api` middleware group, not `web`

## Related Files

- Middleware: `app/Http/Middleware/RedirectIfOnboardingIncomplete.php`
- Kernel: `app/Http/Kernel.php`
- Model: `app/Models/OnboardingProgress.php`
- Service: `app/Services/OnboardingService.php`
- Controller: `app/Http/Controllers/OnboardingController.php`
- Config: `config/onboarding.php`
- Tests: `tests/Feature/Middleware/RedirectIfOnboardingIncompleteTest.php`
