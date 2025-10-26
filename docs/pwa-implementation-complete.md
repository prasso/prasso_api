# PWA Site Serving - Complete Implementation

## Summary

The prasso_api now supports serving Progressive Web Apps (PWAs) as sites using the same logic pattern as GitHub repository sites. The implementation includes intelligent URL routing that prioritizes defined site URLs first, then falls back to PWA app URLs.

## Files Modified

### 1. `app/Http/Controllers/Controller.php`

#### `getClientFromHost()` Method (Lines 103-136)
**Purpose**: Determine which site to serve based on the request URL

**Flow**:
1. Check if URL matches a defined site host
2. If not, check if URL matches any PWA app URL
3. If neither, abort 404

**Key Changes**:
- Added PWA app URL lookup in the `apps` table
- Searches for matching `pwa_app_url` using LIKE queries
- Returns the associated Site from the app's `site_id`
- Comprehensive logging for debugging

#### `getMasterForSite()` Method (Lines 138-169)
**Purpose**: Determine whether to apply Prasso's masterpage

**Key Changes**:
- Added check for PWA-hosted sites
- Skips masterpage for PWA sites (lines 149-154)
- Skips masterpage for GitHub repository sites (existing)
- Returns null for hosted sites, allowing them to serve their own layouts

### 2. `app/Http/Controllers/SitePageController.php`

#### `index()` Method (Lines 55-81)
**Purpose**: Serve the homepage

**PWA Logic**:
- Checks if site has an associated app with `pwa_app_url`
- Serves `public/hosted_pwa/{app_id}/index.html` if it exists
- Falls back to GitHub repository logic if PWA not configured
- Falls back to traditional Prasso welcome page as last resort

#### `viewSitePage()` Method (Lines 374-408)
**Purpose**: Serve specific pages with multi-level resolution

**PWA Logic**:
1. Try exact file path: `/about` → `hosted_pwa/app_id/about`
2. Try with .html extension: `/about` → `hosted_pwa/app_id/about.html`
3. Try directory index: `/about` → `hosted_pwa/app_id/about/index.html`
4. Fall through to Prasso's page system

#### `viewSitePage()` Fallback (Lines 461-483)
**Purpose**: Provide SPA support for client-side routing

**PWA Logic**:
- If page not found in Prasso's SitePages table
- And site has PWA app with `pwa_app_url`
- Serve PWA's `index.html` as fallback
- Allows SPAs to handle routing client-side

## Files Created

### 1. `docs/pwa-app-sites.md`
User-facing documentation for PWA site integration. Covers:
- Overview and how PWA sites work
- Configuration instructions
- Repository structure requirements
- Supported file types
- Best practices
- Troubleshooting guide

### 2. `docs/pwa-site-serving-implementation.md`
Technical implementation details. Covers:
- Changes made to each file
- How the system works
- Priority order for PWA vs GitHub repos
- Configuration examples
- Logging details
- Testing checklist

### 3. `docs/pwa-url-routing-flow.md`
Complete URL routing flow documentation. Covers:
- Request flow with examples
- Step-by-step routing logic
- Complete request lifecycle examples
- Masterpage handling
- Database queries
- Configuration examples
- Priority summary

## How It Works

### URL Routing Priority

```
Request arrives with URL
    ↓
getClientFromHost() checks:
    1. Is URL a defined site host? → YES → Serve site
    2. Is URL a PWA app URL? → YES → Serve PWA
    3. Neither? → Abort 404
```

### Page Serving Priority (for PWA Sites)

```
Request for page /about
    ↓
SitePageController::viewSitePage() checks:
    1. PWA file exists? → YES → Serve from PWA
    2. PWA file with .html exists? → YES → Serve from PWA
    3. PWA directory index exists? → YES → Serve from PWA
    4. Prasso page exists? → YES → Serve from Prasso
    5. PWA fallback exists? → YES → Serve PWA index (SPA routing)
    6. Neither? → Serve welcome page
```

## Configuration

### For Site Administrators

1. Create an app associated with your site
2. Set the `pwa_app_url` field (e.g., `https://myapp.example.com`)
3. Deploy your PWA build to `public/hosted_pwa/{app_id}/`
4. Ensure `index.html` exists at the root
5. The site will automatically serve from the PWA

