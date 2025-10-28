# PWA App URL Implementation - Summary

## Overview
This implementation adds Progressive Web App (PWA) support to the Prasso admin app editor, enabling teams to configure a separate mobile web app URL alongside the existing site URL. This provides a migration path from the legacy Flutter-based `prasso_app` mobile client to a modern React-based PWA (`prasso_web`).

## Changes Made

### 1. Database Schema
**File**: `database/migrations/2025_10_26_000001_add_pwa_app_url_to_apps_table.php`

- Added nullable `pwa_app_url` column (VARCHAR 2048) to the `apps` table
- Column placed after `page_url` for logical grouping
- Includes comment: "URL to the Progressive Web App (PWA) for mobile access"
- Supports rollback via `down()` method

### 2. Eloquent Model
**File**: `app/Models/Apps.php`

- Added `pwa_app_url` to `$fillable` array
- Updated `processUpdates()` method to persist `pwa_app_url` in both create and update paths
- Field is NOT in `$hidden` array, so it appears in API responses
- Handles null values gracefully with `?? null` operator

### 3. Livewire Component
**File**: `app/Livewire/Apps/AppInfoForm.php`

- Added validation rule: `'teamapp.pwa_app_url' => 'nullable|url|max:2048'`
- Validation ensures URLs are properly formatted and within length limits
- Component passes validation to `Apps::processUpdates()`

### 4. Blade View
**File**: `resources/views/livewire/apps/app-info-form.blade.php`

- Completely redesigned to properly render the app info form
- Added PWA App URL input field with:
  - Type: `url` (HTML5 validation)
  - Placeholder: `https://app.example.com`
  - Helper text explaining PWA usage
  - Error display via `<x-input-error>`
- Includes all existing fields: App Name, Page Title, Page URL, Site, Sort Order, App Icon
- Success message display after save

### 5. API & Service Layer
**File**: `app/Services/AppsService.php`

- No changes required—`json_encode()` automatically includes `pwa_app_url` since:
  - Field is in `$fillable`
  - Field is NOT in `$hidden`
  - All service methods use `json_encode($app_data)`
- `pwa_app_url` appears in all API responses:
  - `getAppSettingsBySite()`
  - `getAppSettingsByUser()`
  - `getAppSettings()`

### 6. DNS Setup for faxt.com Domains
**Files**: `app/Livewire/NewSiteAndApp.php`, `app/Livewire/Apps/AppInfoForm.php`

- When a new site is created with a PWA URL on a faxt.com domain, DNS setup is automatically triggered
- When an app is updated with a new PWA URL on a faxt.com domain, DNS setup is automatically triggered
- DNS setup is only triggered for faxt.com domains; external URLs are not processed
- Helper methods:
  - `isFactDomain($url)`: Checks if URL contains 'faxt.com'
  - `extractDomain($url)`: Extracts domain from full URL using `parse_url()`
- Errors during DNS setup are logged but do not block app creation/update

### 7. URL Routing for PWA Sites
**Files**: `app/Http/Controllers/Controller.php`

#### `getClientFromHost()` Method (Lines 103-136)
- **Step 1**: Check if URL matches a defined site host → Return site
- **Step 2**: If no site match, check if URL matches any PWA app URL → Return associated site
- **Step 3**: If neither, abort 404
- Searches `apps` table for matching `pwa_app_url` using LIKE queries
- Supports PWA URLs with paths (e.g., `https://app.example.com/app`)
- Comprehensive logging for debugging

#### `getMasterForSite()` Method (Lines 138-169)
- Skip masterpage for PWA-hosted sites (lines 149-154)
- Skip masterpage for GitHub repository sites (existing)
- Ensures PWA content is served without Prasso wrapper

### 8. PWA Page Serving
**File**: `app/Http/Controllers/SitePageController.php`

#### `index()` Method (Lines 55-81)
- Serves PWA app's `index.html` if configured
- Falls back to GitHub repository logic if PWA not configured
- Falls back to traditional Prasso welcome page as last resort

#### `viewSitePage()` Method (Lines 374-408)
- Multi-level PWA page resolution:
  1. Try exact file path
  2. Try with `.html` extension
  3. Try directory index (`index.html`)
  4. Fall through to Prasso's page system

#### `viewSitePage()` Fallback (Lines 461-483)
- Serves PWA's `index.html` as fallback for non-existent pages
- Enables SPA support for client-side routing
- Only applies if page not found in Prasso's SitePages table

### 9. Documentation
**Files Updated**:
- `docs/apps-and-tabs-actions.md`: Added PWA field to model documentation and migration guidance
- `docs/prasso-admin-pwa-plan.md`: Implementation plan with detailed workstreams
- `docs/pwa-testing-checklist.md`: Comprehensive testing checklist for QA including DNS setup tests

## API Response Example

```json
{
  "id": 1,
  "team_id": 5,
  "site_id": 2,
  "appicon": "https://cdn.example.com/app-icon.png",
  "app_name": "My App",
  "page_title": "Home",
  "page_url": "https://example.com",
  "pwa_app_url": "https://prasso-web.example.com",
  "sort_order": 1,
  "tabs": [...]
}
```

## Backward Compatibility

- ✅ Existing apps without `pwa_app_url` set will return `null` in API responses
- ✅ Existing mobile clients continue to work with `page_url` as fallback
- ✅ Field is optional and does not break existing functionality
- ✅ No breaking changes to API contracts

## Migration Path from prasso_app to PWA

1. **For existing apps**: Leave `pwa_app_url` empty to continue using `page_url`
2. **For new PWA deployments**: Set `pwa_app_url` to the React-based PWA URL
3. **Client-side logic**: Mobile clients should check for `pwa_app_url` and prefer it over `page_url` if present

## Deployment Checklist

- [ ] Run database migration: `php artisan migrate`
- [ ] Verify `pwa_app_url` column exists in `apps` table
- [ ] Test admin form: navigate to `/team/{teamid}/apps/{appid}`
- [ ] Verify PWA URL input field is visible and functional
- [ ] Test API endpoints to confirm `pwa_app_url` appears in responses
- [ ] Run testing checklist from `docs/pwa-testing-checklist.md`
- [ ] Update client applications to use `pwa_app_url` if present

## Files Created
- `database/migrations/2025_10_26_000001_add_pwa_app_url_to_apps_table.php`
- `docs/prasso-admin-pwa-plan.md`
- `docs/pwa-testing-checklist.md`
- `docs/pwa-implementation-summary.md`

## Files Modified
- `app/Models/Apps.php`
- `app/Livewire/Apps/AppInfoForm.php` (added DNS setup for PWA URLs)
- `app/Livewire/NewSiteAndApp.php` (added PWA URL field and DNS setup)
- `resources/views/livewire/apps/app-info-form.blade.php`
- `app/Http/Controllers/Controller.php` (added PWA URL routing and masterpage skipping)
- `app/Http/Controllers/SitePageController.php` (added PWA page serving logic)
- `docs/apps-and-tabs-actions.md`
- `docs/pwa-testing-checklist.md` (added DNS setup test cases)
- `docs/pwa-implementation-summary.md` (this file - updated with routing changes)

## Next Steps

1. **Code Review**: Review changes in the files listed above
2. **Testing**: Execute the testing checklist in staging environment
3. **Client Updates**: Update `prasso_web` and other clients to use `pwa_app_url` when available
4. **Deployment**: Deploy migration and code changes to production
5. **Communication**: Notify teams about PWA migration path and deprecation of `prasso_app`
