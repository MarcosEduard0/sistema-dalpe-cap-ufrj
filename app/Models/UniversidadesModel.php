<?php

namespace App\Models;

use CodeIgniter\Model;

class UniversidadesModel extends Model
{

    //Atributos de config
    protected $table = 'universidades';
    protected $primaryKey = 'universidade_id';
    protected $allowedFields = ['sigla', 'nome'];

    //metodo GET
    public function getUniversidadesComTotais()
    {
        return $this->select('universidades.universidade_id, universidades.sigla, universidades.nome, COUNT(licenciandos.licenciando_id) AS total_licenciandos')
            ->join('licenciandos', 'licenciandos.universidade_id = universidades.universidade_id', 'left')
            ->groupBy(['universidades.universidade_id', 'universidades.sigla', 'universidades.nome'])->findAll();
    }

    public function getUniversidades($id = false)
    {
        if ($id === false)
            return $this->findAll();
        else {
            return $this->asArray()
                ->where(['universidade_id' => $id])
                ->first();
        }
    }

    public function getIdBySigla($sigla)
    {
        return $this->asArray()
            ->where('sigla', $sigla)->first();
    }
}
