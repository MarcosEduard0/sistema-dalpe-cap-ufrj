<?php

namespace App\Controllers;

use App\Models\LicenciandosModel;
use App\Models\SetoresModel;
use App\Models\DocumentosModel;
use App\Models\UniversidadesModel;
use App\Models\EnderecosModel;
use App\Models\LicenciandoSetorModel;

class Licenciandos extends BaseController
{

    protected $licenciandosModel;
    protected $setoresModal;
    protected $universidadesModel;
    protected $documentoModel;
    protected $enderecoModel;
    protected $documentosModel;
    protected $licenciandoSetorModel;

    public function __construct()
    {
        $this->licenciandosModel = new LicenciandosModel();
        $this->setoresModal = new SetoresModel();
        $this->documentosModel = new DocumentosModel();
        $this->universidadesModel = new UniversidadesModel();
        $this->enderecoModel = new EnderecosModel();
        $this->licenciandoSetorModel = new LicenciandoSetorModel();
    }

    public function index()
    {
        $pessoas = $this->licenciandosModel->getLicenciandosParaListagem();
        $vinculosPorPessoa = [];
        foreach ($this->licenciandoSetorModel->getLicenciandosSetores() as $vinculo) {
            $vinculosPorPessoa[$vinculo['licenciando_id']][] = $vinculo;
        }
        $resumo = ['pessoas' => count($pessoas), 'vinculos' => 0, 'concluidos' => 0, 'ativos' => 0, 'pendencias' => 0];
        foreach ($pessoas as &$pessoa) {
            $pessoa['vinculos'] = $vinculosPorPessoa[$pessoa['licenciando_id']] ?? [];
            foreach ($pessoa['vinculos'] as $vinculo) {
                $resumo['vinculos']++;
                if ($vinculo['situacao'] === 'Concluido') $resumo['concluidos']++;
                if ($vinculo['situacao'] === 'Ativo') $resumo['ativos']++;
            }
            if (!$pessoa['universidade_valida_id'] || !$pessoa['vinculos'] || trim($pessoa['email'] ?? '') === '') $resumo['pendencias']++;
        }
        unset($pessoa);
        $this->data = [
            'titulo' => 'Licenciandos',
            'licenciandos' => $pessoas,
            'resumo' => $resumo,
            'universidadesFiltro' => $this->universidadesModel->getUniversidades(),
            'setoresFiltro' => $this->setoresModal->getSetores(),
        ];
        session()->set('posicao', 'Licenciandos');
        $this->data['body'] = view('licenciandos/licenciandos_index', $this->data);
        return $this->render();
    }
    public function setor_select($licenciando_id)
    {
        $licenciando_setor = $this->request->getVar('licenciando_setor');
        return redirect()->to('licenciandos/editar/' . $licenciando_id . '/' . $licenciando_setor);
    }
    public function adicionar()
    {
        session()->set('posicao', 'Licenciandos');
        $this->data['universidades'] = $this->universidadesModel->getUniversidades();
        $this->data['titulo'] = 'Adicionar';
        $this->data['setores'] = $this->setoresModal->getSetores();
        $this->data['periodos'] = get_years();

        if ($this->request->getMethod() == 'post') {
            $this->salvar();
            if (!isset($this->data['validation'])) {
                session()->setFlashdata('msg', msgbox('success', 'O licenciando foi salvo.'));
                return redirect()->to('licenciandos');
            }
        }
        $this->data['componente_card_usuario'] = view('licenciandos/componentes/card_usuario', $this->data);
        $this->data['body'] = view('licenciandos/licenciandos_adicionar', $this->data);
        return $this->render();
    }

