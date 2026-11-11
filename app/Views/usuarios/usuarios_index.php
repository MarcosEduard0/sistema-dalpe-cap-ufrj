<?php
$totalUsuarios = count($usuarios);
$totalAdministradores = 0;
$ultimoAcesso = null;
$datasUsuario = [];
$temOutrosTipos = false;
$fusoUsuarios = new DateTimeZone(config('App')->appTimezone);
foreach ($usuarios as $itemUsuario) {
    if ((int) $itemUsuario['authlevel'] === ADMINISTRADOR) $totalAdministradores++;
    if (!in_array((int) $itemUsuario['authlevel'], [ADMINISTRADOR, COMUM], true)) $temOutrosTipos = true;
    $valorData = $itemUsuario['ultimo_login'] ?? '';
    $dataUsuario = $valorData && $valorData !== '0000-00-00 00:00:00' ? DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $valorData, $fusoUsuarios) : false;
    $valida = $dataUsuario && $dataUsuario->format('Y-m-d H:i:s') === $valorData && $valorData >= '1000-01-01 00:00:00';
    $datasUsuario[$itemUsuario['usuario_id']] = ['ordem' => $valida ? $dataUsuario->getTimestamp() : 0, 'texto' => $valida ? $dataUsuario->format('d/m/Y, H:i') : (!$valorData || $valorData === '0000-00-00 00:00:00' ? 'Nunca' : 'Não informado')];
    if ($valida && (!$ultimoAcesso || $dataUsuario > $ultimoAcesso)) $ultimoAcesso = $dataUsuario;
}
?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/usuarios-lista.css') . '?v=' . filemtime(FCPATH . 'public/assets/css/usuarios-lista.css') ?>">
<main class="usuarios-page" aria-labelledby="users-title">
    <nav class="users-breadcrumb" aria-label="Localização"><i class="bx bx-group" aria-hidden="true"></i><span>Usuários</span><span aria-hidden="true">/</span><span>Listagem</span></nav>
    <h1 id="users-title" class="sr-only">Usuários</h1>
    <div class="users-overview"><section class="users-stats" aria-label="Resumo de usuários">
        <article class="users-card users-stat"><span class="users-stat-icon"><i class="bx bx-group" aria-hidden="true"></i></span><div><h2>Total de usuários</h2><strong><?= $totalUsuarios ?></strong></div></article>
        <article class="users-card users-stat"><span class="users-stat-icon users-admin-icon"><i class="bx bx-check-shield" aria-hidden="true"></i></span><div><h2>Administradores</h2><strong><?= $totalAdministradores ?></strong></div></article>
        <article class="users-card users-stat"><span class="users-stat-icon"><i class="bx bx-calendar" aria-hidden="true"></i></span><div><h2>Último acesso registrado</h2><strong class="users-last-access"><?= $ultimoAcesso ? esc($ultimoAcesso->format('d/m/Y, H:i')) : 'Nenhum acesso' ?></strong><small>Horário de Brasília</small></div></article>
    </section><a class="users-add" href="<?= base_url('usuarios/adicionar') ?>"><i class="bx bx-plus" aria-hidden="true"></i> Adicionar usuário</a></div>
    <section class="users-card users-list" aria-labelledby="users-list-title">
        <div class="users-list-heading"><h2 id="users-list-title"><i class="bx bx-group" aria-hidden="true"></i> Lista de usuários</h2><span><?= $totalUsuarios ?> <?= $totalUsuarios === 1 ? 'usuário' : 'usuários' ?></span></div>
        <div class="users-toolbar"><div class="users-filter"><i class="bx bx-filter-alt" aria-hidden="true"></i><label for="users-filter" class="sr-only">Filtrar por tipo de acesso</label><select id="users-filter" disabled><option value="">Todos os tipos</option><option value="administrador">Administrador</option><option value="comum">Comum</option><?php if ($temOutrosTipos): ?><option value="outro">Não informado</option><?php endif ?></select></div><div class="users-search"><i class="bx bx-search" aria-hidden="true"></i><label for="users-search" class="sr-only">Pesquisar usuário ou nome</label><input type="search" id="users-search" placeholder="Pesquisar usuário..." autocomplete="off" disabled></div></div>
        <div class="users-table-scroll" tabindex="0" role="region" aria-label="Tabela de usuários"><table id="usuarios-table" class="users-table"><thead><tr><th scope="col">Usuário</th><th scope="col">Nome</th><th scope="col">Tipo de acesso</th><th scope="col">Última conexão</th><th scope="col">Ações</th></tr></thead><tbody>
            <?php foreach ($usuarios as $usuario):
                $tipo = (int) $usuario['authlevel'] === ADMINISTRADOR ? 'administrador' : ((int) $usuario['authlevel'] === COMUM ? 'comum' : 'outro');
                $rotuloTipo = ['administrador' => 'Administrador', 'comum' => 'Comum', 'outro' => 'Não informado'][$tipo];
                $dataConexao = $datasUsuario[$usuario['usuario_id']];
            ?>
            <tr><td><a class="users-login" href="<?= base_url('usuarios/editar/' . (int) $usuario['usuario_id']) ?>"><?= esc($usuario['usuario']) ?></a></td><td><span class="users-name"><?= esc($usuario['nome_completo']) ?></span><?php if (!empty($usuario['cargo'])): ?><small class="users-job"><?= esc($usuario['cargo']) ?></small><?php endif ?></td><td data-search="<?= esc($tipo) ?>"><span class="users-type users-type-<?= esc($tipo) ?>"><?= esc($rotuloTipo) ?></span></td><td data-order="<?= (int) $dataConexao['ordem'] ?>"><span class="<?= $dataConexao['ordem'] ? '' : 'users-never' ?>"><?= esc($dataConexao['texto']) ?></span></td><td><div class="users-row-actions"><a class="users-action users-edit" href="<?= base_url('usuarios/editar/' . (int) $usuario['usuario_id']) ?>" title="Editar usuário" aria-label="<?= esc('Editar ' . $usuario['usuario']) ?>"><i class="bx bx-edit" aria-hidden="true"></i></a><button type="button" class="users-action users-delete" data-delete-url="<?= base_url('usuarios/excluir/' . (int) $usuario['usuario_id']) ?>" data-user-name="<?= esc($usuario['nome_completo'] . ' (' . $usuario['usuario'] . ')') ?>" title="Excluir usuário" aria-label="<?= esc('Excluir ' . $usuario['usuario']) ?>" disabled><i class="bx bx-trash" aria-hidden="true"></i></button></div></td></tr>
            <?php endforeach ?>
        </tbody></table></div>
        <div class="users-list-footer"><p id="users-results" role="status"><?= $totalUsuarios ?> <?= $totalUsuarios === 1 ? 'usuário cadastrado' : 'usuários cadastrados' ?></p><nav class="users-pagination" aria-label="Páginas da lista de usuários"><button type="button" id="users-previous" disabled><i class="bx bx-chevron-left" aria-hidden="true"></i> Anterior</button><span id="users-pages"></span><button type="button" id="users-next" disabled>Próximo <i class="bx bx-chevron-right" aria-hidden="true"></i></button></nav></div>
    </section>
    <dialog id="users-delete-dialog" class="users-delete-dialog" aria-labelledby="users-delete-title" aria-describedby="users-delete-description"><div class="users-delete-dialog-icon"><i class="bx bx-trash" aria-hidden="true"></i></div><h2 id="users-delete-title">Excluir usuário?</h2><p id="users-delete-description">O cadastro de <strong id="users-delete-name"></strong> será removido.</p><p class="users-delete-warning">Esta ação não pode ser desfeita.</p><form id="users-delete-form" method="post"><?= csrf_field() ?><div class="users-dialog-actions"><button type="button" id="users-delete-cancel">Cancelar</button><button type="submit" class="users-confirm-delete">Excluir usuário</button></div></form></dialog>
</main>
<script src="<?= base_url('public/assets/js/usuarios-lista.js') . '?v=' . filemtime(FCPATH . 'public/assets/js/usuarios-lista.js') ?>"></script>
