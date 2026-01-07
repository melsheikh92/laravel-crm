#!/bin/bash

# Screenshot Capture Helper Script
# This script helps you capture all CRM screenshots for the landing page

echo "🎯 CRM Screenshot Capture Helper"
echo "=================================="
echo ""

# Base URL
BASE_URL="http://localhost:8000"

# Check if server is running
echo "📡 Checking if server is running..."
if curl -s "$BASE_URL" > /dev/null; then
    echo "✅ Server is running at $BASE_URL"
else
    echo "❌ Server is not running!"
    echo "Please start the server with: php artisan serve"
    exit 1
fi

echo ""
echo "📸 Screenshots Needed:"
echo ""
echo "1. Lead Management"
echo "   URL: $BASE_URL/admin/leads"
echo "   File: leads_dashboard.png"
echo ""
echo "2. Smart Automation"
echo "   URL: $BASE_URL/admin/settings/workflows"
echo "   File: automation_workflows.png"
echo ""
echo "3. Visual Pipeline"
echo "   URL: $BASE_URL/admin/leads (switch to Kanban view)"
echo "   File: sales_pipeline.png"
echo ""
echo "4. Analytics & Insights"
echo "   URL: $BASE_URL/admin/dashboard"
echo "   File: analytics_dashboard.png"
echo ""
echo "5. Team Collaboration"
echo "   URL: $BASE_URL/admin/collaboration/channels"
echo "   File: collaboration_channels.png"
echo ""
echo "6. AI Assistant"
echo "   URL: $BASE_URL/admin/leads (show AI features)"
echo "   File: ai_assistant.png"
echo ""
echo "=================================="
echo ""
echo "📋 Instructions:"
echo "1. Open each URL in your browser"
echo "2. Press F12 to open DevTools"
echo "3. Press Cmd+Shift+P (Mac) or Ctrl+Shift+P (Windows)"
echo "4. Type 'Capture full size screenshot' and press Enter"
echo "5. Rename the downloaded file to match the filename above"
echo "6. Move it to the public/ directory"
echo ""
echo "💡 Tip: Make sure you're logged in and have sample data!"
echo ""

# Ask if user wants to open URLs
read -p "Would you like to open all URLs in your browser? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]
then
    echo "🌐 Opening URLs in browser..."
    open "$BASE_URL/admin/leads"
    sleep 2
    open "$BASE_URL/admin/settings/workflows"
    sleep 2
    open "$BASE_URL/admin/dashboard"
    sleep 2
    open "$BASE_URL/admin/collaboration/channels"
    echo "✅ URLs opened! Start capturing screenshots."
else
    echo "👍 No problem! Open the URLs manually when ready."
fi

echo ""
echo "📁 Save screenshots to:"
echo "$(pwd)/public/"
echo ""
echo "✨ Happy screenshot capturing!"
