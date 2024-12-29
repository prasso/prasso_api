# Site Packages Management

This document describes the site packages functionality in the Prasso API, which allows system administrators to manage feature packages for different sites.

## Overview

The site packages system enables super administrators to:
- Create and manage feature packages (e.g., AutoProHub, Analytics)
- Assign packages to specific sites
- Manage package subscriptions with expiration dates
- Control access to specific features based on package subscriptions

## Database Structure

The system uses two main tables:

### 1. site_packages
Stores the available feature packages:
```sql
- id (primary key)
- name (string)
- slug (string, unique)
- description (text, nullable)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### 2. site_package_subscriptions
Manages the relationship between sites and their subscribed packages:
```sql
- id (primary key)
- site_id (foreign key to sites)
- package_id (foreign key to site_packages)
- subscribed_at (timestamp)
- expires_at (timestamp, nullable)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

## Default Packages

The system comes with three default packages:

1. **AutoProHub**
   - Automotive management features and tools
   - Slug: `autoprohub`

2. **Basic Site**
   - Basic site features including content management
   - Slug: `basic-site`

3. **Advanced Analytics**
   - Enhanced analytics and reporting tools
   - Slug: `advanced-analytics`

## Access Control

- Only super administrators can access the package management interface
- Access is controlled through the `superadmin` middleware
- Package management is available through both web and API interfaces

## Web Interface

Super administrators can access the package management interface through:
1. Navigate to the admin dashboard
2. Click on "Site Packages" under the Sites section
3. Select a site from the dropdown to manage its packages

The interface provides:
- List of all available packages
- Current subscription status for each package
- Subscribe/Unsubscribe buttons
- Expiration dates for active subscriptions

## API Endpoints

All API endpoints are prefixed with `/api/admin/` and require super admin authentication.

### Available Endpoints

1. **List All Packages**
   ```
   GET /api/admin/packages
   ```

2. **Create New Package**
   ```
   POST /api/admin/packages
   Body: {
     "name": "Package Name",
     "description": "Package Description",
     "is_active": true
   }
   ```

3. **Get Site Packages**
   ```
   GET /api/admin/sites/{site}/packages
   ```

4. **Subscribe Site to Package**
   ```
   POST /api/admin/packages/{package}/subscribe
   Body: {
     "site_id": "site_id",
     "expires_at": "2024-12-31" // optional
   }
   ```

5. **Unsubscribe Site from Package**
   ```
   POST /api/admin/packages/{package}/unsubscribe
   Body: {
     "site_id": "site_id"
   }
   ```

## Implementation Details

### Models

1. **SitePackage Model**
   - Manages package data and relationships
   - Includes methods for checking subscription status

2. **Site Model**
   - Extended with package relationship
   - Includes helper method `hasPackage()` to check package access

### Usage in Code

To check if a site has access to a specific package:

```php
if ($site->hasPackage('autoprohub')) {
    // Allow access to AutoProHub features
}
```

## Error Handling

The system includes comprehensive error handling:
- Validation errors for all inputs
- Proper error responses for API endpoints
- Logging of all errors for debugging
- User-friendly error messages in the interface

## Security Considerations

1. All package management routes are protected by:
   - Authentication (`auth` middleware)
   - Super admin authorization (`superadmin` middleware)
   - CSRF protection for web routes
   - Sanctum authentication for API routes

2. Input validation on all endpoints to prevent:
   - Invalid data entry
   - SQL injection
   - Cross-site scripting

## Maintenance

### Adding New Packages

Use the SitePackageSeeder to add new default packages:

```bash
php artisan db:seed --class=SitePackageSeeder
```

### Monitoring

Monitor package usage and subscriptions through:
- Laravel logs (`storage/logs/laravel.log`)
- Database queries on subscription table
- API response monitoring

## Future Enhancements

Planned improvements include:
1. Package usage analytics
2. Automatic expiration notifications
3. Bulk package assignment
4. Package dependency management
5. Custom package configuration options

## Troubleshooting

Common issues and solutions:

1. **Failed to fetch site packages**
   - Verify super admin permissions
   - Check API route accessibility
   - Confirm database connection
   - Review Laravel logs for errors

2. **Package not appearing**
   - Run package seeder
   - Verify package is marked as active
   - Clear application cache

3. **Subscription not updating**
   - Check database permissions
   - Verify CSRF token
   - Confirm proper API authentication
