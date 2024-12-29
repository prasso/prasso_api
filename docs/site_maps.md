# Site Map Documentation

## Overview
The site map functionality allows you to create hierarchical navigation menus for your website. Pages can be organized into a multi-level menu structure, with top-level items and sub-menu items. You can also control which pages appear in the site map.

## Menu Structure
The menu hierarchy is controlled by the `menu_id` field in the `site_pages` table:

- `menu_id = -1`: Page is hidden from the site map
- `menu_id = 0`: Page appears as a top-level menu item
- `menu_id > 0`: Page appears as a sub-menu item under the page with the matching ID

## Usage

### In Page Content
You can include a dynamic site map in any page by using the `SITE_MAP` constant in the page content:

```php
// Example page content
$content = "Welcome to our site! Here's our navigation menu: SITE_MAP";
```

The system will automatically replace `SITE_MAP` with a menu generated from all visible site pages using `$this->site->getSiteMapList($path)`.

### Model Usage
The `SitePages` model provides several methods to work with the menu structure:

```php
// Get all top-level menu items
$topMenuItems = SitePages::topLevel()->get();

// Get all visible menu items (excludes hidden items where menu_id = -1)
$visibleItems = SitePages::visible()->get();

// Get sub-menu items for a specific page
$page = SitePages::find(1);
$subMenuItems = $page->subMenuItems;

// Get the parent menu item for a sub-menu page
$childPage = SitePages::find(2);
$parentMenuItem = $childPage->parentMenu;
```

## Example Menu Structure

```
Home (menu_id: 0)
├── About Us (menu_id: 1)
│   ├── Our Team (menu_id: matches About Us page ID)
│   └── Our History (menu_id: matches About Us page ID)
├── Services (menu_id: 0)
│   ├── Consulting (menu_id: matches Services page ID)
│   └── Training (menu_id: matches Services page ID)
└── Contact (menu_id: 0)

Hidden Page (menu_id: -1) // Won't appear in site map
```

## Database Schema
The menu structure is implemented using the `menu_id` field in the `site_pages` table:

```sql
site_pages
├── id (primary key)
├── menu_id (integer, default: -1)
└── ... (other fields)
```

## Best Practices
1. Assign `menu_id = 0` to main navigation items
2. Use the page's ID as the `menu_id` for sub-menu items
3. Set `menu_id = -1` for utility pages that shouldn't appear in navigation
4. Maintain a logical hierarchy - avoid deep nesting (recommended max: 2-3 levels)
5. Ensure parent pages are visible when creating sub-menu items
