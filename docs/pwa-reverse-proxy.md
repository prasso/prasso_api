# PWA Reverse Proxy Implementation

## Overview

This implementation transforms Prasso into a reverse proxy for Progressive Web Apps (PWAs). Instead of serving static files, Prasso forwards all requests to a Node.js server running at a configured URL. This eliminates the need for Apache vhost configuration for each app.

## Architecture

### Before (Static File Serving)
```
Request: https://myapp.example.com/about
    ↓
Prasso receives request
    ↓
Serves static file from public/hosted_pwa/{app_id}/about.html
```

### After (Reverse Proxy)
```
Request: https://myapp.example.com/about
    ↓
Prasso receives request
    ↓
Proxies to http://localhost:3001/about
    ↓
Node.js server processes request
    ↓
Returns response through Prasso to client
```

## Configuration

### Admin Setup (Single Point of Configuration)

In the App Editor form, configure two fields:

1. **PWA App URL** (Public-facing)
   - Example: `https://myapp.example.com`
   - This is the URL users access
   - DNS points this to Prasso server

2. **PWA Server URL** (Internal)
   - Example: `http://localhost:3001`
   - This is where the Node.js server runs
   - Can be any port or host

### Example Configuration

```
App Name: My React App
PWA App URL: https://myapp.example.com
PWA Server URL: http://localhost:3001
```

### Multiple Apps

Each app can run on a different port:

```
App 1:
  PWA App URL: https://app1.example.com
  PWA Server URL: http://localhost:3001

App 2:
  PWA App URL: https://app2.example.com
  PWA Server URL: http://localhost:3002

App 3:
  PWA App URL: https://app3.example.com
  PWA Server URL: http://localhost:3003
```

## How It Works

### URL Routing

1. **Request arrives** at `https://myapp.example.com/about`
2. **DNS resolves** to Prasso server IP
3. **Controller::getClientFromHost()** checks:
   - Is it a defined site host? → NO
   - Is it a PWA app URL? → YES (matches `pwa_app_url`)
   - Returns associated Site object
4. **SitePageController** receives request
5. **Proxy logic** checks:
   - Does site have `pwa_app_url` and `pwa_server_url`? → YES
   - Calls `proxyRequestToServer()` method
6. **Request forwarded** to `http://localhost:3001/about`
7. **Response returned** to client

### Request Forwarding

The `proxyRequestToServer()` method:

1. **Constructs proxy URL** from `pwa_server_url` + request path
2. **Preserves request method** (GET, POST, PUT, DELETE, etc.)
3. **Forwards headers** (except Host, Connection, Content-Length)
4. **Forwards request body** for POST/PUT/PATCH requests
5. **Preserves query strings** (e.g., `?page=1`)
6. **Returns response** with status code and headers

### Error Handling

If proxy fails:
- Logs error with details
- Falls through to Prasso's page system
- Returns 404 if no Prasso page exists

## Database Schema

### New Column

```sql
ALTER TABLE apps ADD COLUMN pwa_server_url VARCHAR(2048) NULL 
  COMMENT 'Internal URL to the Node.js server for the PWA (e.g., http://localhost:3001)';
```

### Apps Table Fields

```php
protected $fillable = [
    'team_id',
    'site_id',
    'appicon',
    'app_name',
    'page_title',
    'page_url',
    'pwa_app_url',        // Public-facing PWA URL
    'pwa_server_url',     // Internal Node.js server URL (NEW)
    'sort_order',
    'user_role'
];
```

## Implementation Details

### Files Modified

1. **`database/migrations/2025_10_27_000001_add_pwa_server_url_to_apps_table.php`**
   - New migration to add `pwa_server_url` column

2. **`app/Models/Apps.php`**
   - Added `pwa_server_url` to `$fillable` array

3. **`app/Http/Controllers/SitePageController.php`**
   - Updated `index()` method to proxy requests
   - Updated `viewSitePage()` method to proxy requests
   - Added `proxyRequestToServer()` helper method
   - Removed old static file serving logic

4. **`app/Livewire/Apps/AppInfoForm.php`**
   - Added validation rule for `pwa_server_url`

5. **`resources/views/livewire/apps/app-info-form.blade.php`**
   - Added PWA Server URL input field

### Proxy Method

