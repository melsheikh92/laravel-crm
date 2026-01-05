# Test Cases for CRM Feature Implementation

This document contains comprehensive test cases for the newly implemented features:
1. Marketing Automation (Email Campaigns + Templates)
2. Collaboration & Communication (Real-time chat)
3. Customer Service/Support (Full ticketing system)
4. Integrations Marketplace

---

## 1. Marketing Automation - Email Campaigns

### 1.1 Campaign Creation

#### Test Case 1.1.1: Create Campaign (Draft)
**Priority:** High 
**Preconditions:**
- User is logged in as admin
- Marketing module is enabled

**Test Steps:**
1. Navigate to `/admin/marketing/campaigns/create`
2. Fill in campaign details:
   - Name: "Welcome Campaign"
   - Subject: "Welcome to Our Service"
   - Content: "Hello {{name}}, welcome to our platform!"
   - Status: Draft
   - Sender Name: "John Doe"
   - Sender Email: "john@example.com"
   - Reply To: "support@example.com"
3. Click "Save Campaign"

**Expected Result:**
- Campaign is created successfully
- Campaign status is "draft"
- Campaign appears in campaigns list
- Success message is displayed

**Manual Verification:**
- Check database `email_campaigns` table
- Verify all fields are saved correctly
- Verify `user_id` is set to current user

---

#### Test Case 1.1.2: Create Campaign with Template
**Priority:** High  
**Preconditions:**
- At least one email template exists
- User is logged in

**Test Steps:**
1. Navigate to `/admin/marketing/campaigns/create`
2. Fill in campaign details
3. Select an email template from the template dropdown
4. Click "Save Campaign"

**Expected Result:**
- Campaign is created with `template_id` set
- Template content is available for rendering
- Campaign can be edited to use template variables

---

#### Test Case 1.1.3: Create Campaign with Recipients
**Priority:** High  
**Preconditions:**
- At least one person/lead exists in the system

**Test Steps:**
1. Navigate to `/admin/marketing/campaigns/create`
2. Fill in campaign details
3. Click "Add Recipients"
4. Select recipients (persons or leads)
5. Click "Save Campaign"

**Expected Result:**
- Recipients are added to `email_campaign_recipients` table
- Recipient count is displayed
- Each recipient has status "pending"

---

#### Test Case 1.1.4: Create Campaign Validation
**Priority:** Medium  
**Preconditions:**
- User is logged in

**Test Steps:**
1. Navigate to `/admin/marketing/campaigns/create`
2. Leave required fields empty (name, subject, content)
3. Click "Save Campaign"

**Expected Result:**
- Validation errors are displayed
- Campaign is not created
- Error messages indicate missing required fields

---

### 1.2 Campaign Scheduling

#### Test Case 1.2.1: Schedule Campaign
**Priority:** High  
**Preconditions:**
- Campaign exists in "draft" status
- Campaign has at least one recipient

**Test Steps:**
1. Navigate to campaign edit page
2. Change status to "scheduled"
3. Set scheduled date/time (future date)
4. Click "Save Campaign"
5. OR click "Schedule" button on campaign detail page

**Expected Result:**
- Campaign status changes to "scheduled"
- `scheduled_at` field is set
- Queue job is dispatched for scheduled time
- Campaign appears in scheduled campaigns list

**Manual Verification:**
- Check database for `scheduled_at` value
- Verify queue jobs table for scheduled job

---

#### Test Case 1.2.2: Schedule Campaign Without Recipients
**Priority:** Medium  
**Preconditions:**
- Campaign exists with no recipients

**Test Steps:**
1. Navigate to campaign detail page
2. Click "Schedule" button

**Expected Result:**
- Error message: "Campaign must have at least one recipient before scheduling"
- Campaign status remains unchanged

---

#### Test Case 1.2.3: Schedule Campaign with Past Date
**Priority:** Low  
**Preconditions:**
- Campaign exists in draft status

**Test Steps:**
1. Navigate to campaign edit page
2. Set scheduled date to past date
3. Click "Save Campaign"

**Expected Result:**
- Validation error: "Scheduled date must be in the future"
- Campaign is not scheduled

---

### 1.3 Campaign Sending

#### Test Case 1.3.1: Send Campaign Immediately
**Priority:** High  
**Preconditions:**
- Campaign exists in "draft" or "scheduled" status
- Campaign has recipients
- Queue worker is running

**Test Steps:**
1. Navigate to campaign detail page
2. Click "Send Now" button
3. Confirm action

**Expected Result:**
- Campaign status changes to "sending"
- `started_at` field is set
- Queue jobs are dispatched for each recipient
- Emails are sent to recipients
- Recipient status changes to "sent"
- Campaign status eventually changes to "completed"

**Manual Verification:**
- Check email inbox for recipients
- Verify `sent_at` timestamp for recipients
- Check `sent_count` increases
- Verify queue jobs are processed

---

#### Test Case 1.3.2: Send Campaign with Template Variables
**Priority:** High  
**Preconditions:**
- Campaign uses a template with variables ({{name}}, {{company}})
- Recipients have corresponding data

**Test Steps:**
1. Send a campaign with template
2. Check sent emails

**Expected Result:**
- Template variables are replaced with actual values
- Personalization works correctly (name, company, email)
- Email content is properly formatted

---

#### Test Case 1.3.3: Campaign Sending Failure Handling
**Priority:** Medium  
**Preconditions:**
- Campaign exists with invalid email addresses

**Test Steps:**
1. Create campaign with recipients having invalid emails
2. Send campaign
3. Monitor queue jobs

**Expected Result:**
- Failed emails are marked as "failed"
- `failed_count` increases
- `error_message` is stored for failed recipients
- Campaign completes with partial success
- Other valid emails are still sent

---

#### Test Case 1.3.4: Cancel Sending Campaign
**Priority:** Medium  
**Preconditions:**
- Campaign is in "sending" status

**Test Steps:**
1. Navigate to campaign detail page
2. Click "Cancel" button
3. Confirm cancellation

**Expected Result:**
- Campaign status changes to "cancelled"
- No further emails are sent
- Pending recipients remain in "pending" status
- Already sent emails are not affected

---

### 1.4 Campaign Management

#### Test Case 1.4.1: Edit Draft Campaign
**Priority:** High  
**Preconditions:**
- Campaign exists in "draft" status

**Test Steps:**
1. Navigate to campaign edit page
2. Modify campaign name, subject, or content
3. Click "Update Campaign"

