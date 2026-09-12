-- Migration: persistent "remember me" login tokens — idempotent
-- Re-running is safe (CREATE TABLE IF NOT EXISTS).
-- Cookie `gg_remember` = selector(32 hex) + token(64 hex); DB stores only token_hash.

CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `selector` VARCHAR(32) NOT NULL,
    `token_hash` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_selector` (`selector`),
    KEY `idx_user` (`user_id`),
    KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;