# WhatsApp Integration for ProvenSuccess CRM

## Overview
This WhatsApp integration provides **two modes** for sending WhatsApp messages directly from the CRM:

1. **Personal WhatsApp Mode** (Default) - Uses `wa.me` links to open WhatsApp with pre-filled messages
2. **Business API Mode** (Optional) - Sends messages programmatically via Meta's WhatsApp Business API

## Features
- ✅ **Dual Mode Support**: Works with both personal WhatsApp and Business API
- ✅ **Message Templates**: Quick templates for common scenarios
- ✅ **Smart Number Detection**: Automatically prioritizes mobile/WhatsApp labeled numbers
- ✅ **Modal Interface**: Clean, integrated UI for composing messages
- ✅ **Person & Lead Profiles**: Send messages from both contact types

---

## Mode 1: Personal WhatsApp (Recommended for Most Users)

### How It Works
- Click the WhatsApp button on any Person or Lead profile
- Compose your message (optionally use a template)
- Click "Open in WhatsApp"
- Your WhatsApp app/web opens with the message pre-filled
- You manually send the message

### Advantages
✅ **No Setup Required** - Works immediately  
✅ **No API Costs** - Completely free  
✅ **Personal Numbers** - Use your own WhatsApp account  
✅ **No Account Bans** - 100% compliant with WhatsApp ToS  
✅ **See Conversation History** - All messages in your WhatsApp app  

### Limitations
❌ **Manual Sending** - You must click send in WhatsApp  
❌ **No Automation** - Cannot send messages programmatically  
❌ **No CRM History** - Messages aren't logged in the CRM  

### Usage
1. Navigate to **Contacts > Persons** or **Leads**
2. Open any profile with a contact number
3. Click the green **WhatsApp** button
4. Select a template (optional) or type your message
5. Click **"Open in WhatsApp"**
6. Your WhatsApp opens → Review and send

---

## Mode 2: WhatsApp Business API (For Advanced Users)

### When to Use This
- You need to send messages programmatically
- You want messages logged in the CRM
- You're running a business with high message volume
- You need official WhatsApp Business verification

### Setup Instructions

