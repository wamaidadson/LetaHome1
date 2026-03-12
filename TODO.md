# Leta Homes Agency - Database Enhancement TODO

**Status: [IN PROGRESS]**

## Step 1: [PENDING] ✅ Update database.sql with deposit columns, views, indexes, and samples
- Add deposit_amount, deposit_paid, deposit_date to tenants table
- Update sample tenants with deposit data
- Add vw_tenant_summary and vw_monthly_reports views
- Add UNIQUE INDEX on tenants(house_number, plot_id)
- Add INDEX on rent_payments(payment_year)
- Uncomment/fix XAMPP automation section with warnings

## Step 2: [PENDING] Update setup_database.php admin password hash
- Change to match database.sql: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` ('password')

## Step 3: [PENDING] Deprecate add_deposit_columns.php
- Add header comment: DEPRECATED - use database.sql instead
- Mark as safe to delete after verification

## Step 4: [PENDING] Test & Verify
```
1. mysql -u root -p < database.sql
2. Check phpMyAdmin: tables, columns, samples, views
3. php setup_database.php (should detect existing)
4. App test: localhost/leta_homes_agency → login admin/password
5. Test CRUD + payments + statements
```

## Step 5: [PENDING] Documentation
- Update README/QUICK_START_GUIDE.md with new features
- Confirm RENDER deployment compatibility

**Next Action:** Complete Step 1 → Mark as done → Proceed to Step 2

