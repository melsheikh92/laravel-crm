# Extension Marketplace - Comprehensive Setup Guide

Complete installation and configuration guide for the Laravel CRM Extension Marketplace feature.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Installation](#installation)
3. [Database Setup](#database-setup)
4. [Storage Configuration](#storage-configuration)
5. [Payment Gateway Configuration](#payment-gateway-configuration)
6. [Scheduler Configuration](#scheduler-configuration)
7. [Permissions & ACL](#permissions--acl)
8. [Environment Variables](#environment-variables)
9. [Testing the Setup](#testing-the-setup)
10. [Production Deployment](#production-deployment)
11. [Troubleshooting](#troubleshooting)
12. [Advanced Configuration](#advanced-configuration)

---

## Prerequisites

Before setting up the Extension Marketplace, ensure you have:

### System Requirements
- **PHP**: >= 8.1
- **MySQL**: >= 5.7 or **PostgreSQL**: >= 9.6
- **Composer**: >= 2.0
- **Node.js & NPM**: >= 16.x
- **Laravel**: >= 10.0
- **Laravel CRM**: Installed and configured

### Required PHP Extensions
```bash
# Check if required extensions are installed
php -m | grep -E "pdo|mbstring|tokenizer|xml|ctype|json|bcmath|openssl|fileinfo"
```

If any are missing, install them:
```bash
# Ubuntu/Debian
sudo apt-get install php8.1-{pdo,mbstring,tokenizer,xml,ctype,json,bcmath,openssl,fileinfo}

# macOS (Homebrew)
brew install php@8.1
```

### Third-Party Accounts
- **Stripe Account**: For payment processing ([Sign up](https://stripe.com))
- **Mail Service**: For notifications (Mailgun, SendGrid, etc.)

---

## Installation

### Step 1: Verify Package Installation

The marketplace package should be installed in `packages/Webkul/Marketplace`. Verify it's present:

```bash
ls -la packages/Webkul/Marketplace
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install NPM dependencies (if needed)
npm install
```

### Step 3: Publish Package Assets

```bash
# Publish configuration files
php artisan vendor:publish --provider="Webkul\Marketplace\Providers\MarketplaceServiceProvider" --tag=config

# Publish migrations (if not already present)
php artisan vendor:publish --provider="Webkul\Marketplace\Providers\MarketplaceServiceProvider" --tag=migrations

# Publish views (optional - only if you want to customize)
php artisan vendor:publish --provider="Webkul\Marketplace\Providers\MarketplaceServiceProvider" --tag=views

# Publish translations (optional)
php artisan vendor:publish --provider="Webkul\Marketplace\Providers\MarketplaceServiceProvider" --tag=lang
```

---

## Database Setup

### Step 1: Run Migrations

```bash
# Run all marketplace migrations
php artisan migrate

# If you need to rollback
php artisan migrate:rollback --step=1
```

### Step 2: Verify Tables Created

```bash
# MySQL
mysql -u your_username -p your_database -e "SHOW TABLES LIKE 'marketplace_%';"

# Expected tables:
# - marketplace_extensions
# - marketplace_extension_versions
# - marketplace_extension_installations
# - marketplace_extension_reviews
# - marketplace_extension_transactions
# - marketplace_categories
# - marketplace_tags
# - marketplace_extension_tag
```

### Step 3: Seed Initial Data (Optional)

```bash
# Seed categories and sample data
php artisan db:seed --class=Webkul\\Marketplace\\Database\\Seeders\\MarketplaceDatabaseSeeder

# Or seed specific seeders
php artisan db:seed --class=Webkul\\Marketplace\\Database\\Seeders\\CategorySeeder
php artisan db:seed --class=Webkul\\Marketplace\\Database\\Seeders\\ExtensionSeeder
```

---

## Storage Configuration

### Step 1: Create Storage Directories

The marketplace requires specific storage directories for extension packages and backups:

```bash
# Create marketplace storage directories
mkdir -p storage/app/marketplace/packages
mkdir -p storage/app/marketplace/backups
mkdir -p storage/app/marketplace/screenshots
mkdir -p storage/app/marketplace/logos

# Set proper permissions
chmod -R 775 storage/app/marketplace
chown -R www-data:www-data storage/app/marketplace  # Linux
# or
chown -R _www:_www storage/app/marketplace  # macOS
```

### Step 2: Configure Filesystem

The marketplace uses Laravel's filesystem configuration. By default, it uses local storage.

**Option A: Local Storage (Default)**

No additional configuration needed. Files are stored in `storage/app/marketplace/`.

**Option B: Amazon S3 (Recommended for Production)**

1. Install the AWS SDK:
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

2. Add S3 credentials to `.env`:
```env
AWS_ACCESS_KEY_ID=your_access_key_id
AWS_SECRET_ACCESS_KEY=your_secret_access_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-marketplace-bucket
AWS_USE_PATH_STYLE_ENDPOINT=false
```

3. Update `config/filesystems.php` to add a marketplace disk:
```php
'disks' => [
    // ... existing disks ...

    'marketplace' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => false,
    ],
],
```

4. Update `.env` to use S3:
```env
FILESYSTEM_DISK=marketplace
```

**Option C: DigitalOcean Spaces**

1. Install the AWS SDK (Spaces is S3-compatible):
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

2. Add to `.env`:
```env
AWS_ACCESS_KEY_ID=your_spaces_key
AWS_SECRET_ACCESS_KEY=your_spaces_secret
AWS_DEFAULT_REGION=nyc3
AWS_BUCKET=your-space-name
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Step 3: Configure Upload Limits

Update `php.ini` to allow larger file uploads:

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
```

Or in `.htaccess` (Apache):
```apache
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
php_value memory_limit 256M
```

### Step 4: Create Storage Symlink

```bash
# Create public storage symlink
php artisan storage:link
```

---

## Payment Gateway Configuration

### Stripe Setup

The marketplace uses Stripe for payment processing. Follow these steps to configure it:

#### Step 1: Create Stripe Account

1. Visit [https://stripe.com](https://stripe.com) and sign up
2. Complete your business profile
3. Verify your email and identity

#### Step 2: Get API Keys

1. Log in to Stripe Dashboard
2. Navigate to **Developers** → **API keys**
3. You'll see two types of keys:
   - **Publishable key**: Used in frontend (safe to expose)
   - **Secret key**: Used in backend (keep private)

**Test Mode vs Live Mode:**
- Use **Test mode** for development
- Use **Live mode** for production

#### Step 3: Configure Stripe in Laravel

Add your Stripe credentials to `.env`:

```env
# Stripe Configuration
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_PUBLISHABLE_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
STRIPE_CURRENCY=usd
STRIPE_TEST_MODE=true
```

**For Production:**
```env
STRIPE_SECRET_KEY=sk_live_xxxxxxxxxxxxxxxxxxxxx
STRIPE_PUBLISHABLE_KEY=pk_live_xxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
STRIPE_CURRENCY=usd
STRIPE_TEST_MODE=false
```

#### Step 4: Configure Stripe Webhooks

Stripe webhooks notify your application about payment events.

1. Go to **Developers** → **Webhooks** in Stripe Dashboard
2. Click **Add endpoint**
3. Enter your webhook URL:
   ```
   https://yourdomain.com/marketplace/webhook/stripe
   ```
4. Select events to listen for:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
   - `charge.dispute.created`
   - `customer.subscription.created`
   - `customer.subscription.deleted`

5. Copy the **Webhook signing secret** and add it to `.env`:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
   ```

#### Step 5: Test Stripe Integration

Use Stripe's test cards:

```
# Successful payment
Card Number: 4242 4242 4242 4242
Expiry: Any future date
CVC: Any 3 digits
ZIP: Any 5 digits

# Decline (insufficient funds)
Card Number: 4000 0000 0000 9995

# Require authentication (3D Secure)
Card Number: 4000 0027 6000 3184
```

Test the payment flow:
```bash
php artisan tinker

# Test creating a payment
>>> $stripe = new \Webkul\Marketplace\Services\Payment\StripeGateway();
>>> $result = $stripe->createPaymentIntent(100, 'usd', [
    'description' => 'Test Extension Purchase',
    'metadata' => ['extension_id' => 1]
]);
>>> print_r($result);
```

### Additional Payment Gateway Configuration

#### Platform Fee & Revenue Sharing

Configure revenue sharing in `.env`:

```env
# Platform takes 30%, seller gets 70%
MARKETPLACE_PLATFORM_FEE_PERCENTAGE=30

# Minimum amount before seller can request payout ($50)
MARKETPLACE_MINIMUM_PAYOUT_AMOUNT=50

# Payout schedule in days (30 = monthly)
MARKETPLACE_PAYOUT_SCHEDULE_DAYS=30

# Refund period in days
MARKETPLACE_REFUND_PERIOD_DAYS=30
```

#### Currency Configuration

Set your default marketplace currency:

```env
MARKETPLACE_CURRENCY=USD
STRIPE_CURRENCY=usd
```

Supported currencies: USD, EUR, GBP, CAD, AUD, JPY, and more. See [Stripe Currency Support](https://stripe.com/docs/currencies).

---

## Scheduler Configuration

The marketplace includes an automated update checker that notifies users when new versions of their installed extensions are available.

### Step 1: Understanding the Update Command

The update checker command is:
```bash
php artisan marketplace:check-updates
```

Options:
- `--notify`: Send email notifications to users about available updates

### Step 2: Add to Laravel Scheduler

Edit `app/Console/Kernel.php` and add the update checker to the schedule:

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Existing schedules
        $schedule->command('inbound-emails:process')->everyFiveMinutes();

        // Marketplace update checker
        // Check for updates daily at 2:00 AM and send notifications
        $schedule->command('marketplace:check-updates --notify')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Alternative: Check for updates every 6 hours
        // $schedule->command('marketplace:check-updates --notify')
        //     ->everySixHours()
        //     ->withoutOverlapping()
        //     ->runInBackground();

        // Alternative: Check weekly on Monday at 9:00 AM
        // $schedule->command('marketplace:check-updates --notify')
        //     ->weeklyOn(1, '9:00')
        //     ->withoutOverlapping()
        //     ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

### Step 3: Configure Cron Job

The Laravel scheduler requires a single cron entry on your server.

**Linux/macOS:**

1. Open crontab editor:
```bash
crontab -e
```

2. Add this line:
```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path-to-your-project` with your actual project path.

**Example:**
```cron
* * * * * cd /var/www/laravel-crm && php artisan schedule:run >> /dev/null 2>&1
```

**Windows (Task Scheduler):**

1. Open Task Scheduler
2. Create a new basic task
3. Set trigger: Daily, repeat every 1 minute
4. Action: Start a program
5. Program/script: `C:\PHP\php.exe`
6. Arguments: `artisan schedule:run`
7. Start in: `C:\path\to\your\laravel-crm`

### Step 4: Verify Scheduler is Running

```bash
# List all scheduled tasks
php artisan schedule:list

# Expected output should include:
# marketplace:check-updates --notify .... Daily at 02:00

# Test the scheduler manually
php artisan schedule:run

# Test the update checker specifically
php artisan marketplace:check-updates --notify
```

### Step 5: Monitor Scheduler Logs

Add logging to monitor scheduler execution:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('marketplace:check-updates --notify')
        ->dailyAt('02:00')
        ->withoutOverlapping()
        ->runInBackground()
        ->onSuccess(function () {
            \Log::info('Marketplace update check completed successfully');
        })
        ->onFailure(function () {
            \Log::error('Marketplace update check failed');
        });
}
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

### Alternative: Using Queue Workers

For better performance, you can run the update checker via queue:

```bash
# Add to .env
QUEUE_CONNECTION=database

# Run migrations for queue tables
php artisan queue:table
php artisan migrate

# Start queue worker
php artisan queue:work --tries=3 --timeout=300
```

Update the scheduler:
```php
$schedule->command('marketplace:check-updates --notify')
    ->dailyAt('02:00')
    ->onOneServer()
    ->runInBackground();
```

### Production: Supervisor Configuration

For production, use Supervisor to keep the queue worker running:

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-crm/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravel-crm/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## Permissions & ACL

### Step 1: Configure Marketplace Permissions

The marketplace includes a comprehensive ACL (Access Control List) system. Permissions are defined in `packages/Webkul/Marketplace/src/Config/acl.php`.

### Step 2: Assign Permissions to Roles

Via Admin Panel:
1. Navigate to **Settings** → **Roles**
2. Edit or create a role
3. Enable marketplace permissions:
   - `marketplace.extensions.view` - View extensions
   - `marketplace.extensions.create` - Create/submit extensions
   - `marketplace.extensions.edit` - Edit own extensions
   - `marketplace.extensions.delete` - Delete own extensions
   - `marketplace.admin.extensions` - Manage all extensions (admin)
   - `marketplace.admin.reviews` - Moderate reviews (admin)
   - `marketplace.admin.transactions` - View all transactions (admin)

Via Code/Seeder:
```php
use Webkul\User\Models\Role;

$developerRole = Role::create([
    'name' => 'Developer',
    'description' => 'Extension Developer',
]);

$developerRole->permissions()->attach([
    'marketplace.extensions.view',
    'marketplace.extensions.create',
    'marketplace.extensions.edit',
    'marketplace.extensions.delete',
]);
```

### Step 3: Verify Permissions

Test permission checks:
```bash
php artisan tinker

# Check if user has permission
>>> $user = \App\Models\User::find(1);
>>> $user->hasPermission('marketplace.extensions.create');
>>> true

# Check role permissions
>>> $role = \Webkul\User\Models\Role::find(1);
>>> $role->permissions->pluck('name');
```

---

## Environment Variables

### Complete .env Configuration

Add all marketplace-related variables to your `.env` file:

```env
# ================================
# MARKETPLACE CONFIGURATION
# ================================

# Payment Gateway (Stripe)
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_PUBLISHABLE_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
STRIPE_CURRENCY=usd
STRIPE_TEST_MODE=true

# Marketplace General Settings
MARKETPLACE_PAYMENT_GATEWAY=stripe
MARKETPLACE_CURRENCY=USD
MARKETPLACE_PLATFORM_FEE_PERCENTAGE=30
MARKETPLACE_MINIMUM_PAYOUT_AMOUNT=50
MARKETPLACE_PAYOUT_SCHEDULE_DAYS=30
MARKETPLACE_REFUND_PERIOD_DAYS=30

# Storage Settings
MARKETPLACE_MAX_PACKAGE_SIZE=50
FILESYSTEM_DISK=local

# Cache Settings (optional)
MARKETPLACE_CACHE_ENABLED=true
MARKETPLACE_CACHE_TTL=60

# Security Settings
MARKETPLACE_AUTO_SCAN=true
MARKETPLACE_REQUIRE_MANUAL_REVIEW=true

# Queue Configuration (recommended for production)
QUEUE_CONNECTION=database
```

### Environment-Specific Configurations

**Development (.env.local):**
```env
APP_ENV=local
APP_DEBUG=true
STRIPE_TEST_MODE=true
MARKETPLACE_AUTO_SCAN=false
MARKETPLACE_REQUIRE_MANUAL_REVIEW=false
QUEUE_CONNECTION=sync
```

**Staging (.env.staging):**
```env
APP_ENV=staging
APP_DEBUG=true
STRIPE_TEST_MODE=true
MARKETPLACE_AUTO_SCAN=true
MARKETPLACE_REQUIRE_MANUAL_REVIEW=true
QUEUE_CONNECTION=database
```

**Production (.env.production):**
```env
APP_ENV=production
APP_DEBUG=false
STRIPE_TEST_MODE=false
MARKETPLACE_AUTO_SCAN=true
MARKETPLACE_REQUIRE_MANUAL_REVIEW=true
QUEUE_CONNECTION=redis
FILESYSTEM_DISK=s3
```

---

## Testing the Setup

### Step 1: Run Automated Tests

```bash
# Run marketplace-specific tests
php artisan test --filter=Marketplace

# Run all tests
php artisan test

# With coverage
php artisan test --coverage
```

### Step 2: Manual Testing Checklist

**User Flow:**
- [ ] Register as a user
- [ ] Browse marketplace extensions
- [ ] View extension details
- [ ] Purchase a paid extension (use test card)
- [ ] Install an extension
- [ ] Write a review
- [ ] Check for updates
- [ ] Update an extension
- [ ] Uninstall an extension

**Developer Flow:**
- [ ] Register as a developer
- [ ] Access developer portal
- [ ] Submit a new extension
- [ ] Upload extension package
- [ ] Wait for approval (or manually approve via admin)
- [ ] Publish extension
- [ ] View analytics
- [ ] Check earnings
- [ ] Update extension version

**Admin Flow:**
- [ ] Access admin marketplace panel
- [ ] Review pending extensions
- [ ] Approve/reject extensions
- [ ] Moderate reviews
- [ ] View all transactions
- [ ] Manage categories
- [ ] View platform earnings

### Step 3: Test Scheduler

```bash
# Test update checker
php artisan marketplace:check-updates --notify

# Check logs
tail -f storage/logs/laravel.log
```

### Step 4: Test Payment Flow

```bash
# Test Stripe connection
php artisan tinker

>>> $stripe = new \Webkul\Marketplace\Services\Payment\StripeGateway();
>>> $result = $stripe->healthCheck();
>>> echo $result ? 'Stripe Connected!' : 'Stripe Connection Failed';
```

### Step 5: Test Storage

```bash
# Test file upload
php artisan tinker

>>> Storage::disk('local')->put('marketplace/test.txt', 'Hello World');
>>> Storage::disk('local')->exists('marketplace/test.txt');
>>> true

# Clean up
>>> Storage::disk('local')->delete('marketplace/test.txt');
```

---

## Production Deployment

### Pre-Deployment Checklist

- [ ] All tests passing
- [ ] Environment variables configured for production
- [ ] Database migrations completed
- [ ] Storage directories created with proper permissions
- [ ] Stripe configured with live keys
- [ ] Webhooks configured and tested
- [ ] Scheduler cron job configured
- [ ] Queue workers configured (Supervisor)
- [ ] Cache configured (Redis recommended)
- [ ] SSL certificate installed
- [ ] Backup strategy in place

### Step 1: Optimize Application

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Compile assets
npm run production
```

### Step 2: Configure Web Server

**Nginx Example:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/laravel-crm/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Increase upload size for extension packages
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache Example (.htaccess):**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

<IfModule mod_php.c>
    php_value upload_max_filesize 50M
    php_value post_max_size 50M
    php_value max_execution_time 300
    php_value memory_limit 256M
</IfModule>
```

### Step 3: Set File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/laravel-crm

# Set directory permissions
sudo find /var/www/laravel-crm -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/laravel-crm -type f -exec chmod 644 {} \;

# Storage and cache need write permissions
sudo chmod -R 775 /var/www/laravel-crm/storage
sudo chmod -R 775 /var/www/laravel-crm/bootstrap/cache
```

### Step 4: Configure Monitoring

**Application Monitoring:**
- Use tools like **Laravel Telescope**, **Sentry**, or **Bugsnag**
- Monitor error rates, response times, and queue depths

**Server Monitoring:**
- Monitor disk space (extension packages can grow)
- Monitor database performance
- Monitor queue worker status

**Payment Monitoring:**
- Set up Stripe Dashboard alerts
- Monitor failed payments
- Track refund requests

### Step 5: Backup Strategy

```bash
# Database backup (daily)
0 2 * * * /usr/bin/mysqldump -u user -p'password' database > /backups/db-$(date +\%Y\%m\%d).sql

# Extension packages backup (weekly)
0 3 * * 0 tar -czf /backups/marketplace-$(date +\%Y\%m\%d).tar.gz /var/www/laravel-crm/storage/app/marketplace

# Automated backup script
# Save as /usr/local/bin/backup-marketplace.sh
```

---

## Troubleshooting

### Common Issues and Solutions

#### Issue 1: Stripe Connection Failed

**Symptoms:**
- Payment processing fails
- "Invalid API key" errors

**Solutions:**
```bash
# Verify Stripe keys are set
php artisan tinker
>>> config('services.stripe.secret_key')

# Test Stripe connection
>>> $stripe = new \Webkul\Marketplace\Services\Payment\StripeGateway();
>>> $stripe->healthCheck();

# Clear config cache
php artisan config:clear
php artisan config:cache
```

#### Issue 2: Scheduler Not Running

**Symptoms:**
- Update checks not happening
- No notifications being sent

**Solutions:**
```bash
# Check if cron job exists
crontab -l | grep schedule:run

# Test scheduler manually
php artisan schedule:run

# Check scheduler list
php artisan schedule:list

# Verify command works
php artisan marketplace:check-updates

# Check logs
tail -f storage/logs/laravel.log
```

#### Issue 3: File Upload Fails

**Symptoms:**
- "File too large" errors
- Upload timeouts

**Solutions:**
```bash
# Check PHP limits
php -i | grep -E "upload_max_filesize|post_max_size|max_execution_time"

# Update php.ini
sudo nano /etc/php/8.1/fpm/php.ini
# Update values and restart PHP-FPM
sudo service php8.1-fpm restart

# Check storage permissions
ls -la storage/app/marketplace
sudo chmod -R 775 storage/app/marketplace
```

#### Issue 4: Permissions Denied

**Symptoms:**
- "Permission denied" errors
- Users can't access marketplace

**Solutions:**
```bash
# Clear permission cache
php artisan cache:clear

# Check user permissions
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->getAllPermissions();

# Resync permissions
php artisan db:seed --class=Webkul\\User\\Database\\Seeders\\ACLTableSeeder
```

#### Issue 5: Migrations Failed

**Symptoms:**
- Tables not created
- Migration errors

**Solutions:**
```bash
# Check migration status
php artisan migrate:status

# Rollback and retry
php artisan migrate:rollback
php artisan migrate

# Fresh migration (WARNING: Deletes data)
php artisan migrate:fresh

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Debug Mode

Enable debug mode to see detailed errors:

```env
# .env
APP_DEBUG=true
APP_ENV=local
```

**Never enable debug mode in production!**

### Logging

```bash
# Enable query logging
php artisan tinker
>>> DB::enableQueryLog();
>>> // Run your code
>>> dd(DB::getQueryLog());

# Check application logs
tail -f storage/logs/laravel.log

# Check web server logs
# Nginx
sudo tail -f /var/log/nginx/error.log

# Apache
sudo tail -f /var/log/apache2/error.log
```

---

## Advanced Configuration

### Customize Revenue Share

Edit `packages/Webkul/Marketplace/src/Config/marketplace.php`:

```php
'platform_fee_percentage' => env('MARKETPLACE_PLATFORM_FEE_PERCENTAGE', 30),
```

Or in `.env`:
```env
MARKETPLACE_PLATFORM_FEE_PERCENTAGE=20  # Platform gets 20%, seller gets 80%
```

### Multiple Currency Support

Configure accepted currencies:

```php
// In a service provider or configuration
'currencies' => [
    'USD' => ['symbol' => '$', 'rate' => 1.00],
    'EUR' => ['symbol' => '€', 'rate' => 0.85],
    'GBP' => ['symbol' => '£', 'rate' => 0.73],
],
```

### Custom Email Templates

Customize notification emails:

```bash
# Publish mail views
php artisan vendor:publish --tag=marketplace-mail-views

# Edit templates in
resources/views/vendor/marketplace/mail/
```

### Rate Limiting

Protect API endpoints with rate limiting:

```php
// In routes/api.php or marketplace routes
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes
});
```

### Cache Configuration

For better performance, use Redis:

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### CDN Integration

Serve extension packages via CDN:

```php
// In config/filesystems.php
'marketplace-cdn' => [
    'driver' => 's3',
    'key' => env('CDN_KEY'),
    'secret' => env('CDN_SECRET'),
    'region' => env('CDN_REGION'),
    'bucket' => env('CDN_BUCKET'),
    'url' => env('CDN_URL'),
],
```

---

## Support & Resources

### Documentation
- [User Guide](./USER-GUIDE.md) - For extension users
- [Developer Guide](./DEVELOPER-GUIDE.md) - For extension developers
- [API Documentation](./README.md) - API reference

### Community
- **Forum**: https://forum.laravel-crm.com
- **Discord**: https://discord.gg/laravel-crm
- **GitHub Issues**: https://github.com/hamzahllc/laravel-crm/issues

### Contact
- **Technical Support**: support@laravel-crm.com
- **Security Issues**: security@laravel-crm.com
- **Sales**: sales@laravel-crm.com

---

## Changelog

### Version 1.0.0 (2024-01-11)
- Initial setup guide release
- Complete installation instructions
- Payment gateway configuration
- Storage setup guide
- Scheduler configuration
- Troubleshooting section
- Production deployment guide

---

## License

This setup guide is provided as part of the Laravel CRM Extension Marketplace under the MIT License.

---

**Setup Complete! 🎉**

Your Extension Marketplace is now configured and ready to use. For additional help, please refer to the [User Guide](./USER-GUIDE.md) or [Developer Guide](./DEVELOPER-GUIDE.md).
