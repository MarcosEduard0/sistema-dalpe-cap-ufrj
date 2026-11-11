<?php

// Teste da regra, sem consultas ou alterações no banco.
// Executar: C:\xampp\php\php.exe tests\situacao_vinculo.php
define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require FCPATH . 'system/bootstrap.php';

use App\Models\LicenciandoSetorModel as Vinculos;

$casos = [
    ['2026-11-10', 'Ativo', 'Concluido'],
    ['2026-11-11', 'Inativo', 'Concluido'],
    ['2026-11-12', 'Ativo', 'Ativo'],
    [null, 'Pendente', 'Pendente'],
    ['', null, null],
    ['0000-00-00', 'Ativo', 'Ativo'],
    ['2026-02-30', 'Inativo', 'Inativo'],
    ['2025-02-29', null, null],
    ['2024-02-29', null, 'Concluido'],
    ['texto', 'Pendente', 'Pendente'],
];
foreach ($casos as [$termino, $manual, $esperado]) {
    $obtido = Vinculos::situacaoEfetiva(['data_termino' => $termino, 'situacao' => $manual], '2026-11-11');
    if ($obtido !== $esperado) throw new RuntimeException('Falha: ' . json_encode([$termino, $manual, $obtido, $esperado]));
}

// Uma pessoa, dois setores independentes; início não é exigido pela regra antiga.
$concluido = ['licenciando_id' => 1, 'setor_id' => 1, 'data_termino' => '2026-11-10', 'situacao' => 'Ativo'];
$ativo = ['licenciando_id' => 1, 'setor_id' => 2, 'data_termino' => '2026-11-12', 'situacao' => 'Ativo'];
if (Vinculos::situacaoEfetiva($concluido, '2026-11-11') !== 'Concluido'
    || Vinculos::situacaoEfetiva($ativo, '2026-11-11') !== 'Ativo') throw new RuntimeException('Situações misturadas entre setores');

// A data é avaliada na consulta, sem depender de salvar novamente ou de agendamento.
if (Vinculos::situacaoEfetiva($ativo, '2026-11-12') !== 'Concluido') throw new RuntimeException('Virada da data não refletida');
$calculado = Vinculos::classificar(['situacao' => 'Inativo', 'data_termino' => '2020-01-01']);
if ($calculado !== Vinculos::classificar($calculado)) throw new RuntimeException('Classificação não idempotente');
$calculado['data_termino'] = null;
if (Vinculos::situacaoEfetiva($calculado) !== 'Inativo') throw new RuntimeException('Situação manual não retomada');

echo "OK: datas, estados, setores independentes e retomada da situação manual.\n";
