# Logo Replacement Guide
## ProvenSuccess CRM by hamzah LLC

---

## 📁 Logo File Locations

### Admin Panel Logos
**Directory**: `packages/Webkul/Admin/src/Resources/assets/images/`

Files to replace:
- `logo.svg` - Main logo for light theme
- `dark-logo.svg` - Main logo for dark theme  
- `mobile-light-logo.svg` - Mobile logo for light theme
- `mobile-dark-logo.svg` - Mobile logo for dark theme
- `favicon.ico` - Browser favicon (16x16, 32x32, 48x48 sizes)

### Installer Logos
**Directory**: `packages/Webkul/Installer/src/Resources/assets/images/`

Files to replace:
- `krayin-logo.svg` - Installer welcome page logo

---

## 🎨 Logo Specifications

### Recommended Logo Formats

1. **SVG** (Preferred)
   - Scalable vector graphics
   - Works at any size
   - Smaller file size
   - Can be styled with CSS

2. **PNG** (Fallback)
   - High resolution (2x for retina displays)
   - Transparent background
   - Minimum 400px width for main logos

### Logo Dimensions

#### Admin Panel Logos
- **Main Logo**: ~200px width (height auto, maintain aspect ratio)
- **Mobile Logo**: ~150px width (height auto)
- **Favicon**: 16x16, 32x32, 48x48 pixels (multi-size .ico file)

#### Installer Logo
- **Welcome Logo**: ~300px width (height auto)

---

## 📥 How to Get Logos from hamzahllc.com

### Method 1: Direct Download
1. Visit https://hamzahllc.com
2. Right-click on logo → "Save image as..."
3. Save as SVG if available, otherwise PNG

### Method 2: Browser Developer Tools
1. Right-click logo → Inspect Element
2. Find `<img>` or `<svg>` tag
3. Right-click → "Copy image" or view source
4. Save the SVG code or image file

### Method 3: Contact hamzah LLC
- Email: support@hamzahllc.com
- Request logo files in SVG format
- Request logo variations (light/dark versions)

---

## 🔄 Logo Replacement Steps

### Step 1: Prepare Logo Files

1. **Get logos from hamzahllc.com**
   - Main logo (light background)
   - Dark version logo (for dark theme)
   - Mobile versions (if different)
   - Favicon

2. **Optimize logos**
   - Ensure SVG files are optimized
   - Remove unnecessary metadata
   - Ensure proper viewBox attributes

3. **Name files correctly**
   - Keep exact filenames: `logo.svg`, `dark-logo.svg`, etc.
   - Don't rename files (code references these names)

### Step 2: Replace Admin Panel Logos

```bash
# Navigate to admin images directory
cd packages/Webkul/Admin/src/Resources/assets/images/

# Backup old logos (optional)
mkdir -p ../../../../backup-logos
cp *.svg ../../../../backup-logos/
cp favicon.ico ../../../../backup-logos/

# Replace with new logos
# Copy your new logo files here:
# - logo.svg
# - dark-logo.svg
# - mobile-light-logo.svg
# - mobile-dark-logo.svg
# - favicon.ico
```

### Step 3: Replace Installer Logo

```bash
# Navigate to installer images directory
cd packages/Webkul/Installer/src/Resources/assets/images/

# Backup old logo (optional)
cp krayin-logo.svg ../../../../backup-logos/

# Replace with new logo
# Copy your new logo file as: krayin-logo.svg
# OR rename your file and update references in installer views
```

### Step 4: Update Installer Logo Reference (if renamed)

If you rename `krayin-logo.svg` to `provensuccess-logo.svg`, update:

**File**: `packages/Webkul/Installer/src/Resources/views/installer/index.blade.php`
**Line**: ~132

Change:
```blade
src="{{ vite()->asset('images/krayin-logo.svg', 'installer') }}"
```

To:
```blade
src="{{ vite()->asset('images/provensuccess-logo.svg', 'installer') }}"
```

---

## 🎨 Logo Styling Considerations

### Light Theme Logo
- Should work on white/light backgrounds
- May need dark text/icon colors
- Ensure good contrast

### Dark Theme Logo
- Should work on dark backgrounds
- May need light/white text/icon colors
- Ensure good contrast

### Mobile Logos
- Simplified version if main logo is complex
- Same colors as desktop versions
- Optimized for smaller screens

---

## ✅ Logo Testing Checklist

After replacing logos, test:

- [ ] Admin login page displays logo correctly
- [ ] Admin dashboard shows logo in header
- [ ] Dark mode displays dark logo version
- [ ] Mobile view shows mobile logo version
- [ ] Installer welcome page shows logo
- [ ] Favicon appears in browser tab
- [ ] Logos scale properly at different sizes
- [ ] Logos maintain aspect ratio
- [ ] No broken image icons
- [ ] Logos load quickly

---

## 🔧 After Logo Replacement

### 1. Rebuild Frontend Assets

```bash
# Admin panel assets
cd packages/Webkul/Admin
npm run build
# or
npm run dev

# Installer assets
cd ../Installer
npm run build
# or
npm run dev
```

### 2. Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 3. Clear Browser Cache

- Hard refresh: `Ctrl+Shift+R` (Windows/Linux) or `Cmd+Shift+R` (Mac)
- Or clear browser cache completely

---

## 🎯 Logo Best Practices

1. **Consistency**: Use same logo style across all variations
2. **Quality**: Use high-resolution source files
3. **Optimization**: Optimize SVG files (remove unnecessary code)
4. **Accessibility**: Ensure logos are readable at all sizes
5. **Branding**: Match hamzah LLC's brand guidelines

---

## 📞 Need Help?

If you need assistance:
- Check hamzah LLC's brand guidelines (if available)
- Contact support@hamzahllc.com for official logo files
- Ensure you have permission to use the logos

---

**Last Updated**: [Current Date]
**Status**: Ready for logo replacement