#### Step 1: Create a Meta Business Account
1. Go to [Meta Business Suite](https://business.facebook.com/)
2. Create or select your Business Account
3. Navigate to **Business Settings**

#### Step 2: Set Up WhatsApp Business API
1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Create a new App or select an existing one
3. Add the **WhatsApp** product to your app
4. Follow the setup wizard to:
   - Link your WhatsApp Business Account
   - Add a phone number (or use the test number provided)
   - Verify your business

#### Step 3: Get Your Credentials
You need two pieces of information:

**A. Phone Number ID:**
1. In your Meta App Dashboard, go to **WhatsApp > API Setup**
2. Copy the **Phone Number ID** (looks like: `123456789012345`)

**B. Access Token:**
1. In the same page, you'll see a **Temporary Access Token**
2. For production, generate a **Permanent Access Token**:
   - Go to **Business Settings > System Users**
   - Create a System User
   - Assign WhatsApp permissions
   - Generate a token

#### Step 4: Configure in CRM
1. Log in to the CRM
2. Click on your **Profile** (top right)
3. Go to **Account Settings**
4. Scroll to the **WhatsApp Configuration** section
5. Enter:
   - **Phone Number ID**: Your WhatsApp Business Phone Number ID
   - **Access Token**: Your Meta API Access Token
6. Click **Save**

### Usage (Business API Mode)
Once configured, the WhatsApp button automatically switches to **Business API Mode**:
1. Click the WhatsApp button
2. Compose your message
3. Click **"Send via API"**
4. Message is sent automatically via your WhatsApp Business number

### Advantages
✅ **Automated Sending** - Messages sent programmatically  
✅ **CRM Integration** - Messages can be logged (future feature)  
✅ **Professional** - Official WhatsApp Business verification  
✅ **Scalable** - Handle high message volumes  

### Limitations
❌ **Setup Required** - Meta Business account needed  
❌ **Costs Money** - Charges per conversation after free tier  
❌ **Approval Process** - Meta must approve your business  
❌ **Separate Number** - Cannot use personal WhatsApp number  

---

## Message Templates

Both modes include quick templates for common scenarios:

### For Person Profiles:
- **Follow-up Message**: "Hi {name}, I wanted to follow up on our previous conversation..."
- **Introduction**: "Hello {name}, thank you for your interest!..."
- **Check-in**: "Hi {name}, just checking in to see if you have any questions..."
- **Update Notification**: "Hello {name}, I have some exciting updates to share..."

### For Lead Profiles:
- **Follow-up on Lead**: "Hi {name}, I wanted to follow up on your interest in {lead_title}..."
- **Lead Introduction**: "Hello {name}, thank you for your interest in {lead_title}!..."
- **Lead Check-in**: "Hi {name}, just checking in regarding {lead_title}..."
- **Lead Update**: "Hello {name}, I have some exciting updates about {lead_title}..."

---

## Technical Details

### Database Schema
```sql
-- Added to users table
whatsapp_phone_number_id VARCHAR(255) NULL
whatsapp_access_token TEXT NULL
```

### API Endpoints
- `POST /admin/contacts/persons/{id}/whatsapp/send` - Send message from person profile
- `POST /admin/leads/{id}/whatsapp/send` - Send message from lead profile

### How Mode Detection Works
```php
// Check if user has Business API credentials
$hasBusinessAPI = auth()->user()->whatsapp_phone_number_id && 
                  auth()->user()->whatsapp_access_token;

if ($hasBusinessAPI) {
    // Send via Meta Cloud API
} else {
    // Open wa.me link with pre-filled message
}
```

---

## Troubleshooting

### Personal WhatsApp Mode

**"No contact number found"**
- Ensure the person/lead has at least one contact number
- Add a number with label "Mobile" or "WhatsApp" for best results

**WhatsApp doesn't open**
- Check if you have WhatsApp installed (desktop or mobile)
- Try WhatsApp Web: https://web.whatsapp.com
- Ensure pop-ups are not blocked in your browser

### Business API Mode

**"Please configure your WhatsApp credentials"**
- Add your Phone Number ID and Access Token in Account Settings
- Verify the credentials are correct

**"Failed to send message"**
- Check if your Access Token is still valid
- Verify the Phone Number ID is correct
- Ensure the recipient's number is in E.164 format (e.g., +1234567890)
- Check Meta API rate limits
- Review Meta API status page

**Messages not delivering**
- Verify your WhatsApp Business number is active
- Check if the recipient has blocked your business number
- Ensure you're within Meta's messaging windows (24-hour window after last customer message)

---

## Cost Considerations (Business API Only)

- WhatsApp Business API charges per conversation
- First 1,000 conversations/month are free
- After that, pricing varies by country
- Check [Meta's pricing page](https://developers.facebook.com/docs/whatsapp/pricing) for details

**Personal WhatsApp Mode is completely FREE!**

---

## Future Enhancements

### Planned Features:
1. **Receive Messages** (Business API only)
   - Webhook integration with Meta
   - Store incoming messages in CRM
   - Conversation history view
   - Real-time notifications

2. **Message History** (Both modes)
   - Log all sent messages in CRM
   - View conversation timeline
   - Search message history

3. **Custom Templates**
   - Create your own message templates
   - Template variables (name, company, etc.)
   - Template categories

4. **Bulk Messaging** (Business API only)
   - Send to multiple contacts at once
   - Campaign management
   - Delivery reports

---

## Comparison: Personal vs Business API

| Feature | Personal WhatsApp | Business API |
|---------|------------------|--------------|
| **Setup Time** | Instant | 1-2 days |
| **Cost** | Free | Paid (after free tier) |
| **Sending** | Manual | Automatic |
| **Your Number** | Personal | Business |
| **CRM Logging** | No | Yes (future) |
| **Receiving** | In WhatsApp app | In CRM (future) |
| **Compliance** | 100% Safe | Official |
| **Best For** | Small teams, personal touch | Large teams, automation |

---

## Recommendations

### Use Personal WhatsApp Mode If:
- You're a small team (1-10 people)
- You want a personal touch with customers
- You don't need message automation
- You want zero setup and zero cost

### Use Business API Mode If:
- You're a larger organization
- You need message automation
- You want professional WhatsApp Business verification
- You need CRM integration and reporting
- You're willing to invest in setup and costs

---

## Support

For issues or questions:
1. Check this documentation first
2. Review the CRM logs in `storage/logs/laravel.log`
3. For Business API issues, check [Meta's documentation](https://developers.facebook.com/docs/whatsapp)
4. Contact your system administrator

---

**Version**: 2.0.0  
**Last Updated**: 2025-12-30  
**Author**: ProvenSuccess CRM Development Team
