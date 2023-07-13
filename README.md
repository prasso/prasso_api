
## About Prasso
Prasso is a platform that streamlines the app prototype process for owners by providing a wizard-like framework for gathering the necessary details and building custom mobile and web apps.

With Prasso you can create both your app api backend and your web site with one setup form. And then with the admin tools customize those to add branding and content.

This API backend is based on the Laravel framework. It is used to feed application structure information to a Prasso app and site. Prasso app is a rapid prototyping tool that is configured with this api.

## Prasso Concept
1. everything is based on a site.
    
    a site is determined by it’s url.
    a site can support user registration
    
2. Apps have an association to a site.
    1. an app is configured with tabs 
        1. tabs point to views
    2. an app is identified at the backend by the host of the request
        1. the host is associated with the site, the site is associated with the app
    3. when a request is received the host is used to look up the tab configuration that will be loaded by the app
    4. the tab configuration is sent back to the app in json format. 
    5. the app receives the json, parses the data into tabs and shows the tabs.
    6. the user is able to interact with the tabs as they have been configured
3. changing the tab configuration of an app is done through the admin panel
    1. users are associated with sites based on the team they are a member of
    2. teams are assigned to sites
    3. users have roles. admin and user
    4. admins can setup apps through the admin panel based on what sites they have been associated with through their team
4. teams are the basic unit in a social group. teams have coaches and members
### relationships
* team
  * users
     * roles
        Three tiers of user roles exist (three are planned).
        Super-Admin , Site Admin, and App User
  * sites
    * apps
      * tabs
    * pages

1. *Site* Url is a site. The software is configured to use CMS pages based on the site configuration. Sites are configured in the Admin.
2. *Site* Sites have Teams
3. *Site* Sites have site pages. These can be created and maintained using a visual editor, that is GrapesJs
4. *Team* Teams have users. Users belong to teams. When a user registers, a private team is assigned. Users can also be included in other teams.
5. *Team* Teams have sites. Sites have apps. When a user who is a member of a team logs into the Prasso app, the default-designated app will be loaded for use.
6. *Apps* Apps have tabs. App tabs are web page urls. Custom header information can be sent to the url with the request to enable application specific sessions.
7. *Users* Users have roles.
    * how roles work: Allow anyone with a login to log into the app. No role required
      * site-admins can log into the sites they have association with
      * super-admins can access any site admin area
        // INSERT INTO `yourdatabase`.`roles` (`role_name`) VALUES ('super-admin');
        // INSERT INTO `yourdatabase`.`roles` (`role_name`) VALUES ('site-admin');


## No Code Apps
Prasso unites multiple web tools into one mobile app.  For example, a flarum.cloud forum could be a tab,  a YouTube channel could be a tab, a calendar scheduler could be a tab. Together the tabs assemble into a personalized app. 

Prasso is also a rapid prototyping tool. Assemble your app using no-code pages which have been built to prove your concept. Then release it to prove the concept works. 

When a user first registers, the mobile app has tabs that will direct the user to setup their app. The api web site has entry forms for users to assign web pages to the tabs. 
This allows for prototypes to be ran on mobile immediately, when the web page has been setup through some other no-code solutions.



# Overview of functionality

A Prasso site is both business information site and Prasso api site. The api serves the Prasso apps. Apps can be assembled/ built at a Prasso site using the admin tools. And then when a user with an assigned team and app logs into the mobile app, the assembled presentation becomes their personalized mobile app.

## How the mobile app works as a "No-Code" user-built mobile app

Prasso apps consist of User registration and login based on Firebase authentication
And a framework that creates an app dynamically based on the server configuration received for a logged in user.