### For Developers

1. Build your PWA: `npm run build`
2. Deploy to: `public/hosted_pwa/{app_id}/`
3. Ensure all assets are accessible
4. Test all routes in the PWA

### Deployment Structure

```
public/hosted_pwa/{app_id}/
├── index.html
├── assets/
│   ├── js/
│   ├── css/
│   └── images/
└── [other PWA files]
```

## Key Features

✅ **Intelligent URL Routing** - Site URLs take priority, PWA URLs are fallback  
✅ **Multi-Level File Resolution** - Exact file → .html → directory index  
✅ **SPA Support** - PWA index.html fallback for client-side routing  
✅ **No Masterpage Wrapping** - PWA sites served without Prasso masterpage  
✅ **Comprehensive Logging** - All routing decisions are logged  
✅ **Backward Compatible** - Existing sites and GitHub repos continue to work  
✅ **Manual Deployment** - No automated deployment (admin responsibility)  

## Database Schema

### Apps Table
```sql
ALTER TABLE apps ADD COLUMN pwa_app_url VARCHAR(2048) NULL 
  COMMENT 'URL to the Progressive Web App (PWA) for mobile access';
```

### Queries Used

**Site Lookup**:
```sql
SELECT * FROM sites 
WHERE host LIKE '%,myapp.example.com,%' 
   OR host LIKE 'myapp.example.com,%'
   OR host LIKE '%,myapp.example.com'
   OR host = 'myapp.example.com'
```

**PWA App Lookup**:
```sql
SELECT * FROM apps 
WHERE pwa_app_url LIKE 'https://myapp.example.com%'
   OR pwa_app_url = 'https://myapp.example.com'
LIMIT 1
```

## Logging Examples

```
"Site found for host: mysite.com"
"PWA app found for host: myapp.example.com, using associated site 5"
"Serving PWA index page for app 3 on site 5"
"Serving PWA page about for app 3 on site 5"
"Serving PWA page about.html for app 3 on site 5"
"Serving PWA directory index for about for app 3 on site 5"
"Page about not found, serving PWA index page as fallback for app 3 on site 5"
"No site or PWA app found for host: unknown.example.com"
```

## Testing Checklist

- [ ] Deploy a PWA to `public/hosted_pwa/{app_id}/`
- [ ] Create a site with an app that has `pwa_app_url` set
- [ ] Visit the site homepage and verify PWA index.html is served
- [ ] Visit a PWA page and verify it's served correctly
- [ ] Test multi-level resolution (exact file, .html, directory index)
- [ ] Test PWA fallback for non-existent pages (SPA routing)
- [ ] Verify GitHub repository sites still work
- [ ] Verify traditional Prasso sites still work
- [ ] Check logs for proper routing messages
- [ ] Verify masterpage is not applied to PWA sites
- [ ] Test with multiple apps to ensure app_id isolation
- [ ] Test with PWA URLs that have paths (e.g., `https://example.com/app`)
- [ ] Verify site URLs take priority over PWA URLs

## Backward Compatibility

- ✅ Existing GitHub repository sites continue to work unchanged
- ✅ Existing Prasso sites continue to work unchanged
- ✅ PWA serving only activates when `pwa_app_url` is set
- ✅ No breaking changes to existing functionality
- ✅ No changes to existing database schema (except new column)

## Next Steps

1. **Deploy Migration**: Run `php artisan migrate` to add `pwa_app_url` column
2. **Test Routing**: Verify URL routing works as expected
3. **Deploy PWA**: Deploy a test PWA to `public/hosted_pwa/{app_id}/`
4. **Verify Serving**: Test that PWA pages are served correctly
5. **Monitor Logs**: Check logs for routing decisions
6. **Update Clients**: Update mobile clients to use `pwa_app_url` when available

## Related Documentation

- `docs/pwa-app-sites.md` - User guide for PWA site integration
- `docs/pwa-site-serving-implementation.md` - Technical implementation details
- `docs/pwa-url-routing-flow.md` - Complete URL routing flow
- `docs/github-repository-sites.md` - GitHub repository site integration (similar pattern)
- `docs/pwa-implementation-summary.md` - PWA field implementation summary
