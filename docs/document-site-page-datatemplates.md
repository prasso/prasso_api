
### Site Page Data Templates Overview

Site Page Data Templates are a feature in the Prasso framework that define how dynamic data is retrieved, processed, and displayed on site pages. These templates provide a structured way to query and format data from the database, with support for JSON data handling and CSRF protection.

Here's a breakdown of the controller logic that loads a site page:

1.  **Initial Setup**:
    *   Sets the user's current team.
    *   Captures the current HTTP request.

2.  **Team Membership Check**:
    *   Verifies if the user belongs to the current team.
    *   If not, the user is logged out, a message is flashed, and they are redirected to the login page.

3.  **Content Initialization**:
    *   An empty string `$user_content` is prepared to hold the page content.

4.  **Site-Specific Page Logic** (This part executes if a specific site is active and it's not the main application site):
    *   **Attempt to find a custom page** definition (`SitePages`) for the current site and requested page section.
    *   **If a custom page is found**:
        *   The system checks the page `type`:
            *   **Type 2 (S3 File)**:
                *   It tries to fetch content from S3.
                *   If S3 content is found, it's used as the page body.
                *   If S3 content is *not* found, it logs a warning and defaults to treating the page as Type 1 (HTML).
            *   **Type 3 (External URL)**:
                *   If an `external_url` is provided, the user is redirected to that URL.
                *   If no URL is provided, it logs a warning and defaults to treating the page as Type 1 (HTML).
            *   **Type 1 (HTML) or fallback**: The content from the `description` field of the `SitePages` record is used.
        *   The `$user_content` is then populated by processing the page data through a `prepareTemplate` method.
    *   **If no custom page is found (backward compatibility)**:
        *   It checks S3 directly for content for the given site/page.
        *   If S3 content is found, a temporary `SitePages` object is created using this S3 content, and then `$user_content` is populated via `prepareTemplate`.

5.  **Return Content**:
    *   The method returns the `$user_content` (which could be HTML, or an empty string if no specific content was determined).

### Schema
CREATE TABLE `site_page_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `templatename` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `template_data_model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_where_clause` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_data_query` text COLLATE utf8mb4_unicode_ci,
  `order_by_clause` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `include_csrf` tinyint(4) DEFAULT '0',
  `default_blank` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

### Key Concepts

A **Site Page Data Template** defines:
1. **Data Model**: The Eloquent model class used to query the data (e.g., `App\Models\SitePageData`).
2. **Query Conditions**: A WHERE clause to filter records, supporting dynamic parameters like `???` for runtime values.
3. **Data Selection**: Fields to retrieve, with support for raw SQL expressions and JSON data extraction.
4. **Sorting**: Optional ORDER BY clause for sorting results.
5. **CSRF Protection**: Option to include CSRF tokens for secure form submissions.
6. **Default Values**: Fallback JSON data when no records match the query.
7. **Team Context**: Automatic team-based filtering for multi-tenant support.

Templates are processed by the `SitePageService` and integrated into pages through the `[DATA]` placeholder.

### Data Template Structure

Data templates are stored in the `site_page_templates` table with the following structure:

| **Field**               | **Type**        | **Description** |
|-------------------------|-----------------|-----------------|
| `templatename`          | VARCHAR(100)    | Unique identifier for the template |
| `title`                 | VARCHAR(500)    | Human-readable title |
| `description`           | TEXT            | Template description |
| `template_data_model`   | VARCHAR(100)    | Fully qualified model class name |
| `template_where_clause` | VARCHAR(100)    | WHERE conditions with `???` placeholders |
| `template_data_query`   | TEXT            | SQL SELECT expression (defaults to `json_data`) |
| `order_by_clause`       | VARCHAR(500)    | Sorting instructions (e.g., `created_at:asc`) |
| `include_csrf`          | TINYINT(1)      | Whether to include CSRF token |
| `default_blank`         | TEXT            | Default JSON when no data found |


Example template:
```sql
INSERT INTO `site_page_templates` (
  `templatename`, `title`, `description`,
  `template_data_model`, `template_where_clause`,
  `order_by_clause`, `include_csrf`, `default_blank`
) VALUES (
  'sitepage.datatemplates.orders',
  'Completed Orders',
  'Shows completed orders with shipping status',
  'App\\Models\\SitePageData',
  'fk_site_page_id = ??? AND JSON_EXTRACT(json_data, \'$.status\') = \'completed\'',
  'created_at DESC',
  1,
  '{"status": "no_orders"}'
);
```

### Field Reference

#### 1. `templatename`
- **Purpose**: Unique identifier for the template
- **Format**: Dot-notation recommended (e.g., `module.feature.purpose`)
- **Example**: `ecommerce.orders.completed`
- **Note**: Used to reference the template from site pages

#### 2. `title` & `description`
- Human-readable labels for the template
- Displayed in the admin interface
- Helps identify the template's purpose

#### 3. `template_data_model`
- **Purpose**: Specifies the Eloquent model class
- **Format**: Fully qualified class name
- **Example**: `App\Models\Order`
- **Default Behavior**: Queries the model's table

#### 4. `template_where_clause`
- **Purpose**: Defines query conditions
- **Special Syntax**:
  - `???` is replaced with the page's `where_value`
  - Supports raw SQL conditions
  - Can reference JSON fields with `JSON_EXTRACT`
- **Example**: `status = 'active' AND category_id = ???`

#### 5. `template_data_query`
- **Purpose**: Specifies which fields to select
- **Default**: `json_data` (for JSON column storage)
- **Advanced**: Can use SQL expressions
- **Example**: `id, name, JSON_EXTRACT(metadata, '$.price') as price`

#### 6. `order_by_clause`
- **Purpose**: Controls result ordering
- **Format**: `column:direction` or raw SQL
- **Examples**:
  - `created_at:desc` (newest first)
  - `name:asc` (A-Z)
  - `RAND()` (random order)

#### 7. `include_csrf`
- **Type**: Boolean (0/1)
- **Purpose**: Include CSRF token in response
- **Required**: Yes for forms using Laravel's CSRF protection
- **Effect**: Adds `csrftoken` field to JSON output

#### 8. `default_blank`
- **Purpose**: Fallback when no records match
- **Format**: Valid JSON string
- **Example**: `{"message": "No records found"}`
- **Note**: Should match the structure expected by the frontend

### Implementation Example

#### 1. Creating a Template
To display active products on an e-commerce page:

```sql
INSERT INTO `site_page_templates` (
  `templatename`, `title`, `description`,
  `template_data_model`, `template_where_clause`,
  `template_data_query`, `order_by_clause`, `include_csrf`, `default_blank`
) VALUES (
  'ecommerce.products.active',
  'Active Products',
  'Shows all active products with pricing',
  'App\\Models\\Product',
  'status = \'active\' AND category_id = ???',
  'id, name, price, image_url',
  'name:asc',
  1,
  '{"products": [], "message": "No products available"}'
);
```

#### 2. Using in a Page
Reference the template in your page content:

```html
<div class="product-grid">
  [DATA]
</div>

<script>
// Example frontend handling
fetch('/api/page-data')
  .then(response => response.json())
  .then(data => {
    // Render products using the template data
    const container = document.querySelector('.product-grid');
    container.innerHTML = data.products.map(product => `
      <div class="product">
        <img src="${product.image_url}" alt="${product.name}">
        <h3>${product.name}</h3>
        <p>$${product.price.toFixed(2)}</p>
      </div>
    `).join('');
  });
</script>
```

### Best Practices

1. **Naming Conventions**
   - Use dot notation for template names
   - Group related templates by feature
   - Be descriptive but concise

2. **Performance**
   - Add database indexes for filtered/sorted columns
   - Limit result sets with pagination
   - Use JSON columns judiciously

3. **Security**
   - Always validate and sanitize dynamic values
   - Use parameterized queries (handled automatically by Eloquent)
   - Include CSRF tokens for forms

4. **Error Handling**
   - Provide meaningful default values
   - Log template processing errors
   - Validate template configuration

### Advanced Features

#### Dynamic Parameters
Use `???` in where clauses for runtime values:
```
category_id = ??? AND price <= ???
```

#### JSON Data
Query JSON columns with `JSON_EXTRACT`:
```
JSON_EXTRACT(metadata, '$.on_sale') = 'true'
```

#### Team Context
Templates automatically filter by the current team when:
- User is authenticated
- `fk_team_id` column exists
- User has team membership

### Troubleshooting

#### Common Issues
1. **No Data Returned**
   - Check template conditions
   - Verify `where_value` on the page
   - Test the query directly in MySQL

2. **Template Not Found**
   - Verify template name spelling
   - Check database connection
   - Clear template cache if enabled

3. **JSON Parsing Errors**
   - Validate JSON in `default_blank`
   - Ensure consistent data types
   - Check for BOM characters

For additional help, refer to the `SitePageService` class and related test cases.