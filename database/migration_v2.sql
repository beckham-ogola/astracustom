-- =====================================================================
-- AstraCampus - Migration v2
-- Run this ONLY if you already imported an earlier version of schema.sql
-- and have live data you want to keep. It safely adds what's new
-- (M-Pesa STK Push support) without touching your existing tables.
--
-- If you are installing AstraCampus fresh, just import database/schema.sql
-- instead — you do not need this file.
--
-- Usage:
--   mysql -u root -p astracampus < database/migration_v2.sql
-- =====================================================================

USE astracampus;

-- ---------------------------------------------------------------------
-- mpesa_transactions — tracks STK push requests and Safaricom callbacks
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mpesa_transactions (
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
-- New settings rows (INSERT IGNORE — won't overwrite anything if these
-- keys somehow already exist, e.g. you re-run this script)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO settings (setting_key, setting_value, setting_group, is_public) VALUES
('mpesa_environment', 'sandbox', 'mpesa', 0),
('mpesa_till_number', '', 'mpesa', 0),
('mpesa_passkey', '', 'mpesa', 0),
('mpesa_consumer_key', '', 'mpesa', 0),
('mpesa_consumer_secret', '', 'mpesa', 0),
('mpesa_callback_url', '', 'mpesa', 0),
('mpesa_transaction_type', 'CustomerBuyGoodsOnline', 'mpesa', 0);

SELECT 'Migration complete. You can now configure M-Pesa under Settings → M-Pesa Integration.' AS status;
