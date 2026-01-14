<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Starting DocumentationSeeder...');

        $this->createDocumentationCategories();
        $this->createGettingStartedArticles();
        $this->createApiDocumentationArticles();

        $this->command->info('Documentation seeded successfully!');
    }

    /**
     * Create documentation categories.
     *
     * @return void
     */
    protected function createDocumentationCategories()
    {
        $categories = [
            [
                'name' => 'Getting Started',
                'slug' => 'getting-started',
                'description' => 'Quick setup guides and tutorials to get you productive in under 30 minutes',
                'parent_id' => null,
                'icon' => 'heroicon-o-rocket-launch',
                'sort_order' => 1,
                'is_active' => true,
                'visibility' => 'public',
            ],
            [
                'name' => 'API Documentation',
                'slug' => 'api-docs',
                'description' => 'Complete API reference with examples for all endpoints',
                'parent_id' => null,
                'icon' => 'heroicon-o-code-bracket',
                'sort_order' => 2,
                'is_active' => true,
                'visibility' => 'public',
            ],
            [
                'name' => 'Feature Guides',
                'slug' => 'features',
                'description' => 'Detailed guides for each major CRM feature',
                'parent_id' => null,
                'icon' => 'heroicon-o-book-open',
                'sort_order' => 3,
                'is_active' => true,
                'visibility' => 'public',
            ],
            [
                'name' => 'Troubleshooting',
                'slug' => 'troubleshooting',
                'description' => 'Common issues and solutions',
                'parent_id' => null,
                'icon' => 'heroicon-o-wrench-screwdriver',
                'sort_order' => 4,
                'is_active' => true,
                'visibility' => 'public',
            ],
        ];

        foreach ($categories as $category) {
            $existingCategory = DB::table('doc_categories')
                ->where('slug', $category['slug'])
                ->first();

            if (!$existingCategory) {
                DB::table('doc_categories')->insert(array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                $this->command->info("Created category: {$category['name']}");
            }
        }
    }

    /**
     * Create Getting Started articles.
     *
     * @return void
     */
    protected function createGettingStartedArticles()
    {
        $gettingStartedCategory = DB::table('doc_categories')
            ->where('slug', 'getting-started')
            ->first();

        if (!$gettingStartedCategory) {
            $this->command->error('Getting Started category not found!');
            return;
        }

        $articles = [
            [
                'title' => 'Quick Setup Guide (5 Minutes)',
                'slug' => 'quick-setup-guide',
                'content' => $this->getQuickSetupContent(),
                'excerpt' => 'Get up and running with the CRM in just 5 minutes. Covers basic configuration and first steps.',
                'type' => 'getting-started',
                'difficulty_level' => 'beginner',
                'reading_time_minutes' => 5,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Understanding the Dashboard',
                'slug' => 'understanding-dashboard',
                'content' => $this->getDashboardContent(),
                'excerpt' => 'Learn how to navigate and customize your dashboard for maximum productivity.',
                'type' => 'getting-started',
                'difficulty_level' => 'beginner',
                'reading_time_minutes' => 7,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Creating Your First Lead',
                'slug' => 'creating-first-lead',
                'content' => $this->getFirstLeadContent(),
                'excerpt' => 'Step-by-step guide to creating and managing your first lead in the CRM.',
                'type' => 'getting-started',
                'difficulty_level' => 'beginner',
                'reading_time_minutes' => 6,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Managing Contacts',
                'slug' => 'managing-contacts',
                'content' => $this->getContactsContent(),
                'excerpt' => 'Learn how to add, organize, and manage your contact database effectively.',
                'type' => 'getting-started',
                'difficulty_level' => 'beginner',
                'reading_time_minutes' => 8,
                'status' => 'published',
                'visibility' => 'public',
                'sort_order' => 4,
            ],
            [
                'title' => 'Basic Configuration',
                'slug' => 'basic-configuration',
                'content' => $this->getConfigurationContent(),
                'excerpt' => 'Configure essential settings including company info, users, and preferences.',
                'type' => 'getting-started',
                'difficulty_level' => 'intermediate',
                'reading_time_minutes' => 10,
                'status' => 'published',
                'visibility' => 'public',
                'sort_order' => 5,
            ],
        ];

        foreach ($articles as $article) {
            $existingArticle = DB::table('doc_articles')
                ->where('slug', $article['slug'])
                ->first();

            if (!$existingArticle) {
                $articleId = DB::table('doc_articles')->insertGetId(array_merge($article, [
                    'category_id' => $gettingStartedCategory->id,
                    'author_id' => 1, // Default admin user
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                // Create sections for the article
                $this->createArticleSections($articleId, $article['slug']);

                $this->command->info("Created article: {$article['title']}");
            }
        }
    }

    /**
     * Create article sections.
     *
     * @param int $articleId
     * @param string $articleSlug
     * @return void
     */
    protected function createArticleSections($articleId, $articleSlug)
    {
        $sectionsMap = [
            'quick-setup-guide' => [
                [
                    'title' => 'Prerequisites',
                    'content' => $this->getQuickSetupPrerequisitesContent(),
                    'level' => 1,
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Installation Steps',
                    'content' => $this->getQuickSetupInstallationContent(),
                    'level' => 1,
                    'sort_order' => 2,
                ],
                [
                    'title' => 'Initial Configuration',
                    'content' => $this->getQuickSetupConfigContent(),
                    'level' => 1,
                    'sort_order' => 3,
                ],
                [
                    'title' => 'Verification',
                    'content' => $this->getQuickSetupVerificationContent(),
                    'level' => 1,
                    'sort_order' => 4,
                ],
            ],
            'understanding-dashboard' => [
                [
                    'title' => 'Dashboard Overview',
                    'content' => $this->getDashboardOverviewContent(),
                    'level' => 1,
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Key Metrics',
                    'content' => $this->getDashboardMetricsContent(),
                    'level' => 1,
                    'sort_order' => 2,
                ],
                [
                    'title' => 'Customization Options',
                    'content' => $this->getDashboardCustomizationContent(),
                    'level' => 1,
                    'sort_order' => 3,
                ],
            ],
        ];

        $sections = $sectionsMap[$articleSlug] ?? [];

        foreach ($sections as $section) {
            DB::table('doc_sections')->insert(array_merge($section, [
                'article_id' => $articleId,
                'slug' => Str::slug($section['title']),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Content for Quick Setup Guide article.
     */
    protected function getQuickSetupContent()
    {
        return <<<HTML
<h1>Quick Setup Guide (5 Minutes)</h1>

<p>Welcome to the CRM! This guide will help you get up and running in just 5 minutes. By the end, you'll have a working CRM system ready to manage your leads and contacts.</p>

<h2>What You'll Learn</h2>

<ul>
<li>Installing and configuring the CRM</li>
<li>Setting up your first user account</li>
<li>Basic system configuration</li>
<li>Creating your first lead</li>
</ul>

<h2>Before You Start</h2>

<p>Make sure you have:</p>

<ul>
<li>PHP 8.1 or higher installed</li>
<li>MySQL 5.7+ or PostgreSQL 9.6+</li>
<li>Composer installed</li>
<li>Basic knowledge of command line</li>
</ul>

<div class="callout-info">
<strong>Pro Tip:</strong> Keep this guide open in a separate tab as you work through the setup steps.
</div>

<h2>Next Steps</h2>

<p>Use the navigation on the left to jump to specific sections, or follow along sequentially for the complete setup experience.</p>
HTML;
    }

    protected function getQuickSetupPrerequisitesContent()
    {
        return <<<HTML
<h3>System Requirements</h3>

<p>Before installing the CRM, ensure your system meets these requirements:</p>

<h4>Server Requirements</h4>

<ul>
<li><strong>PHP:</strong> 8.1 or higher</li>
<li><strong>Database:</strong> MySQL 5.7+ / PostgreSQL 9.6+ / SQLite 3.8.8+</li>
<li><strong>Web Server:</strong> Apache (with mod_rewrite) or Nginx</li>
<li><strong>PHP Extensions:</strong> BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML</li>
</ul>

<h4>Optional but Recommended</h4>

<ul>
<li>Redis for caching and queues</li>
<li>Supervisor for queue workers</li>
<li>SSL certificate for production</li>
</ul>

<h4>Checking Your Environment</h4>

<p>Run this command to verify your PHP version:</p>

<pre><code>php -v</code></pre>

<p>To check installed extensions:</p>

<pre><code>php -m</code></pre>
HTML;
    }

    protected function getQuickSetupInstallationContent()
    {
        return <<<HTML
<h3>Installation Steps</h3>

<h4>Step 1: Download the CRM</h4>

<p>Clone the repository or download the latest release:</p>

<pre><code>git clone https://github.com/your-org/laravel-crm.git
cd laravel-crm</code></pre>

<h4>Step 2: Install Dependencies</h4>

<p>Install all PHP dependencies using Composer:</p>

<pre><code>composer install --no-dev --optimize-autoloader</code></pre>

<h4>Step 3: Configure Environment</h4>

<p>Copy the example environment file and configure it:</p>

<pre><code>cp .env.example .env</code></pre>

<p>Edit <code>.env</code> and update the following settings:</p>

<pre><code>APP_NAME="Your CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password</code></pre>

<h4>Step 4: Generate Application Key</h4>

<pre><code>php artisan key:generate</code></pre>

<h4>Step 5: Run Migrations</h4>

<p>Create the database tables:</p>

<pre><code>php artisan migrate --force</code></pre>

<h4>Step 6: Seed the Database</h4>

<p>Populate the database with initial data:</p>

<pre><code>php artisan db:seed --force</code></pre>

<h4>Step 7: Create Storage Link</h4>

<pre><code>php artisan storage:link</code></pre>

<h4>Step 8: Cache Configuration</h4>

<pre><code>php artisan config:cache
php artisan route:cache
php artisan view:cache</code></pre>
HTML;
    }

    protected function getQuickSetupConfigContent()
    {
        return <<<HTML
<h3>Initial Configuration</h3>

<h4>Create Admin Account</h4>

<p>During seeding, an admin account is created. The default credentials are:</p>

<ul>
<li><strong>Email:</strong> admin@example.com</li>
<li><strong>Password:</strong> password</li>
</ul>

<div class="callout-warning">
<strong>Important:</strong> Change the admin password immediately after first login!
</div>

<h4>Configure Company Settings</h4>

<ol>
<li>Log in to the admin panel</li>
<li>Navigate to Settings > Company</li>
<li>Update your company information</li>
<li>Set your timezone and date format</li>
<li>Save your changes</li>
</ol>

<h4>Configure Email Settings</h4>

<p>Go to Settings > Mail and configure your email settings:</p>

<ul>
<li>Choose your mail driver (SMTP, Mailgun, etc.)</li>
<li>Enter your mail server details</li>
<li>Set the from address and name</li>
<li>Send a test email to verify configuration</li>
</ul>

<h4>Set Up User Roles</h4>

<p>Navigate to Settings > Roles and Permissions:</p>

<ol>
<li>Review default roles (Admin, Sales Manager, Sales Rep)</li>
<li>Create custom roles if needed</li>
<li>Assign appropriate permissions to each role</li>
</ol>
HTML;
    }

    protected function getQuickSetupVerificationContent()
    {
        return <<<HTML
<h3>Verification</h3>

<h4>Test Your Installation</h4>

<p>Complete these checks to ensure everything is working:</p>

<ol>
<li><strong>Admin Panel Access:</strong> Visit <code>/admin</code> and log in</li>
<li><strong>Create a Test Lead:</strong> Navigate to Leads > Create Lead</li>
<li><strong>Send Test Email:</strong> Use the email test feature in Settings</li>
<li><strong>Check Dashboard:</strong> Verify metrics display correctly</li>
</ol>

<h4>Common Issues</h4>

<h5>Page Not Found / 404 Error</h5>

<p>Ensure your web server is configured correctly and mod_rewrite is enabled.</p>

<h5>Database Connection Error</h5>

<p>Verify your <code>.env</code> database credentials and ensure the database exists.</p>

<h5>Permissions Error</h5>

<p>Set proper permissions:</p>

<pre><code>sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache</code></pre>

<h4>What's Next?</h4>

<p>Congratulations! Your CRM is now set up. Here are recommended next steps:</p>

<ul>
<li>Complete your user profile</li>
<li>Import existing contacts</li>
<li>Customize your dashboard</li>
<li>Explore the feature guides</li>
<li>Set up email integration</li>
</ul>

<div class="callout-success">
<strong>You're All Set!</strong> You can now start using your CRM. Check out other Getting Started articles for more detailed guides.
</div>
HTML;
    }

    /**
     * Content for Understanding Dashboard article.
     */
    protected function getDashboardContent()
    {
        return <<<HTML
<h1>Understanding the Dashboard</h1>

<p>The dashboard is your command center - it gives you an at-a-glance view of your CRM activities, performance metrics, and quick access to frequently used features.</p>

<h2>Dashboard Overview</h2>

<p>When you first log in, you'll see a clean, organized interface designed to help you work efficiently. The dashboard is divided into several key areas:</p>

<ul>
<li><strong>Top Navigation Bar:</strong> Quick access to main modules and settings</li>
<li><strong>Sidebar:</strong> Module navigation and quick links</li>
<li><strong>Main Content Area:</strong> Metrics, charts, and activity feeds</li>
<li><strong>Quick Actions:</strong> Create new leads, contacts, and tasks</li>
</ul>

<h2>Key Metrics</h2>

<p>The dashboard displays essential metrics that help you track your business performance at a glance.</p>

<h2>Customization Options</h2>

<p>Personalize your dashboard to match your workflow and preferences.</p>
HTML;
    }

    protected function getDashboardOverviewContent()
    {
        return <<<HTML
<h3>Dashboard Overview</h3>

<h4>Navigation Bar</h4>

<p>The top navigation bar provides quick access to:</p>

<ul>
<li><strong>Home:</strong> Return to dashboard from anywhere</li>
<li><strong>Leads:</strong> Manage your sales pipeline</li>
<li><strong>Contacts:</strong> Access your contact database</li>
<li><strong>Products:</strong> View and manage products/services</li>
<li><strong>Reports:</strong> Generate business insights</li>
<li><strong>Settings:</strong> Configure system preferences</li>
</ul>

<h4>Sidebar Menu</h4>

<p>The left sidebar offers quick links to:</p>

<ul>
<li>Recently viewed items</li>
<li>Quick actions (Create Lead, Add Contact)</li>
<li>My Tasks and Calendar</li>
<li>Team activities</li>
<li>Notifications center</li>
</ul>

<h4>Main Content Area</h4>

<p>The center area displays:</p>

<ul>
<li>Performance metrics and KPI cards</li>
<li>Activity timeline</li>
<li>Upcoming tasks and appointments</li>
<li>Recent leads and opportunities</li>
<li>Pipeline visualization</li>
</ul>
HTML;
    }

    protected function getDashboardMetricsContent()
    {
        return <<<HTML
<h3>Key Metrics</h3>

<p>Your dashboard shows these essential performance indicators:</p>

<h4>Sales Metrics</h4>

<ul>
<li><strong>Total Revenue:</strong> Month-to-date sales revenue</li>
<li><strong>Won Deals:</strong> Number of closed deals this month</li>
<li><strong>Conversion Rate:</strong> Lead to customer conversion percentage</li>
<li><strong>Average Deal Size:</strong> Average revenue per closed deal</li>
</ul>

<h4>Pipeline Metrics</h4>

<ul>
<li><strong>Total Leads:</strong> All leads in your pipeline</li>
<li><strong>Open Opportunities:</strong> Active deals being worked</li>
<li><strong>Stage Distribution:</strong> Breakdown by pipeline stage</li>
<li><strong>Pipeline Value:</strong> Total value of all opportunities</li>
</ul>

<h4>Activity Metrics</h4>

<ul>
<li><strong>Calls Made:</strong> Number of calls today/this week</li>
<li><strong>Emails Sent:</strong> Email communication count</li>
<li><strong>Meetings Scheduled:</strong> Upcoming appointments</li>
<li><strong>Tasks Completed:</strong> Task completion rate</li>
</ul>

<h4>Understanding the Charts</h4>

<p><strong>Sales Trend Chart:</strong> Shows revenue over time. Look for upward trends and seasonal patterns.</p>

<p><strong>Lead Sources Chart:</strong> Displays where your leads come from. Helps identify best marketing channels.</p>

<p><strong>Pipeline Funnel:</strong> Visualizes your sales pipeline. Watch for bottlenecks in any stage.</p>

<div class="callout-info">
<strong>Tip:</strong> Click on any metric card to drill down into detailed reports and filters.
</div>
HTML;
    }

    protected function getDashboardCustomizationContent()
    {
        return <<<HTML
<h3>Customization Options</h3>

<h4>Personalize Your Dashboard</h4>

<p>Make your dashboard work for you:</p>

<ol>
<li><strong>Rearrange Widgets:</strong> Drag and drop cards to reorder them</li>
<li><strong>Add/Remove Widgets:</strong> Click the customize button to show or hide widgets</li>
<li><strong>Set Date Range:</strong> Choose time periods for metrics (Today, This Week, This Month, Custom)</li>
<li><strong>Save Layouts:</strong> Create multiple dashboard layouts for different purposes</li>
</ol>

<h4>Create Custom Widgets</h4>

<p>Build personalized widgets:</p>

<ul>
<li>Select widget type (Metric, Chart, List, Activity Feed)</li>
<li>Choose data source</li>
<li>Apply filters and conditions</li>
<li>Set refresh interval</li>
</ul>

<h4>Dashboard Layouts</h4>

<p>Create multiple layouts for different scenarios:</p>

<ul>
<li><strong>Sales Dashboard:</strong> Focus on pipeline and revenue metrics</li>
<li><strong>Activity Dashboard:</strong> Monitor calls, emails, and tasks</li>
<li><strong>Team Dashboard:</strong> View team performance and workload</li>
</ul>

<h4>Keyboard Shortcuts</h4>

<p>Navigate faster with these shortcuts:</p>

<ul>
<li><strong>Ctrl + K:</strong> Quick search</li>
<li><strong>Ctrl + L:</strong> Create new lead</li>
<li><strong>Ctrl + C:</strong> Create new contact</li>
<li><strong>Ctrl + D:</strong> Go to dashboard</li>
</ul>

<div class="callout-success">
<strong>Pro Tip:</strong> Set your most-used dashboard as the default landing page after login.
</div>
HTML;
    }

    /**
     * Content for Creating Your First Lead article.
     */
    protected function getFirstLeadContent()
    {
        return <<<HTML
<h1>Creating Your First Lead</h1>

<p>Leads are potential customers who have shown interest in your products or services. This guide walks you through creating and managing your first lead.</p>

<h2>What is a Lead?</h2>

<p>A lead represents a potential sales opportunity. It could be:</p>

<ul>
<li>Someone who filled out a contact form on your website</li>
<li>A business card from a networking event</li>
<li>A referral from an existing customer</li>
<li>A prospect you identified through research</li>
</ul>

<h2>Creating a Lead</h2>

<h3>Method 1: Quick Create</h3>

<ol>
<li>Click the "+ Create" button in the top navigation</li>
<li>Select "Lead" from the dropdown</li>
<li>Fill in the required fields</li>
<li>Click "Save"</li>
</ol>

<h3>Method 2: From Leads Module</h3>

<ol>
<li>Navigate to Leads > All Leads</li>
<li>Click the "Add Lead" button</li>
<li>Complete the lead form</li>
<li>Click "Save"</li>
</ol>

<h2>Essential Lead Information</h2>

<p>Include these key details for effective lead management:</p>

<ul>
<li><strong>Contact Information:</strong> Name, email, phone</li>
<li><strong>Company:</strong> Company name and website</li>
<li><strong>Source:</strong> How they found you</li>
<li><strong>Status:</strong> New, Contacted, Qualified, etc.</li>
<li><strong>Value:</strong> Estimated deal value</li>
<li><strong>Assigned To:</strong> Which team member owns this lead</li>
</ul>

<h2>Next Steps After Creation</h2>

<p>After creating a lead:</p>

<ol>
<li>Research the prospect</li>
<li>Schedule a follow-up call</li>
<li>Log all interactions</li>
<li>Move through pipeline stages</li>
<li>Convert to opportunity when qualified</li>
</ol>
HTML;
    }

    /**
     * Content for Managing Contacts article.
     */
    protected function getContactsContent()
    {
        return <<<HTML
<h1>Managing Contacts</h1>

<p>Contacts are the people and organizations you do business with. This guide shows you how to effectively manage your contact database.</p>

<h2>Contacts vs Leads</h2>

<p><strong>Leads</strong> are potential customers you're trying to acquire.</p>

<p><strong>Contacts</strong> are people you have an ongoing relationship with - customers, partners, vendors, etc.</p>

<h2>Adding Contacts</h2>

<h3>Manual Entry</h3>

<ol>
<li>Go to Contacts > All Contacts</li>
<li>Click "Add Contact"</li>
<li>Fill in contact details</li>
<li>Click "Save"</li>
</ol>

<h3>Import from CSV</h3>

<ol>
<li>Prepare your CSV file with contact data</li>
<li>Go to Contacts > Import</li>
<li>Upload your CSV file</li>
<li>Map columns to contact fields</li>
<li>Review and confirm import</li>
</ol>

<h3>Convert from Lead</h3>

<p>When a lead becomes a customer:</p>

<ol>
<li>Open the lead record</li>
<li>Click "Convert to Contact"</li>
<li>Verify the contact information</li>
<li>Click "Confirm"</li>
</ol>

<h2>Organizing Contacts</h2>

<p>Keep your contacts organized with:</p>

<ul>
<li><strong>Tags:</strong> Add custom tags for categorization</li>
<li><strong>Groups:</strong> Create contact groups (Customers, Partners, VIPs)</li>
<li><strong>Custom Fields:</strong> Add industry-specific information</li>
</ul>

<h2>Best Practices</h2>

<ul>
<li>Keep contact information up-to-date</li>
<li>Log all interactions</li>
<li>Use tags for easy filtering</li>
<li>Regular data cleanup to remove duplicates</li>
<li>Set up automated data enrichment</li>
</ul>
HTML;
    }

    /**
     * Content for Basic Configuration article.
     */
    protected function getConfigurationContent()
    {
        return <<<HTML
<h1>Basic Configuration</h1>

<p>Configure essential settings to customize the CRM for your business needs.</p>

<h2>Company Settings</h2>

<p>Go to Settings > Company to configure:</p>

<ul>
<li><strong>Company Name:</strong> Your organization name</li>
<li><strong>Logo:</strong> Upload your company logo</li>
<li><strong>Address:</strong> Business address and contact info</li>
<li><strong>Timezone:</strong> Set your local timezone</li>
<li><strong>Date Format:</strong> Choose preferred date format</li>
<li><strong>Time Format:</strong> 12-hour or 24-hour clock</li>
<li><strong>Currency:</strong> Set default currency</li>
</ul>

<h2>User Management</h2>

<h3>Adding Users</h3>

<ol>
<li>Go to Settings > Users</li>
<li>Click "Add User"</li>
<li>Enter user details</li>
<li>Assign role and permissions</li>
<li>User receives email invitation</li>
</ol>

<h3>Roles and Permissions</h3>

<p>Configure access levels:</p>

<ul>
<li><strong>Admin:</strong> Full system access</li>
<li><strong>Sales Manager:</strong> Manage team and view all leads</li>
<li><strong>Sales Rep:</strong> Manage own leads and contacts</li>
<li><strong>Read Only:</strong> View-only access</li>
</ul>

<h2>Email Configuration</h2>

<p>Set up email integration:</p>

<ol>
<li>Go to Settings > Mail</li>
<li>Choose mail driver (SMTP recommended)</li>
<li>Enter server details</li>
<li>Configure from name and address</li>
<li>Send test email</li>
</ol>

<h2>Pipeline Configuration</h2>

<p>Customize your sales pipeline:</p>

<ol>
<li>Go to Settings > Pipeline</li>
<li>Add/edit pipeline stages</li>
<li>Set probability percentages</li>
<li>Define stage criteria</li>
<li>Save your pipeline</li>
</ol>

<h2>Automation Settings</h2>

<p>Set up workflows and automations:</p>

<ul>
<li><strong>Auto-assignment rules:</strong> Automatically assign leads</li>
<li><strong>Email notifications:</strong> Configure alert preferences</li>
<li><strong>Task automation:</strong> Auto-create tasks based on triggers</li>
<li><strong>Field updates:</strong> Auto-update fields based on conditions</li>
</ul>

<h2>Security Settings</h2>

<p>Secure your CRM:</p>

<ul>
<li>Enforce strong passwords</li>
<li>Enable two-factor authentication</li>
<li>Set session timeout</li>
<li>Configure IP restrictions</li>
<li>Enable audit logging</li>
</ul>

<div class="callout-info">
<strong>Tip:</strong> Review your configuration periodically and adjust as your business grows.
</div>
HTML;
    }

    /**
     * Create API Documentation articles.
     *
     * @return void
     */
    protected function createApiDocumentationArticles()
    {
        $apiDocsCategory = DB::table('doc_categories')
            ->where('slug', 'api-docs')
            ->first();

        if (!$apiDocsCategory) {
            $this->command->error('API Documentation category not found!');
            return;
        }

        $articles = [
            [
                'title' => 'Compliance API Documentation',
                'slug' => 'compliance-api',
                'content' => $this->getComplianceApiContent(),
                'excerpt' => 'Advanced Compliance Features for GDPR, HIPAA, and SOC 2 - Complete API reference for consent management, data retention, deletion requests, and compliance reporting.',
                'type' => 'api',
                'difficulty_level' => 'advanced',
                'reading_time_minutes' => 25,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($articles as $article) {
            $existingArticle = DB::table('doc_articles')
                ->where('slug', $article['slug'])
                ->first();

            if (!$existingArticle) {
                $articleId = DB::table('doc_articles')->insertGetId(array_merge($article, [
                    'category_id' => $apiDocsCategory->id,
                    'author_id' => 1,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                $this->command->info("Created API article: {$article['title']}");
            }
        }
    }

    /**
     * Content for Compliance API Documentation article.
     */
    protected function getComplianceApiContent()
    {
        return <<<HTML
<h1>Compliance API Documentation</h1>
<h2>Advanced Compliance Features for GDPR, HIPAA, and SOC 2</h2>

<hr>

<h2>📖 Overview</h2>

<p>This document provides comprehensive documentation for all compliance-related API endpoints. These APIs enable programmatic access to consent management, data retention policies, right-to-erasure requests, and compliance reporting features.</p>

<h3>Base URL</h3>
<pre><code>https://your-domain.com/api</code></pre>

<h3>Authentication</h3>
<p>All API endpoints require authentication using Laravel Sanctum or API tokens. Include your API token in the request header:</p>

<pre><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>

<h3>Response Format</h3>
<p>All responses follow a consistent JSON structure:</p>

<p><strong>Success Response:</strong></p>
<pre><code>{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... }
}</code></pre>

<p><strong>Error Response:</strong></p>
<pre><code>{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}</code></pre>

<h3>Common HTTP Status Codes</h3>
<ul>
<li><code>200</code> - OK - Request successful</li>
<li><code>201</code> - Created - Resource created successfully</li>
<li><code>400</code> - Bad Request - Invalid request parameters</li>
<li><code>401</code> - Unauthorized - Authentication required</li>
<li><code>404</code> - Not Found - Resource not found</li>
<li><code>422</code> - Unprocessable Entity - Validation failed</li>
<li><code>500</code> - Internal Server Error - Server error occurred</li>
</ul>

<hr>

<h2>🔐 Consent Management API</h2>

<p>Manage GDPR-compliant consent records for users.</p>

<h3>1. Get All Consent Records</h3>

<p><strong>Endpoint:</strong> <code>GET /api/consent</code></p>

<p><strong>Description:</strong> Retrieve all consent records for the authenticated user.</p>

<p><strong>Query Parameters:</strong></p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>type</code></td>
<td>string</td>
<td>No</td>
<td>Filter by consent type</td>
</tr>
<tr>
<td><code>active_only</code></td>
<td>boolean</td>
<td>No</td>
<td>Return only active consents (default: false)</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X GET "https://your-domain.com/api/consent?active_only=true" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json"</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "consent_type": "marketing",
      "purpose": "Email marketing communications",
      "given_at": "2024-01-15T10:30:00.000000Z",
      "withdrawn_at": null,
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "metadata": {},
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  ]
}</code></pre>

<h3>2. Record Consent</h3>

<p><strong>Endpoint:</strong> <code>POST /api/consent</code></p>

<p><strong>Description:</strong> Record a new consent for the authenticated user.</p>

<p><strong>Request Body:</strong></p>
<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>consent_type</code></td>
<td>string</td>
<td>Yes</td>
<td>Type of consent (e.g., 'marketing', 'analytics')</td>
</tr>
<tr>
<td><code>purpose</code></td>
<td>string</td>
<td>No</td>
<td>Purpose of the consent (max 1000 chars)</td>
</tr>
<tr>
<td><code>metadata</code></td>
<td>object</td>
<td>No</td>
<td>Additional metadata</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X POST "https://your-domain.com/api/consent" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "consent_type": "marketing",
    "purpose": "Email marketing communications",
    "metadata": {
      "source": "mobile_app",
      "version": "2.0"
    }
  }'</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "message": "Consent recorded successfully",
  "data": {
    "id": 1,
    "user_id": 123,
    "consent_type": "marketing",
    "purpose": "Email marketing communications",
    "given_at": "2024-01-15T10:30:00.000000Z",
    "withdrawn_at": null,
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "metadata": {
      "source": "mobile_app",
      "version": "2.0"
    },
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}</code></pre>

<h3>3. Record Multiple Consents</h3>

<p><strong>Endpoint:</strong> <code>POST /api/consent/multiple</code></p>

<p><strong>Description:</strong> Record multiple consents at once for the authenticated user.</p>

<p><strong>Request Body:</strong></p>
<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>consent_types</code></td>
<td>array</td>
<td>Yes</td>
<td>Array of consent types to record</td>
</tr>
<tr>
<td><code>metadata</code></td>
<td>object</td>
<td>No</td>
<td>Shared metadata for all consents</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X POST "https://your-domain.com/api/consent/multiple" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "consent_types": ["marketing", "analytics", "necessary"],
    "metadata": {
      "onboarding": true
    }
  }'</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "message": "Consents recorded successfully",
  "data": [
    {
      "id": 1,
      "consent_type": "marketing",
      "given_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "consent_type": "analytics",
      "given_at": "2024-01-15T10:30:00.000000Z"
    }
  ]
}</code></pre>

<h3>4. Withdraw Consent</h3>

<p><strong>Endpoint:</strong> <code>DELETE /api/consent/{consentType}</code></p>

<p><strong>Description:</strong> Withdraw a specific consent for the authenticated user.</p>

<p><strong>Path Parameters:</strong></p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>consentType</code></td>
<td>string</td>
<td>Yes</td>
<td>Type of consent to withdraw</td>
</tr>
</tbody>
</table>

<p><strong>Request Body:</strong></p>
<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>metadata</code></td>
<td>object</td>
<td>No</td>
<td>Additional metadata for withdrawal</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X DELETE "https://your-domain.com/api/consent/marketing" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "metadata": {
      "reason": "User requested via settings"
    }
  }'</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "message": "Consent withdrawn successfully"
}</code></pre>

<h3>5. Get Active Consents</h3>

<p><strong>Endpoint:</strong> <code>GET /api/consent/active</code></p>

<p><strong>Description:</strong> Retrieve all currently active consents for the authenticated user.</p>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X GET "https://your-domain.com/api/consent/active" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json"</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "data": [
    {
      "id": 1,
      "consent_type": "necessary",
      "purpose": "Essential website functionality",
      "given_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 3,
      "consent_type": "analytics",
      "purpose": "Website usage analytics",
      "given_at": "2024-01-15T10:35:00.000000Z"
    }
  ]
}</code></pre>

<hr>

<h2>📊 Data Retention Policy API</h2>

<p>Manage data retention policies and monitor expired records.</p>

<h3>1. Get All Retention Policies</h3>

<p><strong>Endpoint:</strong> <code>GET /api/retention-policies</code></p>

<p><strong>Description:</strong> Retrieve all data retention policies.</p>

<p><strong>Query Parameters:</strong></p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>model_type</code></td>
<td>string</td>
<td>No</td>
<td>Filter by model type</td>
</tr>
<tr>
<td><code>active_only</code></td>
<td>boolean</td>
<td>No</td>
<td>Return only active policies (default: false)</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X GET "https://your-domain.com/api/retention-policies?active_only=true" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json"</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "data": [
    {
      "id": 1,
      "model_type": "App\\Models\\AuditLog",
      "retention_period_days": 365,
      "delete_after_days": 730,
      "conditions": {},
      "is_active": true,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}</code></pre>

<h3>2. Create Retention Policy</h3>

<p><strong>Endpoint:</strong> <code>POST /api/retention-policies</code></p>

<p><strong>Description:</strong> Create a new data retention policy.</p>

<p><strong>Request Body:</strong></p>
<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>model_type</code></td>
<td>string</td>
<td>Yes</td>
<td>Fully qualified model class name</td>
</tr>
<tr>
<td><code>retention_period_days</code></td>
<td>integer</td>
<td>Yes</td>
<td>Days before records are considered expired (min: 1)</td>
</tr>
<tr>
<td><code>delete_after_days</code></td>
<td>integer</td>
<td>Yes</td>
<td>Days before records should be deleted (min: 1)</td>
</tr>
<tr>
<td><code>conditions</code></td>
<td>object</td>
<td>No</td>
<td>Conditions for policy application</td>
</tr>
<tr>
<td><code>is_active</code></td>
<td>boolean</td>
<td>No</td>
<td>Whether policy is active (default: true)</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X POST "https://your-domain.com/api/retention-policies" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "model_type": "App\\Models\\AuditLog",
    "retention_period_days": 365,
    "delete_after_days": 730,
    "conditions": {},
    "is_active": true
  }'</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "message": "Retention policy created successfully",
  "data": {
    "id": 2,
    "model_type": "App\\Models\\AuditLog",
    "retention_period_days": 365,
    "delete_after_days": 730,
    "conditions": {},
    "is_active": true,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}</code></pre>

<h3>3. Apply Retention Policies</h3>

<p><strong>Endpoint:</strong> <code>POST /api/retention-policies/apply</code></p>

<p><strong>Description:</strong> Apply retention policies to delete expired data.</p>

<p><strong>Query Parameters:</strong></p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>dry_run</code></td>
<td>boolean</td>
<td>No</td>
<td>Preview without deleting (default: true)</td>
</tr>
<tr>
<td><code>model_type</code></td>
<td>string</td>
<td>No</td>
<td>Filter by model type</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X POST "https://your-domain.com/api/retention-policies/apply?dry_run=false" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json"</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "data": {
    "dry_run": false,
    "policies_applied": 3,
    "total_deleted": 125,
    "total_anonymized": 45,
    "results": [
      {
        "policy_id": 1,
        "model_type": "App\\Models\\AuditLog",
        "deleted": 125,
        "anonymized": 0
      }
    ]
  }
}</code></pre>

<hr>

<h2>🗑️ Data Deletion Request API</h2>

<p>Manage GDPR right-to-erasure requests and data exports.</p>

<h3>1. Get All Deletion Requests</h3>

<p><strong>Endpoint:</strong> <code>GET /api/deletion-requests</code></p>

<p><strong>Description:</strong> Retrieve all data deletion requests.</p>

<p><strong>Query Parameters:</strong></p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>status</code></td>
<td>string</td>
<td>No</td>
<td>Filter by status (pending, processing, completed, failed, cancelled)</td>
</tr>
<tr>
<td><code>user_id</code></td>
<td>integer</td>
<td>No</td>
<td>Filter by user ID</td>
</tr>
<tr>
<td><code>email</code></td>
<td>string</td>
<td>No</td>
<td>Filter by email</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X GET "https://your-domain.com/api/deletion-requests?status=pending" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json"</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "email": "user@example.com",
      "requested_at": "2024-01-15T10:30:00.000000Z",
      "processed_at": null,
      "status": "pending",
      "notes": "User requested data deletion",
      "processed_by": null,
      "user": {
        "id": 123,
        "name": "John Doe"
      },
      "processedBy": null
    }
  ]
}</code></pre>

