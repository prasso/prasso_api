# Pull Request: PWA Site Serving with Intelligent URL Routing

## Title

**feat: Add PWA site serving with intelligent URL routing priority**

## Description

### Overview

This PR implements Progressive Web App (PWA) site serving for the prasso_api with intelligent URL routing that prioritizes defined site URLs first, then falls back to PWA app URLs. The implementation follows the same pattern as GitHub repository sites, providing a unified approach to hosting different types of applications.

### Changes Made

#### 1. URL Routing Logic (`app/Http/Controllers/Controller.php`)

**`getClientFromHost()` Method (Lines 103-136)**
- Implements 3-step routing flow:
  1. Check if URL matches a defined site host
  2. If no match, check if URL matches any PWA app URL
  3. If neither, abort 404
- Searches `apps` table for matching `pwa_app_url` using LIKE queries
- Supports PWA URLs with paths (e.g., `https://app.example.com/app`)
- Comprehensive logging for debugging

**`getMasterForSite()` Method (Lines 138-169)**
- Skip masterpage for PWA-hosted sites (lines 149-154)
- Skip masterpage for GitHub repository sites (existing)
- Ensures PWA content is served without Prasso wrapper

#### 2. PWA Page Serving (`app/Http/Controllers/SitePageController.php`)

**`index()` Method (Lines 55-81)**
- Serves PWA app's `index.html` if configured
- Falls back to GitHub repository logic if PWA not configured
- Falls back to traditional Prasso welcome page as last resort

**`viewSitePage()` Method (Lines 374-408)**
- Multi-level PWA page resolution:
  1. Try exact file path
  2. Try with `.html` extension
  3. Try directory index (`index.html`)
  4. Fall through to Prasso's page system

**`viewSitePage()` Fallback (Lines 461-483)**
- Serves PWA's `index.html` as fallback for non-existent pages
- Enables SPA support for client-side routing
- Only applies if page not found in Prasso's SitePages table

### How It Works

#### URL Routing Priority

```
Request arrives with URL
    ↓
getClientFromHost() checks:
    1. Is URL a defined site host? → YES → Serve site
    2. Is URL a PWA app URL? → YES → Serve PWA
    3. Neither? → Abort 404
```

#### Page Serving Priority (for PWA Sites)

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

### Configuration

#### For Site Administrators

1. Create an app associated with your site
2. Set the `pwa_app_url` field (e.g., `https://myapp.example.com`)
3. Deploy your PWA build to `public/hosted_pwa/{app_id}/`
4. Ensure `index.html` exists at the root
5. The site will automatically serve from the PWA

#### Deployment Structure

```
public/hosted_pwa/{app_id}/
├── index.html
├── assets/
│   ├── js/
│   ├── css/
│   └── images/
└── [other PWA files]
```

### Key Features

✅ **Intelligent URL Routing** - Site URLs take priority, PWA URLs are fallback  
✅ **Multi-Level File Resolution** - Exact file → .html → directory index  
✅ **SPA Support** - PWA index.html fallback for client-side routing  
✅ **No Masterpage Wrapping** - PWA sites served without Prasso masterpage  
✅ **Comprehensive Logging** - All routing decisions are logged  
✅ **Backward Compatible** - Existing sites and GitHub repos continue to work  
✅ **Manual Deployment** - No automated deployment (admin responsibility)  

### Database

Uses existing `pwa_app_url` column in `apps` table (added in previous migration):
```sql
ALTER TABLE apps ADD COLUMN pwa_app_url VARCHAR(2048) NULL 
  COMMENT 'URL to the Progressive Web App (PWA) for mobile access';
```

### Logging Examples

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

### Documentation

Created comprehensive documentation:

1. **`docs/pwa-url-routing-flow.md`** - Complete URL routing flow with examples
2. **`docs/pwa-implementation-complete.md`** - Comprehensive overview
3. **`docs/pwa-site-serving-implementation.md`** - Technical implementation details
4. **`docs/DOCUMENTATION_UPDATES.md`** - Change log of documentation updates

Updated existing documentation:

1. **`docs/pwa-implementation-summary.md`** - Added routing and page serving sections
2. **`docs/pwa-app-sites.md`** - Added URL routing priority explanation
3. **`docs/pwa-testing-checklist.md`** - Added URL routing and page serving tests

### Backward Compatibility

- ✅ Existing GitHub repository sites continue to work unchanged
- ✅ Existing Prasso sites continue to work unchanged
- ✅ PWA serving only activates when `pwa_app_url` is set
- ✅ No breaking changes to existing functionality
- ✅ No changes to existing database schema (uses existing column)

### Testing

Comprehensive testing checklist added to `docs/pwa-testing-checklist.md`:

- URL routing tests (site priority, PWA fallback, 404 handling)
- Page serving tests (homepage, multi-level resolution, fallback)
- Logging verification tests
- Masterpage skipping verification
- Edge cases and concurrent updates
- Backward compatibility tests

### Files Modified

1. `app/Http/Controllers/Controller.php` - URL routing and masterpage logic
2. `app/Http/Controllers/SitePageController.php` - PWA page serving logic
3. `docs/pwa-implementation-summary.md` - Updated with routing changes
4. `docs/pwa-app-sites.md` - Updated with URL routing priority
5. `docs/pwa-testing-checklist.md` - Updated with routing and serving tests

### Files Created

1. `docs/pwa-url-routing-flow.md` - URL routing flow documentation
2. `docs/pwa-implementation-complete.md` - Complete implementation overview
3. `docs/pwa-site-serving-implementation.md` - Technical implementation details
4. `docs/DOCUMENTATION_UPDATES.md` - Documentation change log

### Related Issues

This PR implements the PWA site serving feature as part of the PWA migration path from the legacy Flutter-based `prasso_app` mobile client to a modern React-based PWA (`prasso_web`).

### Deployment Notes

1. No database migrations required (uses existing `pwa_app_url` column)
2. No breaking changes to existing functionality
3. PWA deployment is manual (admin responsibility)
4. DNS setup for faxt.com PWA URLs is already implemented
5. Backward compatible with existing sites and GitHub repository sites

### Review Checklist

- [ ] Code follows existing patterns (mirrors GitHub repository site logic)
- [ ] All routing logic is properly tested
- [ ] Logging is comprehensive for debugging
- [ ] Documentation is complete and accurate
- [ ] Backward compatibility is maintained
- [ ] No breaking changes to existing functionality
- [ ] Edge cases are handled (null checks, file existence checks)
- [ ] Performance impact is minimal (LIKE queries on indexed columns)

### Example Scenarios

#### Scenario 1: Traditional Prasso Site
```
Site: mysite.com
  └─ host: "mysite.com"
  └─ app: null (or app without pwa_app_url)
  
Result: Requests to mysite.com are served as traditional Prasso sites
```

#### Scenario 2: PWA App with Separate Domain
```
Site: mysite.com
  └─ host: "mysite.com"
  └─ app: My App
    └─ pwa_app_url: "https://myapp.example.com"
    
Result: 
  - Requests to mysite.com → Traditional Prasso site
  - Requests to myapp.example.com → PWA app
```

#### Scenario 3: PWA App with Site Domain
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

### Questions or Concerns?

Please refer to the comprehensive documentation in the `docs/` folder for detailed information about:
- URL routing flow: `docs/pwa-url-routing-flow.md`
- Complete implementation: `docs/pwa-implementation-complete.md`
- Technical details: `docs/pwa-site-serving-implementation.md`
- Testing procedures: `docs/pwa-testing-checklist.md`
