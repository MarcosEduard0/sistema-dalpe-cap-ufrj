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
    foreach (['universidades', 'setores', 'enderecos', 'licenciandos', 'licenciandosetor', 'documentos'] as $table) {
        $db->query('CREATE TABLE `' . $scratch . '`.`' . $table . '` LIKE `' . str_replace('`', '``', $original) . '`.`' . $table . '`');
    }
    $db->query('USE `' . $scratch . '`');
    $db->table('universidades')->insert(['universidade_id' => 1, 'sigla' => 'UFTEST', 'nome' => 'Universidade de Teste']);
    foreach ([1 => 'Matemática', 2 => 'Física', 3 => 'Química'] as $id => $nome) $db->table('setores')->insert(['setor_id' => $id, 'nome' => $nome, 'descricao' => 'Fixture']);
    $db->query('ALTER TABLE licenciandos DROP COLUMN created_at, DROP COLUMN updated_at, DROP COLUMN deleted_at');
    $sql = preg_replace('/^--.*$/m', '', file_get_contents(FCPATH . 'docs/sql/2026-11-11_auditoria_licenciandos.sql'));
    for ($round=0; $round<2; $round++) foreach (explode(';', $sql) as $statement) if (trim($statement) !== '') $db->query($statement);
    $model = new App\Models\LicenciandosModel($db);
    $id = $model->insert(['dre'=>'AUDIT01','nome_completo'=>'Pessoa de Teste','email'=>'teste@example.com','universidade_id'=>1]);
    $pessoa = $model->find($id);
    $check($id && $pessoa['created_at'] && $pessoa['updated_at'] && $pessoa['deleted_at'] === null, 'Datas de criacao');
    $db->table('licenciandos')->where('licenciando_id',$id)->update(['updated_at'=>'2020-01-01 00:00:00']);
    $model->update($id,['nome_completo'=>'Pessoa Atualizada']);
    $atual = $model->find($id);
    $check($atual['created_at']===$pessoa['created_at'] && $atual['updated_at']!=='2020-01-01 00:00:00', 'Atualizacao preserva criacao');
    $link = new App\Models\LicenciandoSetorModel($db);
    $link->insert(['licenciando_id'=>$id,'setor_id'=>1,'periodo'=>'2026','data_cadastro'=>'2026-11-11','horas_estagio'=>0]);
    $dashboard = new App\Models\DashboardModel($db);
    $check($dashboard->getResumo()['novos30Dias']===1, 'Novo no dashboard');
    $check(count((new App\Models\UniversidadesModel($db))->getUniversidadesComTotais())===1, 'Universidades consultaveis');
    $check($model->delete($id), 'Exclusao logica');
    $check($model->find($id)===null && $model->getLicenciandosAndUniversidadeAndEndereco($id)===null, 'Excluido oculto');
    $raw=$db->table('licenciandos')->where('licenciando_id',$id)->get()->getRowArray();
    $check(!empty($raw['deleted_at']) && $raw['created_at']===$pessoa['created_at'], 'Registro preservado');
    $check($db->table('licenciandosetor')->countAllResults()===1 && count((new App\Models\LicenciandoSetorModel($db))->getLicenciandosSetores())===0, 'Vinculo preservado e oculto');
    $resumo=(new App\Models\DashboardModel($db))->getResumo();
    $check((int)$resumo['totais']['total']===0 && $resumo['novos30Dias']===0 && $resumo['quantVinculos']===0, 'Dashboard oculta excluidos');
    $uni=(new App\Models\UniversidadesModel($db))->getUniversidadesComTotais();
    $check((int)$uni[0]['total_licenciandos']===0, 'Universidade nao conta excluidos');
    $plan=(new Importador($db))->planejar([$linha('AUDIT01')], '2026');
    $check($plan['resumo']['erros']===1, 'Importacao bloqueia DRE excluido');
    echo "OK: SQL idempotente, created/updated, soft delete, vinculos preservados, dashboard, documentos, universidades e DRE excluido; banco isolado.\n";
} finally {
    $db->query('USE `' . str_replace('`', '``', $original) . '`');
    if ($criado) $db->query('DROP DATABASE `' . $scratch . '`');
}