    public function editar($id = null, $licenciandoSetor_id = null)
    {
        session()->set('posicao', 'Licenciandos');

        $this->data['titulo'] = 'Editar';
        $this->data['licenciando'] = $this->licenciandosModel->joinLicenciandoEndereco($id);
        $this->data['periodos'] = get_years();


        if (!is_null($licenciandoSetor_id) && !is_null($id)) {
            $resultLicenSetor = $this->licenciandoSetorModel->find($licenciandoSetor_id);
            $this->data['licenciandoSetores'] = $this->licenciandoSetorModel->getLicenciandosSetores($id);
            $this->data['setor_data'] = $this->licenciandoSetorModel->find($licenciandoSetor_id);
        }

        if (empty($this->data['licenciando']) || empty($resultLicenSetor) || (int) $resultLicenSetor['licenciando_id'] !== (int) $id) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $this->data['licenciandoSetor_id'] = $licenciandoSetor_id;
        $this->data['licenciando']['universidade_sigla'] = $this->universidadesModel->getUniversidades($this->data['licenciando']['universidade_id'])['sigla'];
        $this->data['universidades'] = $this->universidadesModel->getUniversidades();
        $this->data['documentos'] = $this->documentosModel->getDocumentos();
        $this->data['setores'] = $this->setoresModal->getSetores();
        $this->data['licenciando']['licenciando_setor'] = array();

        if ($this->request->getMethod() == 'post') {
            $this->salvar($licenciandoSetor_id);
            if (!isset($this->data['validation'])) {
                session()->setFlashdata('msg', msgbox('success', 'O licenciando foi atualizado.'));
                return redirect()->to('licenciandos/editar/' . $id . '/' . $licenciandoSetor_id);
            }
        }

        $this->data['componente_card_usuario'] = view('licenciandos/componentes/card_usuario', $this->data);
        $this->data['componente_documentos'] = view('licenciandos/componentes/documentos', $this->data);
        $this->data['body'] = view('licenciandos/licenciandos_adicionar',  $this->data);
        return $this->render();
    }

