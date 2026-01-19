# PWA (Progressive Web App) Setup Guide

## Overview

Your Prasso application now supports Progressive Web App (PWA) installation on a **per-site basis**. This means each site in your `sites` table can be installed as a separate, independent PWA app on users' devices.

### Key Features

- **Per-Site Installation**: Each site gets its own unique PWA with its own name, icon, and theme color
- **Offline Support**: Service worker enables offline functionality with intelligent caching
- **Dynamic Manifest**: Manifest is generated dynamically based on the current site's configuration
- **Cross-Site Isolation**: Each site maintains its own cache and app identity

---

## How It Works

### 1. Dynamic Manifest Generation

When a user visits your site, the browser requests `/manifest.json`. Instead of serving a static file, the `PwaController` generates a manifest dynamically based on the current site:

```
GET /manifest.json
```

**Response includes:**
- Site name and description
- Site-specific theme color (`site.main_color`)
- Site icons from the image folder
- App shortcuts (e.g., Dashboard)
- Display mode: `standalone` (fullscreen app experience)

### 2. Service Worker Registration

The service worker is registered automatically when the page loads. It handles:

- **Installation**: Caches essential resources (CSS, JS)
- **Activation**: Cleans up old caches
- **Fetch**: Serves cached content when offline, falls back to network when online

The service worker is also generated dynamically per site with a unique cache name:
```
pwa-cache-{site.id}-{site_name}-v1
```

This ensures each site has its own isolated cache.

### 3. Browser Installation

Users can install the PWA by:

**On Desktop (Chrome/Edge):**
1. Click the install icon in the address bar (or menu → "Install app")
2. Confirm the installation
3. App opens in a standalone window

**On Mobile (iOS/Android):**
1. Open in Safari (iOS) or Chrome (Android)
2. Tap Share → "Add to Home Screen" (iOS) or Menu → "Install app" (Android)
3. App appears as a native app on the home screen

---

## Configuration

### Site Configuration (Database)

Each site in the `sites` table controls its PWA appearance:

| Field | Purpose | Example |
|-------|---------|---------|
| `site_name` | App name | "Faith Lake City" |
| `description` | App description | "Community church app" |
| `main_color` | Theme color | "#1e40af" |
| `image_folder` | Icon location | "images/faith-lake/" |
| `logo_image` | App logo | "logo.png" |

### Required Icons

Place these icon files in your site's image folder (configured in `site.image_folder`):

- `android-chrome-192x192.png` - Small app icon
- `android-chrome-512x512.png` - Large app icon
- `apple-touch-icon.png` - iOS home screen icon
- `favicon-32x32.png` - Favicon
- `favicon-16x16.png` - Favicon

**Example path structure:**
```
public/images/faith-lake/
├── android-chrome-192x192.png
├── android-chrome-512x512.png
├── apple-touch-icon.png
├── favicon-32x32.png
└── favicon-16x16.png
```

---

## Implementation Details

### Files Modified

1. **`app/Http/Controllers/PwaController.php`** (NEW)
   - Generates dynamic manifest per site
   - Generates dynamic service worker per site

2. **`routes/web.php`**
   - Added PWA routes:
     - `GET /manifest.json` → `PwaController@manifest`
     - `GET /service-worker.js` → `PwaController@serviceWorker`

3. **`resources/views/layouts/app.blade.php`**
   - Added manifest link
   - Added PWA meta tags
   - Added service worker registration script

4. **`resources/views/layouts/guest.blade.php`**
   - Added manifest link
   - Added PWA meta tags
   - Added service worker registration script

### PWA Meta Tags

The following meta tags are automatically added to all pages:

```html
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="{{ $site->main_color }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ $site->site_name }}">
```

---

## Testing PWA Installation

### 1. Verify Manifest

Open your browser's DevTools and check:

```
GET https://yourdomain.com/manifest.json
```

Should return JSON with site-specific data.

### 2. Verify Service Worker

In DevTools → Application → Service Workers, you should see:
- Status: `activated and running`
- Scope: `/`
- Cache name: `pwa-cache-{site_id}-{site_name}-v1`

### 3. Test Offline Functionality

1. Open DevTools → Network
2. Check "Offline" checkbox
3. Refresh the page
4. Page should still load from cache

### 4. Install the App

**Desktop:**
- Look for install icon in address bar
- Or: Menu → "Install [Site Name]"

**Mobile:**
- iOS: Share → Add to Home Screen
- Android: Menu → Install app

---

## Cache Strategy

The service worker uses a **Network First, Cache Fallback** strategy:

1. **Online**: Fetch from network, cache the response, serve it
2. **Offline**: Serve from cache
3. **Cache Miss**: Return fallback (home page)

### Cached Resources

By default, these are cached on first visit:
- `/` (home page)
- `/css/app.css`
- `/js/app.js`

Additional resources are cached as users visit pages.

### Cache Invalidation

