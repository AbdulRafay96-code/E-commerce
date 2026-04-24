-- Migration 002: Customer-scoped measurements (SRS §3.4 Measurement class)
-- Stores reusable measurement sets per user. Past orders keep their frozen
-- copies in order_measurements; this table is the "source of truth" for the
-- customer's saved body dimensions.

CREATE TABLE IF NOT EXISTS measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) NOT NULL DEFAULT 'Default',
    chest VARCHAR(20) DEFAULT NULL,
    waist VARCHAR(20) DEFAULT NULL,
    hip VARCHAR(20) DEFAULT NULL,
    shoulder VARCHAR(20) DEFAULT NULL,
    sleeve_length VARCHAR(20) DEFAULT NULL,
    trouser_length VARCHAR(20) DEFAULT NULL,
    kameez_length VARCHAR(20) DEFAULT NULL,
    neck VARCHAR(20) DEFAULT NULL,
    captured_via ENUM('ai','manual') NOT NULL DEFAULT 'manual',
    notes TEXT DEFAULT NULL,
    date_captured TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_measurements_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_measurements_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
