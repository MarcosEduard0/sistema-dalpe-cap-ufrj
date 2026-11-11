# Situação do licenciando

A situação pertence ao vínculo em `licenciandosetor`, não à pessoa. Um licenciando pode estar **Concluído** em um setor e **Ativo** em outro.

Estados exibidos: **Ativo**, **Inativo**, **Pendente** e **Concluído**. O campo `licenciandosetor.situacao` guarda a escolha manual entre os três primeiros. **Concluído** prevalece automaticamente quando `data_termino` é uma data válida, diferente de zero e menor ou igual ao dia atual em `America/Sao_Paulo`. A data de início não participa da regra, preservando o comportamento anterior.

O cálculo ocorre na consulta, sem precisar salvar novamente quando a data chega. Ao remover ou adiar a conclusão, a escolha manual anterior volta a valer. O formulário mostra **Concluído** e bloqueia a seleção manual enquanto a data estiver atingida; a validação do servidor também rejeita a tentativa de gravar esse estado manualmente.

Novos vínculos, inclusive importações sem situação, começam como **Pendente**, e podem aparecer imediatamente como **Concluído** caso tenham data de conclusão já atingida. Vínculos antigos sem classificação ficam com `NULL`, exibido como **Não informado**, salvo quando já concluídos pela data. Isso não é um quinto estado de negócio.

A antiga coluna `licenciandos.situacao` é preservada para não perder eventuais classificações anteriores, mas não é utilizada nem atualizada pela aplicação. Não copiamos esse valor para os vínculos, pois ele não identifica a situação de cada setor.

Antes de usar esta versão em outro ambiente, aplicar a migração:

```powershell
C:\xampp\php\php.exe spark migrate --all
```

A segunda migração adiciona a coluna aos vínculos e não modifica suas datas nem atribui classificações manuais aos registros antigos. O rollback remove a coluna e suas classificações; não deve ser executado sem preservar esses dados.

A home conta situações dos vínculos que possuem licenciando e setor válidos; o total de licenciandos permanece separado. A listagem detalha a situação por setor e a edição usa o vínculo selecionado. **Cadastros com pendências** continua sendo um indicador separado de ausência de instituição válida, setor válido ou e-mail; não significa situação **Pendente**.

Não existe acompanhamento de documentos pendentes: os documentos atuais são modelos para emissão.

Teste da regra sem banco: `C:\xampp\php\php.exe tests\situacao_vinculo.php`.

## Listagem e filtros

A listagem mantém uma linha por pessoa e mostra todos os vínculos e suas situações. Quando setor e situação são combinados, ambos devem corresponder ao mesmo vínculo. Exemplo: quem concluiu Matemática e está ativo em Física aparece em **Física + Ativo**, mas não em **Matemática + Ativo**. Universidade e pesquisa são aplicadas em conjunto com esses filtros.

Os indicadores gerais contam pessoas e vínculos separadamente e permanecem relativos à base completa. O resumo de pendências usa os mesmos critérios da home. Os vínculos são carregados em lote, sem consultas por pessoa.

O CSV de consulta exporta as colunas visíveis de todos os resultados filtrados, em ordem, incluindo todas as páginas. Usa UTF-8, separador `;` e mantém as situações por setor. Campos que poderiam ser interpretados como fórmulas por planilhas são tratados como texto. O importador continua usando seu formato próprio de 13 campos.
