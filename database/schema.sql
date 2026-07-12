-- =====================================================================
-- AstraCampus - Complete School Management System
-- Database Schema + Seed Data
-- MySQL 5.7+
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS astracampus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE astracampus;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    id_number VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    role ENUM('developer','admin','accountant','teacher') NOT NULL DEFAULT 'teacher',
    class_assigned INT UNSIGNED DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- classes
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS classes;
CREATE TABLE classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    level INT NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    UNIQUE KEY uq_class_level (level)
) ENGINE=InnoDB;

ALTER TABLE users ADD CONSTRAINT fk_users_class FOREIGN KEY (class_assigned) REFERENCES classes(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------------
-- students
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS students;
CREATE TABLE students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admission_no VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    dob DATE NOT NULL,
    age INT DEFAULT NULL,
    gender ENUM('Male','Female') NOT NULL,
    birth_cert_no VARCHAR(100) NOT NULL,
    class_id INT UNSIGNED DEFAULT NULL,
    admission_date DATE NOT NULL,
    term VARCHAR(50) DEFAULT NULL,
    photo_consent TINYINT(1) NOT NULL DEFAULT 1,

    guardian1_name VARCHAR(150) NOT NULL,
    guardian1_relation VARCHAR(50) NOT NULL,
    guardian1_id VARCHAR(50) NOT NULL,
    guardian1_phone VARCHAR(30) NOT NULL,
    guardian1_phone_alt VARCHAR(30) DEFAULT NULL,
    guardian1_address VARCHAR(255) DEFAULT NULL,

    guardian2_name VARCHAR(150) DEFAULT NULL,
    guardian2_relation VARCHAR(50) DEFAULT NULL,
    guardian2_id VARCHAR(50) DEFAULT NULL,
    guardian2_phone VARCHAR(30) DEFAULT NULL,
    guardian2_phone_alt VARCHAR(30) DEFAULT NULL,
    guardian2_address VARCHAR(255) DEFAULT NULL,

    medical_conditions TEXT DEFAULT NULL,
    status ENUM('Active','Graduated','Withdrawn') NOT NULL DEFAULT 'Active',
    admission_form_path VARCHAR(255) DEFAULT NULL,
    admitted_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_students_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_students_admitted_by FOREIGN KEY (admitted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- bill_types
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS bill_types;
CREATE TABLE bill_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- fee_structure
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS fee_structure;
CREATE TABLE fee_structure (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_type_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    declared_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    UNIQUE KEY uq_fee_bill_class (bill_type_id, class_id),
    CONSTRAINT fk_fs_bill_type FOREIGN KEY (bill_type_id) REFERENCES bill_types(id) ON DELETE CASCADE,
    CONSTRAINT fk_fs_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- bills
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS bills;
CREATE TABLE bills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    bill_type_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_applied DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_sponsored TINYINT(1) NOT NULL DEFAULT 0,
    sponsored_by INT UNSIGNED DEFAULT NULL,
    term VARCHAR(50) NOT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_bills_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_bills_bill_type FOREIGN KEY (bill_type_id) REFERENCES bill_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- payments
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    bill_id INT UNSIGNED NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('Cash','M-Pesa','Bank Transfer','Cheque') NOT NULL DEFAULT 'Cash',
    receipt_no VARCHAR(30) NOT NULL UNIQUE,
    payment_details JSON DEFAULT NULL,
    payer_name VARCHAR(150) DEFAULT NULL,
    collected_by INT UNSIGNED DEFAULT NULL,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_payments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_payments_bill FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- receipt_reprints
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS receipt_reprints;
CREATE TABLE receipt_reprints (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(30) NOT NULL,
    reprinted_by INT UNSIGNED DEFAULT NULL,
    reprinted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    original_date DATETIME DEFAULT NULL,
    reason VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- mpesa_transactions — tracks STK push requests and Safaricom callbacks
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS mpesa_transactions;
CREATE TABLE mpesa_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    bill_id INT UNSIGNED NOT NULL,
    payment_id INT UNSIGNED DEFAULT NULL,
    phone VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    merchant_request_id VARCHAR(100) DEFAULT NULL,
    checkout_request_id VARCHAR(100) DEFAULT NULL UNIQUE,
    status ENUM('pending','success','failed','cancelled') NOT NULL DEFAULT 'pending',
    mpesa_receipt_number VARCHAR(50) DEFAULT NULL,
    result_code VARCHAR(10) DEFAULT NULL,
    result_desc VARCHAR(255) DEFAULT NULL,
    raw_callback JSON DEFAULT NULL,
    initiated_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mpesa_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_mpesa_bill FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    CONSTRAINT fk_mpesa_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- graduates
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS graduates;
CREATE TABLE graduates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    student_data JSON DEFAULT NULL,
    from_class_id INT UNSIGNED DEFAULT NULL,
    graduated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    graduated_by INT UNSIGNED DEFAULT NULL,
    reason VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- teacher_class_logs
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS teacher_class_logs;
CREATE TABLE teacher_class_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL,
    old_class_id INT UNSIGNED DEFAULT NULL,
    new_class_id INT UNSIGNED DEFAULT NULL,
    changed_by INT UNSIGNED DEFAULT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- messages
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS messages;
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_phone VARCHAR(30) NOT NULL,
    recipient_name VARCHAR(150) DEFAULT NULL,
    message VARCHAR(320) NOT NULL,
    message_type VARCHAR(50) DEFAULT 'receipt',
    sent_by INT UNSIGNED DEFAULT NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'sent'
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- templates
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS templates;
CREATE TABLE templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    uploaded_by INT UNSIGNED DEFAULT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- settings
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_group VARCHAR(50) DEFAULT 'general',
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    updated_by INT UNSIGNED DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- audit_logs
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) DEFAULT NULL,
    record_id INT UNSIGNED DEFAULT NULL,
    old_data JSON DEFAULT NULL,
    new_data JSON DEFAULT NULL,
    details VARCHAR(500) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- VIEWS FOR REPORTING
-- =====================================================================

CREATE OR REPLACE VIEW v_student_balances AS
SELECT
    s.id AS student_id,
    s.admission_no,
    s.full_name,
    s.class_id,
    c.name AS class_name,
    s.status,
    s.guardian1_phone,
    COALESCE(SUM(b.final_amount), 0) AS total_billed,
    COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.student_id = s.id AND p.deleted_at IS NULL), 0) AS total_paid,
    COALESCE(SUM(b.final_amount), 0) - COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.student_id = s.id AND p.deleted_at IS NULL), 0) AS balance
