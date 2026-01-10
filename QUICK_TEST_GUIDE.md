# Quick Testing Guide for AI Features

## ✅ Currently Working Features

### Feature 1: AI Email Reply Generation (READY TO TEST)

**Step-by-Step Testing:**

1. **Login to Admin Panel**
   - Go to `http://localhost:8000/admin/login`
   - Login with your credentials:
     - **Email:** `admin1@example.com`
     - **Password:** `admin123`

2. **Navigate to Emails**
   - Click on **Mail** in the sidebar
   - Go to **Inbox** (or any email folder)
   - Click on an email to view it

3. **Generate AI Reply**
   - Look for the **"✨ AI Reply"** button below the email content (next to Reply, Reply All, Forward buttons)
   - Click the **"✨ AI Reply"** button
   - Wait 2-5 seconds for the AI to generate a reply
   - The reply will automatically populate in the reply form

4. **Review and Send**
   - Review the generated reply
   - Edit it if needed
   - Click **Send** to send the email

**What to Verify:**
- ✅ The AI reply should be contextually relevant to the email
- ✅ If the email is linked to a lead/person, the reply includes CRM context
- ✅ The reply is professional and ready to send

---

### Feature 2: AI Email Summaries (READY TO TEST)

**Step-by-Step Testing:**

1. **Find or Create Email Thread**
   - You need an email with at least 3 messages (original + 2 replies)
   - Navigate to **Mail** → Open an email that has replies in the thread

2. **View Summary**
   - For threads with 3+ emails, the summary section appears automatically
   - Look for a blue box above the email body with **"✨ AI Summary"**
   - The summary should appear after a few seconds

3. **Manual Generation (if needed)**
   - If no summary appears, look for the **"Generate Summary"** button
   - Click it to manually trigger summary generation

**What to Verify:**
- ✅ Summary is concise (2-3 sentences)
- ✅ Highlights main topic and key points
- ✅ Summary is cached (won't regenerate on refresh)

---

## 🔧 Prerequisites Check

### Verify API Configuration

1. **Check API Key is Set:**
   ```bash
   docker compose exec app php artisan magic-ai:test
   ```
   Should show: "Connection Successful!"

2. **If API Key is Missing:**
   - Go to **Settings** → **Configuration**
   - Navigate to **General** → **Magic AI** → **Settings**
   - Enter your OpenRouter API key
   - Select a model (e.g., `openai/gpt-4o-mini`)
   - Save

---

## 📋 Quick Test Checklist

- [ ] Logged into admin panel
- [ ] API key configured (run `php artisan magic-ai:test`)
- [ ] Opened an email in Mail → Inbox
- [ ] Clicked "✨ AI Reply" button
- [ ] Verified reply was generated and is relevant
- [ ] Found an email thread with 3+ messages
- [ ] Verified AI Summary appears (or manually generated)
- [ ] Verified summary is concise and relevant

---

## 🐛 Troubleshooting

### "Generating..." but no reply appears
- Check browser console for errors (F12)
- Verify API key has credits in OpenRouter dashboard
- Check Laravel logs: `docker compose exec app tail -f storage/logs/laravel.log`

### "Missing API Key" error
- Run: `docker compose exec app php artisan magic-ai:test`
- If it fails, configure API key in Settings → Configuration → General → Magic AI

### Summary not appearing
- Ensure email thread has 3+ messages
- Try clicking "Generate Summary" button manually
- Check that emails are linked (have `parent_id` relationships)

---

## 📝 Notes

- **Email Reply Generation** works immediately after implementation
- **Email Summaries** require email threads (parent-child relationships)
- Both features use the existing OpenRouter AI configuration
- API calls typically take 2-5 seconds
- Summaries are cached for 24 hours to reduce API costs

---

## 🚀 Next Steps (Future Features)

The following features require additional setup:
- **AI Insights** - Need UI components added to lead/person pages
- **Conversational Copilot** - Need chat UI widget added to layout

These features have the backend implemented but need frontend UI integration.

