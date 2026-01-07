# Screenshot Capture Guide for Landing Page Slider

## Overview
This guide will help you capture screenshots of each CRM page to display in the interactive features slider on the landing page.

## Screenshots Needed

### 1. Lead Management (`leads_dashboard.png`)
**Page**: http://localhost:8000/admin/leads
**What to capture**: 
- Full leads dashboard showing the lead list/kanban view
- Include the sidebar, header, and main content area
- Show some sample leads in different stages

**Recommended size**: 1920x1080 or 1600x900

---

### 2. Smart Automation (`automation_workflows.png`)
**Page**: http://localhost:8000/admin/settings/workflows
**What to capture**:
- Workflow automation page
- Show workflow builder or list of automated workflows
- Include any visual workflow diagrams if available

**Recommended size**: 1920x1080 or 1600x900

---

### 3. Visual Pipeline (`sales_pipeline.png`)
**Page**: http://localhost:8000/admin/leads (Kanban view)
**What to capture**:
- Kanban board view of the sales pipeline
- Show multiple stages with deals/leads
- Drag-and-drop interface visible

**Recommended size**: 1920x1080 or 1600x900

---

### 4. Analytics & Insights (`analytics_dashboard.png`)
**Page**: http://localhost:8000/admin/dashboard
**What to capture**:
- Main dashboard with charts and statistics
- Revenue graphs, conversion rates, performance metrics
- Show colorful charts and data visualizations

**Recommended size**: 1920x1080 or 1600x900

---

### 5. Team Collaboration (`collaboration_channels.png`)
**Page**: http://localhost:8000/admin/collaboration/channels
**What to capture**:
- Collaboration channels page
- Show chat interface or channel list
- Include message threads if available

**Recommended size**: 1920x1080 or 1600x900

---

### 6. AI Assistant (`ai_assistant.png`)
**Page**: http://localhost:8000/admin/leads (with AI features visible)
**What to capture**:
- Any page showing AI features
- AI lead scoring interface
- AI-generated content or suggestions
- Magic AI panel or assistant

**Recommended size**: 1920x1080 or 1600x900

---

## How to Capture Screenshots

### Method 1: Browser DevTools (Recommended)
1. Open the page in Chrome/Edge
2. Press `F12` to open DevTools
3. Press `Cmd+Shift+P` (Mac) or `Ctrl+Shift+P` (Windows)
4. Type "Capture full size screenshot"
5. Press Enter - screenshot will be saved to Downloads

### Method 2: Browser Extension
1. Install "Full Page Screen Capture" extension
2. Navigate to the page
3. Click the extension icon
4. Download the screenshot

### Method 3: macOS Screenshot Tool
1. Press `Cmd+Shift+4`
2. Press `Space` to capture window
3. Click on the browser window

### Method 4: Windows Snipping Tool
1. Open Snipping Tool
2. Select "Window Snip"
3. Click on the browser window

---

## Screenshot Guidelines

### Best Practices
✅ **Use consistent resolution** (1920x1080 recommended)
✅ **Capture at 100% zoom** (not zoomed in/out)
✅ **Include realistic data** (not empty pages)
✅ **Show the full interface** (sidebar + main content)
✅ **Use light mode** (better for landing page)
✅ **Hide sensitive data** if any

### What to Avoid
❌ Empty pages with no data
❌ Error messages or loading states
❌ Personal/sensitive information
❌ Inconsistent zoom levels
❌ Partial page captures

---

## File Naming Convention

Save screenshots with these exact names in `/public/` directory:

```
public/
├── leads_dashboard.png          (Lead Management)
├── automation_workflows.png     (Smart Automation)
├── sales_pipeline.png           (Visual Pipeline)
├── analytics_dashboard.png      (Analytics & Insights)
├── collaboration_channels.png   (Team Collaboration)
└── ai_assistant.png             (AI Assistant)
```

---

## After Capturing Screenshots

Once you have all 6 screenshots saved in the `/public/` directory, the landing page slider will automatically use them. The code has been updated to reference these new image files.

---

## Quick Checklist

- [ ] Navigate to http://localhost:8000/admin/leads
- [ ] Capture Lead Management screenshot → `leads_dashboard.png`
- [ ] Navigate to http://localhost:8000/admin/settings/workflows
- [ ] Capture Automation screenshot → `automation_workflows.png`
- [ ] Navigate to http://localhost:8000/admin/leads (Kanban view)
- [ ] Capture Pipeline screenshot → `sales_pipeline.png`
- [ ] Navigate to http://localhost:8000/admin/dashboard
- [ ] Capture Analytics screenshot → `analytics_dashboard.png`
- [ ] Navigate to http://localhost:8000/admin/collaboration/channels
- [ ] Capture Collaboration screenshot → `collaboration_channels.png`
- [ ] Capture AI features screenshot → `ai_assistant.png`
- [ ] Save all files to `/public/` directory
- [ ] Refresh landing page to see new screenshots

---

## Troubleshooting

**Q: Screenshots are too large (file size)**
A: Use an image optimizer like TinyPNG or ImageOptim to reduce file size

**Q: Screenshots look blurry**
A: Ensure you're capturing at 100% zoom and using high DPI/retina display

**Q: Can I use placeholder images temporarily?**
A: Yes, the current images will be used until you replace them

**Q: What if I don't have data in some sections?**
A: Seed the database with sample data first using `php artisan db:seed`
