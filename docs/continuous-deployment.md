# Continuous Deployment Setup

This document outlines the continuous deployment (CD) setup for the Prasso API project, which automatically deploys code to the production server when changes are merged into the `master` branch.

## Overview

The deployment pipeline is configured using GitHub Actions and includes the following steps:

1. Triggered on push to `master` branch
2. Sets up PHP and required extensions
3. Installs project dependencies
4. Connects to the production server via SSH
5. Deploys the application
6. Runs database migrations
7. Optimizes the application
8. Restarts necessary services

## Prerequisites

Before setting up the deployment, ensure the following:

### Server Requirements

- Linux server with SSH access
- Git
- PHP 8.2+ with required extensions
- Composer
- Nginx or Apache web server
- MySQL/MariaDB
- Proper file permissions set for the web server user

### GitHub Repository Settings

1. Go to your GitHub repository
2. Navigate to Settings > Secrets > Actions
3. Add the following repository secrets:
   - `SSH_PRIVATE_KEY`: Private SSH key for server authentication
   - `SERVER_IP`: IP address of your production server
   - `SERVER_USER`: SSH username for the server
   - `DEPLOY_PATH`: Full path to the project directory on the server (e.g., `/var/www/prasso-api`)

## Workflow File

The deployment is configured in `.github/workflows/deploy.yml`. This file contains all the necessary steps to build and deploy the application.

## Deployment Process

1. **Checkout Code**: The workflow checks out the latest code from the `master` branch.
2. **Setup PHP**: Configures PHP with required extensions.
3. **Install Dependencies**: Installs PHP dependencies using Composer.
4. **SSH Setup**: Configures SSH access to the server.
5. **Deployment Script**: The following actions are performed on the server:
   - Pulls the latest changes from the repository
   - Installs production dependencies
   - Runs database migrations
   - Clears and caches configuration, routes, and views
   - Sets proper file permissions
   - Restarts the web server and PHP-FPM

## Manual Deployment

If you need to manually trigger a deployment:

1. Push changes to the `master` branch
2. Or manually trigger the workflow from the GitHub Actions tab

## Rollback Procedure

In case of a failed deployment:

1. Revert the problematic commit(s)
2. Push the revert to the `master` branch
3. The deployment will automatically trigger with the previous working version

Alternatively, you can manually SSH into the server and:

```bash
cd /path/to/deployment
# Revert to a previous commit
git reset --hard <previous-commit-hash>

# Run post-deployment commands
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

## Troubleshooting

### Common Issues

1. **Permission Denied** errors:
   - Ensure the web server user has proper permissions on the deployment directory
   - Run: `chown -R www-data:www-data /path/to/deployment`
   - Run: `chmod -R 775 storage bootstrap/cache`

2. **SSH Connection Issues**:
   - Verify the SSH private key is correctly set in GitHub secrets
   - Check if the server's IP address is correct
   - Ensure port 22 is open in the server's firewall

3. **Composer Dependencies**:
   - If dependencies fail to install, check the PHP version and required extensions

### Checking Logs

1. **GitHub Actions Logs**:
   - Go to the "Actions" tab in your GitHub repository
   - Click on the failed workflow run
   - Review the logs for errors

2. **Server Logs**:
   - Check Laravel logs: `tail -f storage/logs/laravel.log`
   - Check PHP-FPM logs: `journalctl -u php8.2-fpm -f`
   - Check web server logs: `journalctl -u nginx -f` or `journalctl -u apache2 -f`

## Security Considerations

1. **Environment Variables**:
   - Never commit `.env` files to version control
   - Ensure sensitive information is stored in GitHub Secrets

2. **SSH Keys**:
   - Use a dedicated deployment user with limited permissions
   - Regularly rotate SSH keys

3. **Dependencies**:
   - Keep all dependencies up to date
   - Review dependency updates for security vulnerabilities

## Maintenance

- Regularly monitor deployment success/failure
- Keep the deployment scripts and documentation up to date
- Review and update server requirements as needed

## Support

For any issues with the deployment process, please contact the development team or refer to the [GitHub Actions documentation](https://docs.github.com/en/actions).
