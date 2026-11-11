<?php
$nomePerfil = $licenciando['nome_completo'] ?? set_value('nome_completo', '', false);
$drePerfil = $licenciando['dre'] ?? set_value('dre', '', false);
$situacaoPerfil = $setor_data['situacao'] ?? '';
$classeSituacao = ['Ativo' => 'ativo', 'Inativo' => 'inativo', 'Pendente' => 'pendente', 'Concluido' => 'concluido'][$situacaoPerfil] ?? 'unknown';
?>
<div class="licenciando-profile-cover" aria-hidden="true"></div>
<div class="licenciando-profile-body">
    <label class="licenciando-photo-picker">
        <span class="sr-only">Selecionar imagem do licenciando</span>
        <img id="imgLicen" src="<?= base_url('public/assets/img/avatar.png') ?>" alt="Foto do licenciando">
        <input type="file" id="filImage" name="filImage" accept="image/*">
        <span class="licenciando-photo-icon" aria-hidden="true"><i class="bx bx-camera"></i></span>
    </label>
    <h2 class="licenciando-profile-name"><?= esc($nomePerfil ?: 'Novo licenciando') ?></h2>
    <p class="licenciando-profile-dre"><?= esc($drePerfil ?: 'DRE não informado') ?></p>
    <?php if (isset($licenciando)): ?>
    <form class="licenciando-sector-switch" action="<?= base_url('licenciandos/setor_select/' . (int) $licenciando['licenciando_id']) ?>" method="post">
        <label for="licenciando-setor-atual">Vínculo em exibição</label>
        <div class="licenciando-sector-control"><i class="bx bxs-graduation" aria-hidden="true"></i>
        <?php
        $opcoesSetores = [];
        foreach ($licenciandoSetores as $vinculoPerfil) $opcoesSetores[$vinculoPerfil['id']] = $vinculoPerfil['nome'];
        echo form_dropdown('licenciando_setor', $opcoesSetores, $licenciandoSetor_id, 'id="licenciando-setor-atual" onchange="this.form.submit()" class="custom-select"');
        ?>
        </div>
    </form>
    <?php else: ?>
    <p class="licenciando-profile-hint">Preencha os dados e adicione os vínculos com os setores.</p>
    <?php endif ?>
</div>
<?php if (isset($licenciando)): ?>
<dl class="licenciando-profile-stats">
    <div><i class="bx bx-time-five" aria-hidden="true"></i><dt>Horas</dt><dd><?= esc($setor_data['horas_estagio'] ?? '0') ?></dd></div>
    <div><i class="bx bxs-institution" aria-hidden="true"></i><dt>Instituição</dt><dd><?= esc($licenciando['universidade_sigla'] ?? 'Não informado') ?></dd></div>
    <div><i class="bx bx-check-shield" aria-hidden="true"></i><dt>Situação do vínculo</dt><dd><span class="licenciando-status licenciando-status-<?= esc($classeSituacao) ?>"><?= esc(\App\Models\LicenciandoSetorModel::SITUACOES[$situacaoPerfil] ?? 'Não informado') ?></span></dd></div>
</dl>
<?php endif ?>
