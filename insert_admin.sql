-- Add admin user for login (admin/password)
INSERT INTO users (user_id, full_name, username, email, password, created_at) 
VALUES ('USR-2026-0001', 'Administrator', 'admin', 'admin@leta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW())
ON CONFLICT (username) DO UPDATE SET 
    password = EXCLUDED.password;

