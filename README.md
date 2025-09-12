# Prasso 

Prasso is an open-source rapid prototyping and app development platform. It includes both a mobile app and an API backend site.

The Prasso API serves data and pages to the mobile app. It provides the backend that enables low-code assembly of functional web and mobile apps.

## Key Benefits

- Simplified app prototyping 
- Customization options
- Time savings compared to traditional development
- Robust and scalable Laravel API backend
- Easy integration with tools like Firebase
- Comprehensive documentation

Prasso allows you to quickly build a mobile app by configuring tabs in the admin that point to pages served by the Prasso API. This enables rapid validation and iteration on app ideas.

## How It Works

**Smart Construction:** Easily create customized mobile and web applications using Prasso's all-in-one platform template. Benefit from our streamlined app prototype process, single setup form, and rapid prototyping tool, all engineered to boost your development workflow.

**Verified Stability:** Benefit from a stable Laravel framework-based API backend, seamless user registration, app configuration with tabs and views, and team-based association and roles, ensuring your app's stability and reliability.

**Effortless Coding:** Experience the ease of Prasso's Low-Code functionality, transforming your ideas into fully functional apps. Our template seamlessly combines a robust backend API with a mobile app interface, ready to manage every aspect of your business operations with simplicity and efficiency.

**Rapid Validation:** Validate your app concept swiftly through rapid deployment, and showcase business information while effortlessly serving Prasso apps through the API.

**User-Friendly Experience:** Enjoy easy setup and integration with Firebase, access extensive documentation, and receive friendly support every step of the way. Faxt.com is open-source and licensed under MIT for seamless customization and collaboration.

## Setup Instructions

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL 5.7+ or MariaDB 10.3+
- Git

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/prasso_api.git
   cd prasso_api
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```
   
   This will install all required PHP packages including:
   - Laravel Framework 11.0
   - Laravel Jetstream 5.0
   - Laravel Sanctum 4.0
   - Laravel Cashier 15.4
   - Filament 3.2
   - Livewire 3.0
   - Intervention/Image 2.7
   - Twilio SDK 6.27
   - Swagger API Documentation (darkaonline/l5-swagger 8.6)
   - AWS S3 Integration (league/flysystem-aws-s3-v3 3.19)
   - Google Calendar Integration (spatie/laravel-google-calendar 3.8)
   
3. **Install local packages**
   
   The project uses several local packages that will be automatically installed from the packages directory:
   - faxt/invenbin
   - prasso/autoprohub
   - prasso/bedrock-html-editor
   - prasso/messaging
   - prasso/project_management

4. **Install JavaScript dependencies**
   ```bash
   npm install
   ```
   
   This will install all required JavaScript packages including:
   - TailwindCSS 3.4
   - Alpine.js 3.13
   - Bootstrap 5.3
   - Laravel Mix 6.0
   - PostCSS 8.5
   - Axios 0.21

5. **Set up environment file**
   ```bash
   cp env_example .env
   ```
   
   Edit the `.env` file to configure:
   - Database connection
   - Mail settings
   - AWS S3 credentials (if using S3 storage)
   - Bedrock HTML Editor settings
   - Twilio SMS settings (if using SMS)
   - Firebase configuration
   - GitHub API credentials (if needed)

6. **Generate application key**
   ```bash
   php artisan key:generate
   ```

7. **Run database migrations**
   ```bash
   php artisan migrate
   ```

8. **Seed the database (optional)**
   ```bash
   php artisan db:seed
   ```

9. **Compile assets**
   ```bash
   npm run dev
   ```
   
   For production:
   ```bash
   npm run prod
   ```

10. **Generate API documentation (optional)**
    ```bash
    php artisan l5-swagger:generate
    ```

11. **Start the development server**
    ```bash
    php artisan serve
    ```
    
    The application will be available at http://localhost:8000

### Additional Configuration

#### Storage Setup

```bash
php artisan storage:link
```

#### Scheduled Tasks

Add this Cron entry to your server to run Laravel's scheduled tasks:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

#### Queue Worker (if using queues)

```bash
php artisan queue:work
```

## Documentation

For more details, check out the other docs:

- [User Guide](docs/user-guide.md) 
- [Getting Started](docs/getting-started.md)
- [Technical Documentation](docs/technical.md)
- [Frequently Asked Questions](docs/faq.md)
