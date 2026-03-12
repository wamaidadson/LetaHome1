@echo off
echo Creating Leta Homes database...
echo.
echo This will create the database "leta_homes" and import the schema.
echo Press Ctrl+C to cancel.
pause

"C:\xampp1\mysql\bin\mysql.exe" -u root -p --execute="CREATE DATABASE IF NOT EXISTS \`leta_homes\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE \`leta_homes\`;" 

if %ERRORLEVEL% == 0 (
    echo.
    echo Importing schema from database.sql...
    "C:\xampp1\mysql\bin\mysql.exe" -u root -p leta_homes < database.sql
    if %ERRORLEVEL% == 0 (
        echo.
        echo Database setup completed successfully!
        echo Run: C:\xampp1\htdocs\leta_homes_agency\test_connection.php to verify
    ) else (
        echo Error importing schema. Check database.sql syntax.
    )
) else (
    echo Error creating database. Check XAMPP MySQL service.
)

pause

