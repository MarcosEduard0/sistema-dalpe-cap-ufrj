<?php

namespace App\Controllers;

use App\Models\UniversidadesModel;

class Universidades extends BaseController
{

    protected $universidadeModel;

    public function __construct()
    {
        $this->universidadeModel = new UniversidadesModel();
    }

    /**
     * Página inicial
     *
     */
    public function index()
    {
        $this->data = [
            'titulo' => 'Universidades',
            'universidades' => $this->universidadeModel->getUniversidadesComTotais(),
        ];
        $sessionData['posicao'] = 'Universidade';
        session()->set($sessionData);
        $this->data['body'] = view('universidades/universidades_index', $this->data);
        return $this->render();
    }

    /**
     * Adiciona uma universidade
     *
     */
    public function adicionar()
    {
        session()->set('posicao', 'Universidade');
        $this->data['titulo'] = 'Adicionar';

        if ($this->request->getMethod() == 'post') {
            $this->salvar();
            if (!isset($this->data['validation'])) {
                session()->setFlashdata('msg', msgbox('success', 'A universidade foi adicionado.'));
                return redirect()->to('universidades');
            }
        }
        $this->data['body'] = view('universidades/universidades_adicionar', $this->data);
        return $this->render();
    }

    /**
     * Edição/vizualizar uma universidade
     *
     */
    public function editar($id = null)
    {
        session()->set('posicao', 'Universidade');
        $this->data = [
            'titulo' => 'Editar',
            'universidade' => $this->universidadeModel->getUniversidades($id),
        ];

        if (empty($this->data['universidade'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        if ($this->request->getMethod() == 'post') {
            $this->salvar();
            if (!isset($this->data['validation'])) {
                session()->setFlashdata('msg', msgbox('success', 'A universidade foi atualizado.'));
                return redirect()->to('universidades/editar/' . $id);
            }
        }
        $this->data['body'] = view('universidades/universidades_adicionar',  $this->data);
        return $this->render();
    }

    /**
     * Salva as informações da universidade
     *
     */
    public function salvar()
    {
        $this->validation->setRule('sigla', 'Sigla', 'required|min_length[3]|max_length[20]');
        $this->validation->setRule('nome', 'Nome', 'required|min_length[3]|max_length[50]');
        $data = [
            'universidade_id' => $this->request->getVar('universidade_id'),
            'sigla' => $this->request->getVar('sigla'),
            'nome' => $this->request->getVar('nome'),
        ];

        if ($this->validation->withRequest($this->request)->run()) {
            $this->universidadeModel->save($data);
        } else {
            $this->data['validation'] = $this->validation;
        }
    }

    /**
     * Deleta uma universidade
     *
     */
    public function excluir($id = null)
    {
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $universidade = $db->query('SELECT universidade_id FROM universidades WHERE universidade_id = ? FOR UPDATE', [$id])->getRowArray();
            if (!$universidade) throw new \CodeIgniter\Exceptions\PageNotFoundException();
            if ($db->table('licenciandos')->where('universidade_id', $id)->countAllResults()) {
                $db->transRollback();
                session()->setFlashdata('msg', msgbox('danger', 'Esta universidade possui licenciandos vinculados e não pode ser excluída.'));
                return redirect()->to('universidades');
            }
            if (!$this->universidadeModel->delete($id) || !$db->transStatus()) throw new \RuntimeException('Falha na exclusão.');
            $db->transCommit();
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $erro) {
            $db->transRollback(); throw $erro;
        } catch (\Throwable $erro) {
            $db->transRollback();
            log_message('error', 'Falha ao excluir universidade: {erro}', ['erro' => $erro->getMessage()]);
            session()->setFlashdata('msg', msgbox('danger', 'Não foi possível excluir a universidade. Tente novamente.'));
            return redirect()->to('universidades');
        }
        session()->setFlashdata('msg', msgbox('success', 'A universidade foi excluída.'));
        return redirect()->to('universidades');
    }
}
