# Apps and Tabs: Supported Actions

Below is a concise reference of the actions this application supports around “apps” and “tabs”, including web routes, API endpoints, access control, models, and the configuration returned to clients.

## Overview

- **Apps** are top-level containers that belong to a `Team` and a `Site` and have many `Tabs` (`app/Models/Apps.php`).
- **Tabs** are navigational items within an App, ordered via `sort_order` and optionally restricted by role (`app/Models/Tabs.php`).
- **Role-based tabs** are filtered for end users via `App\Services\AppsService::getAppSettingsBySite()`.

## Web Actions (Instructor-only)

Routes are under the instructor middleware group (`routes/web.php`):

- **List apps for a team**
  - `GET /team/{teamid}/apps` → `TeamController@index()` → view `apps.show`
  - Shows apps, ownership teams, and the active app.

- **New Site + App wizard**
  - `GET /team/{teamid}/apps/newsiteandapp` → `TeamController@newSiteAndApp()` → view `apps.new-site-wizard`

- **Edit an app**
  - `GET /team/{teamid}/apps/{appid}` → `TeamController@editApp()` → view `apps.edit-app`
  - If `appid == 0` or not found, loads a blank app (no tabs yet).

- **Activate an app for mobile login**
  - `GET /team/{teamid}/apps/{appid}/activate` → `TeamController@activateApp()`
  - Updates `UserActiveApp` for the logged-in user and redirects back to the app list.

- **Upload app icon**
  - `POST /team/{teamid}/apps/{appid}/app_logo_image` → `TeamController@uploadAppIcon()`
  - Validates and stores to S3 using `constants.APP_LOGO_PATH` and `constants.CLOUDFRONT_ASSET_URL`.

- **Edit an existing tab**
  - `GET /team/{teamid}/apps/{appid}/tabs/{tabid}` → `TeamController@editTab()` → view `apps.edit-tab`

- **Add a new tab**
  - `GET /team/{teamid}/apps/{appid}/tabs/new` → `TeamController@addTab()` → view `apps.edit-tab` with default tab

- **Delete an app**
  - `GET /team/{teamid}/apps/{appid}/delete` → `TeamController@deleteApp()`

- **Delete a tab**
  - `GET /team/{teamid}/apps/{appid}/tabs/{tabid}/delete` → `TeamController@deleteTab()`

## API Endpoints

- **Save app**
  - `POST /api/save_app` → `Api\AppController@saveApp()`
  - Persists via `AppsService::saveApp($request)` → `Apps::create($request->all())`
  - Returns `sendResponse($success, 'App saved.')`
  - Auth: This route is not gated by `auth:sanctum` in `routes/api.php` as currently written. If you intend it to be protected, wrap with Sanctum middleware.

- Related Auth/User endpoints (used by mobile/app initialization)
  - `POST /api/register`, `POST /api/login`, `POST /api/logout`
  - `POST /api/save_user/{user}` and `POST /api/app/{apptoken}` (delegated to `Api\AuthController`)

## Configuration Returned to Client

- **Core entrypoint**
  - `UserService::buildConfigReturn($user, $appsService, $site)` builds the app boot payload:
    - `token`: Creates or reuses a Sanctum token.
    - `name`, `uid`, `email`, `photoURL`.
    - `thirdPartyToken`: from `getThirdPartyToken()`.
    - `roles`: JSON-encoded roles array (hidden fields stripped).
    - `personal_team_id`, `team_coach_id`, `coach_uid`.
    - `team_members`: If the user is an instructor on the `Site`, includes team members via `Instructor::getTeamMembersFor()`.
    - `app_data`: JSON returned from `AppsService::getAppSettingsBySite()`.

- **Role-based tab filtering**
  - `AppsService::getAppSettingsBySite(Site $site, $user, $user_access_token)`:
    - No roles: loads `Apps::with('nullroletabs')`.
    - Instructor/admin roles: loads `Apps::with('instructorroletabs')->union(nullroletabs)`.
    - Replaces placeholders with actual values:
      - Replaces `constants.THIRD_PARTY_TOKEN` with user’s `thirdPartyToken`.
      - Replaces `constants.TEAM_ID` with `current_team_id`.
      - Replaces `constants.CSRF_HEADER` with `csrf_token()`.

- **Alternate getters**
  - `getAppSettingsByUser(User $user)`: uses `UserActiveApp` or user’s current team to select app and returns `apps + tabs + team + activeApp` as JSON.
  - `getAppSettings(string $apptoken)`: resolves `User` by personal access token, then returns `team_owner[0]`’s app with tabs as JSON.

## Models and Persistence

- **Apps model** (`app/Models/Apps.php`)
  - Fillable: `team_id, site_id, appicon, app_name, page_title, page_url, sort_order, user_role`
  - Relations:
    - `tabs()` → hasMany `Tabs` ordered by `sort_order`
    - `instructorroletabs()` → Tabs with `team_role = INSTRUCTOR` OR `team_role = null AND restrict_role=false` UNION `nullroletabs()`
    - `nullroletabs()` → Tabs where `team_role IS NULL`
    - `team()` → belongsTo `Team`
    - `activeApp()` → hasOne `UserActiveApp`
  - Mutations:
    - `processUpdates($appModel)` → create/update app and return a success message.

- **Tabs model** (`app/Models/Tabs.php`)
  - Fillable: `id, app_id, icon, label, page_url, page_title, request_header, sort_order, parent, restrict_role`
  - Defaults in `__construct()` via `config('constants.*')` for id, app_id, sort, parent, icon, url, header, restrict_role=false.
  - Persistence:
    - `processUpdates($tab_data)` → `updateOrCreate` by `id`.
  - Relationship: `app()` → belongsTo `Apps`.

## UI Components (Livewire)

- **App edit form** (`app/Livewire/Apps/AppInfoForm.php`)
  - Validates and updates app (including S3 icon upload if provided).
  - Uses `Apps::processUpdates($this->teamapp->toArray())`.

- **Tab edit form** (`app/Livewire/Apps/TabInfoForm.php`)
  - If `app_id` is missing/0 (e.g., adding a tab to a new app from “new” context), it first creates a minimal `Apps` record, then persists the tab via `Tabs::processUpdates()`.
  - Redirects back to the app after save.

- **App manager wrapper** (`app/Livewire/Apps/AppManager.php`)
  - Renders `livewire.apps.app-manager` with supplied `$team` and `$teamapp`.

## Access Control and Auth

- **Instructor-only web actions** use middleware:
  - `auth:sanctum`, `verified`, `instructorusergroup` (`routes/web.php`).
- **Superadmin-only web actions** use:
  - `auth:sanctum`, `verified`, `superadmin`.
- **API auth**:
  - Many API endpoints use Sanctum or are public by design (`routes/api.php`).
  - The mobile/app boot flow relies on issuing a personal access token in `UserService::buildConfigReturn()`.

## Notable Business Rules

- **Active App**: `TeamController@activateApp()` sets which app a user's mobile session uses via `UserActiveApp::processUpdates($user->id, $appid)`.
- **Tabs visibility**: Driven by role-aware relations in `Apps` and resolved by `AppsService::getAppSettingsBySite()`.
- **Placeholders in app JSON**: Token, team, and CSRF placeholders are replaced before returning to clients.

## Summary

- Documented all supported actions for apps and tabs, the API endpoint to save apps, and how the client configuration is assembled and filtered by roles.
