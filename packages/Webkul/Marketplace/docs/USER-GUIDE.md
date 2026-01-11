# Extension Marketplace User Guide

A comprehensive guide for browsing, installing, reviewing, and managing extensions in Laravel CRM.

## Table of Contents

1. [Introduction](#introduction)
2. [Accessing the Marketplace](#accessing-the-marketplace)
3. [Browsing Extensions](#browsing-extensions)
4. [Searching and Filtering](#searching-and-filtering)
5. [Extension Details](#extension-details)
6. [Installing Extensions](#installing-extensions)
7. [Managing Installed Extensions](#managing-installed-extensions)
8. [Updating Extensions](#updating-extensions)
9. [Reviewing Extensions](#reviewing-extensions)
10. [Uninstalling Extensions](#uninstalling-extensions)
11. [Payment and Billing](#payment-and-billing)
12. [Troubleshooting](#troubleshooting)
13. [FAQ](#faq)

---

## Introduction

The Laravel CRM Extension Marketplace is your one-stop shop for discovering, installing, and managing extensions that enhance your CRM experience. Extensions include plugins for new features, themes for customization, and integrations with third-party services.

### What You Can Do

- **Browse** thousands of extensions across different categories
- **Install** extensions with a single click
- **Review** and rate extensions to help the community
- **Manage** all your installed extensions from one place
- **Update** extensions automatically or manually
- **Uninstall** extensions you no longer need

### Extension Types

- **Plugins**: Add new features or functionality (e.g., email marketing, advanced reporting, custom fields)
- **Themes**: Customize the visual appearance of your CRM
- **Integrations**: Connect Laravel CRM with third-party services (e.g., Stripe, Mailchimp, Slack)

---

## Accessing the Marketplace

### From the Dashboard

1. Log in to your Laravel CRM account
2. Click on **Marketplace** in the main navigation menu
3. You'll see the marketplace homepage with featured extensions

### Direct URL

Navigate to: `https://your-crm-domain.com/marketplace`

### Required Permissions

To access the marketplace, you need the following permissions:
- `marketplace.browse` - View the marketplace
- `marketplace.install` - Install extensions
- `marketplace.review` - Write reviews

> **Note**: Your system administrator controls these permissions. Contact them if you don't have access.

---

## Browsing Extensions

### Marketplace Homepage

The marketplace homepage displays:

**Featured Extensions**
- Hand-picked extensions recommended by our team
- Rotating banner of top-rated and trending extensions

**Categories**
- **All**: View all available extensions
- **Plugins**: Feature extensions
- **Themes**: Visual customization
- **Integrations**: Third-party connectors
- **Free**: Extensions with no cost
- **Paid**: Premium extensions
- **Popular**: Most downloaded extensions
- **New**: Recently added extensions
- **Featured**: Editor's picks

**Sorting Options**
- **Newest**: Latest releases first
- **Popular**: Most downloads
- **Highest Rated**: Best reviewed
- **Recently Updated**: Recently maintained
- **Price: Low to High**
- **Price: High to Low**

### Navigation

Use the category tabs to filter by extension type:
1. Click on a category (e.g., "Integrations")
2. The page will show only extensions in that category
3. Use the subcategory filters for more specific results

---

## Searching and Filtering

### Quick Search

1. Locate the search bar at the top of the marketplace
2. Type keywords related to the extension you need
   - Examples: "email", "stripe", "reports", "dark theme"
3. Press Enter or click the search icon
4. Results appear instantly with relevance scoring

### Advanced Filters

Use the sidebar filters to narrow your search:

**By Type**
- ☐ Plugins
- ☐ Themes
- ☐ Integrations

**By Category**
- ☐ Sales & Marketing
- ☐ Analytics & Reporting
- ☐ Customer Support
- ☐ Payment & Billing
- ☐ Communication
- ☐ Productivity
- ☐ And more...

**By Price**
- ☐ Free
- ☐ Paid
- Custom price range slider

**By Rating**
- ☐ 5 stars
- ☐ 4 stars and above
- ☐ 3 stars and above
- ☐ 2 stars and above

**By Compatibility**
- ☐ Compatible with your version
- ☐ All versions

**By Author**
- ☐ Verified developers
- ☐ All developers

### Search Tips

- Use specific keywords: "mailchimp integration" instead of just "email"
- Filter by compatibility to see only extensions that work with your CRM version
- Check "Verified developers" for high-quality extensions
- Sort by "Highest Rated" to find the best extensions

---

## Extension Details

### Viewing Details

Click on any extension card to view its detailed page, which includes:

**Header Section**
- Extension name and logo
- Average rating with star display
- Total number of reviews
- Number of downloads
- Current version
- Price (Free or amount)
- "Install" or "Buy Now" button

**Overview Tab**
- Full description of the extension
- Key features list
- Screenshots and/or demo video
- Use cases and benefits

**Ratings & Reviews Tab**
- Overall rating breakdown (5-star histogram)
- User reviews sorted by:
  - Most Recent
  - Highest Rating
  - Lowest Rating
  - Most Helpful
- Review filters by rating
- Ability to mark reviews as helpful

**Changelog Tab**
- Version history
- Release notes for each version
- Bug fixes and improvements
- New features added
- Breaking changes (if any)

**Support Tab**
- Documentation link
- Support email or contact form
- FAQ section
- Community forum link
- Demo/trial information

**Information Sidebar**
- Developer name (clickable to see all their extensions)
- Last updated date
- Compatible CRM versions
- Language support
- File size
- License type
- Requirements (PHP version, dependencies, etc.)
- External service requirements

**Links Sidebar** (if available)
- Demo URL - Try before you buy
- Documentation - User manual
- Repository - Source code (for open-source extensions)
- Support - Help and contact

---

## Installing Extensions

### Free Extensions

1. Navigate to the extension detail page
2. Review the requirements and compatibility
3. Click the **Install** button
4. A modal will appear with:
   - Extension name and version
   - Compatibility check results
   - Required permissions
   - Terms and conditions
5. Review the information
6. Click **Confirm Installation**
7. Wait for the installation to complete (progress indicator shown)
8. You'll see a success message when done
9. Click **Configure Extension** to set up (if required)

### Paid Extensions

1. Navigate to the extension detail page
2. Click the **Buy Now** button
3. Review the purchase details:
   - Extension name and version
   - Price
   - Payment terms (one-time or subscription)
   - Refund policy
4. Click **Continue to Payment**
5. Enter payment information:
   - Credit/debit card details
   - Billing address
6. Review and confirm the purchase
7. After successful payment, the extension will automatically install
8. You'll receive a receipt via email

### Installation Process

During installation, the system:

1. **Compatibility Check**: Verifies your CRM version is compatible
2. **Security Scan**: Runs automated security checks
3. **Dependency Check**: Ensures required dependencies are available
4. **File Download**: Downloads extension files securely
5. **Installation**: Installs the extension to your CRM
6. **Database Migration**: Runs any required database changes
7. **Configuration**: Sets default configuration values
8. **Activation**: Activates the extension

### Post-Installation

After installation:

1. You'll be redirected to the extension's configuration page (if applicable)
2. Set up required settings (API keys, preferences, etc.)
3. Read the quick start guide provided
4. The extension will appear in "My Extensions"

### Installation Status

Monitor installation progress:
- **Pending**: Installation queued
- **Installing**: Currently being installed
- **Active**: Successfully installed and running
- **Failed**: Installation encountered an error
- **Inactive**: Installed but disabled

---

## Managing Installed Extensions

### My Extensions Page

Access your installed extensions:
1. Click **Marketplace** → **My Extensions** in the main menu
2. Or navigate to: `/marketplace/my-extensions`

### Dashboard Overview

The My Extensions dashboard shows:

**Statistics Cards**
- **Total Installations**: Number of extensions you have
- **Active**: Currently enabled extensions
- **Inactive**: Disabled extensions
- **Updates Available**: Extensions with new versions
- **Failed Installations**: Extensions that had issues

**Extension List**

Each extension card displays:
- Extension logo and name
- Current status badge (Active/Inactive/Failed/Updating)
- Update available indicator (purple badge)
- Current version number
- Installation date
- Short description
- Enable/Disable toggle switch

**Quick Actions**
- **Update**: Install the latest version
- **View Details**: See full extension information
- **Extension Page**: Go to marketplace listing
- **Uninstall**: Remove the extension

### Managing Individual Extensions

Click on an extension to view its management page:

**Extension Information**
- Full description
- Current status
- Enable/Disable toggle
- Current version vs. latest version

**Installation Details**
- Installed date
- Last updated date
- Auto-update setting (toggle)

**Version History**
- List of all available versions
- Current version indicator
- Release dates and changelogs

**Quick Actions Sidebar**
- View in marketplace
- Documentation link
- Contact support
- Uninstall button

**Installation Statistics**
- Total downloads (global)
- Average rating
- Number of reviews

### Enabling/Disabling Extensions

**To Disable an Extension:**
1. Go to My Extensions
2. Find the extension you want to disable
3. Click the toggle switch to OFF (or click the extension and toggle on detail page)
4. The extension will be disabled immediately
5. Status changes to "Inactive"

**To Enable an Extension:**
1. Go to My Extensions
2. Find the disabled extension
3. Click the toggle switch to ON
4. The extension will be enabled immediately
5. Status changes to "Active"

> **Note**: Disabling an extension keeps it installed but turns off its functionality. You can re-enable it anytime without reinstalling.

---

## Updating Extensions

### Automatic Updates

**Enable Auto-Update:**
1. Go to My Extensions
2. Click on the extension you want to auto-update
3. In the Installation Details section, toggle **Auto-Update** to ON
4. The extension will now update automatically when new versions are available

**How Auto-Updates Work:**
- The system checks for updates daily
- When a new version is found, it's installed automatically during off-peak hours
- You'll receive a notification after the update completes
- If the update fails, auto-update is disabled and you're notified

### Manual Updates

**Check for Updates:**
1. Go to My Extensions
2. Click **Check for Updates** button in the header
3. The system will check all installed extensions
4. Extensions with updates will show a purple "Update Available" badge

**Update a Single Extension:**
1. Find the extension with an update available
2. Click the **Update** button on the extension card
   - Or click the extension and then **Update Now** button
3. Review the changelog to see what's new
4. Confirm the update
5. Wait for the update to complete
6. The extension will restart automatically

**Update All Extensions:**
1. Go to My Extensions
2. Click **Update All** (if multiple updates are available)
3. Review the list of extensions to be updated
4. Confirm to proceed
5. Updates will run sequentially
6. You'll be notified when all updates complete

### Update Notifications

You'll be notified of available updates via:
- **In-app notification**: Bell icon in the top navigation
- **Email**: Daily digest of available updates (if enabled)
- **Dashboard widget**: Update counter on the main dashboard
- **Banner**: On My Extensions page

### Version Rollback

If an update causes issues:

1. Go to the extension's detail page
2. Click **Version History**
3. Find the previous working version
4. Click **Rollback to this version**
5. Confirm the rollback
6. The extension will revert to the selected version

> **Warning**: Rolling back may cause data loss if the new version made database changes. Always backup before rolling back.

---

## Reviewing Extensions

### Writing a Review

Share your experience to help other users:

1. Go to the extension's detail page
2. Scroll to the **Ratings & Reviews** tab
3. Click **Write a Review** button
4. You'll see a review form with:
   - Star rating (1-5 stars) - Required
   - Review title - Required
   - Review description - Required (minimum 50 characters)
   - Pros and cons (optional)
   - Screenshots (optional)
5. Fill in your review honestly and constructively
6. Click **Submit Review**
7. Your review will be published immediately

### Review Guidelines

**Do:**
- Be specific about what you liked or disliked
- Mention use cases and how the extension helped you
- Include both pros and cons
- Be respectful and constructive
- Update your review if the extension improves

**Don't:**
- Post spam or promotional content
- Use offensive language
- Write fake reviews
- Include personal information
- Post bug reports (use support channels instead)

### Editing Your Review

1. Go to the extension's detail page
2. Find your review in the Reviews tab
3. Click **Edit** next to your review
4. Make your changes
5. Click **Update Review**

### Deleting Your Review

1. Go to the extension's detail page
2. Find your review
3. Click **Delete**
4. Confirm deletion

### Marking Reviews as Helpful

Help surface the most useful reviews:
1. Read a review
2. Click **Helpful** button if it was useful
3. Click **Not Helpful** if it wasn't useful
4. The most helpful reviews appear at the top

### Review Moderation

Reviews are moderated to ensure quality:
- Spam and abusive reviews are removed
- Reviews must be from verified users who installed the extension
- One review per extension per user
- Reviews can be reported for violating guidelines

---

## Uninstalling Extensions

### Uninstall Process

**To Uninstall an Extension:**

1. Go to **My Extensions**
2. Find the extension you want to remove
3. Click the extension to open its detail page
4. Click the **Uninstall** button in the Quick Actions sidebar
   - Or click **Uninstall** on the extension card
5. A confirmation modal appears with:
   - Warning about data loss
   - List of data that will be removed
   - Option to export data before uninstalling
6. Review the warnings carefully
7. Type the extension name to confirm (for safety)
8. Click **Confirm Uninstall**
9. The uninstallation process begins
10. You'll see a success message when complete

### What Happens During Uninstallation

The system will:
1. Disable the extension
2. Run cleanup procedures
3. Remove extension files
4. Remove database tables (if any)
5. Remove configuration settings
6. Remove scheduled tasks
7. Remove event listeners

### Data Retention

**Data That's Removed:**
- Extension configuration
- Extension-specific database tables
- Extension files and assets
- Scheduled tasks and cron jobs

**Data That's Kept:**
- Extension transaction records (for billing)
- Your reviews of the extension
- Installation history (for troubleshooting)

### Before You Uninstall

**Recommended Steps:**

1. **Export Your Data**: If the extension stores important data, export it first
2. **Check Dependencies**: Make sure no other extensions depend on this one
3. **Read the Documentation**: Some extensions have specific uninstallation instructions
4. **Backup**: Create a full backup of your CRM before uninstalling

### Reinstalling

If you uninstall an extension and want it back:
1. Go to the Marketplace
2. Find the extension
3. Click **Install** again
4. For paid extensions, you won't be charged again if you previously purchased it

---

## Payment and Billing

### Purchasing Paid Extensions

**One-Time Purchase:**
1. Click **Buy Now** on the extension page
2. Review purchase details
3. Enter payment information
4. Complete the purchase
5. Extension installs automatically

**Subscription Extensions:**
- Charged monthly or annually
- Auto-renewal by default
- Cancel anytime from My Extensions

### Payment Methods

Supported payment methods:
- Credit cards (Visa, Mastercard, American Express)
- Debit cards
- PayPal (in supported regions)
- Other regional payment methods

### Billing History

View your purchase history:
1. Go to **Account Settings** → **Billing**
2. View all extension purchases
3. Download receipts
4. Manage payment methods

### Refund Policy

**Refund Eligibility:**
- Within 30 days of purchase
- Extension didn't work as described
- Technical issues that couldn't be resolved
- Compatibility issues

**To Request a Refund:**
1. Go to the extension's detail page
2. Click **Support** tab
3. Click **Request Refund**
4. Fill in the refund request form
5. Submit with reason and details
6. Expect a response within 2-3 business days

### Managing Subscriptions

**To Cancel a Subscription:**
1. Go to My Extensions
2. Click on the subscription extension
3. Click **Manage Subscription**
4. Click **Cancel Subscription**
5. Confirm cancellation
6. You'll keep access until the current billing period ends

**To Update Payment Method:**
1. Go to Account Settings → Billing
2. Click **Payment Methods**
3. Add or update your payment method
4. Set as default for subscriptions

---

## Troubleshooting

### Common Issues

#### Extension Won't Install

**Possible Causes:**
- Incompatible CRM version
- Missing dependencies
- Insufficient server resources
- Network connectivity issues

**Solutions:**
1. Check compatibility requirements
2. Update your CRM to the latest version
3. Contact your system administrator
4. Check the extension's support documentation

#### Extension Shows "Failed" Status

**Solutions:**
1. Click on the extension
2. View the error message
3. Click **Retry Installation**
4. If it fails again, contact support with the error details

#### Extension Not Working After Installation

**Solutions:**
1. Check if the extension is enabled (toggle should be ON)
2. Clear your browser cache (Ctrl+Shift+Delete)
3. Clear application cache: Contact your admin to run `php artisan cache:clear`
4. Check extension configuration settings
5. Review extension documentation for setup instructions

#### Update Failed

**Solutions:**
1. Check the error message on My Extensions page
2. Try the update again
3. If it fails again, consider:
   - Manually downloading and installing the update
   - Rolling back to the previous version
   - Contacting extension support

#### Extension Slowing Down CRM

**Solutions:**
1. Disable the extension temporarily to confirm it's the cause
2. Check extension documentation for performance optimization tips
3. Contact extension support
4. Consider alternative extensions

#### Can't Uninstall Extension

**Solutions:**
1. Disable the extension first
2. Check if other extensions depend on it
3. Try uninstalling from the command line (contact admin):
   ```bash
   php artisan marketplace:uninstall extension-slug
   ```
4. Contact support if the issue persists

### Getting Help

**Extension-Specific Issues:**
1. Check the extension's documentation
2. Contact the extension developer via the Support tab
3. Check community forums for similar issues

**General Marketplace Issues:**
1. Check the [FAQ](#faq) below
2. Contact Laravel CRM support
3. Submit a ticket: support@laravel-crm.com

### Error Messages

**"Incompatible Version"**
- The extension requires a different CRM version
- Solution: Update your CRM or find a compatible version of the extension

**"Security Scan Failed"**
- The extension failed automated security checks
- Solution: Contact the developer or choose a different extension

**"Payment Failed"**
- Your payment method was declined
- Solution: Check your payment details and try again

**"Installation Timeout"**
- The installation took too long and was cancelled
- Solution: Retry installation or contact support

---

## FAQ

### General Questions

**Q: Is the marketplace free to use?**
A: Yes, browsing and installing free extensions is completely free. You only pay for premium extensions.

**Q: How many extensions can I install?**
A: There's no limit on the number of extensions, but be mindful of performance impacts.

**Q: Are extensions secure?**
A: All extensions go through automated security scans and manual review. However, always review permissions before installing.

**Q: Can I install the same extension on multiple CRM instances?**
A: Check the extension's license. Some allow multiple installations, others require separate purchases.

**Q: What happens if an extension developer stops maintaining it?**
A: The extension will continue to work, but you won't receive updates. Consider finding an alternative.

### Installation & Updates

**Q: How long does installation take?**
A: Most extensions install within 1-2 minutes. Complex extensions may take up to 5 minutes.

**Q: Will updates break my customizations?**
A: Updates should not affect your CRM customizations. However, always backup before updating.

**Q: Can I test an extension before installing?**
A: Some extensions offer demo links. Check the extension's detail page.

**Q: What if I install the wrong extension?**
A: You can uninstall it immediately. For paid extensions, request a refund within 30 days.

### Reviews & Ratings

**Q: Can I change my review?**
A: Yes, you can edit or delete your review anytime.

**Q: Can I review an extension without installing it?**
A: No, only users who have installed an extension can review it.

**Q: Can developers respond to reviews?**
A: Yes, developers can respond to reviews on their extensions.

### Payments & Refunds

**Q: Are there any hidden fees?**
A: No, the price shown is the final price. No hidden fees.

**Q: What if I'm charged twice?**
A: Contact billing support immediately with your transaction details.

**Q: How long do refunds take?**
A: Refunds are processed within 5-7 business days to your original payment method.

**Q: Do subscription extensions auto-renew?**
A: Yes, subscriptions auto-renew. You can cancel anytime before the renewal date.

### Technical Questions

**Q: Do extensions work with custom themes?**
A: Most extensions are theme-agnostic, but check the extension's compatibility notes.

**Q: Can extensions access my customer data?**
A: Extensions request specific permissions. Review these carefully before installing.

**Q: What if two extensions conflict?**
A: Disable one or both extensions and contact the developers for a solution.

**Q: Can I modify an installed extension?**
A: Modifications are possible but may be overwritten on updates. Consider forking the extension if it's open-source.

---

## Need More Help?

### Resources

- **Marketplace Homepage**: Browse all extensions
- **Developer Guide**: For creating your own extensions
- **API Documentation**: For programmatic access
- **Community Forums**: Ask questions and share experiences
- **Video Tutorials**: Step-by-step guides on YouTube

### Support Channels

- **Email**: support@laravel-crm.com
- **Live Chat**: Available Mon-Fri, 9am-5pm EST
- **Community Forum**: https://community.laravel-crm.com
- **Documentation**: https://docs.laravel-crm.com

### Feedback

We value your feedback! Help us improve the marketplace:
- **Feature Requests**: Submit via the feedback form
- **Bug Reports**: Email bugs@laravel-crm.com
- **Extension Suggestions**: Tell us what you'd like to see

---

**Last Updated**: January 2026
**Version**: 1.0
**For**: Laravel CRM Extension Marketplace

---

*Happy extending! 🚀*
