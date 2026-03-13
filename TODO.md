# Leta Homes Agency - Supabase Login Fix TODO

## [ ] 1. Update Supabase Credentials
- Edit `php/config.php`: Replace [YOUR-PASSWORD] with real postgres password from Supabase dashboard.
- DSN: `postgresql://postgres.REALPASSWORD@db.bvecgfpvogpmapquoaoc.supabase.co:5432/postgres`

## [ ] 2. Setup Database Schema
- Copy **FULL** `database.sql` content.
- Paste in Supabase Dashboard → SQL Editor → RUN.
- Verify: `SELECT * FROM users;` shows admin.

## [ ] 3. Test Connection
- Visit `http://localhost/leta_homes_agency/test_supabase.php`
- Expect: ✅ Connection, Tables, Admin User.

## [ ] 4. Fix PHP Files (MySQLi → PDO)
- php/dashboard.php: Convert mysqli to PDO.
- php/add_plot.php, php/plots.php, etc.

## [ ] 5. Test Login
- http://localhost/leta_homes_agency/html/login.html
- Username: `admin` Password: `password`

## [ ] 6. Test Dashboard
- Data loads without errors.

**Next: Provide your Supabase postgres password to update config.php. Or run steps 1-3 manually.**
