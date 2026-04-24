-- Stitch House DB Migration 001: SRS alignment
-- Aligns schema with SRS §3.4 Classes and functional requirements.
-- Safe to re-run: uses IF NOT EXISTS / IF NOT EXISTS-style guards where supported.

START TRANSACTION;

-- ============================================================
-- 1. customization table  (SRS §3.4 — core class, previously missing)
-- ============================================================
CREATE TABLE IF NOT EXISTS customization (
    customization_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id         INT NOT NULL,
    order_item_id    INT NULL,
    fabric_type      VARCHAR(100) NULL,
    collar_style     VARCHAR(50)  NULL,
    cuff_style       VARCHAR(50)  NULL,
    fit_preference   ENUM('slim','regular','loose') NOT NULL DEFAULT 'regular',
    unit_price       DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customization_order (order_id),
    INDEX idx_customization_item  (order_item_id),
    CONSTRAINT fk_customization_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2a. admins.role  (SRS §3.4 Administrator class — role-based access)
-- ============================================================
ALTER TABLE admins
    ADD COLUMN role ENUM('super','order_manager','support') NOT NULL DEFAULT 'super' AFTER password;

-- ============================================================
-- 2b. users.address  (SRS §3.4 Customer.Shipping_Address)
-- ============================================================
ALTER TABLE users
    ADD COLUMN address TEXT NULL AFTER phone;

-- ============================================================
-- 3. contact_messages → support ticket tracking  (SRS §3.2.7)
-- ============================================================
ALTER TABLE contact_messages
    ADD COLUMN ticket_number  VARCHAR(20) NULL AFTER id,
    ADD COLUMN user_id        INT NULL AFTER ticket_number,
    ADD COLUMN status         ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open' AFTER message,
    ADD COLUMN admin_response TEXT NULL AFTER status,
    ADD COLUMN resolved_at    TIMESTAMP NULL AFTER admin_response,
    ADD UNIQUE KEY uk_cm_ticket_number (ticket_number),
    ADD INDEX idx_cm_user_id (user_id),
    ADD INDEX idx_cm_status  (status);

-- Back-fill ticket numbers for existing messages
UPDATE contact_messages
SET ticket_number = CONCAT('TKT-', LPAD(id, 6, '0'))
WHERE ticket_number IS NULL;

-- ============================================================
-- 4. products.stock_quantity  (SRS §3.2.3.5 — out-of-stock handling)
-- ============================================================
ALTER TABLE products
    ADD COLUMN stock_quantity INT NOT NULL DEFAULT 0 AFTER price,
    ADD COLUMN fabric_type    VARCHAR(100) NULL AFTER category;

-- Back-fill: assume existing catalog is in stock so products don't vanish from UI
UPDATE products SET stock_quantity = 100 WHERE stock_quantity = 0;

-- ============================================================
-- 5. orders.status enum expansion  (SRS UC-06 — tailoring/shipping stages)
-- ============================================================
ALTER TABLE orders
    MODIFY COLUMN status
        ENUM('pending','processing','in_tailoring','shipped','completed','cancelled')
        NOT NULL DEFAULT 'pending';

COMMIT;