Cache is invalidated when:
- Service worker file changes (new version deployed)
- Site name changes (new cache name generated)
- Manual cache clear in DevTools

---

## Multi-Site Example

### Site 1: Faith Lake City

```
GET https://faithlakecity.com/manifest.json
```

Returns:
```json
{
  "name": "Faith Lake City",
  "short_name": "Faith Lake",
  "theme_color": "#1e40af",
  "icons": [
    {
      "src": "https://cdn.example.com/images/faith-lake/android-chrome-192x192.png",
      "sizes": "192x192"
    }
  ]
}
```

### Site 2: FAXT Development

```
GET https://faxt-dev.com/manifest.json
```

Returns:
```json
{
  "name": "FAXT Development",
  "short_name": "FAXT Dev",
  "theme_color": "#7c3aed",
  "icons": [
    {
      "src": "https://cdn.example.com/images/faxt-dev/android-chrome-192x192.png",
      "sizes": "192x192"
    }
  ]
}
```

Both apps can be installed independently on the same device.

---

## Hidden Login Access

The PWA includes a built-in hidden login mechanism that's accessible without cluttering the user interface. This is especially useful for PWA apps where the main navigation menu may not be visible.

### How It Works

The login button is hidden by default and can be revealed through:

**Mobile/Touchscreen Devices:**
- Triple-tap anywhere on the page

**Laptop/Trackpad:**
- Press the **`L`** key
- Press **`?`** (Shift + /)
- Press **`Escape`** to hide the button

### Features

- **Smart Styling**: Button automatically uses the site's primary color (`main_color` from database)
- **Hover Effects**: Darker shade of the site color on hover for better UX
- **Auto-Hide**: Button automatically disappears after 10 seconds
- **Responsive**: Works on all device types and screen sizes
- **Non-Intrusive**: No UI clutter when not needed
- **Universal**: Available on all master page templates

### Implementation

The login functionality is centralized in `/public/js/pwa-login.js`:

- Detects triple-tap gestures for mobile
- Listens for keyboard shortcuts (`L`, `?`, `Escape`)
- Dynamically reads site theme color from `<meta name="theme-color">` tag
- Creates and manages the login button element
- Handles color brightness adjustment for hover states

### Customization

To modify the login behavior, edit `/public/js/pwa-login.js`:

- **Change auto-hide duration**: Line 28 (currently 10000ms)
- **Change button position**: Line 104-108 (currently bottom-right)
- **Modify keyboard shortcuts**: Lines 70-82
- **Adjust tap detection time**: Line 114 (currently 500ms)

---

## Troubleshooting

### App Won't Install

**Check:**
1. Manifest is valid JSON: `GET /manifest.json`
2. Icons exist at configured paths
3. HTTPS is enabled (PWA requires HTTPS in production)
4. Service worker registers without errors (DevTools → Console)

### Offline Pages Not Loading

**Check:**
1. Service worker is active: DevTools → Application → Service Workers
2. Cache exists: DevTools → Application → Cache Storage
3. No errors in DevTools → Console

### Wrong App Name/Icon

**Check:**
1. Site configuration in database:
   ```sql
   SELECT id, site_name, main_color, image_folder FROM sites;
   ```
2. Icons exist in the configured folder
3. Clear browser cache and reinstall app

### Cache Not Updating

**Solution:**
1. Increment cache version in `PwaController::serviceWorker()`
2. Change the cache name format (e.g., add `-v2`)
3. Users will automatically get new cache on next visit

---

## Best Practices

1. **Icon Sizes**: Always provide both 192x192 and 512x512 icons
2. **Theme Colors**: Use the site's brand color for `main_color`
3. **Descriptions**: Keep descriptions under 150 characters
4. **Testing**: Test on actual devices (Chrome DevTools mobile emulation isn't perfect)
5. **HTTPS**: Always use HTTPS in production
6. **Updates**: Monitor service worker updates and cache invalidation

---

## API Reference

### PwaController Methods

#### `manifest(Request $request): JsonResponse`

Generates a dynamic web manifest for the current site.

**Returns:**
```json
{
  "name": "Site Name",
  "short_name": "Short Name",
  "description": "Description",
  "start_url": "/",
  "scope": "/",
  "display": "standalone",
  "theme_color": "#000000",
  "background_color": "#ffffff",
  "icons": [...],
  "screenshots": [...],
  "shortcuts": [...]
}
```

#### `serviceWorker(Request $request): Response`

Generates a dynamic service worker JavaScript file.

**Returns:** JavaScript code with site-specific cache name

---

## Future Enhancements

- [ ] Add push notifications support
- [ ] Add background sync for offline actions
- [ ] Add app update notifications
- [ ] Add analytics for PWA installations
- [ ] Add custom splash screens per site
- [ ] Add app shortcuts for common actions

---

## Support

For issues or questions about PWA setup, check:
1. Browser DevTools → Application tab
2. Service Worker registration logs in Console
3. Cache Storage for debugging
4. Network tab for manifest/service worker requests
