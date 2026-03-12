# Leta Homes Agency - Supabase Setup Guide

## 1. Database Setup (5 min)
1. Open Supabase Dashboard → SQL Editor
2. Copy **ENTIRE** `database.sql` content
3. Paste → **RUN** 
4. Verify:
   ```
   SELECT * FROM users;  -- admin user
   SELECT * FROM tenants;  -- 3 samples
   SELECT * FROM vw_tenant_summary;  -- views
   ```

## 2. PHP Connection Config
**File:** `php/config_supabase.php` → Save as `php/config.php` (backup old MySQL)

**Update line 8:**
```
define('DB_DSN', 'postgresql://postgres.YOUR_ACTUAL_PASSWORD@db.bvecgfpvogpmapquoaoc.supabase.co:5432/postgres');
```

**Supabase Details:**
- Host: `db.bvecgfpvogpmapquoaoc.supabase.co`
- Port: `5432`
- Database: `postgres`
- User: `postgres`
- Password: From Supabase → Settings → Database

## 3. Test Connection
```
http://localhost/leta_homes_agency/test_supabase.php
```
Expected:
```
✅ Connection Success!
PostgreSQL: 15.x
Tables: users, plots, tenants, rent_payments, receipts
Admin User: admin
Sample Tenants: 3
```

## 4. Launch App
```
http://localhost/leta_homes_agency/
```
Login: `admin`

## 5. Production Notes
- **RLS (Row Level Security):** Disabled by default (matches current app).
- **Env Vars:** Use for password in production.
- **Views:** Arrears auto-calculated, reports ready.

**Ready! 🚀 No further configuration needed.**

