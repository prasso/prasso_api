# Prasso Admin PWA Support Plan

## Objective
Add Progressive Web App (PWA) support within the Prasso admin app editor so teams can configure and deliver a web-based mobile experience as an alternative to the legacy `prasso_app` mobile client.

## Pre-Work
- Verify the `apps` table schema and confirm no existing `pwa_app_url` or similar column exists.
- Decide on API response strategy: always include `pwa_app_url` (null if not set), only include if set, or version the API response.

## Workstreams

### 1. Schema Updates
- Add a nullable `pwa_app_url` column to the `apps` table via migration with appropriate length/indexing considerations.
- Update the `App\Models\Apps` model `$fillable` array to include `pwa_app_url`.
- Verify `pwa_app_url` is **not** added to the `$hidden` array (should be visible in API responses).
- Note migration requirements in deployment checklist.

### 2. Admin Editor UI Enhancements
- **Blocker**: Update `Apps::processUpdates()` to handle the `pwa_app_url` field in both create and update paths.
- Extend the Livewire `AppInfoForm` component to bind and validate the PWA URL (`nullable|url|max:2048`).
- Update the Blade view (`resources/views/livewire/apps/app-info-form.blade.php`) to surface an input field with helper text explaining PWA usage.
- Add messaging/tooltips clarifying that PWA URLs complement existing site URLs.

### 3. API & Service Adjustments
- Update `App\Services\AppsService` to include the PWA URL in JSON payloads wherever `site_url` equivalents appear.
- Audit API controllers/resources that serialize app data to ensure the new field is returned to web/mobile clients and not filtered out.
- Confirm Sanctum or other authentication flows continue to work when clients request the PWA URL.

### 4. Compatibility & Testing
- Execute database migration against staging and confirm rollbacks.
- Validate Livewire form interactions (create/update) including file uploads alongside the new URL field.
- Smoke-test client boot payloads to ensure the PWA URL appears and is correctly mapped in consuming apps.
- Document manual test steps for QA and consider adding feature tests covering the new field.

### 5. Documentation & Rollout
- Update existing admin app documentation to describe how to configure PWA URLs.
- Communicate deprecation of `prasso_app` mobile client and highlight migration path to the React-based `prasso_web` PWA.
- Coordinate release notes and support guidance for customers adopting the new workflow.