**Expected Result:**
- Changes are saved successfully
- Campaign details are updated
- Success message is displayed

---

#### Test Case 1.4.2: Edit Scheduled Campaign
**Priority:** Medium  
**Preconditions:**
- Campaign exists in "scheduled" status

**Test Steps:**
1. Navigate to campaign edit page
2. Modify campaign details
3. Click "Update Campaign"

**Expected Result:**
- Campaign can be edited
- Scheduled date can be changed
- Changes are saved successfully

---

#### Test Case 1.4.3: Edit Sending/Completed Campaign
**Priority:** Low  
**Preconditions:**
- Campaign exists in "sending" or "completed" status

**Test Steps:**
1. Navigate to campaign edit page
2. Try to modify campaign details

**Expected Result:**
- Error message: "Cannot update a campaign that is sending or completed"
- Campaign details remain unchanged

---

#### Test Case 1.4.4: Delete Campaign
**Priority:** Medium  
**Preconditions:**
- Campaign exists in "draft" or "cancelled" status

**Test Steps:**
1. Navigate to campaigns list
2. Click delete button on a draft campaign
3. Confirm deletion

**Expected Result:**
- Campaign is deleted
- Associated recipients are deleted (cascade)
- Success message is displayed

---

#### Test Case 1.4.5: Delete Sending/Completed Campaign
**Priority:** Low  
**Preconditions:**
- Campaign exists in "sending" or "completed" status

**Test Steps:**
1. Navigate to campaigns list
2. Try to delete a sending/completed campaign

**Expected Result:**
- Error message: "Campaign cannot be deleted while sending or completed"
- Campaign is not deleted

---

### 1.5 Campaign Statistics

#### Test Case 1.5.1: View Campaign Statistics
**Priority:** High  
**Preconditions:**
- Campaign exists with sent emails

**Test Steps:**
1. Navigate to campaign detail page
2. View statistics section

**Expected Result:**
- Total recipients count is displayed
- Sent count is displayed
- Failed count is displayed
- Pending count is displayed
- Bounced count is displayed (if applicable)
- Unsubscribed count is displayed (if applicable)
- Statistics are accurate

---

#### Test Case 1.5.2: Campaign Statistics After Sending
**Priority:** Medium  
**Preconditions:**
- Campaign has been sent

**Test Steps:**
1. Send a campaign
2. Wait for emails to be sent
3. Check campaign statistics

**Expected Result:**
- `sent_count` matches number of successfully sent emails
- `failed_count` matches number of failed emails
- `pending_count` is 0 if all emails processed
- Statistics update in real-time

---

### 1.6 Email Templates

#### Test Case 1.6.1: Create Email Template
**Priority:** High  
**Preconditions:**
- User is logged in

**Test Steps:**
1. Navigate to `/admin/marketing/templates/create`
2. Fill in template details:
   - Name: "Welcome Template"
   - Subject: "Welcome {{name}}!"
   - Content: "Hello {{name}}, welcome to {{company}}!"
3. Click "Save Template"

**Expected Result:**
- Template is created successfully
- Template appears in templates list
- Available variables are documented
- Success message is displayed

---

#### Test Case 1.6.2: Edit Email Template
**Priority:** High  
**Preconditions:**
- Template exists

**Test Steps:**
1. Navigate to template edit page
2. Modify template content
3. Click "Update Template"

**Expected Result:**
- Template is updated successfully
- Changes are saved
- Success message is displayed

---

#### Test Case 1.6.3: Delete Unused Template
**Priority:** Medium  
**Preconditions:**
- Template exists and is not used in any campaigns

**Test Steps:**
1. Navigate to templates list
2. Click delete on unused template
3. Confirm deletion

**Expected Result:**
- Template is deleted successfully
- Success message is displayed

---

#### Test Case 1.6.4: Delete Template Used in Campaigns
**Priority:** Medium  
**Preconditions:**
- Template is used in at least one campaign

**Test Steps:**
1. Navigate to templates list
2. Try to delete template used in campaigns

**Expected Result:**
- Error message: "Template cannot be deleted as it is used in campaigns"
- Template is not deleted

---

#### Test Case 1.6.5: Preview Template with Variables
**Priority:** Medium  
**Preconditions:**
- Template exists with variables
- Person or lead exists

**Test Steps:**
1. Navigate to template detail page
2. Click "Preview" button
3. Select a person or lead to preview with

**Expected Result:**
- Template preview shows rendered content
- Variables are replaced with actual values
- Preview matches expected output

---

#### Test Case 1.6.6: Template Variable Rendering
**Priority:** High  
**Preconditions:**
- Template exists with variables: {{name}}, {{company}}, {{email}}, {{phone}}

**Test Steps:**
1. Create campaign with template
2. Add recipients with corresponding data
3. Send campaign
4. Check sent emails

**Expected Result:**
- All template variables are correctly replaced
- Missing data shows empty string or placeholder
- Email formatting is preserved
- HTML content renders correctly

---

### 1.7 Recipient Management

#### Test Case 1.7.1: Add Recipients via CSV Upload
**Priority:** Medium  
**Preconditions:**
- Campaign exists

**Test Steps:**
1. Navigate to campaign edit page
2. Click "Add Recipients"
3. Upload CSV file with email addresses
4. Parse CSV

**Expected Result:**
- CSV is parsed correctly
- Recipients are extracted
- Invalid emails are skipped
- Valid recipients are added to campaign

---

#### Test Case 1.7.2: Add Recipients from Leads/Persons
**Priority:** High  
**Preconditions:**
- Leads and persons exist in system

**Test Steps:**
1. Navigate to campaign edit page
2. Click "Add Recipients"
3. Select leads or persons
4. Save

**Expected Result:**
- Selected leads/persons are added as recipients
- Email addresses are extracted from contacts
- Recipients are linked to original records

---

#### Test Case 1.7.3: Remove Recipients
**Priority:** Low  
**Preconditions:**
- Campaign has recipients

**Test Steps:**
1. Navigate to campaign recipients list
2. Remove a recipient
3. Save changes

**Expected Result:**
- Recipient is removed from campaign
- Changes are saved
- Recipient count updates

---

## 2. Collaboration & Communication

### 2.1 Chat Channels

#### Test Case 2.1.1: Create Group Channel
**Priority:** High  
**Preconditions:**
- User is logged in
- At least one other user exists

**Test Steps:**
1. Navigate to collaboration/chat interface
2. Click "Create Channel"
3. Fill in channel details:
   - Name: "Team Discussion"
   - Type: Group
   - Description: "General team chat"
