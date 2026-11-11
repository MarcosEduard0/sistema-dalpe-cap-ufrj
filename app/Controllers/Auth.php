<?php

namespace App\Controllers;

use App\Models\UsuariosModel;

class Auth extends BaseController
{

    protected $usuariosModel;

    public function __construct()
    {
        $this->usuariosModel = new UsuariosModel();
    }
    /**
     * página de login
     *
     */
    public function index()
    {
        // Se o usuário já tem sessão e está logado, redireciona
        if (isset($_COOKIE['ci_session'])) {
            // Somente agora, depois de garantir que o cookie existe, usamos session()
            if (session()->get('logged_in')) {
                return redirect()->to('home');
            }
            $msg = session()->getFlashdata('msg');
        }

        $this->data['titulo'] = 'Login';
        $this->data['msg'] = isset($msg) ? $msg : null;
        $this->data['body'] = view('login/login_index', $this->data);
        return $this->render();
    }



    /**
     * Autenticação de usuario
     *
     */
    public function login()
    {
        $user = $this->request->getVar('usuario');
        $senha = $this->request->getVar('senha');
        $this->data['usuario'] = $this->usuariosModel->getUsuarioByUser($user);
        session()->markAsFlashdata('msg');

        if (!empty($this->data['usuario'])) {
            if (password_verify($senha, $this->data['usuario']['senha'])) {
                $sessionData = [
                    'usuario' =>   $this->data['usuario'],
                    'authlevel' => $this->data['usuario']['authlevel'],
                    'logged_in' => TRUE,
                ];
                session()->set($sessionData);
                $this->usuariosModel->update($this->data['usuario']['usuario_id'], ['ultimo_login'  => date('Y-m-d H:i:s')]);
                return redirect('home');
            }
        }
        session()->setFlashdata('msg', msgbox('danger', 'Usuário e/ou senha incorretos.'));
        return redirect()->to('login');
    }

    /**
     * Logout de usuario
     *
     */
    public function logout()
    {
        if (session()->has('logged_in') && session()->get('logged_in')) {
            session()->destroy();
            return redirect()->to('login');
        }
    }
}