<h3>2. Create Deletion Request</h3>

<p><strong>Endpoint:</strong> <code>POST /api/deletion-requests</code></p>

<p><strong>Description:</strong> Create a new data deletion request.</p>

<p><strong>Request Body:</strong></p>
<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>user_id</code></td>
<td>integer</td>
<td>No</td>
<td>User ID (defaults to authenticated user)</td>
</tr>
<tr>
<td><code>email</code></td>
<td>string</td>
<td>No</td>
<td>Email address for the request</td>
</tr>
<tr>
<td><code>notes</code></td>
<td>string</td>
<td>No</td>
<td>Additional notes (max 1000 chars)</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X POST "https://your-domain.com/api/deletion-requests" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "notes": "User requested data deletion via mobile app"
  }'</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "message": "Deletion request created successfully",
  "data": {
    "id": 2,
    "user_id": 123,
    "email": "user@example.com",
    "requested_at": "2024-01-15T10:30:00.000000Z",
    "status": "pending",
    "notes": "User requested data deletion via mobile app"
  }
}</code></pre>

<h3>3. Process Deletion Request</h3>

<p><strong>Endpoint:</strong> <code>POST /api/deletion-requests/{id}/process</code></p>

<p><strong>Description:</strong> Process a pending deletion request.</p>

