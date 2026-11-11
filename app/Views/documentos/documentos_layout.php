<?php
$imagensCabecalho = [];
foreach ($imagens as $indice => $imagem) {
    $imagensCabecalho[$indice] = $imagem ? base_url($imagem) . '?v=' . filemtime(FCPATH . $imagem) : '';
}
?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/documentos-cabecalho.css') . '?v=' . filemtime(FCPATH . 'public/assets/css/documentos-cabecalho.css') ?>">
<main class="header-settings" aria-labelledby="header-settings-title">
    <nav class="header-breadcrumb" aria-label="Localização"><a href="<?= base_url('documentos') ?>">Documentos</a><i class="bx bx-chevron-right" aria-hidden="true"></i><span>Cabeçalho</span></nav>
    <section class="header-settings-banner"><span class="header-banner-icon"><i class="bx bx-image-alt" aria-hidden="true"></i></span><div><h1 id="header-settings-title">Cabeçalho dos documentos</h1><p>Defina as imagens exibidas no topo dos documentos emitidos.</p></div></section>
    <?php if (!empty($erroCabecalho)): ?><div class="alert alert-danger" role="alert"><?= esc($erroCabecalho) ?> Selecione novamente os arquivos que deseja enviar.</div><?php endif ?>
    <form id="header-settings-form" action="<?= base_url('documentos/salvar_layout') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="header-settings-grid">
            <aside class="header-settings-card header-guidance" aria-labelledby="header-guidance-title"><h2 id="header-guidance-title"><i class="bx bx-info-circle" aria-hidden="true"></i> Orientações</h2>
                <div class="header-guide-item"><span><i class="bx bx-sort" aria-hidden="true"></i></span><div><h3>Ordem de exibição</h3><p>As posições 1, 2 e 3 aparecem da esquerda para a direita. Posições vazias são ignoradas.</p></div></div>
                <div class="header-guide-item"><span><i class="bx bx-expand" aria-hidden="true"></i></span><div><h3>Proporção da imagem</h3><p>Prefira imagens quadradas, por exemplo, 512 × 512 px. A proporção original será preservada.</p></div></div>
                <div class="header-guide-item"><span><i class="bx bx-file" aria-hidden="true"></i></span><div><h3>Formatos aceitos</h3><div class="header-format-tags"><span>JPG</span><span>JPEG</span><span>PNG</span></div></div></div>
                <div class="header-guide-item"><span><i class="bx bx-image" aria-hidden="true"></i></span><div><h3>Tamanho máximo</h3><p>Até 2 MB por arquivo e até três imagens no cabeçalho.</p></div></div>
                <p class="header-guide-note">As alterações passam a valer nos documentos emitidos após salvar.</p>
            </aside>
            <section class="header-settings-card header-images-section" aria-labelledby="header-images-title">
                <div class="header-images-heading"><h2 id="header-images-title"><i class="bx bx-layer" aria-hidden="true"></i> Imagens do cabeçalho <span id="header-images-count"><?= count(array_filter($imagens)) ?>/3</span></h2><button type="button" id="header-add-image" class="header-primary" disabled><i class="bx bx-plus" aria-hidden="true"></i> Adicionar imagem</button></div>
                <div class="header-images-grid">
                    <?php foreach ($imagensCabecalho as $indice => $src): $slot = $indice + 1; ?>
                    <article class="header-image-card" data-slot="<?= $slot ?>" data-original-src="<?= esc($src) ?>">
                        <div class="header-image-top"><span class="header-position">Posição <?= $slot ?></span><div class="header-image-actions"><button type="button" class="header-pick-file" title="Substituir imagem <?= $slot ?>" aria-label="Selecionar imagem da posição <?= $slot ?>"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="header-remove-image" title="Remover imagem <?= $slot ?>" aria-label="Remover imagem da posição <?= $slot ?>" disabled><i class="bx bx-trash" aria-hidden="true"></i></button></div></div>
                        <label class="header-image-picker" for="imageInput<?= $indice ?>"><img class="header-image-preview" <?= $src ? 'src="' . esc($src) . '"' : '' ?> alt="Imagem da posição <?= $slot ?>" <?= $src ? '' : 'hidden' ?>><span class="header-image-empty" <?= $src ? 'hidden' : '' ?>><i class="bx bx-image-add" aria-hidden="true"></i><span>Selecionar imagem</span><small>JPG, JPEG ou PNG</small></span><span class="sr-only">Selecionar arquivo para a posição <?= $slot ?></span></label>
                        <input type="file" class="header-file-input" id="imageInput<?= $indice ?>" name="imagem<?= $slot ?>" accept="image/jpeg,image/png,.jpg,.jpeg,.png" aria-describedby="header-file-error<?= $slot ?>">
                        <input type="checkbox" class="header-delete-checkbox" name="deleteImagem<?= $slot ?>" id="deleteImagem<?= $slot ?>" value="1" hidden>
                        <div class="header-image-footer"><span class="header-image-status"><?= $src ? 'Imagem atual' : 'Posição disponível' ?></span><button type="button" class="header-undo-remove" hidden>Desfazer remoção</button><p class="header-file-error" id="header-file-error<?= $slot ?>" role="alert" hidden></p></div>
                    </article>
                    <?php endforeach ?>
                </div>
                <section class="header-combined-preview" aria-labelledby="header-preview-title"><div class="header-preview-heading"><h3 id="header-preview-title"><i class="bx bx-show" aria-hidden="true"></i> Prévia da ordem de exibição</h3><span>Antes de salvar</span></div><div id="header-preview-images" class="header-preview-images"><?php foreach ($imagensCabecalho as $indice => $src): if (!$src) continue; ?><img src="<?= esc($src) ?>" alt="Posição <?= $indice + 1 ?>"><?php endforeach ?></div><p id="header-preview-empty" <?= array_filter($imagens) ? 'hidden' : '' ?>>Nenhuma imagem selecionada. Os documentos serão emitidos sem essas imagens no cabeçalho.</p></section>
            </section>
        </div>
        <p id="header-change-status" class="header-change-status" role="status">Nenhuma alteração pendente.</p>
        <div class="header-settings-actions"><a href="<?= base_url('documentos') ?>"><i class="bx bx-arrow-back" aria-hidden="true"></i> Voltar para documentos</a><div><button type="reset" id="header-discard" class="header-secondary"><i class="bx bx-reset" aria-hidden="true"></i> Descartar alterações</button><button type="submit" class="header-primary"><i class="bx bx-save" aria-hidden="true"></i> Salvar alterações</button></div></div>
    </form>
</main>
<script src="<?= base_url('public/assets/js/documentos-cabecalho.js') . '?v=' . filemtime(FCPATH . 'public/assets/js/documentos-cabecalho.js') ?>"></script>
