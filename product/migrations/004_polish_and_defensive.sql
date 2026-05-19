-- =====================================================================
-- Migration 004: Polish (low-stock, per-meas confidence) + defensive items
--   - reorder_level on products (SDD §4.1.4)
--   - login_attempts (rate limiting, NFR-SEC)
--   - idempotency_keys (SDD §3.5)
--   - inventory_reservations (SDD §3.5)
-- Idempotent — safe to re-run.
-- =====================================================================

-- 1. Per-product reorder threshold (SDD §4.1.4 / §3.7 Inventory)
SET @col := (SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'reorder_level');
SET @sql := IF(@col = 0,
  'ALTER TABLE products ADD COLUMN reorder_level INT NOT NULL DEFAULT 5 AFTER stock_quantity',
  'SELECT "reorder_level already exists" AS noop');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. login_attempts — rate limiting on user + admin logins (NFR-SEC, SDD §3.5)
CREATE TABLE IF NOT EXISTS login_attempts (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  identifier    VARCHAR(255) NOT NULL COMMENT 'email or IP — what we throttle',
  attempt_type  ENUM('user','admin') NOT NULL DEFAULT 'user',
  success       TINYINT(1) NOT NULL DEFAULT 0,
  ip_address    VARCHAR(45) NULL,
  attempted_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_la_identifier_time (identifier, attempted_at),
  INDEX idx_la_ip_time         (ip_address, attempted_at)
) ENGINE=InnoDB;

-- 3. idempotency_keys — prevent duplicate orders from double-clicks (SDD §3.5)
CREATE TABLE IF NOT EXISTS idempotency_keys (
  key_hash    CHAR(64) PRIMARY KEY COMMENT 'sha256 of client-provided key + user_id + endpoint',
  user_id     INT NOT NULL,
  endpoint    VARCHAR(100) NOT NULL,
  result_id   INT NULL COMMENT 'e.g. order_id once created',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ik_created (created_at)
) ENGINE=InnoDB;

-- 4. inventory_reservations — temporary stock locks during checkout (SDD §3.5)
CREATE TABLE IF NOT EXISTS inventory_reservations (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  product_id   VARCHAR(50) NOT NULL COLLATE utf8mb4_general_ci COMMENT 'must match products.id collation',
  user_id      INT NOT NULL,
  quantity     INT NOT NULL,
  reserved_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at   DATETIME NOT NULL,
  consumed     TINYINT(1) DEFAULT 0 COMMENT 'set to 1 once order is placed',
  INDEX idx_ir_product_active (product_id, expires_at, consumed),
  INDEX idx_ir_user_active    (user_id, expires_at, consumed)
) ENGINE=InnoDB;

-- Patch existing tables (in case of prior install with wrong collation)
ALTER TABLE inventory_reservations MODIFY product_id VARCHAR(50) NOT NULL COLLATE utf8mb4_general_ci;