FROM students s
LEFT JOIN classes c ON c.id = s.class_id
LEFT JOIN bills b ON b.student_id = s.id AND b.deleted_at IS NULL
WHERE s.deleted_at IS NULL
GROUP BY s.id, s.admission_no, s.full_name, s.class_id, c.name, s.status, s.guardian1_phone;

CREATE OR REPLACE VIEW v_daily_collection AS
SELECT
    DATE(p.payment_date) AS collection_date,
    p.payment_method,
    COUNT(*) AS transaction_count,
    SUM(p.amount_paid) AS total_amount
FROM payments p
WHERE p.deleted_at IS NULL
GROUP BY DATE(p.payment_date), p.payment_method;

CREATE OR REPLACE VIEW v_class_collection AS
SELECT
    c.id AS class_id,
    c.name AS class_name,
    COUNT(DISTINCT s.id) AS student_count,
    COALESCE(SUM(b.final_amount), 0) AS total_fees,
    COALESCE((SELECT SUM(p.amount_paid) FROM payments p
        INNER JOIN bills b2 ON b2.id = p.bill_id
        INNER JOIN students s2 ON s2.id = b2.student_id
        WHERE s2.class_id = c.id AND p.deleted_at IS NULL), 0) AS collected_amount
FROM classes c
LEFT JOIN students s ON s.class_id = c.id AND s.status = 'Active' AND s.deleted_at IS NULL
LEFT JOIN bills b ON b.student_id = s.id AND b.deleted_at IS NULL
WHERE c.is_active = 1
GROUP BY c.id, c.name;

