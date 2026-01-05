# WhatsApp Business API Setup Guide

Complete guide for setting up WhatsApp Business API integration with activity logging, webhooks, templates, and notifications.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Part 1: Meta Business Account Setup](#part-1-meta-business-account-setup)
- [Part 2: WhatsApp Business API Configuration](#part-2-whatsapp-business-api-configuration)
- [Part 3: CRM Credential Configuration](#part-3-crm-credential-configuration)
- [Part 4: Webhook Setup](#part-4-webhook-setup)
- [Part 5: Template Management](#part-5-template-management)
- [Part 6: Testing the Integration](#part-6-testing-the-integration)
- [Part 7: Troubleshooting](#part-7-troubleshooting)
- [Advanced Configuration](#advanced-configuration)

---

## Overview

This integration enables:
- ✅ **Send WhatsApp messages** from person and lead detail pages
- ✅ **Receive incoming messages** via webhooks
- ✅ **Activity logging** - All messages logged in CRM timeline
- ✅ **Template messages** - Create and manage message templates
- ✅ **Real-time notifications** - Get notified of incoming messages
- ✅ **Conversation threading** - View messages grouped by phone number
- ✅ **Multi-user support** - Each user can configure their own WhatsApp Business number

### Integration Architecture

```
Meta WhatsApp Cloud API
        ↕
WhatsAppService (app/Services/WhatsAppService.php)
        ↕
WhatsAppController (app/Http/Controllers/WhatsAppController.php)
        ↕
Activity System + Notifications
        ↕
Vue.js Components + Timeline View
```

---

## Prerequisites

Before you begin, ensure you have:

1. **Meta Business Account**
   - A verified Meta Business Account
   - Admin access to the account

2. **Facebook Developer Account**
   - Linked to your Meta Business Account
   - Developer role or higher

3. **WhatsApp Business Phone Number**
   - A phone number dedicated to WhatsApp Business
   - Not currently registered with personal WhatsApp
   - Can receive SMS/voice calls for verification

4. **CRM Requirements**
   - Laravel CRM installed and running
   - HTTPS enabled (required for webhooks)
   - Public URL accessible by Meta servers

5. **Technical Access**
   - SSH/terminal access to server
   - Ability to set environment variables
   - Database access (for verification)

---

## Part 1: Meta Business Account Setup

### Step 1.1: Create Meta Business Account

1. Navigate to [Meta Business Suite](https://business.facebook.com/)
2. Click **Create Account** or log in with existing account
3. Enter your business name and details
4. Verify your business information
5. Complete the business verification process

**Note:** Business verification may take 1-3 business days. You can continue with development using test credentials during this time.

### Step 1.2: Access Business Settings

1. In Meta Business Suite, click the **Settings** icon (gear icon)
2. Navigate to **Business Settings**
3. Verify you have Admin or Employee access
4. Note your **Business ID** (you'll need this later)

---

## Part 2: WhatsApp Business API Configuration

### Step 2.1: Create Meta App

1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Click **My Apps** → **Create App**
3. Select app type: **Business**
4. Fill in app details:
   - **App name:** `[Your CRM Name] WhatsApp Integration`
   - **App contact email:** Your business email
   - **Business Account:** Select your Meta Business Account
5. Click **Create App**

### Step 2.2: Add WhatsApp Product

1. In your app dashboard, find **Add Products to Your App**
2. Locate **WhatsApp** and click **Set Up**
3. The WhatsApp configuration page will load

### Step 2.3: Configure WhatsApp Business

1. In **WhatsApp > Getting Started**, you'll see:
   - **Test Phone Number** (provided by Meta for development)
   - **Temporary Access Token** (valid for 24 hours)
   - **Phone Number ID** (starts with a long number)

2. **For Development/Testing:**
   - Use the provided test number and temporary token
   - You can send messages to up to 5 recipient numbers

3. **For Production:**
   - Click **Add Phone Number**
   - Enter your business phone number
   - Complete SMS/voice verification
   - Wait for number approval (usually instant)

### Step 2.4: Obtain Permanent Access Token

**Important:** Temporary tokens expire after 24 hours. For production, create a permanent token.

#### Option A: System User Token (Recommended for Production)

1. Go to **Business Settings** → **Users** → **System Users**
2. Click **Add** to create a new system user
3. Name it: `WhatsApp API System User`
4. Assign role: **Admin**
5. Click **Generate New Token**
6. Select your app from the dropdown
7. Select permissions:
   - ✅ `whatsapp_business_messaging`
   - ✅ `whatsapp_business_management`
8. Set token expiration: **Never** (or 60 days for security)
9. Click **Generate Token**
10. **COPY AND SAVE THIS TOKEN IMMEDIATELY** (you won't see it again)

#### Option B: User Access Token (For Development)

1. In **WhatsApp > Getting Started**
2. Use the **Temporary Access Token**
3. For longer-lasting token:
   - Go to **Tools** → **Access Token Tool**
   - Select your app
   - Select permissions (same as above)
   - Generate token

### Step 2.5: Collect Your Credentials

You now need two pieces of information:

**1. Phone Number ID:**
```
Location: WhatsApp > API Setup > Phone Number ID
Format: 123456789012345
Example: 109876543210987
```

**2. Access Token:**
```
Location: Business Settings > System Users > [Your System User] > Generate Token
Format: EAAE... (starts with EAA, very long string)
Example: EAAE1234abcd5678efgh... (200+ characters)
```

**Save these securely!** You'll need them in the next section.

---

## Part 3: CRM Credential Configuration

### Step 3.1: Configure Per-User Credentials

Each CRM user can configure their own WhatsApp Business API credentials.

1. **Log in to the CRM** as the user who will send/receive messages
2. Click on **Profile** icon (top right corner)
3. Select **Account Settings**
4. Scroll to **WhatsApp Configuration** section
5. Enter your credentials:
   - **Phone Number ID:** Paste the value from Step 2.5 #1
   - **Access Token:** Paste the value from Step 2.5 #2
6. Click **Save**

**Verification:**
- Credentials are encrypted and stored in `users` table
- Database columns: `whatsapp_phone_number_id`, `whatsapp_access_token`
- Each user can have different WhatsApp Business numbers

### Step 3.2: Test Credential Configuration

#### Via UI:
1. Navigate to a **Person** or **Lead** detail page
2. Click the **WhatsApp** button (green icon)
3. If credentials are configured, you'll see:
   - "Send via API" button (not "Open in WhatsApp")
   - Template selection dropdown
   - Message textarea

#### Via Database:
```sql
-- Verify credentials are stored
SELECT
    id,
    name,
    email,
    CASE
        WHEN whatsapp_phone_number_id IS NOT NULL THEN 'Configured'
        ELSE 'Not Configured'
    END as phone_number_status,
    CASE
        WHEN whatsapp_access_token IS NOT NULL THEN 'Configured'
        ELSE 'Not Configured'
    END as token_status
FROM users;
```

### Step 3.3: Send Test Message

1. Go to any **Person** with a phone number
2. Click **WhatsApp** button
3. Type a test message: `This is a test message from CRM`
4. Click **Send via API**
5. Check for success message
6. Verify the recipient receives the message on WhatsApp

**Expected Behavior:**
- ✅ Message sends successfully
- ✅ Activity logged in timeline (type: WhatsApp, direction: Outbound)
- ✅ Success notification appears in CRM
- ✅ Recipient receives message on WhatsApp

---

## Part 4: Webhook Setup

Webhooks enable the CRM to receive incoming WhatsApp messages.

### Step 4.1: Prepare Your Webhook URL

Your webhook URL format:
```
https://your-crm-domain.com/api/whatsapp/webhook
```

**Requirements:**
- ✅ Must use HTTPS (not HTTP)
- ✅ Must be publicly accessible (not localhost)
- ✅ Must return 200 OK for verification requests
- ✅ Server must accept GET and POST requests

**For Development (localhost):**
Use a tunneling service like:
- **ngrok:** `ngrok http 8000`
- **cloudflared:** `cloudflared tunnel --url localhost:8000`
- **localtunnel:** `lt --port 8000`

Example with ngrok:
```bash
# Terminal 1: Run your CRM
php artisan serve

# Terminal 2: Create tunnel
ngrok http 8000

# Your webhook URL becomes:
https://abc123.ngrok.io/api/whatsapp/webhook
```

### Step 4.2: Configure Webhook Verify Token

The verify token is a secret string used by Meta to verify your webhook endpoint.

1. **Generate a random token:**
```bash
# Linux/Mac
openssl rand -hex 32

# Or use any random string
echo "my_secure_webhook_token_$(date +%s)"
```

2. **Add to your `.env` file:**
```bash
WHATSAPP_VERIFY_TOKEN=your_random_token_here
```

3. **Clear config cache:**
```bash
php artisan config:clear
```

**Example `.env` entry:**
```env
WHATSAPP_VERIFY_TOKEN=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

### Step 4.3: Configure Webhook in Meta

1. Go to your Meta App Dashboard
2. Navigate to **WhatsApp > Configuration**
3. Find **Webhook** section
4. Click **Edit**

5. **Configure Callback URL:**
   - **Callback URL:** `https://your-crm-domain.com/api/whatsapp/webhook`
   - **Verify Token:** The value from your `.env` file (Step 4.2)
   - Click **Verify and Save**

6. **Subscribe to Webhook Fields:**
   - Click **Manage**
   - Subscribe to:
     - ✅ `messages` (incoming messages)
     - ✅ `message_status` (delivery receipts - optional)
   - Click **Subscribe**

### Step 4.4: Verify Webhook Configuration

#### Check Meta Dashboard:
- Webhook status should show: ✅ **Active**
- Subscribed fields should show: `messages`

#### Test Webhook Verification:
```bash
# Simulate Meta's verification request
curl -X GET "https://your-crm-domain.com/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=your_token&hub.challenge=test123"

# Expected response:
test123
```

#### Check Server Logs:
```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log | grep -i whatsapp
```

### Step 4.5: Test Incoming Messages

1. **Send a test message TO your WhatsApp Business number**
   - Use your personal WhatsApp
   - Send any text message to your Business number

2. **Check CRM for incoming message:**
   - Person should be auto-created (if new number)
   - Activity should be logged (type: WhatsApp, direction: Inbound)
   - Notification should appear in notification center
   - User who owns the phone_number_id should be notified

3. **Verify in database:**
```sql
-- Check for incoming message activity
SELECT
    a.id,
    a.type,
    a.title,
    a.comment,
    a.additional,
    a.created_at,
    p.name as person_name
FROM activities a
LEFT JOIN person_activities pa ON a.id = pa.activity_id
LEFT JOIN persons p ON pa.person_id = p.id
WHERE a.type = 'whatsapp'
ORDER BY a.created_at DESC
LIMIT 5;
```

---

## Part 5: Template Management

WhatsApp requires pre-approved templates for outbound messages sent outside the 24-hour customer care window.

### Step 5.1: Understanding WhatsApp Templates

**Two Types of Templates:**

1. **CRM Templates** (for internal use)
   - Quick message templates stored in CRM database
   - Can be used immediately
   - For messages within 24-hour window
   - User edits before sending

2. **Meta Templates** (for official campaigns)
   - Submitted to Meta for approval
   - Used for marketing/notifications outside 24-hour window
   - Cannot be edited after approval
   - Sent as-is with parameters

### Step 5.2: Create CRM Templates

1. **Navigate to Template Management:**
   - Go to **Settings** → **WhatsApp Templates**
   - Or access directly: `/api/whatsapp/templates`

2. **Create New Template:**
   - Click **Create Template**
   - Fill in the form:

**Template Fields:**

| Field | Required | Description | Example |
|-------|----------|-------------|---------|
| **Name** | Yes | Template identifier | `follow_up_message` |
| **Language** | Yes | Language code | `en`, `es`, `pt`, `ar` |
| **Category** | Yes | Template category | `MARKETING`, `UTILITY`, `AUTHENTICATION` |
| **Status** | Yes | Approval status | `APPROVED`, `PENDING`, `REJECTED` |
| **Header** | No | Optional header text | `Hello from [Company]` |
| **Body** | Yes | Main message content | `Hi {name}, thank you for your interest...` |
| **Footer** | No | Optional footer text | `Reply STOP to unsubscribe` |
| **Meta Template ID** | No | Meta template reference | `hello_world_template` |

3. **Save Template**

**Template Example:**
```
Name: follow_up_lead
Language: en
Category: MARKETING
Status: APPROVED

Header: Follow-up on your inquiry

Body: Hi {1}, thank you for your interest in {2}.
I wanted to follow up and answer any questions you might have.
When would be a good time to chat?

Footer: Reply STOP to opt out
```

### Step 5.3: Using Templates

#### From Person/Lead Detail Page:
1. Click **WhatsApp** button
2. Select template from **Template** dropdown
3. Message populates in textarea
4. Edit if needed (for CRM templates)
5. Click **Send via API**

#### Programmatically:
```php
// Using WhatsAppService
$whatsAppService->sendTemplateMessage(
    to: '+1234567890',
    templateName: 'follow_up_message',
    languageCode: 'en',
    parameters: [
        'body' => [
            ['type' => 'text', 'text' => 'John Doe'],
            ['type' => 'text', 'text' => 'Enterprise CRM Solution']
        ]
    ],
    phoneNumberId: $user->whatsapp_phone_number_id,
    accessToken: $user->whatsapp_access_token
);
```

### Step 5.4: Create Meta Templates (Official)

For messages outside the 24-hour window, create templates in Meta:

1. **Go to Meta Business Manager**
   - Navigate to **WhatsApp Manager**
   - Select your WhatsApp Business Account
   - Go to **Message Templates**

2. **Create Template:**
   - Click **Create Template**
   - Select category: Marketing, Utility, or Authentication
   - Choose language
   - Enter template content with placeholders: `{{1}}`, `{{2}}`, etc.

3. **Submit for Review:**
   - Templates must be approved by Meta (24-48 hours)
   - Follow WhatsApp's template guidelines
   - Avoid promotional language in non-marketing templates

4. **Link to CRM:**
   - Once approved, note the **Template Name** from Meta
   - Create corresponding CRM template
   - Set **Meta Template ID** to match Meta's template name
   - Set **Status** to `APPROVED`

---

## Part 6: Testing the Integration

### Test Checklist

#### ✅ **Test 1: Send Outbound Message**
- [ ] Navigate to Person detail page
- [ ] Click WhatsApp button
- [ ] Select template
- [ ] Send message via API
- [ ] Verify message received on WhatsApp
- [ ] Verify activity logged in timeline
- [ ] Verify activity shows: type=WhatsApp, direction=Outbound

#### ✅ **Test 2: Receive Inbound Message**
- [ ] Send message TO your WhatsApp Business number
- [ ] Verify webhook receives message (check logs)
- [ ] Verify person auto-created (if new number)
- [ ] Verify activity logged in timeline
- [ ] Verify activity shows: type=WhatsApp, direction=Inbound
- [ ] Verify notification appears in notification center
- [ ] Verify notification icon is emerald message icon

#### ✅ **Test 3: Conversation Threading**
- [ ] Send multiple messages to/from same number
- [ ] Navigate to Person timeline
- [ ] Click WhatsApp filter
- [ ] Verify messages grouped by phone number
- [ ] Verify thread is expandable/collapsible
- [ ] Verify message count and latest message preview shown

#### ✅ **Test 4: Multi-User Support**
- [ ] Configure different WhatsApp credentials for different users
- [ ] Send message from User A's WhatsApp
- [ ] Verify activity assigned to User A
- [ ] Send inbound message to User B's WhatsApp number
- [ ] Verify User B receives notification
- [ ] Verify activity assigned to User B

#### ✅ **Test 5: Template Management**
- [ ] Create new template in CRM
- [ ] Set status to APPROVED
- [ ] Navigate to Person page
- [ ] Verify template appears in dropdown
- [ ] Select template
- [ ] Verify body populates message field
- [ ] Edit message
- [ ] Send successfully

#### ✅ **Test 6: Error Handling**
- [ ] Try sending to invalid number
- [ ] Verify error message shown to user
- [ ] Verify error logged in Laravel logs
- [ ] Try sending with expired token
- [ ] Verify graceful error handling

### Manual Testing Commands

#### Check Webhook Configuration:
```bash
# Test webhook verification
curl "https://your-domain.com/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=YOUR_TOKEN&hub.challenge=12345"

# Should return: 12345
```

#### Simulate Incoming Message:
```bash
# Send POST to webhook (replace with your verify token)
curl -X POST https://your-domain.com/api/whatsapp/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "object": "whatsapp_business_account",
    "entry": [{
      "changes": [{
        "value": {
          "messaging_product": "whatsapp",
          "metadata": {
            "display_phone_number": "1234567890",
            "phone_number_id": "YOUR_PHONE_NUMBER_ID"
          },
          "contacts": [{
            "profile": {
              "name": "Test User"
            },
            "wa_id": "1234567890"
          }],
          "messages": [{
            "from": "1234567890",
            "id": "wamid.test123",
            "timestamp": "1234567890",
            "text": {
              "body": "Test message from webhook"
            },
            "type": "text"
          }]
        }
      }]
    }]
  }'
```

#### Check Database:
```sql
-- Verify activities created
SELECT * FROM activities WHERE type = 'whatsapp' ORDER BY created_at DESC LIMIT 10;

-- Verify notifications created
SELECT * FROM notifications WHERE type = 'whatsapp' ORDER BY created_at DESC LIMIT 10;

-- Verify templates
SELECT * FROM whatsapp_templates ORDER BY created_at DESC;
```

#### Monitor Logs:
```bash
# Watch Laravel logs in real-time
tail -f storage/logs/laravel.log | grep -i whatsapp

# Or use laravel log viewer
php artisan log:tail --filter=whatsapp
```

---

## Part 7: Troubleshooting

### Common Issues and Solutions

#### Issue 1: "Webhook verification failed"

**Symptoms:**
- Meta shows "Verification Failed" when configuring webhook
- Red X icon in Meta dashboard

**Causes & Solutions:**

1. **Wrong verify token**
   - Check `.env` file: `WHATSAPP_VERIFY_TOKEN`
   - Clear config cache: `php artisan config:clear`
   - Ensure token matches exactly (no extra spaces)

2. **URL not accessible**
   - Test URL in browser: `https://your-domain.com/api/whatsapp/webhook`
   - Should return 200 OK (even if blank page)
   - Check firewall rules
   - Verify HTTPS is working (not self-signed cert)

3. **Route not found**
   - Verify route exists: `php artisan route:list | grep webhook`
   - Should show: `GET|POST api/whatsapp/webhook`
   - Clear route cache: `php artisan route:clear`

#### Issue 2: "Messages not sending"

**Symptoms:**
- Click send, but message not delivered
- Error message in CRM
- No activity logged

**Diagnosis:**
```bash
# Check logs for API errors
grep "WhatsApp" storage/logs/laravel.log | tail -20
```

**Causes & Solutions:**

1. **Invalid credentials**
   - Phone Number ID incorrect
   - Access Token expired or invalid
   - Solution: Re-generate token in Meta Business Manager
   - Update in CRM Account Settings

2. **Rate limiting**
   - Meta limits messages per second
   - Solution: Implement queue for bulk sending
   - Check Meta's messaging limits documentation

3. **Invalid phone number format**
   - Number must include country code
   - No + or special characters needed
   - Example: 1234567890 (not +1-234-567-890)

4. **24-hour window expired**
   - Can only send freeform messages within 24h of customer message
   - Outside window, must use approved templates
   - Solution: Create and use Meta-approved template

#### Issue 3: "Incoming messages not received"

**Symptoms:**
- Messages sent to WhatsApp Business number
- No activity logged in CRM
- No notification received

**Diagnosis:**
```bash
# Test webhook manually (see Part 6)
# Check if webhook route is accessible
curl https://your-domain.com/api/whatsapp/webhook

# Check webhook logs
tail -f storage/logs/laravel.log | grep webhook
```

**Causes & Solutions:**

1. **Webhook not subscribed**
   - Go to Meta App → WhatsApp → Configuration
   - Verify `messages` field is subscribed
   - Click "Subscribe" if not active

2. **Phone number ID mismatch**
   - Incoming message metadata contains phone_number_id
   - Must match user's configured phone_number_id
   - Solution: Verify correct phone_number_id in user settings

3. **User not found**
   - No user has the phone_number_id from webhook
   - Solution: Ensure at least one user has configured credentials
   - Check: `SELECT * FROM users WHERE whatsapp_phone_number_id IS NOT NULL`

4. **Webhook returning error**
   - Check Laravel logs for PHP errors
   - Webhook must always return 200 OK
   - Fix: Debug error in processIncomingMessage() method

#### Issue 4: "Notifications not appearing"

**Symptoms:**
- Message received and logged
- No notification in notification center

**Diagnosis:**
```sql
-- Check if notifications created
SELECT * FROM notifications WHERE type = 'whatsapp' ORDER BY created_at DESC LIMIT 5;
```

**Causes & Solutions:**

1. **NotificationService not injected**
   - Verify WhatsAppService constructor has NotificationService
   - Should be dependency injected in AppServiceProvider

2. **Notification created but not visible**
   - Check user_id on notification matches logged-in user
   - Check notification center permissions
   - Clear browser cache

3. **Lead assignment issue**
   - Notification should go to lead owner if different from WhatsApp owner
   - Verify lead has user_id set
   - Check: `SELECT * FROM leads WHERE id = X`

#### Issue 5: "Templates not appearing in dropdown"

**Symptoms:**
- Created templates in CRM
- Dropdown is empty or missing templates

**Diagnosis:**
```sql
-- Check templates
SELECT id, name, status, user_id FROM whatsapp_templates ORDER BY name;
```

**Causes & Solutions:**

1. **Status not APPROVED**
   - Only APPROVED templates show in dropdown
   - Solution: Edit template, set status to APPROVED

2. **Wrong user**
   - Templates are user-specific
   - Only show templates created by logged-in user
   - Solution: Create template as the user who needs it

3. **JavaScript error**
   - Check browser console for errors
   - Vue component may not be loading
   - Solution: Clear browser cache, reload page

#### Issue 6: "Activity not showing in timeline"

**Symptoms:**
- Message sent/received
- No activity in timeline

**Diagnosis:**
```sql
-- Check if activity created
SELECT * FROM activities WHERE type = 'whatsapp' ORDER BY created_at DESC LIMIT 5;

-- Check if activity associated with person
SELECT * FROM person_activities WHERE activity_id = X;
```

**Causes & Solutions:**

1. **Activity not created**
   - Check if sendMessage() returned success
   - Check Laravel logs for errors during activity creation
   - Verify ActivityRepository is injected

2. **Activity not associated**
   - Activity created but not linked to person/lead
   - Check person_activities or lead_activities tables
   - Verify attach() methods called in controller

3. **Timeline filter**
   - Timeline may be filtered to other types
   - Click "All" or "WhatsApp" tab in timeline
   - Clear any date filters

### Debug Mode

Enable debug logging for WhatsApp integration:

**1. Add to `.env`:**
```env
LOG_LEVEL=debug
WHATSAPP_DEBUG=true
```

**2. Add detailed logging to WhatsAppService:**
```php
// In sendMessage() method
Log::debug('WhatsApp API Request', [
    'to' => $to,
    'message_length' => strlen($message),
    'phone_number_id' => $phoneNumberId,
]);
```

**3. Monitor logs:**
```bash
tail -f storage/logs/laravel.log
```

### Getting Help

1. **Check Laravel Logs:**
   - `storage/logs/laravel.log`
   - Look for ERROR or EXCEPTION entries

2. **Check Meta Status:**
   - [Meta API Status Page](https://metastatus.com/)
   - Verify WhatsApp API is operational

3. **Review Meta Documentation:**
   - [WhatsApp Business Platform](https://developers.facebook.com/docs/whatsapp)
   - [Cloud API Reference](https://developers.facebook.com/docs/whatsapp/cloud-api)

4. **Test in Meta's API Explorer:**
   - [Graph API Explorer](https://developers.facebook.com/tools/explorer/)
   - Test API calls directly

---

## Advanced Configuration

### Environment Variables Reference

```env
# WhatsApp Webhook Configuration
WHATSAPP_VERIFY_TOKEN=your_secure_random_token_here

# Optional: Debug logging
WHATSAPP_DEBUG=false
LOG_LEVEL=info

# Optional: Webhook retry configuration
WHATSAPP_WEBHOOK_MAX_RETRIES=3
WHATSAPP_WEBHOOK_RETRY_DELAY=5
```

### Database Schema

**Users table (existing):**
```sql
whatsapp_phone_number_id VARCHAR(255) NULL
whatsapp_access_token TEXT NULL
```

**Activities table (existing):**
```sql
type VARCHAR(255)  -- 'whatsapp'
title VARCHAR(255)
comment TEXT  -- Message content
additional JSON  -- {'phone_number': '...', 'direction': 'inbound/outbound'}
```

**WhatsApp Templates table:**
```sql
CREATE TABLE whatsapp_templates (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY REFERENCES users(id),
    name VARCHAR(255),
    language VARCHAR(10),
    status VARCHAR(50),
    category VARCHAR(50),
    header TEXT NULL,
    body TEXT,
    footer TEXT NULL,
    buttons JSON NULL,
    meta_template_id VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### API Endpoints Reference

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/whatsapp/data` | Required | Get templates and API status |
| POST | `/api/whatsapp/person/{id}/send` | Required | Send message from person |
| POST | `/api/whatsapp/lead/{id}/send` | Required | Send message from lead |
| GET/POST | `/api/whatsapp/webhook` | None | Webhook endpoint (public) |
| GET | `/api/whatsapp/templates` | Required | List templates |
| POST | `/api/whatsapp/templates` | Required | Create template |
| PUT | `/api/whatsapp/templates/{id}` | Required | Update template |
| DELETE | `/api/whatsapp/templates/{id}` | Required | Delete template |

### Meta API Version

Current implementation uses: **v18.0**

To upgrade:
1. Update version in `WhatsAppService.php`:
   ```php
   $url = "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";
   ```
2. Test all functionality
3. Review [Meta's changelog](https://developers.facebook.com/docs/graph-api/changelog)

### Rate Limits

**Meta Cloud API Limits:**
- 80 messages per second per phone number
- 1000 messages per second per business
- 250,000 messages per day (varies by tier)

**Best Practices:**
- Implement queue for bulk messages
- Add retry logic with exponential backoff
- Monitor rate limit headers in API responses

### Security Best Practices

1. **Protect Access Tokens:**
   - Store in database (encrypted column)
   - Never commit to version control
   - Rotate periodically
   - Use System User tokens for production

2. **Webhook Security:**
   - Use strong verify token (32+ characters)
   - Validate webhook signatures (future enhancement)
   - Rate limit webhook endpoint
   - Log all webhook activity

3. **User Permissions:**
   - Restrict template management to admins
   - Audit WhatsApp activity logs
   - Monitor for abuse

### Performance Optimization

1. **Queue Messages:**
```php
// Future: Use Laravel Queues
dispatch(new SendWhatsAppMessage($person, $message));
```

2. **Cache Templates:**
```php
// Cache approved templates per user
Cache::remember("whatsapp_templates_{$userId}", 3600, function() {
    return WhatsAppTemplate::where('user_id', $userId)
        ->where('status', 'APPROVED')
        ->get();
});
```

3. **Batch Notifications:**
```php
// Send notifications in bulk
Notification::insert($notificationsArray);
```

### Monitoring and Analytics

**Recommended Metrics to Track:**
- Messages sent per day/week/month
- Messages received per day/week/month
- Response time (time between inbound and outbound)
- Template usage frequency
- Error rate
- Webhook response time

**Example Query:**
```sql
-- WhatsApp activity summary
SELECT
    DATE(created_at) as date,
    JSON_EXTRACT(additional, '$.direction') as direction,
    COUNT(*) as message_count
FROM activities
WHERE type = 'whatsapp'
GROUP BY DATE(created_at), JSON_EXTRACT(additional, '$.direction')
ORDER BY date DESC;
```

---

## Quick Reference Card

### Essential URLs

| Resource | URL |
|----------|-----|
| Meta Business Suite | https://business.facebook.com/ |
| Meta Developers | https://developers.facebook.com/ |
| WhatsApp Manager | https://business.facebook.com/wa/manage/ |
| Graph API Explorer | https://developers.facebook.com/tools/explorer/ |
| Meta Status | https://metastatus.com/ |

### Configuration Checklist

- [ ] Meta Business Account created and verified
- [ ] Meta App created with WhatsApp product
- [ ] Phone Number ID obtained
- [ ] Permanent Access Token generated
- [ ] Credentials configured in CRM user settings
- [ ] Webhook URL configured in Meta
- [ ] Webhook verify token set in `.env`
- [ ] Webhook fields subscribed (messages)
- [ ] Test message sent successfully
- [ ] Test message received successfully
- [ ] Activity logging working
- [ ] Notifications working
- [ ] Templates created and approved

### Support Contacts

- **Meta Support:** https://business.facebook.com/business/help
- **WhatsApp API Docs:** https://developers.facebook.com/docs/whatsapp
- **CRM Documentation:** See WHATSAPP_INTEGRATION.md
- **Manual Testing Guide:** See .auto-claude/specs/001-complete-whatsapp-integration/manual-testing-guide.md

---

## Appendix

### Webhook Payload Examples

**Incoming Text Message:**
```json
{
  "object": "whatsapp_business_account",
  "entry": [{
    "id": "WHATSAPP_BUSINESS_ACCOUNT_ID",
    "changes": [{
      "value": {
        "messaging_product": "whatsapp",
        "metadata": {
          "display_phone_number": "1234567890",
          "phone_number_id": "PHONE_NUMBER_ID"
        },
        "contacts": [{
          "profile": {
            "name": "John Doe"
          },
          "wa_id": "1234567890"
        }],
        "messages": [{
          "from": "1234567890",
          "id": "wamid.XXXXXXXXXXX",
          "timestamp": "1234567890",
          "text": {
            "body": "Hello, I have a question"
          },
          "type": "text"
        }]
      },
      "field": "messages"
    }]
  }]
}
```

**Message Status Update:**
```json
{
  "object": "whatsapp_business_account",
  "entry": [{
    "id": "WHATSAPP_BUSINESS_ACCOUNT_ID",
    "changes": [{
      "value": {
        "messaging_product": "whatsapp",
        "metadata": {
          "display_phone_number": "1234567890",
          "phone_number_id": "PHONE_NUMBER_ID"
        },
        "statuses": [{
          "id": "wamid.XXXXXXXXXXX",
          "status": "delivered",
          "timestamp": "1234567890",
          "recipient_id": "1234567890"
        }]
      },
      "field": "messages"
    }]
  }]
}
```

### Template Parameter Examples

**Simple Text Template:**
```php
$parameters = [
    'body' => [
        ['type' => 'text', 'text' => 'John Doe'],
        ['type' => 'text', 'text' => 'Enterprise Plan']
    ]
];
```

**Template with Header:**
```php
$parameters = [
    'header' => [
        ['type' => 'text', 'text' => 'Special Offer']
    ],
    'body' => [
        ['type' => 'text', 'text' => 'John Doe'],
        ['type' => 'text', 'text' => '50% discount']
    ]
];
```

**Template with Buttons:**
```php
$parameters = [
    'body' => [
        ['type' => 'text', 'text' => 'John Doe']
    ],
    'button' => [
        [
            'type' => 'text',
            'text' => 'https://yoursite.com/offer?id=123'
        ]
    ]
];
```

---

## Changelog

**Version 1.0.0** (2026-01-05)
- Initial comprehensive setup guide
- Covers WhatsApp Business API integration
- Includes webhook setup and configuration
- Template management documentation
- Testing and troubleshooting sections

---

**Document Version:** 1.0.0
**Last Updated:** 2026-01-05
**Integration Version:** Laravel CRM WhatsApp Integration v3.0
**Meta API Version:** v18.0
**Author:** Auto-Claude Development Team
