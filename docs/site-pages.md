# Documentation for Site Pages

## Overview
The `site_pages` table manages different types of pages that can be displayed on the site. Each page can be one of three types, allowing for flexible content management and integration with external resources.

> **Important Note**: If a site is configured to use a GitHub repository (via the `github_repository` field in the site settings), the site pages functionality described in this document will be disabled. In that case, all site content will be sourced directly from the GitHub repository. For details on GitHub repository integration, see [GitHub Repository Sites](github-repository-sites.md).

## Page Types

### 1. HTML Content (Type 1 - Default)
- **Description**: Standard HTML content stored directly in the database
- **Storage**: Content is stored in the `description` column
- **Use Case**: Best for simple, static content that doesn't change frequently

### 2. S3 File (Type 2)
- **Description**: Content loaded from an S3 bucket
- **Storage**: File path follows the pattern: `sites/{site_id}/pages/{page_name}.html`
- **Fallback**: If S3 content is not found, falls back to HTML content
- **Use Case**: For large files or when you want to manage content externally

### 3. External URL (Type 3)
- **Description**: Redirects to an external URL
- **Configuration**: Requires `external_url` field to be set
- **Fallback**: If external_url is not set, falls back to HTML content (Type 1)
- **Use Case**: For integrating external applications or microsites

## Site Editor Interface

The site editor provides a user-friendly interface for managing page types:

1. **Type Selection**:
   - A dropdown menu allows selection between all three page types
   - The type field defaults to Type 1 (HTML Content) for new pages

2. **Dynamic Fields**:
   - The interface adapts based on the selected type
   - For Type 3 (External URL), an additional field appears to enter the URL
   - For Type 1 and 2, the standard content editing fields are shown

## Database Schema

The `site_pages` table includes the following columns:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigInteger | Primary key |
| `fk_site_id` | bigInteger | Foreign key to sites table |
| `section` | string | Page identifier/slug |
| `title` | string | Page title |
| `type` | tinyInteger | 1=HTML, 2=S3 File, 3=URL |
| `description` | text | HTML content (for type 1) |
| `external_url` | string | URL for redirect (for type 3) |
| `url` | string | Legacy URL field |
| `headers` | text | Custom HTTP headers |
| `masterpage` | string | Wrapper template name |
| `template` | string | Content template name |
| `style` | text | Custom CSS styles |
| `login_required` | boolean | Whether authentication is required |
| `user_level` | boolean | Whether admin access is required |
| `where_value` | string | Template query filter value |
| `page_notifications_on` | boolean | Whether to notify admin on changes |
| `menu_id` | bigInteger | Parent menu item ID |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

## Content Processing

### The `getPage` Method
This method in `SitePageController` determines how to handle each page type:

1. **Type 1 (HTML)**: Serves content directly from the database
2. **Type 2 (S3)**: Fetches content from S3, falls back to HTML if not found
3. **Type 3 (URL)**: Performs a 302 redirect to the specified URL

### The `prepareTemplate` Function
Processes HTML content, replacing placeholders with dynamic data:
- Processes template tags
- Handles Livewire components
- Integrates site and user-specific information

## Best Practices

1. **For Static Content**: Use Type 1 (HTML) for simple pages
2. **For Large/Managed Files**: Use Type 2 (S3) for better performance
3. **For External Integration**: Use Type 3 (URL) to link to external applications
4. **Fallback Handling**: Always provide fallback content for S3 pages
5. **Error Logging**: Check logs for any S3 access issues or missing URLs

---

## Supported Tags and Their Descriptions

### 1. `CSRF_TOKEN`
**Description**: Replaces the `CSRF_TOKEN` tag with the CSRF token for the current session. This is useful for including secure forms that require CSRF protection.

**Usage**:
```html
<input type="hidden" name="_token" value="CSRF_TOKEN">
```

**Replacement**:
```php
$page_content = str_replace('CSRF_TOKEN', csrf_token(), $page_content);
```

### 2. `MAIN_SITE_COLOR`
**Description**: Replaces `MAIN_SITE_COLOR` with the main color configured for the site.

**Usage**:
```html
<style>
    body {
        background-color: MAIN_SITE_COLOR;
    }
</style>
```

**Replacement**:
```php
$page_content = str_replace('MAIN_SITE_COLOR', $this->site->main_color, $page_content);
```

