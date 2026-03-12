# Modify Login to Accept Custom Password (admin/password)

## Steps:
1. [x] Create this TODO.md
2. [ ] Edit login.php - change hardcoded password check to 'password' and update docs
3. [ ] Edit php/login.php - change admin hash creation to password_hash('password', PASSWORD_DEFAULT)
4. [ ] Edit html/login.html - update default login docs to admin/password
5. [ ] Edit php/login_pdo.php - ensure accepts 'password' (legacy fallback)
6. [ ] Test login with admin/password
7. [ ] Complete task
