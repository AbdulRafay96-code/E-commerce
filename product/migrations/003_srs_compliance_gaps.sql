-- =====================================================================
-- Migration 003: SRS/SDD compliance gaps
-- Implements high-severity items from doc audit:
--   - confidence_score + raw_landmarks for measurements (SDD §4.1.3)
--   - order_status_logs table (SDD §4.1.8)
--   - audit_logs table for admin actions (SDD §4.1.9)
--   - production_sheets table (SDD §4.1.7)
-- Idempotent — safe to run multiple times.
-- =====================================================================

-- 1. Measurements: confidence_score + raw_landmarks JSON
SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns
                    WHERE table_schema = DATABASE()
                      AND table_name = 'measurements'
                      AND column_name = 'confidence_score');
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE measurements ADD COLUMN confidence_score DECIMAL(4,3) NULL AFTER captured_via',
  'SELECT "confidence_score column already exists" AS noop');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns
                    WHERE table_schema = DATABASE()
                      AND table_name = 'measurements'
                      AND column_name = 'raw_landmarks');
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE measurements ADD COLUMN raw_landmarks JSON NULL AFTER confidence_score',
  'SELECT "raw_landmarks column already exists" AS noop');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. order_status_logs — full history of order status changes (SDD §4.1.8)
CREATE TABLE IF NOT EXISTS order_status_logs (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  order_id      INT NOT NULL,
  status        VARCHAR(50) NOT NULL,
  changed_by    INT NULL COMMENT 'admin_id; NULL when customer initiated',
  changed_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notes         TEXT NULL,
  INDEX idx_osl_order (order_id),
  INDEX idx_osl_changed_at (changed_at),
  CONSTRAINT fk_osl_order  FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_osl_admin  FOREIGN KEY (changed_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3. audit_logs — admin action audit trail (SDD §4.1.9)
CREATE TABLE IF NOT EXISTS audit_logs (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  admin_id      INT NULL,
  action        VARCHAR(100) NOT NULL COMMENT 'e.g. order.status_change, product.create, customer.update',
  entity_type   VARCHAR(50)  NULL    COMMENT 'order, product, customer, measurement, ticket',
  entity_id     INT          NULL,
  details       TEXT         NULL    COMMENT 'JSON or human-readable diff',
  ip_address    VARCHAR(45)  NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_admin    (admin_id),
  INDEX idx_audit_entity   (entity_type, entity_id),
  INDEX idx_audit_created  (created_at),
  CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. production_sheets — admin-generated tailoring sheets (SDD §4.1.7)
CREATE TABLE IF NOT EXISTS production_sheets (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  order_id       INT NOT NULL,
  generated_by   INT NOT NULL COMMENT 'admin_id who generated the sheet',
  tailor_notes   TEXT NULL,
  generated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ps_order (order_id),
  INDEX idx_ps_generated (generated_at),
  CONSTRAINT fk_ps_order FOREIGN KEY (order_id)     REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_ps_admin FOREIGN KEY (generated_by) REFERENCES admins(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Backfill: seed existing orders into order_status_logs so the timeline
-- shows historical entries even for orders placed before this migration
INSERT INTO order_status_logs (order_id, status, changed_by, changed_at, notes)
SELECT o.id, o.status, NULL, o.order_date,
       'Backfilled from migration 003 — historical status only'
FROM orders o
WHERE NOT EXISTS (
  SELECT 1 FROM order_status_logs osl WHERE osl.order_id = o.id
);
