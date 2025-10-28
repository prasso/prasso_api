# PWA App Site Integration

## Overview

The PWA App integration allows site administrators to connect a site directly to a Progressive Web App (PWA) running on a Node.js server. Prasso acts as a reverse proxy, forwarding all requests to the Node.js server without requiring Apache vhost configuration.

## How It Works

When a PWA app is specified for a site:

1. The Node.js server runs independently (e.g., on `http://localhost:3001`)
2. Prasso receives requests to the public PWA URL (e.g., `https://myapp.example.com`)
3. Prasso proxies all requests to the Node.js server
4. The Node.js server processes requests and returns responses
5. Responses are returned to the client through Prasso

### Request Flow

When a user requests a page:

1. **User accesses**: `https://myapp.example.com/about`
2. **DNS resolves** to Prasso server
3. **Prasso receives** the request
4. **Prasso proxies** to `http://localhost:3001/about`
5. **Node.js server** processes the request
6. **Response returned** to client through Prasso

## Configuration

### Setting Up a PWA App Site

1. Start your Node.js server on a local port (e.g., `http://localhost:3001`)
2. Navigate to the App Editor in the admin dashboard
3. Create or edit an app associated with your site
4. Fill in two fields:
   - **PWA App URL**: The public-facing URL (e.g., `https://myapp.example.com`)
   - **PWA Server URL**: The internal Node.js server URL (e.g., `http://localhost:3001`)
5. Save the app configuration
6. DNS setup is automatic for faxt.com domains

### URL Routing Priority

The system uses intelligent URL routing to determine which site to serve:

1. **First**: Check if the request URL matches a defined site host
2. **Second**: If no site match, check if the URL matches any PWA app URL
3. **Third**: If neither, abort 404

This means:
- If you have a site with host `mysite.com` and an app with `pwa_app_url: https://myapp.example.com`
- Requests to `mysite.com` will serve the site
- Requests to `myapp.example.com` will serve the PWA
- Requests to any other URL will return 404

### Required App Configuration

For a site to serve PWA content, the following must be set in the App:

- **`pwa_app_url`**: The public-facing URL to the Progressive Web App
  - Example: `https://myapp.example.com`
  - Must be a valid URL
  - Can be any domain (not limited to faxt.com)

- **`pwa_server_url`**: The internal URL where the Node.js server is running
  - Example: `http://localhost:3001`
  - Must be accessible from the Prasso server
  - Can be localhost, private IP, or remote server

### Node.js Server Requirements

Your Node.js server should:

- Be running and accessible on the configured port
- Handle all HTTP methods (GET, POST, PUT, DELETE, etc.)
- Return appropriate status codes
- Handle routing internally (Prasso just forwards requests)
- Support the request/response headers being forwarded

## Benefits

- **No Apache Configuration**: No vhost setup needed for each app
- **Single Admin Setup**: Just configure two URLs in the app form
- **Full Server Functionality**: Node.js handles all requests, APIs, SSR, etc.
- **Multiple Apps**: Each app runs independently on different ports
- **Automatic DNS**: DNS setup is automatic for faxt.com domains
- **Easy Scaling**: Add new apps without server configuration

## Best Practices

1. **Port Management**: Use different ports for each app (3001, 3002, 3003, etc.)
2. **Process Management**: Use PM2 or systemd to manage Node.js processes
3. **Error Handling**: Implement proper error handling in your Node.js app
4. **Performance**: Optimize your Node.js app for fast response times
5. **Logging**: Implement logging in your Node.js app for debugging
6. **Security**: Keep Node.js server on internal network, only expose through Prasso

## Technical Implementation

The system handles PWA sites by:

1. **App Association**: Linking the site to an app with `pwa_app_url` and `pwa_server_url`
2. **Request Routing**: `Controller::getClientFromHost()` identifies PWA requests by matching `pwa_app_url`
3. **Request Proxying**: `SitePageController` proxies requests to the Node.js server
4. **Response Forwarding**: Responses are returned to the client with status codes and headers
5. **Error Handling**: If proxy fails, falls back to Prasso's page system
6. **Conditional Masterpage**: The masterpage is skipped for PWA-hosted sites

### Key Controllers and Methods

- **`Controller::getClientFromHost()`**: Identifies PWA requests by matching `pwa_app_url`
- **`SitePageController::index()`**: Proxies homepage requests to Node.js server
- **`SitePageController::viewSitePage()`**: Proxies page requests to Node.js server
- **`SitePageController::proxyRequestToServer()`**: Handles the actual proxy forwarding

## Deployment Process

### Deploying a Node.js App to Prasso

1. Develop your Node.js/React/Next.js app
2. Start the Node.js server on a local port (e.g., `http://localhost:3001`)
3. Configure the app in Prasso Admin:
   - PWA App URL: `https://myapp.example.com`
   - PWA Server URL: `http://localhost:3001`
4. DNS setup is automatic for faxt.com domains
5. Users access `https://myapp.example.com` and Prasso proxies to Node.js

### Example: Multi-App Setup

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

Then configure in Prasso Admin:
- App 1: `https://app1.example.com` → `http://localhost:3001`
- App 2: `https://app2.example.com` → `http://localhost:3002`
- App 3: `https://app3.example.com` → `http://localhost:3003`

## Troubleshooting

If your PWA site is not working:

1. **Verify Node.js server is running** on the configured port
2. **Check `pwa_server_url`** is correct (e.g., `http://localhost:3001`)
3. **Verify firewall** allows connection to Node.js port
4. **Check Node.js server logs** for errors
5. **Verify `pwa_app_url`** matches the request URL
6. **Check Prasso logs** for proxy errors
7. **Test Node.js server directly** (e.g., `curl http://localhost:3001/`)

## For More Information

See `docs/pwa-reverse-proxy.md` for comprehensive implementation details.
