-- 2026-02-09-000001_CreatePasswordResets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2026-02-09-000002_AddIndexesForAnalytics
CREATE INDEX idx_propostas_start_date ON propostas (start_date);
CREATE INDEX idx_propostas_status_start_date ON propostas (status, start_date);
CREATE INDEX idx_propostas_created_at ON propostas (created_at);

CREATE INDEX idx_operacional_flag_data ON operacional (flag, data);
CREATE INDEX idx_operacional_flag_tipo_data ON operacional (flag, tipo, data);

-- 2026-02-09-000003_AddGeneratorKwhFields
ALTER TABLE `operacional`
  ADD COLUMN `leitura_g1` TIME NULL AFTER `tempo_g1`,
  ADD COLUMN `kwh_entrada_g1` DECIMAL(10,2) NULL AFTER `leitura_g1`,
  ADD COLUMN `kwh_saida_g1` DECIMAL(10,2) NULL AFTER `kwh_entrada_g1`,
  ADD COLUMN `leitura_g2` TIME NULL AFTER `tempo_g2`,
  ADD COLUMN `kwh_entrada_g2` DECIMAL(10,2) NULL AFTER `leitura_g2`,
  ADD COLUMN `kwh_saida_g2` DECIMAL(10,2) NULL AFTER `kwh_entrada_g2`;

-- 2026-02-09-000004_AddEquipeTurnos
ALTER TABLE `operacional`
  ADD COLUMN `equipe_manha` TINYINT(1) NULL DEFAULT 0 AFTER `limpeza`,
  ADD COLUMN `equipe_noite` TINYINT(1) NULL DEFAULT 0 AFTER `equipe_manha`;

-- 2026-02-09-000005_RemoveTaxaCozinha
ALTER TABLE `operacional` DROP COLUMN `taxa_cozinha`;

-- 2026-02-09-000006_CreateSettings
CREATE TABLE IF NOT EXISTS `settings` (
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
