<?php

namespace App\Controllers;

use App\Models\DashboardModel;

class Home extends BaseController
{
    public function index()
    {
        $this->data = (new DashboardModel())->getResumo();
        $this->data['titulo'] = 'Início';
        session()->set('posicao', 'Home');
        $this->data['body'] = view('home/home_index', $this->data);
        return $this->render();
    }
}