4. Add members
5. Create channel

**Expected Result:**
- Channel is created successfully
- Creator is added as admin
- Channel appears in channels list
- Members can access the channel

---

#### Test Case 2.1.2: Create Direct Message Channel
**Priority:** High  
**Preconditions:**
- User is logged in
- Another user exists

**Test Steps:**
1. Navigate to collaboration/chat interface
2. Click "New Direct Message"
3. Select a user
4. Create channel

**Expected Result:**
- Direct message channel is created
- Only two members (creator and selected user)
- Channel type is "direct"
- Channel appears in channels list

---

#### Test Case 2.1.3: View Channel List
**Priority:** High  
**Preconditions:**
- User has access to channels

**Test Steps:**
1. Navigate to collaboration/chat interface
2. View channels list

**Expected Result:**
- All accessible channels are displayed
- Channels are sorted by last activity
- Unread message indicators are shown
- Channel names and types are displayed

---

#### Test Case 2.1.4: Add Members to Channel
**Priority:** Medium  
**Preconditions:**
- Channel exists
- User is channel admin

**Test Steps:**
1. Navigate to channel settings
2. Click "Add Members"
3. Select users to add
4. Save changes

**Expected Result:**
- Selected users are added as members
- New members receive notification
- Members can access the channel
- Member list updates

---

#### Test Case 2.1.5: Remove Members from Channel
**Priority:** Low  
**Preconditions:**
- Channel exists with multiple members
- User is channel admin

**Test Steps:**
1. Navigate to channel settings
2. Remove a member from channel
3. Save changes

**Expected Result:**
- Member is removed from channel
- Removed member loses access
- Member list updates

---

### 2.2 Chat Messages

#### Test Case 2.2.1: Send Text Message
**Priority:** High  
**Preconditions:**
- User is member of a channel

**Test Steps:**
1. Open a channel
2. Type a message in the input field
3. Click "Send" or press Enter

**Expected Result:**
- Message is sent successfully
- Message appears in chat immediately
- Message is broadcast to all channel members
- Message timestamp is displayed
- Sender name is displayed

---

#### Test Case 2.2.2: Real-time Message Delivery
**Priority:** High  
**Preconditions:**
- Two users are in the same channel
- Broadcasting is configured (Pusher/WebSockets)

**Test Steps:**
1. User A sends a message in channel
2. User B has the channel open in another browser/tab

**Expected Result:**
- User B receives the message immediately (real-time)
- Message appears without page refresh
- Notification sound/indicator appears (if configured)
- Unread count updates

---

#### Test Case 2.2.3: Reply to Message
**Priority:** Medium  
**Preconditions:**
- Channel has messages

**Test Steps:**
1. Click "Reply" on an existing message
2. Type reply message
3. Send reply

**Expected Result:**
- Reply is linked to original message
- Thread structure is maintained
- Reply is displayed below original message
- Reply relationship is visible

---

#### Test Case 2.2.4: Edit Message
**Priority:** Medium  
**Preconditions:**
- User has sent a message

**Test Steps:**
1. Click "Edit" on own message
2. Modify message content
3. Save changes

**Expected Result:**
- Message is updated
- "Edited" indicator appears
- `edited_at` timestamp is set
- Updated message is broadcast to channel members

---

#### Test Case 2.2.5: Delete Message
**Priority:** Medium  
**Preconditions:**
- User has sent a message

**Test Steps:**
1. Click "Delete" on own message
2. Confirm deletion

**Expected Result:**
- Message is marked as deleted
- Message content is hidden or shows "[Message deleted]"
- Message is removed from chat view
- `is_deleted` flag is set

---

#### Test Case 2.2.6: Send Message with File Attachment
**Priority:** Medium  
**Preconditions:**
- Channel exists
- File upload is enabled

**Test Steps:**
1. Open channel
2. Click "Attach File"
3. Select a file
4. Type message (optional)
5. Send message

**Expected Result:**
- File is uploaded successfully
- File attachment appears in message
- File can be downloaded
- File metadata is stored

---

#### Test Case 2.2.7: View Message History
**Priority:** High  
**Preconditions:**
- Channel has message history

**Test Steps:**
1. Open a channel
2. Scroll up to load older messages
3. View message history

**Expected Result:**
- Older messages are loaded (pagination)
- Messages are displayed chronologically
- All message metadata is visible (sender, timestamp, etc.)
- Performance is acceptable for large message lists

---

### 2.3 User Mentions

#### Test Case 2.3.1: Mention User in Message
**Priority:** High  
**Preconditions:**
- Channel has multiple members
- User knows another user's name

**Test Steps:**
1. Type message with "@username" format
2. Send message

**Expected Result:**
- User mention is detected
- Mentioned user receives notification
- Mention is highlighted in message
- Mentioned user's name is linked

---

#### Test Case 2.3.2: Mention Notification
**Priority:** High  
**Preconditions:**
- User A mentions User B in a message

**Test Steps:**
1. User A sends message mentioning User B
2. User B checks notifications

**Expected Result:**
- User B receives notification
- Notification mentions the channel and message
- User B can click notification to go to message
- Notification can be marked as read

---

#### Test Case 2.3.3: Multiple User Mentions
**Priority:** Medium  
**Preconditions:**
- Channel has multiple members

**Test Steps:**
1. Type message mentioning multiple users: "@user1 @user2 @user3"
2. Send message

**Expected Result:**
- All mentioned users are detected
- All mentioned users receive notifications
- All mentions are highlighted in message

---

### 2.4 Notifications

#### Test Case 2.4.1: View Notifications
**Priority:** High  
**Preconditions:**
- User has unread notifications

**Test Steps:**
1. Click notification icon/bell
2. View notifications list

**Expected Result:**
- All notifications are displayed
- Unread notifications are highlighted
- Notifications are sorted by date (newest first)
- Notification details are visible (title, message, timestamp)

---

#### Test Case 2.4.2: Mark Notification as Read
**Priority:** High  
**Preconditions:**
- User has unread notifications

**Test Steps:**
1. Open notifications list
2. Click on a notification or "Mark as read"

**Expected Result:**
- Notification is marked as read
- `read_at` timestamp is set
- Unread count decreases
- Notification appearance updates

---

#### Test Case 2.4.3: Unread Notification Count
**Priority:** High  
**Preconditions:**
- User has unread notifications

**Test Steps:**
1. View notification icon/bell

**Expected Result:**
- Unread count badge is displayed
- Count is accurate
- Count updates in real-time when new notifications arrive

