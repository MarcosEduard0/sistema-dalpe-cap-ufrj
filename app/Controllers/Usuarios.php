<?php

namespace App\Controllers;

use App\Models\UsuariosModel;

class Usuarios extends BaseController
{

    protected $usuariosModel;

    public function __construct()
    {
        $this->usuariosModel = new UsuariosModel();
    }

    /**
     * Pagina inicial
     *
     */
    public function index()
    {
        $this->data = [
            'titulo' => 'Usuários',
            'usuarios' => $this->usuariosModel->getUsuariosParaListagem(),
        ];
        $sessionData['posicao'] = 'Usuarios';
        if (isset($_COOKIE['ci_session'])) session()->set($sessionData);

        $this->data['body'] = view('usuarios/usuarios_index', $this->data);
        return $this->render();
    }

    /**
     * Editar conta de usuário
     *
     */
    public function editar($id = null)
    {
        session()->set('posicao', 'Usuarios');
        $this->data = [
            'titulo' => 'Editar usuário',
            'usuario' => $this->usuariosModel->getUsuarios($id),
        ];

        if (empty($this->data['usuario'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        if ($this->request->getMethod() == 'post') {
            $this->salvar($id);
            if (!isset($this->data['validation'])) {
                session()->setFlashdata('msg', msgbox('success', 'O usuário foi atualizado.'));
                return redirect()->to('usuarios/editar/' . $id);
            }
        }
        $this->data['body'] = view('usuarios/usuarios_adicionar',  $this->data);
        return $this->render();
    }

    /**
     * Adicionar um usuario
     *
     */
    public function adicionar()
    {
        session()->set('posicao', 'Usuarios');
        $this->data['titulo'] = 'Adicionar usuário';

        if ($this->request->getMethod() == 'post') {
            $this->salvar();
            if (!isset($this->data['validation'])) {
                session()->setFlashdata('msg', msgbox('success', 'O usuário foi salvo.'));
                return redirect()->to('usuarios');
            }
        }
        $this->data['body'] = view('usuarios/usuarios_adicionar', $this->data);
        return $this->render();
    }

    /**
     * Salva os dados do usuario
     *
     */
    public function salvar($idRota = null)
    {
        $usuarioId = $this->request->getPost('usuario_id');
        if ($idRota !== null && (string) $usuarioId !== (string) $idRota) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        $atual = $usuarioId ? $this->usuariosModel->find($usuarioId) : null;
        if ($usuarioId && !$atual) throw new \CodeIgniter\Exceptions\PageNotFoundException();
        $arquivo = $this->request->getFile('assinatura');
        $temArquivo = $arquivo && $arquivo->getError() !== UPLOAD_ERR_NO_FILE;
        $remover = (bool) $this->request->getPost('deleteAssinatura');
        $login = $this->request->getPost('usuario');
        $siape = $this->request->getPost('siape');
        $senha1 = $this->request->getPost('senha1') ?? '';
        $senha2 = $this->request->getPost('senha2') ?? '';

        $regraLogin = 'required|min_length[3]|max_length[50]|regex_match[/^[A-Za-z0-9-_.@]+$/]';
        if (!$atual || (string) $atual['usuario'] !== (string) $login) $regraLogin .= '|is_unique[usuarios.usuario]';
        $regraSiape = 'required|min_length[3]|max_length[8]|integer';
        if (!$atual || (string) $atual['siape'] !== (string) $siape) $regraSiape .= '|is_unique[usuarios.siape]';
        $this->validation->setRules([
            'usuario' => ['label' => 'Usuário', 'rules' => $regraLogin],
            'siape' => ['label' => 'SIAPE', 'rules' => $regraSiape],
            'nome_completo' => ['label' => 'Nome completo', 'rules' => 'required|min_length[3]|max_length[50]'],
            'nome_social' => ['label' => 'Nome social', 'rules' => 'permit_empty|max_length[255]'],
            'cargo' => ['label' => 'Cargo', 'rules' => 'required|min_length[3]|max_length[50]'],
            'authlevel' => ['label' => 'Tipo de usuário', 'rules' => 'required|in_list[0,1]'],
            'email' => ['label' => 'E-mail', 'rules' => 'permit_empty|valid_email|max_length[255]'],
            'telefone' => ['label' => 'Telefone', 'rules' => 'permit_empty|max_length[255]'],
        ]);
        if (!$atual || $senha1 !== '' || $senha2 !== '') {
            $this->validation->setRule('senha1', 'Senha', 'trim|required|min_length[3]|matches[senha2]');
            $this->validation->setRule('senha2', 'Confirmar senha', 'trim|required|min_length[3]');
        }
        if ($temArquivo) {
            $this->validation->setRule('assinatura', 'Assinatura', 'uploaded[assinatura]|is_image[assinatura]|mime_in[assinatura,image/jpeg,image/png]|ext_in[assinatura,jpg,jpeg,png]|max_size[assinatura,4096]');
        }
        if (!$this->validation->withRequest($this->request)->run()) {
            $this->data['validation'] = $this->validation;
            return;
        }
        if ($temArquivo && $remover) {
            $this->validation->setError('assinatura', 'Escolha substituir ou remover a assinatura, uma opção por vez.');
            $this->data['validation'] = $this->validation;
            return;
        }
        $dados = [
            'usuario_id' => $usuarioId ?: null, 'usuario' => $login,
            'nome_completo' => $this->request->getPost('nome_completo'),
            'nome_social' => $this->request->getPost('nome_social') ?? '',
            'siape' => $siape, 'cargo' => $this->request->getPost('cargo'),
            'email' => $this->request->getPost('email') ?? '',
            'telefone' => $this->request->getPost('telefone') ?? '',
            'authlevel' => $this->request->getPost('authlevel'),
        ];
        if ($senha1 !== '') $dados['senha'] = password_hash($senha1, PASSWORD_DEFAULT);
        $novoNome = null;
        try {
            if ($temArquivo) {
                $novoNome = bin2hex(random_bytes(16)) . '.' . strtolower($arquivo->getClientExtension());
                $arquivo->move(FCPATH . 'public/assets/uploads/', $novoNome);
                $dados['assinatura'] = $novoNome;
            } elseif ($remover) {
                $dados['assinatura'] = null;
            }
            if (!$this->usuariosModel->save($dados)) throw new \RuntimeException('Falha ao salvar o cadastro.');
        } catch (\Throwable $erro) {
            if ($novoNome) $this->apagarAssinatura($novoNome);
            log_message('error', 'Falha ao salvar usuário: {erro}', ['erro' => $erro->getMessage()]);
            $this->validation->setError('cadastro', 'Não foi possível salvar o cadastro. Tente novamente.');
            $this->data['validation'] = $this->validation;
            return;
        }
        // Somente remova a imagem antiga depois que o cadastro foi salvo.
        if (($temArquivo || $remover) && !empty($atual['assinatura'])) {
            $this->apagarAssinatura($atual['assinatura']);
        }
        if ($usuarioId && (string) (session()->get('usuario')['usuario_id'] ?? '') === (string) $usuarioId) {
            $sessaoAtual = $this->usuariosModel->find($usuarioId);
            session()->set(['usuario' => $sessaoAtual, 'authlevel' => $sessaoAtual['authlevel']]);
        }
    }

    private function apagarAssinatura($nome)
    {
        if (!$nome || basename($nome) !== $nome) return;
        $caminho = FCPATH . 'public/assets/uploads/' . $nome;
        if (is_file($caminho) && !@unlink($caminho)) log_message('error', 'Não foi possível remover uma assinatura antiga.');
    }

    /**
     * Delete o usuario
     *
     */
    public function excluir($id = null)
    {
        $this->usuariosModel->delete($id);
        session()->setFlashdata('msg', msgbox('success', 'O usuário foi deletado.'));
        return redirect()->to('usuarios');
    }
    /**
     * Limpa as sessoes antigas
     *
     */
    private function limparSessoesAntigas($minutos = 5)
    {
        $path = WRITEPATH . 'session/';
        $maxTime = time() - ($minutos * 60);

        foreach (glob($path . 'ci_session*') as $file) {
            if (filemtime($file) < $maxTime) {
                @unlink($file); // Use @ para ignorar erro se não conseguir apagar
            }
        }
    }
}
