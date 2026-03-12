# 🚀 Leta Homes Agency - Render Deployment Guide

## Prerequisites
1. **Clever Cloud MySQL Database** (provided):
   ```
   Host: bfyduaxvse8fo9grcafy-mysql.services.clever-cloud.com
   DB: bfyduaxvse8fo9grcafy
   User: ut2cx39hzatnrjn4
   Port: 3306
   Password: tRiMsboftE5B02SbLLr4
   ```

2. **GitHub Account** with public/private repo.

## Step 1: Setup Database on Clever Cloud
1. Login to [Clever Cloud Dashboard](https://console.clever-cloud.com)
2. Navigate to your MySQL addon → **phpMyAdmin** or **External Access**
3. **Import** `database.sql` file to database `bfyduaxvse8fo9grcafy`
4. Verify tables created: `users`, `plots`, `tenants`, `rent_payments`, `receipts`
5. **Default Login**: `admin` / `admin123`

## Step 2: Prepare & Push to GitHub
```bash
cd c:/xampp1/htdocs/leta_homes_agency
git init
git add .
git commit -m "Initial commit: Leta Homes Agency with Render support"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/leta-homes-agency.git
git push -u origin main
```

## Step 3: Deploy on Render
1. Go to [render.com](https://render.com) → **New+** → **Web Service**
2. Connect GitHub → Select `leta-homes-agency` repo
3. **Runtime**: PHP 8.2
4. **Build Command**: `echo "No build required"`
5. **Start Command**: Leave empty (Render auto-serves PHP)
6. **Environment Variables** (add these):
   ```
   MYSQL_ADDON_HOST=bfyduaxvse8fo9grcafy-mysql.services.clever-cloud.com
   MYSQL_ADDON_DB=bfyduaxvse8fo9grcafy
   MYSQL_ADDON_USER=ut2cx39hzatnrjn4
   MYSQL_ADDON_PASSWORD=tRiMsboftE5B02SbLLr4
   MYSQL_ADDON_PORT=3306
   ```
7. **Advanced**: Upload `render.yaml` for predefined config
8. Click **Create Web Service** → Auto-deploy!

## Step 4: Test Deployment
1. Visit your Render URL (e.g., `https://leta-homes-agency.onrender.com`)
2. **Test Connection**: `/test_connection.php` (should connect to Clever Cloud)
3. **Login**: `admin` / `admin123`
4. Test features: Dashboard, Add Plot/Tenant, Record Payment

## Troubleshooting
- **DB Connection Error**: Check env vars in Render dashboard
- **500 Error**: View **Logs** tab → Enable error reporting
- **Slow Startup**: Render free tier sleeps after inactivity
- **Static Assets**: Served automatically from `assets/`

## Customization
- **Custom Domain**: Render Dashboard → Settings → Custom Domains
- **Upgrade**: Starter ($7/mo) for always-on + faster builds

**Your app is now LIVE on Render with production MySQL! 🎉**

See `TODO.md` for progress tracking.

