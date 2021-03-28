
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

## Functionality
This site is both business information site and Prasso api site. The api serves the Prasso apps. Apps can be assembled/ built here and then when a user with an assigned team and app logs into the mobile app, the assembled presentation becomes their personalized mobile app.

The API. Users are authenticated at Firebase, but the app data is stored here

### Sites
How sites and sites pages work
1. when the site loads, the host is checked to see if it's recorded
2. if the site is recorded, the site object from the table is kept available in the app session for use
3. site pages reference the site table, so if a site has pages they can be used in the display
4. the landing page of the web site is going to use the stored site to look up the landing page for the stored site in the site_pages table
5. if an entry is found in the site pages table for the site and with label of Welcome, that will be what is shown to the web visitor

### Visual Editing (CMS)
This code is integrating the GrapesJS editor (https://grapesjs.com) 
When you edit your site you will be able to use the included components to assemble your pages

## License

Prasso is licensed under the [MIT license](https://opensource.org/licenses/MIT).

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