<p><strong>Path Parameters:</strong></p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>id</code></td>
<td>integer</td>
<td>Yes</td>
<td>Request ID</td>
</tr>
</tbody>
</table>

<p><strong>Request Body:</strong></p>
<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>force</code></td>
<td>boolean</td>
<td>No</td>
<td>Force deletion instead of anonymization (default: false)</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X POST "https://your-domain.com/api/deletion-requests/1/process" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "force": false
  }'</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "message": "Deletion request processed successfully",
  "data": {
    "request_id": 1,
    "status": "completed",
    "method": "anonymized",
    "processed_at": "2024-01-16T14:20:00.000000Z",
    "affected_models": {
      "user": 1,
      "consents": 3,
      "tickets": 5
    }
  }
}</code></pre>

<h3>4. Export User Data</h3>

<p><strong>Endpoint:</strong> <code>POST /api/deletion-requests/export</code></p>

<p><strong>Description:</strong> Export user data for GDPR data portability.</p>

<p><strong>Request Body:</strong></p>
<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>user_id</code></td>
<td>integer</td>
<td>No</td>
<td>User ID (defaults to authenticated user)</td>
</tr>
<tr>
<td><code>format</code></td>
<td>string</td>
<td>No</td>
<td>Export format: json, csv, pdf (default: json)</td>
</tr>
<tr>
<td><code>include_audit_logs</code></td>
<td>boolean</td>
<td>No</td>
<td>Include audit logs in export (default: false)</td>
</tr>
<tr>
<td><code>async</code></td>
<td>boolean</td>
<td>No</td>
<td>Queue export job asynchronously (default: false)</td>
</tr>
</tbody>
</table>

