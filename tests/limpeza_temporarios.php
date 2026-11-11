<?php
// Executar: php tests/limpeza_temporarios.php. Somente pasta isolada no TEMP.
require dirname(__DIR__) . '/app/Libraries/LimpezaTemporarios.php';
use App\Libraries\LimpezaTemporarios;
$pasta = sys_get_temp_dir() . '/dalpe-limpeza-' . bin2hex(random_bytes(8));
mkdir($pasta);
$check = static function ($ok, $msg) { if (!$ok) throw new RuntimeException($msg); };
$criar = static function ($nome, $idade) use ($pasta) { $path = $pasta . '/' . $nome; file_put_contents($path, 'fixture'); touch($path, time() - $idade); return $path; };
$aberto = null;
try {
    $velho = $criar('ci_session' . str_repeat('a', 32), 7300);
    $valido = $criar('ci_session' . str_repeat('b', 32), 1200);
    $uso = $criar('ci_session' . str_repeat('c', 32), 7300);
    $outro = $criar('nao-apagar.txt', 9000);
    $json = $criar(str_repeat('d', 64) . '.json', 1900);
    $novo = $criar(str_repeat('e', 64) . '.json', 60);
    $aberto = fopen($uso, 'r+'); flock($aberto, LOCK_EX);
    $pastas = [[$pasta, '/^ci_session[a-z0-9]{32}$/D', 7200], [$pasta, '/^[a-f0-9]{64}\.json$/D', 1800]];
    $r = LimpezaTemporarios::executar($pasta . '/controle', $pastas);
    $check($r['executou'] && $r['removidos'] === 2 && !$r['falhas'], 'Remove somente vencidos');
    $check(!file_exists($velho) && !file_exists($json), 'Vencidos removidos');
    $check(file_exists($valido) && file_exists($uso) && file_exists($outro) && file_exists($novo), 'Preserva validos, bloqueados e desconhecidos');
    $r = LimpezaTemporarios::executar($pasta . '/controle', $pastas);
    $check(!$r['executou'], 'Intervalo evita varreduras repetidas');
    fclose($aberto); $aberto = null;
    $r = LimpezaTemporarios::executar($pasta . '/controle', $pastas, 0);
    $check($r['removidos'] === 1 && !file_exists($uso), 'Remove vencido depois de liberado');
    $controle = fopen($pasta . '/controle', 'r+'); flock($controle, LOCK_EX);
    try { $check(!LimpezaTemporarios::executar($pasta . '/controle', $pastas, 0)['executou'], 'Nao executa em paralelo'); }
    finally { fclose($controle); }
    echo "OK: expiracao, arquivos em uso, nomes permitidos, intervalo e concorrencia; pasta isolada.\n";
} finally {
    if (is_resource($aberto)) fclose($aberto);
    foreach (new DirectoryIterator($pasta) as $item) if ($item->isFile() || $item->isLink()) unlink($item->getPathname());
    rmdir($pasta);
}