### 3. `SITE_MAP`
**Description**: Replaces `SITE_MAP` with the generated site map list. This can be used for dynamic navigation.

**Usage**:
```html
<nav>
    SITE_MAP
</nav>
```

**Replacement**:
```php
$page_content = str_replace('SITE_MAP', $this->site->getSiteMapList($path), $page_content);
```

### 4. `SITE_NAME`
**Description**: Replaces `SITE_NAME` with the configured name of the site.

**Usage**:
```html
<h1>Welcome to SITE_NAME</h1>
```

**Replacement**:
```php
$page_content = str_replace('SITE_NAME', $this->site->site_name, $page_content);
```

### 5. `SITE_LOGO_FILE`
**Description**: Replaces `SITE_LOGO_FILE` with the path to the site's logo image.

**Usage**:
```html
<img src="SITE_LOGO_FILE" alt="Site Logo">
```

**Replacement**:
```php
$page_content = str_replace('SITE_LOGO_FILE', $this->site->logo_image, $page_content);
```

### 6. `SITE_FAVICON_FILE`
**Description**: Replaces `SITE_FAVICON_FILE` with the path to the site's favicon.

**Usage**:
```html
<link rel="icon" href="SITE_FAVICON_FILE" type="image/png">
```

**Replacement**:
```php
$page_content = str_replace('SITE_FAVICON_FILE', $this->site->favicon, $page_content);
```

### 7. `SITE_DESCRIPTION`
**Description**: Replaces `SITE_DESCRIPTION` with the site description text.

**Usage**:
```html
<meta name="description" content="SITE_DESCRIPTION">
```

**Replacement**:
```php
$page_content = str_replace('SITE_DESCRIPTION', $this->site->description, $page_content);
```

### 8. `PAGE_NAME`
**Description**: Replaces `PAGE_NAME` with the title of the current page.

**Usage**:
```html
<title>PAGE_NAME</title>
```

**Replacement**:
```php
$page_content = str_replace('PAGE_NAME', $pageToProcess->title, $page_content);
```

### 9. `PAGE_SLUG`
**Description**: Replaces `PAGE_SLUG` with the section or slug identifier of the page.

**Usage**:
```html
<span>Page identifier: PAGE_SLUG</span>
```

**Replacement**:
```php
$page_content = str_replace('PAGE_SLUG', $pageToProcess->section, $page_content);
```

### 10. `[CAROUSEL_COMPONENT]`
**Description**: A special placeholder that loads a Livewire component dynamically into the page. When `[CAROUSEL_COMPONENT]` is detected, it is replaced by a `<div>` element and an associated script that loads the Livewire component via AJAX.

**Usage**:
```html
<div>[CAROUSEL_COMPONENT]</div>
```

**Replacement**:
```php
if (strpos($page_content, '[CAROUSEL_COMPONENT]') !== false) {
    $page_content = str_replace(
        '[CAROUSEL_COMPONENT]',
        '<div id="carouseldiv"></div>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadLivewireComponent("prasso-flipper", "carouseldiv", '.$pageToProcess->id.');
        });</script>', 
        $page_content
    );
}
```

**Editing the Carousel Component**:
To edit the carousel component, follow these steps:

1. **Navigate to the Site Pages List**: Go to the list of site pages in your application.
2. **Select the Page**: Find the page that contains the `[CAROUSEL_COMPONENT]` placeholder.
3. **Open the Custom Editor**: Click on the link to open the custom editor for the page. The URL format for the editor is:
   ```
   /sitepages/{site_page_id}/{page_id}/edit-site-page-json-data
   ```
   Replace `{site_page_id}` and `{page_id}` with the appropriate IDs for the site page you want to edit.
4. **Edit the JSON Data**: In the custom editor, you will see the JSON data for the carousel component. Modify the JSON data to update the content and appearance of the carousel slides.
5. **Save Changes**: Save your changes in the custom editor. The updated carousel component will be rendered on the page with the new content.

### 11. User-Specific Tags
These tags are replaced if a user is authenticated:
- `USER_NAME`: Replaces with the authenticated user's name.
- `USER_EMAIL`: Replaces with the authenticated user's email.
- `USER_PROFILE_PHOTO`: Replaces with the URL of the authenticated user's profile photo.

**Usage**:
```html
<p>Welcome, USER_NAME!</p>
<p>Email: USER_EMAIL</p>
<img src="USER_PROFILE_PHOTO" alt="Profile Photo">
```