<p><strong>Example Request (Synchronous):</strong></p>
<pre><code>curl -X POST "https://your-domain.com/api/deletion-requests/export" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "format": "json",
    "include_audit_logs": true,
    "async": false
  }'</code></pre>

<p><strong>Example Response (Synchronous):</strong></p>
<pre><code>{
  "success": true,
  "message": "User data exported successfully",
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "consents": [
      {
        "type": "marketing",
        "given_at": "2024-01-15T10:30:00.000000Z"
      }
    ],
    "tickets": [
      {
        "id": 1,
        "subject": "Support Request"
      }
    ],
    "audit_logs": [
      {
        "event": "created",
        "created_at": "2024-01-15T10:30:00.000000Z"
      }
    ]
  }
}</code></pre>

<hr>

<h2>📈 Compliance Reporting API</h2>

<p>Access compliance metrics, audit reports, and compliance status.</p>

<h3>1. Get Compliance Overview</h3>

<p><strong>Endpoint:</strong> <code>GET /api/compliance/metrics/overview</code></p>

<p><strong>Description:</strong> Get comprehensive compliance metrics overview.</p>

<p><strong>Query Parameters:</strong></p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>start_date</code></td>
<td>date</td>
<td>No</td>
<td>Filter from date (YYYY-MM-DD)</td>
</tr>
<tr>
<td><code>end_date</code></td>
<td>date</td>
<td>No</td>
<td>Filter to date (YYYY-MM-DD)</td>
</tr>
</tbody>
</table>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X GET "https://your-domain.com/api/compliance/metrics/overview?start_date=2024-01-01&end_date=2024-01-31" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json"</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "data": {
    "period": {
      "start_date": "2024-01-01",
      "end_date": "2024-01-31"
    },
    "audit_logging": {
      "total_logs": 15234,
      "events": {
        "created": 8923,
        "updated": 4532,
        "deleted": 1779
      }
    },
    "consent_management": {
      "total_consents": 3456,
      "active_consents": 2890,
      "consent_rate": 83.6
    },
    "data_retention": {
      "active_policies": 4,
      "expired_records": 456,
      "deletable_records": 234
    },
    "encryption": {
      "encrypted_models": 2,
      "encrypted_fields": 4
    },
    "compliance_status": {
      "gdpr": "compliant",
      "hipaa": "compliant",
      "soc2": "compliant"
    }
  }
}</code></pre>

