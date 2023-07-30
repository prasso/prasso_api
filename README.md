
## About Prasso
Prasso is a rapid prototyping and app development platform that streamlines the process of building functional mobile and web apps. Its wizard-like interface enables low-code app assembly, while the backend leverages the robust Laravel framework for scalability. Key benefits include simplified prototyping, customization options, time savings, seamless integration with tools like Firebase, and comprehensive documentation. Prasso allows you to quickly validate and iterate on app ideas before full development. Its open-source nature provides the flexibility to tailor the platform to your needs. Overall, Prasso empowers users to efficiently create, customize and rapidly deploy app prototypes to gather user feedback and refine concepts.

Prasso offers several compelling reasons to use its software:

1. Simplified app prototype process: Prasso provides a wizard-like framework that streamlines the app prototype process, making it easier for owners to gather necessary details and build custom mobile and web apps.

2. Time-saving development: With Prasso, you can create both your app API backend and website using a single setup form. This saves time and streamlines the development process.

3. Customization options: Prasso offers admin tools that allow you to customize your app and website by adding branding and content. This enables you to create a unique and personalized user experience.

4. Rapid prototyping: Prasso's app serves as a rapid prototyping tool, enabling you to quickly build and test app prototypes. This helps you validate your app concept and make iterations before committing to full development.

5. Robust and scalable foundation: The API backend of Prasso is built on the Laravel framework, known for its robustness and scalability. This ensures a stable and reliable foundation for your app.

6. User registration and site association: Prasso supports user registration and association with specific sites, enabling personalized user experiences and effective management of user access and permissions.

7. Low-Code functionality: Prasso offers a Low-Code approach to app development, allowing users to assemble functional apps without requiring coding knowledge. Pre-built components and integrations make app creation accessible to a wider range of users.

8. Unification of web tools: Prasso combines multiple web tools into a single mobile app, allowing you to integrate functionalities like forums, YouTube channels, and calendars. This creates a comprehensive and personalized user experience.

9. Proof of concept and rapid deployment: Prasso's rapid prototyping capabilities enable you to build and release your app quickly to validate your concept. This helps you gather feedback, test user engagement, and refine your app idea before investing in full-scale development.

10. Visual editing with GrapesJs: Prasso provides a visual editor based on GrapesJs, making it easy to visually create and maintain site pages. This simplifies the design and updating of your app's content.

11. Business information and API site: A Prasso site serves as both a business information site and an API site, allowing you to showcase your business information while serving the Prasso apps through the API. It provides a unified platform for managing both aspects.

12. Easy setup and integration with Firebase: Prasso requires Firebase for user authentication and offers easy setup and integration with Firebase projects. This simplifies the authentication process and ensures secure user management.

13. Comprehensive documentation and community support: Prasso provides extensive documentation and has an active community for support. This helps users navigate the platform, troubleshoot issues, and stay updated with the latest features and enhancements.

14. Open-source and licensed under MIT: Prasso is an open-source platform and is licensed under the MIT license. This allows for customization, collaboration, and the freedom to modify the platform according to your needs.

Overall, Prasso empowers you to efficiently build, customize, and validate your app ideas, offering a user-friendly interface, robust framework, and a range of features that simplify the development process.

# Prasso Concept

### Getting Started

After registration, use the **site creation wizard** to make your site. This is the foundation for your apps.

- A site is identified by its **URL**. 
- You can enable **user registration** and **sub-team data isolation** if needed.

Next, add **views** to your site using the **dashboard tools**. Use the **visual editor** or connect **existing URLs**.

### Building Mobile Apps

- Apps **associate** with sites.  
- Apps are configured with **tabs** pointing to site **views**.
- The app **host** identifies the site and tab data to load. This is sent back as **JSON** for the app to parse and display. 
- Update tabs anytime through the **admin panel**.

### Managing Users and Teams

- Users join **teams** associated with sites. They get access based on **role** (admin or user).
- **Admins** can set up apps for their sites through the admin panel.
- **Sub-teams** enable data isolation when configured. The main site team still has access.

### Relationships

There are 3 user roles:

- **Super Admin**
- **Site (Team) Admin**  
- **App User**

### Sites 

- The **site URL** identifies each site.
- Sites use CMS pages based on configuration. 
- Sites are configured in the **Admin Panel**.

### Users and Teams

- Sites have **Users** and optionally **Sub-Teams**.
- **Teams** have **Users**. Users belong to teams.
- New users get a private team. Users can join multiple teams.

### Access and Apps

- Teams are assigned **Sites**. Sites have **Apps**.
- When users log in, their team's default app loads.

### Tabs and Pages

- **Apps** have **Tabs**. Tabs are page **URLs**.
- Custom headers enable app-specific sessions.

### User Roles

- All users can log in to the app.
- **Site Admins** access assigned sites as determined by their team. 
- **Super Admins** access all admin areas.

## Prasso Low Code Apps

### User Registration 

There are two ways users can register:

- If users register through the **Prasso mobile app**, they will be directed to set up their app via the **API site**.

- If users register through an app built with **Prasso**, they automatically join the site that app is associated with. 

The key thing to understand is:

- The **Prasso mobile app** handles standalone user registration and app setup.

- Apps built on the **Prasso platform** link users to their site automatically on registration.


## How the mobile app works as a "Low-Code" user-built mobile app

