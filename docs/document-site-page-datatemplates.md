
### Site Page Data Templates Overview
provided from original source by https://chatgpt.com/share/66f55184-4bd4-8008-be62-a981ea812b70

Site Page Data Templates are a powerful feature in the Prasso developer framework used to define how data is retrieved, processed, and displayed on specific pages. These templates streamline the interaction between the backend and frontend by specifying a clear, structured approach for querying and formatting data.

### Key Concepts

A **Site Page Data Template** outlines:
1. **Data Model**: Specifies which data model will be used to format the data.
2. **SQL Where Clause**: Defines the conditions and restrictions applied to the query (e.g., filtering by status or other criteria).
3. **Fields Selection**: Specifies the fields to be retrieved from the data model.
4. **Ordering**: Defines how the retrieved data should be ordered (e.g., by creation date, alphabetically, etc.).
5. **CSRF Token Inclusion**: Determines whether a CSRF (Cross-Site Request Forgery) token needs to be included in the data for form submissions. Laravel forms, in particular, require this token to ensure secure form handling.
6. **Default Blank Response**: Specifies the default JSON value returned if the query retrieves no data.

These components allow for precise control over the data retrieval and manipulation process for each page in the system.

### Data Template Structure

Below is an example of how a data template is structured in the system:

| **Field Name**          | **Value**                                                 |
|-------------------------|-----------------------------------------------------------|
| **Template Name**        | `sitepage.datatemplates.gogodone`                         |
| **Title**                | `GoGo Delivery Completed Freight Orders`                  |
| **Description**          | `Will produce a list of completed shipments`              |
| **Template Data Model**  | `App\Models\SitePageData`                                 |
| **Template Where Clause**| `fk_site_page_id=??? AND JSON_EXTRACT(json_data, '$.status') ='Completed'` |
| **Order By Clause**      | `created_at:asc`                                          |
| **Include CSRF**         | `0` (0 = No, 1 = Yes)                                     |
| **Default Blank**        | `{}` (returned when no data is found)                     |
| **Template Data Query**  | `json_data`                                               |

### Detailed Field Explanations

#### 1. **Template Name**
   A unique identifier for the data template. This is typically namespaced according to the site page and its purpose. For instance, in the example, the template name is `sitepage.datatemplates.gogodone`, which suggests that it’s used for a GoGo Delivery page listing completed orders.

#### 2. **Title**
   The human-readable title for the template, which helps developers quickly understand the template's purpose. In this case, the title is `GoGo Delivery Completed Freight Orders`, indicating it deals with completed freight orders for GoGo Delivery.

#### 3. **Description**
   A brief description of the template's functionality. It serves as a summary of what the template does. In this example, it produces a list of completed shipments.

#### 4. **Template Data Model**
   The data model from which the data is queried. This is typically a Laravel model (in this case, `App\Models\SitePageData`), which provides the structure and logic for interacting with the underlying database.

#### 5. **Template Where Clause**
   The SQL-like where clause that specifies the conditions applied to the query. For example, `fk_site_page_id=??? AND JSON_EXTRACT(json_data, '$.status') ='Completed'` filters the data to include only completed shipments for a specific page, where `???` would be dynamically replaced by the appropriate site page ID.

#### 6. **Order By Clause**
   Specifies how the data should be ordered once retrieved. In the example, the data will be ordered by the `created_at` field in ascending order (`created_at:asc`), meaning the oldest records will appear first.

#### 7. **Include CSRF**
   Indicates whether a CSRF token should be included in the data. Laravel requires a CSRF token for form submissions to prevent CSRF attacks. A value of `0` means the token is not included, while a value of `1` would include it.

#### 8. **Default Blank**
   Specifies what will be returned if no data matches the query. This is useful to ensure that the frontend receives a predictable response, even when there is no data. The example leaves it blank, meaning an empty JSON object (`{}`) will be returned if the query yields no results.

#### 9. **Template Data Query**
   Specifies the field that will be used to extract the data from the query. In the example, the field is `json_data`, suggesting that the data is stored in JSON format, and this is the field/key name from which data will be extracted.

### Example Use Case

For a page showing completed freight orders for GoGo Delivery, the template defined above will:
- Retrieve data from the `App\Models\SitePageData` model.
- Filter the data where the `status` field in the JSON data is `Completed`.
- Order the results by the creation date in ascending order.
- Return an empty JSON object if no completed shipments are found.
- Optionally include a CSRF token if required for secure form submissions (in this case, it is not included).

### Integration with the Existing System

The Site Page Data Templates feature is already integrated into the existing system, making it straightforward to configure data retrieval for various site pages. Simply define the template with the appropriate conditions and fields for each specific use case, and the framework will handle the rest.

By defining and utilizing these data templates, developers can ensure consistent and efficient data handling across all site pages.
