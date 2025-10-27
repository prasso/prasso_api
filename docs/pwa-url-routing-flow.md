# PWA URL Routing Flow

## Overview

This document describes the complete URL routing flow for PWA site serving in the prasso_api. The system prioritizes defined site URLs first, then falls back to PWA app URLs.

## Request Flow

### Step 1: URL Arrives at Controller

When a request arrives at the application:

```
Request: https://myapp.example.com/about
         ↓
getClientFromHost() is called
```

### Step 2: Check for Defined Site (Priority 1)

The `getClientFromHost()` method first checks if the URL matches a defined site:

```php
$site = Site::getClient($host);
if ($site != null && isset($site)) {
    Log::info("Site found for host: {$host}");
    return $site;
}
```

**What happens:**
- `Site::getClient()` searches the `sites` table for a matching `host` field
- The `host` field can contain comma-separated values for multiple domains
- If a match is found, the Site is returned immediately
- Example: If `sites.host` = `"mysite.com, myapp.example.com"`, a request to `myapp.example.com` will match

### Step 3: Check for PWA App URL (Priority 2)

If no site is found, the system checks if the URL matches any PWA app URL:

```php
$scheme = request()->getScheme();
$fullUrl = $scheme . '://' . $host;

$app = \App\Models\Apps::where('pwa_app_url', 'like', $fullUrl . '%')
    ->orWhere('pwa_app_url', $fullUrl)
    ->first();

if ($app != null && $app->site_id != null) {
    $site = Site::find($app->site_id);
    if ($site != null) {
        Log::info("PWA app found for host: {$host}, using associated site {$site->id}");
        return $site;
    }
}
```

**What happens:**
- Constructs the full URL from scheme and host (e.g., `https://myapp.example.com`)
- Searches the `apps` table for a matching `pwa_app_url`
- Uses `LIKE` matching to support URLs with paths (e.g., `https://myapp.example.com/app`)
- If found, retrieves the associated Site from the app's `site_id`
- Returns the Site object

### Step 4: No Match - Abort 404

If neither a site nor a PWA app URL matches:

```php
Log::info("No site or PWA app found for host: {$host}");
abort(404);
```

## Complete Request Lifecycle

### Example 1: Request to Defined Site

```
Request: https://mysite.com/about
         ↓
getClientFromHost()
  ├─ Check sites table for "mysite.com" → FOUND
  └─ Return Site object
         ↓
SitePageController::viewSitePage('about')
  ├─ Check if site has PWA app with pwa_app_url → NO
  ├─ Check if site has GitHub repo → NO
  ├─ Check Prasso SitePages table → FOUND
  └─ Serve Prasso page with masterpage
```

### Example 2: Request to PWA App URL

```
Request: https://myapp.example.com/
         ↓
getClientFromHost()
  ├─ Check sites table for "myapp.example.com" → NOT FOUND
  ├─ Check apps table for pwa_app_url matching "https://myapp.example.com" → FOUND
  ├─ Get associated Site from app.site_id
  └─ Return Site object
         ↓
SitePageController::index()
  ├─ Check if site has PWA app with pwa_app_url AND pwa_server_url → YES
  ├─ Call proxyRequestToServer() to forward to http://localhost:3001/
  └─ Return response from Node.js server (no masterpage)
```

### Example 3: Request to PWA App Subpage

```
Request: https://myapp.example.com/about
         ↓
getClientFromHost()
  ├─ Check sites table for "myapp.example.com" → NOT FOUND
  ├─ Check apps table for pwa_app_url → FOUND
  └─ Return associated Site object
         ↓
SitePageController::viewSitePage('about')
  ├─ Check if site has PWA app with pwa_app_url AND pwa_server_url → YES
  ├─ Call proxyRequestToServer() to forward to http://localhost:3001/about
  ├─ Node.js server processes request
  └─ Return response from Node.js server
```

## Masterpage Handling

The `getMasterForSite()` method determines whether to apply Prasso's masterpage:

```php
// Skip masterpage for GitHub hosted sites
if ($site != null && !empty($site->deployment_path) && !empty($site->github_repository)) {
    return null;
}

// Skip masterpage for PWA hosted sites (requires both pwa_app_url and pwa_server_url)
if ($site != null && $site->app && !empty($site->app->pwa_app_url) && !empty($site->app->pwa_server_url)) {
    return null;
}

// For regular sites, load masterpage
// ...
```

**Result:**
- PWA sites are served without Prasso's masterpage wrapper
- GitHub repository sites are served without Prasso's masterpage wrapper
- Traditional Prasso sites are served with their configured masterpage

## Database Queries

### Site Lookup

```sql
-- Searches sites table for matching host
SELECT * FROM sites 
WHERE host LIKE '%,myapp.example.com,%' 
   OR host LIKE 'myapp.example.com,%'
   OR host LIKE '%,myapp.example.com'
   OR host = 'myapp.example.com'
```

### PWA App Lookup

```sql
-- Searches apps table for matching pwa_app_url
SELECT * FROM apps 
WHERE pwa_app_url LIKE 'https://myapp.example.com%'
   OR pwa_app_url = 'https://myapp.example.com'
LIMIT 1
```

## Configuration Examples

### Example 1: Traditional Prasso Site

```
Site: mysite.com
  └─ host: "mysite.com"
  └─ app: null (or app without pwa_app_url)
  
Result: Requests to mysite.com are served as traditional Prasso sites
```

### Example 2: PWA App with Separate Domain

```
Site: mysite.com
  └─ host: "mysite.com"
  └─ app: My App
    └─ pwa_app_url: "https://myapp.example.com"
    
Result: 
  - Requests to mysite.com → Traditional Prasso site
  - Requests to myapp.example.com → PWA app
```

### Example 3: PWA App with Site Domain

```
Site: mysite.com
  └─ host: "mysite.com, myapp.mysite.com"
  └─ app: My App
    └─ pwa_app_url: "https://myapp.mysite.com"
    
Result:
  - Requests to mysite.com → Traditional Prasso site
  - Requests to myapp.mysite.com → Site URL match (Priority 1)
    └─ SitePageController checks if site has PWA app
    └─ Serves PWA content
```

## Logging

The implementation includes comprehensive logging for debugging:

```
"Site found for host: mysite.com"
"PWA app found for host: myapp.example.com, using associated site 5"
"No site or PWA app found for host: unknown.example.com"
"Proxying PWA request to http://localhost:3001 for app 3 on site 5"
"Proxying PWA page request for about to http://localhost:3001 for app 3 on site 5"
"Failed to proxy PWA request for app 3: Connection refused"
```

## Priority Summary

| Priority | Check | Result |
|----------|-------|--------|
| 1 | URL matches defined site host | Serve site (with PWA if configured) |
| 2 | URL matches PWA app URL | Serve PWA |
| 3 | No match | Abort 404 |

## Notes

- PWA URLs are matched using `LIKE` queries to support paths (e.g., `https://app.example.com/app`)
- The first matching PWA app is used (if multiple apps have overlapping URLs)
- Site URLs take priority over PWA URLs
- If a site has both a defined host and a PWA app URL, the site's host takes priority
- PWA apps must have a valid `site_id` to be used
- The `pwa_app_url` field is optional; apps without it are treated as traditional page apps
