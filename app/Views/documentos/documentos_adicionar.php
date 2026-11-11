<?php
$editando = isset($documento);
$baseUrl = $editando ? 'editar/' . (int) $documento['documento_id'] : 'adicionar';
$variaveisPreview = config('DocumentoPreview')->variaveis;
$nomeEditor = set_value('nome', $documento['nome'] ?? (isset($modeloOrigem) ? mb_substr('Cópia de ' . $modeloOrigem['nome'], 0, 50) : ''), false);
$conteudoEditor = set_value('conteudo', $documento['conteudo'] ?? ($modeloOrigem['conteudo'] ?? ''), false);
?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/documento-editor.css') . '?v=' . filemtime(FCPATH . 'public/assets/css/documento-editor.css') ?>">
<main class="documento-editor" aria-labelledby="documento-editor-title">
    <div class="doc-editor-heading"><span class="doc-heading-icon"><i class="bx bx-file" aria-hidden="true"></i></span><div><h1 id="documento-editor-title"><?= $editando ? 'Editar documento' : 'Novo documento' ?></h1><nav aria-label="Localização"><a href="<?= base_url('documentos') ?>">Documentos</a><span aria-hidden="true">/</span><span><?= $editando ? 'Editar' : 'Adicionar' ?></span></nav></div></div>
    <section class="doc-card doc-variables" aria-labelledby="doc-variables-title">
        <span class="doc-info-icon"><i class="bx bx-info-circle" aria-hidden="true"></i></span>
        <div><h2 id="doc-variables-title">Variáveis disponíveis</h2><p>Clique para inserir no conteúdo. Na emissão, os marcadores serão substituídos pelos dados do licenciando e do vínculo selecionado.</p>
            <div class="doc-variable-buttons"><?php foreach ($variaveisPreview as $variavel => $exemplo): ?><button type="button" class="doc-variable" data-variable="<?= esc($variavel) ?>" title="<?= esc($variavel === 'PARAGRAFO' ? 'Inserir um novo parágrafo com recuo' : 'Inserir [' . $variavel . '] — exemplo: ' . $exemplo) ?>">[<?= esc($variavel) ?>]</button><?php endforeach ?></div>
            <span id="doc-insert-status" class="sr-only" role="status"></span>
        </div>
    </section>
    <form id="documento-editor-form" class="needs-validation" novalidate action="<?= base_url('documentos/' . $baseUrl) ?>" method="post">
        <?php if (isset($modeloOrigem)): ?><div class="doc-example mb-3"><i class="bx bx-copy" aria-hidden="true"></i><span>Cópia não salva de <strong><?= esc($modeloOrigem['nome']) ?></strong>. Revise o nome e clique em Adicionar para criar um novo modelo.</span></div><?php endif ?>
        <?php if (isset($validation)): ?><div class="alert alert-danger" role="alert"><?= $validation->listErrors() ?></div><?php endif ?>
        <div class="doc-editor-grid">
            <section class="doc-card doc-fields" aria-labelledby="doc-fields-title">
                <h2 id="doc-fields-title"><i class="bx bx-file" aria-hidden="true"></i> Dados do documento</h2>
                <div class="form-group"><label for="doc-nome">Nome <span aria-hidden="true">*</span></label><input id="doc-nome" class="form-control" type="text" name="nome" minlength="3" maxlength="50" required value="<?= esc($nomeEditor) ?>"><div class="invalid-feedback">Informe um nome com 3 a 50 caracteres.</div></div>
                <div class="form-group doc-content-field"><label for="conteudo">Conteúdo <span aria-hidden="true">*</span></label><textarea id="conteudo" class="form-control" name="conteudo" required rows="15" spellcheck="true" aria-describedby="doc-content-help"><?= esc($conteudoEditor) ?></textarea><div class="invalid-feedback">Informe o conteúdo do documento.</div><small id="doc-content-help">Use [PARAGRAFO] para iniciar um parágrafo com recuo.</small></div>
                <div class="doc-example"><i class="bx bx-info-circle" aria-hidden="true"></i><span><strong>Exemplo:</strong> O aluno [NOME], DRE [DRE], matriculado na [UNIVERSIDADE]...</span></div>
            </section>
            <div class="doc-preview-column">
                <section class="doc-card doc-preview-card" aria-labelledby="doc-preview-title">
                    <div class="doc-preview-heading"><h2 id="doc-preview-title"><i class="bx bx-show" aria-hidden="true"></i> Pré-visualização</h2><button type="button" class="doc-expand" data-preview-expand aria-label="Ampliar pré-visualização" title="Ampliar prévia"><i class="bx bx-expand" aria-hidden="true"></i></button></div>
                    <div class="doc-preview-toolbar"><label for="doc-preview-mode">Exibir com</label><select id="doc-preview-mode"><option value="exemplo">Dados fictícios</option><option value="variaveis">Variáveis</option></select><span class="doc-demo-badge">Amostra</span></div>
                    <p class="doc-preview-notice" id="doc-preview-notice">Dados fictícios apenas para conferência. Nenhum cadastro real é utilizado.</p>
                    <div class="doc-paper"><h3 class="js-preview-name"></h3><div id="preview" class="doc-preview-content js-preview-content"></div></div>
                    <p id="doc-preview-warning" class="doc-variable-warning" role="status" hidden></p>
                    <p class="doc-preview-footnote">Prévia do texto. O PDF final usa o cabeçalho e a assinatura configurados.</p>
                    <noscript><p>A pré-visualização precisa de JavaScript. Você pode editar e salvar o modelo normalmente.</p></noscript>
                </section>
                <section class="doc-card doc-writing-tips" aria-labelledby="doc-tips-title"><h2 id="doc-tips-title"><i class="bx bx-bulb" aria-hidden="true"></i> Dicas de escrita</h2><ul><li>Insira os marcadores pelos botões para evitar erros de digitação.</li><li>Confira nomes, datas e quebras de parágrafo na prévia.</li><li>Os dados fictícios não serão salvos no modelo.</li></ul></section>
            </div>
        </div>
        <input type="hidden" name="documento_id" value="<?= $editando ? (int) $documento['documento_id'] : '' ?>">
        <div class="doc-card doc-editor-actions"><a class="doc-cancel" href="<?= base_url('documentos') ?>">Cancelar</a><button type="button" class="doc-preview-button" data-preview-expand><i class="bx bx-show" aria-hidden="true"></i> Ampliar prévia</button><button type="submit" name="submit" class="doc-save"><i class="bx bx-check" aria-hidden="true"></i> <?= $editando ? 'Atualizar' : 'Adicionar' ?></button></div>
    </form>
    <dialog id="doc-preview-dialog" class="doc-preview-dialog" aria-labelledby="doc-dialog-title"><div class="doc-dialog-heading"><h2 id="doc-dialog-title">Pré-visualização do texto</h2><button type="button" id="doc-preview-close" aria-label="Fechar pré-visualização"><i class="bx bx-x" aria-hidden="true"></i></button></div><p class="doc-preview-notice">Amostra para conferência. O PDF final usa o cabeçalho e a assinatura configurados.</p><div class="doc-paper"><h3 class="js-preview-name"></h3><div class="doc-preview-content js-preview-content"></div></div></dialog>
</main>
<script id="doc-preview-data" type="application/json"><?= json_encode($variaveisPreview, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="<?= base_url('public/assets/js/documento-editor.js') . '?v=' . filemtime(FCPATH . 'public/assets/js/documento-editor.js') ?>"></script>
