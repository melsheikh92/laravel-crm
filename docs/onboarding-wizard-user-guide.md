# Onboarding Wizard User Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Step-by-Step Guide](#step-by-step-guide)
   - [Welcome Screen](#welcome-screen)
   - [Step 1: Company Setup](#step-1-company-setup)
   - [Step 2: User Creation](#step-2-user-creation)
   - [Step 3: Pipeline Configuration](#step-3-pipeline-configuration)
   - [Step 4: Email Integration](#step-4-email-integration)
   - [Step 5: Sample Data Import](#step-5-sample-data-import)
   - [Completion Screen](#completion-screen)
4. [Resuming the Wizard](#resuming-the-wizard)
5. [Skipping Steps](#skipping-steps)
6. [Restarting the Wizard](#restarting-the-wizard)
7. [Tips and Best Practices](#tips-and-best-practices)
8. [Troubleshooting](#troubleshooting)
9. [FAQ](#faq)

---

## Introduction

The Onboarding Wizard is a guided, step-by-step setup experience designed to help new users quickly configure their CRM instance and get started with essential features. The wizard eliminates the steep learning curve often associated with CRM systems by walking you through the most critical initial configuration tasks.

### Why Use the Onboarding Wizard?

- **Quick Setup**: Complete your CRM setup in approximately 15 minutes
- **Guided Experience**: Clear instructions and contextual help for each step
- **Auto-Save**: Your progress is saved automatically, so you can resume anytime
- **Flexible**: Skip optional steps and customize based on your needs
- **Sample Data**: Option to import realistic sample data to explore the CRM

### What You'll Configure

The wizard covers five essential setup steps:

1. **Company Setup** - Configure your company profile and basic information
2. **User Creation** - Add your first team member with appropriate role
3. **Pipeline Configuration** - Set up your sales pipeline stages
4. **Email Integration** - Connect your email for seamless communication (optional)
5. **Sample Data Import** - Import sample data to explore features (optional)

### When Does the Wizard Appear?

The onboarding wizard automatically appears when:
- You install the CRM for the first time
- You log in as a new user who hasn't completed onboarding
- You manually restart it from the settings menu

---

## Getting Started

### Accessing the Onboarding Wizard

#### For New Installations

After completing the installation and creating your admin account, the wizard will automatically launch on your first login. You'll be redirected to the welcome screen immediately.

**Screenshot Placeholder**: `onboarding-auto-trigger.png` - Show the automatic redirect after first login

#### Manual Access

If you've skipped the wizard or want to restart it:

1. Log in to your CRM account
2. Navigate to **Settings** → **Other Settings** → **Onboarding**
3. Click the **Restart Onboarding** button
4. Confirm the restart in the modal dialog

**Screenshot Placeholder**: `onboarding-settings-restart.png` - Settings page with restart button

### System Requirements

- Active user account with administrator privileges
- Modern web browser (Chrome, Firefox, Safari, or Edge)
- Stable internet connection for email integration testing

### Time Commitment

| Step | Estimated Time | Required |
|------|---------------|----------|
| Company Setup | 3 minutes | Yes |
| User Creation | 2 minutes | No |
| Pipeline Configuration | 5 minutes | No |
| Email Integration | 4 minutes | No |
| Sample Data Import | 1 minute | No |
| **Total** | **~15 minutes** | |

---

## Step-by-Step Guide

### Welcome Screen

When you start the onboarding wizard, you'll see a welcome screen that provides an overview of what to expect.

**Screenshot Placeholder**: `onboarding-welcome-screen.png` - Full welcome page showing all steps

#### What You'll See

- **Welcome Message**: Personalized greeting with CRM name
- **Overview**: List of all five setup steps with:
  - Step icon and title
  - Brief description
  - Estimated time
  - Required/Optional indicator
- **Feature Highlights**: Three key benefits:
  - ⚡ Quick Setup (~15 minutes total)
  - 💾 Auto-Save (progress saved automatically)
  - 🎯 Flexible (skip optional steps)
- **Get Started Button**: Begins the wizard
- **Resume Link**: If you've already started, shows option to resume from your last step

#### Actions

- Click **Get Started** to begin with Step 1: Company Setup
- Click **Resume from "[Step Name]"** to continue where you left off (if applicable)

---

### Step 1: Company Setup

This required step collects your company's basic information to personalize your CRM experience.

**Screenshot Placeholder**: `onboarding-step1-company-setup.png` - Company setup form with all fields

#### What Information Is Collected

| Field | Required | Description |
|-------|----------|-------------|
| Company Name | Yes | Your official company or business name |
| Industry | No | Your primary business sector (helps provide relevant features) |
| Company Size | No | Number of employees (optimizes dashboard layout) |
| Phone | No | Main company phone number with country code |
| Website | No | Company website URL (must include https:// or http://) |
| Address | No | Full business address for invoices and documents |

#### Step-by-Step Instructions

1. **Enter Company Name** (Required)
   - Type your official company or business name
   - This will appear in reports, email signatures, and customer-facing documents
   - Example: "Acme Corporation"

2. **Select Industry** (Optional)
   - Choose from the dropdown menu
   - Available options include: Technology, Healthcare, Finance, Retail, Manufacturing, Real Estate, Consulting, etc.
   - Helps provide industry-specific features and insights

3. **Select Company Size** (Optional)
   - Choose the range that matches your employee count
   - Options: 1-10, 11-50, 51-200, 201-500, 500+
   - Optimizes dashboard features and user limits

4. **Enter Phone Number** (Optional)
   - Include country code for international numbers
   - Example: "+1 (555) 123-4567"

5. **Enter Website** (Optional)
   - Must include protocol (https:// or http://)
   - Example: "https://www.acmecorp.com"

6. **Enter Address** (Optional)
   - Full street address, city, state/province, postal code, country
   - Appears on invoices and official documents
   - Can be multiple lines

7. Click **Continue** to proceed to the next step

**Screenshot Placeholder**: `onboarding-step1-filled.png` - Form with sample data filled in

#### UI Elements

- **Progress Indicator**: Shows you're on step 1 of 5 (top of page)
- **Progress Bar**: Visual bar showing 20% completion
- **Help Sidebar**: Contextual help panel on the right with:
  - Quick tips about company information
  - Resource links
  - Pro tip callout
- **Info Panel**: Blue information box explaining why this information is needed
- **Field Help Icons**: Question mark icons with tooltips for each field
- **Navigation Buttons**:
  - **Continue**: Saves data and moves to next step
  - **Skip**: Not available (this step is required)

#### Validation

- Company Name is required and must be at least 2 characters
- Website must be a valid URL format if provided
- All other fields are optional

#### Pro Tips

💡 **Info Panel Guidance**: The blue info panel at the top explains: "Your company information personalizes your CRM experience and appears in reports, email templates, and customer-facing documents."

💡 **Update Later**: You can update these details anytime from Settings → Company Settings

💡 **Minimal Required**: Only company name is required to continue - you can add other details later

---

### Step 2: User Creation

This step allows you to add your first team member and invite them to the CRM.

**Screenshot Placeholder**: `onboarding-step2-user-creation.png` - User creation form

#### What Information Is Collected

| Field | Required | Description |
|-------|----------|-------------|
| Full Name | Yes | Team member's first and last name |
| Email Address | Yes | Valid email for login and notifications |
| Role | Yes | User role defining permissions (Sales Rep, Manager, Admin) |
| Send Invitation | No | Checkbox to send invitation email immediately |

#### Step-by-Step Instructions

1. **Enter Full Name**
   - Type the team member's first and last name
   - Example: "Jane Smith"

2. **Enter Email Address**
   - Must be a valid, unique email address
   - This will be used for login and receiving notifications
   - Example: "jane.smith@acmecorp.com"

3. **Select Role**
   - Choose from available roles:
     - **Admin**: Full system access and configuration
     - **Manager**: Manage team members and view all data
     - **Sales Rep**: Manage own leads, contacts, and deals
   - Role determines what features and data the user can access

4. **Send Invitation Email** (Optional)
   - Check the box to send an invitation email immediately
   - Email includes:
     - Welcome message
     - Temporary password
     - Login instructions
     - Getting started guide
   - If unchecked, you'll need to share credentials manually

5. Click **Continue** to save and proceed, or **Skip** if you want to add users later

**Screenshot Placeholder**: `onboarding-step2-filled.png` - Form with sample data and invitation checkbox

#### UI Elements

- **Progress Indicator**: Shows you're on step 2 of 5
- **Progress Bar**: 40% completion
- **Help Sidebar**: Tips about user management and roles
- **Role Descriptions**: Hover over role names to see permission details
- **Navigation Buttons**:
  - **Previous**: Return to Company Setup
  - **Continue**: Save and proceed to Pipeline Configuration
  - **Skip**: Skip this step (if allowed in configuration)

#### What Happens

- User account is created with the selected role
- Random secure password is auto-generated
- If invitation is selected:
  - Email is sent to the user with login credentials
  - User receives a welcome notification
- User metadata is stored for tracking

#### Pro Tips

💡 **Multiple Users**: You can add more team members later from Settings → Users

💡 **Role Selection**: Start with "Sales Rep" for most team members - you can promote them later

💡 **Invitation Email**: Highly recommended to send invitation email for better user experience

💡 **Skip if Solo**: If you're the only user, feel free to skip this step

---

### Step 3: Pipeline Configuration

Configure your sales pipeline stages to track deals through your sales process.

**Screenshot Placeholder**: `onboarding-step3-pipeline-config.png` - Pipeline configuration with stages

#### What Is a Sales Pipeline?

A sales pipeline is a visual representation of where your prospects are in your sales process. Each stage represents a step in your sales journey, from initial contact to closing the deal.

#### Default Pipeline Stages

The wizard provides default stages that work for most businesses:

| Stage | Win Probability | Description |
|-------|----------------|-------------|
| New | 10% | Initial contact or inquiry |
| Qualified | 20% | Prospect meets criteria and shows interest |
| Proposal | 40% | Formal proposal or quote sent |
| Negotiation | 60% | Terms and pricing under discussion |
| Won | 100% | Deal successfully closed |
| Lost | 0% | Deal lost to competitor or abandoned |

#### Step-by-Step Instructions

##### Option 1: Use Default Pipeline Template

1. Review the default stages shown
2. Click **Continue** to use these stages as-is
3. You can customize them later from Settings → Pipelines

**Screenshot Placeholder**: `onboarding-step3-default-stages.png` - Default pipeline stages preview

##### Option 2: Customize Pipeline Stages

1. **Edit Existing Stages**:
   - Click on any stage name to edit it
   - Modify the stage name
   - Adjust the win probability (0-100%)
   - Click the checkmark to save

2. **Reorder Stages**:
   - Drag and drop stages to change their order
   - Stages should flow from initial contact to close
   - Won and Lost stages typically come last

3. **Add New Stages**:
   - Click the **+ Add Stage** button
   - Enter stage name (e.g., "Demo Scheduled")
   - Set win probability percentage
   - Click **Add** to save

4. **Remove Stages**:
   - Click the **×** (delete) icon next to any stage
   - Confirm deletion in the modal
   - Note: You must have at least 2 stages

5. Click **Continue** to save your pipeline configuration

**Screenshot Placeholder**: `onboarding-step3-custom-stages.png` - Customized pipeline with drag-and-drop

##### Option 3: Skip and Use Default

1. Click **Skip** to automatically create a default pipeline
2. Default pipeline will be created with standard stages
3. You can customize it later from Settings → Pipelines

#### UI Elements

- **Progress Indicator**: Shows you're on step 3 of 5
- **Progress Bar**: 60% completion
- **Drag Handles**: Three-line icons for reordering stages
- **Stage Cards**: Each stage shows:
  - Stage name
  - Win probability
  - Edit and delete buttons
- **Add Stage Button**: Plus icon to add new stages
- **Help Sidebar**: Explains pipeline best practices
- **Navigation Buttons**:
  - **Previous**: Return to User Creation
  - **Continue**: Save pipeline and proceed
  - **Skip**: Create default pipeline and skip customization

#### Validation

- Pipeline must have at least 2 stages
- Stage names must be unique
- Win probability must be between 0-100%
- Stage codes are auto-generated from names (e.g., "Demo Scheduled" → "demo-scheduled")

#### Pro Tips

💡 **Start Simple**: Use the default stages to begin - you can refine them as you learn your sales process

💡 **Win Probability**: Set realistic probabilities based on your conversion rates at each stage

💡 **Stage Order**: Arrange stages in chronological order from first contact to close

💡 **Multiple Pipelines**: You can create additional pipelines later for different sales processes

💡 **Best Practice**: Keep 4-7 stages for optimal usability - too many stages become hard to manage

---

### Step 4: Email Integration

Connect your email account to send and receive emails directly from the CRM.

**Screenshot Placeholder**: `onboarding-step4-email-integration.png` - Email integration form with provider selection

#### Supported Email Providers

- **SMTP** (Generic): Any email provider with SMTP access
- **Gmail**: Google Workspace or personal Gmail accounts
- **Outlook**: Microsoft 365 or Outlook.com accounts
- **SendGrid**: SendGrid transactional email service

#### Why Integrate Email?

- Send emails directly from contact and deal records
- Track email opens and clicks
- Keep communication history in one place
- Use email templates for faster responses
- Automate follow-up sequences

#### Step-by-Step Instructions

##### Option 1: Quick Setup with Gmail

1. **Select Provider**: Choose "Gmail" from the dropdown

2. **Pre-filled Settings** appear automatically:
   - SMTP Host: smtp.gmail.com
   - SMTP Port: 587
   - Encryption: TLS

3. **Enter Your Gmail Address**
   - Example: "your-email@gmail.com"

4. **Enter App Password** (not your regular Gmail password)
   - Go to Google Account → Security → 2-Step Verification → App Passwords
   - Generate a new app password for "Mail"
   - Copy and paste the 16-character password

5. **Test Connection** (Recommended)
   - Check the "Test connection before saving" box
   - System sends a test email to verify settings
   - Green checkmark appears if successful

6. Click **Continue** to save and proceed

**Screenshot Placeholder**: `onboarding-step4-gmail.png` - Gmail configuration with pre-filled settings

##### Option 2: Quick Setup with Outlook

1. **Select Provider**: Choose "Outlook" from the dropdown

2. **Pre-filled Settings** appear:
   - SMTP Host: smtp.office365.com
   - SMTP Port: 587
   - Encryption: TLS

3. **Enter Outlook Email**
   - Example: "your-email@outlook.com" or "you@yourcompany.com"

4. **Enter Password**
   - Your regular Outlook/Microsoft 365 password
   - Or app password if you have 2FA enabled

5. **Test Connection** and click **Continue**

**Screenshot Placeholder**: `onboarding-step4-outlook.png` - Outlook configuration

##### Option 3: Manual SMTP Configuration

1. **Select Provider**: Choose "SMTP (Generic)"

2. **Enter SMTP Host**
   - Your email provider's SMTP server address
   - Example: "mail.yourdomain.com"

3. **Enter SMTP Port**
   - Common ports: 25, 465, 587, 2525
   - Check with your email provider

4. **Select Encryption**
   - TLS (recommended for port 587)
   - SSL (for port 465)
   - None (not recommended)

5. **Enter Username**
   - Usually your full email address
   - Some providers use just the username part

6. **Enter Password**
   - Your email account password

7. **Test Connection** and click **Continue**

**Screenshot Placeholder**: `onboarding-step4-smtp.png` - Manual SMTP configuration

##### Option 4: Skip Email Integration

1. Click **Skip** to set up email later
2. You can configure email integration anytime from:
   - Settings → Email Integration
   - Or restart the onboarding wizard

#### UI Elements

- **Progress Indicator**: Shows you're on step 4 of 5
- **Progress Bar**: 80% completion
- **Provider Dropdown**: Select email provider
- **Dynamic Fields**: Fields change based on selected provider
- **Test Connection Checkbox**: Option to verify settings before saving
- **Quick Setup Guide**: Expandable section with provider-specific instructions
- **Help Sidebar**: Links to email setup guides for each provider
- **Navigation Buttons**:
  - **Previous**: Return to Pipeline Configuration
  - **Continue**: Save settings and proceed
  - **Skip**: Skip email integration (can set up later)

#### Validation

- Email address must be valid format
- SMTP host is required
- SMTP port must be a valid port number (1-65535)
- Username and password are required
- If testing is enabled, connection must succeed before continuing

#### Connection Test

When you enable "Test connection before saving":
1. System temporarily configures mail settings
2. Creates SMTP transport connection
3. Attempts to authenticate
4. Shows success or error message
5. If successful, settings are saved
6. If failed, error message shows what went wrong

#### Troubleshooting Connection Issues

**Screenshot Placeholder**: `onboarding-step4-error.png` - Connection error with helpful message

Common issues and solutions:

| Error | Solution |
|-------|----------|
| Authentication failed | Verify username and password are correct |
| Connection timeout | Check SMTP host and port |
| SSL/TLS error | Try different encryption method (TLS vs SSL) |
| Blocked by firewall | Check with IT - port 587 or 465 may be blocked |
| App password required | Gmail/Outlook with 2FA needs app-specific password |

#### Pro Tips

💡 **Skip for Now**: Email integration is optional - skip it if you're not ready

💡 **Test Connection**: Always test the connection to catch issues early

💡 **App Passwords**: Gmail and Outlook with 2FA require app-specific passwords, not your regular password

💡 **Work Email**: Use your company email domain (not personal Gmail) for professional communication

💡 **SendGrid**: For high-volume email, consider using SendGrid or similar transactional email service

---

### Step 5: Sample Data Import

Import realistic sample data to explore CRM features before adding your own data.

**Screenshot Placeholder**: `onboarding-step5-sample-data.png` - Sample data import options

#### What Sample Data Includes

When you import sample data, the system creates:

| Data Type | Quantity | Description |
|-----------|----------|-------------|
| Organizations | 5 companies | Sample businesses with addresses and contact info |
| Persons | 10-15 contacts | Sample people with job titles, emails, phones |
| Deals | 10 opportunities | Sample deals across different pipeline stages |
| Lead Sources | 3 sources | Website, Referral, Email Campaign |
| Lead Types | 2 types | New Business, Existing Customer |

#### Why Import Sample Data?

- **Learn the System**: Explore features with realistic data
- **See Relationships**: Understand how organizations, contacts, and deals connect
- **Test Reports**: Generate sample reports and dashboards
- **Training**: Train team members without affecting real data
- **Templates**: Use samples as templates for your own data

#### Step-by-Step Instructions

##### Option 1: Import All Sample Data

1. Leave all checkboxes selected:
   - ✅ Import sample companies
   - ✅ Import sample contacts
   - ✅ Import sample deals

2. Review what will be imported (shown in info panel)

3. Click **Continue** to import all sample data

**Screenshot Placeholder**: `onboarding-step5-all-selected.png` - All options checked

##### Option 2: Selective Import

1. **Select What to Import**:
   - **Import sample companies** (5 organizations)
     - Acme Corporation
     - TechStart Solutions
     - Global Enterprises
     - Innovation Labs
     - Blue Ocean Industries

   - **Import sample contacts** (10-15 people)
     - Requires companies to be imported first
     - 2-3 contacts per company with job titles

   - **Import sample deals** (10 opportunities)
     - Requires contacts to be imported first
     - Distributed across pipeline stages
     - Realistic values ($5K - $100K)

2. **Dependencies**:
   - Contacts require companies (auto-checked)
   - Deals require contacts (auto-checked)
   - If you uncheck companies, contacts and deals are disabled

3. Click **Continue** to import selected data

**Screenshot Placeholder**: `onboarding-step5-selective.png` - Some options selected showing dependencies

##### Option 3: Skip Sample Data

1. Click **Skip** to proceed without sample data
2. You'll start with a clean CRM
3. Add your own real data immediately

#### UI Elements

- **Progress Indicator**: Shows you're on step 5 of 5 (final step!)
- **Progress Bar**: 100% completion when you click Continue
- **Checkbox Options**: Three customization options
- **Info Panel**: Shows what will be imported
- **Warning Panel**: Explains data can be deleted later
- **Preview List**: Shows sample organization names
- **Help Sidebar**: Tips about sample data
- **Navigation Buttons**:
  - **Previous**: Return to Email Integration
  - **Complete Setup**: Import data and finish wizard
  - **Skip**: Skip sample data and finish wizard

#### What Happens During Import

1. **Auto-Creates Prerequisites**:
   - Default pipeline (if not exists)
   - Lead sources (Website, Referral, Email Campaign)
   - Lead types (New Business, Existing Customer)

2. **Imports Data in Order**:
   - First: Organizations with addresses
   - Second: Persons linked to organizations
   - Third: Deals linked to persons and organizations

3. **Sets Metadata**:
   - Marks each record as sample data
   - Stores import counts in configuration
   - Records completion timestamp

4. **Database Transaction**:
   - All imports happen in a transaction
   - If any error occurs, everything rolls back
   - Ensures data integrity

#### Sample Organizations Created

1. **Acme Corporation** - 123 Main St, New York, NY
2. **TechStart Solutions** - 456 Innovation Dr, San Francisco, CA
3. **Global Enterprises** - 789 Corporate Blvd, Chicago, IL
4. **Innovation Labs** - 321 Research Pkwy, Boston, MA
5. **Blue Ocean Industries** - 654 Industrial Way, Seattle, WA

#### Sample Deals Distribution

Deals are distributed across pipeline stages:
- **New**: 2 deals
- **Qualified**: 2 deals
- **Proposal**: 2 deals
- **Negotiation**: 2 deals
- **Won**: 1 deal
- **Lost**: 1 deal

#### Deleting Sample Data Later

To remove sample data after exploring:
1. Navigate to Settings → Data Management
2. Select "Sample Data Cleanup"
3. Choose what to delete
4. Confirm deletion

**Note**: This feature may require custom implementation

#### Pro Tips

💡 **Recommended for New Users**: Import sample data if this is your first CRM - it helps you learn faster

💡 **Training Mode**: Great for training team members before going live with real data

💡 **Easy to Remove**: Sample data can be deleted in bulk when you're ready for real data

💡 **Skip if Migrating**: If you're migrating from another CRM, skip sample data and import your real data

💡 **Explore First**: Use sample data to test workflows, reports, and automations before production use

---

### Completion Screen

Congratulations! You've successfully completed the onboarding wizard.

**Screenshot Placeholder**: `onboarding-complete.png` - Completion screen with confetti animation

#### What You'll See

1. **Success Animation**:
   - Colorful confetti animation celebrates your completion
   - Large checkmark icon in green circle
   - "🎉 Setup Complete!" headline

2. **Completion Summary**:
   - **Steps Completed**: Number of steps you finished (e.g., 3 steps)
   - **Steps Skipped**: Number of steps you skipped (e.g., 2 steps)
   - **Minutes Spent**: Total time in the wizard (e.g., 12 minutes)

3. **Configured Features List**:
   - Checkmark for each completed step:
     - ✅ Company Setup
     - ✅ Pipeline Configuration
     - ✅ Sample Data Import

4. **What's Next Section** with three action cards:
   - **🏠 Go to Dashboard**: Start managing leads, contacts, and deals
   - **📚 Explore Documentation**: Learn about advanced features
   - **👥 Invite More Team Members**: Add your team and collaborate

5. **Help Resources**:
   - Blue info panel with support options
   - "Need Help?" section
   - Links to Contact Support and Help Center

6. **Main CTA Button**:
   - Large "Start Using [CRM Name]" button
   - Redirects to dashboard or configured redirect URL

7. **Restart Option** (if enabled):
   - "Want to change something?"
   - Link to restart the wizard
   - Confirmation dialog before restarting

**Screenshot Placeholder**: `onboarding-complete-summary.png` - Close-up of completion summary statistics

#### Actions Available

1. **Start Using the CRM**:
   - Click the main blue button
   - Redirects to dashboard
   - Stored "intended URL" (if you were redirected to onboarding) or default dashboard

2. **Explore Documentation**:
   - Click the documentation card
   - Opens help documentation in new tab

3. **Invite Team Members**:
   - Click the team members card
   - Opens user management page to add more users

4. **Contact Support**:
   - Click "Contact Support" link
   - Opens support ticket form

5. **Restart Wizard** (optional):
   - Click "Restart the wizard" link
   - Confirms with dialog: "Are you sure you want to restart the setup wizard? Your current configuration will remain, but you can update settings."
   - Resets progress and returns to welcome screen

#### What Happens in the Background

1. **Progress Marked Complete**:
   - `is_completed` flag set to true
   - `completed_at` timestamp recorded

2. **Completion Email Sent** (if configured):
   - Welcome email sent to user
   - Includes:
     - Congratulations message
     - Completion statistics
     - Next steps recommendations
     - Quick start guide
     - Resource links

3. **Redirect Blocked**:
   - Onboarding middleware no longer redirects you to wizard
   - You can access all CRM features normally

4. **Dashboard Stats Updated**:
   - Admin dashboard shows onboarding completion metrics
   - Completion rate and average time updated

#### Completion Email

**Screenshot Placeholder**: `onboarding-completion-email.png` - Email template preview

The completion email includes:
- **Subject**: "Welcome to [CRM Name] - Setup Complete! 🎉"
- **Personalized Greeting**: "Hi [User Name],"
- **Completion Stats**: Steps completed, time spent
- **Next Steps**: 4 recommended actions
- **Quick Start Guide**: Color-coded sections for key features
- **Resources**: Links to documentation, tutorials, support, community
- **CTA Button**: "Go to Dashboard"

#### Pro Tips

💡 **Take the Tour**: After completion, consider taking the interactive dashboard tour (if available)

💡 **Bookmark Resources**: Save links to documentation and support for quick access

💡 **Invite Team**: Add team members early so they can help build your CRM data

💡 **Set Goals**: Before diving in, define what success looks like for your CRM implementation

💡 **Start Small**: Begin with one feature (e.g., contact management) and expand from there

---

## Resuming the Wizard

The onboarding wizard automatically saves your progress at each step, allowing you to pause and resume anytime.

### How Auto-Save Works

- Progress is saved when you click **Continue** on any step
- Your current step is recorded in the database
- Completed steps are tracked
- Skipped steps are marked separately

### How to Resume

#### Method 1: Automatic Redirect

If you haven't completed onboarding:
1. Log in to the CRM
2. You'll be automatically redirected to your current step
3. Continue from where you left off

**Screenshot Placeholder**: `onboarding-auto-resume.png` - Login redirect to current step

#### Method 2: From Welcome Screen

If you're on the welcome screen:
1. Look for the "Already started?" message
2. Click the "Resume from [Step Name]" link
3. System loads your saved progress

**Screenshot Placeholder**: `onboarding-welcome-resume-link.png` - Resume link on welcome screen

#### Method 3: Direct URL

You can also navigate directly to your last step:
- URL format: `/onboarding/step/{step-name}`
- Example: `/onboarding/step/pipeline_config`

### What's Preserved

When you resume, the following is preserved:
- ✅ All completed steps (marked with checkmark in progress indicator)
- ✅ All skipped steps (marked with X in progress indicator)
- ✅ Your current/next incomplete step
- ✅ Progress percentage and duration tracking
- ❌ Form data from incomplete steps (not preserved - use your browser's form auto-fill)

### What Happens to Form Data

- **Completed Steps**: Data is saved in the database and config
- **Current Step**: Form data is NOT auto-filled unless you completed it previously
- **Incomplete Steps**: No data is saved

### Reviewing Completed Steps

To review or change data from completed steps:
1. Use the **Previous** button to navigate backward
2. Review the data (loaded from database)
3. Make changes if needed
4. Click **Continue** to save updates

### Pro Tips

💡 **Mobile Friendly**: You can start on desktop and resume on mobile - progress syncs by user account

💡 **No Time Limit**: Take as long as you need - there's no expiration on saved progress

💡 **Safe to Logout**: Feel free to log out between steps - your progress is saved

💡 **Update Anytime**: You can go back to previous steps to update information

---

## Skipping Steps

The onboarding wizard allows you to skip optional steps, giving you flexibility in how you set up your CRM.

### Which Steps Can Be Skipped?

| Step | Skippable? | Notes |
|------|-----------|-------|
| Company Setup | ❌ No | Required information for CRM personalization |
| User Creation | ✅ Yes | Can add users later from Settings |
| Pipeline Configuration | ✅ Yes | Default pipeline created automatically if skipped |
| Email Integration | ✅ Yes | Can configure later from Settings |
| Sample Data Import | ✅ Yes | Can be imported later if needed |

**Note**: Skippability can be configured by your CRM administrator via `config/onboarding.php`

### How to Skip a Step

**Screenshot Placeholder**: `onboarding-skip-button.png` - Skip button visible on optional step

1. Look for the **Skip** button next to the Continue button
2. Click **Skip**
3. A confirmation modal appears (if enabled in configuration)
4. Confirm that you want to skip this step
5. System marks the step as skipped and moves to the next step

**Screenshot Placeholder**: `onboarding-skip-confirmation.png` - Skip confirmation modal

### What Happens When You Skip

1. **Step Marked as Skipped**:
   - Saved in `skipped_steps` array in database
   - Shown with ✗ (X) mark in progress indicator
   - Included in completion statistics

2. **Default Behavior**:
   - Some steps have default actions when skipped:
     - **Pipeline Configuration**: Creates default pipeline with standard stages
     - **Email Integration**: No email service configured
     - **User Creation**: No additional user created
     - **Sample Data**: No sample data imported

3. **Progress Updates**:
   - Current step advances to next step
   - Progress percentage increases
   - Duration continues tracking

### Skipped Steps in Progress Indicator

**Screenshot Placeholder**: `onboarding-progress-with-skipped.png` - Progress indicator showing mix of completed and skipped steps

Visual indicators:
- ✅ Green checkmark: Completed step
- ⏺️ Blue circle: Current step (highlighted)
- ✗ Gray X: Skipped step
- ⚪ Gray circle: Pending step

### Going Back to Skipped Steps

You can complete a skipped step later:

#### During Wizard

1. Click **Previous** to navigate back
2. Complete the step
3. Click **Continue** to save
4. Step is removed from `skipped_steps` and added to `completed_steps`

#### After Wizard Completion

1. Navigate to Settings → Onboarding
2. Click **Restart Onboarding**
3. Complete the previously skipped steps
4. Skip or complete other steps as desired

### Skip vs. Continue Without Data

- **Skip**: Explicitly marks step as skipped, may trigger defaults
- **Continue Without Data**: Submits form without optional fields (validation may prevent this)

### Global Skip Configuration

Administrators can disable skipping globally:
- Set `ONBOARDING_ALLOW_SKIP=false` in `.env`
- Or edit `config/onboarding.php`: `'allow_skip' => false`
- When disabled, only required steps must be completed
- Skip button hidden for all steps

### Pro Tips

💡 **Skip to Get Started Faster**: Skip optional steps to get to the dashboard quickly, configure later

💡 **Skip Complex Steps**: If you're not ready for email integration, skip it and set up when you have credentials

💡 **Don't Skip Pipeline**: Unless you have a specific reason, configure your pipeline during onboarding - the default works for most businesses

💡 **Sample Data Decision**: Skip sample data if you're ready to import real data immediately

---

## Restarting the Wizard

You can restart the onboarding wizard to review or change your configuration.

### When to Restart

- Change company information
- Add or remove pipeline stages
- Reconfigure email integration
- Import sample data after initially skipping it
- Review setup with a team member
- Fix mistakes made during initial setup

### How to Restart

**Screenshot Placeholder**: `onboarding-restart-settings.png` - Settings page with restart section

#### From Settings (Recommended)

1. Navigate to **Settings** → **Other Settings** → **Onboarding**
2. Review your current progress:
   - Completion status
   - Completed steps count
   - Skipped steps count
   - Progress percentage
3. Click the **Restart Onboarding** button
4. Confirm in the warning modal:
   - "Are you sure you want to restart the onboarding wizard?"
   - "Your current configuration will remain, but you can update settings."
5. Click **Restart** to confirm
6. Redirected to the welcome screen

**Screenshot Placeholder**: `onboarding-restart-modal.png` - Restart confirmation modal

#### From Completion Screen

If you just completed the wizard:
1. At the bottom of completion screen, find "Want to change something?"
2. Click **Restart the wizard** link
3. Confirm in modal dialog
4. Redirected to welcome screen

### What Happens When You Restart

1. **Progress Reset**:
   - `current_step` reset to first step
   - `completed_steps` array cleared
   - `skipped_steps` array cleared
   - `is_completed` set to false
   - New `started_at` timestamp

2. **Configuration Preserved**:
   - Your company information remains saved
   - Users you created are not deleted
   - Pipeline stages remain configured
   - Email settings remain saved
   - Sample data remains imported

3. **Can Re-do Steps**:
   - Complete steps again with updated information
   - Skip different steps than before
   - Change your configuration choices

### Restart Restrictions

The restart feature can be disabled by administrators:
- Set `ONBOARDING_ALLOW_RESTART=false` in `.env`
- Or edit `config/onboarding.php`: `'allow_restart' => false`
- When disabled, restart button is hidden
- Prevents users from repeatedly restarting

### Restart vs. Manual Configuration

| Action | When to Use |
|--------|-------------|
| **Restart Wizard** | Major changes, reviewing all steps, training |
| **Settings Pages** | Individual changes to company, users, pipeline, email |

### After Restarting

- You'll go through all steps again
- Pre-existing configuration will pre-fill forms
- You can change any settings
- Completion triggers the same actions (email, dashboard stats, etc.)

### Pro Tips

💡 **Configuration Remains**: Restarting doesn't delete your data - it just resets your progress through the wizard

💡 **Training Tool**: Restart the wizard to train new administrators on the setup process

💡 **Quick Updates**: For small changes, use Settings pages instead of restarting entire wizard

💡 **Review Periodically**: Restart the wizard quarterly to review and optimize your configuration

---

## Tips and Best Practices

### Before You Start

#### 1. Gather Information

Collect the following before starting:
- ✅ Company name, industry, size, address
- ✅ Team member names, emails, and intended roles
- ✅ Your sales process stages
- ✅ Email credentials (SMTP host, port, username, password)

#### 2. Allocate Time

- Block 15-20 minutes of uninterrupted time
- Choose a quiet time without meetings
- Complete in one session for best experience (though you can resume later)

#### 3. Choose Your Device

- Desktop or laptop recommended for best experience
- Mobile works but forms are easier to complete on larger screens
- Tablet is a good middle ground

### During the Wizard

#### 1. Read Help Text

- Click the ❓ question mark icons for field-specific help
- Review the help sidebar on each step
- Read info panels for important context

#### 2. Use Realistic Data

- Enter actual company information, not test data
- Use real email addresses for team members
- Configure your actual sales pipeline stages
- This saves rework later

#### 3. Test Email Connection

- Always test email connection before proceeding
- Catch configuration errors early
- Prevents email sending issues later

#### 4. Start Simple with Pipeline

- Use default pipeline stages if unsure
- You can refine stages later as you learn your sales process
- Avoid creating too many stages (4-7 is optimal)

#### 5. Consider Sample Data

- Import sample data if this is your first CRM
- Use it to learn features before adding real data
- Skip it if you're migrating from another system

### After Completion

#### 1. Explore Sample Data

If you imported sample data:
- Click through organizations, contacts, and deals
- Test creating a new deal
- Try running a report
- Understand data relationships

#### 2. Customize Further

- Add custom fields for unique business needs
- Configure email templates
- Set up workflow automations
- Create dashboard widgets

#### 3. Import Real Data

- Use CSV import for contacts and companies
- Import deals manually or via API
- Clean up or delete sample data

#### 4. Invite Your Team

- Add all team members with appropriate roles
- Send invitation emails
- Set up a training session

#### 5. Configure Integrations

- Connect third-party apps (calendars, marketing tools)
- Set up webhooks for real-time data sync
- Enable two-factor authentication

### General Best Practices

#### 1. Company Information

- Use official company name as it appears on documents
- Complete address for professional invoices
- Include country code in phone numbers
- Use company email domain (not personal Gmail)

#### 2. User Management

- Start with fewer roles, add more as needed
- Use "Sales Rep" role for most team members
- Reserve "Admin" for IT/management
- Send invitation emails for better onboarding

#### 3. Pipeline Configuration

- Mirror your actual sales process
- Use clear, action-oriented stage names
- Set realistic win probabilities based on historical data
- Order stages chronologically

#### 4. Email Integration

- Use transactional email service (SendGrid) for high volume
- Enable 2FA and use app passwords
- Test with a real recipient
- Configure from/reply-to addresses correctly

#### 5. Sample Data

- Use sample data for training purposes
- Don't mix sample and real data
- Delete sample data before going live
- Create your own sample data for ongoing training

### Time-Saving Tips

💡 **Use Browser Auto-fill**: Let your browser remember form data

💡 **Copy from Documentation**: Copy email settings from your provider's docs

💡 **Skip and Return**: Skip complex steps, complete simple ones first, return later

💡 **Bulk User Import**: If adding many users, skip wizard step and use CSV import from Settings

💡 **API for Large Datasets**: For 1000+ contacts, use API import instead of UI

### Common Mistakes to Avoid

❌ **Skipping Company Setup**: This is required and personalizes your CRM

❌ **Using Test Data**: Enter real data to avoid rework

❌ **Not Testing Email**: Always test email connection to avoid delivery issues

❌ **Too Many Pipeline Stages**: Keep it simple (4-7 stages)

❌ **Ignoring Help Text**: Read tooltips and help panels for guidance

❌ **Rushing Through**: Take time to configure correctly the first time

❌ **Mixing Sample and Real Data**: Keep them separate or skip sample data

---

## Troubleshooting

### Progress Not Saving

**Symptoms**:
- Clicking Continue doesn't advance to next step
- Progress bar doesn't update
- Current step doesn't change

**Possible Causes & Solutions**:

1. **JavaScript Error**:
   - Open browser console (F12 → Console tab)
   - Look for red error messages
   - Refresh the page and try again

2. **Network Issue**:
   - Check internet connection
   - Look for failed network requests in browser dev tools (F12 → Network tab)
   - Try again when connection is stable

3. **Session Expired**:
   - You may have been logged out
   - Refresh the page and log in again
   - Your progress should be saved up to the last successful step

4. **Form Validation Error**:
   - Look for red error messages below form fields
   - Fill in all required fields
   - Correct any validation errors

**Screenshot Placeholder**: `troubleshooting-validation-errors.png` - Form with validation error messages

### Cannot Skip Optional Steps

**Symptoms**:
- Skip button is not visible
- Skip button is grayed out/disabled

**Possible Causes & Solutions**:

1. **Step is Required**:
   - Company Setup cannot be skipped
   - Check if the step has a "Required" indicator

2. **Skip Disabled Globally**:
   - Administrator may have disabled skipping
   - Contact your system admin
   - Complete all steps or ask admin to enable skipping

3. **Configuration Error**:
   - Check `config/onboarding.php` → `'allow_skip'` setting
   - Should be `true` to enable skipping

### Email Connection Test Fails

**Symptoms**:
- "Connection failed" error message
- "Authentication error" message
- "SMTP timeout" error

**Possible Causes & Solutions**:

1. **Wrong Credentials**:
   - Double-check username (usually full email address)
   - Verify password (copy-paste to avoid typos)
   - For Gmail/Outlook with 2FA, use app password, not regular password

2. **Incorrect SMTP Settings**:
   - Verify SMTP host (e.g., smtp.gmail.com)
   - Check SMTP port (587 for TLS, 465 for SSL)
   - Ensure encryption matches port (TLS for 587, SSL for 465)

3. **Firewall Blocking**:
   - Check if corporate firewall blocks SMTP ports
   - Try different port (587, 465, 2525)
   - Contact IT department

4. **Email Provider Restrictions**:
   - Some providers block "less secure apps"
   - Enable SMTP access in email provider settings
   - Use OAuth for Gmail (if supported)

5. **Network Timeout**:
   - Check internet connection
   - Try again after a few minutes
   - Contact email provider support

**Screenshot Placeholder**: `troubleshooting-email-error.png` - Email connection error with details

**Solutions by Provider**:

**Gmail**:
- Enable "Less secure app access" (if not using 2FA)
- With 2FA: Generate app password at https://myaccount.google.com/apppasswords
- Use app password instead of regular password

**Outlook/Microsoft 365**:
- Enable SMTP AUTH if disabled
- With 2FA: Generate app password in account settings
- Verify your plan allows SMTP sending

**Custom SMTP**:
- Contact email provider for correct settings
- Verify SMTP is enabled on your account
- Check for any IP restrictions

### Sample Data Import Fails

**Symptoms**:
- Error message during import
- Import takes very long
- Partial data imported

**Possible Causes & Solutions**:

1. **Database Error**:
   - Check database connection
   - Ensure database user has write permissions
   - Check database storage space

2. **Missing Dependencies**:
   - Ensure pipeline exists (auto-created if missing)
   - Lead sources and types created automatically
   - Check for database foreign key constraints

3. **Timeout**:
   - Increase PHP max_execution_time
   - Import may take 30-60 seconds for full dataset
   - Refresh page and check if data exists

4. **Transaction Rollback**:
   - If any error occurs, entire import rolls back
   - Check Laravel logs: `storage/logs/laravel.log`
   - Fix the error and retry

### Wizard Doesn't Auto-Trigger

**Symptoms**:
- New user logs in but wizard doesn't appear
- Redirected to dashboard instead of onboarding

**Possible Causes & Solutions**:

1. **Auto-trigger Disabled**:
   - Check `.env` file: `ONBOARDING_AUTO_TRIGGER` should be `true`
   - Or check `config/onboarding.php`: `'auto_trigger' => true`
   - Contact administrator to enable

2. **Middleware Not Active**:
   - Verify middleware is registered
   - Check `app/Http/Kernel.php` → `$middlewareGroups['web']`
   - Should include `RedirectIfOnboardingIncomplete::class`

3. **Already Completed**:
   - User may have completed onboarding previously
   - Check admin dashboard → onboarding stats
   - Manually restart from Settings → Onboarding

4. **Permission Issue**:
   - User may lack permissions
   - Check user's role and permissions
   - Verify onboarding routes are accessible

### Cannot Restart Wizard

**Symptoms**:
- Restart button not visible in Settings
- Restart button is disabled

**Possible Causes & Solutions**:

1. **Restart Disabled**:
   - Check `.env`: `ONBOARDING_ALLOW_RESTART` should be `true`
   - Or `config/onboarding.php`: `'allow_restart' => true`
   - Contact administrator to enable

2. **Permission Issue**:
   - Verify your user role has permission to restart
   - May require administrator role

3. **Configuration Cache**:
   - Run `php artisan config:cache` to refresh configuration
   - Try again after cache refresh

### Browser-Specific Issues

**Symptoms**:
- Wizard works in one browser but not another
- Styles look broken
- Interactions don't work

**Solutions**:

1. **Clear Browser Cache**:
   - Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
   - Or clear browser cache manually

2. **Update Browser**:
   - Use latest version of Chrome, Firefox, Safari, or Edge
   - Avoid Internet Explorer (not supported)

3. **Disable Extensions**:
   - Try in incognito/private mode
   - Disable ad blockers and privacy extensions temporarily

4. **JavaScript Required**:
   - Ensure JavaScript is enabled
   - Check console for errors

### Mobile Issues

**Symptoms**:
- Layout looks wrong on mobile
- Cannot click buttons
- Form fields hard to use

**Solutions**:

1. **Use Desktop**:
   - Wizard is optimized for desktop/tablet
   - Complete on desktop for best experience
   - Can resume on desktop if started on mobile

2. **Rotate to Landscape**:
   - More screen space in landscape mode
   - Easier to interact with forms

3. **Update Mobile Browser**:
   - Use latest version of Safari (iOS) or Chrome (Android)

### Getting Additional Help

If you continue experiencing issues:

1. **Check Logs**:
   - Laravel logs: `storage/logs/laravel.log`
   - Browser console (F12 → Console)
   - Network tab for failed requests

2. **Contact Support**:
   - Click "Contact Support" from completion screen
   - Or navigate to Settings → Support
   - Provide:
     - Step where error occurred
     - Error message
     - Browser and version
     - Screenshot of error

3. **Community Forum**:
   - Search existing posts
   - Ask question with details
   - Community members can help

4. **Documentation**:
   - Read full documentation
   - Check API docs for integrations
   - Review video tutorials

---

## FAQ

### General Questions

**Q: How long does the onboarding wizard take?**

A: Approximately 15 minutes if you complete all steps with data ready. You can complete it faster by skipping optional steps (5-10 minutes) or slower if you need to gather information.

---

**Q: Can I skip the wizard entirely?**

A: The Company Setup step is required. You can skip the other steps, but we recommend completing at least the pipeline configuration for the best experience. Contact your administrator if auto-trigger is forcing you through the wizard.

---

**Q: Is my data saved if I close the browser?**

A: Yes, progress is saved when you click Continue on each step. You can safely close your browser and resume later. However, data in incomplete forms is not saved.

---

**Q: Can I go back and change information?**

A: Yes, use the Previous button during the wizard to go back and update. After completing the wizard, use Settings pages or restart the wizard to make changes.

---

**Q: Do I need to complete all steps?**

A: No, only Company Setup is required. All other steps are optional and can be skipped or completed later from Settings.

---

### Company Setup Questions

**Q: What if I don't have a company website yet?**

A: Leave the website field blank. You can add it later from Settings → Company Settings.

---

**Q: Can I change my company name later?**

A: Yes, navigate to Settings → Company Settings to update your company information anytime.

---

**Q: Which industry should I select?**

A: Choose the industry that best describes your primary business. This helps the CRM provide relevant features and industry benchmarks. If unsure, select "Other" or the closest match.

---

**Q: What if my company size changes?**

A: You can update company size in Settings → Company Settings as your business grows.

---

### User Creation Questions

**Q: How many users can I add during onboarding?**

A: The wizard allows you to add one user at a time. To add multiple users, complete the wizard and use Settings → Users → Bulk Import.

---

**Q: What's the difference between roles?**

A:
- **Admin**: Full access to all features, settings, and data
- **Manager**: Can manage team members and view all data, limited settings access
- **Sales Rep**: Can manage own leads, contacts, and deals only

---

**Q: What if I don't send an invitation email?**

A: The user account is still created, but you'll need to manually share the username and temporary password. Sending an invitation email is recommended for better user experience.

---

**Q: Can I change a user's role later?**

A: Yes, navigate to Settings → Users, find the user, and change their role.

---

### Pipeline Questions

**Q: How many pipeline stages should I have?**

A: Most businesses work best with 4-7 stages. Too few and you lack visibility; too many and it becomes hard to manage. Start with default stages and refine based on usage.

---

**Q: Can I have multiple pipelines?**

A: The wizard creates one pipeline. You can create additional pipelines for different sales processes from Settings → Pipelines.

---

**Q: What are win probabilities used for?**

A: Win probabilities help forecast revenue. The CRM multiplies deal value by win probability to calculate weighted pipeline value. Set realistic probabilities based on your historical conversion rates.

---

**Q: Can I delete pipeline stages later?**

A: Yes, from Settings → Pipelines. Note that you cannot delete stages with existing deals - you must move those deals first.

---

**Q: What happens if I skip pipeline configuration?**

A: A default pipeline with standard stages (New, Qualified, Proposal, Negotiation, Won, Lost) is automatically created.

---

### Email Integration Questions

**Q: Do I need to configure email during onboarding?**

A: No, email integration is optional. You can skip it and configure later from Settings → Email Integration.

---

**Q: Can I use my personal Gmail account?**

A: Yes, but we recommend using a company email address (e.g., you@yourcompany.com) for professional communication.

---

**Q: What's an app password and why do I need it?**

A: If you have two-factor authentication (2FA) enabled on Gmail or Outlook, you cannot use your regular password for SMTP. Generate an app-specific password in your account settings and use that instead.

---

**Q: Can I change email settings later?**

A: Yes, navigate to Settings → Email Integration to update SMTP settings, credentials, or provider.

---

**Q: What if my company uses Microsoft Exchange?**

A: Use Outlook settings with your Exchange server's SMTP host. Contact your IT department for the correct SMTP server address and port.

---

**Q: Will emails be sent from my address?**

A: Yes, emails will be sent using the SMTP account you configure. The "from" address will be the email you entered in the username field.

---

### Sample Data Questions

**Q: Should I import sample data?**

A:
- **Yes**: If this is your first CRM and you want to learn features before adding real data
- **No**: If you're migrating from another CRM or ready to add real data immediately

---

**Q: How do I delete sample data later?**

A: Sample data can be identified and deleted from the data management interface. Each sample record is marked as such in the database. (Note: May require custom implementation)

---

**Q: Will sample data interfere with my real data?**

A: Sample data is separate from real data but uses the same database tables. We recommend deleting sample data before going live to avoid confusion.

---

**Q: Can I import sample data after completing the wizard?**

A: Currently, sample data import is only available during onboarding. You may need to restart the wizard or manually create sample records.

---

**Q: What if sample data import fails?**

A: Check the error message and try again. If the problem persists, skip sample data import and proceed. Contact support if you need assistance.

---

### Progress & Resume Questions

**Q: How long can I pause between steps?**

A: Indefinitely. Your progress is saved until you complete the wizard. There's no timeout or expiration.

---

**Q: Can I complete the wizard on different devices?**

A: Yes, progress is saved by user account. Start on desktop, continue on mobile, or vice versa.

---

**Q: What if I accidentally close the browser?**

A: No problem. Log back in and you'll be redirected to your current step. Your progress is saved.

---

**Q: Can multiple users complete onboarding separately?**

A: Yes, each user has their own onboarding progress. One user completing the wizard doesn't affect other users.

---

### Restart Questions

**Q: Will restarting delete my data?**

A: No, restarting only resets your progress through the wizard. Your company information, users, pipeline, and email settings remain saved.

---

**Q: Why would I restart the wizard?**

A: To review all steps, make major configuration changes, or train new administrators on the setup process.

---

**Q: Can I restart the wizard after completion?**

A: Yes, if the administrator has enabled restart functionality. Navigate to Settings → Onboarding → Restart.

---

**Q: What happens to my previous answers?**

A: Your previous configuration remains in the database. When you restart, forms will be pre-filled with your existing data.

---

### Technical Questions

**Q: What browsers are supported?**

A: The wizard works on all modern browsers: Chrome, Firefox, Safari, and Edge. Internet Explorer is not supported.

---

**Q: Do I need to enable JavaScript?**

A: Yes, JavaScript is required for the wizard to function properly (form validation, AJAX submissions, progress tracking).

---

**Q: Can I complete the wizard via API?**

A: The wizard is designed for browser use. However, you can configure settings via API endpoints if you prefer programmatic setup.

---

**Q: Is the wizard mobile-responsive?**

A: Yes, the wizard works on mobile devices, but we recommend using a desktop or tablet for the best experience.

---

**Q: What if I have a slow internet connection?**

A: The wizard works on slower connections but may take longer to load and save. If you experience timeouts, try completing it when you have a better connection.

---

### Configuration Questions

**Q: Can my administrator customize the wizard?**

A: Yes, administrators can configure which steps are required/optional, enable/disable auto-trigger, allow/prevent skipping, and customize step metadata in `config/onboarding.php`.

---

**Q: Can I add custom steps to the wizard?**

A: Yes, developers can add custom wizard steps by creating step classes that extend `AbstractWizardStep` and adding them to the configuration. See the developer documentation for details.

---

**Q: Can the wizard be disabled entirely?**

A: Yes, set `ONBOARDING_ENABLED=false` in `.env` or `'enabled' => false` in `config/onboarding.php`.

---

### Post-Completion Questions

**Q: What happens after I complete the wizard?**

A: You'll see a completion screen, receive a welcome email (if configured), and be redirected to the dashboard. The wizard will no longer appear on login.

---

**Q: Can I view my onboarding statistics?**

A: Administrators can view onboarding completion metrics in the admin dashboard, including completion rate, average time, and step analytics.

---

**Q: Will I receive any emails after completion?**

A: Yes, you'll receive a completion email with congratulations, statistics, next steps, and resource links (if email is configured).

---

### Support Questions

**Q: Where can I get help if I'm stuck?**

A:
1. Click the ❓ help icons for field-specific guidance
2. Review the help sidebar on each step
3. Read this user guide
4. Contact support via the completion screen or Settings → Support
5. Visit the community forum

---

**Q: Who can I contact for technical support?**

A: Click "Contact Support" from the help resources section or navigate to Settings → Support to submit a ticket.

---

**Q: Is there a video tutorial for the wizard?**

A: Video tutorials may be embedded in the help sidebar for each step (if configured by your administrator). Check the help panel for video links.

---

### Best Practice Questions

**Q: Should I complete the wizard alone or with my team?**

A: We recommend the administrator completes it first, then invites team members. You can restart the wizard to review with your team later.

---

**Q: What's the minimum configuration to start using the CRM?**

A: Just Company Setup (required step). You can skip all other steps and configure them later as needed.

---

**Q: When should I import my real data?**

A: After completing the wizard and exploring with sample data (if imported). Delete or clean up sample data before importing real data to avoid confusion.

---

**Q: How often should I review my onboarding configuration?**

A: Review quarterly or when your business processes change significantly. Update company info, pipeline stages, and email settings as needed.

---

## Conclusion

Congratulations on completing the onboarding wizard! You've successfully configured your CRM and are ready to start managing your sales pipeline effectively.

### Quick Reference

- **Documentation**: Comprehensive guides for all features
- **Video Tutorials**: Step-by-step visual guides
- **Support**: Contact us anytime for assistance
- **Community Forum**: Ask questions and share tips
- **Help Center**: FAQs, troubleshooting, and best practices

### Next Steps

1. **Import Your Data**: Add your real contacts, companies, and deals
2. **Invite Your Team**: Add team members and assign roles
3. **Customize**: Configure custom fields, email templates, and workflows
4. **Integrate**: Connect third-party apps and services
5. **Learn**: Explore advanced features through documentation and tutorials

### Stay Updated

- Subscribe to product updates for new features
- Join our community to share feedback and ideas
- Follow our blog for tips and best practices

Thank you for choosing our CRM! We're excited to help you grow your business.

---

**Document Version**: 1.0
**Last Updated**: January 2026
**For**: Laravel CRM Onboarding Wizard
**Contact**: support@example.com
