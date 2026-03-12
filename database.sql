-- =====================================================
-- LETA HOMES AGENCY - Database Setup Script (Supabase PostgreSQL Ready)
-- =====================================================
-- Run this script in Supabase SQL Editor
-- =====================================================

-- Enable required extensions (UUID for IDs if needed)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- =====================================================
-- DROP EXISTING (for idempotency)
-- =====================================================
DROP VIEW IF EXISTS vw_monthly_reports CASCADE;
DROP VIEW IF EXISTS vw_tenant_summary CASCADE;
DROP TABLE IF EXISTS receipts CASCADE;
DROP TABLE IF EXISTS rent_payments CASCADE;
DROP TABLE IF EXISTS tenants CASCADE;
DROP TABLE IF EXISTS plots CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TYPE IF EXISTS tenant_status CASCADE;
DROP TYPE IF EXISTS deposit_status CASCADE;
DROP TYPE IF EXISTS payment_status CASCADE;

-- =====================================================
-- ENUM TYPES
-- =====================================================
CREATE TYPE deposit_status AS ENUM ('Yes', 'No');
CREATE TYPE tenant_status AS ENUM ('Active', 'Inactive', 'Moved Out');
CREATE TYPE payment_status AS ENUM ('Pending', 'Paid', 'Partial', 'Cancelled');

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

DROP INDEX IF EXISTS idx_username;
CREATE INDEX idx_username ON users (username);
DROP INDEX IF EXISTS idx_user_id;
CREATE INDEX idx_user_id ON users (user_id);