-- =====================================================================
-- SEED DATA
-- =====================================================================

-- Classes
INSERT INTO classes (name, level, description, is_active) VALUES
('Day Care', 1, 'Day care class for early years', 1),
('Play Group', 2, 'Play group for toddlers', 1),
('Pre Primary 1', 3, 'Pre Primary 1 (PP1)', 1),
('Pre Primary 2', 4, 'Pre Primary 2 (PP2)', 1),
('Grade 1', 5, 'First grade - Lower Primary', 1),
('Grade 2', 6, 'Second grade - Lower Primary', 1),
('Grade 3', 7, 'Third grade - Lower Primary', 1);

-- Users (password123 hashed with MD5, see includes/functions.php for hash used at runtime)
-- MD5('password123') = 482c811da5d5b4bc6d497ffa98491e38
INSERT INTO users (username, id_number, password, full_name, email, phone, role, class_assigned, is_active) VALUES
('developer', '1000', '482c811da5d5b4bc6d497ffa98491e38', 'System Developer', 'developer@astracampus.com', '+254700000001', 'developer', NULL, 1),
('admin', '1001', '482c811da5d5b4bc6d497ffa98491e38', 'School Administrator', 'admin@astracampus.com', '+254700000002', 'admin', NULL, 1),
('accountant', '1002', '482c811da5d5b4bc6d497ffa98491e38', 'School Accountant', 'accountant@astracampus.com', '+254700000003', 'accountant', NULL, 1),
('teacher', '1003', '482c811da5d5b4bc6d497ffa98491e38', 'Class Teacher', 'teacher@astracampus.com', '+254700000004', 'teacher', 5, 1);

-- Bill Types
INSERT INTO bill_types (name, description, is_active) VALUES
('Tuition Fee', 'Core tuition fees per term', 1),
('Activity Fee', 'Co-curricular activities and sports', 1),
('Transport Fee', 'School bus transportation', 1),
('Lunch Fee', 'School meals and catering', 1),
('Development Fee', 'Infrastructure and development', 1),
('Uniform Fee', 'School uniform and attire', 1);

-- Sample fee structure (Tuition Fee for all classes)
INSERT INTO fee_structure (bill_type_id, class_id, amount) VALUES
(1, 1, 15000.00), (1, 2, 18000.00), (1, 3, 20000.00), (1, 4, 22000.00), (1, 5, 28000.00), (1, 6, 28000.00), (1, 7, 30000.00),
(2, 1, 2000.00),  (2, 2, 2000.00),  (2, 3, 2500.00),  (2, 4, 2500.00),  (2, 5, 3000.00),  (2, 6, 3000.00),  (2, 7, 3000.00),
(5, 1, 3000.00),  (5, 2, 3000.00),  (5, 3, 3500.00),  (5, 4, 3500.00),  (5, 5, 4000.00),  (5, 6, 4000.00),  (5, 7, 4000.00);

-- Settings
INSERT INTO settings (setting_key, setting_value, setting_group, is_public) VALUES
('school_name', 'AstraCampus School', 'general', 1),
('school_phone', '+254 700 000000', 'general', 1),
('school_email', 'info@astracampus.com', 'general', 1),
('school_address', 'Nairobi, Kenya', 'general', 1),
('school_motto', 'Excellence in Education', 'general', 1),
('current_term', 'Term 1 2026', 'general', 1),
('current_year', '2026', 'general', 1),
('next_admission_no', 'ACS-001', 'system', 0),
('next_receipt_no', 'RCP-2026-00001', 'system', 0),
('mpesa_environment', 'sandbox', 'mpesa', 0),
('mpesa_till_number', '', 'mpesa', 0),
('mpesa_passkey', '', 'mpesa', 0),
('mpesa_consumer_key', '', 'mpesa', 0),
('mpesa_consumer_secret', '', 'mpesa', 0),
('mpesa_callback_url', '', 'mpesa', 0),
('mpesa_transaction_type', 'CustomerBuyGoodsOnline', 'mpesa', 0);