---

#### Test Case 2.4.4: Notification Types
**Priority:** Medium  
**Preconditions:**
- Various notification types exist

**Test Steps:**
1. Generate different notification types:
   - Message in channel
   - User mention
   - Channel invitation
2. Check notifications

**Expected Result:**
- Different notification types are displayed correctly
- Icons/styling differentiate notification types
- Actions are appropriate for each type

---

## 3. Customer Service/Support

### 3.1 Support Tickets

#### Test Case 3.1.1: Create Support Ticket (Admin)
**Priority:** High  
**Preconditions:**
- Admin user is logged in
- Customer (person) exists

**Test Steps:**
1. Navigate to `/admin/support/tickets/create`
2. Fill in ticket details:
   - Title: "Login Issue"
   - Description: "User cannot log in"
   - Customer: Select a person
   - Priority: High
   - Type: Bug
   - Assign to: Select staff member
3. Click "Create Ticket"

**Expected Result:**
- Ticket is created successfully
- Ticket number is auto-generated (unique)
- Ticket status is "open"
- Assigned staff receives notification
- Ticket appears in tickets list

---

#### Test Case 3.1.2: Create Support Ticket (Customer Portal)
**Priority:** High  
**Preconditions:**
- Customer portal is accessible
- Customer account exists

**Test Steps:**
1. Login to customer portal
2. Navigate to "Support" section
3. Click "Create Ticket"
4. Fill in ticket details
5. Submit ticket

**Expected Result:**
- Ticket is created successfully
- Ticket number is generated
- Customer receives confirmation
- Ticket appears in customer's ticket list
- Staff receives notification of new ticket

---

#### Test Case 3.1.3: View Ticket Details
**Priority:** High  
**Preconditions:**
- Ticket exists

**Test Steps:**
1. Navigate to ticket detail page
2. View ticket information

**Expected Result:**
- All ticket details are displayed:
  - Ticket number
  - Title and description
  - Customer information
  - Status and priority
  - Assigned staff
  - Created/updated dates
  - SLA information (if applicable)
- Reply thread is visible

---

#### Test Case 3.1.4: Update Ticket Status
**Priority:** High  
**Preconditions:**
- Ticket exists
- User has permission to update tickets

**Test Steps:**
1. Open ticket detail page
2. Change status (e.g., open → in_progress → resolved)
3. Save changes

**Expected Result:**
- Status is updated successfully
- Status change is logged
- Customer receives notification (if configured)
- Status workflow is followed correctly

---

#### Test Case 3.1.5: Assign Ticket to Staff
**Priority:** High  
**Preconditions:**
- Ticket exists
- Staff members exist

**Test Steps:**
1. Open ticket detail page
2. Click "Assign"
3. Select staff member
4. Save assignment

**Expected Result:**
- Ticket is assigned to selected staff
- Assigned staff receives notification
- Assignment timestamp is recorded
- Ticket status may change to "assigned"

---

#### Test Case 3.1.6: Change Ticket Priority
**Priority:** Medium  
**Preconditions:**
- Ticket exists

**Test Steps:**
1. Open ticket detail page
2. Change priority (low → medium → high → urgent)
3. Save changes

**Expected Result:**
- Priority is updated
- SLA may be recalculated based on new priority
- Priority change is logged
- Ticket sorting/filtering reflects new priority

---

#### Test Case 3.1.7: Close Ticket
**Priority:** High  
**Preconditions:**
- Ticket exists in resolved status

**Test Steps:**
1. Open ticket detail page
2. Click "Close Ticket"
3. Confirm action

**Expected Result:**
- Ticket status changes to "closed"
- `closed_at` timestamp is set
- `closed_by` is recorded
- Ticket cannot be reopened (or requires special permission)
- Customer receives notification

---

### 3.2 Ticket Replies

#### Test Case 3.2.1: Staff Reply to Ticket
**Priority:** High  
**Preconditions:**
- Ticket exists
- Staff member is assigned to ticket

**Test Steps:**
1. Open ticket detail page
2. Type reply in reply box
3. Click "Send Reply"

**Expected Result:**
- Reply is added to ticket thread
- Reply is visible to customer
- Customer receives email notification
- Reply timestamp is recorded
- Staff name is displayed with reply

---

#### Test Case 3.2.2: Customer Reply to Ticket
**Priority:** High  
**Preconditions:**
- Ticket exists
- Customer has access

**Test Steps:**
1. Customer opens ticket in portal
2. Types reply
3. Sends reply

**Expected Result:**
- Reply is added to ticket thread
- Reply is visible to staff
- Assigned staff receives notification
- Ticket status may change to "waiting_customer" → "in_progress"
- Reply timestamp is recorded

---

#### Test Case 3.2.3: Internal Note (Staff Only)
**Priority:** Medium  
**Preconditions:**
- Ticket exists
- Staff member has permission

**Test Steps:**
1. Open ticket detail page
2. Click "Add Internal Note"
3. Type internal note
4. Save note

**Expected Result:**
- Internal note is added to ticket
- Note is marked as `is_internal = true`
- Note is visible only to staff members
- Customer cannot see internal notes
- Note appears in thread with different styling

---

#### Test Case 3.2.4: Reply with File Attachment
**Priority:** Medium  
**Preconditions:**
- Ticket exists
- File upload is enabled

**Test Steps:**
1. Open ticket detail page
2. Type reply
3. Attach file(s)
4. Send reply

**Expected Result:**
- File(s) are uploaded successfully
- Attachments are linked to reply
- Files can be downloaded by recipient
- File metadata is stored
- Attachment count is displayed

---

### 3.3 SLA Management

#### Test Case 3.3.1: Create SLA Rule
**Priority:** High  
**Preconditions:**
- Admin user is logged in

**Test Steps:**
1. Navigate to `/admin/support/slas/create`
2. Fill in SLA details:
   - Name: "High Priority SLA"
   - Priority: High
   - First Response Time: 60 minutes
   - Resolution Time: 240 minutes
   - Status: Active
3. Save SLA

**Expected Result:**
- SLA rule is created successfully
- SLA appears in SLA list
- SLA can be assigned to tickets
- SLA rules are enforced

---

#### Test Case 3.3.2: SLA Calculation on Ticket Creation
**Priority:** High  
**Preconditions:**
- SLA rules exist for different priorities

**Test Steps:**
1. Create a ticket with priority "high"
2. Assign appropriate SLA
3. Check ticket SLA fields

