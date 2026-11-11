<?php

namespace App\Libraries;

use App\Models\EnderecosModel;
use App\Models\LicenciandosModel;
use App\Models\LicenciandoSetorModel;

class ImportacaoLicenciandos
{
    public const MAX_BYTES = 10485760;
    public const MAX_REGISTROS = 5000;
    public const CABECALHOS = ['email', 'nome_completo', 'nome_social', 'dre', 'universidade', 'professor', 'setor', 'endereco', 'bairro', 'cep', 'cidade', 'telefone1', 'telefone2'];
    public const EXEMPLO_NOME = 'João Eduardo de Souza';
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }
    private function banco()
    {
        return $this->db ?? ($this->db = \Config\Database::connect());
    }
    private static function chave(string $texto): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', trim($texto));
        return preg_replace('/[^a-z0-9]/', '', strtolower($ascii === false ? $texto : $ascii));
    }
    private static function chaveDre(string $dre): string
    {
        return 'dre:' . mb_strtoupper(trim($dre));
    }

    public function ler(string $arquivo): array
    {
        if (!is_file($arquivo) || filesize($arquivo) > self::MAX_BYTES) throw new \InvalidArgumentException('Envie um CSV de até 10 MB.');
        $texto = file_get_contents($arquivo);
        if ($texto === false || !mb_check_encoding($texto, 'UTF-8')) throw new \InvalidArgumentException('Salve o arquivo como CSV UTF-8 e envie novamente.');
        $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto);
        $texto = str_replace(["\r\n", "\r"], "\n", $texto);
        if (strpos($texto, "\0") !== false) throw new \InvalidArgumentException('O arquivo contém caracteres inválidos. Salve como CSV UTF-8.');
        $aliases = [
            ['email'],
            ['nomecompleto', 'nome'],
            ['nomesocial'],
            ['dre'],
            ['universidade', 'instituicao', 'instituicaosigla', 'siglauniversidade'],
            ['professor', 'professora', 'profpratica', 'profapratica', 'professorapratica'],
            ['setor', 'setorcurricular'],
            ['endereco'],
            ['bairro'],
            ['cep'],
            ['cidade'],
            ['telefone1'],
            ['telefone2'],
        ];
        $primeira = strtok($texto, "\n");
        $separador = null;
        foreach ([';', ','] as $candidato) {
            $colunas = str_getcsv($primeira ?: '', $candidato, '"', '');
            if (count($colunas) !== 13) continue;
            $valido = true;
            foreach ($colunas as $i => $coluna) if (!in_array(self::chave($coluna), $aliases[$i], true)) $valido = false;
            if ($valido) {
                $separador = $candidato;
                break;
            }
        }
        if (!$separador) throw new \InvalidArgumentException('Cabeçalho inválido. Use as 13 colunas do modelo, na mesma ordem, separadas por ponto e vírgula ou vírgula.');
        $aspasAbertas = false;
        for ($i = 0, $tamanho = strlen($texto); $i < $tamanho; $i++) {
            if ($texto[$i] !== '"') continue;
            if ($aspasAbertas && $i + 1 < $tamanho && $texto[$i + 1] === '"') {
                $i++;
                continue;
            }
            $aspasAbertas = !$aspasAbertas;
        }
        if ($aspasAbertas) throw new \InvalidArgumentException('Há um campo com aspas sem fechamento no CSV. Revise o arquivo.');
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $texto);
        rewind($stream);
        fgetcsv($stream, 0, $separador, '"', '');
        $linha = substr_count(substr($texto, 0, ftell($stream)), "\n") + 1;
        $registros = [];
        try {
            while (!feof($stream)) {
                $inicio = ftell($stream);
                $numero = $linha;
                $campos = fgetcsv($stream, 0, $separador, '"', '');
                $linha += substr_count(substr($texto, $inicio, ftell($stream) - $inicio), "\n");
                if ($campos === false) break;
                if (count($campos) === 1 && trim((string) $campos[0]) === '') continue;
                if (count($campos) !== 13) throw new \InvalidArgumentException('Linha ' . $numero . ': esperadas 13 colunas, encontradas ' . count($campos) . '.');
                foreach ($campos as $campo) if (strlen($campo) > 10000) throw new \InvalidArgumentException('Linha ' . $numero . ': um campo excede 10.000 bytes. Revise o conteúdo do CSV.');
                $registros[] = ['linha' => $numero, 'dados' => array_combine(self::CABECALHOS, array_map('trim', $campos))];
                if (count($registros) > self::MAX_REGISTROS) throw new \InvalidArgumentException('Divida o arquivo em lotes de até 5.000 registros.');
            }
        } finally {
            fclose($stream);
        }
        if (!$registros) throw new \InvalidArgumentException('O CSV contém apenas o cabeçalho ou está vazio.');
        return $registros;
    }

    private function catalogo(string $tabela, string $campo): array
    {
        $indice = [];
        foreach ($this->banco()->table($tabela)->get()->getResultArray() as $item) $indice[self::chave($item[$campo])][] = $item;
        return $indice;
    }

    public function planejar(array $registros, string $periodo): array
    {
        $ano = (int) substr(LicenciandoSetorModel::hoje(), 0, 4);
        if (!preg_match('/^\d{4}$/', $periodo) || (int) $periodo < 2018 || (int) $periodo > $ano) throw new \InvalidArgumentException('Selecione um ano de referência válido.');
        if (!$registros || count($registros) > self::MAX_REGISTROS) throw new \InvalidArgumentException('Envie entre 1 e 5.000 registros.');
        $universidades = $this->catalogo('universidades', 'sigla');
        $setores = $this->catalogo('setores', 'nome');
        $existentes = [];
        $vinculados = [];
        foreach (array_chunk(array_unique(array_column(array_column($registros, 'dados'), 'dre')), 500) as $dres) {
            foreach ($this->banco()->table('licenciandos')->whereIn('dre', $dres)->get()->getResultArray() as $pessoa) $existentes[self::chaveDre($pessoa['dre'])][] = $pessoa;
        }
        $ids = [];
        foreach ($existentes as $pessoas) foreach ($pessoas as $pessoa) $ids[] = $pessoa['licenciando_id'];
        foreach (array_chunk($ids, 500) as $lote) {
            foreach ($this->banco()->table('licenciandosetor')->whereIn('licenciando_id', $lote)->get()->getResultArray() as $link) $vinculados[$link['licenciando_id']][(int) $link['setor_id']] = true;
        }
        $operacoes = [];
        $linhas = [];
        $resumo = ['registros' => count($registros), 'novos_licenciandos' => 0, 'novos_vinculos' => 0, 'ignorados' => 0, 'erros' => 0];
        foreach ($registros as $registro) {
            $d = $registro['dados'];
            $erros = [];
            $key = self::chaveDre($d['dre']);
            foreach (['email' => 100, 'nome_completo' => 100, 'nome_social' => 100, 'dre' => 30, 'endereco' => 50, 'bairro' => 50, 'cep' => 10, 'cidade' => 50, 'telefone1' => 100, 'telefone2' => 100] as $campo => $limite) {
                if (mb_strlen($d[$campo]) > $limite) $erros[] = $campo . ': máximo de ' . $limite . ' caracteres';
            }
            if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido';
            if (mb_strlen($d['nome_completo']) < 3) $erros[] = 'Informe o nome completo';
            if ($d['nome_completo'] === self::EXEMPLO_NOME) $erros[] = 'Substitua ou exclua a linha fictícia do modelo';
            if ($d['dre'] === '') $erros[] = 'DRE obrigatório';
            $uni = $universidades[self::chave($d['universidade'])] ?? [];
            if (count($uni) !== 1) $erros[] = 'Sigla de universidade inexistente ou ambígua';
            $nomesSetores = array_map('trim', explode(',', $d['setor']));
            $professores = $d['professor'] === '' ? array_fill(0, count($nomesSetores), '') : array_map('trim', explode(',', $d['professor']));
            if (count($professores) !== count($nomesSetores)) $erros[] = 'Informe um professor por setor, na mesma ordem';
            $links = [];
            foreach ($nomesSetores as $i => $nomeSetor) {
                $matches = $setores[self::chave($nomeSetor)] ?? [];
                if (count($matches) !== 1) {
                    $erros[] = 'Setor inexistente ou ambíguo: ' . ($nomeSetor ?: '(vazio)');
                    continue;
                }
                if (mb_strlen($professores[$i] ?? '') > 100) $erros[] = 'Professor: máximo de 100 caracteres';
                $setorId = (int) $matches[0]['setor_id'];
                $professor = $professores[$i] ?? '';
                if (isset($links[$setorId]) && $links[$setorId]['professor'] !== $professor) $erros[] = 'Setor repetido com professores diferentes';
                $links[$setorId] = ['setor_id' => $setorId, 'professor' => $professor];
            }
            if (count($existentes[$key] ?? []) > 1) $erros[] = 'Há mais de um cadastro no banco com este DRE';
            $pessoaAtual = $existentes[$key][0] ?? null;
            $pessoal = array_intersect_key($d, array_flip(['email', 'nome_completo', 'nome_social', 'dre', 'endereco', 'bairro', 'cep', 'cidade', 'telefone1', 'telefone2']));
            $pessoal['universidade_id'] = $uni[0]['universidade_id'] ?? null;
            if (!$pessoaAtual && isset($operacoes[$key]) && $operacoes[$key]['dados'] !== $pessoal) $erros[] = 'DRE repetido no arquivo com dados pessoais diferentes';
            $acao = 'erro';
            $mensagem = implode('; ', array_unique($erros));
            if (!$erros) {
                $ja = ($pessoaAtual ? ($vinculados[$pessoaAtual['licenciando_id']] ?? []) : []) + array_fill_keys(array_keys($operacoes[$key]['vinculos'] ?? []), true);
                $novos = array_diff_key($links, $ja);
                if (!$novos) {
                    $acao = 'ignorado';
                    $mensagem = 'Os vínculos deste DRE/setor já existem ou já foram incluídos no arquivo.';
                    $resumo['ignorados']++;
                } else {
                    $primeira = !isset($operacoes[$key]);
                    if ($primeira) $operacoes[$key] = ['id' => $pessoaAtual['licenciando_id'] ?? null, 'dados' => $pessoal, 'vinculos' => []];
                    $operacoes[$key]['vinculos'] += $novos;
                    $acao = !$pessoaAtual && $primeira ? 'novo' : 'vinculo';
                    $mensagem = count($novos) . ' novo(s) vínculo(s).' . ($pessoaAtual ? ' Dados pessoais existentes serão mantidos.' : '');
                    $resumo['novos_vinculos'] += count($novos);
                }
            } else $resumo['erros']++;
            $linhas[] = ['linha' => $registro['linha'], 'dados' => $d, 'acao' => $acao, 'mensagem' => $mensagem];
        }
        foreach ($operacoes as $operacao) if (!$operacao['id']) $resumo['novos_licenciandos']++;
        return ['periodo' => $periodo, 'resumo' => $resumo, 'linhas' => $linhas, 'operacoes' => $operacoes];
    }

    public function executar(array $registros, string $periodo): array
    {
        $db = $this->banco();
        $tabelas = $db->query("SHOW TABLE STATUS WHERE Name IN ('licenciandos', 'licenciandosetor', 'enderecos')")->getResultArray();
        if (count($tabelas) !== 3 || array_filter($tabelas, static function ($tabela) {
            return strcasecmp($tabela['Engine'], 'InnoDB') !== 0;
        })) {
            throw new \InvalidArgumentException('A importação requer as tabelas licenciandos, licenciandosetor e enderecos em InnoDB para garantir a gravação do lote.');
        }
        $db->transBegin();
        try {
            // Revalide o banco na confirmação para considerar cadastros criados após a prévia.
            $plano = $this->planejar($registros, $periodo);
            if ($plano['resumo']['erros']) throw new \InvalidArgumentException('Os dados mudaram ou há erros. Confira o arquivo novamente.');
            $enderecos = new EnderecosModel($db);
            $pessoas = new LicenciandosModel($db);
            $vinculos = new LicenciandoSetorModel($db);
            foreach ($plano['operacoes'] as $op) {
                $id = $op['id'];
                if (!$id) {
                    $endereco = array_intersect_key($op['dados'], array_flip(['endereco', 'bairro', 'cep', 'cidade']));
                    $enderecoId = $enderecos->insert($endereco);
                    if (!$enderecoId) throw new \RuntimeException('Falha ao cadastrar endereço.');
                    $dados = array_diff_key($op['dados'], $endereco);
                    $dados['endereco_id'] = $enderecoId;
                    $id = $pessoas->insert($dados);
                    if (!$id) throw new \RuntimeException('Falha ao cadastrar licenciando.');
                }
                foreach ($op['vinculos'] as $link) {
                    $link += ['licenciando_id' => $id, 'periodo' => $periodo, 'horas_estagio' => 0, 'data_cadastro' => LicenciandoSetorModel::hoje(), 'data_termino' => '0000-00-00', 'situacao' => 'Pendente'];
                    if (!$vinculos->insert($link)) throw new \RuntimeException('Falha ao cadastrar vínculo.');
                }
            }
            if (!$db->transStatus()) throw new \RuntimeException('Falha na gravação do lote.');
            $db->transCommit();
            return $plano;
        } catch (\Throwable $erro) {
            $db->transRollback();
            throw $erro;
        }
    }
}
