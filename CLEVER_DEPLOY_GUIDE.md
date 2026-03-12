# Leta Homes Agency - Clever Cloud Deployment Guide

## Prerequisites
1. Clever Cloud account: https://console.clever-cloud.com
2. Git repo with this project
3. MySQL add-on (auto-created)

## Step 1: Create Application
1. Login to Clever Cloud console
2. **Add application** → **PHP**
3. Connect GitHub/GitLab repo
4. Select `leta_homes_agency`
5. Instance: **S** (dev) or **M** (prod)
6. Region: **wa9** (Europe/Africa)

## Step 2: Add MySQL Add-on
1. App Dashboard → **Add-ons**
2. **Link add-on** → **MySQL**
3. Plan: **dev** (free) or **production**
4. Note env vars: `MYSQL_ADDON_*`

## Step 3: Environment Variables
```
MYSQL_ADDON_HOST     = [auto-filled]
MYSQL_ADDON_USER     = [auto-filled]
MYSQL_ADDON_PASSWORD = [auto-filled]
MYSQL_ADDON_DB       = [auto-filled]
MYSQL_ADDON_PORT     = [auto-filled]
APP_ENV              = production
```

## Step 4: Database Setup
1. SSH to MySQL add-on or use phpMyAdmin link
2. Import `database.sql`:
```bash
mysql -h $MYSQL_ADDON_HOST -u $MYSQL_ADDON_USER -p$MYSQL_ADDON_PASSWORD $MYSQL_ADDON_DB < database.sql
```

## Step 5: Deploy
1. Push to git → auto-deploy
2. Access: `https://your-app.cleverapps.io`
3. Login: **admin** / **password**

## Security
```
1. Change admin password immediately
2. Set `APP_ENV=production` 
3. Enable HTTPS (auto on Clever Cloud)
4. Review logs in console
```

**Default Admin:**
- Username: `admin`
- Password: `password`

**Support:** Clever Cloud docs: https://www.clever-cloud.com/doc/