**Replacements**:
```php
$page_content = str_replace('USER_NAME', $user->name, $page_content);
$page_content = str_replace('USER_EMAIL', $user->email, $page_content);
$page_content = str_replace('USER_PROFILE_PHOTO', $user->getProfilePhoto(), $page_content);
```

### 12. Site-Specific Tags
These tags are replaced to include details specific to the current site context and the data being processed:
- `[SITE_CSS]`: Replaces with the custom CSS defined for the current site.
- `[SITE_ID]`: Replaces with the unique ID of the current site.
- `[DATA_PAGE_ID]`: Replaces with the unique ID of the specific page being processed.
- `[TEAM_ID]`: Replaces with the unique ID of the team associated with the current site.
- `[HEADER]`: A site_page with title of [HEADER] can be created. The description of the site_page replaces the [HEADER] tag in the page content.

**Usage**:
```html
<p>Site Identifier: SITE_ID</p>
<p>Page Identifier: DATA_PAGE_ID</p>
<p>Team Identifier: TEAM_ID</p>
```

**Replacements**:
```php
$page_content = str_replace('[SITE_ID]', $this->site->id, $page_content);
$page_content = str_replace('[DATA_PAGE_ID]', $pageToProcess->id, $page_content);
$page_content = str_replace('[TEAM_ID]', $this->site->teamFromSite()->id, $page_content);
```

These replacements ensure that pages dynamically include contextual information about the site, the processed data page, and the associated team.
---

## Example Page Content Before Processing
```html
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to PAGE_NAME</title>
    <link rel="icon" href="SITE_FAVICON_FILE">
</head>
<body>
    <h1>Welcome to SITE_NAME</h1>
    <div>[CAROUSEL_COMPONENT]</div>
    <p>User: USER_NAME</p>
</body>
</html>
```

## Example Page Content After Processing
```html
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Home Page</title>
    <link rel="icon" href="/images/favicon.png">
</head>
<body>
    <h1>Welcome to MySite</h1>
    <div id="carouseldiv"></div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        loadLivewireComponent("prasso-flipper", "carouseldiv", 1);
    });
    </script>
    <p>User: John Doe</p>
</body>
</html>
```
```markdown
# Carousel Component Documentation

The `[CAROUSEL_COMPONENT]` is a special placeholder that can be used within a site page to dynamically load a Livewire carousel component. When this placeholder is detected in the page content, it is replaced with a `<div>` element and an associated script that loads the Livewire component via AJAX.

## Usage

To use the carousel component, simply embed the `[CAROUSEL_COMPONENT]` placeholder within an HTML element on your site page:

```html
<article>
    [CAROUSEL_COMPONENT]