'Prasso by faxt (Fast Api eXtraction Technologies)'
![](https://i.imgur.com/K69SPIt.png)

    
### How the app is built using "No-Code"
1. The hierarchy is Prasso - Site - Site-Pages. Prasso - Teams - Users and Apps. Apps - Tabs
![](https://i.imgur.com/zjpAojl.png)

3. Prasso - SITES are based on a domain. The software is configured to use CMS pages based on the site configuration. Sites are configured in the Admin. When a user enters the URL of a Prasso site into their browser, the Prasso software will show that site as configured in the admin tool.
4. SITES have site pages. These can be created and maintained using a built in visual editor, that is based on GrapesJs opensource project.

    Sites are the landing page of the App home web site.  Example Prasso sites: https://prasso.io, https://barimorphosis.com,   https://lileyscapes.prasso.io , https://mercyfullfarms.com 
    

6. Prasso - USERS belong to TEAMS. When a user registers, a private team is assigned. Users can also be included in other teams.
7. TEAMS have APPS. When a user who is a member of a team logs into the Prasso app, the default-designated app will be loaded for use. 
9. APPS have TABS. USERS "build" their apps in the Prasso Admin tool. APP TABS are web page urls. Custom header information can be sent to the url with the request to enable application specific sessions.

## Prasso setup
Firebase is required for the Prasso app, since users are authenticated at Firebase, but the app data is stored at the API site.
- If you don't have a Firebase project configured, create one for your app. 
- Download the files for adding to your iOS and Android projects.
- Enable Authentication for email/password
- Create a Cloud Firestore database.

### Help to configure the setup for dns configuration

the command is

```jsx
php artisan setup:dns sitename
```

- install and setup the aws cli
    - at aws you will need to obtain an iam key with access to arn:aws:route53:::hostedzone/Z05231071JDWEVVYAI5HR because no identity-based policy allows the route53:ChangeResourceRecordSets action
    

## Admin Site
### Sites
How sites and sites pages work
1. At least one site must be configured in the site table. (example: prasso.io ) When the site loads, the host is checked to see if it's url/domain is recorded in the site table.
2. if the site is recorded, the site object from the table is kept available in the app session for use. The site object is the UI configuration of the site.
3. site pages reference the site table, so if a site has pages they can be used in the display as links. These links look like https://prasso.io/page/Welcome-OLDPRASSO
4. Code that runs when a site is loaded uses the current domain to compare with the stored site url to look up the landing page contents. if an entry is found in the site pages table for the site and with label of Welcome, that will be what is shown to the web visitor on the home page.

### Site Pages
Site Pages are of two types. 1. an external url or 2. masterpage and html content.

If a site page is html content it can also specify a data template to be used when the url is loaded.

Data templates are stored in the database and are associated with a site_page in the editor.
EXAMPLE SQL: 
    List all site_page_templates: SELECT * FROM faxt_api.site_page_templates;

There are two ways to embed a data template within a site page. (see Site Pate Template section )
1. Embed [DATA] in the site page description ([DATA]) as shown in the example:
x-data='{"videos":[[DATA] ]}' >

The value [DATA] is replaced when the site page is passed to  getTemplateData($site_page) (which is in SitePageService).

In the getTemplateData function, the template named in the site page is retrieved. The template containS instructions on how to retrieve the data in the field - template_data_query. If the site page has a template, the site page description will have a placeholder where the data is inserted - that is '[LATE_DATA]'.
2. use Javascript from your site page which sends the request for template data to an api endpoint.
    /sitepages/{siteid}/{pageid}/lateTemplateData
    where siteid is your prasso site id as an integer
    and pageid is your site page id as an integer
    Sample: 
    ```
     function fetchLateData() {
    console.log('fetching data from /sitepages/6/59/lateTemplateData');
    fetch('/sitepages/6/54/lateTemplateData', {
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

### Site Page Template

Prerequisite: Use the data template editor to add a data-template record. (See the Data Template section)
        
To create a site page template, follow these steps:

1. Create an HTML form with a placeholder that will be replaced by the template.
2. Create a template and add its name as a record to the site_templates table, allowing it to be selected for the site page.
3. Edit the site page record and specify the newly created data template as the template.

### Data Template Template
/site-page-data-templates
Template Name: example sitepage.templates.xxx
Title:
Description:
Template Data Model: the class name of data table. example App\Models\SiteMedia
Template Where Clause: the field that will limit what data comes from the table. example fk_site_id
Template Data Query: the field that will be selected. example media_title
Order By Clause: the field that will be used to order returned data. example media_title:asc
This is a single item form that will be saved: if checked data will be single record. unchecked will be an array
Json for default blank form: the json data which will be structured as expected to be returned. example {
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
Summary: Name the site page by its ID and provide information on how to retrieve data in the template_data_query. The model is listed first, followed by a colon, and then the query that limits the displayed models.
EXAMPLE: 
'App\Models\SiteMedia':'CONCAT(\'{"s3media_url":"\', s3media_url, \'","media_title":"\', media_title, \'","thumb_url":"\', thumb_url,\'"}\') as thumb_display')

### Visual Editing (CMS)
This code is integrating the GrapesJS editor (https://grapesjs.com) 
When you edit your site you will be able to use the included components to assemble your pages.

## License

Prasso is licensed under the [MIT license](https://opensource.org/licenses/MIT).

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
