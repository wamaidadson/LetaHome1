# Render Deployment TODO for Leta Homes Agency

## Plan Breakdown & Progress Tracking

### 1. ✅ Update Database Configuration Files [Completed]
- [x] Edit `config.php` to use environment variables with local fallback
- [x] Edit `php/config.php` to sync changes
- [x] Update `test_connection.php` for env var support

### 3. ✅ Create Deployment Files [Completed]
- [x] Created `render.yaml` for Render service config
- [x] Created `RENDER_DEPLOY_GUIDE.md` with full instructions
- [ ] Create `render.yaml` for Render service config
- [ ] Create/update `README.md` with full deployment guide
- [ ] Update `.gitignore` if needed (ignore local SQL dumps)

### 4. GitHub Preparation [Manual]
- [ ] Initialize git repo: `git init`
- [ ] Add remote: `git remote add origin <your-github-repo>`
- [ ] Commit: `git add . && git commit -m "Prepare for Render deploy"`
- [ ] Push: `git push -u origin main`

### 5. Deploy on Render [Manual]
- [ ] New → Web Service → Connect GitHub repo
- [ ] Runtime: PHP
- [ ] Build: `echo "No build required"`
- [ ] Add Clever Cloud DB as external resource
- [ ] Auto-deploy enabled
- [ ] Test live URL, check logs for DB connection

### 6. Post-Deployment [Manual]
- [ ] Test login, add tenant/plot, record payment
- [ ] Customize domain if needed
- [ ] Monitor logs

**Current Status: Code changes done. Next: User runs DB import on Clever Cloud, pushes to GitHub, deploys on Render.**

