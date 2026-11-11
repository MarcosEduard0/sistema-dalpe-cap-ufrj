<?php

// Testes do leitor CSV, sem consultas ou alterações no banco.
define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths(); require FCPATH . 'system/bootstrap.php';

use App\Libraries\ImportacaoLicenciandos as Importador;

$reader = new Importador(); $file = tempnam(sys_get_temp_dir(), 'dalpe-csv-');
$headers = Importador::CABECALHOS;
$row = ['ana@example.com', 'Ana de Exemplo', '', '001234', 'UFRJ', 'Professora A, Professor B', 'Matemática, Física', "Rua Exemplo\nCasa 1", 'Centro', '00000-000', 'Rio de Janeiro', '21999990000', ''];
$write = static function ($columns, $rows, $separator = ';', $bom = true) use ($file) {
    $out = fopen($file, 'w'); if ($bom) fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $columns, $separator, '"', '');
    foreach ($rows as $data) fputcsv($out, $data, $separator, '"', ''); fclose($out);
};
$check = static function ($ok, $message) { if (!$ok) throw new RuntimeException($message); };
$reject = static function () use ($reader, $file) {
    try { $reader->ler($file); } catch (InvalidArgumentException $expected) { return; }
    throw new RuntimeException('CSV inválido aceito');
};
try {
    foreach ([';', ','] as $separator) {
        $write($headers, [$row, $row], $separator);
        $data = $reader->ler($file);
        $check(count($data) === 2 && $data[0]['dados']['dre'] === '001234', 'Separador ou zeros perdidos');
        $check($data[0]['dados']['setor'] === 'Matemática, Física' && $data[0]['dados']['endereco'] === $row[7], 'Campo com vírgula ou quebra de linha corrompido');
        $check($data[0]['linha'] === 2 && $data[1]['linha'] === 4, 'Numeração física das linhas incorreta');
    }
    $oldHeaders = ['Email', 'Nome', 'Nome Social', 'DRE', 'Instituição(sigla)', 'Prof.ª Prática', 'Setor Curricular', 'Endereço', 'Bairro', 'CEP', 'Cidade', 'Telefone 1', 'Telefone 2'];
    $write($oldHeaders, [$row], ',', false); $check(count($reader->ler($file)) === 1, 'Cabeçalho anterior não reconhecido');
    $long = $row; $long[5] = str_repeat('A', 1500); $write($headers, [$long]);
    $check(strlen($reader->ler($file)[0]['dados']['professor']) === 1500, 'Linha truncada pelo leitor');
    $write($headers, [array_slice($row, 0, 12)]); $reject();
    $write($headers, [array_merge($row, ['extra'])]); $reject();
    $badHeader = $headers; [$badHeader[0], $badHeader[1]] = [$badHeader[1], $badHeader[0]]; $write($badHeader, [$row]); $reject();
    $write($headers, []); $reject();
    file_put_contents($file, "\xFF\xFEtexto"); $reject();
    file_put_contents($file, implode(';', $headers) . "\n" . '"aspas abertas'); $reject();
    $write($headers, array_fill(0, Importador::MAX_REGISTROS + 1, $row)); $reject();
    $out = fopen($file, 'w'); ftruncate($out, Importador::MAX_BYTES + 1); fclose($out); $reject();
    echo "OK: delimitadores, BOM, UTF-8, cabeçalhos, aspas, campos longos, linhas e limites.\n";
} finally { unlink($file); }