-- =====================================================
-- PLOTS TABLE
-- =====================================================
DROP TABLE IF EXISTS plots CASCADE;
CREATE TABLE plots (
    id SERIAL PRIMARY KEY,
    plot_name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_plot_name ON plots (plot_name);

-- =====================================================
-- TENANTS TABLE
-- =====================================================
CREATE TABLE tenants (
    id SERIAL PRIMARY KEY,
    tenant_id VARCHAR(20) UNIQUE NOT NULL,
    plot_id INTEGER NOT NULL REFERENCES plots(id) ON DELETE CASCADE,
    tenant_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    house_number VARCHAR(20),
    house_type VARCHAR(50),
    rent_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    commission_percentage DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
    deposit_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    deposit_paid deposit_status DEFAULT 'No',
    deposit_date DATE,
    move_in_date DATE,
    status tenant_status DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (house_number, plot_id)
);

DROP INDEX IF EXISTS idx_deposit;
CREATE INDEX idx_deposit ON tenants (deposit_paid, deposit_date);
DROP INDEX IF EXISTS idx_tenant_id;
CREATE INDEX idx_tenant_id ON tenants (tenant_id);
DROP INDEX IF EXISTS idx_plot_id;
CREATE INDEX idx_plot_id ON tenants (plot_id);
DROP INDEX IF EXISTS idx_status;
CREATE INDEX idx_status ON tenants (status);

-- =====================================================
-- RENT PAYMENTS TABLE
-- =====================================================
DROP TABLE IF EXISTS rent_payments CASCADE;
CREATE TABLE rent_payments (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    commission_percentage DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    commission_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_status payment_status DEFAULT 'Paid',
    payment_month DATE NOT NULL,
    payment_year INTEGER NOT NULL,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

DROP INDEX IF EXISTS idx_tenant_id;
CREATE INDEX idx_tenant_id ON rent_payments (tenant_id);
DROP INDEX IF EXISTS idx_payment_date;
CREATE INDEX idx_payment_date ON rent_payments (payment_date);
DROP INDEX IF EXISTS idx_payment_month;
CREATE INDEX idx_payment_month ON rent_payments (payment_month);
DROP INDEX IF EXISTS idx_payment_status;
CREATE INDEX idx_payment_status ON rent_payments (payment_status);
DROP INDEX IF EXISTS idx_payment_year;
CREATE INDEX idx_payment_year ON rent_payments (payment_year);

-- =====================================================
-- RECEIPTS TABLE
-- =====================================================
DROP TABLE IF EXISTS receipts CASCADE;
CREATE TABLE receipts (
    id SERIAL PRIMARY KEY,
    receipt_number VARCHAR(30) UNIQUE NOT NULL,
    payment_id INTEGER NOT NULL REFERENCES rent_payments(id) ON DELETE CASCADE,
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

DROP INDEX IF EXISTS idx_receipt_number;
CREATE INDEX idx_receipt_number ON receipts (receipt_number);
DROP INDEX IF EXISTS idx_payment_id;
CREATE INDEX idx_payment_id ON receipts (payment_id);
DROP INDEX IF EXISTS idx_tenant_id;
CREATE INDEX idx_tenant_id ON receipts (tenant_id);

-- =====================================================
-- VIEWS
-- =====================================================
CREATE OR REPLACE VIEW vw_tenant_summary AS
SELECT 
    t.id, t.tenant_id, t.tenant_name, t.plot_id, p.plot_name,
    t.house_number, t.house_type, t.rent_amount, t.move_in_date, t.status,
    t.deposit_amount, t.deposit_paid, t.deposit_date,
    COALESCE(SUM(rp.amount_paid), 0) AS total_paid,
    COUNT(rp.id) AS payments_count,
    ROUND((EXTRACT(EPOCH FROM age(NOW(), t.move_in_date))/2629746)::numeric * t.rent_amount - COALESCE(SUM(rp.amount_paid),0), 2) AS arrears
FROM tenants t
LEFT JOIN plots p ON t.plot_id = p.id
LEFT JOIN rent_payments rp ON t.id = rp.tenant_id AND rp.payment_status='Paid'
GROUP BY t.id, p.plot_name;

CREATE OR REPLACE VIEW vw_monthly_reports AS
SELECT 
    EXTRACT(YEAR FROM payment_date) AS year,
    EXTRACT(MONTH FROM payment_date) AS month,
    COUNT(*) AS total_payments,
    SUM(amount_paid) AS total_collected,
    SUM(commission_amount) AS total_commission,
    AVG(commission_percentage) AS avg_commission_rate
FROM rent_payments
WHERE payment_status='Paid'
GROUP BY EXTRACT(YEAR FROM payment_date), EXTRACT(MONTH FROM payment_date)
ORDER BY year DESC, month DESC;

-- =====================================================
-- DEFAULT ADMIN USER
-- =====================================================
INSERT INTO users (user_id, full_name, username, email, password)
SELECT 'USR-2026-0001','Administrator','admin','admin@leta.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='admin');

-- =====================================================
-- SAMPLE PLOTS
-- =====================================================
INSERT INTO plots (plot_name, location) VALUES
('Sunrise Apartments','Kenyatta Avenue, Nairobi'),
('Green Valley Heights','Mombasa Road, Nairobi'),
('Riverside Gardens','Kiserian Road, Nairobi'),
('Metro Plaza','Central Business District, Nairobi')
ON CONFLICT DO NOTHING;

-- =====================================================
-- SAMPLE TENANTS
-- =====================================================
INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, rent_amount, commission_percentage, deposit_amount, deposit_paid, deposit_date, move_in_date, status)
SELECT 'TH-2026-0001', id, 'John Doe', '0722123456', '101', '1 Bedroom', 15000.00, 10.00, 30000.00, 'Yes','2026-01-01','2026-01-01','Active'
FROM plots WHERE plot_name='Sunrise Apartments'
ON CONFLICT DO NOTHING;

INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, rent_amount, commission_percentage, deposit_amount, deposit_paid, deposit_date, move_in_date, status)
SELECT 'TH-2026-0002', id, 'Jane Smith', '0722987654', '202', '2 Bedroom', 25000.00, 10.00, 50000.00, 'Yes','2026-01-15','2026-01-15','Active'
FROM plots WHERE plot_name='Green Valley Heights'
ON CONFLICT DO NOTHING;

INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, rent_amount, commission_percentage, deposit_amount, deposit_paid, deposit_date, move_in_date, status)
SELECT 'TH-2026-0003', id, 'Michael Johnson', '0711234567','A1','Studio',10000.00,10.00,0.00,'No',NULL,'2026-02-01','Active'
FROM plots WHERE plot_name='Riverside Gardens'
ON CONFLICT DO NOTHING;

-- =====================================================
-- VERIFY DATABASE SETUP
-- =====================================================
SELECT 'Database setup completed successfully for Supabase PostgreSQL!' AS status;
SELECT tablename FROM pg_tables WHERE schemaname = 'public';

