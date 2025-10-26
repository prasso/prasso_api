# PWA App Site Integration

## Overview

The PWA App integration allows site administrators to connect a site directly to a Progressive Web App (PWA). When enabled, the site's pages will be sourced directly from the deployed PWA rather than being manually configured through the admin interface.

## How It Works

When a PWA app is specified for a site:

1. The PWA is deployed to `public/hosted_pwa/{app_id}/`
2. Site pages are sourced directly from the deployed PWA content
3. The manual site pages editor is automatically disabled
4. Content updates are managed through the PWA's deployment process
5. Changes to the PWA are automatically reflected on the site

### Page Resolution Order

When a user requests a page, the system follows this resolution order:

1. **Direct file match**: Serve the exact file path (e.g., `/about` → `hosted_pwa/app_id/about`)
2. **HTML extension**: Try with `.html` extension (e.g., `/about` → `hosted_pwa/app_id/about.html`)
3. **Directory index**: Check for `index.html` in a directory (e.g., `/about` → `hosted_pwa/app_id/about/index.html`)
4. **Prasso pages**: Fall back to Prasso's page system if the file doesn't exist in the PWA
5. **PWA index fallback**: If no Prasso page is found, serve the PWA's `index.html` (useful for single-page applications)

## Configuration

### Setting Up a PWA App Site

1. Navigate to the Site Editor in the admin dashboard
2. Create a new site or edit an existing one
3. Create or edit an app associated with the site
4. In the PWA App URL field, enter the URL to your PWA
   - Example: `https://app.example.com`
5. The system will automatically configure the site to serve from the PWA
6. Save the site and app configuration

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

### Required Site Properties

For a site to serve PWA content, the following must be set:

- **`pwa_app_url`** (in the Apps model): The URL to the Progressive Web App
  - Must be a valid URL
  - Can be any domain (not limited to faxt.com)
  - Stored in the `apps` table and can be viewed/managed through the App Editor

### PWA Structure Requirements

Your PWA should follow these conventions:

- **Build Output**: The PWA's build output should be deployed to `public/hosted_pwa/{app_id}/`
- **Index Page**: Include an `index.html` file at the root of the deployment directory
- **Assets**: Store assets in appropriate subdirectories (e.g., `assets/`, `js/`, `css/`)
- **Routing**: For single-page applications, implement client-side routing to handle all paths

### Supported File Types

- HTML files (`.html`, `.htm`)
- JavaScript files (`.js`)
- CSS files (`.css`)
- Image files (`.jpg`, `.jpeg`, `.png`, `.gif`, `.svg`, `.webp`)
- JSON files (`.json`)
- Font files (`.woff`, `.woff2`, `.ttf`, `.eot`)
- Any other static assets

## Benefits

- **Modern Web Stack**: Use modern frameworks like React, Vue, or Angular for your site
- **Client-Side Routing**: Leverage client-side routing for fast, responsive navigation
- **Development Workflow**: Develop PWAs independently and deploy them to Prasso
- **Unified Hosting**: Host both traditional Prasso sites and modern PWAs on the same infrastructure
- **Automatic Updates**: Deploy new versions of your PWA and see them reflected immediately

## Limitations

When using a PWA for site content:

- The manual site pages editor is disabled
- Site page data templates cannot be used
- Custom site page types (S3, URL) are not supported
- Dynamic content processing is limited to standard site tags
- The Prasso masterpage is skipped for PWA-hosted sites

## Best Practices

1. **Build Process**: Automate your PWA build and deployment process
2. **Version Management**: Use semantic versioning for your PWA releases
3. **Testing**: Test all routes and assets before deploying to production
4. **Error Handling**: Implement proper error handling and 404 pages in your PWA
5. **Performance**: Optimize your PWA for fast loading and good performance
6. **Caching**: Implement appropriate caching strategies for static assets

## Technical Implementation

The system handles PWA sites by:

1. **App Association**: Linking the site to an app with a configured `pwa_app_url`
2. **Page Serving**: When a request comes in, the `SitePageController` checks for files in the deployed PWA
3. **File Resolution**: Following the page resolution order (direct file → .html extension → directory index → Prasso pages → PWA index)
4. **Fallback Handling**: If a page isn't found in Prasso's system, the PWA's `index.html` is served as a fallback (useful for SPAs)
5. **Conditional Masterpage**: The masterpage is skipped for PWA-hosted sites (when the app has `pwa_app_url` set)

### Key Controllers and Services

- **`SitePageController::index()`**: Serves the PWA's `index.html` for the homepage
- **`SitePageController::viewSitePage()`**: Serves specific pages from the PWA with multi-level resolution
- **`Controller::__construct()`**: Skips masterpage setup for PWA-hosted sites

## Deployment Process

### Deploying a PWA to Prasso

1. Build your PWA (e.g., `npm run build`)
2. Copy the build output to `public/hosted_pwa/{app_id}/` on the server
3. Ensure `index.html` exists at the root of the deployment directory
4. Verify all assets are accessible and properly served
5. Test the site in a browser to confirm all routes work

### Automated Deployment

For automated deployments, consider:

- Using CI/CD pipelines (GitHub Actions, GitLab CI, etc.) to build and deploy your PWA
- Setting up webhooks to trigger deployments on code changes
- Implementing health checks to verify successful deployments
- Using version management to track PWA releases

## Troubleshooting

If your PWA site is not displaying correctly:

1. Verify the PWA is deployed to `public/hosted_pwa/{app_id}/`
2. Check that `index.html` exists in the deployment directory
3. Ensure all assets are properly deployed and accessible
4. Verify file permissions are correct (readable by the web server)
5. Check the system logs for any errors related to file access
6. Test individual routes in your PWA to confirm they work
7. Verify the app's `pwa_app_url` is correctly configured

## Migration from Traditional Sites

To migrate a traditional Prasso site to a PWA:

1. Develop your PWA using your preferred framework
2. Build and deploy the PWA to `public/hosted_pwa/{app_id}/`
3. Create or update an app with the `pwa_app_url` pointing to your PWA
4. Associate the app with your site
5. Test the site to confirm all pages and functionality work
6. Update any external links or references to point to the new PWA site

## Example PWA Deployment

```bash
# Build your React PWA
npm run build

# Copy build output to Prasso
scp -r build/* user@server:/var/www/prasso/public/hosted_pwa/123/

# Verify deployment
curl https://your-site.com/
```

For additional support or questions, please contact the system administrator.
