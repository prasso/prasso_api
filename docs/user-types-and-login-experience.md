# Prasso User Types and Post-Login Experience

Based on the documentation and codebase analysis, Prasso has **three distinct user types** with different access levels and capabilities:

## 1. **Regular User (App User)**
- **Role ID**: No specific role required (anyone with login)
- **Authentication Guard**: `web` (standard Laravel authentication)
- **Model**: `App\Models\User`

### What Regular Users See After Login:
- **Dashboard**: Basic site landing page (`/dashboard` route redirects to site index)
- **Limited Access**: Can view public site pages and content
- **Team Membership**: Can be invited to teams as members (not owners)
- **App Usage**: Can use mobile/web apps if they're team members
- **Profile Management**: Basic profile editing capabilities
- **No Administrative Functions**: Cannot create sites, manage teams, or access admin panels

## 2. **Instructor (Site Admin)**
- **Role ID**: 2 (`INSTRUCTOR` constant)
- **Role Name**: `site-admin`
- **Authentication Guard**: `instructor` 
- **Model**: `App\Models\Instructor` (extends User)

### What Instructors See After Login:
- **Site Management Dashboard**: Access to create and manage their own sites
- **Team Administration**: 
  - Create and manage teams
  - Invite users to their teams
  - Manage team members and roles
- **Site Creation**: "Create a new Site and App" option when they log in
- **Content Management**:
  - Create and edit site pages
  - Manage site page data and templates
  - Upload and manage images
- **App Building**:
  - Configure mobile/web apps
  - Create and arrange app tabs
  - Set app icons and branding
- **Site Customization**:
  - Edit site appearance and branding
  - Manage site settings
  - Configure data templates
- **Team Communication**: Send messages to team members
- **Filament Admin Panel**: Access to `site-admin` panel for their own sites
- **Scoped Access**: Can only manage sites and teams they own or are associated with

## 3. **Super Admin**
- **Role ID**: 1 (`SUPER_ADMIN` constant)
- **Role Name**: `super-admin`
- **Authentication Guard**: `superadmin`
- **Model**: `App\Models\SuperAdmin`

### What Super Admins See After Login:
- **Global Administrative Access**: Can access any site admin area
- **System-Wide Management**:
  - Manage all sites across the platform
  - Access all teams and users
  - View and edit any content
- **Advanced Site Management**:
  - Create and manage site page data templates
  - Access TSV data import functionality
  - Manage JSON data for site pages
  - Configure site packages
- **User Management**: Update any user's profile and permissions
- **Platform Administration**:
  - Access to both `admin` and `site-admin` Filament panels
  - Manage system-wide settings
  - Override all access restrictions
- **Developer Tools**: Access to advanced technical features and debugging tools

## Key Differences in Access:

### Navigation and Menus:
- **Regular Users**: Basic navigation, primarily consumption-focused
- **Instructors**: Administrative menus for their sites/teams, content creation tools
- **Super Admins**: Full administrative interface with system-wide controls

### Dashboard Content:
- **Regular Users**: Site content and basic profile options
- **Instructors**: Site management options, team administration, app building tools
- **Super Admins**: Platform-wide statistics, system management, all user/site controls

### Permissions:
- **Regular Users**: Read-only access to assigned content
- **Instructors**: Full control over their own sites, teams, and associated content
- **Super Admins**: Unrestricted access to all platform features and data

## Technical Implementation Details:

### Authentication Configuration:
The system uses Laravel's multi-guard authentication system defined in `config/auth.php`:
- `web` guard for regular users
- `instructor` guard for site admins
- `superadmin` guard for super administrators

### Role Constants:
Defined in `config/constants.php`:
- `INSTRUCTOR` = 2
- `SUPER_ADMIN` = 1
- `INSTRUCTOR_ROLE_TEXT` = 'site-admin'
- `SUPER_ADMIN_ROLE_TEXT` = 'super-admin'

### Route Protection:
Routes are protected by middleware groups:
- `instructorusergroup` middleware for instructor-level access
- `superadmin` middleware for super admin access
- Standard `auth` middleware for basic authenticated access

### Filament Admin Panels:
The system provides two Filament admin panels:
- `admin` panel: Accessible only by Super Admins
- `site-admin` panel: Accessible by Instructors and Super Admins

The system uses role-based access control to ensure each user type sees only the appropriate interface and functionality for their permission level.

## Granting User Access to Team Site-Admin Pages

### Process for Adding Users to Teams with Site-Admin Access:

1. **Team Owner/Administrator Access Required**:
   - Only team owners or existing administrators can invite new users to teams
   - Super Admins can manage any team

2. **Inviting Users to Teams**:
   - Navigate to Team Management section
   - Click "Invite User" or "Add Team Member"
   - Enter the user's email address
   - Select role: "Admin" (for site-admin access) or "User" (for basic access)

3. **Role Assignment Process**:
   - When a user accepts the invitation, the system automatically assigns the appropriate role:
     - **Admin role** → `INSTRUCTOR` role (ID: 2) → Site-admin access
     - **User role** → Basic user role (ID: 1) → Limited access
   - Role assignment is stored in the `user_role` table

4. **Site-Admin Access Activation**:
   - Users with `INSTRUCTOR` role gain access to:
     - Site-admin Filament panel for their team's sites
     - Team management functions
     - Site content creation and editing
     - App configuration for their team's sites
   - Access is scoped to sites owned by their team

5. **Technical Implementation**:
   - Role assignment happens in `AcceptInvitation` action
   - Uses `UserRole::create()` to assign role to user
   - Authentication guard: `instructor` for site-admin users
   - Middleware: `instructorusergroup` protects admin routes

### Important Notes:
- Users must accept their team invitation to gain access
- Site-admin access is team-specific (users only see sites owned by their teams)
- Super Admins bypass all team restrictions and have global access
- Role changes require re-invitation or direct database modification by Super Admins
