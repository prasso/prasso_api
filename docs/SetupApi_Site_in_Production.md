NOTE: to view local developer setup details see this document: [technical.md](technical.md)

# Procedure: Setting up Prasso Application

## Table of Contents

1. [Clone the Code](#clone-the-code)
2. [Make Configuration Changes](#make-configuration-changes)
3. [Initial Setup](#initial-setup)
4. [MySQL Installation](#mysql-installation)
5. [Environment Setup](#environment-setup)
6. [Composer Installation](#composer-installation)
7. [Configure Apache](#configure-apache)
8. [Database Setup](#database-setup)
9. [Testing](#testing)

## Clone the Code

1. Clone the code from [Prasso GitHub Repository](https://github.com/prasso/prasso_api).
   ```bash
   git clone https://github.com/prasso/prasso_api.git
   ```

## Make Configuration Changes

2. Make the changes suggested in the documentation at `{project code path}/docs/technical.md` under the section #installation and configuration.

## Initial Setup

3. Log in with a user that can use sudo:
   ```bash
   sudo -i
   ```
   Required PHP Extensions:
   - fileinfo
   - intl
   - zip
   - mysqli
   - pdo_mysql

4. Change to the `/var/www/html` directory:
   ```bash
   git clone https://github.com/Prasso/Api_Site.git your_prasso_folder
   cd your_prasso_folder
   ```

5. Set ownership for the newly created folder:
   ```bash
   chown -R yourserveruser:yourserveruser your_prasso_folder
   ```
   Logout of sudo. Ensure the user you are using is the one just added as owner. Log out and in if needed.

## MySQL Installation

1. Update package information:
   ```bash
   sudo apt update
   ```

2. Install MySQL:
   ```bash
   sudo apt install mysql-server
   ```

3. Secure the MySQL installation:
   ```bash
   sudo mysql_secure_installation
   ```

4. Log in to MySQL:
   ```bash
   sudo mysql -u root -p
   ```

5. Create a database and user for the application:
   ```sql
   CREATE DATABASE your_prasso_folder;
   CREATE USER 'yourserveruser'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON your_prasso_folder.* TO 'yourserveruser'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

## Environment Setup

1. Copy the example `.env` file and configure it with your details:
   ```bash
   cp .env.example .env
   nano .env
   ```
   - Update the database configuration:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=your_prasso_folder
     DB_USERNAME=yourserveruser
     DB_PASSWORD=your_password
     ```

2. Generate the application key:
   ```bash
   php artisan key:generate
   ```

## Composer Installation

1. Change to the application directory:
   ```bash
   cd /var/www/html/your_prasso_folder
   ```

2. Install Composer dependencies:
   ```bash
   composer install
   ```

3. Get sudo again and set appropriate permissions:
   ```bash
   sudo chown -R www-data:www-data public/uploads/
   sudo chown -R www-data:www-data public/temp/
   sudo chown -R www-data:www-data storage/uploads
   sudo chown -R www-data:www-data storage/tmp
   sudo chgrp -R www-data storage bootstrap/cache
   sudo chmod -R ug+rwx storage bootstrap/cache
   ```

## Configure Apache

1. Edit the Apache configuration file:
   ```bash
   cd /etc/apache2/sites-available/
   sudo nano 000-default.conf
   ```

2. Copy and modify the VirtualHost block to match your new app and path.

3. Copy an SSL configuration file:
   ```bash
   sudo cp {existing_ssl_config_file} {new_ssl_config_file}
   ```

4. Edit the new SSL configuration file to match your new app and path.

5. Check your edits:
   ```bash
   sudo apachectl configtest
   ```

6. Deploy the new site:
   ```bash
   sudo a2ensite {new_ssl_config_file}
   ```

7. Reboot Apache:
   ```bash
   sudo systemctl reload apache2
   ```

8. Change to the public directory of your new site and create the `.htaccess` file:
   ```bash
   cd /var/www/html/your_prasso_folder/public/
   nano .htaccess
   ```

9. Add the following content:
   ```apache
   Options +FollowSymLinks -Indexes
   RewriteEngine On
   RewriteCond %{HTTP:Authorization} .
   RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteRule ^ index.php [L]
   ```

10. Set up DNS records in Route 53 or your domain name service provider pointing to the new site.

11. Log back into the server as the user owning the new app folder.

## Database Setup

1. Create the database. After setting up the database in `.env`, run migrations:
   ```bash
   php artisan migrate
   ```

2. Run the seed SQL found at `{project code path}/docs/prasso_initial.sql`.

3. Edit the `sites` table to add the DNS entry used for this site to the `host` field. If more than one, separate with a comma.

## Testing

1. Test by loading the site in your browser.

We appreciate your interest and contributions to Prasso!