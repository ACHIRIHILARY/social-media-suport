<?php

require_once __DIR__ . '/../src/config/database.php';

$pdo = getDbConnection();

$sql = "

-- ─────────────────────────────────────────
-- Table: prayer_requests
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS prayer_requests (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(254) NOT NULL,
    subject         VARCHAR(200) DEFAULT NULL,
    message         TEXT         NOT NULL,
    is_read         TINYINT(1)   NOT NULL DEFAULT 0,
    is_prayed       TINYINT(1)   NOT NULL DEFAULT 0,
    ip_address      VARBINARY(16) NOT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_read   (is_read),
    INDEX idx_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Table: admin_users
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(60)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    last_login      DATETIME     DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Table: rate_limit
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS rate_limit (
    ip_address      VARBINARY(16) NOT NULL,
    action          VARCHAR(40)   NOT NULL,
    attempt_count   SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    window_start    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ip_address, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($sql);
    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
