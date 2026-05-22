-- Migration: Apply charset, FK, and soft-delete fixes to existing database
-- Run this once against your existing database

-- 1. Convert queue_antrian_admisi from latin1 to utf8mb4
ALTER TABLE queue_antrian_admisi CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Convert queue_penggilan_antrian from utf8 to utf8mb4
ALTER TABLE queue_penggilan_antrian CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. Add index on referenced column if not exists
SET @dbname = DATABASE();
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'queue_antrian_admisi' AND INDEX_NAME = 'idx_no_antrian');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE queue_antrian_admisi ADD INDEX idx_no_antrian (no_antrian)', 'SELECT ''Index already exists''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Add deleted column for soft-delete support
ALTER TABLE queue_antrian_admisi ADD COLUMN deleted tinyint(1) NOT NULL DEFAULT '0' AFTER updated_date;
ALTER TABLE queue_penggilan_antrian ADD COLUMN deleted tinyint(1) NOT NULL DEFAULT '0' AFTER loket;

-- 5. Add proper FK constraint
ALTER TABLE queue_penggilan_antrian DROP INDEX IF EXISTS Fk_antrian;
ALTER TABLE queue_penggilan_antrian DROP FOREIGN KEY IF EXISTS fk_panggilan_antrian;
ALTER TABLE queue_penggilan_antrian ADD CONSTRAINT fk_panggilan_antrian FOREIGN KEY (antrian) REFERENCES queue_antrian_admisi (no_antrian) ON DELETE CASCADE ON UPDATE CASCADE;