**Expected Result:**
- Appropriate SLA is selected based on priority
- `sla_due_at` is calculated correctly
- First response deadline is set
- Resolution deadline is set

---

#### Test Case 3.3.3: SLA Breach Detection
**Priority:** High  
**Preconditions:**
- Ticket exists with SLA
- SLA deadline has passed

**Test Steps:**
1. Create ticket with SLA
2. Wait for SLA deadline to pass (or manually trigger check)
3. Run SLA check job/command

**Expected Result:**
- SLA breach is detected
- `sla_breached` flag is set to true
- Breach notification is sent to staff
- Ticket is flagged/highlighted
- Breach is logged

---

#### Test Case 3.3.4: SLA First Response Tracking
**Priority:** High  
**Preconditions:**
- Ticket exists with SLA
- First response time is defined

**Test Steps:**
1. Create ticket with SLA
2. Staff sends first reply
3. Check SLA compliance

**Expected Result:**
- First response time is calculated
- Response is within SLA deadline (or breach is detected)
- SLA status is updated
- Compliance metrics are tracked

---

### 3.4 Knowledge Base

#### Test Case 3.4.1: Create Knowledge Base Article
**Priority:** High  
**Preconditions:**
- Admin user is logged in
- Category exists

**Test Steps:**
1. Navigate to `/admin/support/knowledge-base/articles/create`
2. Fill in article details:
   - Title: "How to Reset Password"
   - Content: Article content with HTML formatting
   - Category: Select category
   - Status: Published
3. Save article

**Expected Result:**
- Article is created successfully
- Slug is auto-generated from title
- Article appears in knowledge base
- Article is searchable
- Article can be viewed by customers (if published)

---

#### Test Case 3.4.2: Edit Knowledge Base Article
**Priority:** High  
**Preconditions:**
- Article exists

**Test Steps:**
1. Navigate to article edit page
2. Modify article content
3. Save changes

**Expected Result:**
- Article is updated successfully
- Changes are saved
- Article versioning (if implemented)
- Success message is displayed

---

#### Test Case 3.4.3: Publish/Unpublish Article
**Priority:** Medium  
**Preconditions:**
- Article exists in draft status

**Test Steps:**
1. Open article edit page
2. Change status from "draft" to "published"
3. Save changes

**Expected Result:**
- Article status changes to "published"
- `published_at` timestamp is set
- Article becomes visible to customers
- Article appears in public knowledge base

---

#### Test Case 3.4.4: Create Knowledge Base Category
**Priority:** Medium  
**Preconditions:**
- Admin user is logged in

**Test Steps:**
1. Navigate to categories management
2. Create new category:
   - Name: "Account Management"
   - Slug: "account-management"
   - Description: "Articles about account settings"
   - Parent category (optional)
3. Save category

**Expected Result:**
- Category is created successfully
- Category appears in category tree
- Articles can be assigned to category
- Category can have subcategories (if parent_id is set)

---

#### Test Case 3.4.5: Search Knowledge Base
**Priority:** High  
**Preconditions:**
- Knowledge base has articles

**Test Steps:**
1. Navigate to knowledge base search
2. Enter search query
3. View search results

**Expected Result:**
- Relevant articles are returned
- Search works on title and content
- Results are ranked by relevance
- Search is fast and responsive

---

#### Test Case 3.4.6: Article Helpfulness Rating
**Priority:** Low  
**Preconditions:**
- Article exists and is published
- Customer can view articles

**Test Steps:**
1. Customer views article
2. Clicks "Helpful" or "Not Helpful"
3. Rating is recorded

**Expected Result:**
- Rating is saved
- `helpful_count` or `not_helpful_count` increments
- Ratings are tracked for analytics
- Thank you message is displayed

---

## 4. Integrations Marketplace

### 4.1 Integration Discovery

#### Test Case 4.1.1: View Integrations Marketplace
**Priority:** High  
**Preconditions:**
- Admin user is logged in
- Integrations are available

**Test Steps:**
1. Navigate to `/admin/integrations`
2. View integrations marketplace

**Expected Result:**
- Available integrations are displayed
- Integrations are categorized
- Integration cards show:
  - Name and description
  - Icon/logo
  - Category
  - Status (installed/available)
  - Install button
- Marketplace is searchable/filterable

---

#### Test Case 4.1.2: Search Integrations
**Priority:** Medium  
**Preconditions:**
- Integrations marketplace has multiple integrations

**Test Steps:**
1. Navigate to integrations marketplace
2. Enter search query (e.g., "stripe")
3. View filtered results

**Expected Result:**
- Search filters integrations by name/description
- Results are relevant
- Search is fast and responsive

---

#### Test Case 4.1.3: Filter Integrations by Category
**Priority:** Medium  
**Preconditions:**
- Integrations exist in multiple categories

**Test Steps:**
1. Navigate to integrations marketplace
2. Select category filter (e.g., "payments", "calendar")
3. View filtered integrations

**Expected Result:**
- Only integrations in selected category are shown
- Filter works correctly
- Clear filter option is available

---

### 4.2 Integration Installation

#### Test Case 4.2.1: Install Integration
**Priority:** High  
**Preconditions:**
- Integration exists and is available
- Admin user is logged in

**Test Steps:**
1. Navigate to integration detail page
2. Click "Install" button
3. Follow installation wizard
4. Provide required configuration
5. Complete installation

**Expected Result:**
- Integration is installed successfully
- `is_installed` flag is set to true
- `installed_at` timestamp is set
- `installed_by` is recorded
- Integration status changes to "active"
- Integration appears in "Installed" section

---

#### Test Case 4.2.2: Install Stripe Integration
**Priority:** High  
**Preconditions:**
- Stripe integration is available
- User has Stripe API keys

**Test Steps:**
1. Navigate to Stripe integration page
2. Click "Install"
3. Enter Stripe API keys (test/live)
4. Configure webhook URL
5. Test connection
6. Complete installation

**Expected Result:**
- Stripe integration is installed
- API keys are securely stored
- Connection test passes
- Integration can sync data
- Webhook endpoint is configured

---

#### Test Case 4.2.3: Install Google Calendar Integration
**Priority:** High  
**Preconditions:**
- Google Calendar integration is available
- User has Google account

**Test Steps:**
1. Navigate to Google Calendar integration page
2. Click "Install"
3. Authorize with Google OAuth
4. Grant calendar permissions
5. Complete installation

**Expected Result:**
- OAuth flow completes successfully
- Access tokens are stored securely
- Calendar permissions are granted
- Integration can sync events
- Two-way sync works (CRM ↔ Google Calendar)

