-- Auditoria e exclusao logica de licenciandos. MySQL / MariaDB.
-- Faca backup e selecione o banco correto. DDL realiza commit implicito.
-- DBeaver: execute como script com Alt+X, na mesma conexao.
-- Nao preenche datas historicas e nao exclui registros.
SELECT DATABASE() AS banco_selecionado;

SET @dalpe_existe = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'licenciandos' AND COLUMN_NAME = 'created_at');
SET @dalpe_sql = IF(@dalpe_existe = 0, 'ALTER TABLE `licenciandos` ADD COLUMN `created_at` DATETIME NULL DEFAULT NULL', 'SELECT ''created_at ja existe'' AS resultado');
PREPARE dalpe_auditoria FROM @dalpe_sql;
EXECUTE dalpe_auditoria;
DEALLOCATE PREPARE dalpe_auditoria;

SET @dalpe_existe = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'licenciandos' AND COLUMN_NAME = 'updated_at');
SET @dalpe_sql = IF(@dalpe_existe = 0, 'ALTER TABLE `licenciandos` ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL', 'SELECT ''updated_at ja existe'' AS resultado');
PREPARE dalpe_auditoria FROM @dalpe_sql;
EXECUTE dalpe_auditoria;
DEALLOCATE PREPARE dalpe_auditoria;

SET @dalpe_existe = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'licenciandos' AND COLUMN_NAME = 'deleted_at');
SET @dalpe_sql = IF(@dalpe_existe = 0, 'ALTER TABLE `licenciandos` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL', 'SELECT ''deleted_at ja existe'' AS resultado');
PREPARE dalpe_auditoria FROM @dalpe_sql;
EXECUTE dalpe_auditoria;
DEALLOCATE PREPARE dalpe_auditoria;

SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'licenciandos' AND COLUMN_NAME IN ('created_at','updated_at','deleted_at');
-- Nao registra versao na tabela migrations; a migracao PHP detecta colunas existentes.
