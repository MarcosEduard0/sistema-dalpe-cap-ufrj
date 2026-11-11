<?php
$editando = isset($universidade);
$acao = $editando ? 'editar/' . (int) $universidade['universidade_id'] : 'adicionar';
$tituloForm = $editando ? 'Editar universidade' : 'Adicionar universidade';
$erros = isset($validation) ? $validation->getErrors() : [];
$valores = [];
foreach (['sigla', 'nome'] as $campo) {
    $valores[$campo] = isset($validation) ? (string) service('request')->getPost($campo) : (string) ($universidade[$campo] ?? '');
}
?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/universidades-lista.css') . '?v=' . filemtime(FCPATH . 'public/assets/css/universidades-lista.css') ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/universidade-form.css') . '?v=' . filemtime(FCPATH . 'public/assets/css/universidade-form.css') ?>">
<main class="universidades-page uni-form-page" aria-labelledby="uni-form-title">
    <nav class="uni-breadcrumb" aria-label="Caminho da página"><a href="<?= base_url('universidades') ?>">Universidades</a><i class="bx bx-chevron-right" aria-hidden="true"></i><span aria-current="page"><?= esc($tituloForm) ?></span></nav>
    <div class="uni-card uni-form-shell">
        <section class="uni-banner"><span class="uni-banner-icon"><i class="bx bxs-institution" aria-hidden="true"></i></span><div><h1 id="uni-form-title"><?= esc($tituloForm) ?></h1><p><?= $editando ? 'Atualize os dados da universidade cadastrada.' : 'Cadastre uma nova universidade parceira do programa.' ?></p></div></section>
        <form action="<?= base_url('universidades/' . $acao) ?>" method="post">
            <?= csrf_field() ?>
            <?php if ($editando): ?><input type="hidden" name="universidade_id" value="<?= (int) $universidade['universidade_id'] ?>"><?php endif ?>
            <?php if ($erros): ?><div class="uni-form-errors" role="alert"><strong>Confira os campos antes de salvar.</strong><ul><?php foreach ($erros as $erro): ?><li><?= esc($erro) ?></li><?php endforeach ?></ul></div><?php endif ?>
            <section class="uni-form-section" aria-labelledby="uni-data-title">
                <h2 id="uni-data-title"><i class="bx bxs-institution" aria-hidden="true"></i> Dados da universidade</h2>
                <div class="uni-form-fields">
                    <div><label for="sigla">Sigla <span aria-hidden="true">*</span></label><div class="uni-field"><i class="bx bx-font" aria-hidden="true"></i><input id="sigla" name="sigla" type="text" value="<?= esc($valores['sigla']) ?>" placeholder="Ex.: UFRJ" minlength="3" maxlength="20" required <?= isset($erros['sigla']) ? 'aria-invalid="true" aria-describedby="sigla-error"' : '' ?>></div><?php if (isset($erros['sigla'])): ?><p class="uni-field-error" id="sigla-error"><?= esc($erros['sigla']) ?></p><?php endif ?></div>
                    <div><label for="nome">Nome <span aria-hidden="true">*</span></label><div class="uni-field"><i class="bx bxs-institution" aria-hidden="true"></i><input id="nome" name="nome" type="text" value="<?= esc($valores['nome']) ?>" placeholder="Ex.: Universidade Federal do Rio de Janeiro" minlength="3" maxlength="50" required <?= isset($erros['nome']) ? 'aria-invalid="true" aria-describedby="nome-error"' : '' ?>></div><?php if (isset($erros['nome'])): ?><p class="uni-field-error" id="nome-error"><?= esc($erros['nome']) ?></p><?php endif ?></div>
                </div>
            </section>
            <div class="uni-form-actions"><a class="uni-form-cancel" href="<?= base_url('universidades') ?>">Cancelar</a><button class="uni-form-submit" type="submit"><i class="bx <?= $editando ? 'bx-save' : 'bx-plus' ?>" aria-hidden="true"></i><?= $editando ? 'Salvar alterações' : 'Adicionar universidade' ?></button></div>
        </form>
    </div>
</main>
