<?php
$numero = static function ($valor) { return number_format((int) $valor, 0, ',', '.'); };
$percentual = (int) $totais['total'] > 0 ? round(100 * $totais['pendencias'] / $totais['total'], 1) : 0;
$admin = session()->get('authlevel') == ADMINISTRADOR;
$graficos = ['instituicoes' => $instituicoes, 'periodos' => $periodos, 'setores' => $setores];
?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/dashboard.css') . '?v=' . filemtime(FCPATH . 'public/assets/css/dashboard.css') ?>">
<main class="dashboard" aria-label="Painel de licenciandos">
    <div class="dash-intro"><div><span class="dash-eyebrow">DALPE · CAp UFRJ</span><h1>Visão geral</h1><p>Acompanhe os cadastros e encontre o que precisa de atenção.</p></div><a class="dash-refresh" href="<?= base_url('home') ?>"><i class="fas fa-sync-alt" aria-hidden="true"></i> Atualizar painel</a></div>
    <section class="dash-stats" aria-label="Indicadores principais">
        <article class="dash-card dash-stat"><span class="dash-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></span><div><h2>Licenciandos</h2><strong><?= $numero($totais['total']) ?></strong><small>Total de pessoas cadastradas</small></div></article>
        <article class="dash-card dash-stat"><span class="dash-icon"><i class="fas fa-user-check" aria-hidden="true"></i></span><div><h2>Vínculos ativos</h2><strong><?= $numero($situacoes['Ativo']) ?></strong><small>Estágios ativos nos setores</small></div></article>
        <article class="dash-card dash-stat"><span class="dash-icon"><i class="fas fa-user-plus" aria-hidden="true"></i></span><div><h2>Novos (30 dias)</h2><strong><?= $numero($novos30Dias) ?></strong><small>Pessoas criadas nos últimos 30 dias*</small></div></article>
        <article class="dash-card dash-stat dash-warning"><span class="dash-icon"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></span><div><h2>Cadastros com pendências</h2><strong><?= $numero($totais['pendencias']) ?></strong><small><?= esc(number_format($percentual, 1, ',', '.')) ?>% com dados básicos ausentes</small></div></article>
    </section>
    <p class="dash-note">* Considera apenas cadastros com data de criação registrada a partir desta atualização.</p>
    <section class="dash-secondary" aria-label="Cadastros de apoio">
        <?php foreach ([['Setores', $quantSetores, 'layer-group'], ['Universidades', $quantUniversidades, 'university'], ['Modelos de documentos', $quantDocumentos, 'file-alt']] as $indicador): ?>
        <article class="dash-card dash-mini"><i class="fas fa-<?= esc($indicador[2]) ?>" aria-hidden="true"></i><div><h2><?= esc($indicador[0]) ?></h2><strong><?= $numero($indicador[1]) ?></strong></div></article>
        <?php endforeach ?>
        <article class="dash-card dash-mini"><i class="far fa-clock" aria-hidden="true"></i><div><h2>Consulta realizada em</h2><strong class="dash-time"><?= esc($consultadoEm) ?></strong><small>Horário de Brasília</small></div></article>
    </section>
    <div class="dash-charts">
        <?php foreach (['instituicoes' => ['Licenciandos por instituição', 'university'], 'periodos' => ['Licenciandos por período', 'chart-line']] as $chave => $grafico): ?>
        <section class="dash-card dash-panel"><h2><i class="fas fa-<?= esc($grafico[1]) ?>" aria-hidden="true"></i> <?= esc($grafico[0]) ?></h2>
            <?php if ($graficos[$chave]): ?><div class="dash-chart"><canvas id="dash-<?= esc($chave) ?>" role="img" aria-label="<?= esc($grafico[0]) ?>"></canvas></div>
            <details class="dash-data"><summary>Consultar valores</summary><ul><?php foreach ($graficos[$chave] as $item): ?><li><?= esc($item['nome']) ?> <strong><?= $numero($item['quantidade']) ?></strong></li><?php endforeach ?></ul></details>
            <?php else: ?><p class="dash-empty">Nenhum dado disponível.</p><?php endif ?>
            <?php if ($chave === 'periodos'): ?><p class="dash-note">Pessoas distintas em cada período de estágio. Uma pessoa pode aparecer em mais de um período.</p><?php endif ?>
        </section>
        <?php endforeach ?>
        <section class="dash-card dash-panel"><h2><i class="fas fa-user-check" aria-hidden="true"></i> Situação dos vínculos</h2>
            <?php
            $coresSituacao = ['Ativo' => '#16855b', 'Inativo' => '#758294', 'Pendente' => '#d69c23', 'Concluido' => '#118eaa', 'nao_informado' => '#dde3e9'];
            $rotulosSituacao = \App\Models\LicenciandoSetorModel::SITUACOES + ['nao_informado' => 'Não informado'];
            $partes = []; $acumulado = 0;
            foreach ($situacoes as $chave => $quantidade) {
                $proximo = $acumulado + ($quantVinculos ? 100 * $quantidade / $quantVinculos : 0);
                if ($quantidade) $partes[] = $coresSituacao[$chave] . ' ' . $acumulado . '% ' . $proximo . '%';
                $acumulado = $proximo;
            }
            ?>
            <div class="dash-situation">
                <div class="dash-donut" aria-hidden="true" style="background:conic-gradient(<?= esc(implode(', ', $partes) ?: '#dde3e9 0% 100%') ?>)"><div><strong><?= $numero($quantVinculos) ?></strong><small>Vínculos</small></div></div>
                <ul class="dash-status-list"><?php foreach ($situacoes as $chave => $quantidade): ?><li><span class="dash-status-dot" style="background:<?= esc($coresSituacao[$chave]) ?>" aria-hidden="true"></span><span><?= esc($rotulosSituacao[$chave]) ?></span><strong><?= $numero($quantidade) ?></strong></li><?php endforeach ?></ul>
            </div>
            <p class="dash-note">Cada v?nculo com setor ? contado separadamente. Uma pessoa pode ter situa??es diferentes em setores distintos.</p>
            <?php if ($situacoes['nao_informado']): ?><p class="dash-note">Vínculos sem classificação e sem conclusão atingida aparecem como “Não informado”.</p><?php endif ?>
        </section>
    </div>
    <div class="dash-middle">
        <section class="dash-card dash-panel"><h2><i class="fas fa-layer-group" aria-hidden="true"></i> Licenciandos por setor</h2>
            <?php if ($setores): ?><div class="dash-chart-scroll"><div class="dash-sector-chart" style="min-width: <?= max(440, count($setores) * 65) ?>px"><canvas id="dash-setores" role="img" aria-label="Licenciandos por setor"></canvas></div></div><details class="dash-data"><summary>Consultar valores</summary><ul><?php foreach ($setores as $item): ?><li><?= esc($item['nome']) ?> <strong><?= $numero($item['quantidade']) ?></strong></li><?php endforeach ?></ul></details><?php else: ?><p class="dash-empty">Nenhum setor cadastrado.</p><?php endif ?>
            <p class="dash-note">Pessoas distintas por setor; um licenciando pode estar vinculado a vários setores.</p>
        </section>
        <section class="dash-card dash-panel"><div class="dash-panel-heading"><h2><i class="fas fa-users" aria-hidden="true"></i> Últimos licenciandos</h2><a href="<?= base_url('licenciandos') ?>">Ver todos <span aria-hidden="true">→</span></a></div><p class="dash-note">Ordem decrescente do identificador de cadastro.</p>
            <ul class="dash-recent"><?php foreach ($ultimos as $pessoa): $nome = trim($pessoa['nome_social'] ?? '') ?: $pessoa['nome_completo']; ?>
            <li><span class="dash-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($nome, 0, 1))) ?></span><div><a href="<?= base_url('licenciandos/editar/' . (int) $pessoa['licenciando_id'] . '/' . (int) $pessoa['vinculo_id']) ?>"><?= esc($nome) ?></a><small>Cadastro #<?= (int) $pessoa['licenciando_id'] ?></small></div><span class="dash-university"><?= esc($pessoa['sigla'] ?: 'Sem instituição') ?></span></li>
            <?php endforeach ?></ul><?php if (!$ultimos): ?><p class="dash-empty">Nenhum licenciando cadastrado.</p><?php endif ?>
        </section>
    </div>
    <div class="dash-bottom">
        <section class="dash-card dash-panel"><h2><i class="fas fa-chart-bar" aria-hidden="true"></i> Evolução de cadastros de vínculos</h2><div class="dash-evolution"><?php foreach ($evolucao as $label => $quantidade): ?><div><span><?= esc($label) ?></span><strong><?= $numero($quantidade) ?></strong><small>vínculos cadastrados</small></div><?php endforeach ?></div><p class="dash-note">Usa a data de cadastro no setor. Uma pessoa pode gerar vários vínculos. Datas ausentes ou inválidas ficam fora da contagem.</p></section>
        <section class="dash-card dash-panel"><h2><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Pendências de cadastro</h2><ul class="dash-pending"><?php foreach (['sem_instituicao' => 'Sem instituição válida', 'sem_setor' => 'Sem setor válido', 'sem_email' => 'Sem e-mail'] as $chave => $label): ?><li><span><?= esc($label) ?></span><span class="dash-meter" aria-hidden="true"><span style="width: <?= $totais['total'] ? round(100 * $totais[$chave] / $totais['total'], 2) : 0 ?>%"></span></span><strong><?= $numero($totais[$chave]) ?></strong></li><?php endforeach ?></ul><p class="dash-note">Uma pessoa pode ter mais de uma pendência. O total no topo conta cada pessoa uma única vez.</p><a class="dash-text-link" href="<?= base_url('licenciandos') ?>">Conferir cadastros <span aria-hidden="true">→</span></a></section>
        <section class="dash-card dash-panel"><h2><i class="fas fa-bolt" aria-hidden="true"></i> Ações rápidas</h2><div class="dash-actions"><a href="<?= base_url('relatorios') ?>"><i class="fas fa-chart-bar" aria-hidden="true"></i> Relatório consolidado</a><a href="<?= base_url('licenciandos') ?>#lic-export"><i class="fas fa-download" aria-hidden="true"></i> Exportar licenciandos</a><a href="<?= base_url('licenciandos/adicionar') ?>"><i class="fas fa-plus" aria-hidden="true"></i> Cadastrar licenciando</a><a href="<?= base_url('licenciandos') ?>"><i class="fas fa-search" aria-hidden="true"></i> Consultar licenciandos</a><?php if ($admin): ?><a href="<?= base_url('documentos') ?>"><i class="far fa-file-alt" aria-hidden="true"></i> Modelos de documentos</a><a href="<?= base_url('setores') ?>"><i class="fas fa-layer-group" aria-hidden="true"></i> Gerenciar setores</a><?php endif ?></div><p class="dash-note">Exporte a listagem de licenciandos com os filtros desejados.</p></section>
    </div>
</main>
<script id="dashboard-data" type="application/json"><?= json_encode($graficos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="<?= base_url('public/assets/js/plugins/chartjs.min.js') ?>"></script>
<script src="<?= base_url('public/assets/js/dashboard.js') . '?v=' . filemtime(FCPATH . 'public/assets/js/dashboard.js') ?>"></script>