---

#### Test Case 4.2.4: Install Webhook Integration (Zapier)
**Priority:** Medium  
**Preconditions:**
- Webhook integration is available
- User has webhook URL

**Test Steps:**
1. Navigate to webhook integration page
2. Click "Install"
3. Enter webhook URL
4. Configure authentication (if required)
5. Test webhook
6. Complete installation

**Expected Result:**
- Webhook integration is installed
- Webhook URL is configured
- Test webhook is sent successfully
- Integration can receive events
- Events are forwarded to webhook URL

---

### 4.3 Integration Configuration

#### Test Case 4.3.1: Configure Installed Integration
**Priority:** High  
**Preconditions:**
- Integration is installed

**Test Steps:**
1. Navigate to installed integrations
2. Click "Configure" on an integration
3. Modify configuration settings
4. Save changes

**Expected Result:**
- Configuration page opens
- Settings can be modified
- Changes are saved successfully
- Integration reinitializes with new settings
- Success message is displayed

---

#### Test Case 4.3.2: Test Integration Connection
**Priority:** High  
**Preconditions:**
- Integration is installed and configured

**Test Steps:**
1. Navigate to integration settings
2. Click "Test Connection"

**Expected Result:**
- Connection test is performed
- Success or failure message is displayed
- Connection status is updated
- Error details are shown if test fails

---

#### Test Case 4.3.3: View Integration Logs
**Priority:** Medium  
**Preconditions:**
- Integration is installed
- Integration has activity logs

**Test Steps:**
1. Navigate to integration detail page
2. Click "View Logs"
3. Review integration activity

**Expected Result:**
- Integration logs are displayed
- Logs show:
  - Timestamp
  - Level (info/warning/error)
  - Message
  - Context data
- Logs are paginated
- Logs can be filtered by level

---

### 4.4 Integration Functionality

#### Test Case 4.4.1: Stripe Customer Sync
**Priority:** High  
**Preconditions:**
- Stripe integration is installed and configured
- Stripe has customers

**Test Steps:**
1. Navigate to integration settings
2. Click "Sync Customers"
3. Monitor sync process

**Expected Result:**
- Stripe customers are synced to CRM
- Customers are created/updated in persons/contacts
- Sync status is displayed
- Sync logs are recorded
- Errors are handled gracefully

---

#### Test Case 4.4.2: Google Calendar Event Sync
**Priority:** High  
**Preconditions:**
- Google Calendar integration is installed
- Google Calendar has events
- CRM has activities

**Test Steps:**
1. Configure two-way sync
2. Create event in Google Calendar
3. Check CRM activities

**Expected Result:**
- Google Calendar events sync to CRM activities
- CRM activities sync to Google Calendar
- Sync is bidirectional
- Conflicts are handled appropriately
- Sync happens in real-time or scheduled

---

#### Test Case 4.4.3: Webhook Event Trigger
**Priority:** Medium  
**Preconditions:**
- Webhook integration is installed
- Webhook URL is configured
- CRM events occur (e.g., lead created)

**Test Steps:**
1. Create a lead in CRM
2. Monitor webhook endpoint

**Expected Result:**
- Webhook is triggered on event
- Event data is sent to webhook URL
- Webhook payload includes relevant data
- Webhook delivery is logged
- Retry mechanism works if delivery fails

---

### 4.5 Integration Management

#### Test Case 4.5.1: Uninstall Integration
**Priority:** Medium  
**Preconditions:**
- Integration is installed

**Test Steps:**
1. Navigate to integration settings
2. Click "Uninstall"
3. Confirm uninstallation

**Expected Result:**
- Integration is uninstalled
- `is_installed` flag is set to false
- Integration data is preserved (or cleaned up based on policy)
- Integration status changes to "inactive"
- Integration can be reinstalled later

---

#### Test Case 4.5.2: View Integration Status
**Priority:** Medium  
**Preconditions:**
- Integration is installed

**Test Steps:**
1. Navigate to integrations list
2. View integration status

**Expected Result:**
- Integration status is displayed (active/inactive/error)
- Status indicator is clear (color-coded)
- Last sync time is shown (if applicable)
- Error messages are visible if status is "error"

---

#### Test Case 4.5.3: Integration Error Handling
**Priority:** High  
**Preconditions:**
- Integration is installed
- Integration encounters an error (e.g., API rate limit, invalid credentials)

**Test Steps:**
1. Trigger integration error scenario
2. Check integration status and logs

**Expected Result:**
- Error is caught and logged
- Integration status changes to "error"
- Error message is stored
- Notification is sent to admin (if configured)
- Integration can be reconfigured to resolve error

---

## 5. Integration Testing

### 5.1 Cross-Feature Integration

#### Test Case 5.1.1: Marketing Campaign → Email Integration
**Priority:** High  
**Preconditions:**
- Marketing campaign exists
- Email module is configured

**Test Steps:**
1. Create and send marketing campaign
2. Check email delivery
3. Verify email tracking

**Expected Result:**
- Campaign emails are sent via email module
- Email delivery status is tracked
- Email opens/clicks are recorded (if tracking enabled)
- Email records are created in email module

---

#### Test Case 5.1.2: Support Ticket → Email Notification
**Priority:** High  
**Preconditions:**
- Support ticket exists
- Email notifications are enabled

**Test Steps:**
1. Create support ticket
2. Staff replies to ticket
3. Check email notifications

**Expected Result:**
- Customer receives email notification
- Staff receives email notification (if configured)
- Email notifications contain ticket details
- Email links work correctly

---

#### Test Case 5.1.3: Collaboration → Activity Feed
**Priority:** Medium  
**Preconditions:**
- Collaboration is enabled
- Activity module exists

**Test Steps:**
1. Send message in collaboration channel
2. Check activity feed

**Expected Result:**
- Collaboration activities appear in activity feed (if integrated)
- Activity feed shows relevant collaboration events
- Activities are properly categorized

---

## 6. Performance Testing

### 6.1 Marketing Campaign Performance

#### Test Case 6.1.1: Send Large Campaign
**Priority:** Medium  
**Preconditions:**
- Campaign exists with 1000+ recipients
- Queue system is configured

**Test Steps:**
1. Create campaign with 1000 recipients
2. Send campaign
3. Monitor queue processing

**Expected Result:**
- Campaign sending processes efficiently
- Queue jobs are processed in batches
- Server performance remains acceptable
- All emails are eventually sent
- No memory leaks or timeouts

---