```php
private function proxyRequestToServer($serverUrl, $path, Request $request)
{
    // Constructs full proxy URL
    // Forwards all request details (method, headers, body, query string)
    // Returns response with status code and headers
    // Handles errors gracefully
}
```

## Benefits

✅ **No Apache Configuration** - No vhost setup needed  
✅ **Single Admin Setup** - Just fill in two URLs  
✅ **Multiple Apps** - Each on different port  
✅ **DNS Handled** - Automatic DNS setup for faxt.com domains  
✅ **Full Server Functionality** - Node.js can handle API requests, SSR, etc.  
✅ **Easy Scaling** - Add apps without server configuration  
✅ **Transparent Proxy** - Clients see the PWA URL, not internal port  

## Deployment

### Node.js App Setup

1. **Build your React/Next.js app**
   ```bash
   npm run build
   ```

2. **Start Node.js server** (e.g., on port 3001)
   ```bash
   npm start
   # or
   node server.js
   ```

3. **Configure in Prasso Admin**
   - PWA App URL: `https://myapp.example.com`
   - PWA Server URL: `http://localhost:3001`

4. **DNS Setup** (automatic for faxt.com domains)
   - Points `myapp.example.com` to Prasso server

### Production Considerations

- **Port Management**: Use different ports for each app (3001, 3002, 3003, etc.)
- **Process Management**: Use PM2 or systemd to manage Node.js processes
- **Reverse Proxy Caching**: Consider caching headers for performance
- **Timeout**: Default 30 seconds (configurable)
- **Error Handling**: Gracefully falls back to Prasso pages if proxy fails

## Example: Multi-App Setup

```bash
# Terminal 1: App 1 on port 3001
cd /path/to/app1
npm start

# Terminal 2: App 2 on port 3002
cd /path/to/app2
npm start

# Terminal 3: App 3 on port 3003
cd /path/to/app3
npm start
```

Then in Prasso Admin:

```
App 1:
  Name: App One
  PWA App URL: https://app1.example.com
  PWA Server URL: http://localhost:3001

App 2:
  Name: App Two
  PWA App URL: https://app2.example.com
  PWA Server URL: http://localhost:3002

App 3:
  Name: App Three
  PWA App URL: https://app3.example.com
  PWA Server URL: http://localhost:3003
```

## Logging

All proxy operations are logged:

```
"Proxying PWA request to http://localhost:3001 for app 1 on site 5"
"Proxying PWA page request for /about to http://localhost:3001 for app 1 on site 5"
"Failed to proxy PWA request for app 1: Connection refused"
"Proxy request failed for URL http://localhost:3001/about: ..."
```

## Troubleshooting

### Proxy Request Fails

**Problem**: "Failed to proxy PWA request"

**Solutions**:
1. Verify Node.js server is running on configured port
2. Check `pwa_server_url` is correct (e.g., `http://localhost:3001`)
3. Verify firewall allows connection to Node.js port
4. Check Node.js server logs for errors

### Requests Timeout

**Problem**: Requests take too long or timeout

**Solutions**:
1. Check Node.js server performance
2. Increase timeout (currently 30 seconds)
3. Optimize Node.js app
4. Check network connectivity

### Headers Not Forwarded

**Problem**: Custom headers not reaching Node.js app

**Solutions**:
1. Some headers are intentionally skipped (Host, Connection, Content-Length)
2. Check Node.js app receives headers via `req.headers`
3. Verify header names (case-insensitive)

## Security Considerations

- **Internal URLs Only**: `pwa_server_url` should be internal (localhost or private IP)
- **Port Isolation**: Use high port numbers (3000+) to avoid conflicts
- **Firewall**: Restrict access to Node.js ports from outside network
- **Authentication**: Implement auth in Node.js app if needed
- **HTTPS**: Public URL uses HTTPS, internal can use HTTP

## Performance

- **Proxy Overhead**: Minimal (just forwarding requests)
- **Caching**: Implement in Node.js app for best performance
- **Connection Pooling**: Laravel HTTP client handles this
- **Timeout**: 30 seconds (configurable if needed)

## Future Enhancements

- Load balancing across multiple Node.js instances
- Caching layer for static assets
- Request/response middleware
- Health checks for Node.js servers
- Automatic server restart on failure
