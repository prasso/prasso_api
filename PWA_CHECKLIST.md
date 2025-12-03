# PWA Implementation Checklist

## ✅ Code Implementation (COMPLETE)

- [x] Created `PwaController.php` with manifest and service worker generation
- [x] Added PWA routes to `routes/web.php`
- [x] Updated `resources/views/layouts/app.blade.php` with PWA meta tags and service worker registration
- [x] Updated `resources/views/layouts/guest.blade.php` with PWA meta tags and service worker registration

## 📋 Per-Site Setup Required

For each site you want to enable as a PWA, complete these steps:

### 1. Database Configuration

Ensure each site has these fields populated in the `sites` table:

```sql
UPDATE sites SET
  site_name = 'Your Site Name',
  description = 'Your site description (max 150 chars)',
  main_color = '#HexColor',
  image_folder = 'images/your-site/'
WHERE id = YOUR_SITE_ID;
```

**Required fields:**
- `site_name` - Will be the app name
- `description` - Will be the app description
- `main_color` - Theme color (hex format)
- `image_folder` - Path to icon folder (must end with `/`)

### 2. Icon Files

Create or upload these icon files to `public/{image_folder}`:

```
public/images/your-site/
├── android-chrome-192x192.png    (192x192 pixels)
├── android-chrome-512x512.png    (512x512 pixels)
├── apple-touch-icon.png          (180x180 pixels)
├── favicon-32x32.png             (32x32 pixels)
└── favicon-16x16.png             (16x16 pixels)
```

**Icon Requirements:**
- Format: PNG with transparency
- 192x192: Used for Android home screen
- 512x512: Used for splash screens and app stores
- apple-touch-icon.png: iOS home screen icon
- Favicons: Browser tabs

### 3. Verify Configuration

Test that your PWA is working:

```bash
# 1. Check manifest is generated correctly
curl https://yourdomain.com/manifest.json | jq .

# 2. Check service worker is served
curl https://yourdomain.com/service-worker.js

# 3. Verify icons are accessible
curl -I https://yourdomain.com/images/your-site/android-chrome-192x192.png
```

### 4. Test Installation

**Desktop (Chrome/Edge):**
1. Open https://yourdomain.com
2. Look for install icon in address bar
3. Click and confirm installation
4. App should open in standalone window

**Mobile (iOS):**
1. Open in Safari
2. Tap Share button
3. Tap "Add to Home Screen"
4. Confirm

**Mobile (Android):**
1. Open in Chrome
2. Tap Menu (⋮)
3. Tap "Install app"
4. Confirm

### 5. Verify in DevTools

**Chrome/Edge DevTools:**
1. Open DevTools (F12)
2. Go to Application tab
3. Check Service Workers section - should show active service worker
4. Check Cache Storage - should show `pwa-cache-{site_id}-{site_name}-v1`
5. Check Manifest - should show your site's manifest

---

## 🔧 Configuration Examples

### Example 1: Faith Lake City Church

**Database:**
```sql
UPDATE sites SET
  site_name = 'Faith Lake City',
  description = 'Community church in Faith Lake City',
  main_color = '#1e40af',
  image_folder = 'images/faith-lake/'
WHERE id = 2;
```

**Icons location:** `public/images/faith-lake/`

**Result:** 
- App name: "Faith Lake City"
- Theme color: Blue (#1e40af)
- Installable as separate app

### Example 2: FAXT Development

**Database:**
```sql
UPDATE sites SET
  site_name = 'FAXT Development',
  description = 'FAXT development and testing platform',
  main_color = '#7c3aed',
  image_folder = 'images/faxt-dev/'
WHERE id = 1;
```

**Icons location:** `public/images/faxt-dev/`

**Result:**
- App name: "FAXT Development"
- Theme color: Purple (#7c3aed)
- Installable as separate app

---

## 🧪 Testing Checklist

For each site, verify:

- [ ] Manifest is valid JSON at `/manifest.json`
- [ ] Service worker is active in DevTools
- [ ] All icon files exist and are accessible
- [ ] App can be installed on desktop
- [ ] App can be installed on mobile (iOS and Android)
- [ ] Offline mode works (DevTools → Network → Offline)
- [ ] App name is correct
- [ ] App icon is correct
- [ ] Theme color is correct
- [ ] Each site has its own separate app cache

---

## 🚀 Deployment

### Before Going Live

1. [ ] All sites have icons in place
2. [ ] All sites have correct `site_name`, `description`, `main_color`
3. [ ] HTTPS is enabled (required for PWA)
4. [ ] Service worker is caching correctly
5. [ ] Tested on real devices

### After Deployment

1. [ ] Monitor service worker errors in logs
2. [ ] Check cache hit rates
3. [ ] Monitor PWA installation metrics (if available)
4. [ ] Update cache version if needed

---

## 📱 Multi-Site Installation Example

Users can now install multiple sites as separate apps:

```
Device Home Screen:
├── Faith Lake City (blue icon)
├── FAXT Development (purple icon)
└── Other Apps...
```

Each app:
- Has its own name and icon
- Has its own cache storage
- Works independently
- Can be uninstalled separately

---

## 🐛 Troubleshooting

### Manifest not loading
- Check site has `site_name` and `description`
- Verify image_folder ends with `/`
- Check HTTPS is enabled

### Icons not showing
- Verify icons exist at `public/{image_folder}`
- Check icon filenames match exactly
- Verify image_folder path in database

### Service worker not registering
- Check browser console for errors
- Verify `/service-worker.js` is accessible
- Check HTTPS is enabled

### App won't install
- Ensure HTTPS is enabled
- Check manifest is valid JSON
- Verify all required icons exist
- Try clearing browser cache

---

## 📚 Additional Resources

- [PWA_SETUP.md](./PWA_SETUP.md) - Detailed technical documentation
- [MDN PWA Documentation](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [Web.dev PWA Guide](https://web.dev/progressive-web-apps/)

---

## 💡 Next Steps

1. Update each site's configuration in the database
2. Upload icon files for each site
3. Test installation on desktop and mobile
4. Monitor service worker performance
5. Gather user feedback on PWA experience
