# Datas e exclusão lógica de licenciandos

Aplicar a migração `2026-11-11-090000_AddAuditoriaLicenciandos` antes de
publicar o código. No desenvolvimento local ela já foi executada.
Para produção com acesso apenas SFTP, executar no banco o arquivo
`docs/sql/2026-11-11_auditoria_licenciandos.sql` (DBeaver: Alt+X).

- `created_at`: criação pelo cadastro ou importação, sem alteração posterior.
- `updated_at`: última gravação da pessoa pelo model, incluindo a exclusão lógica.
- `deleted_at`: data da exclusão lógica; `NULL` identifica cadastro visível.

As datas são `DATETIME`, no fuso configurado pela aplicação. São mantidas pelo
model CodeIgniter, não por triggers. Escritas externas por SQL precisam preencher
as datas explicitamente. Alterar um vínculo isoladamente não representa uma
alteração dos dados pessoais; salvar o formulário completo grava a pessoa.
Os campos não são aceitos como entrada dos formulários.

Registros anteriores ficam com datas desconhecidas (`NULL`), sem aproximação por
ID ou pela data de cadastro no setor. O card Novos (30 dias) passa a contar pessoas
visíveis com `created_at` nos últimos 30 dias de calendário, incluindo hoje.
O painel informa que os cadastros antigos sem data não entram nesse indicador.

Excluir uma pessoa preserva os vínculos e o endereço. Ela fica oculta nas telas,
consultas de documentos e estatísticas. O DRE permanece reservado: cadastro manual
e importação não recriam nem reativam silenciosamente a mesma pessoa.
Universidades com registros preservados continuam protegidas contra exclusão,
mesmo quando todos esses registros estão excluídos logicamente.

Não há tela de lixeira/restauração nesta etapa. Uma restauração administrativa
exige revisar a pessoa e seus relacionamentos antes de limpar `deleted_at`.
O rollback da migração bloqueia se houver pessoas excluídas, para evitar
reaparecimento acidental. Não execute rollback sem backup: datas seriam perdidas.

Teste isolado: `php tests/auditoria_licenciandos.php`.
