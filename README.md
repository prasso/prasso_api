
## About Prasso, Rapid Prototype Tool

A Laravel team site, used to feed application structure information to a Prasso app. Prasso app is a rapid prototyping tool that is configured with this api.
### Prasso Definition·
Definition
- to exercise, practise, to be busy with, carry on
- to undertake, to do
- to accomplish, perform
- to commit, perpetrate
- to manage public affairs, transact public business
- to exact tribute, revenue, debts
- to act

## Prasso Concept
1. everything is based on a site.
    
    a site is determined by it’s url
    
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


Three tiers of user roles exist (three are planned).
Super-Admin , Site Admin, and App User

## Admin Site
### Sites
How sites and sites pages work
1. At least one site must be configured in the site table. (example: prasso.io ) When the site loads, the host is checked to see if it's url/domain is recorded in the site table.
2. if the site is recorded, the site object from the table is kept available in the app session for use. The site object is the UI configuration of the site.
3. site pages reference the site table, so if a site has pages they can be used in the display as links. These links look like https://prasso.io/page/Welcome-OLDPRASSO
4. Code that runs when a site is loaded uses the current domain to compare with the stored site url to look up the landing page contents. if an entry is found in the site pages table for the site and with label of Welcome, that will be what is shown to the web visitor on the home page.

### Visual Editing (CMS)
This code is integrating the GrapesJS editor (https://grapesjs.com) 
When you edit your site you will be able to use the included components to assemble your pages

## License

Prasso is licensed under the [MIT license](https://opensource.org/licenses/MIT).

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