<h3>2. Get Compliance Status</h3>

<p><strong>Endpoint:</strong> <code>GET /api/compliance/status</code></p>

<p><strong>Description:</strong> Get overall compliance status with issues and warnings.</p>

<p><strong>Example Request:</strong></p>
<pre><code>curl -X GET "https://your-domain.com/api/compliance/status" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Accept: application/json"</code></pre>

<p><strong>Example Response:</strong></p>
<pre><code>{
  "success": true,
  "data": {
    "overall_status": "compliant",
    "frameworks": {
      "gdpr": {
        "status": "compliant",
        "issues": [],
        "warnings": []
      },
      "hipaa": {
        "status": "compliant",
        "issues": [],
        "warnings": ["Field encryption not enabled"]
      },
      "soc2": {
        "status": "non_compliant",
        "issues": ["Audit logging disabled"],
        "warnings": []
      }
    },
    "features": {
      "audit_logging": true,
      "consent_management": true,
      "data_retention": true,
      "field_encryption": false
    }
  }
}</code></pre>

<h3>3. Generate Audit Report</h3>

<p><strong>Endpoint:</strong> <code>POST /api/compliance/reports/audit/generate</code></p>

<p><strong>Description:</strong> Generate an audit report in the specified format.</p>

<p><strong>Request Body:</strong></p>
<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>format</code></td>
<td>string</td>
<td>Yes</td>
<td>Report format: csv, json, pdf</td>
</tr>
<tr>
<td><code>start_date</code></td>
<td>date</td>
<td>No</td>
<td>Filter from date (YYYY-MM-DD)</td>
</tr>
<tr>
<td><code>end_date</code></td>
<td>date</td>
<td>No</td>
<td>Filter to date (YYYY-MM-DD)</td>
</tr>
<tr>
<td><code>event</code></td>
<td>string</td>
<td>No</td>
<td>Filter by event type</td>
</tr>
<tr>
<td><code>limit</code></td>
<td>integer</td>
<td>No</td>
<td>Max records (1-10000)</td>
</tr>
<tr>
<td><code>include_statistics</code></td>
<td>boolean</td>
<td>No</td>
<td>Include summary statistics (default: false)</td>
</tr>
</tbody>
</table>

