-- Migrações equivalentes a AddSituacaoLicenciandos e AddSituacaoVinculos.
-- MySQL / MariaDB. Selecione o banco correto antes de executar o arquivo inteiro.
-- Faça backup do banco antes da execução. ALTER TABLE realiza commit implícito.
-- Não há atualização, exclusão ou classificação dos cadastros existentes.
-- Execute em uma única conexão, por exemplo pela aba SQL do phpMyAdmin.
-- DBeaver: confira a conexão e o banco no topo do editor, retire a seleção
-- do texto e use Alt+X (Executar script SQL), com delimitador padrão ;.
-- Não use Ctrl+Enter com todo o arquivo selecionado: isso pode enviar
-- os comandos como uma única instrução e gerar erro 1064 próximo de SET.
-- Execute sequencialmente, na mesma conexão, sem paralelizar os comandos.

SELECT DATABASE() AS banco_selecionado;

-- 1. Coluna histórica por pessoa, preservada pela primeira migração.
-- A aplicação atual utiliza a situação por vínculo, adicionada na etapa 2.
SET @dalpe_coluna_existe = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'licenciandos'
      AND COLUMN_NAME = 'situacao'
);

SET @dalpe_sql = IF(
    @dalpe_coluna_existe = 0,
    'ALTER TABLE `licenciandos` ADD COLUMN `situacao` ENUM(''Ativo'',''Inativo'',''Pendente'',''Concluido'') NULL DEFAULT NULL',
    'SELECT ''licenciandos.situacao já existe, nenhuma alteração aplicada'' AS resultado'
);
PREPARE dalpe_migracao FROM @dalpe_sql;
EXECUTE dalpe_migracao;
DEALLOCATE PREPARE dalpe_migracao;

-- 2. Situação manual de cada vínculo com setor, usada atualmente.
-- Concluído é calculado pela aplicação com base na data de término.
SET @dalpe_coluna_existe = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'licenciandosetor'
      AND COLUMN_NAME = 'situacao'
);

SET @dalpe_sql = IF(
    @dalpe_coluna_existe = 0,
    'ALTER TABLE `licenciandosetor` ADD COLUMN `situacao` ENUM(''Ativo'',''Inativo'',''Pendente'') NULL DEFAULT NULL',
    'SELECT ''licenciandosetor.situacao já existe, nenhuma alteração aplicada'' AS resultado'
);
PREPARE dalpe_migracao FROM @dalpe_sql;
EXECUTE dalpe_migracao;
DEALLOCATE PREPARE dalpe_migracao;

-- Conferência: devem aparecer duas linhas com as definições abaixo.
-- licenciandos:     enum('Ativo','Inativo','Pendente','Concluido'), permite NULL.
-- licenciandosetor: enum('Ativo','Inativo','Pendente'), permite NULL.
-- Se uma coluna já existia com outra definição, este script a preserva.
-- Nesse caso, confira a diferença antes de publicar o código.
SELECT TABLE_NAME AS tabela, COLUMN_NAME AS coluna,
       COLUMN_TYPE AS tipo, IS_NULLABLE AS permite_null,
       COLUMN_DEFAULT AS valor_padrao
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('licenciandos', 'licenciandosetor')
  AND COLUMN_NAME = 'situacao'
ORDER BY TABLE_NAME;

-- Este arquivo não registra versões na tabela migrations do CodeIgniter.
-- Se o comando spark migrate for utilizado depois, as migrações PHP existentes
-- verificarão as colunas e registrarão suas versões sem recriá-las.
