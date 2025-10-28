# PWA Reverse Proxy Implementation

## Overview

This implementation adds PWA reverse proxy support to the prasso_api, allowing Prasso to act as a transparent proxy for Node.js applications. Instead of serving static files, Prasso forwards all requests to a Node.js server running at a configured URL.

## Changes Made

### 1. Database Migration
**File**: `database/migrations/2025_10_27_000001_add_pwa_server_url_to_apps_table.php`

Added `pwa_server_url` column to the `apps` table:

- Stores the internal URL where the Node.js server is running
- Example: `http://localhost:3001`
- Nullable field (only used when PWA proxying is enabled)

### 2. Apps Model
**File**: `app/Models/Apps.php`

Updated the model to include the new field:

- Added `pwa_server_url` to `$fillable` array
- Field is NOT in `$hidden` array (appears in API responses)

### 3. SitePageController - index() Method
**File**: `app/Http/Controllers/SitePageController.php` (lines 55-81)

Added PWA proxy logic to the `index()` method:

- Checks if the site has an associated app with both `pwa_app_url` and `pwa_server_url`
- If yes, proxies the request to the Node.js server
- Falls back to GitHub repository logic if PWA is not configured
- Falls back to traditional Prasso welcome page if neither PWA nor GitHub repo is configured

**Key Logic**:
```php
if ($this->site != null) {
    $app = $this->site->app;
    if ($app && !empty($app->pwa_app_url) && !empty($app->pwa_server_url)) {
        try {
            $proxyResponse = $this->proxyRequestToServer($app->pwa_server_url, $request->path(), $request);
            Log::info("Proxying PWA request to {$app->pwa_server_url} for app {$app->id} on site {$this->site->id}");
            return $proxyResponse;
        } catch (\Exception $e) {
            Log::error("Failed to proxy PWA request for app {$app->id}: {$e->getMessage()}");
            // Fall through to traditional handling
        }
    }
}
```

### 4. SitePageController - viewSitePage() Method
**File**: `app/Http/Controllers/SitePageController.php` (lines 374-408)

Added PWA proxy logic for page requests:

- Checks if the site has an associated app with both `pwa_app_url` and `pwa_server_url`
- If yes, proxies the request to the Node.js server with the requested path
- Falls back to Prasso's page system if proxy fails

**Key Logic**:
```php
if ($this->site != null) {
    $app = $this->site->app;
    if ($app && !empty($app->pwa_app_url) && !empty($app->pwa_server_url)) {
        try {
            $proxyResponse = $this->proxyRequestToServer($app->pwa_server_url, '/' . $section, $request);
            Log::info("Proxying PWA page request for {$section} to {$app->pwa_server_url} for app {$app->id} on site {$this->site->id}");
            return $proxyResponse;
        } catch (\Exception $e) {
            Log::error("Failed to proxy PWA page request for {$section} on app {$app->id}: {$e->getMessage()}");
            // Fall through to Prasso's page system
        }
    }
}
```

### 5. SitePageController - proxyRequestToServer() Method
**File**: `app/Http/Controllers/SitePageController.php` (lines 913-978)

New helper method that handles the actual HTTP proxy forwarding:

- Constructs the full proxy URL from server URL + request path + query string
- Determines the HTTP method (GET, POST, PUT, DELETE, etc.)
- Builds headers to forward (skips Host, Connection, Content-Length)
- Forwards request body for POST/PUT/PATCH requests
- Executes the proxy request using Laravel's HTTP client
- Returns response with status code and headers
- Handles errors gracefully with logging

**Key Features**:
- 30-second timeout for proxy requests
- Preserves all request/response details
- Comprehensive error handling and logging

### 6. AppInfoForm Validation
**File**: `app/Livewire/Apps/AppInfoForm.php`

Updated form validation to include the new field:

- Added validation rule: `'teamapp.pwa_server_url' => 'nullable|url|max:2048'`
- Validates that the URL is properly formatted
- Allows null values (optional field)

### 7. App Info Form View
**File**: `resources/views/livewire/apps/app-info-form.blade.php`

Added PWA Server URL input field to the admin form:

- Input field for `pwa_server_url`
- Placeholder: `http://localhost:3001`
- Helper text explaining the field's purpose
- Error display for validation failures

