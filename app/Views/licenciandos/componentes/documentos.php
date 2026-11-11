<?php
$itensDocumentos = [[
    'nome' => 'Crachá',
    'icone' => 'bx-id-card',
    'url' => base_url('documentos/cracha/' . (int) $licenciando['licenciando_id'] . '/' . (int) $licenciandoSetor_id),
]];
foreach ($documentos as $documento) {
    $itensDocumentos[] = [
        'nome' => $documento['nome'],
        'icone' => 'bx-file',
        'url' => base_url('documentos/emitir/' . (int) $licenciando['licenciando_id'] . '/' . (int) $licenciandoSetor_id . '/' . (int) $documento['documento_id']),
    ];
}
?>
<section class="card licenciando-documents" aria-labelledby="licenciando-documents-title">
    <div class="licenciando-documents-heading"><span class="licenciando-documents-icon"><i class="bx bx-file" aria-hidden="true"></i></span><h2 id="licenciando-documents-title">Documentos</h2><span class="licenciando-documents-count"><?= count($itensDocumentos) ?></span></div>
    <p class="licenciando-documents-caption">Emissão para o vínculo selecionado.</p>
    <ul class="licenciando-document-list">
        <?php foreach ($itensDocumentos as $itemDocumento): ?>
        <li><a class="licenciando-document-card" href="<?= esc($itemDocumento['url']) ?>" target="_blank" rel="noopener" aria-label="<?= esc('Abrir ' . $itemDocumento['nome'] . ' em nova aba') ?>">
            <span class="licenciando-document-icon"><i class="bx <?= esc($itemDocumento['icone']) ?>" aria-hidden="true"></i></span>
            <span class="licenciando-document-text"><strong><?= esc($itemDocumento['nome']) ?></strong><small>Abrir PDF <i class="bx bx-link-external" aria-hidden="true"></i></small></span>
            <i class="bx bx-show licenciando-document-open" aria-hidden="true"></i>
        </a></li>
        <?php endforeach ?>
    </ul>
</section>
