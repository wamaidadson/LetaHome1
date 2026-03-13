-- LETA HOMES AGENCY - MySQL Database Setup (XAMPP Compatible)
-- Run in phpMyAdmin or mysql CLI: mysql -u root -p leta_homes_agency < database_mysql.sql

-- Drop existing for clean setup
DROP VIEW IF EXISTS `vw_monthly_reports`;
DROP VIEW IF EXISTS `vw_tenant_summary`;
DROP TABLE IF EXISTS `receipts`;
DROP TABLE IF EXISTS `rent_payments`;
DROP TABLE IF EXISTS `tenants`;
DROP TABLE IF EXISTS `plots`;
DROP TABLE IF EXISTS `users`;

-- USERS TABLE
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(20) UNIQUE NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- PLOTS TABLE
CREATE TABLE `plots` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plot_name` VARCHAR(100) NOT NULL,
    `location` VARCHAR(200),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TENANT STATUS ENUM equivalent (TINYINT)
CREATE TABLE `tenant_statuses` (
    `id` TINYINT PRIMARY KEY,
    `name` VARCHAR(20) NOT NULL
);
INSERT INTO `tenant_statuses` (`id`, `name`) VALUES (1, 'Active'), (2, 'Inactive'), (3, 'Moved Out');

-- PAYMENT STATUS
CREATE TABLE `payment_statuses` (
    `id` TINYINT PRIMARY KEY,
    `name` VARCHAR(20) NOT NULL
);
INSERT INTO `payment_statuses` (`id`, `name`) VALUES (1, 'Pending'), (2, 'Paid'), (3, 'Partial'), (4, 'Cancelled');

-- DEPOSIT STATUS
CREATE TABLE `deposit_statuses` (
    `id` TINYINT PRIMARY KEY,
    `name` VARCHAR(3) NOT NULL
);
INSERT INTO `deposit_statuses` (`id`, `name`) VALUES (1, 'Yes'), (2, 'No');

-- TENANTS TABLE
CREATE TABLE `tenants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` VARCHAR(20) UNIQUE NOT NULL,
    `plot_id` INT NOT NULL,
    `tenant_name` VARCHAR(100) NOT NULL,
    `phone_number` VARCHAR(20),
    `house_number` VARCHAR(20),
    `house_type` VARCHAR(50),
    `rent_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `commission_percentage` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    `deposit_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `deposit_status_id` TINYINT DEFAULT 2,
    `deposit_date` DATE,
    `move_in_date` DATE,
    `status_id` TINYINT DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`plot_id`) REFERENCES `plots`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`deposit_status_id`) REFERENCES `deposit_statuses`(`id`),
    FOREIGN KEY (`status_id`) REFERENCES `tenant_statuses`(`id`),
    UNIQUE KEY `unique_house_plot` (`house_number`, `plot_id`)
) ENGINE=InnoDB;

-- RENT PAYMENTS TABLE
CREATE TABLE `rent_payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `payment_date` DATE NOT NULL,
    `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `commission_percentage` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    `commission_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_status_id` TINYINT DEFAULT 2,
    `payment_month` DATE NOT NULL,
    `payment_year` INT NOT NULL,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`payment_status_id`) REFERENCES `payment_statuses`(`id`)
) ENGINE=InnoDB;

-- RECEIPTS TABLE
CREATE TABLE `receipts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `receipt_number` VARCHAR(30) UNIQUE NOT NULL,
    `payment_id` INT NOT NULL,
    `tenant_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`payment_id`) REFERENCES `rent_payments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- VIEWS
CREATE VIEW `vw_tenant_summary` AS
SELECT 
    t.id, t.tenant_id, t.tenant_name, t.plot_id, p.plot_name,
    t.house_number, t.house_type, t.rent_amount, t.move_in_date, ts.name as status,
    t.deposit_amount, ds.name as deposit_paid, t.deposit_date,
    COALESCE(SUM(rp.amount_paid), 0) AS total_paid,
    COUNT(rp.id) AS payments_count,
    ROUND(DATEDIFF(NOW(), t.move_in_date)/30 * t.rent_amount - COALESCE(SUM(rp.amount_paid),0), 2) AS arrears
FROM tenants t
LEFT JOIN plots p ON t.plot_id = p.id
LEFT JOIN tenant_statuses ts ON t.status_id = ts.id
LEFT JOIN deposit_statuses ds ON t.deposit_status_id = ds.id
LEFT JOIN rent_payments rp ON t.id = rp.tenant_id AND rp.payment_status_id=2
GROUP BY t.id, p.plot_name, ts.name, ds.name;

CREATE VIEW `vw_monthly_reports` AS
SELECT 
    YEAR(payment_date) AS year,
    MONTH(payment_date) AS month,
    COUNT(*) AS total_payments,
    SUM(amount_paid) AS total_collected,
    SUM(commission_amount) AS total_commission,
    AVG(commission_percentage) AS avg_commission_rate
FROM rent_payments
WHERE payment_status_id=2
GROUP BY YEAR(payment_date), MONTH(payment_date)
ORDER BY year DESC, month DESC;

-- DEFAULT ADMIN
INSERT IGNORE INTO `users` (`user_id`, `full_name`, `username`, `email`, `password`) VALUES 
('USR-2026-0001','Administrator','admin','admin@leta.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- SAMPLE PLOTS
INSERT IGNORE INTO `plots` (`plot_name`, `location`) VALUES
('Sunrise Apartments','Kenyatta Avenue, Nairobi'),
('Green Valley Heights','Mombasa Road, Nairobi'),
('Riverside Gardens','Kiserian Road, Nairobi'),
('Metro Plaza','Central Business District, Nairobi');

-- SAMPLE TENANTS (after plots)
INSERT IGNORE INTO `tenants` (`tenant_id`, `plot_id`, `tenant_name`, `phone_number`, `house_number`, `house_type`, `rent_amount`, `commission_percentage`, `deposit_amount`, `deposit_status_id`, `deposit_date`, `move_in_date`, `status_id`) VALUES
('TH-2026-0001', 1, 'John Doe', '0722123456', '101', '1 Bedroom', 15000.00, 10.00, 30000.00, 1, '2026-01-01','2026-01-01',1),
('TH-2026-0002', 2, 'Jane Smith', '0722987654', '202', '2 Bedroom', 25000.00, 10.00, 50000.00, 1, '2026-01-15','2026-01-15',1);

-- Indexes for performance
CREATE INDEX `idx_plot_name` ON `plots` (`plot_name`);
CREATE INDEX `idx_tenant_id` ON `tenants` (`tenant_id`);
CREATE INDEX `idx_plot_id` ON `tenants` (`plot_id`);
CREATE INDEX `idx_status` ON `tenants` (`status_id`);

SELECT 'Database setup completed successfully for MySQL!' AS status;

