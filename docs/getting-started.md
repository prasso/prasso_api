# Getting Started

A video of the following content is [Prasso Admin Fundamentals](/). 
You may find it helpful to view the video of [Prasso Fundamentals Overview](https://www.youtube.com/watch?v=bBe3Qu7fxeY) for some context around the operations described below.


## Create an Account

- Open the Prasso mobile app or use the /login page of your Prasso web site
- Tap "Register" and enter your email and password
- You will receive a confirmation email to activate your account
- All newly registered users receive the user access level. Admin access must be assigned by an existing admin.

## Create your Site
After registration, your team admin can assign you a role. Once you have the correct access, use the **site creation wizard** to make your site. This is the foundation for your apps.


## Manage Sites

- Users who are Admins of a team will see an option to Create a new Site and App when they log in

- A site is identified by its **URL**.
- You can enable **user registration** and **sub-team data isolation** if needed.

Next, add **views** to your site using the **dashboard tools**. Code the page manually, or use the **visual editor** or connect **existing URLs** to create your pages/views.

### Add Views (Pages)

- In the admin dashboard, go to Site Pages
- Click "Add New Page"
- Enter a page name and use the visual editor to add content
- Click "Publish" to make the page live

### Edit Pages

- Go to Site Pages and click a page to edit, choose source edits or visual editor.
- Make changes using either the visual editor or the source window.
- Click "Update Page" to publish changes
- When filling in a site page that uses a URL, keep the masterpage blank. Otherwise, when editing the URL is not visible. 

## Build a Mobile App

- Apps **associate** with sites. 
- Apps are configured with **tabs** pointing to site **views**.
- The app **host** identifies the site and tab data to load. This is sent back as **JSON** for the app to parse and display.
- Update tabs anytime through the **admin panel**. 


### Configure App Tabs

- In the admin, go to Apps and select your app
- Click "Add Tab" and enter the page URL
- Arrange tabs using drag and drop
- Click "Update App" to publish changes

## Manage Users and Teams

- Users join **teams** associated with sites. They get access based on **role** (admin or user).
- **Admins** can set up apps for their sites through the admin panel.
- **Sub-teams** enable data isolation when configured. The main site team still has access.

## Manage Teams

- Team Admins will see the team management option on their dashboard when logged in

### Create a Sub-Team

- Sites that have sub-teams enabled will show an option to add a team from the teams management page.
- Go to Teams and click "Add Team"
- Enter a team name and description
- Subteams exist to restrict data access to the sub-team. Sub-Admins who are owners of the parent team have view to all sub-team data

### Add Users

- Go to Team Members and click "Invite User"
- Enter the email address and role (Admin or User)
- The user will receive an invitation to join the team