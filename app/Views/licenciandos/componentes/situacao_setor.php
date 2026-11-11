<?php
$campoSituacao = $novoVinculo ? 'situacao[]' : 'situacao';
$manual = $novoVinculo ? 'Pendente' : ($setor_data['situacao_manual'] ?? '');
$postSituacao = service('request')->getPost('situacao');
if (!$novoVinculo && is_string($postSituacao)) $manual = $postSituacao;
$dadosSituacao = ['situacao' => $manual, 'data_termino' => $novoVinculo ? null : ($setor_data['data_termino'] ?? null)];
if (!$novoVinculo && is_string(service('request')->getPost('data_termino'))) $dadosSituacao['data_termino'] = service('request')->getPost('data_termino');
$concluido = \App\Models\LicenciandoSetorModel::situacaoEfetiva($dadosSituacao) === 'Concluido';
?>
<div class="col-md-12">
    <div class="form-group js-situacao-setor" data-hoje="<?= esc(\App\Models\LicenciandoSetorModel::hoje()) ?>">
        <label class="d-block">Situação do vínculo com este setor
            <select class="custom-select js-situacao-select" name="<?= esc($campoSituacao) ?>" <?= $concluido ? 'disabled' : '' ?>>
                <?php if (!$novoVinculo): ?><option value="" <?= $manual === '' ? 'selected' : '' ?>>Não informado</option><?php endif ?>
                <?php foreach (\App\Models\LicenciandoSetorModel::SITUACOES as $valor => $rotulo): ?>
                <option value="<?= esc($valor) ?>" <?= $valor === 'Concluido' ? 'disabled' : '' ?> <?= ($concluido ? $valor === 'Concluido' : $manual === $valor) ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <input type="hidden" class="js-situacao-manual" name="<?= esc($campoSituacao) ?>" value="<?= esc($manual) ?>" <?= $concluido ? '' : 'disabled' ?>>
        <small class="form-text text-muted">Concluído é automático quando a data de conclusão é válida e já foi atingida. Os demais estados são definidos neste vínculo.</small>
        <small class="form-text text-info js-situacao-aviso" <?= $concluido ? '' : 'hidden' ?>>Conclusão automática. Ao remover ou adiar a data, a situação manual anterior será retomada.</small>
    </div>
</div>
