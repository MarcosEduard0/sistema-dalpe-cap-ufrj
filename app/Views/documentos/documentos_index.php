<?php
$variaveisDisponiveis = config('DocumentoPreview')->variaveis;
$totalDocumentos = count($documentos);
$totalComVariaveis = 0;
$contagemVariaveis = [];
foreach ($documentos as $item) {
    preg_match_all('/\[([A-Z_]+)\]/', $item['conteudo'] ?? '', $marcadores);
    $reconhecidas = array_intersect(array_unique($marcadores[1]), array_keys($variaveisDisponiveis));
    $contagemVariaveis[$item['documento_id']] = count($reconhecidas);
    if ($reconhecidas) $totalComVariaveis++;
}
?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/documentos-lista.css') . '?v=' . filemtime(FCPATH . 'public/assets/css/documentos-lista.css') ?>">
<main class="documentos-page" aria-labelledby="documentos-page-title">
    <div class="docs-heading"><span class="docs-heading-icon"><i class="bx bx-file" aria-hidden="true"></i></span><div><h1 id="documentos-page-title">Documentos</h1><nav aria-label="Localização"><a href="<?= base_url('home') ?>"><i class="bx bx-home-alt" aria-hidden="true"></i> Início</a><span aria-hidden="true">/</span><span>Documentos</span></nav></div></div>
    <div class="docs-overview">
        <section class="docs-stats" aria-label="Resumo dos modelos">
            <article class="docs-card docs-stat"><span class="docs-stat-icon"><i class="bx bx-file" aria-hidden="true"></i></span><div><h2>Total de modelos</h2><strong><?= $totalDocumentos ?></strong></div></article>
            <article class="docs-card docs-stat"><span class="docs-stat-icon"><i class="bx bx-code-curly" aria-hidden="true"></i></span><div><h2>Modelos com variáveis</h2><strong><?= $totalComVariaveis ?></strong></div></article>
            <article class="docs-card docs-stat"><span class="docs-stat-icon"><i class="bx bx-layer" aria-hidden="true"></i></span><div><h2>Variáveis disponíveis</h2><strong><?= count($variaveisDisponiveis) ?></strong></div></article>
        </section>
        <div class="docs-main-actions"><a class="docs-primary" href="<?= base_url('documentos/adicionar') ?>"><i class="bx bx-plus" aria-hidden="true"></i> Adicionar documento</a><a class="docs-secondary" href="<?= base_url('documentos/layout_documentos') ?>"><i class="bx bx-layer" aria-hidden="true"></i> Configurar cabeçalho</a></div>
    </div>
    <section class="docs-card docs-list" aria-labelledby="docs-list-title">
        <div class="docs-list-heading"><h2 id="docs-list-title"><i class="bx bx-file" aria-hidden="true"></i> Lista de documentos</h2><span class="docs-list-badge"><?= $totalDocumentos ?> modelos</span></div>
        <div class="docs-toolbar"><div class="docs-filter"><label for="docs-filter">Uso de variáveis</label><select id="docs-filter" disabled><option value="">Todos os modelos</option><option value="com">Com variáveis</option><option value="sem">Sem variáveis</option></select></div><div class="docs-search"><i class="bx bx-search" aria-hidden="true"></i><label for="docs-search" class="sr-only">Pesquisar pelo nome do documento</label><input id="docs-search" type="search" placeholder="Pesquisar documento..." autocomplete="off" disabled></div></div>
        <div class="docs-table-scroll" tabindex="0" role="region" aria-label="Tabela de modelos de documentos">
            <table id="documentos-table" class="docs-table"><thead><tr><th scope="col">Nome do documento</th><th scope="col">Variáveis no modelo</th><th scope="col">Ações</th></tr></thead><tbody>
                <?php foreach ($documentos as $documento): $quantidade = $contagemVariaveis[$documento['documento_id']]; ?>
                <tr><td><a class="docs-document-name" href="<?= base_url('documentos/editar/' . (int) $documento['documento_id']) ?>"><span class="docs-row-icon"><i class="bx bx-file" aria-hidden="true"></i></span><span><?= esc($documento['nome']) ?></span></a></td>
                    <td data-order="<?= $quantidade ?>" data-search="<?= $quantidade ? 'com' : 'sem' ?>"><span class="docs-variable-count <?= $quantidade ? '' : 'docs-variable-none' ?>"><?= $quantidade ?> <?= $quantidade === 1 ? 'variável' : 'variáveis' ?></span></td>
                    <td><div class="docs-row-actions">
                        <a class="docs-action docs-edit" href="<?= base_url('documentos/editar/' . (int) $documento['documento_id']) ?>" title="Editar documento" aria-label="<?= esc('Editar ' . $documento['nome']) ?>"><i class="bx bx-edit-alt" aria-hidden="true"></i></a>
                        <a class="docs-action docs-copy" href="<?= base_url('documentos/adicionar') . '?copiar=' . (int) $documento['documento_id'] ?>" title="Criar cópia" aria-label="<?= esc('Criar cópia de ' . $documento['nome']) ?>"><i class="bx bx-copy" aria-hidden="true"></i></a>
                        <button type="button" class="docs-action docs-delete" data-delete-url="<?= base_url('documentos/excluir/' . (int) $documento['documento_id']) ?>" data-document-name="<?= esc($documento['nome']) ?>" title="Excluir documento" aria-label="<?= esc('Excluir ' . $documento['nome']) ?>" disabled><i class="bx bx-trash" aria-hidden="true"></i></button>
                    </div></td>
                </tr>
                <?php endforeach ?>
            </tbody></table>
        </div>
        <div class="docs-list-footer"><p id="docs-results" role="status"><?= $totalDocumentos ?> <?= $totalDocumentos === 1 ? 'modelo cadastrado' : 'modelos cadastrados' ?></p><nav class="docs-pagination" aria-label="Páginas da lista"><button type="button" id="docs-previous" disabled><i class="bx bx-chevron-left" aria-hidden="true"></i> Anterior</button><span id="docs-pages"></span><button type="button" id="docs-next" disabled>Próximo <i class="bx bx-chevron-right" aria-hidden="true"></i></button></nav></div>
        <p class="docs-list-note">Cada modelo pode ser emitido com os dados do licenciando e do vínculo selecionado.</p>
    </section>
    <dialog id="docs-delete-dialog" class="docs-delete-dialog" aria-labelledby="docs-delete-title" aria-describedby="docs-delete-description"><div class="docs-delete-dialog-icon"><i class="bx bx-trash" aria-hidden="true"></i></div><h2 id="docs-delete-title">Excluir documento?</h2><p id="docs-delete-description">O modelo <strong id="docs-delete-name"></strong> será removido da lista de documentos.</p><p class="docs-delete-warning">Esta ação não pode ser desfeita.</p><form id="docs-delete-form" method="post"><?= csrf_field() ?><div class="docs-dialog-actions"><button type="button" id="docs-delete-cancel">Cancelar</button><button type="submit" class="docs-confirm-delete">Excluir documento</button></div></form></dialog>
</main>
<script src="<?= base_url('public/assets/js/documentos-lista.js') . '?v=' . filemtime(FCPATH . 'public/assets/js/documentos-lista.js') ?>"></script>
