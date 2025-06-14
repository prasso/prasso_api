# Site Creation Wizard Guide

This guide will walk you through the process of creating a new site and app using the Site Creation Wizard.

## Overview

The Site Creation Wizard is a step-by-step process that helps you:
1. Set up basic site information
2. Configure site features
3. Customize the appearance
4. Review and finalize your site creation

## Step-by-Step Instructions

### Step 1: Basic Information

In the first step, you'll need to provide:
- Site Name: Enter a name for your site
- Description: Provide a brief description of your site
- Host: This will be automatically generated based on your site name (yoursite.prasso.io)

### Step 2: Feature Configuration

Select which features you want to enable for your site:
- User Registration: Toggle whether users can register on your site
- Subteams: Enable if you want to support team subdivisions
- Livestreaming: Enable if your site will include livestreaming capabilities
- Invitation Only: Set whether site access is by invitation only

### Step 3: Site Customization

Customize your site's appearance:
- Main Color: Choose your site's primary color theme
- Logo Image: Upload your site's logo (prepare this image file in advance and upload it to your server)
  - The logo should be in a web-compatible format (PNG, JPG)
  - Upload the logo file to your server before starting the wizard
  - You'll need the URL path to your uploaded logo
- Image Folder: Specify a folder for your site's images (automatically generated based on site name)
- GitHub Repository: Optionally specify a GitHub repository in the format `username/repository` (e.g., `prasso/website-template`)
  - When specified, site pages will be sourced from this repository instead of being configured manually
  - The repository must follow specific structure requirements:
    - Create a `pages` directory at the root level of your repository
    - Name files according to the URL path they should serve (e.g., `pages/about.html` will be served at `/about`)
    - Create `pages/index.html` for the site's homepage
    - Store assets in an `assets` directory
    - Optional templates can be stored in a `templates` directory
  - Supported file types include HTML, Markdown, text, CSS, JavaScript, and common image formats
  - See [GitHub Repository Sites](github-repository-sites.md) for detailed information
- Custom CSS: Add any custom CSS styling (optional)
- Custom JavaScript: Add any custom JavaScript functionality (optional)

### Step 4: Review and Create

In the final step:
1. Review all your site settings
2. Confirm the host name is correct
3. Prepare your favicon:
   - Visit [favicon.io](https://favicon.io/favicon-converter/) to generate your favicon files
   - Upload the generated files to your server before proceeding
   - Use the path to your main `favicon.ico` file in the favicon input field
4. Click "Create Site" to finalize your site creation

## After Creation

Once your site is created:
- Your logo will be uploaded and processed
- If livestreaming was enabled, the necessary settings will be configured
- You'll be set as the site administrator
- Your site will be immediately accessible at your chosen domain (yoursite.prasso.io)

### GitHub Repository Integration

If you specified a GitHub repository:
- The manual site pages editor will be automatically disabled
- Content updates will be managed through GitHub's version control system
- Changes to the repository will be automatically reflected on the site
- You can leverage Git's version control for content management
- Multiple contributors can work on the site content through GitHub
- You can use pull requests to review content changes before they go live

## Important Notes

- All site names will automatically be converted to lowercase and spaces will be removed for the host name
- The host name will automatically append '.prasso.io' if not included
- Logo images should be in a web-compatible format (PNG, JPG, etc.)
- The system automatically creates necessary team and user permissions

### GitHub Repository Limitations

When using a GitHub repository for site content:
- The manual site pages editor is disabled
- Site page data templates cannot be used
- Custom site page types (S3, URL) are not supported
- Dynamic content processing is limited to standard site tags

### GitHub Repository Best Practices

1. Use a `main` or `production` branch for live content
2. Make changes in a development branch before merging to production
3. Include a README.md file with instructions for contributors
4. Create reusable templates for consistent page structure
5. Organize assets in subdirectories by type (images, css, js)

For additional support or questions, please contact the system administrator.
