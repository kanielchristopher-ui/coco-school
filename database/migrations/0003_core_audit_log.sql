-- 0003 : journal d'audit du Core

-- Journal d'audit du Core (CocoCore\Database\PdoAuditLog)
CREATE TABLE IF NOT EXISTS `core_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(60) NOT NULL,
  `entity` VARCHAR(60) NOT NULL,
  `entity_id` INT UNSIGNED DEFAULT NULL,
  `details_json` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_core_audit_entity` (`entity`, `entity_id`),
  KEY `idx_core_audit_actor` (`actor_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
