can # Hosting Guide for Leta Homes Agency

## Local Development (Using XAMPP)

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP with PHP and MySQL modules
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Set Up Database
1. Open phpMyAdmin at http://localhost/phpmyadmin
2. Create a new database named `leta_homes`
3. Click on the "Import" tab
4. Select the `database.sql` file from this project
5. Click "Go" to import

### Step 3: Configure Database Connection
Edit `php/config.php` if your database credentials differ:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Default XAMPP has no password
define('DB_NAME', 'leta_homes');
define('DB_PORT', '3306');
```

### Step 4: Access the Application
- Open your browser and go to: http://localhost/leta_homes_agency/html/
- Login with default admin credentials:
  - Username: `admin`
  - Password: `password`

---

## Production Hosting (Live Server)

### Option 1: Shared Hosting (cPanel)

#### Step 1: Upload Files
1. Login to cPanel
2. Go to "File Manager" → "public_html"
3. Upload all project files to a new folder (e.g., `leta_homes`)
4. Or upload to public_html for root access

#### Step 2: Create Database
1. In cPanel, go to "MySQL Databases"
2. Create a new database (e.g., `letahomes_agency`)
3. Create a database user with a strong password
4. Add user to database with all privileges

#### Step 3: Import Database
1. Go to "phpMyAdmin" in cPanel
2. Select your database
3. Import the `database.sql` file

#### Step 4: Update Configuration
Edit `php/config.php` with your live database credentials:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_db_username');
define('DB_PASSWORD', 'your_db_password');
define('DB_NAME', 'letahomes_agency');
define('DB_PORT', '3306');
```

#### Step 5: Access Your Site
- Go to http://yourdomain.com/leta_homes/html/
- Or http://yourdomain.com/ if you uploaded to public_html

---

### Option 2: VPS/Cloud Server (DigitalOcean, AWS, etc.)

#### Step 1: Server Setup
1. Create a VPS with Ubuntu 20.04 or newer
2. Install LAMP stack:
```bash
sudo apt update
sudo apt install apache2 php mysql-server php-mysql
```

#### Step 2: Configure MySQL
```bash
sudo mysql_secure_installation
sudo mysql
CREATE DATABASE leta_homes;
CREATE USER 'letauser'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON leta_homes.* TO 'letauser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 3: Import Database
```bash
mysql -u letauser -p leta_homes < /path/to/database.sql
```

#### Step 4: Upload Files
```bash
sudo cp -r /path/to/project /var/www/html/leta_homes
sudo chown -R www-data:www-data /var/www/html/leta_homes
```

#### Step 5: Configure Apache
```bash
sudo nano /etc/apache2/sites-available/leta_homes.conf
```
Add:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/leta_homes
    <Directory /var/www/html/leta_homes>
        AllowOverride All
    </Directory>
</VirtualHost>
```
Then:
```bash
sudo a2enmod rewrite
sudo a2ensite leta_homes.conf
sudo systemctl restart apache2
```

#### Step 6: Update PHP Configuration
Edit `php/config.php` with your live database credentials as shown above.

---

## Important Security Tips for Production

1. **Change Default Admin Password**
   - Login to admin account
   - Go to settings and change password

2. **Enable HTTPS**
   - Install SSL certificate (Let's Encrypt is free)
   - Force HTTPS in `.htaccess` or Apache config

3. **Secure Database Credentials**
   - Never use 'root' database user in production
   - Use strong passwords

4. **File Permissions**
```bash
sudo find /var/www/html -type f -exec chmod 644 {} \;
sudo find /var/www/html -type d -exec chmod 755 {} \;
```

5. **Hide Sensitive Files**
   - Add to `.htaccess`:
```apache
# Block access to config files
<FilesMatch "^(config|database)\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## Troubleshooting

### Common Issues:

1. **"Database Connection Error"**
   - Check database credentials in php/config.php
   - Verify MySQL service is running
   - Check database name exists

2. **"Session Not Working"**
   - Check PHP session configuration
   - Ensure session directory is writable

3. **"404 Not Found"**
   - Enable mod_rewrite
   - Check .htaccess file exists

4. **"Permission Denied"**
   - Check file ownership: `www-data:www-data`
   - Verify directory permissions are 755
   - Verify file permissions are 644

---

## Support

For issues or questions, please refer to the project documentation or contact the developer.

---

## Hosting on Netlify (Limited - Requires External Database)

Netlify only supports static sites (HTML/CSS/JS) and doesn't run PHP or MySQL. However, you can host the **frontend** on Netlify with an external database service.

### Option 1: Netlify + PlanetScale (MySQL)

**PlanetScale** is a free MySQL-compatible serverless database.

#### Step 1: Set Up PlanetScale Database
1. Go to https://planetscale.com/ and sign up (free tier available)
2. Create a new database
3. Get your database connection string

#### Step 2: Deploy Frontend to Netlify
1. Create a Netlify account at https://netlify.com
2. Connect your GitHub repository
3. Set build command: leave empty
4. Publish directory: html
5. Deploy

#### Step 3: Update Configuration
You'll need to create a serverless API (using Netlify Functions) or use a different hosting for the PHP backend.

---

## Alternative: Convert to Static Site

If you want full Netlify hosting, you would need to convert to a static site using:
- **Frontend**: The current HTML/CSS/JS (works on Netlify)
- **Backend**: Netlify Functions or external API
- **Database**: Firebase, Supabase, or PlanetScale

This would require significant code changes to convert PHP to JavaScript/Node.js.

