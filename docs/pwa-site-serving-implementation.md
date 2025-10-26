# PWA Site Serving Implementation

## Overview

This implementation adds PWA site serving to the prasso_api, allowing sites to be served directly from Progressive Web Apps using the same logic pattern as GitHub repository sites.

## Changes Made

### 1. SitePageController - index() Method
**File**: `app/Http/Controllers/SitePageController.php` (lines 55-81)

Added PWA app serving logic to the `index()` method:

- Checks if the site has an associated app with `pwa_app_url` configured
- If yes, attempts to serve `public/hosted_pwa/{app_id}/index.html`
- Falls back to GitHub repository logic if PWA is not configured
- Falls back to traditional Prasso welcome page if neither PWA nor GitHub repo is configured

**Key Logic**:
```php
if ($this->site != null) {
    $app = $this->site->app;
    if ($app && !empty($app->pwa_app_url)) {
        $pwaPath = public_path('hosted_pwa/' . $app->id . '/index.html');
        if (file_exists($pwaPath)) {
            Log::info("Serving PWA index page for app {$app->id} on site {$this->site->id}");
            return response()->file($pwaPath);
        }
    }
}
```

### 2. SitePageController - viewSitePage() Method
**File**: `app/Http/Controllers/SitePageController.php` (lines 374-408)

Added PWA app page serving logic with multi-level resolution:

- **Level 1**: Try exact file path (e.g., `/about` → `hosted_pwa/app_id/about`)
- **Level 2**: Try with `.html` extension (e.g., `/about` → `hosted_pwa/app_id/about.html`)
- **Level 3**: Try directory index (e.g., `/about` → `hosted_pwa/app_id/about/index.html`)
- Falls back to Prasso's page system if none of the above exist

**Key Logic**:
```php
if ($this->site != null) {
    $app = $this->site->app;
    if ($app && !empty($app->pwa_app_url)) {
        $pagePath = public_path('hosted_pwa/' . $app->id . '/' . $section);
        
        if (file_exists($pagePath)) {
            return response()->file($pagePath);
        } else if (file_exists($pagePath . '.html')) {
            return response()->file($pagePath . '.html');
        } else if (is_dir($pagePath) && file_exists($pagePath . '/index.html')) {
            return response()->file($pagePath . '/index.html');
        }
    }
}
```

### 3. SitePageController - viewSitePage() Fallback
**File**: `app/Http/Controllers/SitePageController.php` (lines 461-483)

Added PWA app fallback logic when a page is not found in Prasso's page system:

- If a page is not found in the Prasso SitePages table
- And the site has a PWA app with `pwa_app_url` configured
- Serve the PWA's `index.html` as a fallback (useful for single-page applications)

**Key Logic**:
```php
if ($this->site != null) {
    $app = $this->site->app;
    if ($app && !empty($app->pwa_app_url)) {
        $indexPath = public_path('hosted_pwa/' . $app->id . '/index.html');
        if (file_exists($indexPath)) {
            Log::info("Page {$section} not found, serving PWA index page as fallback...");
            return response()->file($indexPath);
        }
    }
}
```

### 4. Controller - Constructor
**File**: `app/Http/Controllers/Controller.php` (lines 37-44)

Updated the base Controller to skip masterpage setup for PWA-hosted sites:

- Checks if site is GitHub-hosted (has `deployment_path` and `github_repository`)
- Checks if site is PWA-hosted (has app with `pwa_app_url`)
- Skips masterpage setup for both types of hosted sites
- This prevents Prasso's masterpage from wrapping PWA content

**Key Logic**:
```php
$isGitHubHosted = $site != null && !empty($site->deployment_path) && !empty($site->github_repository);
$isPwaHosted = $site != null && $site->app && !empty($site->app->pwa_app_url);

if (!($isGitHubHosted || $isPwaHosted)) {
    $this->masterpage = $this->getMasterForSite($site);
    View::share('masterPage', $this->masterpage);
}
```

## How It Works

### Request Flow for PWA Sites

1. **User requests a page** (e.g., `https://mysite.com/about`)
2. **SitePageController::viewSitePage()** is called with section = `about`
3. **PWA check**: If site has app with `pwa_app_url`:
   - Try `public/hosted_pwa/{app_id}/about`
   - Try `public/hosted_pwa/{app_id}/about.html`
   - Try `public/hosted_pwa/{app_id}/about/index.html`
4. **Prasso page check**: If not found in PWA, check Prasso SitePages table
5. **PWA fallback**: If not in Prasso, serve `public/hosted_pwa/{app_id}/index.html`
6. **Default fallback**: If nothing found, serve welcome page

### Deployment Structure

PWA apps should be deployed to:
```
public/hosted_pwa/{app_id}/
├── index.html
├── assets/
│   ├── js/
│   ├── css/
│   └── images/
└── [other PWA files]
```

## Priority Order

When a site has both PWA and GitHub repository configured:

1. **PWA is checked first** (lines 391-408 in viewSitePage)
2. **GitHub repository is checked second** (lines 428-443 in viewSitePage)
3. **Prasso pages are checked third**
4. **PWA fallback is used** (lines 473-483 in viewSitePage)
5. **GitHub fallback is used** (lines 497-505 in viewSitePage)
6. **Welcome page is used as last resort**

This prioritization allows PWA sites to take precedence while maintaining backward compatibility with GitHub repository sites.

## Configuration

### For Site Administrators

1. Create or edit an app associated with the site
2. Set the `pwa_app_url` field to the PWA URL (e.g., `https://app.example.com`)
3. Deploy the PWA build to `public/hosted_pwa/{app_id}/`
4. The site will automatically serve from the PWA

### For Developers

1. Build your PWA (e.g., `npm run build`)
2. Deploy the build output to `public/hosted_pwa/{app_id}/`
3. Ensure `index.html` exists at the root
4. Verify all assets are accessible

## Logging

The implementation includes comprehensive logging:

- `"Serving PWA index page for app {app_id} on site {site_id}"` - When serving PWA index
- `"Serving PWA page {section} for app {app_id} on site {site_id}"` - When serving PWA page
- `"Serving PWA page {section}.html for app {app_id} on site {site_id}"` - When serving with .html
- `"Serving PWA directory index for {section}..."` - When serving directory index
- `"Page {section} not found, serving PWA index page as fallback..."` - When using PWA fallback

## Backward Compatibility

- ✅ Existing GitHub repository sites continue to work unchanged
- ✅ Existing Prasso sites continue to work unchanged
- ✅ PWA serving only activates when `pwa_app_url` is set
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