'Prasso by faxt (Fast Api eXtraction Technologies)'
![](https://i.imgur.com/K69SPIt.png)
    
### How the app is built using "Low-Code"
The hierarchy is Prasso - Site - Site-Pages. Prasso - Teams - Users and Apps. Apps - Tabs
![](https://i.imgur.com/zjpAojl.png)

### Prasso Sites

- **Sites** are based on a domain. The Prasso software displays sites according to admin configuration. 

- When users enter a Prasso site URL, they see that site's configured pages.

- **Site pages** can be created and edited visually with the built-in GrapesJS editor.

- The site is the homepage for its apps. Example sites: https://faxt.com, https://gogodeliveryonline.com, https://lileyscapes.prasso.io, https://faithlakecity.com

### Users and Teams

- **Users** belong to **Teams**. New users get a private team. Users can join multiple teams.

### Accessing Apps

- **Sites** have **Apps**. When users log in, their team's default app loads.

- The loaded app is determined by the URL and the user's team membership.

### Building Apps

- In the Admin, users build apps with **Tabs**, which are **URLs**.

- Custom headers enable app-specific sessions.


## Self-Hosted Prasso Setup

To set up a self-hosted Prasso app:

- **Firebase** is required for user authentication. App data is stored on the API site.

- If you don't have a Firebase project, create one for your app. 

- Download the Firebase iOS and Android SDK files.

- In Firebase, enable email/password authentication. 

- Create a Cloud Firestore database.

The key steps are:

- Use **Firebase** for user auth and Cloud Firestore.

- Your app's actual **data is on the API site** you configure.

- Download the necessary Firebase **SDKs** to link to your app clients.


### Help to configure the setup for dns configuration

the command is

```jsx
php artisan setup:dns sitename
```

- install and setup the aws cli
    - at aws you will need to obtain an iam key with access to arn:aws:route53:::hostedzone/Z05231071JDWEVVYAI5HR because no identity-based policy allows the route53:ChangeResourceRecordSets action
    
## Admin Site

### Sites

How sites and site pages work:

1. At least one **site** must be in the **site table** (e.g. prasso.io). When the site loads, the **host is checked** against the site table.

2. If the site is recorded, its **configuration object** is available in the app session. This defines the site UI. 

3. **Site pages** reference the site table. Page links look like: `https://prasso.io/page/Welcome-OLDPRASSO`

4. When a site loads, its **domain is compared** to site URLs to get the **landing page content**. If a `Welcome` page exists for that site, it will display.

### Site Pages 

Site Pages are either:

1. An **external URL**

2. **HTML content** with a **page template**

HTML pages can specify a data template to populate content.

**Page templates** are stored in the database and associated with a site_page. 

To embed content obtained through a data template:
Either
1. Use `[DATA]` placeholder in the page. This will be replaced by the template.  Example:
```
x-data='{"videos":[[DATA] ]}' >
```

Or
2. Use a **JavaScript request** to `/sitepages/{siteid}/{pageid}/lateTemplateData` to get template data. If using this method, the placeholder becomes [LATE_DATA]. Example:
where siteid is your prasso site id as an integer
    and pageid is your site page id as an integer
 
    ```
     function fetchLateData() {
    console.log('fetching data from /sitepages/6/59/lateTemplateData');
    fetch('/sitepages/6/59/lateTemplateData', {
      method: 'POST',
 headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: '_token=CSRF_TOKEN'
      
    }).then(response => {
      return response.json();
    }).then(data => {
      gridOptions.api.setRowData(data);
    }).catch(error => {
      // Handle error
    });
  }
  ``` 

### Site Page Templates

Prerequisite: Add a [**data template**](#data-templates)

To create a template:

1. Add HTML with `[DATA]` placeholder. 
2. Create the template in `site_templates` table.

3. Select template for the site page.

### Data Templates 

Use the data template editor to define template info:

- Name
- Model
- Where clause 
- Query
- Order by
- Single/Multiple records

Provide sample JSON for expected data structure. Example data for the form:
```
Template Name: example sitepage.datatemplates.xxx
Title: your title
Description: your description
Template Data Model: the class name of data table. example App\Models\SiteMedia
Template Where Clause: the field that will limit what data comes from the table. example fk_site_id
Template Data Query: the field that will be selected. example media_title
Order By Clause: the field that will be used to order returned data. example media_title:asc
This is a single item form that will be saved: if checked data will be single record. unchecked will be an array
Json for default blank form: the json data which will be structured as expected to be returned. example 
```
{
  "csrftoken": "",
  "customer": "",
  "status": "",
  "item": "",
  "pickupAddress": {
    "address": "",
    "city": "",
    "state": "",
    "zip": ""
  },
  "deliveryAddress": {
    "address": "",
    "city": "",
    "state": "",
    "zip": ""
  }
}
```
Summary: Name the site page by its ID and provide information on how to retrieve data in the template_data_query. The model is listed first, followed by a colon, and then the query that limits the displayed models.
EXAMPLE: 
'App\Models\SiteMedia':'CONCAT(\'{"s3media_url":"\', s3media_url, \'","media_title":"\', media_title, \'","thumb_url":"\', thumb_url,\'"}\') as thumb_display')
```

### Visual Editing

The [**GrapesJS editor**](https://grapesjs.com) enables visual site editing with components. 

## License

Prasso is licensed under the [MIT license](https://opensource.org/licenses/MIT).

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
