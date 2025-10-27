# PWA Reverse Proxy Implementation - Summary

## Overview

Prasso now acts as a reverse proxy for Progressive Web Apps, eliminating the need for Apache vhost configuration. Each app runs independently on a Node.js server, and Prasso forwards requests transparently.

## What Changed

### Database
- **New Column**: `pwa_server_url` in `apps` table
- **Migration**: `2025_10_27_000001_add_pwa_server_url_to_apps_table.php`

### Models
- **Apps.php**: Added `pwa_server_url` to `$fillable`

### Controllers
- **SitePageController.php**:
  - `index()`: Proxies homepage requests to Node.js server
  - `viewSitePage()`: Proxies page requests to Node.js server
  - `proxyRequestToServer()`: New helper method for proxying

### Forms
- **AppInfoForm.php**: Added validation for `pwa_server_url`
- **app-info-form.blade.php**: Added PWA Server URL input field

## How to Use

### 1. Start Node.js App

```bash
cd /path/to/your/nextjs/app
npm start
# Runs on http://localhost:3001
```

### 2. Configure in Prasso Admin

Navigate to App Editor and fill in:

- **PWA App URL**: `https://myapp.example.com` (public URL)
- **PWA Server URL**: `http://localhost:3001` (internal server)

DNS setup is automatic for faxt.com domains.

### 3. Access the App

Users access `https://myapp.example.com` and Prasso proxies to `http://localhost:3001`

## Multiple Apps

Each app runs on a different port:

```
App 1: https://app1.example.com → http://localhost:3001
App 2: https://app2.example.com → http://localhost:3002
App 3: https://app3.example.com → http://localhost:3003
```

## Key Features

✅ **No Apache Configuration** - Single admin form setup  
✅ **Multiple Apps** - Each on different port  
✅ **Full Server Functionality** - Node.js handles everything  
✅ **Automatic DNS** - For faxt.com domains  
✅ **Transparent Proxy** - Clients see public URL  
✅ **Error Handling** - Falls back to Prasso pages if proxy fails  
✅ **Request Forwarding** - All methods, headers, body, query strings  

## Request Flow

```
User Request: https://myapp.example.com/about
    ↓
DNS resolves to Prasso server
    ↓
Controller::getClientFromHost()
  - Checks if URL matches PWA app URL
  - Returns associated Site
    ↓
SitePageController::viewSitePage()
  - Checks if site has pwa_server_url
  - Calls proxyRequestToServer()
    ↓
proxyRequestToServer()
  - Forwards to http://localhost:3001/about
  - Preserves method, headers, body, query string
    ↓
Node.js server processes request
    ↓
Response returned through Prasso to client
```

## Files Created

1. `database/migrations/2025_10_27_000001_add_pwa_server_url_to_apps_table.php`
2. `docs/pwa-reverse-proxy.md` - Comprehensive documentation

## Files Modified

1. `app/Models/Apps.php` - Added `pwa_server_url` to fillable
2. `app/Http/Controllers/SitePageController.php` - Proxy logic
3. `app/Livewire/Apps/AppInfoForm.php` - Validation rule
4. `resources/views/livewire/apps/app-info-form.blade.php` - Form field

## Deployment Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Verify `pwa_server_url` column exists in `apps` table
- [ ] Start Node.js app on configured port
- [ ] Configure PWA App URL and PWA Server URL in admin form
- [ ] Verify DNS setup (automatic for faxt.com)
- [ ] Test accessing PWA through public URL
- [ ] Check logs for proxy messages
- [ ] Verify Node.js app receives requests

## Logging

All proxy operations are logged:

```
"Proxying PWA request to http://localhost:3001 for app 1 on site 5"
"Proxying PWA page request for /about to http://localhost:3001 for app 1 on site 5"
"Failed to proxy PWA request for app 1: Connection refused"
```

## Troubleshooting

**Proxy fails to connect**
- Verify Node.js server is running on configured port
- Check `pwa_server_url` is correct
- Verify firewall allows connection

**Requests timeout**
- Check Node.js server performance
- Verify network connectivity
- Check Node.js server logs

**Headers not forwarded**
- Some headers are intentionally skipped (Host, Connection, Content-Length)
- Check Node.js app receives headers via `req.headers`

## Next Steps

1. Run migration
2. Start Node.js apps on configured ports
3. Configure apps in Prasso admin
4. Test accessing apps through public URLs
5. Monitor logs for any issues

## Documentation

For detailed information, see:
- `docs/pwa-reverse-proxy.md` - Complete implementation guide
- `docs/pwa-app-sites.md` - User guide (updated)
- `docs/pwa-testing-checklist.md` - Testing procedures (update pending)
