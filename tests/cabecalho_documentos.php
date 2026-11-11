<?php

// Testes de arquivos em pasta temporária isolada; não altera o cabeçalho real.
define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require FCPATH . 'system/bootstrap.php';

use App\Libraries\CabecalhoDocumentos;

class UploadCabecalhoFixture
{
    private $source;
    private $ext;
    private $size;
    private $fail;
    public function __construct($source, $ext = 'png', $size = null, $fail = false)
    {
        $this->source = $source; $this->ext = $ext; $this->size = $size; $this->fail = $fail;
    }
    public function getError() { return UPLOAD_ERR_OK; }
    public function getClientExtension() { return $this->ext; }
    public function isValid() { return true; }
    public function hasMoved() { return false; }
    public function getMimeType() { return 'image/png'; }
    public function getSize() { return $this->size ?? filesize($this->source); }
    public function getTempName() { return $this->source; }
    public function move($dir, $name)
    {
        if ($this->fail) throw new RuntimeException('Falha de movimentação simulada');
        if (!copy($this->source, $dir . $name)) throw new RuntimeException('Falha na fixture');
    }
}

$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dalpe-cabecalho-' . bin2hex(random_bytes(8));
mkdir($root);
$fixture = $root . DIRECTORY_SEPARATOR . 'fixture.png';
// PNG mínimo conhecido, exclusivo do teste.
file_put_contents($fixture, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
$target = $root . DIRECTORY_SEPARATOR . 'cabecalho'; mkdir($target);
foreach (['img1.png', 'img1.jpg', 'img2.jpg', 'img3.png'] as $name) file_put_contents($target . '/' . $name, 'original-' . $name);
$check = static function ($ok, $message) { if (!$ok) throw new RuntimeException($message); };
$snapshot = static function () use ($target) {
    $hashes = [];
    foreach (glob($target . '/*') ?: [] as $path) if (is_file($path)) $hashes[basename($path)] = hash_file('sha256', $path);
    ksort($hashes); return $hashes;
};
$service = new CabecalhoDocumentos($target);
$original = $snapshot();
try {
    $service->salvar([], []);
    $check($snapshot() === $original, 'Envio vazio alterou arquivos');
    foreach ([new UploadCabecalhoFixture($fixture, 'pdf'), new UploadCabecalhoFixture($fixture, 'png', 2097153)] as $invalid) {
        try { $service->salvar([1 => new UploadCabecalhoFixture($fixture), 2 => $invalid], [3 => true]); throw new RuntimeException('Arquivo inválido aceito'); }
        catch (InvalidArgumentException $expected) {}
        $check($snapshot() === $original, 'Lote inválido alterou imagens');
    }
    try { $service->salvar([1 => new UploadCabecalhoFixture($fixture), 2 => new UploadCabecalhoFixture($fixture, 'png', null, true)], [3 => true]); }
    catch (RuntimeException $expected) {}
    $check($snapshot() === $original, 'Falha no envio alterou imagens');

    // Força falha de instalação depois de preservar os originais e instalar a posição 1.
    mkdir($target . '/img2.png');
    try { $service->salvar([1 => new UploadCabecalhoFixture($fixture), 2 => new UploadCabecalhoFixture($fixture)], []); }
    catch (Throwable $expected) {}
    rmdir($target . '/img2.png');
    $check($snapshot() === $original, 'Rollback não restaurou os originais');
    $check(!(glob($target . '/.alteracao-*') ?: []), 'Arquivos temporários não limpos');

    $service->salvar([1 => new UploadCabecalhoFixture($fixture)], [3 => true]);
    $check(hash_file('sha256', $target . '/img1.png') === hash_file('sha256', $fixture), 'Substituição falhou');
    $check(!file_exists($target . '/img1.jpg'), 'Extensão antiga não removida');
    $check(!file_exists($target . '/img3.png'), 'Remoção não aplicada');
    $check(hash_file('sha256', $target . '/img2.jpg') === $original['img2.jpg'], 'Outra posição alterada');
    echo "OK: validação do lote, limite, substituição, remoção e rollback em pasta isolada.\n";
} finally {
    foreach (glob($target . '/*') ?: [] as $path) if (is_file($path)) unlink($path);
    rmdir($target); unlink($fixture); rmdir($root);
}