<p><strong>Example Request (JSON):</strong></p>
<pre><code>curl -X POST "https://your-domain.com/api/compliance/reports/audit/generate" \\
  -H "Authorization: Bearer YOUR_API_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "format": "json",
    "start_date": "2024-01-01",
    "end_date": "2024-01-31",
    "event": "created",
    "limit": 100,
    "include_statistics": true
  }'</code></pre>

<p><strong>Example Response (JSON):</strong></p>
<pre><code>{
  "success": true,
  "format": "json",
  "data": {
    "metadata": {
      "title": "Audit Report",
      "generated_at": "2024-01-15T10:30:00.000000Z",
      "filters": {
        "event": "created",
        "start_date": "2024-01-01",
        "end_date": "2024-01-31"
      }
    },
    "statistics": {
      "total_records": 100,
      "events": {
        "created": 100
      }
    },
    "records": [
      {
        "id": 1,
        "event": "created",
        "auditable_type": "App\\Models\\User",
        "auditable_id": 123,
        "user_id": 456,
        "created_at": "2024-01-15T10:30:00.000000Z"
      }
    ]
  }
}</code></pre>

<hr>

<h2>🔧 Configuration</h2>

<h3>Environment Variables</h3>

<p>Add these to your <code>.env</code> file to configure compliance features:</p>

