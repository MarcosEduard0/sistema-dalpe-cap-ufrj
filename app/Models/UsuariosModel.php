<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuariosModel extends Model
{

    //Atributos de config
    protected $table = 'usuarios';
    protected $primaryKey = 'usuario_id';
    protected $allowedFields = ['usuario', 'senha', 'nome_completo', 'nome_social', 'email', 'siape', 'cargo', 'telefone', 'assinatura', 'authlevel', 'ultimo_login'];
    // protected $beforeInsert = ['beforeInsert'];
    // protected $beforeUpdate = ['beforeUpdate'];

    //metodo get
    public function getUsuariosParaListagem()
    {
        return $this->select('usuario_id, usuario, nome_completo, nome_social, cargo, authlevel, ultimo_login')->findAll();
    }

    public function getUsuarios($id = false)
    {

        if ($id === false)
            return $this->findAll();
        else {
            return $this->asArray()
                ->where(['usuario_id' => $id])
                ->first();
        }
    }

    //metodo get
    public function getUsuarioByUser($user)
    {

        return $this->asArray()
            ->where(['usuario' => $user])
            ->first();
    }
}
