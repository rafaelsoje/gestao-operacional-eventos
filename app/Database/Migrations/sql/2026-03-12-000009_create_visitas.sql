-- Migration SQL equivalente a:
-- app/Database/Migrations/2026-03-12-000009_CreateVisitas.php
-- Banco alvo: MySQL/MariaDB

-- =========================
-- UP
-- =========================

CREATE TABLE IF NOT EXISTS `visitas` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `data_visita` DATE NOT NULL,
  `tipo_visita` VARCHAR(255) NOT NULL,
  `responsavel_exc` VARCHAR(255) NOT NULL,
  `cliente` VARCHAR(255) NOT NULL,
  `evento` VARCHAR(255) NOT NULL,
  `data_evento` DATE NOT NULL,
  `horario_inicio` TIME NOT NULL,
  `horario_termino` TIME NOT NULL,
  `publico_estimado` INT(11) NOT NULL,
  `objetivo_visita` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visitas_data_visita` (`data_visita`),
  KEY `idx_visitas_data_evento` (`data_evento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- DOWN (opcional, manual)
-- =========================
-- DROP TABLE IF EXISTS `visitas`;