    public function excluir($id = null)
    {
        $pessoa = $this->licenciandosModel->find($id);
        if (!$pessoa) throw new \CodeIgniter\Exceptions\PageNotFoundException();
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            if (!$this->licenciandosModel->delete($id)) throw new \RuntimeException('Falha ao remover o cadastro.');
            if (!$db->transStatus()) throw new \RuntimeException('Falha na exclusão.');
            $db->transCommit();
        } catch (\Throwable $erro) {
            $db->transRollback();
            log_message('error', 'Falha ao excluir licenciando: {erro}', ['erro' => $erro->getMessage()]);
            session()->setFlashdata('msg', msgbox('danger', 'Não foi possível excluir o cadastro. Tente novamente.'));
            return redirect()->to('licenciandos');
        }
        session()->setFlashdata('msg', msgbox('success', 'O licenciando foi excluído.'));
        return redirect()->to('licenciandos');
    }

    public function salvar($licenciandoSetor_id = null)
    {
        $dreNovo = $this->request->getVar('dre');
        $licenciandoId = $this->request->getVar('licenciando_id');
        $enderecoId = $this->request->getVar('endereco_id');
        $situacao = $this->request->getPost('situacao');
        if ($licenciandoSetor_id !== null) {
            $vinculoAtual = $this->licenciandoSetorModel->find($licenciandoSetor_id);
            if (!$vinculoAtual || (int) $vinculoAtual['licenciando_id'] !== (int) $licenciandoId) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException();
            }
            $situacao = $situacao ?? $vinculoAtual['situacao_manual'];
        } elseif (empty($licenciandoId)) {
            $situacao = $situacao ?? [];
        } else {
            // A situação só pode ser atualizada com um vínculo identificado.
            $this->validation->setError('situacao', 'Selecione o vínculo com o setor para editar.');
            $this->data['validation'] = $this->validation;
            return;
        }
        $valoresSituacao = is_array($situacao) ? $situacao : [$situacao];
        $formatoValido = empty($licenciandoId) ? is_array($situacao) : !is_array($situacao);
        foreach ($valoresSituacao as $valor) {
            $formatoValido = $formatoValido && ($valor === null || $valor === '' || in_array($valor, ['Ativo', 'Inativo', 'Pendente'], true));
        }
        if (!$formatoValido) {
            $this->validation->setError('situacao', 'Selecione Ativo, Inativo ou Pendente. Concluído é calculado pela data de conclusão do vínculo.');
            $this->data['validation'] = $this->validation;
            return;
        }

        $this->validation->setRule('nome_completo', 'Nome Completo', 'required|min_length[3]|max_length[100]');
        $this->validation->setRule('dre', 'DRE', 'required|max_length[32]|is_unique[licenciandos.dre]');
        $this->validation->setRule('setor_id', 'Setor Curricular', 'required');
        $this->validation->setRule('universidade_id', 'Universidade', 'required');

        if (!empty($licenciandoId)) {
            $licenciando = $this->licenciandosModel->getLicenciandos($licenciandoId);
            if (!$licenciando) throw new \CodeIgniter\Exceptions\PageNotFoundException();
            $dreAntigo = $licenciando['dre'];

            if (!strcmp($dreAntigo, $dreNovo))
                $this->validation->setRule('dre', 'DRE', 'required|max_length[32]');
        }

        $dataLicenciando = [
            'licenciando_id' =>  $licenciandoId,
            'dre' => $dreNovo,
            'nome_completo' => $this->request->getVar('nome_completo'),
            'nome_social' => $this->request->getVar('nome_social'),
            'email' => $this->request->getVar('email'),
            'telefone1' => $this->request->getVar('telefone1'),
            'telefone2' => $this->request->getVar('telefone2'),
            'endereco_id' => $enderecoId,
            'universidade_id' => $this->request->getVar('universidade_id'),
            'setores_id' => $this->request->getVar('setor_id'),
            'observacao' => $this->request->getVar('observacao'),

        ];

        $dataSetor = [
            'setor_id' =>  $dataLicenciando['setores_id'],
            'horas_estagio' => $this->request->getVar('horas_estagio'),
            'data_inicio' => $this->request->getVar('data_inicio'),
            'data_termino' => $this->request->getVar('data_termino'),
            'professor' => $this->request->getVar('professor'),
            'periodo' => $this->request->getVar('periodo'),
        ];

        $dataAdress = [
            'endereco_id' => $enderecoId,
            'endereco' => $this->request->getVar('endereco'),
            'bairro' => $this->request->getVar('bairro'),
            'cep' => $this->request->getVar('cep'),
            'cidade' => $this->request->getVar('cidade'),
        ];


        if ($this->validation->withRequest($this->request)->run()) {

            // Atualizando informações do setor do licenciando
            if (!is_null($licenciandoSetor_id)) {
                $result = $this->licenciandoSetorModel->find($licenciandoSetor_id);
                $dataSetor['setor_id'] = $result['setor_id'];
                $dataSetor['id'] =  $result['id'];
                $dataSetor['situacao'] = $situacao === '' ? null : $situacao;
                $this->licenciandoSetorModel->save($dataSetor);
            }

            // Atualizando/Salvando informações do endereço do licenciando
            $this->enderecoModel->save($dataAdress);

            // Caso estaja adicionando um licenciando, pegue o id do ultimo licenciando salvo
            if (empty($enderecoId)) {
                $dataLicenciando['endereco_id'] = $this->enderecoModel->getLastEnderecoSalvo();
            }

            // Salvando as informações do licenciando
            $this->licenciandosModel->save($dataLicenciando);

            // Adicionando as informaçãoes dos setores do licenciando
            if (empty($licenciandoId)) {
                $save = $dataSetor;
                $save['licenciando_id'] =  $this->licenciandosModel->getLastLicenciandoSave();

                for ($i = 0; $i < count($dataSetor['setor_id']); $i++) {
                    $save['setor_id'] = $dataSetor['setor_id'][$i];
                    $save['data_cadastro'] = date('Y-m-d');
                    $save['horas_estagio'] = $dataSetor['horas_estagio'][$i];
                    $save['data_inicio'] = $dataSetor['data_inicio'][$i] ?? null;
                    $save['data_termino'] = $dataSetor['data_termino'][$i];
                    $save['situacao'] = $situacao[$i] ?? 'Pendente';
                    $save['professor'] = $dataSetor['professor'][$i];
                    $save['periodo'] = $dataSetor['periodo'][$i];

                    $this->licenciandoSetorModel->save($save);
                }
            }

            if (!is_null($licenciandoSetor_id)) {
                $dataLicenciandoSetor = $this->licenciandoSetorModel->getLicenciandosSetores($licenciandoId);
                $newSetoresId = array();
                $oldSetoresId = $dataLicenciando['setores_id'];

                foreach ($dataLicenciandoSetor as $licenciando_setor) {
                    array_push($newSetoresId, $licenciando_setor['setor_id']);
                }


                $adicionar = array_diff($oldSetoresId, $newSetoresId);
                $remover = array_diff($newSetoresId, $oldSetoresId);


                $dataSetorSave['licenciando_id'] = $licenciandoId;
                $dataSetorSave['data_cadastro'] = date('Y-m-d');
                if (count($adicionar) > 0) {
                    foreach ($adicionar as $id) {
                        $dataSetorSave['setor_id'] = $id;
                        $this->licenciandoSetorModel->save($dataSetorSave);
                    }
                }
                if (count($remover) > 0) {
                    foreach ($remover as $id) {
                        $dataSetorSave['setor_id'] = $id;
                        $this->licenciandoSetorModel->where([
                            'licenciando_id' => $dataSetorSave['licenciando_id'],
                            'setor_id' => $dataSetorSave['setor_id']
                        ])->delete();
                    }
                }
            }
        } else {
            $this->data['validation'] = $this->validation;
        }
    }

    /**
     * Primeira página de importação.
     * Se GET, mostra o formulário. Se for POST, lidar com upload e importação de CSV.
     *
     */
    public function importar()
    {
        session()->set('posicao', 'Licenciandos');
        $this->limparImportacaoPendente();
        $this->data = [
            'titulo' => 'Importar licenciandos',
            'periodos' => get_years(),
            'cabecalhosImportacao' => \App\Libraries\ImportacaoLicenciandos::CABECALHOS,
            'exemploImportacao' => $this->exemploImportacao(),
        ];
        if ($this->request->getMethod() === 'post') {
            $this->validation->setRules(['userfile' => ['label' => 'Arquivo CSV', 'rules' => 'uploaded[userfile]|max_size[userfile,10240]|ext_in[userfile,csv]'], 'periodo' => ['label' => 'Ano de referência', 'rules' => 'required|integer']]);
            if (!$this->validation->withRequest($this->request)->run()) $this->data['validation'] = $this->validation;
            else {
                try {
                    $servico = new \App\Libraries\ImportacaoLicenciandos();
                    $registros = $servico->ler($this->request->getFile('userfile')->getTempName());
                    $periodo = (string) $this->request->getPost('periodo');
                    $plano = $servico->planejar($registros, $periodo);
                    $token = null;
                    if (!$plano['resumo']['erros'] && $plano['resumo']['novos_vinculos']) {
                        $token = bin2hex(random_bytes(32));
                        $payload = ['periodo' => $periodo, 'registros' => $registros, 'expira' => time() + 1800, 'usuario' => (int) (session()->get('usuario')['usuario_id'] ?? 0)];
                        if (file_put_contents($this->pastaImportacoes() . $token . '.json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX) === false) throw new \RuntimeException('Falha ao preparar a conferência.');
                        session()->set('lic_import_token', $token);
                    }
                    $this->data += ['plano' => $plano, 'tokenImportacao' => $token];
                    $this->data['body'] = view('licenciandos/import/conferencia', $this->data);
                    return $this->render();
                } catch (\InvalidArgumentException $erro) {
                    $this->data['erroImportacao'] = $erro->getMessage();
                } catch (\Throwable $erro) {
                    log_message('error', 'Falha ao conferir CSV: {erro}', ['erro' => $erro->getMessage()]);
                    $this->data['erroImportacao'] = 'Não foi possível conferir o arquivo. Tente novamente.';
                }
            }
        }
        $this->data['body'] = view('licenciandos/import/index', $this->data);
        return $this->render();
    }

    private function exemploImportacao(): array
    {
        $uni = $this->universidadesModel->orderBy('universidade_id')->first();
        $setor = $this->setoresModal->orderBy('setor_id')->first();
        return ['licenciando@example.com', \App\Libraries\ImportacaoLicenciandos::EXEMPLO_NOME, '', '000000000', $uni['sigla'] ?? 'SIGLA', 'Professor(a) de exemplo', $setor['nome'] ?? 'SETOR', 'Rua de Exemplo, 100', 'Bairro de Exemplo', '00000-000', 'Rio de Janeiro', '21999990000', ''];
    }

    public function modelo_importacao()
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, \App\Libraries\ImportacaoLicenciandos::CABECALHOS, ';', '"', '');
        fputcsv($stream, $this->exemploImportacao(), ';', '"', '');
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        return $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="modelo_importacao_licenciandos.csv"')->setBody($csv);
    }

    private function pastaImportacoes(): string
    {
        $pasta = WRITEPATH . 'importacoes_licenciandos' . DIRECTORY_SEPARATOR;
        if (!is_dir($pasta) && !mkdir($pasta, 0775, true) && !is_dir($pasta)) throw new \RuntimeException('Pasta temporária indisponível.');
        return $pasta;
    }

    private function limparImportacaoPendente(): void
    {
        $token = session()->get('lic_import_token');
        if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token)) {
            $arquivo = WRITEPATH . 'importacoes_licenciandos' . DIRECTORY_SEPARATOR . $token . '.json';
            if (is_file($arquivo)) @unlink($arquivo);
        }
        session()->remove('lic_import_token');
    }

    public function confirmar_importacao()
    {
        $gravado = false;
        $token = $this->request->getPost('token_importacao');
        $esperado = session()->get('lic_import_token');
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token) || !is_string($esperado) || !hash_equals($esperado, $token)) {
            session()->setFlashdata('msg', msgbox('danger', 'Conferência inválida ou já utilizada. Selecione o arquivo novamente.'));
            return redirect()->to('licenciandos/importar');
        }
        try {
            $arquivo = $this->pastaImportacoes() . $token . '.json';
            $payload = is_file($arquivo) ? json_decode(file_get_contents($arquivo), true, 512, JSON_THROW_ON_ERROR) : null;
            if (!$payload || $payload['expira'] < time() || $payload['usuario'] !== (int) (session()->get('usuario')['usuario_id'] ?? 0)) throw new \InvalidArgumentException('A conferência expirou. Selecione o arquivo novamente.');
            $this->limparImportacaoPendente();
            $plano = (new \App\Libraries\ImportacaoLicenciandos())->executar($payload['registros'], $payload['periodo']);
            $gravado = true;
            $linhas = array_map(static function ($linha) {
                return ['linha' => $linha['linha'], 'nome' => $linha['dados']['nome_completo'], 'dre' => $linha['dados']['dre'], 'acao' => $linha['acao'], 'mensagem' => $linha['mensagem']];
            }, $plano['linhas']);
            session()->setFlashdata('lic_import_resultados', ['resumo' => $plano['resumo'], 'linhas' => $linhas]);
            return redirect()->to('licenciandos/import_results');
        } catch (\InvalidArgumentException $erro) {
            $this->limparImportacaoPendente();
            session()->setFlashdata('msg', msgbox('danger', esc($erro->getMessage())));
        } catch (\Throwable $erro) {
            $this->limparImportacaoPendente();
            log_message('error', 'Falha na importação: {erro}', ['erro' => $erro->getMessage()]);
            session()->setFlashdata('msg', msgbox('danger', $gravado
                ? 'O lote foi gravado, mas não foi possível exibir o resultado. Consulte a listagem antes de reenviar.'
                : 'A importação não foi concluída. Nenhum registro deste lote foi gravado.'));
        }
        return redirect()->to('licenciandos/importar');
    }

    public function import_results()
    {
        session()->set('posicao', 'Licenciandos');
        $resultado = session()->getFlashdata('lic_import_resultados');
        if (!$resultado) return redirect()->to('licenciandos/importar');
        $this->data = ['titulo' => 'Resultado da importação', 'resultado' => $resultado];
        $this->data['body'] = view('licenciandos/import/results', $this->data);
        return $this->render();
    }
}
