<?php

// Integração local. Cria um banco temporário com a estrutura atual e o remove.
// Requer permissão local para CREATE DATABASE e CREATE TRIGGER.
define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
chdir(FCPATH); require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths(); require FCPATH . 'system/bootstrap.php';

use App\Libraries\ImportacaoLicenciandos as Importador;

$db = Config\Database::connect();
if (!is_cli() || !in_array($db->hostname, ['localhost', '127.0.0.1'], true)) throw new RuntimeException('Execute somente no ambiente local.');
$original = $db->query('SELECT DATABASE() nome')->getRowArray()['nome'];
$scratch = 'dalpe_teste_importacao_' . bin2hex(random_bytes(6)); $criado = false;
$check = static function ($ok, $message) { if (!$ok) throw new RuntimeException($message); };
$linha = static function ($dre, $setores = 'Matemática', $professor = 'Professor A') {
    return ['linha' => 2, 'dados' => array_combine(Importador::CABECALHOS, ['teste@example.com', 'Pessoa de Teste', '', $dre, 'UFTEST', $professor, $setores, 'Rua de Teste', 'Centro', '00000-000', 'Rio de Janeiro', '', ''])];
};
$snapshot = static function () use ($db) {
    $hashes = [];
    foreach (['licenciandos' => 'licenciando_id', 'licenciandosetor' => 'id', 'enderecos' => 'endereco_id'] as $table => $id) {
        $hashes[$table] = hash('sha256', serialize($db->query('SELECT * FROM ' . $table . ' ORDER BY ' . $id)->getResultArray()));
    }
    return $hashes;
};
try {
    $db->query('CREATE DATABASE `' . $scratch . '` CHARACTER SET utf8mb4'); $criado = true;
    foreach (['universidades', 'setores', 'enderecos', 'licenciandos', 'licenciandosetor'] as $table) {
        $db->query('CREATE TABLE `' . $scratch . '`.`' . $table . '` LIKE `' . str_replace('`', '``', $original) . '`.`' . $table . '`');
    }
    $db->query('USE `' . $scratch . '`');
    $db->table('universidades')->insert(['universidade_id' => 1, 'sigla' => 'UFTEST', 'nome' => 'Universidade de Teste']);
    foreach ([1 => 'Matemática', 2 => 'Física', 3 => 'Química'] as $id => $nome) $db->table('setores')->insert(['setor_id' => $id, 'nome' => $nome, 'descricao' => 'Fixture']);
    $service = new Importador($db); $ano = substr(App\Models\LicenciandoSetorModel::hoje(), 0, 4);
    $rows = [$linha('00123', 'Matemática, Física', 'Professor A, Professor B'), $linha('00123'), $linha('00456', 'Física')];
    $plan = $service->planejar($rows, $ano);
    $check($plan['resumo']['novos_licenciandos'] === 2 && $plan['resumo']['novos_vinculos'] === 3 && $plan['resumo']['ignorados'] === 1, 'Planejamento incorreto');
    $check($db->table('licenciandos')->countAllResults() === 0, 'Conferência gravou dados');
    $service->executar($rows, $ano);
    $check($db->table('licenciandos')->countAllResults() === 2 && $db->table('licenciandosetor')->countAllResults() === 3, 'Gravação incorreta');
    $check($db->table('licenciandos')->where('dre', '00123')->get()->getRowArray()['dre'] === '00123', 'DRE perdeu zeros');
    $check($db->table('licenciandosetor')->where('situacao', 'Pendente')->countAllResults() === 3, 'Situação inicial incorreta');
    $before = $snapshot(); $repeat = $service->executar($rows, $ano);
    $check($repeat['resumo']['novos_vinculos'] === 0 && $snapshot() === $before, 'Repetição duplicou registros');
    $extra = $linha('00123', 'Química'); $extra['dados']['nome_completo'] = 'Nome que não deve substituir';
    $service->executar([$extra], $ano);
    $check($db->table('licenciandos')->where('dre', '00123')->get()->getRowArray()['nome_completo'] === 'Pessoa de Teste', 'Dados pessoais existentes alterados');
    $check($db->table('enderecos')->countAllResults() === 2 && $db->table('licenciandosetor')->countAllResults() === 4, 'Novo vínculo criou endereço ou pessoa extra');
    $bad = $linha('00999'); $bad['dados']['email'] = 'invalido';
    $before = $snapshot();
    try { $service->executar([$bad], $ano); throw new RuntimeException('Registro inválido aceito'); } catch (InvalidArgumentException $expected) {}
    $check($snapshot() === $before, 'Validação alterou dados');
    $db->query("CREATE TRIGGER falha_importacao BEFORE INSERT ON licenciandosetor FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Falha simulada'");
    $falhou = false;
    try { $service->executar([$linha('00999')], $ano); } catch (Throwable $expected) { $falhou = true; }
    $check($falhou && $snapshot() === $before, 'Falha de gravação deixou lote parcial');
    echo "OK: conferência, novos vínculos, repetição, dados existentes, validação e rollback em banco isolado.\n";
} finally {
    $db->query('USE `' . str_replace('`', '``', $original) . '`');
    if ($criado) $db->query('DROP DATABASE `' . $scratch . '`');
}