#### Test Case 6.1.2: Campaign Template Rendering Performance
**Priority:** Low  
**Preconditions:**
- Campaign uses template with complex HTML

**Test Steps:**
1. Create campaign with complex template
2. Send to multiple recipients
3. Monitor rendering time

**Expected Result:**
- Template rendering is fast
- Variable substitution is efficient
- No performance degradation with many recipients

---

### 6.2 Collaboration Performance

#### Test Case 6.2.1: Real-time Message Delivery Performance
**Priority:** High  
**Preconditions:**
- Broadcasting is configured
- Multiple users are in channel

**Test Steps:**
1. Have 10+ users in a channel
2. Send message
3. Measure delivery time

**Expected Result:**
- Messages are delivered in real-time (< 1 second)
- Broadcasting system handles load
- No message loss
- System remains responsive

---

#### Test Case 6.2.2: Load Test - Multiple Channels
**Priority:** Medium  
**Preconditions:**
- Multiple channels exist
- Multiple users are active

**Test Steps:**
1. Create 50+ active channels
2. Have users send messages simultaneously
3. Monitor system performance

**Expected Result:**
- System handles multiple concurrent channels
- Message delivery remains fast
- Database queries are optimized
- No performance degradation

---

## 7. Security Testing

### 7.1 Authorization & Permissions

#### Test Case 7.1.1: Marketing Campaign Permissions
**Priority:** High  
**Preconditions:**
- Multiple user roles exist

**Test Steps:**
1. Login as non-admin user
2. Try to access marketing campaigns
3. Try to create/edit campaigns

**Expected Result:**
- Access is restricted based on permissions
- Unauthorized users cannot access campaigns
- Permission checks are enforced at controller level

---

#### Test Case 7.1.2: Collaboration Channel Access
**Priority:** High  
**Preconditions:**
- Channels exist
- User is not member of a channel

**Test Steps:**
1. Login as user
2. Try to access channel where user is not a member
3. Try to send message to restricted channel

**Expected Result:**
- User cannot access restricted channels
- Messages cannot be sent to channels user is not member of
- Access control is enforced

---

#### Test Case 7.1.3: Support Ticket Access Control
**Priority:** High  
**Preconditions:**
- Tickets exist
- Users have different roles

**Test Steps:**
1. Login as customer
2. Try to access another customer's ticket
3. Login as staff
4. Verify staff can access all tickets

**Expected Result:**
- Customers can only access their own tickets
- Staff can access all tickets (based on permissions)
- Access control is enforced correctly

---

### 7.2 Data Validation & Sanitization

#### Test Case 7.2.1: XSS Prevention in Campaigns
**Priority:** High  
**Preconditions:**
- User can create campaigns

**Test Steps:**
1. Create campaign with XSS payload in content:
   - `<script>alert('XSS')</script>`
   - `<img src=x onerror=alert('XSS')>`
2. Send campaign
3. Check email output

**Expected Result:**
- XSS payloads are sanitized/escaped
- Scripts do not execute in emails
- HTML is properly sanitized
- Safe HTML is preserved

---

#### Test Case 7.2.2: SQL Injection Prevention
**Priority:** High  
**Preconditions:**
- User can input data in forms

**Test Steps:**
1. Enter SQL injection payload in search/input fields:
   - `'; DROP TABLE users; --`
   - `1' OR '1'='1`
2. Submit forms
3. Check database

**Expected Result:**
- SQL injection attempts are prevented
- Input is properly parameterized
- Database queries use prepared statements
- No SQL execution occurs

---

#### Test Case 7.2.3: File Upload Security
**Priority:** High  
**Preconditions:**
- File upload is enabled

**Test Steps:**
1. Try to upload malicious files:
   - PHP shell script
   - Executable file
   - Oversized file
2. Check file handling

**Expected Result:**
- File types are validated
- Dangerous file types are rejected
- File size limits are enforced
- Files are stored securely
- File names are sanitized

---

## 8. API Testing

### 8.1 Marketing API Endpoints

#### Test Case 8.1.1: Create Campaign via API
**Priority:** Medium  
**Preconditions:**
- API authentication is configured
- API token exists

**Test Steps:**
1. Send POST request to `/api/admin/marketing/campaigns`
2. Include authentication token
3. Send campaign data in JSON format

**Expected Result:**
- Campaign is created successfully
- API returns 201 Created status
- Response includes campaign data
- Campaign is accessible via web interface

---

#### Test Case 8.1.2: Get Campaigns via API
**Priority:** Medium  
**Preconditions:**
- Campaigns exist
- API authentication is configured

**Test Steps:**
1. Send GET request to `/api/admin/marketing/campaigns`
2. Include authentication token
3. Check response

**Expected Result:**
- API returns list of campaigns
- Response format is JSON
- Pagination works (if implemented)
- Filtering/sorting works (if implemented)

---

### 8.2 Collaboration API Endpoints

#### Test Case 8.2.1: Send Message via API
**Priority:** Medium  
**Preconditions:**
- Channel exists
- API authentication is configured

**Test Steps:**
1. Send POST request to `/api/admin/collaboration/chat/message`
2. Include channel_id and message content
3. Check response

**Expected Result:**
- Message is sent successfully
- API returns message data
- Message is broadcast to channel members
- Response includes message ID and timestamp

---

#### Test Case 8.2.2: Get Channel Messages via API
**Priority:** Medium  
**Preconditions:**
- Channel has messages
- API authentication is configured

**Test Steps:**
1. Send GET request to `/api/admin/collaboration/chat/channel/{id}/messages`
2. Include authentication token
3. Check response

**Expected Result:**
- API returns messages for channel
- Messages are ordered by timestamp
- Pagination works (if implemented)
- Response format is JSON

---

## 9. User Experience Testing

### 9.1 Responsive Design

#### Test Case 9.1.1: Mobile Responsiveness - Campaigns
**Priority:** Medium  
**Preconditions:**
- Marketing campaigns UI exists

**Test Steps:**
1. Access campaigns interface on mobile device/browser
2. Test creating campaign
3. Test viewing campaign list
4. Test campaign details

**Expected Result:**
- Interface is responsive
- Forms are usable on mobile
- Navigation works on touch devices
- Content is readable without zooming

---

#### Test Case 9.1.2: Mobile Responsiveness - Collaboration
**Priority:** High  
**Preconditions:**
- Collaboration UI exists

**Test Steps:**
1. Access collaboration interface on mobile
2. Test sending messages
3. Test viewing channels
4. Test notifications

