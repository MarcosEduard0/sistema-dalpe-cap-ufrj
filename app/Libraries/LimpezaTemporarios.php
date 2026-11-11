<?php

namespace App\Libraries;

class LimpezaTemporarios
{
    /** Executada pelo tráfego da aplicação; não inicia sessões. */
    public static function automatica(): void
    {
        $app = config('App');
        $pastas = [];
        if ($app->sessionDriver === 'CodeIgniter\\Session\\Handlers\\FileHandler' && $app->sessionExpiration > 0) {
            $pastas[] = [$app->sessionSavePath, '/^' . preg_quote($app->sessionCookieName, '/') . '[a-zA-Z0-9,-]{16,256}$/D', (int) $app->sessionExpiration];
        }
        $pastas[] = [WRITEPATH . 'importacoes_licenciandos', '/^[a-f0-9]{64}\.json$/D', 1800];
        $pastas[] = [WRITEPATH . 'debugbar', '/^debugbar_[0-9]+\.json$/D', 86400];
        $resultado = self::executar(WRITEPATH . '.limpeza-temporarios.lock', $pastas);
        if ($resultado['falhas']) log_message('error', 'Limpeza de temporários: {total} falhas de acesso ou remoção. Verifique as permissões de writable.', ['total' => $resultado['falhas']]);
    }

    /** Somente arquivos reconhecidos, vencidos e sem bloqueio são removidos. */
    public static function executar(string $controle, array $pastas, int $intervalo = 300): array
    {
        $resultado = ['executou' => false, 'removidos' => 0, 'falhas' => 0];
        $lock = @fopen($controle, 'c+');
        if (!$lock) { $resultado['falhas']++; return $resultado; }
        try {
            if (!flock($lock, LOCK_EX | LOCK_NB)) return $resultado;
            rewind($lock);
            $ultima = (int) stream_get_contents($lock);
            $agora = time();
            if ($ultima > 0 && $agora >= $ultima && $agora - $ultima < $intervalo) return $resultado;
            $resultado['executou'] = true;
            foreach ($pastas as [$pasta, $padrao, $validade]) {
                if ($validade <= 0 || !is_dir($pasta) || is_link($pasta)) continue;
                $diretorio = @opendir($pasta);
                if (!$diretorio) { $resultado['falhas']++; continue; }
                try {
                    while (false !== ($nome = readdir($diretorio))) {
                        if (!preg_match($padrao, $nome)) continue;
                        $arquivo = rtrim($pasta, '/\\') . DIRECTORY_SEPARATOR . $nome;
                        clearstatcache(true, $arquivo);
                        if (is_link($arquivo) || !is_file($arquivo)) continue;
                        $modificado = @filemtime($arquivo);
                        if ($modificado === false || $modificado >= $agora - $validade) continue;
                        $handle = @fopen($arquivo, 'r+');
                        if (!$handle) { if (file_exists($arquivo)) $resultado['falhas']++; continue; }
                        try {
                            if (!flock($handle, LOCK_EX | LOCK_NB)) continue;
                            $stat = fstat($handle);
                            if ($stat && $stat['mtime'] < $agora - $validade) {
                                if (@unlink($arquivo)) $resultado['removidos']++;
                                elseif (file_exists($arquivo)) $resultado['falhas']++;
                            }
                        } finally { fclose($handle); }
                    }
                } finally { closedir($diretorio); }
            }
            rewind($lock); ftruncate($lock, 0); fwrite($lock, (string) $agora); fflush($lock);
        } finally { fclose($lock); }
        return $resultado;
    }
}
