## Technical Documentation

- ## Overview of project structure
  - standard for a Laravel project
- ## Key technologies
  - Laravel, Tailwind, AlpineJs, MySQL
- ## Installation and configuration
  - composer install
  - Setting environment variables - copy the env_example file to .env and fill in the appropriate values for your setup
- ## Deployment
  - Hosting options 
  - CI/CD workflows
- ## API reference
  - Endpoints, parameters, responses
- Customization
  - ## Extending core functionality
- [Contributing guidelines](/docs/contributing.md) 





## Component Docs

- ## [Laravel Backend](https://github.com/laravel/laravel)
  - Framework overview
  - Custom controllers
  - Database schema
- ## Blade Templates Frontend
  - Component architecture
  - App/Site management
  - Page routing
- ## Firebase Integration

Firebase provides user authentication and database storage for Prasso apps. 

To integrate Firebase:

### 1. Create Firebase project

Go to the Firebase console and create a new project. This will initialize Firebase for your Prasso app.

### 2. Add Firebase to app

- Register the Prasso mobile app in the Firebase project by adding its bundle ID. This links the app to Firebase.

- Download the GoogleService-Info.plist file. This contains credentials for the iOS app to connect to Firebase.

- Follow the Firebase workflow to add the SDK to the Xcode project.

### 3. Enable authentication 

In the Firebase console:

- Enable Email/Password authentication in the Sign-in Method tab.

- Enable user creation under the Users and Permissions tab. 

This allows new user registration directly from the mobile app.

### 4. Add Cloud Firestore

Under Develop > Database, add Cloud Firestore to the project. Set up security rules to control data access.

### 5. Add API key 

Under Project Settings > General, register the Prasso API server and get an API key. 

Use this key to allow the API backend to interact with Firebase services like authentication.

Let me know if you would like any part of the Firebase integration setup covered in more detail!

- ## Mobile App
  - Built with Flutter [prasso_app](https://github.com/prasso/prasso_app)

  
