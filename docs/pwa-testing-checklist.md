# PWA App URL Implementation - Testing Checklist

## Pre-Deployment Testing

### 1. Database Migration
- [ ] Run migration: `php artisan migrate`
- [ ] Verify `pwa_app_url` column exists in `apps` table
- [ ] Confirm column is nullable and accepts URLs up to 2048 characters
- [ ] Test rollback: `php artisan migrate:rollback` and verify column is removed
- [ ] Re-run migration to confirm idempotency

### 2. Model & Fillable Array
- [ ] Verify `pwa_app_url` is in `App\Models\Apps::$fillable`
- [ ] Confirm `pwa_app_url` is NOT in `$hidden` array (should be visible in API responses)
- [ ] Test creating an app with `pwa_app_url` set
- [ ] Test updating an app with `pwa_app_url` set

### 3. Admin Form UI
- [ ] Navigate to app editor (`/team/{teamid}/apps/{appid}`)
- [ ] Verify PWA App URL input field is visible
- [ ] Verify placeholder text displays: "https://app.example.com"
- [ ] Verify helper text displays: "Optional: URL to the Progressive Web App (PWA) for mobile access..."
- [ ] Test entering a valid URL (e.g., `https://prasso-web.example.com`)
- [ ] Test entering an invalid URL and verify validation error appears
- [ ] Test leaving the field blank and verify form saves successfully
- [ ] Test updating an existing app with a PWA URL
- [ ] Verify success message displays after save

### 4. Form Validation
- [ ] Test invalid URL format: `invalid-url` → validation error
- [ ] Test URL exceeding 2048 characters → validation error
- [ ] Test valid HTTPS URL: `https://app.example.com` → accepted
- [ ] Test valid HTTP URL: `http://localhost:3000` → accepted
- [ ] Test empty/null value → accepted (nullable field)

### 5. API Payload Verification
- [ ] Call `GET /api/app/{apptoken}` and verify `pwa_app_url` is in response
- [ ] Call `POST /api/save_app` with `pwa_app_url` and verify it persists
- [ ] Verify `pwa_app_url` appears in `AppsService::getAppSettingsBySite()` output
- [ ] Verify `pwa_app_url` appears in `AppsService::getAppSettingsByUser()` output
- [ ] Verify `pwa_app_url` appears in `AppsService::getAppSettings()` output

### 6. Backward Compatibility
- [ ] Existing apps without `pwa_app_url` set should return `null` in API responses
- [ ] Existing apps should continue to work with `page_url` as fallback
- [ ] Mobile clients should gracefully handle `pwa_app_url` being null

### 7. File Upload Integration
- [ ] Test updating app icon alongside PWA URL in same form submission
- [ ] Verify both icon and PWA URL persist correctly
- [ ] Test form with icon upload but no PWA URL
- [ ] Test form with PWA URL but no icon upload

### 7. DNS Setup for faxt.com Domains
- [ ] Create a new site with PWA URL on faxt.com domain (e.g., `https://app.faxt.com`)
- [ ] Verify DNS setup is called for both site domain and PWA domain
- [ ] Check logs for DNS setup completion messages
- [ ] Test updating an app with a new faxt.com PWA URL
- [ ] Verify DNS setup is triggered only for faxt.com domains, not external URLs
- [ ] Test PWA URL with non-faxt.com domain (e.g., `https://external.com`) - should NOT trigger DNS setup

### 8. URL Routing for PWA Sites
- [ ] Deploy a PWA to `public/hosted_pwa/{app_id}/`
- [ ] Create a site with host `mysite.com` and an app with `pwa_app_url: https://myapp.example.com`
- [ ] Request to `mysite.com` → Verify site is served (not PWA)
- [ ] Request to `myapp.example.com` → Verify PWA is served
- [ ] Request to `unknown.example.com` → Verify 404 is returned
- [ ] Check logs for routing messages:
  - [ ] "Site found for host: mysite.com"
  - [ ] "PWA app found for host: myapp.example.com, using associated site X"
  - [ ] "No site or PWA app found for host: unknown.example.com"
- [ ] Test PWA URL with path: `https://app.example.com/app` → Verify PWA is served
- [ ] Test site URL takes priority over PWA URL (if both match)

### 9. PWA Page Serving
- [ ] Request PWA homepage `/` → Verify `public/hosted_pwa/{app_id}/index.html` is served
- [ ] Request PWA page `/about` → Verify multi-level resolution works:
  - [ ] Try exact file: `hosted_pwa/{app_id}/about`
  - [ ] Try with .html: `hosted_pwa/{app_id}/about.html`
  - [ ] Try directory index: `hosted_pwa/{app_id}/about/index.html`
- [ ] Request non-existent PWA page → Verify PWA fallback serves `index.html`
- [ ] Verify masterpage is NOT applied to PWA sites
- [ ] Check logs for serving messages:
  - [ ] "Serving PWA index page for app X on site Y"
  - [ ] "Serving PWA page about for app X on site Y"
  - [ ] "Page about not found, serving PWA index page as fallback..."

### 10. Edge Cases
- [ ] Test app creation with PWA URL set (via form and API)
- [ ] Test app update removing PWA URL (set to empty/null)
- [ ] Test app update changing PWA URL to different value
- [ ] Test concurrent updates to same app
- [ ] Test PWA URL with subdomain: `https://pwa.myapp.faxt.com`
- [ ] Test PWA URL with port: `https://app.faxt.com:3000` (should extract domain correctly)

## Post-Deployment Verification

### 1. Staging Environment
- [ ] Deploy migration to staging
- [ ] Verify schema changes in staging database
- [ ] Test full admin workflow in staging
- [ ] Verify API responses include `pwa_app_url` in staging

### 2. Production Readiness
- [ ] Backup production database before migration
- [ ] Run migration in production
- [ ] Verify no errors in application logs
- [ ] Spot-check a few apps to confirm `pwa_app_url` is null/empty as expected
- [ ] Test admin form with a test app in production

## Notes
- The `pwa_app_url` field is optional and does not break existing functionality
- Clients should be updated to use `pwa_app_url` if present, otherwise fall back to `page_url`
- This change is backward compatible with existing mobile apps
