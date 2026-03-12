-- =====================================================
-- LETA HOMES AGENCY - Database Setup Script
-- =====================================================
-- Run this script to create all necessary tables
-- for the Leta Homes Agency application
-- =====================================================

-- =====================================================
-- FOR HOSTING/cPanel: Create 'leta_homes' DB manually first!
-- 1. cPanel → MySQL Databases → Create DB 'leta_homes'
-- 2. Add user with ALL PRIVILEGES
-- 3. phpMyAdmin → Select leta_homes → Import this file
-- =====================================================
--LOCAL XAMPP: Run in terminal: mysql -u root -p < database.sql
--  OR Uncomment below (requires root privileges):
--CREATE DATABASE IF NOT EXISTS `leta_homes` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--USE `leta_homes`;

-- =====================================================
-- USERS TABLE - Stores user accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PLOTS TABLE - Stores property/plots information
-- =====================================================
CREATE TABLE IF NOT EXISTS plots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plot_name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plot_name (plot_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TENANTS TABLE - Stores tenant information
-- =====================================================
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(20) UNIQUE NOT NULL,
    plot_id INT NOT NULL,
    tenant_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    house_number VARCHAR(20),
    house_type VARCHAR(50),
    rent_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    commission_percentage DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
    deposit_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    deposit_paid ENUM('Yes', 'No') DEFAULT 'No',\n    deposit_date DATE,
    move_in_date DATE,
    status ENUM('Active', 'Inactive', 'Moved Out') DEFAULT 'Active',
    UNIQUE KEY unique_house_plot (house_number, plot_id),
    INDEX idx_deposit (deposit_paid, deposit_date)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_plot_id (plot_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- RENT PAYMENTS TABLE - Stores rent payment records
-- =====================================================
CREATE TABLE IF NOT EXISTS rent_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    commission_percentage DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
    commission_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    payment_status ENUM('Pending', 'Paid', 'Partial', 'Cancelled') DEFAULT 'Paid',
payment_month DATE NOT NULL,
    payment_year YEAR NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_month (payment_month),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_year (payment_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VIEW: Tenant Summary (for arrears/statements)
-- =====================================================
CREATE OR REPLACE VIEW vw_tenant_summary AS
SELECT 
    t.id, t.tenant_id, t.tenant_name, t.plot_id, p.plot_name,
    t.house_number, t.house_type, t.rent_amount, t.move_in_date, t.status,
    t.deposit_amount, t.deposit_paid, t.deposit_date,
    COALESCE(SUM(rp.amount_paid), 0) as total_paid,
    COUNT(rp.id) as payments_count,
    ROUND((DATEDIFF(CURDATE(), t.move_in_date)/30) * t.rent_amount - COALESCE(SUM(rp.amount_paid), 0), 2) as arrears
FROM tenants t
LEFT JOIN plots p ON t.plot_id = p.id
LEFT JOIN rent_payments rp ON t.id = rp.tenant_id AND rp.payment_status = 'Paid'
GROUP BY t.id;

-- =====================================================
-- VIEW: Monthly Reports
-- =====================================================
CREATE OR REPLACE VIEW vw_monthly_reports AS
SELECT 
    YEAR(payment_date) as year,
    MONTH(payment_date) as month,
    COUNT(*) as total_payments,
    SUM(amount_paid) as total_collected,
    SUM(commission_amount) as total_commission,
    AVG(commission_percentage) as avg_commission_rate
FROM rent_payments 
WHERE payment_status = 'Paid'
GROUP BY YEAR(payment_date), MONTH(payment_date)
ORDER BY year DESC, month DESC;


-- =====================================================
-- RECEIPTS TABLE - Stores receipt information
-- =====================================================
CREATE TABLE IF NOT EXISTS receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(30) UNIQUE NOT NULL,
    payment_id INT NOT NULL,
    tenant_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES rent_payments(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_receipt_number (receipt_number),
    INDEX idx_payment_id (payment_id),
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT ADMIN USER (if not exists)
-- =====================================================
INSERT INTO users (user_id, full_name, username, email, password)
SELECT 'USR-2026-0001', 'Administrator', 'admin', 'admin@leta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
FROM DUAL
WHERE NOT EXISTS (SELECT username FROM users WHERE username = 'admin');

-- =====================================================
-- SAMPLE DATA - Insert sample plots and tenants (for testing)
-- =====================================================

-- Sample plots
INSERT INTO plots (plot_name, location) VALUES 
('Sunrise Apartments', 'Kenyatta Avenue, Nairobi'),
('Green Valley Heights', 'Mombasa Road, Nairobi'),
('Riverside Gardens', 'Kiserian Road, Nairobi'),
('Metro Plaza', 'Central Business District, Nairobi')
ON DUPLICATE KEY UPDATE location = VALUES(location);

-- Sample tenants (only if plots exist and no tenants yet)
INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, rent_amount, commission_percentage, deposit_amount, deposit_paid, deposit_date, move_in_date, status)\nSELECT 'TH-2026-0001', p.id, 'John Doe', '0722123456', '101', '1 Bedroom', 15000.00, 10.00, 30000.00, 'Yes', '2026-01-01', '2026-01-01', 'Active'\nFROM plots p WHERE p.plot_name = 'Sunrise Apartments'\nAND NOT EXISTS (SELECT 1 FROM tenants WHERE tenant_id = 'TH-2026-0001');

INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, rent_amount, commission_percentage, deposit_amount, deposit_paid, deposit_date, move_in_date, status)\nSELECT 'TH-2026-0002', p.id, 'Jane Smith', '0722987654', '202', '2 Bedroom', 25000.00, 10.00, 50000.00, 'Yes', '2026-01-15', '2026-01-15', 'Active'\nFROM plots p WHERE p.plot_name = 'Green Valley Heights'\nAND NOT EXISTS (SELECT 1 FROM tenants WHERE tenant_id = 'TH-2026-0002');

INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, rent_amount, commission_percentage, deposit_amount, deposit_paid, deposit_date, move_in_date, status)\nSELECT 'TH-2026-0003', p.id, 'Michael Johnson', '0711234567', 'A1', 'Studio', 10000.00, 10.00, 0.00, 'No', NULL, '2026-02-01', 'Active'\nFROM plots p WHERE p.plot_name = 'Riverside Gardens'\nAND NOT EXISTS (SELECT 1 FROM tenants WHERE tenant_id = 'TH-2026-0003');

-- =====================================================
-- VERIFY DATABASE SETUP
-- =====================================================
SELECT 'Database setup completed successfully!' AS status;
SELECT 'Tables created:' AS info;
SHOW TABLES;

