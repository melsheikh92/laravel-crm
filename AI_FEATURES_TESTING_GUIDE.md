# AI Features Testing Guide

This guide will help you test all the newly implemented AI features in the CRM.

## Prerequisites

### 1. Configure OpenRouter API Key

Before testing any AI features, you need to configure the OpenRouter API key:

1. Log in to the admin panel
2. Go to **Settings** → **Configuration**
3. Navigate to **General** → **Magic AI** → **Settings**
4. Enter your OpenRouter API key in the `API Key` field
5. Select your preferred AI model
6. Save the configuration

**Get an API Key:**
- Sign up at [OpenRouter.ai](https://openrouter.ai/)
- Generate an API key from your dashboard
- Add credits to your account (required for API usage)

---

## Feature 1: AI Email Reply Generation

### Testing Steps

1. **Access Email View:**
   - Navigate to **Mail** → **Inbox** (or any email folder)
   - Open an email to view its details

2. **Generate AI Reply:**
   - In the email view, look for the **"✨ AI Reply"** button
   - Click the button
   - Wait for the AI to generate a contextual reply (this may take a few seconds)
   - The generated reply will appear in the reply form

3. **Verify the Reply:**
   - Check that the reply is contextually relevant
   - It should reference the email subject and content
   - If the email is linked to a lead/person, the reply should include relevant CRM context

4. **Edit and Send:**
   - You can edit the generated reply before sending
   - Click **Send** to send the email with the AI-generated content

### API Endpoint (for direct testing)

```bash
POST /admin/mail/ai/generate-reply
Content-Type: application/json

{
    "email_id": 1,
    "tone": "professional",  // Optional: professional, casual, formal
    "length": "medium"        // Optional: short, medium, long
}
```

### Expected Response

```json
{
    "data": {
        "reply": "Generated reply text here...",
        "model": "model-name"
    },
    "message": "AI reply generated successfully."
}
```

---

## Feature 2: AI Email Summaries

### Testing Steps

1. **Create/Find Email Thread:**
   - You need an email thread with at least 3 emails
   - Navigate to **Mail** → Open an email that has replies

2. **View Summary:**
   - For threads with 3+ emails, summaries are auto-generated
   - The summary appears in a blue highlighted box above the email body
   - Look for **"✨ AI Summary"** section

3. **Manual Generation:**
   - If no summary appears, click the **"Generate Summary"** button
   - Wait for the summary to be generated

4. **Verify Summary:**
   - The summary should be 2-3 sentences
   - It should highlight the main topic and key points
   - Summary is cached, so refreshing won't regenerate it

### API Endpoint

```bash
GET /admin/mail/ai/{email_id}/summary
```

### Expected Response

```json
{
    "data": {
        "summary": "Summary text here...",
        "cached": false,
        "model": "model-name"
    },
    "message": "AI summary generated successfully."
}
```

---

## Feature 3: AI Insight Generation

### Testing Steps

1. **Access a Lead:**
   - Navigate to **Leads** → Select a lead
   - Open the lead detail page

2. **Generate Insights (API):**
   Currently, insights are generated via API. Here's how to test:

### API Endpoints

#### Generate Insights for a Lead

```bash
POST /admin/ai/insights/lead/{leadId}/generate
```

Example:
```bash
curl -X POST http://localhost:8000/admin/ai/insights/lead/1/generate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

#### Get Insights for a Lead

```bash
GET /admin/ai/insights/lead/{leadId}
```

Example:
```bash
curl -X GET http://localhost:8000/admin/ai/insights/lead/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Get Insights for a Person

```bash
GET /admin/ai/insights/person/{personId}
```

### Expected Response

```json
{
    "data": [
        {
            "id": 1,
            "type": "lead_scoring",
            "title": "High Priority Lead",
            "description": "This lead shows strong engagement...",
            "priority": 8,
            "metadata": {}
        },
        {
            "id": 2,
            "type": "relationship",
            "title": "Active Communication",
            "description": "Regular email interactions...",
            "priority": 7,
            "metadata": {}
        }
    ]
}
```

### Insight Types

- **lead_scoring**: Prioritization recommendations
- **relationship**: Interaction patterns and sentiment
- **opportunity**: Upsell/cross-sell suggestions

---

## Feature 4: Conversational AI Copilot

### Testing Steps

1. **Access Copilot API:**
   The copilot chat interface can be tested via API endpoints.

### API Endpoints

#### Send a Message

```bash
POST /admin/ai/copilot/message
Content-Type: application/json

{
    "message": "Show me leads from last week",
    "conversation_id": null  // Optional: to continue existing conversation
}
```

Example:
```bash
curl -X POST http://localhost:8000/admin/ai/copilot/message \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "What are my top leads this month?",
    "conversation_id": null
  }'
```

#### Get User Conversations

```bash
GET /admin/ai/copilot/conversations
```

#### Get Messages for a Conversation

```bash
GET /admin/ai/copilot/conversations/{conversationId}/messages
```

### Expected Response

```json
{
    "data": {
        "conversation_id": 1,
        "message": "AI response here..."
    }
}
```

### Conversation Flow

1. Send first message → Creates new conversation
2. Use `conversation_id` from response for follow-up messages
3. The AI maintains context across messages in the same conversation

---

## Testing Checklist

- [ ] OpenRouter API key configured
- [ ] AI Email Reply Generation tested
- [ ] AI Email Summaries tested (with email thread)
- [ ] AI Insights generated for a lead
- [ ] Copilot conversation started
- [ ] All API endpoints return expected responses

---

## Troubleshooting

### "Missing API Key" Error

- Verify API key is configured in Settings → Configuration → General → Magic AI
- Check that the API key is valid and has credits

### "AI service error" Messages

- Verify your OpenRouter account has sufficient credits
- Check the model selection matches available models
- Review Laravel logs: `storage/logs/laravel.log`

### No Summaries Appearing

- Ensure email thread has at least 3 messages
- Check that the email has a `lead_id` or `person_id` for context
- Try manually clicking "Generate Summary" button

### Routes Not Found

- Run: `php artisan route:clear`
- Run: `php artisan config:clear`
- Verify routes are registered: `php artisan route:list | grep ai`

---

## Database Verification

Verify migrations were run successfully:

```bash
docker compose exec app php artisan migrate:status
```

You should see:
- `2025_12_31_125544_add_ai_generated_to_emails_table`
- `2025_12_31_125932_add_summary_to_emails_table`
- `2025_12_31_130125_create_ai_insights_table`
- `2025_12_31_131617_create_copilot_conversations_table`
- `2025_12_31_131617_create_copilot_messages_table`

---

## Notes

- All AI features require an active internet connection
- API calls to OpenRouter may take 2-5 seconds
- Summaries are cached for 24 hours to reduce API costs
- Ensure you have sufficient OpenRouter credits for testing

