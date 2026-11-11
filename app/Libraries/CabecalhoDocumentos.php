<?php

namespace App\Libraries;

class CabecalhoDocumentos
{
    public const MAX_BYTES = 2097152;
    private $diretorio;

    public function __construct(?string $diretorio = null)
    {
        $this->diretorio = rtrim($diretorio ?? FCPATH . 'public/assets/img/documentos/cabecalho', '/\\') . DIRECTORY_SEPARATOR;
    }

    public function salvar(array $uploads, array $remover): void
    {
        // Valide todo o lote antes de alterar qualquer imagem existente.
        $validos = [];
        foreach (range(1, 3) as $slot) {
            $file = $uploads[$slot] ?? null;
            if (!empty($remover[$slot]) || !$file || $file->getError() === UPLOAD_ERR_NO_FILE) continue;
            $ext = strtolower($file->getClientExtension());
            $mime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'][$ext] ?? null;
            if (!$file->isValid() || $file->hasMoved() || !$mime || $file->getMimeType() !== $mime
                || $file->getSize() > self::MAX_BYTES || @getimagesize($file->getTempName()) === false) {
                throw new \InvalidArgumentException('Imagem ' . $slot . ': envie um arquivo JPG, JPEG ou PNG válido de até 2 MB.');
            }
            $validos[$slot] = [$file, $ext];
        }
        if (!is_dir($this->diretorio) && !mkdir($this->diretorio, 0775, true) && !is_dir($this->diretorio)) {
            throw new \RuntimeException('Não foi possível acessar a pasta do cabeçalho.');
        }
        $stage = $this->diretorio . '.alteracao-' . bin2hex(random_bytes(8)) . DIRECTORY_SEPARATOR;
        if (!mkdir($stage, 0700)) throw new \RuntimeException('Não foi possível preparar as imagens.');
        $backups = [];
        $instalados = [];
        try {
            foreach ($validos as $slot => [$file, $ext]) {
                $file->move($stage, 'novo' . $slot . '.' . $ext);
            }
            foreach (range(1, 3) as $slot) {
                if (!isset($validos[$slot]) && empty($remover[$slot])) continue;
                foreach (['jpg', 'jpeg', 'png'] as $ext) {
                    $original = $this->diretorio . 'img' . $slot . '.' . $ext;
                    if (!is_file($original)) continue;
                    $backup = $stage . 'original' . $slot . '.' . $ext;
                    if (!rename($original, $backup)) throw new \RuntimeException('Não foi possível preservar a imagem anterior.');
                    $backups[$original] = $backup;
                }
            }
            foreach ($validos as $slot => [$file, $ext]) {
                $destino = $this->diretorio . 'img' . $slot . '.' . $ext;
                if (!rename($stage . 'novo' . $slot . '.' . $ext, $destino)) throw new \RuntimeException('Não foi possível salvar a imagem.');
                $instalados[] = $destino;
            }
        } catch (\Throwable $erro) {
            foreach ($instalados as $destino) unlink($destino);
            foreach ($backups as $original => $backup) {
                if (!rename($backup, $original)) {
                    // Preserve o backup para recuperação se o sistema de arquivos falhar.
                    throw new \RuntimeException('Falha ao restaurar as imagens. Cópias preservadas em ' . $stage, 0, $erro);
                }
            }
            throw $erro;
        } finally {
            // Nunca apague um backup cuja restauração tenha falhado.
            $pendentes = array_filter($backups, 'is_file');
            if (empty($pendentes) || !isset($erro)) {
                foreach (glob($stage . '*') ?: [] as $arquivo) unlink($arquivo);
                rmdir($stage);
            }
        }
    }
}
