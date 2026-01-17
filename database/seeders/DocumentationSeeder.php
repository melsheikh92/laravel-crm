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
        $this->createFeatureGuidesArticles();
        $this->createApiDocumentationArticles();
        $this->createTroubleshootingArticles();

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
     * Create Feature Guides articles.
     *
     * @return void
     */
    protected function createFeatureGuidesArticles()
    {
        $featuresCategory = DB::table('doc_categories')
            ->where('slug', 'features')
            ->first();

        if (!$featuresCategory) {
            $this->command->error('Feature Guides category not found!');
            return;
        }

        $articles = [
            [
                'title' => 'Leads Management Complete Guide',
                'slug' => 'leads-management',
                'content' => $this->getLeadsManagementContent(),
                'excerpt' => 'Master lead management from creation to conversion. Learn pipeline stages, lead scoring, qualification, and best practices for closing deals.',
                'type' => 'feature-guide',
                'difficulty_level' => 'beginner',
                'reading_time_minutes' => 15,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Contacts Management Complete Guide',
                'slug' => 'contacts-management',
                'content' => $this->getContactsManagementContent(),
                'excerpt' => 'Organize and manage your contact database effectively. Learn about contact relationships, tagging, segmentation, and data management best practices.',
                'type' => 'feature-guide',
                'difficulty_level' => 'beginner',
                'reading_time_minutes' => 12,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Products Management Complete Guide',
                'slug' => 'products-management',
                'content' => $this->getProductsManagementContent(),
                'excerpt' => 'Manage your product catalog and services. Learn about product types, pricing, inventory, and how to link products to opportunities.',
                'type' => 'feature-guide',
                'difficulty_level' => 'intermediate',
                'reading_time_minutes' => 10,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($articles as $article) {
            $existingArticle = DB::table('doc_articles')
                ->where('slug', $article['slug'])
                ->first();

            if (!$existingArticle) {
                $articleId = DB::table('doc_articles')->insertGetId(array_merge($article, [
                    'category_id' => $featuresCategory->id,
                    'author_id' => 1,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                $this->command->info("Created feature article: {$article['title']}");
            }
        }
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
                'type' => 'api-doc',
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

    /**
     * Content for Leads Management feature guide.
     */
    protected function getLeadsManagementContent()
    {
        return <<<HTML
<h1>Leads Management Complete Guide</h1>

<h2>Table of Contents</h2>
<ol>
<li><a href="#introduction">Introduction</a></li>
<li><a href="#understanding-leads">Understanding Leads</a></li>
<li><a href="#creating-leads">Creating Leads</a></li>
<li><a href="#pipeline-management">Pipeline Management</a></li>
<li><a href="#lead-qualification">Lead Qualification</a></li>
<li><a href="#lead-scoring">Lead Scoring</a></li>
<li><a href="#converting-leads">Converting Leads</a></li>
<li><a href="#best-practices">Best Practices</a></li>
<li><a href="#troubleshooting">Troubleshooting</a></li>
</ol>

<hr>

<h2 id="introduction">Introduction</h2>

<p>Leads Management is the core of your sales process. This guide covers everything you need to know about managing leads from initial capture through to conversion into customers.</p>

<h3>Key Features</h3>
<ul>
<li><strong>Lead Capture:</strong> Multiple ways to add leads to your CRM</li>
<li><strong>Pipeline Stages:</strong> Visual sales pipeline with customizable stages</li>
<li><strong>Lead Scoring:</strong> AI-powered scoring to prioritize opportunities</li>
<li><strong>Qualification:</strong> Structured qualification criteria</li>
<li><strong>Activity Tracking:</strong> Log all interactions and communications</li>
<li><strong>Conversion:</strong> Convert qualified leads to customers</li>
</ul>

<h3>Use Cases</h3>
<ul>
<li><strong>Sales Reps:</strong> Manage your pipeline and close deals</li>
<li><strong>Sales Managers:</strong> Track team performance and pipeline health</li>
<li><strong>Marketing:</strong> Pass qualified leads to sales</li>
<li><strong>Executives:</strong> Monitor revenue forecasts and sales metrics</li>
</ul>

<hr>

<h2 id="understanding-leads">Understanding Leads</h2>

<h3>What is a Lead?</h3>

<p>A lead represents a potential sales opportunity - a person or organization that has shown interest in your products or services and could become a customer.</p>

<h4>Lead vs. Contact vs. Customer</h4>

<table>
<thead>
<tr>
<th>Type</th>
<th>Definition</th>
<th>Stage</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Lead</strong></td>
<td>Potential opportunity being qualified</td>
<td>Early stage</td>
</tr>
<tr>
<td><strong>Contact</strong></td>
<td>Person in your database (could be customer, partner, etc.)</td>
<td>Any stage</td>
</tr>
<tr>
<td><strong>Customer</strong></td>
<td>Purchased your product/service</td>
<td>Closed stage</td>
</tr>
</tbody>
</table>

<h3>Lead Lifecycle</h3>

<ol>
<li><strong>Capture:</strong> Lead enters your system</li>
<li><strong>Qualification:</strong> Determine if lead is a good fit</li>
<li><strong>Nurturing:</strong> Build relationship and provide value</li>
<li><strong>Proposal:</strong> Present solution and pricing</li>
<li><strong>Negotiation:</strong> Work through objections and terms</li>
<li><strong>Closing:</strong> Convert to customer or disqualify</li>
</ol>

<hr>

<h2 id="creating-leads">Creating Leads</h2>

<h3>Method 1: Manual Creation</h3>

<ol>
<li>Navigate to <strong>Leads > All Leads</strong></li>
<li>Click <strong>+ Add Lead</strong> button</li>
<li>Fill in required fields:
<ul>
<li>First Name, Last Name</li>
<li>Email or Phone</li>
<li>Company Name</li>
<li>Lead Source</li>
</ul>
</li>
<li>Add optional details:
<ul>
<li>Title, Website</li>
<li>Estimated Value</li>
<li>Expected Close Date</li>
<li>Assigned To</li>
</ul>
</li>
<li>Click <strong>Save</strong></li>
</ol>

<h3>Method 2: Quick Create</h3>

<ol>
<li>Click <strong>+ Create</strong> in top navigation</li>
<li>Select <strong>Lead</strong></li>
<li>Fill in essential fields</li>
<li>Click <strong>Save</strong></li>
</ol>

<h3>Method 3: Import from CSV</h3>

<ol>
<li>Go to <strong>Leads > Import</strong></li>
<li>Download CSV template</li>
<li>Fill in lead data</li>
<li>Upload CSV file</li>
<li>Map columns to lead fields</li>
<li>Review and confirm import</li>
</ol>

<h3>Method 4: Web Forms</h3>

<p>Leads can be automatically created from:</p>
<ul>
<li>Website contact forms</li>
<li>Landing page submissions</li>
<li>Email integration</li>
<li>API integrations</li>
</ul>

<h3>Required vs. Optional Fields</h3>

<p><strong>Required Fields:</strong></p>
<ul>
<li>Contact Name or Company Name</li>
<li>Email or Phone Number</li>
<li>Lead Source</li>
</ul>

<p><strong>Optional but Recommended:</strong></p>
<ul>
<li>Estimated Deal Value</li>
<li>Expected Close Date</li>
<li>Industry</li>
<li>Assigned Sales Rep</li>
<li>Tags/Labels</li>
</ul>

<hr>

<h2 id="pipeline-management">Pipeline Management</h2>

<h3>Understanding the Sales Pipeline</h3>

<p>The sales pipeline is a visual representation of your sales process. Each lead moves through stages from initial contact to closing.</p>

<h4>Default Pipeline Stages</h4>

<table>
<thead>
<tr>
<th>Stage</th>
<th>Description</th>
<th>Probability</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>New</strong></td>
<td>Lead just entered the system</td>
<td>10%</td>
</tr>
<tr>
<td><strong>Contacted</strong></td>
<td>Initial outreach completed</td>
<td>20%</td>
</tr>
<tr>
<td><strong>Qualified</strong></td>
<td>Lead meets qualification criteria</td>
<td>40%</td>
</tr>
<tr>
<td><strong>Proposal</strong></td>
<td>Proposal sent to prospect</td>
<td>60%</td>
</tr>
<tr>
<td><strong>Negotiation</strong></td>
<td>Terms being discussed</td>
<td>80%</td>
</tr>
<tr>
<td><strong>Closed Won</strong></td>
<td>Deal successfully closed</td>
<td>100%</td>
</tr>
<tr>
<td><strong>Closed Lost</strong></td>
<td>Deal not successful</td>
<td>0%</td>
</tr>
</tbody>
</table>

<h3>Moving Leads Through Stages</h3>

<h4>Method 1: Drag and Drop (Kanban View)</h4>

<ol>
<li>Go to <strong>Leads > Pipeline</strong></li>
<li>Drag lead card to next stage</li>
<li>Confirm stage change</li>
</ol>

<h4>Method 2: Lead Detail Page</h4>

<ol>
<li>Open lead record</li>
<li>Click <strong>Change Stage</strong> button</li>
<li>Select new stage</li>
<li>Add notes about stage change</li>
<li>Click <strong>Save</strong></li>
</ol>

<h4>Method 3: Bulk Update</h4>

<ol>
<li>Select multiple leads from list view</li>
<li>Click <strong>Bulk Actions > Change Stage</strong></li>
<li>Select target stage</li>
<li>Confirm update</li>
</ol>

<h3>Customizing Pipeline Stages</h3>

<p>Administrators can customize stages:</p>

<ol>
<li>Go to <strong>Settings > Pipeline</strong></li>
<li>Add, edit, or remove stages</li>
<li>Set probability percentages</li>
<li>Define stage criteria</li>
<li>Reorder stages as needed</li>
</ol>

<div class="callout-info">
<strong>Tip:</strong> Keep your pipeline simple (5-7 stages max) for better adoption and clearer forecasting.
</div>

<h3>Pipeline Views</h3>

<h4>Kanban View</h4>
<ul>
<li>Visual cards arranged by stage</li>
<li>Drag and drop to move leads</li>
<li>Shows key info at a glance</li>
</ul>

<h4>List View</h4>
<ul>
<li>Table format with all details</li>
<li>Sortable columns</li>
<li>Bulk actions available</li>
</ul>

<h4>Calendar View</h4>
<ul>
<li>Leads plotted by expected close date</li>
<li>Visual timeline of upcoming closes</li>
</ul>

<hr>

<h2 id="lead-qualification">Lead Qualification</h2>

<h3>Why Qualify Leads?</h3>

<p>Not all leads are worth pursuing. Qualification helps you focus on leads most likely to buy, saving time and increasing close rates.</p>

<h3>BANT Qualification Framework</h3>

<p>Use the BANT framework to qualify leads:</p>

<h4>1. Budget</h4>
<p>Does the prospect have budget available?</p>
<ul>
<li>What is their budget range?</li>
<li>When is budget available?</li>
<li>Who controls the budget?</li>
</ul>

<h4>2. Authority</h4>
<p>Are you talking to the decision-maker?</p>
<ul>
<li>Can they make the purchase decision?</li>
<li>Do they need approval from others?</li>
<li>Who else needs to be involved?</li>
</ul>

<h4>3. Need</h4>
<p>Do they have a pain point we can solve?</p>
<ul>
<li>What problem are they trying to solve?</li>
<li>What are their current challenges?</li>
<li>Why do they need a solution now?</li>
</ul>

<h4>4. Timeline</h4>
<p>When are they looking to purchase?</p>
<ul>
<li>What is their decision timeline?</li>
<li>Any events driving the timeline?</li>
<li>Is the timeline realistic?</li>
</ul>

<h3>Qualification Questions</h3>

<p><strong>Discovery Questions to Ask:</strong></p>

<ul>
<li>"Tell me about your current situation..."</li>
<li>"What challenges are you facing?"</li>
<li>"What prompted you to look for a solution now?"</li>
<li>"What does success look like for you?"</li>
<li>"What's your budget for this project?"</li>
<li>"Who else will be involved in the decision?"</li>
<li>"What's your timeline for implementation?"</li>
</ul>

<h3>Setting Lead Qualification Status</h3>

<ol>
<li>Open lead record</li>
<li>Scroll to <strong>Qualification</strong> section</li>
<li>Select qualification status:
<ul>
<li>Not Qualified</li>
<li>Qualified</li>
<li>Highly Qualified</li>
</ul>
</li>
<li>Add qualification notes</li>
<li>Save changes</li>
</ol>

<h3>Disqualifying Leads</h3>

<p>Don't be afraid to disqualify leads. Common reasons:</p>

<ul>
<li><strong>No Budget:</strong> Can't afford your solution</li>
<li><strong>No Need:</strong> Problem isn't significant enough</li>
<li><strong>No Timeline:</strong> Not looking to buy in reasonable timeframe</li>
<li><strong>Bad Fit:</strong> Not your ideal customer profile</li>
<li><strong>Ghosted:</strong>Unresponsive after multiple attempts</li>
</ul>

<p>To disqualify:</p>
<ol>
<li>Open lead</li>
<li>Change stage to <strong>Closed Lost</strong></li>
<li>Select reason for disqualification</li>
<li>Add notes for future reference</li>
</ol>

<hr>

<h2 id="lead-scoring">Lead Scoring</h2>

<h3>What is Lead Scoring?</h3>

<p>Lead scoring automatically assigns a score (0-100) to each lead based on how likely they are to buy. Higher scores indicate hotter leads worth prioritizing.</p>

<h3>How Scoring Works</h3>

<p>The system analyzes multiple factors:</p>

<h4>Engagement (30% weight)</h4>
<ul>
<li>Email opens and clicks</li>
<li>Website visits</li>
<li>Meeting attendance</li>
<li>Response rate</li>
</ul>

<h4>Demographics (25% weight)</h4>
<ul>
<li>Job title relevance</li>
<li>Company size</li>
<li>Industry fit</li>
<li>Location</li>
</ul>

<h4>Behavior (25% weight)</h4>
<ul>
<li>Content downloads</li>
<li>Product page views</li>
<li>Pricing page visits</li>
<li>Trial signups</li>
</ul>

<h4>Timing (10% weight)</h4>
<ul>
<li>Expected close date</li>
<li>Urgency indicators</li>
</ul>

<h4>Social Proof (10% weight)</h4>
<ul>
<li>Referral source</li>
<li>Company recognition</li>
</ul>

<h3>Interpreting Lead Scores</h3>

<table>
<thead>
<tr>
<th>Score Range</th>
<th>Priority</th>
<th>Recommended Action</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>80-100</strong></td>
<td>Hot Lead</td>
<td>Immediate follow-up, high priority</td>
</tr>
<tr>
<td><strong>60-79</strong></td>
<td>Warm Lead</td>
<td>Active nurturing, regular contact</td>
</tr>
<tr>
<td><strong>40-59</strong></td>
<td>Cool Lead</td>
<td>Monitor and nurture occasionally</td>
</tr>
<tr>
<td><strong>0-39</strong></td>
<td>Cold Lead</td>
<td>Consider disqualifying</td>
</tr>
</tbody>
</table>

<h3>Viewing Lead Scores</h3>

<p><strong>On Lead Detail Page:</strong></p>
<ul>
<li>Score badge shows in header</li>
<li>Click for detailed breakdown</li>
<li>See score trend over time</li>
</ul>

<p><strong>In Pipeline View:</strong></p>
<ul>
<li>Score displayed on card</li>
<li>Color-coded (green=hot, yellow=warm, red=cold)</li>
<li>Sort by score to prioritize</li>
</ul>

<h3>Improving Lead Scores</h3>

<p>Activities that increase lead score:</p>

<ul>
<li>Responding to emails quickly</li>
<li>Scheduling meetings/demos</li>
<li>Sharing relevant content</li>
<li>Building multiple stakeholder relationships</li>
<li>Confirming budget and timeline</li>
<li>Moving to next pipeline stage</li>
</ul>

<hr>

<h2 id="converting-leads">Converting Leads</h2>

<h3>When to Convert</h3>

<p>Convert a lead to a customer when:</p>

<ul>
<li>Deal is closed and contract signed</li>
<li>Payment received or committed</li>
<li>Implementation/start date confirmed</li>
</ul>

<h3>Conversion Process</h3>

<ol>
<li>Open lead you want to convert</li>
<li>Ensure all information is complete:
<ul>
<li>Contact details</li>
<li>Deal value</li>
<li>Products/services purchased</li>
<li>Contract terms</li>
</ul>
</li>
<li>Click <strong>Convert to Customer</strong></li>
<li>Verify/create contact record</li>
<li>Create associated account (if B2B)</li>
<li>Add products/services to deal</li>
<li>Set actual close date and value</li>
<li>Click <strong>Confirm Conversion</strong></li>
</ol>

<h3>What Happens During Conversion</h3>

<p>When you convert a lead:</p>

<ul>
<li><strong>Contact Created:</strong> Lead contact information becomes a contact record</li>
<li><strong>Account Created:</strong> Company account is created (if B2B)</li>
<li><strong>Deal Closed:</strong> Lead stage changes to Closed Won</li>
<li><strong>Revenue Recorded:</strong> Deal value added to revenue metrics</li>
<li><strong>Activities Preserved:</strong> All notes and activities remain attached</li>
</ul>

<h3>Post-Conversion Actions</h3>

<p>After converting, remember to:</p>

<ol>
<li>Send welcome email/onboarding info</li>
<li>Assign customer success manager</li>
<li>Schedule implementation kickoff</li>
<li>Create follow-up tasks</li>
<li>Notify internal teams (sales, support, billing)</li>
<li>Update forecasting and reports</li>
</ol>

<hr>

<h2 id="best-practices">Best Practices</h2>

<h3>Data Quality</h3>

<ul>
<li><strong>Complete Required Fields:</strong> Always fill in required information</li>
<li><strong>Regular Updates:</strong> Keep lead information current</li>
<li><strong>Clean Data:</strong> Remove duplicates and bad data monthly</li>
<li><strong>Standardize:</strong> Use consistent naming conventions</li>
</ul>

<h3>Pipeline Management</h3>

<ul>
<li><strong>Move Leads Forward:</strong> Progress leads through stages regularly</li>
<li><strong>Don't Rush:</strong> Only advance when truly qualified</li>
<li><strong>Remove Dead Leads:</strong> Close lost leads that aren't moving</li>
<li><strong>Stage Criteria:</strong> Follow consistent stage definitions</li>
</ul>

<h3>Activity Tracking</h3>

<ul>
<li><strong>Log Everything:</strong> Record all interactions in CRM</li>
<li><strong>Timely Updates:</strong> Log activities while fresh</li>
<li><strong>Be Detailed:</strong> Include context and next steps</li>
<li><strong>Set Tasks:</strong> Create follow-up tasks after interactions</li>
</ul>

<h3>Follow-Up</h3>

<ul>
<li><strong>Speed Matters:</strong> Respond to new leads within 5 minutes</li>
<li><strong>Persistence:</strong> 5+ touchpoints typically needed</li>
<li><strong>Multi-Channel:</strong> Use email, phone, social media</li>
<li><strong>Add Value:</strong> Each interaction should provide value</li>
</ul>

<h3>Qualification</h3>

<ul>
<li><strong>Qualify Early:</strong> Use BANT in first conversation</li>
<li><strong>Be Ruthless:</strong> Disqualify poor fits quickly</li>
<li><strong>Document Reasons:</strong> Note why qualified or disqualified</li>
<li><strong>Focus on Hot Leads:</strong> Prioritize high-scoring opportunities</li>
</ul>

<h3>Forecasting</h3>

<ul>
<li><strong>Realistic Values:</strong> Use accurate deal values</li>
<li><strong>Consistent Stages:</strong> Understand probability of each stage</li>
<li><strong>Close Dates:</strong> Set realistic expected close dates</li>
<li><strong>Update Regularly:</strong> Refresh forecasts weekly</li>
</ul>

<hr>

<h2 id="troubleshooting">Troubleshooting</h2>

<h3>Issue: Pipeline Has Too Many Stalled Leads</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Leads not being qualified properly</li>
<li>Lack of follow-up activity</li>
<li>Unrealistic close dates</li>
<li>Stage definitions unclear</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Review all leads in each stage</li>
<li>Contact leads to re-qualify</li>
<li>Close lost leads that aren't moving</li>
<li>Clarify stage criteria with team</li>
</ul>

<h3>Issue: Low Conversion Rate</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Poor lead quality from marketing</li>
<li>Ineffective qualification process</li>
<li>Sales skills gap</li>
<li>Product-market fit issues</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Review lead sources and quality</li>
<li>Improve qualification criteria</li>
<li>Provide sales training</li>
<li>Analyze won/lost deals for patterns</li>
</ul>

<h3>Issue: Leads Not Moving Through Pipeline</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Lack of activity/follow-up</li>
<li>Unclear next steps</li>
<li>Objections not addressed</li>
<li>Competitor activity</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Increase activity frequency</li>
<li>Set clear next steps after each call</li>
<li>Document and address objections</li>
<li>Ask about competitive solutions</li>
</ul>

<h3>Issue: Duplicate Lead Records</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Multiple imports</li>
<li>Manual data entry errors</li>
<li>Integration creating duplicates</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Use duplicate detection feature</li>
<li>Merge duplicate records</li>
<li>Standardize data entry process</li>
<li>Clean up existing duplicates</li>
</ul>

<div class="callout-success">
<strong>Need More Help?</strong> Check out our other feature guides or contact support for personalized assistance.
</div>

<hr>

<h2>Appendix</h2>

<h3>Keyboard Shortcuts</h3>

<ul>
<li><strong>Ctrl + L:</strong> Create new lead</li>
<li><strong>Ctrl + K:</strong> Quick search</li>
<li><strong>Ctrl + D:</strong> Go to dashboard</li>
<li><strong>ESC:</strong> Close modal/form</li>
</ul>

<h3>Related Features</h3>

<ul>
<li><a href="#lead-scoring">Lead Scoring</a> - AI-powered scoring</li>
<li><a href="#sales-forecasting">Sales Forecasting</a> - Revenue prediction</li>
<li><a href="#contacts-management">Contacts Management</a> - Customer database</li>
<li><a href="#products-management">Products Management</a> - Product catalog</li>
</ul>
HTML;
    }

    /**
     * Content for Contacts Management feature guide.
     */
    protected function getContactsManagementContent()
    {
        return <<<HTML
<h1>Contacts Management Complete Guide</h1>

<h2>Table of Contents</h2>
<ol>
<li><a href="#introduction">Introduction</a></li>
<li><a href="#understanding-contacts">Understanding Contacts</a></li>
<li><a href="#creating-contacts">Creating Contacts</a></li>
<li><a href="#organizing-contacts">Organizing Contacts</a></li>
<li><a href="#contact-relationships">Contact Relationships</a></li>
<li><a href="#importing-exporting">Importing & Exporting</a></li>
<li><a href="#data-maintenance">Data Maintenance</a></li>
<li><a href="#best-practices">Best Practices</a></li>
<li><a href="#troubleshooting">Troubleshooting</a></li>
</ol>

<hr>

<h2 id="introduction">Introduction</h2>

<p>Contacts Management helps you organize all the people and organizations you do business with. This guide covers contact creation, organization, relationships, and data maintenance.</p>

<h3>Key Features</h3>
<ul>
<li><strong>Comprehensive Database:</strong> Store all contact information in one place</li>
<li><strong>Relationships:</strong> Link contacts to accounts, deals, and other contacts</li>
<li><strong>Tagging & Segmentation:</strong> Organize contacts with tags and groups</li>
<li><strong>Activity Tracking:</strong> Log all interactions and communications</li>
<li><strong>Import/Export:</strong> Bulk import and export contact data</li>
<li><strong>Duplicate Management:</strong> Find and merge duplicate records</li>
</ul>

<h3>Use Cases</h3>
<ul>
<li><strong>Sales Teams:</strong> Manage prospects and customers</li>
<li><strong>Marketing:</strong> Segment for campaigns and newsletters</li>
<li><strong>Customer Support:</strong> Access customer history</li>
<li><strong>Partnerships:</strong> Manage vendor and partner relationships</li>
</ul>

<hr>

<h2 id="understanding-contacts">Understanding Contacts</h2>

<h3>What is a Contact?</h3>

<p>A contact is an individual person - a customer, prospect, partner, vendor, or anyone else you interact with in your business.</p>

<h3>Contact vs. Lead vs. Account</h3>

<table>
<thead>
<tr>
<th>Type</th>
<th>Represents</th>
<th>Example</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Contact</strong></td>
<td>Individual person</td>
<td>John Smith</td>
</tr>
<tr>
<td><strong>Lead</strong></td>
<td>Opportunity being worked</td>
<td>Deal with John Smith</td>
</tr>
<tr>
<td><strong>Account</strong></td>
<td>Organization/company</td>
<td>Acme Corporation</td>
</tr>
</tbody>
</table>

<h3>Contact Types</h3>

<p>Common contact types in CRM:</p>

<ul>
<li><strong>Prospects:</strong> Potential customers</li>
<li><strong>Customers:</strong> Current paying customers</li>
<li><strong>Partners:</strong> Business partners and resellers</li>
<li><strong>Vendors:</strong> Suppliers and service providers</li>
<li><strong>Employees:</strong> Internal team members</li>
</ul>

<h3>Contact Information Structure</h3>

<p>Each contact can store:</p>

<h4>Basic Information</h4>
<ul>
<li>Name, Title, Department</li>
<li>Email, Phone, Mobile</li>
<li>Address, City, State, Zip</li>
<li>Photo</li>
</ul>

<h4>Professional Information</h4>
<ul>
<li>Company</li>
<li>Industry</li>
<li>Website, LinkedIn</li>
<li>Background notes</li>
</ul>

<h4>Custom Information</h4>
<ul>
<li>Custom fields (industry-specific)</li>
<li>Tags and labels</li>
<li>Group membership</li>
</ul>

<hr>

<h2 id="creating-contacts">Creating Contacts</h2>

<h3>Method 1: Manual Creation</h3>

<ol>
<li>Go to <strong>Contacts > All Contacts</strong></li>
<li>Click <strong>+ Add Contact</strong></li>
<li>Fill in contact information:
<ul>
<li><strong>Required:</strong> First name, Last name, Email</li>
<li><strong>Optional:</strong> Phone, Company, Title, etc.</li>
</ul>
</li>
<li>Click <strong>Save</strong></li>
</ol>

<h3>Method 2: From Lead Conversion</h3>

<p>Contacts are automatically created when:</p>
<ul>
<li>Converting a lead to customer</li>
<li>Winning a deal</li>
<li>Email integration adds new contacts</li>
</ul>

<h3>Method 3: Import from CSV</h3>

<ol>
<li>Go to <strong>Contacts > Import</strong></li>
<li>Download CSV template</li>
<li>Prepare your data (required columns: First Name, Last Name, Email)</li>
<li>Upload CSV file</li>
<li>Map CSV columns to CRM fields</li>
<li>Choose import options:
<ul>
<li>Create new contacts only</li>
<li>Update existing contacts</li>
<li>Create and update</li>
</ul>
</li>
<li>Review import summary</li>
<li>Confirm import</li>
</ol>

<h3>Method 4: Business Card Scanner</h3>

<ol>
<li>Click <strong>+ Create > Scan Business Card</strong></li>
<li>Upload business card image</li>
<li>System auto-fills contact info</li>
<li>Review and edit extracted data</li>
<li>Save contact</li>
</ol>

<h3>Method 5: Email Integration</h3>

<p>Contacts automatically created from:</p>
<ul>
<li>Email conversations</li>
<li>Calendar invites</li>
<li>Email signatures</li>
</ul>

<h3>Required vs. Optional Fields</h3>

<p><strong>Required Fields:</strong></p>
<ul>
<li>First Name</li>
<li>Last Name</li>
<li>Email Address</li>
</ul>

<p><strong>Optional Fields:</strong></p>
<ul>
<li>Phone, Mobile</li>
<li>Title, Department</li>
<li>Company, Website</li>
<li>Address information</li>
<li>Social media links</li>
<li>Custom fields</li>
</ul>

<div class="callout-info">
<strong>Tip:</strong> Always include at least one communication method (email or phone) to ensure you can reach the contact.
</div>

<hr>

<h2 id="organizing-contacts">Organizing Contacts</h2>

<h3>Using Tags</h3>

<p>Tags are flexible labels for categorizing contacts:</p>

<h4>Creating Tags</h4>
<ol>
<li>Open a contact record</li>
<li>Scroll to Tags section</li>
<li>Click <strong>+ Add Tag</strong></li>
<li>Type tag name and press Enter</li>
<li>Tag is created and applied</li>
</ol>

<h4>Popular Tag Examples</h4>

<p><strong>By Status:</strong></p>
<ul>
<li>#prospect, #customer, #inactive</li>
</ul>

<p><strong>By Priority:</strong></p>
<ul>
<li>#vip, #high-priority, #standard</li>
</ul>

<p><strong>By Source:</strong></p>
<ul>
<li>#referral, #trade-show, #website</li>
</ul>

<p><strong>By Interests:</strong></p>
<ul>
<li>#enterprise, #startup, #non-profit</li>
</ul>

<h4>Filtering by Tags</h4>

<ol>
<li>Go to Contacts list view</li>
<li>Click <strong>Filters</strong></li>
<li>Select tag from dropdown</li>
<li>View only contacts with that tag</li>
</ol>

<h3>Using Groups</h3>

<p>Groups are more structured than tags and can include hierarchy:</p>

<h4>Creating Groups</h4>

<ol>
<li>Go to <strong>Contacts > Groups</strong></li>
<li>Click <strong>+ New Group</strong></li>
<li>Enter group name and description</li>
<li>Set group visibility (public/private)</li>
<li>Save group</li>
</ol>

<h4>Adding Contacts to Groups</h4>

<ol>
<li>Open contact record</li>
<li>Scroll to Groups section</li>
<li>Click <strong>+ Add to Group</strong></li>
<li>Select group(s)</li>
<li>Save changes</li>
</ol>

<h4>Popular Group Examples</h4>

<ul>
<li><strong>Customers by Tier:</strong> Enterprise, Mid-market, SMB</li>
<li><strong>Geographic:</strong> North America, Europe, APAC</li>
<li><strong>Industry:</strong> Healthcare, Finance, Tech</li>
<li><strong>Partners:</strong> Resellers, Integrators, Referral Partners</li>
</ul>

<h3>Custom Fields</h3>

<p>Add industry-specific information:</p>

<ol>
<li>Go to <strong>Settings > Contact Fields</strong></li>
<li>Click <strong>+ Add Field</strong></li>
<li>Select field type:
<ul>
<li>Text, Number, Date</li>
<li>Dropdown, Multi-select</li>
<li>Checkbox, Currency</li>
</ul>
</li>
<li>Enter field label and options</li>
<li>Set field properties (required, unique, etc.)</li>
<li>Save field</li>
</ol>

<h3>Search and Filter</h3>

<h4>Basic Search</h4>
<ul>
<li>Search bar finds contacts by name, email, company</li>
<li>Autocomplete suggests matching contacts</li>
</ul>

<h4>Advanced Filters</h4>

<p>Filter by:</p>
<ul>
<li>Contact type (customer, prospect, etc.)</li>
<li>Tags</li>
<li>Groups</li>
<li>Created date range</li>
<li>Custom field values</li>
</ul>

<p>Combine multiple filters to narrow results.</p>

<h4>Saved Searches</h4>

<p>Save frequently used filters:</p>
<ol>
<li>Apply desired filters</li>
<li>Click <strong>Save as Smart View</strong></li>
<li>Name your view</li>
<li>View appears in sidebar for quick access</li>
</ol>

<hr>

<h2 id="contact-relationships">Contact Relationships</h2>

<h3>Contact to Account</h3>

<p>Link contacts to company accounts:</p>

<ol>
<li>Open contact record</li>
<li>In Account field, search and select company</li>
<li>Contact is now linked to account</li>
<li>View shows all contacts at same account</li>
</ol>

<h3>Contact to Contact</h3>

<p>Link related contacts (e.g., decision-maker, influencer, assistant):</p>

<ol>
<li>Open contact record</li>
<li>Scroll to <strong>Related Contacts</strong></li>
<li>Click <strong>+ Add Relationship</strong></li>
<li>Select related contact</li>
<li>Choose relationship type:
<ul>
<li>Reports To</li>
<li>Assistant</li>
<li>Team Member</li>
<li>Spouse</li>
<li>Custom type</li>
</ul>
</li>
<li>Save relationship</li>
</ol>

<h3>Contact to Deals</h3>

<p>View all deals associated with a contact:</p>

<ul>
<li>Open contact record</li>
<li>Scroll to <strong>Deals</strong> section</li>
<li>See all opportunities linked to this contact</li>
<li>Click deal to view details</li>
</ul>

<h3>Contact to Activities</h3>

<p>All interactions logged on contact:</p>

<ul>
<li>Notes and call logs</li>
<li>Emails sent/received</li>
<li>Meetings and tasks</li>
<li>Documents shared</li>
</ul>

<p>View on contact timeline tab.</p>

<hr>

<h2 id="importing-exporting">Importing & Exporting</h2>

<h3>Importing Contacts</h3>

<h4>Supported File Formats</h4>
<ul>
<li>CSV (Comma Separated Values)</li>
<li>Excel (.xlsx)</li>
<li>vCard (.vcf)</li>
</ul>

<h4>Import Process</h4>

<ol>
<li>Prepare data file with required columns</li>
<li>Go to <strong>Contacts > Import</strong></li>
<li>Upload file</li>
<li>Map columns to CRM fields</li>
<li>Set import preferences:
<ul>
<li>Duplicate handling (skip, update, merge)</li>
<li>Required field validation</li>
<li>Default values</li>
</ul>
</li>
<li>Preview import data</li>
<li>Confirm and run import</li>
<li>Review import results</li>
</ol>

<h4>Import Best Practices</h4>

<ul>
<li>Clean data before importing (remove duplicates)</li>
<li>Standardize data formats (dates, phone numbers)</li>
<li>Use consistent naming conventions</li>
<li>Include email for every contact</li>
<li>Test import with small batch first</li>
</ul>

<h3>Exporting Contacts</h3>

<ol>
<li>Go to <strong>Contacts > All Contacts</strong></li>
<li>Apply filters to export subset (optional)</li>
<li>Click <strong>Export</strong> button</li>
<li>Choose export options:
<ul>
<li><strong>Format:</strong> CSV, Excel, PDF</li>
<li><strong>Fields:</strong> All fields or selected</li>
<li><strong>Records:</strong> All visible or all matching filters</li>
</ul>
</li>
<li>Click <strong>Generate Export</strong></li>
<li>Download file when ready</li>
</ol>

<h4>Export Use Cases</h4>

<ul>
<li>Backup contact data</li>
<li>Email marketing campaigns</li>
<li>Data analysis in spreadsheets</li>
<li>Migration to other systems</li>
</ul>

<hr>

<h2 id="data-maintenance">Data Maintenance</h2>

<h3>Finding Duplicates</h3>

<p>System automatically detects potential duplicates based on:</p>

<ul>
<li>Same email address</li>
<li>Same name + company</li>
<li>Same phone number</li>
</ul>

<h4>View Duplicate Suggestions</h4>

<ol>
<li>Go to <strong>Contacts > All Contacts</strong></li>
<li>Click <strong>Find Duplicates</strong></li>
<li>Review list of potential duplicates</li>
<li>Preview each duplicate pair</li>
</ol>

<h3>Merging Duplicates</h3>

<ol>
<li>Select duplicate records to merge</li>
<li>Click <strong>Merge Contacts</strong></li>
<li>Choose master record (data to keep)</li>
<li>Select which fields to keep from each record</li>
<li>Preview merged contact</li>
<li>Confirm merge</li>
</ol>

<p><strong>Result:</strong></p>
<ul>
<li>One combined contact record</li>
<li>All activities preserved</li>
<li>All relationships maintained</li>
<li>Duplicate record deleted</li>
</ul>

<h3>Data Cleanup</h3>

<h4>Regular Maintenance Tasks</h4>

<ul>
<li><strong>Monthly:</strong> Review and merge duplicates</li>
<li><strong>Quarterly:</strong> Update outdated information</li>
<li><strong>Annually:</strong> Remove inactive contacts</li>
</ul>

<h4>Bulk Updates</h4>

<p>Update multiple contacts at once:</p>

<ol>
<li>Select contacts from list view</li>
<li>Click <strong>Bulk Actions > Edit</strong></li>
<li>Choose field to update</li>
<li>Enter new value</li>
<li>Confirm bulk update</li>
</ol>

<h3>Data Quality Rules</h3>

<p>Set validation rules to ensure data quality:</p>

<ul>
<li>Required fields</li>
<li>Unique fields</li>
<li>Field formats (email, phone)</li>
<li>Picklist values only</li>
</ul>

<hr>

<h2 id="best-practices">Best Practices</h2>

<h3>Data Entry</h3>

<ul>
<li><strong>Complete Information:</strong> Fill out all relevant fields</li>
<li><strong>Standardize:</strong> Use consistent naming and formatting</li>
<li><strong>Validate:</strong> Verify contact info is accurate</li>
<li><strong>Update Regularly:</strong> Keep contact information current</li>
</ul>

<h3>Organization</h3>

<ul>
<li><strong>Use Tags:</strong> Tag every contact with relevant attributes</li>
<li><strong>Create Groups:</strong> Group contacts by meaningful categories</li>
<li><strong>Custom Fields:</strong> Add fields for industry-specific data</li>
<li><strong>Smart Views:</strong> Save frequently used filters</li>
</ul>

<h3>Privacy and Security</h3>

<ul>
<li><strong>Access Control:</strong> Limit who can view sensitive contacts</li>
<li><strong>Data Privacy:</strong> Follow GDPR and privacy regulations</li>
<li><strong>Permissions:</strong> Use roles to control access</li>
<li><strong>Audit Log:</strong> Track who accesses what data</li>
</ul>

<h3>Integration</h3>

<ul>
<li><strong>Email Sync:</strong> Connect email for activity logging</li>
<li><strong>Calendar Integration:</strong> Sync meetings and calls</li>
<li><strong>Social Media:</strong> Link LinkedIn, Twitter profiles</li>
<li><strong>Marketing Automation:</strong> Sync with marketing tools</li>
</ul>

<h3>Team Collaboration</h3>

<ul>
<li><strong>Assign Ownership:</strong> Every contact should have an owner</li>
<li><strong>Share Notes:</strong> Log all interactions for team visibility</li>
<li><strong>@Mentions:</strong> Tag team members in notes</li>
<li><strong>Notifications:</strong> Set alerts for important contacts</li>
</ul>

<hr>

<h2 id="troubleshooting">Troubleshooting</h2>

<h3>Issue: Too Many Duplicate Contacts</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Multiple imports without deduplication</li>
<li>Lack of validation rules</li>
<li>Manual data entry errors</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Run duplicate detection regularly</li>
<li>Merge duplicate records</li>
<li>Implement duplicate prevention rules</li>
<li>Train team on proper data entry</li>
</ul>

<h3>Issue: Outdated Contact Information</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Contacts change jobs/roles</li>
<li>No process for updating data</li>
<li>Lack of regular maintenance</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Schedule quarterly data reviews</li>
<li>Use email validation tools</li>
<li>Implement data enrichment services</li>
<li>Make it easy for contacts to self-update</li>
</ul>

<h3>Issue: Can't Find Contact</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Spelling variations</li>
<li>Search doesn't check all fields</li>
<li>Contact archived or deleted</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Try partial name search</li>
<li>Search by email or phone</li>
<li>Check archived contacts</li>
<li>Use advanced filters</li>
</ul>

<h3>Issue: Import Failed</h3>

<p><strong>Common Causes:</strong></p>
<ul>
<li>Invalid file format</li>
<li>Missing required fields</li>
<li>Incorrect data formats</li>
<li>Duplicate conflicts</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Use CSV template provided</li>
<li>Ensure all required fields populated</li>
<li>Check data formats (dates, emails)</li>
<li>Choose appropriate duplicate handling</li>
</ul>

<div class="callout-success">
<strong>Need More Help?</strong> Check out our other feature guides or contact support for personalized assistance.
</div>

<hr>

<h2>Appendix</h2>

<h3>Keyboard Shortcuts</h3>

<ul>
<li><strong>Ctrl + C:</strong> Create new contact</li>
<li><strong>Ctrl + K:</strong> Quick search</li>
<li><strong>Ctrl + F:</strong> Advanced search</li>
<li><strong>ESC:</strong> Close form</li>
</ul>

<h3>Related Features</h3>

<ul>
<li><a href="#leads-management">Leads Management</a> - Sales opportunities</li>
<li><a href="#products-management">Products Management</a> - Product catalog</li>
<li><a href="#sales-forecasting">Sales Forecasting</a> - Revenue prediction</li>
</ul>
HTML;
    }

    /**
     * Content for Products Management feature guide.
     */
    protected function getProductsManagementContent()
    {
        return <<<HTML
<h1>Products Management Complete Guide</h1>

<h2>Table of Contents</h2>
<ol>
<li><a href="#introduction">Introduction</a></li>
<li><a href="#understanding-products">Understanding Products</a></li>
<li><a href="#creating-products">Creating Products</a></li>
<li><a href="#product-catalog">Product Catalog</a></li>
<li><a href="#pricing">Pricing</a></li>
<li><a href="#inventory-management">Inventory Management</a></li>
<li><a href="#product-bundles">Product Bundles</a></li>
<li><a href="#best-practices">Best Practices</a></li>
<li><a href="#troubleshooting">Troubleshooting</a></li>
</ol>

<hr>

<h2 id="introduction">Introduction</h2>

<p>Products Management helps you organize, price, and track your product catalog and services. This guide covers product creation, pricing, inventory, and linking products to opportunities.</p>

<h3>Key Features</h3>
<ul>
<li><strong>Product Catalog:</strong> Centralized database of products/services</li>
<li><strong>Flexible Pricing:</strong> Standard prices, discounts, and custom pricing</li>
<li><strong>Inventory Tracking:</strong> Monitor stock levels and availability</li>
<li><strong>Product Bundles:</strong> Combine products into packages</li>
<li><strong>Deal Line Items:</strong> Add products to opportunities</li>
<li><strong>Price Books:</strong> Multiple pricing tiers and currencies</li>
</ul>

<h3>Use Cases</h3>

<ul>
<li><strong>Sales Teams:</strong> Quickly add products to deals and quotes</li>
<li><strong>Sales Ops:</strong> Maintain accurate product catalog</li>
<li><strong>Finance:</strong> Track revenue by product line</li>
<li><strong>Inventory Management:</strong> Monitor stock levels</li>
<li><strong>Marketing:</strong> Promote specific products and bundles</li>
</ul>

<hr>

<h2 id="understanding-products">Understanding Products</h2>

<h3>What is a Product?</h3>

<p>A product represents anything you sell - physical goods, digital products, services, or subscriptions. In the CRM, products are added to deals to calculate total value.</p>

<h3>Product Types</h3>

<table>
<thead>
<tr>
<th>Type</th>
<th>Description</th>
<th>Examples</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Physical Product</strong></td>
<td>Tangible goods shipped to customer</td>
<td>Hardware, equipment, merchandise</td>
</tr>
<tr>
<td><strong>Digital Product</strong></td>
<td>Electronic goods delivered online</td>
<li>Software, ebooks, courses</td>
</tr>
<tr>
<td><strong>Service</strong></td>
<td>Professional services rendered</td>
<li>Consulting, training, support</td>
</tr>
<tr>
<td><strong>Subscription</strong></td>
<td>Recurring billing products</td>
<li>SaaS memberships, warranties</td>
</tr>
<tr>
<td><strong>Bundle</strong></td>
<td>Multiple products sold together</td>
<li>Product packages, suites</td>
</tr>
</tbody>
</table>

<h3>Product Information</h3>

<p>Each product stores:</p>

<h4>Basic Information</h4>
<ul>
<li>Product Name</li>
<li>Product Code/SKU</li>
<li>Description</li>
<li>Product Type</li>
</ul>

<h4>Pricing</h4>
<ul>
<li>List Price (standard price)</li>
<li>Cost (internal cost)</li>
<li>Currency</li>
<li>Tax class</li>
</ul>

<h4>Inventory (Physical Products)</h4>
<ul>
<li>Quantity in stock</li>
<li>Reorder level</li>
<li>Reorder quantity</li>
</ul>

<h4>Additional Details</h4>
<ul>
<li>Product family/category</li>
<li>Active status</li>
<li>Custom fields</li>
<li>Attachments (spec sheets, images)</li>
</ul>

<hr>

<h2 id="creating-products">Creating Products</h2>

<h3>Creating a Single Product</h3>

<ol>
<li>Go to <strong>Products > All Products</strong></li>
<li>Click <strong>+ Add Product</strong></li>
<li>Fill in product information:
<ul>
<li><strong>Required:</strong> Name, Product Code</li>
<li><strong>Optional:</strong> Description, Price, Cost</li>
</ul>
</li>
<li>Select product type</li>
<li>Set pricing and inventory (if applicable)</li>
<li>Click <strong>Save</strong></li>
</ol>

<h3>Product Fields Explained</h3>

<h4>Basic Information</h4>

<ul>
<li><strong>Product Name:</strong> Descriptive name (visible to customers)</li>
<li><strong>Product Code:</strong> Unique identifier (SKU)</li>
<li><strong>Description:</strong> Detailed product information</li>
<li><strong>Product Family:</strong> Category/grouping</li>
</ul>

<h4>Pricing Information</h4>

<ul>
<li><strong>List Price:</strong> Standard selling price</li>
<li><strong>Cost:</strong> Internal cost (for margin calculation)</li>
<li><strong>Currency:</strong> Price currency</li>
<li><strong>Taxable:</strong> Whether product is taxable</li>
</ul>

<h4>Inventory (Optional)</h4>

<ul>
<li><strong>Quantity:</strong> Current stock on hand</li>
<li><strong>Reorder Level:</strong> Minimum stock before reorder</li>
<li><strong>Reorder Quantity:</strong> Quantity to reorder</li>
<li><strong>Track Inventory:</strong> Enable/disable stock tracking</li>
</ul>

<h3>Creating Products in Bulk</h3>

<h4>Import from CSV</h4>

<ol>
<li>Go to <strong>Products > Import</strong></li>
<li>Download CSV template</li>
<li>Fill in product data</li>
<li>Upload CSV file</li>
<li>Map columns to product fields</li>
<li>Review and confirm import</li>
</ol>

<h4>CSV Template Fields</h4>

<ul>
<li>product_name (required)</li>
<li>product_code (required, unique)</li>
<li>description</li>
<li>product_type</li>
<li>list_price</li>
<li>cost</li>
<li>quantity</li>
<li>active</li>
</ul>

<h3>Cloning Products</h3>

<p>Quickly create similar products:</p>

<ol>
<li>Open existing product</li>
<li>Click <strong>Clone</strong></li>
<li>Update unique fields (name, code)</li>
<li>Modify other fields as needed</li>
<li>Save as new product</li>
</ol>

<div class="callout-info">
<strong>Tip:</strong> Use clone to create product variants (different sizes, colors, etc.) with same base configuration.
</div>

<hr>

<h2 id="product-catalog">Product Catalog</h2>

<h3>Viewing Products</h3>

<h4>List View</h4>
<ul>
<li>Table format with all products</li>
<li>Sortable columns</li>
<li>Quick edit from list</li>
</ul>

<h4>Card View</h4>
<ul>
<li>Visual cards with product image</li>
<li>Key info at a glance</li>
<li>Drag and drop to reorder</li>
</ul>

<h3>Filtering Products</h3>

<p>Filter by:</p>

<ul>
<li><strong>Product Family:</strong> Category or type</li>
<li><strong>Product Type:</strong> Physical, digital, service, etc.</li>
<li><strong>Status:</strong> Active or inactive</li>
<li><strong>Price Range:</strong> Min/max price</li>
<li><strong>Stock Level:</strong> In stock or out of stock</li>
</ul>

<h3>Product Families</h3>

<p>Organize products into categories:</p>

<ol>
<li>Go to <strong>Products > Product Families</strong></li>
<li>Click <strong>+ New Family</strong></li>
<li>Enter family name and description</li>
<li>Save family</li>
<li>Assign products to family</li>
</ol>

<h4>Common Product Families</h4>

<ul>
<li>By Product Line: Software, Hardware, Services</li>
<li>By Category: Office, Field, Home</li>
<li>By Tier: Basic, Professional, Enterprise</li>
<li>By Industry: Healthcare, Finance, Retail</li>
</ul>

<hr>

<h2 id="pricing">Pricing</h2>

<h3>Standard Pricing</h3>

<p>Each product has a list price:</p>

<ul>
<li><strong>List Price:</strong> Standard selling price</li>
<li><strong>Cost:</strong> Internal cost</li>
<li><strong>Margin:</strong> Profit margin (calculated automatically)</li>
</ul>

<h3>Price Books</h3>

<p>Price books allow different pricing for different scenarios:</p>

<h4>Creating Price Books</h4>

<ol>
<li>Go to <strong>Products > Price Books</strong></li>
<li>Click <strong>+ New Price Book</strong></li>
<li>Enter price book name:
<ul>
<li>Standard Price Book (default)</li>
<li>Partner Pricing</li>
<li>Volume Discounts</li>
<li>Regional Pricing</li>
</ul>
</li>
<li>Add products with special prices</li>
<li>Save price book</li>
</ol>

<h4>Using Price Books</h4>

<ul>
<li><strong>Standard Price Book:</strong> Default pricing</li>
<li><strong>Customer-Specific:</strong> Special pricing for key accounts</li>
<li><strong>Partner Pricing:</strong> Reseller or distributor pricing</li>
<li><strong>Volume Discounts:</strong> Tiered pricing based on quantity</li>
</ul>

<h3>Discounts</h3>

<h4>Product-Level Discounts</h4>

<p>Set discounts on individual products in deals:</p>
<ul>
<li>Percentage discount</li>
<li>Fixed amount discount</li>
</ul>

<h4>Volume Discounts</h4>

<p>Automatic discounts based on quantity:</p>

<ul>
<li>Buy 10-20 units: 5% off</li>
<li>Buy 21-50 units: 10% off</li>
<li>Buy 50+ units: 15% off</li>
</ul>

<h3>Multi-Currency</h3>

<p>Sell in multiple currencies:</p>

<ol>
<li>Enable multi-currency in settings</li>
<li>Add currencies to product prices</li>
<li>Set exchange rates</li>
<li>Select currency when adding to deal</li>
</ol>

<div class="callout-info">
<strong>Tip:</strong> Always keep list prices updated. Regular price reviews ensure accurate deal values and forecasts.
</div>

<hr>

<h2 id="inventory-management">Inventory Management</h2>

<h3>Enabling Inventory Tracking</h3>

<p>For physical products, track inventory:</p>

<ol>
<li>Open product record</li>
<li>Scroll to <strong>Inventory</strong> section</li>
<li>Enable <strong>Track Inventory</strong></li>
<li>Set initial quantity</li>
<li>Set reorder level and quantity</li>
<li>Save product</li>
</ol>

<h3>Inventory Levels</h3>

<table>
<thead>
<tr>
<th>Level</th>
<th>Meaning</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>In Stock</strong></td>
<td>Quantity > Reorder Level</td>
<td>Normal selling</td>
</tr>
<tr>
<td><strong>Low Stock</strong></td>
<td>Quantity ≤ Reorder Level</td>
<td>Trigger reorder</td>
</tr>
<tr>
<td><strong>Out of Stock</strong></td>
<td>Quantity = 0</td>
<td>Backorder or disallow sales</td>
</tr>
</tbody>
</table>

<h3>Updating Inventory</h3>

<h4>Manual Adjustment</h4>

<ol>
<li>Open product</li>
<li>Edit <strong>Quantity</strong> field</li>
<li>Add adjustment note (required)</li>
<li>Save changes</li>
</ol>

<h4>Automatic Updates</h4>

<p>Inventory updates automatically when:</p>
<ul>
<li>Deal is closed (quantity deducted)</li>
<li>Deal is lost (quantity restored)</li>
<li>Products are received (add stock)</li>
</ul>

<h3>Low Stock Alerts</h3>

<p>Configure notifications:</p>

<ol>
<li>Go to <strong>Settings > Notifications</strong></li>
<li>Enable <strong>Low Stock Alerts</strong></li>
<li>Set who receives alerts</li>
<li>Choose alert frequency</li>
<li>Save settings</li>
</ol>

<h3>Inventory Reports</h3>

<h4>Stock Level Report</h4>

<p>View all products with stock levels:</p>

<ol>
<li>Go to <strong>Products > Reports > Stock Levels</strong></li>
<li>See current inventory for all products</li>
<li>Filter by low stock or out of stock</li>
<li>Export report for purchasing</li>
</ol>

<h4>Inventory Movement Report</h4>

<p>Track inventory changes over time:</p>

<ul>
<li>Products added (receipts)</li>
<li>Products removed (sales)</li>
<li>Adjustments made</li>
<li>Net change</li>
</ul>

<hr>

<h2 id="product-bundles">Product Bundles</h2>

<h3>What are Bundles?</h3>

<p>Bundles combine multiple products into a package, often at a discounted price. Examples:</p>

<ul>
<li><strong>Hardware + Software:</strong> Computer with pre-installed software</li>
<li><strong>Service Packages:</strong> Setup + training + support</li>
<li><strong>Product Suites:</strong> Multiple products sold together</li>
<li><strong>Starter Kits:</strong> Everything a new customer needs</li>
</ul>

<h3>Creating a Bundle</h3>

<ol>
<li>Go to <strong>Products > All Products</strong></li>
<li>Click <strong>+ Add Product</strong></li>
<li>Set product type to <strong>Bundle</strong></li>
<li>Enter bundle name and description</li>
<li>Scroll to <strong>Bundle Products</strong></li>
<li>Add products to include in bundle:
<ul>
<li>Select product</li>
<li>Set quantity</li>
<li>Adjust individual product prices if needed</li>
</ul>
</li>
<li>Set bundle pricing:
<ul>
<li><strong>Bundle Price:</strong> Total price for package</li>
<li><strong>Component Prices:</strong> Show individual prices</li>
<li><strong>Discount:</strong> Percentage off regular total</li>
</ul>
</li>
<li>Save bundle</li>
</ol>

<h3>Bundle Pricing Example</h3>

<p><strong>Product Suite Bundle:</strong></p>

<table>
<thead>
<tr>
<th>Product</th>
<th>Regular Price</th>
<th>Bundle Price</th>
</tr>
</thead>
<tbody>
<tr>
<td>Product A</td>
<td>$100</td>
<td>$80</td>
</tr>
<tr>
<td>Product B</td>
<td>$150</td>
<td>$120</td>
</tr>
<tr>
<td>Product C</td>
<td>$200</td>
<td>$160</td>
</tr>
<tr>
<td><strong>Totals</strong></td>
<td><strong>$450</strong></td>
<td><strong>$360</strong> (20% discount)</td>
</tr>
</tbody>
</table>

<h3>Using Bundles in Deals</h3>

<p>Adding bundles to opportunities:</p>

<ol>
<li>Open deal/opportunity</li>
<li>Go to <strong>Products</strong> section</li>
<li>Add bundle to deal</li>
<li>System shows:
<ul>
<li>Bundle price</li>
<li>Individual component products</li>
<li>Total discount applied</li>
</ul>
</li>
<li>Modify quantities if needed</li>
<li>Save changes</li>
</ol>

<div class="callout-info">
<strong>Tip:</strong> Bundles increase average deal size and simplify sales process. Create bundles for common customer scenarios.
</div>

<hr>

<h2 id="best-practices">Best Practices</h2>

<h3>Product Catalog Management</h3>

<ul>
<li><strong>Keep Current:</strong> Regularly update products and pricing</li>
<li><strong>Be Consistent:</strong> Use standardized naming conventions</li>
<li><strong>Use SKUs:</strong> Assign unique codes to every product</li>
<li><strong>Add Descriptions:</strong> Provide clear, detailed descriptions</li>
<li><strong>Product Images:</strong> Add images for visual identification</li>
</ul>

<h3>Pricing Strategy</h3>

<ul>
<li><strong>Competitive Pricing:</strong> Research competitor pricing</li>
<li><strong>Value-Based Pricing:</strong> Price based on value delivered</li>
<li><strong>Tiered Pricing:</strong> Offer good/better/best options</li>
<li><strong>Psychological Pricing:</strong> Use $99 instead of $100</li>
<li><strong>Regular Reviews:</strong> Update pricing quarterly</li>
</ul>

<h3>Inventory Management</h3>

<ul>
<li><strong>Track Inventory:</strong> Enable tracking for physical goods</li>
<li><strong>Set Reorder Points:</strong> Prevent stockouts</li>
<li><strong>Regular Audits:</strong> Count inventory periodically</li>
<li><strong>Safety Stock:</strong> Keep buffer for demand spikes</li>
<li><strong>ABC Analysis:</strong> Focus on high-value items</li>
</ul>

<h3>Product Organization</h3>

<ul>
<li><strong>Product Families:</strong> Group related products</li>
<li><strong>Clear Hierarchy:</strong> Categories and subcategories</li>
<li><strong>Tags:</strong> Add tags for flexible categorization</li>
<li><strong>Searchable:</strong> Use keywords in descriptions</li>
</ul>

<h3>Deal Management</h3>

<ul>
<li><strong>Add Products:</strong> Always include products in deals</li>
<li><strong>Be Specific:</strong> Use exact products, not generic items</li>
<li><strong>Quantities:</strong> Ensure quantities are accurate</li>
<li><strong>Pricing:</strong> Use discounts strategically</li>
<li><strong>Margin:</strong> Monitor profit margins on deals</li>
</ul>

<hr>

<h2 id="troubleshooting">Troubleshooting</h2>

<h3>Issue: Can't Add Product to Deal</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Product is inactive</li>
<li>Insufficient inventory (if tracking enabled)</li>
<li>Permission issue</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Check product is active</li>
<li>Verify inventory available</li>
<li>Check user permissions</li>
</ul>

<h3>Issue: Pricing Not Correct</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Wrong price book selected</li>
<li>Outdated list price</li>
<li>Discount applied incorrectly</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Verify correct price book</li>
<li>Update list price if needed</li>
<li>Check discount calculations</li>
</ul>

<h3>Issue: Inventory Not Updating</h3>

<p><strong>Causes:</strong></p>
<ul>
<li>Inventory tracking disabled</li>
<li>Deal not closed</li>
<li>System error</li>
</ul>

<p><strong>Solutions:</strong></p>
<ul>
<li>Enable inventory tracking on product</li>
<li>Ensure deal is closed won</li>
<li>Contact administrator if issue persists</li>
</ul>

<h3>Issue: Too Many Similar Products</h3>

<p><strong>Problem:</strong> Catalog clutter with variations</p>

<p><strong>Solutions:</strong></p>
<ul>
<li>Use product variants instead of separate products</li>
<li>Bundle related items together</li>
<li>Inactivate obsolete products</li>
<li>Consolidate similar products</li>
</ul>

<div class="callout-success">
<strong>Need More Help?</strong> Check out our other feature guides or contact support for personalized assistance.
</div>

<hr>

<h2>Appendix</h2>

<h3>Keyboard Shortcuts</h3>

<ul>
<li><strong>Ctrl + P:</strong> Create new product</li>
<li><strong>Ctrl + K:</strong> Quick search products</li>
<li><strong>Ctrl + F:</strong> Advanced search</li>
</ul>

<h3>Related Features</h3>

<ul>
<li><a href="#leads-management">Leads Management</a> - Adding products to deals</li>
<li><a href="#contacts-management">Contacts Management</a> - Customer information</li>
<li><a href="#sales-forecasting">Sales Forecasting</a> - Revenue by product</li>
</ul>
HTML;
    }

    /**
     * Create Troubleshooting articles.
     *
     * @return void
     */
    protected function createTroubleshootingArticles()
    {
        $troubleshootingCategory = DB::table('doc_categories')
            ->where('slug', 'troubleshooting')
            ->first();

        if (!$troubleshootingCategory) {
            $this->command->error('Troubleshooting category not found!');
            return;
        }

        $articles = [
            [
                'title' => 'Login Issues',
                'slug' => 'login-issues',
                'content' => $this->getLoginIssuesContent(),
                'excerpt' => 'Troubleshoot common login problems including redirect loops, forgotten passwords, and authentication failures.',
                'type' => 'troubleshooting',
                'difficulty_level' => 'beginner',
                'reading_time_minutes' => 5,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Database Connection Problems',
                'slug' => 'database-connection-problems',
                'content' => $this->getDatabaseConnectionContent(),
                'excerpt' => 'Resolve database connectivity issues, connection timeouts, and common configuration errors.',
                'type' => 'troubleshooting',
                'difficulty_level' => 'intermediate',
                'reading_time_minutes' => 8,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Performance Issues',
                'slug' => 'performance-issues',
                'content' => $this->getPerformanceIssuesContent(),
                'excerpt' => 'Diagnose and fix slow page loads, high memory usage, and other performance bottlenecks.',
                'type' => 'troubleshooting',
                'difficulty_level' => 'intermediate',
                'reading_time_minutes' => 10,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Email Sending Failures',
                'slug' => 'email-sending-failures',
                'content' => $this->getEmailSendingContent(),
                'excerpt' => 'Fix email notification problems, SMTP configuration issues, and delivery failures.',
                'type' => 'troubleshooting',
                'difficulty_level' => 'intermediate',
                'reading_time_minutes' => 7,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => false,
                'sort_order' => 4,
            ],
            [
                'title' => 'File Upload Errors',
                'slug' => 'file-upload-errors',
                'content' => $this->getFileUploadContent(),
                'excerpt' => 'Resolve file upload problems including size limits, permission errors, and unsupported formats.',
                'type' => 'troubleshooting',
                'difficulty_level' => 'beginner',
                'reading_time_minutes' => 6,
                'status' => 'published',
                'visibility' => 'public',
                'featured' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($articles as $article) {
            $existingArticle = DB::table('doc_articles')
                ->where('slug', $article['slug'])
                ->first();

            if (!$existingArticle) {
                $articleId = DB::table('doc_articles')->insertGetId(array_merge($article, [
                    'category_id' => $troubleshootingCategory->id,
                    'author_id' => 1,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                $this->command->info("Created troubleshooting article: {$article['title']}");
            }
        }
    }

    /**
     * Content for Login Issues article.
     */
    protected function getLoginIssuesContent()
    {
        return <<<HTML
<h1>Login Issues</h1>

<h2>Problem Diagnosis</h2>

<p>Experiencing difficulties accessing the admin panel? This guide covers the most common login problems and their solutions.</p>

<hr>

<h2>Issue: 302 Redirect Loop</h2>

<h3>Problem Diagnosis</h3>

<p>You're experiencing a **302 redirect loop** when attempting to log in to the admin panel at <code>http://127.0.0.1:8001/admin/login</code>. The page appears to refresh continuously without allowing you to log in.</p>

<h3>Root Cause</h3>

<p>The most common cause is that the **database has no users** - the <code>users</code> table is completely empty. This means:</p>

<ul>
<li>Login credentials cannot be validated</li>
<li>No user account exists to authenticate</li>
<li>The system cannot proceed past the login page</li>
</ul>

<h3>Solution Applied</h3>

<p>Run the database seeder to create the default admin user and role:</p>

<pre><code class="language-bash">php artisan db:seed --class="Webkul\\Installer\\Database\\Seeders\\User\\DatabaseSeeder"</code></pre>

<p>This seeder creates:</p>

<ol>
<li><strong>Administrator Role</strong> (ID: 1) with full permissions (<code>permission_type: 'all'</code>)</li>
<li><strong>Admin User</strong> (ID: 1) with the following credentials</li>
</ol>

<h3>Default Admin Credentials</h3>

<table>
<thead>
<tr>
<th>Field</th>
<th>Value</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Email</strong></td>
<td><code>admin@example.com</code></td>
</tr>
<tr>
<td><strong>Password</strong></td>
<td><code>admin123</code></td>
</tr>
</tbody>
</table>

<h3>Verification Results</h3>

<ul>
<li>✅ User created successfully</li>
<li>✅ User status: Active</li>
<li>✅ Role: Administrator (Super Admin)</li>
<li>✅ Dashboard permission: GRANTED</li>
</ul>

<h3>Next Steps</h3>

<ol>
<li>Navigate to <code>http://127.0.0.1:8001/admin/login</code></li>
<li>Log in using the credentials above</li>
<li><strong>IMPORTANT:</strong> Change the default password immediately after logging in for security</li>
<li>You should now be able to access the admin dashboard without any redirect loop</li>
</ol>

<div class="callout-warning">
<strong>Security Warning:</strong> Always change the default password after first login. Using default credentials in production is a serious security risk.
</div>

<h3>Technical Details</h3>

<ul>
<li>The 302 status code is <strong>normal</strong> for form submissions - it indicates a redirect</li>
<li>On successful login, you'll be redirected to <code>/admin/dashboard</code></li>
<li>The issue is not the redirect itself, but the missing user account preventing successful authentication</li>
</ul>

<hr>

<h2>Issue: Forgotten Password</h2>

<h3>Problem Diagnosis</h3>

<p>You cannot remember your admin password and cannot log in to the system.</p>

<h3>Solutions</h3>

<h4>Solution 1: Use Password Reset (Recommended)</h4>

<ol>
<li>On the login page, click <strong>"Forgot Password"</strong></li>
<li>Enter your email address</li>
<li>Check your email for the reset link</li>
<li>Create a new secure password</li>
</ol>

<h4>Solution 2: Reset via Artisan Command</h4>

<p>If email is not configured, use this artisan command:</p>

<pre><code class="language-bash">php artisan tinker</code></pre>

<p>Then in the tinker console:</p>

<pre><code class="language-php">\\App\\Models\\User::where('email', 'admin@example.com')->update(['password' => bcrypt('new_secure_password')]);</code></pre>

<div class="callout-danger">
<strong>Warning:</strong> Replace <code>'new_secure_password'</code> with a strong, secure password. Use a password manager to generate one.
</div>

<hr>

<h2>Issue: "Session Expired" Errors</h2>

<h3>Problem Diagnosis</h3>

<p>You're frequently logged out or see "Session expired" messages, even when actively using the system.</p>

<h3>Root Causes</h3>

<ul>
<li>Session lifetime is too short</li>
<li>PHP session storage directory has permission issues</li>
<li>Multiple logins from different locations</li>
<li>Browser clearing cookies/cookies blocked</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Increase Session Lifetime</h4>

<p>Edit <code>config/session.php</code>:</p>

<pre><code class="language-php">'lifetime' => env('SESSION_LIFETIME', 120), // Increase from default</code></pre>

<p>Then clear the config cache:</p>

<pre><code class="language-bash">php artisan config:clear</code></pre>

<h4>Solution 2: Fix Session Storage Permissions</h4>

<pre><code class="language-bash">sudo chmod -R 775 storage/framework/sessions
sudo chown -R www-data:www-data storage/framework/sessions</code></pre>

<h4>Solution 3: Check Browser Settings</h4>

<ul>
<li>Ensure cookies are enabled for your domain</li>
<li>Check that the site is not in "Incognito/Private" mode with strict settings</li>
<li>Clear browser cache and cookies, then try again</li>
</ul>

<hr>

<h2>Issue: CSRF Token Mismatch</h2>

<h3>Problem Diagnosis</h3>

<p>You see the error: <code>"CSRF token mismatch"</code> when attempting to log in.</p>

<h3>Root Causes</h3>

<ul>
<li>Browser cache has old CSRF token</li>
<li>Session storage is not writable</li>
<li><code>APP_URL</code> in .env is incorrect</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Clear Application Cache</h4>

<pre><code class="language-bash">php artisan cache:clear
php artisan config:clear
php artisan session:clear
php artisan view:clear</code></pre>

<h4>Solution 2: Verify APP_URL</h4>

<p>Check your <code>.env</code> file:</p>

<pre><code>APP_URL=http://127.0.0.1:8001</code></pre>

<p>Ensure this matches the URL you're using to access the application.</p>

<hr>

<h2>Still Having Issues?</h2>

<p>If none of these solutions resolve your login problem:</p>

<ol>
<li>Check the Laravel logs: <code>storage/logs/laravel.log</code></li>
<li>Verify database connection is working</li>
<li>Ensure all migrations have been run: <code>php artisan migrate:status</code></li>
<li>Check that the web server is running with correct permissions</li>
<li>Review PHP error logs for additional details</li>
</ol>

<div class="callout-info">
<strong>Need More Help?</strong> Check the <a href="/docs/database-connection-problems">Database Connection Problems</a> guide or contact support with error logs.
</div>
HTML;
    }

    /**
     * Content for Database Connection Problems article.
     */
    protected function getDatabaseConnectionContent()
    {
        return <<<HTML
<h1>Database Connection Problems</h1>

<p>Complete guide to diagnosing and resolving database connectivity issues in the CRM application.</p>

<hr>

<h2>Issue: "SQLSTATE[HY000] [2002] Connection Refused"</h2>

<h3>Problem Diagnosis</h3>

<p>The application cannot connect to the database server. This error typically indicates the database server is not running or is not accessible.</p>

<h3>Root Causes</h3>

<ul>
<li>MySQL/MariaDB service is not running</li>
<li>Incorrect host or port in configuration</li>
<li>Firewall blocking the connection</li>
<li>Database server not listening on the specified port</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Start Database Service</h4>

<p><strong>Ubuntu/Debian:</strong></p>

<pre><code class="language-bash">sudo systemctl start mysql
sudo systemctl status mysql</code></pre>

<p><strong>CentOS/RHEL:</strong></p>

<pre><code class="language-bash">sudo systemctl start mysqld
sudo systemctl status mysqld</code></pre>

<p><strong>macOS (Homebrew):</strong></p>

<pre><code class="language-bash">brew services start mysql
brew services list</code></pre>

<h4>Solution 2: Verify Database Credentials</h4>

<p>Check your <code>.env</code> file:</p>

<pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password</code></pre>

<p>Test the connection manually:</p>

<pre><code class="language-bash">mysql -h 127.0.0.1 -P 3306 -u your_username -p</code></pre>

<h4>Solution 3: Check Firewall Settings</h4>

<pre><code class="language-bash">sudo ufw status
sudo ufw allow 3306/tcp  # If needed</code></pre>

<hr>

<h2>Issue: "Access Denied for User"</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>SQLSTATE[HY000] [1698] Access denied for user 'username'@'host'</code></p>

<h3>Root Causes</h3>

<ul>
<li>Incorrect username or password</li>
<li>User does not have permission to access the database</li>
<li>User account is not allowed to connect from the current host</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Verify User Permissions</h4>

<p>Log in to MySQL as root:</p>

<pre><code class="language-bash">mysql -u root -p</code></pre>

<p>Check user permissions:</p>

<pre><code class="language-sql">SELECT user, host FROM mysql.user;
SHOW GRANTS FOR 'your_username'@'localhost';</code></pre>

<h4>Solution 2: Grant Necessary Permissions</h4>

<pre><code class="language-sql">GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;</code></pre>

<h4>Solution 3: Create New Database User</h4>

<pre><code class="language-sql">CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON crm_database.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;</code></pre>

<div class="callout-warning">
<strong>Security:</strong> Use a strong password. Avoid using root credentials in your application.
</div>

<hr>

<h2>Issue: "Unknown Database"</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>SQLSTATE[HY000] [1049] Unknown database 'database_name'</code></p>

<h3>Solution</h3>

<h4>Create the Database</h4>

<pre><code class="language-bash">mysql -u root -p</code></pre>

<pre><code class="language-sql">CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></pre>

<p>Then run migrations:</p>

<pre><code class="language-bash">php artisan migrate</code></pre>

<hr>

<h2>Issue: "MySQL Server Has Gone Away"</h2>

<h3>Problem Diagnosis</h3>

<p>The connection to the database server was lost during a long-running operation.</p>

<h3>Root Causes</h3>

<ul>
<li>Database connection timed out</li>
<li>MySQL <code>wait_timeout</code> is too low</li>
<li>Network issues interrupting the connection</li>
<li>Long-running query exceeding timeout limits</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Increase MySQL Timeout</h4>

<p>Edit MySQL configuration (<code>my.cnf</code> or <code>my.ini</code>):</p>

<pre><code>[mysqld]
wait_timeout = 28800
interactive_timeout = 28800
max_allowed_packet = 256M</code></pre>

<p>Restart MySQL:</p>

<pre><code class="language-bash">sudo systemctl restart mysql</code></pre>

<h4>Solution 2: Configure Laravel Reconnect</h4>

<p>In <code>config/database.php</code>, under the MySQL connection:</p>

<pre><code class="language-php">'mysql' => [
    // ...
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ],
],</code></pre>

<h4>Solution 3: Use Queue for Long Operations</h4>

<p>For long-running database operations, use Laravel queues:</p>

<pre><code class="language-bash">php artisan queue:work --tries=3 --timeout=300</code></pre>

<hr>

<h2>Issue: Too Many Connections</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>SQLSTATE[HY000] [1040] Too many connections</code></p>

<h3>Root Causes</h3>

<ul>
<li>MySQL <code>max_connections</code> limit reached</li>
<li>Connection leaks (not properly closing connections)</li>
<li>High traffic overwhelming connection pool</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Increase Connection Limit</h4>

<p>In MySQL configuration:</p>

<pre><code>[mysqld]
max_connections = 500</code></pre>

<h4>Solution 2: Enable Connection Pooling</h4>

<p>In <code>config/database.php</code>:</p>

<pre><code class="language-php">'mysql' => [
    'driver' => 'mysql',
    'pool' => [
        'max_connections' => 50,
        'max_wait_time' => 30,
    ],
],</code></pre>

<h4>Solution 3: Check for Connection Leaks</h4>

<p>Ensure your code is not creating unnecessary connections in loops:</p>

<pre><code class="language-php">// ❌ BAD - Creates new connection each iteration
foreach (\$items as \$item) {
    DB::connection()->select(...);
}

// ✅ GOOD - Reuses connection
foreach (\$items as \$item) {
    DB::select(...);
}</code></pre>

<hr>

<h2>Prevention Tips</h2>

<h3>1. Use Environment Variables</h3>

<p>Always use <code>.env</code> file for database credentials. Never hardcode them.</p>

<h3>2. Enable Query Logging (Development Only)</h3>

<pre><code class="language-bash">DB::enableQueryLog();
// Run your queries
dd(DB::getQueryLog());</code></pre>

<h3>3. Monitor Database Health</h3>

<pre><code class="language-bash">mysqladmin -u root -p processlist
mysqladmin -u root -p status</code></pre>

<h3>4. Regular Backups</h3>

<pre><code class="language-bash">mysqldump -u root -p database_name > backup.sql</code></pre>

<hr>

<h2>Still Having Issues?</h2>

<div class="callout-danger">
<strong>Critical:</strong> Before making changes to database configuration, always backup your data first!
</div>

<ol>
<li>Check Laravel logs: <code>storage/logs/laravel.log</code></li>
<li>Check MySQL error logs: <code>/var/log/mysql/error.log</code></li>
<li>Verify your .env configuration is correct</li>
<li>Test database connection from command line</li>
<li>Ensure all required PHP extensions are installed: <code>php-mysql</code>, <code>php-mysqli</code>, <code>php-pdo</code></li>
</ol>

<div class="callout-info">
<strong>Need More Help?</strong> Consult the official <a href="https://laravel.com/docs/database" target="_blank">Laravel Database Documentation</a> or contact support.
</div>
HTML;
    }

    /**
     * Content for Performance Issues article.
     */
    protected function getPerformanceIssuesContent()
    {
        return <<<HTML
<h1>Performance Issues</h1>

<p>Diagnose and resolve common performance bottlenecks in your CRM application.</p>

<hr>

<h2>Issue: Slow Page Load Times</h2>

<h3>Problem Diagnosis</h3>

<p>Pages take more than 3-5 seconds to load, causing poor user experience.</p>

<h3>Root Causes</h3>

<ul>
<li>Unoptimized database queries (N+1 problem)</li>
<li>Lack of caching</li>
<li>Large asset files (images, CSS, JS)</li>
<li>Server resource limitations</li>
<li>Missing database indexes</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Enable Query Optimization</h4>

<p><strong>Identify N+1 Queries:</strong></p>

<p>Install Laravel Debugbar in development:</p>

<pre><code class="language-bash">composer require barryvdh/laravel-debugbar --dev</code></pre>

<p>Look for queries being executed in loops. Use eager loading:</p>

<pre><code class="language-php">// ❌ BAD - N+1 Query Problem
\$leads = Lead::all();
foreach (\$leads as \$lead) {
    echo \$lead->contact->name; // Separate query for each lead
}

// ✅ GOOD - Eager Loading
\$leads = Lead::with('contact')->get();
foreach (\$leads as \$lead) {
    echo \$lead->contact->name; // No additional queries
}</code></pre>

<h4>Solution 2: Add Database Indexes</h4>

<p>Create a migration for indexes:</p>

<pre><code class="language-bash">php artisan make:migration add_performance_indexes</code></pre>

<pre><code class="language-php">public function up()
{
    Schema::table('leads', function (Blueprint \$table) {
        \$table->index('status');
        \$table->index('created_at');
        \$table->index(['user_id', 'status']);
    });
}</code></pre>

<h4>Solution 3: Enable Caching</h4>

<p><strong>Configure Cache Driver:</strong> In <code>.env</code>:</p>

<pre><code>CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis</code></pre>

<p><strong>Cache Frequently Accessed Data:</strong></p>

<pre><code class="language-php">// Cache for 1 hour
\$leads = Cache::remember('leads.active', 3600, function () {
    return Lead::where('status', 'active')->get();
});</code></pre>

<h4>Solution 4: Optimize Assets</h4>

<p>Minify and combine assets:</p>

<pre><code class="language-bash">npm run build
# or
php artisan optimize:clear</code></pre>

<p>Enable compression in <code>public/.htaccess</code>:</p>

<pre><code><IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css
    AddOutputFilterByType DEFLATE application/javascript
</IfModule></code></pre>

<hr>

<h2>Issue: High Memory Usage</h2>

<h3>Problem Diagnosis</h3>

<p>The application consumes excessive memory, leading to slowdowns or crashes.</p>

<h3>Root Causes</h3>

<ul>
<li>Loading large datasets into memory</li>
<li>Memory leaks in long-running processes</li>
<li>Inefficient data processing</li>
<li>Unoptimized image handling</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Use Chunking for Large Datasets</h4>

<pre><code class="language-php">// ❌ BAD - Loads all records at once
\$users = User::all();
foreach (\$users as \$user) {
    // Process user
}

// ✅ GOOD - Processes in chunks
User::chunk(100, function (\$users) {
    foreach (\$users as \$user) {
        // Process user
    }
});</code></pre>

<h4>Solution 2: Use Lazy Collections</h4>

<pre><code class="language-php">\$users = User::cursor(); // Uses generator
foreach (\$users as \$user) {
    // Process user
}</code></pre>

<h4>Solution 3: Optimize Image Handling</h4>

<p>Use image optimization:</p>

<pre><code class="language-bash">composer require intervention/image</code></pre>

<pre><code class="language-php">// Resize and compress
\$image = Image::make(\$file)->resize(800, null, function (\$constraint) {
    \$constraint->aspectRatio();
    \$constraint->upsize();
})->encode('jpg', 80);</code></pre>

<hr>

<h2>Issue: Slow API Responses</h2>

<h3>Problem Diagnosis</h3>

<p>API endpoints take too long to respond, affecting client applications.</p>

<h3>Solutions</h3>

<h4>Solution 1: Implement API Response Caching</h4>

<pre><code class="language-php">// Cache API responses
public function index()
{
    return Cache::remember('api.leads', 300, function () {
        return LeadResource::collection(Lead::all());
    });
}</code></pre>

<h4>Solution 2: Use Pagination</h4>

<pre><code class="language-php">public function index()
{
    return LeadResource::collection(
        Lead::paginate(50) // Returns 50 items per page
    );
}</code></pre>

<h4>Solution 3: Implement Rate Limiting</h4>

<p>In <code>routes/api.php</code>:</p>

<pre><code class="language-php">Route::middleware('throttle:60,1')->group(function () {
    Route::apiResource('leads', LeadController::class);
});</code></pre>

<hr>

<h2>Issue: Database Query Slowdowns</h2>

<h3>Problem Diagnosis</h3>

<p>Database queries are taking too long to execute.</p>

<h3>Solutions</h3>

<h4>Solution 1: Use Query Logging to Identify Slow Queries</h4>

<pre><code class="language-bash">php artisan tinker</code></pre>

<pre><code class="language-php">DB::enableQueryLog();
Lead::where('status', 'active')->get();
dd(DB::getQueryLog());</code></pre>

<h4>Solution 2: Optimize SELECT Statements</h4>

<pre><code class="language-php">// ❌ BAD - Selects all columns
\$lead = Lead::find(\$id);

// ✅ GOOD - Selects only needed columns
\$lead = Lead::select('id', 'name', 'email')->find(\$id);</code></pre>

<h4>Solution 3: Use Database Query Cache</h4>

<pre><code class="language-php">// Cache query results for 10 minutes
\$leads = DB::table('leads')
    ->where('status', 'active')
    ->remember(600)
    ->get();</code></pre>

<hr>

<h2>Prevention Tips</h2>

<h3>1. Enable OPcache</h3>

<p>In <code>php.ini</code>:</p>

<pre><code>opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=10000</code></pre>

<h3>2. Use Queue for Heavy Operations</h3>

<pre><code class="language-php">dispatch(new SendEmailsJob(\$leads));</code></pre>

<h3>3. Monitor Performance</h3>

<p>Use Laravel Telescope in development:</p>

<pre><code class="language-bash">composer require laravel/telescope --dev
php artisan telescope:install
php artisan telescope:publish</code></pre>

<h3>4. Regular Database Maintenance</h3>

<pre><code class="language-bash">mysql -u root -p -e "OPTIMIZE TABLE your_table_name;"
mysql -u root -p -e "ANALYZE TABLE your_table_name;"</code></pre>

<hr>

<h2>Performance Checklist</h2>

<ul>
<li>✅ All Eloquent relationships use eager loading</li>
<li>✅ Frequently queried columns are indexed</li>
<li>✅ Caching is enabled for expensive operations</li>
<li>✅ Assets are minified and compressed</li>
<li>✅ Images are optimized</li>
<li>✅ Database queries use select() to limit columns</li>
<li>✅ Large datasets use chunking or cursors</li>
<li>✅ Queue workers are running for background jobs</li>
<li>✅ OPcache is enabled</li>
<li>✅ Regular performance monitoring in place</li>
</ul>

<div class="callout-success">
<strong>Best Practice:</strong> Set up application performance monitoring (APM) tools like New Relic or Datadog for production environments.
</div>

<div class="callout-info">
<strong>Need More Help?</strong> Check the <a href="https://laravel.com/docs/performance" target="_blank">Laravel Performance Documentation</a> for comprehensive optimization guides.
</div>
HTML;
    }

    /**
     * Content for Email Sending Failures article.
     */
    protected function getEmailSendingContent()
    {
        return <<<HTML
<h1>Email Sending Failures</h1>

<p>Complete troubleshooting guide for email notification problems and SMTP configuration issues.</p>

<hr>

<h2>Issue: Emails Not Sending</h2>

<h3>Problem Diagnosis</h3>

<p>Email notifications are not being delivered to users. No error messages may be visible.</p>

<h3>Root Causes</h3>

<ul>
<li>SMTP configuration is incorrect</li>
<li>Mail credentials are wrong</li>
<li>Mail server is blocking connections</li>
<li>Queue workers are not running</li>
<li>SSL/TLS certificate issues</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Verify SMTP Configuration</h4>

<p>Check your <code>.env</code> file:</p>

<pre><code>MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="\${APP_NAME}"</code></pre>

<div class="callout-warning">
<strong>Important:</strong> For Gmail, use an <a href="https://support.google.com/accounts/answer/185833" target="_blank">App Password</a> instead of your regular password.
</div>

<h4>Solution 2: Test SMTP Connection</h4>

<p>Use a command-line test:</p>

<pre><code class="language-bash">telnet smtp.gmail.com 587</code></pre>

<p>Or use an online tool like <a href="https://www.smtp-diagnostics.com/" target="_blank">SMTP Diagnostics</a>.</p>

<h4>Solution 3: Clear Configuration Cache</h4>

<pre><code class="language-bash">php artisan config:clear
php artisan cache:clear</code></pre>

<hr>

<h2>Issue: "Connection Timed Out"</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>Swift_TransportException: Connection to smtp.server.com:587 timed out</code></p>

<h3>Root Causes</h3>

<ul>
<li>Firewall blocking outbound SMTP connections</li>
<li>Wrong SMTP port</li>
<li>Server network restrictions</li>
<li>ISP blocking SMTP ports</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Check Firewall Rules</h4>

<pre><code class="language-bash">sudo ufw status
sudo ufw allow 587/tcp
sudo ufw allow 465/tcp</code></pre>

<h4>Solution 2: Try Alternative Ports</h4>

<ul>
<li><strong>Port 587:</strong> Submission with TLS</li>
<li><strong>Port 465:</strong> SMTPS (SMTP over SSL)</li>
<li><strong>Port 2525:</strong> Alternative (often used by hosting providers)</li>
</ul>

<h4>Solution 3: Use Encryption from Start</h4>

<p>In <code>config/mail.php</code>:</p>

<pre><code class="language-php">'smtp' => [
    'transport' => 'smtp',
    'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
    'port' => env('MAIL_PORT', 587),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'timeout' => null,
    'auth_mode' => null,
],</code></pre>

<hr>

<h2>Issue: "Authentication Failed"</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>Failed to authenticate on SMTP server</code></p>

<h3>Root Causes</h3>

<ul>
<li>Incorrect username or password</li>
<li>Two-factor authentication enabled (need App Password)</li>
<li>Account requires "Less Secure Apps" to be enabled</li>
<li>Username format is incorrect</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Use App Password (Gmail)</h4>

<ol>
<li>Go to Google Account settings</li>
<li>Enable 2-Step Verification if not already enabled</li>
<li>Go to Security > App Passwords</li>
<li>Generate a new App Password for "Mail"</li>
<li>Use this 16-character password in your <code>.env</code></li>
</ol>

<h4>Solution 2: Verify Username Format</h4>

<p>Most providers require the full email address as the username:</p>

<pre><code>MAIL_USERNAME=youremail@domain.com
# NOT: MAIL_USERNAME=youremail</code></pre>

<h4>Solution 3: Test Credentials Manually</h4>

<p>Use Telnet to test authentication:</p>

<pre><code class="language-bash">telnet smtp.gmail.com 587
EHLO localhost
STARTTLS
AUTH PLAIN BASE64(username\0username\0password)</code></pre>

<hr>

<h2>Issue: Queue Emails Not Sending</h2>

<h3>Problem Diagnosis</h3>

<p>Queued emails are stacking up but not being sent.</p>

<h3>Root Causes</h3>

<ul>
<li>Queue worker is not running</li>
<li>Queue jobs are failing silently</li>
<li>Redis/Database connection issues</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Start Queue Worker</h4>

<pre><code class="language-bash">php artisan queue:work --tries=3 --timeout=300</code></pre>

<p>For production, use Supervisor to keep workers running:</p>

<pre><code class="language-bash">sudo apt install supervisor</code></pre>

<p>Create <code>/etc/supervisor/conf.d/laravel-worker.conf</code>:</p>

<pre><code>[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log</code></pre>

<h4>Solution 2: Check Queue Status</h4>

<pre><code class="language-bash">php artisan queue:failed</code></pre>

<p>Retry failed jobs:</p>

<pre><code class="language-bash">php artisan queue:retry all</code></pre>

<h4>Solution 3: Monitor Queue in Real-Time</h4>

<pre><code class="language-bash">php artisan queue:work --verbose</code></pre>

<hr>

<h2>Issue: SSL Certificate Errors</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>SSL certificate problem: unable to get local issuer certificate</code></p>

<h3>Solutions</h3>

<h4>Solution 1: Update CA Certificate Bundle</h4>

<p><strong>Ubuntu/Debian:</strong></p>

<pre><code class="language-bash">sudo apt update
sudo apt install ca-certificates</code></pre>

<p><strong>macOS (Homebrew):</strong></p>

<pre><code class="language-bash">brew install ca-certificates</code></pre>

<h4>Solution 2: Disable SSL Verification (Not Recommended for Production)</h4>

<p>In <code>config/mail.php</code>:</p>

<pre><code class="language-php">'stream' => [
    'ssl' => [
        'allow_self_signed' => true,
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
],</code></pre>

<div class="callout-danger">
<strong>Warning:</strong> Disabling SSL verification is a security risk. Only use for testing!
</div>

<hr>

<h2>Common Mail Service Settings</h2>

<h3>Gmail</h3>

<pre><code>MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls</code></pre>

<h3>Office 365 / Outlook</h3>

<pre><code>MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls</code></pre>

<h3>SendGrid</h3>

<pre><code>MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=apikey
MAIL_PASSWORD=YOUR_SENDGRID_API_KEY</code></pre>

<h3>Mailgun</h3>

<pre><code>MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=YOUR_MAILGUN_USERNAME
MAIL_PASSWORD=YOUR_MAILGUN_PASSWORD</code></pre>

<h3>Amazon SES</h3>

<pre><code>MAIL_HOST=email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=YOUR_SES_SMTP_USERNAME
MAIL_PASSWORD=YOUR_SES_SMTP_PASSWORD</code></pre>

<hr>

<h2>Testing Email Configuration</h2>

<h3>Test with Tinker</h3>

<pre><code class="language-bash">php artisan tinker</code></pre>

<pre><code class="language-php">Mail::raw('Test email', function (\$message) {
    \$message->to('test@example.com')->subject('Test');
});</code></pre>

<h3>Create Test Route</h3>

<p>In <code>routes/web.php</code>:</p>

<pre><code class="language-php">Route::get('/test-email', function () {
    Mail::raw('Test email body', function (\$message) {
        \$message->to('your-email@example.com')
                ->subject('Test Email');
    });
    return 'Email sent!';
});</code></pre>

<hr>

<h2>Prevention Tips</h2>

<ul>
<li>✅ Use queue workers for sending emails</li>
<li>✅ Monitor failed queue jobs regularly</li>
<li>✅ Use environment-specific mail settings</li>
<li>✅ Implement email logging for debugging</li>
<li>✅ Test email configuration after deployment</li>
<li>✅ Use reputable email service providers for better deliverability</li>
</ul>

<div class="callout-info">
<strong>Need More Help?</strong> Check the <a href="https://laravel.com/docs/mail" target="_blank">Laravel Mail Documentation</a> or consult your email provider's support documentation.
</div>
HTML;
    }

    /**
     * Content for File Upload Errors article.
     */
    protected function getFileUploadContent()
    {
        return <<<HTML
<h1>File Upload Errors</h1>

<p>Complete guide to resolving file upload problems including size limits, permission errors, and format issues.</p>

<hr>

<h2>Issue: "File Too Large" Error</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>The file exceeds the maximum upload size</code> or <code>POST content length exceeds maximum</code></p>

<h3>Root Causes</h3>

<ul>
<li>PHP <code>upload_max_filesize</code> is too small</li>
<li>PHP <code>post_max_size</code> is too small</li>
<li>Nginx <code>client_max_body_size</code> limit</li>
<li>Application validation rules</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Increase PHP Upload Limits</h4>

<p>Edit <code>php.ini</code>:</p>

<pre><code>upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 256M
max_execution_time = 300</code></pre>

<p>Find your <code>php.ini</code> location:</p>

<pre><code class="language-bash">php --ini</code></pre>

<p>Restart PHP-FPM after changes:</p>

<pre><code class="language-bash">sudo systemctl restart php8.2-fpm</code></pre>

<h4>Solution 2: Configure Nginx Limits</h4>

<p>Edit Nginx configuration:</p>

<pre><code>server {
    client_max_body_size 100M;
    client_body_timeout 300s;
}</code></pre>

<p>Restart Nginx:</p>

<pre><code class="language-bash">sudo systemctl restart nginx</code></pre>

<h4>Solution 3: Update Laravel Validation</h4>

<pre><code class="language-php">'file' => 'required|file|max:102400', // 100MB in kilobytes</code></pre>

<hr>

<h2>Issue: "Permission Denied" When Uploading</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>failed to open stream: Permission denied</code></p>

<h3>Root Causes</h3>

<ul>
<li>Storage directory is not writable</li>
<li>Wrong ownership on storage directories</li>
<li>SELinux is blocking write access</li>
<li>Cloud storage configuration issues</li>
</ul>

<h3>Solutions</h3>

<h4>Solution 1: Fix Storage Permissions</h4>

<pre><code class="language-bash">sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache</code></pre>

<h4>Solution 2: Set Correct Ownership</h4>

<pre><code class="language-bash">sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache</code></pre>

<h4>Solution 3: Check SELinux (RHEL/CentOS)</h4>

<pre><code class="language-bash">sudo chcon -R -t httpd_sys_rw_content_t storage
sudo setsebool -P httpd_unified 1</code></pre>

<h4>Solution 4: Create Symbolic Link</h4>

<pre><code class="language-bash">php artisan storage:link</code></pre>

<hr>

<h2>Issue: "Invalid File Type" Error</h2>

<h3>Problem Diagnosis</h3>

<p>Error: <code>The file must be a file of type: jpg, png, pdf</code></p>

<h3>Solutions</h3>

<h4>Solution 1: Update Validation Rules</h4>

<pre><code class="language-php">// Allow multiple file types
'file' => 'required|mimes:jpg,jpeg,png,gif,pdf,doc,docx'

// Allow any file type (use with caution)
'file' => 'required|file'</code></pre>

<h4>Solution 2: Check MIME Types</h4>

<p>Common MIME types:</p>

<table>
<thead>
<tr>
<th>Extension</th>
<th>MIME Type</th>
</tr>
</thead>
<tbody>
<tr>
<td>.jpg / .jpeg</td>
<td>image/jpeg</td>
</tr>
<tr>
<td>.png</td>
<td>image/png</td>
</tr>
<tr>
<td>.gif</td>
<td>image/gif</td>
</tr>
<tr>
<td>.pdf</td>
<td>application/pdf</td>
</tr>
<tr>
<td>.doc</td>
<td>application/msword</td>
</tr>
<tr>
<td>.docx</td>
<td>application/vnd.openxmlformats-officedocument.wordprocessingml.document</td>
</tr>
<tr>
<td>.xls</td>
<td>application/vnd.ms-excel</td>
</tr>
<tr>
<td>.xlsx</td>
<td>application/vnd.openxmlformats-officedocument.spreadsheetml.sheet</td>
</tr>
</tbody>
</table>

<h4>Solution 3: Use MIME Type Validation</h4>

<pre><code class="language-php">'file' => 'required|mimetypes:image/jpeg,image/png,application/pdf'</code></pre>

<hr>

<h2>Issue: File Not Saving to Database</h2>

<h3>Problem Diagnosis</h3>

<p>File uploads successfully but no record is created in the database.</p>

<h3>Solutions</h3>

<h4>Solution 1: Check Database Connection</h4>

<pre><code class="language-bash">php artisan db:show
php artisan migrate:status</code></pre>

<h4>Solution 2: Verify Mass Assignment</h4>

<p>In your model:</p>

<pre><code class="language-php">protected \$fillable = ['filename', 'filepath', 'filesize', 'mime_type'];</code></pre>

<h4>Solution 3: Check Form Submission</h4>

<p>Ensure form has <code>enctype="multipart/form-data"</code>:</p>

<pre><code class="language-html"><form method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form></code></pre>

<hr>

<h2>Issue: Image Upload Problems</h2>

<h3>Problem Diagnosis</h3>

<p>Images fail to upload or display correctly after upload.</p>

<h3>Solutions</h3>

<h4>Solution 1: Verify Image Library</h4>

<pre><code class="language-bash">php -m | grep gd
php -m | grep imagick</code></pre>

<p>Install GD if missing:</p>

<pre><code class="language-bash">sudo apt install php-gd
sudo systemctl restart php8.2-fpm</code></pre>

<h4>Solution 2: Resize Large Images</h4>

<pre><code class="language-bash">composer require intervention/image</code></pre>

<pre><code class="language-php">use Intervention\\Image\\ImageManager;

public function upload(Request \$request)
{
    \$image = \$request->file('image');
    \$filename = time() . '.' . \$image->getClientOriginalExtension();

    Image::make(\$image)
        ->resize(1920, null, function (\$constraint) {
            \$constraint->aspectRatio();
            \$constraint->upsize();
        })
        ->save(storage_path('app/public/' . \$filename));

    return response()->json(['path' => \$filename]);
}</code></pre>

<h4>Solution 3: Convert to Consistent Format</h4>

<pre><code class="language-php">Image::make(\$image)->encode('jpg', 80)->save(\$path);</code></pre>

<hr>

<h2>Issue: Cloud Storage Upload Failures</h2>

<h3>Problem Diagnosis</h3>

<p>Files fail to upload to S3, Azure, or other cloud storage.</p>

<h3>Solutions</h3>

<h4>Solution 1: Verify Environment Variables</h4>

<p>For AWS S3:</p>

<pre><code>FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_URL=https://your-bucket.s3.amazonaws.com</code></pre>

<h4>Solution 2: Check Bucket Permissions</h4>

<ul>
<li>Ensure bucket policy allows writes</li>
<li>Verify IAM user has S3 write permissions</li>
<li>Check CORS configuration if uploading from browser</li>
</ul>

<h4>Solution 3: Test Connection</h4>

<pre><code class="language-bash">php artisan tinker</code></pre>

<pre><code class="language-php">Storage::disk('s3')->put('test.txt', 'Hello World');</code></pre>

<hr>

<h2>Best Practices</h2>

<h3>1. Validate Files Properly</h3>

<pre><code class="language-php">\$request->validate([
    'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf'
]);</code></pre>

<h3>2. Generate Unique Filenames</h3>

<pre><code class="language-php">\$filename = uniqid() . '_' . \$file->getClientOriginalName();
// or
\$filename = Str::uuid() . '.' . \$file->getClientOriginalExtension();</code></pre>

<h3>3. Store Metadata</h3>

<pre><code class="language-php">\$upload = Upload::create([
    'filename' => \$filename,
    'original_name' => \$file->getClientOriginalName(),
    'mime_type' => \$file->getMimeType(),
    'size' => \$file->getSize(),
    'path' => \$path,
]);</code></pre>

<h3>4. Use Storage Facade</h3>

<pre><code class="language-php">// ✅ GOOD - Works with any disk
Storage::put('uploads/file.txt', \$content);

// ❌ BAD - Tightly coupled to local disk
file_put_contents(storage_path('app/uploads/file.txt'), \$content);</code></pre>

<h3>5. Implement Virus Scanning</h3>

<pre><code class="language-bash">composer require gramilabs/php-clamav</code></pre>

<hr>

<h2>Common File Size Reference</h2>

<table>
<thead>
<tr>
<th>Size</th>
<th>Bytes</th>
<th>Kilobytes</th>
<th>Megabytes</th>
<th>Typical Use</th>
</tr>
</thead>
<tbody>
<tr>
<td>1 KB</td>
<td>1,024</td>
<td>1</td>
<td>0.001</td>
<td>Text files</td>
</tr>
<tr>
<td>100 KB</td>
<td>102,400</td>
<td>100</td>
<td>0.1</td>
<td>Small images</td>
</tr>
<tr>
<td>1 MB</td>
<td>1,048,576</td>
<td>1,024</td>
<td>1</td>
<td>High-res images</td>
</tr>
<tr>
<td>10 MB</td>
<td>10,485,760</td>
<td>10,240</td>
<td>10</td>
<td>PDFs, audio</td>
</tr>
<tr>
<td>100 MB</td>
<td>104,857,600</td>
<td>102,400</td>
<td>100</td>
<td>Videos, archives</td>
</tr>
</tbody>
</table>

<div class="callout-success">
<strong>Tip:</strong> For large file uploads, consider using chunked uploads or direct uploads to cloud storage with presigned URLs.
</div>

<div class="callout-info">
<strong>Need More Help?</strong> Check the <a href="https://laravel.com/docs/filesystem" target="_blank">Laravel Filesystem Documentation</a> for comprehensive storage management.
</div>
HTML;
    }
}
