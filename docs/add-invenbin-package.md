### what is this document ###
This document is a compilation of steps used to create training videos. 
Subject of this document is the training video showing how to add faxt/invenbin to your Laravel project: https://github.com/bperreault/Prasso_Invenbin (prasso/Prasso_Invenbin was forked from here )
https://github.com/prasso/CreateShotCutProjectUsingAI was used to generate the voiceover
ChatGPT was used to generate the script below using the readme of the project, transcript here:
https://chatgpt.com/share/fbadcef3-c5b7-4853-9eee-2586e59843cd
Find the completed video at this channel:
https://www.youtube.com/@faxt8281


### Video Script: How to Integrate and Use the Faxt Invenbin Package in Your Laravel API Project

---

#### [Opening Scene: Introduction]

**[Video Host On Screen]**

**Host:**  
"Welcome to this tutorial on integrating the Faxt Invenbin package into your Laravel API project. Whenever you need an efficient way to handle inventory this package has got you covered. We'll walk you through the installation, configuration, and usage of the package, so let's get started!"

**[Text Overlay: Introduction to Faxt Invenbin for Laravel]**

---

#### [Scene 2: Overview of the Package]

**[Screen Recording: Project Overview]**

**Host (Voiceover):**  
"The Faxt Invenbin package is designed for comprehensive inventory management within a Laravel-based ERP system. It includes features like product management, inventory tracking, usage logging, and more. You can manage product categories, types, statuses, bills of material, and the items within those bills."

**[Text Overlay: Features of Faxt Invenbin]**

---

#### [Scene 3: Installation Steps]

**[Screen Recording: Terminal and Composer Setup]**

**Host (Voiceover):**  
"Let's dive into the installation process. First, clone your project repository if you haven't done so already. Then, configure your environment variables in the .env file. After that, run the following command to install the necessary PHP dependencies:"

**[Text Overlay:**
```
composer install
``]**

**Host (Voiceover):**  
"Next, migrate the database tables by running:"

**[Text Overlay:**
```
php artisan migrate
``]**

**Host (Voiceover):**  
"Now, serve your application with the following command:"

**[Text Overlay:**
```
php artisan serve
``]**

---

#### [Scene 4: Installing the Faxt Invenbin Package]

**[Screen Recording: Editing Composer.json and Requiring Package]**

**Host (Voiceover):**  
"To install the Faxt Invenbin package, first, add the repository to your composer.json file under the repositories section like this:"

**[Text Overlay:**
```
"repositories": [
    {
        "type": "path",
        "url": "packages/faxt/invenbin"
    }
]
``]**

**Host (Voiceover):**  
"Now, require the package by running the following command:"

**[Text Overlay:**
```
composer require faxt/invenbin:dev-master
``]**

**Host (Voiceover):**  
"If you want a specific version, just replace 'dev-master' with the desired version."

---

#### [Scene 5: Optional Configurations]

**[Screen Recording: Publishing Config and Setting Up Swagger]**

**Host (Voiceover):**  
"Next, publish the configuration files if needed using the following command:"

**[Text Overlay:**
```
php artisan vendor:publish --provider="Faxt\Invenbin\InvenbinServiceProvider" --tag="config"
``]**

**Host (Voiceover):**  
"If you're using Swagger, you'll need to configure the paths. Open the 'config/l5-swagger.php' file, and add the source directory for Faxt Invenbin to the paths section, like this:"

**[Text Overlay:**
```
'paths' => [
    'annotations' => [
        base_path('app'),
        base_path('packages/faxt/invenbin/src'),
    ],
    'docs' => storage_path('api-docs'),
    'views' => base_path('resources/views/vendor/l5-swagger'),
],
``]**

**Host (Voiceover):**  
"Don't forget to include the @OA\Info() annotation in your application."

---

#### [Scene 6: Registering the Admin Panel]

**[Screen Recording: Editing AppServiceProvider]**

**Host (Voiceover):**  
"To use the admin panel, you'll need to register it in your 'AppServiceProvider'. Add the following line in the 'register' method:"

**[Text Overlay:**
```
use Faxt\Invenbin\Support\Facades\InvenbinPanel;

public function register(): void
{
    InvenbinPanel::register();
}
``]**

---

#### [Scene 7: Running Migrations]

**[Screen Recording: Running Migrations in Terminal]**

**Host (Voiceover):**  
"Finally, run the package migrations to set up the necessary database tables. Use the command:"

**[Text Overlay:**
```
php artisan migrate
``]**

---

#### [Scene 8: Using the Faxt Invenbin Package]

**[Screen Recording: API Documentation and Endpoints]**

**Host (Voiceover):**  
"With everything set up, you can now access the API documentation at '/api/documentation'. Here, you can interact with the endpoints using tools like Swagger or Postman."

**[Text Overlay: Example API Endpoints]**

**Host (Voiceover):**  
"Use these endpoints to manage products, inventory, usage logs, categories, product types, statuses, bills of material, and more."

---

#### [Closing Scene: Conclusion]

**[Video Host On Screen]**

**Host:**  
"And that's it! You've successfully integrated the Faxt Invenbin package into your Laravel API project. Now you can efficiently manage your inventory and keep everything organized. If you found this tutorial helpful, don't forget to like and subscribe for more videos like this. Thanks for watching!"

**[Text Overlay: Like, Share, Subscribe]**

---

**[End of Video]**