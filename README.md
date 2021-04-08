
## About Prasso

A Laravel team site, used to feed application structure information to a Prasso app.
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

## Functionality
A Prasso site is both business information site and Prasso api site. The api serves the Prasso apps. Apps can be assembled/ built at a Prasso site using the admin tools. And then when a user with an assigned team and app logs into the mobile app, the assembled presentation becomes their personalized mobile app.

## Prasso setup
Firebase is required for the Prasso app, since users are authenticated at Firebase, but the app data is stored at the API site.

### Sites
How sites and sites pages work
1. At least one site must be configured in the site table. When the site loads, the host is checked to see if it's recorded
2. if the site is recorded, the site object from the table is kept available in the app session for use
3. site pages reference the site table, so if a site has pages they can be used in the display
4. the landing page of the web site is going to use the stored site to look up the landing page for the stored site in the site_pages table. if an entry is found in the site pages table for the site and with label of Welcome, that will be what is shown to the web visitor

### Visual Editing (CMS)
This code is integrating the GrapesJS editor (https://grapesjs.com) 
When you edit your site you will be able to use the included components to assemble your pages

## License

Prasso is licensed under the [MIT license](https://opensource.org/licenses/MIT).

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
