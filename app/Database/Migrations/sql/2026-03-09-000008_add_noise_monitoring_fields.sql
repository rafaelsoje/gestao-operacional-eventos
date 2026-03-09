-- Migration SQL equivalente a:
-- app/Database/Migrations/2026-03-09-000008_AddNoiseMonitoringFields.php
-- Banco alvo: MySQL/MariaDB

-- =========================
-- UP
-- =========================

SET @db := DATABASE();

SET @has_ruido_tribuna_c := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'operacional'
    AND COLUMN_NAME = 'ruido_tribuna_c'
);
SET @sql := IF(
  @has_ruido_tribuna_c = 0,
  'ALTER TABLE operacional ADD COLUMN ruido_tribuna_c DECIMAL(6,2) NULL AFTER monitor_sonoro',
  'SELECT "ruido_tribuna_c already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_ruido_pista_cavalo := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'operacional'
    AND COLUMN_NAME = 'ruido_pista_cavalo'
);
SET @sql := IF(
  @has_ruido_pista_cavalo = 0,
  'ALTER TABLE operacional ADD COLUMN ruido_pista_cavalo DECIMAL(6,2) NULL AFTER ruido_tribuna_c',
  'SELECT "ruido_pista_cavalo already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_ruido_corredor_jb := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'operacional'
    AND COLUMN_NAME = 'ruido_corredor_jb'
);
SET @sql := IF(
  @has_ruido_corredor_jb = 0,
  'ALTER TABLE operacional ADD COLUMN ruido_corredor_jb DECIMAL(6,2) NULL AFTER ruido_pista_cavalo',
  'SELECT "ruido_corredor_jb already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_ruido_media := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'operacional'
    AND COLUMN_NAME = 'ruido_media'
);
SET @sql := IF(
  @has_ruido_media = 0,
  'ALTER TABLE operacional ADD COLUMN ruido_media DECIMAL(6,2) NULL AFTER ruido_corredor_jb',
  'SELECT "ruido_media already exists"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- =========================
-- DOWN (opcional, manual)
-- =========================
-- ALTER TABLE operacional DROP COLUMN ruido_media;
-- ALTER TABLE operacional DROP COLUMN ruido_corredor_jb;
-- ALTER TABLE operacional DROP COLUMN ruido_pista_cavalo;
-- ALTER TABLE operacional DROP COLUMN ruido_tribuna_c;

