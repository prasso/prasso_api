# PWA Reverse Proxy Implementation - Complete Guide

## Summary

The prasso_api now supports hosting Progressive Web Apps (PWAs) by acting as a reverse proxy. Prasso forwards all requests to a Node.js server running at a configured URL, eliminating the need for Apache vhost configuration. Each app runs independently on a different port.

## Files Modified

### 1. `database/migrations/2025_10_27_000001_add_pwa_server_url_to_apps_table.php`

**Purpose**: Add the `pwa_server_url` column to track internal Node.js server URLs

**Changes**:
- Adds nullable `pwa_server_url` column (VARCHAR 2048)
- Stores the internal URL where the Node.js server runs (e.g., `http://localhost:3001`)
- Includes rollback support

### 2. `app/Models/Apps.php`

**Purpose**: Update the Apps model to include the new field

**Changes**:
- Added `pwa_server_url` to `$fillable` array
- Field is NOT in `$hidden` array, so it appears in API responses

### 3. `app/Http/Controllers/Controller.php`

#### `getClientFromHost()` Method (Lines 103-136)
**Purpose**: Determine which site to serve based on the request URL

**Flow**:
1. Check if URL matches a defined site host
2. If not, check if URL matches any PWA app URL
3. If neither, abort 404

**Key Changes**:
- Searches `apps` table for matching `pwa_app_url` using LIKE queries
- Returns the associated Site from the app's `site_id`
- Comprehensive logging for debugging

#### `getMasterForSite()` Method (Lines 138-169)
**Purpose**: Determine whether to apply Prasso's masterpage

**Key Changes**:
- Skips masterpage for PWA sites (when `pwa_app_url` and `pwa_server_url` are set)
- Skips masterpage for GitHub repository sites (existing)
- Returns null for hosted sites, allowing them to serve their own layouts

### 4. `app/Http/Controllers/SitePageController.php`

#### `index()` Method (Lines 55-81)
**Purpose**: Proxy homepage requests to Node.js server

**PWA Logic**:
- Checks if site has an associated app with `pwa_app_url` and `pwa_server_url`
- Proxies request to Node.js server
- Falls back to GitHub repository logic if PWA not configured
- Falls back to traditional Prasso welcome page as last resort

#### `viewSitePage()` Method (Lines 374-408)
**Purpose**: Proxy page requests to Node.js server

**PWA Logic**:
- Checks if site has an associated app with `pwa_app_url` and `pwa_server_url`
- Proxies request to Node.js server with the requested path
- Falls back to Prasso's page system if proxy fails

#### `proxyRequestToServer()` Method (Lines 913-978)
**Purpose**: Handle the actual HTTP proxy forwarding

**Key Features**:
- Forwards all HTTP methods (GET, POST, PUT, DELETE, etc.)
- Preserves request headers (except Host, Connection, Content-Length)
- Forwards request body for POST/PUT/PATCH requests
- Preserves query strings
- Returns response with status code and headers
- Handles errors gracefully with logging

### 5. `app/Livewire/Apps/AppInfoForm.php`

**Purpose**: Update form validation to include `pwa_server_url`

**Changes**:
- Added validation rule: `'teamapp.pwa_server_url' => 'nullable|url|max:2048'`

### 6. `resources/views/livewire/apps/app-info-form.blade.php`

**Purpose**: Add PWA Server URL input field to the form

**Changes**:
- Added PWA Server URL input field
- Includes placeholder: `http://localhost:3001`
- Includes helper text explaining the field

## How It Works

### Request Flow

```
User Request: https://myapp.example.com/about
    ↓
DNS resolves to Prasso server
    ↓
Controller::getClientFromHost()
  - Matches pwa_app_url
  - Returns associated Site
    ↓
SitePageController::viewSitePage()
  - Checks for pwa_server_url
  - Calls proxyRequestToServer()
    ↓
proxyRequestToServer()
  - Forwards to http://localhost:3001/about
  - Preserves method, headers, body, query string
    ↓
Node.js server processes request
    ↓
Response returned to client
```

