# Quick Start Guide - Rebranding Implementation
## ProvenSuccess CRM by hamzah LLC

---

## 🚀 What's Been Done

✅ **Phase 1: Code Rebranding - COMPLETE**
- All code references updated from Krayin to ProvenSuccess
- All URLs updated to hamzahllc.com
- All language files updated
- Package namespaces updated

---

## 📋 What You Need to Do Next

### Step 1: Get Brand Assets (5 minutes)

1. **Visit https://hamzahllc.com**
2. **Extract Primary Brand Color:**
   - Right-click on colored elements → Inspect
   - Find the hex color code (e.g., `#1A2B3C`)
   - Write it down

3. **Download Logos:**
   - Right-click logo → Save image as...
   - Get SVG if available, otherwise PNG
   - You'll need:
     - Main logo (light background)
     - Dark version (for dark theme)
     - Mobile versions (if different)
     - Favicon

### Step 2: Update Brand Colors (2 minutes)

Open these 3 files and replace `#0E90D9` with your brand color:

1. **`packages/Webkul/Admin/src/Config/core_config.php`** (line 179)
   ```php
   'default' => '#YOUR_COLOR_HERE',
   ```

2. **`packages/Webkul/Admin/src/Resources/views/components/layouts/anonymous.blade.php`** (line 68)
   ```php
   $brandColor = ... ?? '#YOUR_COLOR_HERE';
   ```

3. **`packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php`** (line 78)
   ```php
   $brandColor = ... ?? '#YOUR_COLOR_HERE';
   ```

4. **`packages/Webkul/Installer/src/Resources/views/installer/index.blade.php`** (line ~40)
   ```css
   --brand-color: #YOUR_COLOR_HERE;
   ```

### Step 3: Replace Logos (5 minutes)

1. **Admin Panel Logos:**
   ```bash
   cd packages/Webkul/Admin/src/Resources/assets/images/
   ```
   Replace these files:
   - `logo.svg`
   - `dark-logo.svg`
   - `mobile-light-logo.svg`
   - `mobile-dark-logo.svg`
   - `favicon.ico`

2. **Installer Logo:**
   ```bash
   cd packages/Webkul/Installer/src/Resources/assets/images/
   ```
   Replace: `krayin-logo.svg`

### Step 4: Update Environment (1 minute)

Edit your `.env` file:
```env
APP_NAME="ProvenSuccess"
APP_URL="https://your-domain.com"
```

### Step 5: Rebuild & Clear Cache (2 minutes)

```bash
# Rebuild frontend assets
cd packages/Webkul/Admin
npm run build

cd ../Installer
npm run build

# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 6: Test (5 minutes)

1. ✅ Login page shows ProvenSuccess branding
2. ✅ Dashboard displays correct logo
3. ✅ Brand colors appear correctly
4. ✅ Dark mode works
5. ✅ Installer shows ProvenSuccess branding

---

## 📚 Detailed Guides

- **`BRAND_COLORS_GUIDE.md`** - Complete guide for updating colors
- **`LOGO_REPLACEMENT_GUIDE.md`** - Complete guide for replacing logos
- **`REBRANDING_PLAN.md`** - Full rebranding plan
- **`IMPLEMENTATION_SUMMARY.md`** - What's been done

---

## ⚡ Quick Reference

### Files to Update Colors:
- `packages/Webkul/Admin/src/Config/core_config.php` (line 179)
- `packages/Webkul/Admin/src/Resources/views/components/layouts/anonymous.blade.php` (line 68)
- `packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php` (line 78)
- `packages/Webkul/Installer/src/Resources/views/installer/index.blade.php` (line ~40)

### Logo Locations:
- Admin: `packages/Webkul/Admin/src/Resources/assets/images/`
- Installer: `packages/Webkul/Installer/src/Resources/assets/images/`

---

## 🎯 Total Time Estimate

- Brand extraction: 5 minutes
- Color updates: 2 minutes
- Logo replacement: 5 minutes
- Environment update: 1 minute
- Rebuild & cache: 2 minutes
- Testing: 5 minutes

**Total: ~20 minutes**

---

## ❓ Need Help?

Check the detailed guides:
- `BRAND_COLORS_GUIDE.md` for color updates
- `LOGO_REPLACEMENT_GUIDE.md` for logo replacement

---

**Status**: Ready for final implementation steps!

