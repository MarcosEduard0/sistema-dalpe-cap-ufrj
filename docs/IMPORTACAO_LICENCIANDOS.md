# Importação de licenciandos

O fluxo tem três etapas: selecionar um CSV, conferir o planejamento no servidor e confirmar a gravação. A amostra no navegador mostra até cinco registros. A conferência completa verifica todo o arquivo, mostra até 100 registros na tabela e lista os erros encontrados. Qualquer registro com erro bloqueia a confirmação do lote.

O botão **Baixar CSV de exemplo** gera um arquivo UTF-8 com BOM, separador `;`, cabeçalho e uma linha fictícia. A linha fictícia deve ser substituída ou excluída. O exemplo também está disponível na tela. O importador aceita `;` e `,`, respeitando as aspas do CSV.

As 13 colunas, nesta ordem, são:

```text
email;nome_completo;nome_social;dre;universidade;professor;setor;endereco;bairro;cep;cidade;telefone1;telefone2
```

Todas as colunas precisam existir, inclusive as vazias. Nomes dos cabeçalhos anteriores, como `Nome`, `Instituição(sigla)` e `Setor Curricular`, continuam aceitos na mesma ordem. O arquivo deve estar em UTF-8, ter até 10 MB e até 5.000 registros. O DRE é tratado como texto para preservar zeros à esquerda.

Universidades são identificadas pela sigla e setores pelo nome. A consulta desconsidera diferenças de caixa e acentuação, mas rejeita correspondências ambíguas. Para vários setores no mesmo registro, separe os nomes por vírgula dentro do campo. Quando informado, deve haver um professor por setor, na mesma ordem. O CSV precisa manter o campo entre aspas quando o separador do arquivo também for vírgula.

O DRE identifica a pessoa. Para pessoas existentes, os dados pessoais e o endereço são preservados, e apenas novos setores são acrescentados. O par DRE/setor já existente é ignorado, inclusive quando aparece novamente no próprio arquivo. O ano escolhido é atribuído aos novos vínculos e não altera essa regra de duplicidade. Dados pessoais conflitantes para um novo DRE repetido no arquivo são sinalizados para correção.

Novos vínculos começam com situação **Pendente**, zero horas e data de cadastro atual em `America/Sao_Paulo`. As datas de início e conclusão são preenchidas posteriormente na edição. A data de término ausente usa `0000-00-00`, conforme a estrutura legada atual do banco, e não produz situação Concluído.

Na conferência, os registros pendentes ficam por até 30 minutos em `writable/importacoes_licenciandos`, fora da pasta pública. Um token vinculado à sessão e ao usuário permite confirmar uma única vez. Os cadastros são consultados novamente na confirmação. A gravação usa transação sobre as três tabelas InnoDB, com IDs retornados pelos inserts, e reverte o lote se ocorrer erro. Nenhuma nova alteração de estrutura de tabela é necessária para este fluxo.

O CSV de consulta exportado pela listagem tem cinco colunas. Para importar, utilize o modelo específico de 13 colunas oferecido nesta tela.

Verificações locais:

```powershell
C:\xampp\php\php.exe tests\importacao_csv.php
C:\xampp\php\php.exe tests\importacao_integracao.php
```

O primeiro teste não acessa o banco. O segundo exige conexão local e permissões para criar banco e trigger: copia apenas a estrutura para um banco temporário isolado, testa a gravação e remove esse banco ao terminar.