**Expected Result:**
- Chat interface is mobile-friendly
- Message input is accessible
- Channels list is navigable
- Real-time updates work on mobile

---

### 9.2 Accessibility

#### Test Case 9.2.1: Keyboard Navigation
**Priority:** Medium  
**Preconditions:**
- Features are accessible via web interface

**Test Steps:**
1. Navigate interface using only keyboard
2. Test all major functions
3. Verify focus indicators

**Expected Result:**
- All functions are keyboard accessible
- Tab order is logical
- Focus indicators are visible
- Keyboard shortcuts work (if implemented)

---

#### Test Case 9.2.2: Screen Reader Compatibility
**Priority:** Low  
**Preconditions:**
- Screen reader software available

**Test Steps:**
1. Use screen reader to navigate features
2. Test creating campaigns
3. Test collaboration interface
4. Test support tickets

**Expected Result:**
- Screen reader can read all content
- ARIA labels are present
- Forms are properly labeled
- Interactive elements are announced

---

## 10. Error Handling & Edge Cases

### 10.1 Error Scenarios

#### Test Case 10.1.1: Network Failure During Campaign Send
**Priority:** Medium  
**Preconditions:**
- Campaign is being sent
- Network connection is interrupted

**Test Steps:**
1. Start sending campaign
2. Interrupt network connection
3. Restore connection
4. Check campaign status

**Expected Result:**
- Campaign sending handles network errors gracefully
- Failed emails are retried (if retry logic exists)
- Campaign status reflects failures
- System recovers when connection is restored

---

#### Test Case 10.1.2: Database Connection Loss
**Priority:** Low  
**Preconditions:**
- Application is running
- Database connection is lost

**Test Steps:**
1. Stop database service
2. Try to access features
3. Restart database
4. Check application recovery

**Expected Result:**
- Error messages are user-friendly
- System handles database errors gracefully
- Application recovers when database is restored
- No data corruption occurs

---

#### Test Case 10.1.3: Queue Worker Failure
**Priority:** Medium  
**Preconditions:**
- Queue jobs are pending
- Queue worker fails

**Test Steps:**
1. Send campaign (creates queue jobs)
2. Stop queue worker
3. Restart queue worker
4. Check job processing

**Expected Result:**
- Queue jobs remain in queue
- Jobs are processed when worker restarts
- No jobs are lost
- Failed jobs are logged

---

### 10.2 Edge Cases

#### Test Case 10.2.1: Very Long Campaign Content
**Priority:** Low  
**Preconditions:**
- User can create campaigns

**Test Steps:**
1. Create campaign with very long content (100KB+)
2. Save campaign
3. Send campaign

**Expected Result:**
- Campaign saves successfully
- Content is stored correctly
- Email sending works
- Performance is acceptable

---

#### Test Case 10.2.2: Special Characters in Content
**Priority:** Medium  
**Preconditions:**
- User can create campaigns/messages

**Test Steps:**
1. Create content with special characters:
   - Emojis: 😀 🎉 ✅
   - Unicode: 中文 العربية
   - HTML entities: &amp; &lt; &gt;
2. Save and send

**Expected Result:**
- Special characters are handled correctly
- Encoding is preserved
- Content displays correctly in emails/messages
- No encoding errors occur

---

#### Test Case 10.2.3: Concurrent Message Sending
**Priority:** Medium  
**Preconditions:**
- Multiple users in channel

**Test Steps:**
1. Have multiple users send messages simultaneously
2. Check message order
3. Verify all messages are delivered

**Expected Result:**
- All messages are received
- Message order is preserved (or consistent)
- No messages are lost
- System handles concurrency correctly

---

## 11. Data Migration & Backup

### 11.1 Data Integrity

#### Test Case 11.1.1: Campaign Data Backup
**Priority:** Medium  
**Preconditions:**
- Campaigns exist
- Backup system is configured

**Test Steps:**
1. Create campaigns
2. Perform backup
3. Restore from backup
4. Verify campaign data

**Expected Result:**
- Backup includes all campaign data
- Recipients are backed up
- Templates are backed up
- Restore process works correctly
- Data integrity is maintained

---

#### Test Case 11.1.2: Collaboration Data Export
**Priority:** Low  
**Preconditions:**
- Channels and messages exist

**Test Steps:**
1. Export collaboration data
2. Verify export format
3. Check data completeness

**Expected Result:**
- Export includes channels, messages, members
- Export format is usable (JSON/CSV)
- All data is included
- Export can be imported (if import feature exists)

---

## 12. Documentation & Help

### 12.1 User Documentation

#### Test Case 12.1.1: Feature Documentation Availability
**Priority:** Low  
**Preconditions:**
- Documentation exists

**Test Steps:**
1. Look for help/documentation links
2. Access documentation for each feature
3. Verify documentation completeness

**Expected Result:**
- Documentation is accessible
- Documentation covers all features
- Examples are provided
- Documentation is up-to-date

---

#### Test Case 12.1.2: In-App Help/Tooltips
**Priority:** Low  
**Preconditions:**
- UI has help elements

**Test Steps:**
1. Hover over help icons
2. Click help links
3. View tooltips

**Expected Result:**
- Help icons/tooltips are present
- Help content is useful
- Help is contextually relevant
- Help is easy to understand

---

## Notes for Testers

### Testing Environment Setup
1. Ensure all required services are running (database, queue worker, broadcasting server)
2. Configure email service for testing (use mail trap or similar)
3. Set up test users with different roles
4. Configure integrations with test credentials
5. Enable test mode for external services (Stripe test mode, etc.)

### Test Data Preparation
- Create test campaigns with various configurations
- Set up test channels and messages
- Create test support tickets
- Install test integrations
- Prepare test files for uploads

### Automated Testing Recommendations
- Unit tests for services (CampaignService, ChatService, etc.)
- Feature tests for controllers
- Integration tests for queue jobs
- API tests for endpoints
- Browser tests for UI workflows (using Laravel Dusk or similar)

### Regression Testing
- After each bug fix, run relevant test cases
- Test all features after major updates
- Verify existing functionality is not broken
- Check database migrations don't break existing data

### Performance Benchmarks
- Campaign sending: Should handle 1000+ recipients efficiently
- Real-time messaging: Should deliver messages in < 1 second
- API response times: Should be < 500ms for most endpoints
- Database queries: Should be optimized, use indexes

---

**Document Version:** 1.0  
**Last Updated:** January 2025  
**Test Coverage:** Marketing, Collaboration, Support, Integrations