## Configuration

### For Site Administrators

1. Start Node.js server on a local port (e.g., `http://localhost:3001`)
2. Create or edit an app in the App Editor
3. Fill in two fields:
   - **PWA App URL**: `https://myapp.example.com` (public URL)
   - **PWA Server URL**: `http://localhost:3001` (internal server)
4. Save the app configuration
5. DNS setup is automatic for faxt.com domains

6. Deployment command (shown in popup after saving)

   After you save, Prasso will show a popup with a copy-pasteable command to deploy the frontend using `deploy.py`. The command is pre-filled using:
   - `--sudo-user` from `.env` (e.g., `DEPLOY_SUDO_USER`)
   - `--app-user` from `.env` (e.g., `DEPLOY_APP_USER`)
   - `--web-user` from `.env` (e.g., `DEPLOY_WEB_USER`)
   - `--app-dir-name` = `<site_name>_app` (derived from the Site)
   - `--port` from the newly saved App (from `pwa_server_url` port)

   Example (values will be filled for the current app):
   ```bash
   ./deploy.py \
     --server <PRASSO_SERVER_IP_OR_HOST> \
     --sudo-user <VALUE_FROM_ENV> \
     --app-user <VALUE_FROM_ENV> \
     --web-user <VALUE_FROM_ENV> \
     --app-dir-name <site_name>_app \
     --port <derived_port> \
     --key /path/to/your-key.pem \
     --install-pm2
   ```

   Notes
   - Deploy path: `/var/www/html/prasso_api/public/hosted_sites/<site_name>_app`
   - PM2 process name: `<site_name>_app`
   - Ensure the SSH key path is correct for your workstation
   - Re-run with `--update-only` for subsequent updates (no PM2/ownership changes)

### Multiple Apps Example

```
App 1: https://app1.example.com → http://localhost:3001
App 2: https://app2.example.com → http://localhost:3002
App 3: https://app3.example.com → http://localhost:3003
```

## Key Features

✅ **No Apache Configuration** - No vhost setup needed  
✅ **Single Admin Setup** - Just configure two URLs  
✅ **Full Server Functionality** - Node.js handles everything  
✅ **Multiple Apps** - Each on different port  
✅ **Automatic DNS** - For faxt.com domains  
✅ **Transparent Proxy** - Clients see public URL  
✅ **Error Handling** - Falls back to Prasso pages  
✅ **Comprehensive Logging** - All proxy operations logged  

## Database Schema

### New Column
```sql
ALTER TABLE apps ADD COLUMN pwa_server_url VARCHAR(2048) NULL 
  COMMENT 'Internal URL to the Node.js server for the PWA (e.g., http://localhost:3001)';
```

## Logging Examples

```
"Proxying PWA request to http://localhost:3001 for app 1 on site 5"
"Proxying PWA page request for /about to http://localhost:3001 for app 1 on site 5"
"Failed to proxy PWA request for app 1: Connection refused"
"Proxy request failed for URL http://localhost:3001/about: ..."
```

## Backward Compatibility

- ✅ Existing GitHub repository sites continue to work unchanged
- ✅ Existing Prasso sites continue to work unchanged
- ✅ PWA proxying only activates when both `pwa_app_url` and `pwa_server_url` are set
- ✅ No breaking changes to existing functionality
- ✅ No changes to existing database schema (except new column)

## Next Steps

1. **Run Migration**: `php artisan migrate`
2. **Start Node.js Apps**: Start your Node.js servers on configured ports
3. **Configure Apps**: Fill in PWA App URL and PWA Server URL in admin form
4. **Test Access**: Access apps through public URLs
5. **Monitor Logs**: Check logs for proxy operations

## Related Documentation

- `docs/pwa-reverse-proxy.md` - Comprehensive reverse proxy guide
- `docs/pwa-app-sites.md` - User guide for PWA site integration
- `docs/PWA_REVERSE_PROXY_SUMMARY.md` - Quick reference summary
