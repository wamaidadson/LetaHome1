# Quick Hosting Guide - No XAMPP Required

## Free Hosting Options

Yes! Here are free hosting providers:

| Provider | Features | Link |
|----------|----------|------|
| 000WebHost | 1GB storage, PHP, MySQL | 000webhost.com |
| FreeHostia | 250MB storage, PHP, MySQL | freehostia.com |
| Byet.Host | 1GB storage, PHP, MySQL | byet.host |
| InfinityFree | Unlimited storage, PHP, MySQL | infinityfree.net |

**Note:** Free hosting may have limitations (ads, slower speed, limited storage).

---

## What You Need

1. **A domain name** (e.g., yourwebsite.com) - optional with free hosting
2. **Web hosting** with PHP & MySQL support

---

## Step 1: Get Web Hosting

Recommended providers (all have PHP & MySQL pre-installed):

| Provider | Price | Link |
|----------|-------|------|
| SiteGround | $2.99/mo | siteground.com |
| Bluehost | $2.95/mo | bluehost.com |
| HostGator | $2.75/mo | hostgator.com |
| Namecheap | $1.58/mo | namecheap.com |

**What to buy:**
- Shared Hosting plan (cheapest)
- Register a domain name (or use one you already have)

---

## Step 2: Create Database

After buying hosting:

1. **Login to your hosting control panel** (cPanel)
2. Look for **"MySQL Databases"** or **"Databases"**
3. Create a new database:
   - Database name: `leta_homes`
   - Click Create
4. Create a database user:
   - Username: `letauser`
   - Password: Create a strong password (copy it!)
5. **Add user to database** - select ALL privileges

**Save these details:**
- Database name: `leta_homes`
- Database user: `letauser`
- Database password: [your password]
- Host: usually `localhost`

---

## Step 3: Import Database

1. In cPanel, find **"phpMyAdmin"**
2. Click on your database (`leta_homes`)
3. Click **"Import"** tab
4. Click **"Choose File"** and select `database.sql`
5. Click **"Go"** or **"Import"**

---

## Step 4: Upload Files

**Option A: Using File Manager (Easiest)**
1. In cPanel, open **"File Manager"**
2. Go to `public_html` folder
3. Click **"Upload"**
4. Upload ALL files from your project folder
5. Make sure to upload all folders (html, php, assets, etc.)

**Option B: Using FTP**
1. Download FileZilla (free) from filezilla-project.org
2. Get FTP credentials from cPanel → "FTP Accounts"
3. Connect and drag files to `public_html`

---

## Step 5: Configure Database Connection

1. In File Manager, go to `/php/` folder
2. Find and edit `config.php`
3. Update these lines with your database details:

```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'letauser');
define('DB_PASSWORD', 'your_database_password');
define('DB_NAME', 'leta_homes');
define('DB_PORT', '3306');
```

4. Save the file

---

## Step 6: Access Your Website

That's it! Open your browser and go to:

```
http://yourdomain.com/html/
```

**Login:**
- Username: `admin`
- Password: `password`

---

## Important: Change Admin Password

After logging in:
1. Go to settings/profile
2. Change the default password immediately!

---

## Troubleshooting

**"Database Connection Error"**
- Check config.php has correct database name, user, password
- Make sure user has privileges to access database

**"Page Not Found"**
- Make sure all files are in public_html
- Check that html folder exists

**"This site can't be reached"**
- Wait 24 hours for domain to propagate
- Check DNS settings at your domain registrar

---

## Need Help?

Contact your hosting provider's support team - they're available 24/7 to help!

