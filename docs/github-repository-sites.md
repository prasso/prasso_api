# GitHub Repository Site Integration

## Overview

The GitHub Repository integration allows site administrators to connect a site directly to a GitHub repository. When enabled, the site's pages will be sourced directly from the repository rather than being manually configured through the admin interface.

## How It Works

When a GitHub repository is specified for a site:

1. Site pages are sourced directly from the repository content
2. The manual site pages editor is automatically disabled
3. Content updates are managed through GitHub's version control system
4. Changes to the repository are automatically reflected on the site

## Configuration

### Setting Up a GitHub Repository Site

1. Navigate to the Site Editor in the admin dashboard
2. Create a new site or edit an existing one
3. In the GitHub Repository field, enter the repository in the format `username/repository`
   - Example: `prasso/website-template`
4. Save the site configuration

### Repository Structure Requirements

Your GitHub repository should follow these conventions:

- **Pages Directory**: Create a `pages` directory at the root level of your repository
- **File Naming**: Name files according to the URL path they should serve
  - Example: `pages/about.html` will be served at `/about`
- **Index Page**: Create `pages/index.html` for the site's homepage
- **Assets**: Store assets in an `assets` directory
- **Templates**: Optional templates can be stored in a `templates` directory

### Supported File Types

- HTML files (`.html`, `.htm`)
- Markdown files (`.md`)
- Text files (`.txt`)
- CSS files (`.css`)
- JavaScript files (`.js`)
- Image files (`.jpg`, `.jpeg`, `.png`, `.gif`, `.svg`)

## Benefits

- **Version Control**: Leverage Git's version control for content management
- **Collaborative Editing**: Multiple contributors can work on the site content
- **CI/CD Integration**: Integrate with GitHub Actions for automated testing and deployment
- **Code Review**: Use pull requests to review content changes before they go live
- **Rollback Capability**: Easily revert to previous versions if needed

## Limitations

When using a GitHub repository for site content:

- The manual site pages editor is disabled
- Site page data templates cannot be used
- Custom site page types (S3, URL) are not supported
- Dynamic content processing is limited to standard site tags

## Best Practices

1. **Branch Management**: Use a `main` or `production` branch for live content
2. **Development Branch**: Make changes in a development branch before merging to production
3. **README**: Include a README.md file with instructions for contributors
4. **Templates**: Create reusable templates for consistent page structure
5. **Assets Organization**: Organize assets in subdirectories by type (images, css, js)

## Technical Implementation

The system handles GitHub repository sites by:

1. Cloning the repository to a local cache
2. Periodically pulling updates from the repository
3. Serving files directly from the cached repository
4. Processing HTML files to replace site tags and variables
5. Mapping repository paths to site URLs

## Troubleshooting

If your GitHub repository site is not displaying correctly:

1. Verify the repository URL is correct and the repository is public
2. Check that the repository structure follows the required conventions
3. Ensure the necessary files exist in the repository
4. Review the system logs for any errors related to repository access
5. Confirm that GitHub API access is working properly

For additional support or questions, please contact the system administrator.
