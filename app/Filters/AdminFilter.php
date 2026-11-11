<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('MY_html_helper');

        // Verifica se está logado e se o tipo de usuário é administrador
        if (!session()->has('logged_in') || session()->get('authlevel') != ADMINISTRADOR) {
            session()->setFlashdata('msg', msgbox('danger', 'Você não tem permissão para acessar essa página.'));
            return redirect()->to('home');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nada aqui normalmente
    }
}