<pre><code># Compliance Features
COMPLIANCE_ENABLED=true

# Audit Logging
AUDIT_LOGGING_ENABLED=true
AUDIT_LOG_RETENTION_DAYS=365

# Consent Management
CONSENT_MANAGEMENT_ENABLED=true
CONSENT_CAPTURE_IP=true
CONSENT_CAPTURE_USER_AGENT=true

# Data Retention
DATA_RETENTION_ENABLED=true
DATA_RETENTION_AUTO_DELETE=false
DATA_RETENTION_PREFER_ANONYMIZATION=true

# Field Encryption
FIELD_ENCRYPTION_ENABLED=true
FIELD_ENCRYPTION_AUTO_DECRYPT=true

# GDPR Right to Erasure
GDPR_ENABLED=true
GDPR_ANONYMIZE_INSTEAD_OF_DELETE=true
GDPR_SEND_CONFIRMATION_EMAIL=true

# Compliance Reporting
COMPLIANCE_REPORTING_ENABLED=true</code></pre>

<hr>

<h2>🛡️ Security Best Practices</h2>

<h3>1. API Authentication</h3>
<ul>
<li>Always use secure API tokens</li>
<li>Rotate tokens regularly</li>
<li>Use HTTPS for all API requests</li>
<li>Never expose tokens in client-side code</li>
</ul>

<h3>2. Rate Limiting</h3>
<p>API endpoints are subject to rate limiting. Default limits:</p>
<ul>
<li>60 requests per minute per user</li>
<li>1000 requests per hour per user</li>
</ul>

<h3>3. Data Privacy</h3>
<ul>
<li>Only authorized users can access their own consent records</li>
<li>Admin privileges required for accessing other users' data</li>
<li>Deletion requests are logged in audit trail</li>
<li>Exported data should be transmitted securely</li>
</ul>

<div class="callout-info">
<strong>Last Updated:</strong> January 2024
<strong>API Version:</strong> 1.0.0
</div>
HTML;
    }
}
