# PWA Quick Reference

## What Changed?

Your Prasso application now supports **per-site PWA installation**. Each site can be installed as a separate app on users' devices.

## Key Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/PwaController.php` | Generates dynamic manifest and service worker |
| `routes/web.php` | PWA routes (manifest.json, service-worker.js) |
| `resources/views/layouts/app.blade.php` | PWA meta tags and service worker registration |
| `resources/views/layouts/guest.blade.php` | PWA meta tags and service worker registration |

## How It Works

1. **User visits site** → Browser requests `/manifest.json`
2. **PwaController generates manifest** → Uses site config (name, color, icons)
3. **Browser registers service worker** → Enables offline support
4. **User installs app** → App appears on home screen with site's branding

## Per-Site Configuration

Each site needs:

```sql
UPDATE sites SET
  site_name = 'App Name',
  description = 'App description',
  main_color = '#HexColor',
  image_folder = 'images/site-name/'
WHERE id = SITE_ID;
```

## Required Icons

Place in `public/{image_folder}`:

- `android-chrome-192x192.png`
- `android-chrome-512x512.png`
- `apple-touch-icon.png`
- `favicon-32x32.png`
- `favicon-16x16.png`

## Testing

```bash
# Check manifest
curl https://yourdomain.com/manifest.json | jq .

# Check service worker
curl https://yourdomain.com/service-worker.js

# DevTools: Application → Service Workers (should be active)
# DevTools: Application → Cache Storage (should have pwa-cache-*)
```

## Installation

**Desktop:** Install icon in address bar
**iOS:** Share → Add to Home Screen
**Android:** Menu → Install app

## Hidden Login Access

The PWA includes a hidden login button accessible without cluttering the UI:

### How to Access Login

**On Mobile/Touchscreen:**
- Triple-tap anywhere on the page to reveal the login button

**On Laptop/Trackpad:**
- Press **`L`** key to show login button
- Press **`?`** (Shift + /) to show login button
- Press **`Escape`** to hide login button

### Features

- Login button automatically appears in bottom-right corner
- Uses the site's primary color (from `main_color` in database)
- Auto-hides after 10 seconds
- Hover effect with darker shade of site color
- Works on all master page templates
- No UI clutter when not needed

## Multi-Site Example

Both apps can be installed independently:

```
Device:
├── Faith Lake City (blue app)
├── FAXT Development (purple app)
└── Other apps...
```

Each has:
- Own name and icon
- Own cache storage
- Independent offline support

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Manifest not loading | Check site_name, description, image_folder in DB |
| Icons not showing | Verify icons exist at `public/{image_folder}` |
| Service worker not active | Check HTTPS is enabled, clear cache |
| App won't install | Ensure HTTPS, valid manifest, all icons present |

## Cache Strategy

- **Online:** Fetch from network, cache response
- **Offline:** Serve from cache
- **Cache miss:** Return home page

Cache name: `pwa-cache-{site_id}-{site_name}-v1`

## Documentation

- **Full Setup:** See `PWA_SETUP.md`
- **Setup Checklist:** See `PWA_CHECKLIST.md`
- **This File:** Quick reference

## Common Tasks

### Update Site Branding

```sql
UPDATE sites SET
  main_color = '#NewColor',
  site_name = 'New Name'
WHERE id = SITE_ID;
```

Users will see updated app on next visit.

### Add New Site as PWA

1. Create site in `sites` table
2. Set `site_name`, `description`, `main_color`, `image_folder`
3. Upload icons to `public/{image_folder}`
4. Done! Site is now installable as PWA

### Clear Cache

Users can clear app cache in:
- **DevTools:** Application → Cache Storage → Delete
- **Device:** App settings → Storage → Clear cache
- **Automatic:** Service worker clears old caches on update

### Update Service Worker

Edit `app/Http/Controllers/PwaController.php` → `serviceWorker()` method

Change cache name to force update:
```php
$cacheName = "pwa-cache-{$site->id}-{$siteName}-v2"; // v1 → v2
```

## API Endpoints

| Endpoint | Returns |
|----------|---------|
| `GET /manifest.json` | Web app manifest (JSON) |
| `GET /service-worker.js` | Service worker script (JS) |

Both are dynamic per site.

## Browser Support

| Browser | Desktop | Mobile |
|---------|---------|--------|
| Chrome | ✅ | ✅ |
| Edge | ✅ | ✅ |
| Firefox | ⚠️ | ✅ |
| Safari | ⚠️ | ✅ |

✅ = Full support
⚠️ = Partial support

## Performance Tips

1. Keep icons under 100KB
2. Use PNG format with transparency
3. Cache only essential resources
4. Monitor service worker errors
5. Update cache version when deploying

## Security

- PWA requires HTTPS in production
- Service worker scope is `/` (entire site)
- Each site has isolated cache
- No cross-site data sharing

## Next Steps

1. See `PWA_CHECKLIST.md` for setup steps
2. Update each site's configuration
3. Upload icons for each site
4. Test on real devices
5. Monitor service worker performance
