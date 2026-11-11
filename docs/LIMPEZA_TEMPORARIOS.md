# Limpeza complementar de temporários

A coleta automática de sessões depende do PHP e do servidor. No XAMPP local,
`session.gc_probability=1` e `session.gc_divisor=1000`: ela é probabilística,
não um agendamento. O CodeIgniter aplica `App.sessionExpiration` ao prazo de
coleta. A configuração do servidor de produção precisa ser conferida lá.

A rotina anterior rodava a cada construção de Auth, varria todos os arquivos
e removia sessões com mais de dez minutos, embora o prazo da aplicação seja
de duas horas. Também não verificava bloqueios de arquivos em uso.

`App\Libraries\LimpezaTemporarios::automatica()` agora roda no evento
`pre_system` das requisições HTTP. Um arquivo fixo de controle e um bloqueio
exclusivo permitem uma varredura no máximo a cada cinco minutos, sem executar
duas limpezas simultâneas. A leitura do diretório é incremental, sem carregar
uma lista completa de nomes em memória.

| Arquivos reconhecidos | Prazo sem atualização |
| --- | --- |
| Sessões do FileHandler na pasta configurada e com prefixo do cookie | `App.sessionExpiration`, atualmente 7200 segundos |
| Tokens de conferência em `writable/importacoes_licenciandos` | 1800 segundos |
| `debugbar_<timestamp>.json` em `writable/debugbar` | 86400 segundos |

Sessões com expiração zero ou outro driver não são removidas pela rotina.
Arquivos recentes, nomes desconhecidos, links simbólicos e arquivos com
bloqueio de uso são preservados. A data é conferida novamente depois de
obter o bloqueio. Falhas de acesso/remoção são registradas no logger.
Logs, uploads, documentos e caches não são apagados por essa rotina.

O arquivo `writable/.limpeza-temporarios.lock` permanece no servidor: é um
único controle reutilizado, não um temporário criado a cada acesso.

Sem tráfego HTTP não há execução. O primeiro acesso depois do intervalo
faz a limpeza; para remoção em horário fixo mesmo sem acessos, é necessário
agendamento pelo administrador da hospedagem. Permissões de escrita/remoção
continuam sendo necessárias. A rotina não substitui os mecanismos de
expiração e logout da aplicação.

Validação isolada, sem remover arquivos reais do projeto:

```powershell
C:\xampp\php\php.exe tests/limpeza_temporarios.php
```
