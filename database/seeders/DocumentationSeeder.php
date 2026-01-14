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

        $this->command->info('Getting Started articles seeded successfully!');
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
}