### 8. Controller - getMasterForSite() Method
**File**: `app/Http/Controllers/Controller.php` (lines 138-169)

Updated to skip masterpage for PWA-hosted sites:

- Checks if site is PWA-hosted (has app with both `pwa_app_url` and `pwa_server_url`)
- Skips masterpage setup for PWA sites
- Skips masterpage for GitHub repository sites (existing)
- This prevents Prasso's masterpage from wrapping PWA content

## How It Works

### Request Flow for PWA Sites

1. **User requests a page** (e.g., `https://myapp.example.com/about`)
2. **DNS resolves** to Prasso server
3. **Controller::getClientFromHost()** identifies the PWA by matching `pwa_app_url`
4. **SitePageController::viewSitePage()** is called with section = `about`
5. **PWA proxy check**: If site has app with both `pwa_app_url` and `pwa_server_url`:
   - Calls `proxyRequestToServer()` to forward request to Node.js
   - Returns response from Node.js server
6. **Fallback**: If proxy fails, falls back to Prasso's page system
7. **Default fallback**: If nothing found, serves welcome page

### Proxy Request Flow

1. **Construct proxy URL**: `http://localhost:3001/about`
2. **Determine HTTP method**: GET, POST, PUT, DELETE, etc.
3. **Build headers**: Forward all headers except Host, Connection, Content-Length
4. **Forward body**: For POST/PUT/PATCH, forward request body
5. **Execute request**: Use Laravel HTTP client with 30-second timeout
6. **Return response**: Return status code, headers, and body from Node.js

## Configuration

### For Site Administrators

1. Start Node.js server on a local port (e.g., `http://localhost:3001`)
2. Create or edit an app associated with the site
3. Fill in two fields:
   - **PWA App URL**: `https://myapp.example.com` (public URL)
   - **PWA Server URL**: `http://localhost:3001` (internal Node.js server)
4. Save the app configuration
5. DNS setup is automatic for faxt.com domains

### For Developers

1. Develop your Node.js/React/Next.js app
2. Start the Node.js server on the configured port
3. Ensure the server handles all routes and HTTP methods
4. Test the app through the public URL

## Logging

The implementation includes comprehensive logging:

- `"Proxying PWA request to http://localhost:3001 for app {app_id} on site {site_id}"` - When proxying homepage
- `"Proxying PWA page request for {section} to http://localhost:3001..."` - When proxying page
- `"Failed to proxy PWA request for app {app_id}: {error}"` - When proxy fails
- `"Proxy request failed for URL http://localhost:3001/about: {error}"` - Detailed proxy errors

## Backward Compatibility

- ✅ Existing GitHub repository sites continue to work unchanged
- ✅ Existing Prasso sites continue to work unchanged
- ✅ PWA proxying only activates when both `pwa_app_url` and `pwa_server_url` are set
- ✅ No breaking changes to existing functionality

## Files Modified

1. `app/Http/Controllers/SitePageController.php`
   - Added PWA serving logic to `index()` method
   - Added PWA serving logic to `viewSitePage()` method
   - Added PWA fallback logic to `viewSitePage()` method

2. `app/Http/Controllers/Controller.php`
   - Updated constructor to skip masterpage for PWA-hosted sites

## Files Created

1. `docs/pwa-app-sites.md` - User documentation for PWA site integration
2. `docs/pwa-site-serving-implementation.md` - Technical implementation details (this file)

## Testing Checklist

- [ ] Deploy a PWA to `public/hosted_pwa/{app_id}/`
- [ ] Create a site with an app that has `pwa_app_url` set
- [ ] Visit the site homepage and verify PWA index.html is served
- [ ] Visit a PWA page and verify it's served correctly
- [ ] Test multi-level resolution (exact file, .html, directory index)
- [ ] Test PWA fallback for non-existent pages
- [ ] Verify GitHub repository sites still work
- [ ] Verify traditional Prasso sites still work
- [ ] Check logs for proper PWA serving messages
- [ ] Verify masterpage is not applied to PWA sites
- [ ] Test with multiple apps to ensure app_id isolation

## Notes

- PWA deployment is not automated (as per requirements)
- The `pwa_app_url` field is already implemented in the Apps model
- DNS setup for faxt.com PWA URLs is already implemented in NewSiteAndApp and AppInfoForm
- This implementation mirrors the GitHub repository site logic for consistency