</article>
```

## Site Page Data

The content and configuration for the carousel component is stored in the `site_page_data` table. The relevant entry would look like this:

```sql
INSERT INTO `site_page_data`
(
    `fk_site_page_id`,
    `data_key`,
    `json_data`,
    `fk_team_id`
)
VALUES
(
    40,
    '[CAROUSEL_COMPONENT]',
    '[ 
        "<section class=''palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left'' style=''height: 389px;''>\\n    <div class=''palette content-wrapper flex items-center h-full''>\\n        <div class=''flex-1''>\\n            <header><div class=''slide-header mb-3''><p>Welcome</p></div></header>\\n            <div class=''text-content text-2 mb-4''>Join us for Sunday service every week with Bible Study at 10 AM and Worship Service at 11:00 AM. All are welcome to experience worship and community.</div>\\n            <button class=''px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition ease-in-out duration-200''>\\n                Learn More\\n            </button>\\n        </div>\\n        <div class=''flex-1''>\\n            <a href=''page/About'' class=''block w-full h-full''>\\n                <img src=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png''\\n                    srcset=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x''\\n                    alt=''Church Image'' class=''rounded h-full object-cover''>\\n            </a>\\n        </div>\\n    </div>\\n</section>",
    
        "<section class=''palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left'' style=''height: 389px;''>\\n        <div class=''palette content-wrapper flex items-center h-full''>\\n            <div class=''flex-1''>\\n                <header><div class=''slide-header mb-3''><p>Community Events</p></div></header>\\n                <div class=''text-content text-lg mb-6''>Discover our upcoming events and gatherings that strengthen our community and bring us together in fellowship.</div>\\n                <a href=''/page/events'' class=''mt-4 px-6 py-2 bg-white text-green-600 rounded-lg font-semibold hover:bg-gray-200 transition ease-in-out duration-200 inline-block''>View Events</a>\\n            </div>\\n            <div class=''flex-1''>\\n                <a href=''/page/About'' class=''block w-full h-full''>\\n                    <img src=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png''\\n                        srcset=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x''\\n                        alt=''Church Image'' class=''rounded h-full object-cover''>\\n                </a>\\n            </div>\\n        </div>\\n    </section>",
    
        "<section class=''palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left'' style=''height: 389px;''>\\n        <div class=''palette content-wrapper flex items-center h-full''>\\n            <div class=''flex-1''>\\n                <header><div class=''slide-header mb-3''><p>Bible Study</p></div></header>\\n                <div class=''text-content text-2 mb-4''>Join us every week for Bible study as we dive deeper into scripture and grow in our faith together.</div>\\n                <button class=''px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition ease-in-out duration-200''>\\n                    Join a Group\\n                </button>\\n            </div>\\n            <div class=''flex-1''>\\n                <a href=''/page/About'' class=''block w-full h-full''>\\n                    <img src=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png''\\n                        srcset=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x''\\n                        alt=''Church Image'' class=''rounded h-full object-cover''>\\n                </a>\\n            </div>\\n        </div>\\n    </section>",
    
        "<section class=''palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left'' style=''height: 389px;''>\\n    <div class=''palette content-wrapper flex items-center h-full''>\\n        <div class=''flex-1''>\\n            <header><div class=''slide-header mb-3''><p>Children''s Ministry</p></div></header>\\n            <div class=''text-content text-2 mb-4''>Our church offers a vibrant program for children where they can learn, play, and grow in a safe and joyful environment.</div>\\n            <button class=''px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition ease-in-out duration-200''>\\n                Learn More\\n            </button>\\n        </div>\\n        <div class=''flex-1''>\\n            <a href=''/page/About'' class=''block w-full h-full''>\\n                <img src=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png''\\n                    srcset=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x''\\n                    alt=''Church Image'' class=''rounded h-full object-cover''>\\n            </a>\\n        </div>\\n    </div>\\n</section>",
    
        "<section class=''palette site-section text-gray-800 py-8 px-6 rounded-lg shadow-lg text-left'' style=''height: 389px;''>\\n    <div class=''palette content-wrapper flex items-center h-full''>\\n        <div class=''flex-1''>\\n            <header><div class=''slide-header mb-3''><p>Building Improvement Project</p></div></header>\\n            <div class=''text-content text-2 mb-4''>Support our ministry through secure online giving and help us continue our work and mission in the community.</div>\\n            <button class=''px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition ease-in-out duration-200''>\\n                Donate Now\\n            </button>\\n        </div>\\n        <div class=''flex-1''>\\n            <a href=''/page/About'' class=''block w-full h-full''>\\n                <img src=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png''\\n                    srcset=''https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png, https://images.prasso.io/fbc/cdn.files/images/gallery/slides/b058a7cc-b809-4e03-9167-a83a73473829@2x.png 2x''\\n                    alt=''Church Image'' class=''rounded h-full object-cover''>\\n            </a>\\n        </div>\\n    </div>\\n</section>"
    ]',
    41
);
```

In this example, the `fk_site_page_id` field refers to the ID of the site page that contains the `[CAROUSEL_COMPONENT]` placeholder. The `json_data` field stores an array of HTML sections that represent the individual slides in the carousel.

When the page is rendered, the `[CAROUSEL_COMPONENT]` placeholder is replaced with a `<div>` element and a script that loads the Livewire component using the data from the `json_data` field.

## Customizing the Carousel

To customize the content and appearance of the carousel, you can modify the HTML sections stored in the `json_data` field. Each section represents a single slide in the carousel and can be customized with your own content, images, and styles.

Here are a few key points about the carousel component:

- The carousel is built using Tailwind CSS utility classes for styling.
- You can include images in the slides by using the `<img>` tag and specifying the source and dimensions.
- The carousel is responsive and will adjust its layout based on the screen size.
- The carousel automatically handles slide transitions and navigation.

If you need to make more significant changes to the carousel functionality or behavior, you may need to modify the underlying Livewire component code.

</details>